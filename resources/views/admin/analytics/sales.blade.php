@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#a52a2a] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Sales Report</h1>
                <p class="text-red-100 text-sm sm:text-lg">Detailed breakdown of your revenue and sales performance</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</h3>
            <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalOrders }}</h3>
            <p class="text-gray-600 text-sm font-medium">Total Orders</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $completedOrders }}</h3>
            <p class="text-gray-600 text-sm font-medium">Completed Orders</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">₱{{ number_format($averageOrderValue, 2) }}</h3>
            <p class="text-gray-600 text-sm font-medium">Avg Order Value</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Daily Sales (last 30 days) -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Sales (Last {{ $period }} Days)</h3>
            @if($salesData->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3 font-semibold text-gray-700">Date</th>
                                <th class="text-right py-2 px-3 font-semibold text-gray-700">Orders</th>
                                <th class="text-right py-2 px-3 font-semibold text-gray-700">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData as $day)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-800">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                <td class="py-2 px-3 text-right text-gray-600">{{ $day->orders }}</td>
                                <td class="py-2 px-3 text-right font-medium text-[#800000]">₱{{ number_format($day->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="font-medium">No sales data for this period</p>
                    <p class="text-sm">Completed orders will appear here</p>
                </div>
            @endif
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue (Last 12 Months)</h3>
            @if($monthlyRevenue->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3 font-semibold text-gray-700">Month</th>
                                <th class="text-right py-2 px-3 font-semibold text-gray-700">Orders</th>
                                <th class="text-right py-2 px-3 font-semibold text-gray-700">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRevenue as $month)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-800">{{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y') }}</td>
                                <td class="py-2 px-3 text-right text-gray-600">{{ $month->orders }}</td>
                                <td class="py-2 px-3 text-right font-medium text-[#800000]">₱{{ number_format($month->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="font-medium">No monthly data yet</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Payment Methods -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
            @if($paymentMethods->count() > 0)
                <div class="space-y-3">
                    @foreach($paymentMethods as $method)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800">{{ ucfirst($method->payment_method ?? 'Unknown') }}</p>
                            <p class="text-sm text-gray-500">{{ $method->count }} orders</p>
                        </div>
                        <span class="text-lg font-bold text-[#800000]">₱{{ number_format($method->total ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">No payment data yet</p>
                </div>
            @endif
        </div>

        <!-- Top Products by Revenue -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products by Revenue</h3>
            @if($topProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 flex items-center justify-center bg-[#800000] text-white text-xs font-bold rounded-full">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->sold }} sold</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-[#800000]">₱{{ number_format($item->revenue ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">No product sales data yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
