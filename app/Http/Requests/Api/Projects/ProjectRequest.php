<?php

namespace App\Http\Requests\Api\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Helpers\ProjectHelper;

class ProjectRequest extends FormRequest
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
                    'project_title' => [Rule::requiredIf($this->isMethod('post'))],
                    'start_date'    => 'date', 
                    'end_date'      => 'date|after_or_equal:start_date',
                ];
    }

    /**
     * Validate posted data with existing data. Like start_date and end_date comparison 
     *
     * @return void
     */
    public function validate($project)
    {
        // validate start_date and end_date
        if(isset($this->end_date) && !isset($this->start_date))
        {
            if(!empty($project->start_date) && $this->end_date < $project->start_date)
            {
                throw ValidationException::withMessages(['end_date' => 'The end date field must be a date after or equal to start date.']);
            }
            elseif(empty($project->start_date))
            {
                $this->start_date    = $this->end_date;
            }
        }
        
        $project_id = null;

        if($this->isMethod('put'))
        {
            $project_id = $project['project_id'];
        }
        
        // validate project_code
        if(isset($this->project_code))
        {
            $project_data = ProjectHelper::project_code_exist($this->project_code, $project_id);

            if($project_data)
            {
                throw ValidationException::withMessages(['project_code' => 'The project code already in use. Please try something else.']);
            }
        }
    }
}
