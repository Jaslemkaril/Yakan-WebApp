@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="space-y-6">
    <!-- Inventory Header -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2 flex items-center">
                    <i class="fas fa-warehouse mr-3"></i>
                    Inventory Management
                </h1>
                <p class="text-red-100 text-lg">Monitor and manage your product inventory</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.products.create') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Product
                </a>
                <a href="{{ route('admin.inventory.low-stock') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>Low Stock ({{ $lowStockCount }})
                </a>
                <a href="{{ route('admin.inventory.create') }}" class="bg-white text-red-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Value</p>
                        <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalValue, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lowStockCount }}</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
                    </div>
                    <i class="fas fa-chart-bar text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by product name..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">All Status</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}>Overstock</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Action Legend -->
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs text-gray-600">
                <!-- Inventory Actions Group -->
                <div class="sm:col-span-3 lg:col-span-3">
                    <div class="font-medium text-gray-700 mb-2 sm:mb-1">Inventory Actions:</div>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-box text-red-600"></i>
                            <span>View</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-edit text-blue-600"></i>
                            <span>Edit</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-plus text-green-600"></i>
                            <span>Restock</span>
                        </div>
                    </div>
                </div>
                
                <!-- Product Actions Group -->
                <div class="sm:col-span-3 lg:col-span-3">
                    <div class="font-medium text-gray-700 mb-2 sm:mb-1">Product Actions:</div>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-eye text-purple-600"></i>
                            <span>View</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-tag text-orange-600"></i>
                            <span>Edit</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-plus-circle text-white bg-red-600 px-1 py-0.5 rounded"></i>
                            <span>Create</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min/Max Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        @php
                            $inventory = $product->inventory;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ !$inventory ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('uploads/products/' . $product->image) }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->category->name ?? 'No Category' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($inventory)
                                    <div class="text-sm font-medium {{ $inventory->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $inventory->quantity }}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400 italic">{{ $product->stock ?? 0 }}</div>
                                    <div class="text-xs text-orange-600">No inventory</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($inventory)
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $inventory->min_stock_level }}</span> / 
                                        <span class="font-medium">{{ $inventory->max_stock_level }}</span>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($inventory)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inventory->stock_status_color }}">
                                        {{ $inventory->stock_status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        No Tracking
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($inventory)
                                    <div class="font-medium">{{ $inventory->total_sold ?? 0 }}</div>
                                    @if($inventory->last_sale_at)
                                        <div class="text-xs text-gray-500">{{ $inventory->last_sale_at->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <div class="text-gray-400">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($inventory)
                                    <div class="font-medium">₱{{ number_format($inventory->total_revenue ?? 0, 2) }}</div>
                                @else
                                    <div class="text-gray-400">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    @if($inventory)
                                        <!-- Inventory Actions -->
                                        <a href="{{ route('admin.inventory.show', $inventory) }}" class="text-red-600 hover:text-red-900" title="View Inventory">
                                            <i class="fas fa-box"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $inventory) }}" class="text-blue-600 hover:text-blue-900" title="Edit Inventory">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($inventory->isLowStock())
                                            <button onclick="restockModal({{ $inventory->id }})" class="text-green-600 hover:text-green-900" title="Restock">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Divider -->
                                        <div class="w-px h-4 bg-gray-300"></div>
                                    @else
                                        <!-- Create Inventory Action -->
            
            <!-- Pagination -->
            @if($products->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $products->links() }}
                </div>
            @endif                      
                                        <!-- Divider -->
                                        <div class="w-px h-4 bg-gray-300"></div>
                                    @endif
                                    
                                    <!-- Product Actions -->
                                    <a href="{{ route('products.show', $product->id) }}" class="text-purple-600 hover:text-purple-900" title="View Product">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-orange-600 hover:text-orange-900" title="Edit Product">
                                        <i class="fas fa-tag"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="text-gray-500 font-medium">No products found</p>
                                        <a href="{{ route('admin.products.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                            Add First Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($products->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div id="restockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900">Restock Inventory</h3>
            <form id="restockForm" method="POST" action="#" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="quantity" id="restockQuantity">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Quantity to Add</label>
                    <input type="number" name="restock_amount" min="1" max="1000" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-maroon-600 hover:bg-maroon-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        Restock
                    </button>
                    <button type="button" onclick="closeRestockModal()" class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function restockModal(inventoryId) {
    const modal = document.getElementById('restockModal');
    const form = document.getElementById('restockForm');
    form.action = `/admin/inventory/${inventoryId}/restock`;
    modal.classList.remove('hidden');
}

function closeRestockModal() {
    document.getElementById('restockModal').classList.add('hidden');
}
</script>
@endsection
