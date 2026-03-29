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
            <input type="hidden" name="auth_token" id="step1AuthToken" value="{{ request('auth_token') }}">
            
            <!-- Fabric Type Selection -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mb-8 max-w-4xl mx-auto">
                <div class="flex items-center justify-center mb-6 text-center">
                    <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900">Choose Fabric Type</h3>
                </div>
                
                @if($fabricTypes->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center text-yellow-800">
                        <p>No fabric types available at the moment. Please try again later.</p>
                    </div>
                @else
                    <div class="flex flex-wrap justify-center gap-6">
                        @foreach($fabricTypes as $type)
                            <label class="fabric-option w-full md:w-[280px] group relative bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-red-700 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1" 
                                   onclick="selectFabricOption('{{ $type->id }}')">
                                <input type="radio" name="fabric_type" value="{{ $type->id }}" class="sr-only">
                                <div class="text-center">
                                    <h4 class="font-bold text-lg text-gray-900 mb-2">{{ $type->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $type->description ?? 'Premium fabric option' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quantity and Specifications -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mb-8 max-w-2xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            Meters
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
                        <p class="text-xs text-gray-500 mt-2">
                            <span id="priceInfo">Price will be calculated after you select a pattern in the next step</span>
                        </p>
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
                            @foreach($intendedUses as $use)
                                <option value="{{ $use->id }}">{{ $use->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Special Requirements -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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

// Form validation & AJAX submit
document.getElementById('fabricSelectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fabricType = document.querySelector('input[name="fabric_type"]:checked');
    const quantity = document.getElementById('fabric_quantity_meters').value;
    const intendedUse = document.getElementById('intended_use').value;
    
    if (!fabricType) { showNotification('Please select a fabric type', 'warning'); return; }
    if (!quantity || quantity < 0.5 || quantity > 100) { showNotification('Please enter a valid quantity between 0.5 and 100 meters', 'warning'); return; }
    if (!intendedUse) { showNotification('Please select the intended use for this fabric', 'warning'); return; }
    
    // Sync auth token from URL into hidden field
    const urlToken = new URLSearchParams(window.location.search).get('auth_token');
    if (urlToken) document.getElementById('step1AuthToken').value = urlToken;
    
    submitWizardForm('fabricSelectionForm', 'Saving fabric selection...', 'Almost there!');
});

// Update fabric cost dynamically based on selected pattern's price_per_meter
document.addEventListener('DOMContentLoaded', function() {
    const meterInput = document.getElementById('fabric_quantity_meters');
    const priceInfo = document.getElementById('priceInfo');
    
    function updatePriceInfo() {
        try {
            // Get selected pattern data from sessionStorage
            const wizardDataStr = sessionStorage.getItem('wizard_data');
            if (wizardDataStr) {
                const wizardData = JSON.parse(wizardDataStr);
                const meters = parseFloat(meterInput.value) || 0;
                
                // If pattern is selected, show dynamic price
                if (wizardData.pattern && wizardData.pattern.selected_ids && wizardData.pattern.selected_ids.length > 0) {
                    // We don't have direct access to pattern data here, so show this message
                    priceInfo.innerHTML = '<em>Pattern-specific pricing will be applied at checkout</em>';
                } else {
                    priceInfo.innerHTML = '<strong>Select a pattern first</strong> to see pricing based on that pattern';
                }
            }
        } catch (e) {
            console.log('Pattern data not yet available');
        }
    }
    
    if (meterInput) {
        // Check for pattern selection periodically
        setInterval(updatePriceInfo, 500);
        
        // Update on meter change
        meterInput.addEventListener('input', updatePriceInfo);
        meterInput.addEventListener('change', updatePriceInfo);
        
        // Initial check
        updatePriceInfo();
    }
});
</script>
@endpush

<!-- Wizard Loading Overlay -->
<div id="wizardLoadingOverlay" style="display:none;position:fixed;inset:0;z-index:9999;background:linear-gradient(135deg,#800000 0%,#500000 60%,#300000 100%);align-items:center;justify-content:center;">
    <div style="text-align:center;animation:wlFadeIn 0.4s ease;color:white;">
        <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:40px;">
            <div style="width:52px;height:52px;background:white;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:900;color:#800000;box-shadow:0 4px 20px rgba(0,0,0,0.3);">Y</div>
            <span style="font-size:28px;font-weight:800;letter-spacing:-0.5px;">Yakan</span>
        </div>
        <div style="width:64px;height:64px;border:4px solid rgba(255,255,255,0.3);border-top:4px solid white;border-radius:50%;animation:wlSpin 0.9s linear infinite;margin:0 auto 24px;"></div>
        <p style="font-size:18px;font-weight:600;margin-bottom:8px;" id="wlTitle">Saving your progress...</p>
        <p style="font-size:14px;opacity:0.7;" id="wlSubtitle">Please wait a moment</p>
        <div style="height:3px;background:rgba(255,255,255,0.15);border-radius:2px;overflow:hidden;width:200px;margin:20px auto 0;"><div id="wlProgress" style="height:100%;background:rgba(255,255,255,0.75);border-radius:2px;width:0%;"></div></div>
    </div>
</div>
<style>
@keyframes wlSpin { to { transform: rotate(360deg); } }
@keyframes wlFadeIn { from { opacity:0;transform:translateY(20px); } to { opacity:1;transform:translateY(0); } }
</style>
<script>
function showWizardLoading(title, subtitle) {
    var ov = document.getElementById('wizardLoadingOverlay');
    if (title) document.getElementById('wlTitle').textContent = title;
    if (subtitle) document.getElementById('wlSubtitle').textContent = subtitle;
    ov.style.display = 'flex';
    // Animate progress bar — reaches ~88% then slows (never appears stuck at 100%)
    var prog = document.getElementById('wlProgress');
    if (prog) { prog.style.transition = 'none'; prog.style.width = '0%'; void prog.offsetHeight; prog.style.transition = 'width 20s cubic-bezier(0.1,0.4,0.2,1)'; prog.style.width = '88%'; }
    if (window._wlTimer) clearTimeout(window._wlTimer);
    window._wlTimer = setTimeout(function() {
        var sub = document.getElementById('wlSubtitle');
        if (sub && ov.style.display === 'flex') sub.textContent = 'Still processing\u2026 almost there';
    }, 15000);
}
function hideWizardLoading() {
    document.getElementById('wizardLoadingOverlay').style.display = 'none';
    if (window._wlTimer) { clearTimeout(window._wlTimer); window._wlTimer = null; }
}
function getWizardAuthToken() {
    return new URLSearchParams(window.location.search).get('auth_token') ||
           localStorage.getItem('yakan_auth_token') || '';
}
function submitWizardForm(formId, title, subtitle) {
    var form = document.getElementById(formId);
    if (!form) return;
    showWizardLoading(title || 'Saving your progress...', subtitle || 'Please wait a moment');
    var formData = new FormData(form);
    var token = getWizardAuthToken();
    var url = form.action;
    if (token) {
        formData.set('auth_token', token);
        url += (url.indexOf('?') >= 0 ? '&' : '?') + 'auth_token=' + encodeURIComponent(token);
    }
    fetch(url, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && (data.redirect_url || data.next_url || data.review_url)) {
            window.location.href = data.redirect_url || data.next_url || data.review_url;
        } else if (data.success) { location.reload(); }
        else { hideWizardLoading(); alert(data.message || 'An error occurred. Please try again.'); }
    })
    .catch(function() { hideWizardLoading(); form.submit(); });
}
</script>
@endsection
