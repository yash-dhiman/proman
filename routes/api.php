<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\ProjectsController;
use App\Http\Controllers\Api\TasklistsController;
use App\Http\Controllers\Api\Tasks\TasksController;

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
    Route::resource('users', UsersController::class);

    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists', TasklistsController::class);        
    });

    Route::prefix('projects')->group(function () {
        Route::resource('{project_id}/tasklists/{tasklist_id}/tasks', TasksController::class);        
    });

    Route::resource('projects', ProjectsController::class);
});