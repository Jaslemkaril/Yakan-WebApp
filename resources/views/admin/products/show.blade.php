@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
<div class="space-y-6">
    <!-- Product Details Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold mb-2">Product Details</h1>
                <p class="text-red-100 text-lg">View complete product information and statistics</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- Product Overview -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="flex flex-col lg:flex-row">
            <!-- Product Image Section -->
            <div class="lg:w-1/3 p-6 bg-gray-50 border-r border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-image text-[#800000] mr-2"></i>
                    Product Image
                </h3>
                
                @if($product->image)
                    <div class="relative group">
                        <img src="{{ $product->image_src }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-80 object-cover rounded-lg shadow-lg group-hover:shadow-xl transition-shadow">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-300"></div>
                        
                        <!-- Image Actions -->
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="window.open('{{ $product->image_src }}', '_blank')" 
                                    class="bg-white/90 backdrop-blur-sm text-gray-700 rounded-lg p-2 hover:bg-white transition-colors shadow-lg">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600 font-medium">{{ $product->image }}</p>
                        <p class="text-xs text-gray-500">Click expand to view full size</p>
                    </div>
                @else
                    <div class="w-full h-80 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500 font-medium">No Image Available</p>
                        <p class="text-xs text-gray-400 mt-1">Upload an image to display product</p>
                    </div>
                @endif
            </div>

            <!-- Product Information Section -->
            <div class="lg:w-2/3 p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h2>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $product->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-2 h-2 mr-1 rounded-full {{ $product->status == 'active' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                {{ ucfirst($product->status) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-hashtag mr-1"></i>ID: {{ $product->id }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex space-x-2">
                        <button onclick="window.print()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-print"></i>
                        </button>
                        <button onclick="copyProductLink()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Product Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Category</div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $product->category?->name ?? 'Uncategorized' }}
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Price</div>
                            <div class="text-2xl font-bold text-green-600">₱{{ number_format($product->price, 2) }}</div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Stock Quantity</div>
                            <div class="flex items-center justify-between">
                                <div class="text-2xl font-bold {{ $product->stock <= 5 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->stock }}
                                </div>
                                <span class="text-sm text-gray-500">units</span>
                            </div>
                            @if($product->stock <= 5 && $product->stock > 0)
                                <div class="mt-2 text-xs text-yellow-600 font-medium">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Low Stock
                                </div>
                            @elseif($product->stock == 0)
                                <div class="mt-2 text-xs text-red-600 font-medium">
                                    <i class="fas fa-times-circle mr-1"></i>Out of Stock
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Total Inventory Value</div>
                            <div class="text-2xl font-bold text-[#800000]">₱{{ number_format($product->price * $product->stock, 2) }}</div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Created Date</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $product->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $product->created_at->diffForHumans() }}</div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Last Updated</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $product->updated_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $product->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-align-left text-[#800000] mr-2"></i>
                        Product Description
                    </h3>
                    <div class="prose prose-sm max-w-none">
                        @if($product->description)
                            <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                        @else
                            <p class="text-gray-400 italic">No description provided for this product.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information Tabs -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('analytics')" id="analytics-tab" class="py-4 px-1 border-b-2 border-[#800000] font-medium text-sm text-[#800000] tab-button">
                    <i class="fas fa-chart-line mr-2"></i>Analytics
                </button>
                <button onclick="showTab('history')" id="history-tab" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 tab-button">
                    <i class="fas fa-history mr-2"></i>History
                </button>
                <button onclick="showTab('related')" id="related-tab" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 tab-button">
                    <i class="fas fa-boxes mr-2"></i>Related Products
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Analytics Tab -->
            <div id="analytics-content" class="tab-content">
                @if($product->inventory)
                    <!-- Inventory Analytics -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-box text-red-600 mr-2"></i>
                            Inventory Analytics
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-red-600 font-medium">Current Stock</p>
                                        <p class="text-2xl font-bold text-red-900">{{ $product->inventory->quantity }}</p>
                                        <p class="text-xs text-red-700 mt-1">{{ $product->inventory->stock_status }}</p>
                                    </div>
                                    <i class="fas fa-warehouse text-red-500 text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-green-600 font-medium">Total Sold</p>
                                        <p class="text-2xl font-bold text-green-900">{{ $product->inventory->total_sold ?? 0 }}</p>
                                        @if($product->inventory->last_sale_at)
                                            <p class="text-xs text-green-700 mt-1">Last: {{ $product->inventory->last_sale_at->format('M d') }}</p>
                                        @endif
                                    </div>
                                    <i class="fas fa-shopping-cart text-green-500 text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-[#800000] font-medium">Revenue</p>
                                        <p class="text-2xl font-bold text-[#800000]">₱{{ number_format($product->inventory->total_revenue ?? 0, 0) }}</p>
                                        <p class="text-xs text-[#800000] mt-1">From sales</p>
                                    </div>
                                    <i class="fas fa-chart-line text-[#800000] text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-lg p-6 border border-amber-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-amber-600 font-medium">Stock Range</p>
                                        <p class="text-2xl font-bold text-amber-900">{{ $product->inventory->min_stock_level }}-{{ $product->inventory->max_stock_level }}</p>
                                        <p class="text-xs text-amber-700 mt-1">Min/Max levels</p>
                                    </div>
                                    <i class="fas fa-sliders-h text-amber-500 text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $product->inventory->stock_status_color }}">
                                    {{ $product->inventory->stock_status }}
                                </span>
                                @if($product->inventory->isLowStock())
                                    <span class="text-sm text-yellow-600">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Low stock alert
                                    </span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.inventory.edit', $product->inventory->id) }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-edit mr-2"></i>Edit Inventory
                                </a>
                                <a href="{{ route('admin.inventory.show', $product->inventory->id) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-eye mr-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- No Inventory -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-yellow-800 mb-2">No Inventory Tracking</h4>
                                <p class="text-sm text-yellow-600">This product doesn't have inventory management enabled</p>
                            </div>
                            <a href="{{ route('admin.inventory.create') }}?product_name={{ urlencode($product->name) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Create Inventory
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Basic Analytics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#800000] font-medium">Potential Revenue</p>
                                <p class="text-2xl font-bold text-[#800000]">₱{{ number_format($product->price * $product->stock, 2) }}</p>
                            </div>
                            <i class="fas fa-dollar-sign text-[#800000] text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-medium">Stock Status</p>
                                <p class="text-2xl font-bold text-green-900">
                                    {{ $product->stock > 10 ? 'Healthy' : ($product->stock > 0 ? 'Low' : 'Out') }}
                                </p>
                            </div>
                            <i class="fas fa-warehouse text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-lg p-6 border border-amber-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-amber-600 font-medium">Product Age</p>
                                <p class="text-2xl font-bold text-amber-900">{{ $product->created_at->diffInDays() }} days</p>
                            </div>
                            <i class="fas fa-calendar text-amber-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Performance Indicators</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Price Range</span>
                            <span class="text-sm font-medium text-gray-900">₱{{ number_format($product->price * 0.8, 2) }} - ₱{{ number_format($product->price * 1.2, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Profit Margin (Est.)</span>
                            <span class="text-sm font-medium text-green-600">30-40%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Recommended Restock</span>
                            <span class="text-sm font-medium text-gray-900">{{ max(20, $product->stock * 2) }} units</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="history-content" class="tab-content hidden">
                <div class="space-y-4">
                    <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-50 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-[#800000] text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Product Created</p>
                            <p class="text-xs text-gray-500">{{ $product->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-50 rounded-full flex items-center justify-center">
                            <i class="fas fa-edit text-[#800000] text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Last Updated</p>
                            <p class="text-xs text-gray-500">{{ $product->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg opacity-50">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-eye text-gray-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">View History</p>
                            <p class="text-xs text-gray-500">No view history available</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products Tab -->
            <div id="related-content" class="tab-content hidden">
                <div class="text-center py-8">
                    <i class="fas fa-boxes text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">Related products feature coming soon</p>
                    <p class="text-sm text-gray-400 mt-2">This will show products in the same category</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <div class="text-sm text-gray-500 mb-4 sm:mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Last updated {{ $product->updated_at->diffForHumans() }}
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.products.edit', $product->id) }}" 
               class="px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-all transform hover:scale-105 shadow-lg">
                <i class="fas fa-edit mr-2"></i>Edit Product
            </a>
            <a href="{{ route('admin.products.index') }}" 
               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Products
            </a>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-[#800000]', 'text-[#800000]');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-[#800000]', 'text-[#800000]');
}

function copyProductLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        // Show success message (you could implement a toast notification here)
        alert('Product link copied to clipboard!');
    });
}

// Initialize with analytics tab shown
document.addEventListener('DOMContentLoaded', function() {
    showTab('analytics');
});
</script>
@endsection
