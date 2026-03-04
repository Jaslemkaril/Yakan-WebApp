<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomOrderController as AdminCustomOrderController;
use App\Http\Controllers\Admin\AdminNotificationController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['admin', 'throttle:120,1'])
    ->prefix('v1/admin')
    ->as('admin.') // 👈 Add admin name prefix
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Custom Orders Management
        |--------------------------------------------------------------------------
        */
        Route::prefix('custom-orders')
            ->as('custom-orders.') // 👈 name prefix
            ->group(function () {

                Route::get('/', [AdminCustomOrderController::class, 'index'])
                    ->name('index');

                Route::get('/pending', [AdminCustomOrderController::class, 'getPendingOrders'])
                    ->name('pending');

                Route::get('/{id}', [AdminCustomOrderController::class, 'show'])
                    ->name('show');

                Route::post('/{id}/quote-price', [AdminCustomOrderController::class, 'quotePrice'])
                    ->name('quote-price');

                Route::post('/{id}/reject', [AdminCustomOrderController::class, 'rejectOrder'])
                    ->name('reject');

                Route::post('/{id}/notify-user', [AdminCustomOrderController::class, 'notifyUser'])
                    ->name('notify-user');

                Route::post('/{id}/mark-processing', [AdminCustomOrderController::class, 'markAsProcessing'])
                    ->name('mark-processing');

                Route::post('/{id}/mark-completed', [AdminCustomOrderController::class, 'markAsCompleted'])
                    ->name('mark-completed');

                Route::get('/statistics', [AdminCustomOrderController::class, 'getStatistics'])
                    ->name('statistics');
            });

        /*
        |--------------------------------------------------------------------------
        | Admin Notifications
        |--------------------------------------------------------------------------
        */
        Route::prefix('notifications')
            ->as('notifications.') // 👈 IMPORTANT
            ->group(function () {

                Route::get('/', [AdminNotificationController::class, 'index'])
                    ->name('index');

                Route::get('/unread-count', [AdminNotificationController::class, 'unreadCount'])
                    ->name('unread-count');

                Route::post('/{id}/read', [AdminNotificationController::class, 'markAsRead'])
                    ->name('read');

                Route::post('/mark-all-read', [AdminNotificationController::class, 'markAllRead'])
                    ->name('mark-all-read');
            });
    });