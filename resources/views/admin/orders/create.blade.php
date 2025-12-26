@extends('layouts.admin')

@section('title', 'Create Order')

@section('content')
<div class="space-y-6">
    <!-- Create Order Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Create New Order</h1>
                <p class="text-blue-100 text-lg">Create a manual order for a customer</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.regular.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Create Order Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6">
            <form action="{{ route('admin.orders.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Customer Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Customer Information</h3>
                    
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Customer <span class="text-red-500">*</span>
                        </label>
                        <select id="user_id" 
                                name="user_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Choose a customer...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Order Items -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                        <button type="button" 
                                id="addItemBtn"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>
                    
                    <div id="orderItems" class="space-y-3">
                        <!-- Initial item row -->
                        <div class="item-row bg-gray-50 p-4 rounded-lg border">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Product <span class="text-red-500">*</span>
                                    </label>
                                    <select name="items[0][product_id]" 
                                            required
                                            class="product-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Select a product...</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-stock="{{ $product->stock }}">
                                            {{ $product->name }} - ₱{{ number_format($product->price, 2) }} (Stock: {{ $product->stock }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="items[0][quantity]" 
                                           min="1" 
                                           value="1"
                                           required
                                           class="quantity-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Subtotal: <span class="item-subtotal font-semibold">₱0.00</span>
                                </div>
                                <button type="button" 
                                        class="remove-item-btn text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Order Summary</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium" id="subtotal">₱0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (0%):</span>
                                <span class="font-medium">₱0.00</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between">
                                <span class="font-semibold text-gray-900">Total:</span>
                                <span class="font-bold text-lg text-blue-600" id="totalAmount">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Additional Information</h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Order Notes
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Add any special instructions or notes for this order...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Stock will be automatically updated when order is created
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.regular.index') }}" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-shopping-cart mr-2"></i>Create Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const products = @json($products);
    
    // Add new item
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('orderItems');
        const newItemRow = createItemRow(itemIndex + 1);
        container.insertAdjacentHTML('beforeend', newItemRow);
        itemIndex++;
        updateOrderSummary();
    });
    
    // Remove item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const itemRow = e.target.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                itemRow.remove();
                updateOrderSummary();
            } else {
                alert('At least one item is required');
            }
        }
    });
    
    // Product selection change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const price = parseFloat(selectedOption.dataset.price) || 0;
            const stock = parseInt(selectedOption.dataset.stock) || 0;
            const quantityInput = e.target.closest('.item-row').querySelector('.quantity-input');
            
            // Update max quantity
            quantityInput.max = stock;
            
            // Reset quantity if it exceeds stock
            if (parseInt(quantityInput.value) > stock) {
                quantityInput.value = Math.min(1, stock);
            }
            
            updateItemSubtotal(e.target.closest('.item-row'));
            updateOrderSummary();
        }
    });
    
    // Quantity change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            updateItemSubtotal(e.target.closest('.item-row'));
            updateOrderSummary();
        }
    });
    
    function createItemRow(index) {
        const productOptions = products.map(product => 
            `<option value="${product.id}" data-price="${product.price}" data-stock="${product.stock}">
                ${product.name} - ₱${product.price.toFixed(2)} (Stock: ${product.stock})
            </option>`
        ).join('');
        
        return `
            <div class="item-row bg-gray-50 p-4 rounded-lg border">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${index}][product_id]" 
                                required
                                class="product-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select a product...</option>
                            ${productOptions}
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="items[${index}][quantity]" 
                               min="1" 
                               value="1"
                               required
                               class="quantity-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Subtotal: <span class="item-subtotal font-semibold">₱0.00</span>
                    </div>
                    <button type="button" 
                            class="remove-item-btn text-red-600 hover:text-red-800 text-sm">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
            </div>
        `;
    }
    
    function updateItemSubtotal(itemRow) {
        const select = itemRow.querySelector('.product-select');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const subtotalSpan = itemRow.querySelector('.item-subtotal');
        
        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        const subtotal = price * quantity;
        subtotalSpan.textContent = `₱${subtotal.toFixed(2)}`;
    }
    
    function updateOrderSummary() {
        const itemSubtotals = document.querySelectorAll('.item-subtotal');
        let subtotal = 0;
        
        itemSubtotals.forEach(span => {
            const value = parseFloat(span.textContent.replace('₱', '').replace(',', ''));
            subtotal += value;
        });
        
        document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `₱${subtotal.toFixed(2)}`;
    }
    
    // Initialize first item
    updateOrderSummary();
});
</script>
@endsection
