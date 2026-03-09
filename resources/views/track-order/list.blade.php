@extends('layouts.app')

@section('title', 'Your Orders')

@push('styles')
<style>
    .orders-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
    }

    .order-card {
        background: white;
        border-radius: 16px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .order-card:hover {
        border-color: #800000;
        box-shadow: 0 10px 30px rgba(128, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .order-card.custom-order:hover {
        border-color: #7c3aed;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.1);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Regular order statuses */
    .status-pending_confirmation { background: #fef3c7; color: #92400e; }
    .status-confirmed   { background: #d1fae5; color: #065f46; }
    .status-processing  { background: #dbeafe; color: #1e40af; }
    .status-shipped     { background: #e0e7ff; color: #4338ca; }
    .status-delivered   { background: #ccfbf1; color: #0f766e; }
    .status-completed   { background: #d1fae5; color: #065f46; }
    .status-cancelled   { background: #fee2e2; color: #991b1b; }
    .status-refunded    { background: #f3f4f6; color: #374151; }
    /* Custom order statuses */
    .status-pending         { background: #fef3c7; color: #92400e; }
    .status-price_quoted    { background: #dbeafe; color: #1e40af; }
    .status-approved        { background: #d1fae5; color: #065f46; }
    .status-in_production   { background: #ede9fe; color: #5b21b6; }
    .status-rejected        { background: #fee2e2; color: #991b1b; }
    .status-verification_pending { background: #ffedd5; color: #9a3412; }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="orders-hero py-16 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <h1 class="text-4xl font-bold mb-2">Your Orders</h1>
            <p class="text-xl opacity-90">{{ $orders->count() }} order(s) found</p>
        </div>
    </section>

    <!-- Orders List -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="space-y-6">
            @foreach($orders as $order)
                @php
                    $isCustom = ($order->_order_type ?? '') === 'custom';
                    $statusLabel = match($order->status) {
                        'pending_confirmation' => 'Pending Confirmation',
                        'price_quoted'         => 'Price Quoted',
                        'in_production'        => 'In Production',
                        'verification_pending' => 'Verification Pending',
                        default                => ucfirst($order->status),
                    };
                    $displayAmount = $isCustom
                        ? ($order->final_price ?? $order->estimated_price ?? null)
                        : ($order->total_amount ?? null);
                @endphp
                <div class="order-card {{ $isCustom ? 'custom-order' : '' }} p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Order Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                @if($isCustom)
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-purple-100 text-purple-700 rounded">🎨 Custom Order</span>
                                @else
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-red-50 text-[#800000] rounded">🛒 Order</span>
                                @endif
                                <h3 class="text-lg font-bold text-gray-900">#{{ $order->id }}</h3>
                                <span class="status-badge status-{{ str_replace(' ', '_', $order->status) }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            @if(!$isCustom && $order->tracking_number)
                            <p class="text-sm text-gray-600 mb-1">
                                <strong>Tracking:</strong> {{ $order->tracking_number }}
                            </p>
                            @endif
                            <p class="text-sm text-gray-600 mb-1">
                                <strong>Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}
                            </p>
                            @if(!$isCustom && isset($order->orderItems))
                            <p class="text-sm text-gray-600">
                                <strong>Items:</strong> {{ $order->orderItems->count() }} item(s)
                            </p>
                            @endif
                        </div>

                        <!-- Order Amount -->
                        <div class="text-center md:text-right">
                            @if($displayAmount)
                                <p class="text-2xl font-bold {{ $isCustom ? 'text-purple-700' : '' }}" @if(!$isCustom) style="color: #800000;" @endif>
                                    ₱{{ number_format($displayAmount, 2) }}
                                </p>
                            @else
                                <p class="text-sm text-gray-400 italic">Awaiting quote</p>
                            @endif
                            @if(!$isCustom)
                            <p class="text-sm text-gray-600">
                                Payment: <span class="font-semibold {{ ($order->payment_status ?? '') === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ ucfirst($order->payment_status ?? 'N/A') }}
                                </span>
                            </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 flex-wrap">
                            @if($isCustom)
                                <a href="{{ route('custom_orders.show', $order->id) }}"
                                   class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition-opacity font-semibold text-sm"
                                   style="background-color: #7c3aed;">
                                    View Custom Order
                                </a>
                            @else
                                @if($order->tracking_number)
                                <a href="{{ route('track-order.show', $order->tracking_number) }}"
                                   class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition-opacity font-semibold text-sm"
                                   style="background-color: #800000;">
                                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Track Order
                                </a>
                                @endif
                                @auth
                                    @if($order->user_id === auth()->id())
                                        <a href="{{ route('orders.show', $order->id) }}"
                                           class="px-6 py-3 border-2 rounded-lg font-semibold hover:bg-gray-50 transition-colors text-sm"
                                           style="border-color: #800000; color: #800000;">
                                            View Details
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>

                    @if(!$isCustom && isset($order->orderItems) && $order->orderItems->count())
                    <!-- Order Items Preview (regular orders only) -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex gap-2 overflow-x-auto">
                            @foreach($order->orderItems->take(4) as $item)
                                @if($item->product && $item->product->hasImage())
                                    <img src="{{ $item->product->image_url }}"
                                         alt="{{ $item->product->name }}"
                                         class="w-16 h-16 object-cover rounded-lg flex-shrink-0"
                                         title="{{ $item->product->name }}">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                            @endforeach
                            @if($order->orderItems->count() > 4)
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-semibold text-gray-600">+{{ $order->orderItems->count() - 4 }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Track Another Order -->
        <div class="mt-8 text-center">
            <a href="{{ route('track-order.index') }}"
               class="inline-flex items-center px-6 py-3 border-2 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
               style="border-color: #800000; color: #800000;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Track Another Order
            </a>
        </div>
    </div>
@endsection
