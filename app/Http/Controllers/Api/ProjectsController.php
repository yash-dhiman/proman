<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projects;
use Illuminate\Validation\Rules;
use App\Http\Resources\api\ProjectResource;
use App\Events\PodcastProcessed;

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
    public function show(Request $request, $id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $projects_data          = Projects::find_projects($this->company_id, deobfuscate($id));       
        
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
    public function store(Request $request)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];

        $project_data                       = $request->validate([
                                                                    'project_title' => ['required']
                                                                ]);

        $project = new Projects;
        $project->project_title            = $project_data['project_title'];
        $project->project_description      = $request->project_description;
        $project->start_date               = $request->start_date;
        $project->end_date                 = $request->end_date;
        $project->category_id              = deobfuscate($request->category_id);
        $project->company_id               = $this->company_id;
        $project->created_by               = $this->current_user_id;
        $project->status_id                = deobfuscate($request->status_id);
        $project->save();

        return response()->json([
                                        "success" => true,
                                        "message" => "New project created",
                                        'data' => new ProjectResource($project)
                                    ]);
    }

    /**
     * Delete a project.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];
        $project_data                       = $request->validate([
                                                                    'project_title' => ['required']
                                                                ]);

        $project = Projects::where('company_id', $this->company_id)->where('deleted', 0)->find(deobfuscate($id));

        if(!$project)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. Project, you trying to update, not found."
                                    ], 404);
        }

        $project->project_title            = $project_data['project_title'];
        $project->project_description      = $request->project_description;
        $project->start_date               = $request->start_date;
        $project->end_date                 = $request->end_date;
        $project->category_id              = deobfuscate($request->category_id);
        $project->updated_by               = $this->current_user_id;
        $project->status_id                = deobfuscate($request->status_id);
        $project->assignees                = deobfuscate_multiple($request->assignees);
        
        if($project->save())
        {
            PodcastProcessed::dispatch($project);

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
    public function destroy(Request $request, $id)
    {
        $this->current_user                 = $request->user();
        $this->company_id                   = $this->current_user['company_id'];
        $this->current_user_id              = $this->current_user['user_id'];

        $project = Projects::where('company_id', $this->company_id)->find(deobfuscate($id));

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
