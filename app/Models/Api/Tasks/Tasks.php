<?php

namespace App\Models\api\Tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Api\Projects;
use App\Models\Api\Tasklists;
use App\Models\Api\Tasks\Comments;
use App\Models\Api\Files;

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

    /**
     * Get the attachment with comments/replies.
     */
    public function attachments(): HasMany
    {
        return $this->HasMany(Files::class, 'related_to_id', 'task_id')->where('company_id', get_company_id())->where('related_to', 'T')->where('deleted', 0)
        ->where(function ($query) {
            $query->where('extra_info->show_as_attachment', 'true')
                  ->orWhereNull('extra_info');
        });
    }

    public static function find_tasks(int $company_id,  int $project_id = null, int $tasklist_id = null, int $task_id = null, array $filter = array())
    {
        $query  = Tasks::with('project')->with('tasklist')->with('attachments')->select('tasks.*')
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

    public function save_task($task, $attachments = array())
    {
        $this->fill($task);
        $response = $this->save();

        if(!empty($attachments))
        {
            // adding / updating new task_id as related_to_id of the attachments
            array_walk($attachments, function(&$attachment, $key, $task_id){
                $attachment['related_to_id'] = $task_id;
            }, $this->task_id);

            $response   = Files::upsert($attachments, ['file_id'], ['file_type','related_to','related_to_id','file_real_name', 'file_name']);
        }

        
        return $response;
    }

    /**
     * Update task and update the attachments / files
     *
     * @param array $task        Array of task's data
     * @param array $attachments    Array of attachemnts
     * @return bool
     */
    public function update_task($task, $attachments = array())
    {
        $this->fill($task);
        $response       = $this->save($task);

        if(!empty($attachments))
        {
            $response   = Files::upsert($attachments, ['file_id'], ['file_type','related_to','related_to_id','file_real_name', 'file_name']);
        }

        return $response;
    }

    /**
     * Get all of the task's comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comments::class, 'comments');
    }
}
