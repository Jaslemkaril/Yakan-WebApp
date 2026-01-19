@extends('layouts.app')

@section('title', $chat->subject)

@section('content')
<style>
    .chat-container { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); }
    .chat-header { background: white; border-bottom: 2px solid #f3f4f6; }
    .message-user { 
        background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); 
        color: white;
        box-shadow: 0 2px 8px rgba(139, 0, 0, 0.15);
    }
    .message-support { 
        background: white;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    .status-badge {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    .status-closed {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
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
<div class="min-h-screen chat-container py-8">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Header with Chat Info -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-start gap-6">
                <div class="flex-1">
                    <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-[#8B0000] font-semibold mb-4 transition hover:gap-3 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Chats
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $chat->subject }}</h1>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold {{ $chat->status === 'closed' ? 'status-closed' : 'status-badge' }}">
                            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $chat->status === 'closed' ? 'bg-red-600' : 'bg-green-600' }}"></span>
                            {{ ucfirst($chat->status) }}
                        </span>
                        <span class="text-gray-600 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Started {{ $chat->created_at->diffForHumans() }}
                        </span>
                        <span class="text-gray-600 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            {{ count($messages) }} message{{ count($messages) !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                @if($chat->status !== 'closed')
                    <form action="{{ route('chats.close', $chat) }}" method="POST" onsubmit="return confirm('Close this chat? You won\'t be able to send new messages.');">
                        @csrf
                        <button type="submit" class="btn-outline px-6 py-2.5 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2 shadow-sm hover:shadow-md">
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
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col" style="height: 600px;">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50" id="messagesContainer">
                @forelse($messages as $message)
                    <div class="flex {{ $message->sender_type === 'user' ? 'justify-end' : 'justify-start' }} animate-fadeIn">
                        <div class="flex flex-col {{ $message->sender_type === 'user' ? 'items-end' : 'items-start' }} max-w-xs">
                            <p class="text-xs font-semibold text-gray-500 mb-2 px-2">
                                {{ $message->sender_type === 'user' ? 'You' : 'Support Team' }}
                            </p>
                            <div class="rounded-2xl px-5 py-3 {{ $message->sender_type === 'user' ? 'message-user' : 'message-support' }}">
                                @if($message->image_path)
                                    @php
                                        // Handle different image path formats
                                        $imagePath = $message->image_path;
                                        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                            $chatImageUrl = $imagePath;
                                        } elseif (str_starts_with($imagePath, 'data:image')) {
                                            $chatImageUrl = $imagePath;
                                        } else {
                                            $chatImageUrl = asset('storage/' . $imagePath);
                                        }
                                    @endphp
                                    <a href="{{ $chatImageUrl }}" target="_blank" class="block mb-3">
                                        <img src="{{ $chatImageUrl }}" 
                                             alt="Attached image" 
                                             class="max-w-full rounded-xl shadow-md border-2 {{ $message->sender_type === 'user' ? 'border-white/30' : 'border-gray-200' }}"
                                             style="max-height: 250px; object-fit: contain; cursor: pointer;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden items-center gap-2 px-4 py-3 bg-red-50 rounded-lg text-red-700 text-sm">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            Image could not be loaded
                                        </div>
                                    </a>
                                @endif
                                <p class="break-words leading-relaxed whitespace-pre-line">{{ $message->message }}</p>
                            </div>
                            
                            @if($message->sender_type === 'admin' && str_contains($message->message, 'PRICE QUOTE'))
                                @php
                                    $hasResponse = \App\Models\ChatMessage::where('chat_id', $chat->id)
                                        ->where('sender_type', 'user')
                                        ->where('message', 'like', '%accepted the price quote%')
                                        ->orWhere('message', 'like', '%declined the price quote%')
                                        ->where('created_at', '>', $message->created_at)
                                        ->exists();
                                @endphp
                                
                                @if(!$hasResponse)
                                    <div class="flex gap-2 mt-3 px-2">
                                        <form action="{{ route('chats.respond-quote', $chat) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="response" value="accepted">
                                            <input type="hidden" name="quote_message_id" value="{{ $message->id }}">
                                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                ✓ Accept Price
                                            </button>
                                        </form>
                                        <form action="{{ route('chats.respond-quote', $chat) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="response" value="declined">
                                            <input type="hidden" name="quote_message_id" value="{{ $message->id }}">
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                ✗ Decline
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                            
                            <p class="text-xs text-gray-400 mt-2 px-2">
                                {{ $message->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="inline-flex p-4 bg-gray-100 rounded-2xl mb-4">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-gray-900 font-semibold">No messages yet</p>
                            <p class="text-gray-500 text-sm">Start the conversation below</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Message Form -->
        @if($chat->status !== 'closed')
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mt-6">
                <form action="{{ route('chats.send-message', $chat) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-5">
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Your Message
                        </label>
                        
                        <!-- Image Preview (shows above input when image is selected) -->
                        <div id="imagePreview" class="hidden mb-4">
                            <div class="relative inline-block">
                                <img id="previewImg" src="" alt="Preview" class="max-h-52 rounded-xl shadow-lg border-2 border-gray-200">
                                <button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 bg-white hover:bg-red-50 rounded-full p-2 shadow-lg transition border-2 border-gray-200 hover:border-red-300">
                                    <svg class="w-4 h-4 text-gray-600 hover:text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
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
                            <textarea id="message" name="message" required rows="1" class="flex-1 border-none bg-transparent resize-none outline-none text-sm text-gray-900 placeholder-gray-400 px-2 py-1" placeholder="Type your message here..." style="min-height: 24px; max-height: 120px; overflow-y: auto;" oninput="autoResize(this)"></textarea>

                            <!-- Send Button -->
                            <button type="submit" class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-[#8B0000] to-[#6B0000] hover:from-[#6B0000] hover:to-[#5B0000] border-none rounded-full cursor-pointer transition-all duration-200 flex-shrink-0 p-0 shadow-md hover:shadow-lg" title="Send message">
                                <svg class="w-4 h-4 text-white block" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                                </svg>
                            </button>
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
                </form>
            </div>
        @else
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200 p-8 text-center mt-6">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="text-gray-900 font-semibold text-lg">This chat is closed</p>
                <p class="text-gray-600 text-sm mt-1">You cannot send new messages. <a href="{{ route('chats.create') }}" class="text-[#8B0000] hover:text-[#6B0000] font-semibold">Start a new chat</a></p>
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
        background: #f3f4f6;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>

<script>
    document.body.classList.add('chat-page');

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
        const imageInput = document.getElementById('image');
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
