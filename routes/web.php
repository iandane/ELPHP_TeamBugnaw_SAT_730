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
Route::middleware('auth:sanctum')->post('/api/logout', [AuthController::class, 'logout']);

// GET route to retrieve user data
Route::middleware('auth:sanctum')->get('/api/user', [AuthController::class, 'getUser']);

// Project routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/projects', [ProjectController::class, 'index']);
    Route::post('/api/projects', [ProjectController::class, 'store']);
    Route::get('/api/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/api/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/api/projects/{id}', [ProjectController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Update user data with token
    Route::put('/api/user/{token}', [UserController::class, 'update']);
});
Route::middleware('auth:sanctum')->delete('/api/user/{id}', [UserController::class, 'destroy']);


