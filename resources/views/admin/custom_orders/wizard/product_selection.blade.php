@extends('admin.layouts.app')

@section('title', 'Select Product - Admin Custom Order')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Admin Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Create Custom Order</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-red-50 text-[#800000] rounded-full">Step 1: Select Product</span>
                </div>
                <a href="{{ route('admin_custom_orders.create.choice') }}" class="text-gray-500 hover:text-gray-700">
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
                    <span class="ml-3 font-bold text-green-600">Product</span>
                </div>
                <div class="w-16 h-1 bg-green-600 rounded-full"></div>
                <div class="flex items-center group opacity-60">
                    <div class="relative">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                            2
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse opacity-0 group-hover:opacity-100"></div>
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
        
        <!-- Customer Selection -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Customer</h3>
            <form action="{{ route('admin_custom_orders.create.product') }}" method="POST" id="customerForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                        <select name="user_id" id="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                            <option value="">Choose a customer...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Selection -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Select Product to Customize</h3>
                <div class="text-sm text-gray-500">
                    Found <span id="productCount">{{ $products->count() }}</span> products
                </div>
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="productsGrid">
                    @foreach($products as $product)
                        <div class="product-card border-2 border-gray-200 rounded-lg overflow-hidden hover:border-[#800000] hover:shadow-lg transition-all duration-300 cursor-pointer"
                             onclick="selectProduct({{ $product->id }}, '{{ $product->name }}', '{{ $product->category ?? 'other' }}', '{{ $product->price ?? 0 }}')">
                            
                            <div class="relative">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Category Badge -->
                                <div class="absolute top-2 left-2">
                                    <span class="bg-white bg-opacity-90 text-xs px-2 py-1 rounded-full font-semibold text-[#800000]">
                                        {{ ucfirst($product->category ?? 'Other') }}
                                    </span>
                                </div>
                                
                                <!-- Customizable Badge -->
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-semibold">
                                        Customizable
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <h4 class="font-bold text-gray-900 mb-2">{{ $product->name }}</h4>
                                
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ Str::limit($product->description ?? 'Beautiful Yakan textile product perfect for customization', 80) }}
                                </p>
                                
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        @if($product->price > 0)
                                            <span class="text-lg font-bold text-[#800000]">₱{{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-sm text-gray-500">Price on quote</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-1">
                                    <span class="text-xs bg-red-50 text-[#800000] px-2 py-1 rounded-full">Yakan Patterns</span>
                                    <span class="text-xs bg-red-50 text-[#800000] px-2 py-1 rounded-full">Custom Colors</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Products Available</h3>
                    <p class="text-gray-500 mb-4">There are no products available for customization at the moment.</p>
                    <a href="{{ route('admin_custom_orders.create.choice') }}" class="inline-flex items-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Choices
                    </a>
                </div>
            @endif
        </div>

        <!-- Admin Notes -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-[#800000] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-[#800000] mb-2">Admin Product Selection</h3>
                    <div class="text-sm text-[#800000] space-y-2">
                        <p>• Select a customer first before choosing a product</p>
                        <p>• All products shown are available for customization with Yakan patterns</p>
                        <p>• After product selection, you'll be able to choose patterns and colors</p>
                        <p>• You can set quantity and notes in the customization step</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="productSelectionForm" action="{{ route('admin_custom_orders.store.product') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="user_id" id="selectedUserId">
    <input type="hidden" name="product_id" id="selectedProductId">
    <input type="hidden" name="product_name" id="selectedProductName">
    <input type="hidden" name="product_category" id="selectedProductCategory">
    <input type="hidden" name="product_price" id="selectedProductPrice">
</form>

<script>
function selectProduct(productId, productName, productCategory, productPrice) {
    // Check if customer is selected
    const userId = document.getElementById('user_id').value;
    if (!userId) {
        alert('Please select a customer first before choosing a product.');
        return;
    }

    // Show loading state
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingDiv.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex flex-col items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#800000] mb-3"></div>
            <span class="text-gray-700">Preparing customization for ${productName}...</span>
        </div>
    `;
    document.body.appendChild(loadingDiv);

    // Set form values
    document.getElementById('selectedUserId').value = userId;
    document.getElementById('selectedProductId').value = productId;
    document.getElementById('selectedProductName').value = productName;
    document.getElementById('selectedProductCategory').value = productCategory;
    document.getElementById('selectedProductPrice').value = productPrice;

    // Submit form after a short delay for UX
    setTimeout(() => {
        document.getElementById('productSelectionForm').submit();
    }, 800);
}

// Add hover effects
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
@endsection
