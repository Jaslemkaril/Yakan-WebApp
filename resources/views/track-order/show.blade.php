@extends('layouts.app')

@section('title', 'Track Order - ' . $order->tracking_number)

@push('styles')
<style>
    .tracking-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .tracking-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        padding-bottom: 40px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        bottom: -20px;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-item.active::before {
        background: #800000;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 3px solid #e5e7eb;
        z-index: 1;
    }

    .timeline-item.active .timeline-icon {
        border-color: #800000;
        background: #800000;
        color: white;
        box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
    }

    .timeline-item.completed .timeline-icon {
        border-color: #22c55e;
        background: #22c55e;
        color: white;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-processing { background: #dbeafe; color: #1e40af; }
    .status-shipped { background: #e0e7ff; color: #4338ca; }
    .status-delivered { background: #d1fae5; color: #065f46; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .info-card {
        background: #f9fafb;
        border-radius: 16px;
        padding: 20px;
        border: 2px solid #e5e7eb;
    }

    .pulse-animation {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="tracking-hero py-12 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-white">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-full mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold mb-2">Order Tracking</h1>
                <p class="text-xl opacity-90">{{ $order->tracking_number }}</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Tracking Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Current Status Card -->
                <div class="tracking-card p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Current Status</h2>
                        <span class="status-badge status-{{ strtolower($order->status) }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="bg-gradient-to-r from-maroon-50 to-maroon-100 rounded-2xl p-6 mb-6" style="background: linear-gradient(to right, rgba(128, 0, 0, 0.05), rgba(128, 0, 0, 0.1));">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background-color: #800000;">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-1">
                                    {{ $order->status === 'completed' ? 'Order Received' : ($order->tracking_status ?? 'Order Placed') }}
                                </h3>
                                <p class="text-gray-600">
                                    @if($order->status === 'completed')
                                        Thank you for confirming your order! We hope you enjoy your purchase.
                                    @elseif($order->estimated_delivery_date)
                                        Estimated delivery: <strong>{{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('M d, Y') }}</strong>
                                    @else
                                        We'll update you with delivery information soon
                                    @endif
                                </p>
                            </div>
                            @if($order->status === 'processing' || $order->status === 'shipped')
                                <div class="pulse-animation">
                                    <svg class="w-6 h-6 text-maroon-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking History</h3>
                    <div class="timeline">
                        @php
                            // Handle both string and array formats
                            $history = is_array($order->tracking_history) 
                                ? $order->tracking_history 
                                : (is_string($order->tracking_history) ? json_decode($order->tracking_history, true) : []);
                            $history = $history ?? [];
                            $statuses = ['Order Placed', 'Processing', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Order Received'];
                            $currentStatus = $order->status === 'completed' ? 'Order Received' : ($order->tracking_status ?? 'Order Placed');
                            $currentIndex = array_search($currentStatus, $statuses);
                        @endphp

                        @forelse($history as $index => $event)
                            <div class="timeline-item {{ $index === 0 ? 'active' : 'completed' }}">
                                <div class="timeline-icon">
                                    @if($index === 0)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $event['status'] ?? 'Update' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $event['date'] ?? now()->format('M d, Y h:i A') }}</p>
                                    @if(isset($event['note']))
                                        <p class="text-sm text-gray-700 mt-2">{{ $event['note'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="timeline-item active">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Order Placed</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                    <p class="text-sm text-gray-700 mt-2">Your order has been received and is being processed.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Order Items -->
                <div class="tracking-card p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Order Items</h3>
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                @if($item->product && $item->product->hasImage())
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-20 h-20 object-cover rounded-lg">
                                @else
                                    <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $item->product->name ?? 'Product' }}</h4>
                                    <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">₱{{ number_format($item->price * $item->quantity, 2) }}</p>
                                    <p class="text-sm text-gray-600">₱{{ number_format($item->price, 2) }} each</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="tracking-card p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Order ID</span>
                            <span class="font-semibold text-gray-900">#{{ $order->id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Order Date</span>
                            <span class="font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery Option</span>
                            <span class="font-semibold text-gray-900">{{ $order->delivery_type === 'pickup' ? 'Store Pickup' : 'Delivery' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Status</span>
                            <span class="font-semibold {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-900">Total Amount</span>
                                <span class="font-bold text-xl" style="color: #800000;">₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Courier Info -->
                @if($order->courier_name)
                    <div class="tracking-card p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Courier Information</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Courier</p>
                                <p class="font-semibold text-gray-900">{{ $order->courier_name }}</p>
                            </div>
                            @if($order->courier_contact)
                                <div>
                                    <p class="text-sm text-gray-600">Contact</p>
                                    <p class="font-semibold text-gray-900">{{ $order->courier_contact }}</p>
                                </div>
                            @endif
                            @if($order->courier_tracking_url)
                                <a href="{{ $order->courier_tracking_url }}" target="_blank" 
                                   class="block w-full text-center px-4 py-2 text-white rounded-lg hover:opacity-90 transition-opacity"
                                   style="background-color: #800000;">
                                    Track on Courier Website
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Order Received Button - Show when Out for Delivery -->
                @if($order->tracking_status === 'Out for Delivery' && $order->status !== 'completed')
                    <div class="tracking-card p-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Order Out for Delivery</h3>
                            <p class="text-sm text-gray-600 mb-4">Your order is on its way! Click below to confirm receipt and leave a review.</p>
                            <a href="{{ route('reviews.create.order', $order->id) }}" 
                               class="block w-full px-4 py-3 text-white font-semibold rounded-lg hover:opacity-90 transition-opacity"
                               style="background-color: #22c55e;">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Order Received - Leave Review
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Delivery Address -->
                <div class="tracking-card p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Delivery Address</h3>
                    <div class="text-sm text-gray-700">
                        <p class="font-semibold text-gray-900">{{ $order->user->name ?? $order->customer_name ?? 'Customer' }}</p>
                        <p class="mt-2">{{ $order->user->email ?? $order->customer_email ?? 'No email' }}</p>
                        @if($order->user && $order->user->phone)
                            <p>{{ $order->user->phone }}</p>
                        @elseif($order->customer_phone)
                            <p>{{ $order->customer_phone }}</p>
                        @endif
                    </div>

                    <!-- Embedded Map -->
                    <div class="mt-4">
                        <div class="rounded-lg overflow-hidden border-2 border-gray-200">
                            @php
                                // Use custom coordinates if available, otherwise default to Manila
                                $latitude = $order->delivery_latitude ?? 14.5995;
                                $longitude = $order->delivery_longitude ?? 121.0194;
                                $address = $order->delivery_address ?? 'Manila, Philippines';
                                
                                // Generate Google Maps embed URL
                                $mapUrl = "https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY_HERE&q={$latitude},{$longitude}&zoom=15";
                                
                                // Alternative: Use iframe embed (no API key needed)
                                $mapUrl = "https://maps.google.com/maps?q={$latitude},{$longitude}&t=&z=15&ie=UTF8&iwloc=&output=embed";
                            @endphp
                            <iframe 
                                width="100%" 
                                height="250" 
                                frameborder="0" 
                                style="border:0"
                                src="{{ $mapUrl }}"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $address }}
                        </p>
                    </div>
                </div>

                <!-- Need Help -->
                <div class="info-card">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-1">Need Help?</h4>
                            <p class="text-sm text-gray-600 mb-3">Contact our customer support for any questions about your order.</p>
                            <a href="#" class="text-sm font-semibold hover:underline" style="color: #800000;">Contact Support →</a>
                        </div>
                    </div>
                </div>

                <!-- Track Another Order -->
                <a href="{{ route('track-order.index') }}" 
                   class="block w-full text-center px-6 py-3 border-2 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
                   style="border-color: #800000; color: #800000;">
                    Track Another Order
                </a>
            </div>
        </div>
    </div>
@endsection
