<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register-form');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password-form');

