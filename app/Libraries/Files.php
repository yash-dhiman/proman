<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use App\Models\Api\Files as filesModel;
use Illuminate\Support\Str;

class Files 
{
    protected $request;
    protected $companyId;
    protected $userId;
    protected $projectId;

    public function __construct(Request $request)
    {
        $this->request      = $request;
        $this->companyId    = get_company_id();
        $this->userId       = get_user_id();
        $this->projectId    = deobfuscate($this->request->project_id);
    }

    public function extractInlineImages(string $connectedWith='TC', bool $replaceUrl=true, bool $showAsAttachment=false)
    {    
        $html                   = $this->request->description;
        
        // Create DOM from string
        $doc                    = new \DOMDocument();
        libxml_use_internal_errors(true);
        $html                   = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $doc->loadHTML('<div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS); // loads your html

        $xpath                  = new \DOMXPath($doc);
        $element                = 'img';
        $attribute              = 'src';
        $nodelist               = $xpath->query("//" . $element); // find your image
        $attachments            = array();

        for ($i = 0; $i < $nodelist->length; $i++)
        {
            $node               = $nodelist->item($i);
            $imageData          = $node->attributes->getNamedItem($attribute)->nodeValue;

            if (preg_match('/data:([^;]*);base64,(.*)/', $imageData, $imageData))
            {
                $fileExtension       = str_replace('image/', '', $imageData[1]);

                // get image real name from title/alt if set
                $title          = $node->attributes->getNamedItem('title');
                $alt            = $node->attributes->getNamedItem('alt');
                $imagetitle     = $title ? trim($node->attributes->getNamedItem('title')->nodeValue) : '';
                $imageAlt       = $alt ? trim($node->attributes->getNamedItem('alt')->nodeValue) : '';

                if (!empty($imagetitle))
                {
                    $realName   = $imagetitle . '.' . $fileExtension;
                }
                elseif (!empty($imageAlt))
                {
                    $realName   = $imageAlt . '.' . $fileExtension;
                }

                $fileDestination = 'C:/xampp/htdocs/proman_files/';

                //checking for project directory path
                //create if not exist
                if (!file_exists($fileDestination . $this->companyId))
                {
                    mkdir($fileDestination . $this->companyId);
                }

                //if project mentioned in request if not exist create folder and move uploaded file to the folder
                //otherwise move file to tmp folder
                if ($this->projectId)
                {
                    $fileDestination    = $fileDestination . $this->companyId. '/' . $this->projectId. '/';
                }
                else
                {
                    $fileDestination    = $fileDestination . $this->companyId. '/tmp/';
                }

                if (!file_exists($fileDestination))
                {
                    mkdir($fileDestination);
                }

                chmod($fileDestination, 0755);
                $randomChars        = Str::random(10);
                $fileName           = md5($this->companyId . '+' . $this->userId) . time() . $randomChars . '.' . $fileExtension;
                $realName           = !empty($realName) ? $realName : $fileName;
                $filePath           = $fileDestination . $fileName;

                if (!file_exists($filePath))
                {
                    if (!file_put_contents($filePath, base64_decode($imageData[2])))
                    {
                        continue;
                    }

                    //changing file permissions
                    chmod($filePath, 0644);

                    $file_size              = filesize($filePath);
                    $file_size              = $file_size / 1000;
                    $date_time              = date('Y-m-d H:i:s');
                    $extra_info             = array('source' => 'inline', 'show_as_attachment' => $showAsAttachment);

                    $file                   = new filesModel();
                    $file->company_id       = $this->companyId;
                    $file->project_id       = $this->projectId ? $this->projectId : '0';
                    $file->related_to_id    = 0;
                    $file->file_name        = $fileName;
                    $file->file_type        = $fileExtension;
                    $file->file_extension   = strtolower($fileExtension);
                    $file->file_real_name   = htmlentities(mb_convert_encoding($realName, 'UTF-8'));
                    $file->file_size        = $file_size;
                    $file->source           = 'uploaded';
                    $file->related_to       = $connectedWith;
                    $file->created_by       = $this->userId;
                    $file->created_at       = $date_time;
                    $file->updated_at       = $date_time;
                    $file->last_activity    = $date_time;
                    $file->status           = 'uploaded';
                    $file->extra_info       = json_encode($extra_info);

                    $file->save();
                    $file_id            = $file->file_id;

                    if ($file_id)
                    {
                        if ($replaceUrl)
                        {
                            $ecrypted_projectId     = '';
                            $ecrypted_projectId     = obfuscate($this->projectId);
                            $file_url               = company_file_url() .'/thumb/?image=' . obfuscate($this->companyId) . '/' . $ecrypted_projectId. '/' . md5($fileName) . '/' . urlencode($realName);
                            
                            $node->setAttribute($attribute, $file_url);
                            $node->setAttribute('data-pm-cid', obfuscate($this->companyId));
                            $node->setAttribute('data-pm-pid', $ecrypted_projectId);
                            $node->setAttribute('data-pm-fid', obfuscate($file_id));
                        }
                        else
                        {
                            $node->parentNode->removeChild($node);
                        }

                        $attachments[]              = array(
                                                                'file_id'               => obfuscate($file_id),
                                                                'file_extension'        => $fileExtension,
                                                                'file_real_name'        => $realName,
                                                                'file_name'             => $file->file_name,
                                                                'file_type'             => $file->file_type,
                                                                'file_version'          => 1,
                                                                'file_size'             => $file->file_size,
                                                                'pages'                 => 0
                                                            );
                    }
                }
            }
        }

        if (!empty($attachments))
        {
            $doc->encoding              = 'utf-8';
            $requestAttachments         = $this->request->attachments ?? array();
            $this->request->merge([
                'description' => preg_replace(array("/^<div>/i", "/<\/div>$/i"), '', trim($doc->saveHTML(), " \t\n\r\0\x0B")),
                'attachments' => array_merge($requestAttachments, $attachments)
            ]);
        }

        return true;
    }
}