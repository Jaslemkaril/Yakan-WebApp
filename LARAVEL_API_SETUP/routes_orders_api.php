<?php

/**
 * API Routes - Order Management
 * 
 * Add these routes to your routes/api.php file
 * 
 * Usage:
 * - Mobile: POST /api/v1/orders
 * - Admin: GET /api/v1/admin/orders
 * - Admin: PATCH /api/v1/admin/orders/{id}/status
 */

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function () {
    
    // ===================== MOBILE - ORDER SUBMISSION =====================
    // Customer submits order from mobile app
    Route::post('/orders', [OrderController::class, 'store'])
        ->name('orders.store');

    // Customer views their orders
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    // Customer views single order details
    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->name('orders.show');

    
    // ===================== ADMIN - ORDER MANAGEMENT =====================
    // Admin views all orders (requires authentication and admin role)
    Route::get('/admin/orders', [OrderController::class, 'adminIndex'])
        ->middleware(['auth:sanctum']) // Ensure admin is authenticated
        ->name('admin.orders.index');

    // Admin updates order status (confirms, marks as processing, ships, etc.)
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus'])
        ->middleware(['auth:sanctum']) // Ensure admin is authenticated
        ->name('admin.orders.update-status');

});
