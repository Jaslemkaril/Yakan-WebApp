@extends('layouts.app')

@section('title', 'Shopping Cart - Yakan')

@push('styles')
<style>
    .cart-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .cart-item {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
        position: relative;
        overflow: hidden;
    }

    .cart-item.selected {
        border-color: #800000;
        background: #fef2f2;
    }

    .cart-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #800000, #c2410c);
    }

    .cart-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(128, 0, 0, 0.15);
        border-color: #800000;
    }

    .cart-checkbox {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        accent-color: #800000;
    }

    .cart-checkbox:checked {
        border-color: #800000;
    }

    .select-all-container {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 2px solid #f3f4f6;
    }

    .product-image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    }

    .product-image-wrapper img {
        transition: transform 0.3s ease;
        object-fit: cover;
    }

    .product-image-wrapper:hover img {
        transform: scale(1.05);
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 10px;
        padding: 0.5rem;
        border: 2px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .quantity-control:hover {
        border-color: #800000;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.1);
    }

    .quantity-btn {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: none;
        background: white;
        color: #374151;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .quantity-btn:hover {
        background: #800000;
        color: white;
        transform: scale(1.08);
        box-shadow: 0 4px 8px rgba(128, 0, 0, 0.2);
    }

    .quantity-btn:active {
        transform: scale(0.95);
    }

    .quantity-input {
        width: 50px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 16px;
        color: #1f2937;
    }

    .btn-primary {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        padding: 14px 28px;
        border-radius: 10px;
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

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.35);
    }

    .btn-primary:active {
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: white;
        color: #800000;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 700;
        border: 2px solid #800000;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(128, 0, 0, 0.1);
    }

    .btn-secondary:hover {
        background: #800000;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(128, 0, 0, 0.25);
    }

    .btn-secondary:active {
        transform: translateY(-1px);
    }

    .empty-cart {
        background: white;
        border-radius: 16px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 2px solid #f3f4f6;
    }

    .empty-cart-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        box-shadow: 0 8px 20px rgba(251, 191, 36, 0.2);
    }

    .remove-btn {
        padding: 8px 12px;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 14px;
    }

    .remove-btn:hover {
        background: #fecaca;
        transform: scale(1.05);
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #ecfdf5;
        color: #047857;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .category-badge {
        display: inline-block;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
    }

    .order-summary-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 2px solid #f3f4f6;
        position: sticky;
        top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 15px;
    }

    .summary-row.total {
        border-top: 2px solid #e5e7eb;
        padding-top: 16px;
        margin-top: 16px;
        font-size: 18px;
    }

    .summary-row.total .label {
        font-weight: 700;
        color: #1f2937;
    }

    .summary-row.total .value {
        font-weight: 700;
        color: #800000;
        font-size: 24px;
    }
    
    /* Modal animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="cart-hero py-12 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl lg:text-5xl font-bold text-white mb-2 flex items-center gap-3">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Shopping Cart
            </h1>
            <p class="text-lg text-gray-100">Review your items and proceed to checkout</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @php
            $hasItems = $cartItems && (is_countable($cartItems) ? count($cartItems) > 0 : $cartItems->count() > 0);
        @endphp
        
        @if($hasItems)
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <main class="lg:w-2/3">
                    <!-- Select All -->
                    <div class="select-all-container">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="selectAll" class="cart-checkbox" checked onchange="toggleSelectAll(this)">
                            <span class="font-semibold text-gray-900">Select All</span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        @foreach($cartItems as $index => $item)
                            @php
                                $product = $item->product;
                            @endphp
                            
                            @if($product)
                                <div class="cart-item selected" data-item-id="{{ $item->id }}">
                                    <div class="flex gap-4">
                                        <!-- Checkbox -->
                                        <div class="flex items-start pt-1">
                                            <input type="checkbox" class="item-checkbox cart-checkbox" data-item-id="{{ $item->id }}" data-price="{{ $product->price * $item->quantity }}" checked onchange="updateSelection()">
                                        </div>

                                        <!-- Product Image -->
                                        <div class="w-28 h-28 flex-shrink-0 product-image-wrapper">
                                            @if($product->image)
                                                <img src="{{ $product->image_src }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-3">
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $product->name }}</h3>
                                                    @if($product->category)
                                                        <span class="category-badge">
                                                            {{ $product->category->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <button class="remove-btn" onclick="removeItem({{ $item->id }})">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Remove
                                                </button>
                                            </div>

                                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $product->description ?? 'Premium quality product' }}</p>

                                            <div class="flex items-center justify-between flex-wrap gap-4">
                                                <div>
                                                    <div class="text-2xl font-bold text-maroon-600 item-subtotal">₱{{ number_format($product->price * $item->quantity, 2) }}</div>
                                                    <div class="text-sm text-gray-500">₱{{ number_format($product->price, 2) }} each</div>
                                                </div>

                                                <div class="quantity-control">
                                                    <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, parseInt(this.parentElement.querySelector('.quantity-input').value) - 1)">−</button>
                                                    <input type="number" value="{{ $item->quantity }}" min="1" class="quantity-input" data-item-id="{{ $item->id }}" readonly>
                                                    <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, parseInt(this.parentElement.querySelector('.quantity-input').value) + 1)">+</button>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                @php
                                                    // Check inventory table first (correct source), fallback to product.stock
                                                    $stockLevel = $product->inventory ? $product->inventory->quantity : ($product->stock ?? 0);
                                                @endphp
                                                
                                                @if($stockLevel > 10)
                                                    <span class="stock-badge">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        In Stock ({{ $stockLevel }} available)
                                                    </span>
                                                @elseif($stockLevel > 0 && $stockLevel <= 10)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-300">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Low Stock ({{ $stockLevel }} left)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-300">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Out of Stock
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                                    <p class="text-yellow-800">
                                        <strong>Warning:</strong> Cart item #{{ $item->id }} has no product (Product ID: {{ $item->product_id }})
                                        <button onclick="removeItem({{ $item->id }})" class="ml-2 text-yellow-600 hover:text-yellow-800 underline">Remove</button>
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('products.index') }}" class="btn-secondary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </main>

                <!-- Order Summary -->
                <aside class="lg:w-1/3">
                    <div class="order-summary-card">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-maroon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Order Summary
                        </h3>
                        
                        @php
                            $subtotal = 0;
                            foreach($cartItems as $item) {
                                if($item->product) {
                                    $subtotal += $item->product->price * $item->quantity;
                                }
                            }
                            $itemCount = count($cartItems);
                            $shippingFee = (float) ($shippingFee ?? 0);
                            $total = $subtotal + ($itemCount > 0 ? $shippingFee : 0);
                        @endphp

                        <div class="space-y-0 mb-6 pb-6 border-b-2 border-gray-200">
                            <div class="summary-row">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold text-gray-900" id="subtotalDisplay">₱{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold {{ $shippingFee > 0 ? 'text-gray-900' : 'text-green-600' }}" id="shippingDisplay">{{ $shippingFee > 0 ? '₱' . number_format($shippingFee, 2) : 'Free' }}</span>
                            </div>
                            <div class="summary-row total">
                                <span class="label">Total</span>
                                <span class="value" id="totalDisplay">₱{{ number_format($total, 2) }}</span>
                            </div>
                            <input type="hidden" id="baseShippingFee" value="{{ $shippingFee }}">
                        </div>

                        <button onclick="proceedToCheckout()" class="btn-primary w-full text-lg py-4 justify-center mb-4">
                            <span id="checkoutText">Proceed to Checkout (<span id="selectedCount">{{ $itemCount }}</span> items)</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </button>

                        <div class="text-center text-sm text-gray-500 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Secure Checkout
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                
                <h3 class="text-3xl font-bold text-gray-900 mb-3">Your cart is empty</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto text-lg leading-relaxed">
                    Looks like you haven't added any items yet. Start shopping to fill your cart with amazing products!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-3">
                        <span>Start Shopping</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('custom_orders.index') }}" class="btn-secondary text-lg px-8 py-3">
                        <span>Custom Orders</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <!-- Backdrop -->
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" onclick="closeConfirmationModal()"></div>
            
            <!-- Modal -->
            <div class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12">
                            <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2" id="modalTitle">
                                Remove Item?
                            </h3>
                            <p class="text-gray-600" id="modalMessage">
                                Are you sure you want to remove this item from your cart?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                    <button onclick="confirmAction()" 
                            class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-red-600 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        Remove
                    </button>
                    <button onclick="closeConfirmationModal()" 
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let confirmCallback = null;

        function showConfirmationModal(title, message, callback) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            confirmCallback = callback;
            const modal = document.getElementById('confirmationModal');
            modal.classList.remove('hidden');
            modal.classList.add('animate-fadeIn');
        }

        function closeConfirmationModal() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('hidden');
            confirmCallback = null;
        }

        function confirmAction() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmationModal();
        }

        function toggleSelectAll(checkbox) {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            itemCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
                const cartItem = cb.closest('.cart-item');
                if (checkbox.checked) {
                    cartItem.classList.add('selected');
                } else {
                    cartItem.classList.remove('selected');
                }
            });
            updateSelection();
        }

        function updateSelection() {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            const baseShippingFee = parseFloat(document.getElementById('baseShippingFee')?.value || '0');
            
            // Update select all checkbox
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            
            // Update cart item styling
            itemCheckboxes.forEach(cb => {
                const cartItem = cb.closest('.cart-item');
                if (cb.checked) {
                    cartItem.classList.add('selected');
                } else {
                    cartItem.classList.remove('selected');
                }
            });
            
            // Calculate totals for selected items
            let selectedSubtotal = 0;
            let selectedCount = 0;
            
            itemCheckboxes.forEach(cb => {
                if (cb.checked) {
                    selectedSubtotal += parseFloat(cb.dataset.price);
                    selectedCount++;
                }
            });

            const applicableShipping = selectedCount > 0 ? baseShippingFee : 0;
            const finalTotal = selectedSubtotal + applicableShipping;
            const shippingDisplay = document.getElementById('shippingDisplay');
            
            // Update displays
            document.getElementById('subtotalDisplay').textContent = '₱' + selectedSubtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('totalDisplay').textContent = '₱' + finalTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            if (shippingDisplay) {
                shippingDisplay.textContent = applicableShipping > 0
                    ? '₱' + applicableShipping.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                    : 'Free';
                shippingDisplay.classList.toggle('text-green-600', applicableShipping <= 0);
                shippingDisplay.classList.toggle('text-gray-900', applicableShipping > 0);
            }
            document.getElementById('selectedCount').textContent = selectedCount;
        }

        function proceedToCheckout() {
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
            
            if (selectedItems.length === 0) {
                alert('Please select items to checkout');
                return;
            }
            
            // Get selected item IDs
            const selectedIds = selectedItems.map(cb => cb.dataset.itemId);
            
            // Create form to POST selected items
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("cart.checkout") }}';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // Add auth_token for Railway (cookies are stripped by edge proxy)
            const authToken = localStorage.getItem('yakan_auth_token');
            if (authToken) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'auth_token';
                tokenInput.value = authToken;
                form.appendChild(tokenInput);
            }
            
            // Add selected item IDs
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_items[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        function updateQuantity(itemId, newQuantity) {
            if (newQuantity < 1) return;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const authToken = localStorage.getItem('yakan_auth_token') 
                || sessionStorage.getItem('yakan_auth_token')
                || sessionStorage.getItem('auth_token');
            
            // Build request body with auth_token for Railway
            const requestBody = { 
                quantity: newQuantity,
                auth_token: authToken // Always include in body
            };
            
            fetch(`/cart/update/${itemId}${authToken ? '?auth_token=' + encodeURIComponent(authToken) : ''}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Auth-Token': authToken || '', // Also send in header
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include', // Include cookies for authentication
                body: JSON.stringify(requestBody)
            })
            .then(r => {
                console.log('=== RESPONSE DEBUG ===');
                console.log('Status:', r.status);
                console.log('Status Text:', r.statusText);
                console.log('OK:', r.ok);
                console.log('====================');
                
                if (r.status === 401) {
                    // Unauthorized - redirect to login
                    console.error('🔴 401 Unauthorized - redirecting to login');
                    window.location.href = '/login-user';
                    throw new Error('Unauthorized');
                }
                if (!r.ok) {
                    console.error('❌ Request failed:', r.status, r.statusText);
                    throw new Error('Update failed with status: ' + r.status);
                }
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    // DON'T reload - update the page dynamically to preserve session
                    
                    // Find the cart item element
                    const itemElement = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
                    
                    if (itemElement && data.new_quantity !== undefined) {
                        // 1. Update the quantity input
                        const quantityInput = itemElement.querySelector('.quantity-input[data-item-id="' + itemId + '"]');
                        if (quantityInput) {
                            quantityInput.value = data.new_quantity;
                        }
                        
                        // 2. Update the item subtotal display
                        if (data.item_subtotal !== undefined) {
                            const subtotalElement = itemElement.querySelector('.item-subtotal');
                            if (subtotalElement) {
                                subtotalElement.textContent = '₱' + data.item_subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            }
                        }
                        
                        // 3. Update the checkbox data-price (CRITICAL - used by updateSelection())
                        const checkbox = itemElement.querySelector('.item-checkbox[data-item-id="' + itemId + '"]');
                        if (checkbox && data.item_subtotal !== undefined) {
                            checkbox.setAttribute('data-price', data.item_subtotal);
                        }
                    }
                    
                    // 4. Recalculate Order Summary based on CHECKED items only
                    updateSelection();
                    
                    // Show success message briefly
                    const msg = document.createElement('div');
                    msg.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    msg.textContent = '✓ Cart updated';
                    document.body.appendChild(msg);
                    setTimeout(() => msg.remove(), 2000);
                } else if (data.redirect) {
                    alert('⚠️ Redirecting: ' + data.message);
                    window.location.href = data.redirect;
                } else {
                    console.error('❌ Update failed:', data.message);
                    alert('❌ Update failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('=== ERROR CAUGHT ===');
                console.error('Error:', error);
                console.error('Message:', error.message);
                console.error('Stack:', error.stack);
                console.error('==================');
                
                if (error.message !== 'Unauthorized') {
                    alert('❌ ERROR: ' + error.message + '\n\nCheck console for details.');
                }
            });
        }

        function removeItem(itemId) {
            showConfirmationModal(
                'Remove Item?',
                'Are you sure you want to remove this item from your cart?',
                () => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const authToken = localStorage.getItem('yakan_auth_token') || sessionStorage.getItem('yakan_auth_token');
                    
                    // Build URL with auth_token query parameter for Railway
                    let url = `/cart/remove/${itemId}`;
                    if (authToken) {
                        url += `?auth_token=${encodeURIComponent(authToken)}`;
                    }
                    
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include' // Include cookies for authentication
                    })
                    .then(r => {
                        if (!r.ok) {
                            throw new Error('Remove failed');
                        }
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // DON'T reload - remove the item from DOM to preserve session
                            const itemElement = document.querySelector(`[data-item-id=\"${itemId}\"]`);
                            if (itemElement) {
                                itemElement.remove();
                            }
                            
                            // If cart is now empty, show empty cart message
                            const cartItems = document.querySelectorAll('.cart-item');
                            if (cartItems.length === 0) {
                                // Replace cart content with empty cart message (NO RELOAD - preserve session)
                                const cartContainer = document.querySelector('.flex.flex-col.lg\\:flex-row.gap-8');
                                if (cartContainer) {
                                    cartContainer.innerHTML = `
                                        <div class="empty-cart" style="width: 100%;">
                                            <div class="empty-cart-icon">
                                                <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                            </div>
                                            
                                            <h3 class="text-3xl font-bold text-gray-900 mb-3">Your cart is empty</h3>
                                            <p class="text-gray-600 mb-8 max-w-md mx-auto text-lg leading-relaxed">
                                                Looks like you haven't added any items yet. Start shopping to fill your cart with amazing products!
                                            </p>
                                            
                                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                                <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-3">
                                                    <span>Start Shopping</span>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                                    </svg>
                                                </a>
                                                
                                                <a href="{{ route('custom_orders.index') }}" class="btn-secondary text-lg px-8 py-3">
                                                    <span>Custom Orders</span>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                }
                                
                                // Update cart badge
                                const cartBadge = document.querySelector('.cart-badge');
                                if (cartBadge) {
                                    cartBadge.textContent = '0';
                                }
                            } else {
                                // Show success message
                                const msg = document.createElement('div');
                                msg.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                                msg.textContent = '✓ Item removed';
                                document.body.appendChild(msg);
                                setTimeout(() => msg.remove(), 2000);
                                
                                // Update selection to recalculate totals based on remaining checked items
                                updateSelection();
                            }
                        } else {
                            console.error('Remove failed:', data.message);
                            alert('Failed to remove item. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error removing item:', error);
                        alert('An error occurred. Please refresh the page and try again.');
                    });
                }
            );
        }
    </script>
@endsection
