<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Tasks;
use Illuminate\Validation\Rules;
use App\Http\Resources\Api\TaskResource;
use Illuminate\Validation\ValidationException;

class TasksController extends Controller
{
    /**
     * ing of Tasks.
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

        $tasks_info             = array();

        foreach($tasks_data as $task_data)
        {
            $tasks_info[]       = new TaskResource($task_data);
        }

        return response()->json([
                                    "success" => true,
                                    "message" => "Tasks data",
                                    'data' => $tasks_info
                                ]);
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
                                    "success" => true,
                                    "message" => "Task details",
                                    'data' => $tasks_info
                                ]);
    }

    /**
     * Create a new task.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $project_id, $tasklist_id)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];
        $project_id                         = deobfuscate($project_id);
        $tasklist_id                        = deobfuscate($tasklist_id);

        $task_data                          = $request->validate([
                                                                    'task_title'    => ['required'],
                                                                    'start_date'    => 'date', 
                                                                    'end_date'      => 'date|after_or_equal:start_date',
                                                                ]);

        $task = new Tasks;
        $task->task_title                   = $task_data['task_title'];
        $task->task_description             = $request->task_description;
        $task->start_date                   = $request->start_date;
        $task->end_date                     = $request->end_date;
        $task->company_id                   = $this->company_id;
        $task->project_id                   = $project_id;
        $task->tasklist_id                  = $tasklist_id;
        $task->created_by                   = $this->current_user_id;
        $task->status                       = $request->status;
        $task->stage_id                     = deobfuscate($request->stage_id);
        $task->assignees                    = isset($request->assignees) ? json_encode(deobfuscate_multiple($request->assignees)) : '[]';

        $task->save();

        return response()->json([
                                        "success" => true,
                                        "message" => "New task created",
                                        'data' => new TaskResource($task)
                                    ]);
    }

    /**
     * Delete a task.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $project_id, $tasklist_id, $task_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $project_id                     = deobfuscate($project_id);
        $tasklist_id                    = deobfuscate($tasklist_id);
        $task_id                        = deobfuscate($task_id);
        $task_data                      = $request->validate([
                                                                    'task_title' => ['required'],
                                                                    'start_date'    => 'date', 
                                                                    'end_date'      => 'date|after_or_equal:start_date',
                                                                ]);

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

        if(isset($request->end_date) && !isset($request->start_date))
        {
            if(!empty($task->start_date) && $request->end_date < $task->start_date)
            {
                throw ValidationException::withMessages(['end_date' => 'The end date field must be a date after or equal to start date.']);
            }
            elseif(empty($task->start_date))
            {
                $request->start_date    = $request->end_date;
            }
        }

        $task->task_title               = $task_data['task_title'];
        $task->task_description         = isset($request->task_description) ? $request->task_description : $task->task_description;
        $task->start_date               = isset($request->start_date) ? $request->start_date : $task->start_date;
        $task->end_date                 = isset($request->end_date) ? $request->end_date : $task->end_date;
        $task->updated_by               = $this->current_user_id;
        $task->status                   = isset($request->status) ? $request->status : $task->status;
        $task->stage_id                 = isset($request->stage_id) ? deobfuscate($request->stage_id) : $task->stage_id3;
        $task->assignees                = isset($request->assignees) ? json_encode(deobfuscate_multiple($request->assignees)) : '[]';

        if($task->save())
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
