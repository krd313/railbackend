<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProjectImageController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\TaskImageController;
use App\Http\Controllers\API\TaskNoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('projects', ProjectController::class)->middleware('auth:sanctum');

Route::apiResource('tasks', TaskController::class)->middleware('auth:sanctum');
Route::apiResource('task-images', TaskImageController::class)->middleware('auth:sanctum');
Route::apiResource('tasknotes', TaskNoteController::class)->middleware('auth:sanctum');
Route::apiResource('project-images', ProjectImageController::class)->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);
