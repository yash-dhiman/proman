<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Tasks\TaskRequest;
use App\Models\Api\Tasks\Tasks;
use App\Http\Resources\Api\Tasks\TaskResource;
use App\Http\Resources\Api\Tasks\TaskCollection;

class TasksController extends Controller
{
    /**
     * Listing of Tasks.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $project_id, $tasklist_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $tasklist_id            = deobfuscate($tasklist_id);
        $tasks_data             = Tasks::find_tasks($this->company_id, $project_id, $tasklist_id);

        if(!$tasks_data)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Tasks not found."
                                    ], 404);
        }

        return response()->json( new TaskCollection($tasks_data) );
    }

    /**
     * Task details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id, $tasklist_id, $task_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];

        $tasks_data             = Tasks::find_tasks($this->company_id, deobfuscate($project_id), deobfuscate($tasklist_id), deobfuscate($task_id));

        if(!empty($tasks_data))
        {
            $tasks_data = $tasks_data[0];
        }
        else
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Task not found."
                                    ], 404);
        }

        $tasks_info             = array();
        $tasks_info             = new taskResource($tasks_data);
        return response()->json([
                                    "success"   => true,
                                    "message"   => "Task details",
                                    'data'      => $tasks_info
                                ]);
    }

    /**
     * Create a new task.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaskRequest $request, $project_id, $tasklist_id)
    {
        // data validation 
        $request->validate(array());

        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        
        $task                           = new Tasks;
        $task_data                      = $request->get_post_data();
        $task_data['project_id']        = deobfuscate($project_id);
        $task_data['tasklist_id']       = deobfuscate($tasklist_id);

        $attachments                    = $request->prepare_attachments_data();

        if($task->save_task($task_data, $attachments))
        {
            $tasks_data                 = Tasks::find_tasks($this->company_id, $task->project_id, $task->tasklist_id, $task->task_id);

            if(!empty($tasks_data))
            {
                $tasks_data             = $tasks_data[0];
            }

            return response()->json([
                                            "success"   => true,
                                            "message"   => "New task created",
                                            'data'      => new TaskResource($tasks_data)
                                        ]);
        }
    }

    /**
     * Delete a task.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(TaskRequest $request, $project_id, $tasklist_id, $task_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $project_id                     = deobfuscate($project_id);
        $tasklist_id                    = deobfuscate($tasklist_id);
        $task_id                        = deobfuscate($task_id);
        $task_data                      = $request->get_put_data();

        $task                           = tasks::where('company_id', $this->company_id)
                                            ->where('project_id', $project_id)
                                            ->where('tasklist_id', $tasklist_id)
                                            ->where('deleted', 0)->find($task_id);

        if(!$task)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. task, you trying to update, not found."
                                    ], 404);
        }

        // data validation 
        $request->validate($task);

        $attachments            = $request->prepare_attachments_data($task_id);
        
        if($task->update_task($task_data, $attachments))
        {
            return response()->json([
                                            "success" => true,
                                            "message" => "Task update successfuly.",
                                            'data' => new taskResource($task)
                                        ]);
        }
    }

    /**
     * Delete a task.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $project_id, $tasklist_id, $task_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $project_id                     = deobfuscate($project_id);
        $tasklist_id                    = deobfuscate($tasklist_id);
        $task_id                        = deobfuscate($task_id);

        $task                           = tasks::where('company_id', $this->company_id)
                                            ->where('project_id', $project_id)
                                            ->where('tasklist_id', $tasklist_id)
                                            ->where('deleted', 0)->find($task_id);

        if(!$task)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Task not found."
                                    ], 404);
        }

        $task->delete();

        return response()->json([
                                        "success" => true,
                                        "message" => "Task deleted successfuly."
                                    ]);
    }
}
