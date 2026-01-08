<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\PaymentController;

Route::prefix('v1')->group(function () {
    // ===================== AUTHENTICATION (Public) =====================
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login-guest', [AuthController::class, 'loginGuest']);

    // ===================== PRODUCTS (Public) =====================
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/search', [ProductController::class, 'search']);

    // ===================== ORDERS (Public - Read Only) =====================
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // ===================== AUTHENTICATED ROUTES =====================
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Orders (Create - Requires Auth)
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{id}/upload-receipt', [OrderController::class, 'uploadReceipt']);
        
        // Payment proof upload (mobile/web)
        Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);

        // Wishlist
        Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/wishlist/add', [\App\Http\Controllers\Api\WishlistController::class, 'add']);
        Route::post('/wishlist/remove', [\App\Http\Controllers\Api\WishlistController::class, 'remove']);
        Route::post('/wishlist/check', [\App\Http\Controllers\Api\WishlistController::class, 'check']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'add']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Addresses (for mobile app)
        Route::get('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'index']);
        Route::get('/addresses/default', [\App\Http\Controllers\Api\AddressController::class, 'getDefault']);
        Route::post('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'store']);
        Route::put('/addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
        Route::post('/addresses/{address}/set-default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);

        // Chats (for mobile app)
        Route::get('/chats', [ChatController::class, 'index']);
        Route::get('/chats/{id}', [ChatController::class, 'show']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/chats/{id}/respond-quote', [ChatController::class, 'respondToQuote']);
        Route::patch('/chats/{id}/status', [ChatController::class, 'updateStatus']);

        // Admin Orders
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
    });
});
