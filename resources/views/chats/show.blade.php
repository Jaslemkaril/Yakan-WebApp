@extends('layouts.app')

@section('title', $chat->subject)

@section('content')
<style>
    .maroon-bg { background: linear-gradient(135deg, #800000 0%, #600000 100%); }
    .maroon-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); }
    .maroon-text { color: #ffffff; }
    .maroon-text-secondary { color: #e8d4d4; }
    .maroon-text-tertiary { color: #d9c0c0; }
    .white-btn { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; }
    .white-btn:hover { background: linear-gradient(135deg, #f3f3f3 0%, #ffffff 100%); }
    .message-user { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; }
    .message-support { background: linear-gradient(135deg, #a00000 0%, #800000 100%); color: #ffffff; border: 1px solid #800000; }
</style>
<div class="min-h-screen maroon-bg py-8">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Header with Chat Info -->
        <div class="maroon-card rounded-2xl shadow-2xl border p-6 mb-6" style="border-color: #800000;">
            <div class="flex justify-between items-start gap-6">
                <div class="flex-1">
                    <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 maroon-text font-semibold mb-4 transition hover:gap-3 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Chats
                    </a>
                    <h1 class="text-3xl font-bold maroon-text mb-4">{{ $chat->subject }}</h1>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold" style="background: rgba(160, 0, 0, 0.3); color: #e8d4d4; border: 1px solid rgba(160, 0, 0, 0.5);">
                            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: #d9c0c0;"></span>
                            {{ ucfirst($chat->status) }}
                        </span>
                        <span class="maroon-text-secondary text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Started {{ $chat->created_at->diffForHumans() }}
                        </span>
                        <span class="maroon-text-secondary text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            {{ count($messages) }} message{{ count($messages) !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                @if($chat->status !== 'closed')
                    <form action="{{ route('chats.close', $chat) }}" method="POST" onsubmit="return confirm('Close this chat? You won\'t be able to send new messages.');">
                        @csrf
                        <button type="submit" class="white-btn px-6 py-2.5 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-xl hover:scale-105 transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Close Chat
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Messages Container -->
        <div class="maroon-card rounded-2xl shadow-2xl border overflow-hidden flex flex-col" style="height: 600px; border-color: #800000;">
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messagesContainer">
                @forelse($messages as $message)
                    <div class="flex {{ $message->sender_type === 'user' ? 'justify-end' : 'justify-start' }} animate-fadeIn">
                        <div class="flex flex-col {{ $message->sender_type === 'user' ? 'items-end' : 'items-start' }} max-w-xs">
                            <p class="text-xs font-semibold maroon-text-tertiary mb-2 px-2">
                                {{ $message->sender_type === 'user' ? 'You' : 'Support Team' }}
                            </p>
                            <div class="rounded-2xl px-5 py-3 shadow-lg {{ $message->sender_type === 'user' ? 'message-user' : 'message-support' }}">
                                @if($message->image_path)
                                    <img src="{{ asset('storage/' . $message->image_path) }}" alt="Chat image" class="max-w-xs rounded-lg mb-3 shadow-lg" style="border: 1px solid #800000;">
                                @endif
                                <p class="break-words leading-relaxed">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs maroon-text-tertiary mt-2 px-2">
                                {{ $message->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="inline-flex p-4 rounded-2xl mb-4" style="background: rgba(255, 255, 255, 0.2);">
                                <svg class="w-16 h-16 maroon-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="maroon-text font-semibold">No messages yet</p>
                            <p class="maroon-text-secondary text-sm">Start the conversation below</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Message Form -->
        @if($chat->status !== 'closed')
            <div class="maroon-card rounded-2xl shadow-2xl border p-6 mt-6" style="border-color: #800000;">
                <form action="{{ route('chats.send-message', $chat) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-5">
                        <label for="message" class="block text-sm font-semibold maroon-text mb-2.5 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Your Message
                        </label>
                        <textarea id="message" name="message" required rows="3" class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 transition resize-none" placeholder="Type your message here..." style="background: rgba(160, 0, 0, 0.3); border: 2px solid #800000; color: #ffffff;"></textarea>
                        @error('message')
                            <p class="text-red-300 text-sm mt-2 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-5">
                        <label for="image" class="block text-sm font-semibold maroon-text mb-2.5 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Attach Image (Optional)
                        </label>
                        <div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition" id="imageDropZone" style="border-color: #800000;">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="updateImagePreview(this)">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="maroon-text-secondary text-sm font-medium">Drag and drop or click to select</p>
                            <p class="maroon-text-tertiary text-xs mt-1">PNG, JPG, GIF up to 5MB</p>
                        </div>
                        <div id="imagePreview" class="mt-3 hidden">
                            <div class="relative inline-block">
                                <img id="previewImg" src="" alt="Preview" class="max-h-40 rounded-lg shadow-lg" style="border: 1px solid #800000;">
                                <button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 white-btn rounded-full p-1.5 shadow-lg transition">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full white-btn px-6 py-3 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:scale-105 transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Message
                    </button>
                </form>
            </div>
        @else
            <div class="rounded-2xl p-8 text-center border-2 mt-6" style="background: linear-gradient(135deg, #a00000 0%, #800000 100%); border-color: #800000;">
                <svg class="w-16 h-16 mx-auto mb-4 maroon-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="maroon-text font-semibold text-lg">This chat is closed</p>
                <p class="maroon-text-secondary text-sm mt-1">You cannot send new messages. <a href="{{ route('chats.create') }}" class="maroon-text hover:text-white font-semibold">Start a new chat</a></p>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    #messagesContainer::-webkit-scrollbar {
        width: 8px;
    }
    
    #messagesContainer::-webkit-scrollbar-track {
        background: #a00000;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb {
        background: #c00000;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb:hover {
        background: #d00000;
    }
</style>

<script>
    document.body.classList.add('chat-page');

    const imageDropZone = document.getElementById('imageDropZone');
    const imageInput = document.getElementById('image');

    if (imageDropZone && imageInput) {
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

    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
</script>
@endsection
