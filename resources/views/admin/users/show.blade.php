@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- User Details Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-900 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">User Details</h1>
                <p class="text-maroon-100 text-lg">View and manage user information and activity</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.users.edit', $user->id) }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}" class="bg-white text-maroon-700 rounded-lg px-4 py-2 hover:bg-maroon-50 transition-colors font-semibold">
                    <i class="fas fa-edit mr-2"></i>Edit User
                </a>
                <a href="{{ route('admin.users.index') }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">User Profile</h2>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $user->last_login_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-2 h-2 mr-1 rounded-full {{ $user->last_login_at ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                {{ $user->last_login_at ? 'Active' : 'Inactive' }}
                            </span>
                            <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="auth_token" value="{{ request()->get('auth_token', '') }}">
                                <button type="submit" 
                                        class="px-3 py-1 rounded-full text-xs font-semibold transition-colors
                                        {{ $user->last_login_at ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                    {{ $user->last_login_at ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="auth_token" value="{{ request()->get('auth_token', '') }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" value="{{ $user->name }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="{{ $user->email }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="{{ $user->phone }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent"
                                       placeholder="+63 912 345 6789">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <input type="text" name="address" value="{{ $user->address }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent"
                                       placeholder="123 Main St, City, Country">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">User Role</label>
                                <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>👤 User</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>👑 Admin</option>
                                    <option value="order_staff" {{ $user->role == 'order_staff' ? 'selected' : '' }}>🧾 Order Staff</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                                    <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>🔴 Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Last updated {{ $user->updated_at->diffForHumans() }}
                            </div>
                            <div class="flex space-x-3">
                                <button type="submit" 
                                        class="px-6 py-3 bg-maroon-700 text-white rounded-lg hover:bg-maroon-800 transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Update User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-maroon-600 mr-2"></i>
                        Activity Statistics
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-maroon-700">{{ $user->orders_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total Orders</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $user->completed_orders_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Completed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ $user->cancelled_orders_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Cancelled</div>
                        </div>
                    </div>
                    
                    @if($user->total_spent > 0)
                        <div class="mt-6 pt-6 border-t">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Total Spent</span>
                                <span class="text-2xl font-bold text-green-600">₱{{ number_format($user->total_spent, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Avatar -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-circle text-maroon-600 mr-2"></i>
                    User Avatar
                </h3>
                <div class="text-center">
                    <div class="w-32 h-32 bg-maroon-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-maroon-700 font-bold text-4xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-xs mt-1">ID: #{{ $user->id }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt text-maroon-600 mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.users.edit', $user->id) }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}" 
                       class="w-full flex items-center justify-center px-4 py-3 bg-maroon-700 text-white rounded-lg hover:bg-maroon-800 transition-colors font-medium">
                        <i class="fas fa-edit mr-2"></i>Edit User
                    </a>
                    <button onclick="resetPassword()" 
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <i class="fas fa-key mr-2"></i>Reset Password
                    </button>
                    @if($user->id != auth()->guard('admin')->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="auth_token" value="{{ request()->get('auth_token', '') }}">
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                                <i class="fas fa-trash mr-2"></i>Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-maroon-600 mr-2"></i>
                        Account Information
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">User ID</span>
                        <span class="text-sm font-medium text-gray-900">#{{ $user->id }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Member Since</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Account Age</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->created_at->diffInDays() }} days</span>
                    </div>
                    @if($user->last_login_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Last Login</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->last_login_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Email Verified</span>
                        <span class="text-sm font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                            {{ $user->email_verified_at ? '✓ Verified' : '✗ Not Verified' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetPassword() {
    if (confirm('Are you sure you want to reset this user\'s password? A new password will be sent to their email.')) {
        // Implement password reset functionality
        alert('Password reset functionality coming soon!');
    }
}
</script>

<style>
.bg-maroon-700 { background-color: #800000; }
.bg-maroon-800 { background-color: #660000; }
.bg-maroon-900 { background-color: #4d0000; }
.bg-maroon-100 { background-color: #ffebeb; }
.text-maroon-700 { color: #800000; }
.text-maroon-800 { color: #660000; }
.text-maroon-900 { color: #4d0000; }
.text-maroon-600 { color: #990000; }
.text-maroon-100 { color: #ffebeb; }
.border-maroon-500 { border-color: #800000; }
.ring-maroon-500 { --tw-ring-color: #800000; }
.hover\:bg-maroon-800:hover { background-color: #660000; }
.hover\:bg-maroon-50:hover { background-color: #ffebeb; }
.hover\:text-maroon-900:hover { color: #4d0000; }
</style>
@endsection
