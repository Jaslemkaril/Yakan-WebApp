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
                    <div class="space-y-4">
                        @foreach($cartItems as $index => $item)
                            @php
                                $product = $item->product;
                            @endphp
                            
                            @if($product)
                                <div class="cart-item" data-item-id="{{ $item->id }}">
                                    <div class="flex gap-4">
                                        <!-- Product Image -->
                                        <div class="w-28 h-28 flex-shrink-0 product-image-wrapper">
                                            @if($product->image)
                                                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
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
                                                    <div class="text-2xl font-bold text-maroon-600">₱{{ number_format($product->price * $item->quantity, 2) }}</div>
                                                    <div class="text-sm text-gray-500">₱{{ number_format($product->price, 2) }} each</div>
                                                </div>

                                                <div class="quantity-control">
                                                    <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">−</button>
                                                    <input type="number" value="{{ $item->quantity }}" min="1" class="quantity-input" readonly>
                                                    <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">+</button>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <span class="stock-badge">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    In Stock ({{ $product->stock ?? 0 }} available)
                                                </span>
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
                            $total = $subtotal;
                        @endphp

                        <div class="space-y-0 mb-6 pb-6 border-b-2 border-gray-200">
                            <div class="summary-row">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold text-green-600">Free</span>
                            </div>
                            <div class="summary-row total">
                                <span class="label">Total</span>
                                <span class="value">₱{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('cart.checkout') }}" class="btn-primary w-full text-lg py-4 justify-center mb-4">
                            <span>Proceed to Checkout</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>

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

    <script>
        function updateQuantity(itemId, newQuantity) {
            if (newQuantity < 1) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ quantity: newQuantity })
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
            });
        }

        function removeItem(itemId) {
            if (!confirm('Remove this item?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
            });
        }
    </script>
@endsection
