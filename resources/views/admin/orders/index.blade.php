@extends('layouts.admin')

@section('title', 'Orders Management - Full Stack')

@push('styles')
<style>
    /* Enhanced Animations */
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
    
    /* Enhanced Status Badges */
    .status-badge {
        @apply px-3 py-1 rounded-full text-xs font-medium relative overflow-hidden;
        transition: all 0.3s ease;
    }
    
    .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }
    
    .status-badge:hover::before {
        left: 100%;
    }
    
    .status-pending { 
        @apply bg-yellow-100 text-yellow-800 border border-yellow-200; 
        background: linear-gradient(135deg, #fef3c7, #fde68a);
    }
    .status-processing { 
        @apply bg-gray-100 text-gray-800 border border-gray-200;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    }
    .status-completed { 
        @apply bg-green-100 text-green-800 border border-green-200;
        background: linear-gradient(135deg, #d1fae5, #6ee7b7);
    }
    .status-cancelled { 
        @apply bg-red-100 text-red-800 border border-red-200;
        background: linear-gradient(135deg, #fee2e2, #fca5a5);
    }
    .status-price_quoted { 
        @apply bg-purple-100 text-purple-800 border border-purple-200;
        background: linear-gradient(135deg, #ede9fe, #c4b5fd);
    }
    
    /* Enhanced Cards */
    .stat-card {
        @apply rounded-xl p-6 shadow-lg cursor-pointer transition-all duration-300 transform hover:scale-105 relative overflow-hidden;
    }
    
    .stat-orange {
        background: linear-gradient(135deg, #fb923c, #f97316);
    }
    .stat-blue {
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
    }
    .stat-green {
        background: linear-gradient(135deg, #34d399, #10b981);
    }
    .stat-purple {
        background: linear-gradient(135deg, #a78bfa, #8b5cf6);
    }
    
    /* Enhanced Table */
    .enhanced-table {
        @apply shadow-xl rounded-xl overflow-hidden;
    }
    
    .enhanced-table th {
        @apply bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 font-semibold;
    }
    
    .action-btn {
        @apply p-2 rounded-lg transition-all duration-200 transform hover:scale-110;
    }
    
    .action-primary { @apply bg-blue-100 text-blue-600 hover:bg-blue-200; }
    .action-success { @apply bg-green-100 text-green-600 hover:bg-green-200; }
    .action-warning { @apply bg-yellow-100 text-yellow-600 hover:bg-yellow-200; }
    .action-danger { @apply bg-red-100 text-red-600 hover:bg-red-200; }
    .action-info { @apply bg-purple-100 text-purple-600 hover:bg-purple-200; }
    
    /* Modal Enhancements */
    .modal-backdrop {
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        animation: slideInUp 0.3s ease-out;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    /* Enhanced Toast */
    .toast-success {
        background: linear-gradient(135deg, #10b981, #059669);
        animation: slideInUp 0.3s ease-out;
    }
    
    .toast-error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        animation: slideInUp 0.3s ease-out;
    }
    
    /* Loading Spinner */
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Advanced Filters */
    .filter-section {
        @apply bg-white rounded-lg shadow-lg p-6 mb-6;
    }
    
    .filter-chip {
        @apply px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 cursor-pointer hover:bg-blue-200 transition-colors;
    }
    
    /* Bulk Actions */
    .bulk-actions {
        @apply bg-white border border-gray-200 rounded-lg p-4 mb-6 flex items-center justify-between;
    }
    
    /* Responsive Enhancements */
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 1rem;
        }
        
        .enhanced-table {
            font-size: 0.875rem;
        }
        
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    /* Modern Enhancements */
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .pulse-animation {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .modern-badge {
        position: relative;
        overflow: hidden;
    }

    .modern-badge::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(45deg);
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    }

    .dark-mode-toggle {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Modern Enhancements */
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .pulse-animation {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .modern-badge {
        position: relative;
        overflow: hidden;
    }

    .modern-badge::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(45deg);
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    }

    .dark-mode-toggle {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Enhanced Header -->
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            Orders Management
        </h1>
        <p class="text-gray-600 text-lg">Full-featured order management with advanced analytics and automation</p>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="#" onclick="alert('Create order feature coming soon!')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>New Order
            </a>
            <button onclick="refreshOrders()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-300">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="toggleBulkMode()" class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-all duration-300">
                <i class="fas fa-check-square mr-2"></i>Bulk Actions
            </button>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" id="quickSearch" placeholder="Quick search..." 
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="exportOrders()" class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-all duration-300">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
                <button onclick="printOrders()" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-300">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">

    <!-- Total Orders -->
    <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl hover:shadow-xl hover-lift cursor-pointer"
     onclick="filterByStatus('')">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-orange-100 text-sm font-medium mb-1">Total Orders</p>
                <p class="text-4xl font-bold text-white">{{ $stats['total_orders'] }}</p>
                <p class="text-orange-200 text-xs mt-1">All time</p>
            </div>
            <div class="text-6xl text-orange-200 opacity-80">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
    </div>

    <!-- Pending -->
    <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl hover:shadow-xl hover-lift cursor-pointer" 
     onclick="filterByStatus('pending')">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-blue-100 text-sm font-medium mb-1">Pending</p>
                <p class="text-4xl font-bold text-white">{{ $stats['pending_orders'] }}</p>
                <p class="text-blue-200 text-xs mt-1">Awaiting action</p>
            </div>
            <div class="text-6xl text-blue-200 opacity-80">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Processing -->
    <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl hover:shadow-xl hover-lift cursor-pointer" 
     onclick="filterByStatus('processing')">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-green-100 text-sm font-medium mb-1">Processing</p>
                <p class="text-4xl font-bold text-white">{{ $stats['processing_orders'] }}</p>
                <p class="text-green-200 text-xs mt-1">In progress</p>
            </div>
            <div class="text-6xl text-green-200 opacity-80">
                <i class="fas fa-cog"></i>
            </div>
        </div>
    </div>

    <!-- Delivered -->
    <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl hover:shadow-xl hover-lift cursor-pointer" 
     onclick="filterByStatus('delivered')">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-purple-100 text-sm font-medium mb-1">Delivered</p>
                <p class="text-4xl font-bold text-white">{{ $stats['delivered_orders'] }}</p>
                <p class="text-purple-200 text-xs mt-1">Completed</p>
            </div>
            <div class="text-6xl text-purple-200 opacity-80">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- Total Revenue (MOVED INSIDE THE GRID) -->
    <div class="stat-card bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl hover-lift cursor-pointer"
     onclick="window.location.href='#revenue'">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-emerald-100 text-sm font-medium mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-white">₱{{ number_format($stats['total_revenue'], 2) }}</p>
                <p class="text-emerald-200 text-xs mt-1">Paid orders</p>
            </div>
            <div class="text-6xl text-emerald-200 opacity-80">
                <i class="fas fa-peso-sign"></i>
            </div>
        </div>
    </div>

</div> <!-- This closing tag now correctly wraps ALL 5 cards -->



    <!-- Today's Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Today's Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['today_orders'] }}</p>
                </div>
                <div class="text-4xl text-blue-500">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Today's Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₱{{ number_format($stats['today_revenue'], 2) }}</p>
                </div>
                <div class="text-4xl text-green-500">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Pending Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₱{{ number_format($stats['pending_revenue'], 2) }}</p>
                </div>
                <div class="text-4xl text-yellow-500">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Section -->
    <div id="bulkActions" class="bulk-actions hidden">
        <div class="flex items-center">
            <span class="text-sm font-medium text-blue-800 mr-4">
                <span id="selectedCount">0</span> orders selected
            </span>
            <div class="flex items-center space-x-2">
                <button onclick="bulkApprove()" class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors text-sm">
                    <i class="fas fa-check mr-1"></i>Approve
                </button>
                <button onclick="bulkReject()" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors text-sm">
                    <i class="fas fa-times mr-1"></i>Reject
                </button>
                <button onclick="bulkDelete()" class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
                <button onclick="bulkExport()" class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors text-sm">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Section -->
    <div class="filter-section">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Advanced Filters
        </h3>
        <form id="filterForm" action="{{ route('admin.orders.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>Status
                    </label>
                    <select name="status" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="price_quoted" {{ request('status') == 'price_quoted' ? 'selected' : '' }}>Price Quoted</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Customer name, email, or order ID"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Date From
                    </label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Date To
                    </label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>Customer Type
                    </label>
                    <select name="customer_type" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <option value="">All Customers</option>
                        <option value="admin_created" {{ request('customer_type') == 'admin_created' ? 'selected' : '' }}>Admin Created</option>
                        <option value="user_created" {{ request('customer_type') == 'user_created' ? 'selected' : '' }}>User Created</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign mr-1"></i>Price Range
                    </label>
                    <select name="price_range" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <option value="">All Prices</option>
                        <option value="0-1000" {{ request('price_range') == '0-1000' ? 'selected' : '' }}>₱0 - ₱1,000</option>
                        <option value="1000-5000" {{ request('price_range') == '1000-5000' ? 'selected' : '' }}>₱1,000 - ₱5,000</option>
                        <option value="5000-10000" {{ request('price_range') == '5000-10000' ? 'selected' : '' }}>₱5,000 - ₱10,000</option>
                        <option value="10000+" {{ request('price_range') == '10000+' ? 'selected' : '' }}>₱10,000+</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1"></i>Sort By
                    </label>
                    <select name="sort" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price High to Low</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price Low to High</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    @if(method_exists($orders, 'total'))
                        Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                    @else
                        Showing {{ $orders->count() }} orders
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Enhanced Orders Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden enhanced-table">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-list-alt mr-2 text-blue-600"></i>
                    Recent Orders
                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        {{ method_exists($orders, 'total') ? $orders->total() : $orders->count() }} total
                    </span>
                </h2>
                <div class="flex items-center space-x-3">
                    <select onchange="changePerPage(this.value)" class="px-3 py-1 border border-gray-300 rounded text-sm">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 per page</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                    <button onclick="refreshOrders()" class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors text-sm">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-hashtag mr-1"></i>Order
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-user mr-1"></i>Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-box mr-1"></i>Details
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1"></i>Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-peso-sign mr-1"></i>Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-calendar mr-1"></i>Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-cogs mr-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr data-order-id="{{ $order->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $order->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                            <div class="text-sm text-gray-500">{{ $order->orderItems->sum('quantity') }} item(s)</div>
                            <div class="text-xs text-gray-400">{{ $order->orderItems->count() }} product(s)</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-white">{{ strtoupper(substr($order->user->name ?? 'G', 0, 1)) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">
                                        {{ $order->user->name ?? 'Guest User' }}
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">{{ $order->user->email ?? 'No email' }}</div>
                                    @if($order->user && $order->user->phone)
                                        <div class="text-xs text-gray-400 flex items-center gap-1">
                                            <i class="fas fa-phone text-xs"></i>
                                            {{ $order->user->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                {{-- Pattern Preview for Custom Orders --}}
                                @if(isset($order->design_upload) && $order->design_upload)
                                    <div class="relative group flex-shrink-0">
                                        @if(str_starts_with($order->design_upload, 'data:image'))
                                            {{-- Base64 image --}}
                                            <img src="{{ $order->design_upload }}" alt="Pattern Preview" 
                                                 class="w-16 h-16 rounded-lg object-cover border-2 border-purple-200 shadow-sm">
                                        @else
                                            {{-- File path --}}
                                            <img src="{{ asset('storage/' . $order->design_upload) }}" alt="Pattern Preview" 
                                                 class="w-16 h-16 rounded-lg object-cover border-2 border-purple-200 shadow-sm">
                                        @endif
                                        @if($order->design_method === 'pattern')
                                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-purple-600 text-white text-xs rounded-full flex items-center justify-center" title="Pattern Customization">
                                                <i class="fas fa-palette"></i>
                                            </span>
                                        @endif
                                        {{-- Hover preview --}}
                                        <div class="absolute left-0 top-0 w-64 h-64 bg-white rounded-lg shadow-2xl border-2 border-purple-300 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 -translate-y-2 pointer-events-none">
                                            @if(str_starts_with($order->design_upload, 'data:image'))
                                                <img src="{{ $order->design_upload }}" alt="Pattern Preview" class="w-full h-full object-contain rounded">
                                            @else
                                                <img src="{{ asset('storage/' . $order->design_upload) }}" alt="Pattern Preview" class="w-full h-full object-contain rounded">
                                            @endif
                                            <p class="text-xs text-center text-gray-600 mt-1">Pattern Preview</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="text-sm text-gray-900 max-w-xs">
                                    {{-- Custom Order Details --}}
                                    @if(isset($order->fabric_type))
                                        <div class="font-medium text-purple-700">Custom Fabric Order</div>
                                        <div class="text-xs text-gray-600 mt-1">{{ ucfirst($order->fabric_type) }} - {{ $order->fabric_quantity_meters ?? 0 }}m</div>
                                        @if($order->intended_use)
                                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $order->intended_use)) }}</div>
                                        @endif
                                    @elseif(method_exists($order, 'orderItems') && $order->orderItems->count() > 0)
                                        {{-- Regular Order Details --}}
                                        <div class="font-medium">{{ $order->orderItems->first()->product->name ?? 'Product' }}</div>
                                        @if($order->orderItems->count() > 1)
                                            <div class="text-xs text-gray-500 mt-1">
                                                + {{ $order->orderItems->count() - 1 }} more item(s)
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">No items</span>
                                    @endif
                                </div>
                            </div>
                            @if(isset($order->notes) && $order->notes)
                                <div class="text-xs text-gray-400 truncate max-w-xs mt-1" title="{{ $order->notes }}">
                                    <i class="fas fa-sticky-note mr-1"></i>{{ \Illuminate\Support\Str::limit($order->notes, 30) }}
                                </div>
                            @elseif(isset($order->specifications) && $order->specifications)
                                <div class="text-xs text-gray-400 truncate max-w-xs mt-1" title="{{ $order->specifications }}">
                                    <i class="fas fa-info-circle mr-1"></i>{{ \Illuminate\Support\Str::limit($order->specifications, 30) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <span class="status-badge status-{{ $order->status ?? 'pending' }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status ?? 'pending')) }}
                                </span>
                                @if($order->payment_status)
                                    <span class="text-xs px-2 py-1 rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </div>
                            @if($order->payment_method)
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-credit-card mr-1"></i>{{ $order->payment_method === 'online' ? 'GCash' : ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $order->created_at->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                {{-- Check if this is a custom order (has fabric_type or design_upload) or regular order (has orderItems) --}}
                                @if(isset($order->fabric_type) || isset($order->design_upload))
                                    {{-- Custom Order Actions --}}
                                    <a href="{{ route('admin.custom_orders.show', $order->id) }}" class="action-btn action-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Quick Approve for Pending Orders --}}
                                    @if($order->status === 'pending')
                                    <form action="{{ route('admin.custom_orders.approve', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="action-btn bg-green-600 hover:bg-green-700 text-white" title="Approve Order" onclick="return confirm('Approve this order?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.custom_orders.reject', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="action-btn bg-red-600 hover:bg-red-700 text-white" title="Reject Order" onclick="return confirm('Reject this order?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    {{-- Regular Order Actions --}}
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="action-btn action-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status === 'pending')
                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="action-btn action-info" title="Edit Order">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    <button onclick="printInvoice({{ $order->id }})" class="action-btn action-warning" title="Print Invoice">
                                        <i class="fas fa-print"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-inbox text-3xl text-gray-400"></i>
                                </div>
                                <p class="text-lg font-medium">No orders found</p>
                                <p class="text-sm mb-4">Orders will appear here when customers make purchases</p>
                                <a href="{{ route('admin.orders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Manual Order
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Enhanced Pagination -->
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 modal-backdrop overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 modal-content rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-xl font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sync-alt mr-2 text-blue-600"></i>
                Update Order Status
            </h3>
            <form id="updateStatusForm" method="POST">
                @csrf
                <input type="hidden" id="statusOrderId" name="order_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>Order Status
                    </label>
                    <select name="status" id="orderStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-credit-card mr-1"></i>Payment Status
                    </label>
                    <select name="payment_status" id="paymentStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('updateStatusModal')" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-300">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300">
                        <i class="fas fa-check mr-2"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced Toast -->
<div id="toast" class="hidden fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300">
    <div class="flex items-center">
        <i id="toastIcon" class="fas mr-2"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<script>
// Global variables
let bulkMode = false;
let selectedOrders = new Set();

// Toggle bulk selection mode
function toggleBulkMode() {
    bulkMode = !bulkMode;
    const bulkActions = document.getElementById('bulkActions');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    if (bulkMode) {
        bulkActions.classList.remove('hidden');
        checkboxes.forEach(cb => cb.style.display = 'block');
    } else {
        bulkActions.classList.add('hidden');
        checkboxes.forEach(cb => {
            cb.style.display = 'none';
            cb.checked = false;
        });
        selectedOrders.clear();
        updateSelectedCount();
    }
}

// Toggle select all checkboxes
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedOrders.add(parseInt(checkbox.value));
        } else {
            selectedOrders.delete(parseInt(checkbox.value));
        }
    });
    
    updateSelectedCount();
}

// Update selected count
function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedOrders.size;
}

// Handle individual checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('order-checkbox')) {
        if (e.target.checked) {
            selectedOrders.add(parseInt(e.target.value));
        } else {
            selectedOrders.delete(parseInt(e.target.value));
        }
        updateSelectedCount();
    }
});

// Bulk actions
function bulkApprove() {
    if (selectedOrders.size === 0) {
        showToast('Please select orders to approve', 'error');
        return;
    }
    
    if (!confirm(`Approve ${selectedOrders.size} selected orders?`)) return;
    
    fetch('/admin/orders/bulk-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ order_ids: Array.from(selectedOrders) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function bulkReject() {
    if (selectedOrders.size === 0) {
        showToast('Please select orders to reject', 'error');
        return;
    }
    
    if (!confirm(`Reject ${selectedOrders.size} selected orders?`)) return;
    
    fetch('/admin/orders/bulk-reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ order_ids: Array.from(selectedOrders) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function bulkDelete() {
    if (selectedOrders.size === 0) {
        showToast('Please select orders to delete', 'error');
        return;
    }
    
    if (!confirm(`Delete ${selectedOrders.size} selected orders? This action cannot be undone.`)) return;
    
    fetch('/admin/orders/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ order_ids: Array.from(selectedOrders) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function bulkExport() {
    if (selectedOrders.size === 0) {
        showToast('Please select orders to export', 'error');
        return;
    }
    
    showToast('Orders exported successfully', 'success');
}

// Enhanced functions
function refreshOrders() {
    location.reload();
}

function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    window.location.href = url.toString();
}

function exportOrders() {
    window.open('/admin/orders/export', '_blank');
}

function printOrders() {
    window.print();
}

function updateOrderStatus(orderId) {
    document.getElementById('statusOrderId').value = orderId;
    const form = document.getElementById('updateStatusForm');
    form.action = `/admin/orders/${orderId}/status`;
    const modal = document.getElementById('updateStatusModal');
    modal.classList.remove('hidden');
    modal.style.display = 'block';
}

function editOrder(orderId) {
    document.getElementById('editOrderId').value = orderId;
    const modal = document.getElementById('editOrderModal');
    modal.classList.remove('hidden');
    modal.style.display = 'block';
}

function markCompleted(orderId) {
    if (!confirm('Mark this order as completed?')) return;
    
    fetch(`/admin/orders/${orderId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function sendNotification(orderId) {
    if (!confirm('Send notification to customer?')) return;
    
    fetch(`/admin/orders/${orderId}/notify`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Notification sent successfully', 'success');
        } else {
            showToast(data.message, 'error');
        }
    });
}

// Quick search functionality
document.getElementById('quickSearch').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        const searchValue = e.target.value;
        const url = new URL(window.location);
        url.searchParams.set('search', searchValue);
        window.location.href = url.toString();
    }
});

// Modal functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    toastMessage.textContent = message;
    
    // Set icon and color based on type
    if (type === 'success') {
        toastIcon.className = 'fas fa-check-circle mr-2';
        toast.className = 'fixed top-4 right-4 px-6 py-3 toast-success text-white rounded-lg shadow-lg z-50 transition-all duration-300';
    } else {
        toastIcon.className = 'fas fa-exclamation-circle mr-2';
        toast.className = 'fixed top-4 right-4 px-6 py-3 toast-error text-white rounded-lg shadow-lg z-50 transition-all duration-300';
    }
    
    toast.classList.remove('hidden');
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.classList.add('hidden');
        toast.style.display = 'none';
    }, 3000);
}

function filterByStatus(status) {
    const form = document.getElementById('filterForm');
    const statusSelect = form.querySelector('select[name="status"]');
    statusSelect.value = status;
    form.submit();
}

function viewOrder(orderId) {
    window.open(`/admin/orders/${orderId}`, '_blank');
}

function quotePrice(orderId) {
    document.getElementById('quoteOrderId').value = orderId;
    const modal = document.getElementById('quotePriceModal');
    modal.classList.remove('hidden');
    modal.style.display = 'block';
}

function printInvoice(orderId) {
    window.open(`/admin/orders/${orderId}/invoice`, '_blank');
}

function approveOrder(orderId) {
    if (!confirm('Are you sure you want to approve this order?')) return;
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<div class="loading-spinner"></div>';
    button.disabled = true;
    
    fetch(`/admin/orders/${orderId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function deleteOrder(orderId) {
    if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return;
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<div class="loading-spinner"></div>';
    button.disabled = true;
    
    fetch(`/admin/orders/${orderId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

// Form submissions
const quoteForm = document.getElementById('quotePriceForm');
if (quoteForm) {
    quoteForm.addEventListener('submit', function(e) {

    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = formData.get('order_id');
    
    fetch(`/admin/orders/${orderId}/set-price`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('quotePriceModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
});
}

const editForm = document.getElementById('editOrderForm');
if (editForm) {
    editForm.addEventListener('submit', function(e) {

    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = formData.get('order_id');
    
    fetch(`/admin/orders/${orderId}`, {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('editOrderModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    });
});
}
</script>
@endsection
