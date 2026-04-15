@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
@php
    $activeUsersCount = $users->filter(function ($u) {
        return $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(5));
    })->count();
    $inactiveUsersCount = $users->count() - $activeUsersCount;
@endphp
<div class="space-y-6">
    <!-- User Management Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">User Management</h1>
                <p class="text-maroon-100 text-lg">Manage system users and their permissions</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.users.create') }}" class="bg-white text-[#800000] rounded-lg px-4 py-2 hover:bg-gray-100 transition-colors font-semibold">
                    <i class="fas fa-user-plus mr-2"></i>Add New User
                </a>
                <button onclick="exportUsers()" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Users</p>
                    <p class="text-2xl font-bold text-maroon-700">{{ $users->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-maroon-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-maroon-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Active Users</p>
                    <p class="text-2xl font-bold text-[#800000]">{{ $activeUsersCount }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-[#800000] text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Admin Users</p>
                    <p class="text-2xl font-bold text-maroon-600">{{ $users->where('role', 'admin')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-maroon-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-maroon-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Inactive Users</p>
                    <p class="text-2xl font-bold text-[#800000]">{{ $inactiveUsersCount }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-times text-[#800000] text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-filter text-maroon-600 mr-2"></i>
                Filters & Search
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by name or email..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            <option value="">All Roles</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="order_staff" {{ request('role') == 'order_staff' ? 'selected' : '' }}>Order Staff</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-medium">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium flex items-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-red-50 border border-red-200 text-[#800000] px-6 py-4 rounded-lg flex items-center">
            <i class="fas fa-check-circle text-[#800000] mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Users Table -->
    @if($users->count() > 0)
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->avatar)
                                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover border-2 border-maroon-600">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-maroon-100 flex items-center justify-center">
                                                    <span class="text-maroon-700 font-semibold text-sm">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $user->role == 'admin' ? 'bg-maroon-100 text-maroon-800' : ($user->role == 'order_staff' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                                        <i class="fas {{ $user->role == 'admin' ? 'fa-user-shield' : ($user->role == 'order_staff' ? 'fa-receipt' : 'fa-user') }} mr-1"></i>
                                        {{ $user->role === 'order_staff' ? 'Order Staff' : ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $isActiveNow = $user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(5));
                                        $hasActiveSession = in_array($user->id, $activeUserIds);

                                        if ($isActiveNow || $hasActiveSession) {
                                            $statusText = 'Active now';
                                            $statusColor = 'bg-green-100 text-green-800';
                                            $dotColor = 'bg-green-600';
                                        } elseif ($user->last_seen_at) {
                                            $statusText = 'Active ' . $user->last_seen_at->diffForHumans();
                                            $statusColor = 'bg-blue-100 text-blue-800';
                                            $dotColor = 'bg-blue-600';
                                        } else {
                                            $statusText = 'Inactive';
                                            $statusColor = 'bg-gray-100 text-gray-600';
                                            $dotColor = 'bg-gray-400';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                        <span class="w-2 h-2 mr-1 rounded-full {{ $dotColor }}"></span>
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                    <div class="text-xs">{{ $user->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.users.show', $user->id) }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all" 
                                           title="View Details">
                                            <i class="fas fa-eye mr-1.5"></i>
                                            View
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-[#800000] bg-red-50 hover:bg-red-100 rounded-lg transition-all" 
                                           title="Edit User">
                                            <i class="fas fa-edit mr-1.5"></i>
                                            Edit
                                        </a>
                                        @if($user->id != auth()->guard('admin')->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                @if(request()->has('auth_token'))
                                                    <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') }}">
                                                @endif
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-all" 
                                                        title="Delete User"
                                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash mr-1.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-12 text-center">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Users Found</h3>
            <p class="text-gray-500 mb-6">Get started by adding your first user.</p>
            <a href="{{ route('admin.users.create') }}" class="px-6 py-3 bg-maroon-700 text-white rounded-lg hover:bg-maroon-800 transition-colors font-medium">
                <i class="fas fa-user-plus mr-2"></i>Add First User
            </a>
        </div>
    @endif
</div>

<script>
function exportUsers() {
    // Export functionality - you can implement this as needed
    alert('Export functionality coming soon!');
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
