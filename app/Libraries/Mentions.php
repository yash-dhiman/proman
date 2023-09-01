<?php

namespace App\Libraries;

use Illuminate\Http\Request;
use App\Models\Api\Mentions as mentionsModel;

class Mentions
{
    protected $request;
    protected $companyId;
    protected $userId;
    protected $projectId;
    protected $element;
    protected $attribute;

    public function __construct(Request $request)
    {
        $this->request          = $request;
        $this->companyId        = get_company_id();
        $this->userId           = get_user_id();
        $this->projectId        = deobfuscate($this->request->project_id);
        $this->element          = 'pmmention';
        $this->attribute        = 'data-id';
    }

    public function save(string $relatedTo = 'TC', int $relatedToId, bool $edit = false)
    {
        $mentionedUsers        = $this->getMentionedUsers();

        // ------- remove mentions on edit
        if ($edit) {
            // ------- old mentioned users
            $oldMentionedUsers = array();

            if ($oldMentionedUsersData = mentionsModel::select('mentioned_to')->where('company_id', $this->companyId)->where('related_to_id', $relatedToId)->where('related_to', $relatedTo)->get()->toArray()) {

                foreach ($oldMentionedUsersData as $oldMentionedUserData) {
                    $oldMentionedUsers[]    = $oldMentionedUserData['mentioned_to'];
                }
            } elseif (!$mentionedUsers) {
                return false;
            }

            // ------- get new mentioned user
            $newMentioned                   = array_diff($mentionedUsers, $oldMentionedUsers);

            // ------- get removed mentioned users
            $mentionedToRemove              = array_diff($oldMentionedUsers, $mentionedUsers);

            // ------- delete removed mentioned users from table
            if (!empty($mentionedToRemove)) {
                mentionsModel::where('company_id', $this->companyId)->where('related_to_id', $relatedToId)->where('related_to', $relatedTo)->whereIn('mentioned_to', $mentionedToRemove)->delete();
            }

            $mentionedUsers = $newMentioned;
        }

        // ------- mentions data entry
        if ($mentionedUsers) {

            $mentionData    = array(
                'company_id'        => $this->companyId,
                'project_id'        => $this->projectId,
                'related_to'        => $relatedTo,
                'related_to_id'     => $relatedToId,
                'mentioned_by'      => $this->userId,
                'created_at'        => date('Y-m-d H:i:s')
            );
            $mention        = array();

            foreach ($mentionedUsers as $mentionedUser) {
                $mentionData['mentioned_to']    = $mentionedUser;
                $mention[]                      = $mentionData;
            }

            mentionsModel::insert($mention);
            $this->request->merge(array('meta_data' => array('mentioned_users' => $mentionedUsers)));
        }

        return true;
    }

    private function getMentionedUsers()
    {
        $html                   = $this->request->description;

        // Create DOM from string
        $doc                    = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $html); // loads your html

        $xpath                  = new \DOMXPath($doc);
        $nodelist               = $xpath->query("//" . $this->element); // find your pmmention tag
        $mentionedUsers         = array();

        for ($i = 0; $i < $nodelist->length; $i++) {
            $node               = $nodelist->item($i);
            $mentionedUsers[]   = $node->attributes->getNamedItem($this->attribute)->nodeValue;
        }

        return deobfuscate_multiple($mentionedUsers, TRUE);
    }
}
