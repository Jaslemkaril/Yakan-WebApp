@auth
@php
    $unreadNotificationCount = auth()->user()->notifications()->whereNull('read_at')->count();
    $notificationItems = auth()->user()->notifications()->orderBy('created_at', 'desc')->take(6)->get();
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">

    {{-- ── Bell Button ── --}}
    <button @click="open = !open"
            class="notif-bell relative p-2.5 rounded-full transition-all duration-300 focus:outline-none group"
            :class="open ? 'bg-[#800000]/10 text-[#800000]' : 'text-gray-500 hover:text-[#800000] hover:bg-[#800000]/8'"
            aria-label="Notifications">

        {{-- Pulse ring when there are unread notifications --}}
        @if($unreadNotificationCount > 0)
            <span class="absolute inset-0 rounded-full bg-[#800000]/20 notif-pulse-ring"></span>
        @endif

        <svg class="w-[22px] h-[22px] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        {{-- Badge --}}
        <span id="notification-badge"
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 flex items-center justify-center text-[10px] font-bold text-white rounded-full shadow-md leading-none transition-all duration-300 {{ $unreadNotificationCount === 0 ? 'scale-0 opacity-0' : 'scale-100 opacity-100' }}"
              style="background: #800000;">
            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
        </span>
    </button>

    {{-- ── Dropdown Panel ── --}}
    <div x-show="open"
         x-cloak
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="absolute right-0 mt-3 w-[400px] bg-white rounded-2xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.18)] z-50 border border-gray-100/80 overflow-hidden">

        {{-- ── Header ── --}}
        <div class="relative overflow-hidden px-5 py-4" style="background: linear-gradient(135deg, #800000 0%, #5a0000 100%);">
            {{-- Decorative circles --}}
            <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full opacity-[0.07]" style="background:white;"></div>
            <div class="absolute -bottom-8 -left-4 w-20 h-20 rounded-full opacity-[0.05]" style="background:white;"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.15); backdrop-filter:blur(4px); border:1px solid rgba(255,255,255,0.25);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-[15px] leading-tight tracking-wide">Notifications</h3>
                        @if($unreadNotificationCount > 0)
                            <p class="text-white/60 text-[11px] mt-0.5 font-medium">{{ $unreadNotificationCount }} unread message{{ $unreadNotificationCount !== 1 ? 's' : '' }}</p>
                        @else
                            <p class="text-white/60 text-[11px] mt-0.5 font-medium">All caught up!</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Unread pill --}}
                    @if($unreadNotificationCount > 0)
                        <span class="text-[11px] font-bold text-[#800000] bg-white px-2.5 py-1 rounded-full shadow-sm">
                            {{ $unreadNotificationCount }} new
                        </span>
                    @endif
                    {{-- Mark all read --}}
                    @if($unreadNotificationCount > 0)
                        <button onclick="markAllNotificationsRead(event)"
                                class="text-[11px] text-white/70 hover:text-white underline underline-offset-2 transition duration-200 whitespace-nowrap">
                            Mark all read
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Divider with subtle label --}}
        @if($notificationItems->count() > 0)
        <div class="px-5 pt-3 pb-1 flex items-center gap-2">
            <span class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Recent</span>
            <div class="flex-1 h-px bg-gray-100"></div>
        </div>
        @endif

        {{-- ── Notification List ── --}}
        <div class="max-h-[360px] overflow-y-auto notif-scroll">
            @forelse($notificationItems as $notification)
                @php
                    $isUnread = !$notification->read_at;
                    $msg = strtolower($notification->message ?? '');
                    $title = strtolower($notification->title ?? '');

                    // Determine type for icon/color
                    if (str_contains($msg, 'payment') || str_contains($msg, 'verified') || str_contains($msg, 'bank') || str_contains($msg, 'gcash') || str_contains($title, 'payment')) {
                        $iconType = 'payment';
                    } elseif (str_contains($msg, 'delivered') || str_contains($msg, 'shipped') || str_contains($title, 'deliver') || str_contains($title, 'ship')) {
                        $iconType = 'delivery';
                    } elseif (str_contains($msg, 'approved') || str_contains($msg, 'confirmed') || str_contains($title, 'approved') || str_contains($title, 'confirmed')) {
                        $iconType = 'approved';
                    } elseif (str_contains($msg, 'cancelled') || str_contains($msg, 'rejected') || str_contains($title, 'cancel') || str_contains($title, 'reject')) {
                        $iconType = 'cancelled';
                    } elseif (str_contains($msg, 'order') || str_contains($title, 'order')) {
                        $iconType = 'order';
                    } elseif (str_contains($msg, 'custom') || str_contains($title, 'custom')) {
                        $iconType = 'custom';
                    } else {
                        $iconType = 'default';
                    }
                @endphp

                <div onclick="markNotificationAsRead({{ $notification->id }}, this)"
                     class="notif-item group relative flex items-start gap-3.5 px-5 py-3.5 cursor-pointer transition-all duration-200 border-b border-gray-50 last:border-b-0
                            {{ $isUnread ? 'bg-[#fdf8f8]' : 'bg-white' }}
                            hover:bg-[#faf3f3]"
                     data-id="{{ $notification->id }}">

                    {{-- Unread left stripe --}}
                    <div class="absolute left-0 top-0 bottom-0 w-[3px] rounded-r-full transition-all duration-300
                                {{ $isUnread ? 'bg-[#800000]' : 'bg-transparent group-hover:bg-[#800000]/30' }}"></div>

                    {{-- Icon bubble --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm transition-transform duration-200 group-hover:scale-105 mt-0.5
                        @if($iconType === 'payment') bg-blue-50
                        @elseif($iconType === 'delivery') bg-purple-50
                        @elseif($iconType === 'approved') bg-green-50
                        @elseif($iconType === 'cancelled') bg-red-50
                        @elseif($iconType === 'order') bg-amber-50
                        @elseif($iconType === 'custom') bg-pink-50
                        @else bg-gray-50
                        @endif">
                        @if($iconType === 'payment')
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        @elseif($iconType === 'delivery')
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        @elseif($iconType === 'approved')
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($iconType === 'cancelled')
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($iconType === 'order')
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @elseif($iconType === 'custom')
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Text content --}}
                    <div class="flex-1 min-w-0 pr-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[13px] font-semibold leading-snug transition-colors duration-200
                                      {{ $isUnread ? 'text-gray-900 group-hover:text-[#800000]' : 'text-gray-600 group-hover:text-[#800000]' }}">
                                {{ $notification->title ?? 'Notification' }}
                            </p>
                            @if($isUnread)
                                <span class="notif-unread-dot flex-shrink-0 w-2 h-2 rounded-full mt-1.5" style="background:#800000;"></span>
                            @endif
                        </div>
                        <p class="text-[12px] text-gray-500 mt-1 leading-relaxed line-clamp-2">
                            {{ Str::limit($notification->message, 100) }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-[11px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty state --}}
                <div class="py-14 px-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:linear-gradient(135deg,#fdf2f2,#fce8e8);">
                        <svg class="w-8 h-8" style="color:#800000;opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-semibold text-sm">All caught up!</p>
                    <p class="text-gray-400 text-xs mt-1.5 max-w-[200px] leading-relaxed">No notifications right now. We'll let you know when something happens.</p>
                </div>
            @endforelse
        </div>

        {{-- ── Footer ── --}}
        <div class="border-t border-gray-100 bg-gray-50/60">
            <a href="{{ route('notifications.index') }}"
               class="flex items-center justify-center gap-2 py-3.5 px-5 text-[13px] font-semibold transition-all duration-200 group/footer"
               style="color:#800000;">
                <span class="group-hover/footer:underline">View all notifications</span>
                <svg class="w-4 h-4 transition-transform duration-200 group-hover/footer:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<style>
    /* ── Unread dot pulse ── */
    @keyframes notif-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(128,0,0,0.35); }
        50%       { box-shadow: 0 0 0 6px rgba(128,0,0,0); }
    }
    .notif-pulse-ring {
        animation: notif-pulse 2s ease-in-out infinite;
    }

    /* ── Bell shake on first load if unread ── */
    @keyframes notif-bell-shake {
        0%,100% { transform: rotate(0deg); }
        15%      { transform: rotate(12deg); }
        30%      { transform: rotate(-10deg); }
        45%      { transform: rotate(8deg); }
        60%      { transform: rotate(-6deg); }
        75%      { transform: rotate(3deg); }
    }
    .notif-bell-shake svg {
        animation: notif-bell-shake 1s ease 0.5s 1;
        transform-origin: top center;
    }

    /* ── Scrollbar ── */
    .notif-scroll { scrollbar-width: thin; scrollbar-color: #d4a5a5 #f9fafb; }
    .notif-scroll::-webkit-scrollbar { width: 5px; }
    .notif-scroll::-webkit-scrollbar-track { background: #f9fafb; border-radius: 10px; }
    .notif-scroll::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #e8cccc, #c08080); border-radius: 10px; }
    .notif-scroll::-webkit-scrollbar-thumb:hover { background: #b05050; }

    /* ── Item hover lift ── */
    .notif-item { transform: translateX(0); }
    .notif-item:hover { transform: translateX(2px); }

    /* ── Line clamp ── */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ── Static color helpers ── */
    [x-cloak] { display: none !important; }
</style>

<script>
function markNotificationAsRead(notificationId, el) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Visually mark as read immediately — no full reload
            if (el) {
                el.classList.remove('bg-[#fdf8f8]');
                el.classList.add('bg-white');
                const dot = el.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
                const stripe = el.querySelector('.absolute.left-0');
                if (stripe) { stripe.classList.remove('bg-[#800000]'); stripe.classList.add('bg-transparent'); }
            }

            // Update badge
            const badge = document.getElementById('notification-badge');
            if (badge) {
                const current = parseInt(badge.textContent.replace('+', '')) || 0;
                const next = Math.max(0, current - 1);
                if (next === 0) {
                    badge.classList.add('scale-0', 'opacity-0');
                    badge.classList.remove('scale-100', 'opacity-100');
                } else {
                    badge.textContent = next > 9 ? '9+' : next;
                }
            }
        }
    })
    .catch(err => console.error('Notification error:', err));
}

function markAllNotificationsRead(e) {
    e.stopPropagation();
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Clear all unread styling
            document.querySelectorAll('.notif-unread-dot').forEach(d => d.remove());
            document.querySelectorAll('.notif-item').forEach(item => {
                item.classList.remove('bg-[#fdf8f8]');
                item.classList.add('bg-white');
                const stripe = item.querySelector('.absolute.left-0');
                if (stripe) { stripe.classList.remove('bg-[#800000]'); stripe.classList.add('bg-transparent'); }
            });

            // Hide badge
            const badge = document.getElementById('notification-badge');
            if (badge) { badge.classList.add('scale-0', 'opacity-0'); badge.classList.remove('scale-100', 'opacity-100'); }

            // Hide the pulse ring
            document.querySelectorAll('.notif-pulse-ring').forEach(r => r.remove());

            // Remove the "mark all read" button
            e.target.remove();
        }
    })
    .catch(err => console.error('Mark all error:', err));
}

// Add shake animation if there are unread notifications
document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('notification-badge');
    const bell = document.querySelector('.notif-bell');
    if (badge && !badge.classList.contains('scale-0') && bell) {
        bell.classList.add('notif-bell-shake');
    }
});
</script>
@endauth
