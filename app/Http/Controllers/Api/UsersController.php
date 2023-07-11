<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsersController extends Controller
{
    /**
     * Listing of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $users_data             = User::find_users($this->company_id);

        if(!$users_data)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Users not found."
                                    ], 404);
        }

        $users_info             = array();

        foreach($users_data as $user_data)
        {
            $users_info[]       = new UserResource($user_data);
        }

        return response()->json([
                                    "success" => true,
                                    "message" => "Users data",
                                    'data' => $users_info
                                ]);
    }

    /**
     * User details.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $this->current_user     = $request->user();
        $this->company_id       = $this->current_user['company_id'];
        $this->current_user_id  = $this->current_user['user_id'];
        $users_data             = User::find_users($this->company_id, deobfuscate($id));       
        
        if(!empty($users_data))
        {
            $users_data = $users_data[0];
        }
        else
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "User not found."
                                    ], 404);
        }

        $users_info             = array();
        $users_info             = new UserResource($users_data);
        return response()->json([
                                    "success" => true,
                                    "message" => "Users data",
                                    'data' => $users_info
                                ]);
    }

    /**
     * Create a new users.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->current_user         = $request->user();
        $this->company_id           = $this->current_user['company_id'];
        $this->current_user_id      = $this->current_user['user_id'];

        $user_data                  = $request->validate([
                                                        'first_name'    => ['required'],
                                                        'email'         => ['required', 'email'],
                                                        'password'      => ['required', Rules\Password::defaults()],
                                                    ]);

        $user = new User;
        $user->first_name           = $user_data['first_name'];
        $user->last_name            = $request->last_name;
        $user->email                = $user_data['email'];
        $user->password             = Hash::make($request->password);
        $user->company_id           = $this->company_id;
        $user->created_by           = $this->current_user_id;
        $user->role_id              = 2;
        $user->save();

        return response()->json([
                                        "success" => true,
                                        "message" => "New users created",
                                        'data' => new UserResource($user)
                                    ]);
    }

    /**
     * Update user info.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->current_user         = $request->user();
        $this->company_id           = $this->current_user['company_id'];
        $this->current_user_id      = $this->current_user['user_id'];

        $user_data                  = $request->validate([
                                                        'first_name'    => ['required'],
                                                        'email'         => ['required', 'email'],
                                                        'password'      => ['required', Rules\Password::defaults()],
                                                    ]);
                                                    
        $user_id                    = deobfuscate($id);

        if(!$user_id)
        {
            throw ValidationException::withMessages([
                'company' => __('auth.companyNoExist'),
            ]);
        }

        $user                       = User::where('company_id', $this->company_id)->where('deleted', 0)->find($user_id);
        
        if(!$user)
        {
            return response()->json([
                                        "success" => false,
                                        "message" => "Invalid request. User trying to update, not found."
                                    ], 404);
        }

        $user->first_name           = $user_data['first_name'];
        $user->last_name            = $request->last_name;
        $user->email                = $user_data['email'];
        $user->password             = Hash::make($request->password);
        $user->profile_pic          = $request->profile_picture;
        $user->role_id              = 2;
    
        if($user->save())
        {
            $users_data             = User::find_users($this->company_id, $user_id); 
        }

        return response()->json([
                                        "success" => true,
                                        "message" => "Users data updated successfuly",
                                        'data' => new UserResource($users_data[0])
                                    ]);
    }
}
