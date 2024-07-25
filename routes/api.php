<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'getUserProfile']);
    Route::put('/profile', [UserController::class, 'updateUserProfile']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
});

Route::post('/auth', [UserController::class, 'authUser']);
Route::post('/pre-register', [UserController::class, 'preRegister']);
Route::post('/register', [UserController::class, 'registerUser']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
