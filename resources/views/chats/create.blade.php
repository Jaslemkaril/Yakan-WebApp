@extends('layouts.app')

@section('title', 'Start New Chat')

@section('content')
<style>
    .chat-container { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); }
    .btn-maroon {
        background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%);
        color: white;
        transition: all 0.3s ease;
    }
    .btn-maroon:hover {
        background: linear-gradient(135deg, #6B0000 0%, #5B0000 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 0, 0, 0.25);
    }
    .btn-outline {
        background: white;
        color: #8B0000;
        border: 2px solid #8B0000;
    }
    .btn-outline:hover {
        background: #8B0000;
        color: white;
    }
</style>
<div class="min-h-screen chat-container py-12">
    <div class="max-w-3xl mx-auto px-4">
        <!-- Header Section -->
        <div class="mb-10">
            <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-[#8B0000] font-semibold mb-6 transition hover:gap-3 group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Chats
            </a>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-3 bg-gradient-to-br from-[#8B0000] to-[#6B0000] rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-1">Start a New Chat</h1>
                    <p class="text-gray-600 text-base">Get in touch with our support team</p>
                </div>
            </div>
            <div class="h-1.5 w-24 bg-gradient-to-r from-[#8B0000] to-[#6B0000] rounded-full shadow-md mt-4"></div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <form action="{{ route('chats.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Subject -->
                <div class="mb-8">
                    <label for="subject" class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Subject
                    </label>
                    <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8B0000] focus:border-transparent transition" placeholder="What is your inquiry about?" value="{{ old('subject') }}">
                    @error('subject')
                        <p class="text-red-600 text-sm mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Message Input with Inline Attachment -->
                <div class="mb-8">
                    <label for="message" class="block text-sm font-bold text-gray-700 mb-3">
                        Message
                    </label>
                    
                    <!-- Image Preview (shows above input when image is selected) -->
                    <div id="imagePreview" class="hidden mb-4">
                        <div class="relative inline-block">
                            <img id="previewImg" src="" alt="Preview" class="max-h-52 rounded-xl shadow-lg border-2 border-gray-200">
                            <button type="button" onclick="clearImage()" class="absolute -top-3 -right-3 bg-white hover:bg-red-50 rounded-full p-2.5 shadow-lg transition-all duration-200 border-2 border-gray-200 hover:border-red-300">
                                <svg class="w-5 h-5 text-gray-600 hover:text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Horizontal Input Layout -->
                    <div class="flex items-center gap-3 bg-gray-50 border-2 border-gray-300 rounded-3xl px-4 py-3 transition-all duration-200 focus-within:border-[#8B0000] focus-within:bg-white">
                        <!-- Attach Image Button (+ icon) -->
                        <label for="image" class="cursor-pointer flex items-center justify-center w-10 h-10 bg-white border-2 border-gray-200 rounded-full hover:bg-[#8B0000] hover:border-[#8B0000] transition-all duration-200 flex-shrink-0 group" title="Attach image">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="updateImagePreview(this)">
                            <svg class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors duration-200 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </label>

                        <!-- Message Input -->
                        <textarea id="message" name="message" required rows="1" class="flex-1 border-none bg-transparent resize-none outline-none text-sm text-gray-900 placeholder-gray-400 px-2 py-1" placeholder="Describe your issue or question in detail..." style="min-height: 24px; max-height: 120px; overflow-y: auto;" oninput="autoResize(this)">{{ old('message') }}</textarea>

                        <!-- Send Button (initially hidden, shows as submit at bottom) -->
                    </div>

                    @error('message')
                        <p class="text-red-600 text-sm mt-2 flex items-center gap-1 pl-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    @error('image')
                        <p class="text-red-600 text-sm mt-2 flex items-center gap-1 pl-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 btn-maroon px-6 py-3.5 rounded-lg font-semibold flex items-center justify-center gap-2 shadow-md">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                        </svg>
                        Send Chat
                    </button>
                    <a href="{{ route('chats.index') }}" class="flex-1 btn-outline px-6 py-3.5 rounded-lg font-semibold transition-all duration-300 text-center flex items-center justify-center gap-2">
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
        imageDropZone.style.borderColor = '#8B0000';
        imageDropZone.style.backgroundColor = '#fef2f2';
    });
    imageDropZone.addEventListener('dragleave', () => {
        imageDropZone.style.borderColor = '#d1d5db';
        imageDropZone.style.backgroundColor = 'transparent';
    });
    imageDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        imageDropZone.style.borderColor = '#d1d5db';
        imageDropZone.style.backgroundColor = 'transparent';
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            updateImagePreview(imageInput);
        }
    });

    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

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
