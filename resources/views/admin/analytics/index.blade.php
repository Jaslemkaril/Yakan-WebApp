@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <!-- Analytics Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#600000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Analytics Dashboard</h1>
                <p class="text-indigo-100 text-lg">Deep insights into your e-commerce performance</p>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-900">₱{{ number_format($salesData->sum('revenue') ?? 0, 2) }}</h3>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-dollar-sign text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $salesData->sum('orders') ?? 0 }}</h3>
                </div>
                <div class="p-3 bg-red-50 rounded-full">
                    <i class="fas fa-shopping-cart text-[#800000]"></i>
                </div>
            </div>
        </div>

        <!-- New Users -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">New Users</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $userGrowth->sum('users') ?? 0 }}</h3>
                </div>
                <div class="p-3 bg-red-50 rounded-full">
                    <i class="fas fa-users text-[#800000]"></i>
                </div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Order Value</p>
                    <h3 class="text-2xl font-bold text-gray-900">₱{{ number_format(($salesData->sum('revenue') / max($salesData->sum('orders'), 1)) ?? 0, 2) }}</h3>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-chart-line text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend</h3>
            <div class="h-64 flex items-center justify-center text-gray-500">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl mb-2"></i>
                    <p>Chart functionality coming soon</p>
                </div>
            </div>
        </div>

        <!-- User Growth Chart -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth</h3>
            <div class="h-64 flex items-center justify-center text-gray-500">
                <div class="text-center">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>Chart functionality coming soon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products</h3>
        @if($productPerformance->count() > 0)
            <div class="space-y-3">
                @foreach($productPerformance->take(5) as $index => $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-500">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-sm text-gray-500">{{ $product->order_items_sum_quantity ?? 0 }} sold</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">₱{{ number_format($product->order_items_sum_price ?? 0, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box text-4xl mb-2"></i>
                <p>No product data available</p>
            </div>
        @endif
    </div>
</div>
@endsection
