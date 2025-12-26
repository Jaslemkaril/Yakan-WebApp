@extends('layouts.app')

@section('title', 'Order Details - Custom Order')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-maroon-50 via-white to-maroon-50">
    <!-- Enhanced Progress Bar -->
    <div class="bg-white shadow-lg border-b-2" style="border-bottom-color:#e0b0b0;">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-center space-x-4 md:space-x-6">
                <div class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">✓</div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Fabric</span>
                </div>
                <div class="w-8 md:w-20 h-1 rounded-full" style="background-color:#800000;"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">✓</div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Pattern</span>
                </div>
                <div class="w-8 md:w-20 h-1 rounded-full" style="background: linear-gradient(to right, #800000, #a0a0a0);"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="relative">
                        <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">3</div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Details</span>
                </div>
                <div class="w-8 md:w-20 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center group cursor-pointer opacity-60">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 group-hover:scale-110">4</div>
                    <span class="ml-2 md:ml-3 font-medium text-xs md:text-base text-gray-500">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Header -->
    <div class="bg-white border-b-2" style="border-bottom-color:#e0b0b0;">
        <div class="absolute inset-0 opacity-5">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="yakanHeaderPattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="50" cy="50" r="35" fill="#8B0000" stroke="#ffffff" stroke-width="1"/>
                        <circle cx="50" cy="50" r="25" fill="#FFD700" stroke="#ffffff" stroke-width="0.5"/>
                        <circle cx="50" cy="50" r="12" fill="#8B0000"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#yakanHeaderPattern)"/>
            </svg>
        </div>
        <div class="container mx-auto px-4 py-12 relative">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4" style="color:#800000;">Order Details</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Tell us about your custom order requirements and preferences</p>
            </div>
        </div>
    </div>

    <!-- Enhanced Order Form -->
    <div class="container mx-auto px-4 py-8">
        <form id="orderDetailsForm" method="POST" action="{{ route('custom_orders.store.step3') }}" class="max-w-4xl mx-auto">
            @csrf
            
            <!-- Order Information Section -->
            <div class="bg-white rounded-2xl shadow-xl border-2 p-8 mb-8" style="border-color:#e0b0b0;">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background-color:#800000;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Order Information</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Order Name -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Order Name <span style="color:#800000;">*</span>
                        </label>
                        <input type="text" name="order_name" required
                               class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300"
                               style="border-color:#e0b0b0;"
                               placeholder="Give your order a memorable name">
                    </div>

                    <!-- Size -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Size <span style="color:#800000;">*</span>
                        </label>
                        <select name="size" required class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;">
                            <option value="">Select size</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Large">Large</option>
                            <option value="Extra Large">Extra Large</option>
                        </select>
                    </div>




                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="bg-white rounded-2xl shadow-xl border-2 p-8 mb-8" style="border-color:#e0b0b0;">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background-color:#800000;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Contact Information</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email
                        </label>
                        <input type="email" name="customer_email" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="your-email@example.com">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948-.684l1.498-4.493a1 1 0 011.502-.684l1.498 4.493a1 1 0 00.948.684H19a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                            </svg>
                            Phone Number
                        </label>
                        <input type="tel" name="customer_phone" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="09XXXXXXXXX">
                    </div>
                </div>
            </div>

            <!-- Delivery Options Section -->
            <div class="bg-white rounded-2xl shadow-xl border-2 p-8 mb-8" style="border-color:#e0b0b0;">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background-color:#800000;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Delivery Options</h3>
                </div>

                <!-- Delivery Type -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Delivery Type <span style="color:#800000;">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex-1 flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all duration-300" style="border-color:#e0b0b0;">
                            <input type="radio" name="delivery_type" value="delivery" class="w-4 h-4" style="accent-color:#800000;" checked required>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Delivery to Address</p>
                                <p class="text-sm text-gray-600">We'll deliver to your location</p>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all duration-300" style="border-color:#e0b0b0;">
                            <input type="radio" name="delivery_type" value="pickup" class="w-4 h-4" style="accent-color:#800000;" required>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Store Pickup</p>
                                <p class="text-sm text-gray-600">Pick up at our store</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Delivery Address Selection -->
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2.121 2.121 0 01-4.242 0m9.242 0H15"/>
                        </svg>
                        Delivery Address <span style="color:#800000;">*</span>
                    </label>

                    @php
                        $userAddresses = auth()->user()->addresses()->get();
                        $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();
                    @endphp

                    @if($userAddresses->count() > 0)
                        <div class="space-y-3 mb-4">
                            @foreach($userAddresses as $address)
                                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all duration-300 hover:shadow-md" style="border-color:#e0b0b0; {{ $address->id === ($defaultAddress->id ?? null) ? 'background-color:#f5e6e8;' : '' }}">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1 mr-3" style="accent-color:#800000;" {{ $address->id === ($defaultAddress->id ?? null) ? 'checked' : '' }} required />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-bold text-gray-900">{{ $address->full_name }}</p>
                                            @if($address->is_default)
                                                <span class="px-2 py-1 text-xs font-bold text-white rounded" style="background-color:#800000;">★ Default</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-700">{{ $address->street }}, {{ $address->barangay }}</p>
                                        <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                                        <p class="text-sm text-gray-600 mt-1">📞 {{ $address->phone_number }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <a href="{{ route('addresses.index') }}" class="inline-flex items-center text-sm font-semibold transition-colors duration-300" style="color:#800000;">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add or Manage Addresses
                        </a>
                    @else
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800 mb-3">No saved addresses found. Please add one first.</p>
                            <a href="{{ route('addresses.create') }}" class="inline-flex items-center px-4 py-2 text-white rounded-lg font-semibold transition-all duration-300 transform hover:scale-105" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Address
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Description Section -->
            <div class="bg-white rounded-2xl shadow-xl border-2 p-8 mb-8" style="border-color:#e0b0b0;">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background-color:#800000;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Order Description</h3>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-3">Special Instructions</label>
                    <textarea name="special_instructions" rows="4" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="Any special requests or notes for your order..."></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 mb-8">
                <button type="submit" class="flex-1 group relative px-8 py-4 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Continue to Review
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bg-maroon-50 { background-color: #faf5f5; }
    .bg-maroon-100 { background-color: #f5e6e8; }

    form > div {
        animation: slideIn 0.4s ease-out forwards;
    }

    form > div:nth-child(2) { animation-delay: 0.1s; }
    form > div:nth-child(3) { animation-delay: 0.2s; }
    form > div:nth-child(4) { animation-delay: 0.3s; }
    form > div:nth-child(5) { animation-delay: 0.4s; }

    input:focus, select:focus, textarea:focus {
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
    }
</style>
@endsection
