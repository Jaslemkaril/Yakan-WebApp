@extends('layouts.admin')

@section('title', 'Chat: ' . $chat->subject)

@section('content')
<style>
    .chat-page-bg { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); min-height: 100vh; padding: 24px; margin: -24px; }
    .chat-header { margin-bottom: 24px; }
    .chat-header h1 { font-size: 1.75rem; font-weight: bold; color: #1f2937; margin-bottom: 4px; }
    .chat-header .back-link { color: #6b7280; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; transition: color 0.3s; }
    .chat-header .back-link:hover { color: #8B0000; }
    
    .info-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
    .info-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .info-card h3 { color: #1f2937; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    .info-card p { color: #6b7280; margin-bottom: 8px; font-size: 0.875rem; }
    .info-card strong { color: #1f2937; }
    
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-open { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .status-closed { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .btn-action { display: block; width: 100%; padding: 10px 16px; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; transition: all 0.3s; margin-bottom: 10px; font-size: 0.875rem; }
    .btn-primary { background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(139, 0, 0, 0.25); }
    .btn-secondary { background: white; color: #8B0000; border: 2px solid #8B0000; }
    .btn-secondary:hover { background: #8B0000; color: white; }
    
    .conversation-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px; }
    .conversation-card h3 { color: #1f2937; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    
    .messages-container { background: #f9fafb; border-radius: 8px; padding: 16px; max-height: 400px; overflow-y: auto; }
    .message { margin-bottom: 16px; max-width: 80%; }
    .message-user { margin-left: 0; }
    .message-admin { margin-left: auto; }
    .message-bubble { padding: 12px 16px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .message-user .message-bubble { background: white; color: #1f2937; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
    .message-admin .message-bubble { background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); color: white; border-bottom-right-radius: 4px; }
    .message-sender { font-size: 0.7rem; font-weight: 600; margin-bottom: 4px; }
    .message-user .message-sender { color: #6b7280; }
    .message-admin .message-sender { color: rgba(255,255,255,0.8); }
    .message-text { font-size: 0.875rem; line-height: 1.5; word-break: break-word; }
    .message-time { font-size: 0.65rem; margin-top: 6px; opacity: 0.7; }
    .message-user .message-time { color: #9ca3af; }
    .message-admin .message-time { color: rgba(255,255,255,0.7); }
    
    .reply-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .reply-card h3 { color: #1f2937; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.875rem; resize: vertical; background: white; color: #1f2937; min-height: 100px; }
    .form-textarea::placeholder { color: #9ca3af; }
    .form-textarea:focus { outline: none; border-color: #8B0000; }
    
    .drop-zone { border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .drop-zone:hover { border-color: #8B0000; background: #fef2f2; }
    .drop-zone i { font-size: 1.5rem; color: #9ca3af; margin-bottom: 8px; }
    .drop-zone p { color: #6b7280; font-size: 0.8rem; }
    
    .btn-send { background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); color: white; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-send:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(139, 0, 0, 0.25); }
    
    .header-actions { display: flex; gap: 10px; align-items: center; }
    .status-select { padding: 8px 12px; border-radius: 8px; background: white; border: 2px solid #e5e7eb; color: #1f2937; font-size: 0.875rem; cursor: pointer; }
    .status-select option { color: #1f2937; background: white; }
    .btn-delete { background: #dc3545; color: white; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px; }
    .btn-delete:hover { background: #c82333; }
    
    .messages-container::-webkit-scrollbar { width: 6px; }
    .messages-container::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
    .messages-container::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .messages-container::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    
    .closed-notice { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; }
    .closed-notice i { font-size: 2rem; color: #9ca3af; margin-bottom: 12px; }
    .closed-notice h4 { color: #1f2937; font-weight: 600; margin-bottom: 8px; }
    .closed-notice p { color: #6b7280; font-size: 0.875rem; }
</style>

<div class="chat-page-bg">
    <!-- Header -->
    <div class="chat-header">
        <div class="flex justify-between items-start">
            <div>
                <a href="{{ route('admin.chats.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Chats
                </a>
                <h1><i class="fas fa-comments mr-2"></i>{{ $chat->subject }}</h1>
            </div>
            <div class="header-actions">
                <form action="{{ route('admin.chats.update-status', $chat) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="status-select">
                        <option value="open" {{ $chat->status === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ $chat->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="closed" {{ $chat->status === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </form>
                <form action="{{ route('admin.chats.destroy', $chat) }}" method="POST" onsubmit="return confirm('Delete this chat?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="info-cards">
        <div class="info-card">
            <h3><i class="fas fa-user"></i> Customer Info</h3>
            <p><strong>Name:</strong> {{ $chat->user_name ?? 'Guest' }}</p>
            <p><strong>Email:</strong> {{ $chat->user_email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $chat->user_phone ?? 'N/A' }}</p>
            <p style="margin-top: 12px;"><strong>Status:</strong> <span class="status-badge status-{{ $chat->status }}">{{ ucfirst($chat->status) }}</span></p>
        </div>
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Chat Info</h3>
            <p><strong>Created:</strong> {{ $chat->created_at->format('M d, Y H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $chat->updated_at->format('M d, Y H:i') }}</p>
            <p><strong>Total Messages:</strong> {{ $messages->count() }}</p>
            <p><strong>Unread:</strong> {{ $chat->unreadCount() }}</p>
        </div>
        <div class="info-card">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <button onclick="showQuoteModal()" class="btn-action btn-primary" style="margin-bottom: 10px;">
                <i class="fas fa-dollar-sign"></i> Send Quote
            </button>
            @if($chat->user_id)
                <a href="{{ route('admin.users.show', $chat->user_id) }}" class="btn-action btn-secondary">
                    <i class="fas fa-user-circle"></i> View Customer
                </a>
            @endif
            <a href="{{ route('admin.chats.index') }}" class="btn-action btn-secondary">
                <i class="fas fa-list"></i> All Chats
            </a>
        </div>
    </div>

    <!-- Conversation -->
    <div class="conversation-card">
        <h3><i class="fas fa-comments"></i> Conversation</h3>
        <div class="messages-container" id="messagesContainer">
            @forelse($messages as $message)
                <div class="message message-{{ $message->sender_type === 'user' ? 'user' : 'admin' }}">
                    <div class="message-bubble">
                        <p class="message-sender">
                            {{ $message->sender_type === 'user' ? ($message->user?->name ?? 'Customer') : 'Admin' }}
                        </p>
                        @if($message->image_path)
                            <img src="{{ asset('storage/' . $message->image_path) }}" alt="Chat image" style="max-width: 200px; border-radius: 8px; margin-bottom: 8px;">
                        @endif
                        <p class="message-text">{{ $message->message }}</p>
                        <p class="message-time">{{ $message->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #9ca3af;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 12px;"></i>
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Reply Form -->
    @if($chat->status !== 'closed')
        <div class="reply-card">
            <h3><i class="fas fa-reply"></i> Send Reply</h3>
            <form action="{{ route('admin.chats.reply', $chat) }}" method="POST" enctype="multipart/form-data" id="replyForm">
                @csrf
                
                <!-- Image Preview (shows above input when image is selected) -->
                <div id="imagePreview" class="hidden" style="margin-bottom: 12px;">
                    <div style="position: relative; display: inline-block;">
                        <img id="previewImg" src="" alt="Preview" style="max-height: 150px; border-radius: 12px; border: 2px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <button type="button" onclick="clearImage()" style="position: absolute; top: -8px; right: -8px; background: white; border: 2px solid #e5e7eb; border-radius: 50%; padding: 6px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'; this.style.borderColor='#fca5a5'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                            <svg style="width: 14px; height: 14px; color: #ef4444;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Horizontal Input Layout -->
                <div style="display: flex; align-items: center; gap: 8px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 24px; padding: 8px 12px; transition: all 0.2s;" onfocus="this.style.borderColor='#8B0000'" onblur="this.style.borderColor='#e5e7eb'">
                    <!-- Attach Image Button (+ icon) -->
                    <label for="image" style="cursor: pointer; display: flex; align-items: center; justify-center; width: 36px; height: 36px; background: white; border-radius: 50%; transition: all 0.2s; flex-shrink: 0; border: 2px solid #e5e7eb;" onmouseover="this.style.background='#8B0000'; this.style.borderColor='#8B0000';" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb';" title="Attach image">
                        <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="updateImagePreview(this)">
                        <svg style="width: 20px; height: 20px; color: #6b7280; transition: color 0.2s; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24" onmouseover="this.style.color='white'" onmouseout="this.style.color='#6b7280'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </label>

                    <!-- Message Input -->
                    <textarea id="message" name="message" required style="flex: 1; border: none; background: transparent; resize: none; outline: none; font-size: 14px; color: #1f2937; padding: 8px 4px; min-height: 20px; max-height: 120px; overflow-y: auto;" placeholder="Type your message..." rows="1" oninput="autoResize(this)"></textarea>

                    <!-- Send Button -->
                    <button type="submit" style="display: flex; align-items: center; justify-center; width: 36px; height: 36px; background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); border: none; border-radius: 50%; cursor: pointer; transition: all 0.2s; flex-shrink: 0; padding: 0;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(139,0,0,0.3)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'" title="Send message">
                        <svg style="width: 16px; height: 16px; color: white; display: block;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                        </svg>
                    </button>
                </div>

                @error('message')
                    <p style="color: #dc2626; font-size: 0.75rem; margin-top: 8px; padding-left: 12px;">{{ $message }}</p>
                @enderror
                @error('image')
                    <p style="color: #dc2626; font-size: 0.75rem; margin-top: 8px; padding-left: 12px;">{{ $message }}</p>
                @enderror
            </form>
        </div>
    @else
        <div class="reply-card">
            <div class="closed-notice">
                <i class="fas fa-lock"></i>
                <h4>Chat Closed</h4>
                <p>This chat has been closed. Change the status to reply.</p>
            </div>
        </div>
    @endif
</div>

<script>
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

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // Auto-scroll to bottom of messages
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Quote Modal Functions
    function showQuoteModal() {
        document.getElementById('quoteModal').classList.remove('hidden');
    }

    function closeQuoteModal() {
        document.getElementById('quoteModal').classList.add('hidden');
    }

    function sendQuote() {
        const price = document.getElementById('quotePrice').value;
        const description = document.getElementById('quoteDescription').value;
        
        if (!price || !description) {
            alert('Please fill in both price and description');
            return;
        }

        const quoteMessage = `📋 PRICE QUOTE\n\nPrice: ₱${parseFloat(price).toLocaleString()}\n\nDescription:\n${description}\n\nPlease review and let us know if you'd like to proceed with this custom order.`;
        
        document.getElementById('message').value = quoteMessage;
        closeQuoteModal();
        
        // Scroll to reply form
        document.querySelector('.reply-card').scrollIntoView({ behavior: 'smooth' });
    }
</script>

<!-- Quote Modal -->
<div id="quoteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Send Price Quote</h3>
            <button onclick="closeQuoteModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (₱)</label>
                <input type="number" id="quotePrice" step="0.01" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8B0000] focus:border-transparent" placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="quoteDescription" rows="4" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8B0000] focus:border-transparent resize-none" placeholder="Describe what's included in this quote..."></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button onclick="sendQuote()" class="flex-1 bg-gradient-to-r from-[#8B0000] to-[#6B0000] hover:from-[#6B0000] hover:to-[#5B0000] text-white font-semibold py-3 rounded-lg transition-all">
                    <i class="fas fa-paper-plane mr-2"></i> Send Quote
                </button>
                <button onclick="closeQuoteModal()" class="px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-lg transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
