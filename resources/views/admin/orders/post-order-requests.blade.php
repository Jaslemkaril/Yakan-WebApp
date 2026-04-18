@extends('layouts.admin')

@section('title', 'Post-Order Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Post-Order Request</h1>
            <p class="text-sm text-gray-500">Combined regular and custom cancel/refund requests</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">Cancel requests</h2>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Cancel</span>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-amber-600">{{ $stats['cancel']['pending'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Pending</div>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-green-600">{{ $stats['cancel']['approved'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Approved</div>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-red-600">{{ $stats['cancel']['rejected'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Rejected</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">Refund requests</h2>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Refund</span>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-blue-600">{{ $stats['refund']['under_review'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Under review</div>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-green-600">{{ $stats['refund']['refunded'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Refunded</div>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <div class="text-xl font-bold text-red-600">{{ $stats['refund']['rejected'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Rejected</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 space-y-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ request()->fullUrlWithQuery(['type' => 'all', 'page' => null]) }}" class="px-4 py-2 rounded-lg text-sm font-medium border {{ $typeFilter === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">All</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'cancel', 'page' => null]) }}" class="px-4 py-2 rounded-lg text-sm font-medium border {{ $typeFilter === 'cancel' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">Cancel requests</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'refund', 'page' => null]) }}" class="px-4 py-2 rounded-lg text-sm font-medium border {{ $typeFilter === 'refund' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">Refund requests</a>
        </div>

        <form method="GET" class="w-full">
            @foreach(request()->except(['search', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search order or customer..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gray-900 focus:ring-gray-900"
            >
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Order type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($requests as $item)
                        @php
                            $typeClasses = $item['type'] === 'cancel'
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-blue-100 text-blue-700';

                            $statusClasses = match($item['status_key']) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'approved', 'refunded' => 'bg-green-100 text-green-700',
                                'under_review' => 'bg-blue-100 text-blue-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">{{ $item['display_id'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $item['customer'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeClasses }}">
                                    {{ ucfirst($item['type']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm text-gray-700">{{ ucfirst($item['order_type']) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">P{{ number_format((float) $item['amount'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ $item['view_url'] }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    View
                                    <span aria-hidden="true">↗</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No post-order requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($requests, 'links'))
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
