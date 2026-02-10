@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 shadow-2xl" style="background: linear-gradient(to right, #800000, #600000);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black text-white">Reports & Analytics</h1>
                    <p class="text-maroon-100 mt-2">Comprehensive insights into your business performance</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg focus:outline-none focus:ring-2 focus:ring-white/50">
                            <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last Year</option>
                        </select>
                    </form>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.reports.export.sales', ['period' => $period]) }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-maroon-50 text-maroon-800 font-black rounded-lg shadow-xl hover:shadow-2xl transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Sales
                        </a>
                        <a href="{{ route('admin.reports.export.inventory') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-maroon-50 text-maroon-800 font-black rounded-lg shadow-xl hover:shadow-2xl transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Revenue Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
                        <p class="text-2xl font-black text-gray-900">${{ number_format($salesData['totalRevenue'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000]">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 font-medium">Total Orders</p>
                        <p class="text-2xl font-black text-gray-900">{{ $salesData['totalOrders'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Customers Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000]">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 font-medium">New Customers</p>
                        <p class="text-2xl font-black text-gray-900">{{ $customerData['newCustomers'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 font-medium">Avg Order Value</p>
                        <p class="text-2xl font-black text-gray-900">${{ number_format($salesData['averageOrderValue'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales Trend Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-black text-gray-900 mb-4">Sales Trend</h3>
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>

            <!-- Top Products Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-black text-gray-900 mb-4">Top Selling Products</h3>
                <canvas id="productsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Sections -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales Analytics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                    <h3 class="text-lg font-black text-white">Sales Analytics</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Total Revenue</span>
                        <span class="text-xl font-black text-gray-900">${{ number_format($salesData['totalRevenue'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Total Orders</span>
                        <span class="text-xl font-black text-gray-900">{{ $salesData['totalOrders'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Avg Order Value</span>
                        <span class="text-xl font-black text-gray-900">${{ number_format($salesData['averageOrderValue'], 2) }}</span>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-black text-gray-700 mb-3">Top Products</h4>
                        <div class="space-y-2">
                            @foreach($salesData['topSellingProducts']->take(5) as $product)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 truncate">{{ $product->name }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $product->quantity_sold ?? 0 }} sold</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Analytics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-[#800000] to-[#600000] px-6 py-4">
                    <h3 class="text-lg font-black text-white">Product Analytics</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Total Products</span>
                        <span class="text-xl font-black text-gray-900">{{ $productData['totalProducts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Active Products</span>
                        <span class="text-xl font-black text-gray-900">{{ $productData['activeProducts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Low Stock Items</span>
                        <span class="text-xl font-black text-yellow-600">{{ $productData['lowStockProducts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Out of Stock</span>
                        <span class="text-xl font-black text-red-600">{{ $productData['outOfStockProducts'] }}</span>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-black text-gray-700 mb-3">Top Performers</h4>
                        <div class="space-y-2">
                            @foreach($productData['topProducts']->take(5) as $product)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 truncate">{{ $product->name }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $product->order_items_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Analytics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-[#800000] to-[#600000] px-6 py-4">
                    <h3 class="text-lg font-black text-white">Customer Analytics</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Total Customers</span>
                        <span class="text-xl font-black text-gray-900">{{ $customerData['totalCustomers'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">New Customers</span>
                        <span class="text-xl font-black text-green-600">{{ $customerData['newCustomers'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Active Customers</span>
                        <span class="text-xl font-black text-gray-900">{{ $customerData['activeCustomers'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Growth Rate</span>
                        <span class="text-xl font-black text-[#800000]">{{ number_format($customerData['customerGrowthRate'], 1) }}%</span>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <div class="bg-red-50 rounded-lg p-3">
                            <p class="text-sm text-[#800000]">
                                <strong>{{ $customerData['newCustomers'] }}</strong> new customers joined in the last {{ $period }} days
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Inventory Analytics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                    <h3 class="text-lg font-black text-white">Inventory Analytics</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-black text-gray-900">{{ $inventoryData['totalItems'] }}</div>
                            <div class="text-sm text-gray-600">Total Items</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-black text-gray-900">${{ number_format($inventoryData['totalValue'], 0) }}</div>
                            <div class="text-sm text-gray-600">Total Value</div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Low Stock Alerts</span>
                            <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-black rounded-full">
                                {{ $inventoryData['lowStockItems'] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Out of Stock</span>
                            <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs font-black rounded-full">
                                {{ $inventoryData['outOfStockItems'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Analytics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 px-6 py-4">
                    <h3 class="text-lg font-black text-white">Review Analytics</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-black text-gray-900">{{ $reviewData['totalReviews'] }}</div>
                            <div class="text-sm text-gray-600">Total Reviews</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-black text-gray-900">{{ number_format($reviewData['averageRating'], 1) }}</div>
                            <div class="text-sm text-gray-600">Avg Rating</div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @for($i = 5; $i >= 1; $i--)
                            <div class="flex items-center">
                                <span class="text-sm font-bold text-gray-700 w-12">{{ $i }}★</span>
                                <div class="flex-1 mx-3">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $reviewData['ratingDistribution'][$i] ?? 0 > 0 ? (($reviewData['ratingDistribution'][$i] / $reviewData['totalReviews']) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600 w-8">{{ $reviewData['ratingDistribution'][$i] ?? 0 }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($salesData['dailySales']->pluck('date')),
            datasets: [{
                label: 'Daily Sales',
                data: @json($salesData['dailySales']->pluck('total')),
                borderColor: 'rgb(128, 0, 0)',
                backgroundColor: 'rgba(128, 0, 0, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Top Products Chart
    const productsCtx = document.getElementById('productsChart').getContext('2d');
    new Chart(productsCtx, {
        type: 'bar',
        data: {
            labels: @json($salesData['topSellingProducts']->pluck('name')),
            datasets: [{
                label: 'Units Sold',
                data: @json($salesData['topSellingProducts']->pluck('quantity_sold')),
                backgroundColor: 'rgba(128, 0, 0, 0.8)',
                borderColor: 'rgb(128, 0, 0)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Auto-refresh metrics every 30 seconds
    setInterval(function() {
        fetch('{{ route("admin.reports.metrics") }}')
            .then(response => response.json())
            .then(data => {
                // Update real-time metrics here if needed
                console.log('Real-time metrics:', data);
            });
    }, 30000);
});
</script>
@endsection
