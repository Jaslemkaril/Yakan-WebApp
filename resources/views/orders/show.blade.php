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
                        @php
                            $hasBankReceipt = !empty($order->bank_receipt) && $order->payment_method === 'bank_transfer';
                            $hasProof = $hasBankReceipt || (!empty($order->payment_proof_path) && $order->payment_method === 'bank_transfer');
                            $effectivePaymentStatus = ($hasProof && $order->payment_status === 'pending')
                                ? 'verification_pending'
                                : $order->payment_status;
                            $paymentStatusLabel = [
                                'paid' => 'Paid ✓',
                                'verified' => 'Paid ✓',
                                'verification_pending' => 'Awaiting Verification',
                                'pending' => 'Pending',
                                'failed' => 'Failed',
                            ][$effectivePaymentStatus] ?? ucfirst(str_replace('_', ' ', $effectivePaymentStatus));
                        @endphp
                        <p class="text-3xl font-bold text-gray-900">{{ $paymentStatusLabel }}</p>
                    </div>
                    <div class="w-14 h-14 bg-[#800000] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Action Buttons -->
        @if(in_array($order->status, ['pending', 'pending_confirmation']))
        <div class="mb-8 bg-white rounded-xl shadow-md p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Cancel Order</h3>
            <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation <span class="text-red-600">*</span></label>
                    <select name="cancel_reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        <option value="">-- Select a reason --</option>
                        <option value="Changed my mind">Changed my mind</option>
                        <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                        <option value="Ordered by mistake">Ordered by mistake</option>
                        <option value="Delivery takes too long">Delivery takes too long</option>
                        <option value="Duplicate order">Duplicate order</option>
                        <option value="Want to change items">Want to change items</option>
                        <option value="Financial reasons">Financial reasons</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Order
                </button>
            </form>
        </div>
        @endif

        @if($order->status === 'delivered')
        <div class="mb-8" id="confirm-receipt-card">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-md border-2 border-green-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 bg-green-600 rounded-lg flex items-center justify-center shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Confirm Receipt</h3>
                            <p class="text-xs text-green-700 font-medium">Your order has been marked as delivered</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-5">Have you received all your items in good condition? Confirming lets the seller know your order is complete.</p>
                    <button id="confirm-received-btn"
                        onclick="confirmOrderReceived({{ $order->id }}, '{{ csrf_token() }}')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 active:scale-95 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Yes, I Received My Order
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($order->status === 'completed')
        <div class="mb-8">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl shadow-md border-2 border-emerald-400 p-6 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-emerald-800">Order Completed!</h3>
                    <p class="text-sm text-emerald-700">You have confirmed receipt of this order. Thank you for shopping with Yakan!</p>
                </div>
            </div>
        </div>
        @endif

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
                                    @php
                                        // Prefer accessor that handles full URLs, storage, or uploads
                                        $imageUrl = $item->product?->image_url ?? '';
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name ?? 'Product' }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                            <svg class="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
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

                    @if($order->estimated_delivery_date)
                        <div class="pt-4 pb-4 border-t border-gray-200">
                            <div class="flex items-center gap-3 p-4 rounded-xl"
                                 style="background: linear-gradient(135deg, #fff5f5 0%, #ffe4e4 100%); border: 1px solid #f5c6c6;">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#800000;">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Estimated Delivery Date</p>
                                    <p class="text-lg font-bold" style="color:#800000;">
                                        {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('F d, Y') }}
                                    </p>
                                    @if(!in_array($order->status, ['delivered','completed','cancelled']))
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->isPast() ? 'Expected soon' : \Carbon\Carbon::parse($order->estimated_delivery_date)->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

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
                            @php
                                $statusChip = match($effectivePaymentStatus) {
                                    'paid', 'verified' => ['✓ Paid', 'bg-green-100 text-green-700'],
                                    'verification_pending' => ['⏳ Verification Pending', 'bg-yellow-100 text-yellow-800'],
                                    'pending' => ['⏳ Pending', 'bg-yellow-100 text-yellow-800'],
                                    default => ['✕ Failed', 'bg-red-100 text-red-700'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusChip[1] }}">
                                {{ $statusChip[0] }}
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
                        @elseif($order->payment_method === 'bank_transfer')
                            @php
                                $hasReceipt = !empty($order->bank_receipt) || !empty($order->payment_proof_path);
                                $receiptPath = $order->bank_receipt ?: $order->payment_proof_path;
                                // Support both Cloudinary URLs and local storage paths
                                $receiptUrl = $receiptPath
                                    ? ((str_starts_with($receiptPath, 'http://') || str_starts_with($receiptPath, 'https://'))
                                        ? $receiptPath
                                        : \Illuminate\Support\Facades\Storage::url($receiptPath))
                                    : null;
                                $isCleared = in_array($effectivePaymentStatus, ['paid', 'verified', 'verification_pending'], true);
                            @endphp

                            @if($hasReceipt || $isCleared)
                                <!-- Receipt uploaded or already paid/verified -->
                                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-4 text-center">
                                    <div class="flex items-center justify-center gap-2 mb-2">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="font-bold text-green-800">Receipt on File</p>
                                    </div>
                                    <p class="text-sm text-green-700 mb-3">Awaiting verification. You can view your uploaded receipt below.</p>
                                    @if($hasReceipt && $receiptUrl)
                                        <a href="{{ $receiptUrl }}" target="_blank" class="inline-block px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all text-sm">
                                            👁️ View Receipt
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- Need to upload receipt -->
                                <a href="{{ route('payment.bank', $order->id) }}" class="block w-full text-center px-4 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                                    📄 Upload Receipt
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- ===== REVIEW SECTION ===== --}}
        @if(in_array($order->status, ['delivered', 'completed']) && auth()->check())
        <div class="mt-10" id="review-section">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                Rate Your Purchase
            </h2>

            <div class="space-y-6">
                @foreach($order->orderItems as $item)
                    @php
                        $existingReview = \App\Models\Review::where('order_item_id', $item->id)
                            ->where('user_id', auth()->id())
                            ->first();
                    @endphp
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        {{-- Item header --}}
                        <div class="flex items-center gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                @if($item->product?->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $item->product->name ?? 'Product' }}</p>
                                <p class="text-sm text-gray-500">Qty: {{ $item->quantity }} &bull; ₱{{ number_format($item->price, 2) }} each</p>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            @if($existingReview)
                                {{-- Show existing review --}}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-1">
                                        @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-6 h-6 {{ $s <= $existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600 font-semibold">{{ $existingReview->rating }}/5</span>
                                        <span class="ml-auto text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">✓ Review Submitted</span>
                                    </div>
                                    @if($existingReview->title)
                                        <p class="font-semibold text-gray-800">"{{ $existingReview->title }}"</p>
                                    @endif
                                    @if($existingReview->comment)
                                        <p class="text-gray-700 text-sm">{{ $existingReview->comment }}</p>
                                    @endif
                                    @if($existingReview->review_images && count($existingReview->review_images) > 0)
                                        <div class="flex gap-2 flex-wrap mt-2">
                                            @foreach($existingReview->review_images as $imgUrl)
                                                <img src="{{ $imgUrl }}" alt="Review photo" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                            @endforeach
                                        </div>
                                    @endif
                                    <p class="text-xs text-gray-400">Submitted {{ $existingReview->created_at->format('M d, Y') }}</p>
                                </div>
                            @else
                                {{-- Review form --}}
                                <form action="{{ route('reviews.store.order-item', $item) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="space-y-4">
                                        {{-- Star Rating --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                                            <div class="flex gap-1" id="stars-{{ $item->id }}">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <button type="button" data-value="{{ $s }}" data-group="{{ $item->id }}"
                                                        class="star-btn w-9 h-9 text-gray-300 hover:text-yellow-400 transition-colors"
                                                        onclick="setRating({{ $item->id }}, {{ $s }})">
                                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    </button>
                                                @endfor
                                                <input type="hidden" name="rating" id="rating-{{ $item->id }}" value="" required>
                                            </div>
                                        </div>

                                        {{-- Title --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Review Title</label>
                                            <input type="text" name="title" maxlength="255" placeholder="Summarize your experience" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                        </div>

                                        {{-- Comment --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Your Review</label>
                                            <textarea name="comment" rows="3" maxlength="1000" placeholder="Share your experience with this product..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent resize-none"></textarea>
                                        </div>

                                        {{-- Photo Upload --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Photos (optional, up to 5)</label>
                                            <input type="file" name="images[]" accept="image/*" multiple
                                                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#800000] file:text-white hover:file:bg-[#600000] cursor-pointer"
                                                onchange="previewImages(this, 'preview-{{ $item->id }}')">
                                            <div id="preview-{{ $item->id }}" class="flex flex-wrap gap-2 mt-2"></div>
                                        </div>

                                        <button type="submit" class="bg-[#800000] hover:bg-[#600000] text-white font-bold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow">
                                            Submit Review
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function setRating(groupId, value) {
    document.getElementById('rating-' + groupId).value = value;
    const stars = document.querySelectorAll('#stars-' + groupId + ' .star-btn');
    stars.forEach(function(btn) {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.toggle('text-yellow-400', v <= value);
        btn.classList.toggle('text-gray-300', v > value);
    });
}

function previewImages(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    const files = Array.from(input.files).slice(0, 5);
    files.forEach(function(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endsection

@push('scripts')
<script>
async function confirmOrderReceived(orderId, csrf) {
    const btn = document.getElementById('confirm-received-btn');
    if (!btn) return;

    const userConfirmed = confirm('Please confirm: Have you already received this order in good condition?');
    if (!userConfirmed) {
        return;
    }

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Confirming…`;

    try {
        const res = await fetch(`/orders/${orderId}/confirm-received`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });

        const data = await res.json();

        if (data.success) {
            // Replace the confirm card with a success banner
            const card = document.getElementById('confirm-receipt-card');
            if (card) {
                card.innerHTML = `
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl shadow-md border-2 border-emerald-400 p-6 flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-emerald-800">✅ Order Completed!</h3>
                            <p class="text-sm text-emerald-700">You have confirmed receipt of this order. Thank you for shopping with Yakan!</p>
                        </div>
                    </div>`;
            }
            // Also update the Order Status card
            const statusEl = document.querySelector('.text-3xl.font-bold.text-gray-900');
            if (statusEl && statusEl.closest('.border-l-4')) {
                statusEl.textContent = 'Completed';
            }
        } else {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Yes, I Received My Order`;
            alert(data.message || 'Could not confirm order. Please try again.');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Yes, I Received My Order`;
        alert('Network error. Please try again.');
    }
}
</script>
@endpush
