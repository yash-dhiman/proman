<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Tasks\CommentRequest;
use App\Models\Api\Tasks\Comments;
use App\Http\Resources\Api\Tasks\ReplyResource;
use App\Http\Resources\Api\Tasks\ReplyCollection;
use App\Libraries\Mentions;

class CommentRepliesController extends Controller
{
    /**
     * Listing of Comments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $project_id, $tasklist_id, $task_id, $comment_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $tasklist_id            = deobfuscate($tasklist_id);
        $task_id                = deobfuscate($task_id);
        $comment_id             = deobfuscate($comment_id);
        $replies_data           = Comments::find_replies($this->company_id, $task_id, $comment_id);

        if (!$replies_data) {
            return response()->json([
                'success' => false,
                'message' => 'Comment replies not found.'
            ], 404);
        }

        return response()->json( [
                                    'success' => true,
                                    'message' => 'Comment replies data.',
                                    'data' => new ReplyCollection($replies_data)
                                 ]
                                );
    }

    /**
     *  Comment details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id, $tasklist_id, $task_id, $comment_id, $reply_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $task_id                = deobfuscate($task_id);
        $comment_id             = deobfuscate($comment_id);
        $reply_id               = deobfuscate($reply_id);
        $replies_data           = Comments::find_replies($this->company_id, $task_id, $comment_id, $reply_id);

        if (!empty($replies_data)) {
            $replies_data       = $replies_data[0];
        } else {
            return response()->json([
                                        'success' => false,
                                        'message' => 'Comment reply not found.'
                                    ], 404);
        }

        $replies_info          = array();
        $replies_info          = new ReplyResource($replies_data);
        return response()->json([
                                    'success'   => true,
                                    'message'   => 'Comment reply details',
                                    'data'      => $replies_info
                                ]);
    }

    /**
     * Create a new comment reply.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, $project_id, $tasklist_id, $task_id, $comment_id)
    {
        // data validation
        $request->validate(array());

        $this->current_user         = $request->user();
        $this->company_id           = $this->current_user['company_id'];
        $this->current_user_id      = $this->current_user['user_id'];
        $task_id                    = deobfuscate($task_id);
        $comment_id                 = deobfuscate($comment_id);

        $reply                      = new Comments;
        $comment_data               = $request->get_post_data();
        $comment_data['task_id']    = $task_id;
        $comment_data['parent_id']  = $comment_id;

        $attachments                = $request->prepare_attachments_data();

        if ($reply->save_comment($comment_data, $attachments)) 
        {
            $replies_data           = Comments::find_replies($this->company_id, $task_id, $comment_id, $reply->comment_id);

            if (!empty($replies_data)) {
                
                // extract and save mentions on comment
                $mention            = new Mentions($request);
                $mention->save('TC', $reply->comment_id);

                $replies_data       = $replies_data[0];
            }

            return response()->json([
                                        'success'   => true,
                                        'message'   => 'New comment reply created',
                                        'data'      => new ReplyResource($replies_data)
                                    ]);
        }
    }

    /**
     * Delete a comment reply.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, $project_id, $tasklist_id, $task_id, $comment_id, $reply_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $comment_id             = deobfuscate($comment_id);
        $reply_id               = deobfuscate($reply_id);
        $task_id                = deobfuscate($task_id);
        $comment_data           = $request->get_put_data();

        $reply                  = Comments::where('company_id', $this->company_id)
                                            ->where('task_id', $task_id)
                                            ->where('deleted', 0)->find($reply_id);

        if (!$reply) {
            return response()->json([
                                        'success' => false,
                                        'message' => 'Invalid request. Comment reply, you trying to update, not found.'
                                    ], 404);
        }

        // data validation
        $request->validate([]);

        $attachments            = $request->prepare_attachments_data($reply_id);

        if ($reply->update_comment($comment_data, $attachments)) {
            $reply              = Comments::find_replies($this->company_id, $task_id, $comment_id, $reply_id);
            
            // extract and save mentions on comment
            $mention            = new Mentions($request);
            $mention->save('TC', $reply_id, true);

            return response()->json([
                                        'success' => true,
                                        'message' => 'Comment reply update successfuly.',
                                        'data' => new ReplyResource($reply[0])
                                    ]);
        }
    }

    /**
     * Delete a comment reply.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $project_id, $tasklist_id, $task_id, $comment_id, $reply_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $task_id                = deobfuscate($task_id);
        $reply_id               = deobfuscate($reply_id);

        $reply                  = Comments::where('company_id', $this->company_id)
                                    ->where('task_id', $task_id)
                                    ->where('deleted', 0)->find($reply_id);

        if (!$reply) {
            return response()->json([
                                        'success' => false,
                                        'message' => ' Comment reply not found.'
                                    ], 404);
        }

        $reply->delete();

        return response()->json([
                                    'success' => true,
                                    'message' => ' Comment reply deleted successfuly.'
                                ]);
    }
}
