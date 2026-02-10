@extends('admin.layouts.app')

@section('title', 'Create Custom Order - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Admin Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Create Custom Order</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-red-50 text-[#800000] rounded-full">Admin Mode</span>
                </div>
                <a href="{{ route('admin.custom-orders.index') }}" class="text-gray-500 hover:text-gray-700">
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
                    <div class="relative">
                        <div class="w-8 h-8 bg-[#800000] text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
                            1
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-3 font-bold text-[#800000]">Choose Type</span>
                </div>
                <div class="w-16 h-1 bg-gradient-to-r from-[#800000] to-gray-300 rounded-full"></div>
                <div class="flex items-center group opacity-60">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                        2
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Customize</span>
                </div>
                <div class="w-16 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center group opacity-60">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-[#800000] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Choose Custom Order Type</h2>
                    <p class="text-gray-600 mt-1">Select how you want to create a custom order for your customer</p>
                </div>
            </div>
        </div>

        <!-- Choice Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Product-Based Customization -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden hover:border-[#800000] hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Product Customization</h3>
                            <p class="text-sm text-gray-500">Customize existing products with Yakan patterns</p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Select Product First</h4>
                                <p class="text-sm text-gray-600">Choose from available products in catalog</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Assign Customer</h4>
                                <p class="text-sm text-gray-600">Select which customer this order is for</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Apply Patterns</h4>
                                <p class="text-sm text-gray-600">Customize with authentic Yakan patterns</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-[#800000] mb-2">Perfect for:</h4>
                        <ul class="text-sm text-[#800000] space-y-1">
                            <li>• Customer phone orders</li>
                            <li>• In-person customizations</li>
                            <li>• Business bulk orders</li>
                            <li>• Gift orders</li>
                        </ul>
                    </div>

                    <button onclick="selectChoice('product')" class="w-full bg-[#800000] text-white py-3 px-6 rounded-lg font-semibold hover:bg-[#600000] transition-colors duration-200 flex items-center justify-center">
                        <span>Start Product Customization</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Fabric-First Customization -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden hover:border-[#800000] hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Fabric Design First</h3>
                            <p class="text-sm text-gray-500">Design patterns first, then apply to fabric</p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Choose Fabric Type</h4>
                                <p class="text-sm text-gray-600">Select from premium fabrics (Cotton, Silk, etc.)</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Design Patterns</h4>
                                <p class="text-sm text-gray-600">Create custom Yakan patterns with pixel-grid effects</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#800000] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-900">Assign Customer</h4>
                                <p class="text-sm text-gray-600">Select customer and specify fabric quantity</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-[#800000] mb-2">Perfect for:</h4>
                        <ul class="text-sm text-[#800000] space-y-1">
                            <li>• Custom fabric orders</li>
                            <li>• Design projects</li>
                            <li>• Bulk fabric requests</li>
                            <li>• Creative commissions</li>
                        </ul>
                    </div>

                    <button onclick="selectChoice('fabric')" class="w-full bg-[#800000] text-white py-3 px-6 rounded-lg font-semibold hover:bg-[#600000] transition-colors duration-200 flex items-center justify-center">
                        <span>Start Fabric Design</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Admin Help Section -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Admin Custom Order Creation</h3>
                    <div class="text-sm text-yellow-700 space-y-2">
                        <p>As an admin, you can create custom orders on behalf of customers. This is useful for:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Phone orders and in-person requests</li>
                            <li>Customer service customizations</li>
                            <li>Bulk business orders</li>
                            <li>Creating sample orders for demonstrations</li>
                        </ul>
                        <p class="mt-2 font-semibold">All admin-created orders will be marked as "Admin Created" and tracked with your admin ID.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectChoice(choice) {
    // Show loading state
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingDiv.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#800000] mr-3"></div>
            <span class="text-gray-700">Loading customization options...</span>
        </div>
    `;
    document.body.appendChild(loadingDiv);

    // Redirect based on choice
    setTimeout(() => {
        if (choice === 'product') {
            window.location.href = '{{ route("admin.custom-orders.create.product") }}';
        } else if (choice === 'fabric') {
            window.location.href = '{{ route("admin.custom-orders.create.fabric") }}';
        }
    }, 500);
}
</script>
@endsection
