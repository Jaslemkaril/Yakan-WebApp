<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Order;
use App\Models\Chat;

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

        // Share pending orders count with admin layout
        View::composer('layouts.admin', function ($view) {
            try {
                $pendingOrdersCount = Order::whereRaw('LOWER(status) = ?', ['pending'])->count();
                $view->with('pendingOrdersCount', $pendingOrdersCount);
            } catch (\Throwable $e) {
                \Log::error('View composer error (pendingOrdersCount): ' . $e->getMessage());
                $view->with('pendingOrdersCount', 0);
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
