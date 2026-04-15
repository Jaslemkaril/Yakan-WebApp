@extends('layouts.admin')

@section('title', 'Cancelled Orders')

@section('content')
<div class="space-y-6">
    <div class="bg-[#8b1d1d] rounded-2xl p-8 text-white shadow-xl">
        <h1 class="text-3xl font-bold mb-2">Cancelled Orders</h1>
        <p class="text-red-100 text-lg">Track cancelled orders and cancellation details.</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order ID, customer name, email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8b1d1d] focus:border-[#8b1d1d]">
            </div>
            <button type="submit" class="px-4 py-2 bg-[#8b1d1d] text-white rounded-lg hover:bg-[#6f1717] transition-colors">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Order ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Customer Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Product/Item Ordered</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Quantity</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Total Price</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date Cancelled</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Reason</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Cancelled By</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Refund Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    @php
                        $items = $order->orderItems;
                        $itemNames = $items->pluck('product.name')->filter()->take(2)->implode(', ');
                        if ($itemNames === '') {
                            $itemNames = 'N/A';
                        }
                        if ($items->count() > 2) {
                            $itemNames .= ' +' . ($items->count() - 2) . ' more';
                        }
                        $totalQty = (int) $items->sum('quantity');
                        $cancelReason = $order->admin_notes ?: ($order->notes ?: 'N/A');
                        $cancelledBy = 'N/A';
                        $source = strtolower((string) ($order->source ?? ''));
                        if ($source === 'mobile' || $source === 'web') {
                            $cancelledBy = 'Customer';
                        } elseif ($source === 'admin') {
                            $cancelledBy = 'Admin/Staff';
                        }
                        $refundStatus = strtolower((string) ($order->payment_status ?? '')) === 'refunded' ? 'Refunded' : 'No Refund';
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-semibold text-[#8b1d1d]">{{ $order->order_ref ?? ('#' . $order->id) }}</td>
                        <td class="px-4 py-3">{{ $order->user->name ?? $order->customer_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $itemNames }}</td>
                        <td class="px-4 py-3">{{ $totalQty }}</td>
                        <td class="px-4 py-3">PHP {{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
                        <td class="px-4 py-3">{{ optional($order->cancelled_at ?? $order->updated_at)->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3 max-w-xs truncate" title="{{ $cancelReason }}">{{ $cancelReason }}</td>
                        <td class="px-4 py-3">{{ $cancelledBy }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $refundStatus === 'Refunded' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $refundStatus }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No cancelled orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
