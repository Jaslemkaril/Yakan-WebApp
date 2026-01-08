@extends('layouts.app')

@section('title', 'Optional: Upload Reference Image - Custom Order')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-100 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">✓</div>
                    <span class="ml-2 font-medium text-green-600">Fabric</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">2</div>
                    <span class="ml-2 text-gray-500">Patterns</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">3</div>
                    <span class="ml-2 text-gray-500">Details</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Show Us What You Want</h1>
            <p class="text-gray-600">Upload a reference image of the pattern or design you'd like us to create</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-red-800 mb-1">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('custom_orders.store.image') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf

                <!-- Upload Area -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        📸 Upload Reference Image
                    </label>
                    
                    <div class="border-3 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer" 
                         id="dropZone"
                         style="border-color:#c08080; background-color:#fff5f5;"
                         onclick="document.getElementById('imageInput').click()">
                        
                        <div id="uploadPrompt">
                            <svg class="w-16 h-16 mx-auto mb-4" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-lg font-semibold text-gray-700 mb-2">Click to upload or drag and drop</p>
                            <p class="text-sm text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                        </div>

                        <div id="imagePreview" class="hidden">
                            <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg">
                            <button type="button" onclick="clearImage(event)" class="mt-4 px-4 py-2 text-white rounded-lg transition-colors" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                Remove Image
                            </button>
                        </div>
                    </div>

                    <input type="file" 
                           id="imageInput" 
                           name="reference_image" 
                           accept="image/png,image/jpeg,image/jpg" 
                           class="hidden"
                           onchange="handleImageSelect(event)">
                    
                    @error('reference_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-2">
                        📝 Describe What You Want
                    </label>
                    <textarea name="description" 
                              rows="4" 
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-colors" style="--focus-border-color:#800000;" onfocus="this.style.borderColor='#800000'; this.style.boxShadow='0 0 0 3px rgba(128,0,0,0.2)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                              placeholder="Example: I want this diamond pattern in red and gold colors for a traditional dress..."
                              required>{{ old('description') }}</textarea>
                    <p class="mt-2 text-sm text-gray-500">Be as detailed as possible - colors, size, intended use, etc.</p>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Skip Option -->
                <div class="mb-8 p-4 rounded-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mt-0.5 mr-3 flex-shrink-0" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold mb-1" style="color:#800000;">Don't have a reference image?</p>
                            <p class="text-sm text-gray-700">You can skip this step and browse our pattern gallery in the next step.</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('custom_orders.create.step1') }}" 
                       class="px-6 py-3 bg-white border-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-semibold" style="border-color:#c08080;">
                        ← Back
                    </a>
                    
                    <div class="flex gap-3">
                        <button type="button"
                                onclick="skipStep()"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold">
                            Skip This Step
                        </button>
                        <button type="submit" 
                                class="px-6 py-3 text-white rounded-lg transition-all font-semibold shadow-lg" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            Continue →
                        </button>
                    </div>
                </div>
            </form>

        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">💡 Tips for Best Results:</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-start">
                    <span class="text-2xl mr-3">📷</span>
                    <div>
                        <p class="font-semibold text-gray-800">Clear Photos</p>
                        <p class="text-sm text-gray-600">Use well-lit, focused images</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="text-2xl mr-3">🎨</span>
                    <div>
                        <p class="font-semibold text-gray-800">Show Details</p>
                        <p class="text-sm text-gray-600">Close-up shots work best</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="text-2xl mr-3">🌈</span>
                    <div>
                        <p class="font-semibold text-gray-800">True Colors</p>
                        <p class="text-sm text-gray-600">Avoid filters or heavy editing</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="text-2xl mr-3">📐</span>
                    <div>
                        <p class="font-semibold text-gray-800">Multiple Angles</p>
                        <p class="text-sm text-gray-600">Upload multiple images if needed</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Image upload handling
function handleImageSelect(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large. Maximum size is 10MB.');
            return;
        }

        // Validate file type
        if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
            alert('Please upload a PNG or JPG image.');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('uploadPrompt').classList.add('hidden');
            document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

// Drag and drop
const dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#800000';
    dropZone.style.backgroundColor = '#ffe0e0';
});

dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = '#c08080';
    dropZone.style.backgroundColor = '#fff5f5';
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#c08080';
    dropZone.style.backgroundColor = '#fff5f5';
    
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('imageInput').files = e.dataTransfer.files;
        handleImageSelect({ target: { files: [file] } });
    }
});

// Clear image
function clearImage(event) {
    event.stopPropagation();
    document.getElementById('imageInput').value = '';
    document.getElementById('uploadPrompt').classList.remove('hidden');
    document.getElementById('imagePreview').classList.add('hidden');
}

// Skip step
function skipStep() {
    window.location.href = '{{ route("custom_orders.create.pattern") }}';
}

// Debug form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    console.log('Form submitting...');
    console.log('Form action:', this.action);
    console.log('Form method:', this.method);
    console.log('Description value:', document.querySelector('textarea[name="description"]').value);
    console.log('Image file:', document.getElementById('imageInput').files[0]);
    
    // Check if description is filled
    const description = document.querySelector('textarea[name="description"]').value.trim();
    if (description.length < 10) {
        e.preventDefault();
        alert('Please provide a detailed description (at least 10 characters)');
        return false;
    }
});
</script>
@endsection
