@extends('layouts.admin')

@section('title', 'Create Pattern')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 shadow-2xl" style="background: linear-gradient(to right, #800000, #600000);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center">
                <a href="{{ route('admin.patterns.index') }}" class="text-maroon-100 hover:text-white mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-white">Create New Pattern</h1>
                    <p class="text-maroon-100 mt-2">Add a new Yakan cultural pattern to the archive</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <form id="patternCreateForm" method="POST" action="{{ route('admin.patterns.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-maroon-600 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-maroon-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-maroon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Basic Information</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Pattern Name <span class="text-red-500 ml-1">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500 transition-all duration-200 group-hover:border-gray-300" 
                               placeholder="Enter pattern name..." />
                        @error('name') 
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </span> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500">
                            <option value="traditional" {{ old('category', 'traditional') == 'traditional' ? 'selected' : '' }}>Traditional</option>
                            <option value="modern" {{ old('category') == 'modern' ? 'selected' : '' }}>Modern</option>
                            <option value="contemporary" {{ old('category') == 'contemporary' ? 'selected' : '' }}>Contemporary</option>
                        </select>
                        @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty Level *</label>
                        <select name="difficulty_level" id="difficulty_level" onchange="updatePriceByDifficulty()" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500">
                            <option value="simple" {{ old('difficulty_level', 'simple') == 'simple' ? 'selected' : '' }}>Simple</option>
                            <option value="medium" {{ old('difficulty_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="complex" {{ old('difficulty_level') == 'complex' ? 'selected' : '' }}>Complex</option>
                        </select>
                        @error('difficulty_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Color</label>
                        <input type="text" name="base_color" value="{{ old('base_color') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" />
                        @error('base_color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Production Time (Days) *</label>
                        <input type="number" min="1" max="180" name="production_days" value="{{ old('production_days', 14) }}" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" placeholder="e.g., 14" />
                        @error('production_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pattern Price (₱) *</label>
                        <input type="number" step="0.01" min="0" name="pattern_price" value="{{ old('pattern_price', 0) }}" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" placeholder="e.g., 1500" />
                        @error('pattern_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Meter (₱) *</label>
                        <input type="number" step="0.01" min="0" name="price_per_meter" value="{{ old('price_per_meter', 0) }}" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" placeholder="e.g., 500" />
                        @error('price_per_meter') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500">{{ old('description') }}</textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-black text-gray-900 mb-4">Settings</h2>
                <div class="flex items-center mb-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', true)) />
                    <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                </div>
                <p class="text-xs text-gray-600">✓ Each pattern now has its own individual price set above</p>
            </div>

            <!-- Tags -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-black text-gray-900 mb-4">Tags</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tags', []))) class="rounded mr-2" />
                            <span class="text-sm">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- SVG Pattern Upload -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-600 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">SVG Pattern (Customizable)</h2>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-white border-2 border-dashed border-green-300 rounded-lg p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-900 mb-2">
                                ✨ Upload SVG for Color Customization
                            </p>
                            <p class="text-xs text-green-700 mb-4">
                                SVG files allow users to customize pattern colors when creating custom orders. This makes patterns more flexible and personalized!
                            </p>
                            <input type="file" name="svg_file" accept=".svg" class="hidden" id="svg-upload" onchange="handleSvgUpload(event)" />
                            <button type="button" onclick="event.preventDefault(); document.getElementById('svg-upload').click();" 
                                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md text-sm">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Choose SVG File
                            </button>
                            <div id="svg-preview" class="mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center p-6 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Tip:</span> Fill in all required fields marked with *
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('admin.patterns.index') }}" 
                       class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-all duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn"
                            class="px-10 py-4 bg-gradient-to-r from-red-600 to-red-700 text-white font-bold text-lg rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl flex items-center border-2 border-red-800"
                            onclick="this.disabled=true; this.querySelector('#submitBtnText').classList.add('hidden'); this.querySelector('#submitBtnLoading').classList.remove('hidden'); this.form.submit();">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="relative">
                            <span id="submitBtnText">Create Pattern</span>
                            <span id="submitBtnLoading" class="hidden">Creating...</span>
                            <span class="absolute -top-1 -right-2 w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Debug logging
console.log('Pattern create form script loaded');

// Auto-update price based on difficulty level
function updatePriceByDifficulty() {
    const difficultyLevel = document.getElementById('difficulty_level').value;
    const priceInput = document.querySelector('input[name="base_price_multiplier"]');
    
    const priceMap = {
        'simple': 1300,
        'medium': 1950,
        'complex': 2600
    };
    
    if (priceInput && priceMap[difficultyLevel]) {
        priceInput.value = priceMap[difficultyLevel];
    }
}

// Set initial price on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePriceByDifficulty();
});

// SVG upload handler
function handleSvgUpload(e) {
    const file = e.target.files[0];
    const svgPreview = document.getElementById('svg-preview');
    
    if (file && file.type === 'image/svg+xml') {
        const reader = new FileReader();
        reader.onload = function(e) {
            svgPreview.innerHTML = `
                <div class="flex items-center space-x-3 bg-white border border-green-300 rounded-lg p-3">
                    <div class="flex-shrink-0 w-16 h-16 bg-green-50 rounded-lg flex items-center justify-center border border-green-200">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">${e.target.result}</svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                    <button type="button" onclick="removeSvgFile()" 
                            class="flex-shrink-0 text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;
        };
        reader.readAsText(file);
    } else {
        alert('Please upload a valid SVG file');
        e.target.value = '';
    }
}

function removeSvgFile() {
    document.getElementById('svg-upload').value = '';
    document.getElementById('svg-preview').innerHTML = '';
}

// Media upload handler
function handleMediaUpload(e) {
    const files = e.target.files;
    displayMediaPreviews(files);
}

// Enhanced media upload with drag & drop
const dropzone = document.getElementById('dropzone');
const mediaUpload = document.getElementById('media-upload');
const mediaPreview = document.getElementById('media-preview');

// Drag and drop functionality
if (dropzone) {
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && mediaUpload) {
            try {
                // Create a new FileList-like object
                const dt = new DataTransfer();
                Array.from(files).forEach(file => dt.items.add(file));
                mediaUpload.files = dt.files;
                displayMediaPreviews(files);
            } catch (error) {
                console.log('DataTransfer not supported, using fallback');
                displayMediaPreviews(files);
            }
        }
    });
}

function displayMediaPreviews(files) {
    if (!mediaPreview) return;
    
    mediaPreview.innerHTML = '';
    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <div class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                        <img src="${e.target.result}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                            ${file.name}
                        </div>
                    </div>
                    <input type="text" name="media_alt[${index}]" placeholder="Enter alt text for accessibility..." 
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                `;
                mediaPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('patternCreateForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitBtnLoading = document.getElementById('submitBtnLoading');
    
    if (form && submitBtn) {
        // Add submit handler
        form.addEventListener('submit', function(e) {
            // Don't prevent default - let the form submit naturally
            
            // Just disable the button to prevent double submission
            if (!submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                if (submitBtnText) submitBtnText.classList.add('hidden');
                if (submitBtnLoading) submitBtnLoading.classList.remove('hidden');
            }
            
            // Form will submit naturally - no preventDefault()
        });
    }
});
</script>
@endsection
