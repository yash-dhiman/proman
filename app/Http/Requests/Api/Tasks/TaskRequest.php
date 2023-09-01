<?php

namespace App\Http\Requests\Api\Tasks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Exceptions\InvalidRequestException;
use App\Helpers\ProjectHelper;
use App\Helpers\TasklistHelper;
use App\Libraries\Files;

class TaskRequest extends FormRequest
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
            'title'    => ['required'],
            'start_date'    => 'date',
            'end_date'      => 'date|after_or_equal:start_date',
        ];
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
        $files                      = new Files($this);
        $files->extractInlineImages('T', true, true);
        $this->prepare_assignees();
        $task                       = $this->all();
        $task['company_id']         = get_company_id();
        $task['created_by']         = get_user_id();

        if (isset($task['stage_id'])) {
            $task['stage_id']       = deobfuscate($task['stage_id']);
        }

        if (isset($task['assignees'])) {
            $task['assignees']      = json_encode(deobfuscate_multiple($task['assignees']));
        }

        return $task;
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
        $files                      = new Files($this);
        $files->extractInlineImages('T', true, true);
        $this->prepare_assignees();
        $task                       = $this->all();
        $task['updated_by']         = get_user_id();

        if (isset($task['stage_id'])) {
            $task['stage_id']       = deobfuscate($task['stage_id']);
        }

        if (isset($task['completed'])) {
            if ($task['completed'] == true) {
                $task['completed_by']       = get_user_id();
                $task['completed_at']       = date('Y-m-d H:i:s');
            } else {
                $task['completed_by']       = null;
                $task['completed_at']       = null;
            }
        }

        if (isset($task['assignees'])) {
            $task['assignees']      = json_encode($task['assignees']);
        }

        return $task;
    }

    /**
     * Validate posted data with existing data. Like start_date and end_date comparison
     *
     * @return void
     */
    public function validate($task)
    {
        $this->is_valid_request();

        // validate start_date and end_date
        if (isset($this->end_date) && !isset($this->start_date)) {
            if (!empty($task->start_date) && $this->end_date < $task->start_date) {
                throw ValidationException::withMessages(['end_date' => 'The end date field must be a date after or equal to start date.']);
            } elseif (empty($task->start_date)) {
                $this->start_date    = $this->end_date;
            }
        }

        $this->merge(array('task' => $task));
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
    }

    /**
     * Function to prepare attachments data and return as array
     *
     * Note: All ids will be deobfuscate
     *
     * @return array
     */
    public function prepare_attachments_data($task_id = null)
    {
        $attachments = $this->attachments;

        if (!empty($attachments)) {
            foreach ($attachments as $key => $attachment) {
                $attachments[$key]['file_id']           = deobfuscate($attachment['file_id']);
                $attachments[$key]['company_id']        = get_company_id();
                $attachments[$key]['created_by']        = $attachments[$key]['updated_by'] = get_user_id();
                $attachments[$key]['project_id']        = deobfuscate($this->project_id);
                $attachments[$key]['related_to_id']     = $task_id;
                $attachments[$key]['related_to']        = 'T';
                $attachments[$key]['file_extension']    = $attachment['file_extension'];
                $attachments[$key]['file_real_name']    = $attachment['file_real_name'];
            }
        }

        return $attachments;
    }

    /**
     * Prepare list of assignees (New and old assignees)
     * Assignees' list is mergerd in request data within meta_data
     * 
     * Note: All ids will be deobfuscate
     * @return void
     */
    public function prepare_assignees()
    {
        $newAssignees = [];
        $oldAssignees = [];

        if (!empty($this->assignees)) {
            $this->merge(array('assignees' => deobfuscate_multiple($this->assignees)));

            if (!empty($this->task->assignees)) {
                $originalAssignees  = json_decode($this->task->assignees);
                $newAssignees       = array_diff($this->assignees, $originalAssignees);
                $oldAssignees       = array_diff($originalAssignees, $this->assignees);
            } else {
                $newAssignees   = $this->assignees;
                $oldAssignees   = array();
            }
        }

        $this->merge(array('meta_data' => array('new_assignees' => $newAssignees, 'old_assignees' => $oldAssignees)));
    }
}
