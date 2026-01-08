@extends('layouts.app')

@section('title', 'My Chats')

@section('content')
<style>
    .chat-header-gradient {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }
    
    .chat-header-gradient::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .chat-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        border: 1px solid #f3f4f6;
    }
    
    .chat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #dc2626, #800000);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .chat-card:hover::before {
        transform: scaleX(1);
    }
    
    .chat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        border-color: #800000;
    }
    
    .maroon-btn {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        transition: all 0.3s ease;
    }
    
    .maroon-btn:hover {
        background: linear-gradient(135deg, #600000 0%, #400000 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(128, 0, 0, 0.3);
    }
    
    .status-badge {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    
    .status-badge.open {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    
    .status-badge.closed {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }
</style>

<!-- Hero Header -->
<div class="chat-header-gradient py-16 relative">
    <div class="max-w-6xl mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-1">My Chats</h1>
                    <p class="text-white/80 text-base">Connect with our support team</p>
                </div>
            </div>
            <a href="{{ route('chats.create') }}" class="bg-white text-[#800000] px-7 py-3.5 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-xl hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Chat
            </a>
        </div>
    </div>
</div>

<!-- Chats Content -->
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Chats List -->
        @if($chats->count() > 0)
            <div class="grid gap-5">
                @foreach($chats as $chat)
                    <a href="{{ route('chats.show', $chat) }}" class="group block chat-card transition-all duration-300 p-6">
                        <div class="flex justify-between items-start gap-6">
                            <div class="flex-1 min-w-0">
                                <!-- Chat Title -->
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="flex-shrink-0 p-3 bg-gradient-to-br from-red-50 to-red-100 rounded-xl transition-all group-hover:from-[#800000] group-hover:to-[#600000]">
                                        <svg class="w-6 h-6 text-[#800000] group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-[#800000] transition mb-2">{{ $chat->subject }}</h3>
                                        <p class="text-gray-600 text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Yakan User
                                        </p>
                                    </div>
                                </div>

                                <!-- Latest Message Preview -->
                                @if($chat->latestMessage())
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-100 group-hover:bg-red-50 group-hover:border-red-100 transition">
                                        <p class="text-sm text-gray-700 line-clamp-2 leading-relaxed">{{ Str::limit($chat->latestMessage()->message, 120) }}</p>
                                    </div>
                                @endif

                                <!-- Chat Meta Info -->
                                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $chat->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        {{ $chat->messages()->count() }} message{{ $chat->messages()->count() !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right Section: Status & Badge -->
                            <div class="text-right flex flex-col items-end gap-3 flex-shrink-0">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold status-badge {{ $chat->status }}">
                                    <span class="inline-block w-2 h-2 rounded-full {{ $chat->status === 'open' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ ucfirst($chat->status) }}
                                </span>

                                <!-- Unread Badge -->
                                @if($chat->unreadCount() > 0)
                                    <span class="inline-flex items-center justify-center bg-[#800000] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">
                                        {{ $chat->unreadCount() }} new
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Updated {{ $chat->updated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($chats->hasPages())
                <div class="mt-8">
                    {{ $chats->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg p-16 text-center border border-gray-100">
                <div class="mb-6">
                    <div class="inline-flex p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-3xl mb-6">
                        <svg class="w-20 h-20 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No chats yet</h3>
                <p class="text-gray-600 mb-8 text-base max-w-md mx-auto">Start a conversation with our support team to get help with your orders and questions</p>
                <a href="{{ route('chats.create') }}" class="inline-flex items-center gap-2 maroon-btn px-8 py-4 rounded-xl font-semibold shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create New Chat
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
