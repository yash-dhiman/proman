<?php
namespace App\Helpers;

use App\Models\Api\Projects\Projects;
use App\Helpers\Builder;

class ProjectHelper
{
    public static function project_exist($project_id)
    {
        $project = Projects::where('company_id', get_company_id())->where('deleted', 0)->findOr($project_id, function () {
            return false;
        });

        return $project ?? false;
    }

    public static function project_code_exist($project_code, $project_id)
    {
        $project = Projects::where('company_id', get_company_id())->where('deleted', 0)->where('project_code', $project_code)
        ->when($project_id, function ($project, $project_id) {
            $project->whereNot('project_id', $project_id);
        })->get()->first();

        return $project ?? false;
    }  
}
