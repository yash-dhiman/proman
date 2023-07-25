<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Projects\ProjectRequest;
use App\Models\Api\Projects;
use App\Http\Resources\Api\ProjectResource;
use Illuminate\Validation\ValidationException;

class ProjectsController extends Controller
{
    /**
     * Listing of projects.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $projects_data             = Projects::find_projects($this->company_id);
        if(!$projects_data)
        {
            return response()->json([
                "success" => false,
                "message" => "Projects not found."
            ], 404);
        }
        
        $projects_info             = array();
        
        foreach($projects_data as $project_data)
        {            
            $projects_info[]       = new ProjectResource($project_data);
        }

        return response()->json([
                                    "success" => true,
                                    "message" => "Projects data",
                                    'data' => $projects_info
                                ]);
    }

    /**
     * Project details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id)
    {
        print_r($project_id);
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $projects_data          = Projects::find_projects($this->company_id, deobfuscate($project_id));       
        
        if(!empty($projects_data))
        {
            $projects_data = $projects_data[0];
        }
        else
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Project not found."
                                    ], 404);
        }

        $projects_info             = array();
        $projects_info             = new ProjectResource($projects_data);
        return response()->json([
                                    "success" => true,
                                    "message" => "Project details",
                                    'data' => $projects_info
                                ]);
    }

    /**
     * Create a new project.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectRequest $request)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];

        // data validation 
        $request->validate(array());

        $project                            = new Projects;
        $project->company_id                = $this->company_id;
        $project->created_by                = $this->current_user_id;
        $request->merge(array('company_id' => $this->company_id, 'current_user_id' => $this->current_user_id));
         
        if($project->save_project($request->all()))
        {
            return response()->json([
                                        "success" => true,
                                        "message" => "New project created",
                                        'data' => new ProjectResource($project)
                                    ]);
        }
    }

    /**
     * Delete a project.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $project_id)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];
        $project                            = Projects::where('company_id', $this->company_id)->where('deleted', 0)->find(deobfuscate($project_id));

        if(!$project)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. Project, you trying to update, not found."
                                    ], 404);
        }

        // data validation 
        $request->validate($project);

        $project->updated_by                = $this->current_user_id;

        $request->merge(array('company_id' => $this->company_id, 'current_user_id' => $this->current_user_id));
         
        if($project->save_project($request->all()))
        {
            return response()->json([
                                        "success" => true,
                                        "message" => "Project update successfuly.",
                                        'data' => new ProjectResource($project)
                                    ]);
        }
    }

    /**
     * Delete a project.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $project_id)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];

        $project = Projects::where('company_id', $this->company_id)->find(deobfuscate($project_id));

        if(!$project)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Project not found."
                                    ], 404);
        }

        $project->delete();

        return response()->json([
                                    "success" => true,
                                    "message" => "Project deleted successfuly."
                                ]);
    }
}
