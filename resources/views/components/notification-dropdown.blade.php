@auth
<div x-data="notificationDropdown()" class="relative">
    <!-- Bell Icon Button with Animation -->
    <button @click="open = !open" class="relative p-2.5 text-gray-600 hover:text-maroon-600 transition duration-300 hover:scale-110 group">
        <div class="absolute inset-0 bg-maroon-600/10 rounded-full opacity-0 group-hover:opacity-100 transition duration-300"></div>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        <!-- Animated Badge -->
        <span id="notification-badge" class="absolute -top-2 -right-2 bg-gradient-to-br from-red-500 via-red-600 to-red-700 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-lg animate-pulse z-20 {{ auth()->user()->notifications()->count() === 0 ? 'hidden' : '' }}">
            {{ auth()->user()->notifications()->count() > 9 ? '9+' : auth()->user()->notifications()->count() }}
        </span>
    </button>

    <!-- Enhanced Dropdown Menu -->
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-3 w-96 bg-white rounded-2xl shadow-2xl z-50 overflow-hidden border border-gray-100 backdrop-blur-sm">
        
        <!-- Premium Header with Decorative Elements -->
        <div class="bg-gradient-to-r from-maroon-600 via-maroon-700 to-maroon-800 p-6 text-white relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20 animate-float"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 animate-float-delayed"></div>
            <div class="absolute top-1/2 left-1/2 w-24 h-24 bg-white/3 rounded-full -ml-12 -mt-12 animate-pulse"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/30 shadow-lg">
                        <svg class="w-6 h-6 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg tracking-wide">Notifications</h3>
                        <p class="text-xs text-white/70 font-medium">{{ auth()->user()->notifications()->count() }} new update{{ auth()->user()->notifications()->count() !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                <span class="bg-white/20 px-4 py-2 rounded-full text-sm font-bold backdrop-blur-sm border border-white/30 shadow-lg">
                    {{ auth()->user()->notifications()->count() }}
                </span>
            </div>
        </div>

        <!-- Notifications List with Premium Scroll -->
        <div class="max-h-96 overflow-y-auto scrollbar-premium">
            @forelse(auth()->user()->notifications()->orderBy('created_at', 'desc')->take(6)->get() as $index => $notification)
                <div class="p-4 border-b border-gray-100 hover:bg-gradient-to-r hover:from-maroon-50 hover:to-transparent transition duration-300 cursor-pointer group relative overflow-hidden" style="animation: slideInDown 0.4s ease-out {{ $index * 0.08 }}s both;">
                    <!-- Left accent bar -->
                    <div class="absolute left-0 top-0 w-1.5 h-full bg-gradient-to-b from-maroon-400 via-maroon-500 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    
                    <!-- Hover background glow -->
                    <div class="absolute inset-0 bg-gradient-to-r from-maroon-500/0 via-maroon-500/0 to-maroon-500/0 group-hover:from-maroon-500/5 group-hover:via-maroon-500/3 group-hover:to-transparent transition duration-300"></div>
                    
                    <div class="flex gap-3 relative z-10">
                        <!-- Dynamic Icon with Animation -->
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-maroon-100 to-maroon-50 flex items-center justify-center group-hover:from-maroon-200 group-hover:to-maroon-100 transition duration-300 shadow-sm group-hover:shadow-md group-hover:scale-110">
                                @if(str_contains($notification->message, 'payment'))
                                    <svg class="w-5 h-5 text-maroon-600 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                @elseif(str_contains($notification->message, 'order'))
                                    <svg class="w-5 h-5 text-maroon-600 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                @elseif(str_contains($notification->message, 'approved'))
                                    <svg class="w-5 h-5 text-green-600 group-hover:animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-maroon-600 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 group-hover:text-maroon-600 transition duration-200">
                                {{ $notification->title ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1.5 line-clamp-2 leading-relaxed group-hover:text-gray-700 transition">
                                {{ Str::limit($notification->message, 90) }}
                            </p>
                            <div class="flex items-center gap-2 mt-2.5">
                                <svg class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-.707.707a1 1 0 101.414 1.414L9 9.414V6z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-xs text-gray-400 font-medium group-hover:text-gray-500 transition">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center animate-fadeIn">
                    <div class="text-6xl mb-4 opacity-60 animate-bounce">🔔</div>
                    <p class="text-gray-600 font-semibold text-lg">No notifications yet</p>
                    <p class="text-xs text-gray-400 mt-2">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        <!-- Premium Footer with Gradient -->
        <div class="bg-gradient-to-r from-gray-50 via-white to-gray-50 p-4 border-t border-gray-100 hover:from-gray-100 hover:via-gray-50 hover:to-gray-100 transition duration-300">
            <a href="{{ route('notifications.index') }}" class="text-sm font-bold text-maroon-600 hover:text-maroon-700 flex items-center justify-center gap-2 group/link py-2.5 px-3 rounded-lg hover:bg-maroon-50 transition duration-300">
                <span>View All Notifications</span>
                <svg class="w-4 h-4 group-hover/link:translate-x-1 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes float-delayed {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(20px); }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    
    .animate-float-delayed {
        animation: float-delayed 6s ease-in-out infinite;
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    .animate-bounce {
        animation: bounce 1s ease-in-out infinite;
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out;
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .scrollbar-premium::-webkit-scrollbar {
        width: 8px;
    }
    
    .scrollbar-premium::-webkit-scrollbar-track {
        background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
        border-radius: 10px;
    }
    
    .scrollbar-premium::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #e8cccc, #d4a5a5);
        border-radius: 10px;
        border: 2px solid #f9fafb;
    }
    
    .scrollbar-premium::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #d4a5a5, #c08080);
    }
    
    .text-maroon-600 { color: #800000; }
    .hover\:text-maroon-700:hover { color: #600000; }
    .from-maroon-600 { --tw-gradient-from: #800000; }
    .via-maroon-700 { --tw-gradient-via: #600000; }
    .to-maroon-800 { --tw-gradient-to: #400000; }
    .from-maroon-50 { --tw-gradient-from: #faf8f8; }
    .to-transparent { --tw-gradient-to: transparent; }
    .from-maroon-100 { --tw-gradient-from: #f5e6e6; }
    .to-maroon-50 { --tw-gradient-to: #faf8f8; }
    .from-maroon-200 { --tw-gradient-from: #e8cccc; }
    .to-maroon-100 { --tw-gradient-to: #f5e6e6; }
    .from-maroon-400 { --tw-gradient-from: #b30000; }
    .hover\:bg-maroon-50:hover { background-color: #faf8f8; }
</style>
@endauth
