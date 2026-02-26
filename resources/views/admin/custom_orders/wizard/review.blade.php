@extends('admin.layouts.app')

@section('title', 'Review Custom Order - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Admin Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Create Custom Order</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Step 3: Review & Create</span>
                </div>
                <a href="{{ route('admin.custom_orders.create.choice') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center space-x-6">
                <div class="flex items-center group">
                    <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
                        ✓
                    </div>
                    <span class="ml-3 font-bold text-green-600">{{ isset($wizardData['product']) ? 'Product' : 'Fabric' }}</span>
                </div>
                <div class="w-16 h-1 bg-green-600 rounded-full"></div>
                <div class="flex items-center group">
                    <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
                        ✓
                    </div>
                    <span class="ml-3 font-bold text-green-600">Design</span>
                </div>
                <div class="w-16 h-1 bg-green-600 rounded-full"></div>
                <div class="flex items-center group">
                    <div class="relative">
                        <div class="w-8 h-8 bg-gradient-to-r from-[#800000] to-[#600000] text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg animate-pulse">
                            3
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-3 font-bold text-green-600">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Order Summary</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Customer Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-[#800000] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Customer Information
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            @php
                                // Build full name from user's account
                                $fullName = $user->name;
                                if ($user->first_name && $user->last_name) {
                                    $fullName = trim($user->first_name . ' ' . ($user->middle_initial ? $user->middle_initial . '. ' : '') . $user->last_name);
                                }
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium">{{ $fullName }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>
                            @if(isset($defaultAddress) && $defaultAddress)
                                @if($defaultAddress->phone_number)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Phone:</span>
                                        <span class="font-medium">{{ $defaultAddress->phone_number }}</span>
                                    </div>
                                @endif
                                <div class="pt-3 mt-3 border-t border-gray-200">
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 text-[#800000] mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <span class="font-semibold text-gray-700 text-sm">{{ $defaultAddress->label ?? 'Default Address' }}</span>
                                                @if($defaultAddress->is_default)
                                                    <span class="ml-2 px-2 py-0.5 bg-red-50 text-[#800000] text-xs font-medium rounded-full">Default</span>
                                                @endif
                                            </div>
                                            @if($defaultAddress->full_name)
                                                <p class="text-sm text-gray-700 font-medium">{{ $defaultAddress->full_name }}</p>
                                            @endif
                                            <p class="text-sm text-gray-600 mt-1">{{ $defaultAddress->formatted_address }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Type Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        @if(isset($wizardData['product']))
                            <svg class="w-5 h-5 text-[#800000] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Product Information
                        @else
                            <svg class="w-5 h-5 text-[#800000] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            Fabric Information
                        @endif
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @if(isset($wizardData['product']))
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Product:</span>
                                    <span class="font-medium">{{ $wizardData['product']['name'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Category:</span>
                                    <span class="font-medium">{{ ucfirst($wizardData['product']['category'] ?? 'Other') }}</span>
                                </div>
                                @if($wizardData['product']['price'] > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Base Price:</span>
                                        <span class="font-medium">₱{{ number_format($wizardData['product']['price'], 2) }}</span>
                                    </div>
                                @endif
                                @if(isset($wizardData['quantity']))
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Quantity:</span>
                                        <span class="font-medium">{{ $wizardData['quantity'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fabric Type:</span>
                                    <span class="font-medium">{{ $wizardData['fabric']['type'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Quantity:</span>
                                    <span class="font-medium">{{ $wizardData['fabric']['quantity_meters'] }} meters</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Intended Use:</span>
                                    <span class="font-medium">{{ ucfirst($wizardData['fabric']['intended_use']) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Design Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-[#800000] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Design Specifications
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Pattern Selection</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-3" style="background-color: {{ $wizardData['colors'][0] ?? '#B22222' }}"></div>
                            <span class="font-medium">{{ ucfirst($wizardData['pattern'] ?? 'Unknown') }} Pattern</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            Primary Color: {{ $wizardData['colors'][0] ?? 'Not specified' }}
                        </div>
                        @if(isset($wizardData['colors'][1]))
                            <div class="text-sm text-gray-600">
                                Accent Color: {{ $wizardData['colors'][1] }}
                            </div>
                        @endif
                    </div>
                </div>
                
                @if(isset($wizardData['estimated_price']) && $wizardData['estimated_price'] > 0)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Pricing</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimated Price:</span>
                                <span class="font-bold text-lg text-[#800000]">₱{{ number_format($wizardData['estimated_price'], 2) }}</span>
                            </div>
                            <div class="text-xs text-gray-500">Final price will be confirmed after review</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Additional Notes -->
        @if(isset($wizardData['notes']) && !empty($wizardData['notes']))
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-gray-700">{{ $wizardData['notes'] }}</p>
                </div>
            </div>
        @endif

        <!-- Admin Confirmation -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-green-900 mb-2">Ready to Create Order</h3>
                    <div class="text-sm text-green-700 space-y-2">
                        <p>• This order will be created as "Admin Created" and assigned to you</p>
                        <p>• The customer will receive notification of their new custom order</p>
                        <p>• Order status will be set to "Pending" awaiting your review</p>
                        <p>• You can modify pricing and details after creation if needed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <form action="{{ route('admin.custom_orders.store') }}" method="POST" class="flex justify-between items-center">
            @csrf
            <div class="flex space-x-4">
                <a href="{{ isset($wizardData['product']) ? route('admin.custom_orders.create.product.customize') : route('admin.custom_orders.create.pattern') }}" 
                   class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                    ← Back to Design
                </a>
                <a href="{{ route('admin.custom_orders.create.choice') }}" 
                   class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium">
                    Cancel
                </a>
            </div>
            
            <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold flex items-center text-lg">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create Custom Order
            </button>
        </form>
    </div>
</div>
@endsection
