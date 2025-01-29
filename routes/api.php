<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('authGoogle', [AuthController::class, 'loginGoogle']);
Route::get('auth/callback', [AuthController::class, 'handleAuthCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('todos')->controller(TodoController::class)->group(function () {
        Route::get('summary', 'getSummary');
        Route::put('updateStatus/{todo}', 'updateStatus');
        Route::post('redo/{todo}', 'redo');
    });

    Route::apiResource('todos', TodoController::class);

    
    Route::post('/logout', [AuthController::class, 'logout']);
});