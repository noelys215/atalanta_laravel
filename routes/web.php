<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect('/register');
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register-form');

Route::post('/register', [UserController::class, 'registerUser'])->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password-form');

Route::post('/forgot-password', [UserController::class, 'forgotPassword'])->name('forgot-password');

Route::get('/email/verify/{token}', [UserController::class, 'verifyEmail'])->name('verify-email');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'getUserProfile']);
    Route::put('/profile', [UserController::class, 'updateUserProfile']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
});


