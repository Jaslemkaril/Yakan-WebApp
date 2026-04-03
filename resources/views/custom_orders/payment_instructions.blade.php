@extends('layouts.app')

@section('content')
@php
    $paymentOrders = $paymentOrders ?? collect([$order]);
    $isBatchPayment = $isBatchPayment ?? ($paymentOrders->count() > 1);
    $batchOrderNumber = $order->batch_order_number ?? null;
    $displayAmount = (float) ($instructions['amount'] ?? $order->final_price ?? 0);

    $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
    $isDelivery = $deliveryType !== 'pickup';
    $summaryShippingFee = $isDelivery ? (float) ($order->shipping_fee ?? 0) : 0;

    $priceBreakdown = method_exists($order, 'getPriceBreakdown') ? $order->getPriceBreakdown() : null;
    $adminDeliveryFee = (float) (($priceBreakdown['breakdown']['delivery_fee'] ?? 0));

    $storedQuoted = (float) ($order->final_price ?? 0);

    if ($isBatchPayment) {
        $summaryQuotedPrice = $displayAmount;
        $summaryDisplayShipping = 0;
    } else {
        $summaryQuotedPrice = $storedQuoted;
        $summaryDisplayShipping = ($isDelivery && $adminDeliveryFee <= 0) ? $summaryShippingFee : 0;
    }

    $summaryTotal = $summaryQuotedPrice + $summaryDisplayShipping;
@endphp
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <span class="text-blue-600">💳</span>
                {{ $instructions['title'] ?? 'Payment Instructions' }}
            </h1>
            <p class="text-gray-600">
                @if($isBatchPayment)
                    Complete one payment for {{ $paymentOrders->count() }} custom items{{ $batchOrderNumber ? ' • ' . $batchOrderNumber : '' }}
                @else
                    Complete your payment for Custom Order {{ $order->display_ref }}
                @endif
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column: Instructions + Confirm Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Instructions Card (Maya / E-wallet style) -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ $instructions['title'] ?? 'Payment Instructions' }}
                            </h2>
                            <p class="text-sm text-gray-600">Follow these steps to complete your payment</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @if(!empty($instructions['steps']))
                            @foreach($instructions['steps'] as $index => $step)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-700 text-sm">{{ $step }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Payment Details Block (Maya or Bank) -->
                        @if(isset($instructions['gcash_number']))
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 mt-2 border-2 border-blue-200">
                                <div class="text-sm text-gray-600 mb-1">Maya Number</div>
                                <div class="text-2xl font-bold text-blue-600">{{ $instructions['gcash_number'] }}</div>
                                <div class="text-sm text-gray-700 mt-2 font-medium">Account Name: {{ $instructions['account_name'] }}</div>
                            </div>
                        @else
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 mt-2 border-2 border-green-200">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <div class="text-gray-600 mb-1">Bank</div>
                                        <div class="font-semibold text-gray-900">{{ $instructions['bank_name'] ?? 'Your Bank' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 mb-1">Account Name</div>
                                        <div class="font-semibold text-gray-900">{{ $instructions['account_name'] ?? 'Tuwas Yakan' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 mb-1">Account Number</div>
                                        <div class="font-mono font-semibold text-gray-900">{{ $instructions['account_number'] ?? '0000-0000-0000' }}</div>
                                    </div>
                                    @if(isset($instructions['branch']))
                                        <div>
                                            <div class="text-gray-600 mb-1">Branch</div>
                                            <div class="font-semibold text-gray-900">{{ $instructions['branch'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Amount & Reference -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div class="bg-white rounded-xl p-4 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">Amount to Pay</div>
                                <div class="text-3xl font-bold text-green-600">₱{{ number_format($instructions['amount'] ?? $order->final_price, 2) }}</div>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">Reference Code</div>
                                <div class="font-mono font-bold text-lg text-gray-900">{{ $instructions['reference_code'] ?? ('ORDER-' . $order->id) }}</div>
                            </div>
                        </div>

                        @if(!empty($instructions['notes']))
                            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 mt-4">
                                <p class="text-sm text-blue-800">{{ $instructions['notes'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Confirmation Form (styled similar to Maya page) -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200">
                        Confirm Your Payment
                    </h2>

                    <form method="POST" action="{{ route('custom_orders.payment.confirm.process', $order->id) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Transaction Reference -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference / ID</label>
                            <input type="text" name="transaction_id" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter transaction ID or reference number">
                            @error('transaction_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Receipt Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Receipt</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Upload a clear screenshot or PDF of your payment (max 5MB).</p>
                            @error('receipt')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Transfer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Transfer</label>
                            <input type="date" name="transfer_date" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   max="{{ now()->format('Y-m-d') }}">
                            @error('transfer_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (optional)</label>
                            <textarea name="payment_notes" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Any additional information about your payment..."></textarea>
                        </div>

                        <button type="submit"
                                class="w-full mt-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-center px-6 py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl font-semibold">
                            I have completed the payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">
                        Order Summary
                    </h2>

                    <div class="space-y-3 mb-6 text-sm">
                        @if($isBatchPayment)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Batch Items</span>
                                <span class="font-medium text-gray-900">{{ $paymentOrders->count() }}</span>
                            </div>
                            <div class="max-h-36 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1 bg-gray-50">
                                @foreach($paymentOrders as $item)
                                    <div class="flex justify-between text-xs text-gray-700">
                                        <span>Order #{{ $item->id }}</span>
                                        <span>₱{{ number_format($item->final_price ?? $item->estimated_price ?? 0, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div class="flex justify-between">
                            <span class="text-gray-600">Agreed Price</span>
                            <span class="font-medium text-gray-900">₱{{ number_format($summaryQuotedPrice, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping Fee</span>
                            <span class="font-medium text-gray-900">
                                @if($isBatchPayment)
                                    Included
                                @elseif($summaryDisplayShipping > 0)
                                    ₱{{ number_format($summaryDisplayShipping, 2) }}
                                @else
                                    ₱0.00
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Quantity</span>
                            <span class="font-medium text-gray-900">{{ $order->quantity }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount</span>
                            <span class="text-3xl font-bold text-blue-600">₱{{ number_format($summaryTotal, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-blue-600">₱{{ number_format($displayAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <div class="flex justify-between">
                            <span>Order ID:</span>
                            <span class="font-medium text-gray-900">{{ $order->display_ref }}</span>
                        </div>
                        @php
                            $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                        @endphp
                        <div class="flex justify-between">
                            <span>Delivery Option:</span>
                            <span class="font-medium text-gray-900">
                                {{ $deliveryType === 'pickup' ? 'Store Pickup' : 'Delivery' }}
                            </span>
                        </div>
                        @if($order->delivery_address)
                            <div class="flex flex-col text-xs">
                                <span class="text-gray-600">Delivery Address:</span>
                                <span class="mt-1 font-medium text-gray-900 whitespace-pre-line">{{ $order->delivery_address }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Payment Method:</span>
                            <span class="font-medium text-gray-900">
                                @if(in_array($order->payment_method, ['online_banking', 'gcash']))
                                    Maya
                                @elseif($order->payment_method === 'bank_transfer')
                                    Bank Transfer
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? '')) }}
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-lg text-xs font-medium">{{ ucfirst($order->status) }}</span>
                        </div>
                    </div>

                    <!-- Quick Reference Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border-2 border-blue-200">
                        <div class="text-xs font-semibold text-blue-900 mb-3 uppercase tracking-wide">Quick Reference</div>
                        <div class="space-y-3 text-sm">
                            @if(isset($instructions['gcash_number']))
                                <div>
                                    <span class="text-xs text-blue-700">Send to:</span>
                                    <div class="font-bold text-blue-900 text-lg">{{ $instructions['gcash_number'] }}</div>
                                </div>
                            @endif
                            <div>
                                <span class="text-xs text-blue-700">Amount:</span>
                                <div class="font-bold text-green-600 text-xl">₱{{ number_format($summaryTotal, 2) }}</div>
                            </div>
                            <div>
                                <span class="text-xs text-blue-700">Reference:</span>
                                <div class="font-bold text-blue-900 font-mono">{{ $instructions['reference_code'] ?? ('ORDER-' . $order->id) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Link -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <a href="{{ route('custom_orders.payment', $order) }}"
                           class="block w-full text-center text-gray-700 hover:text-blue-600 px-4 py-2 rounded-xl border-2 border-gray-200 hover:border-blue-600 transition-all duration-200 font-medium">
                            ← Back to Payment Method
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
