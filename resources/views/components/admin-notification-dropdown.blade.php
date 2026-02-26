<!-- Admin Notification Dropdown -->
<div class="relative" x-data="adminNotificationDropdown()" x-init="init()" @click.outside="open = false">
    
    <!-- Notification Button -->
    <button @click="open = !open; loadNotifications()" class="relative p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span x-show="unreadCount > 0" 
              class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold"
              x-text="unreadCount">
        </span>
    </button>

    <!-- Dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500" x-show="unreadCount > 0">
                    <span x-text="unreadCount"></span> unread
                </span>
                <button x-show="unreadCount > 0" 
                        @click="markAllAsRead()" 
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Mark all read
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto custom-scrollbar">
            <template x-for="notification in notifications" :key="notification.id">
                <div :class="{'bg-blue-50': !notification.is_read}" 
                     class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 cursor-pointer transition-colors"
                     @click="notification.url ? window.location.href = notification.url : null">
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="{
                                     'bg-blue-100': notification.type === 'order',
                                     'bg-indigo-100': notification.type === 'custom_order',
                                     'bg-green-100': notification.type === 'payment',
                                     'bg-purple-100': notification.type === 'shipping',
                                     'bg-gray-100': notification.type === 'system'
                                 }">
                                <i :class="notification.icon || 'fas fa-bell'" 
                                   class="text-sm"
                                   :class="{
                                       'text-blue-600': notification.type === 'order',
                                       'text-indigo-600': notification.type === 'custom_order',
                                       'text-green-600': notification.type === 'payment',
                                       'text-purple-600': notification.type === 'shipping',
                                       'text-gray-600': notification.type === 'system'
                                   }"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-medium text-gray-900 truncate" x-text="notification.title"></h4>
                                <button @click.stop="markAsRead(notification.id)" 
                                        class="text-xs text-blue-600 hover:text-blue-800 ml-2" 
                                        x-show="!notification.is_read">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2" x-text="notification.message"></p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500" x-text="notification.created_at"></span>
                                <span x-show="notification.url" class="text-xs text-blue-600 font-medium">
                                    View →
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="notifications.length === 0" class="px-4 py-8 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bell-slash text-gray-400 text-xl"></i>
                </div>
                <p class="text-sm text-gray-600">No notifications</p>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="px-4 py-8 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-sm text-gray-600 mt-2">Loading...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <a href="/admin/notifications" class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                View All Notifications →
            </a>
        </div>
    </div>
</div>

<script>
function adminNotificationDropdown() {
    return {
        open: false,
        notifications: [],
        unreadCount: 0,
        loading: false,
        
        getAuthToken() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('auth_token') || sessionStorage.getItem('auth_token') || null;
        },

        apiUrl(path) {
            const token = this.getAuthToken();
            return token ? `${path}?auth_token=${encodeURIComponent(token)}` : path;
        },

        init() {
            // Load unread count on page load
            this.loadUnreadCount();
            
            // Poll for new notifications every 30 seconds
            setInterval(() => {
                this.loadUnreadCount();
            }, 30000);
        },
        
        async loadNotifications() {
            this.loading = true;
            try {
                const response = await fetch(this.apiUrl('/api/v1/admin/notifications'), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                } else {
                    console.error('Failed to load notifications:', response.status);
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadUnreadCount() {
            try {
                const response = await fetch(this.apiUrl('/api/v1/admin/notifications/unread-count'), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.unreadCount = data.unread_count || 0;
                }
            } catch (error) {
                console.error('Failed to load unread count:', error);
            }
        },
        
        async markAsRead(id) {
            try {
                const response = await fetch(this.apiUrl(`/api/v1/admin/notifications/${id}/read`), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    // Update the notification in the list
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification) {
                        notification.is_read = true;
                    }
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch(this.apiUrl('/api/v1/admin/notifications/mark-all-read'), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    // Mark all notifications as read in the list
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error);
            }
        }
    }
}
</script>
