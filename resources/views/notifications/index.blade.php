@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    /* Hide floating background elements on notifications page */
    .floating-element,
    .floating-1,
    .floating-2,
    .floating-3 {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    
    /* Modal animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-maroon-600 to-maroon-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">Notifications</h1>
                        <p class="text-gray-600 mt-1">Stay updated with your orders and activities</p>
                    </div>
                </div>
                @if($notifications->count() > 0)
                    <div class="flex gap-3">
            
                        <button onclick="clearAllNotifications()" class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition duration-300 shadow-md hover:shadow-lg font-medium flex items-center gap-2">
                            <i class="fas fa-trash"></i>
                            Clear All
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        // Determine notification type and styling
                        $type = 'info';
                        $icon = 'fa-bell';
                        $iconColor = '#1d4ed8';
                        $borderColor = '#2563eb';
                        $bgColor = '#dbeafe';
                        
                        if (str_contains(strtolower($notification->title ?? ''), 'payment')) {
                            $type = 'payment';
                            $icon = 'fa-credit-card';
                            $iconColor = '#15803d';
                            $borderColor = '#16a34a';
                            $bgColor = '#dcfce7';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'order')) {
                            $type = 'order';
                            $icon = 'fa-box';
                            $iconColor = '#800000';
                            $borderColor = '#800000';
                            $bgColor = '#f5e6e6';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'shipped')) {
                            $type = 'shipped';
                            $icon = 'fa-truck';
                            $iconColor = '#7e22ce';
                            $borderColor = '#9333ea';
                            $bgColor = '#e9d5ff';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'delivered')) {
                            $type = 'delivered';
                            $icon = 'fa-check-circle';
                            $iconColor = '#047857';
                            $borderColor = '#059669';
                            $bgColor = '#d1fae5';
                        } elseif (str_contains(strtolower($notification->title ?? ''), 'custom')) {
                            $type = 'custom';
                            $icon = 'fa-palette';
                            $iconColor = '#c2410c';
                            $borderColor = '#ea580c';
                            $bgColor = '#fed7aa';
                        }
                    @endphp
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition duration-300 p-6 overflow-hidden relative" style="border-left: 4px solid {{ $borderColor }};">
                        <div class="relative flex items-start justify-between z-10">
                            <div class="flex items-start gap-4 flex-1">
                                <!-- Icon -->
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-sm" style="background-color: {{ $bgColor }};">
                                        <i class="fas {{ $icon }} text-xl" style="color: {{ $iconColor }};"></i>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="text-lg font-bold text-gray-900">
                                            {{ $notification->title ?? 'Notification' }}
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white z-20" style="border: 2px solid {{ $borderColor }}; color: {{ $borderColor }};">
                                            <span class="inline-block w-2 h-2 rounded-full mr-1.5" style="background-color: {{ $borderColor }};"></span>
                                            {{ ucfirst($type) }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mt-2 leading-relaxed text-sm">
                                        {{ $notification->message }}
                                    </p>
                                    
                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center gap-1.5 text-gray-500">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M d, Y H:i') }}">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </time>
                                        </span>
                                        
                                        @if($notification->data && isset($notification->data['order_id']))
                                            <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:shadow-md transition font-medium text-xs" style="background-color: {{ $bgColor }}; color: {{ $borderColor }};">
                                                <i class="fas fa-box"></i>
                                                View Order
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Button -->
                            <button onclick="deleteNotification({{ $notification->id }}, this)" 
                                    class="ml-4 flex-shrink-0 text-gray-300 hover:text-red-600 transition duration-200 p-2 hover:bg-red-50 rounded-lg" 
                                    title="Delete notification">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="mt-12">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm p-16 text-center border border-gray-100">
                <div class="text-6xl mb-6">
                    🔔
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Notifications</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">You're all caught up! Check back later for updates on your orders and account activities.</p>
                <a href="{{ route('products.index') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-300 shadow-md hover:shadow-lg font-semibold flex items-center gap-2 justify-center">
                    <i class="fas fa-home"></i>
                    Back to Products
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" onclick="closeConfirmationModal()"></div>
        
        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-2" id="modalTitle">
                            Delete Notification?
                        </h3>
                        <p class="text-gray-600" id="modalMessage">
                            Are you sure you want to delete this notification? This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <button onclick="confirmAction()" 
                        class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-red-600 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                    Delete
                </button>
                <button onclick="closeConfirmationModal()" 
                        class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;

function showConfirmationModal(title, message, callback) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    confirmCallback = callback;
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('hidden');
    modal.classList.add('animate-fadeIn');
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.add('hidden');
    confirmCallback = null;
}

function confirmAction() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmationModal();
}

function deleteNotification(notificationId, button) {
    showConfirmationModal(
        'Delete Notification?',
        'Are you sure you want to delete this notification? This action cannot be undone.',
        () => {
            button.disabled = true;
    
    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Fade out the notification card
            const card = button.closest('.bg-white.rounded-xl');
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                card.remove();
                // Check if there are no more notifications
                const container = document.querySelector('.space-y-3');
                if (container && container.children.length === 0) {
                    // Show empty state without reload
                    const emptyState = document.createElement('div');
                    emptyState.className = 'bg-white rounded-xl shadow-sm p-16 text-center border border-gray-100';
                    emptyState.innerHTML = `
                        <div class="text-6xl mb-6">
                            🔔
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Notifications</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">You're all caught up! Check back later for updates on your orders and account activities.</p>
                        <a href="${window.location.origin}/products" class="inline-block px-8 py-3 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-300 shadow-md hover:shadow-lg font-semibold flex items-center gap-2 justify-center">
                            <i class="fas fa-home"></i>
                            Back to Products
                        </a>
                    `;
                    container.parentElement.replaceChild(emptyState, container);
                    
                    // Remove the "Clear All" button
                    const clearAllBtn = document.querySelector('button[onclick="clearAllNotifications()"]');
                    if (clearAllBtn) {
                        clearAllBtn.parentElement.remove();
                    }
                }
            }, 300);
        } else {
            throw new Error(data.message || 'Failed to delete notification');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Failed to delete notification. Please try again.');
        button.disabled = false;
    });
        }
    );
}

function markAllAsRead() {
    const url = '/notifications/mark-all-read';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message and reload
            alert('All notifications marked as read!');
            location.reload();
        } else {
            throw new Error(data.message || 'Failed to mark notifications as read');
        }
    })
    .catch(error => {
        console.error('Mark all as read error:', error);
        alert('Failed to mark notifications as read. Please try again.');
    });
}

function clearAllNotifications() {
    showConfirmationModal(
        'Clear All Notifications?',
        'Are you sure you want to delete all notifications? This will permanently remove all your notifications.',
        () => {
            fetch('/notifications/clear', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove all notification cards with animation
            const notificationsList = document.querySelector('.space-y-3');
            if (notificationsList) {
                notificationsList.style.transition = 'opacity 0.3s ease';
                notificationsList.style.opacity = '0';
                
                setTimeout(() => {
                    // Replace with empty state
                    const container = notificationsList.parentElement;
                    notificationsList.remove();
                    
                    const emptyState = document.createElement('div');
                    emptyState.className = 'bg-white rounded-xl shadow-sm p-16 text-center border border-gray-100';
                    emptyState.innerHTML = `
                        <div class="text-6xl mb-6">
                            🔔
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Notifications</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">You're all caught up! Check back later for updates on your orders and account activities.</p>
                        <a href="${window.location.origin}/products" class="inline-block px-8 py-3 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-300 shadow-md hover:shadow-lg font-semibold flex items-center gap-2 justify-center">
                            <i class="fas fa-home"></i>
                            Back to Products
                        </a>
                    `;
                    container.appendChild(emptyState);
                    
                    // Remove the "Clear All" button from header
                    const clearAllBtn = document.querySelector('button[onclick="clearAllNotifications()"]');
                    if (clearAllBtn) {
                        clearAllBtn.parentElement.remove();
                    }
                    
                    // Update notification badge in header
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.classList.add('scale-0', 'opacity-0');
                        badge.classList.remove('scale-100', 'opacity-100');
                    }
                }, 300);
            }
        } else {
            throw new Error(data.message || 'Failed to clear notifications');
        }
    })
    .catch(error => {
        console.error('Clear all error:', error);
        alert('Failed to clear notifications. Please try again.');
    });
        }
    );
}
</script>
@endsection
