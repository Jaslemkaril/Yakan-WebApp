@extends('layouts.app')

@section('title', 'Review Your Order - Custom Order')

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
                <div class="w-8 md:w-20 h-1 rounded-full" style="background-color:#800000;"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">✓</div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Details</span>
                </div>
                <div class="w-8 md:w-20 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="relative">
                        <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">4</div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Header (no decorative banner) -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Review Your Order</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Confirm your custom design and submit your order to our master craftsmen</p>
            </div>
        </div>
    </div>

    <!-- Enhanced Review Content -->
    <div class="container mx-auto px-4 py-8">

        @if ($errors->any())
            <div class="max-w-6xl mx-auto mb-6 bg-maroon-50 border border-maroon-200 text-maroon-800 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Please fix the following before submitting your custom order:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Order Summary -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Enhanced Order Details -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Order Details</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Product Info -->
                        <div class="flex items-start space-x-4 pb-6 border-b-2 border-gray-200">
                            <div class="w-24 h-24 bg-gradient-to-br from-maroon-100 to-maroon-200 rounded-xl flex items-center justify-center shadow-lg">
                                @if(isset($product) && $product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <span class="text-3xl font-bold" style="color:#800000;">{{ isset($product) ? substr($product->name, 0, 1) : 'Y' }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-xl text-gray-900">{{ isset($product) ? $product->name : 'Custom Yakan Fabric' }}</h4>
                                <p class="text-sm text-gray-600 mt-2">{{ isset($product) ? $product->description : 'Premium fabric with authentic Yakan patterns' }}</p>
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-sm text-gray-500 font-medium">Base Price:</span>
                                    <span class="font-bold text-lg" style="color:#800000;">₱{{ isset($product) ? number_format($product->price, 2) : '1,300.00' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fabric Details (from Step 1) -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-5l-2-2H6a2 2 0 00-2 2v6"/>
                                </svg>
                                Fabric Details
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Type:</span><span class="ml-2 text-gray-900">{{ $wizardData['fabric']['type'] ?? '—' }}</span></div>
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Quantity:</span><span class="ml-2 text-gray-900">{{ $wizardData['fabric']['quantity_meters'] ?? '—' }} m</span></div>
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Use:</span><span class="ml-2 text-gray-900">{{ $wizardData['fabric']['intended_use'] ?? '—' }}</span></div>
                            </div>
                        </div>

                        <!-- Patterns Selected -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                </svg>
                                Patterns Applied
                            </h5>
                            
                            @php
                                $patternPreviewUrl = null;
                                $rawPreview = $wizardData['pattern']['preview_image'] ?? null;
                                $storedPath = $wizardData['pattern']['preview_image_path'] ?? null;

                                if (!empty($rawPreview)) {
                                    $patternPreviewUrl = $rawPreview; // already url or data URI
                                } elseif (!empty($storedPath)) {
                                    $patternPreviewUrl = Storage::url($storedPath);
                                }
                            @endphp

                            <!-- Pattern Preview Image -->
                            @if($patternPreviewUrl)
                            <div class="mb-6">
                                <div class="rounded-xl p-4 border-2 shadow-lg" style="background: linear-gradient(to bottom right, #f5e6e8, #e8ccd1); border-color:#8b3a56;">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="text-sm font-semibold text-gray-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Your Customized Pattern
                                        </h6>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Final Preview</span>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 shadow-inner">
                                        <img src="{{ $patternPreviewUrl }}" 
                                             alt="Pattern Preview" 
                                             class="w-full h-auto rounded-lg border-2 shadow-md" style="border-color:#e0b0b0;"
                                             style="max-height: 350px; object-fit: contain;">
                                    </div>
                                    @if(isset($wizardData['pattern']['customization_settings']) && is_array($wizardData['pattern']['customization_settings']))
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border" style="border-color:#d9a3b3;">
                                            <div class="text-xs text-gray-500">Scale</div>
                                            <div class="font-bold" style="color:#8b3a56;">{{ $wizardData['pattern']['customization_settings']['scale'] ?? 1 }}x</div>
                                        </div>
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border" style="border-color:#d9a3b3;">
                                            <div class="text-xs text-gray-500">Rotation</div>
                                            <div class="font-bold" style="color:#8b3a56;">{{ $wizardData['pattern']['customization_settings']['rotation'] ?? 0 }}°</div>
                                        </div>
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border" style="border-color:#d9a3b3;">
                                            <div class="text-xs text-gray-500">Opacity</div>
                                            <div class="font-bold" style="color:#8b3a56;">{{ round(($wizardData['pattern']['customization_settings']['opacity'] ?? 0.85) * 100) }}%</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse($selectedPatterns as $p)
                                    <div class="flex items-center space-x-3 p-3 rounded-lg border" style="background-color:#f5e6e8; border-color:#d9a3b3;">
                                        @php $thumb = optional($p->media->first())->url; @endphp
                                        @if($thumb)
                                            <img src="{{ $thumb }}" alt="{{ $p->name }}" class="w-8 h-8 rounded object-cover"/>
                                        @else
                                            <div class="w-8 h-8 rounded" style="background: linear-gradient(to bottom right, #800000, #a00000);"></div>
                                        @endif
                                        <span class="text-sm font-medium text-gray-700">{{ $p->name }}</span>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No patterns selected.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Customer Information
                            </h5>

                            @php
                                $customerName      = data_get($wizardData, 'details.customer_name') ?? optional(auth()->user())->name;
                                $customerEmail     = data_get($wizardData, 'details.customer_email') ?? optional(auth()->user())->email;
                                $customerPhone     = data_get($wizardData, 'details.customer_phone');
                                $deliveryAddress   = data_get($wizardData, 'details.delivery_address');
                                $deliveryType      = data_get($wizardData, 'details.delivery_type');
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($customerName)
                                    <div class="flex items-center">
                                        <span class="text-gray-500 font-medium w-24">Name</span>
                                        <span class="ml-2 text-gray-900 font-medium">{{ $customerName }}</span>
                                    </div>
                                @endif

                                @if($customerEmail)
                                    <div class="flex items-center">
                                        <span class="text-gray-500 font-medium w-24">Email</span>
                                        <span class="ml-2 text-gray-900 font-medium">{{ $customerEmail }}</span>
                                    </div>
                                @endif

                                @if($customerPhone)
                                    <div class="flex items-center">
                                        <span class="text-gray-500 font-medium w-24">Phone</span>
                                        <span class="ml-2 text-gray-900 font-medium">{{ $customerPhone }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center">
                                    <span class="text-gray-500 font-medium w-24">Delivery</span>
                                    <span class="ml-2 text-gray-900 font-medium">
                                        @if($deliveryType === 'pickup')
                                            Store Pickup
                                        @elseif($deliveryType === 'delivery')
                                            Delivery
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>

                                @if($deliveryAddress)
                                    <div class="md:col-span-2">
                                        <div class="flex items-start">
                                            <span class="text-gray-500 font-medium w-24">Delivery Address</span>
                                            <span class="ml-2 text-gray-900 whitespace-pre-line">{{ $deliveryAddress }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Pricing & Actions -->
            <div class="space-y-8">
                
                <!-- Enhanced Pricing Breakdown -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Pricing Breakdown</h3>
                    </div>
                    
                    @php
                        $patternCount = isset($selectedPatterns) ? $selectedPatterns->count() : 0;
                        $basePrice = isset($product) ? (float) ($product->price ?? 1300) : 1300;
                        $patternFee = $patternCount * 200;
                        $addons = session('wizard.details.addons') ?? [];
                        $addonsTotal = collect($addons)->sum(function($addon) {
                            return $addon == 'priority_production' ? 500 : ($addon == 'gift_wrapping' ? 150 : ($addon == 'extra_patterns' ? 200 : 100));
                        });
                        $finalTotal = $basePrice + $patternFee + $addonsTotal;
                    @endphp
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Base Price</span>
                            <span class="font-medium text-gray-900">₱{{ number_format($basePrice, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Pattern Fees ({{ $patternCount }})</span>
                            <span class="font-medium text-gray-900">₱{{ number_format($patternFee, 2) }}</span>
                        </div>
                        @if(!empty($addons))
                            @foreach($addons as $addon)
                                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                    <span class="text-gray-600 font-medium">{{ $addon == 'priority_production' ? '⚡ Priority Production' : ($addon == 'gift_wrapping' ? '🎁 Gift Wrapping' : ($addon == 'extra_patterns' ? '🎨 Extra Patterns' : '🛡️ Shipping Insurance')) }}</span>
                                    <span class="font-medium text-gray-900">₱{{ number_format($addon == 'priority_production' ? 500 : ($addon == 'gift_wrapping' ? 150 : ($addon == 'extra_patterns' ? 200 : 100)), 2) }}</span>
                                </div>
                            @endforeach
                        @endif
                        <div class="border-t-2 border-gray-200 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Final Total</span>
                                <span class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($finalTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Production Timeline -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Production Timeline</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg" style="background-color:#800000;">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Order Confirmed</p>
                                <p class="text-sm text-gray-600">Today</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg" style="background-color:#a00000;">
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Design Production</p>
                                <p class="text-sm text-gray-600">{{ session('wizard.details.addons') && in_array('priority_production', session('wizard.details.addons')) ? '3-5 days' : '7-10 days' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Quality Check</p>
                                <p class="text-sm text-gray-600">1-2 days</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Shipping</p>
                                <p class="text-sm text-gray-600">2-3 days</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 rounded-xl border-2" style="background: linear-gradient(to right, #f5e6e8, #e8ccd1); border-color:#8b3a56;">
                        <p class="text-sm font-bold" style="color:#8b3a56;">Estimated Delivery</p>
                        <p class="text-xl font-bold" style="color:#8b3a56;">{{ session('wizard.details.addons') && in_array('priority_production', session('wizard.details.addons')) ? date('M d, Y', strtotime('+10 days')) : date('M d, Y', strtotime('+17 days')) }}</p>
                    </div>
                </div>

                <!-- Enhanced Submit Actions -->
                <div class="space-y-4">
                    <form method="POST" action="{{ route('custom_orders.complete.wizard') }}" id="submitOrderForm">
                        @csrf
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mb-2">
                            <div class="flex items-center mb-4">
                                <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M5 8h14M5 16h.01M5 12h.01"/>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900">Finalize Details</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" min="1" id="quantity" name="quantity" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2" style="--tw-ring-color:#800000;" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Option *</label>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <label class="inline-flex items-center px-4 py-3 rounded-lg border-2 text-sm cursor-pointer transition-all duration-200 hover:border-maroon-800 hover:bg-maroon-50" style="border-color:#8b3a56;" id="delivery-option-label">
                                            <input type="radio" name="delivery_type" value="delivery" class="mr-3 w-4 h-4 delivery-radio" checked style="accent-color: #8b3a56;">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <span class="font-medium">Delivery to Address</span>
                                            </div>
                                        </label>
                                        <label class="inline-flex items-center px-4 py-3 rounded-lg border-2 text-sm cursor-pointer transition-all duration-200 hover:border-maroon-800 hover:bg-maroon-50" style="border-color:#8b3a56;" id="pickup-option-label">
                                            <input type="radio" name="delivery_type" value="pickup" class="mr-3 w-4 h-4 delivery-radio" style="accent-color: #8b3a56;">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <span class="font-medium">Store Pickup</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Delivery Address Selection -->
                                <div id="delivery-address-section">
                                    @if($userAddresses->count() > 0)
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Select Delivery Address *
                                        </label>
                                        <div class="space-y-2 mb-4">
                                            @foreach($userAddresses as $address)
                                                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-maroon-400" style="border-color: {{ $address->id === ($defaultAddress->id ?? null) ? '#8b3a56' : '#e5e7eb' }}; background-color: {{ $address->id === ($defaultAddress->id ?? null) ? '#f5e6e8' : 'white' }};">
                                                    <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1 mr-3" style="accent-color: #8b3a56;" {{ $address->id === ($defaultAddress->id ?? null) ? 'checked' : '' }} required />
                                                    <div class="flex-1">
                                                        <p class="font-bold text-gray-900">{{ $address->street_name }}, {{ $address->barangay }}</p>
                                                        <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->province }} {{ $address->zip_code }}</p>
                                                        @if($address->landmark)
                                                            <p class="text-xs text-gray-500 mt-1">Landmark: {{ $address->landmark }}</p>
                                                        @endif
                                                        @if($address->is_default)
                                                            <span class="inline-block mt-2 px-2 py-1 bg-maroon-100 text-maroon-700 text-xs font-bold rounded">Default Address</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="text-xs text-gray-500 mb-4">
                                            <a href="{{ route('addresses.index') }}" class="text-maroon-600 hover:text-maroon-700 font-semibold">Manage your addresses</a>
                                        </p>
                                    @else
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Delivery Address *
                                            <span class="text-xs text-gray-500 font-normal">(Please provide complete address for delivery)</span>
                                        </label>
                                        <div class="space-y-3">
                                            <input type="text" name="delivery_house" id="delivery_house" placeholder="House / Unit / Building No. *" value="{{ data_get($wizardData, 'details.delivery_house', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                            <input type="text" name="delivery_street" id="delivery_street" placeholder="Street Name *" value="{{ data_get($wizardData, 'details.delivery_street', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <input type="text" name="delivery_barangay" id="delivery_barangay" placeholder="Barangay *" value="{{ data_get($wizardData, 'details.delivery_barangay', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                                <input type="text" name="delivery_city" id="delivery_city" placeholder="City / Municipality *" value="{{ data_get($wizardData, 'details.delivery_city', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <input type="text" name="delivery_province" id="delivery_province" placeholder="Province *" value="{{ data_get($wizardData, 'details.delivery_province', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                                <input type="text" name="delivery_zip" id="delivery_zip" placeholder="ZIP Code (optional)" value="{{ data_get($wizardData, 'details.delivery_zip', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" />
                                            </div>
                                            <input type="text" name="delivery_landmark" id="delivery_landmark" placeholder="Landmark (e.g., near SM Mall, beside gas station)" value="{{ data_get($wizardData, 'details.delivery_landmark', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" />
                                            <div class="bg-maroon-50 border border-maroon-200 rounded-lg p-3">
                                                <p class="text-xs text-maroon-800 flex items-start">
                                                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color: #8b3a56;">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                    </svg>
                                                    Please provide as much detail as possible so our courier can find your location easily. Landmark is very helpful!
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Store Pickup Information -->
                                <div id="pickup-info-section" class="hidden">
                                    <div class="bg-gradient-to-br from-maroon-50 to-maroon-100 border-2 border-maroon-200 rounded-xl p-6">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-maroon-900 mb-2 text-lg">Store Pickup Location</h4>
                                                <p class="text-sm text-maroon-800 mb-3">Pick up your order at our Yakan weaving center:</p>
                                                <div class="bg-white rounded-lg p-4 border border-maroon-300">
                                                    <p class="font-semibold text-gray-900">Tuwas Yakan Weaving Center</p>
                                                    <p class="text-sm text-gray-700 mt-2">
                                                        123 Yakan Street, Barangay Tulay<br>
                                                        Zamboanga City, Philippines 7000
                                                    </p>
                                                    <p class="text-xs text-gray-600 mt-3">
                                                        <strong>Operating Hours:</strong><br>
                                                        Monday - Saturday: 8:00 AM - 6:00 PM<br>
                                                        Sunday: Closed
                                                    </p>
                                                    <p class="text-xs text-gray-600 mt-2">
                                                        <strong>Contact:</strong> (062) 123-4567
                                                    </p>
                                                </div>
                                                <p class="text-xs text-maroon-700 mt-3 flex items-start">
                                                    <svg class="w-4 h-4 mr-1 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" style="color: #8b3a56;">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                    </svg>
                                                    We will notify you when your order is ready for pickup. Please bring a valid ID.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="specifications" class="block text-sm font-medium text-gray-700 mb-1">Special Requests / Notes</label>
                                    <textarea id="specifications" name="specifications" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2" style="--tw-ring-color:#800000;" placeholder="Tell us any specific requests (e.g., sizing, placement, extra details)"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="submitBtn" class="group relative w-full px-8 py-4 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color:#8b3a56 !important;" onmouseover="this.style.backgroundColor='#7a3350 !important'" onmouseout="this.style.backgroundColor='#8b3a56 !important'">
                            <span class="flex items-center justify-center" id="submitBtnText">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"/>
                                </svg>
                                Submit Custom Order
                            </span>
                            <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-20 transition-opacity duration-300" style="background-color:#8b3a56;"></div>
                        </button>
                    </form>
                    
                    <script>
                    document.getElementById('submitOrderForm').addEventListener('submit', function(e) {
                        const btn = document.getElementById('submitBtn');
                        const btnText = document.getElementById('submitBtnText');
                        btn.disabled = true;
                        btnText.innerHTML = '<svg class="w-6 h-6 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Submitting...';
                    });
                    </script>
                    
                    <button type="button" onclick="window.history.back()" class="group block w-full text-center px-8 py-3 bg-white border-2 text-maroon-700 rounded-xl font-bold hover:bg-maroon-50 transition-all duration-300 transform hover:scale-105" style="border-color:#8b3a56;">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back to Order Details
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load SVG design from session/localStorage
    loadSVGDesignPreview();
    
    // Initialize delivery/pickup toggle
    initDeliveryToggle();
});

function initDeliveryToggle() {
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const deliverySection = document.getElementById('delivery-address-section');
    const pickupSection = document.getElementById('pickup-info-section');
    const deliveryOptionLabel = document.getElementById('delivery-option-label');
    const pickupOptionLabel = document.getElementById('pickup-option-label');
    
    // Get delivery address fields
    const deliveryFields = [
        document.getElementById('delivery_house'),
        document.getElementById('delivery_street'),
        document.getElementById('delivery_barangay'),
        document.getElementById('delivery_city'),
        document.getElementById('delivery_province')
    ];
    
    function toggleDeliveryPickup() {
        const selectedValue = document.querySelector('input[name="delivery_type"]:checked').value;
        
        if (selectedValue === 'delivery') {
            // Show delivery fields, hide pickup info
            deliverySection.classList.remove('hidden');
            pickupSection.classList.add('hidden');
            
            // Make delivery fields required
            deliveryFields.forEach(field => {
                if (field) field.setAttribute('required', 'required');
            });
            
            // Update label styling
            deliveryOptionLabel.classList.add('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            deliveryOptionLabel.classList.remove('border-gray-300');
            pickupOptionLabel.classList.remove('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            pickupOptionLabel.classList.add('border-gray-300');
            
        } else {
            // Show pickup info, hide delivery fields
            deliverySection.classList.add('hidden');
            pickupSection.classList.remove('hidden');
            
            // Remove required from delivery fields
            deliveryFields.forEach(field => {
                if (field) field.removeAttribute('required');
            });
            
            // Update label styling
            pickupOptionLabel.classList.add('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            pickupOptionLabel.classList.remove('border-gray-300');
            deliveryOptionLabel.classList.remove('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            deliveryOptionLabel.classList.add('border-gray-300');
        }
    }
    
    // Add event listeners
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', toggleDeliveryPickup);
    });
    
    // Set initial state
    toggleDeliveryPickup();
}

function loadSVGDesignPreview() {
    // Load saved design from localStorage
    const savedPattern = localStorage.getItem('selectedYakanPattern');
    const savedColor = localStorage.getItem('selectedYakanColor');
    
    if (savedPattern && savedColor) {
        // Update the SVG preview with saved design
        updateSVGPreview(savedPattern, savedColor);
    }
}

function updateSVGPreview(patternType, color) {
    const svgContainer = document.getElementById('svgPreviewContainer');
    if (!svgContainer) return;
    
    // Create SVG based on pattern type and color
    const svgPatterns = {
        'sussuh': `
            <svg width="100%" height="400" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="reviewPattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <path d="M50,10 L90,50 L50,90 L10,50 Z" fill="${color}" stroke="#ffffff" stroke-width="2"/>
                        <path d="M50,30 L70,50 L50,70 L30,50 Z" fill="#FFD700" stroke="#ffffff" stroke-width="1"/>
                        <circle cx="50" cy="50" r="8" fill="${color}"/>
                    </pattern>
                </defs>
                <rect width="600" height="400" fill="url(#reviewPattern)" color="${color}"/>
                <text x="300" y="200" text-anchor="middle" font-size="24" fill="#666" font-family="Arial">Sussuh Diamond Pattern</text>
            </svg>
        `,
        'banga': `
            <svg width="100%" height="400" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="reviewPattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="50" cy="50" r="35" fill="${color}" stroke="#ffffff" stroke-width="2"/>
                        <circle cx="50" cy="50" r="25" fill="#FFD700" stroke="#ffffff" stroke-width="1"/>
                        <circle cx="50" cy="50" r="12" fill="${color}"/>
                    </pattern>
                </defs>
                <rect width="600" height="400" fill="url(#reviewPattern)" color="${color}"/>
                <text x="300" y="200" text-anchor="middle" font-size="24" fill="#666" font-family="Arial">Banga Circle Pattern</text>
            </svg>
        `
    };
    
    svgContainer.innerHTML = svgPatterns[patternType] || svgPatterns['sussuh'];
}

function exportDesign() {
    const previewImg = document.getElementById('reviewPreviewImg');
    if (previewImg) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
            canvas.width = img.naturalWidth || 1200;
            canvas.height = img.naturalHeight || 800;
            ctx.drawImage(img, 0, 0);
            const link = document.createElement('a');
            link.download = 'custom-yakan-design.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showNotification('Design exported successfully!', 'success');
        };
        img.src = previewImg.src;
        return;
    }

    // Fallback: export inline SVG if present
    const svgElement = document.querySelector('#svgPreviewContainer svg');
    if (svgElement) {
        const svgData = new XMLSerializer().serializeToString(svgElement);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.onload = function () {
            canvas.width = 600;
            canvas.height = 400;
            ctx.drawImage(img, 0, 0);
            const link = document.createElement('a');
            link.download = 'custom-yakan-design.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showNotification('Design exported successfully!', 'success');
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
    }
}

function editDesign() {
    // Navigate back to design step based on flow
    @if(isset($product))
        window.location.href = "{{ route('custom_orders.create.product.customize') }}";
    @else
        window.location.href = "{{ route('custom_orders.create.pattern') }}";
    @endif
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        warning: 'bg-yellow-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    notification.classList.add(...colors[type].split(' '));
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.color-circle {
    transition: all 0.3s ease;
}

.color-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.pattern-badge {
    transition: all 0.3s ease;
}

.pattern-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>
@endpush

@endsection
