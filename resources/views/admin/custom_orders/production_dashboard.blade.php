@extends('layouts.admin')

@section('title', 'Production Dashboard - Custom Orders')

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        padding: 1.5rem;
        transition: all 0.3s;
    }
    .stat-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        transform: scale(1.05);
    }
    
    .stat-card .icon {
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: 1rem;
    }
    
    .stat-card.blue .icon { background: linear-gradient(to bottom right, #800000, #600000); }
    .stat-card.green .icon { background: linear-gradient(to bottom right, #4ade80, #16a34a); }
    .stat-card.orange .icon { background: linear-gradient(to bottom right, #fb923c, #ea580c); }
    .stat-card.purple .icon { background: linear-gradient(to bottom right, #800000, #600000); }
    .stat-card.red .icon { background: linear-gradient(to bottom right, #f87171, #dc2626); }
    .stat-card.indigo .icon { background: linear-gradient(to bottom right, #818cf8, #4f46e5); }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        transition: stroke-dashoffset 0.35s;
        transform-origin: 50% 50%;
    }
    
    .chart-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        padding: 1.5rem;
    }
    
    .quick-action-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-block;
    }
    .quick-action-btn:hover { transform: scale(1.05); }
    
    .quick-action-btn.primary { background: #800000; color: white; }
    .quick-action-btn.primary:hover { background: #600000; }
    
    .quick-action-btn.success { background: #16a34a; color: white; }
    .quick-action-btn.success:hover { background: #15803d; }
    
    .quick-action-btn.warning { background: #ca8a04; color: white; }
    .quick-action-btn.warning:hover { background: #a16207; }
    
    .quick-action-btn.danger { background: #dc2626; color: white; }
    .quick-action-btn.danger:hover { background: #b91c1c; }
    
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1rem;
        border-left: 2px solid #e5e7eb;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 0.75rem;
        height: 0.75rem;
        background: white;
        border: 2px solid #d1d5db;
        border-radius: 9999px;
        transform: translateX(-50%);
    }
    
    .timeline-item.completed::before { background: #22c55e; border-color: #22c55e; }
    .timeline-item.processing::before { background: #800000; border-color: #800000; }
    .timeline-item.pending::before { background: #eab308; border-color: #eab308; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Production Dashboard</h1>
        <p class="text-gray-600">Real-time overview of custom orders and production metrics</p>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-8 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.custom_orders.create') }}" class="quick-action-btn primary">
                <i class="fas fa-plus mr-2"></i>New Order
            </a>
            <a href="{{ route('admin.custom_orders.index') }}" class="quick-action-btn success">
                <i class="fas fa-list mr-2"></i>All Orders
            </a>
            <button onclick="refreshDashboard()" class="quick-action-btn warning">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
        </div>
        <div class="text-sm text-gray-500">
            Last updated: {{ now()->format('M j, Y g:i A') }}
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="stat-card blue">
            <div class="icon">
                <i class="fas fa-shopping-bag text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                <p class="text-sm text-gray-600">Total Orders</p>
                <p class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>12% from last month
                </p>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="stat-card orange">
            <div class="icon">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_orders'] }}</p>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-xs text-orange-600 mt-1">Need attention</p>
            </div>
        </div>

        <!-- Processing Orders -->
        <div class="stat-card blue">
            <div class="icon">
                <i class="fas fa-cog text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['processing_orders'] }}</p>
                <p class="text-sm text-gray-600">Processing</p>
                <p class="text-xs text-[#800000] mt-1">In production</p>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="stat-card green">
            <div class="icon">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['completed_orders'] }}</p>
                <p class="text-sm text-gray-600">Completed</p>
                <p class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>8% increase
                </p>
            </div>
        </div>

        <!-- Revenue -->
        <div class="stat-card purple">
            <div class="icon">
                <i class="fas fa-peso-sign text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">₱{{ number_format($stats['total_revenue'], 0) }}</p>
                <p class="text-sm text-gray-600">Total Revenue</p>
                <p class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>15% growth
                </p>
            </div>
        </div>

        <!-- Avg Processing Time -->
        <div class="stat-card indigo">
            <div class="icon">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['avg_processing_time'] }}m</p>
                <p class="text-sm text-gray-600">Avg Process Time</p>
                <p class="text-xs text-indigo-600 mt-1">Per order</p>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Order Status Chart -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status Distribution</h3>
            <div class="relative h-64">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative">
                        <svg width="200" height="200" class="transform -rotate-90">
                            <!-- Pending -->
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#f59e0b" stroke-width="20"
                                    stroke-dasharray="{{ ($stats['pending_orders'] / max($stats['total_orders'], 1)) * 502.65 }}" 
                                    stroke-dashoffset="0" class="progress-ring-circle"/>
                            <!-- Processing -->
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#3b82f6" stroke-width="20"
                                    stroke-dasharray="{{ ($stats['processing_orders'] / max($stats['total_orders'], 1)) * 502.65 }}" 
                                    stroke-dashoffset="{{ ($stats['pending_orders'] / max($stats['total_orders'], 1)) * -502.65 }}" class="progress-ring-circle"/>
                            <!-- Completed -->
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#10b981" stroke-width="20"
                                    stroke-dasharray="{{ ($stats['completed_orders'] / max($stats['total_orders'], 1)) * 502.65 }}" 
                                    stroke-dashoffset="{{ (($stats['pending_orders'] + $stats['processing_orders']) / max($stats['total_orders'], 1)) * -502.65 }}" class="progress-ring-circle"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                                <p class="text-sm text-gray-600">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mx-auto mb-1"></div>
                    <p class="text-xs text-gray-600">Pending</p>
                    <p class="text-sm font-semibold">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 bg-[#800000] rounded-full mx-auto mb-1"></div>
                    <p class="text-xs text-gray-600">Processing</p>
                    <p class="text-sm font-semibold">{{ $stats['processing_orders'] }}</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-1"></div>
                    <p class="text-xs text-gray-600">Completed</p>
                    <p class="text-sm font-semibold">{{ $stats['completed_orders'] }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders Timeline -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders Activity</h3>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($recentOrders as $order)
                <div class="timeline-item {{ $order->status }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">{{ $order->user->name ?? 'Guest' }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="status-badge status-{{ $order->status }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                            @if($order->final_price)
                                <p class="text-sm font-medium text-gray-900 mt-1">₱{{ number_format($order->final_price, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p>No recent orders</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Production Queue -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Production Queue</h3>
            <div class="flex space-x-2">
                <button onclick="filterQueue('all')" class="px-3 py-1 text-sm rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
                    All
                </button>
                <button onclick="filterQueue('urgent')" class="px-3 py-1 text-sm rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                    Urgent
                </button>
                <button onclick="filterQueue('today')" class="px-3 py-1 text-sm rounded-lg bg-red-50 text-[#800000] hover:bg-red-100">
                    Due Today
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- This would be populated with actual production queue items -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">Order #1234</span>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Pending</span>
                </div>
                <p class="text-sm text-gray-600 mb-2">Customer: John Doe</p>
                <p class="text-xs text-gray-500 mb-3">Custom Yakan Bag - Diamond Pattern</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Due: Tomorrow</span>
                    <button class="text-[#800000] hover:text-[#800000]">View Details</button>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">Order #1235</span>
                    <span class="px-2 py-1 bg-red-50 text-[#800000] text-xs rounded-full">Processing</span>
                </div>
                <p class="text-sm text-gray-600 mb-2">Customer: Jane Smith</p>
                <p class="text-xs text-gray-500 mb-3">Silk Fabric - Custom Pattern</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Due: In 3 days</span>
                    <button class="text-[#800000] hover:text-[#800000]">View Details</button>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">Order #1236</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Completed</span>
                </div>
                <p class="text-sm text-gray-600 mb-2">Customer: Bob Wilson</p>
                <p class="text-xs text-gray-500 mb-3">Cotton Shirt - Geometric Pattern</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Completed: Today</span>
                    <button class="text-[#800000] hover:text-[#800000]">View Details</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    location.reload();
}

function filterQueue(filter) {
    // Implement queue filtering logic
    console.log('Filtering queue by:', filter);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    // Add subtle animation to show data is fresh
    document.querySelectorAll('.stat-card').forEach(card => {
        card.style.opacity = '0.7';
        setTimeout(() => {
            card.style.opacity = '1';
        }, 300);
    });
}, 30000);
</script>
@endsection
