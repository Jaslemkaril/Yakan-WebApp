@extends('layouts.admin')

@section('title', 'Users Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#a52a2a] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Users Report</h1>
                <p class="text-red-100 text-sm sm:text-lg">Overview of user activity and growth</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalUsers }}</h3>
            <p class="text-gray-600 text-sm font-medium">Total Users</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $newUsersThisMonth }}</h3>
            <p class="text-gray-600 text-sm font-medium">New This Month</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $newUsersThisWeek }}</h3>
            <p class="text-gray-600 text-sm font-medium">New This Week</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- User Growth by Month -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth (Last 12 Months)</h3>
            @if($userGrowth->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3 font-semibold text-gray-700">Month</th>
                                <th class="text-right py-2 px-3 font-semibold text-gray-700">New Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userGrowth as $month)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-800">{{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y') }}</td>
                                <td class="py-2 px-3 text-right font-medium text-[#800000]">{{ $month->users }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/></svg>
                    <p class="font-medium">No growth data yet</p>
                </div>
            @endif
        </div>

        <!-- Top Customers -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Customers by Orders</h3>
            @if($topCustomers->count() > 0)
                <div class="space-y-3">
                    @foreach($topCustomers as $index => $customer)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 flex items-center justify-center bg-[#800000] text-white text-xs font-bold rounded-full">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-[#800000]">{{ $customer->orders_count }} orders</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">No customer order data yet</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Users</h3>
        @if($recentUsers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Name</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Email</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2 px-3 text-gray-800 font-medium">{{ $user->name }}</td>
                            <td class="py-2 px-3 text-gray-600">{{ $user->email }}</td>
                            <td class="py-2 px-3 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p class="font-medium">No users yet</p>
            </div>
        @endif
    </div>
</div>
@endsection
