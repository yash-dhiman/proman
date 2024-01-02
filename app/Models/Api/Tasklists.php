<?php

namespace App\Models\api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Api\Projects\Projects;

class Tasklists extends Model
{
    use HasFactory;
    
    protected $primaryKey   = 'tasklist_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                    'title', 
                                    'description', 
                                    'company_id',
                                    'created_at',
                                    'category_id',
                                    'status_id',
                                    'start_date',
                                    'end_date',
                                ];
    /**
     * Get the user that owns the phone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id', 'project_id');
    }
    
    public static function find_tasklists(int $company_id,  int $project_id = null, int $tasklist_id = null, array $filter = array())
    {
        $query  = tasklists::with('project')->select('tasklists.*')
                    ->join('company', 'company.company_id', 'tasklists.company_id');
                    // ->join('projects', 'projects.project_id', 'tasklists.project_id');

        if($project_id)
        {
            $query  = $query->where('tasklists.project_id', $project_id);
        }

        if($tasklist_id)
        {
            $query  = $query->where('tasklists.tasklist_id', $tasklist_id);
        }

        return $query->where('tasklists.company_id', $company_id)->get()->toArray();
    }
}
