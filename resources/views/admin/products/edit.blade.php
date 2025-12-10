@extends('layouts.admin')
@section('title', 'Edit Product')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white shadow rounded-lg mt-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Edit Product</h2>
        <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Products
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 border border-red-200 bg-red-50 rounded">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Product Name -->
        <div>
            <label class="block font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-600" required>
        </div>

        <!-- Category -->
        <div>
            <label class="block font-medium text-gray-700 mb-2">Category</label>
            <div class="flex gap-2">
                <select name="category_id" id="categorySelect"
                    class="border rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2 focus:ring-red-600"
                    style="border-color: #800000;">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <button type="button" onclick="toggleNewCategory()" 
                    class="px-4 py-2 text-white rounded hover:opacity-90 transition-colors whitespace-nowrap"
                    style="background-color: #800000;">
                    <i class="fas fa-plus mr-1"></i> New
                </button>
            </div>
            
            <!-- New Category Input (Hidden by default) -->
            <div id="newCategoryDiv" class="mt-3 hidden">
                <div class="p-4 border-2 rounded-lg" style="border-color: #800000; background-color: #fff5f5;">
                    <label class="block font-medium text-gray-700 mb-2">New Category Name</label>
                    <div class="flex gap-2">
                        <input type="text" id="newCategoryInput" placeholder="Enter category name"
                            class="border rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2"
                            style="border-color: #800000;">
                        <button type="button" onclick="addNewCategory()" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                            <i class="fas fa-check"></i> Add
                        </button>
                        <button type="button" onclick="toggleNewCategory()" 
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
                              onclick="document.getElementById('categorySelect').value='{{ $category->id }}'">
                            {{ $category->name }}
                            @if(old('category_id', $product->category_id) == $category->id)
                                <i class="fas fa-check ml-1"></i>
                            @endif
                        </span>
                    @endforeach
                    @if($categories->isEmpty())
                        <span class="text-sm text-gray-500 italic">No categories yet. Create one above!</span>
                    @endif
                </div>
            </div>
        </div>

        <script>
        function toggleNewCategory() {
            const div = document.getElementById('newCategoryDiv');
            const input = document.getElementById('newCategoryInput');
            div.classList.toggle('hidden');
            if (!div.classList.contains('hidden')) {
                input.focus();
            } else {
                input.value = '';
            }
        }

        function addNewCategory() {
            const input = document.getElementById('newCategoryInput');
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
                    const select = document.getElementById('categorySelect');
                    const option = new Option(data.category.name, data.category.id, true, true);
                    select.add(option);
                    
                    alert('Category "' + data.category.name + '" created successfully!');
                    input.value = '';
                    toggleNewCategory();
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

        document.getElementById('newCategoryInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addNewCategory();
            }
        });
        </script>

        <!-- Price -->
        <div>
            <label class="block font-medium text-gray-700">Price (₱)</label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-600" step="0.01"
                placeholder="0.00" required>
        </div>

        <!-- Stock -->
        <div>
            <label class="block font-medium text-gray-700">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-600" min="0"
                required>
        </div>

        <!-- Description -->
        <div>
            <label class="block font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-600">{{ old('description', $product->description) }}</textarea>
        </div>

        <!-- Professional Image Upload Section -->
        <div class="border-2 border-dashed rounded-lg p-6" style="border-color: #800000;">
            <label class="block font-bold text-gray-900 mb-4 text-lg">
                <i class="fas fa-images mr-2" style="color: #800000;"></i>Product Images
            </label>
            <p class="text-sm text-gray-600 mb-4">
                Upload up to 4 images. The first image will be the main product image. Recommended size: 800x800px.
            </p>
            
            <!-- Existing Images Display -->
            @php
                $images = is_array($product->all_images) ? $product->all_images : (json_decode($product->all_images, true) ?? []);
            @endphp
            @if(count($images) > 0)
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-blue-900">Current Images ({{ count($images) }})</h4>
                    <button type="button" onclick="deleteAllExistingImages()" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        <i class="fas fa-trash mr-1"></i>Delete All Current Images
                    </button>
                </div>
                <div class="grid grid-cols-5 gap-2" id="existingImagesGrid">
                    @foreach($images as $index => $img)
                    <div class="relative group existing-image" data-image-path="{{ $img['path'] }}">
                        <img src="{{ asset('uploads/products/' . $img['path']) }}" 
                             alt="Product image {{ $index + 1 }}"
                             class="w-full aspect-square object-cover rounded border-2 border-blue-300">
                        <button type="button" onclick="deleteExistingImage('{{ $img['path'] }}', this)" 
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 transition-colors shadow-lg opacity-0 group-hover:opacity-100">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1">
                            @if($index === 0)
                                <span class="text-white text-xs font-bold block">
                                    <i class="fas fa-star"></i> Main
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="delete_images" id="deleteImagesInput" value="">
                <p class="text-xs text-gray-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>Click delete button to remove images. New images will be added to remaining images.
                </p>
            </div>
            @endif
            
            <!-- Image Upload Area -->
            <div id="imageUploadArea" class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-4">
                <!-- Main Image Slot -->
                <div class="image-slot relative aspect-square border-2 rounded-lg overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer group"
                     style="border-color: #800000;"
                     onclick="document.getElementById('mainImageInput').click()">
                    <input type="file" id="mainImageInput" name="images[]" accept="image/*" class="hidden" onchange="handleImageSelect(event, 0)">
                    
                    <div class="preview-container hidden absolute inset-0">
                        <img src="" alt="Preview" class="w-full h-full object-cover">
                        <div class="absolute top-1 right-1">
                            <button type="button" onclick="removeImage(event, 0)" 
                                class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 transition-colors shadow-lg">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1" onclick="event.stopPropagation()">
                            <span class="text-white text-xs font-bold block">
                                <i class="fas fa-star"></i> Main
                            </span>
                        </div>
                    </div>
                    
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-gray-600">
                        <i class="fas fa-camera text-2xl mb-1"></i>
                        <span class="text-xs font-medium">Main</span>
                    </div>
                </div>

                <!-- Additional Image Slots (3 more) -->
                @for ($i = 1; $i < 4; $i++)
                <div class="image-slot relative aspect-square border-2 border-dashed rounded-lg overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer group"
                     style="border-color: #ccc;"
                     onclick="document.getElementById('imageInput{{ $i }}').click()">
                    <input type="file" id="imageInput{{ $i }}" name="images[]" accept="image/*" class="hidden" onchange="handleImageSelect(event, {{ $i }})">
                    
                    <div class="preview-container hidden absolute inset-0">
                        <img src="" alt="Preview" class="w-full h-full object-cover">
                        <div class="absolute top-1 right-1">
                            <button type="button" onclick="removeImage(event, {{ $i }})" 
                                class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 transition-colors shadow-lg">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1" onclick="event.stopPropagation()">
                            <span class="text-white text-xs font-bold block">Image {{ $i + 1 }}</span>
                        </div>
                    </div>
                    
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-gray-600">
                        <i class="fas fa-plus text-xl mb-1"></i>
                        <span class="text-xs">Add</span>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Image Guidelines -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                    <div class="text-xs text-blue-800">
                        <p class="font-medium mb-1">Image Upload Tips:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Maximum file size: 5MB per image</li>
                            <li>Supported formats: JPEG, PNG, GIF, WebP</li>
                            <li>Square images work best (e.g., 800x800px)</li>
                            <li>First image becomes the main product display</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let uploadedImages = [];
        let imagesToDelete = [];
        
        function deleteExistingImage(imagePath, button) {
            if (!confirm('Are you sure you want to delete this image?')) {
                return;
            }
            
            imagesToDelete.push(imagePath);
            document.getElementById('deleteImagesInput').value = JSON.stringify(imagesToDelete);
            
            // Remove from UI
            const imageDiv = button.closest('.existing-image');
            imageDiv.style.opacity = '0.5';
            imageDiv.style.pointerEvents = 'none';
            
            // Add deleted indicator
            const deletedBadge = document.createElement('div');
            deletedBadge.className = 'absolute inset-0 flex items-center justify-center bg-black/60';
            deletedBadge.innerHTML = '<span class="text-white text-xs font-bold"><i class="fas fa-trash mr-1"></i>Will be deleted</span>';
            imageDiv.appendChild(deletedBadge);
        }
        
        function deleteAllExistingImages() {
            if (!confirm('Are you sure you want to delete ALL current images? This cannot be undone.')) {
                return;
            }
            
            const existingImages = document.querySelectorAll('.existing-image');
            existingImages.forEach(img => {
                const imagePath = img.getAttribute('data-image-path');
                if (!imagesToDelete.includes(imagePath)) {
                    imagesToDelete.push(imagePath);
                }
                img.style.opacity = '0.5';
                img.style.pointerEvents = 'none';
                
                const deletedBadge = document.createElement('div');
                deletedBadge.className = 'absolute inset-0 flex items-center justify-center bg-black/60';
                deletedBadge.innerHTML = '<span class="text-white text-xs font-bold"><i class="fas fa-trash mr-1"></i>Will be deleted</span>';
                img.appendChild(deletedBadge);
            });
            
            document.getElementById('deleteImagesInput').value = JSON.stringify(imagesToDelete);
        }
        
        function handleImageSelect(event, index) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                event.target.value = '';
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5MB');
                event.target.value = '';
                return;
            }
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const slots = document.querySelectorAll('.image-slot');
                const slot = slots[index];
                const preview = slot.querySelector('.preview-container');
                const img = preview.querySelector('img');
                const emptyState = slot.querySelector('.absolute.inset-0.flex');
                
                img.src = e.target.result;
                preview.classList.remove('hidden');
                emptyState.classList.add('hidden');
                
                uploadedImages[index] = file;
            };
            reader.readAsDataURL(file);
        }
        
        function removeImage(event, index) {
            event.stopPropagation();
            event.preventDefault();
            
            const slots = document.querySelectorAll('.image-slot');
            const slot = slots[index];
            const preview = slot.querySelector('.preview-container');
            const img = preview.querySelector('img');
            const emptyState = slot.querySelector('.absolute.inset-0.flex');
            const input = document.getElementById(index === 0 ? 'mainImageInput' : `imageInput${index}`);
            
            img.src = '';
            preview.classList.add('hidden');
            emptyState.classList.remove('hidden');
            input.value = '';
            uploadedImages[index] = null;
        }
        </script>

        <!-- Status -->
        <div>
            <label class="block font-medium text-gray-700">Status</label>
            <select name="status"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-600" required>
                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Product Stats (Read-only info) -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-600"></i>Product Information
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Product ID:</span>
                    <span class="font-medium ml-2">#{{ $product->id }}</span>
                </div>
                <div>
                    <span class="text-gray-600">SKU:</span>
                    <span class="font-medium ml-2">{{ $product->sku ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Created:</span>
                    <span class="font-medium ml-2">{{ $product->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Last Updated:</span>
                    <span class="font-medium ml-2">{{ $product->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition-colors duration-200">
                <i class="fas fa-save mr-2"></i>Update Product
            </button>
            <a href="{{ route('admin.products.index') }}"
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition-colors duration-200">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
@endsection
