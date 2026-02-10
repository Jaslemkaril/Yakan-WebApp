@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
/* Print styles */
@media print {
    /* Hide non-essential elements */
    .no-print,
    aside,
    .sidebar-mobile,
    .sidebar-overlay,
    nav,
    .mobile-header,
    header,
    button,
    .filter-section,
    .flex.gap-3,
    [x-data],
    .fixed {
        display: none !important;
    }
    
    /* Reset body and main content */
    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 12pt !important;
        line-height: 1.4 !important;
    }
    
    /* Full width for main content */
    main,
    .main-content,
    .min-h-screen,
    .flex.min-h-screen > div {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 15px !important;
    }
    
    /* Remove margins from flex containers */
    .flex {
        display: block !important;
    }
    
    .ml-0,
    .ml-72,
    [class*="ml-"] {
        margin-left: 0 !important;
    }
    
    /* Page setup */
    @page {
        margin: 1.5cm;
        size: A4 portrait;
    }
    
    /* Card styles for print */
    .card-hover-lift,
    .bg-white,
    [class*="rounded"] {
        box-shadow: none !important;
        transform: none !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    
    /* Remove backgrounds */
    .bg-gradient-to-br,
    .bg-gradient-to-b,
    [class*="bg-gradient"] {
        background: white !important;
    }
    
    /* Make maroon header visible on print */
    .bg-\[\\#800000\] {
        background: #800000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color: white !important;
    }
    
    /* Table styles */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 10pt !important;
    }
    
    th, td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
        text-align: left !important;
    }
    
    /* Hide animations */
    * {
        animation: none !important;
        transition: none !important;
    }
    
    /* Print header */
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #800000;
    }
    
    /* Show print header */
    .hidden.print\\:block {
        display: block !important;
    }
    
    /* Ensure grid displays properly */
    .grid {
        display: grid !important;
    }
    
    /* Page breaks */
    h1, h2, h3 {
        page-break-after: avoid;
    }
    
    .page-break {
        page-break-before: always;
    }
}

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
    <!-- Print Only Header -->
    <div class="hidden print:block print-header" style="display: none;">
        <h1 style="font-size: 24pt; color: #800000; margin-bottom: 5px;">Yakan E-commerce Dashboard Report</h1>
        <p style="font-size: 12pt; color: #666;">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p style="font-size: 11pt; color: #888;">Period: {{ ucfirst($period) }}</p>
    </div>
    
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none no-print">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 float-animation" style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 p-3 sm:p-4 md:p-6 space-y-6 sm:space-y-8">
        <!-- Filter and Print Controls -->
        <div class="no-print bg-white rounded-lg sm:rounded-xl shadow-md p-3 sm:p-4 md:p-6 animate-fade-in-up">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 flex-1">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Sales Period
                        </label>
                        <form action="{{ route('admin.dashboard') }}" method="GET" id="filterForm">
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" name="period" value="daily" 
                                    class="px-4 py-2 rounded-lg font-medium transition-colors {{ $period == 'daily' ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    <i class="fas fa-calendar-day mr-1"></i> Daily
                                </button>
                                <button type="submit" name="period" value="weekly" 
                                    class="px-4 py-2 rounded-lg font-medium transition-colors {{ $period == 'weekly' ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    <i class="fas fa-calendar-week mr-1"></i> Weekly
                                </button>
                                <button type="submit" name="period" value="yearly" 
                                    class="px-4 py-2 rounded-lg font-medium transition-colors {{ $period == 'yearly' ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    <i class="fas fa-calendar-alt mr-1"></i> Yearly
                                </button>
                                <button type="submit" name="period" value="all" 
                                    class="px-4 py-2 rounded-lg font-medium transition-colors {{ $period == 'all' ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    <i class="fas fa-infinity mr-1"></i> All Time
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    @if($outOfStockCount > 0)
                    <a href="#out-of-stock-section" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ $outOfStockCount }} Out of Stock</span>
                    </a>
                    @endif
                    
                    <a href="{{ route('admin.dashboard.export', ['period' => $period]) }}" download class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-file-csv"></i>
                        <span class="hidden sm:inline">Export CSV</span>
                        <span class="sm:hidden">CSV</span>
                    </a>
                    
                    <button onclick="window.print()" class="px-4 py-2 bg-[#800000] text-white rounded-lg font-medium hover:bg-[#600000] transition-colors flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        <span class="hidden sm:inline">Print Report</span>
                        <span class="sm:hidden">Print</span>
                    </button>
                </div>
            </div>
            
            @if($period != 'all')
            <div class="mt-3 pt-3 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Showing 
                    <span class="font-semibold text-[#800000]">
                        @if($period == 'daily')
                            Today's
                        @elseif($period == 'weekly')
                            This Week's
                        @elseif($period == 'yearly')
                            This Year's
                        @endif
                    </span>
                    data
                </p>
            </div>
            @endif
        </div>

        <!-- Enhanced Welcome Header -->
        <div class="animate-fade-in-up">
            <div class="bg-[#800000] rounded-xl sm:rounded-2xl md:rounded-3xl p-3 sm:p-4 md:p-6 lg:p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
                        <div class="space-y-2 sm:space-y-3 md:space-y-4">
                            <div class="flex items-center space-x-1.5 sm:space-x-2 md:space-x-3">
                                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 md:w-3 md:h-3 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-green-300 text-xs sm:text-sm font-medium">System Online</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold leading-tight">
                                Welcome back, <span class="gradient-text text-white">Admin</span>
                            </h1>
                            <p class="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl text-indigo-100 max-w-2xl">
                                Here's your comprehensive business overview for {{ now()->format('F j, Y') }}
                            </p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 md:gap-3 pt-1 sm:pt-2">
                                <div class="flex items-center space-x-1 sm:space-x-1.5 md:space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-2 sm:px-2.5 md:px-4 py-1 sm:py-1.5 md:py-2">
                                    <i class="fas fa-calendar-alt text-xs sm:text-sm"></i>
                                    <span class="text-xs sm:text-sm">{{ now()->format('l') }}</span>
                                </div>
                                <div class="flex items-center space-x-1 sm:space-x-1.5 md:space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-2 sm:px-2.5 md:px-4 py-1 sm:py-1.5 md:py-2">
                                    <i class="fas fa-clock text-xs sm:text-sm"></i>
                                    <span class="text-xs sm:text-sm">{{ now()->format('g:i A') }}</span>
                                </div>
                                <div class="hidden sm:flex items-center space-x-1.5 md:space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-2.5 md:px-4 py-1.5 md:py-2">
                                    <i class="fas fa-sun text-xs sm:text-sm"></i>
                                    <span class="text-xs sm:text-sm">{{ now()->format('h:i A') }} PST</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 sm:mt-4 lg:mt-0 lg:ml-8">
                            <div class="bg-white/10 backdrop-blur-md rounded-lg sm:rounded-xl md:rounded-2xl p-3 sm:p-4 md:p-6 border border-white/20">
                                <div class="text-center">
                                    <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-1 sm:mb-2">{{ $totalOrders }}</div>
                                    <div class="text-xs sm:text-sm text-indigo-200">Total Orders</div>
                                    <div class="mt-1 sm:mt-2 md:mt-3 text-lg sm:text-xl md:text-2xl font-semibold">₱{{ number_format($totalRevenue, 0) }}</div>
                                    <div class="text-xs sm:text-sm text-indigo-200">Total Revenue</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 animate-slide-in-left">
            <!-- Total Revenue Card -->
            <a href="{{ route('admin.analytics.sales') }}" class="group relative block cursor-pointer">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-xl sm:rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">Live</span>
                            <span class="text-xs text-gray-500 mt-1 hidden sm:inline">+12.5%</span>
                        </div>
                    </div>
                    <div class="space-y-0.5 sm:space-y-1 md:space-y-2">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 0) }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm font-medium">Total Revenue</p>
                    </div>
                    <div class="mt-2 sm:mt-3 md:mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ min(100, round($totalRevenue / 1000)) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#800000] rounded-full progress-animate" style="width: {{ min(100, $totalRevenue / 1000) }}%"></div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Total Orders Card -->
            <a href="{{ route('admin.orders.index') }}" class="group relative block cursor-pointer">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-xl sm:rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">{{ $pendingOrders }} pending</span>
                            <span class="text-xs text-gray-500 mt-1 hidden sm:inline">+8.2%</span>
                        </div>
                    </div>
                    <div class="space-y-0.5 sm:space-y-1 md:space-y-2">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $totalOrders }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm font-medium">Total Orders</p>
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
            </a>

            <!-- Total Users Card -->
            <a href="{{ route('admin.users.index') }}" class="group relative block cursor-pointer">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-xl sm:rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">Active</span>
                            <span class="text-xs text-gray-500 mt-1 hidden sm:inline">+15.3%</span>
                        </div>
                    </div>
                    <div class="space-y-0.5 sm:space-y-1 md:space-y-2">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $totalUsers }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm font-medium">Total Users</p>
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
            </a>

            <!-- Completed Orders Card -->
            <a href="{{ route('admin.orders.index') }}?status=completed" class="group relative block cursor-pointer">
                <div class="absolute -inset-0.5 bg-[#800000] rounded-xl sm:rounded-2xl opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
                <div class="relative bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 card-hover-lift">
                    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">{{ $completedOrders }} done</span>
                            <span class="text-xs text-gray-500 mt-1 hidden sm:inline">{{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) : 0 }}% rate</span>
                        </div>
                    </div>
                    <div class="space-y-0.5 sm:space-y-1 md:space-y-2">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $completedOrders }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm font-medium">Completed Orders</p>
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
            </a>
        </div>

        <!-- Additional Analytics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Average Order Value -->
            <a href="{{ route('admin.analytics.sales') }}" class="block bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 shadow-lg border border-gray-100 card-hover-lift hover:shadow-xl transition-shadow cursor-pointer">
                <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v3m-6-1v-6a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">AOV</span>
                    </div>
                </div>
                <div class="space-y-0.5 sm:space-y-1">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">₱{{ number_format($averageOrderValue, 2) }}</h3>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium">Avg Order Value</p>
                    <p class="text-xs text-gray-500 hidden sm:block">Per completed order</p>
                </div>
            </a>

            <!-- Today's Orders -->
            <a href="{{ route('admin.orders.index') }}?date=today" class="block bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 shadow-lg border border-gray-100 card-hover-lift hover:shadow-xl transition-shadow cursor-pointer">
                <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">Today</span>
                    </div>
                </div>
                <div class="space-y-0.5 sm:space-y-1">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">{{ $todayOrders }}</h3>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium">Orders Today</p>
                    <p class="text-xs text-gray-500 hidden sm:block">₱{{ number_format($todayRevenue, 0) }} revenue</p>
                </div>
            </a>

            <!-- Shipped Orders -->
            <a href="{{ route('admin.orders.index') }}?status=shipped" class="block bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 shadow-lg border border-gray-100 card-hover-lift hover:shadow-xl transition-shadow cursor-pointer">
                <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">In Transit</span>
                    </div>
                </div>
                <div class="space-y-0.5 sm:space-y-1">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">{{ $shippedOrders }}</h3>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium">Shipped Orders</p>
                    <p class="text-xs text-gray-500 hidden sm:block">On the way to customers</p>
                </div>
            </a>

            <!-- Orders with Notes -->
            <a href="{{ route('admin.orders.index') }}" class="block bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 shadow-lg border border-gray-100 card-hover-lift hover:shadow-xl transition-shadow cursor-pointer">
                <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-[#800000] rounded-lg sm:rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-[#800000] bg-[#fef2f2] px-2 py-0.5 sm:py-1 rounded-full">Notes</span>
                    </div>
                </div>
                <div class="space-y-0.5 sm:space-y-1">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">{{ $ordersWithNotes }}</h3>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium">Orders with Notes</p>
                    <p class="text-xs text-gray-500 hidden sm:block">{{ $totalOrders > 0 ? round(($ordersWithNotes / $totalOrders) * 100) : 0 }}% of total orders</p>
                </div>
            </a>
        </div>

        <!-- Payment & Delivery Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Payment Methods Breakdown -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 border border-gray-100 card-hover-lift">
                <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-white text-sm sm:text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Payment Methods</h2>
                        <p class="text-xs sm:text-sm text-gray-500">Revenue distribution</p>
                    </div>
                </div>
                <div class="space-y-3 sm:space-y-4">
                    @foreach($paymentMethods as $method)
                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center
                                    {{ $method->payment_method === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }}">
                                    <i class="fas {{ $method->payment_method === 'online' ? 'fa-mobile-alt' : 'fa-university' }} text-sm sm:text-base"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm sm:text-base">
                                        {{ $method->payment_method === 'online' ? 'GCash' : 'Bank Transfer' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $method->count }} orders</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base sm:text-lg font-bold text-gray-900">₱{{ number_format($method->total, 0) }}</div>
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

        <!-- Enhanced Top Products Section with Best Sellers & Low Sales -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 card-hover-lift" x-data="{ activeTab: 'bestsellers' }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Product Performance</h2>
                        <p class="text-sm text-gray-500">Sales analytics and insights</p>
                    </div>
                </div>
                
                <!-- Tab Buttons -->
                <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                    <button @click="activeTab = 'bestsellers'" 
                        :class="activeTab === 'bestsellers' ? 'bg-[#800000] text-white' : 'bg-transparent text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-md font-medium transition-all duration-200 text-sm">
                        <i class="fas fa-trophy mr-1"></i> Best Sellers
                    </button>
                    <button @click="activeTab = 'lowsales'" 
                        :class="activeTab === 'lowsales' ? 'bg-[#800000] text-white' : 'bg-transparent text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-md font-medium transition-all duration-200 text-sm">
                        <i class="fas fa-arrow-down mr-1"></i> Low Sales
                    </button>
                </div>
            </div>
            
            <!-- Best Sellers Tab -->
            <div x-show="activeTab === 'bestsellers'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if($topProducts->count() > 0)
                    <!-- Best Sellers Chart -->
                    <div class="mb-8 bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-green-600"></i>Sales Comparison
                        </h3>
                        <div class="relative h-80">
                            <canvas id="bestSellersChart"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        @foreach($topProducts as $index => $item)
                            @php
                                $product = $item->product;
                            @endphp
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-green-400 to-emerald-600 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                <div class="relative bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 text-center border border-green-100 hover:border-green-200 transition-all duration-300">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-lg">
                                        <span class="text-white font-bold text-lg">{{ $index + 1 }}</span>
                                    </div>
                                    @if($product && $product->image)
                                        <div class="w-16 h-16 mx-auto mb-3 rounded-lg overflow-hidden bg-white shadow-md">
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-md">
                                            <i class="fas fa-box text-green-600 text-2xl"></i>
                                        </div>
                                    @endif
                                    <h3 class="font-bold text-gray-900 text-sm mb-1 truncate" title="{{ $product->name ?? 'N/A' }}">
                                        {{ $product->name ?? 'Product ' . ($index + 1) }}
                                    </h3>
                                    <p class="text-xs text-gray-600 mb-1">
                                        <i class="fas fa-box mr-1"></i>{{ $item->sold ?? 0 }} sold
                                    </p>
                                    <p class="text-xs font-semibold text-green-600">
                                        <i class="fas fa-peso-sign mr-1"></i>{{ number_format($item->revenue ?? 0, 2) }}
                                    </p>
                                    @if($product && $product->stock > 0)
                                        <div class="mt-2">
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>In Stock
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No best sellers yet</h3>
                        <p class="text-gray-500">Start selling to see your top products here</p>
                    </div>
                @endif
            </div>
            
            <!-- Low Sales Tab -->
            <div x-show="activeTab === 'lowsales'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if($lowSalesProducts->count() > 0)
                    <!-- Low Sales Chart -->
                    <div class="mb-8 bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-orange-600"></i>Sales Comparison
                        </h3>
                        <div class="relative h-80">
                            <canvas id="lowSalesChart"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        @foreach($lowSalesProducts as $index => $item)
                            @php
                                $product = $item->product;
                            @endphp
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-400 to-red-600 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                <div class="relative bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-4 text-center border border-orange-100 hover:border-orange-200 transition-all duration-300">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-lg">
                                        <i class="fas fa-arrow-down text-white text-lg"></i>
                                    </div>
                                    @if($product && $product->image)
                                        <div class="w-16 h-16 mx-auto mb-3 rounded-lg overflow-hidden bg-white shadow-md">
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-red-100 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-md">
                                            <i class="fas fa-box text-orange-600 text-2xl"></i>
                                        </div>
                                    @endif
                                    <h3 class="font-bold text-gray-900 text-sm mb-1 truncate" title="{{ $product->name ?? 'N/A' }}">
                                        {{ $product->name ?? 'Product ' . ($index + 1) }}
                                    </h3>
                                    <p class="text-xs text-gray-600 mb-1">
                                        <i class="fas fa-box mr-1"></i>{{ $item->sold ?? 0 }} sold
                                    </p>
                                    <p class="text-xs font-semibold text-orange-600">
                                        <i class="fas fa-peso-sign mr-1"></i>{{ number_format($item->revenue ?? 0, 2) }}
                                    </p>
                                    @if($product && $product->stock <= 0)
                                        <div class="mt-2">
                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">
                                                <i class="fas fa-times-circle mr-1"></i>Out of Stock
                                            </span>
                                        </div>
                                    @elseif($product && $product->stock < 10)
                                        <div class="mt-2">
                                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Low Stock
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-lightbulb text-yellow-600 text-xl mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Boost These Products</h4>
                                <p class="text-sm text-gray-600">Consider running promotions or improving visibility for these items to increase sales.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No sales data available</h3>
                        <p class="text-gray-500">Products with low sales will appear here</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Out of Stock Items Section -->
        @if($outOfStockCount > 0)
        <div id="out-of-stock-section" class="bg-white rounded-2xl shadow-xl p-6 border-2 border-red-200 card-hover-lift">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-lg flex items-center justify-center animate-pulse">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Out of Stock Items</h2>
                        <p class="text-sm text-red-600">{{ $outOfStockCount }} product(s) need restocking</p>
                    </div>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                    <span>Manage Stock</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($outOfStockItems as $product)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 hover:bg-red-100 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900 text-sm truncate flex-1" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h3>
                            <span class="ml-2 px-2 py-1 bg-red-600 text-white text-xs rounded-full flex-shrink-0">
                                0 stock
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mb-2">
                            <i class="fas fa-tag mr-1"></i>₱{{ number_format($product->price, 2) }}
                        </p>
                        @if($product->category)
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-folder mr-1"></i>{{ $product->category }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

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

    // Best Sellers Chart
    const bestSellersCtx = document.getElementById('bestSellersChart');
    if (bestSellersCtx) {
        new Chart(bestSellersCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($topProducts->map(function($item) { return optional($item->product)->name ?? 'Product'; })),
                datasets: [{
                    label: 'Units Sold',
                    data: @json($topProducts->pluck('sold')),
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(22, 163, 74, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(5, 150, 105, 0.8)',
                        'rgba(4, 120, 87, 0.8)',
                        'rgba(6, 95, 70, 0.8)',
                        'rgba(5, 83, 58, 0.8)',
                        'rgba(4, 72, 50, 0.8)',
                        'rgba(3, 60, 43, 0.8)',
                        'rgba(2, 50, 35, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(22, 163, 74)',
                        'rgb(16, 185, 129)',
                        'rgb(5, 150, 105)',
                        'rgb(4, 120, 87)',
                        'rgb(6, 95, 70)',
                        'rgb(5, 83, 58)',
                        'rgb(4, 72, 50)',
                        'rgb(3, 60, 43)',
                        'rgb(2, 50, 35)'
                    ],
                    borderRadius: 8,
                    borderWidth: 2,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
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
                                return 'Sold: ' + context.parsed.x + ' units';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' units';
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    // Low Sales Chart
    const lowSalesCtx = document.getElementById('lowSalesChart');
    if (lowSalesCtx) {
        new Chart(lowSalesCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($lowSalesProducts->map(function($item) { return optional($item->product)->name ?? 'Product'; })),
                datasets: [{
                    label: 'Units Sold',
                    data: @json($lowSalesProducts->pluck('sold')),
                    backgroundColor: [
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(234, 88, 12, 0.8)',
                        'rgba(194, 65, 12, 0.8)',
                        'rgba(154, 52, 18, 0.8)',
                        'rgba(120, 40, 13, 0.8)',
                        'rgba(92, 31, 11, 0.8)',
                        'rgba(88, 28, 12, 0.8)',
                        'rgba(78, 22, 6, 0.8)',
                        'rgba(69, 19, 5, 0.8)'
                    ],
                    borderColor: [
                        'rgb(251, 146, 60)',
                        'rgb(249, 115, 22)',
                        'rgb(234, 88, 12)',
                        'rgb(194, 65, 12)',
                        'rgb(154, 52, 18)',
                        'rgb(120, 40, 13)',
                        'rgb(92, 31, 11)',
                        'rgb(88, 28, 12)',
                        'rgb(78, 22, 6)',
                        'rgb(69, 19, 5)'
                    ],
                    borderRadius: 8,
                    borderWidth: 2,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
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
                                return 'Sold: ' + context.parsed.x + ' units';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' units';
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
