<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// GET route to retrieve user data
Route::middleware('auth:sanctum')->get('/api/user', [AuthController::class, 'getUser']);


// Project
Route::middleware('auth:sanctum')->group(function () {
    // Get all projects for the authenticated user
    Route::get('/api/projects', [ProjectController::class, 'index']);

    Route::post('/api/projects', [ProjectController::class, 'store']);

    // Get a specific project
    Route::get('/api/projects/{id}', [ProjectController::class, 'show']);

    // Update a specific project
    Route::put('/api/projects/{id}', [ProjectController::class, 'update']);

    // Delete a specific project
    Route::delete('/api/projects/{id}', [ProjectController::class, 'destroy']);
});

// Update a user data
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/api/user', [UserController::class, 'update']);
});
