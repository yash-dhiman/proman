<?php

namespace App\Models\Api\Projects;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;
    protected $primaryKey = 'role_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                    'title',
                                    'description',
                                    'company_id',
                                    'project_id',
                                    'created_by',
                                    'created_at',
                                    'permissions',
                                ];

    public static function find_roles(int $company_id, int $project_id = null, int $role_id = null, array $filter = array())
    {
        $query  = Roles::select('roles.*')->join('company', 'company.company_id', 'roles.company_id');

        if($project_id)
        {
            $query  = $query->where('roles.project_id', $project_id);
        }

        if($role_id)
        {
            $query  = $query->where('roles.role_id', $role_id);
        }

        return $query->where('roles.company_id', $company_id)->get()->toArray();
    }

    /**
     * Save role
     *
     * @param array $role       Array of role's data
     * @return void
     */
    public function save_role($role)
    {
        $this->fill($role);
        return $this->save();
    }

    /**
     * Update role 
     *
     * @param array $role        Array of role's data
     * @return bool
     */
    public function update_role($role)
    {
        $this->fill($role);
        return $this->save($role);
    }
}
