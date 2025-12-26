@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    /* Hide floating background elements on notifications page */
    .floating-element,
    .floating-1,
    .floating-2,
    .floating-3 {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-maroon-600 to-maroon-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">Notifications</h1>
                        <p class="text-gray-600 mt-1">Stay updated with your orders and activities</p>
                    </div>
                </div>
                @if($notifications->count() > 0)
                    <form action="{{ route('notifications.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition duration-300 shadow-md hover:shadow-lg font-medium flex items-center gap-2">
                            <i class="fas fa-trash"></i>
                            Clear All
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        // Determine notification type and styling
                        $type = 'info';
                        $icon = 'fa-bell';
                        $iconColor = '#1d4ed8';
                        $borderColor = '#2563eb';
                        $bgColor = '#dbeafe';
                        
                        if (str_contains(strtolower($notification->title ?? ''), 'payment')) {
                            $type = 'payment';
                            $icon = 'fa-credit-card';
                            $iconColor = '#15803d';
                            $borderColor = '#16a34a';
                            $bgColor = '#dcfce7';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'order')) {
                            $type = 'order';
                            $icon = 'fa-box';
                            $iconColor = '#800000';
                            $borderColor = '#800000';
                            $bgColor = '#f5e6e6';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'shipped')) {
                            $type = 'shipped';
                            $icon = 'fa-truck';
                            $iconColor = '#7e22ce';
                            $borderColor = '#9333ea';
                            $bgColor = '#e9d5ff';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'delivered')) {
                            $type = 'delivered';
                            $icon = 'fa-check-circle';
                            $iconColor = '#047857';
                            $borderColor = '#059669';
                            $bgColor = '#d1fae5';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'custom')) {
                            $type = 'custom';
                            $icon = 'fa-palette';
                            $iconColor = '#c2410c';
                            $borderColor = '#ea580c';
                            $bgColor = '#fed7aa';
                        }
                    @endphp
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition duration-300 p-6 overflow-hidden relative" style="border-left: 4px solid {{ $borderColor }};">
                        <div class="relative flex items-start justify-between z-10">
                            <div class="flex items-start gap-4 flex-1">
                                <!-- Icon -->
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-sm" style="background-color: {{ $bgColor }};">
                                        <i class="fas {{ $icon }} text-xl" style="color: {{ $iconColor }};"></i>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="text-lg font-bold text-gray-900">
                                            {{ $notification->title ?? 'Notification' }}
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white z-20" style="border: 2px solid {{ $borderColor }}; color: {{ $borderColor }};">
                                            <span class="inline-block w-2 h-2 rounded-full mr-1.5" style="background-color: {{ $borderColor }};"></span>
                                            {{ ucfirst($type) }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mt-2 leading-relaxed text-sm">
                                        {{ $notification->message }}
                                    </p>
                                    
                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center gap-1.5 text-gray-500">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M d, Y H:i') }}">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </time>
                                        </span>
                                        
                                        @if($notification->data && isset($notification->data['order_id']))
                                            <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:shadow-md transition font-medium text-xs" style="background-color: {{ $bgColor }}; color: {{ $borderColor }};">
                                                <i class="fas fa-box"></i>
                                                View Order
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Button -->
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="ml-4 flex-shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-600 transition duration-200 p-2 hover:bg-red-50 rounded-lg" title="Delete notification">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="mt-12">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm p-16 text-center border border-gray-100">
                <div class="text-6xl mb-6">
                    🔔
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Notifications</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">You're all caught up! Check back later for updates on your orders and account activities.</p>
                <a href="{{ route('products.index') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-300 shadow-md hover:shadow-lg font-semibold flex items-center gap-2 justify-center">
                    <i class="fas fa-home"></i>
                    Back to Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
