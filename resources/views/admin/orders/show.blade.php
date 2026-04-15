@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
@php
    $normalizedDeliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
    if ($normalizedDeliveryType === 'deliver') {
        $normalizedDeliveryType = 'delivery';
    }
    $isPickup = $normalizedDeliveryType === 'pickup';
    $effectiveDeliveryAddress = trim((string) ($order->delivery_address ?: $order->shipping_address ?: ''));
    $effectivePaymentDate = $order->payment_verified_at ?? $order->updated_at ?? $order->created_at;

    $summaryShippingFee = (float) ($order->shipping_fee ?? 0);
    if ($isPickup) {
        $summaryShippingFee = 0;
    }

    $summarySubtotal = $order->subtotal !== null
        ? (float) $order->subtotal
        : max(((float) ($order->total_amount ?? 0) - $summaryShippingFee), 0);
@endphp
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
                    <h1 class="text-3xl font-bold text-gray-900">Order {{ $order->order_ref ?? '#'.$order->id }}</h1>
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
                            @case('maya') Maya @break
                            @case('online') GCash @break
                            @case('online_banking') GCash @break
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
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Reference Number:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $order->order_ref ?? ('ORDER-' . $order->id) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment ID:</span>
                    <span class="text-sm font-medium text-gray-900 break-all text-right">{{ $order->payment_reference ?: 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment Date:</span>
                    <span class="text-sm font-medium text-gray-900 text-right">{{ $effectivePaymentDate ? $effectivePaymentDate->format('M j, Y g:i A') : 'N/A' }}</span>
                </div>

                <!-- Payment Receipt - Show based on actual payment method -->
                @php
                    // Find the receipt URL - check all possible fields
                    $receiptSource = $order->gcash_receipt ?: ($order->bank_receipt ?: $order->payment_proof_path);
                    $receiptDisplayUrl = null;
                    if ($receiptSource) {
                        $receiptDisplayUrl = (str_starts_with($receiptSource, 'http://') || str_starts_with($receiptSource, 'https://'))
                            ? $receiptSource
                            : asset('storage/' . $receiptSource);
                    }

                    $isPaymongo = strtolower((string) ($order->payment_method ?? '')) === 'paymongo';
                    
                    // Label based on payment method
                    $receiptLabel = match($order->payment_method) {
                        'paymongo' => 'PayMongo Verified Receipt',
                        'maya' => 'Maya Receipt',
                        'gcash' => 'GCash Receipt',
                        'online', 'online_banking' => 'GCash Receipt',
                        'bank_transfer' => 'Bank Transfer Receipt',
                        default => 'Payment Receipt',
                    };
                @endphp
                @php
                    $paymongoReceiptUrl = route('admin.orders.paymongo_receipt', $order);
                    $authToken = request()->query('auth_token');
                    if (!empty($authToken)) {
                        $paymongoReceiptUrl .= (str_contains($paymongoReceiptUrl, '?') ? '&' : '?') . 'auth_token=' . urlencode($authToken);
                    }
                @endphp
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ $receiptLabel }}:</span>
                        @if($isPaymongo)
                            <button type="button" onclick="viewPaymongoReceipt('{{ $paymongoReceiptUrl }}')"
                                class="inline-flex items-center px-3 py-2 text-white rounded-lg transition-colors text-sm font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                View Verified Receipt
                            </button>
                        @elseif($receiptDisplayUrl)
                            <button type="button" onclick="viewAdminReceipt('{{ $receiptDisplayUrl }}')" 
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
            @php
                $recipientName  = $order->userAddress->full_name  ?? $order->customer_name  ?? $order->user?->name  ?? null;
                $recipientPhone = $order->userAddress->phone_number ?? $order->customer_phone ?? null;
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Type:</span>
                    <span class="text-sm font-medium text-gray-900">
                        {{ $isPickup ? 'Store Pickup' : 'Home Delivery' }}
                    </span>
                </div>
                @if($recipientName)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Recipient:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $recipientName }}</span>
                    </div>
                @endif
                @if($recipientPhone)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Contact Number:</span>
                        <span class="text-sm font-semibold text-gray-900">
                            <a href="tel:{{ $recipientPhone }}" class="hover:underline" style="color:#800000">{{ $recipientPhone }}</a>
                        </span>
                    </div>
                @endif
                @if($effectiveDeliveryAddress && !$isPickup)
                    <div class="pt-2">
                        <p class="text-xs text-gray-500 mb-1">Delivery Address:</p>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $effectiveDeliveryAddress }}</p>
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
                                        <img src="{{ $item->product->image_src }}" 
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
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Customer Notes</h3>
                        <p class="text-sm text-gray-500">Special instructions from customer</p>
                    </div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                    <p class="text-gray-700 leading-relaxed">{{ $order->customer_notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Order Progress Tracker -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <svg class="w-5 h-5 text-[#800000] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-900">Order Progress</h3>
        </div>
        
        <div class="relative px-4">
            <!-- Progress Line Background -->
            <div class="absolute top-8 left-0 right-0 h-0.5 bg-gray-200" style="margin: 0 4rem;"></div>
            <!-- Active Progress Line -->
            <div class="absolute top-8 left-0 h-0.5 bg-[#800000] transition-all duration-500" style="margin-left: 4rem; width: 
                @if($order->status == 'pending') 0%
                @elseif($order->status == 'processing') calc(33.33% - 2.67rem)
                @elseif($order->status == 'shipped') calc(66.66% - 5.33rem)
                @elseif(in_array($order->status, ['delivered', 'completed'])) calc(100% - 8rem)
                @else 0%
                @endif;"></div>
            
            <!-- Progress Steps -->
            <div class="relative flex justify-between items-start">
                <!-- Pending -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered', 'completed']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered', 'completed']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['pending', 'processing', 'shipped', 'delivered', 'completed']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'pending' ? 'text-red-900' : 'text-gray-600' }}">Pending</p>
                </div>

                <!-- Processing -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['processing', 'shipped', 'delivered', 'completed']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['processing', 'shipped', 'delivered', 'completed']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['processing', 'shipped', 'delivered', 'completed']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'processing' ? 'text-red-900' : 'text-gray-600' }}">Processing</p>
                </div>

                <!-- Shipped -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ in_array($order->status, ['shipped', 'delivered', 'completed']) ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ in_array($order->status, ['shipped', 'delivered', 'completed']) ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ in_array($order->status, ['shipped', 'delivered', 'completed']) ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2 {{ $order->status == 'shipped' ? 'text-red-900' : 'text-gray-600' }}">Shipping</p>
                </div>

                <!-- Delivered -->
                <div class="flex flex-col items-center" style="width: 25%;">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center transition-all duration-300 {{ $order->status == 'completed' ? 'shadow-lg' : 'bg-gray-300' }}" style="{{ $order->status == 'completed' ? 'background-color: #800000;' : '' }}">
                        <svg class="w-8 h-8 {{ $order->status == 'completed' ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium mt-2" style="{{ $order->status == 'completed' ? 'color: #800000;' : 'color: #6B7280;' }}">Delivered</p>
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
                    {{ $order->status == 'delivered' ? 'text-blue-700' : '' }}
                    {{ $order->status == 'completed' ? 'text-green-700' : '' }}
                    {{ $order->status == 'cancelled' ? 'text-red-600' : '' }}">
                    @if($order->status == 'delivered')
                        Delivered — Awaiting Customer Confirmation
                    @elseif($order->status == 'completed')
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
                    <form action="{{ route('admin.orders.quickUpdateStatus', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this order as delivered? Please confirm delivery with the customer first.')">
                        @csrf
                        <input type="hidden" name="status" value="delivered">
                        <input type="hidden" name="confirm_delivery" value="1">
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mark as Delivered
                        </button>
                    </form>
                @elseif($order->status == 'delivered')
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Delivered to Customer</p>
                        <p class="text-xs text-gray-500 mt-1">Awaiting customer confirmation for final completion</p>
                    </div>
                @elseif($order->status == 'completed')
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Order Completed</p>
                        <p class="text-xs text-gray-500 mt-1">Confirmed as received by customer</p>
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
                @php
                    $canCancelOrder = !in_array(strtolower((string) $order->status), ['delivered', 'completed', 'refunded', 'cancelled'], true);
                @endphp

                @if(isset($latestRefundRequest) && $latestRefundRequest)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-900">Customer Refund Request</p>
                        @php
                            $adminRefundStatusMap = [
                                'requested' => 'bg-yellow-100 text-yellow-800',
                                'under_review' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-indigo-100 text-indigo-800',
                                'processed' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $adminRefundClass = $adminRefundStatusMap[$latestRefundRequest->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $adminRefundClass }}">{{ ucfirst(str_replace('_', ' ', $latestRefundRequest->status)) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">Requested by {{ $latestRefundRequest->user->name ?? 'Customer' }} on {{ optional($latestRefundRequest->requested_at)->format('M d, Y h:i A') ?? $latestRefundRequest->created_at->format('M d, Y h:i A') }}</p>
                    <p class="text-sm text-gray-700"><span class="font-semibold">Reason:</span> {{ $latestRefundRequest->reason }}</p>
                    @if(!empty($latestRefundRequest->details))
                        <p class="text-sm text-gray-700 mt-1"><span class="font-semibold">Details:</span> {{ $latestRefundRequest->details }}</p>
                    @endif
                    @php
                        $adminRefundEvidence = is_array($latestRefundRequest->evidence_paths ?? null) ? $latestRefundRequest->evidence_paths : [];
                    @endphp
                    @if(!empty($adminRefundEvidence))
                        <div class="mt-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Evidence</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($adminRefundEvidence as $evidencePath)
                                    @php
                                        $adminEvidenceUrl = route('admin.orders.refund_evidence.view', ['refundRequest' => $latestRefundRequest->id, 'index' => $loop->index]);
                                        $adminExt = strtolower(pathinfo(parse_url($evidencePath, PHP_URL_PATH) ?? $evidencePath, PATHINFO_EXTENSION));
                                        $adminIsImageEvidence = in_array($adminExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                                        $adminIsVideoEvidence = in_array($adminExt, ['mp4', 'mov', 'webm'], true);
                                    @endphp

                                    @if($adminIsImageEvidence)
                                        <a href="{{ $adminEvidenceUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 bg-white" title="Open full image">
                                            <img src="{{ $adminEvidenceUrl }}" alt="Refund evidence" class="w-24 h-24 object-cover hover:opacity-90 transition-opacity">
                                        </a>
                                    @elseif($adminIsVideoEvidence)
                                        <div class="rounded-lg overflow-hidden border border-blue-200 bg-black">
                                            <video controls preload="metadata" class="w-40 h-24 object-cover">
                                                <source src="{{ $adminEvidenceUrl }}">
                                                Your browser does not support video playback.
                                            </video>
                                        </div>
                                    @else
                                        <a href="{{ $adminEvidenceUrl }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded border border-gray-300 text-xs text-gray-700 bg-white hover:bg-gray-100 transition-colors">
                                            View PDF
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if(!empty($latestRefundRequest->admin_note))
                        <p class="text-sm text-gray-700 mt-1"><span class="font-semibold">Admin Note:</span> {{ $latestRefundRequest->admin_note }}</p>
                    @endif
                </div>

                @if(in_array($latestRefundRequest->status, ['requested', 'under_review']))
                <form action="{{ route('admin.orders.refund_requests.approve', $latestRefundRequest->id) }}" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="admin_note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Optional note for customer"></textarea>
                    <button type="submit" onclick="return confirm('Approve and process this refund request?');" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approve & Process Refund
                    </button>
                </form>

                <form action="{{ route('admin.orders.refund_requests.reject', $latestRefundRequest->id) }}" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="admin_note" rows="2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Reason for rejection (required)"></textarea>
                    <button type="submit" onclick="return confirm('Reject this refund request?');" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reject Refund Request
                    </button>
                </form>
                @endif
                @endif

                <!-- Refund Button -->
                @if((!isset($latestRefundRequest) || !$latestRefundRequest) && $order->payment_status === 'paid' && in_array($order->status, ['completed', 'delivered']))
                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure you want to refund this order? This action cannot be undone.');" class="w-full text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Refund Order
                    </button>
                </form>
                @endif
                
                <!-- Download Invoice -->
                <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors duration-200 font-medium flex items-center justify-center inline-block text-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Invoice
                </a>
                
                <!-- Cancel Order -->
                @if($canCancelOrder)
                <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this order?');" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Order
                    </button>
                </form>
                @else
                <button type="button" disabled class="w-full bg-gray-300 text-gray-600 px-4 py-2 rounded-lg cursor-not-allowed font-medium flex items-center justify-center" title="Delivered/completed orders cannot be cancelled.">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel Order Unavailable
                </button>
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

<!-- Verified PayMongo Receipt Modal -->
<div id="paymongoReceiptModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Verified PayMongo Receipt</h3>
            <button onclick="closePaymongoReceiptModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div id="paymongoReceiptLoading" class="text-sm text-gray-500">Fetching verified receipt from PayMongo...</div>
            <div id="paymongoReceiptError" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"></div>

            <div id="paymongoReceiptBody" class="hidden">
                <div id="paymongoReceiptPrintArea" class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-6 py-5 text-white" style="background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-wider opacity-90">Yakan E-commerce Platform</p>
                                <h4 class="text-2xl font-bold">Payment Receipt</h4>
                                <p class="text-sm opacity-90">Verified directly from PayMongo</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 border border-white/30">Verified</span>
                                <p id="pmFetchedAt" class="text-xs mt-2 opacity-90"></p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Reference Number</p>
                        <p id="pmRefNumber" class="text-lg font-bold text-gray-900 break-all">-</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Customer Name</p>
                            <p id="pmCustomerName" class="text-sm font-semibold text-gray-900 break-all text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Customer Email</p>
                            <p id="pmCustomerEmail" class="text-sm font-semibold text-gray-900 break-all text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment ID</p>
                            <p id="pmPaymentId" class="text-sm font-semibold text-gray-900 break-all text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p id="pmPaymentMethod" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment Date</p>
                            <p id="pmPaidAt" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Status</p>
                            <p id="pmStatus" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <p class="text-sm text-gray-500">Amount</p>
                            <p id="pmAmount" class="text-xl font-bold" style="color:#800000;">-</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <p class="text-xs text-gray-500">This receipt is generated from PayMongo API data and is safe for admin verification.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button onclick="closePaymongoReceiptModal()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold">
                    Close
                </button>
                <button id="paymongoPrintBtn" onclick="printPaymongoReceipt()" class="hidden px-6 py-3 text-white rounded-lg transition-colors font-semibold" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                    </svg>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmTrackingDelivered(form) {
    if (!form) {
        return true;
    }

    const trackingStatusField = form.querySelector('select[name="tracking_status"]');
    const confirmDeliveryField = form.querySelector('input[name="confirm_delivery"]');
    const statusValue = (trackingStatusField?.value || '').trim().toLowerCase();

    if (statusValue === 'delivered') {
        const confirmed = confirm('Are you sure you want to set tracking status to Delivered? This should only be done after verifying delivery with the customer.');
        if (!confirmed) {
            return false;
        }

        if (confirmDeliveryField) {
            confirmDeliveryField.value = '1';
        }
    } else if (confirmDeliveryField) {
        confirmDeliveryField.value = '0';
    }

    return true;
}

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

function closePaymongoReceiptModal() {
    const modal = document.getElementById('paymongoReceiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function formatPaymongoStatus(status) {
    if (!status) return 'N/A';
    return String(status).replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
}

function safeText(value, fallback) {
    if (value === null || value === undefined) return fallback;
    const text = String(value).trim();
    return text === '' ? fallback : text;
}

function escapeHtml(value) {
    return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function formatReceiptDate(isoText) {
    if (!isoText) return 'N/A';
    const dateObj = new Date(isoText);
    if (Number.isNaN(dateObj.getTime())) return safeText(isoText, 'N/A');
    return dateObj.toLocaleString();
}

function printPaymongoReceipt() {
    const receipt = window.__paymongoReceiptData;
    if (!receipt) {
    return;
    }

    const printWindow = window.open('', '_blank', 'width=860,height=900');
    if (!printWindow) {
    alert('Please allow pop-ups to print the receipt.');
    return;
    }

    const amountText = `${safeText(receipt.currency, 'PHP')} ${Number(receipt.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verified PayMongo Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; color: #1f2937; }
        .receipt { border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
        .header { background: #800000; color: #fff; padding: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 6px 0 0 0; font-size: 12px; opacity: 0.92; }
        .section { padding: 16px 20px; border-top: 1px solid #f0f0f0; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px dashed #d1d5db; }
        .row:last-child { border-bottom: none; }
        .label { font-size: 12px; color: #6b7280; }
        .value { font-size: 14px; font-weight: 700; text-align: right; word-break: break-word; }
        .amount { font-size: 24px; color: #800000; font-weight: 700; }
        .footer { background: #f9fafb; font-size: 11px; color: #6b7280; padding: 14px 20px; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Yakan Payment Receipt</h1>
            <p>Verified directly from PayMongo API</p>
            <p>Fetched at ${escapeHtml(formatReceiptDate(receipt.fetched_at))}</p>
        </div>
        <div class="section">
            <div class="row"><div class="label">Reference Number</div><div class="value">${escapeHtml(safeText(receipt.reference_number, 'N/A'))}</div></div>
            <div class="row"><div class="label">Customer Name</div><div class="value">${escapeHtml(safeText(receipt.customer_name, 'N/A'))}</div></div>
            <div class="row"><div class="label">Customer Email</div><div class="value">${escapeHtml(safeText(receipt.customer_email, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment ID</div><div class="value">${escapeHtml(safeText(receipt.payment_id, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment Method</div><div class="value">${escapeHtml(safeText(receipt.payment_method, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment Date</div><div class="value">${escapeHtml(formatReceiptDate(receipt.paid_at))}</div></div>
            <div class="row"><div class="label">Status</div><div class="value">${escapeHtml(formatPaymongoStatus(receipt.status))}</div></div>
            <div class="row"><div class="label">Amount</div><div class="value amount">${escapeHtml(amountText)}</div></div>
        </div>
        <div class="footer">This document is generated from trusted gateway data and is intended for admin verification.</div>
    </div>
    <script>window.onload = function() { window.print(); }<\/script>
</body>
</html>`;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
}

async function viewPaymongoReceipt(endpointUrl) {
    const modal = document.getElementById('paymongoReceiptModal');
    const loading = document.getElementById('paymongoReceiptLoading');
    const error = document.getElementById('paymongoReceiptError');
    const body = document.getElementById('paymongoReceiptBody');
    const printBtn = document.getElementById('paymongoPrintBtn');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    body.classList.add('hidden');
    printBtn.classList.add('hidden');
    error.classList.add('hidden');
    error.textContent = '';

    try {
        const response = await fetch(endpointUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const responseText = await response.text();
        let payload = null;

        try {
            payload = responseText ? JSON.parse(responseText) : null;
        } catch (_) {
            payload = null;
        }

        if (!payload || typeof payload !== 'object') {
            const looksLikeHtml = /^\s*</.test(responseText || '');
            if (response.status === 401 || response.status === 403 || response.status === 419 || response.redirected || looksLikeHtml) {
                throw new Error('Admin session expired. Please refresh the page and login again, then retry.');
            }

            const snippet = responseText ? responseText.slice(0, 120).replace(/\s+/g, ' ').trim() : '';
            throw new Error(snippet || 'Server returned an unexpected response while loading the verified receipt.');
        }

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Unable to load verified receipt from PayMongo.');
        }

        const receipt = payload.receipt || {};
        window.__paymongoReceiptData = receipt;

        document.getElementById('pmRefNumber').textContent = safeText(receipt.reference_number, 'N/A');
        document.getElementById('pmCustomerName').textContent = safeText(receipt.customer_name, 'N/A');
        document.getElementById('pmCustomerEmail').textContent = safeText(receipt.customer_email, 'N/A');
        document.getElementById('pmPaymentId').textContent = safeText(receipt.payment_id, 'N/A');
        document.getElementById('pmPaymentMethod').textContent = safeText(receipt.payment_method, 'N/A');
        document.getElementById('pmStatus').textContent = formatPaymongoStatus(receipt.status);
        document.getElementById('pmAmount').textContent = (receipt.currency || 'PHP') + ' ' + Number(receipt.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('pmPaidAt').textContent = formatReceiptDate(receipt.paid_at);
        document.getElementById('pmFetchedAt').textContent = receipt.fetched_at
            ? ('Fetched at ' + formatReceiptDate(receipt.fetched_at))
            : '';

        loading.classList.add('hidden');
        body.classList.remove('hidden');
        printBtn.classList.remove('hidden');
    } catch (fetchError) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = fetchError.message || 'Unable to load verified receipt from PayMongo.';
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const imageModal = document.getElementById('adminReceiptModal');
    if (imageModal) {
        imageModal.addEventListener('click', function(e) {
            if (e.target === imageModal) {
                closeAdminReceiptModal();
            }
        });
    }

    const paymongoModal = document.getElementById('paymongoReceiptModal');
    if (paymongoModal) {
        paymongoModal.addEventListener('click', function(e) {
            if (e.target === paymongoModal) {
                closePaymongoReceiptModal();
            }
        });
    }
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAdminReceiptModal();
            closePaymongoReceiptModal();
        }
    });
});
</script>
@endsection
