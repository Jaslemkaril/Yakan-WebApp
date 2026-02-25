<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomOrderController as AdminCustomOrderController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group.
|
*/

Route::middleware(['admin', 'throttle:120,1'])->prefix('v1/admin')->group(function () {
    
    // Custom Orders Management
    Route::prefix('/custom-orders')->group(function () {
        Route::get('/', [AdminCustomOrderController::class, 'index']);
        Route::get('/pending', [AdminCustomOrderController::class, 'getPendingOrders']);
        Route::get('/{id}', [AdminCustomOrderController::class, 'show']);
        Route::post('/{id}/quote-price', [AdminCustomOrderController::class, 'quotePrice']);
        Route::post('/{id}/reject', [AdminCustomOrderController::class, 'rejectOrder']);
        Route::post('/{id}/notify-user', [AdminCustomOrderController::class, 'notifyUser']);
        Route::post('/{id}/mark-processing', [AdminCustomOrderController::class, 'markAsProcessing']);
        Route::post('/{id}/mark-completed', [AdminCustomOrderController::class, 'markAsCompleted']);
        Route::get('/statistics', [AdminCustomOrderController::class, 'getStatistics']);
    });
    
    // Admin Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminNotificationController::class, 'index']);
        Route::get('/unread-count', [App\Http\Controllers\Admin\AdminNotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllAsRead']);
    });
    
});
