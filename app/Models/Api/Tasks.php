<?php

namespace App\Models\api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
                                    'task_title', 
                                    'task_description', 
                                    'company_id',
                                    'project_id',
                                    'tasklist_id',
                                    'created_at',
                                    'category_id',
                                    'status_id',
                                    'start_date',
                                    'end_date',
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
}
