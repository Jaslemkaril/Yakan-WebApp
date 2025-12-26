@extends('layouts.app')

@push('styles')
<style>
    .payment-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .payment-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .payment-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
    }

    .payment-card:hover {
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.15);
        border-color: #800000;
    }

    .payment-method-option {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method-option:hover {
        border-color: #800000;
        background-color: #fef2f2;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.1);
    }

    .payment-method-option input[type="radio"]:checked + .payment-method-content {
        color: #800000;
    }

    .payment-method-option input[type="radio"]:checked ~ .check-icon {
        color: #800000;
    }

    .payment-method-option.selected {
        border-color: #800000;
        background-color: #fef2f2;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.15);
    }

    .payment-btn {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(128, 0, 0, 0.2);
    }

    .payment-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 0, 0.3);
    }

    .payment-btn:active {
        transform: translateY(0);
    }

    .order-summary-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .order-summary-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .order-summary-icon svg {
        width: 24px;
        height: 24px;
        color: white;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .summary-row.total {
        border-top: 2px solid #800000;
        padding-top: 1rem;
        margin-top: 1rem;
        font-size: 1.125rem;
    }

    .summary-row.total .label {
        font-weight: 700;
        color: #1f2937;
    }

    .summary-row.total .value {
        font-weight: 700;
        color: #800000;
        font-size: 1.5rem;
    }

    .delivery-badge {
        display: inline-block;
        background: linear-gradient(135deg, #fef2f2 0%, #fde8e8 100%);
        color: #800000;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 700;
        border: 2px solid #800000;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #800000;
        font-weight: 700;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .back-link:hover {
        color: #600000;
        transform: translateX(-4px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="payment-hero py-12 relative rounded-2xl mb-8">
            <div class="relative z-10 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-2 flex items-center justify-center gap-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Complete Payment
                </h1>
                <p class="text-lg text-gray-100">Order #{{ $order->id }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Order Summary -->
        <div class="payment-card p-8 mb-8">
            <div class="order-summary-header">
                <div class="order-summary-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Order Summary</h2>
            </div>

            <div class="space-y-4">
                <!-- Customer Info -->
                <div class="summary-row">
                    <span class="text-gray-600 font-medium">Customer</span>
                    <span class="font-semibold text-gray-900">{{ $order->user->name ?? auth()->user()->name ?? 'N/A' }}</span>
                </div>

                @php
                    $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                @endphp

                <!-- Delivery Option -->
                <div class="summary-row">
                    <span class="text-gray-600 font-medium">Delivery Option</span>
                    <span class="delivery-badge">
                        {{ $deliveryType === 'pickup' ? '🏪 Store Pickup' : '🚚 Delivery' }}
                    </span>
                </div>

                @if($order->delivery_address)
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg p-4 border-2 border-red-200 mt-4">
                        <span class="text-gray-700 text-sm font-bold block mb-2">📍 Delivery Address</span>
                        <span class="font-medium text-gray-900 text-sm whitespace-pre-line block">{{ $order->delivery_address }}</span>
                    </div>
                @endif

                <div class="border-t-2 border-gray-200 pt-4 mt-4 space-y-3">
                    <!-- Customized Product Details -->
                    @if(method_exists($order, 'isFabricOrder') && $order->isFabricOrder())
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Product</span>
                            <span class="font-semibold text-gray-900">Custom Fabric Order</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Fabric Type</span>
                            <span class="font-semibold text-gray-900">{{ $order->fabric_type ?? 'N/A' }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Quantity</span>
                            <span class="font-semibold text-gray-900">{{ $order->formatted_fabric_quantity ?? ($order->fabric_quantity_meters . ' m') }}</span>
                        </div>
                        @if(!empty($order->intended_use_label))
                            <div class="summary-row">
                                <span class="text-gray-600 font-medium">Intended Use</span>
                                <span class="font-semibold text-gray-900">{{ $order->intended_use_label }}</span>
                            </div>
                        @endif
                    @else
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Product</span>
                            <span class="font-semibold text-gray-900">{{ $order->product->name ?? 'Custom Product' }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Quantity</span>
                            <span class="font-semibold text-gray-900">{{ $order->quantity }}</span>
                        </div>
                    @endif

                    <!-- Total Amount -->
                    <div class="summary-row total">
                        <span class="label">Total Amount</span>
                        <span class="value">₱{{ number_format($order->final_price, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Selection -->
        <form method="POST" action="{{ route('custom_orders.payment.process', $order->id) }}" class="payment-card p-8 mb-8">
            @csrf
            <div class="order-summary-header mb-8">
                <div class="order-summary-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Select Payment Method</h2>
            </div>

            <div class="space-y-4 mb-8">
                <!-- GCash Payment -->
                <label class="payment-method-option {{ old('payment_method') === 'online_banking' ? 'selected' : '' }}">
                    <input type="radio" name="payment_method" value="online_banking" {{ old('payment_method') === 'online_banking' ? 'checked' : '' }} class="w-5 h-5 accent-maroon-600">
                    <div class="payment-method-content flex-1 ml-4">
                        <div class="font-bold text-lg">💳 GCash</div>
                        <p class="text-sm text-gray-600 mt-1">Pay using GCash e-wallet - Fast & Secure</p>
                    </div>
                    <svg class="check-icon w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </label>

                <!-- Bank Transfer -->
                <label class="payment-method-option {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}">
                    <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }} class="w-5 h-5 accent-maroon-600">
                    <div class="payment-method-content flex-1 ml-4">
                        <div class="font-bold text-lg">🏦 Bank Transfer</div>
                        <p class="text-sm text-gray-600 mt-1">Direct transfer to our bank account - Secure & Reliable</p>
                    </div>
                    <svg class="check-icon w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </label>
            </div>

            @error('payment_method')
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ $message }}</p>
                </div>
            @enderror

            <button type="submit" class="payment-btn w-full text-lg py-4 justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Continue to Payment
            </button>
        </form>

        <!-- Back Link -->
        <div class="text-center">
            <a href="{{ route('custom_orders.show', $order) }}" class="back-link text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Order Details
            </a>
        </div>

    </div>
</div>
@endsection
