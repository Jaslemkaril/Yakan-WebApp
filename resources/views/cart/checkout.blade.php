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
                <span class="text-red-600">🛒</span>
                Checkout
            </h1>
            <p class="text-gray-600">Review your order and complete payment</p>
        <!-- Hidden checkout form to collect selected payment_method via form attributes -->
<form id="checkout-form" action="{{ route('cart.checkout.process') }}" method="POST" class="hidden">
    @csrf
    <!-- radios above are bound here via form="checkout-form" -->
    <input type="hidden" name="confirm" value="1" />
</form>

</div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Order Items Section -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Delivery Option
                        </h2>

                        <p class="text-sm text-gray-600 mb-3">
                            Choose whether you want your order delivered or picked up at the store.
                        </p>

                        @php $selectedDeliveryType = old('delivery_type', 'delivery'); @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label id="delivery-radio-label" class="delivery-option-label flex items-center p-4 border-2 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all {{ $selectedDeliveryType === 'delivery' ? 'border-red-500 bg-red-50 ring-2 ring-red-200' : 'border-gray-200' }}">
                                <input type="radio" name="delivery_type" value="delivery" form="checkout-form" class="w-5 h-5 text-red-600" {{ $selectedDeliveryType === 'delivery' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                        </svg>
                                        Delivery
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">Send to your address</div>
                                </div>
                            </label>

                            <label id="pickup-radio-label" class="delivery-option-label flex items-center p-4 border-2 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all {{ $selectedDeliveryType === 'pickup' ? 'border-red-500 bg-red-50 ring-2 ring-red-200' : 'border-gray-200' }}">
                                <input type="radio" name="delivery_type" value="pickup" form="checkout-form" class="w-5 h-5 text-red-600" {{ $selectedDeliveryType === 'pickup' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Pickup
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">Pick up from store</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Order Items
                        </h2>
                        
                        <div class="space-y-4">
                            @php $total = 0; @endphp
                            @foreach($cartItems as $item)
                                @php 
                                    $subtotal = $item->quantity * $item->product->price;
                                    $total += $subtotal;
                                @endphp
                                
                                <div class="flex items-start gap-4 py-4 border-b border-gray-100 last:border-0 cart-item-card">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24 bg-white rounded-xl overflow-hidden border border-gray-200 shadow-sm product-image-container">
                                        @if($item->product->image)
                                            <img src="{{ asset('uploads/products/' . $item->product->image) }}" 
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
                                        @method('PATCH')
                                        <input id="update-qty-{{ $item->id }}" name="quantity" type="number" min="1" @if(!is_null($item->product->stock)) max="{{ $item->product->stock }}" @endif value="{{ $item->quantity }}">
                                    </form>
                                    
                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $item->product->name }}</h3>
                                        @if($item->product->description)
                                            <p class="text-sm text-gray-500 mb-2 line-clamp-1">{{ $item->product->description }}</p>
                                        @endif
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 text-sm text-gray-600">
                                            <div class="mb-2 sm:mb-0">
                                                <span class="font-medium">Price:</span>
                                                <span class="ml-1 text-gray-900">₱{{ number_format($item->product->price, 2) }}</span>
                                                <span class="text-gray-400 mx-2">•</span>
                                                <span class="font-medium">Qty:</span>
                                                <span class="ml-1 text-gray-900">{{ $item->quantity }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                    <button type="button" class="px-3 py-1.5 text-gray-700 hover:bg-gray-100 qty-btn qty-minus" data-target="qty-{{ $item->id }}" data-item-id="{{ $item->id }}" data-action="decrease">−</button>
                                                    <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" @if(!is_null($item->product->stock)) max="{{ $item->product->stock }}" @endif value="{{ $item->quantity }}" class="w-12 text-center py-1.5 focus:outline-none" readonly />
                                                    <button type="button" class="px-3 py-1.5 text-gray-700 hover:bg-gray-100 qty-btn qty-plus" data-target="qty-{{ $item->id }}" data-item-id="{{ $item->id }}" data-action="increase">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Subtotal and Remove -->
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="text-lg font-bold text-gray-900 subtotal-price" id="subtotal-{{ $item->id }}">₱{{ number_format($subtotal, 2) }}</div>
                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 flex items-center gap-1 remove-btn">
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

                    <!-- Delivery Address Section (only visible when delivery is selected) -->
                    <div id="delivery-address-section" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Delivery Address
                        </h2>

                        @if($addresses->count() > 0)
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1">
                                    <span class="text-red-500">*</span> Select Saved Address
                                </label>
                                <div class="space-y-3">
                                    @foreach($addresses as $address)
                                        <label class="flex items-start p-4 border-2 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all {{ ($defaultAddress && $defaultAddress->id === $address->id) || old('address_id') == $address->id ? 'border-red-500 bg-red-50 ring-2 ring-red-200' : 'border-gray-200' }}">
                                            <input type="radio" name="address_id" value="{{ $address->id }}" form="checkout-form" 
                                                   {{ ($defaultAddress && $defaultAddress->id === $address->id) || old('address_id') == $address->id ? 'checked' : '' }}
                                                   class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0">
                                            <div class="ml-3 flex-1">
                                                <div class="font-semibold text-gray-900 flex items-center gap-2">
                                                    {{ $address->label }}
                                                    @if($address->is_default)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Default</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <div>{{ $address->full_name }} • {{ $address->phone_number }}</div>
                                                    <div>{{ $address->formatted_address }}</div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <a href="{{ route('addresses.index') }}" class="inline-flex items-center gap-2 mt-4 text-sm text-red-600 hover:text-red-700 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add New Address
                                </a>
                            </div>
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded">
                                <p class="text-sm text-yellow-800 flex items-start gap-2">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>No saved addresses yet. <a href="{{ route('addresses.create') }}" class="font-semibold text-yellow-900 hover:underline">Create one now</a></span>
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

                        <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-6 border-2 border-red-200 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center shadow-md">
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
                                        <span class="text-gray-800"><strong>Address:</strong> 123 Yakan Street, Basilan City, Philippines 7300</span>
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

                    <!-- Customer Notes Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                      class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none">{{ old('customer_notes') }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Maximum 500 characters</p>
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
                            <!-- GCash Payment Option -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all duration-200 group">
                                <input type="radio" name="payment_method" value="online" required class="w-5 h-5 text-red-600 focus:ring-red-500 focus:ring-2" form="checkout-form">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center group-hover:from-blue-200 group-hover:to-blue-300 transition-all">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">GCash</div>
                                            <div class="text-sm text-gray-600">Pay securely online</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-6 h-6 text-green-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>

                            <!-- Bank Transfer Option -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all duration-200 group">
                                <input type="radio" name="payment_method" value="bank_transfer" class="w-5 h-5 text-red-600 focus:ring-red-500 focus:ring-2" form="checkout-form">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-lg flex items-center justify-center group-hover:from-green-200 group-hover:to-green-300 transition-all">
                                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">Bank Transfer</div>
                                            <div class="text-sm text-gray-600">Direct bank payment</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-6 h-6 text-green-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
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
                                <span class="font-bold text-gray-900 order-subtotal text-lg">₱{{ number_format($subtotal ?? $total, 2) }}</span>
                            </div>

                            <!-- Coupon Section -->
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border-2 border-amber-200 shadow-sm">
                                @if(session('success'))
                                    <div class="text-green-600 text-sm mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="text-red-600 text-sm mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form action="{{ route('cart.coupon.apply') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Enter coupon code" value="{{ $appliedCoupon->code ?? '' }}" class="flex-1 min-w-0 border-2 border-amber-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" @if(!empty($appliedCoupon)) disabled @endif>
                                    @if(empty($appliedCoupon))
                                        <button type="submit" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-lg hover:from-red-700 hover:to-red-800 font-bold transition-all shadow-md whitespace-nowrap text-sm">Apply</button>
                                    @else
                                        <button type="submit" formaction="{{ route('cart.coupon.remove') }}" formmethod="POST" onclick="event.preventDefault(); document.getElementById('remove-coupon-form').submit();" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-bold transition-all shadow-md whitespace-nowrap text-sm">Remove</button>
                                    @endif
                                </form>
                                <form id="remove-coupon-form" action="{{ route('cart.coupon.remove') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                                @if(!empty($appliedCoupon))
                                    <div class="text-sm text-amber-900 mt-3 flex items-center gap-2 bg-white rounded-lg p-2">
                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="min-w-0"><strong>Coupon:</strong> {{ $appliedCoupon->code }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Shipping Fee Row -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Shipping Fee</span>
                                <span class="font-bold text-green-600 text-lg">FREE</span>
                            </div>

                            <!-- Tax Row -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Tax</span>
                                <span class="font-bold text-gray-900">₱0.00</span>
                            </div>

                            <!-- Discount Row (if applicable) -->
                            @if(($discount ?? 0) > 0)
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 bg-green-50 px-3 rounded-lg">
                                <span class="text-gray-600 font-medium">Discount</span>
                                <span class="font-bold text-green-600 text-lg">− ₱{{ number_format($discount, 2) }}</span>
                            </div>
                            @endif
                            
                            <!-- Total Amount -->
                            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-4 border-2 border-red-200 mt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-lg font-bold text-gray-900">Total Amount</span>
                                    <span class="text-3xl font-bold text-red-600 order-total">₱{{ number_format($total, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-600 text-right">Inclusive of all taxes</p>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button id="place-order-button" type="button" class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white text-center px-6 py-4 rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl font-bold text-lg mb-3 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Place Order
                        </button>
                        
                        <!-- Back to Cart Button -->
                        <a href="{{ route('cart.index') }}" class="block w-full text-center text-gray-700 hover:text-red-600 px-6 py-3 rounded-xl border-2 border-gray-300 hover:border-red-600 transition-all duration-200 font-bold flex items-center justify-center gap-2">
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

<script>
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
        
        fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ quantity: newQuantity })
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
        
        // Update total amount
        const totalEl = document.querySelector('.order-total');
        if (totalEl && data.total_amount) {
            totalEl.textContent = `₱${parseFloat(data.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        // Update item count
        const itemCountEl = document.querySelector('.subtotal-items');
        if (itemCountEl && data.total_items) {
            itemCountEl.textContent = `Subtotal (${data.total_items} item${data.total_items !== 1 ? 's' : ''})`;
        }
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
    deliveryTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleDeliveryPickup(this.value);
            
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
                    // Check if an address is selected
                    const selectedAddress = document.querySelector('input[name="address_id"]:checked');
                    if (!selectedAddress) {
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
                    checkoutForm.submit();
                }
            }
        });
    }
});
</script>

@endsection