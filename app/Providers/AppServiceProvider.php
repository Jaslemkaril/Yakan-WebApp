<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Chat;
use App\Models\CustomOrder;
use App\Models\OrderRefundRequest;
use App\Models\CustomOrderRefundRequest;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production (Railway, Heroku, etc.)
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Share notification counts with admin layout
        View::composer('layouts.admin', function ($view) {
            try {
                // Pending regular orders
                $pendingOrdersCount = Order::whereRaw('LOWER(status) = ?', ['pending'])->count();
                
                // Post-Order Requests: Pending cancellation and refund requests
                $postOrderRequestsCount = 0;
                if (Schema::hasTable('order_refund_requests')) {
                    $postOrderRequestsCount += OrderRefundRequest::whereIn('status', ['requested', 'under_review'])->count();
                }
                if (Schema::hasTable('custom_order_refund_requests')) {
                    $postOrderRequestsCount += CustomOrderRefundRequest::whereIn('status', ['requested', 'under_review'])->count();
                }
                
                // Custom Orders: Pending quotes + pending cancellation/refund requests
                $customOrdersCount = 0;
                if (Schema::hasTable('custom_orders')) {
                    $customOrdersCount = CustomOrder::where('status', 'pending')->count();
                }
                if (Schema::hasTable('custom_order_refund_requests')) {
                    $customOrdersCount += CustomOrderRefundRequest::whereIn('status', ['requested', 'under_review'])->count();
                }
                
                $view->with([
                    'pendingOrdersCount' => $pendingOrdersCount,
                    'postOrderRequestsCount' => $postOrderRequestsCount,
                    'customOrdersCount' => $customOrdersCount,
                ]);
            } catch (\Throwable $e) {
                \Log::error('View composer error (admin counts): ' . $e->getMessage());
                $view->with([
                    'pendingOrdersCount' => 0,
                    'postOrderRequestsCount' => 0,
                    'customOrdersCount' => 0,
                ]);
            }
        });

        // Share unread chat count with app layout - DISABLED for debugging
        View::composer('layouts.app', function ($view) {
            $view->with('unreadChatCount', 0);
            /*
            try {
                $unreadChatCount = 0;
                if (auth()->check() && auth()->id()) {
                    // Count chats that have unread messages from admin
                    $unreadChatCount = Chat::where('user_id', auth()->id())
                        ->whereHas('messages', function ($query) {
                            $query->where('is_read', false)
                                  ->where('sender_type', '!=', 'user');
                        })
                        ->count();
                }
                $view->with('unreadChatCount', $unreadChatCount);
            } catch (\Throwable $e) {
                \Log::error('View composer error (unreadChatCount): ' . $e->getMessage());
                $view->with('unreadChatCount', 0);
            }
            */
        });
    }
}
