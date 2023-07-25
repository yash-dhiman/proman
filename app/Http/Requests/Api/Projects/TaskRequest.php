<?php

namespace App\Http\Requests\Api\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Exceptions\InvalidRequestException;
use App\Helpers\ProjectHelper;
use App\Helpers\TasklistHelper;

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
                    'task_title'    => ['required'],
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
        $task                       = $this->all();
        $task['company_id']         = get_company_id();
        $task['created_by']         = get_user_id();

        if(isset($task['stage_id']))
        {
            $task['stage_id']       = deobfuscate($task['stage_id']);
        }

        if(isset($task['assignees']))
        {
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
        $task                       = $this->all();
        $task['updated_by']         = get_user_id();

        if(isset($task['stage_id']))
        {
            $task['stage_id']       = deobfuscate($task['stage_id']);
        }
        
        if(isset($task['completed']))
        {
            if($task['completed'] == true)
            {
                $task['completed_by']       = get_user_id();
                $task['completed_at']       = date('Y-m-d H:i:s');
            }
            else
            {
                $task['completed_by']       = null;
                $task['completed_at']       = null;
            }
        }

        if(isset($task['assignees']))
        {
            $task['assignees']      = json_encode(deobfuscate_multiple($task['assignees']));
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
        if(isset($this->end_date) && !isset($this->start_date))
        {
            if(!empty($task->start_date) && $this->end_date < $task->start_date)
            {
                throw ValidationException::withMessages(['end_date' => 'The end date field must be a date after or equal to start date.']);
            }
            elseif(empty($task->start_date))
            {
                $this->start_date    = $this->end_date;
            }
        }
    }

    /**
     * Validate posted data with existing data. Like start_date and end_date comparison 
     *
     * @return void
     */
    public function is_valid_request()
    {
        $this->merge(['project_data' => ProjectHelper::project_exist(deobfuscate($this->project_id))]);

        if(!$this->project_data)
        {
            throw new InvalidRequestException('Invalid request');
        }
        
        $this->merge(['tasklist_data' => TasklistHelper::tasklist_exist(deobfuscate($this->tasklist_id))]);

        if(!$this->tasklist_data)
        {
            throw new InvalidRequestException('Invalid request');
        }
    }
}
