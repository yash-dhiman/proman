<?php

namespace App\Http\Requests\api\tasks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Exceptions\InvalidRequestException;
use App\Helpers\ProjectHelper;
use App\Helpers\TasklistHelper;
use App\Helpers\TaskHelper;
use App\Libraries\Files;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'comment_description'    => ['required']
        ];
    }

    /**
     * Checking request is valid or not.
     * Checking Project and tasklist exists.
     *
     * @return void
     */
    public function is_valid_request()
    {
        $this->merge(['project' => ProjectHelper::project_exist(deobfuscate($this->project_id))]);

        if (!$this->project) {
            throw new InvalidRequestException('Invalid request');
        }

        $this->merge(['tasklist' => TasklistHelper::tasklist_exist(deobfuscate($this->tasklist_id))]);

        if (!$this->tasklist) {
            throw new InvalidRequestException('Invalid request');
        }

        $this->merge(['task' => TaskHelper::task_exist(deobfuscate($this->tasklist_id))]);

        if (!$this->tasklist) {
            throw new InvalidRequestException('Invalid request');
        }
    }

    /**
     * Get posted task data
     *
     * Note: All ids will be deobfuscate
     *
     * @return array    Posted task data
     */
    public function get_post_data()
    {
        $files                          = new Files($this);
        $files->extractInlineImages();
        $comment                        = $this->all();
        $comment['company_id']          = get_company_id();
        $comment['created_by']          = get_user_id();

        return $comment;
    }

    /**
     * Get posted task data to update task info
     *
     * Note: All ids will be deobfuscate
     *
     * @return array    Posted task data
     */
    public function get_put_data()
    {
        $files                          = new Files($this);
        $files->extractInlineImages();
        $comment                        = $this->all();
        return $comment;
    }

    /**
     * Function to prepare attachments data and return as array
     *
     * Note: All ids will be deobfuscate
     *
     * @return array
     */
    public function prepare_attachments_data($comment_id = null)
    {
        $attachments = $this->attachments;

        if(!empty($attachments))
        {
            foreach($attachments as $key => $attachment)
            {
                $attachments[$key]['file_id']           = deobfuscate($attachment['file_id']);
                $attachments[$key]['company_id']        = get_company_id();
                $attachments[$key]['created_by']        = $attachments[$key]['updated_by'] = get_user_id();
                $attachments[$key]['project_id']        = deobfuscate($this->project_id);
                $attachments[$key]['related_to_id']     = $comment_id;
                $attachments[$key]['related_to']        = 'TC';
                $attachments[$key]['file_extension']    = $attachment['file_extension'];
                $attachments[$key]['file_real_name']    = $attachment['file_real_name'];
            }
        }

        return $attachments;
    }
}
