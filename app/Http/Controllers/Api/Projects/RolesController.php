<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Projects\Roles;
use App\Http\Requests\Api\Projects\RoleRequest;
use App\Http\Resources\Api\Projects\RoleResource;
use App\Http\Resources\Api\Projects\RoleCollection;

class RolesController extends Controller
{
    /**
     * Listing of Roles.
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
        $roles_data             = Roles::find_roles($this->company_id, $project_id);

        if (!$roles_data) {
            return response()->json([
                "success" => false,
                "message" => "Roles not found."
            ], 404);
        }

        return response()->json(new RoleCollection($roles_data));
    }

    /**
     * Role details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $project_id, $role_id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];

        $roles_data             = Roles::find_roles($this->company_id, deobfuscate($project_id), deobfuscate($role_id));

        if (!empty($roles_data)) {
            $roles_data = $roles_data[0];
        } else {
            return response()->json([
                                        "success" => false,
                                        "message" => "Role not found."
                                    ], 404);
        }

        $roles_info             = array();
        $roles_info             = new roleResource($roles_data);
        return response()->json([
                                    "success"   => true,
                                    "message"   => "Role details",
                                    'data'      => $roles_info
                                ]);
    }

    /**
     * Create a new role.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request, $project_id)
    {
        // data validation 
        $request->validate(array());

        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];

        $role                           = new Roles;
        $role_data                      = $request->get_post_data();
        $role_data['project_id']        = deobfuscate($project_id);

        if ($role->save_role($role_data)) {
            $roles_data                 = Roles::find_roles($this->company_id, $role->project_id, $role->role_id);

            if (!empty($roles_data)) {
                $roles_data             = $roles_data[0];
            }

            return response()->json([
                "success"   => true,
                "message"   => "New role created",
                'data'      => new RoleResource($roles_data)
            ]);
        }
    }

    /**
     * Delete a role.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $project_id, $role_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $project_id                     = deobfuscate($project_id);
        $role_id                        = deobfuscate($role_id);

        $role                           = roles::where('company_id', $this->company_id)
                                            ->where('project_id', $project_id)
                                            ->where('deleted', 0)->find($role_id);


        if (!$role) {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. role, you trying to update, not found."
                                    ], 404);
        }

        // data validation 
        $request->validate($role);
        $role_data                      = $request->get_put_data();

        if ($role->update_role($role_data)) {
            return response()->json([
                                        "success" => true,
                                        "message" => "Role update successfuly.",
                                        'data' => new RoleResource($role)
                                    ]);
        }
    }

    /**
     * Delete a role.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $project_id, $role_id)
    {
        $this->current_user             = $request->user();
        $this->company_id               = $this->current_user['company_id'];
        $this->current_user_id          = $this->current_user['user_id'];
        $project_id                     = deobfuscate($project_id);
        $role_id                        = deobfuscate($role_id);

        $role                           = roles::where('company_id', $this->company_id)
                                            ->where('project_id', $project_id)
                                            ->where('deleted', 0)->find($role_id);

        if (!$role) {
            return response()->json([
                                        "success" => false,
                                        "message" => "Role not found."
                                    ], 404);
        }

        $role->delete();

        return response()->json([
                                    "success" => true,
                                    "message" => "Role deleted successfuly."
                                ]);
    }
}
