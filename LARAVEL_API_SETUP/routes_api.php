<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;

Route::prefix('api')->group(function () {
    
    // Public routes (no authentication required)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken']);

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/search', [ProductController::class, 'search']);

    // Protected routes (authentication required)
    Route::middleware('auth:api')->group(function () {
        
        // Authentication
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'getCurrentUser']);

        // User profile and addresses
        Route::get('/user/profile', [UserController::class, 'getProfile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::get('/user/addresses', [UserController::class, 'getSavedAddresses']);
        Route::post('/user/addresses', [UserController::class, 'createAddress']);
        Route::put('/user/addresses/{id}', [UserController::class, 'updateAddress']);
        Route::delete('/user/addresses/{id}', [UserController::class, 'deleteAddress']);

        // Orders
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{id}/status', [OrderController::class, 'getStatus']);

        // Payments
        Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
        Route::get('/payments/{orderId}/status', [PaymentController::class, 'getStatus']);
        
        // Admin only: verify payments
        Route::post('/payments/verify', [PaymentController::class, 'verify']);
    });
});
