<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Tasks\CommentRequest;
use App\Models\Api\Tasks\Comments;
use App\Http\Resources\Api\Tasks\CommentResource;
use App\Http\Resources\Api\Tasks\CommentCollection;

class CommentsController extends Controller
{
    /**
     * Listing of Comments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $project_id, $tasklist_id, $task_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $tasklist_id            = deobfuscate($tasklist_id);
        $task_id                = deobfuscate($task_id);
        $comments_data          = Comments::find_comments($this->company_id, $task_id);

        if (!$comments_data) {
            return response()->json([
                "success" => false,
                "message" => "Comments not found."
            ], 404);
        }

        return response()->json( new CommentCollection($comments_data) );
    }

    /**
     *  Comment details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id, $tasklist_id, $task_id, $comment_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $task_id                = deobfuscate($task_id);
        $comment_id             = deobfuscate($comment_id);
        $comments_data          = Comments::find_comments($this->company_id, $task_id, $comment_id);

        if (!empty($comments_data)) {
            $comments_data      = $comments_data[0];
        } else {
            return response()->json([
                "success" => false,
                "message" => "Comment not found."
            ], 404);
        }

        $comments_info          = array();
        $comments_info          = new CommentResource($comments_data);
        return response()->json([
            "success"   => true,
            "message"   => "Comment details",
            'data'      => $comments_info
        ]);
    }

    /**
     * Create a new comment.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, $project_id, $tasklist_id, $task_id)
    {
        // data validation
        $request->validate(array());

        $this->current_user         = $request->user();
        $this->company_id           = $this->current_user['company_id'];
        $this->current_user_id      = $this->current_user['user_id'];
        $task_id                    = deobfuscate($task_id);

        $comment                    = new Comments;
        $comment_data               = $request->get_post_data();
        $comment_data['task_id']    = $task_id;

        $attachments                = $request->prepare_attachments_data();

        if ($comment->save_comment($comment_data, $attachments)) {
            $comments_data          = Comments::find_comments($this->company_id, $task_id, $comment->comment_id);

            if (!empty($comments_data)) {
                $comments_data      = $comments_data[0];
            }

            return response()->json([
                                        "success"   => true,
                                        "message"   => "New comment created",
                                        'data'      => new CommentResource($comments_data)
                                    ]);
        }
    }

    /**
     * Delete a comment.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, $project_id, $tasklist_id, $task_id, $comment_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $comment_id             = deobfuscate($comment_id);
        $task_id                = deobfuscate($task_id);
        $comment_data           = $request->get_put_data();

        $comment                = Comments::where('company_id', $this->company_id)
                                            ->where('task_id', $task_id)
                                            ->where('deleted', 0)->find($comment_id);

        if (!$comment) {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. Comment, you trying to update, not found."
                                    ], 404);
        }

        // data validation
        $request->validate([]);

        $attachments = $request->prepare_attachments_data($comment_id);

        if ($comment->update_comment($comment_data, $attachments)) {
            $comment                = Comments::find_comments($this->company_id, $task_id, $comment_id);
            return response()->json([
                                        "success" => true,
                                        "message" => "Comment update successfuly.",
                                        'data' => new CommentResource($comment[0])
                                    ]);
        }
    }

    /**
     * Delete a comment.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $project_id, $tasklist_id, $task_id, $comment_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $task_id                        = deobfuscate($task_id);
        $comment_id                     = deobfuscate($comment_id);

        $comment                        = Comments::where('company_id', $this->company_id)
                                            ->where('task_id', $task_id)
                                            ->where('deleted', 0)->find($comment_id);

        if (!$comment) {
            return response()->json([
                                        "success" => false,
                                        "message" => " Comment not found."
                                    ], 404);
        }

        $comment->delete();

        return response()->json([
                                    "success" => true,
                                    "message" => " Comment deleted successfuly."
                                ]);
    }
}
