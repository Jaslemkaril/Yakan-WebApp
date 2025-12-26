<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
        // Share pending orders count with admin layout
        View::composer('layouts.admin', function ($view) {
            $pendingOrdersCount = Order::whereRaw('LOWER(status) = ?', ['pending'])->count();
            $view->with('pendingOrdersCount', $pendingOrdersCount);
        });

        // Share unread chat count with app layout
        View::composer('layouts.app', function ($view) {
            $unreadChatCount = 0;
            if (auth()->check()) {
                // Count chats that have unread messages from admin
                $unreadChatCount = Chat::where('user_id', auth()->id())
                    ->whereHas('messages', function ($query) {
                        $query->where('is_read', false)
                              ->where('sender_type', '!=', 'user');
                    })
                    ->count();
            }
            $view->with('unreadChatCount', $unreadChatCount);
        });
    }
}
