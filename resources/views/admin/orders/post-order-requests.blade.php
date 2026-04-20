@extends('layouts.admin')

@section('title', 'Post-Order Requests')

@push('styles')
<style>
    .post-order-stat-card {
        border-radius: 0.75rem;
        padding: 1.25rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
        transition: box-shadow 0.2s ease;
        border-left: 4px solid #800000;
    }

    .post-order-stat-card:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
    }

    .kpi-stat-btn {
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        display: block;
    }

    .kpi-stat-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        border-color: #800000 !important;
    }

    .kpi-stat-btn.kpi-active {
        border-color: #800000 !important;
        background: #fff5f5 !important;
        box-shadow: 0 0 0 2px rgba(128,0,0,0.2);
    }

    .post-order-filter-section {
        background: #ffffff;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 2px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .post-order-table-wrap {
        background: #ffffff;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .post-order-filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        border: 2px solid #d1d5db;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.25rem;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .post-order-filter-chip-active {
        background: #800000;
        color: #ffffff;
        border-color: #800000;
        box-shadow: 0 2px 6px 0 rgba(128, 0, 0, 0.3);
    }

    .post-order-filter-chip-idle {
        background: #ffffff;
        color: #374151;
        border-color: #9ca3af;
    }

    .post-order-filter-chip-idle:hover {
        background: #f3f4f6;
        border-color: #6b7280;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Post-Order Request</h1>
            <p class="text-gray-600">Combined regular and custom cancel/refund requests</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</span>
            <button id="printReportBtn" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm"
                title="Print report">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="post-order-stat-card">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">Cancel requests</h2>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Cancel</span>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                @php
                    $cancelPendingActive = $typeFilter === 'cancel' && $statusFilter === 'pending';
                    $cancelApprovedActive = $typeFilter === 'cancel' && $statusFilter === 'approved';
                    $cancelRejectedActive = $typeFilter === 'cancel' && $statusFilter === 'rejected';
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['type' => 'cancel', 'status' => 'pending', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $cancelPendingActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-amber-600">{{ $stats['cancel']['pending'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Pending</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'cancel', 'status' => 'approved', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $cancelApprovedActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-green-600">{{ $stats['cancel']['approved'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Approved</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'cancel', 'status' => 'rejected', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $cancelRejectedActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-red-600">{{ $stats['cancel']['rejected'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Rejected</div>
                </a>
            </div>
        </div>

        <div class="post-order-stat-card">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">Refund requests</h2>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Refund</span>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                @php
                    $refundReviewActive = $typeFilter === 'refund' && $statusFilter === 'under_review';
                    $refundedActive = $typeFilter === 'refund' && $statusFilter === 'refunded';
                    $refundRejectedActive = $typeFilter === 'refund' && $statusFilter === 'rejected';
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['type' => 'refund', 'status' => 'under_review', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $refundReviewActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-blue-600">{{ $stats['refund']['under_review'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Under review</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'refund', 'status' => 'refunded', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $refundedActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-green-600">{{ $stats['refund']['refunded'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Refunded</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'refund', 'status' => 'rejected', 'page' => null]) }}" class="kpi-stat-btn bg-gray-50 border border-gray-200 rounded-lg py-2 {{ $refundRejectedActive ? 'kpi-active' : '' }}">
                    <div class="text-xl font-bold text-red-600">{{ $stats['refund']['rejected'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Rejected</div>
                </a>
                </div>
            </div>
        </div>
    </div>

    <div class="post-order-filter-section space-y-3">
        <div class="flex flex-wrap gap-3">
            <a href="{{ request()->fullUrlWithQuery(['type' => 'all', 'status' => null, 'page' => null]) }}" class="post-order-filter-chip {{ $typeFilter === 'all' && $statusFilter === '' ? 'post-order-filter-chip-active' : 'post-order-filter-chip-idle' }}">All</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'cancel', 'status' => null, 'page' => null]) }}" class="post-order-filter-chip {{ $typeFilter === 'cancel' && $statusFilter === '' ? 'post-order-filter-chip-active' : 'post-order-filter-chip-idle' }}">Cancel requests</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'refund', 'status' => null, 'page' => null]) }}" class="post-order-filter-chip {{ $typeFilter === 'refund' && $statusFilter === '' ? 'post-order-filter-chip-active' : 'post-order-filter-chip-idle' }}">Refund requests</a>
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
                class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]"
            >
        </form>
    </div>

    <div class="post-order-table-wrap">
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
                        <tr class="hover:bg-gray-50" data-row-id="{{ $item['row_id'] }}">
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
                                @if(($item['action_kind'] ?? '') === 'cancel_modal')
                                    <button
                                        type="button"
                                        class="post-order-view-cancel inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                        data-request='@json($item['cancel_payload'] ?? [])'
                                    >
                                        View
                                    </button>
                                @elseif(($item['action_kind'] ?? '') === 'refund_modal')
                                    <button
                                        type="button"
                                        class="post-order-view-refund inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                        data-refund='@json($item['refund_payload'] ?? [])'
                                    >
                                        View
                                    </button>
                                @else
                                    <a href="{{ $item['view_url'] }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                        View
                                    </a>
                                @endif
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
</div>

<div id="postOrderCancelModal" class="fixed inset-0 bg-black/55 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 id="poCancelOrderTitle" class="text-xl font-bold text-gray-900">Order #</h2>
                <div id="poCancelStatusBadgeWrap" class="mt-2"></div>
            </div>
            <button id="poCancelCloseBtn" type="button" class="w-11 h-11 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 text-xl" aria-label="Close modal">×</button>
        </div>

        <div class="p-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Customer</p><p id="poCancelCustomer" class="text-lg font-semibold text-gray-900"></p></div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Refund amount</p><p id="poCancelRefundAmount" class="text-lg font-semibold text-gray-900"></p></div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Payment method</p><p id="poCancelPaymentMethod" class="text-lg font-semibold text-gray-900"></p></div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Order status</p><p id="poCancelOrderStatus" class="text-lg font-semibold text-gray-900"></p></div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Cancellation reason</p><p id="poCancelReason" class="text-lg font-semibold text-gray-900"></p></div>
            <div id="poCancelCustomerNoteWrap" class="bg-gray-50 rounded-xl p-3 border border-gray-100"><p class="text-sm text-gray-500">Customer note</p><p id="poCancelCustomerNote" class="text-lg font-semibold text-gray-900"></p></div>

            <div id="poCancelActionForm" class="space-y-3 hidden">
                <label for="poCancelRejectionReason" class="block text-sm font-semibold text-gray-700">Rejection reason <span class="text-rose-600">(required for reject)</span></label>
                <textarea id="poCancelRejectionReason" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y" placeholder="e.g. Order already prepared and out for delivery"></textarea>
                <label for="poCancelAdminNote" class="block text-sm font-semibold text-gray-700">Admin note (optional)</label>
                <textarea id="poCancelAdminNote" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y" placeholder="e.g. Refund initiated via GCash..."></textarea>
                <div id="poCancelFormError" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" id="poCancelApproveBtn" class="w-full px-4 py-3 bg-[#800000] text-white rounded-lg font-semibold hover:bg-[#600000] transition-colors">Approve & refund</button>
                    <button type="button" id="poCancelRejectBtn" class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Reject request</button>
                </div>
            </div>

            <div id="poCancelResolvedMessage" class="text-center rounded-xl px-4 py-4 font-medium hidden"></div>

            <div class="flex justify-end gap-3 pt-1">
                <a id="poCancelOpenOrderBtn" href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Open order details</a>
            </div>
        </div>
    </div>
</div>

<div id="postOrderRefundModal" class="fixed inset-0 bg-black/55 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div class="lg:col-span-2 p-3.5 border-r border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 id="poRefundId" class="text-xl font-bold text-gray-900">Refund #</h2>
                        <span id="poRefundStatusBadge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold mt-2"></span>
                    </div>
                    <button id="poRefundCloseBtn" type="button" class="w-11 h-11 border border-gray-300 rounded-xl text-xl text-gray-700 hover:bg-gray-100" aria-label="Close modal">×</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Customer</p><p id="poRefundCustomer" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Order</p><p id="poRefundOrder" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Refund type</p><p id="poRefundType" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Reason</p><p id="poRefundReason" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Amount</p><p id="poRefundAmount" class="text-base font-semibold text-[#800000]"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Refund to</p><p id="poRefundTo" class="text-base font-semibold text-gray-900"></p></div>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 mb-3"><p class="text-xs text-gray-500">Customer note</p><p id="poRefundCustomerNote" class="text-base font-semibold text-gray-900"></p></div>

                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Photo proof</p>
                    <div id="poRefundEvidenceWrap" class="min-h-[72px] rounded-xl border border-gray-200 p-3 bg-gray-50 text-center text-gray-500 text-sm"></div>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 space-y-3">
                <h3 class="text-sm font-semibold text-gray-800">Choose an action</h3>
                <p class="text-xs text-gray-600">Review the customer's claim and photo proof before deciding.</p>

                <div class="space-y-2">
                    <button type="button" id="poRefundApproveBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Approve & release refund</button>
                    <button type="button" id="poRefundRequestReturnBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Request item return</button>
                    <button type="button" id="poRefundRejectBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Reject request</button>
                    <button type="button" id="poRefundRejectNotReturnedBtn" class="hidden w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Reject (item not returned)</button>
                </div>

                <label for="poRefundAdminNote" class="text-sm font-semibold text-gray-700">Admin note</label>
                <textarea id="poRefundAdminNote" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y" placeholder="e.g. Photo verified, refund approved..."></textarea>
                <p id="poRefundError" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700"></p>
                <div id="poRefundReadonlyMessage" class="hidden rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 text-center"></div>

                <div class="flex justify-end pt-1">
                    <a id="poRefundOpenOrderBtn" href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Open order details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="poEvidencePreviewModal" class="fixed inset-0 bg-black/80 z-[60] hidden items-center justify-center p-4">
    <button id="poEvidencePreviewCloseBtn" type="button" class="absolute top-4 right-4 w-10 h-10 rounded-full border border-white/40 bg-black/40 text-white text-2xl leading-none hover:bg-black/60" aria-label="Close image preview">×</button>
    <img id="poEvidencePreviewImg" src="" alt="Refund evidence preview" class="hidden max-w-[95vw] max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/20">
    <video id="poEvidencePreviewVideo" class="hidden max-w-[95vw] max-h-[90vh] rounded-lg shadow-2xl border border-white/20 bg-black" controls playsinline></video>
</div>

<script>
    (function () {
        const csrfToken = '{{ csrf_token() }}';
        const authToken = new URLSearchParams(window.location.search).get('auth_token') || '';

        const postTo = function(url, data) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            if (authToken) {
                const authInput = document.createElement('input');
                authInput.type = 'hidden';
                authInput.name = 'auth_token';
                authInput.value = authToken;
                form.appendChild(authInput);
            }

            Object.keys(data || {}).forEach(function (key) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key] || '';
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        };

        const cancelModal = document.getElementById('postOrderCancelModal');
        const cancelOpenButtons = document.querySelectorAll('.post-order-view-cancel');
        const cancelCloseBtn = document.getElementById('poCancelCloseBtn');
        const cancelApproveBtn = document.getElementById('poCancelApproveBtn');
        const cancelRejectBtn = document.getElementById('poCancelRejectBtn');
        const cancelErrorEl = document.getElementById('poCancelFormError');
        const cancelActionForm = document.getElementById('poCancelActionForm');
        const cancelResolvedMessage = document.getElementById('poCancelResolvedMessage');
        const cancelRejectionReason = document.getElementById('poCancelRejectionReason');
        const cancelAdminNote = document.getElementById('poCancelAdminNote');
        let currentCancelPayload = null;

        const cancelBadge = function (state) {
            if (state === 'approved') {
                return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Approved</span>';
            }
            if (state === 'rejected') {
                return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Rejected</span>';
            }
            return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>';
        };

        const openCancelModal = function (payload) {
            currentCancelPayload = payload || {};
            document.getElementById('poCancelOrderTitle').textContent = 'Order ' + (currentCancelPayload.order_id || '#');
            document.getElementById('poCancelStatusBadgeWrap').innerHTML = cancelBadge(currentCancelPayload.status_state);
            document.getElementById('poCancelCustomer').textContent = currentCancelPayload.customer || 'N/A';
            document.getElementById('poCancelRefundAmount').textContent = 'P' + (currentCancelPayload.refund_amount || '0.00');
            document.getElementById('poCancelPaymentMethod').textContent = currentCancelPayload.payment_method || 'N/A';
            document.getElementById('poCancelOrderStatus').textContent = currentCancelPayload.order_status || 'N/A';
            document.getElementById('poCancelReason').textContent = currentCancelPayload.cancel_reason || 'N/A';
            
            // Show customer note only if not empty
            const customerNote = currentCancelPayload.customer_note || '';
            const customerNoteWrap = document.getElementById('poCancelCustomerNoteWrap');
            if (customerNote.trim() !== '') {
                document.getElementById('poCancelCustomerNote').textContent = customerNote;
                customerNoteWrap.classList.remove('hidden');
            } else {
                customerNoteWrap.classList.add('hidden');
            }
            
            document.getElementById('poCancelOpenOrderBtn').href = currentCancelPayload.order_show_url || '#';
            cancelRejectionReason.value = '';
            cancelAdminNote.value = currentCancelPayload.admin_note || '';
            cancelErrorEl.classList.add('hidden');

            if (currentCancelPayload.status_state === 'pending') {
                cancelActionForm.classList.remove('hidden');
                cancelResolvedMessage.classList.add('hidden');
            } else {
                cancelActionForm.classList.add('hidden');
                cancelResolvedMessage.classList.remove('hidden');
                if (currentCancelPayload.status_state === 'approved') {
                    cancelResolvedMessage.className = 'text-center rounded-xl px-4 py-4 font-medium bg-green-50 text-green-700 border border-green-100';
                    cancelResolvedMessage.textContent = 'This request was approved and refund was initiated.';
                } else {
                    cancelResolvedMessage.className = 'text-center rounded-xl px-4 py-4 font-medium bg-rose-50 text-rose-700 border border-rose-100';
                    cancelResolvedMessage.textContent = 'This request was rejected.';
                }
            }

            cancelModal.classList.remove('hidden');
            cancelModal.classList.add('flex');
        };

        const closeCancelModal = function () {
            cancelModal.classList.add('hidden');
            cancelModal.classList.remove('flex');
        };

        cancelOpenButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                try {
                    openCancelModal(JSON.parse(button.getAttribute('data-request') || '{}'));
                } catch (error) {
                    openCancelModal({});
                }
            });
        });

        cancelApproveBtn.addEventListener('click', function () {
            if (!currentCancelPayload || !currentCancelPayload.approve_url) {
                return;
            }
            postTo(currentCancelPayload.approve_url, {
                admin_note: (cancelAdminNote.value || '').trim(),
            });
        });

        cancelRejectBtn.addEventListener('click', function () {
            if (!currentCancelPayload || !currentCancelPayload.reject_url) {
                return;
            }
            const rejectionReason = (cancelRejectionReason.value || '').trim();
            if (!rejectionReason) {
                cancelErrorEl.classList.remove('hidden');
                cancelErrorEl.textContent = 'Rejection reason is required when rejecting a request.';
                return;
            }
            cancelErrorEl.classList.add('hidden');
            const adminNote = (cancelAdminNote.value || '').trim();
            const isCustom = !!currentCancelPayload.is_custom;
            postTo(currentCancelPayload.reject_url, {
                rejection_reason: rejectionReason,
                admin_note: isCustom ? (adminNote || rejectionReason) : adminNote,
            });
        });

        cancelCloseBtn.addEventListener('click', closeCancelModal);
        cancelModal.addEventListener('click', function (event) {
            if (event.target === cancelModal) {
                closeCancelModal();
            }
        });

        const refundModal = document.getElementById('postOrderRefundModal');
        const refundOpenButtons = document.querySelectorAll('.post-order-view-refund');
        const refundCloseBtn = document.getElementById('poRefundCloseBtn');
        const refundApproveBtn = document.getElementById('poRefundApproveBtn');
        const refundRequestReturnBtn = document.getElementById('poRefundRequestReturnBtn');
        const refundRejectBtn = document.getElementById('poRefundRejectBtn');
        const refundRejectNotReturnedBtn = document.getElementById('poRefundRejectNotReturnedBtn');
        const refundErrorEl = document.getElementById('poRefundError');
        const refundReadonly = document.getElementById('poRefundReadonlyMessage');
        const refundAdminNote = document.getElementById('poRefundAdminNote');
        const evidenceWrap = document.getElementById('poRefundEvidenceWrap');
        let currentRefundPayload = null;
        let activeRefundTrigger = null;

        const statusClass = function (state) {
            if (state === 'awaiting_return') return 'bg-amber-100 text-amber-700';
            if (state === 'refunded') return 'bg-green-100 text-green-700';
            if (state === 'rejected') return 'bg-rose-100 text-rose-700';
            return 'bg-blue-100 text-blue-700';
        };

        const evidencePreviewModal = document.getElementById('poEvidencePreviewModal');
        const evidencePreviewCloseBtn = document.getElementById('poEvidencePreviewCloseBtn');
        const evidencePreviewImg = document.getElementById('poEvidencePreviewImg');
        const evidencePreviewVideo = document.getElementById('poEvidencePreviewVideo');

        const openEvidencePreview = function (type, src) {
            if (!src) return;
            evidencePreviewModal.classList.remove('hidden');
            evidencePreviewModal.classList.add('flex');
            if (type === 'video') {
                evidencePreviewImg.classList.add('hidden');
                evidencePreviewImg.src = '';
                evidencePreviewVideo.classList.remove('hidden');
                
                // Add Cloudinary poster for better video preview
                if (src.includes('cloudinary.com') && src.includes('/video/upload/')) {
                    const posterUrl = src.replace('/video/upload/', '/video/upload/so_0,f_jpg/').replace('#t=0.1', '');
                    evidencePreviewVideo.setAttribute('poster', posterUrl);
                }
                
                evidencePreviewVideo.src = src;
                evidencePreviewVideo.load();
                evidencePreviewVideo.play().catch(function () {});
            } else {
                evidencePreviewVideo.pause();
                evidencePreviewVideo.classList.add('hidden');
                evidencePreviewVideo.src = '';
                evidencePreviewImg.classList.remove('hidden');
                evidencePreviewImg.src = src;
            }
        };

        const closeEvidencePreview = function () {
            evidencePreviewModal.classList.add('hidden');
            evidencePreviewModal.classList.remove('flex');
            evidencePreviewImg.src = '';
            evidencePreviewImg.classList.add('hidden');
            evidencePreviewVideo.pause();
            evidencePreviewVideo.src = '';
            evidencePreviewVideo.removeAttribute('poster');
            evidencePreviewVideo.classList.add('hidden');
        };

        const renderEvidence = function (items) {
            const evidence = Array.isArray(items) ? items : [];
            if (!evidence.length) {
                evidenceWrap.innerHTML = '<span class="text-sm text-gray-500">No evidence uploaded.</span>';
                return;
            }

            const cards = evidence.map(function (item) {
                const isVideo = !!item.is_video;
                const thumb = item.preview_url || item.open_url || item.fallback_url || '#';
                const safeThumb = String(thumb).replace(/'/g, '&#39;');
                const type = isVideo ? 'video' : 'image';
                
                // Generate Cloudinary video poster
                let posterAttr = '';
                if (isVideo && safeThumb.includes('cloudinary.com')) {
                    const posterUrl = safeThumb.replace('/video/upload/', '/video/upload/so_0,f_jpg/');
                    posterAttr = ' poster="' + posterUrl + '"';
                }
                
                const media = isVideo
                    ? '<video src="' + safeThumb + '#t=0.1" class="h-16 w-full object-cover rounded border border-gray-200 bg-black" muted playsinline preload="metadata"' + posterAttr + '></video>'
                    : '<img src="' + safeThumb + '" class="h-16 w-full object-cover rounded border border-gray-200" alt="Evidence">';
                
                // Add #t=0.1 to video src for better thumbnail generation
                const dataSrc = isVideo ? safeThumb + '#t=0.1' : safeThumb;

                return '<button type="button" class="po-evidence-item w-24 text-left relative" data-type="' + type + '" data-src="' + dataSrc + '">'
                    + media
                    + (isVideo ? '<div class="absolute inset-0 flex items-center justify-center pointer-events-none"><span class="text-white text-2xl">▶</span></div>' : '')
                    + '</button>';
            }).join('');

            evidenceWrap.innerHTML = '<div class="flex flex-wrap gap-3">' + cards + '</div>';

            evidenceWrap.querySelectorAll('.po-evidence-item').forEach(function (button) {
                button.addEventListener('click', function () {
                    openEvidencePreview(button.getAttribute('data-type'), button.getAttribute('data-src'));
                });
            });
        };

        const setRefundMode = function (payload) {
            const state = payload.status_state || 'under_review';
            const isCustom = !!payload.is_custom;

            refundApproveBtn.classList.remove('hidden');
            refundRejectBtn.classList.remove('hidden');
            refundRequestReturnBtn.classList.remove('hidden');
            refundRejectNotReturnedBtn.classList.add('hidden');
            refundReadonly.classList.add('hidden');
            refundAdminNote.disabled = false;
            refundApproveBtn.disabled = false;
            refundRejectBtn.disabled = false;
            refundRequestReturnBtn.disabled = false;

            if (state === 'refunded' || state === 'rejected') {
                refundApproveBtn.classList.add('hidden');
                refundRejectBtn.classList.add('hidden');
                refundRequestReturnBtn.classList.add('hidden');
                refundReadonly.classList.remove('hidden');
                refundReadonly.textContent = state === 'refunded'
                    ? 'This request is already refunded.'
                    : 'This request has already been rejected.';
                refundAdminNote.disabled = true;
                return;
            }

            if (state === 'awaiting_return') {
                refundApproveBtn.classList.remove('hidden');
                refundApproveBtn.textContent = 'Release refund';
                refundRejectBtn.classList.add('hidden');
                refundRequestReturnBtn.classList.add('hidden');
                refundRejectNotReturnedBtn.classList.remove('hidden');
                if (isCustom) {
                    refundRejectNotReturnedBtn.classList.add('hidden');
                }
                return;
            }

            refundApproveBtn.textContent = isCustom ? 'Approve request' : 'Approve & release refund';
            refundRequestReturnBtn.classList.toggle('hidden', isCustom || !payload.request_return_url);
            refundRejectNotReturnedBtn.classList.add('hidden');
        };

        const openRefundModal = function (payload) {
            currentRefundPayload = payload || {};
            document.getElementById('poRefundId').textContent = 'Refund ' + (currentRefundPayload.refund_id || '#');
            const badge = document.getElementById('poRefundStatusBadge');
            badge.textContent = currentRefundPayload.status_label || 'Under review';
            badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold mt-2 ' + statusClass(currentRefundPayload.status_state || 'under_review');
            document.getElementById('poRefundCustomer').textContent = currentRefundPayload.customer || 'Customer';
            document.getElementById('poRefundOrder').textContent = currentRefundPayload.order_ref || '-';
            document.getElementById('poRefundType').textContent = currentRefundPayload.refund_type || 'Refund';
            document.getElementById('poRefundReason').textContent = currentRefundPayload.reason || 'No reason provided.';
            document.getElementById('poRefundAmount').textContent = 'P' + (currentRefundPayload.amount || '0.00');
            document.getElementById('poRefundTo').textContent = currentRefundPayload.refund_to || 'N/A';
            document.getElementById('poRefundCustomerNote').textContent = currentRefundPayload.customer_note || 'No customer note provided.';
            document.getElementById('poRefundOpenOrderBtn').href = currentRefundPayload.order_show_url || '#';
            refundAdminNote.value = currentRefundPayload.admin_note || '';
            refundErrorEl.className = 'hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700';
            renderEvidence(currentRefundPayload.evidence || []);
            setRefundMode(currentRefundPayload);
            refundModal.classList.remove('hidden');
            refundModal.classList.add('flex');
        };

        const closeRefundModal = function () {
            refundModal.classList.add('hidden');
            refundModal.classList.remove('flex');
        };

        refundOpenButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activeRefundTrigger = button;
                try {
                    openRefundModal(JSON.parse(button.getAttribute('data-refund') || '{}'));
                } catch (error) {
                    openRefundModal({});
                }
            });
        });

        const setRefundButtonsDisabled = function (disabled) {
            [refundApproveBtn, refundRequestReturnBtn, refundRejectBtn, refundRejectNotReturnedBtn].forEach(function (button) {
                if (!button) return;
                button.disabled = disabled;
                button.classList.toggle('opacity-60', disabled);
                button.classList.toggle('cursor-not-allowed', disabled);
            });
        };

        const resolveActionUrl = function (url) {
            if (!url) return '';

            try {
                const parsed = new URL(url, window.location.origin);
                if (authToken && !parsed.searchParams.has('auth_token')) {
                    parsed.searchParams.set('auth_token', authToken);
                }
                return parsed.toString();
            } catch (error) {
                return url;
            }
        };

        const submitRefundAction = async function (url, requireAdminNote) {
            if (!url) return;

            const note = (refundAdminNote.value || '').trim();
            if (requireAdminNote && !note) {
                refundErrorEl.classList.remove('hidden');
                refundErrorEl.textContent = 'Admin note is required for this action.';
                return;
            }

            refundErrorEl.classList.add('hidden');
            setRefundButtonsDisabled(true);

            const requestBody = { admin_note: note };
            if (authToken) {
                requestBody.auth_token = authToken;
            }

            try {
                const response = await fetch(resolveActionUrl(url), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(requestBody),
                });

                const contentType = response.headers.get('content-type') || '';
                let result = {};

                if (contentType.includes('application/json')) {
                    result = await response.json();
                }

                if (!response.ok || result.success === false) {
                    const firstError = result.errors ? Object.values(result.errors)[0] : null;
                    const firstErrorMessage = Array.isArray(firstError) ? firstError[0] : null;
                    throw new Error(firstErrorMessage || result.message || 'Unable to process this action right now.');
                }

                if (result.refund && typeof result.refund === 'object') {
                    currentRefundPayload = Object.assign({}, currentRefundPayload || {}, result.refund);
                    if (activeRefundTrigger) {
                        activeRefundTrigger.setAttribute('data-refund', JSON.stringify(currentRefundPayload));
                    }
                    openRefundModal(currentRefundPayload);
                }

                if (result.message) {
                    refundErrorEl.className = 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700';
                    refundErrorEl.classList.remove('hidden');
                    refundErrorEl.textContent = result.message;
                }
            } catch (error) {
                refundErrorEl.className = 'rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700';
                refundErrorEl.classList.remove('hidden');
                refundErrorEl.textContent = error && error.message ? error.message : 'Unable to process this action right now.';
            } finally {
                setRefundButtonsDisabled(false);
            }
        };

        refundApproveBtn.addEventListener('click', function () {
            void submitRefundAction(currentRefundPayload?.approve_release_url, false);
        });
        refundRequestReturnBtn.addEventListener('click', function () {
            void submitRefundAction(currentRefundPayload?.request_return_url, false);
        });
        refundRejectBtn.addEventListener('click', function () {
            void submitRefundAction(currentRefundPayload?.reject_url, true);
        });
        refundRejectNotReturnedBtn.addEventListener('click', function () {
            void submitRefundAction(currentRefundPayload?.reject_not_returned_url, true);
        });

        refundCloseBtn.addEventListener('click', closeRefundModal);
        refundModal.addEventListener('click', function (event) {
            if (event.target === refundModal) {
                closeRefundModal();
            }
        });

        evidencePreviewCloseBtn.addEventListener('click', closeEvidencePreview);
        evidencePreviewModal.addEventListener('click', function (event) {
            if (event.target === evidencePreviewModal) {
                closeEvidencePreview();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeCancelModal();
                closeRefundModal();
                closeEvidencePreview();
            }
        });
    })();
</script>

<!-- Print Date-Range Modal -->
<div id="printDateModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Print Report</h3>
            <button id="printDateModalClose" type="button" class="w-9 h-9 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 text-xl leading-none">×</button>
        </div>
        <p class="text-sm text-gray-500">Select a date range to include in the report. Leave blank to include all dates.</p>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date From</label>
                <input id="printDateFrom" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date To</label>
                <input id="printDateTo" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] text-sm">
            </div>
        </div>
        <div class="flex gap-3 pt-1">
            <button id="printDateConfirm" type="button"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-white font-semibold text-sm transition-colors"
                style="background:#800000;" onmouseover="this.style.background='#600000'" onmouseout="this.style.background='#800000'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Open Print Preview
            </button>
            <button id="printDateCancel" type="button"
                class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const printBtn         = document.getElementById('printReportBtn');
    const printModal       = document.getElementById('printDateModal');
    const printModalClose  = document.getElementById('printDateModalClose');
    const printDateCancel  = document.getElementById('printDateCancel');
    const printDateConfirm = document.getElementById('printDateConfirm');
    const dateFrom         = document.getElementById('printDateFrom');
    const dateTo           = document.getElementById('printDateTo');

    const printBaseUrl = '{{ route("admin.orders.post_order_requests.print") }}';
    const authToken    = new URLSearchParams(window.location.search).get('auth_token') || '';
    const currentType  = '{{ $typeFilter }}';
    const currentStatus = '{{ $statusFilter }}';

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            printModal.classList.remove('hidden');
            printModal.classList.add('flex');
        });
    }

    const closePrintModal = function () {
        printModal.classList.add('hidden');
        printModal.classList.remove('flex');
    };

    if (printModalClose) printModalClose.addEventListener('click', closePrintModal);
    if (printDateCancel) printDateCancel.addEventListener('click', closePrintModal);
    printModal.addEventListener('click', function (e) {
        if (e.target === printModal) closePrintModal();
    });

    if (printDateConfirm) {
        printDateConfirm.addEventListener('click', function () {
            const params = new URLSearchParams();
            if (authToken) params.set('auth_token', authToken);
            if (currentType && currentType !== 'all') params.set('type', currentType);
            if (currentStatus) params.set('status', currentStatus);
            if (dateFrom.value) params.set('date_from', dateFrom.value);
            if (dateTo.value) params.set('date_to', dateTo.value);
            const url = printBaseUrl + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
            closePrintModal();
        });
    }
})();
</script>
@endsection
