@extends('layouts.admin')
@section('title', request('as_bundle') ? 'Create Bundle' : 'Add Product')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#800000] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold mb-2">{{ request('as_bundle') ? 'Create Bundle' : 'Add New Product' }}</h1>
                <p class="text-red-100 text-lg">{{ request('as_bundle') ? 'Manage your product catalog and inventory' : 'Create a new product for your catalog' }}</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Products
            </a>
        </div>
    </div>

@php
    $isBundleForm = (bool) old('is_bundle', ($bundleFeatureEnabled ?? false) && request('as_bundle') ? 1 : 0);
    $initialBundleItems = old('bundle_items', $isBundleForm ? [['product_id' => '', 'quantity' => 1]] : []);
    $initialVariantRows = old('variant_rows', $initialVariantRows ?? []);
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

    <form id="addProductForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        
        <!--Preserve auth_token if present in URL -->
        @if(request()->has('auth_token'))
            <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') }}">
        @endif

        @if($isBundleForm)
        {{-- BUNDLE CREATION UI --}}
        <input type="hidden" name="is_bundle" value="1">
        <input type="hidden" name="status" value="active">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Bundle Configuration --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Bundle Details --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Bundle details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bundle name</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Summer Starter Pack" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" placeholder="Describe what's included in this bundle..." 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Bundle Photo --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Bundle photo</h3>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer" 
                        onclick="document.getElementById('bundlePhotoInput').click()">
                        <div id="bundlePhotoPreview" class="hidden">
                            <img src="" alt="Bundle preview" class="mx-auto max-h-48 rounded-lg mb-2">
                            <button type="button" onclick="event.stopPropagation(); removeBundlePhoto()" 
                                class="text-sm text-red-600 hover:text-red-700">Remove photo</button>
                        </div>
                        <div id="bundlePhotoPlaceholder">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-1 text-sm text-gray-600">Upload bundle cover photo</p>
                            <p class="text-xs text-gray-500">(PNG, JPG up to 5MB)</p>
                        </div>
                        <input type="file" id="bundlePhotoInput" name="images[]" accept="image/*" class="hidden" onchange="previewBundlePhoto(event)">
                    </div>
                </div>

                {{-- Select Products --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Select products</h3>
                    
                    <input type="text" id="bundleProductSearch" placeholder="Search existing products..." 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">
                    
                    <div id="bundleProductList" class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($bundleComponents ?? [] as $product)
                        <div class="bundle-product-item flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50" 
                            data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ $product->price }}">
                            <div class="flex items-center gap-3 flex-1">
                                <input type="checkbox" class="bundle-product-checkbox rounded border-gray-300 text-[#800000] focus:ring-[#800000]" 
                                    value="{{ $product->id }}" onchange="toggleBundleProduct(this, {{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                    @if($product->category)
                                    <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-900">₱{{ number_format($product->price, 2) }}</span>
                                <button type="button" class="bundle-add-btn px-3 py-1 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50" 
                                    onclick="addBundleProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
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
                        <p class="text-lg font-semibold text-gray-900" id="bundlePreviewTitle">Untitled bundle</p>
                        <p class="text-xs text-gray-500">No items added</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Items in bundle</p>
                        <div id="bundlePreviewItems" class="space-y-2 min-h-[100px]">
                            <p class="text-sm text-gray-400 italic">Add products from the left</p>
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
                            <span class="text-[#800000]" id="bundleFinalPrice">₱0</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="price" id="bundlePriceInput" value="0">
                    <input type="hidden" name="bundle_items_json" id="bundleItemsJson" value="[]">
                    
                    <div class="mt-6 space-y-2">
                        <button id="createProductSubmitBtn" type="submit" class="w-full px-4 py-2 bg-[#800000] text-white rounded-lg font-semibold hover:bg-[#600000] transition-colors">
                            Publish bundle
                        </button>
                        <button type="button" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                            Save as draft
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let bundleItems = [];
        
        function previewBundlePhoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('#bundlePhotoPreview img').src = e.target.result;
                document.getElementById('bundlePhotoPreview').classList.remove('hidden');
                document.getElementById('bundlePhotoPlaceholder').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
        
        function removeBundlePhoto() {
            document.getElementById('bundlePhotoInput').value = '';
            document.getElementById('bundlePhotoPreview').classList.add('hidden');
            document.getElementById('bundlePhotoPlaceholder').classList.remove('hidden');
        }
        
        function toggleBundleProduct(checkbox, productId, productName, productPrice) {
            if (checkbox.checked) {
                addBundleProduct(productId, productName, productPrice);
            } else {
                removeBundleProduct(productId);
            }
        }
        
        function addBundleProduct(productId, productName, productPrice) {
            // Check if already added
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
            
            // Check the checkbox
            document.querySelector(`input[value="${productId}"].bundle-product-checkbox`).checked = true;
            
            updateBundlePreview();
        }
        
        function removeBundleProduct(productId) {
            bundleItems = bundleItems.filter(item => item.id !== productId);
            
            // Uncheck the checkbox
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
            
            // Update hidden input
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
        </script>

        @else
        {{-- REGULAR PRODUCT CREATION UI --}}
        <div>
            <label class="block font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name') }}"
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
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                        <div class="group relative">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white cursor-pointer hover:opacity-80 transition-opacity"
                                  style="background-color: #800000;"
                                  onclick="document.getElementById('categorySelect').value='{{ $category->id }}'">
                                {{ $category->name }}
                            </span>
                            <!-- Delete button (visible on hover) -->
                            <button type="button" 
                                    onclick="deleteCategory('{{ $category->id }}', '{{ $category->name }}')"
                                    class="absolute -top-2 -right-2 hidden group-hover:block w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold hover:bg-red-700 transition-colors"
                                    title="Delete category">
                                ×
                            </button>
                        </div>
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

            const createUrl = new URL('/admin/categories', window.location.origin);
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
                    // Add new option to select
                    const select = document.getElementById('categorySelect');
                    const option = new Option(data.category.name, data.category.id, true, true);
                    select.add(option);
                    
                    // Reset and hide form
                    input.value = '';
                    toggleNewCategory();

                    showUiToast(`Category "${data.category.name}" added successfully!`, 'success');
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

        // Allow Enter key to submit new category
        document.getElementById('newCategoryInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addNewCategory();
            }
        });

        async function deleteCategory(categoryId, categoryName) {
            const confirmed = await showUiConfirm(`Are you sure you want to delete "${categoryName}"?`);
            if (!confirmed) {
                return;
            }

            const authToken = new URLSearchParams(window.location.search).get('auth_token')
                || localStorage.getItem('yakan_auth_token')
                || sessionStorage.getItem('auth_token');

            const deleteUrl = new URL(`/admin/categories/${categoryId}`, window.location.origin);
            if (authToken) {
                deleteUrl.searchParams.set('auth_token', authToken);
            }

            fetch(deleteUrl.toString(), {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
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
                console.error('Delete error:', error);
                showUiToast(error.message || 'Failed to delete category. Please try again.', 'error');
            });
        }

        </script>

        <!-- Price -->
        <div>
            <label class="block font-medium text-gray-700">Price (₱)</label>
            <input type="number" name="price" value="{{ old('price') }}"
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
                        <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                        <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₱)</option>
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
                        value="{{ old('discount_value') }}"
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
                        value="{{ old('discount_starts_at') ? str_replace(' ', 'T', old('discount_starts_at')) : '' }}"
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
                        value="{{ old('discount_ends_at') ? str_replace(' ', 'T', old('discount_ends_at')) : '' }}"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#800000]"
                    >
                    @error('discount_ends_at')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div>
            <label class="block font-medium text-gray-700">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}"
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]" min="0"
                required>
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
                    <div class="variant-row grid grid-cols-12 gap-2 items-center rounded-lg bg-white border border-[#800000]/10 p-2">
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
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]">{{ old('description') }}</textarea>
        </div>

        <!-- Professional Image Upload Section -->
        <div class="border-2 border-dashed rounded-lg p-6" style="border-color: #800000;">
            <label class="block font-bold text-gray-900 mb-4 text-lg">
                <i class="fas fa-images mr-2" style="color: #800000;"></i>Product Images
            </label>
            <p class="text-sm text-gray-600 mb-4">
                Upload up to 4 images. The first image will be the main product image. Recommended size: 800x800px.
            </p>
            
            <!-- Image Upload Area -->
            <div id="imageUploadArea" class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-4">
                <!-- Main Image Slot -->
                <div class="image-slot relative aspect-square border-2 rounded-lg overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer group"
                     style="border-color: #800000;" onclick="document.getElementById('mainImageInput').click()">
                    <input type="file" id="mainImageInput" name="images[]" accept="image/*" class="hidden" onchange="handleImageSelect(event, 0)">
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <i class="fas fa-camera text-3xl mb-2" style="color: #800000;"></i>
                        <span class="text-xs font-bold" style="color: #800000;">Main Image</span>
                        <span class="text-xs text-gray-500">Required</span>
                    </div>
                    <div class="preview-container hidden absolute inset-0">
                        <img src="" alt="" class="w-full h-full object-cover">
                        <div class="absolute top-1 right-1">
                            <button type="button" onclick="removeImage(event, 0)" 
                                    class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 shadow-lg">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1" onclick="event.stopPropagation()">
                            <span class="text-white text-xs font-bold block">
                                <i class="fas fa-star"></i> Main
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Additional Image Slots (3 more) -->
                @for ($i = 1; $i < 4; $i++)
                <div class="image-slot relative aspect-square border-2 border-dashed rounded-lg overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer group"
                     onclick="document.getElementById('imageInput{{ $i }}').click()">
                    <input type="file" id="imageInput{{ $i }}" name="images[]" accept="image/*" class="hidden" onchange="handleImageSelect(event, {{ $i }})">
                    <div class="absolute inset-0 flex flex-col items-center justify-center opacity-50 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-plus text-2xl text-gray-400"></i>
                        <span class="text-xs text-gray-400 mt-1">Add</span>
                    </div>
                    <div class="preview-container hidden absolute inset-0">
                        <img src="" alt="" class="w-full h-full object-cover">
                        <div class="absolute top-1 right-1">
                            <button type="button" onclick="removeImage(event, {{ $i }})" 
                                    class="bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 shadow-lg">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1" onclick="event.stopPropagation()">
                            <span class="text-white text-xs font-bold block">Image {{ $i + 1 }}</span>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Image Guidelines -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-[#800000] mt-1 mr-2"></i>
                    <div class="text-sm text-[#800000]">
                        <p class="font-medium mb-1">Image Guidelines:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Format: JPG, PNG, WEBP</li>
                            <li>Size: Maximum 5MB per image</li>
                            <li>Recommended: Square images (1:1 ratio) at least 800x800px</li>
                            <li>First image will be displayed as main product image</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let uploadedImages = [];
        
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
                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#800000]" required>
                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Submit Button -->
        <button id="createProductSubmitBtn" type="submit"
            class="bg-[#800000] text-white px-6 py-3 rounded-lg hover:bg-[#600000] transition-colors duration-200 font-medium shadow-lg">
            <i class="fas fa-plus mr-2"></i>Create Product
        </button>
        @endif
    </form>

    <div id="addProductLoadingOverlay" class="fixed inset-0 z-[10001] hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-gray-200 p-6 text-center">
            <div class="mx-auto mb-4 h-10 w-10 border-4 border-[#800000]/20 border-t-[#800000] rounded-full animate-spin"></div>
            <p id="loadingOverlayTitle" class="text-lg font-bold text-gray-900">Adding product...</p>
            <p id="loadingOverlayMessage" class="mt-1 text-sm text-gray-600">Please wait while we save your product details.</p>
        </div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('addProductForm');
        const submitBtn = document.getElementById('createProductSubmitBtn');
        const overlay = document.getElementById('addProductLoadingOverlay');
        const variantRowsContainer = document.getElementById('variantRowsContainer');
        const addVariantRowBtn = document.getElementById('addVariantRowBtn');

        let variantIndex = {{ is_array($initialVariantRows) ? count($initialVariantRows) : 0 }};

        function addVariantRow() {
            if (!variantRowsContainer) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'variant-row grid grid-cols-12 gap-2 items-center rounded-lg bg-white border border-[#800000]/10 p-2';
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

        if (!form || !submitBtn || !overlay) {
            return;
        }

        const isBundleForm = {{ $isBundleForm ? 'true' : 'false' }};
        const loadingOverlayTitle = document.getElementById('loadingOverlayTitle');
        const loadingOverlayMessage = document.getElementById('loadingOverlayMessage');

        form.addEventListener('submit', function (event) {
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = '1';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            
            if (isBundleForm) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Publishing Bundle...';
                if (loadingOverlayTitle) loadingOverlayTitle.textContent = 'Publishing bundle...';
                if (loadingOverlayMessage) loadingOverlayMessage.textContent = 'Please wait while we create your bundle.';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Product...';
                if (loadingOverlayTitle) loadingOverlayTitle.textContent = 'Adding product...';
                if (loadingOverlayMessage) loadingOverlayMessage.textContent = 'Please wait while we save your product details.';
            }
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        });
    })();
    </script>
</div>
</div>
@endsection
