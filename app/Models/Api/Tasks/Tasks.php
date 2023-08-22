<?php

namespace App\Models\api\Tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Api\Projects;
use App\Models\Api\Tasklists;
use App\Models\Api\Tasks\Comments;

class Tasks extends Model
{
    use HasFactory;
    protected $primaryKey   = 'task_id';
    
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
                                    'tasklist_id',
                                    'status',
                                    'stage_id',
                                    'start_date',
                                    'end_date',
                                    'assignees',
                                    'created_at',
                                    'created_by',
                                    'updated_by',
                                    'updated_at',
                                    'custom_fields',
                                    'completed',
                                    'completed_by',
                                    'completed_at',
                                ];

    /**
     * Get the tasks with project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id', 'project_id');
    }

    /**
     * Get the tasks with tasklist.
     */
    public function tasklist(): BelongsTo
    {
        return $this->belongsTo(Tasklists::class, 'tasklist_id', 'tasklist_id');
    }
    
    public static function find_tasks(int $company_id,  int $project_id = null, int $tasklist_id = null, int $task_id = null, array $filter = array())
    {
        $query  = Tasks::with('project')->with('tasklist')->select('tasks.*')
                    ->join('company', 'company.company_id', 'tasks.company_id');

        if($task_id)
        {
            $query  = $query->where('tasks.task_id', $task_id);
        }

        if($project_id)
        {
            $query  = $query->where('tasks.project_id', $project_id);
        }

        if($tasklist_id)
        {
            $query  = $query->where('tasks.tasklist_id', $tasklist_id);
        }

        return $query->where('tasks.company_id', $company_id)->get()->toArray();
    }

    public function save_task($task)
    {
        $this->fill($task);
        return $this->save();
    }

    /**
     * Get all of the task's comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comments::class, 'comments');
    }
}
