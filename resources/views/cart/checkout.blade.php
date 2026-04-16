@extends('layouts.app')

@push('styles')
<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-image-container {
        transition: all 0.3s ease;
    }
    
    .product-image-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    
    .cart-item-card {
        transition: all 0.2s ease;
    }
    
    .cart-item-card:hover {
        background-color: #f9fafb;
    }
    
    .remove-btn {
        transition: all 0.2s ease;
    }
    
    .remove-btn:hover {
        transform: scale(1.05);
    }
    
    .qty-btn {
        transition: all 0.15s ease;
    }
    
    .qty-btn:active {
        transform: scale(0.95);
    }
    
    .qty-updating {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .subtotal-price {
        transition: all 0.3s ease;
    }
    
    @media (max-width: 640px) {
        .mobile-stack {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <span style="color: #800000;">🛒</span>
                Checkout
            </h1>
            <p class="text-gray-600">Review your order and complete payment</p>
        <!-- Hidden checkout form to collect selected payment_method via form attributes -->
<form id="checkout-form" action="{{ route('cart.checkout.process') }}" method="POST" class="hidden">
    @csrf
    <!-- radios above are bound here via form="checkout-form" -->
    <input type="hidden" name="confirm" value="1" />
    <input type="hidden" name="payment_option" id="paymentOptionInput" value="{{ old('payment_option', 'full') }}" />
    <input type="hidden" name="coupon_code" id="coupon-code-input" value="{{ $appliedCoupon->code ?? '' }}" />
    <input type="hidden" name="discount_amount" id="discount-amount-input" value="{{ $discount ?? 0 }}" />
    <input type="hidden" name="coupon_applies_to" id="coupon-applies-to-input" value="{{ !empty($appliedCoupon) ? $appliedCoupon->getAppliesTo() : '' }}" />
    <input type="hidden" name="downpayment_rate" id="downpaymentRateInput" value="50" />
    
    {{-- Pass selected cart items through form to survive session loss on Railway --}}
    @if(session()->has('selected_cart_items'))
        @foreach(session('selected_cart_items') as $itemId)
            <input type="hidden" name="selected_items[]" value="{{ $itemId }}" />
        @endforeach
    @endif
</form>

</div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Order Items Section -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Delivery Option
                        </h2>

                        <p class="text-sm text-gray-600 mb-3">
                            Choose whether you want your order delivered or picked up at the store.
                        </p>

                        @php $selectedDeliveryType = old('delivery_type', 'delivery'); @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label id="delivery-radio-label" class="delivery-option-label flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all {{ $selectedDeliveryType === 'delivery' ? 'ring-2' : 'border-gray-200' }}" style="{{ $selectedDeliveryType === 'delivery' ? 'border-color: #800000; background-color: #fff5f5; --tw-ring-color: rgba(128, 0, 0, 0.2);' : '' }}" onmouseover="if (!this.querySelector('input').checked) { this.style.borderColor='#a00000'; this.style.backgroundColor='#fff5f5'; }" onmouseout="if (!this.querySelector('input').checked) { this.style.borderColor=''; this.style.backgroundColor=''; }">
                                <input type="radio" name="delivery_type" value="delivery" form="checkout-form" class="w-5 h-5" style="accent-color: #800000;" {{ $selectedDeliveryType === 'delivery' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                        </svg>
                                        Delivery
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">Send to your address</div>
                                </div>
                            </label>

                            <label id="pickup-radio-label" class="delivery-option-label flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all {{ $selectedDeliveryType === 'pickup' ? 'ring-2' : 'border-gray-200' }}" style="{{ $selectedDeliveryType === 'pickup' ? 'border-color: #800000; background-color: #fff5f5; --tw-ring-color: rgba(128, 0, 0, 0.2);' : '' }}" onmouseover="if (!this.querySelector('input').checked) { this.style.borderColor='#a00000'; this.style.backgroundColor='#fff5f5'; }" onmouseout="if (!this.querySelector('input').checked) { this.style.borderColor=''; this.style.backgroundColor=''; }">
                                <input type="radio" name="delivery_type" value="pickup" form="checkout-form" class="w-5 h-5" style="accent-color: #800000;" {{ $selectedDeliveryType === 'pickup' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Pickup
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">Pick up from store</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Address Section (only visible when delivery is selected) -->
                    <div id="delivery-address-section" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Delivery Address
                            </div>
                        </h2>

                        @if($addresses->count() > 0)
                            @php
                                $selectedAddress = $defaultAddress ?? $addresses->first();
                            @endphp
                            
                            <!-- Selected Address Display (Shopee style) -->
                            <div class="mb-4">
                                <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-bold text-gray-900">{{ $selectedAddress->full_name }}</span>
                                            <span class="text-gray-600">({{ $selectedAddress->phone_number }})</span>
                                            @if($selectedAddress->is_default)
                                                <span class="px-2 py-0.5 bg-red-100 text-xs font-semibold rounded" style="color: #800000;">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-700">{{ $selectedAddress->formatted_address }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $selectedAddress->city }}, {{ $selectedAddress->region }}, {{ $selectedAddress->postal_code }}</p>
                                    </div>
                                    <button type="button" onclick="openAddressModal()" class="text-sm font-medium px-4 py-2 rounded-lg border-2 transition-all" style="color: #800000; border-color: #800000;" onmouseover="this.style.backgroundColor='#fff5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                        Change
                                    </button>
                                </div>
                            </div>

                            <!-- Hidden input for selected address -->
                            <input type="hidden" name="address_id" id="selectedAddressInput" value="{{ $selectedAddress->id }}" form="checkout-form">
                            
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded">
                                <p class="text-sm text-yellow-800 flex items-start gap-2">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>
                                        No saved addresses yet.
                                        <button type="button" onclick="openNewAddressModal()" class="font-semibold text-yellow-900 hover:underline">Create one now</button>
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Pickup Information Section (only visible when pickup is selected) -->
                    <div id="pickup-info-section" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hidden">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Store Pickup Information
                        </h2>

                        <div class="rounded-xl p-6 border-2 shadow-sm" style="background: linear-gradient(135deg, #fff5f5 0%, #fff5f5 100%); border-color: rgba(128, 0, 0, 0.2);">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center shadow-md" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);">
                                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900 mb-2 text-lg">Yakan Weaving Store</h3>
                                    <p class="text-gray-700 mb-4 flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-gray-800"><strong>Address:</strong> Yakan Village, Upper Calarian, Labuan-Limpapa Road, National Road, Zamboanga City, Philippines 7000</span>
                                    </p>
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-start gap-2 bg-white rounded-lg p-3 border border-red-100">
                                            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <div class="font-semibold text-gray-900">Store Hours</div>
                                                <div class="text-gray-600">Monday - Saturday: 9:00 AM - 6:00 PM</div>
                                                <div class="text-red-600 text-xs mt-1">Closed on Sundays & Holidays</div>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2 bg-white rounded-lg p-3 border border-red-100">
                                            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <div>
                                                <div class="font-semibold text-gray-900">Contact Number</div>
                                                <div class="text-gray-600">+63 917-123-4567</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 bg-yellow-50 rounded-lg p-4 border-2 border-yellow-300">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="text-sm">
                                                <strong class="text-yellow-900">Important Reminders:</strong>
                                                <ul class="mt-2 space-y-1 text-yellow-800 list-disc list-inside">
                                                    <li>Bring a valid government-issued ID</li>
                                                    <li>Present your order confirmation number</li>
                                                    <li>Orders can be picked up 1-3 business days after confirmation</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Order Items
                        </h2>
                        
                        <div class="space-y-4">
                            @foreach($cartItems as $item)
                                @php 
                                    $variant = $item->variant ?? null;
                                    $unitPrice = (float) $item->product->getDiscountedPrice((float) ($variant?->price ?? $item->product->price));
                                    $lineSubtotal = (int) $item->quantity * $unitPrice;
                                    $maxStock = $variant
                                        ? (int) ($variant->stock ?? 0)
                                        : (int) ($item->product->inventory?->quantity ?? $item->product->stock ?? 0);
                                @endphp
                                
                                <div class="flex items-start gap-4 py-4 border-b border-gray-100 last:border-0 cart-item-card">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24 bg-white rounded-xl overflow-hidden border border-gray-200 shadow-sm product-image-container">
                                        @if($item->product->image)
                                            <img src="{{ $item->product->image_src }}" 
                                                 alt="{{ $item->product->name }}"
                                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Hidden form for quantity updates -->
                                    <form id="update-form-{{ $item->id }}" action="{{ route('cart.update', $item->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('PUT')
                                        <input id="update-qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ max(1, $maxStock) }}" value="{{ $item->quantity }}">
                                    </form>
                                    
                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $item->product->name }}</h3>
                                        @if($variant)
                                            <p class="text-xs text-gray-500 mb-2">Variant: {{ $variant->display_name }}</p>
                                        @endif
                                        @if($item->product->description)
                                            <p class="text-sm text-gray-500 mb-2 line-clamp-1">{{ $item->product->description }}</p>
                                        @endif
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 text-sm text-gray-600">
                                            <div class="mb-2 sm:mb-0">
                                                <span class="font-medium">Price:</span>
                                                <span class="ml-1 text-gray-900">₱{{ number_format($unitPrice, 2) }}</span>
                                                <span class="text-gray-400 mx-2">•</span>
                                                <span class="font-medium">Qty:</span>
                                                <span class="ml-1 text-gray-900">{{ $item->quantity }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                    <button type="button" class="px-3 py-1.5 text-gray-700 hover:bg-gray-100 qty-btn qty-minus" data-target="qty-{{ $item->id }}" data-item-id="{{ $item->id }}" data-action="decrease">−</button>
                                                    <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ max(1, $maxStock) }}" value="{{ $item->quantity }}" class="w-12 text-center py-1.5 focus:outline-none" readonly />
                                                    <button type="button" class="px-3 py-1.5 text-gray-700 hover:bg-gray-100 qty-btn qty-plus" data-target="qty-{{ $item->id }}" data-item-id="{{ $item->id }}" data-action="increase">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Subtotal and Remove -->
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="text-lg font-bold text-gray-900 subtotal-price" id="subtotal-{{ $item->id }}">₱{{ number_format($lineSubtotal, 2) }}</div>
                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs flex items-center gap-1 remove-btn" style="color: #800000;" onmouseover="this.style.color='#600000'" onmouseout="this.style.color='#800000'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div id="payment-method-section" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hidden">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Payment Method
                        </h2>
                        
                        <div class="space-y-3">
                            <!-- PayMongo Payment Option -->
                            <div class="border-2 border-gray-200 rounded-xl overflow-hidden transition-all duration-200" id="paymongo-option-wrap">
                                <label class="relative flex items-center p-4 cursor-pointer hover:bg-blue-50 transition-all duration-200 group">
                                    <input type="radio" name="payment_method" value="paymongo" checked required class="w-5 h-5 text-blue-600 focus:ring-blue-500 focus:ring-2" form="checkout-form" onclick="showPaymentDetails('paymongo')">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-white border border-blue-100 rounded-lg flex items-center justify-center p-1 overflow-hidden">
                                                <svg viewBox="0 0 48 48" class="w-full h-full"><rect width="48" height="48" rx="8" fill="#0B7B3E"/><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-size="10" font-weight="bold" fill="white" font-family="Arial">Pay</text></svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">PayMongo</div>
                                                <div class="text-sm text-gray-500">GCash, Credit/Debit Card, GrabPay</div>
                                            </div>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-blue-600 payment-check-paymongo hidden" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </label>
                                <div id="paymongo-details" class="hidden px-4 pb-4">
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
                                        <span class="text-2xl">💳</span>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-800">You'll be redirected to PayMongo Checkout</p>
                                            <p class="text-xs text-blue-700 mt-1">Pay securely with GCash, credit/debit card, or GrabPay.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-5">
                            <h3 class="text-sm font-bold text-gray-900 mb-3">Payment Plan</h3>
                            @php $selectedPaymentOption = old('payment_option', 'full'); @endphp
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                                    <input type="radio" name="payment_option_selector" value="full" {{ $selectedPaymentOption !== 'downpayment' ? 'checked' : '' }} class="mt-0.5 w-5 h-5" style="accent-color: #800000;">
                                    <span>
                                        <span class="block font-semibold text-gray-900">Full Payment</span>
                                        <span class="block text-xs text-gray-600">Pay 100% now at checkout.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                                    <input type="radio" name="payment_option_selector" value="downpayment" {{ $selectedPaymentOption === 'downpayment' ? 'checked' : '' }} class="mt-0.5 w-5 h-5" style="accent-color: #800000;">
                                    <span>
                                        <span class="block font-semibold text-gray-900">50% Downpayment</span>
                                        <span class="block text-xs text-gray-600">Pay 50% now and settle the remaining 50% before release/shipping.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <script>
                        function showPaymentDetails(type) {
                            const paymongoWrap = document.getElementById('paymongo-option-wrap');
                            const paymongoDetails = document.getElementById('paymongo-details');

                            if (paymongoWrap) {
                                paymongoWrap.classList.remove('border-blue-400');
                            }
                            if (paymongoDetails) {
                                paymongoDetails.classList.add('hidden');
                            }
                            document.querySelectorAll('.payment-check-paymongo').forEach(el => el.classList.add('hidden'));

                            if (type === 'paymongo') {
                                if (paymongoDetails) {
                                    paymongoDetails.classList.remove('hidden');
                                }
                                if (paymongoWrap) {
                                    paymongoWrap.classList.add('border-blue-400');
                                }
                                document.querySelectorAll('.payment-check-paymongo').forEach(el => el.classList.remove('hidden'));
                            }
                        }

                        showPaymentDetails('paymongo');
                        </script>
                    </div>

                    <!-- Customer Notes Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6" style="color: #800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Order Notes <span class="text-sm font-normal text-gray-500">(Optional)</span>
                        </h2>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                            <p class="text-sm text-blue-800 flex items-start gap-2">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Add any special instructions or requests for your order (e.g., gift wrapping, special packaging, color preferences).</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Your Notes</label>
                            <textarea name="customer_notes" form="checkout-form" rows="4"
                                      placeholder="Example: Please gift wrap this order. I prefer darker colors. Please handle with care..."
                                      class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 transition-all resize-none" style="focus:ring-color: #800000; focus:border-color: #800000;">{{ old('customer_notes') }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Maximum 500 characters</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-6 border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b-2 border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Order Summary
                        </h2>
                        
                        <div class="space-y-4 mb-6">
                            <!-- Subtotal Row -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium subtotal-items">Subtotal ({{ count($cartItems) }} items)</span>
                                <span class="font-bold text-gray-900 order-subtotal text-lg">₱{{ number_format($total, 2) }}</span>
                            </div>

                            <!-- Coupon Section -->
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border-2 border-amber-200 shadow-sm">
                                {{-- Available coupons chips --}}
                                @if(!empty($availableCoupons) && $availableCoupons->count() > 0)
                                <div class="mb-3">
                                    <p class="text-xs font-bold text-amber-800 mb-2">🎟️ Available coupons — click to apply:</p>
                                    <div class="flex flex-wrap gap-2 coupon-chips-container">
                                        @foreach($availableCoupons as $ac)
                                        <button type="button"
                                            data-code="{{ $ac->code }}"
                                            onclick="applyAvailableCoupon('{{ $ac->code }}')"
                                            class="coupon-chip text-xs px-3 py-1.5 rounded-full border-2 border-amber-500 bg-white text-amber-900 font-semibold hover:bg-amber-100 transition-all shadow-sm {{ ($appliedCoupon->code ?? '') === $ac->code ? 'bg-amber-500 text-white border-amber-600' : '' }}">
                                            🏷️ {{ $ac->code }}: {{ $ac->getDiscountDescription() }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                {{-- AJAX coupon messages --}}
                                {{-- Hidden input kept for form submission --}}
                                <input id="coupon-input" type="hidden" value="{{ $appliedCoupon->code ?? '' }}">
                                <div id="coupon-msg" class="hidden mb-2 text-sm flex items-center gap-2"></div>
                                @if(session('success'))
                                    <div class="text-green-600 text-sm mb-2">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="text-red-600 text-sm mb-2">{{ session('error') }}</div>
                                @endif
                                @if(!empty($appliedCoupon))
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-xs text-amber-800">Applied: <strong>{{ $appliedCoupon->code }}</strong></span>
                                    <button id="remove-coupon-btn" type="button"
                                        class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-full font-semibold transition-all">
                                        ✕ Remove
                                    </button>
                                </div>
                                @endif
                                {{-- Applied badge (shown by JS after successful apply) --}}
                                <div id="coupon-applied-info" class="{{ empty($appliedCoupon) ? 'hidden' : '' }} text-sm text-amber-900 mt-3 flex items-center gap-2 bg-white rounded-lg p-2">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="min-w-0"><strong>Coupon:</strong> <span id="coupon-applied-code">{{ $appliedCoupon->code ?? '' }}</span></span>
                                </div>
                            </div>

                            <!-- Shipping Fee Row -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Shipping Fee</span>
                                @php
                                    $shippingFee = 0;
                                    $deliveryType = old('delivery_type', 'delivery');
                                    
                                    if ($deliveryType === 'delivery' && isset($defaultAddress)) {
                                        $city = strtolower($defaultAddress->city ?? '');
                                        $region = strtolower($defaultAddress->province ?? $defaultAddress->region ?? '');
                                        $postalCode = $defaultAddress->postal_code ?? '';

                                        // Zone 1 — ₱100: Zamboanga City + Zamboanga Peninsula + BARMM
                                        if (str_contains($city, 'zamboanga') ||
                                                str_contains($region, 'zamboanga') ||
                                                str_contains($region, 'barmm') || str_contains($region, 'bangsamoro') ||
                                                in_array($city, ['dipolog city', 'dapitan city', 'pagadian city',
                                                                 'isabela city', 'zamboanga del norte', 'zamboanga del sur',
                                                                 'zamboanga sibugay', 'ipil', 'jolo', 'bongao',
                                                                 'cotabato city', 'marawi city', 'lamitan city'])) {
                                            $shippingFee = 100;
                                        }
                                        // Zone 2 — ₱180: Other Mindanao (~500–900 km)
                                        elseif (str_contains($region, 'mindanao') || str_contains($region, 'davao') ||
                                                str_contains($region, 'soccsksargen') || str_contains($region, 'caraga') ||
                                                str_contains($region, 'northern mindanao') ||
                                                in_array($city, ['davao city', 'digos city', 'tagum city', 'panabo city',
                                                                 'general santos city', 'koronadal city', 'kidapawan city',
                                                                 'cagayan de oro city', 'iligan city', 'ozamiz city',
                                                                 'butuan city', 'surigao city', 'malaybalay city'])) {
                                            $shippingFee = 180;
                                        }
                                        // Zone 3 — ₱250: Visayas (~700–1200 km)
                                        elseif (str_contains($region, 'visayas') ||
                                                in_array($city, ['cebu city', 'iloilo city', 'bacolod city',
                                                                 'tacloban city', 'dumaguete city', 'tagbilaran city',
                                                                 'ormoc city', 'calbayog city', 'roxas city'])) {
                                            $shippingFee = 250;
                                        }
                                        // Zone 4 — ₱300: NCR + Luzon nearby (~1400–1800 km)
                                        elseif (str_contains($region, 'ncr') || str_contains($region, 'metro manila') ||
                                                str_contains($city, 'manila') || str_contains($region, 'calabarzon') ||
                                                str_contains($region, 'central luzon') ||
                                                in_array($city, ['quezon city', 'makati city', 'pasig city', 'taguig city',
                                                                 'caloocan city', 'antipolo city', 'angeles city',
                                                                 'san fernando city', 'batangas city', 'lucena city'])) {
                                            $shippingFee = 300;
                                        }
                                        // Zone 5 — ₱350: Far Luzon / Remote (~1800+ km)
                                        else {
                                            $shippingFee = 350;
                                        }
                                    }
                                @endphp
                                
                                @if($shippingFee == 0)
                                    <span class="font-bold text-green-600 text-lg" id="shippingFeeDisplay">FREE</span>
                                @else
                                    <span class="font-bold text-gray-900 text-lg" id="shippingFeeDisplay">₱{{ number_format($shippingFee, 2) }}</span>
                                @endif
                                
                                <input type="hidden" name="shipping_fee" id="shippingFeeInput" value="{{ $shippingFee }}" form="checkout-form">
                            </div>

                            <!-- Discount Row (if applicable) -->
                            <div id="discount-row" class="{{ ($discount ?? 0) > 0 ? '' : 'hidden' }} flex justify-between items-center py-3 border-b border-gray-100 bg-green-50 px-3 rounded-lg">
                                <span class="text-gray-600 font-medium">Discount</span>
                                <span class="font-bold text-green-600 text-lg" id="discount-amount">− ₱{{ number_format($discount ?? 0, 2) }}</span>
                            </div>
                            
                            <!-- Total Amount -->
                            <div class="rounded-xl p-4 border-2 mt-4" style="background: linear-gradient(135deg, #fff5f5 0%, #fff5f5 100%); border-color: rgba(128, 0, 0, 0.2);">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-lg font-bold text-gray-900">Total Amount</span>
                                    @php
                                        $finalTotal = $total + $shippingFee - ($discount ?? 0);
                                    @endphp
                                    <span class="text-3xl font-bold order-total" style="color: #800000;" id="finalTotalDisplay">₱{{ number_format($finalTotal, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-600 text-right">Inclusive of shipping</p>
                            </div>

                            <div id="paymentPlanSummary" class="rounded-xl p-4 border border-gray-200 bg-gray-50 mt-3">
                                <div class="flex justify-between items-center">
                                    <span id="payNowLabel" class="text-sm font-semibold text-gray-700">Pay Now</span>
                                    <span id="payNowDisplay" class="text-xl font-bold" style="color: #800000;">₱{{ number_format($finalTotal, 2) }}</span>
                                </div>
                                <div id="remainingBalanceRow" class="hidden mt-2 pt-2 border-t border-gray-200 flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Remaining Balance</span>
                                    <span id="remainingBalanceDisplay" class="text-sm font-semibold text-gray-800">₱0.00</span>
                                </div>
                                <p id="paymentPlanHint" class="text-xs text-gray-500 mt-2">Full payment is selected.</p>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button id="place-order-button" type="button" class="w-full text-white text-center px-6 py-4 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl font-bold text-lg mb-3 flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);" onmouseover="this.style.background='linear-gradient(135deg, #600000 0%, #400000 100%)'" onmouseout="this.style.background='linear-gradient(135deg, #800000 0%, #600000 100%)'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Place Order
                        </button>
                        
                        <!-- Back to Cart Button -->
                        <a href="{{ route('cart.index') }}" class="block w-full text-center text-gray-700 px-6 py-3 rounded-xl border-2 border-gray-300 transition-all duration-200 font-bold flex items-center justify-center gap-2" style="" onmouseover="this.style.color='#800000'; this.style.borderColor='#800000';" onmouseout="this.style.color='#374151'; this.style.borderColor='#d1d5db';">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Cart
                        </a>

                        <!-- Security Badges -->
                        <div class="mt-6 pt-6 border-t-2 border-gray-200 space-y-3">
                            <div class="flex items-center gap-3 text-sm text-gray-700 bg-green-50 p-3 rounded-lg border border-green-200">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-semibold">Secure Checkout</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-700 bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                                </svg>
                                <span class="font-semibold">Fast Delivery</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-700 bg-purple-50 p-3 rounded-lg border border-purple-200">
                                <svg class="w-5 h-5 text-purple-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-semibold">Money-back Guarantee</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

<!-- Order Placement Loading Overlay -->
<div id="checkout-loading-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:linear-gradient(160deg,#6b0000 0%,#800000 45%,#3d0000 100%); align-items:center; justify-content:center; flex-direction:column;">
    <div style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(20px); border-radius:24px; padding:48px 40px; text-align:center; max-width:340px; width:90%; box-shadow:0 32px 64px rgba(0,0,0,0.4); animation:checkFadeUp 0.3s ease-out;">
        <div style="font-size:52px; margin-bottom:16px;">🛒</div>
        <h2 style="color:#fff; font-size:20px; font-weight:700; margin-bottom:8px; font-family:system-ui,sans-serif;">Placing Your Order…</h2>
        <p style="color:rgba(255,255,255,0.7); font-size:13px; margin-bottom:24px; font-family:system-ui,sans-serif;">Please wait, do not close this page.</p>
        <div style="width:40px;height:40px;border:4px solid rgba(255,255,255,0.25);border-top-color:#fff;border-radius:50%;animation:checkSpin 0.8s linear infinite;margin:0 auto;"></div>
    </div>
</div>
<style>
@keyframes checkFadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes checkSpin   { to{transform:rotate(360deg)} }
</style>

<script>
// Helper: inject auth_token (and buy_now params if applicable) into any form before programmatic submit
// (form.submit() doesn't fire the submit event, so the layout JS can't intercept it)
function injectAuthAndSubmit(form) {
    const authToken = localStorage.getItem('yakan_auth_token');
    if (authToken && !form.querySelector('input[name="auth_token"]')) {
        const t = document.createElement('input');
        t.type = 'hidden'; t.name = 'auth_token'; t.value = authToken;
        form.appendChild(t);
    }
    // Carry buy_now params through the form POST (so processCheckout knows it's Buy Now)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('buy_now') === '1') {
        ['buy_now', 'product_id', 'variant_id', 'quantity'].forEach(function(key) {
            if (urlParams.has(key) && !form.querySelector('input[name="' + key + '"]')) {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = key; inp.value = urlParams.get(key);
                form.appendChild(inp);
            }
        });
    }
    // Show loading overlay before submitting
    const overlay = document.getElementById('checkout-loading-overlay');
    if (overlay) { overlay.style.display = 'flex'; }
    form.submit();
}

function parsePesoAmount(text) {
    const cleaned = String(text || '').replace(/[^0-9.-]/g, '');
    const parsed = parseFloat(cleaned);
    return Number.isFinite(parsed) ? parsed : 0;
}

function updatePaymentPlanSummary() {
    const totalEl = document.getElementById('finalTotalDisplay');
    const payNowLabel = document.getElementById('payNowLabel');
    const payNowDisplay = document.getElementById('payNowDisplay');
    const remainingRow = document.getElementById('remainingBalanceRow');
    const remainingDisplay = document.getElementById('remainingBalanceDisplay');
    const paymentPlanHint = document.getElementById('paymentPlanHint');
    const selectedOption = document.querySelector('input[name="payment_option_selector"]:checked')?.value || 'full';
    const downpaymentRateInput = document.getElementById('downpaymentRateInput');
    const paymentOptionInput = document.getElementById('paymentOptionInput');

    if (!totalEl || !payNowDisplay) {
        return;
    }

    const totalAmount = parsePesoAmount(totalEl.textContent);
    const isDownpayment = selectedOption === 'downpayment';
    const payNow = isDownpayment ? (totalAmount * 0.5) : totalAmount;
    const remaining = Math.max(0, totalAmount - payNow);

    if (payNowLabel) {
        payNowLabel.textContent = isDownpayment ? 'Pay Now (50%)' : 'Pay Now';
    }
    payNowDisplay.textContent = '₱' + payNow.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (remainingDisplay) {
        remainingDisplay.textContent = '₱' + remaining.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (remainingRow) {
        remainingRow.classList.toggle('hidden', !isDownpayment);
    }

    if (paymentPlanHint) {
        paymentPlanHint.textContent = isDownpayment
            ? 'You will pay 50% now. The remaining balance must be settled before release/shipping.'
            : 'Full payment is selected.';
    }

    if (downpaymentRateInput) {
        downpaymentRateInput.value = isDownpayment ? '50' : '';
    }

    if (paymentOptionInput) {
        paymentOptionInput.value = isDownpayment ? 'downpayment' : 'full';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const clamp = (val, min, max) => {
        let v = parseInt(val || 0, 10);
        if (isNaN(v)) v = min;
        if (v < min) v = min;
        if (max && !isNaN(max)) v = Math.min(v, parseInt(max, 10));
        return v;
    };

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Function to update cart via AJAX
    function updateCartQuantity(itemId, newQuantity) {
        console.log('Updating cart item:', itemId, 'to quantity:', newQuantity);
        console.log('CSRF Token:', csrfToken);

        // For buy_now (URL-based), include product_id so server can find it without a session
        const bodyPayload = { quantity: newQuantity };
        if (itemId === 'buy_now') {
            const urlParams = new URLSearchParams(window.location.search);
            const pid = urlParams.get('product_id');
            const vid = urlParams.get('variant_id');
            if (pid) bodyPayload.product_id = pid;
            if (vid) bodyPayload.variant_id = vid;
        }
        
        fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(bodyPayload)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`Server returned ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Update the displayed quantity
                const qtyInput = document.getElementById(`qty-${itemId}`);
                if (qtyInput) qtyInput.value = newQuantity;
                
                // Update subtotal for this item
                const subtotalEl = document.getElementById(`subtotal-${itemId}`);
                if (subtotalEl && data.item_subtotal) {
                    subtotalEl.textContent = `₱${parseFloat(data.item_subtotal).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }
                
                // Update order summary
                updateOrderSummary(data);
            } else {
                console.error('Update failed:', data.message);
                alert(data.message || 'Failed to update quantity');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to update cart. Please try again.');
            location.reload();
        });
    }

    // Function to update order summary
    function updateOrderSummary(data) {
        // Update subtotal
        const subtotalEl = document.querySelector('.order-subtotal');
        if (subtotalEl && data.cart_total) {
            subtotalEl.textContent = `₱${parseFloat(data.cart_total).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        // Update final total (subtotal - discount + current shipping)
        const totalEl = document.querySelector('.order-total');
        if (totalEl && data.total_amount !== undefined) {
            const shippingInput = document.getElementById('shippingFeeInput');
            const shipping = shippingInput ? parseFloat(shippingInput.value) || 0 : 0;
            const grandTotal = parseFloat(data.total_amount) + shipping;
            totalEl.textContent = `₱${grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        // Update item count
        const itemCountEl = document.querySelector('.subtotal-items');
        if (itemCountEl && data.total_items) {
            itemCountEl.textContent = `Subtotal (${data.total_items} item${data.total_items !== 1 ? 's' : ''})`;
        }

        updatePaymentPlanSummary();
    }

    // Handle minus button clicks
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-item-id');
            const id = this.getAttribute('data-target');
            const input = document.getElementById(id);
            if (!input || !itemId) return;
            
            const min = parseInt(input.getAttribute('min') || '1', 10);
            const max = parseInt(input.getAttribute('max') || '0', 10) || null;
            const currentValue = parseInt(input.value, 10) || 1;
            const newValue = clamp(currentValue - 1, min, max);
            
            if (newValue !== currentValue && newValue >= min) {
                updateCartQuantity(itemId, newValue);
            }
        });
    });

    // Handle plus button clicks
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-item-id');
            const id = this.getAttribute('data-target');
            const input = document.getElementById(id);
            if (!input || !itemId) return;
            
            const min = parseInt(input.getAttribute('min') || '1', 10);
            const max = parseInt(input.getAttribute('max') || '0', 10) || null;
            const currentValue = parseInt(input.value, 10) || 1;
            const newValue = clamp(currentValue + 1, min, max);
            
            if (newValue !== currentValue) {
                updateCartQuantity(itemId, newValue);
            }
        });
    });

    // Delivery Type Toggle Handler
    const deliveryAddressSection = document.getElementById('delivery-address-section');
    const pickupInfoSection = document.getElementById('pickup-info-section');
    const deliveryTypeRadios = document.querySelectorAll('input[name="delivery_type"]');
    
    // Function to toggle sections
    function toggleDeliveryPickup(deliveryType) {
        if (deliveryType === 'delivery') {
            deliveryAddressSection.classList.remove('hidden');
            pickupInfoSection.classList.add('hidden');
            // Make address selection required
            const addressRadios = document.querySelectorAll('input[name="address_id"]');
            addressRadios.forEach(radio => {
                radio.setAttribute('required', 'required');
            });
        } else if (deliveryType === 'pickup') {
            deliveryAddressSection.classList.add('hidden');
            pickupInfoSection.classList.remove('hidden');
            // Remove required from address selection when pickup is selected
            const addressRadios = document.querySelectorAll('input[name="address_id"]');
            addressRadios.forEach(radio => {
                radio.removeAttribute('required');
            });
        }
    }
    
    // Set initial state based on checked radio
    const checkedRadio = document.querySelector('input[name="delivery_type"]:checked');
    if (checkedRadio) {
        toggleDeliveryPickup(checkedRadio.value);
    } else {
        // Default to delivery if nothing is checked
        toggleDeliveryPickup('delivery');
    }
    
    // Add change listeners
    const deliveryShippingFee = {{ $shippingFee }};
    deliveryTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleDeliveryPickup(this.value);

            // Update shipping fee display
            const feeDisplay = document.getElementById('shippingFeeDisplay');
            const feeInput   = document.getElementById('shippingFeeInput');
            const subtotal   = parseFloat('{{ $total }}');
            const rawDiscount = parseFloat('{{ $discount ?? 0 }}');
            const totalEl    = document.getElementById('finalTotalDisplay');
            let activeFee;
            if (this.value === 'pickup') {
                activeFee = 0;
                feeDisplay.textContent = 'FREE';
                feeDisplay.className = 'font-bold text-green-600 text-lg';
                if (feeInput) feeInput.value = 0;
            } else {
                activeFee = deliveryShippingFee;
                if (deliveryShippingFee === 0) {
                    feeDisplay.textContent = 'FREE';
                    feeDisplay.className = 'font-bold text-green-600 text-lg';
                } else {
                    feeDisplay.textContent = '₱' + deliveryShippingFee.toFixed(2);
                    feeDisplay.className = 'font-bold text-gray-900 text-lg';
                }
                if (feeInput) feeInput.value = deliveryShippingFee;
            }
            const discount = Math.min(rawDiscount, activeFee);
            if (totalEl) totalEl.textContent = '₱' + (subtotal + activeFee - discount).toFixed(2);
            updatePaymentPlanSummary();
            
            // Update visual styling for selected option
            const deliveryLabel = document.getElementById('delivery-radio-label');
            const pickupLabel = document.getElementById('pickup-radio-label');
            
            if (this.value === 'delivery') {
                deliveryLabel.classList.remove('border-gray-200');
                deliveryLabel.classList.add('border-red-500', 'bg-red-50', 'ring-2', 'ring-red-200');
                pickupLabel.classList.remove('border-red-500', 'bg-red-50', 'ring-2', 'ring-red-200');
                pickupLabel.classList.add('border-gray-200');
            } else {
                pickupLabel.classList.remove('border-gray-200');
                pickupLabel.classList.add('border-red-500', 'bg-red-50', 'ring-2', 'ring-red-200');
                deliveryLabel.classList.remove('border-red-500', 'bg-red-50', 'ring-2', 'ring-red-200');
                deliveryLabel.classList.add('border-gray-200');
            }
        });
    });

    const checkoutForm = document.getElementById('checkout-form');
    const paymentSection = document.getElementById('payment-method-section');
    const placeOrderButton = document.getElementById('place-order-button');

    document.querySelectorAll('input[name="payment_option_selector"]').forEach(function(radio) {
        radio.addEventListener('change', updatePaymentPlanSummary);
    });

    const finalTotalDisplayEl = document.getElementById('finalTotalDisplay');
    if (finalTotalDisplayEl) {
        const totalObserver = new MutationObserver(function() {
            updatePaymentPlanSummary();
        });
        totalObserver.observe(finalTotalDisplayEl, { childList: true, characterData: true, subtree: true });
    }

    updatePaymentPlanSummary();

    // Handle Place Order button click
    if (placeOrderButton) {
        placeOrderButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value;
            
            // Show payment section if it's hidden
            if (paymentSection.classList.contains('hidden')) {
                // Validate delivery fields based on delivery type
                let isValid = true;
                const errors = [];
                
                if (deliveryType === 'delivery') {
                    // Check the hidden selected address input instead of a non-existent radio
                    const selectedAddressId = document.getElementById('selectedAddressInput')?.value?.trim();
                    if (!selectedAddressId) {
                        isValid = false;
                        errors.push('Please select a delivery address');
                    }
                }
                
                if (!isValid) {
                    alert('Please complete the following:\n' + errors.join('\n'));
                    return;
                }
                
                paymentSection.classList.remove('hidden');
                paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                placeOrderButton.textContent = 'Confirm & Place Order';
                placeOrderButton.setAttribute('data-step', 'confirm');
            } else {
                // Validate that a payment method is selected
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentMethod) {
                    alert('Please select a payment method');
                    return;
                }
                
                // Submit the form
                if (checkoutForm) {
                    injectAuthAndSubmit(checkoutForm);
                }
            }
        });
    }
});

// Address Modal Functions
function openAddressModal() {
    const modal = document.getElementById('addressModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddressModal() {
    const modal = document.getElementById('addressModal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function selectAddress(addressId, fullName, phoneNumber, formattedAddress, city, region, postalCode, isDefault) {
    // Update hidden input
    document.getElementById('selectedAddressInput').value = addressId;
    
    // Calculate shipping fee based on region (Professional Philippine courier rates)
    let shippingFee = 0;
    const cityLower = city.toLowerCase();
    const regionLower = region.toLowerCase();
    
    // ₱100 — Zamboanga City + Peninsula + BARMM
    if (cityLower.includes('zamboanga') || regionLower.includes('zamboanga') ||
             ['isabela', 'dipolog', 'dapitan', 'pagadian'].includes(cityLower)) {
        shippingFee = 100;
    }
    // ₱120 - Western Mindanao
    else if (['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao'].includes(cityLower) ||
             regionLower.includes('barmm') || regionLower.includes('armm')) {
        shippingFee = 120;
    }
    // ₱150 - Other Mindanao regions
    else if (regionLower.includes('mindanao') ||
             ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'].includes(cityLower)) {
        shippingFee = 150;
    }
    // ₱180 - Visayas
    else if (regionLower.includes('visayas') ||
             ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'].includes(cityLower)) {
        shippingFee = 180;
    }
    // ₱220 - Metro Manila & nearby
    else if (cityLower.includes('manila') || regionLower.includes('ncr') ||
             ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'].includes(cityLower)) {
        shippingFee = 220;
    }
    // ₱250 - Northern Luzon
    else if (regionLower.includes('luzon') ||
             ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'].includes(cityLower)) {
        shippingFee = 250;
    }
    // ₱280 - Remote islands & far areas
    else {
        shippingFee = 280;
    }
    
    // Update shipping fee display
    const shippingFeeDisplay = document.getElementById('shippingFeeDisplay');
    const shippingFeeInput = document.getElementById('shippingFeeInput');
    
    if (shippingFee === 0) {
        shippingFeeDisplay.innerHTML = 'FREE';
        shippingFeeDisplay.className = 'font-bold text-green-600 text-lg';
    } else {
        shippingFeeDisplay.innerHTML = '₱' + shippingFee.toFixed(2);
        shippingFeeDisplay.className = 'font-bold text-gray-900 text-lg';
    }
    shippingFeeInput.value = shippingFee;
    
    // Update total amount
    const subtotal = parseFloat('{{ $total }}');
    const rawDiscount = parseFloat('{{ $discount ?? 0 }}');
    const couponAppliesTo = document.getElementById('coupon-applies-to-input')?.value || '';
    const discount = couponAppliesTo === 'shipping' ? Math.min(rawDiscount, shippingFee) : rawDiscount;
    const finalTotal = subtotal + shippingFee - discount;
    
    const finalTotalDisplay = document.getElementById('finalTotalDisplay');
    finalTotalDisplay.innerHTML = '₱' + finalTotal.toFixed(2);
    updatePaymentPlanSummary();
    
    // Update displayed address
    const addressDisplay = document.querySelector('#delivery-address-section .bg-gray-50');
    let defaultBadge = isDefault ? '<span class="px-2 py-0.5 bg-red-100 text-xs font-semibold rounded" style="color: #800000;">Default</span>' : '';
    
    addressDisplay.innerHTML = `
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="font-bold text-gray-900">${fullName}</span>
                <span class="text-gray-600">(${phoneNumber})</span>
                ${defaultBadge}
            </div>
            <p class="text-sm text-gray-700">${formattedAddress}</p>
            <p class="text-sm text-gray-600 mt-1">${city}, ${region}, ${postalCode}</p>
        </div>
        <button type="button" onclick="openAddressModal()" class="text-sm font-medium px-4 py-2 rounded-lg border-2 transition-all" style="color: #800000; border-color: #800000;" onmouseover="this.style.backgroundColor='#fff5f5'" onmouseout="this.style.backgroundColor='transparent'">
            Change
        </button>
    `;
    
    closeAddressModal();
}

function splitCheckoutFullName(fullName) {
    const safeName = (fullName || '').trim();
    if (!safeName) {
        return { firstName: '', lastName: '' };
    }

    const parts = safeName.split(/\s+/);
    if (parts.length === 1) {
        return { firstName: parts[0], lastName: '' };
    }

    return {
        firstName: parts.slice(0, -1).join(' '),
        lastName: parts[parts.length - 1]
    };
}

let editAddressCascadingInitialized = false;

function initializeEditAddressModalCascading(prefill = {}) {
    const regionSelect = document.getElementById('edit_region_id');
    const provinceSelect = document.getElementById('edit_province_id');
    const citySelect = document.getElementById('edit_city_id');
    const barangaySelect = document.getElementById('edit_barangay_id');
    const barangayHint = document.getElementById('edit_barangay_hint');

    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        return;
    }

    const tokenFromUrl = new URLSearchParams(window.location.search).get('auth_token');
    const token = tokenFromUrl || localStorage.getItem('yakan_auth_token') || sessionStorage.getItem('auth_token') || '';
    const apiUrl = (path) => token ? `${path}?auth_token=${encodeURIComponent(token)}` : path;

    const resetSelect = (select, placeholder) => {
        select.innerHTML = `<option value="">-- ${placeholder} --</option>`;
        select.disabled = true;
        select.classList.add('bg-gray-100');
    };

    const enableSelect = (select) => {
        select.disabled = false;
        select.classList.remove('bg-gray-100');
    };

    const selectByLabel = (select, labelText) => {
        if (!labelText) return '';
        const normalized = String(labelText).toLowerCase().trim();
        for (let i = 0; i < select.options.length; i++) {
            const optionLabel = String(select.options[i].textContent || '').toLowerCase().trim();
            if (optionLabel === normalized) {
                select.selectedIndex = i;
                return select.options[i].value;
            }
        }
        return '';
    };

    const loadRegions = (selectedRegionName) => {
        fetch(apiUrl('/addresses/api/regions'))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                data.data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.id;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
                enableSelect(regionSelect);

                const selectedRegionId = selectByLabel(regionSelect, selectedRegionName);
                if (selectedRegionId) {
                    loadProvinces(selectedRegionId, prefill.provinceName || '');
                }
            })
            .catch(error => console.error('Error loading edit regions:', error));
    };

    const loadProvinces = (regionId, selectedProvinceName) => {
        fetch(apiUrl(`/addresses/api/provinces/${regionId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                data.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.name;
                    provinceSelect.appendChild(option);
                });
                enableSelect(provinceSelect);

                const selectedProvinceId = selectByLabel(provinceSelect, selectedProvinceName);
                if (selectedProvinceId) {
                    loadCities(selectedProvinceId, prefill.cityName || '');
                }
            })
            .catch(error => console.error('Error loading edit provinces:', error));
    };

    const loadCities = (provinceId, selectedCityName) => {
        fetch(apiUrl(`/addresses/api/cities/${provinceId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                data.data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                enableSelect(citySelect);

                const selectedCityId = selectByLabel(citySelect, selectedCityName);
                if (selectedCityId) {
                    loadBarangays(selectedCityId, prefill.barangayName || '');
                }
            })
            .catch(error => console.error('Error loading edit cities:', error));
    };

    const loadBarangays = (cityId, selectedBarangayName) => {
        if (barangayHint) barangayHint.classList.add('hidden');

        fetch(apiUrl(`/addresses/api/barangays/${cityId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;

                if (data.data.length === 0) {
                    barangaySelect.innerHTML = '<option value="">-- No barangays available --</option>';
                    barangaySelect.disabled = true;
                    if (barangayHint) barangayHint.classList.remove('hidden');
                    return;
                }

                barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                data.data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.id;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
                enableSelect(barangaySelect);
                selectByLabel(barangaySelect, selectedBarangayName);
            })
            .catch(error => console.error('Error loading edit barangays:', error));
    };

    if (!editAddressCascadingInitialized) {
        regionSelect.addEventListener('change', function() {
            resetSelect(provinceSelect, 'Select Province');
            resetSelect(citySelect, 'Select City/Municipality');
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadProvinces(this.value, '');
            }
        });

        provinceSelect.addEventListener('change', function() {
            resetSelect(citySelect, 'Select City/Municipality');
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadCities(this.value, '');
            }
        });

        citySelect.addEventListener('change', function() {
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadBarangays(this.value, '');
            }
        });

        editAddressCascadingInitialized = true;
    }

    resetSelect(provinceSelect, 'Select Province');
    resetSelect(citySelect, 'Select City/Municipality');
    resetSelect(barangaySelect, 'Select Barangay');
    loadRegions(prefill.regionName || '');
}

function openEditAddressModal(addressId, label, fullName, phoneNumber, streetAddress, barangay, city, province, region, postalCode, isDefault) {
    closeAddressModal();

    const parsedName = splitCheckoutFullName(fullName);
    
    // Populate edit modal
    document.getElementById('editAddressId').value = addressId;
    document.getElementById('editLabel').value = label;
    document.getElementById('editFirstName').value = parsedName.firstName;
    document.getElementById('editLastName').value = parsedName.lastName;
    document.getElementById('editPhoneNumber').value = phoneNumber;
    document.getElementById('editStreetAddress').value = streetAddress;
    document.getElementById('editPostalCode').value = postalCode;
    document.getElementById('editIsDefault').checked = isDefault;

    initializeEditAddressModalCascading({
        regionName: region,
        provinceName: province,
        cityName: city,
        barangayName: barangay
    });
    
    // Show edit modal
    document.getElementById('editAddressModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditAddressModal() {
    document.getElementById('editAddressModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openNewAddressModal() {
    closeAddressModal();
    initializeNewAddressModalCascading();
    document.getElementById('newAddressModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeNewAddressModal() {
    document.getElementById('newAddressModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function submitEditAddress() {
    const form = document.getElementById('editAddressForm');
    const addressId = document.getElementById('editAddressId').value;

    // Validate required fields
    const firstName = document.getElementById('editFirstName')?.value?.trim();
    const lastName = document.getElementById('editLastName')?.value?.trim();
    const regionId = document.getElementById('edit_region_id')?.value;
    const provinceId = document.getElementById('edit_province_id')?.value;
    const cityId = document.getElementById('edit_city_id')?.value;
    if (!firstName || !lastName || !regionId || !provinceId || !cityId) {
        alert('Please complete First Name, Last Name, Region, Province, and City/Municipality.');
        return;
    }

    // Build form data
    const formData = new FormData(form);
    formData.set('_method', 'PUT');

    // Auth token
    const authToken = localStorage.getItem('yakan_auth_token') || '';
    if (authToken) formData.set('auth_token', authToken);

    // Buy-now params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('buy_now') === '1') {
        ['buy_now','product_id','variant_id','quantity'].forEach(k => { if (urlParams.has(k)) formData.set(k, urlParams.get(k)); });
    }

    // Show loading spinner on button
    const btn = document.querySelector('#editAddressModal button[onclick="submitEditAddress()"]');
    const origText = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Saving...'; }

    fetch(`/addresses/${addressId}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html,application/json,*/*' }
    })
    .then(res => {
        // Success — reload checkout page to refresh the address display + shipping fee
        window.location.reload();
    })
    .catch(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
        alert('Failed to save. Please try again.');
    });
}

function submitNewAddress() {
    const form = document.getElementById('newAddressForm');
    
    // Validate required fields
    const label = form.querySelector('[name="label"]').value;
    const firstName = form.querySelector('[name="first_name"]').value;
    const lastName = form.querySelector('[name="last_name"]').value;
    const phoneNumber = form.querySelector('[name="phone_number"]').value;
    const formattedAddress = form.querySelector('[name="formatted_address"]').value;
    const regionId = form.querySelector('[name="region_id"]').value;
    const provinceId = form.querySelector('[name="province_id"]').value;
    const cityId = form.querySelector('[name="city_id"]').value;
    const postalCode = form.querySelector('[name="postal_code"]').value;
    
    if (!label || !firstName || !lastName || !phoneNumber || !formattedAddress || !regionId || !provinceId || !cityId || !postalCode) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Set form action and submit
    form.action = '{{ route("addresses.store") }}';
    form.method = 'POST';
    injectAuthAndSubmit(form);
}

let newAddressCascadingInitialized = false;

function initializeNewAddressModalCascading() {
    const regionSelect = document.getElementById('new_region_id');
    const provinceSelect = document.getElementById('new_province_id');
    const citySelect = document.getElementById('new_city_id');
    const barangaySelect = document.getElementById('new_barangay_id');
    const barangayHint = document.getElementById('new_barangay_hint');

    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        return;
    }

    const tokenFromUrl = new URLSearchParams(window.location.search).get('auth_token');
    const token = tokenFromUrl || localStorage.getItem('yakan_auth_token') || sessionStorage.getItem('auth_token') || '';
    const apiUrl = (path) => token ? `${path}?auth_token=${encodeURIComponent(token)}` : path;

    const resetSelect = (select, placeholder) => {
        select.innerHTML = `<option value="">-- ${placeholder} --</option>`;
        select.disabled = true;
        select.classList.add('bg-gray-100');
    };

    const enableSelect = (select) => {
        select.disabled = false;
        select.classList.remove('bg-gray-100');
    };

    const loadRegions = () => {
        fetch(apiUrl('/addresses/api/regions'))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                data.data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.id;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
                enableSelect(regionSelect);
            })
            .catch(error => console.error('Error loading regions:', error));
    };

    const loadProvinces = (regionId) => {
        fetch(apiUrl(`/addresses/api/provinces/${regionId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                data.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.name;
                    provinceSelect.appendChild(option);
                });
                enableSelect(provinceSelect);
            })
            .catch(error => console.error('Error loading provinces:', error));
    };

    const loadCities = (provinceId) => {
        fetch(apiUrl(`/addresses/api/cities/${provinceId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                data.data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                enableSelect(citySelect);
            })
            .catch(error => console.error('Error loading cities:', error));
    };

    const loadBarangays = (cityId) => {
        if (barangayHint) barangayHint.classList.add('hidden');

        fetch(apiUrl(`/addresses/api/barangays/${cityId}`))
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;

                if (data.data.length === 0) {
                    barangaySelect.innerHTML = '<option value="">-- No barangays available --</option>';
                    barangaySelect.disabled = true;
                    if (barangayHint) barangayHint.classList.remove('hidden');
                    return;
                }

                barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                data.data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.id;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
                enableSelect(barangaySelect);
            })
            .catch(error => console.error('Error loading barangays:', error));
    };

    if (!newAddressCascadingInitialized) {
        regionSelect.addEventListener('change', function() {
            resetSelect(provinceSelect, 'Select Province');
            resetSelect(citySelect, 'Select City/Municipality');
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadProvinces(this.value);
            }
        });

        provinceSelect.addEventListener('change', function() {
            resetSelect(citySelect, 'Select City/Municipality');
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadCities(this.value);
            }
        });

        citySelect.addEventListener('change', function() {
            resetSelect(barangaySelect, 'Select Barangay');
            if (this.value) {
                loadBarangays(this.value);
            }
        });

        newAddressCascadingInitialized = true;
    }

    resetSelect(provinceSelect, 'Select Province');
    resetSelect(citySelect, 'Select City/Municipality');
    resetSelect(barangaySelect, 'Select Barangay');
    loadRegions();
}
</script>

<!-- Philippine Locations + Distance Shipping Script -->
<script>
// ============================================================
// Philippine Locations Data (Region → City → Barangay)
// Shipping zones measured from store: Zamboanga City
// ============================================================
const PH_LOCATIONS = {
    "Zamboanga Peninsula (Region IX)": {
        shippingZone: 0,
        cities: {
            "Zamboanga City":          { postal: "7000", barangays: ["Ayala","Baliwasan","Baluno","Boalan","Buenavista","Cabatangan","Calarian","Campo Islam","Campo Uno","Canelar","Capisan","Cawa-Cawa","Culianan","Dita","Divisoria","Dulian (Upper Bunguiao)","Guiwan","Kasanyangan","La Paz","Labuan","Lamisahan","Lapakan","Latawan","Licomo","Ligaw","Lumayang","Lumbangan","Lunzuran","Maasin","Mampang","Manicahan","Mercedes","Pababag","Pagatpat","Pamucutan","Panubigan","Pasonanca","Putik","San Jose Cawa-Cawa","San Jose Gusu","San Roque","Santa Barbara","Santa Catalina","Santa Maria","Santo Niño","Sibulao","Sinunoc","Taluksangay","Tawagan Norte","Tawagan Sur","Taytay","Tetuan","Tigbalabag","Tolosa","Tugbungan","Tumaga","Tukuran","Tulungatung","Upper Calarian","Vitali","Zambowood","Other"] },
            "Zamboanga del Norte (Dipolog City)": { postal: "7100", barangays: [] },
            "Dapitan City":            { postal: "7101", barangays: [] },
            "Pagadian City":           { postal: "7016", barangays: [] },
            "Zamboanga del Sur (Molave)": { postal: "7023", barangays: [] },
            "Ipil (Zamboanga Sibugay)":{ postal: "7001", barangays: [] },
            "Isabela City (Basilan)":  { postal: "7300", barangays: [] }
        }
    },
    "BARMM (Bangsamoro)": {
        shippingZone: 1,
        cities: {
            "Jolo (Sulu)":         { postal: "7400", barangays: [] },
            "Bongao (Tawi-Tawi)":  { postal: "7500", barangays: [] },
            "Cotabato City":       { postal: "9600", barangays: [] },
            "Marawi City":         { postal: "9700", barangays: [] },
            "Lamitan City (Basilan)":{ postal: "7302", barangays: [] },
            "Parang (Maguindanao)":{ postal: "9607", barangays: [] }
        }
    },
    "Davao Region (Region XI)": {
        shippingZone: 2,
        cities: {
            "Davao City":          { postal: "8000", barangays: [] },
            "Digos City":          { postal: "8002", barangays: [] },
            "Tagum City":          { postal: "8100", barangays: [] },
            "Panabo City":         { postal: "8105", barangays: [] },
            "Mati City":           { postal: "8200", barangays: [] }
        }
    },
    "SOCCSKSARGEN (Region XII)": {
        shippingZone: 2,
        cities: {
            "General Santos City": { postal: "9500", barangays: [] },
            "Koronadal City":      { postal: "9506", barangays: [] },
            "Kidapawan City":      { postal: "9400", barangays: [] },
            "Tacurong City":       { postal: "9800", barangays: [] },
            "Isulan (Sultan Kudarat)": { postal: "9805", barangays: [] }
        }
    },
    "Northern Mindanao (Region X)": {
        shippingZone: 2,
        cities: {
            "Cagayan de Oro City": { postal: "9000", barangays: [] },
            "Iligan City":         { postal: "9200", barangays: [] },
            "Ozamiz City":         { postal: "7200", barangays: [] },
            "Oroquieta City":      { postal: "7207", barangays: [] },
            "Gingoog City":        { postal: "9014", barangays: [] },
            "Malaybalay City":     { postal: "8700", barangays: [] },
            "Valencia City":       { postal: "8709", barangays: [] }
        }
    },
    "Caraga (Region XIII)": {
        shippingZone: 2,
        cities: {
            "Butuan City":         { postal: "8600", barangays: [] },
            "Surigao City":        { postal: "8400", barangays: [] },
            "Bayugan City":        { postal: "8502", barangays: [] },
            "Tandag City":         { postal: "8300", barangays: [] }
        }
    },
    "Central Visayas (Region VII)": {
        shippingZone: 3,
        cities: {
            "Cebu City":           { postal: "6000", barangays: [] },
            "Mandaue City":        { postal: "6014", barangays: [] },
            "Lapu-Lapu City":      { postal: "6015", barangays: [] },
            "Dumaguete City":      { postal: "6200", barangays: [] },
            "Tagbilaran City":     { postal: "6300", barangays: [] }
        }
    },
    "Western Visayas (Region VI)": {
        shippingZone: 3,
        cities: {
            "Iloilo City":         { postal: "5000", barangays: [] },
            "Bacolod City":        { postal: "6100", barangays: [] },
            "Roxas City":          { postal: "5800", barangays: [] },
            "Kalibo (Aklan)":      { postal: "5600", barangays: [] }
        }
    },
    "Eastern Visayas (Region VIII)": {
        shippingZone: 3,
        cities: {
            "Tacloban City":       { postal: "6500", barangays: [] },
            "Ormoc City":          { postal: "6541", barangays: [] },
            "Calbayog City":       { postal: "6710", barangays: [] },
            "Catbalogan City":     { postal: "6700", barangays: [] }
        }
    },
    "NCR (Metro Manila)": {
        shippingZone: 4,
        cities: {
            "Manila":              { postal: "1000", barangays: [] },
            "Quezon City":         { postal: "1100", barangays: [] },
            "Makati City":         { postal: "1200", barangays: [] },
            "Pasig City":          { postal: "1600", barangays: [] },
            "Taguig City":         { postal: "1630", barangays: [] },
            "Caloocan City":       { postal: "1400", barangays: [] },
            "Mandaluyong City":    { postal: "1550", barangays: [] },
            "Marikina City":       { postal: "1800", barangays: [] },
            "Parañaque City":      { postal: "1700", barangays: [] },
            "Las Piñas City":      { postal: "1750", barangays: [] },
            "Muntinlupa City":     { postal: "1770", barangays: [] },
            "Valenzuela City":     { postal: "1440", barangays: [] },
            "Malabon City":        { postal: "1470", barangays: [] },
            "Navotas City":        { postal: "1485", barangays: [] },
            "San Juan City":       { postal: "1500", barangays: [] },
            "Pasay City":          { postal: "1300", barangays: [] }
        }
    },
    "CALABARZON (Region IV-A)": {
        shippingZone: 4,
        cities: {
            "Antipolo City":       { postal: "1870", barangays: [] },
            "Lucena City":         { postal: "4301", barangays: [] },
            "Calamba City":        { postal: "4027", barangays: [] },
            "Santa Rosa City":     { postal: "4026", barangays: [] },
            "Batangas City":       { postal: "4200", barangays: [] },
            "Lipa City":           { postal: "4217", barangays: [] },
            "Cavite City":         { postal: "4100", barangays: [] },
            "Bacoor City":         { postal: "4102", barangays: [] }
        }
    },
    "Central Luzon (Region III)": {
        shippingZone: 4,
        cities: {
            "San Fernando City (Pampanga)": { postal: "2000", barangays: [] },
            "Angeles City":        { postal: "2009", barangays: [] },
            "Olongapo City":       { postal: "2200", barangays: [] },
            "Malolos City":        { postal: "3000", barangays: [] },
            "Cabanatuan City":     { postal: "3100", barangays: [] },
            "Tarlac City":         { postal: "2300", barangays: [] }
        }
    },
    "Ilocos Region (Region I)": {
        shippingZone: 5,
        cities: {
            "Laoag City":          { postal: "2900", barangays: [] },
            "Vigan City":          { postal: "2700", barangays: [] },
            "San Fernando City (La Union)": { postal: "2500", barangays: [] },
            "Dagupan City":        { postal: "2400", barangays: [] }
        }
    },
    "Cagayan Valley (Region II)": {
        shippingZone: 5,
        cities: {
            "Tuguegarao City":     { postal: "3500", barangays: [] },
            "Santiago City":       { postal: "3311", barangays: [] },
            "Ilagan City":         { postal: "3300", barangays: [] }
        }
    },
    "CAR (Cordillera)": {
        shippingZone: 5,
        cities: {
            "Baguio City":         { postal: "2600", barangays: [] },
            "Tabuk City":          { postal: "3800", barangays: [] }
        }
    },
    "Bicol Region (Region V)": {
        shippingZone: 5,
        cities: {
            "Legazpi City":        { postal: "4500", barangays: [] },
            "Naga City":           { postal: "4400", barangays: [] },
            "Sorsogon City":       { postal: "4700", barangays: [] }
        }
    },
    "MIMAROPA (Region IV-B)": {
        shippingZone: 5,
        cities: {
            "Puerto Princesa City":{ postal: "5300", barangays: [] },
            "Calapan City":        { postal: "5200", barangays: [] },
            "Romblon (Romblon)":   { postal: "5500", barangays: [] }
        }
    }
};

// Shipping fees per zone (distance from Zamboanga City)
const SHIPPING_ZONES = {
    0: 100,  // Within Zamboanga City
    1: 100,  // Zamboanga Peninsula + BARMM (~150-500 km)
    2: 180,  // Other Mindanao regions (~500-900 km)
    3: 250,  // Visayas (~700-1200 km)
    4: 300,  // NCR + Luzon nearby (~1400-1800 km)
    5: 350   // Far Luzon / Remote (~1800+ km)
};

function populateRegionDropdown(prefix) {
    const sel = document.getElementById(prefix + 'Region');
    const currentVal = sel.value;
    sel.innerHTML = '<option value="">-- Select Region --</option>';
    for (const region in PH_LOCATIONS) {
        const opt = document.createElement('option');
        opt.value = region;
        opt.textContent = region;
        if (region === currentVal) opt.selected = true;
        sel.appendChild(opt);
    }
    populateCityDropdown(prefix);
}

function populateCityDropdown(prefix) {
    const regionSel = document.getElementById(prefix + 'Region');
    const citySel   = document.getElementById(prefix + 'City');
    const region    = regionSel.value;
    citySel.innerHTML = '<option value="">-- Select City --</option>';
    if (region && PH_LOCATIONS[region]) {
        const cities = PH_LOCATIONS[region].cities;
        for (const city in cities) {
            const opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            citySel.appendChild(opt);
        }
    }
    populateBarangayDropdown(prefix);
    autoFillPostal(prefix);
}

function populateBarangayDropdown(prefix) {
    const regionSel = document.getElementById(prefix + 'Region');
    const citySel   = document.getElementById(prefix + 'City');
    const container = document.getElementById(prefix + 'BarangayContainer');
    const region    = regionSel.value;
    const city      = citySel.value;
    const cls = 'w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all';

    if (region && city && PH_LOCATIONS[region] && PH_LOCATIONS[region].cities[city]) {
        const barangays = PH_LOCATIONS[region].cities[city].barangays;
        if (barangays.length > 0) {
            // Known barangay list — use dropdown
            const sel = document.createElement('select');
            sel.id = prefix + 'Barangay'; sel.name = 'barangay'; sel.className = cls;
            sel.innerHTML = '<option value="">-- Select Barangay --</option>';
            barangays.forEach(b => sel.appendChild(new Option(b, b)));
            container.innerHTML = ''; container.appendChild(sel);
        } else {
            // No barangay data — use free-text input so user can enter specific location
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = prefix + 'Barangay'; inp.name = 'barangay';
            inp.placeholder = 'Enter your barangay / district name'; inp.className = cls;
            container.innerHTML = ''; container.appendChild(inp);
        }
    } else {
        const sel = document.createElement('select');
        sel.id = prefix + 'Barangay'; sel.name = 'barangay'; sel.className = cls;
        sel.innerHTML = '<option value="">-- Select Barangay --</option>';
        container.innerHTML = ''; container.appendChild(sel);
    }
}

function autoFillPostal(prefix) {
    const regionSel  = document.getElementById(prefix + 'Region');
    const citySel    = document.getElementById(prefix + 'City');
    const postalInp  = document.getElementById(prefix + 'PostalCode');
    const region     = regionSel.value;
    const city       = citySel.value;
    if (region && city && PH_LOCATIONS[region] && PH_LOCATIONS[region].cities[city]) {
        const postal = PH_LOCATIONS[region].cities[city].postal;
        if (postal) postalInp.value = postal;
    }
}

// Initialize region dropdowns on page load (for possible pre-fill)
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('editRegion')) {
        populateRegionDropdown('edit');
    }
    if (document.getElementById('newRegion')) {
        populateRegionDropdown('new');
    }
});
</script>

<!-- Address Selection Modal -->
@if($addresses->count() > 0)
<div id="addressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="padding-top: 80px;">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[75vh] overflow-hidden shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">My Address</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body - Scrollable -->
        <div class="overflow-y-auto max-h-[calc(75vh-180px)] p-6">
            <div class="space-y-3">
                @foreach($addresses as $address)
                    <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-all cursor-pointer" onclick="selectAddress({{ $address->id }}, '{{ addslashes($address->full_name) }}', '{{ $address->phone_number }}', '{{ addslashes($address->formatted_address) }}', '{{ $address->city }}', '{{ addslashes($address->province ?? '') }}', '{{ $address->postal_code }}', {{ $address->is_default ? 'true' : 'false' }})">
                        <div class="flex items-start gap-3">
                            <!-- Radio Button -->
                            <input type="radio" name="modal_address_selection" value="{{ $address->id }}" class="w-5 h-5 mt-1 flex-shrink-0" style="accent-color: #800000;" {{ ($selectedAddress->id ?? '') == $address->id ? 'checked' : '' }}>
                            
                            <!-- Address Content -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900">{{ $address->full_name }}</span>
                                            <span class="text-gray-600 text-sm">({{ $address->phone_number }})</span>
                                        </div>
                                        <div class="flex gap-2 mb-2">
                                            @if($address->is_default)
                                                <span class="px-2 py-0.5 bg-red-100 text-xs font-semibold rounded" style="color: #800000;">Default</span>
                                            @endif
                                            @if($address->label)
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded">{{ $address->label }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 mb-1">{{ $address->formatted_address }}</p>
                                <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->region }}, {{ $address->postal_code }}</p>
                            </div>
                            
                            <!-- Edit Button -->
                            <button type="button" onclick="event.stopPropagation(); openEditAddressModal({{ $address->id }}, '{{ addslashes($address->label) }}', '{{ addslashes($address->full_name) }}', '{{ $address->phone_number }}', '{{ addslashes($address->street) }}', '{{ addslashes($address->barangay ?? '') }}', '{{ $address->city }}', '{{ addslashes($address->province ?? '') }}', '{{ addslashes($address->region ?? '') }}', '{{ $address->postal_code }}', {{ $address->is_default ? 'true' : 'false' }})" class="text-sm px-3 py-1.5 rounded border transition-all" style="color: #800000; border-color: #800000;" onmouseover="this.style.backgroundColor='#fff5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                Edit
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" onclick="openNewAddressModal()" class="w-full text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);" onmouseover="this.style.background='linear-gradient(135deg, #600000 0%, #400000 100%)'" onmouseout="this.style.background='linear-gradient(135deg, #800000 0%, #600000 100%)'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Address
            </button>
        </div>
    </div>
</div>
@endif

<!-- Edit Address Modal -->
<div id="editAddressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="padding-top: 80px;">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[75vh] overflow-hidden shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Edit Address</h3>
            <button type="button" onclick="closeEditAddressModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body - Scrollable Form -->
        <div class="overflow-y-auto max-h-[calc(75vh-180px)] p-6">
            <form id="editAddressForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="editAddressId" name="address_id">
                <input type="hidden" name="from_checkout" value="1">
                
                <!-- Name -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">First Name <span style="color: #800000;">*</span></label>
                        <input type="text" id="editFirstName" name="first_name" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name <span style="color: #800000;">*</span></label>
                        <input type="text" id="editLastName" name="last_name" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                    </div>
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline" style="color: #800000;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        Phone Number <span style="color: #800000;">*</span>
                    </label>
                    <input type="tel" id="editPhoneNumber" name="phone_number" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>

                <!-- Region -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Region <span style="color: #800000;">*</span>
                    </label>
                    <select id="edit_region_id" name="region_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all">
                        <option value="">-- Select Region --</option>
                    </select>
                </div>

                <!-- Province -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Province <span style="color: #800000;">*</span>
                    </label>
                    <select id="edit_province_id" name="province_id" required disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select Province --</option>
                    </select>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        City/Municipality <span style="color: #800000;">*</span>
                    </label>
                    <select id="edit_city_id" name="city_id" required disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select City/Municipality --</option>
                    </select>
                </div>
                
                <!-- Barangay -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                    <select id="edit_barangay_id" name="barangay_id" disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select Barangay --</option>
                    </select>
                    <p id="edit_barangay_hint" class="hidden text-xs text-gray-400 mt-1">No barangays listed for this city — you can skip this field.</p>
                </div>

                <!-- Street Address -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Street Address <span style="color: #800000;">*</span>
                    </label>
                    <input type="text" id="editStreetAddress" name="formatted_address" required placeholder="House No., Building, Street Name" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>
                
                <!-- Postal Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Postal Code <span style="color: #800000;">*</span>
                    </label>
                    <input type="text" id="editPostalCode" name="postal_code" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>

                <!-- Address Label -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline" style="color: #800000;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Address Label <span style="color: #800000;">*</span>
                    </label>
                    <select id="editLabel" name="label" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                        <option value="">Select a label</option>
                        <option value="Home">🏠 Home</option>
                        <option value="Work">💼 Work</option>
                        <option value="Other">📍 Other</option>
                    </select>
                </div>
                
                <!-- Set as Default -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editIsDefault" name="is_default" value="1" class="w-5 h-5 rounded" style="accent-color: #800000;">
                    <label for="editIsDefault" class="text-sm font-semibold text-gray-700">Set as default address</label>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-3">
            <button type="button" onclick="closeEditAddressModal()" class="flex-1 px-6 py-3 rounded-lg border-2 border-gray-300 font-semibold text-gray-700 transition-all hover:bg-gray-100">
                Cancel
            </button>
            <button type="button" onclick="submitEditAddress()" class="flex-1 text-white px-6 py-3 rounded-lg font-semibold transition-all" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);" onmouseover="this.style.background='linear-gradient(135deg, #600000 0%, #400000 100%)'" onmouseout="this.style.background='linear-gradient(135deg, #800000 0%, #600000 100%)'">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- New Address Modal -->
<div id="newAddressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="padding-top: 80px;">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[75vh] overflow-hidden shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Add New Address</h3>
            <button type="button" onclick="closeNewAddressModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body - Scrollable Form -->
        <div class="overflow-y-auto max-h-[calc(75vh-180px)] p-6">
            <form id="newAddressForm" action="{{ route('addresses.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="from_checkout" value="1">
                @php
                    $checkoutUser = auth()->user();
                    $checkoutPrefillFirstName = old('first_name', $checkoutUser->first_name ?? '');
                    $checkoutPrefillLastName = old('last_name', $checkoutUser->last_name ?? '');

                    if (($checkoutPrefillFirstName === '' || $checkoutPrefillLastName === '') && !empty($checkoutUser?->name)) {
                        $checkoutNameParts = preg_split('/\s+/', trim((string) $checkoutUser->name));
                        if ($checkoutPrefillLastName === '' && count($checkoutNameParts) > 1) {
                            $checkoutPrefillLastName = array_pop($checkoutNameParts);
                        }
                        if ($checkoutPrefillFirstName === '') {
                            $checkoutPrefillFirstName = implode(' ', $checkoutNameParts);
                        }
                    }
                @endphp
                
                <!-- Name -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">First Name <span style="color: #800000;">*</span></label>
                        <input type="text" name="first_name" value="{{ $checkoutPrefillFirstName }}" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name <span style="color: #800000;">*</span></label>
                        <input type="text" name="last_name" value="{{ $checkoutPrefillLastName }}" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                    </div>
                </div>
                
                <!-- Phone Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline" style="color: #800000;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        Phone Number <span style="color: #800000;">*</span>
                    </label>
                    <input type="tel" name="phone_number" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>
                
                <!-- Region -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Region <span style="color: #800000;">*</span>
                    </label>
                    <select id="new_region_id" name="region_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all">
                        <option value="">-- Select Region --</option>
                    </select>
                </div>

                <!-- Province -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Province <span style="color: #800000;">*</span>
                    </label>
                    <select id="new_province_id" name="province_id" required disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select Province --</option>
                    </select>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        City/Municipality <span style="color: #800000;">*</span>
                    </label>
                    <select id="new_city_id" name="city_id" required disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select City/Municipality --</option>
                    </select>
                </div>

                <!-- Barangay -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                    <select id="new_barangay_id" name="barangay_id" disabled class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all bg-gray-100">
                        <option value="">-- Select Barangay --</option>
                    </select>
                    <p id="new_barangay_hint" class="hidden text-xs text-gray-400 mt-1">No barangays listed for this city — you can skip this field.</p>
                </div>

                <!-- Street Address -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Street Address <span style="color: #800000;">*</span>
                    </label>
                    <input type="text" name="formatted_address" required placeholder="House No., Building, Street Name" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>
                
                <!-- Postal Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Postal Code <span style="color: #800000;">*</span>
                    </label>
                    <input type="text" id="newPostalCode" name="postal_code" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                </div>

                <!-- Address Label -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline" style="color: #800000;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Address Label <span style="color: #800000;">*</span>
                    </label>
                    <select name="label" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="focus:ring-color: #800000;">
                        <option value="">Select a label</option>
                        <option value="Home">🏠 Home</option>
                        <option value="Work">💼 Work</option>
                        <option value="Other">📍 Other</option>
                    </select>
                </div>
                
                <!-- Set as Default -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" class="w-5 h-5 rounded" style="accent-color: #800000;">
                    <label class="text-sm font-semibold text-gray-700">Set as default address</label>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-3">
            <button type="button" onclick="closeNewAddressModal()" class="flex-1 px-6 py-3 rounded-lg border-2 border-gray-300 font-semibold text-gray-700 transition-all hover:bg-gray-100">
                Cancel
            </button>
            <button type="button" onclick="submitNewAddress()" class="flex-1 text-white px-6 py-3 rounded-lg font-semibold transition-all" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);" onmouseover="this.style.background='linear-gradient(135deg, #600000 0%, #400000 100%)'" onmouseout="this.style.background='linear-gradient(135deg, #800000 0%, #600000 100%)'">
                Submit
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ── Coupon AJAX ──────────────────────────────────────────────────────────
    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content
                          || '{{ csrf_token() }}';
    const removeBtn    = document.getElementById('remove-coupon-btn');
    const couponInput  = document.getElementById('coupon-input');
    const couponMsg    = document.getElementById('coupon-msg');
    const appliedInfo  = document.getElementById('coupon-applied-info');
    const appliedCode  = document.getElementById('coupon-applied-code');
    const discountRow  = document.getElementById('discount-row');
    const discountAmt  = document.getElementById('discount-amount');
    const finalDisplay = document.getElementById('finalTotalDisplay');
    const hiddenCode   = document.getElementById('coupon-code-input');
    const hiddenDisc   = document.getElementById('discount-amount-input');
    const hiddenApply  = document.getElementById('coupon-applies-to-input');

    // Current page query params (for buy-now subtotal)
    const urlParams = new URLSearchParams(window.location.search);
    const buyNow    = urlParams.get('buy_now');
    const productId = urlParams.get('product_id');
    const quantity  = urlParams.get('quantity') || 1;

    function showMsg(text, type) {
        couponMsg.className = 'mb-2 text-sm flex items-center gap-2 ' +
            (type === 'success' ? 'text-green-600' : 'text-red-600');
        couponMsg.innerHTML = text;
        couponMsg.classList.remove('hidden');
        setTimeout(() => couponMsg.classList.add('hidden'), 5000);
    }

    function recalcTotal(discount) {
        const subtotalText = document.querySelector('#finalTotalDisplay')?.dataset.subtotal;
        const shippingFee  = parseFloat(document.getElementById('shippingFeeInput')?.value || 0);
        const subtotal     = parseFloat(subtotalText || 0);
        if (isNaN(subtotal)) return;
        const newTotal = Math.max(0, subtotal + shippingFee - discount);
        if (finalDisplay) finalDisplay.textContent = '₱' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        updatePaymentPlanSummary();
    }

    async function removeCoupon() {
        try {
            await fetch('{{ route("cart.coupon.remove") }}', {
                method:  'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
        } catch(e) {}
        if (couponInput) couponInput.value = '';
        if (appliedInfo) appliedInfo.classList.add('hidden');
        if (discountRow) discountRow.classList.add('hidden');
        if (hiddenCode)  hiddenCode.value = '';
        if (hiddenDisc)  hiddenDisc.value = 0;
        if (hiddenApply) hiddenApply.value = '';
        recalcTotal(0);
        const rb2 = document.getElementById('remove-coupon-btn');
        if (rb2) rb2.remove();
        // Reset chip styles
        document.querySelectorAll('.coupon-chip').forEach(c => {
            c.disabled = false;
            c.classList.remove('opacity-50', 'bg-amber-500', 'text-white', 'border-amber-600', 'ring-2', 'ring-amber-500');
        });
        showMsg('Coupon removed.', 'success');
    }

    if (removeBtn) removeBtn.addEventListener('click', removeCoupon);

    // Store subtotal in dataset for recalc
    if (finalDisplay) {
        const shippingFee = parseFloat(document.getElementById('shippingFeeInput')?.value || 0);
        const appliedDiscount = parseFloat('{{ $discount ?? 0 }}');
        const finalVal = parseFloat('{{ $finalTotal ?? 0 }}');
        finalDisplay.dataset.subtotal = (finalVal - shippingFee + appliedDiscount).toFixed(2);
    }

    window.applyAvailableCoupon = async function applyAvailableCoupon(code) {
        // Highlight the clicked chip and dim others
        document.querySelectorAll('.coupon-chip').forEach(c => {
            c.classList.toggle('ring-2', c.dataset.code === code);
            c.classList.toggle('ring-amber-500', c.dataset.code === code);
            c.classList.toggle('opacity-50', c.dataset.code !== code);
            c.disabled = true;
        });
        try {
            const body = { code };
            body.shipping_fee = parseFloat(document.getElementById('shippingFeeInput')?.value || 0);
            const urlParams2 = new URLSearchParams(window.location.search);
            const bn = urlParams2.get('buy_now'), pid = urlParams2.get('product_id'), vid = urlParams2.get('variant_id'), qty = urlParams2.get('quantity') || 1;
            if (bn) { body.buy_now = 1; body.product_id = pid; body.quantity = qty; if (vid) body.variant_id = vid; }
            const res = await fetch('{{ route("cart.coupon.apply") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (data.success) {
                showMsg('✓ ' + data.message, 'success');
                if (hiddenCode) hiddenCode.value = code;
                if (hiddenDisc) hiddenDisc.value = data.discount || 0;
                if (hiddenApply) hiddenApply.value = data.applies_to || 'shipping';
                if (couponInput) couponInput.value = code;
                if (appliedCode) appliedCode.textContent = code;
                if (appliedInfo) appliedInfo.classList.remove('hidden');
                if (discountAmt && data.discount > 0) {
                    discountAmt.textContent = '− ₱' + parseFloat(data.discount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    if (discountRow) discountRow.classList.remove('hidden');
                }
                recalcTotal(parseFloat(data.discount || 0));
                // Show inline remove button if not already present
                let rb = document.getElementById('remove-coupon-btn');
                if (!rb) {
                    rb = document.createElement('button');
                    rb.id = 'remove-coupon-btn';
                    rb.type = 'button';
                    rb.className = 'text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-full font-semibold transition-all mt-2';
                    rb.textContent = '✕ Remove';
                    document.querySelector('.coupon-chips-container')?.after(rb);
                    rb.addEventListener('click', removeCoupon);
                }
                // Update chip styles: mark selected
                document.querySelectorAll('.coupon-chip').forEach(c => {
                    const isSelected = c.dataset.code === code;
                    c.disabled = false;
                    c.classList.toggle('bg-amber-500', isSelected);
                    c.classList.toggle('text-white', isSelected);
                    c.classList.toggle('border-amber-600', isSelected);
                    c.classList.remove('opacity-50');
                });
            } else {
                showMsg(data.message || 'Invalid coupon.', 'error');
                document.querySelectorAll('.coupon-chip').forEach(c => { c.disabled = false; c.classList.remove('opacity-50'); });
            }
        } catch(e) {
            showMsg('Could not apply coupon. Please try again.', 'error');
            document.querySelectorAll('.coupon-chip').forEach(c => { c.disabled = false; c.classList.remove('opacity-50'); });
        }
    }
})();
</script>
@endpush