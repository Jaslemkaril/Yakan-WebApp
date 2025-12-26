@extends('layouts.admin')

@section('title', 'Orders Management')

@push('styles')
<style>
    /* Animations */
    @keyframes slideInUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    /* Status Badges */
    .status-badge {
        @apply px-3 py-1 rounded-full text-xs font-medium;
        transition: all 0.3s ease;
    }
    
    .status-pending { 
        @apply bg-yellow-100 text-yellow-800 border border-yellow-200;
    }
    .status-processing { 
        @apply bg-gray-100 text-gray-800 border border-gray-200;
    }
    .status-completed { 
        @apply bg-green-100 text-green-800 border border-green-200;
    }
    .status-cancelled { 
        @apply bg-red-100 text-red-800 border border-red-200;
    }
    .status-shipped { 
        @apply bg-blue-100 text-blue-800 border border-blue-200;
    }
    .status-delivered { 
        @apply bg-green-100 text-green-800 border border-green-200;
    }
    
    /* Cards */
    .stat-card {
        @apply rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 bg-white;
        border-left: 4px solid #800000;
    }
    
    .stat-card-icon {
        @apply w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0;
        background-color: #800000;
    }
    
    .stat-card-icon svg {
        @apply w-6 h-6 text-white;
    }
    
    /* Table */
    .enhanced-table {
        @apply shadow-lg rounded-xl overflow-hidden;
    }
    
    .enhanced-table th {
        @apply bg-gray-100 text-gray-700 font-semibold text-sm;
    }
    
    .enhanced-table td {
        @apply py-3 px-4;
    }
    
    .enhanced-table tbody tr {
        @apply border-b border-gray-200 hover:bg-gray-50 transition-colors;
    }
    
    /* Action Buttons */
    .action-btn {
        @apply p-2 rounded-lg transition-all duration-200 transform hover:scale-110;
    }
    
    .action-primary { @apply bg-blue-100 text-blue-600 hover:bg-blue-200; }
    .action-success { @apply bg-green-100 text-green-600 hover:bg-green-200; }
    .action-warning { @apply bg-yellow-100 text-yellow-600 hover:bg-yellow-200; }
    .action-danger { @apply bg-red-100 text-red-600 hover:bg-red-200; }
    
    /* Filter Section */
    .filter-section {
        @apply bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 1rem;
        }
        
        .enhanced-table {
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Orders Management</h1>
                    <p class="text-gray-600">Manage and track all customer orders</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="#" onclick="alert('Create order feature coming soon!')" class="px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-all duration-300 font-semibold flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Order
                    </a>
                    <button onclick="location.reload()" class="px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-all duration-300 shadow border border-gray-200 font-semibold flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A9.001 9.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <!-- Total Orders -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Total Orders</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">All time</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Pending</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_orders'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Awaiting action</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Processing Orders -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Processing</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['processing_orders'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">In progress</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Delivered Orders -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Delivered</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['delivered_orders'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Completed</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Total Revenue</p>
                        <p class="text-3xl font-bold text-[#800000]">₱{{ number_format($stats['total_revenue'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Paid orders</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Today's Orders</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['today_orders'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">New orders today</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Today's Revenue</p>
                        <p class="text-3xl font-bold text-[#800000]">₱{{ number_format($stats['today_revenue'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Revenue today</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Pending Revenue</p>
                        <p class="text-3xl font-bold text-gray-900">₱{{ number_format($stats['pending_revenue'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Awaiting payment</p>
                    </div>
                    <div class="stat-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="filter-section">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Advanced Filters
                </h3>
            </div>
            <form id="filterForm" action="{{ route('admin.regular.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] transition-all duration-300">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Customer, email, order ID..."
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] transition-all duration-300">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] transition-all duration-300">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] transition-all duration-300">
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <button type="submit" class="px-6 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-all duration-300 font-semibold shadow-md flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.regular.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-300 font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear
                        </a>
                    </div>
                    <div class="text-sm text-gray-600 font-medium">
                        @if(method_exists($orders, 'total'))
                            Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                        @else
                            Showing {{ $orders->count() }} orders
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden enhanced-table">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Recent Orders
                        <span class="ml-2 px-2 py-1 bg-[#fef2f2] text-[#800000] text-xs rounded-full font-semibold">
                            {{ method_exists($orders, 'total') ? $orders->total() : $orders->count() }} total
                        </span>
                    </h2>
                    <select onchange="location.href='{{ route('admin.regular.index') }}?per_page=' + this.value" class="px-3 py-1 border border-gray-300 rounded text-sm">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 per page</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
            </div>

            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">Order ID</th>
                        <th class="text-left">Customer</th>
                        <th class="text-left">Amount</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">Payment</th>
                        <th class="text-left">Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-semibold text-[#800000]">#{{ $order->id }}</td>
                            <td>
                                <div class="font-medium text-gray-900">{{ $order->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $order->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($order->status) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ strtolower($order->payment_status) }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="action-btn action-primary" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="action-btn action-success" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                No orders found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($orders, 'links'))
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif

    </div>
</div>

<script>
    function changePerPage(value) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', value);
        window.location = url.toString();
    }
</script>
@endsection
