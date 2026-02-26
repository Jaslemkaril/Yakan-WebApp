@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Confirm Payment</h1>
            <p class="text-gray-600">Order #{{ $order->id }} • {{ ucfirst($order->payment_method) }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Order Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Product</span>
                    <span class="font-medium">{{ $order->product->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Quantity</span>
                    <span class="font-medium">{{ $order->quantity }}</span>
                </div>
                <div class="border-t pt-2 mt-2">
                    <div class="flex justify-between">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-2xl font-bold text-red-600">₱{{ number_format($order->final_price, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Instructions</h2>

            @switch($order->payment_method)
                @case('online_banking')
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Please transfer the amount to our bank account:</p>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-1">
                                <p class="font-mono text-sm">Bank: <span class="font-semibold">Sample Bank</span></p>
                                <p class="font-mono text-sm">Account Name: <span class="font-semibold">Yakan E-commerce</span></p>
                                <p class="font-mono text-sm">Account Number: <span class="font-semibold">1234567890</span></p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">After transferring, please upload the receipt or enter the transaction reference below.</p>
                    </div>
                    @break

                @case('gcash')
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Send payment to our GCash account:</p>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-1">
                                <p class="font-mono text-sm">GCash Number: <span class="font-semibold">09123456789</span></p>
                                <p class="font-mono text-sm">Account Name: <span class="font-semibold">Yakan E-commerce</span></p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">After sending, please upload the screenshot or enter the reference number.</p>
                    </div>
                    @break

                @case('bank_transfer')
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Transfer to our bank account:</p>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-1">
                                <p class="font-mono text-sm">Bank: <span class="font-semibold">Sample Bank</span></p>
                                <p class="font-mono text-sm">Account Name: <span class="font-semibold">Yakan E-commerce</span></p>
                                <p class="font-mono text-sm">Account Number: <span class="font-semibold">1234567890</span></p>
                                <p class="font-mono text-sm">Branch: <span class="font-semibold">Main Branch</span></p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">After transferring, please upload the receipt or enter the transaction reference.</p>
                    </div>
                    @break
            @endswitch
        </div>

        <!-- Payment Confirmation Form -->
        <form method="POST" action="{{ route('custom_orders.confirm_payment', $order->id) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @csrf

            <h2 class="text-lg font-semibold text-gray-900 mb-4">Confirm Payment</h2>

            <div class="space-y-4">
                <!-- Transaction ID/Reference -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference / ID</label>
                    <input type="text" name="transaction_id" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                           placeholder="Enter transaction ID or reference number">
                    @error('transaction_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Receipt Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Receipt (optional)</label>
                    <input type="file" name="receipt" accept="image/*,.pdf,image/heic,image/heif"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="mt-1 text-xs text-gray-500">Upload screenshot or photo of receipt (JPG, PNG, PDF, WebP — max 10MB)</p>
                    @error('receipt')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (optional)</label>
                    <textarea name="payment_notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Any additional information about your payment..."></textarea>
                </div>
            </div>

            <button type="submit" class="w-full mt-6 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition-colors duration-200">
                Submit Payment Confirmation
            </button>
        </form>

        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="{{ route('custom_orders.payment', $order) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                ← Back to Payment Method
            </a>
        </div>

    </div>
</div>
@endsection
