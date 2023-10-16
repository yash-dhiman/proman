<?php

namespace App\Http\Requests\Api\Projects;

use Illuminate\Foundation\Http\FormRequest;
use App\Exceptions\InvalidRequestException;
use App\Helpers\ProjectHelper;

class RoleRequest extends FormRequest
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
            'title'         => ['required'],
            'description'   => ['required'],
            'permissions'   => ['required'],
        ];
    }

    /**
     * Get posted role data
     *
     * Note: All ids will be deobfuscate
     *
     * @return array    Posted role data
     */
    public function get_post_data()
    {
        $role                   = $this->all();
        $role['company_id']     = get_company_id();
        $role['created_by']     = get_user_id();
        $role['permissions']    = json_encode(deobfuscate_multiple($this->permissions));
        return $role;
    }

    /**
     * Get posted role data to update role info
     *
     * Note: All ids will be deobfuscate
     *
     * @return array    Posted role data
     */
    public function get_put_data()
    {
        $role                   = $this->all();
        $role['company_id']     = get_company_id();
        $role['created_by']     = get_user_id();
        $role['permissions']    = json_encode(deobfuscate_multiple($this->permissions));
        return $role;
    }

    /**
     * Validate posted data with existing data. Like start_date and end_date comparison
     *
     * @return void
     */
    public function validate($role)
    {
        $this->is_valid_request();
    }

    /**
     * Checking request is valid or not.
     * Checking Project exists.
     *
     * @return void
     */
    public function is_valid_request()
    {
        $this->merge(['project' => ProjectHelper::project_exist(deobfuscate($this->project_id))]);

        if (!$this->project) {
            throw new InvalidRequestException('Invalid request');
        }
    }
}
