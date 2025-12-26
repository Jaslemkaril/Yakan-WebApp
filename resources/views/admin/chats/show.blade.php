@extends('layouts.admin')

@section('title', 'Chat: ' . $chat->subject)

@section('content')
<style>
    .chat-page-bg { background: linear-gradient(135deg, #800000 0%, #600000 100%); min-height: 100vh; padding: 24px; margin: -24px; }
    .chat-header { margin-bottom: 24px; }
    .chat-header h1 { font-size: 1.75rem; font-weight: bold; color: #ffffff; margin-bottom: 4px; }
    .chat-header .back-link { color: #e8d4d4; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; transition: color 0.3s; }
    .chat-header .back-link:hover { color: #ffffff; }
    
    .info-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
    .info-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .info-card h3 { color: #ffffff; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    .info-card p { color: #e8d4d4; margin-bottom: 8px; font-size: 0.875rem; }
    .info-card strong { color: #ffffff; }
    
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-open { background: rgba(40, 167, 69, 0.2); color: #90EE90; border: 1px solid rgba(40, 167, 69, 0.4); }
    .status-pending { background: rgba(255, 193, 7, 0.2); color: #FFD700; border: 1px solid rgba(255, 193, 7, 0.4); }
    .status-closed { background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); }
    
    .btn-action { display: block; width: 100%; padding: 10px 16px; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; transition: all 0.3s; margin-bottom: 10px; font-size: 0.875rem; }
    .btn-primary { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    .btn-secondary { background: rgba(255, 255, 255, 0.1); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); }
    .btn-secondary:hover { background: rgba(255, 255, 255, 0.2); }
    
    .conversation-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); margin-bottom: 24px; }
    .conversation-card h3 { color: #ffffff; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    
    .messages-container { background: rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 16px; max-height: 400px; overflow-y: auto; }
    .message { margin-bottom: 16px; max-width: 80%; }
    .message-user { margin-left: 0; }
    .message-admin { margin-left: auto; }
    .message-bubble { padding: 12px 16px; border-radius: 12px; }
    .message-user .message-bubble { background: rgba(255, 255, 255, 0.95); color: #800000; border-bottom-left-radius: 4px; }
    .message-admin .message-bubble { background: linear-gradient(135deg, #600000 0%, #400000 100%); color: #ffffff; border-bottom-right-radius: 4px; }
    .message-sender { font-size: 0.7rem; font-weight: 600; margin-bottom: 4px; }
    .message-user .message-sender { color: #800000; }
    .message-admin .message-sender { color: #e8d4d4; }
    .message-text { font-size: 0.875rem; line-height: 1.5; word-break: break-word; }
    .message-time { font-size: 0.65rem; margin-top: 6px; opacity: 0.7; }
    .message-user .message-time { color: #666; }
    .message-admin .message-time { color: #d9c0c0; }
    
    .reply-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .reply-card h3 { color: #ffffff; font-weight: 600; margin-bottom: 16px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #ffffff; margin-bottom: 8px; }
    .form-textarea { width: 100%; padding: 12px; border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 8px; font-size: 0.875rem; resize: vertical; background: rgba(0, 0, 0, 0.2); color: #ffffff; min-height: 100px; }
    .form-textarea::placeholder { color: #d9c0c0; }
    .form-textarea:focus { outline: none; border-color: rgba(255, 255, 255, 0.5); }
    
    .drop-zone { border: 2px dashed rgba(255, 255, 255, 0.3); border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .drop-zone:hover { border-color: rgba(255, 255, 255, 0.6); background: rgba(255, 255, 255, 0.05); }
    .drop-zone i { font-size: 1.5rem; color: #d9c0c0; margin-bottom: 8px; }
    .drop-zone p { color: #d9c0c0; font-size: 0.8rem; }
    
    .btn-send { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-send:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    
    .header-actions { display: flex; gap: 10px; align-items: center; }
    .status-select { padding: 8px 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff; font-size: 0.875rem; cursor: pointer; }
    .status-select option { color: #333; background: #fff; }
    .btn-delete { background: #dc3545; color: #ffffff; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px; }
    .btn-delete:hover { background: #c82333; }
    
    .messages-container::-webkit-scrollbar { width: 6px; }
    .messages-container::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 3px; }
    .messages-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
    .messages-container::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }
    
    .closed-notice { background: rgba(0, 0, 0, 0.2); border-radius: 12px; padding: 24px; text-align: center; }
    .closed-notice i { font-size: 2rem; color: #d9c0c0; margin-bottom: 12px; }
    .closed-notice h4 { color: #ffffff; font-weight: 600; margin-bottom: 8px; }
    .closed-notice p { color: #d9c0c0; font-size: 0.875rem; }
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
            @if($chat->user_id)
                <a href="{{ route('admin.users.show', $chat->user_id) }}" class="btn-action btn-primary">
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
                <div style="text-align: center; padding: 40px; color: #d9c0c0;">
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
            <form action="{{ route('admin.chats.reply', $chat) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom: 16px;">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" required class="form-textarea" placeholder="Type your reply..."></textarea>
                    @error('message')
                        <p style="color: #ff6b6b; font-size: 0.8rem; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Attach Image (Optional)</label>
                    <div class="drop-zone" id="imageDropZone">
                        <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="updateImagePreview(this)">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag and drop or click to select</p>
                    </div>
                    <div id="imagePreview" class="mt-3 hidden">
                        <img id="previewImg" src="" alt="Preview" style="max-height: 100px; border-radius: 8px; border: 2px solid rgba(255,255,255,0.3);">
                        <button type="button" onclick="clearImage()" style="display: block; margin-top: 8px; color: #ff6b6b; font-size: 0.75rem; background: none; border: none; cursor: pointer;">Remove</button>
                    </div>
                </div>

                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
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
    const imageDropZone = document.getElementById('imageDropZone');
    const imageInput = document.getElementById('image');

    if (imageDropZone && imageInput) {
        imageDropZone.addEventListener('click', () => imageInput.click());
        imageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageDropZone.style.borderColor = 'rgba(255, 255, 255, 0.6)';
            imageDropZone.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
        });
        imageDropZone.addEventListener('dragleave', () => {
            imageDropZone.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            imageDropZone.style.backgroundColor = 'transparent';
        });
        imageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageDropZone.style.borderColor = 'rgba(255, 255, 255, 0.3)';
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
        if (imageInput) imageInput.value = '';
        document.getElementById('imagePreview').classList.add('hidden');
    }

    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
</script>
@endsection
