<?php

namespace App\Models\api\Tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasManyThrough;
use App\Models\Api\Tasks;
use App\Models\Api\Files;
use DB;

class Comments extends Model
{
    use HasFactory;
    protected $primaryKey   = 'comment_id';
    protected $table        = 'task_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                    'description',
                                    'company_id',
                                    'task_id',
                                    'parent_id',
                                    'created_at',
                                    'created_by',
                                    'updated_at',
                                ];

    /**
     * Get the comments with tasks.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Tasks::class, 'task_id', 'task_id')->where('company_id', get_company_id())->where('deleted', 0);
    }

    /**
     * Get the comments with tasks.
     */
    public function replies(): hasMany
    {
        return $this->hasMany(Comments::class, 'parent_id', 'comment_id')->with('attachments')->where('company_id', get_company_id())->where('deleted', 0);
    }

    /**
     * Get the comments with tasks.
     */
    public function attachments(): HasMany
    {
        return $this->HasMany(Files::class, 'related_to_id', 'comment_id')->where('company_id', get_company_id())->where('related_to', 'TC')->where('deleted', 0);
    }

    public static function find_comments(int $company_id, int $task_id = null, int $comment_id = null, array $filter = array())
    {
        $query      = Comments::with('replies')->with('attachments')->select('task_comments.*')
                    ->join('company', 'company.company_id', 'task_comments.company_id');

        if($task_id)
        {
            $query  = $query->where('task_comments.task_id', $task_id);
        }

        if($comment_id)
        {
            $query  = $query->where('task_comments.comment_id', $comment_id);
        }

        $query->where('task_comments.parent_id', null);

        return $query->where('task_comments.company_id', $company_id)->get()->toArray();
    }

    /**
     * Create new comment and update the attachments / files
     *
     * @param array $comment        Array of comment's data
     * @param array $attachments    Array of attachemnts
     * @return bool
     */
    public function save_comment($comment, $attachments = array())
    {
        $this->fill($comment);
        $response       = $this->save($comment);
        $comment_id     = $this->comment_id;

        if(!empty($attachments))
        {
            // adding / updating new comment_id as related_to_id of the attachments
            array_walk($attachments, function(&$attachment, $key, $comment_id){
                $attachment['related_to_id'] = $comment_id;
            }, $comment_id);

            $response   = Files::upsert($attachments, ['file_id'], ['file_type','related_to','related_to_id','file_real_name', 'file_name']);
        }

        return $response;
    }

    /**
     * Update comment and update the attachments / files
     *
     * @param array $comment        Array of comment's data
     * @param array $attachments    Array of attachemnts
     * @return bool
     */
    public function update_comment($comment, $attachments = array())
    {
        $this->fill($comment);
        $response       = $this->save($comment);

        if(!empty($attachments))
        {
            $response = Files::upsert($attachments, ['file_id'], ['file_type','related_to','related_to_id','file_real_name', 'file_name']);
        }

        return $response;
    }

    public static function find_replies(int $company_id, int $task_id = null, int $comment_id = null, int $reply_id = null, array $filter = array())
    {
        $query      = Comments::with('attachments')->select('task_comments.*')
                    ->join('company', 'company.company_id', 'task_comments.company_id');

        if($task_id)
        {
            $query  = $query->where('task_comments.task_id', $task_id);
        }

        if($comment_id)
        {
            $query  = $query->where('task_comments.parent_id', $comment_id);
        }
        else
        {
            $query->whereNotNull('task_comments.parent_id');
        }

        if($reply_id)
        {
            $query  = $query->where('task_comments.comment_id', $reply_id);
        }

        return $query->where('task_comments.company_id', $company_id)->get()->toArray();
    }
}

