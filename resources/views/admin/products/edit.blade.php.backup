@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="space-y-6">
    <!-- Edit Product Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Edit Product</h1>
                <p class="text-purple-100 text-lg">Modify product information and details</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.products.show', $product->id) }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-eye mr-2"></i>View Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- Product Edit Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                        Basic Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Product Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $product->name) }}" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                   placeholder="Enter product name">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <select id="category_id" 
                                        name="category_id" 
                                        required
                                        class="flex-1 px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-colors"
                                        style="border-color: #800000;">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="toggleNewCategoryEdit()" 
                                    class="px-4 py-2 text-white rounded hover:opacity-90 transition-colors whitespace-nowrap"
                                    style="background-color: #800000;">
                                    <i class="fas fa-plus mr-1"></i> New
                                </button>
                            </div>
                            
                            <!-- New Category Input -->
                            <div id="newCategoryDivEdit" class="mt-3 hidden">
                                <div class="p-4 border-2 rounded-lg" style="border-color: #800000; background-color: #fff5f5;">
                                    <label class="block font-medium text-gray-700 mb-2">New Category Name</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="newCategoryInputEdit" placeholder="Enter category name"
                                            class="border rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2"
                                            style="border-color: #800000;">
                                        <button type="button" onclick="addNewCategoryEdit()" 
                                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                                            <i class="fas fa-check"></i> Add
                                        </button>
                                        <button type="button" onclick="toggleNewCategoryEdit()" 
                                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Available Categories -->
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Available Categories:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($categories as $category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white cursor-pointer hover:opacity-80 transition-opacity {{ old('category_id', $product->category_id) == $category->id ? 'ring-2 ring-offset-2 ring-yellow-400' : '' }}"
                                              style="background-color: #800000;"
                                              onclick="document.getElementById('category_id').value='{{ $category->id }}'">
                                            {{ $category->name }}
                                            @if(old('category_id', $product->category_id) == $category->id)
                                                <i class="fas fa-check ml-1"></i>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <script>
                        function toggleNewCategoryEdit() {
                            const div = document.getElementById('newCategoryDivEdit');
                            const input = document.getElementById('newCategoryInputEdit');
                            div.classList.toggle('hidden');
                            if (!div.classList.contains('hidden')) {
                                input.focus();
                            } else {
                                input.value = '';
                            }
                        }

                        function addNewCategoryEdit() {
                            const input = document.getElementById('newCategoryInputEdit');
                            const categoryName = input.value.trim();
                            
                            if (!categoryName) {
                                alert('Please enter a category name');
                                return;
                            }

                            fetch('{{ route("admin.categories.store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: categoryName })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const select = document.getElementById('category_id');
                                    const option = new Option(data.category.name, data.category.id, true, true);
                                    select.add(option);
                                    
                                    alert('Category "' + data.category.name + '" created successfully!');
                                    input.value = '';
                                    toggleNewCategoryEdit();
                                    location.reload();
                                } else {
                                    alert('Error: ' + (data.message || 'Failed to create category'));
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Failed to create category. Please try again.');
                            });
                        }

                        document.getElementById('newCategoryInputEdit')?.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                addNewCategoryEdit();
                            }
                        });
                        </script>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors resize-none"
                                  placeholder="Enter product description...">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-tag text-purple-600 mr-2"></i>
                        Pricing & Inventory
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Price (₱) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', $product->price) }}" 
                                       step="0.01" 
                                       min="0"
                                       required
                                       class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                       placeholder="0.00">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Quantity <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="stock" 
                                       name="stock" 
                                       value="{{ old('stock', $product->stock) }}" 
                                       min="0"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                       placeholder="0">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">units</span>
                            </div>
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" 
                                    name="status" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>
                                    🟢 Active
                                </option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>
                                    🔴 Inactive
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Stock Alert -->
                    <div id="stockAlert" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Low Stock Alert</p>
                                <p class="text-xs text-yellow-700 mt-1">Consider restocking this item soon.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Image -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-image text-purple-600 mr-2"></i>
                        Product Image
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Current Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                            <div class="flex items-center space-x-4">
                                @if($product->image)
                                    <div class="relative group">
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm group-hover:shadow-md transition-shadow">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-colors"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-700 font-medium">{{ $product->image }}</p>
                                        <p class="text-xs text-gray-500">Current product image</p>
                                    </div>
                                @else
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-700 font-medium">No image uploaded</p>
                                        <p class="text-xs text-gray-500">Upload an image to display product</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- New Image Upload -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload New Image
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to keep current image</p>
                            </div>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Image Preview</label>
                            <div class="relative inline-block">
                                <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-purple-200 shadow-md">
                                <button type="button" id="removeImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Management -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-box text-red-600 mr-2"></i>
                        Inventory Management
                    </h3>
                    
                    @if($product->inventory)
                        <!-- Existing Inventory -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-medium text-green-800">Inventory Record Found</h4>
                                    <p class="text-sm text-green-600">This product has inventory tracking enabled</p>
                                </div>
                                <a href="{{ route('admin.inventory.edit', $product->inventory->id) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-edit mr-2"></i>Edit Inventory
                                </a>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-600">Current Stock</div>
                                    <div class="text-lg font-semibold {{ $product->inventory->isLowStock() ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $product->inventory->quantity }}
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-600">Min/Max Level</div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $product->inventory->min_stock_level }} / {{ $product->inventory->max_stock_level }}
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-600">Total Sold</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product->inventory->total_sold ?? 0 }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-600">Revenue</div>
                                    <div class="text-sm font-medium text-gray-900">₱{{ number_format($product->inventory->total_revenue ?? 0, 2) }}</div>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex items-center gap-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->inventory->stock_status_color }}">
                                    {{ $product->inventory->stock_status }}
                                </span>
                                @if($product->inventory->last_sale_at)
                                    <span class="text-xs text-gray-500">
                                        Last sale: {{ $product->inventory->last_sale_at->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- No Inventory - Create One -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-medium text-yellow-800">No Inventory Record</h4>
                                    <p class="text-sm text-yellow-600">Enable inventory tracking for this product</p>
                                </div>
                                <a href="{{ route('admin.inventory.create') }}?product_name={{ urlencode($product->name) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Inventory
                                </a>
                            </div>
                            
                            <div class="text-sm text-yellow-700">
                                <i class="fas fa-info-circle mr-1"></i>
                                Creating an inventory record will allow you to track stock levels, sales, and automatically manage inventory when orders are completed.
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Product Stats -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-purple-600 mr-2"></i>
                        Product Statistics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-sm text-gray-600">Product ID</div>
                            <div class="text-lg font-semibold text-gray-900">#{{ $product->id }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-sm text-gray-600">Created</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $product->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-sm text-gray-600">Last Updated</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $product->updated_at->format('M d, Y') }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-sm text-gray-600">Total Value</div>
                            <div class="text-lg font-semibold text-green-600">₱{{ number_format($product->price * $product->stock, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Last updated {{ $product->updated_at->diffForHumans() }}
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.products.index') }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-save mr-2"></i>Update Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stock alert
    const stockInput = document.getElementById('stock');
    const stockAlert = document.getElementById('stockAlert');
    
    function checkStock() {
        const stock = parseInt(stockInput.value);
        if (stock <= 5 && stock > 0) {
            stockAlert.classList.remove('hidden');
        } else {
            stockAlert.classList.add('hidden');
        }
    }
    
    stockInput.addEventListener('input', checkStock);
    checkStock(); // Check on load
    
    // Image preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImage = document.getElementById('removeImage');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
    
    removeImage.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        previewImg.src = '';
    });
    
    // Format price input
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
});
</script>
@endsection
