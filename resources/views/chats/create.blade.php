@extends('layouts.app')

@section('title', 'Start New Chat')

@section('content')
<style>
    .maroon-bg { background: linear-gradient(135deg, #800000 0%, #600000 100%); }
    .maroon-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); }
    .maroon-text { color: #ffffff; }
    .maroon-text-secondary { color: #e8d4d4; }
    .maroon-text-tertiary { color: #d9c0c0; }
    .white-btn { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; }
    .white-btn:hover { background: linear-gradient(135deg, #f3f3f3 0%, #ffffff 100%); }
</style>
<div class="min-h-screen maroon-bg py-12">
    <div class="max-w-3xl mx-auto px-4">
        <!-- Header Section -->
        <div class="mb-10">
            <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 maroon-text font-semibold mb-6 transition hover:gap-3 group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Chats
            </a>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #800000;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold maroon-text mb-1">Start a New Chat</h1>
                    <p class="maroon-text-secondary text-base">Get in touch with our support team</p>
                </div>
            </div>
            <div class="h-1.5 w-24 rounded-full shadow-lg mt-4" style="background: linear-gradient(90deg, #ffffff, #f3f3f3, #ffffff);"></div>
        </div>

        <!-- Form Card -->
        <div class="maroon-card rounded-2xl shadow-2xl border p-8" style="border-color: #800000;">
            <form action="{{ route('chats.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Subject -->
                <div class="mb-8">
                    <label for="subject" class="block text-sm font-bold maroon-text mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Subject
                    </label>
                    <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 transition" placeholder="What is your inquiry about?" value="{{ old('subject') }}" style="background: rgba(160, 0, 0, 0.3); border: 2px solid #800000; color: #ffffff;">
                    @error('subject')
                        <p class="text-red-300 text-sm mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Message -->
                <div class="mb-8">
                    <label for="message" class="block text-sm font-bold maroon-text mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Message
                    </label>
                    <textarea id="message" name="message" required rows="6" class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 transition resize-none" placeholder="Describe your issue or question in detail..." style="background: rgba(160, 0, 0, 0.3); border: 2px solid #800000; color: #ffffff;">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-300 text-sm mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div class="mb-8">
                    <label for="image" class="block text-sm font-bold maroon-text mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Attach Image (Optional)
                    </label>
                    <div class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition" id="imageDropZone" style="border-color: #800000;">
                        <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="updateImagePreview(this)">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="maroon-text-secondary font-semibold">Drag and drop an image or click to select</p>
                        <p class="maroon-text-tertiary text-sm mt-1">PNG, JPG, GIF up to 5MB</p>
                    </div>
                    <div id="imagePreview" class="mt-4 hidden">
                        <div class="relative inline-block">
                            <img id="previewImg" src="" alt="Preview" class="max-h-48 rounded-lg shadow-lg" style="border: 1px solid #800000;">
                            <button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 white-btn rounded-full p-1.5 shadow-lg transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>
                    @error('image')
                        <p class="text-red-300 text-sm mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 white-btn px-6 py-3.5 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:scale-105 transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Chat
                    </button>
                    <a href="{{ route('chats.index') }}" class="flex-1 px-6 py-3.5 rounded-lg font-semibold transition-all duration-300 text-center flex items-center justify-center gap-2 border" style="background: rgba(160, 0, 0, 0.3); border-color: #800000; color: #ffffff;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const imageDropZone = document.getElementById('imageDropZone');
    const imageInput = document.getElementById('image');

    imageDropZone.addEventListener('click', () => imageInput.click());
    imageDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageDropZone.style.borderColor = '#ffffff';
        imageDropZone.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
    });
    imageDropZone.addEventListener('dragleave', () => {
        imageDropZone.style.borderColor = '#800000';
        imageDropZone.style.backgroundColor = 'transparent';
    });
    imageDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        imageDropZone.style.borderColor = '#800000';
        imageDropZone.style.backgroundColor = 'transparent';
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            updateImagePreview(imageInput);
        }
    });

    function updateImagePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearImage() {
        imageInput.value = '';
        document.getElementById('imagePreview').classList.add('hidden');
    }
</script>
@endsection
