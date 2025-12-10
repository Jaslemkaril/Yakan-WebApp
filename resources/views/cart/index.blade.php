@extends('layouts.app')

@section('title', 'Shopping Cart - Yakan')

@push('styles')
<style>
    .cart-hero {
        background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
        position: relative;
        overflow: hidden;
    }

    .cart-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(251, 146, 60, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .cart-item {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
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
        background: linear-gradient(90deg, #dc2626, #ea580c);
    }

    .cart-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 0.25rem;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: white;
        color: #374151;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-btn:hover {
        background: #dc2626;
        color: white;
        transform: scale(1.1);
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 16px;
    }

    .order-summary {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 100px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .summary-row:last-child {
        border-bottom: none;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid #f3f4f6;
    }

    .promo-code {
        background: #f9fafb;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .empty-cart {
        background: white;
        border-radius: 20px;
        padding: 4rem 2rem;
        text-center: box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .cart-image {
        aspect-ratio: 1;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }

    .cart-image::before {
        content: '📦';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        opacity: 0.3;
    }

    .remove-btn {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cart-item:hover .remove-btn {
        opacity: 1;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="cart-hero py-12 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="animate-fade-in-up">
                <h1 class="text-4xl lg:text-5xl font-bold text-gradient mb-4">Shopping Cart</h1>
                <p class="text-xl text-gray-700">Review your items and proceed to checkout</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($cartItems && $cartItems->count() > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <main class="lg:w-2/3">
                    <div class="space-y-4">
                        @foreach($cartItems as $index => $item)
                            <div class="cart-item animate-fade-in-up" style="animation-delay: {{ $index * 0.1 }}s" data-item-id="{{ $item->id }}">
                                <div class="flex gap-4">
                                    <!-- Product Image -->
                                    <div class="cart-image w-24 h-24 flex-shrink-0">
                                        @if($item->product->image)
                                            <img src="{{ asset('uploads/products/' . $item->product->image) }}" alt="{{ $item->product->name }}" 
                                                 class="w-full h-full object-cover rounded-lg"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gray-100 rounded-lg flex items-center justify-center\'><div class=\'text-2xl\'>📦</div></div>';">
                                        @else
                                            <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
                                                <div class="text-2xl">📦</div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $item->product->name }}</h3>
                                                @if($item->product->category)
                                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                                                        {{ $item->product->category->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="flex items-center gap-3">
                                                <div class="text-right">
                                                    <div class="text-xl font-bold text-red-600">₱{{ number_format($item->product->price * $item->quantity) }}</div>
                                                    <div class="text-sm text-gray-500">₱{{ number_format($item->product->price) }} each</div>
                                                </div>
                                                
                                                <button class="remove-btn p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Product Description -->
                                        <p class="text-gray-600 text-sm mb-3">
                                            {{ Str::limit($item->product->description ?? 'Premium quality product', 100) }}
                                        </p>

                                        <!-- Quantity Control -->
                                        <div class="flex items-center justify-between">
                                            <div class="quantity-control">
                                                <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    </svg>
                                                </button>
                                                <input type="number" value="{{ $item->quantity }}" min="1" class="quantity-input" readonly>
                                                <button type="button" class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                In Stock
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Continue Shopping -->
                    <div class="mt-8">
                        <a href="{{ route('products.index') }}" class="btn-secondary inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </main>

                <!-- Order Summary -->
                <aside class="lg:w-1/3">
                    <div class="order-summary">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h3>
                        
                        @php
                            $subtotal = $cartItems->sum(function($item) {
                                return $item->product->price * $item->quantity;
                            });
                            $shipping = 0; // Free shipping
                            $total = $subtotal + $shipping;
                        @endphp

                        <div class="space-y-2">
                            <div class="summary-row">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold" data-summary="subtotal">₱{{ number_format($subtotal) }}</span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold text-green-600">Free</span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="text-lg font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-bold text-red-600" data-summary="total">₱{{ number_format($total) }}</span>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="promo-code">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                            <div class="flex gap-2">
                                <input type="text" placeholder="Enter code" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                                <button class="btn-secondary px-4 py-2">Apply</button>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="mt-6 space-y-3">
                            <a href="{{ route('cart.checkout') }}" class="btn-primary w-full text-lg py-4 inline-flex items-center justify-center">
                                <span>Proceed to Checkout</span>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                            
                            <div class="text-center text-sm text-gray-500">
                                <svg class="w-4 h-4 inline mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Secure Checkout
                            </div>
                        </div>

                        <!-- Trust Badges -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="text-xs text-gray-600">Authentic</div>
                                </div>
                                <div>
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div class="text-xs text-gray-600">Fast Delivery</div>
                                </div>
                                <div>
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                                    <div class="text-xs text-gray-600">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="empty-cart animate-fade-in-up">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Looks like you haven't added any items yet. Start shopping to fill your cart with amazing products!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-3 inline-flex items-center">
                        <span>Start Shopping</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('custom_orders.index') }}" class="btn-secondary text-lg px-8 py-3 inline-flex items-center">
                        <span>Custom Orders</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </a>
                </div>

                <!-- Recommended Products -->
                <div class="mt-12">
                    <h4 class="text-lg font-semibold text-gray-900 mb-6">You might also like</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $recommendedProducts = \App\Models\Product::take(3)->get();
                        @endphp
                        @foreach($recommendedProducts as $product)
                            <div class="bg-white rounded-xl p-4 shadow-lg hover:shadow-xl transition-shadow">
                                <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-4 overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gray-100 flex items-center justify-center\'><div class=\'text-3xl\'>📦</div></div>';">
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                            <div class="text-3xl">📦</div>
                                        </div>
                                    @endif
                                </div>
                                <h5 class="font-semibold text-gray-900 mb-2">{{ $product->name }}</h5>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-red-600">₱{{ number_format($product->price ?? 0) }}</span>
                                    <a href="{{ route('products.show', $product->id) }}" class="btn-primary px-4 py-2 text-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- JavaScript for Cart Operations -->
    <script>
        function updateQuantity(itemId, newQuantity) {
            console.log('Updating quantity - Item ID:', itemId, 'New Qty:', newQuantity);
            
            if (newQuantity < 1) {
                console.log('Quantity is less than 1, aborting');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
            const quantityInput = cartItem?.querySelector('.quantity-input');
            const priceDisplay = cartItem?.querySelector('.text-xl.font-bold.text-red-600');
            
            // Make AJAX call to update quantity using PATCH
            fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    quantity: newQuantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Update the quantity input value
                    if (quantityInput) {
                        quantityInput.value = newQuantity;
                    }
                    
                    // Get product price from the cart item
                    const eachPriceText = cartItem?.querySelector('.text-sm.text-gray-500');
                    const eachPrice = parseFloat(eachPriceText?.textContent.match(/[\d.]+/)?.[0] || 0);
                    const itemTotal = eachPrice * newQuantity;
                    
                    // Update the item total price
                    if (priceDisplay) {
                        priceDisplay.textContent = '₱' + itemTotal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                    }
                    
                    // Update order summary
                    updateOrderSummary();
                } else {
                    alert('Failed to update quantity: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                alert('Error updating quantity. Please try again.');
            });
        }
        
        function updateOrderSummary() {
            // Calculate new subtotal from all items
            const cartItems = document.querySelectorAll('[data-item-id]');
            let subtotal = 0;
            
            cartItems.forEach(item => {
                const priceText = item.querySelector('.text-sm.text-gray-500');
                const quantityInput = item.querySelector('.quantity-input');
                
                if (priceText && quantityInput) {
                    const price = parseFloat(priceText.textContent.match(/[\d.]+/)?.[0] || 0);
                    const quantity = parseInt(quantityInput.value) || 0;
                    subtotal += price * quantity;
                }
            });
            
            // Update summary display
            const subtotalDisplay = document.querySelector('[data-summary="subtotal"]');
            const totalDisplay = document.querySelector('[data-summary="total"]');
            
            if (subtotalDisplay) {
                subtotalDisplay.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            }
            
            if (totalDisplay) {
                totalDisplay.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            }
        }

        // Delete cart item handler
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing delete button handlers');
            const deleteButtons = document.querySelectorAll('.remove-btn');
            console.log('Found ' + deleteButtons.length + ' delete buttons');
            
            deleteButtons.forEach((button, index) => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const cartItem = this.closest('.cart-item');
                    const itemId = cartItem?.getAttribute('data-item-id');
                    
                    console.log('Delete button clicked - Item ID:', itemId);
                    
                    if (!itemId) {
                        alert('Could not identify item. Please try again.');
                        return;
                    }
                    
                    if (confirm('Are you sure you want to remove this item from your cart?')) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        console.log('Sending delete request for item:', itemId);
                        
                        fetch(`/cart/remove/${itemId}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            console.log('Delete response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Delete response data:', data);
                            if (data.success) {
                                console.log('Delete successful, removing item from DOM');
                                // Remove the item from the page
                                cartItem.remove();
                                
                                // Update order summary
                                updateOrderSummary();
                                
                                // Check if cart is now empty
                                const remainingItems = document.querySelectorAll('[data-item-id]').length;
                                if (remainingItems === 0) {
                                    console.log('Cart is now empty, reloading page');
                                    location.reload();
                                }
                            } else {
                                alert('Failed to remove item: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error removing item:', error);
                            alert('Error removing item. Please try again.');
                        });
                    }
                });
            });
        });
    </script>
@endsection
