@extends('admin.layouts.app')

@section('title', 'Select Fabric - Admin Custom Order')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Admin Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Create Custom Order</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-red-50 text-[#800000] rounded-full">Step 1: Select Fabric</span>
                </div>
                <a href="{{ route('admin.custom_orders.create.choice') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center space-x-6">
                <div class="flex items-center group">
                    <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
                        ✓
                    </div>
                    <span class="ml-3 font-bold text-green-600">Fabric</span>
                </div>
                <div class="w-16 h-1 bg-green-600 rounded-full"></div>
                <div class="flex items-center group opacity-60">
                    <div class="relative">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                            2
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse opacity-0 group-hover:opacity-100"></div>
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Design</span>
                </div>
                <div class="w-16 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center group opacity-60">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Fabric Selection Form -->
        <form action="{{ route('admin.custom_orders.store.fabric') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Customer Selection -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Customer</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                        <select name="user_id" id="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                            <option value="">Choose a customer...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fabric Type Selection -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Choose Fabric Type</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach(['Cotton', 'Silk', 'Polyester Cotton Blend', 'Linen', 'Canvas', 'Jersey Knit'] as $fabricType)
                        <div class="fabric-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-[#800000] hover:shadow-md transition-all duration-200" 
                             onclick="selectFabric('{{ $fabricType }}')" 
                             data-fabric="{{ $fabricType }}">
                            <div class="flex items-center mb-3">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 transition-all duration-200 fabric-radio"></div>
                                <input type="radio" name="fabric_type" value="{{ $fabricType }}" class="sr-only" id="fabric_{{ str_replace(' ', '_', strtolower($fabricType)) }}">
                                <label for="fabric_{{ str_replace(' ', '_', strtolower($fabricType)) }}" class="font-bold text-gray-900 cursor-pointer">{{ $fabricType }}</label>
                            </div>
                            <p class="text-sm text-gray-600">{{ $fabricType }} fabric perfect for custom Yakan patterns with excellent texture and durability</p>
                        </div>
                    @endforeach
                </div>
                @error('fabric_type')
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    </div>
                @enderror
            </div>

            <!-- Fabric Specifications -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Fabric Specifications</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fabric_quantity_meters" class="block text-sm font-medium text-gray-700 mb-2">Quantity (meters) *</label>
                        <input type="number" 
                               id="fabric_quantity_meters" 
                               name="fabric_quantity_meters" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                               step="0.1" 
                               min="0.5" 
                               max="100" 
                               required
                               placeholder="e.g., 2.5">
                        <p class="text-sm text-gray-500 mt-1">Minimum 0.5 meters, maximum 100 meters</p>
                        @error('fabric_quantity_meters')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="intended_use" class="block text-sm font-medium text-gray-700 mb-2">Intended Use *</label>
                        <select id="intended_use" 
                                name="intended_use" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                            <option value="">Select intended use...</option>
                            <option value="clothing">Clothing</option>
                            <option value="accessories">Accessories</option>
                            <option value="home_decor">Home Decor</option>
                            <option value="bags">Bags</option>
                            <option value="crafts">Crafts</option>
                            <option value="upholstery">Upholstery</option>
                            <option value="other">Other</option>
                        </select>
                        @error('intended_use')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Admin Notes Section -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-[#800000] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-[#800000] mb-2">Fabric Selection Guidelines</h3>
                        <div class="text-sm text-[#800000] space-y-2">
                            <p>• <strong>Cotton:</strong> Best for everyday items, breathable and easy to work with</p>
                            <p>• <strong>Silk:</strong> Premium choice for luxury items, beautiful drape</p>
                            <p>• <strong>Polyester Cotton Blend:</strong> Durable and wrinkle-resistant</p>
                            <p>• <strong>Linen:</strong> Natural texture, great for summer items</p>
                            <p>• <strong>Canvas:</strong> Heavy-duty, perfect for bags and upholstery</p>
                            <p>• <strong>Jersey Knit:</strong> Stretchy, comfortable for clothing</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.custom_orders.create.choice') }}" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                    ← Back to Choices
                </a>
                
                <button type="submit" class="px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-medium flex items-center">
                    <span>Continue to Pattern Design</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectFabric(fabricType) {
    // Update radio button selection
    const radio = document.querySelector(`input[value="${fabricType}"]`);
    const radioDiv = document.querySelector(`[data-fabric="${fabricType}"] .fabric-radio`);
    
    // Clear all selections
    document.querySelectorAll('.fabric-radio').forEach(div => {
        div.classList.remove('bg-[#800000]', 'border-[#800000]');
        div.classList.add('border-gray-300');
    });
    
    // Set selected
    radio.checked = true;
    radioDiv.classList.remove('border-gray-300');
    radioDiv.classList.add('bg-[#800000]', 'border-[#800000]');
    
    // Update card border
    document.querySelectorAll('.fabric-option').forEach(card => {
        card.classList.remove('border-[#800000]', 'bg-red-50');
        card.classList.add('border-gray-200');
    });
    
    const selectedCard = document.querySelector(`[data-fabric="${fabricType}"]`);
    selectedCard.classList.remove('border-gray-200');
    selectedCard.classList.add('border-[#800000]', 'bg-red-50');
}

// Add form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const userId = document.getElementById('user_id').value;
    const fabricType = document.querySelector('input[name="fabric_type"]:checked');
    const quantity = document.getElementById('fabric_quantity_meters').value;
    const intendedUse = document.getElementById('intended_use').value;
    
    if (!userId) {
        e.preventDefault();
        alert('Please select a customer.');
        return;
    }
    
    if (!fabricType) {
        e.preventDefault();
        alert('Please select a fabric type.');
        return;
    }
    
    if (!quantity || quantity < 0.5) {
        e.preventDefault();
        alert('Please enter a valid quantity (minimum 0.5 meters).');
        return;
    }
    
    if (!intendedUse) {
        e.preventDefault();
        alert('Please select the intended use.');
        return;
    }
});
</script>
@endsection
