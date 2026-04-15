@extends('layouts.admin')

@section('title', 'Order Staff Dashboard')

@section('content')
<div class="space-y-6">
    <div class="bg-[#8b1d1d] rounded-2xl p-8 text-white shadow-xl">
        <h1 class="text-3xl font-bold mb-2">Order Staff Dashboard</h1>
        <p class="text-red-100 text-lg">Process orders, update status, and confirm refunds.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <p class="text-sm text-gray-600 font-medium">Pending Confirmation</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $pendingConfirmationCount }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <p class="text-sm text-gray-600 font-medium">In Processing / Shipping</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $processingCount }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <p class="text-sm text-gray-600 font-medium">Refund-Eligible Orders</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $readyForRefundCount }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <p class="text-sm text-gray-600 font-medium">Refunded Today</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $refundedTodayCount }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.regular.index') }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}"
               class="px-4 py-2 bg-[#8b1d1d] text-white rounded-lg hover:bg-[#6f1717] transition-colors">
                Manage Regular Orders
            </a>
            <a href="{{ route('admin.orders.index') }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}"
               class="px-4 py-2 bg-[#8b1d1d] text-white rounded-lg hover:bg-[#6f1717] transition-colors">
                Custom Orders Dashboard
            </a>
            <a href="{{ route('admin.custom-orders.index') }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}"
               class="px-4 py-2 border border-[#8b1d1d] text-[#8b1d1d] rounded-lg hover:bg-red-50 transition-colors">
                Open Custom Orders List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->user->name ?? $order->customer_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">PHP {{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ optional($order->created_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No recent orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
