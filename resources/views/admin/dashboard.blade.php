@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
/* Custom animations and styles */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

.animate-pulse-slow {
    animation: pulse 2s infinite;
}

.animate-slide-in-left {
    animation: slideInLeft 0.5s ease-out;
}

/* Glass morphism effect */
.glass-effect {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Gradient text */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Card hover effects */
.card-hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Neon glow effect */
.neon-glow {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
}

/* Progress bar animation */
.progress-animate {
    transition: width 1.5s ease-out;
}

/* Floating animation */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-purple-50">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation" style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 p-6 space-y-8">
        <!-- Enhanced Welcome Header -->
        <div class="animate-fade-in-up">
            <div class="bg-[#800000] rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-green-300 text-sm font-medium">System Online</span>
                            </div>
                            <h1 class="text-4xl lg:text-5xl font-bold leading-tight">
                                Welcome back, <span class="gradient-text text-white">Admin</span>
                            </h1>
                            <p class="text-xl text-indigo-100 max-w-2xl">
                                Here's your comprehensive business overview for {{ now()->format('F j, Y') }}
                            </p>
                            <div class="flex flex-wrap gap-3 pt-2">
                                <div class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span class="text-sm">{{ now()->format('l') }}</span>
                                </div>
                                <div class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                                    <i class="fas fa-clock"></i>
                                    <span class="text-sm">{{ now()->format('g:i A') }}</span>
                                </div>
                                <div class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                                    <i class="fas fa-sun"></i>
                                    <span class="text-sm">{{ now()->format('h:i A') }} PST</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 lg:mt-0 lg:ml-8">
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                                <div class="text-center">
                                    <div class="text-5xl font-bold mb-2">{{ $totalOrders }}</div>
                                    <div class="text-sm text-indigo-200">Total Orders</div>
                                    <div class="mt-3 text-2xl font-semibold">₱{{ number_format($totalRevenue, 0) }}</div>
                                    <div class="text-sm text-indigo-200">Total Revenue</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-in-left">
            <!-- Total Revenue Card -->
            <div class="group relative">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-2xl p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">Live</span>
                            <span class="text-xs text-gray-500 mt-1">+12.5%</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-3xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 0) }}</h3>
                        <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ min(100, round($totalRevenue / 1000)) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#800000] rounded-full progress-animate" style="width: {{ min(100, $totalRevenue / 1000) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="group relative">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-2xl p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">{{ $pendingOrders }} pending</span>
                            <span class="text-xs text-gray-500 mt-1">+8.2%</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-3xl font-bold text-gray-900">{{ $totalOrders }}</h3>
                        <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ min(100, round($totalOrders / 20)) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#800000] rounded-full progress-animate" style="width: {{ min(100, $totalOrders / 20) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="group relative">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-2xl p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">Active</span>
                            <span class="text-xs text-gray-500 mt-1">+15.3%</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-3xl font-bold text-gray-900">{{ $totalUsers }}</h3>
                        <p class="text-gray-600 text-sm font-medium">Total Users</p>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ min(100, round($totalUsers / 100)) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#800000] rounded-full progress-animate" style="width: {{ min(100, $totalUsers / 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Orders Card -->
            <div class="group relative">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-2xl p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">{{ $completedOrders }} done</span>
                            <span class="text-xs text-gray-500 mt-1">{{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) : 0 }}% rate</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-3xl font-bold text-gray-900">{{ $completedOrders }}</h3>
                        <p class="text-gray-600 text-sm font-medium">Completed Orders</p>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Completion Rate</span>
                            <span>{{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) : 0 }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#800000] rounded-full progress-animate" style="width: {{ $totalOrders > 0 ? min(100, ($completedOrders / $totalOrders) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Analytics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Average Order Value -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#800000] rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v3m-6-1v-6a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">AOV</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-gray-900">₱{{ number_format($averageOrderValue, 2) }}</h3>
                    <p class="text-gray-600 text-sm font-medium">Avg Order Value</p>
                    <p class="text-xs text-gray-500">Per completed order</p>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#800000] rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">Today</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $todayOrders }}</h3>
                    <p class="text-gray-600 text-sm font-medium">Orders Today</p>
                    <p class="text-xs text-gray-500">₱{{ number_format($todayRevenue, 0) }} revenue</p>
                </div>
            </div>

            <!-- Shipped Orders -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#800000] rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">In Transit</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $shippedOrders }}</h3>
                    <p class="text-gray-600 text-sm font-medium">Shipped Orders</p>
                    <p class="text-xs text-gray-500">On the way to customers</p>
                </div>
            </div>

            <!-- Orders with Notes -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#800000] rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-1 rounded-full">Notes</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $ordersWithNotes }}</h3>
                    <p class="text-gray-600 text-sm font-medium">Orders with Notes</p>
                    <p class="text-xs text-gray-500">{{ $totalOrders > 0 ? round(($ordersWithNotes / $totalOrders) * 100) : 0 }}% of total orders</p>
                </div>
            </div>
        </div>

        <!-- Payment & Delivery Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Methods Breakdown -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Payment Methods</h2>
                        <p class="text-sm text-gray-500">Revenue distribution</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach($paymentMethods as $method)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    {{ $method->payment_method === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }}">
                                    <i class="fas {{ $method->payment_method === 'online' ? 'fa-mobile-alt' : 'fa-university' }}"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ $method->payment_method === 'online' ? 'GCash' : 'Bank Transfer' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $method->count }} orders</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">₱{{ number_format($method->total, 0) }}</div>
                                <div class="text-xs text-gray-500">{{ $totalRevenue > 0 ? round(($method->total / $totalRevenue) * 100) : 0 }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery Types Breakdown -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Delivery Types</h2>
                        <p class="text-sm text-gray-500">Customer preferences</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach($deliveryTypes as $type)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    {{ $type->delivery_type === 'delivery' ? 'bg-purple-100 text-purple-600' : 'bg-orange-100 text-orange-600' }}">
                                    <i class="fas {{ $type->delivery_type === 'delivery' ? 'fa-shipping-fast' : 'fa-store' }}"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ ucfirst($type->delivery_type) }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $type->count }} orders</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">{{ $type->count }}</div>
                                <div class="text-xs text-gray-500">{{ $totalOrders > 0 ? round(($type->count / $totalOrders) * 100) : 0 }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Enhanced Charts Section -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Sales Chart -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Sales Overview</h2>
                            <p class="text-sm text-gray-500">Last 7 days performance</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            Export
                        </button>
                        <button class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Filter
                        </button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Order Status Chart -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-pie text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Order Status</h2>
                            <p class="text-sm text-gray-500">Real-time distribution</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.regular.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                        <span>View All</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
                <div class="h-80">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Enhanced Top Products Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-trophy text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Top Selling Products</h2>
                        <p class="text-sm text-gray-500">Best performers this week</p>
                    </div>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                    <span>View All</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            @if($topProducts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach($topProducts as $index => $product)
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-400 to-red-600 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300"></div>
                            <div class="relative bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-4 text-center border border-orange-100 hover:border-orange-200 transition-all duration-300">
                                <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-white font-bold text-lg">{{ $index + 1 }}</span>
                                </div>
                                <h3 class="font-bold text-gray-900 text-sm mb-1 truncate">{{ $product->name ?? 'Product ' . ($index + 1) }}</h3>
                                <p class="text-xs text-gray-600">{{ $product->quantity_sold ?? 0 }} sold</p>
                                <div class="mt-2 flex items-center justify-center space-x-1">
                                    @for($i = 0; $i < min(5, $product->quantity_sold ?? 0); $i++)
                                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No sales data yet</h3>
                    <p class="text-gray-500">Start selling to see your top products here</p>
                </div>
            @endif
        </div>

        <!-- Enhanced Recent Orders & Quick Actions -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Recent Orders -->
            <div class="xl:col-span-2 bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                            <p class="text-sm text-gray-500">Latest customer activity</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.regular.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                        <span>View All</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                    @if($recentOrders->count() > 0)
                        @foreach($recentOrders as $order)
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-400 to-indigo-600 rounded-xl opacity-0 group-hover:opacity-10 transition duration-300"></div>
                                <div class="relative bg-white rounded-xl p-4 border border-gray-100 hover:border-blue-200 transition-all duration-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                                                <i class="fas fa-shopping-bag text-white"></i>
                                            </div>
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <p class="font-bold text-gray-900">
                                                        #{{ $order->id ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center space-x-2 text-sm text-gray-600 mt-1">
                                                    <span>{{ $order->user_name ?? 'Guest' }}</span>
                                                    <span>•</span>
                                                    <span>{{ $order->created_at ?? 'Recent' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <div class="text-right">
                                                <p class="font-bold text-gray-900">₱{{ number_format($order->amount ?? 0, 0) }}</p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $order->status ?? 'pending' }}
                                                </span>
                                            </div>
                                            <div class="flex space-x-1">
                                                <button class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-200 transition-colors">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-shopping-bag text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No recent orders</h3>
                            <p class="text-gray-500">Orders will appear here once customers start shopping</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Quick Actions -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Quick Actions</h2>
                        <p class="text-sm text-gray-500">Common tasks</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <a href="{{ route('admin.products.create') }}" class="group block w-full text-left p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl transition-all duration-300 border border-blue-100 hover:border-blue-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus text-white group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Add Product</p>
                                <p class="text-sm text-gray-600">Create new product listing</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.inventory.create') }}" class="group block w-full text-left p-4 bg-gradient-to-r from-orange-50 to-red-50 hover:from-orange-100 hover:to-red-100 rounded-xl transition-all duration-300 border border-orange-100 hover:border-orange-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-white group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Add Inventory</p>
                                <p class="text-sm text-gray-600">Track product stock</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="group block w-full text-left p-4 bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 rounded-xl transition-all duration-300 border border-purple-100 hover:border-purple-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-white group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Manage Users</p>
                                <p class="text-sm text-gray-600">View customer accounts</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.analytics') }}" class="group block w-full text-left p-4 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 rounded-xl transition-all duration-300 border border-green-100 hover:border-green-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-bar text-white group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">View Reports</p>
                                <p class="text-sm text-gray-600">Analytics & insights</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log the data being passed to charts
    console.log('Sales Data Labels:', @json($allSalesData->isNotEmpty() ? $allSalesData->pluck('date') : []));
    console.log('Sales Data Values:', @json($allSalesData->isNotEmpty() ? $allSalesData->pluck('revenue') : []));
    console.log('Order Status Data:', [
        {{ $ordersByStatus['completed'] ?? 0 }},
        {{ $ordersByStatus['processing'] ?? 0 }},
        {{ $ordersByStatus['pending'] ?? 0 }},
        {{ $ordersByStatus['cancelled'] ?? 0 }}
    ]);
    
    // Chart.js global defaults
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#6b7280';
    
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($allSalesData->isNotEmpty() ? $allSalesData->pluck('date') : []),
            datasets: [{
                label: 'Sales',
                data: @json($allSalesData->isNotEmpty() ? $allSalesData->pluck('revenue') : []),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Sales: ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        },
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });

    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Processing', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $ordersByStatus['completed'] ?? 0 }},
                    {{ $ordersByStatus['processing'] ?? 0 }},
                    {{ $ordersByStatus['pending'] ?? 0 }},
                    {{ $ordersByStatus['cancelled'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(59, 130, 246)',
                    'rgb(250, 204, 21)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
