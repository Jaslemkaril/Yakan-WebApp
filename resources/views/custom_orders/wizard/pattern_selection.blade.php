@extends('layouts.app')

@section('title', 'Step 2: Pattern Selection - Custom Order')

@push('styles')
<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fade-out {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(-10px); }
}

@keyframes slide-in-right {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes pulse-once {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-fade-out {
    animation: fade-out 0.3s ease-out;
}

.animate-pulse-once {
    animation: pulse-once 0.6s ease-in-out;
}

.pattern-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.pattern-card:hover {
    transform: translateY(-4px);
}

.preview-container {
    transition: all 0.3s ease;
}

.preview-container:hover {
    border-color: #800000;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.status-badge {
    transition: all 0.2s ease;
}

.toast {
    animation: slide-in-right 0.3s ease-out;
}

/* Floating preview sidebar */
#floatingPreview {
    transform: translateX(0);
    transition: transform 0.3s ease-out;
}

#floatingPreview:hover {
    transform: translateX(-4px);
}

/* Responsive adjustments */
@media (max-width: 1023px) {
    #floatingPreview {
        width: 90vw;
        max-width: 400px;
        right: 5vw;
        left: 5vw;
    }
}

/* Pattern grid adjustment for floating sidebar */
@media (min-width: 1024px) {
    .pattern-grid-adjusted {
        margin-right: 24rem; /* 384px sidebar width */
    }
}

/* Hide floating preview on print */
@media print {
    #floatingPreview,
    #mobilePreviewToggle {
        display: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-100 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center font-semibold" style="background-color:#800000;">
                        ✓
                    </div>
                    <span class="ml-2 font-medium" style="color:#800000;">Fabric</span>
                </div>
                <div class="w-16 h-1 rounded-full" style="background-color:#800000;"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center font-semibold" style="background-color:#800000;">
                        2
                    </div>
                    <span class="ml-2 font-bold" style="color:#800000;">Pattern</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">
                        3
                    </div>
                    <span class="ml-2 text-gray-500">Review</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8 relative z-10">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Choose Your Yakan Patterns</h1>
            <p class="text-gray-600">Select from our traditional Yakan weaving patterns, each with unique cultural significance. You can choose multiple patterns to create your unique combination!</p>
        </div>

        <!-- Pattern Categories -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Choose by Pattern Type</h3>
            <div class="flex flex-wrap gap-3">
                <button onclick="filterPatterns('all')" class="category-btn px-4 py-2 text-white rounded-lg font-medium transition-colors" style="background-color:#800000;">
                    All Patterns
                </button>
                <button onclick="filterPatterns('traditional')" class="category-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Traditional
                </button>
                <button onclick="filterPatterns('geometric')" class="category-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Geometric
                </button>
                <button onclick="filterPatterns('floral')" class="category-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Nature & Floral
                </button>
                <button onclick="filterPatterns('abstract')" class="category-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Symbolic
                </button>
            </div>
        </div>

        <!-- Selected Patterns Display -->
        <div id="selectedPatternsDisplay" class="bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Your Pattern Selection</h3>
                <button onclick="clearSelection()" class="text-sm text-red-600 hover:text-red-700 font-medium">
                    Clear All
                </button>
            </div>
            <div id="selectedPatternsList" class="flex flex-wrap gap-3">
                <!-- Selected patterns will appear here -->
            </div>
            <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                <p class="text-sm text-purple-800">
                    <strong>Combination Info:</strong> 
                    <span id="combinationInfo">Select patterns to see combination details</span>
                </p>
            </div>
        </div>

        <!-- Floating Live Preview Sidebar -->
        <div class="fixed right-4 top-20 w-80 bg-white rounded-xl shadow-2xl border-4 z-50 max-h-[calc(100vh-6rem)] overflow-hidden" id="floatingPreview" style="display: block !important; visibility: visible !important; position: fixed !important; right: 1rem !important; top: 5rem !important; border-color:#800000;">
            <!-- Preview Header -->
            <div class="text-white p-4" style="background: linear-gradient(to right, #800000, #a00000);">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">Live Preview</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs opacity-90">Real-time</span>
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Controls -->
            <div class="p-3 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-wrap gap-2">
                    <button onclick="togglePreviewMode()" class="px-2 py-1 rounded text-xs font-medium transition-colors flex items-center" style="background-color:#fff5f5; color:#800000;">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Mode
                    </button>
                    <button onclick="toggleGrid()" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium hover:bg-gray-200 transition-colors flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Grid
                    </button>
                    <button onclick="resetPreview()" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium hover:bg-gray-200 transition-colors flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>
            
            <!-- Scrollable Preview Content -->
            <div class="p-4 overflow-y-auto max-h-[calc(100vh-12rem)]">
                <!-- Fabric Display -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-700 text-sm">Fabric</h4>
                        @if(session('wizard.fabric.type'))
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Selected</span>
                        @endif
                    </div>
                    <div class="h-20 rounded-lg border-2 border-gray-200 flex items-center justify-center transition-all duration-300 hover:border-purple-300 hover:shadow-md" id="fabricPreview">
                        @if(session('wizard.fabric.type'))
                            <div class="text-center transform hover:scale-105 transition-transform">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-1">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-gray-900">{{ session('wizard.fabric.type') }}</p>
                                <p class="text-xs text-gray-600">{{ session('wizard.fabric.quantity_meters') }}m</p>
                            </div>
                        @else
                            <div class="text-center">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-xs text-gray-400">No fabric</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Interactive 2D Canvas Preview - ENLARGED -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h4 class="font-medium text-gray-900 text-base">Live Pattern Preview</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Real-time customization</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="patternStatus" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Not selected</span>
                            <div id="combinedStatus" class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-green-600 font-medium">Live</span>
                            </div>
                        </div>
                    </div>
                    <canvas id="fabricCanvas" 
                            width="400" 
                            height="350" 
                            class="w-full rounded-xl border-4 shadow-2xl cursor-move transition-all duration-300 bg-white" style="border-color:#c08080;"
                            style="image-rendering: crisp-edges; min-height: 350px;">
                    </canvas>
                    
                    <!-- Canvas Controls -->
                    <div class="mt-4 space-y-2.5">
                        <!-- Scale Control -->
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600">Scale</label>
                            <input type="range" id="patternScale" min="0.5" max="3" step="0.1" value="1" 
                                   class="w-32 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                   oninput="updateCanvasPreview()">
                            <span id="scaleValue" class="text-xs text-gray-600 w-8">1.0x</span>
                        </div>
                        
                        <!-- Rotation Control -->
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600">Rotate</label>
                            <input type="range" id="patternRotation" min="0" max="360" step="5" value="0" 
                                   class="w-32 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                   oninput="updateCanvasPreview()">
                            <span id="rotationValue" class="text-xs text-gray-600 w-8">0°</span>
                        </div>
                        
                        <!-- Opacity Control -->
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600">Opacity</label>
                            <input type="range" id="patternOpacity" min="0.3" max="1" step="0.1" value="0.85" 
                                   class="w-32 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                   oninput="updateCanvasPreview()">
                            <span id="opacityValue" class="text-xs text-gray-600 w-8">85%</span>
                        </div>
                        
                        <!-- Blend Mode Control (Hidden) -->
                        <div class="hidden">
                            <select id="blendMode" class="text-xs border border-gray-300 rounded px-2 py-1 cursor-pointer" onchange="updateCanvasPreview()">
                                <option value="multiply">Multiply</option>
                                <option value="overlay">Overlay</option>
                                <option value="normal">Normal</option>
                                <option value="screen">Screen</option>
                                <option value="darken">Darken</option>
                                <option value="lighten">Lighten</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Color Customization Section -->
                    <div class="mt-5 p-3 rounded-lg border shadow-sm" style="background: linear-gradient(to right, #fff5f5, #fff0f0); border-color:#e0b0b0;">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-xs font-bold" style="color:#800000;">🎨 Color Customization</h5>
                            <button type="button" onclick="toggleColorControls()" class="text-xs font-medium" style="color:#800000;">
                                <span id="colorToggleText">Show</span>
                            </button>
                        </div>
                        
                        <div id="colorControls" class="space-y-2 hidden">
                            <!-- Hue Rotation -->
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-700">Hue</label>
                                <input type="range" id="hueRotation" min="0" max="360" step="1" value="0" 
                                       class="w-24 h-1 bg-gradient-to-r from-red-500 via-green-500 to-blue-500 rounded-lg appearance-none cursor-pointer"
                                       oninput="updateCanvasPreview()">
                                <span id="hueValue" class="text-xs text-gray-600 w-10">0°</span>
                            </div>
                            
                            <!-- Saturation -->
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-700">Saturation</label>
                                <input type="range" id="saturation" min="0" max="200" step="10" value="100" 
                                       class="w-24 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                       oninput="updateCanvasPreview()">
                                <span id="saturationValue" class="text-xs text-gray-600 w-10">100%</span>
                            </div>
                            
                            <!-- Brightness -->
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-700">Brightness</label>
                                <input type="range" id="brightness" min="50" max="150" step="5" value="100" 
                                       class="w-24 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                       oninput="updateCanvasPreview()">
                                <span id="brightnessValue" class="text-xs text-gray-600 w-10">100%</span>
                            </div>
                            
                            <!-- Color Overlay -->
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-700">Color Tint</label>
                                <input type="color" id="colorTint" value="#ffffff" 
                                       class="w-16 h-6 rounded cursor-pointer border border-gray-300"
                                       oninput="updateCanvasPreview()">
                                <input type="range" id="tintStrength" min="0" max="100" step="5" value="0" 
                                       class="w-16 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                       oninput="updateCanvasPreview()">
                            </div>
                            
                            <!-- Quick Color Presets -->
                            <div class="mt-2">
                                <p class="text-xs font-medium text-gray-700 mb-1">Quick Colors:</p>
                                <div class="flex gap-1 flex-wrap">
                                    <button type="button" onclick="applyColorPreset('red')" class="w-6 h-6 rounded-full bg-red-500 hover:ring-2 ring-purple-400 transition-all" title="Red"></button>
                                    <button type="button" onclick="applyColorPreset('blue')" class="w-6 h-6 rounded-full bg-blue-500 hover:ring-2 ring-purple-400 transition-all" title="Blue"></button>
                                    <button type="button" onclick="applyColorPreset('green')" class="w-6 h-6 rounded-full bg-green-500 hover:ring-2 ring-purple-400 transition-all" title="Green"></button>
                                    <button type="button" onclick="applyColorPreset('yellow')" class="w-6 h-6 rounded-full bg-yellow-500 hover:ring-2 ring-purple-400 transition-all" title="Yellow"></button>
                                    <button type="button" onclick="applyColorPreset('purple')" class="w-6 h-6 rounded-full bg-purple-500 hover:ring-2 ring-purple-400 transition-all" title="Purple"></button>
                                    <button type="button" onclick="applyColorPreset('pink')" class="w-6 h-6 rounded-full bg-pink-500 hover:ring-2 ring-purple-400 transition-all" title="Pink"></button>
                                    <button type="button" onclick="applyColorPreset('orange')" class="w-6 h-6 rounded-full bg-orange-500 hover:ring-2 ring-purple-400 transition-all" title="Orange"></button>
                                    <button type="button" onclick="applyColorPreset('teal')" class="w-6 h-6 rounded-full bg-teal-500 hover:ring-2 ring-purple-400 transition-all" title="Teal"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-4 flex gap-2">
                        <button type="button" onclick="resetCanvas()" 
                                class="flex-1 px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-medium transition-colors">
                            Reset
                        </button>
                        <button type="button" onclick="saveCustomization()" 
                                class="flex-1 px-2 py-1 bg-maroon-100 hover:bg-maroon-200 text-maroon-700 rounded text-xs font-medium transition-colors">
                            Save
                        </button>
                    </div>
                </div>
                
                <!-- Pattern Info Card -->
                <div class="mt-4">
                    <div class="rounded-lg p-3 border" style="background: linear-gradient(to bottom right, #fff5f5, #fff0f0); border-color:#e0b0b0;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold" style="color:#800000;">Selected Pattern</p>
                                <p id="selectedPatternName" class="text-sm font-bold text-gray-800 mt-1">None</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-600">Fabric</p>
                                <p class="text-sm font-medium text-gray-800">{{ session('wizard.fabric.type', 'Not selected') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Toggle Button -->
        <button onclick="toggleMobilePreview()" class="lg:hidden fixed bottom-4 right-4 w-14 h-14 text-white rounded-full shadow-lg z-40 flex items-center justify-center" id="mobilePreviewToggle" style="background-color:#800000;">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>

        <!-- Pattern Grid with adjusted spacing for floating sidebar -->
        <form action="{{ route('custom_orders.store.pattern') }}" method="POST" class="pr-96 lg:pr-0">
            @csrf
            <input type="hidden" name="product_id" value="{{ session('wizard.product.id') }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                @foreach($patterns as $pattern)
                    <div class="pattern-card bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 cursor-pointer border-2 border-transparent relative" style="--hover-border:#800000;" 
                         onclick="togglePatternSelection({{ $pattern->id }})"
                         data-pattern-id="{{ $pattern->id }}"
                         data-category="{{ $pattern->category }}"
                         data-pattern-name="{{ $pattern->name }}"
                         data-pattern-svg="{{ $pattern->hasSvg() ? base64_encode($pattern->getSvgContent()) : '' }}"
                         data-pattern-image="{{ $pattern->media->isNotEmpty() ? $pattern->media->first()->url : '' }}">
                        
                        <!-- Multi-Selection Checkbox -->
                        <div class="absolute top-2 left-2 z-20">
                            <div class="w-6 h-6 bg-white border-2 border-gray-300 rounded flex items-center justify-center transition-all duration-200" id="checkbox-{{ $pattern->id }}">
                                <svg class="w-4 h-4 text-purple-600 hidden" id="check-{{ $pattern->id }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Pattern Preview -->
                        <div class="h-48 bg-gradient-to-br from-purple-100 to-red-100 relative overflow-hidden">
                            @if($pattern->hasSvg())
                                <!-- Display SVG Pattern -->
                                <div class="absolute inset-0 flex items-center justify-center p-4">
                                    {!! $pattern->getSvgContent() !!}
                                </div>
                            @elseif($pattern->media->isNotEmpty())
                                @php
                                    $firstMedia = $pattern->media->first();
                                    $patternImage = $firstMedia->url;
                                @endphp
                                <!-- Display Pattern Image from Database -->
                                <img src="{{ $patternImage }}" 
                                     alt="{{ $firstMedia->alt_text ?? $pattern->name }}"
                                     class="w-full h-full object-cover"
                                     data-pattern-image="{{ $patternImage }}"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @else
                                <!-- No Pattern Image Available -->
                                <div class="absolute inset-0 flex items-center justify-center" data-pattern-svg="">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 text-purple-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        <p class="text-purple-600 font-medium">{{ $pattern->name }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Overlay Badges -->
                            <div class="absolute inset-0 pointer-events-none">
                                <!-- Difficulty Badge -->
                                <div class="absolute bottom-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 bg-{{ $pattern->difficulty_color }}-100 text-{{ $pattern->difficulty_color }}-800 text-xs font-bold rounded-full shadow-md">
                                        {{ ucfirst($pattern->difficulty_level) }}
                                    </span>
                                </div>
                                
                                <!-- Popularity Indicator -->
                                @if($pattern->popularity_score > 10)
                                    <div class="absolute bottom-2 left-2">
                                        <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full shadow-md">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Popular
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Pattern Info -->
                        <div class="p-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $pattern->name }}</h3>
                            @if($pattern->description)
                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($pattern->description, 100) }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full font-medium" style="background-color:#fff5f5; color:#800000;">
                                        {{ ucfirst($pattern->category) }}
                                    </span>
                                    <span class="text-gray-500">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $pattern->estimated_days }} days
                                    </span>
                                </div>
                                <span class="font-bold" style="color:#800000;">
                                    ×{{ number_format($pattern->base_price_multiplier, 2) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Hidden Radio Input -->
                        <input type="radio" name="pattern_id" value="{{ $pattern->id }}" class="hidden" id="pattern_{{ $pattern->id }}">
                    </div>
                @endforeach
            </div>

            <!-- Selected Pattern Display -->
            <div id="selectedPatternInfo" class="hidden mb-6 p-4 rounded-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                <h4 class="font-bold mb-2" style="color:#800000;">Selected Pattern: <span id="selectedPatternName"></span></h4>
                <p class="text-gray-700 text-sm" id="selectedPatternDescription"></p>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between items-center">
                <a href="{{ route('custom_orders.create.step1') }}" class="inline-flex items-center px-6 py-3 bg-white border-2 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors" style="border-color:#c08080;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Fabric
                </a>
                
                <button type="button" onclick="submitPatternSelection()" class="inline-flex items-center px-6 py-3 text-white rounded-lg font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none" id="continueBtn" style="background-color:#800000;" onmouseover="if(!this.disabled) this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'" disabled>
                    <span id="reviewBtnText">Review Order</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="reviewBtnIcon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span id="reviewBtnPulse" class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-ping hidden"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedPattern = null;
let previewZoom = 1;
let showGrid = false;
let previewMode = 'default'; // 'default', 'fabric-only', 'pattern-only'

function selectPattern(patternId) {
    // Remove previous selection
    document.querySelectorAll('.pattern-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`[data-pattern-id="${patternId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('border-purple-600', 'bg-purple-50');
        
        // Add selection animation
        selectedCard.style.transform = 'scale(0.98)';
        setTimeout(() => {
            selectedCard.style.transform = 'scale(1)';
        }, 150);
    }
    
    // Enable continue button
    document.getElementById('continueBtn').disabled = false;
    
    // Update status badges
    updateStatusBadges('selected');
    
    // Update live preview with animation
    updateLivePreview(patternId);
}

function updateStatusBadges(status) {
    const patternStatus = document.getElementById('patternStatus');
    const combinedStatus = document.getElementById('combinedStatus');
    
    if (status === 'selected') {
        patternStatus.className = 'text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full';
        patternStatus.textContent = 'Selected';
        
        combinedStatus.innerHTML = `
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs text-green-600">Ready</span>
        `;
    } else {
        patternStatus.className = 'text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full';
        patternStatus.textContent = 'Not selected';
        
        combinedStatus.innerHTML = `
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs text-gray-500">Waiting</span>
        `;
    }
}

function updateLivePreview(patternId) {
    // Get pattern data from the card
    const patternCard = document.querySelector(`[data-pattern-id="${patternId}"]`);
    if (!patternCard) return;
    
    const patternName = patternCard.querySelector('h3').textContent;
    const patternDescription = patternCard.querySelector('p')?.textContent || '';
    const patternImage = patternCard.getAttribute('data-pattern-image');
    const patternSVG = patternCard.getAttribute('data-pattern-svg');
    
    // Add loading animation
    const patternPreview = document.getElementById('patternPreviewLive');
    const largeCombinedPreview = document.getElementById('largeCombinedPreview');
    
    // Show loading state with smooth transition
    showLoadingState(patternPreview);
    showLoadingState(largeCombinedPreview);
    
    // Simulate loading delay for better UX
    setTimeout(() => {
        // Update small pattern preview
        if (patternImage) {
            patternPreview.innerHTML = `
                <img src="${patternImage}" 
                     alt="${patternName}"
                     class="w-full h-full object-cover rounded-lg animate-fade-in shadow-inner"
                     style="animation-duration: 0.5s;">
            `;
        } else if (patternSVG) {
            patternPreview.innerHTML = `
                <div class="w-full h-full flex items-center justify-center animate-fade-in" style="background: url('data:image/svg+xml;base64,${patternSVG}') center/cover no-repeat;"></div>
            `;
        } else {
            patternPreview.innerHTML = `
                <div class="text-center animate-fade-in p-2">
                    <svg class="w-10 h-10 text-purple-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <p class="text-xs text-gray-600 font-medium">${patternName}</p>
                </div>
            `;
        }
        
        // Update large combined preview with enhanced visuals
        updateEnhancedPreview(largeCombinedPreview, patternImage, patternSVG, patternName, patternDescription);
        
        // Update canvas preview
        updateCanvasPreview();
    }, 300);
}

function showLoadingState(element) {
    element.innerHTML = `
        <div class="text-center">
            <div class="w-8 h-8 border-2 border-purple-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
            <p class="text-gray-400 text-sm">Loading...</p>
        </div>
    `;
}

function updateEnhancedPreview(element, patternImage, svgData, patternName, patternDescription) {
    const fabricType = '{{ session("wizard.fabric.type", "Cotton") }}';
    const gridOverlay = showGrid ? '<div class="absolute inset-0 pointer-events-none" style="background-image: repeating-linear-gradient(0deg, rgba(0,0,0,0.05) 0px, transparent 1px, transparent 19px, rgba(0,0,0,0.05) 20px), repeating-linear-gradient(90deg, rgba(0,0,0,0.05) 0px, transparent 1px, transparent 19px, rgba(0,0,0,0.05) 20px); background-size: 20px 20px;"></div>' : '';
    
    if (previewMode === 'fabric-only') {
        // Show only fabric texture
        element.innerHTML = `
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-red-50">
                <div class="absolute inset-0 opacity-40" style="background: linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%), linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%); background-size: 30px 30px; background-position: 0 0, 15px 15px;"></div>
                ${gridOverlay}
                <div class="absolute bottom-2 left-2 right-2 bg-white/90 backdrop-blur-sm rounded-lg p-2 shadow-lg">
                    <p class="text-xs font-semibold text-gray-800">Fabric: ${fabricType}</p>
                </div>
            </div>
        `;
    } else if (previewMode === 'pattern-only') {
        // Show only pattern with enhanced display
        if (patternImage) {
            element.innerHTML = `
                <div class="absolute inset-0 bg-white">
                    <img src="${patternImage}" 
                         alt="${patternName}"
                         class="w-full h-full object-contain p-4 animate-fade-in"
                         style="transform: scale(${previewZoom}); transition: transform 0.3s ease;">
                    ${gridOverlay}
                    <div class="absolute bottom-2 left-2 right-2 bg-black/70 backdrop-blur-sm rounded-lg p-2 shadow-lg">
                        <p class="text-xs font-semibold text-white">${patternName}</p>
                    </div>
                </div>
            `;
        } else if (svgData) {
            element.innerHTML = `
                <div class="absolute inset-0 bg-white flex items-center justify-center">
                    <div class="w-full h-full animate-fade-in" style="background: url('data:image/svg+xml;base64,${svgData}') center/contain no-repeat; transform: scale(${previewZoom}); transition: transform 0.3s ease;"></div>
                    ${gridOverlay}
                </div>
            `;
        } else {
            element.innerHTML = `
                <div class="absolute inset-0 bg-gradient-to-br from-purple-100 to-red-100 flex items-center justify-center">
                    <div class="text-center p-4">
                        <svg class="w-16 h-16 text-purple-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <p class="text-sm font-bold text-gray-700">${patternName}</p>
                    </div>
                    ${gridOverlay}
                </div>
            `;
        }
    } else {
        // Default: show combined fabric + pattern with enhanced visuals
        if (patternImage) {
            element.innerHTML = `
                <div class="absolute inset-0 overflow-hidden" style="background: linear-gradient(135deg, #f5f3ff 0%, #fef2f2 100%);">
                    <!-- Fabric texture background with weave pattern -->
                    <div class="absolute inset-0">
                        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="fabricWeave" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <rect width="20" height="20" fill="${getFabricColor(fabricType)}"/>
                                    <path d="M0 10h20M10 0v20" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
                                    <circle cx="5" cy="5" r="0.5" fill="rgba(0,0,0,0.05)"/>
                                    <circle cx="15" cy="15" r="0.5" fill="rgba(0,0,0,0.05)"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#fabricWeave)" opacity="0.4"/>
                        </svg>
                    </div>
                    <!-- Pattern overlay with realistic blend -->
                    <div class="absolute inset-0 flex items-center justify-center" style="mix-blend-mode: multiply;">
                        <img src="${patternImage}" 
                             alt="${patternName}"
                             class="w-full h-full object-cover animate-fade-in"
                             style="transform: scale(${previewZoom}); transition: transform 0.3s ease; opacity: 0.75; filter: contrast(1.1) saturate(1.2);">
                    </div>
                    <!-- Subtle overlay for depth -->
                    <div class="absolute inset-0" style="background: radial-gradient(circle at center, transparent 40%, rgba(0,0,0,0.05) 100%);"></div>
                    ${gridOverlay}
                    <!-- Info overlay -->
                    <div class="absolute bottom-2 left-2 right-2 bg-gradient-to-r from-purple-900/90 to-red-900/90 backdrop-blur-md rounded-lg p-2 shadow-xl border border-white/20">
                        <p class="text-xs font-bold text-white">${patternName}</p>
                        <p class="text-xs text-purple-100">Merged on ${fabricType}</p>
                    </div>
                </div>
            `;
        } else if (svgData) {
            element.innerHTML = `
                <div class="absolute inset-0">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-red-50">
                        <div class="absolute inset-0 opacity-20" style="background: linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%), linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%); background-size: 30px 30px; background-position: 0 0, 15px 15px;"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-3/4 h-3/4 animate-fade-in" style="background: url('data:image/svg+xml;base64,${svgData}') center/contain no-repeat; transform: scale(${previewZoom}); transition: transform 0.3s ease; opacity: 0.8;"></div>
                    </div>
                    ${gridOverlay}
                </div>
            `;
        } else {
            element.innerHTML = `
                <div class="absolute inset-0 bg-gradient-to-br from-purple-100 to-red-100">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center p-4">
                            <svg class="w-16 h-16 text-purple-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <p class="text-sm font-bold text-gray-700">${patternName}</p>
                            <p class="text-xs text-gray-600 mt-1">Select to preview</p>
                        </div>
                    </div>
                    ${gridOverlay}
                </div>
            `;
        }
    }
}

// New UX functions
function togglePreviewMode() {
    const modes = ['default', 'fabric-only', 'pattern-only'];
    const currentIndex = modes.indexOf(previewMode);
    previewMode = modes[(currentIndex + 1) % modes.length];
    
    // Re-update preview if pattern is selected
    if (selectedPattern) {
        updateLivePreview(selectedPattern);
    }
    
    // Show toast notification
    showToast(`Preview mode: ${previewMode.replace('-', ' ')}`);
}

function resetPreview() {
    previewZoom = 1;
    showGrid = false;
    previewMode = 'default';
    
    // Clear selection
    document.querySelectorAll('.pattern-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50');
    });
    
    // Reset UI
    document.getElementById('continueBtn').disabled = true;
    document.getElementById('patternPreviewLive').innerHTML = `
        <div class="text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-400 text-sm">Click a pattern below</p>
            <p class="text-gray-400 text-xs mt-1">to see preview</p>
        </div>
    `;
    
    document.getElementById('combinedPreview').innerHTML = `
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <div class="w-8 h-8 bg-gray-200 rounded-full animate-pulse mx-auto mb-2"></div>
                <p class="text-gray-400 text-sm">Select a pattern</p>
                <p class="text-gray-400 text-xs mt-1">to see combination</p>
            </div>
        </div>
    `;
    
    document.getElementById('largeCombinedPreview').innerHTML = `
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <p class="text-gray-400 font-medium">Interactive Preview</p>
                <p class="text-gray-400 text-sm mt-1">Select a pattern to begin</p>
            </div>
        </div>
    `;
    
    updateStatusBadges('reset');
    showToast('Preview reset');
}

// Multiple Pattern Selection
let selectionMode = 'single'; // 'single', 'multiple', 'custom'
let selectedPatterns = new Set();
let maxPatterns = 5;

function setSelectionMode(mode) {
    console.log('Setting selection mode to:', mode);
    selectionMode = mode;
    
    // Update button styles
    document.querySelectorAll('.selection-mode-btn').forEach(btn => {
        btn.classList.remove('bg-purple-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    const activeBtn = document.getElementById(mode + 'ModeBtn');
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
        activeBtn.classList.add('bg-purple-600', 'text-white');
    }
    
    // Update info text
    const infoText = {
        'single': '✨ <strong>Single Pattern:</strong> Choose one traditional Yakan pattern for your fabric',
        'multiple': '🎨 <strong>Multiple Patterns:</strong> Combine up to 5 patterns for a unique design',
        'custom': '✏️ <strong>Custom Design:</strong> Work with our weavers to create a unique pattern'
    };
    
    document.getElementById('selectionModeInfo').innerHTML = `<p>${infoText[mode]}</p>`;
    
    // Show/hide selected patterns display
    const selectedDisplay = document.getElementById('selectedPatternsDisplay');
    if (mode === 'multiple') {
        selectedDisplay.classList.remove('hidden');
    } else {
        selectedDisplay.classList.add('hidden');
        // Clear selection when switching to single mode
        if (selectedPatterns.size > 1) {
            clearSelection();
        }
    }
    
    // Clear selection when switching modes
    if (mode !== 'multiple') {
        clearSelection();
    }
    
    console.log('Selection mode changed to:', mode, 'with', selectedPatterns.size, 'patterns selected');
    showToast(`Switched to ${mode} pattern selection`);
}

function togglePatternSelection(patternId) {
    console.log('Pattern clicked:', patternId);
    
    const card = document.querySelector(`[data-pattern-id="${patternId}"]`);
    const checkbox = document.getElementById(`checkbox-${patternId}`);
    const check = document.getElementById(`check-${patternId}`);
    
    console.log('Elements found:', { card: !!card, checkbox: !!checkbox, check: !!check });
    
    if (!card || !checkbox || !check) {
        console.error('Required elements not found');
        return;
    }
    
    // Get pattern image URL from card data attribute or img element
    let patternImageUrl = card.getAttribute('data-pattern-image');
    
    // If not in card attribute, try to find img element inside
    if (!patternImageUrl || patternImageUrl === '') {
        const imgElement = card.querySelector('img[data-pattern-image]');
        if (imgElement) {
            patternImageUrl = imgElement.getAttribute('data-pattern-image') || imgElement.src;
        }
    }
    
    // If still not found, try to get the src from any img element in the card
    if (!patternImageUrl || patternImageUrl === '') {
        const anyImg = card.querySelector('.h-48 img');
        if (anyImg) {
            patternImageUrl = anyImg.src;
        }
    }
    
    // Get SVG data from card attribute
    const svgData = card.getAttribute('data-pattern-svg');
    const patternName = card.getAttribute('data-pattern-name');
    const patternCategory = card.getAttribute('data-category');
    
    console.log('Pattern data extracted:', { 
        imageUrl: patternImageUrl || 'missing',
        svgData: svgData ? `${svgData.substring(0, 50)}...` : 'missing',
        name: patternName,
        category: patternCategory
    });
    
    if (selectionMode === 'single') {
        // Single pattern mode - replace selection
        selectedPatterns.clear();
        selectedPatterns.add(patternId);
        
        // Update all checkboxes
        document.querySelectorAll('[id^="checkbox-"]').forEach(cb => {
            cb.classList.remove('border-purple-600', 'bg-purple-50');
            cb.classList.add('border-gray-300');
        });
        document.querySelectorAll('[id^="check-"]').forEach(ch => ch.classList.add('hidden'));
        
        // Update selected checkbox
        checkbox.classList.remove('border-gray-300');
        checkbox.classList.add('border-purple-600', 'bg-purple-50');
        check.classList.remove('hidden');
        
        // Store current selection globally
        selectedPattern = patternId;
        
        // Load pattern into canvas
        const patternData = {
            id: patternId,
            name: patternName,
            category: patternCategory,
            svgData: svgData,
            imageUrl: patternImageUrl
        };
        
        console.log('Loading pattern to canvas:', patternData);
        loadPatternToCanvas(patternData);
        
        // Update pattern status badge
        const statusEl = document.getElementById('patternStatus');
        if (statusEl) {
            statusEl.textContent = 'Selected';
            statusEl.className = 'text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium';
        }
        
        // Update selected pattern name in info card
        const nameEl = document.getElementById('selectedPatternName');
        if (nameEl) {
            nameEl.textContent = patternName;
        }
        
        // Update status badges
        updateStatusBadges('selected');
        
    } else if (selectionMode === 'multiple') {
        // Multiple patterns mode
        if (selectedPatterns.has(patternId)) {
            selectedPatterns.delete(patternId);
            checkbox.classList.remove('border-purple-600', 'bg-purple-50');
            checkbox.classList.add('border-gray-300');
            check.classList.add('hidden');
        } else {
            if (selectedPatterns.size >= maxPatterns) {
                showToast(`Maximum ${maxPatterns} patterns allowed`);
                return;
            }
            selectedPatterns.add(patternId);
            checkbox.classList.remove('border-gray-300');
            checkbox.classList.add('border-purple-600', 'bg-purple-50');
            check.classList.remove('hidden');
            
            // Load first pattern into canvas
            if (selectedPatterns.size === 1) {
                const patternData = {
                    id: patternId,
                    name: patternName,
                    category: patternCategory,
                    svgData: svgData,
                    imageUrl: patternImageUrl
                };
                loadPatternToCanvas(patternData);
            }
        }
    }
    
    console.log('Selected patterns after toggle:', Array.from(selectedPatterns));
    
    // Update Review Order button state
    updateReviewOrderButton();
    
    updateSelectedPatternsDisplay();
    updateLivePreview();
}

function clearSelection() {
    selectedPatterns.clear();
    
    // Reset all checkboxes
    document.querySelectorAll('[id^="checkbox-"]').forEach(cb => {
        cb.classList.remove('border-purple-600', 'bg-purple-50');
        cb.classList.add('border-gray-300');
    });
    document.querySelectorAll('[id^="check-"]').forEach(ch => ch.classList.add('hidden'));
    
    // Reset card styles
    document.querySelectorAll('.pattern-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50');
    });
    
    // Update Review Order button state
    updateReviewOrderButton();
    
    updateSelectedPatternsDisplay();
    updateLivePreview();
    showToast('Selection cleared');
}

function updateSelectedPatternsDisplay() {
    const display = document.getElementById('selectedPatternsDisplay');
    const list = document.getElementById('selectedPatternsList');
    const info = document.getElementById('combinationInfo');
    
    if (selectedPatterns.size === 0) {
        display.classList.add('hidden');
        return;
    }
    
    display.classList.remove('hidden');
    
    // Build selected patterns list
    list.innerHTML = '';
    selectedPatterns.forEach(patternId => {
        const card = document.querySelector(`[data-pattern-id="${patternId}"]`);
        const name = card.dataset.patternName;
        const svg = card.dataset.patternSvg;
        
        const badge = document.createElement('div');
        badge.className = 'flex items-center space-x-2 bg-purple-100 text-purple-800 px-3 py-2 rounded-lg';
        badge.innerHTML = `
            <div class="w-6 h-6 rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/contain no-repeat;"></div>
            <span class="text-sm font-medium">${name}</span>
            <button onclick="removePattern(${patternId})" class="text-purple-600 hover:text-purple-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;
        list.appendChild(badge);
    });
    
    // Update combination info
    if (selectedPatterns.size === 1) {
        info.textContent = 'Single pattern selected';
    } else {
        const complexity = Array.from(selectedPatterns).reduce((total, id) => {
            const card = document.querySelector(`[data-pattern-id="${id}"]`);
            return total + (parseInt(card.dataset.complexity || 5));
        }, 0);
        
        info.textContent = `${selectedPatterns.size} patterns combined • Estimated complexity: ${complexity}/50 • Additional cost: ${(selectedPatterns.size - 1) * 15}%`;
    }
}

function removePattern(patternId) {
    selectedPatterns.delete(patternId);
    const checkbox = document.getElementById(`checkbox-${patternId}`);
    const check = document.getElementById(`check-${patternId}`);
    
    checkbox.classList.remove('border-purple-600', 'bg-purple-50');
    checkbox.classList.add('border-gray-300');
    check.classList.add('hidden');
    
    updateSelectedPatternsDisplay();
    updateLivePreview();
}

function updateLivePreview() {
    const patternPreview = document.getElementById('patternPreviewLive');
    const combinedPreview = document.getElementById('combinedPreview');
    const largePreview = document.getElementById('largeCombinedPreview');
    const continueBtn = document.getElementById('continueBtn');
    
    console.log('Updating live preview, selected patterns:', Array.from(selectedPatterns));
    
    if (selectedPatterns.size === 0) {
        // Reset to empty state
        patternPreview.innerHTML = `
            <div class="text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-400 text-sm">Select patterns below</p>
            </div>
        `;
        
        combinedPreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <div class="w-8 h-8 bg-gray-200 rounded-full animate-pulse mx-auto mb-2"></div>
                    <p class="text-gray-400 text-sm">Select patterns</p>
                </div>
            </div>
        `;
        
        largePreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <p class="text-gray-400 font-medium">Interactive Preview</p>
                </div>
            </div>
        `;
        
        continueBtn.disabled = true;
        return;
    }
    
    // Generate combined pattern preview
    if (selectedPatterns.size === 1) {
        // Single pattern
        const patternId = Array.from(selectedPatterns)[0];
        const card = document.querySelector(`[data-pattern-id="${patternId}"]`);
        
        console.log('Looking for pattern card:', patternId, card);
        
        if (!card) {
            console.error('Pattern card not found:', patternId);
            return;
        }
        
        // Get SVG data from stored dataset
        let svg = card.dataset.patternSvg;
        const name = card.dataset.patternName || 'Pattern';
        
        // If not stored, try to extract it again
        if (!svg) {
            const patternDiv = card.querySelector('.w-32');
            if (patternDiv && patternDiv.style.background) {
                const bgStyle = patternDiv.style.background;
                const match = bgStyle.match(/base64,([^)]+)/);
                    if (match && match[1]) {
                        svg = match[1];
                        card.dataset.patternSvg = svg; // Store for next time
                }
            }
        }
        
        console.log('Pattern data:', { 
            patternId, 
            svg: svg ? `${svg.substring(0, 50)}...` : 'missing', 
            name
        });
        
        if (!svg) {
            console.error('SVG data not found for pattern:', patternId);
            // Show a placeholder
            patternPreview.innerHTML = `
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-2 rounded bg-purple-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">${name}</p>
                    <p class="text-xs text-gray-500">Pattern selected</p>
                </div>
            `;
            return;
        }
        
        console.log('Updating preview with SVG data');
        
        patternPreview.innerHTML = `
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-2 rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/contain no-repeat;"></div>
                <p class="text-sm font-medium text-gray-900">${name}</p>
                <p class="text-xs text-gray-500">Single pattern</p>
            </div>
        `;
        
        combinedPreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-full h-full rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/cover no-repeat;"></div>
            </div>
        `;
        
        largePreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-full h-full rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/cover no-repeat; transform: scale(${previewZoom});"></div>
            </div>
        `;
        
    } else {
        // Multiple patterns - create combination
        console.log('Creating combined preview for multiple patterns...');
        
        const svgs = Array.from(selectedPatterns).map(id => {
            const card = document.querySelector(`[data-pattern-id="${id}"]`);
            if (!card) {
                console.error('Card not found for pattern:', id);
                return null;
            }
            
            // Get SVG from stored dataset or extract it
            let svg = card.dataset.patternSvg;
            if (!svg) {
                const patternDiv = card.querySelector('.w-32');
                if (patternDiv && patternDiv.style.background) {
                    const bgStyle = patternDiv.style.background;
                    const match = bgStyle.match(/base64,([^)]+)/);
                    if (match && match[1]) {
                        svg = match[1];
                        card.dataset.patternSvg = svg;
                    }
                }
            }
            return svg;
        }).filter(svg => svg);
        
        console.log('Multiple patterns preview:', { 
            count: svgs.length, 
            svgs: svgs.map(s => s ? `${s.substring(0, 30)}...` : 'missing'),
            selectedPatterns: Array.from(selectedPatterns)
        });
        
        if (svgs.length === 0) {
            console.error('No valid SVG data found for multiple patterns');
            return;
        }
        
        // Create combined pattern using CSS masks/gradients
        const combinedSvg = createCombinedPattern(svgs);
        console.log('Combined pattern CSS:', combinedSvg);
        
        patternPreview.innerHTML = `
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-2 rounded" style="background: ${combinedSvg};"></div>
                <p class="text-sm font-medium text-gray-900">${selectedPatterns.size} Patterns</p>
                <p class="text-xs text-gray-500">Combined design</p>
            </div>
        `;
        
        combinedPreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-full h-full rounded" style="background: ${combinedSvg};"></div>
            </div>
        `;
        
        largePreview.innerHTML = `
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-full h-full rounded" style="background: ${combinedSvg}; transform: scale(${previewZoom});"></div>
            </div>
        `;
        
        console.log('Multiple patterns preview updated successfully');
    }
    
    continueBtn.disabled = false;
}

function useExistingSvg(svg, name) {
    const patternPreview = document.getElementById('patternPreviewLive');
    const combinedPreview = document.getElementById('combinedPreview');
    const largePreview = document.getElementById('largeCombinedPreview');
    
    console.log('Using existing SVG:', { svg: svg ? `${svg.substring(0, 50)}...` : 'missing', name });
    
    patternPreview.innerHTML = `
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-2 rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/contain no-repeat;"></div>
            <p class="text-sm font-medium text-gray-900">${name}</p>
            <p class="text-xs text-gray-500">Single pattern</p>
        </div>
    `;
    
    combinedPreview.innerHTML = `
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-full h-full rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/cover no-repeat;"></div>
        </div>
    `;
    
    largePreview.innerHTML = `
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-full h-full rounded" style="background: url('data:image/svg+xml;base64,${svg}') center/cover no-repeat; transform: scale(${previewZoom});"></div>
        </div>
    `;
}

function createCombinedPattern(svgs) {
    console.log('Creating combined pattern from', svgs.length, 'SVGs');
    
    if (svgs.length === 0) {
        return 'linear-gradient(45deg, #f3f4f6 25%, transparent 25%, transparent 75%, #f3f4f6 75%, #f3f4f6), linear-gradient(45deg, #f3f4f6 25%, transparent 25%, transparent 75%, #f3f4f6 75%, #f3f4f6)';
    }
    
    if (svgs.length === 1) {
        return `url('data:image/svg+xml;base64,${svgs[0]}') center/cover repeat`;
    }
    
    // Create a more sophisticated combined pattern for multiple patterns
    const baseLayer = `url('data:image/svg+xml;base64,${svgs[0]}') center/40% repeat`;
    
    if (svgs.length === 2) {
        const overlayLayer = `url('data:image/svg+xml;base64,${svgs[1]}') center/30% repeat`;
        return `linear-gradient(rgba(255,255,255,0.3), rgba(255,255,255,0.3)), ${baseLayer}, ${overlayLayer}`;
    }
    
    if (svgs.length === 3) {
        const overlayLayer1 = `url('data:image/svg+xml;base64,${svgs[1]}') 20% 20%/25% repeat`;
        const overlayLayer2 = `url('data:image/svg+xml;base64,${svgs[2]}') 80% 80%/25% repeat`;
        return `linear-gradient(rgba(255,255,255,0.2), rgba(255,255,255,0.2)), ${baseLayer}, ${overlayLayer1}, ${overlayLayer2}`;
    }
    
    // For 4+ patterns, create a grid layout
    const layers = svgs.map((svg, index) => {
        const positions = [
            '0% 0%', '50% 0%', '0% 50%', '50% 50%',
            '25% 25%', '75% 25%', '25% 75%', '75% 75%'
        ];
        const position = positions[index % positions.length];
        const size = index < 4 ? '40%' : '25%';
        return `url('data:image/svg+xml;base64,${svg}') ${position}/${size} no-repeat`;
    }).join(', ');
    
    return `linear-gradient(rgba(255,255,255,0.15), rgba(255,255,255,0.15)), ${layers}`;
}

function zoomPreview(direction) {
    if (direction === 'in') {
        previewZoom = Math.min(previewZoom + 0.1, 2);
    } else {
        previewZoom = Math.max(previewZoom - 0.1, 0.5);
    }
    
    // Re-update preview if patterns are selected
    if (selectedPatterns.size > 0) {
        updateLivePreview();
    }
    
    showToast(`Zoom: ${Math.round(previewZoom * 100)}%`);
}

function toggleGrid() {
    showGrid = !showGrid;
    
    // Re-update preview if pattern is selected
    if (selectedPattern) {
        updateLivePreview(selectedPattern);
    }
    
    showToast(showGrid ? 'Grid enabled' : 'Grid disabled');
}

function showToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 left-4 right-4 lg:left-auto lg:right-4 lg:w-auto bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove after 2 seconds
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 2000);
}

function toggleMobilePreview() {
    const floatingPreview = document.getElementById('floatingPreview');
    const toggle = document.getElementById('mobilePreviewToggle');
    
    if (floatingPreview.style.display === 'block') {
        floatingPreview.style.display = 'none';
        toggle.classList.remove('bg-red-600');
        toggle.classList.add('bg-purple-600');
    } else {
        floatingPreview.style.display = 'block';
        toggle.classList.remove('bg-purple-600');
        toggle.classList.add('bg-red-600');
    }
}

// Initialize mobile preview visibility
document.addEventListener('DOMContentLoaded', function() {
    const floatingPreview = document.getElementById('floatingPreview');
    if (window.innerWidth < 1024) {
        floatingPreview.style.display = 'none';
    }
});

function submitPatternSelection() {
    if (selectedPatterns.size === 0) {
        showToast('Please select at least one pattern');
        return;
    }
    
    // Capture the canvas as base64 image
    const canvas = document.getElementById('fabricCanvas');
    let previewImage = null;
    
    if (canvas) {
        try {
            // Convert canvas to base64 PNG
            previewImage = canvas.toDataURL('image/png');
            console.log('Canvas preview captured:', previewImage.substring(0, 50) + '...');
        } catch (error) {
            console.error('Error capturing canvas:', error);
        }
    }
    
    // Get customization settings
    const customizationSettings = {
        scale: parseFloat(document.getElementById('patternScale')?.value || 1),
        rotation: parseFloat(document.getElementById('patternRotation')?.value || 0),
        opacity: parseFloat(document.getElementById('patternOpacity')?.value || 0.85),
        hue: parseFloat(document.getElementById('hueRotation')?.value || 0),
        saturation: parseFloat(document.getElementById('saturation')?.value || 100),
        brightness: parseFloat(document.getElementById('brightness')?.value || 100),
    };

    // Build a normal POST form so Laravel redirects with session reliably
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('custom_orders.store.pattern') }}';

    // CSRF token
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    // selection_mode
    const mode = document.createElement('input');
    mode.type = 'hidden';
    mode.name = 'selection_mode';
    mode.value = selectionMode || 'single';
    form.appendChild(mode);

    // patterns[]
    Array.from(selectedPatterns).forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'patterns[]';
        inp.value = id;
        form.appendChild(inp);
    });

    // preview_image (canvas screenshot)
    if (previewImage) {
        const prev = document.createElement('input');
        prev.type = 'hidden';
        prev.name = 'preview_image';
        prev.value = previewImage;
        form.appendChild(prev);
    }
    
    // customization_settings (JSON)
    const settings = document.createElement('input');
    settings.type = 'hidden';
    settings.name = 'customization_settings';
    settings.value = JSON.stringify(customizationSettings);
    form.appendChild(settings);

    document.body.appendChild(form);
    form.submit();
}

function calculateComplexity() {
    return Array.from(selectedPatterns).reduce((total, id) => {
        const card = document.querySelector(`[data-pattern-id="${id}"]`);
        return total + (parseInt(card.dataset.complexity || 5));
    }, 0);
}

// Legacy function for backward compatibility
function selectPattern(patternId) {
    if (selectionMode === 'single') {
        togglePatternSelection(patternId);
    }
}

function updateCombinedPreview(element, svgData, patternName, sizeClass) {
    const fabricType = '{{ session("wizard.fabric.type", "Cotton") }}';
    
    if (svgData) {
        element.innerHTML = `
            <div class="absolute inset-0">
                <!-- Fabric background -->
                <div class="absolute inset-0 opacity-30" style="background: linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%, ${getFabricColor(fabricType)}), linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%, ${getFabricColor(fabricType)}); background-size: 20px 20px; background-position: 0 0, 10px 10px;"></div>
                <!-- Pattern overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="${sizeClass} opacity-80" style="background: url('data:image/svg+xml;base64,${svgData}') center/contain no-repeat;"></div>
                </div>
            </div>
        `;
    } else {
        element.innerHTML = `
            <div class="absolute inset-0">
                <div class="absolute inset-0 opacity-30" style="background: linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%, ${getFabricColor(fabricType)}), linear-gradient(45deg, ${getFabricColor(fabricType)} 25%, transparent 25%, transparent 75%, ${getFabricColor(fabricType)} 75%, ${getFabricColor(fabricType)}); background-size: 20px 20px; background-position: 0 0, 10px 10px;"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <p class="text-gray-700 font-medium text-sm">${patternName}</p>
                </div>
            </div>
        `;
    }
}

function getFabricColor(fabricType) {
    const colors = {
        'Cotton': '#f3f4f6',
        'Silk': '#fef3c7',
        'Polyester Cotton Blend': '#e5e7eb',
        'Linen': '#fef2f2',
        'Canvas': '#f9fafb',
        'Jersey Knit': '#f0f9ff'
    };
    return colors[fabricType] || '#f3f4f6';
}

function filterPatterns(category) {
    // Update button states
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('bg-purple-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('bg-purple-600', 'text-white');
    
    // Filter patterns
    document.querySelectorAll('.pattern-card').forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Pre-select if coming from back navigation
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing pattern selection page');
    
    const checkedRadio = document.querySelector('input[name="pattern_id"]:checked');
    if (checkedRadio) {
        selectPattern(checkedRadio.value);
    }
    
    // Initialize 2D Canvas Preview
    console.log('Initializing canvas...');
    initializeCanvas();
    console.log('Canvas initialized successfully');
    
    // Check if patterns exist
    const patternCards = document.querySelectorAll('.pattern-card');
    console.log('Found', patternCards.length, 'pattern cards');
});

// ========================================
// 2D INTERACTIVE CANVAS PREVIEW SYSTEM
// ========================================

let canvas, ctx;
let currentPattern = null;
let patternImage = null;
let fabricColor = '{{ session("wizard.fabric.type") ? "#f3f4f6" : "#ffffff" }}';

function initializeCanvas() {
    canvas = document.getElementById('fabricCanvas');
    if (!canvas) return;
    
    ctx = canvas.getContext('2d');
    
    // Set high DPI for crisp rendering
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    
    // Draw initial fabric
    drawFabricBase();
}

function drawFabricBase() {
    if (!ctx) return;
    
    const width = canvas.width / (window.devicePixelRatio || 1);
    const height = canvas.height / (window.devicePixelRatio || 1);
    
    // Fabric background with texture
    const fabricType = '{{ session("wizard.fabric.type", "Cotton") }}';
    ctx.fillStyle = getFabricColor(fabricType);
    ctx.fillRect(0, 0, width, height);
    
    // Add fabric texture overlay
    ctx.globalAlpha = 0.1;
    for (let i = 0; i < width; i += 4) {
        for (let j = 0; j < height; j += 4) {
            if (Math.random() > 0.5) {
                ctx.fillStyle = '#000';
                ctx.fillRect(i, j, 2, 2);
            }
        }
    }
    ctx.globalAlpha = 1.0;
    
    // Add subtle gradient
    const gradient = ctx.createLinearGradient(0, 0, width, height);
    gradient.addColorStop(0, 'rgba(255, 255, 255, 0.1)');
    gradient.addColorStop(1, 'rgba(0, 0, 0, 0.05)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, width, height);
}

function loadPatternToCanvas(patternData) {
    currentPattern = patternData;
    
    console.log('loadPatternToCanvas called with:', patternData);
    console.log('Image URL check:', patternData.imageUrl);
    console.log('SVG Data check:', patternData.svgData ? 'present' : 'missing');
    
    // HYBRID SYSTEM: Try to load pattern image, fallback to SVG generation
    if (patternData.imageUrl && patternData.imageUrl !== '' && !patternData.imageUrl.includes('data-pattern-image')) {
        // Option 1: Use uploaded pattern image
        console.log('Loading from image URL:', patternData.imageUrl);
        loadPatternImage(patternData.imageUrl);
    } else if (patternData.svgData && patternData.svgData !== '') {
        // Option 2: Use SVG pattern data
        console.log('Loading from SVG data');
        loadPatternFromSVG(patternData.svgData);
    } else {
        // Option 3: Generate procedural pattern - LAST RESORT
        console.log('WARNING: No valid image or SVG data, generating procedural pattern for:', patternData.name);
        generateProceduralPattern(patternData);
    }
}

function loadPatternImage(imageUrl) {
    console.log('=== LOADING PATTERN IMAGE ===');
    console.log('Image URL:', imageUrl);
    
    patternImage = new Image();
    patternImage.crossOrigin = 'anonymous';
    
    patternImage.onload = function() {
        console.log('✓ Pattern image loaded successfully, dimensions:', patternImage.width, 'x', patternImage.height);
        updateCanvasPreview();
    };
    
    patternImage.onerror = function(error) {
        console.error('✗ Pattern image failed to load:', error);
        console.log('Falling back to SVG or procedural pattern');
        
        // Try SVG data if available
        if (currentPattern && currentPattern.svgData) {
            console.log('Attempting SVG fallback...');
            loadPatternFromSVG(currentPattern.svgData);
        } else {
            console.log('No SVG data, using procedural pattern');
            generateProceduralPattern(currentPattern);
        }
    };
    
    patternImage.src = imageUrl;
    console.log('Image loading initiated...');
}

function loadPatternFromSVG(svgData) {
    // Convert SVG to image
    const svgBlob = new Blob([atob(svgData)], {type: 'image/svg+xml'});
    const url = URL.createObjectURL(svgBlob);
    
    patternImage = new Image();
    patternImage.onload = function() {
        URL.revokeObjectURL(url);
        updateCanvasPreview();
    };
    patternImage.src = url;
}

function generateProceduralPattern(patternData) {
    // Generate SVG pattern based on pattern name/category
    const patternName = patternData.name || 'Default';
    const category = patternData.category || 'geometric';
    
    let svgContent = '';
    
    // Generate different patterns based on category
    if (category === 'geometric' || category === 'traditional') {
        svgContent = generateGeometricPattern(patternName);
    } else if (category === 'floral') {
        svgContent = generateFloralPattern(patternName);
    } else {
        svgContent = generateDefaultPattern(patternName);
    }
    
    // Convert SVG to image
    const svgBlob = new Blob([svgContent], {type: 'image/svg+xml'});
    const url = URL.createObjectURL(svgBlob);
    
    patternImage = new Image();
    patternImage.onload = function() {
        URL.revokeObjectURL(url);
        updateCanvasPreview();
    };
    patternImage.src = url;
}

function generateGeometricPattern(name) {
    // ========================================
    // AUTHENTIC YAKAN TEXTILE PATTERNS
    // ========================================
    // Realistic woven patterns matching actual Yakan textiles
    // Features:
    // - Concentric layered diamonds
    // - Multiple vibrant colors (red, orange, pink, yellow)
    // - Border stripes and decorative motifs
    // - Small figure/human motifs (traditional)
    // - Pixelated woven texture
    // Colors: Red (#C41E3A), Dark Red (#8B0000), Orange (#FF6B35),
    //         Pink (#FF1493), Yellow (#FFD700), Light Pink (#FFB6C1)
    // ========================================
    
    const patterns = {
        // Siniluan - Realistic woven diamond pattern like actual Yakan textile
        'siniluan': `
            <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="siniluan" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
                        <!-- Base red background -->
                        <rect width="80" height="80" fill="#C41E3A"/>
                        
                        <!-- Outer diamond frame - Dark red -->
                        <path d="M40 5 L70 35 L40 65 L10 35 Z" fill="#8B0000" stroke="#6B0000" stroke-width="0.5"/>
                        
                        <!-- Second layer - Orange -->
                        <path d="M40 12 L63 35 L40 58 L17 35 Z" fill="#FF6B35" stroke="#E55A2B" stroke-width="0.5"/>
                        
                        <!-- Third layer - Pink -->
                        <path d="M40 18 L57 35 L40 52 L23 35 Z" fill="#FF1493" stroke="#E01283" stroke-width="0.5"/>
                        
                        <!-- Fourth layer - Yellow -->
                        <path d="M40 24 L51 35 L40 46 L29 35 Z" fill="#FFD700" stroke="#E5C100" stroke-width="0.5"/>
                        
                        <!-- Center - Light pink -->
                        <path d="M40 30 L45 35 L40 40 L35 35 Z" fill="#FFB6C1" stroke="#FF9EB1" stroke-width="0.5"/>
                        
                        <!-- Add pixelated texture overlay -->
                        <rect x="38" y="33" width="2" height="2" fill="#FFFFFF" opacity="0.3"/>
                        <rect x="42" y="33" width="2" height="2" fill="#000000" opacity="0.1"/>
                        
                        <!-- Small accent figures (simplified human motifs) -->
                        <rect x="38" y="8" width="4" height="3" fill="#FFD700"/>
                        <rect x="39" y="11" width="2" height="4" fill="#FFD700"/>
                        
                        <!-- Border pattern dots -->
                        <circle cx="10" cy="10" r="1.5" fill="#FFD700"/>
                        <circle cx="70" cy="10" r="1.5" fill="#FFD700"/>
                        <circle cx="10" cy="70" r="1.5" fill="#FFD700"/>
                        <circle cx="70" cy="70" r="1.5" fill="#FFD700"/>
                    </pattern>
                </defs>
                <rect width="200" height="200" fill="url(#siniluan)"/>
            </svg>
        `,
        
        // Bulinglangit - Realistic woven zigzag pattern
        'bulinglangit': `
            <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="bulinglangit" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                        <!-- Base red -->
                        <rect width="60" height="60" fill="#C41E3A"/>
                        
                        <!-- Diagonal stripes creating zigzag effect -->
                        <path d="M0 30 L15 15 L30 30 L45 15 L60 30 L60 32 L45 17 L30 32 L15 17 L0 32 Z" fill="#8B0000"/>
                        <path d="M0 32 L15 17 L30 32 L45 17 L60 32 L60 34 L45 19 L30 34 L15 19 L0 34 Z" fill="#FF6B35"/>
                        <path d="M0 34 L15 19 L30 34 L45 19 L60 34 L60 36 L45 21 L30 36 L15 21 L0 36 Z" fill="#FF1493"/>
                        <path d="M0 36 L15 21 L30 36 L45 21 L60 36 L60 38 L45 23 L30 38 L15 23 L0 38 Z" fill="#FFD700"/>
                        
                        <!-- Mirrored bottom half -->
                        <path d="M0 28 L15 43 L30 28 L45 43 L60 28 L60 26 L45 41 L30 26 L15 41 L0 26 Z" fill="#8B0000"/>
                        <path d="M0 26 L15 41 L30 26 L45 41 L60 26 L60 24 L45 39 L30 24 L15 39 L0 24 Z" fill="#FF6B35"/>
                        <path d="M0 24 L15 39 L30 24 L45 39 L60 24 L60 22 L45 37 L30 22 L15 37 L0 22 Z" fill="#FF1493"/>
                        <path d="M0 22 L15 37 L30 22 L45 37 L60 22 L60 20 L45 35 L30 20 L15 35 L0 20 Z" fill="#FFD700"/>
                        
                        <!-- Small figure motif at center -->
                        <rect x="28" y="28" width="4" height="2" fill="#FFB6C1"/>
                        <rect x="29" y="30" width="2" height="3" fill="#FFB6C1"/>
                    </pattern>
                </defs>
                <rect width="200" height="200" fill="url(#bulinglangit)"/>
            </svg>
        `,
        
        // Default: Seputangan - Realistic woven border pattern
        'default': `
            <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="seputangan" x="0" y="0" width="70" height="70" patternUnits="userSpaceOnUse">
                        <!-- Base red -->
                        <rect width="70" height="70" fill="#C41E3A"/>
                        
                        <!-- Central diamond -->
                        <path d="M35 15 L50 30 L35 45 L20 30 Z" fill="#8B0000" stroke="#6B0000" stroke-width="0.5"/>
                        <path d="M35 20 L45 30 L35 40 L25 30 Z" fill="#FF6B35" stroke="#E55A2B" stroke-width="0.5"/>
                        <path d="M35 25 L40 30 L35 35 L30 30 Z" fill="#FFD700" stroke="#E5C100" stroke-width="0.5"/>
                        
                        <!-- Border stripes (top) -->
                        <rect x="0" y="5" width="70" height="2" fill="#FFD700"/>
                        <rect x="0" y="7" width="70" height="1" fill="#FF6B35"/>
                        <rect x="0" y="8" width="70" height="1" fill="#FF1493"/>
                        
                        <!-- Border stripes (bottom) -->
                        <rect x="0" y="62" width="70" height="2" fill="#FFD700"/>
                        <rect x="0" y="64" width="70" height="1" fill="#FF6B35"/>
                        <rect x="0" y="65" width="70" height="1" fill="#FF1493"/>
                        
                        <!-- Small decorative dots -->
                        <circle cx="15" cy="15" r="1.5" fill="#FFD700"/>
                        <circle cx="55" cy="15" r="1.5" fill="#FFD700"/>
                        <circle cx="15" cy="55" r="1.5" fill="#FFD700"/>
                        <circle cx="55" cy="55" r="1.5" fill="#FFD700"/>
                        
                        <!-- Tiny figure motif -->
                        <rect x="33" y="28" width="4" height="2" fill="#FFB6C1"/>
                        <rect x="34" y="30" width="2" height="2" fill="#FFB6C1"/>
                    </pattern>
                </defs>
                <rect width="200" height="200" fill="url(#seputangan)"/>
            </svg>
        `
    };
    
    // Match pattern name to authentic design
    const patternKey = name.toLowerCase().includes('siniluan') ? 'siniluan' :
                       name.toLowerCase().includes('bulinglangit') ? 'bulinglangit' :
                       'default';
    
    return patterns[patternKey];
}

function generateFloralPattern(name) {
    // Pinalantupan - Traditional Yakan floral/star motif
    return `
        <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="pinalantupan" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                    <rect width="60" height="60" fill="#8B0000"/>
                    <!-- Central star/flower -->
                    <polygon points="30,15 33,24 42,24 35,30 38,39 30,33 22,39 25,30 18,24 27,24" fill="#FFD700"/>
                    <!-- Corner accents -->
                    <rect x="5" y="5" width="8" height="8" fill="#FFD700"/>
                    <rect x="47" y="5" width="8" height="8" fill="#FFD700"/>
                    <rect x="5" y="47" width="8" height="8" fill="#FFD700"/>
                    <rect x="47" y="47" width="8" height="8" fill="#FFD700"/>
                    <!-- White center dot -->
                    <circle cx="30" cy="30" r="3" fill="#FFFFFF"/>
                </pattern>
            </defs>
            <rect width="200" height="200" fill="url(#pinalantupan)"/>
        </svg>
    `;
}

function generateDefaultPattern(name) {
    // Girigiti - Traditional Yakan comb/teeth pattern
    return `
        <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="girigiti" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                    <rect width="40" height="40" fill="#8B0000"/>
                    <!-- Vertical comb teeth -->
                    <rect x="5" y="10" width="3" height="20" fill="#FFD700"/>
                    <rect x="11" y="10" width="3" height="20" fill="#FFD700"/>
                    <rect x="17" y="10" width="3" height="20" fill="#FFD700"/>
                    <rect x="23" y="10" width="3" height="20" fill="#FFD700"/>
                    <rect x="29" y="10" width="3" height="20" fill="#FFD700"/>
                    <rect x="35" y="10" width="3" height="20" fill="#FFD700"/>
                    <!-- Horizontal bars -->
                    <rect x="3" y="8" width="34" height="2" fill="#FFD700"/>
                    <rect x="3" y="30" width="34" height="2" fill="#FFD700"/>
                    <!-- White accent -->
                    <rect x="3" y="19" width="34" height="1" fill="#FFFFFF"/>
                </pattern>
            </defs>
            <rect width="200" height="200" fill="url(#girigiti)"/>
        </svg>
    `;
}

function updateCanvasPreview() {
    if (!ctx || !patternImage) return;
    
    const width = canvas.width / (window.devicePixelRatio || 1);
    const height = canvas.height / (window.devicePixelRatio || 1);
    
    // Get control values
    const scale = parseFloat(document.getElementById('patternScale').value);
    const rotation = parseFloat(document.getElementById('patternRotation').value);
    const opacity = parseFloat(document.getElementById('patternOpacity').value);
    const blendMode = document.getElementById('blendMode')?.value || 'multiply';
    
    // Get color customization values
    const hue = parseFloat(document.getElementById('hueRotation')?.value || 0);
    const saturation = parseFloat(document.getElementById('saturation')?.value || 100);
    const brightness = parseFloat(document.getElementById('brightness')?.value || 100);
    const tintStrength = parseFloat(document.getElementById('tintStrength')?.value || 0);
    const colorTint = document.getElementById('colorTint')?.value || '#ffffff';
    
    // Update value displays
    document.getElementById('scaleValue').textContent = scale.toFixed(1) + 'x';
    document.getElementById('rotationValue').textContent = rotation + '°';
    document.getElementById('opacityValue').textContent = Math.round(opacity * 100) + '%';
    if (document.getElementById('hueValue')) {
        document.getElementById('hueValue').textContent = hue + '°';
        document.getElementById('saturationValue').textContent = saturation + '%';
        document.getElementById('brightnessValue').textContent = brightness + '%';
    }
    
    // Clear and redraw fabric base
    ctx.clearRect(0, 0, width, height);
    drawFabricBase();
    
    // Save context state
    ctx.save();
    
    // Apply blend mode for realistic merging
    ctx.globalCompositeOperation = blendMode;
    
    // Apply transformations
    ctx.translate(width / 2, height / 2);
    ctx.rotate((rotation * Math.PI) / 180);
    ctx.scale(scale, scale);
    ctx.globalAlpha = opacity;
    
    // Apply color filters using CSS filter syntax
    // IMPORTANT: Canvas 2D context filter supports CSS filter functions
    const filterString = `hue-rotate(${hue}deg) saturate(${saturation / 100}) brightness(${brightness / 100})`;
    ctx.filter = filterString;
    console.log('Applying filter:', filterString);
    
    // Draw pattern image directly (better quality than createPattern)
    const patternWidth = patternImage.width * scale;
    const patternHeight = patternImage.height * scale;
    
    // Tile the pattern to fill the canvas
    for (let x = -width * 2; x < width * 2; x += patternWidth) {
        for (let y = -height * 2; y < height * 2; y += patternHeight) {
            ctx.drawImage(patternImage, x, y, patternWidth, patternHeight);
        }
    }
    
    // Apply color tint overlay if strength > 0
    if (tintStrength > 0) {
        ctx.filter = 'none';
        ctx.globalCompositeOperation = 'overlay';
        ctx.globalAlpha = tintStrength / 100;
        ctx.fillStyle = colorTint;
        ctx.fillRect(-width * 2, -height * 2, width * 4, height * 4);
    }
    
    // Restore context
    ctx.restore();
}

function resetCanvas() {
    document.getElementById('patternScale').value = 1;
    document.getElementById('patternRotation').value = 0;
    document.getElementById('patternOpacity').value = 0.85;
    if (document.getElementById('blendMode')) {
        document.getElementById('blendMode').value = 'multiply';
    }
    // Reset color controls
    if (document.getElementById('hueRotation')) {
        document.getElementById('hueRotation').value = 0;
        document.getElementById('saturation').value = 100;
        document.getElementById('brightness').value = 100;
        document.getElementById('colorTint').value = '#ffffff';
        document.getElementById('tintStrength').value = 0;
    }
    updateCanvasPreview();
    showToast('Canvas reset to defaults');
}

// Toggle color controls visibility
function toggleColorControls() {
    const controls = document.getElementById('colorControls');
    const toggleText = document.getElementById('colorToggleText');
    
    if (controls.classList.contains('hidden')) {
        controls.classList.remove('hidden');
        controls.classList.add('animate-fade-in');
        toggleText.textContent = 'Hide';
    } else {
        controls.classList.add('hidden');
        toggleText.textContent = 'Show';
    }
}

// Apply color presets
function applyColorPreset(color) {
    const presets = {
        'red': { hue: 0, saturation: 100, brightness: 100 },
        'blue': { hue: 210, saturation: 100, brightness: 100 },
        'green': { hue: 120, saturation: 100, brightness: 100 },
        'yellow': { hue: 55, saturation: 100, brightness: 100 },
        'purple': { hue: 280, saturation: 100, brightness: 100 },
        'pink': { hue: 330, saturation: 100, brightness: 100 },
        'orange': { hue: 25, saturation: 100, brightness: 100 },
        'teal': { hue: 180, saturation: 100, brightness: 100 }
    };
    
    const preset = presets[color];
    if (preset) {
        const hueEl = document.getElementById('hueRotation');
        const saturationEl = document.getElementById('saturation');
        const brightnessEl = document.getElementById('brightness');
        
        if (hueEl) hueEl.value = preset.hue;
        if (saturationEl) saturationEl.value = preset.saturation;
        if (brightnessEl) brightnessEl.value = preset.brightness;
        
        // Update the display values
        if (document.getElementById('hueValue')) {
            document.getElementById('hueValue').textContent = preset.hue + '°';
        }
        if (document.getElementById('saturationValue')) {
            document.getElementById('saturationValue').textContent = preset.saturation + '%';
        }
        if (document.getElementById('brightnessValue')) {
            document.getElementById('brightnessValue').textContent = preset.brightness + '%';
        }
        
        // Apply to canvas
        updateCanvasPreview();
        showToast(`✓ Applied ${color} color`);
    } else {
        console.error('Color preset not found:', color);
    }
}

function saveCustomization() {
    if (!canvas) return;
    
    // Just save the current settings to localStorage or sessionStorage
    const settings = {
        scale: parseFloat(document.getElementById('patternScale')?.value || 1),
        rotation: parseFloat(document.getElementById('patternRotation')?.value || 0),
        opacity: parseFloat(document.getElementById('patternOpacity')?.value || 0.85),
        hue: parseFloat(document.getElementById('hueRotation')?.value || 0),
        saturation: parseFloat(document.getElementById('saturation')?.value || 100),
        brightness: parseFloat(document.getElementById('brightness')?.value || 100),
    };
    
    // Store in localStorage for persistence
    localStorage.setItem('patternCustomization', JSON.stringify(settings));
    
    showToast('Customization saved!');
}

function downloadPreview() {
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.download = 'fabric-preview-' + Date.now() + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    showToast('Preview downloaded!');
}

// Zoom controls for large preview
function zoomPreview(direction) {
    if (direction === 'in') {
        previewZoom = Math.min(previewZoom + 0.2, 3);
    } else {
        previewZoom = Math.max(previewZoom - 0.2, 0.5);
    }
    
    // Re-update preview if pattern is selected
    if (selectedPattern) {
        updateLivePreview(selectedPattern);
    }
    
    showToast(`Zoom: ${(previewZoom * 100).toFixed(0)}%`);
}

// Toggle grid overlay
function toggleGrid() {
    showGrid = !showGrid;
    
    // Re-update preview if pattern is selected
    if (selectedPattern) {
        updateLivePreview(selectedPattern);
    }
    
    showToast(showGrid ? 'Grid enabled' : 'Grid disabled');
}

// Mobile preview toggle
function toggleMobilePreview() {
    const preview = document.getElementById('floatingPreview');
    if (preview.style.display === 'none') {
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// Update Review Order button state
function updateReviewOrderButton() {
    const continueBtn = document.getElementById('continueBtn');
    const btnText = document.getElementById('reviewBtnText');
    const btnPulse = document.getElementById('reviewBtnPulse');
    
    if (!continueBtn) return;
    
    if (selectedPatterns.size > 0) {
        // Enable button with animation
        continueBtn.disabled = false;
        continueBtn.classList.add('animate-pulse-once');
        
        // Show pulse indicator
        if (btnPulse) {
            btnPulse.classList.remove('hidden');
        }
        
        // Update text
        if (btnText) {
            btnText.textContent = `Review Order (${selectedPatterns.size} pattern${selectedPatterns.size > 1 ? 's' : ''})`;
        }
        
        // Show success toast
        showToast('✓ Pattern selected! Click "Review Order" to continue');
        
        // Remove animation class after it completes
        setTimeout(() => {
            continueBtn.classList.remove('animate-pulse-once');
        }, 1000);
    } else {
        // Disable button
        continueBtn.disabled = true;
        
        // Hide pulse indicator
        if (btnPulse) {
            btnPulse.classList.add('hidden');
        }
        
        // Reset text
        if (btnText) {
            btnText.textContent = 'Review Order';
        }
    }
}

// Toast notification system
function showToast(message) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = 'toast-notification fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Auto remove after 2 seconds
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Update pattern selection to load into canvas
const originalTogglePattern = window.togglePatternSelection;
window.togglePatternSelection = function(patternId) {
    // Call original function
    if (originalTogglePattern) {
        originalTogglePattern(patternId);
    }
    
    // Load pattern into canvas
    const patternCard = document.querySelector(`[data-pattern-id="${patternId}"]`);
    if (patternCard) {
        const patternData = {
            id: patternId,
            name: patternCard.dataset.patternName,
            category: patternCard.dataset.category,
            svgData: patternCard.dataset.patternSvg,
            imageUrl: patternCard.dataset.patternImage // Will add this to cards
        };
        
        loadPatternToCanvas(patternData);
        
        // Update pattern status
        document.getElementById('patternStatus').textContent = 'Selected';
        document.getElementById('patternStatus').className = 'text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium';
        
        // Update selected pattern name in info card
        const selectedPatternNameEl = document.getElementById('selectedPatternName');
        if (selectedPatternNameEl) {
            selectedPatternNameEl.textContent = patternData.name;
        }
    }
};

</script>
@endsection
