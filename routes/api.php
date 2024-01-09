<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\Projects\ProjectsController;
use App\Http\Controllers\Api\Projects\RolesController;
use App\Http\Controllers\Api\TasklistsController;
use App\Http\Controllers\Api\Tasks\TasksController;
use App\Http\Controllers\Api\Tasks\CommentsController;
use App\Http\Controllers\Api\Tasks\CommentRepliesController;
use App\Http\Controllers\Api\FileController;
use App\Http\Resources\api\UserResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::resource('users', UsersController::class);
    
    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists', TasklistsController::class);        
    });
    
    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/roles', RolesController::class);        
    });

    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists/{tasklist_id}/tasks', TasksController::class);        
    });
    
    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists/{tasklist_id}/tasks/{task_id}/comments/{comment_id}/replies', CommentRepliesController::class);        
    });
    
    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists/{tasklist_id}/tasks/{task_id}/comments', CommentsController::class);        
    });

    Route::resource('projects', ProjectsController::class);
    Route::post('files', [FileController::class, 'upload'])->name('files.store');
});