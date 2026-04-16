@extends('layouts.admin')

@section('title', 'Order Staff Dashboard')

@section('content')
<div class="space-y-6">
    @php
        $authToken = request('auth_token');
        $dashboardBase = route('staff.dashboard');
        $dashboardUrl = function (string $scope) use ($dashboardBase, $authToken) {
            $params = ['scope' => $scope];

            if (!empty($authToken)) {
                $params['auth_token'] = $authToken;
            }

            return $dashboardBase . '?' . http_build_query($params);
        };

        $regularOrdersUrl = route('admin.regular.index') . ($authToken ? ('?auth_token=' . $authToken) : '');
        $customDashboardUrl = route('admin.orders.index') . ($authToken ? ('?auth_token=' . $authToken) : '');
        $customOrdersUrl = route('admin.custom-orders.index') . ($authToken ? ('?auth_token=' . $authToken) : '');
    @endphp

    <div class="bg-[#8b1d1d] rounded-2xl p-8 text-white shadow-xl">
        <h1 class="text-3xl font-bold mb-2">Order Staff Dashboard</h1>
        <p class="text-red-100 text-lg">Process orders, update status, and confirm refunds.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <a href="{{ $dashboardUrl('pending_confirmation') }}" class="group bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:border-[#8b1d1d] hover:shadow-xl transition-all duration-200">
            <p class="text-sm text-gray-600 font-medium">Pending Confirmation</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $pendingConfirmationCount }}</p>
            <p class="text-xs text-gray-400 mt-3 group-hover:text-[#8b1d1d]">Click to view matching orders</p>
        </a>

        <a href="{{ $dashboardUrl('processing_shipping') }}" class="group bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:border-[#8b1d1d] hover:shadow-xl transition-all duration-200">
            <p class="text-sm text-gray-600 font-medium">In Processing / Shipping</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $processingCount }}</p>
            <p class="text-xs text-gray-400 mt-3 group-hover:text-[#8b1d1d]">Click to view matching orders</p>
        </a>

        <a href="{{ $dashboardUrl('refund_eligible') }}" class="group bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:border-[#8b1d1d] hover:shadow-xl transition-all duration-200">
            <p class="text-sm text-gray-600 font-medium">Refund-Eligible Orders</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $readyForRefundCount }}</p>
            <p class="text-xs text-gray-400 mt-3 group-hover:text-[#8b1d1d]">Click to view matching orders</p>
        </a>

        <a href="{{ $dashboardUrl('refunded_today') }}" class="group bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:border-[#8b1d1d] hover:shadow-xl transition-all duration-200">
            <p class="text-sm text-gray-600 font-medium">Refunded Today</p>
            <p class="text-3xl font-bold text-[#8b1d1d] mt-2">{{ $refundedTodayCount }}</p>
            <p class="text-xs text-gray-400 mt-3 group-hover:text-[#8b1d1d]">Click to view matching orders</p>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ $dashboardUrl('recent') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeScope === 'recent' ? 'bg-[#8b1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-[#8b1d1d]' }}">
                Recent
            </a>
            <a href="{{ $dashboardUrl('pending_confirmation') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeScope === 'pending_confirmation' ? 'bg-[#8b1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-[#8b1d1d]' }}">
                Pending Confirmation
            </a>
            <a href="{{ $dashboardUrl('processing_shipping') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeScope === 'processing_shipping' ? 'bg-[#8b1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-[#8b1d1d]' }}">
                Processing / Shipping
            </a>
            <a href="{{ $dashboardUrl('refund_eligible') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeScope === 'refund_eligible' ? 'bg-[#8b1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-[#8b1d1d]' }}">
                Refund-Eligible
            </a>
            <a href="{{ $dashboardUrl('refunded_today') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeScope === 'refunded_today' ? 'bg-[#8b1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-[#8b1d1d]' }}">
                Refunded Today
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ $regularOrdersUrl }}"
               class="px-4 py-2 bg-[#8b1d1d] text-white rounded-lg hover:bg-[#6f1717] transition-colors">
                Manage Regular Orders
            </a>
            <a href="{{ $customDashboardUrl }}"
               class="px-4 py-2 bg-[#8b1d1d] text-white rounded-lg hover:bg-[#6f1717] transition-colors">
                Custom Orders Dashboard
            </a>
            <a href="{{ $customOrdersUrl }}"
               class="px-4 py-2 border border-[#8b1d1d] text-[#8b1d1d] rounded-lg hover:bg-red-50 transition-colors">
                Open Custom Orders List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">{{ $ordersTitle }}</h2>
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
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No orders found for this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
