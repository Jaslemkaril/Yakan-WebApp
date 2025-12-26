@extends('layouts.app')

@section('title', 'My Chats')

@section('content')
<style>
    .maroon-bg { background: linear-gradient(135deg, #800000 0%, #600000 100%); }
    .maroon-card { background: linear-gradient(135deg, #a00000 0%, #800000 100%); }
    .maroon-card-hover:hover { background: linear-gradient(135deg, #900000 0%, #700000 100%); }
    .maroon-text { color: #ffffff; }
    .maroon-text-secondary { color: #e8d4d4; }
    .maroon-text-tertiary { color: #d9c0c0; }
    .white-btn { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000; }
    .white-btn:hover { background: linear-gradient(135deg, #f3f3f3 0%, #ffffff 100%); }
</style>
<div class="min-h-screen maroon-bg py-12">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header Section -->
        <div class="mb-12">
            <div class="flex justify-between items-start gap-6 mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%);">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold maroon-text mb-1">My Chats</h1>
                            <p class="maroon-text-secondary text-base">Connect with our support team</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('chats.create') }}" class="white-btn px-7 py-3.5 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2 shadow-xl hover:shadow-2xl hover:scale-105 transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Chat
                </a>
            </div>
            <div class="h-1.5 w-24 rounded-full shadow-lg" style="background: linear-gradient(90deg, #ffffff, #f3f3f3, #ffffff);"></div>
        </div>

        <!-- Chats List -->
        @if($chats->count() > 0)
            <div class="grid gap-4">
                @foreach($chats as $chat)
                    <a href="{{ route('chats.show', $chat) }}" class="group block maroon-card-hover rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 border hover:translate-x-1 overflow-hidden relative" style="border-color: #800000;">
                        <!-- Animated background gradient on hover -->
                        <div class="absolute inset-0 transition-all duration-300" style="background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,0)); opacity: 0;" class="group-hover:opacity-10"></div>
                        
                        <div class="relative flex justify-between items-start gap-6">
                            <div class="flex-1 min-w-0">
                                <!-- Chat Title -->
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0 p-2.5 rounded-lg shadow-md transition-all" style="background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #800000;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold maroon-text group-hover:text-white transition">{{ $chat->subject }}</h3>
                                        <p class="maroon-text-secondary text-sm mt-1.5 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $chat->user_name ?? 'Guest' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Latest Message Preview -->
                                @if($chat->latestMessage())
                                    <div class="rounded-lg p-3.5 mb-4 border" style="background: rgba(160, 0, 0, 0.3); border-color: rgba(160, 0, 0, 0.5);">
                                        <p class="text-sm line-clamp-2 leading-relaxed maroon-text">{{ Str::limit($chat->latestMessage()->message, 120) }}</p>
                                    </div>
                                @endif

                                <!-- Chat Meta Info -->
                                <div class="flex flex-wrap items-center gap-4 text-xs maroon-text-secondary">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $chat->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d9c0c0;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        {{ $chat->messages()->count() }} message{{ $chat->messages()->count() !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right Section: Status & Badge -->
                            <div class="text-right flex flex-col items-end gap-3 flex-shrink-0">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold" style="background: rgba(160, 0, 0, 0.3); color: #e8d4d4; border: 1px solid rgba(160, 0, 0, 0.5);">
                                    <span class="inline-block w-2 h-2 rounded-full" style="background: #d9c0c0;"></span>
                                    {{ ucfirst($chat->status) }}
                                </span>

                                <!-- Unread Badge -->
                                @if($chat->unreadCount() > 0)
                                    <span class="inline-flex items-center justify-center text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse" style="background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000;">
                                        {{ $chat->unreadCount() }} new
                                    </span>
                                @else
                                    <span class="maroon-text-tertiary text-xs">Updated {{ $chat->updated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($chats->hasPages())
                <div class="mt-10">
                    {{ $chats->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="rounded-2xl shadow-2xl p-16 text-center border" style="background: linear-gradient(135deg, #a00000 0%, #800000 100%); border-color: #800000;">
                <div class="mb-6">
                    <div class="inline-flex p-4 rounded-2xl mb-6" style="background: rgba(255, 255, 255, 0.2);">
                        <svg class="w-24 h-24 maroon-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold maroon-text mb-2">No chats yet</h3>
                <p class="maroon-text-secondary mb-8 text-base">Start a conversation with our support team to get help</p>
                <a href="{{ route('chats.create') }}" class="inline-flex items-center gap-2 white-btn px-8 py-3.5 rounded-xl font-semibold transition-all duration-300 shadow-xl hover:shadow-2xl hover:scale-105 transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create New Chat
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .grid > a {
        animation: slideIn 0.3s ease-out;
    }

    .grid > a:nth-child(1) { animation-delay: 0.05s; }
    .grid > a:nth-child(2) { animation-delay: 0.1s; }
    .grid > a:nth-child(3) { animation-delay: 0.15s; }
    .grid > a:nth-child(n+4) { animation-delay: 0.2s; }
</style>
@endsection
