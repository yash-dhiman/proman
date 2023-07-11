<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'project_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                    'project_name', 
                                    'project_description', 
                                    'company_id',
                                    'created_at',
                                    'category_id',
                                    'status_id',
                                    'start_date',
                                    'end_date',
                                ];

    public static function find_projects(int $company_id, int $project_id = null, array $filter = array())
    {
        $query  = Projects::select('projects.*')->join('company', 'company.company_id', 'projects.company_id');

        if($project_id)
        {
            $query  = $query->where('projects.project_id', $project_id);
        }

        return $query->where('projects.company_id', $company_id)->get()->toArray();
    }
}
