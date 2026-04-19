@extends('layouts.admin')
@php
use Illuminate\Support\Facades\Storage;
$isBundleForm = (bool) old('is_bundle', ($bundleFeatureEnabled ?? false) && isset($existingBundleItems) && $existingBundleItems->isNotEmpty());
@endphp
@section('title', $isBundleForm ? 'Edit Bundle' : 'Edit Product')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#800000] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold mb-2">{{ $isBundleForm ? 'Edit Bundle' : 'Edit Product' }}</h1>
                <p class="text-red-100 text-lg">Update {{ $isBundleForm ? 'bundle' : 'product' }} details for {{ $product->name }}</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Products
            </a>
        </div>
    </div>

@php
    $initialBundleItems = old('bundle_items', isset($existingBundleItems) ? $existingBundleItems->map(function ($item) {
        return [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ];
    })->toArray() : []);
    $initialVariantRows = old('variant_rows', $existingVariantRows ?? []);
    if (!is_array($initialVariantRows)) {
        $initialVariantRows = [];
    }
@endphp

<div class="{{ $isBundleForm ? 'max-w-7xl mx-auto' : 'max-w-3xl mx-auto' }} p-6 bg-white shadow rounded-lg">

    @if ($errors->any())
        <div class="mb-4 p-4 border border-red-200 bg-red-50 rounded-lg">
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
        
        <!-- Preserve auth_token if present in URL -->
        @if(request()->has('auth_token'))
            <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') }}">
        @endif

        @if($isBundleForm)
        {{-- BUNDLE EDIT UI --}}
        <input type="hidden" name="is_bundle" value="1">
        <input type="hidden" name="status" value="{{ old('status', $product->status) }}">
        <input type="hidden" name="stock" value="{{ old('stock', $product->stock) }}">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Bundle Configuration --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Bundle Details --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Bundle details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bundle name</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" placeholder="e.g. Summer Starter Pack" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" placeholder="Describe what's included in this bundle..." 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Bundle Photo --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Bundle photo</h3>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer" 
                        onclick="document.getElementById('bundlePhotoInput').click()">
                        <div id="bundlePhotoPreview" class="{{ $product->image ? '' : 'hidden' }}">
                            <img src="{{ $product->image ? (str_starts_with($product->image, 'http') ? $product->image : asset('uploads/products/' . $product->image)) : '' }}" alt="Bundle preview" class="mx-auto max-h-48 rounded-lg mb-2">
                            <button type="button" onclick="event.stopPropagation(); removeBundlePhoto()" 
                                class="text-sm text-red-600 hover:text-red-700">Remove photo</button>
                        </div>
                        <div id="bundlePhotoPlaceholder" class="{{ $product->image ? 'hidden' : '' }}">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-1 text-sm text-gray-600">Upload bundle cover photo</p>
                            <p class="text-xs text-gray-500">(PNG, JPG up to 5MB)</p>
                        </div>
                        <input type="file" id="bundlePhotoInput" name="images[]" accept="image/*" class="hidden" onchange="previewBundlePhoto(event)">
                    </div>
                    <input type="hidden" name="keep_existing_image" id="keepExistingImage" value="{{ $product->image ? '1' : '0' }}">
                </div>

                {{-- Select Products --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Select products</h3>
                    
                    <input type="text" id="bundleProductSearch" placeholder="Search existing products..." 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">
                    
                    <div id="bundleProductList" class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($bundleComponents ?? [] as $component)
                        @php
                            $isSelected = collect($initialBundleItems)->contains('product_id', $component->id);
                        @endphp
                        <div class="bundle-product-item flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50" 
                            data-product-id="{{ $component->id }}" data-product-name="{{ $component->name }}" data-product-price="{{ $component->price }}">
                            <div class="flex items-center gap-3 flex-1">
                                <input type="checkbox" class="bundle-product-checkbox rounded border-gray-300 text-[#800000] focus:ring-[#800000]" 
                                    value="{{ $component->id }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleBundleProduct(this, {{ $component->id }}, '{{ addslashes($component->name) }}', {{ $component->price }})">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $component->name }}</p>
                                    @if($component->category ?? null)
                                    <p class="text-xs text-gray-500">{{ $component->category->name ?? 'Uncategorized' }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-900">₱{{ number_format($component->price, 2) }}</span>
                                <button type="button" class="bundle-add-btn px-3 py-1 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50" 
                                    onclick="addBundleProduct({{ $component->id }}, '{{ addslashes($component->name) }}', {{ $component->price }})">
                                    + Add
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-3">Click a product to add it to the bundle.</p>
                </div>

                {{-- Pricing & Discount --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Pricing & discount</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bundle discount</label>
                        <select name="bundle_discount_type" id="bundleDiscountType" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]" 
                            onchange="calculateBundlePrice()">
                            <option value="percentage_10">10% off total</option>
                            <option value="percentage_15">15% off total</option>
                            <option value="percentage_20">20% off total</option>
                            <option value="percentage_25">25% off total</option>
                            <option value="percentage_30">30% off total</option>
                            <option value="custom">Custom discount</option>
                        </select>
                        
                        <div id="customDiscountInput" class="mt-2 hidden">
                            <input type="number" name="custom_discount" min="0" step="0.01" placeholder="Enter discount amount" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]" 
                                onchange="calculateBundlePrice()">
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">Applied on top of individual product prices.</p>
                </div>
            </div>

            {{-- Right Column - Bundle Preview --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg border border-gray-200 p-4 sticky top-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Bundle preview</h3>
                    
                    <div class="mb-4">
                        <p class="text-lg font-semibold text-gray-900" id="bundlePreviewTitle">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">{{ count($initialBundleItems) }} item(s) added</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Items in bundle</p>
                        <div id="bundlePreviewItems" class="space-y-2 min-h-[100px]">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Original total</span>
                            <span class="font-medium" id="bundleOriginalTotal">₱0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Bundle discount</span>
                            <span class="font-medium text-red-600" id="bundleDiscountAmount">-₱0</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                            <span class="text-gray-900">Bundle price</span>
                            <span class="text-[#800000]" id="bundleFinalPrice">₱{{ number_format($product->price, 2) }}</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="price" id="bundlePriceInput" value="{{ $product->price }}">
                    <input type="hidden" name="bundle_items_json" id="bundleItemsJson" value="">
                    
                    <div class="mt-6 space-y-2">
                        <button id="updateBundleSubmitBtn" type="submit" class="w-full px-4 py-2 bg-[#800000] text-white rounded-lg font-semibold hover:bg-[#600000] transition-colors">
                            Update bundle
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Initialize bundle items from existing data
        @php
        $bundleComponentsForJs = isset($bundleComponents) ? $bundleComponents : collect();
        $bundleItemsInitialData = collect($initialBundleItems)->map(function($item) use ($bundleComponentsForJs) {
            $product = $bundleComponentsForJs->firstWhere('id', $item['product_id']);
            return [
                'id' => $item['product_id'],
                'name' => $product->name ?? 'Unknown Product',
                'price' => $product->price ?? 0,
                'quantity' => $item['quantity']
            ];
        })->values();
        @endphp
        let bundleItems = @json($bundleItemsInitialData);
        
        function previewBundlePhoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('#bundlePhotoPreview img').src = e.target.result;
                document.getElementById('bundlePhotoPreview').classList.remove('hidden');
                document.getElementById('bundlePhotoPlaceholder').classList.add('hidden');
                document.getElementById('keepExistingImage').value = '0';
            };
            reader.readAsDataURL(file);
        }
        
        function removeBundlePhoto() {
            document.getElementById('bundlePhotoInput').value = '';
            document.getElementById('bundlePhotoPreview').classList.add('hidden');
            document.getElementById('bundlePhotoPlaceholder').classList.remove('hidden');
            document.getElementById('keepExistingImage').value = '0';
        }
        
        function toggleBundleProduct(checkbox, productId, productName, productPrice) {
            if (checkbox.checked) {
                addBundleProduct(productId, productName, productPrice);
            } else {
                removeBundleProduct(productId);
            }
        }
        
        function addBundleProduct(productId, productName, productPrice) {
            const existing = bundleItems.find(item => item.id === productId);
            if (existing) {
                alert('This product is already in the bundle');
                return;
            }
            
            bundleItems.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 1
            });
            
            document.querySelector(`input[value="${productId}"].bundle-product-checkbox`).checked = true;
            updateBundlePreview();
        }
        
        function removeBundleProduct(productId) {
            bundleItems = bundleItems.filter(item => item.id !== productId);
            
            const checkbox = document.querySelector(`input[value="${productId}"].bundle-product-checkbox`);
            if (checkbox) checkbox.checked = false;
            
            updateBundlePreview();
        }
        
        function updateBundleQuantity(productId, quantity) {
            const item = bundleItems.find(item => item.id === productId);
            if (item) {
                item.quantity = parseInt(quantity) || 1;
                updateBundlePreview();
            }
        }
        
        function updateBundlePreview() {
            const previewContainer = document.getElementById('bundlePreviewItems');
            const bundleName = document.querySelector('input[name="name"]').value || 'Untitled bundle';
            
            document.getElementById('bundlePreviewTitle').textContent = bundleName;
            
            if (bundleItems.length === 0) {
                previewContainer.innerHTML = '<p class="text-sm text-gray-400 italic">Add products from the left</p>';
                document.querySelector('#bundlePreviewTitle + p').textContent = 'No items added';
            } else {
                document.querySelector('#bundlePreviewTitle + p').textContent = `${bundleItems.length} item(s) added`;
                
                previewContainer.innerHTML = bundleItems.map(item => `
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">${item.name}</p>
                            <p class="text-xs text-gray-500">₱${Number(item.price).toFixed(2)} × ${item.quantity}</p>
                        </div>
                        <div class="flex items-center gap-2 ml-2">
                            <input type="number" min="1" value="${item.quantity}" 
                                class="w-12 px-1 py-1 text-xs border border-gray-300 rounded text-center" 
                                onchange="updateBundleQuantity(${item.id}, this.value)">
                            <button type="button" onclick="removeBundleProduct(${item.id})" 
                                class="text-red-600 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
            
            document.getElementById('bundleItemsJson').value = JSON.stringify(bundleItems);
            calculateBundlePrice();
        }
        
        function calculateBundlePrice() {
            const originalTotal = bundleItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            let discount = 0;
            const discountType = document.getElementById('bundleDiscountType').value;
            
            if (discountType.startsWith('percentage_')) {
                const percentage = parseInt(discountType.split('_')[1]);
                discount = originalTotal * (percentage / 100);
            } else if (discountType === 'custom') {
                discount = parseFloat(document.querySelector('input[name="custom_discount"]').value) || 0;
                document.getElementById('customDiscountInput').classList.remove('hidden');
            } else {
                document.getElementById('customDiscountInput').classList.add('hidden');
            }
            
            const finalPrice = Math.max(0, originalTotal - discount);
            
            document.getElementById('bundleOriginalTotal').textContent = `₱${originalTotal.toFixed(2)}`;
            document.getElementById('bundleDiscountAmount').textContent = `-₱${discount.toFixed(2)}`;
            document.getElementById('bundleFinalPrice').textContent = `₱${finalPrice.toFixed(2)}`;
            document.getElementById('bundlePriceInput').value = finalPrice.toFixed(2);
        }
        
        // Product search
        document.getElementById('bundleProductSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const products = document.querySelectorAll('.bundle-product-item');
            
            products.forEach(product => {
                const productName = product.dataset.productName.toLowerCase();
                if (productName.includes(searchTerm)) {
                    product.classList.remove('hidden');
                } else {
                    product.classList.add('hidden');
                }
            });
        });
        
        // Update bundle name in preview
        document.querySelector('input[name="name"]').addEventListener('input', function(e) {
            document.getElementById('bundlePreviewTitle').textContent = e.target.value || 'Untitled bundle';
        });
        
        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateBundlePreview();
        });
        </script>

        @else
        {{-- REGULAR PRODUCT EDIT UI --}}

        <!-- Product Name -->
        <div>
            <label class="block font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]" required>
        </div>

        <!-- Category -->
        <div>
            <label class="block font-medium text-gray-700 mb-2">Category</label>
            <div class="flex gap-2">
                <select name="category_id" id="categorySelect"
                    class="border rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2 focus:ring-[#800000]"
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white cursor-pointer hover:opacity-80 transition-opacity group relative {{ old('category_id', $product->category_id) == $category->id ? 'ring-2 ring-offset-2 ring-yellow-400' : '' }}"
                              style="background-color: #800000;"
                              onclick="document.getElementById('categorySelect').value='{{ $category->id }}'">
                            {{ $category->name }}
                            @if(old('category_id', $product->category_id) == $category->id)
                                <i class="fas fa-check ml-1"></i>
                            @endif
                            <button type="button" class="ml-2 hidden group-hover:inline-flex items-center justify-center w-4 h-4 rounded-full bg-white bg-opacity-30 hover:bg-opacity-50 transition-all"
                                    onclick="event.stopPropagation(); deleteCategory({{ $category->id }});" 
                                    title="Delete category">
                                <i class="fas fa-times text-white text-xs"></i>
                            </button>
                        </span>
                    @endforeach
                    @if($categories->isEmpty())
                        <span class="text-sm text-gray-500 italic">No categories yet. Create one above!</span>
                    @endif
                </div>
            </div>
        </div>

        <script>
        let toastTimeout = null;

        function showUiToast(message, type = 'success') {
            const existing = document.getElementById('adminUiToast');
            if (existing) {
                existing.remove();
            }
            if (toastTimeout) {
                clearTimeout(toastTimeout);
            }

            const toast = document.createElement('div');
            toast.id = 'adminUiToast';
            const isSuccess = type === 'success';
            toast.className = `fixed top-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-xl text-white text-sm font-semibold transition-all duration-300 ${isSuccess ? 'bg-green-600' : 'bg-red-600'}`;
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.textContent = message;

            document.body.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            toastTimeout = setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function showUiConfirm(message) {
            return new Promise((resolve) => {
                const existing = document.getElementById('adminUiConfirm');
                if (existing) {
                    existing.remove();
                }

                const overlay = document.createElement('div');
                overlay.id = 'adminUiConfirm';
                overlay.className = 'fixed inset-0 z-[10000] flex items-center justify-center bg-black/45 px-4';

                overlay.innerHTML = `
                    <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-gray-200 p-5">
                        <h3 class="text-base font-bold text-gray-900 mb-2">Confirm Delete</h3>
                        <p class="text-sm text-gray-600 mb-5">${message}</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="confirmCancelBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Cancel</button>
                            <button type="button" id="confirmDeleteBtn" class="px-4 py-2 rounded-lg bg-[#800000] text-white hover:bg-[#600000] transition">Delete</button>
                        </div>
                    </div>
                `;

                document.body.appendChild(overlay);

                const cleanup = (result) => {
                    overlay.remove();
                    resolve(result);
                };

                overlay.querySelector('#confirmCancelBtn')?.addEventListener('click', () => cleanup(false));
                overlay.querySelector('#confirmDeleteBtn')?.addEventListener('click', () => cleanup(true));
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        cleanup(false);
                    }
                });
            });
        }

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
                showUiToast('Please enter a category name', 'error');
                return;
            }

            const authToken = new URLSearchParams(window.location.search).get('auth_token')
                || localStorage.getItem('yakan_auth_token')
                || sessionStorage.getItem('auth_token');

            const createUrl = new URL('{{ route("admin.categories.store") }}', window.location.origin);
            if (authToken) {
                createUrl.searchParams.set('auth_token', authToken);
            }

            fetch(createUrl.toString(), {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Auth-Token': authToken || ''
                },
                body: JSON.stringify({
                    name: categoryName,
                    auth_token: authToken || undefined
                })
            })
            .then(async response => {
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    if (response.status === 401 || response.status === 403 || text.toLowerCase().includes('login')) {
                        throw new Error('Session expired. Please refresh the page and login again.');
                    }
                    throw new Error('Unexpected server response while creating category.');
                }

                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('categorySelect');
                    const option = new Option(data.category.name, data.category.id, true, true);
                    select.add(option);
                    
                    showUiToast(`Category "${data.category.name}" added successfully!`, 'success');
                    input.value = '';
                    toggleNewCategory();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showUiToast(data.message || 'Failed to create category', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showUiToast(error.message || 'Failed to create category. Please try again.', 'error');
            });
        }

        document.getElementById('newCategoryInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addNewCategory();
            }
        });

        async function deleteCategory(categoryId) {
            const confirmed = await showUiConfirm('Are you sure you want to delete this category?');
            if (!confirmed) {
                return;
            }

            const authToken = new URLSearchParams(window.location.search).get('auth_token')
                || localStorage.getItem('yakan_auth_token')
                || sessionStorage.getItem('auth_token');

            const deleteUrl = new URL(`{{ url('admin/categories') }}/${categoryId}`, window.location.origin);
            if (authToken) {
                deleteUrl.searchParams.set('auth_token', authToken);
            }

            fetch(deleteUrl.toString(), {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Auth-Token': authToken || ''
                }
            })
            .then(async response => {
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    if (response.status === 401 || response.status === 403 || text.toLowerCase().includes('login')) {
                        throw new Error('Session expired. Please refresh the page and login again.');
                    }
                    throw new Error('Unexpected server response while deleting category.');
                }

                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    showUiToast('Category deleted successfully!', 'success');
                    location.reload();
                } else {
                    showUiToast(data.message || 'Failed to delete category', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showUiToast(error.message || 'Failed to delete category. Please try again.', 'error');
            });
        }
        </script>

        <!-- Price -->
        <div>
            <label class="block font-medium text-gray-700">Price (₱)</label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]" step="0.01"
                placeholder="0.00" required>
        </div>

        <!-- Product-level Discount -->
        <div class="rounded-lg border border-[#800000]/20 bg-[#fff8f8] p-4 space-y-3">
            <div>
                <h3 class="font-semibold text-gray-900">Product-level Discount</h3>
                <p class="text-sm text-gray-600">Optional. This discount applies to the base product price and all variant prices.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                    <select id="discountTypeInput" name="discount_type" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        <option value="">No Discount</option>
                        <option value="percent" {{ old('discount_type', $product->discount_type) === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                        <option value="fixed" {{ old('discount_type', $product->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (₱)</option>
                    </select>
                    @error('discount_type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value</label>
                    <input
                        id="discountValueInput"
                        type="number"
                        name="discount_value"
                        value="{{ old('discount_value', $product->discount_value) }}"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#800000]"
                    >
                    <p id="discountValueHelp" class="text-xs text-gray-500 mt-1">Select a discount type to activate this field.</p>
                    @error('discount_value')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p id="discountPreview" class="hidden text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2"></p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Starts At (optional)</label>
                    <input
                        type="datetime-local"
                        name="discount_starts_at"
                        value="{{ old('discount_starts_at', optional($product->discount_starts_at)->format('Y-m-d\\TH:i')) }}"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#800000]"
                    >
                    @error('discount_starts_at')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ends At (optional)</label>
                    <input
                        type="datetime-local"
                        name="discount_ends_at"
                        value="{{ old('discount_ends_at', optional($product->discount_ends_at)->format('Y-m-d\\TH:i')) }}"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#800000]"
                    >
                    @error('discount_ends_at')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Current Stock (read-only) -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Current Stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $product->available_stock }} units</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $product->available_stock === 0 ? 'bg-red-100 text-red-800' : ($product->available_stock == 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                    {{ $product->stock_status }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Use the <strong>Stock In</strong> button on the products list to add stock.</p>
        </div>

        <!-- Product Variants -->
        <div class="rounded-lg border border-[#800000]/20 bg-[#fff8f8] p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Product Variants</h3>
                    <p class="text-sm text-gray-600">Define size/color variants with different price and stock.</p>
                </div>
                <button type="button" id="addVariantRowBtn" class="inline-flex items-center px-3 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] text-sm font-medium">
                    <i class="fas fa-plus mr-2"></i>Add Variant
                </button>
            </div>

            <div id="variantRowsContainer" class="space-y-2">
                @foreach($initialVariantRows as $variantIndex => $variantRow)
                    <div class="variant-row grid grid-cols-14 gap-2 items-center rounded-lg bg-white border border-[#800000]/10 p-2">
                        <div class="col-span-2">
                            <input type="text" name="variant_rows[{{ $variantIndex }}][sku]" value="{{ $variantRow['sku'] ?? '' }}" placeholder="SKU" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        </div>
                        <div class="col-span-2">
                            <input type="text" name="variant_rows[{{ $variantIndex }}][size]" value="{{ $variantRow['size'] ?? '' }}" placeholder="Size" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        </div>
                        <div class="col-span-2">
                            <input type="text" name="variant_rows[{{ $variantIndex }}][color]" value="{{ $variantRow['color'] ?? '' }}" placeholder="Color" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" min="0" name="variant_rows[{{ $variantIndex }}][price]" value="{{ $variantRow['price'] ?? '' }}" placeholder="Price" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        </div>
                        <div class="col-span-2">
                            <input type="number" min="0" name="variant_rows[{{ $variantIndex }}][stock]" value="{{ $variantRow['stock'] ?? '' }}" placeholder="Stock" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                        </div>
                        <div class="col-span-2">
                            <input type="hidden" name="variant_rows[{{ $variantIndex }}][existing_image]" value="{{ $variantRow['image'] ?? '' }}">
                            <input type="file" name="variant_rows[{{ $variantIndex }}][image]" accept="image/*" class="w-full border rounded px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#800000]">
                            @if(!empty($variantRow['image']))
                                <p class="text-[11px] text-gray-500 mt-1">Current image kept unless replaced</p>
                            @endif
                        </div>
                        <div class="col-span-1 text-center">
                            <input type="hidden" name="variant_rows[{{ $variantIndex }}][is_active]" value="0">
                            <label class="inline-flex items-center justify-center">
                                <input type="checkbox" name="variant_rows[{{ $variantIndex }}][is_active]" value="1" {{ !array_key_exists('is_active', $variantRow) || (int) ($variantRow['is_active'] ?? 1) === 1 ? 'checked' : '' }} class="rounded border-gray-300 text-[#800000] focus:ring-[#800000]">
                            </label>
                        </div>
                        <div class="col-span-1 text-right">
                            <button type="button" class="remove-variant-row px-2 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-500">Tip: when variants are provided, product base price/stock are auto-derived from variants.</p>
            @error('variant_rows')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]">{{ old('description', $product->description) }}</textarea>
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
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-[#800000]">Current Images ({{ count($images) }})</h4>
                    <button type="button" onclick="deleteAllExistingImages()" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        <i class="fas fa-trash mr-1"></i>Delete All Current Images
                    </button>
                </div>
                <div class="grid grid-cols-5 gap-2" id="existingImagesGrid">
                    @foreach($images as $index => $img)
                    <div class="relative group existing-image" data-image-path="{{ $img['path'] }}">
                        <img src="{{ str_starts_with($img['path'], 'http') ? $img['path'] : asset('uploads/products/' . $img['path']) }}" 
                             alt="Product image {{ $index + 1 }}"
                             class="w-full aspect-square object-cover rounded border-2 border-red-300">
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
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-[#800000] mt-0.5 mr-2"></i>
                    <div class="text-xs text-[#800000]">
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

        (function () {
            const variantRowsContainer = document.getElementById('variantRowsContainer');
            const addVariantRowBtn = document.getElementById('addVariantRowBtn');

            let variantIndex = {{ is_array($initialVariantRows) ? count($initialVariantRows) : 0 }};

            function addVariantRow() {
                if (!variantRowsContainer) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'variant-row grid grid-cols-14 gap-2 items-center rounded-lg bg-white border border-[#800000]/10 p-2';
                wrapper.innerHTML = `
                    <div class="col-span-2">
                        <input type="text" name="variant_rows[${variantIndex}][sku]" placeholder="SKU" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="variant_rows[${variantIndex}][size]" placeholder="Size" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="variant_rows[${variantIndex}][color]" placeholder="Color" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="0.01" min="0" name="variant_rows[${variantIndex}][price]" placeholder="Price" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-2">
                        <input type="number" min="0" name="variant_rows[${variantIndex}][stock]" placeholder="Stock" class="w-full border rounded px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-2">
                        <input type="hidden" name="variant_rows[${variantIndex}][existing_image]" value="">
                        <input type="file" name="variant_rows[${variantIndex}][image]" accept="image/*" class="w-full border rounded px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    </div>
                    <div class="col-span-1 text-center">
                        <input type="hidden" name="variant_rows[${variantIndex}][is_active]" value="0">
                        <label class="inline-flex items-center justify-center">
                            <input type="checkbox" name="variant_rows[${variantIndex}][is_active]" value="1" checked class="rounded border-gray-300 text-[#800000] focus:ring-[#800000]">
                        </label>
                    </div>
                    <div class="col-span-1 text-right">
                        <button type="button" class="remove-variant-row px-2 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                variantRowsContainer.appendChild(wrapper);
                variantIndex += 1;
            }

            if (addVariantRowBtn) {
                addVariantRowBtn.addEventListener('click', addVariantRow);
            }

            if (variantRowsContainer) {
                variantRowsContainer.addEventListener('click', function (event) {
                    const removeBtn = event.target.closest('.remove-variant-row');
                    if (!removeBtn) {
                        return;
                    }

                    removeBtn.closest('.variant-row')?.remove();
                });
            }

            const basePriceInput = document.querySelector('input[name="price"]');
            const discountTypeInput = document.getElementById('discountTypeInput');
            const discountValueInput = document.getElementById('discountValueInput');
            const discountValueHelp = document.getElementById('discountValueHelp');
            const discountPreview = document.getElementById('discountPreview');

            function formatPeso(value) {
                return '₱' + Number(value).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function refreshDiscountPreview() {
                if (!basePriceInput || !discountTypeInput || !discountValueInput || !discountPreview) {
                    return;
                }

                const base = parseFloat(basePriceInput.value) || 0;
                const type = discountTypeInput.value;
                const rawValue = parseFloat(discountValueInput.value) || 0;

                if (!type || rawValue <= 0 || base <= 0) {
                    discountPreview.textContent = '';
                    discountPreview.classList.add('hidden');
                    return;
                }

                let discountAmount = 0;
                if (type === 'percent') {
                    const percent = Math.min(100, Math.max(0, rawValue));
                    discountAmount = base * (percent / 100);
                } else {
                    discountAmount = Math.min(base, Math.max(0, rawValue));
                }

                const discounted = Math.max(0, base - discountAmount);
                discountPreview.textContent = `Preview: ${formatPeso(base)} -> ${formatPeso(discounted)} (${formatPeso(discountAmount)} off)`;
                discountPreview.classList.remove('hidden');
            }

            function refreshDiscountControlState() {
                if (!discountTypeInput || !discountValueInput) {
                    return;
                }

                const type = discountTypeInput.value;
                if (type === 'percent') {
                    discountValueInput.max = '100';
                    discountValueInput.placeholder = '0 - 100';
                    if ((parseFloat(discountValueInput.value) || 0) > 100) {
                        discountValueInput.value = '100';
                    }
                    if (discountValueHelp) {
                        discountValueHelp.textContent = 'Percent discount must be between 0 and 100.';
                    }
                } else if (type === 'fixed') {
                    discountValueInput.removeAttribute('max');
                    discountValueInput.placeholder = '0.00';
                    if (discountValueHelp) {
                        discountValueHelp.textContent = 'Fixed discount in pesos. Values above base price are capped automatically.';
                    }
                } else {
                    discountValueInput.removeAttribute('max');
                    if (discountValueHelp) {
                        discountValueHelp.textContent = 'Select a discount type to activate this field.';
                    }
                }

                refreshDiscountPreview();
            }

            if (discountTypeInput) {
                discountTypeInput.addEventListener('change', refreshDiscountControlState);
            }

            if (discountValueInput) {
                discountValueInput.addEventListener('input', refreshDiscountPreview);
            }

            if (basePriceInput) {
                basePriceInput.addEventListener('input', refreshDiscountPreview);
            }

            refreshDiscountControlState();
        })();
        </script>

        <!-- Status -->
        <div>
            <label class="block font-medium text-gray-700">Status</label>
            <select name="status"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]" required>
                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Product Stats (Read-only info) -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                <i class="fas fa-info-circle mr-2 text-[#800000]"></i>Product Information
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
                class="bg-[#800000] text-white px-6 py-3 rounded-lg hover:bg-[#600000] transition-colors duration-200 font-medium shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Product
            </button>
            <a href="{{ route('admin.products.index') }}"
                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
        @endif
    </form>
</div>

@if(!$product->is_bundle)
{{-- ===== Stock History Panel (outside the edit form) ===== --}}
<div class="max-w-3xl mx-auto mt-6 bg-white shadow rounded-lg p-6">
    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-boxes mr-2 text-[#800000]"></i>Stock History — {{ $product->name }}
    </h2>

    @php
        $hasVariants = $product->variants->isNotEmpty();
    @endphp

    @if($hasVariants)
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900">
        <p class="font-semibold">Stock in/out is disabled for products with variants.</p>
        <p class="text-sm mt-1">Update stock per variant in the Product Variants section above, then click <strong>Update Product</strong>.</p>
    </div>
    @else

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <form action="{{ route('admin.products.stockIn', $product->id) }}" method="POST" class="rounded-lg border border-green-200 bg-green-50 p-4">
            @csrf
            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
            <input type="hidden" name="from_edit" value="1">
            <div class="mb-3">
                <h4 class="font-semibold text-green-800"><i class="fas fa-plus-circle mr-1"></i>Stock In</h4>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qty to Add</label>
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                    <input type="text" name="note" placeholder="e.g. New delivery batch"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <button type="submit"
                        class="w-full px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium whitespace-nowrap">
                    <i class="fas fa-plus mr-1"></i>Add Stock
                </button>
            </div>
        </form>

        <form action="{{ route('admin.products.stockOut', $product->id) }}" method="POST" class="rounded-lg border border-red-200 bg-red-50 p-4">
            @csrf
            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
            <input type="hidden" name="from_edit" value="1">
            <div class="mb-3">
                <h4 class="font-semibold text-red-800"><i class="fas fa-minus-circle mr-1"></i>Stock Out</h4>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qty to Remove</label>
                    <input type="number" name="quantity" min="1" max="{{ $product->available_stock }}" value="1" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                    <input type="text" name="note" placeholder="e.g. Damaged item, manual adjustment"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <button type="submit"
                        class="w-full px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium whitespace-nowrap">
                    <i class="fas fa-minus mr-1"></i>Remove Stock
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">Today</p>
            <p class="text-2xl font-bold text-blue-800">{{ $today > 0 ? '+' : '' }}{{ $today }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
            <p class="text-xs text-purple-600 font-semibold uppercase tracking-wide">This Week</p>
            <p class="text-2xl font-bold text-purple-800">{{ $thisWeek > 0 ? '+' : '' }}{{ $thisWeek }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-xs text-green-600 font-semibold uppercase tracking-wide">This Year</p>
            <p class="text-2xl font-bold text-green-800">{{ $thisYear > 0 ? '+' : '' }}{{ $thisYear }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
            <p class="text-xs text-amber-600 font-semibold uppercase tracking-wide">Overall</p>
            <p class="text-2xl font-bold text-amber-800">{{ $overall > 0 ? '+' : '' }}{{ $overall }}</p>
        </div>
    </div>

    {{-- Recent log entries --}}
    @if($recentLogs->isEmpty())
        <p class="text-gray-500 text-sm text-center py-4">No stock additions recorded yet.</p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-2 text-gray-600 font-semibold">Date &amp; Time</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">Movement</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">Before</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">Change</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">After</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">Added By</th>
                    <th class="px-3 py-2 text-gray-600 font-semibold">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($recentLogs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-700">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-3 py-2">
                        @if($log->quantity >= 0)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Stock In</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Stock Out</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-600">
                        @if($log->stock_before !== null)
                            {{ $log->stock_before }} units
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2 font-bold {{ $log->quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                    </td>
                    <td class="px-3 py-2 font-semibold text-gray-800">
                        @if($log->stock_after !== null)
                            {{ $log->stock_after }} units
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-600">{{ $log->creator?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-500">{{ $log->note ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endif

<div id="updateProductLoadingOverlay" class="fixed inset-0 z-[10001] hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-gray-200 p-6 text-center">
        <div class="mx-auto mb-4 h-10 w-10 border-4 border-[#800000]/20 border-t-[#800000] rounded-full animate-spin"></div>
        <p id="updateLoadingOverlayTitle" class="text-lg font-bold text-gray-900">Updating {{ $isBundleForm ? 'bundle' : 'product' }}...</p>
        <p id="updateLoadingOverlayMessage" class="mt-1 text-sm text-gray-600">Please wait while we save your changes.</p>
    </div>
</div>

<script>
(function () {
    const form = document.querySelector('form[action*="products/"]');
    const submitBtns = form ? form.querySelectorAll('button[type="submit"]') : [];
    const overlay = document.getElementById('updateProductLoadingOverlay');
    const isBundleForm = {{ $isBundleForm ? 'true' : 'false' }};
    
    if (!form || submitBtns.length === 0 || !overlay) {
        return;
    }
    
    form.addEventListener('submit', function (event) {
        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        
        form.dataset.submitting = '1';
        submitBtns.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            if (isBundleForm) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating Bundle...';
            } else {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating Product...';
            }
        });
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    });
})();
</script>

</div>
@endsection
