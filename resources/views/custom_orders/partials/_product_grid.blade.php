<section class="space-y-6" data-section="products">
    {{-- Enhanced Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900">Select Products <span class="text-red-500">*</span></p>
                <p class="text-sm text-gray-500 font-light">Choose multiple products for your orders</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200 shadow-sm">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                Required
            </span>
            <span class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold border border-blue-200 shadow-sm">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Multiple
            </span>
        </div>
    </div>

    <input type="hidden" name="product_ids" id="product_ids_input" required>

    {{-- Enhanced Product Grid --}}
    <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6" id="productGrid" role="list">
        @foreach($categories as $category)
            @foreach($category->products as $product)
                <div class="product-card group relative cursor-pointer rounded-xl sm:rounded-2xl bg-white border-2 border-gray-200 shadow-md hover:shadow-xl hover:border-red-400 transition-all duration-300 ease-out overflow-hidden transform hover:-translate-y-1 sm:hover:-translate-y-2"
                     data-product-id="{{ $product->id }}"
                     data-product-name="{{ $product->name }}"
                     data-product-type="{{ strtolower(str_replace(' ', '', $product->name)) }}"
                     role="button" tabindex="0" aria-pressed="false" aria-label="Select {{ $product->name }}" aria-selected="false">
                    
                    {{-- Product Image Container --}}
                    <div class="aspect-square bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden">
                        @if($product->image)
                            <div class="relative w-full h-full">
                                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <div class="w-16 h-16 bg-gradient-to-br from-gray-300 to-gray-400 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500">No Image</span>
                            </div>
                        @endif
                        
                        {{-- Enhanced Selection Checkmark --}}
                        <div class="product-checkmark absolute top-2 sm:top-3 right-2 sm:right-3 w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full hidden items-center justify-center text-white shadow-lg transform scale-0 group-hover:scale-100 transition-all duration-300">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2 sm:stroke-width-3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        {{-- Quantity Badge --}}
                        <div class="product-quantity absolute top-2 sm:top-3 left-2 sm:left-3 bg-white/90 backdrop-blur-sm px-1.5 sm:px-2 py-1 rounded-lg shadow-md opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden">
                            <div class="flex items-center space-x-1">
                                <button type="button" class="quantity-btn minus w-4 h-4 sm:w-5 sm:h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors" data-product-id="{{ $product->id }}">
                                    <svg class="w-2 h-2 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="quantity-display text-xs font-bold text-gray-700 min-w-[16px] sm:min-w-[20px] text-center">1</span>
                                <button type="button" class="quantity-btn plus w-4 h-4 sm:w-5 sm:h-5 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition-colors" data-product-id="{{ $product->id }}">
                                    <svg class="w-2 h-2 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Category Badge --}}
                        @if($category->name)
                            <div class="absolute bottom-10 sm:bottom-12 left-2 sm:left-3 bg-white/90 backdrop-blur-sm px-1.5 sm:px-2 py-1 rounded-lg shadow-md opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="text-xs font-bold text-gray-700">{{ $category->name }}</span>
                            </div>
                        @endif

                        {{-- Hover Action Button --}}
                        <div class="absolute bottom-2 sm:bottom-3 left-2 sm:left-3 right-2 sm:right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-1 sm:translate-y-2 group-hover:translate-y-0">
                            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white text-center py-1.5 sm:py-2 px-2 sm:px-3 lg:px-4 rounded-lg font-bold text-xs shadow-lg hover:shadow-xl transition-shadow duration-200">
                                <span class="hidden xs:inline sm:hidden lg:inline">Add to Order</span>
                                <span class="xs:hidden sm:inline lg:hidden">Add</span>
                            </div>
                        </div>
                    </div>

                    {{-- Enhanced Product Info --}}
                    <div class="p-3 sm:p-4 bg-gradient-to-b from-white to-gray-50">
                        <div class="text-center space-y-1.5 sm:space-y-2">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors duration-200">
                                {{ $product->name }}
                            </h3>
                            @if($product->price)
                                <div class="flex items-center justify-center space-x-1">
                                    <span class="text-sm sm:text-base lg:text-lg font-black bg-gradient-to-r from-red-600 to-red-700 bg-clip-text text-transparent">₱{{ number_format($product->price, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Selected State Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/20 to-red-600/20 border-2 border-red-500 rounded-2xl hidden opacity-0 transition-all duration-300"></div>
                </div>
            @endforeach
        @endforeach
    </div>

    {{-- Enhanced Error Message --}}
    @error('product_ids')
        <div class="flex items-center gap-3 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-red-800">Product Selection Required</p>
                <p class="text-sm text-red-700">{{ $message }}</p>
            </div>
        </div>
    @enderror

    {{-- Selection Summary --}}
    <div id="selectionSummary" class="hidden bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-green-800">Products Selected</p>
                <p class="text-sm text-green-700" id="selectedProductsCount">0 products selected</p>
            </div>
        </div>
        <div id="selectedProductsList" class="space-y-2 max-h-40 overflow-y-auto">
            <!-- Selected products will be listed here -->
        </div>
    </div>
</section>

<script>
// Custom CSS for xs breakpoint (320px and up)
const style = document.createElement('style');
style.textContent = `
    @media (min-width: 320px) {
        .xs\\:inline { display: inline !important; }
        .xs\\:hidden { display: none !important; }
        .xs\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .xs\\:gap-6 { gap: 1.5rem !important; }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    const productIdsInput = document.getElementById('product_ids_input');
    const selectionSummary = document.getElementById('selectionSummary');
    const selectedProductsCount = document.getElementById('selectedProductsCount');
    const selectedProductsList = document.getElementById('selectedProductsList');
    
    // Store selected products with quantities
    let selectedProducts = {};

    productCards.forEach(card => {
        const productId = card.dataset.productId;
        const productName = card.dataset.productName;
        const checkmark = card.querySelector('.product-checkmark');
        const quantityBadge = card.querySelector('.product-quantity');
        const quantityDisplay = card.querySelector('.quantity-display');
        const minusBtn = card.querySelector('.quantity-btn.minus');
        const plusBtn = card.querySelector('.quantity-btn.plus');

        // Initialize product quantity
        selectedProducts[productId] = { name: productName, quantity: 0 };

        // Card click handler
        card.addEventListener('click', function(e) {
            // Don't toggle if clicking on quantity buttons
            if (e.target.closest('.quantity-btn')) {
                return;
            }
            
            toggleProductSelection(productId, productName, card);
        });

        // Quantity button handlers
        minusBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            updateProductQuantity(productId, -1, card);
        });

        plusBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            updateProductQuantity(productId, 1, card);
        });

        // Hover effects
        card.addEventListener('mouseenter', function() {
            if (selectedProducts[productId].quantity > 0) {
                this.style.transform = 'translateY(-4px)';
            }
        });

        card.addEventListener('mouseleave', function() {
            if (selectedProducts[productId].quantity > 0) {
                this.style.transform = '';
            }
        });
    });

    function toggleProductSelection(productId, productName, card) {
        const currentQuantity = selectedProducts[productId].quantity;
        
        if (currentQuantity === 0) {
            // Add product
            updateProductQuantity(productId, 1, card);
        } else {
            // Remove product
            updateProductQuantity(productId, -currentQuantity, card);
        }
    }

    function updateProductQuantity(productId, change, card) {
        const currentQuantity = selectedProducts[productId].quantity;
        const newQuantity = Math.max(0, currentQuantity + change);
        
        selectedProducts[productId].quantity = newQuantity;
        
        // Update UI
        const borderDiv = card.querySelector('.aspect-square');
        if (newQuantity > 0) {
            borderDiv.classList.add('border-amber-500', 'ring-2', 'ring-amber-400', 'ring-offset-2');
            borderDiv.classList.remove('border-gray-200');
        } else {
            borderDiv.classList.remove('border-amber-500', 'ring-2', 'ring-amber-400', 'ring-offset-2');
            borderDiv.classList.add('border-gray-200');
        }
        
        // Update hidden input with proper JSON structure for backend
        updateSelectionSummary();
        
        // Add visual feedback
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.style.transform = '';
        }, 150);
    }

    function updateSelectionSummary() {
        const selectedProductIds = Object.keys(selectedProducts).filter(id => selectedProducts[id].quantity > 0);
        const totalItems = selectedProductIds.reduce((sum, id) => sum + selectedProducts[id].quantity, 0);
        
        if (selectedProductIds.length > 0) {
            selectionSummary.classList.remove('hidden');
            selectedProductsCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''} from ${selectedProductIds.length} product${selectedProductIds.length !== 1 ? 's' : ''}`;
            
            // Update products list
            selectedProductsList.innerHTML = selectedProductIds.map(id => `
                <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-green-200">
                    <span class="text-sm font-medium text-gray-700">${selectedProducts[id].name}</span>
                    <span class="text-sm font-bold text-green-600">Qty: ${selectedProducts[id].quantity}</span>
                </div>
            `).join('');
            
            // Update hidden input - proper JSON format for backend
            const productData = selectedProductIds.map(id => ({
                id: parseInt(id),
                quantity: selectedProducts[id].quantity
            }));
            productIdsInput.value = JSON.stringify(productData);
            
            // Trigger custom event for sidebar integration
            document.dispatchEvent(new CustomEvent('productSelectionChanged', {
                detail: {
                    products: productData,
                    totalItems: totalItems
                }
            }));
        } else {
            selectionSummary.classList.add('hidden');
            productIdsInput.value = '';
            
            // Trigger event for sidebar
            document.dispatchEvent(new CustomEvent('productSelectionChanged', {
                detail: {
                    products: [],
                    totalItems: 0
                }
            }));
        }
    }
});
</script>
