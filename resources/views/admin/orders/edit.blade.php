@extends('layouts.admin')

@section('title', 'Edit Order')

@section('content')
@php
    $normalizedDeliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
    if ($normalizedDeliveryType === 'deliver') {
        $normalizedDeliveryType = 'delivery';
    }
    $isPickup = $normalizedDeliveryType === 'pickup';
    $isPaymentLocked = in_array(strtolower((string) ($order->payment_status ?? 'pending')), ['paid', 'verified'], true);
    $canFullyEdit = strtolower((string) $order->status) === 'pending';
    $canQuantityEditOnly = !$canFullyEdit && $isPickup && !$isPaymentLocked;
    $canEditItems = $canFullyEdit || $canQuantityEditOnly;
@endphp
<div class="space-y-6">
    <!-- Edit Order Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Edit Order {{ $order->order_ref ?? '#'.$order->id }}</h1>
                <p class="text-red-100 text-lg">Modify order details and items</p>
                @if(!$canEditItems)
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        This order is locked for editing
                    </div>
                @elseif($canQuantityEditOnly)
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-sliders-h mr-1"></i>
                        Pickup unpaid: quantity-only editing enabled
                    </div>
                @endif
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-eye mr-2"></i>View Order
                </a>
                <a href="{{ route('admin.regular.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    @if(!$canEditItems)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
            <div>
                <p class="text-sm text-yellow-800 font-medium">Order Cannot Be Edited</p>
                <p class="text-xs text-yellow-700 mt-1">Only pending orders or pickup unpaid quantity updates are allowed.</p>
            </div>
        </div>
    </div>
    @elseif($canQuantityEditOnly)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
            <div>
                <p class="text-sm text-blue-800 font-medium">Quantity-Only Edit Mode</p>
                <p class="text-xs text-blue-700 mt-1">You can adjust item quantities for pickup settlement. Product, customer, and notes are locked.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Order Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6">
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                
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
                                @if(!$canFullyEdit) disabled
                                @endif
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent @if(!$canFullyEdit) bg-gray-100 @endif">
                            <option value="">Choose a customer...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>
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
                                @if(!$canFullyEdit) disabled
                                @endif
                                class="px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors text-sm @if(!$canFullyEdit) opacity-50 cursor-not-allowed @endif">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>
                    
                    <div id="orderItems" class="space-y-3">
                        <!-- Existing order items -->
                        @foreach($order->orderItems as $index => $orderItem)
                        <div class="item-row bg-gray-50 p-4 rounded-lg border">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Product <span class="text-red-500">*</span>
                                    </label>
                                    <select name="items[{{ $index }}][product_id]" 
                                            required
                                            @if(!$canFullyEdit) disabled
                                            @endif
                                            class="product-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent @if(!$canFullyEdit) bg-gray-100 @endif">
                                        <option value="">Select a product...</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-stock="{{ $product->stock }}"
                                                {{ old("items.{$index}.product_id", $orderItem->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} - ₱{{ number_format($product->price, 2) }} (Stock: {{ $product->stock }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @if(!$canFullyEdit)
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ old("items.{$index}.product_id", $orderItem->product_id) }}">
                                    @endif
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="items[{{ $index }}][quantity]" 
                                           min="1" 
                                           value="{{ old("items.{$index}.quantity", $orderItem->quantity) }}"
                                           required
                                           @if(!$canEditItems) disabled
                                           @endif
                                           class="quantity-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent @if(!$canEditItems) bg-gray-100 @endif">
                                    @if($canQuantityEditOnly)
                                        <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $orderItem->id }}">
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Subtotal: <span class="item-subtotal font-semibold">₱{{ number_format($orderItem->price * $orderItem->quantity, 2) }}</span>
                                </div>
                                <button type="button" 
                                        @if(!$canFullyEdit) disabled
                                        @endif
                                        class="remove-item-btn text-red-600 hover:text-red-800 text-sm @if(!$canFullyEdit) opacity-50 cursor-not-allowed @endif">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Order Summary</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium" id="subtotal">₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (0%):</span>
                                <span class="font-medium">₱0.00</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between">
                                <span class="font-semibold text-gray-900">Total:</span>
                                <span class="font-bold text-lg text-[#800000]" id="totalAmount">₱{{ number_format($order->total_amount, 2) }}</span>
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
                                  @if(!$canFullyEdit) disabled
                                  @endif
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent @if(!$canFullyEdit) bg-gray-100 @endif"
                                  placeholder="Add any special instructions or notes for this order...">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Stock will be automatically updated when order is saved
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.orders.show', $order->id) }}" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        @if($canEditItems)
                        <button type="submit" 
                                class="px-6 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                            <i class="fas fa-save mr-2"></i>{{ $canQuantityEditOnly ? 'Update Quantities' : 'Update Order' }}
                        </button>
                        @else
                        <button type="button" 
                                disabled
                                class="px-6 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed opacity-50">
                            <i class="fas fa-lock mr-2"></i>Order Locked
                        </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $order->orderItems->count() }};
    const products = @json($products);
    const canFullyEdit = {{ $canFullyEdit ? 'true' : 'false' }};
    const canEditItems = {{ $canEditItems ? 'true' : 'false' }};
    
    // Add new item
    document.getElementById('addItemBtn').addEventListener('click', function() {
        if (!canFullyEdit) return;
        
        const container = document.getElementById('orderItems');
        const newItemRow = createItemRow(itemIndex);
        container.insertAdjacentHTML('beforeend', newItemRow);
        itemIndex++;
        updateOrderSummary();
    });
    
    // Remove item
    document.addEventListener('click', function(e) {
        if (!canFullyEdit) return;
        
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
        if (!canFullyEdit) return;
        
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
        if (!canEditItems) return;
        
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
                                class="product-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
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
                               class="quantity-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
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
    
    // Initialize existing items
    document.querySelectorAll('.item-row').forEach(row => {
        updateItemSubtotal(row);
    });
    updateOrderSummary();
});
</script>
@endsection
