@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Order Details</h1>
                    <p class="text-gray-600">Order #<span class="font-bold text-[#800000]">{{ $order->order_ref }}</span></p>
                    <p class="text-sm text-gray-500 mt-2">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Order Status -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#800000] hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Order Status</p>
                        <p class="text-3xl font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                    </div>
                    <div class="w-14 h-14 bg-[#800000] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#800000] hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Payment Status</p>
                        <p class="text-3xl font-bold text-gray-900">
                            @if($order->payment_status === 'paid' || $order->payment_status === 'verified')
                                Paid ✓
                            @else
                                {{ ucfirst($order->payment_status) }}
                            @endif
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-[#800000] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Order Items <span class="text-[#800000]">({{ $order->orderItems->count() }})</span></h2>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200">
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden border border-gray-300">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                            <svg class="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900">{{ $item->product->name ?? 'Product' }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">SKU: <span class="font-medium">{{ $item->product->sku ?? 'N/A' }}</span></p>
                                    <div class="flex items-center gap-3 mt-3">
                                        <span class="inline-block px-3 py-1 bg-[#fef2f2] text-[#800000] text-xs font-semibold rounded-lg">Qty: {{ $item->quantity }}</span>
                                        <span class="inline-block px-3 py-1 bg-[#fef2f2] text-[#800000] text-xs font-semibold rounded-lg">₱{{ number_format($item->price, 2) }} each</span>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs text-gray-600 mb-1">Subtotal</p>
                                    <p class="text-xl font-bold text-[#800000]">₱{{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Delivery Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Delivery Type</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst($order->delivery_type === 'deliver' ? 'Delivery' : 'Pickup') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Tracking Number</p>
                            <p class="text-lg font-bold text-gray-900 font-mono">{{ $order->tracking_number ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($order->delivery_type === 'deliver')
                        <div class="pt-6 border-t-2 border-gray-200">
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Delivery Address</p>
                            <p class="text-gray-900">{{ $order->delivery_address }}</p>
                        </div>
                    @endif
                </div>

                <!-- Payment Information -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Payment Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Payment Method</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Payment Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#fef2f2] text-[#800000]">
                                @if($order->payment_status === 'paid' || $order->payment_status === 'verified')
                                    ✓ Paid
                                @elseif($order->payment_status === 'pending')
                                    ⏳ Pending
                                @else
                                    ✕ Failed
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 sticky top-6">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Subtotal</span>
                            <span class="font-bold">₱{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Shipping</span>
                            <span class="font-bold">₱{{ number_format($order->shipping_fee, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-[#800000]">
                                <span class="font-medium">Discount</span>
                                <span class="font-bold">-₱{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t-2 border-gray-200 pt-4 flex justify-between bg-[#fef2f2] rounded-lg p-3">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-[#800000]">₱{{ number_format($order->total_amount ?? $order->total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</p>
                        </div>
                        <p class="font-bold text-gray-900">{{ $order->customer_name }}</p>
                        <p class="text-xs text-gray-600 mt-2">{{ $order->customer_email }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $order->customer_phone }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        @if($order->payment_status === 'pending' && $order->payment_method === 'gcash')
                            <a href="{{ route('payment.online', $order->id) }}" class="block w-full text-center px-4 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                                💳 Complete Payment
                            </a>
                        @elseif($order->payment_status === 'pending' && $order->payment_method === 'bank_transfer')
                            <a href="{{ route('payment.bank', $order->id) }}" class="block w-full text-center px-4 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                                📄 Upload Receipt
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
