@extends('layouts.app')

@section('title', 'Select Fabric - Custom Order')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-100">
    <!-- Enhanced Progress Bar -->
    <div class="bg-white shadow-lg border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-center space-x-6">
                <div class="flex items-center group cursor-pointer">
                    <div class="relative">
                        <div class="w-10 h-10 bg-gradient-to-r from-red-700 to-red-800 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110">
                            1
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-3 font-bold" style="color:#800000;">Fabric</span>
                </div>
                <div class="w-20 h-1 bg-gradient-to-r from-red-700 to-gray-300 rounded-full"></div>
                <div class="flex items-center group cursor-pointer opacity-60">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 group-hover:scale-110">
                        2
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Pattern</span>
                </div>
                <div class="w-20 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center group cursor-pointer opacity-60">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 group-hover:scale-110">
                        3
                    </div>
                    <span class="ml-3 font-medium text-gray-500">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="container mx-auto px-4 py-12 relative z-10">
        <div class="text-center">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-red-700 to-red-900 bg-clip-text text-transparent mb-4">Choose Your Fabric</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Select the perfect fabric foundation for your custom Yakan pattern masterpiece</p>
        </div>
    </div>

    <!-- Fabric Selection Form -->
    <div class="container mx-auto px-4 py-8">
        <form action="{{ route('custom_orders.store.step1') }}" method="POST" id="fabricSelectionForm">
            @csrf
            
            <!-- Fabric Type Selection -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 mb-8">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900">Choose Fabric Type</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Cotton Option -->
                    <label class="fabric-option group relative bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-red-700 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1" 
                           onclick="selectFabricOption('cotton')">
                        <input type="radio" name="fabric_type" value="cotton" class="sr-only">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-200 transition-colors">
                                <svg class="w-8 h-8 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 mb-2">Cotton</h4>
                            <p class="text-sm text-gray-600">Soft, breathable, and comfortable for everyday wear</p>
                        </div>
                    </label>

                    <!-- Silk Option -->
                    <label class="fabric-option group relative bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1" style="--hover-border-color:#800000;" 
                           onclick="selectFabricOption('silk')">
                        <input type="radio" name="fabric_type" value="silk" class="sr-only">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors" style="background-color:#fff5f5;">
                                <svg class="w-8 h-8" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 mb-2">Silk</h4>
                            <p class="text-sm text-gray-600">Luxurious, smooth, and perfect for special occasions</p>
                        </div>
                    </label>

                    <!-- Linen Option -->
                    <label class="fabric-option group relative bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1" style="--hover-border-color:#800000;" 
                           onclick="selectFabricOption('linen')">
                        <input type="radio" name="fabric_type" value="linen" class="sr-only">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors" style="background-color:#fff5f5;">
                                <svg class="w-8 h-8" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 mb-2">Linen</h4>
                            <p class="text-sm text-gray-600">Lightweight, durable, and great for warm weather</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Quantity and Specifications -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            Quantity (meters)
                        </label>
                        <input type="number" 
                               id="fabric_quantity_meters" 
                               name="fabric_quantity_meters" 
                               min="0.5" 
                               max="100" 
                               step="0.1" 
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" 
                               value="{{ old('fabric_quantity_meters', 2) }}" 
                               required>
                    </div>

                    <!-- Intended Use -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Intended Use
                        </label>
                        <select id="intended_use" 
                                name="intended_use" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                required>
                            <option value="">Select intended use</option>
                            <option value="clothing">Clothing</option>
                            <option value="home_decor">Home Decor</option>
                            <option value="crafts">Crafts</option>
                        </select>
                    </div>
                </div>

                <!-- Special Requirements -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
 
                        </ .414- .414 .414 .4 .4.
                    </svg>
                    Special Requirements (Optional)
                </label>
                <textarea name="special_requirements" 
                          rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent" style="--tw-ring-color:#800000;"
                          placeholder="Any special requirements or preferences..."></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" 
                        class="group relative px-12 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white rounded-xl font-bold hover:from-red-800 hover:to-red-900 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Continue to Design Selection
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.fabric-option {
    transition: all 0.3s ease;
}

.fabric-option:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.fabric-option.selected {
    border-color: #800000 !important;
    background: linear-gradient(135deg, rgba(128, 0, 0, 0.1), rgba(128, 0, 0, 0.05)) !important;
    box-shadow: 0 0 30px rgba(128, 0, 0, 0.3) !important;
}

.fabric-option:hover {
    border-color: #800000 !important;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>
@endpush

@push('scripts')
<script>
function selectFabricOption(type) {
    // Remove selected class from all options
    document.querySelectorAll('.fabric-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    const clickedOption = event.currentTarget;
    clickedOption.classList.add('selected');
    
    // Check the radio button
    clickedOption.querySelector('input[type="radio"]').checked = true;
    
    // Add animation
    clickedOption.style.animation = 'pulse 0.5s ease-out';
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        warning: 'bg-yellow-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    notification.className += ` ${colors[type]}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Form validation
document.getElementById('fabricSelectionForm').addEventListener('submit', function(e) {
    const fabricType = document.querySelector('input[name="fabric_type"]:checked');
    const quantity = document.getElementById('fabric_quantity_meters').value;
    const intendedUse = document.getElementById('intended_use').value;
    
    if (!fabricType) {
        e.preventDefault();
        showNotification('Please select a fabric type', 'warning');
        return false;
    }
    
    if (!quantity || quantity < 0.5 || quantity > 100) {
        e.preventDefault();
        showNotification('Please enter a valid quantity between 0.5 and 100 meters', 'warning');
        return false;
    }
    
    if (!intendedUse) {
        e.preventDefault();
        showNotification('Please select the intended use for this fabric', 'warning');
        return false;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...</span>';
    
    showNotification('Processing your fabric selection...', 'info');
});
</script>
@endpush
@endsection
