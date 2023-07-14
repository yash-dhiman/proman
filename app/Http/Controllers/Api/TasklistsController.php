<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Tasklists;
use App\Http\Resources\Api\TasklistResource;

class TasklistsController extends Controller
{
    /**
     * Listing of tasklists.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $project_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $project_id             = deobfuscate($project_id);
        $tasklists_data         = Tasklists::find_tasklists($this->company_id, $project_id);
        
        if(!$tasklists_data)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Tasklists not found."
                                    ], 404);
        }

        $tasklists_info             = array();

        foreach($tasklists_data as $tasklist_data)
        {
            $tasklists_info[]       = new TasklistResource($tasklist_data);
        }

        return response()->json([
                                    "success" => true,
                                    "message" => "Tasklists data",
                                    'data' => $tasklists_info
                                ]);
    }

    /**
     * Tasklist details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id, $tasklist_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $tasklists_data          = Tasklists::find_tasklists($this->company_id, deobfuscate($project_id));       
        
        if(!empty($tasklists_data))
        {
            $tasklists_data = $tasklists_data[0];
        }
        else
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Tasklist not found."
                                    ], 404);
        }

        $tasklists_info             = array();
        $tasklists_info             = new tasklistResource($tasklists_data);
        return response()->json([
                                    "success" => true,
                                    "message" => "Tasklist details",
                                    'data' => $tasklists_info
                                ]);
    }
}
