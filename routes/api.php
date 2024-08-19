<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'getUserProfile']);
    Route::put('/profile', [UserController::class, 'updateUserProfile']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);

    // Order Routes
    Route::get('/orders/myorders', [OrderController::class, 'getMyOrders']);
    Route::post('/orders', [OrderController::class, 'addOrderItems']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrderById']);
    Route::put('/orders/{id}/pay', [OrderController::class, 'updateOrderToPaid']);
    Route::put('/orders/{id}/ship', [OrderController::class, 'updateOrderToShipped']);
    Route::get('/orders', [OrderController::class, 'getOrders'])->middleware('is_admin');
});

Route::post('/auth', [UserController::class, 'authUser']);
Route::post('/pre-register', [UserController::class, 'preRegister']);
Route::post('/register', [UserController::class, 'registerUser']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::get('/email/verify/{token}', [UserController::class, 'verifyEmail']);
Route::post('/login', [UserController::class, 'authUser']);

// Product Routes
Route::get('/products', [ProductController::class, 'getProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProductById']);
Route::get('/products/slug/{slug}', [ProductController::class, 'getProductBySlug']);

Route::post('/products', [ProductController::class, 'createProduct'])
    ->middleware('auth:sanctum');
Route::put('/products/{id}', [ProductController::class, 'updateProduct'])
    ->middleware('auth:sanctum');
Route::delete('/products/{id}', [ProductController::class, 'deleteProduct'])
    ->middleware('auth:sanctum');

// Stripe Routes
Route::post('/stripe/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
Route::post('/stripe/retrieve-checkout-session', [StripeController::class, 'retrieveCheckoutSession']);
Route::post('/stripe/order-history', [StripeController::class, 'getOrderHistoryByEmail']);
