@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(to bottom right, #800000, #A05050);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                    <p class="text-gray-500">Order details and management</p>
                </div>
            </div>
            <a href="{{ route('admin.regular.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Orders
            </a>
        </div>
    </div>

    <!-- Order Status Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Customer Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Customer</h3>
            </div>
            <div class="space-y-2">
                <p class="text-gray-700 font-medium">{{ $order->customer_name ?? $order->user->name ?? 'Guest' }}</p>
                <p class="text-sm text-gray-500">{{ $order->customer_email ?? $order->user->email ?? 'No email' }}</p>
                @if($order->customer_phone)
                    <p class="text-sm text-gray-500">{{ $order->customer_phone }}</p>
                @endif
            </div>
        </div>

        <!-- Order Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium text-white
                        {{ $order->status == 'pending' ? 'bg-yellow-500' : '' }}
                        {{ $order->status == 'processing' ? 'bg-blue-500' : '' }}
                        {{ $order->status == 'shipped' ? 'bg-indigo-500' : '' }}
                        {{ $order->status == 'delivered' ? 'bg-gray-900' : '' }}
                        {{ $order->status == 'completed' ? 'bg-gray-900' : '' }}
                        {{ $order->status == 'cancelled' ? 'bg-red-600' : '' }}">
                        {{ $order->status == 'completed' ? 'Order Received' : ucfirst($order->status) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Payment:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium text-white
                        {{ $order->payment_status == 'pending' ? 'bg-yellow-500' : '' }}
                        {{ in_array($order->payment_status, ['paid', 'verified']) ? 'bg-gray-900' : '' }}
                        {{ $order->payment_status == 'refunded' ? 'bg-purple-600' : '' }}
                        {{ $order->payment_status == 'failed' ? 'bg-red-600' : '' }}">
                        {{ in_array($order->payment_status, ['paid', 'verified']) ? 'Paid' : ucfirst($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Summary</h3>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Amount:</span>
                    <span class="text-lg font-bold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Order Date:</span>
                    <span class="text-sm text-gray-700">{{ $order->created_at->format('M j, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Tracking #:</span>
                    <span class="text-sm font-mono text-gray-700">{{ $order->tracking_number ?? $order->order_ref ?? ('ORD-' . $order->id) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Delivery Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Payment Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Payment Information</h3>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Method:</span>
                    <span class="text-sm font-medium text-gray-900">
                        @switch($order->payment_method)
                            @case('gcash') GCash @break
                            @case('bank_transfer') Bank Transfer @break
                            @case('cash') Cash on Delivery @break
                            @default {{ ucfirst($order->payment_method ?? 'N/A') }}
                        @endswitch
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $order->payment_status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ in_array($order->payment_status, ['paid', 'verified']) ? 'bg-gray-900 text-white' : '' }}
                        {{ $order->payment_status == 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $order->payment_status == 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ in_array($order->payment_status, ['paid', 'verified']) ? 'Paid' : ucfirst($order->payment_status) }}
                    </span>
                </div>

                <!-- GCash Receipt -->
                @if($order->payment_method === 'gcash' || $order->gcash_receipt)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">GCash Receipt:</span>
                            @if($order->gcash_receipt)
                                <button type="button" onclick="viewAdminReceipt('{{ asset('storage/' . $order->gcash_receipt) }}')" 
                                    class="inline-flex items-center px-3 py-2 text-white rounded-lg transition-colors text-sm font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Receipt
                                </button>
                            @else
                                <span class="text-sm text-gray-500 italic">No receipt uploaded</span>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Bank Transfer Receipt -->
                @if($order->payment_method === 'bank_transfer' || $order->bank_receipt || $order->payment_proof_path)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Bank Transfer Receipt:</span>
                            @php
                                $receiptPath = $order->bank_receipt ?: $order->payment_proof_path;
                            @endphp
                            @if($receiptPath)
                                <button type="button" onclick="viewAdminReceipt('{{ asset('storage/' . $receiptPath) }}')" 
                                    class="inline-flex items-center px-3 py-2 text-white rounded-lg transition-colors text-sm font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Receipt
                                </button>
                            @else
                                <span class="text-sm text-gray-500 italic">No receipt uploaded</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Delivery Information</h3>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Type:</span>
                    <span class="text-sm font-medium text-gray-900">
                        {{ $order->delivery_type == 'pickup' ? 'Store Pickup' : 'Home Delivery' }}
                    </span>
                </div>
                @if($order->delivery_address && $order->delivery_type == 'delivery')
                    <div class="pt-2">
                        <p class="text-xs text-gray-500 mb-1">Delivery Address:</p>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $order->delivery_address }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Items Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Order Items</h2>
                <span class="text-sm text-gray-500">{{ $order->orderItems->count() }} items</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($order->orderItems as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('uploads/products/' . $item->product->image) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="h-16 w-16 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="h-16 w-16 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border border-gray-200">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? 'Deleted Product' }}</div>
                                    @if($item->product && $item->product->category)
                                        <div class="text-sm text-gray-500">{{ $item->product->category->name ?? 'Uncategorized' }}</div>
                                    @endif
                                    @if($item->product && $item->product->sku)
                                        <div class="text-xs text-gray-400 font-mono mt-1">SKU: {{ $item->product->sku }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">₱{{ number_format($item->price, 2) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900">{{ $item->quantity }}</span>
                                <span class="ml-2 text-xs text-gray-500">{{ $item->quantity > 1 ? 'units' : 'unit' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-semibold text-gray-900">₱{{ number_format($item->price * $item->quantity, 2) }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p>No items found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($order->orderItems->count() > 0)
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-900">Total Amount:</td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-lg font-bold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</div>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Admin Activity Log -->
    @php
        $adminNotifications = \App\Models\Notification::where('user_id', auth()->id())
            ->where('type', 'order')
            ->where('data->order_id', $order->id)
            ->latest()
            ->limit(5)
            ->get();
    @endphp
    @if($adminNotifications->count() > 0)
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Admin Activity Log</h3>
                <p class="text-sm text-gray-500">Important order events and confirmations</p>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($adminNotifications as $notification)
                <div class="flex items-start p-4 bg-gradient-to-r {{ strpos($notification->title, 'Confirmed by Customer') !== false ? 'from-green-50 to-emerald-50 border-l-4 border-green-500' : 'rounded-lg' }}" style="{{ strpos($notification->title, 'Confirmed by Customer') === false ? 'background: linear-gradient(to right, #fff5f5, #ffe5e5); border-left: 4px solid #A05050;' : '' }}">
                    <div class="flex-shrink-0 mt-1">
                        @if(strpos($notification->title, 'Confirmed by Customer') !== false)
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" style="color: #A05050;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-semibold" style="{{ strpos($notification->title, 'Confirmed by Customer') !== false ? 'color: #065f46;' : 'color: #800000;' }}">
                            {{ $notification->title }}
                        </p>
                        <p class="text-sm mt-1" style="{{ strpos($notification->title, 'Confirmed by Customer') !== false ? 'color: #047857;' : 'color: #A05050;' }}">
                            {{ $notification->message }}
                        </p>
                        <p class="text-xs mt-2" style="{{ strpos($notification->title, 'Confirmed by Customer') !== false ? 'color: #059669;' : 'color: #C08080;' }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Order Timeline -->
    @if($order->tracking_history)
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Order Timeline</h3>
                <p class="text-sm text-gray-500">Complete history of order status changes</p>
            </div>
        </div>

        <div class="relative">
            <div class="absolute left-6 top-0 bottom-0 w-0.5" style="background-color: #e0b0b0;"></div>
            <div class="space-y-6">
                @foreach(is_array($order->tracking_history) ? $order->tracking_history : json_decode($order->tracking_history, true) ?? [] as $index => $event)
                    <div class="relative flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center relative z-10" style="background-color: rgba(160, 80, 80, 0.2);">
                            <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="ml-6 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ $event['status'] ?? 'Status Update' }}</p>
                                <p class="text-xs text-gray-500">{{ $event['date'] ?? 'N/A' }}</p>
                            </div>
                            @if(isset($event['note']))
                                <p class="text-sm text-gray-600 mt-1">{{ $event['note'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Customer Notes & Admin Notes Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Customer Notes -->
        @if($order->customer_notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Customer Notes</h3>
                        <p class="text-sm text-gray-500">Special instructions from customer</p>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-gray-700 leading-relaxed">{{ $order->customer_notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Order Progress Tracker -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-900">Order Progress</h3>
        </div>
        
        <div class="relative px-4">
            <!-- Progress Line Background -->
            <div class="absolute top-8 left-0 right-0 h-0.5 bg-gray-200" style="margin: 0 4rem;"></div>
            <!-- Active Progress Line -->
            <div class="absolute top-8 left-0 h-0.5 bg-blue-600 transition-all duration-500" style="margin-left: 4rem; width: 
                @if($order->status == 'pending') 0%
                @elseif($order->status == 'processing') calc(33.33% - 2.67rem)
                @elseif($order->status == 'shipped') calc(66.66% - 5.33rem)
                @elseif($order->status == 'delivered') calc(100% - 8rem)
                @else 0%
                @endif;"></div>
            
            <!-- Progress Steps -->
            <div class="relative flex justify-between items-start">
                <!-- Pending -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'pending' ? 'text-red-900' : 'text-gray-600' }}">Pending</p>
                </div>

                <!-- Processing -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'processing' ? 'text-red-900' : 'text-gray-600' }}">Processing</p>
                </div>

                <!-- Shipped -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['shipped', 'delivered']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['shipped', 'delivered']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['shipped', 'delivered']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'shipped' ? 'text-red-900' : 'text-gray-600' }}">Shipping</p>
                </div>

                <!-- Delivered -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ $order->status == 'delivered' ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ $order->status == 'delivered' ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ $order->status == 'delivered' ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2" style="{{ $order->status == 'delivered' ? 'color: #800000;' : 'color: #6B7280;' }}">Delivered</p>
                </div>
            </div>
        </div>

        <!-- Status Box -->
        <div class="mt-6 rounded-lg p-4" style="background-color: #fff5f5; border: 1px solid #e0b0b0;">
            <div class="text-center">
                <span class="text-sm text-gray-600">Status: </span>
                <span class="text-sm font-bold 
                    {{ $order->status == 'pending' ? 'text-yellow-600' : '' }}
                    {{ $order->status == 'processing' ? 'text-red-900' : '' }}
                    {{ $order->status == 'shipped' ? 'text-red-900' : '' }}
                    {{ $order->status == 'delivered' ? 'text-red-900' : '' }}
                    {{ $order->status == 'cancelled' ? 'text-red-600' : '' }}">
                    @if($order->status == 'delivered')
                        Completed
                    @else
                        {{ ucfirst($order->status) }}
                    @endif
                </span>
            </div>
        </div>

        @if($order->status == 'cancelled')
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-red-700">This order has been cancelled</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Update Status Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Update Order Status</h3>
            </div>
            
            <div class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Current Status:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium text-white
                            {{ $order->status == 'pending' ? 'bg-yellow-500' : '' }}
                            {{ $order->status == 'processing' ? 'bg-red-900' : '' }}
                            {{ $order->status == 'shipped' ? 'bg-indigo-500' : '' }}
                            {{ $order->status == 'delivered' ? 'bg-gray-900' : '' }}
                            {{ $order->status == 'completed' ? 'bg-green-600' : '' }}
                            {{ $order->status == 'cancelled' ? 'bg-red-600' : '' }}">
                            @if($order->status == 'pending')
                                Pending
                            @elseif($order->status == 'processing')
                                Processing
                            @elseif($order->status == 'shipped')
                                Shipped
                            @elseif($order->status == 'delivered')
                                Delivered
                            @elseif($order->status == 'completed')
                                Completed
                            @elseif($order->status == 'cancelled')
                                Cancelled
                            @endif
                        </span>
                    </div>
                </div>

                @if($order->status == 'pending')
                    <form action="{{ route('admin.orders.quickUpdateStatus', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="w-full text-white px-4 py-3 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Mark as Processing
                        </button>
                    </form>
                @elseif($order->status == 'processing')
                    <form action="{{ route('admin.orders.quickUpdateStatus', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="w-full text-white px-4 py-3 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            Mark as Shipped
                        </button>
                    </form>
                @elseif($order->status == 'shipped')
                    <form action="{{ route('admin.orders.quickUpdateStatus', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mark as Delivered
                        </button>
                    </form>
                @elseif($order->status == 'delivered' || $order->status == 'completed')
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Order Completed</p>
                        <p class="text-xs text-gray-500 mt-1">This order has been delivered</p>
                    </div>
                @elseif($order->status == 'cancelled')
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-red-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Order Cancelled</p>
                        <p class="text-xs text-gray-500 mt-1">This order has been cancelled</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="space-y-3">
                <!-- Refund Button -->
                @if($order->payment_status === 'paid' && $order->status !== 'cancelled')
                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" onclick="return confirm('Are you sure you want to refund this order? This action cannot be undone.');" class="w-full text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Refund Order
                    </button>
                </form>
                @endif
                
                <!-- Download Invoice -->
                <a href="{{ route('admin.orders.invoice', $order->id) }}" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors duration-200 font-medium flex items-center justify-center inline-block text-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Invoice
                </a>
                
                <!-- Cancel Order (if not cancelled) -->
                @if($order->status !== 'cancelled')
                <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this order?');" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Order
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Admin Notes Section -->
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                    <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Admin Notes</h3>
                    <p class="text-sm text-gray-500">Internal notes (not visible to customer)</p>
                </div>
            </div>
            
            <form action="{{ route('admin.orders.update-notes', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <textarea name="admin_notes" rows="4" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 resize-none" style="outline: none;" onfocus="this.style.borderColor='#800000'; this.style.boxShadow='0 0 0 3px rgba(128, 0, 0, 0.1)';" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                          placeholder="Add internal notes about this order...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                <button type="submit" class="mt-3 w-full text-white py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Admin Notes
                </button>
            </form>
        </div>
    </div>

    <!-- Tracking Management Section -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: rgba(128, 0, 0, 0.1);">
                <svg class="w-5 h-5" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Tracking Management</h3>
                <p class="text-sm text-gray-500">Update delivery tracking information</p>
            </div>
        </div>

        <form action="{{ route('admin.orders.update_tracking', $order->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tracking Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tracking Status <span class="text-red-500">*</span>
                    </label>
                    <select name="tracking_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                        <option value="">Select Status</option>
                        <option value="Order Placed" {{ $order->tracking_status === 'Order Placed' ? 'selected' : '' }}>📦 Order Placed</option>
                        <option value="Processing" {{ $order->tracking_status === 'Processing' ? 'selected' : '' }}>⚙️ Processing</option>
                        <option value="Packed" {{ $order->tracking_status === 'Packed' ? 'selected' : '' }}>📦 Packed</option>
                        <option value="Shipped" {{ $order->tracking_status === 'Shipped' ? 'selected' : '' }}>🚚 Shipped</option>
                        <option value="Out for Delivery" {{ $order->tracking_status === 'Out for Delivery' ? 'selected' : '' }}>🚛 Out for Delivery</option>
                        <option value="Delivered" {{ $order->tracking_status === 'Delivered' ? 'selected' : '' }}>✅ Delivered</option>
                    </select>
                </div>

                <!-- Courier Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Courier Name</label>
                    <input type="text" name="courier_name" value="{{ old('courier_name', $order->courier_name) }}" 
                           placeholder="e.g., LBC, J&T, Ninja Van"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                           style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                </div>

                <!-- Courier Contact -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Courier Contact</label>
                    <input type="text" name="courier_contact" value="{{ old('courier_contact', $order->courier_contact) }}" 
                           placeholder="Phone or email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                           style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                </div>

                <!-- Estimated Delivery Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Delivery Date</label>
                    <input type="date" name="estimated_delivery_date" 
                           value="{{ old('estimated_delivery_date', $order->estimated_delivery_date ? \Carbon\Carbon::parse($order->estimated_delivery_date)->format('Y-m-d') : '') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                           style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                </div>
            </div>

            <!-- Courier Tracking URL -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Courier Tracking URL</label>
                <input type="url" name="courier_tracking_url" value="{{ old('courier_tracking_url', $order->courier_tracking_url) }}" 
                       placeholder="https://tracking.courier.com/track/ABC123"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                       style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                <p class="text-xs text-gray-500 mt-1">External tracking link for customers</p>
            </div>

            <!-- Tracking Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Notes</label>
                <textarea name="tracking_notes" rows="3" 
                          placeholder="Add notes about this tracking update..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors resize-none" 
                          style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">{{ old('tracking_notes', $order->tracking_notes) }}</textarea>
            </div>

            <!-- Delivery Location (Optional) -->
            <div class="border-t pt-6">
                <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Delivery Location (Optional - For Map Display)
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                        <input type="text" name="delivery_address" value="{{ old('delivery_address', $order->delivery_address) }}" 
                               placeholder="e.g., 123 Main St, Manila, Philippines"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                               style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                        <input type="number" step="0.00000001" name="delivery_latitude" value="{{ old('delivery_latitude', $order->delivery_latitude) }}" 
                               placeholder="14.5995"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                               style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                        <input type="number" step="0.00000001" name="delivery_longitude" value="{{ old('delivery_longitude', $order->delivery_longitude) }}" 
                               placeholder="121.0194"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 transition-colors" 
                               style="border-color: #800000; --tw-ring-color: rgba(128, 0, 0, 0.2);">
                    </div>
                    
                    <div class="flex items-end">
                        <a href="https://www.google.com/maps" target="_blank" 
                           class="text-xs hover:underline flex items-center" style="color: #800000;">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Get coordinates from Google Maps
                        </a>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-2">
                    💡 <strong>Tip:</strong> Right-click on Google Maps → Click coordinates to copy → Paste here
                </p>
            </div>

            <!-- Current Tracking Info -->
            @if($order->tracking_status || $order->courier_name)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Current Tracking Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @if($order->tracking_status)
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold text-gray-900 ml-2">{{ $order->tracking_status }}</span>
                            </div>
                        @endif
                        @if($order->courier_name)
                            <div>
                                <span class="text-gray-600">Courier:</span>
                                <span class="font-semibold text-gray-900 ml-2">{{ $order->courier_name }}</span>
                            </div>
                        @endif
                        @if($order->estimated_delivery_date)
                            <div>
                                <span class="text-gray-600">Est. Delivery:</span>
                                <span class="font-semibold text-gray-900 ml-2">{{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if($order->delivered_at)
                            <div>
                                <span class="text-gray-600">Delivered:</span>
                                <span class="font-semibold text-green-600 ml-2">{{ \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                @if($order->tracking_number)
                <a href="{{ route('track-order.show', $order->tracking_number) }}" target="_blank" 
                   class="text-sm font-medium hover:underline" style="color: #800000;">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Preview Customer Tracking Page
                </a>
                @else
                <span class="text-sm text-gray-500">No tracking number assigned yet</span>
                @endif
                <button type="submit" class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition-opacity font-semibold" style="background-color: #800000;">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Tracking Information
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Receipt Modal -->
<div id="adminReceiptModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Payment Receipt</h3>
            <button onclick="closeAdminReceiptModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="bg-gray-100 rounded-xl p-4 mb-4">
                <img id="adminReceiptImage" src="" alt="Payment Receipt" class="w-full rounded-xl border-2 border-gray-300 shadow-lg">
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button onclick="closeAdminReceiptModal()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold">
                    Close
                </button>
                <a id="adminDownloadBtn" href="#" download class="px-6 py-3 text-white rounded-lg transition-colors font-semibold" style="background-color: #800000;">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Receipt
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Receipt Viewer Modal for Admin
function viewAdminReceipt(receiptUrl) {
    const modal = document.getElementById('adminReceiptModal');
    const receiptImage = document.getElementById('adminReceiptImage');
    const downloadBtn = document.getElementById('adminDownloadBtn');
    
    receiptImage.src = receiptUrl;
    downloadBtn.href = receiptUrl;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAdminReceiptModal() {
    const modal = document.getElementById('adminReceiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('adminReceiptModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAdminReceiptModal();
            }
        });
    }
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAdminReceiptModal();
        }
    });
});
</script>
@endsection
