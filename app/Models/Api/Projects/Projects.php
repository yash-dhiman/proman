<?php

namespace App\Models\Api\Projects;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
                                    'title',
                                    'description',
                                    'company_id',
                                    'created_by',
                                    'created_at',
                                    'category_id',
                                    'status_id',
                                    'start_date',
                                    'end_date',
                                    'assignees',
                                    'project_code'
                                ];

    public static function find_projects(int $company_id, int $project_id = null, array $filter = array())
    {
        $query  = Projects::select('projects.*')->join('company', 'company.company_id', 'projects.company_id');

        if($project_id)
        {
            $query  = $query->where('projects.project_id', $project_id);
        }

        return $query->where('projects.company_id', $company_id)->get();
    }

    public function save_project($project)
    {
        if(isset($project['project_title']))
        {
            $this->project_title         = $project['project_title'];
        }

        if(isset($project['project_code']))
        {
            $this->project_code         = strtoupper($project['project_code']);
        }
        else
        {
            $this->project_code         = strtoupper(substr($project['project_title'], 0, 3));
        }

        if(isset($project['project_description']))
        {
            $this->project_description   = $project['project_description'];
        }

        if(isset($project['start_date']))
        {
            $this->start_date            = $project['start_date'];
        }

        if(isset($project['end_date']))
        {
            $this->end_date              = $project['end_date'];
        }

        if(isset($project['category_id']))
        {
            $this->category_id           = deobfuscate($project['category_id']);
        }

        if(isset($project['status_id']))
        {
            $this->status_id             = deobfuscate($project['status_id']);
        }

        if(isset($project['assignees']))
        {
            $this->assignees             = json_encode(deobfuscate_multiple($project['assignees']));
        }

        return $this->save();
    }
}
