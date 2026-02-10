@extends('layouts.admin')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Products Management')

@section('content')
<div class="space-y-6">
    <!-- Products Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold mb-2">Products Management</h1>
                <p class="text-red-100 text-lg">Manage your product catalog and inventory</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button id="bulkDeleteBtn" onclick="confirmBulkDelete()" class="hidden bg-red-500/90 backdrop-blur-sm text-white border border-red-400/30 rounded-lg px-4 py-2 hover:bg-red-600 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Selected (<span id="selectedCount">0</span>)
                </button>
                <button class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export Products
                </button>
                <a href="{{ route('admin.products.create') }}" class="bg-white text-[#800000] px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Product
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-[#800000] hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10l8-4m0 0l-8-4-8 4m8 4v10l-8-4m0-10l8 4"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $products->where('status', 'active')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $products->where('stock', '<=', 10)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v-2m0 0v-2m0 0h2m-2 0h-2m7.5 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $products->where('stock', 0)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-2-2V6a2 2 0 012-2h3.672a2 2 0 012 2v2m0 0h5.094a2 2 0 012 2v2a2 2 0 01-2 2h-5.094m0 0a2 2 0 01-2-2V6a2 2 0 012-2h3.672a2 2 0 012 2v2m0 0h.001"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <form method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    <option value="">All Categories</option>
                    @php
                        $categories = ['Yakan Bags', 'Yakan Fabrics', 'Accessories', 'Home Decor'];
                    @endphp
                    @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="stock" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow card-hover relative" data-product-id="{{ $product->id }}">
                <!-- Checkbox for bulk selection -->
                <div class="absolute top-3 left-3 z-10">
                    <input type="checkbox" 
                           class="product-checkbox w-5 h-5 text-[#800000] bg-white border-gray-300 rounded focus:ring-[#800000] focus:ring-2 cursor-pointer"
                           value="{{ $product->id }}"
                           onchange="updateBulkDeleteButton()">
                </div>
                <div class="aspect-w-16 aspect-h-9 bg-gray-100 relative overflow-hidden rounded-lg h-48">
                    @if($product->image)
                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" 
                             class="w-full h-full object-cover"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f0e9ff%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%236b21a8%22 font-size=%2248%22%3E🧵%3C/text%3E%3C/svg%3E';">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-red-50 to-orange-50 flex flex-col items-center justify-center">
                            <div class="text-4xl mb-2">🧵</div>
                            <div class="text-sm text-gray-600">{{ $product->name }}</div>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @switch($product->status)
                                @case('active')
                                    bg-green-100 text-green-800
                                @break
                                @case('inactive')
                                    bg-red-100 text-red-800
                                @break
                                @default
                                    bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                    
                    <!-- Stock Badge -->
                    @if($product->stock <= 10)
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($product->stock == 0)
                                bg-red-100 text-red-800
                            @else
                                bg-yellow-100 text-yellow-800
                            @endif
                        ">
                            @if($product->stock == 0)
                                Out of Stock
                            @else
                                Low Stock ({{ $product->stock }})
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
                
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-500 mb-2 truncate">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                    
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-lg font-bold text-gray-900">₱{{ number_format($product->price, 2) }}</p>
                            <p class="text-xs text-gray-500">Stock: {{ $product->stock }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">SKU</div>
                            <div class="text-sm font-medium">{{ $product->sku ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.products.show', $product->id) }}" 
                           class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors text-sm">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="{{ route('admin.products.edit', $product->id) }}" 
                           class="flex-1 text-center px-3 py-2 bg-[#800000] text-white rounded hover:bg-[#600000] transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button type="button"
                                onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')" 
                                class="flex-1 px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No products found</p>
                <a href="{{ route('admin.products.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Your First Product
                </a>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($products->hasPages())
        <div class="mt-8">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Product</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <strong id="productName" class="text-gray-900"></strong>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button"
                        id="confirmDeleteBtn"
                        onclick="handleDelete()"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 z-50 transform transition-all duration-300">
    <div class="bg-white rounded-lg shadow-xl border-l-4 p-4 max-w-md">
        <div class="flex items-center">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="ml-3">
                <p id="toastMessage" class="text-sm font-medium"></p>
            </div>
            <button onclick="closeToast()" class="ml-auto flex-shrink-0">
                <i class="fas fa-times text-gray-400 hover:text-gray-600"></i>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let productToDelete = null;
let productsToDelete = [];

// Open delete modal for single product
function confirmDelete(productId, productName) {
    productToDelete = productId;
    productsToDelete = [];
    document.getElementById('productName').textContent = productName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

// Open delete modal for bulk delete
function confirmBulkDelete() {
    if (productsToDelete.length === 0) return;
    productToDelete = null;
    document.getElementById('productName').textContent = `${productsToDelete.length} product(s)`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

// Close modal
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    productToDelete = null;
}

// Main delete handler
function handleDelete() {
    if (productToDelete) {
        // Single delete
        deleteProduct();
    } else if (productsToDelete.length > 0) {
        // Bulk delete
        bulkDeleteProducts();
    }
}

// Delete single product
function deleteProduct() {
    if (!productToDelete) return;
    
    const btn = document.getElementById('confirmDeleteBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    console.log('Deleting product:', productToDelete);
    console.log('CSRF Token:', token);
    
    fetch(`/admin/products/${productToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => {
        console.log('Response status:', r.status);
        return r.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast('Product deleted successfully!', 'success');
            closeDeleteModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message || 'Delete failed', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
            closeDeleteModal();
        }
    })
    .catch(err => {
        console.error('Delete error:', err);
        showToast('Error deleting product: ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
        closeDeleteModal();
    });
    
    closeDeleteModal();
}

// Delete multiple products
function bulkDeleteProducts() {
    if (productsToDelete.length === 0) return;
    
    const btn = document.getElementById('confirmDeleteBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    Promise.all(productsToDelete.map(id =>
        fetch(`/admin/products/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        }).then(r => r.json())
    ))
    .then(results => {
        const success = results.filter(r => r.success).length;
        showToast(`Deleted ${success}/${results.length} products`, 'success');
        setTimeout(() => window.location.reload(), 500);
    })
    .catch(err => {
        console.error(err);
        showToast('Error deleting products', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
    
    closeDeleteModal();
}

// Update bulk delete button
function updateBulkDeleteButton() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const btn = document.getElementById('bulkDeleteBtn');
    const count = document.getElementById('selectedCount');
    
    productsToDelete = Array.from(checked).map(c => c.value);
    
    if (checked.length > 0) {
        btn.classList.remove('hidden');
        count.textContent = checked.length;
    } else {
        btn.classList.add('hidden');
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    const toastContainer = toast.querySelector('div');
    
    // Set message
    toastMessage.textContent = message;
    
    // Set icon and color based on type
    if (type === 'success') {
        toastIcon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
        toastContainer.classList.remove('border-red-500');
        toastContainer.classList.add('border-green-500');
    } else {
        toastIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
        toastContainer.classList.remove('border-green-500');
        toastContainer.classList.add('border-red-500');
    }
    
    // Show toast
    toast.classList.remove('hidden');
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        closeToast();
    }, 3000);
}

function closeToast() {
    document.getElementById('toast').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
