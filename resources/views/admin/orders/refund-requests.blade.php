@extends('layouts.admin')

@section('title', 'Refund Requests')

@push('styles')
<style>
    .refund-stat-card {
        @apply rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 bg-white border border-gray-200;
        border-left: 4px solid #800000;
    }

    .refund-filter-section {
        @apply bg-white rounded-xl shadow-lg p-6 border border-gray-200;
    }

    .refund-table-wrap {
        @apply bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden;
    }

    .refund-filter-chip {
        @apply px-4 py-2 rounded-lg border text-sm font-semibold transition-all duration-200;
    }

    .refund-filter-chip-active {
        @apply bg-[#800000] text-white border-[#800000];
    }

    .refund-filter-chip-idle {
        @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-50;
    }

    .refund-status-badge {
        @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold;
    }

    .refund-status-under-review {
        @apply bg-blue-100 text-blue-700;
    }

    .refund-status-awaiting-return {
        @apply bg-amber-100 text-amber-700;
    }

    .refund-status-refunded {
        @apply bg-green-100 text-green-700;
    }

    .refund-status-rejected {
        @apply bg-rose-100 text-rose-700;
    }

    .refund-timeline-dot {
        @apply inline-block w-2.5 h-2.5 rounded-full mt-1 flex-shrink-0;
    }

    .refund-input {
        @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y;
    }
</style>
@endpush

@section('content')
@php
    $activeFilter = strtolower((string) ($statusFilter ?? 'all'));
    $searchValue = (string) request('search', '');
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Refund Requests</h1>
                <p class="text-gray-600">Review and resolve customer refund cases.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</span>
                <a href="{{ route('admin.regular.index') }}" class="px-4 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 border border-gray-300 font-semibold text-sm">
                    Back to Orders
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="refund-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Under Review</p>
                <p class="text-3xl font-bold text-blue-700">{{ $stats['under_review'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Needs admin decision</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Awaiting Return</p>
                <p class="text-3xl font-bold text-amber-700">{{ $stats['awaiting_return'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Waiting item shipment</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Refunded</p>
                <p class="text-3xl font-bold text-green-700">{{ $stats['refunded'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Successfully paid out</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Rejected</p>
                <p class="text-3xl font-bold text-rose-700">{{ $stats['rejected'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Closed without refund</p>
            </div>
        </div>

        <form method="GET" class="refund-filter-section space-y-4">
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        'all' => 'All',
                        'under_review' => 'Under review',
                        'awaiting_return' => 'Awaiting return',
                        'refunded' => 'Refunded',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                @foreach($filters as $filterValue => $filterLabel)
                    <button type="submit" name="status" value="{{ $filterValue }}" class="refund-filter-chip {{ $activeFilter === $filterValue ? 'refund-filter-chip-active' : 'refund-filter-chip-idle' }}">
                        {{ $filterLabel }}
                    </button>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $activeFilter }}">
                <input type="text" name="search" value="{{ $searchValue }}" placeholder="Search refund ID, order or customer..." class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">
                <button type="submit" class="px-5 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] font-semibold">Search</button>
                <a href="{{ route('admin.orders.refund_requests.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-center">Reset</a>
            </div>
        </form>

        <div class="refund-table-wrap">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Refund ID</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Customer</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Type</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($refundRequests as $refundRequest)
                            @php
                                $order = $refundRequest->order;
                                $customerName = $refundRequest->user->name ?? $order->user->name ?? $order->customer_name ?? 'Customer';
                                $workflow = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));

                                $statusState = 'under_review';
                                if (in_array($workflow, ['awaiting_return_shipment', 'return_in_transit'], true)) {
                                    $statusState = 'awaiting_return';
                                } elseif (in_array($workflow, ['processed', 'pending_payout', 'return_received', 'approved'], true) || strtolower((string) ($refundRequest->payout_status ?? '')) === 'completed') {
                                    $statusState = 'refunded';
                                } elseif ($workflow === 'rejected' || strtoupper((string) ($refundRequest->final_decision ?? '')) === 'REJECT') {
                                    $statusState = 'rejected';
                                }

                                $statusLabel = match ($statusState) {
                                    'awaiting_return' => 'Awaiting return',
                                    'refunded' => 'Refunded',
                                    'rejected' => 'Rejected',
                                    default => 'Under review',
                                };

                                $statusClass = match ($statusState) {
                                    'awaiting_return' => 'refund-status-awaiting-return',
                                    'refunded' => 'refund-status-refunded',
                                    'rejected' => 'refund-status-rejected',
                                    default => 'refund-status-under-review',
                                };

                                $rawRefundAmount = $refundRequest->refund_amount;
                                $rawApprovedAmount = $refundRequest->approved_amount;
                                $rawRecommendedAmount = $refundRequest->recommended_refund_amount;
                                $rawOrderAmount = $order->total_amount ?? $order->total;

                                $displayAmount = 0.0;
                                if ($rawApprovedAmount !== null && (float) $rawApprovedAmount > 0) {
                                    $displayAmount = (float) $rawApprovedAmount;
                                } elseif ($rawRefundAmount !== null && (float) $rawRefundAmount > 0) {
                                    $displayAmount = (float) $rawRefundAmount;
                                } elseif ($rawRecommendedAmount !== null && (float) $rawRecommendedAmount > 0) {
                                    $displayAmount = (float) $rawRecommendedAmount;
                                } elseif ($rawOrderAmount !== null && (float) $rawOrderAmount > 0) {
                                    $displayAmount = (float) $rawOrderAmount;
                                } elseif ($rawRefundAmount !== null) {
                                    $displayAmount = (float) $rawRefundAmount;
                                } elseif ($rawApprovedAmount !== null) {
                                    $displayAmount = (float) $rawApprovedAmount;
                                } elseif ($rawRecommendedAmount !== null) {
                                    $displayAmount = (float) $rawRecommendedAmount;
                                } elseif ($rawOrderAmount !== null) {
                                    $displayAmount = (float) $rawOrderAmount;
                                }
                                $refundRef = (string) ($refundRequest->refund_reference ?? ('RF-' . str_pad((string) $refundRequest->id, 4, '0', STR_PAD_LEFT)));

                                $rawEvidence = $refundRequest->evidence_paths;
                                if (is_string($rawEvidence) && $rawEvidence !== '') {
                                    $decodedEvidence = json_decode($rawEvidence, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedEvidence)) {
                                        $rawEvidence = $decodedEvidence;
                                    }
                                }
                                $evidenceList = array_values(array_filter(is_array($rawEvidence) ? $rawEvidence : [], fn ($value) => $value !== null && $value !== ''));

                                $evidencePreviews = [];
                                foreach ($evidenceList as $evidencePath) {
                                    $ext = strtolower(pathinfo(parse_url((string) $evidencePath, PHP_URL_PATH) ?? (string) $evidencePath, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
                                    $isVideo = in_array($ext, ['mp4', 'mov', 'webm'], true);
                                    $url = route('admin.orders.refund_evidence.view', ['refundRequest' => $refundRequest->id, 'index' => count($evidencePreviews)]);

                                    $rawEvidencePath = str_replace('\\', '/', ltrim((string) $evidencePath, '/'));
                                    $publicEvidencePath = ltrim(str_replace(['public/', 'storage/'], '', $rawEvidencePath), '/');
                                    $fallbackEvidenceUrl = asset('storage/' . $publicEvidencePath);
                                    $previewUrl = (str_starts_with((string) $evidencePath, 'http://') || str_starts_with((string) $evidencePath, 'https://'))
                                        ? (string) $evidencePath
                                        : $fallbackEvidenceUrl;

                                    $evidencePreviews[] = [
                                        'url' => $url,
                                        'open_url' => $previewUrl,
                                        'preview_url' => $previewUrl,
                                        'fallback_url' => $fallbackEvidenceUrl,
                                        'is_image' => $isImage,
                                        'is_video' => $isVideo,
                                    ];
                                }

                                $modalPayload = [
                                    'refund_id' => $refundRef,
                                    'refund_request_id' => $refundRequest->id,
                                    'status_state' => $statusState,
                                    'status_label' => $statusLabel,
                                    'customer' => $customerName,
                                    'order_ref' => $order->order_ref ?? ('#' . $order->id),
                                    'order_show_url' => route('admin.orders.show', $order),
                                    'refund_type' => ucfirst(str_replace('_', ' ', (string) ($refundRequest->reason ?? 'Refund'))),
                                    'reason' => trim((string) ($refundRequest->comment ?? $refundRequest->details ?? '')),
                                    'amount' => number_format($displayAmount, 2),
                                    'refund_to' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'GCash'))),
                                    'customer_note' => trim((string) ($refundRequest->comment ?? $refundRequest->details ?? '')),
                                    'requested_at' => optional($refundRequest->requested_at)->format('M d, h:i A') ?? $refundRequest->created_at->format('M d, h:i A'),
                                    'admin_note' => trim((string) ($refundRequest->admin_note ?? '')),
                                    'evidence' => $evidencePreviews,
                                    'approve_release_url' => route('admin.orders.refund_requests.quick_release', $refundRequest),
                                    'request_return_url' => route('admin.orders.refund_requests.request_return', $refundRequest),
                                    'reject_url' => route('admin.orders.refund_requests.reject', $refundRequest),
                                    'reject_not_returned_url' => route('admin.orders.refund_requests.reject_not_returned', $refundRequest),
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-[#800000]">#{{ $refundRef }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $customerName }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', (string) ($refundRequest->reason ?? 'Refund'))) }}</td>
                                <td class="px-4 py-3 text-gray-900 font-semibold">₱{{ number_format($displayAmount, 2) }}</td>
                                <td class="px-4 py-3"><span class="refund-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="px-4 py-3">
                                    <button type="button" class="refund-review-btn px-4 py-2 border border-gray-300 rounded-lg font-semibold hover:bg-gray-100" data-refund='@json($modalPayload)'>
                                        Review
                                        <span class="ml-1">↗</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No refund requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($refundRequests, 'links'))
            <div>
                {{ $refundRequests->links() }}
            </div>
        @endif
    </div>
</div>

<div id="refundReviewModal" class="fixed inset-0 bg-black/55 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div class="lg:col-span-2 p-3.5 border-r border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 id="modalRefundId" class="text-xl font-bold text-gray-900">Refund #</h2>
                        <span id="modalStatusBadge" class="refund-status-badge mt-2"></span>
                    </div>
                    <button id="closeRefundReviewModal" type="button" class="w-11 h-11 border border-gray-300 rounded-xl text-xl text-gray-700 hover:bg-gray-100" aria-label="Close modal">×</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Customer</p><p id="modalCustomer" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Order</p><p id="modalOrder" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Refund type</p><p id="modalRefundType" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Reason</p><p id="modalReason" class="text-base font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Amount</p><p id="modalAmount" class="text-base font-semibold text-[#800000]"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3"><p class="text-xs text-gray-500">Refund to</p><p id="modalRefundTo" class="text-base font-semibold text-gray-900"></p></div>
                </div>

                <div id="modalCustomerNoteWrap" class="bg-gray-50 rounded-xl p-3 border border-gray-100 mb-3">
                    <p class="text-xs text-gray-500">Customer note</p>
                    <p id="modalCustomerNote" class="text-base font-semibold text-gray-900"></p>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Photo proof</p>
                    <div id="modalEvidenceWrap" class="min-h-[72px] rounded-xl border border-gray-200 p-3 bg-gray-50 text-center text-gray-500 text-sm"></div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                    <p class="text-sm font-semibold text-gray-800 mb-2">Refund timeline</p>
                    <div id="modalTimeline" class="space-y-2"></div>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50">
                <div id="modalActionSection" class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-800">Choose an action</h3>
                    <p class="text-xs text-gray-600">Review the customer's claim and photo proof before deciding.</p>

                    <div class="space-y-2">
                        <button type="button" id="modalApproveReleaseBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Approve & release refund</button>
                        <button type="button" id="modalRequestReturnBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Request item return</button>
                        <button type="button" id="modalRejectBtn" class="w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Reject request</button>
                    </div>

                    <div id="modalAwaitingHint" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Waiting for customer to return the item. Once received and inspected, release the refund.
                    </div>

                    <button type="button" id="modalAwaitingReleaseBtn" class="hidden w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Release refund</button>
                    <button type="button" id="modalRejectNotReturnedBtn" class="hidden w-full min-h-[42px] px-4 py-2 border border-gray-400 rounded-lg bg-white text-gray-800 hover:bg-gray-100 font-semibold leading-tight text-[15px]">Reject (item not returned)</button>

                    <div id="modalAdminNoteSection" class="border-t border-gray-200 pt-3">
                        <label for="modalAdminNote" class="text-sm font-semibold text-gray-700">Admin note</label>
                        <textarea id="modalAdminNote" rows="3" class="refund-input mt-2" placeholder="e.g. Photo verified, refund approved..."></textarea>
                        <p id="modalActionError" class="hidden mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700"></p>
                    </div>

                    <div id="modalReadonlyMessage" class="hidden rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 text-center"></div>

                    <div class="flex justify-end pt-1">
                        <a id="modalOpenOrderBtn" href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Open order details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('refundReviewModal');
        const closeBtn = document.getElementById('closeRefundReviewModal');
        const reviewButtons = document.querySelectorAll('.refund-review-btn');

        const refundIdEl = document.getElementById('modalRefundId');
        const statusBadgeEl = document.getElementById('modalStatusBadge');
        const customerEl = document.getElementById('modalCustomer');
        const orderEl = document.getElementById('modalOrder');
        const refundTypeEl = document.getElementById('modalRefundType');
        const reasonEl = document.getElementById('modalReason');
        const amountEl = document.getElementById('modalAmount');
        const refundToEl = document.getElementById('modalRefundTo');
        const customerNoteEl = document.getElementById('modalCustomerNote');
        const customerNoteWrapEl = document.getElementById('modalCustomerNoteWrap');
        const evidenceWrapEl = document.getElementById('modalEvidenceWrap');
        const timelineEl = document.getElementById('modalTimeline');

        const actionSection = document.getElementById('modalActionSection');
        const actionError = document.getElementById('modalActionError');
        const adminNoteEl = document.getElementById('modalAdminNote');
        const adminNoteSectionEl = document.getElementById('modalAdminNoteSection');
        const readonlyMessageEl = document.getElementById('modalReadonlyMessage');
        const openOrderBtn = document.getElementById('modalOpenOrderBtn');

        const approveReleaseBtn = document.getElementById('modalApproveReleaseBtn');
        const requestReturnBtn = document.getElementById('modalRequestReturnBtn');
        const rejectBtn = document.getElementById('modalRejectBtn');
        const awaitingHint = document.getElementById('modalAwaitingHint');
        const awaitingReleaseBtn = document.getElementById('modalAwaitingReleaseBtn');
        const rejectNotReturnedBtn = document.getElementById('modalRejectNotReturnedBtn');

        let payload = null;

        function resetActions() {
            actionError.classList.add('hidden');
            actionError.textContent = '';
            adminNoteEl.value = payload?.admin_note || '';
            adminNoteSectionEl.classList.remove('hidden');
            readonlyMessageEl.classList.add('hidden');
            readonlyMessageEl.textContent = '';

            [approveReleaseBtn, requestReturnBtn, rejectBtn].forEach(function (btn) {
                btn.classList.remove('hidden');
                btn.disabled = false;
            });
            awaitingHint.classList.add('hidden');
            awaitingReleaseBtn.classList.add('hidden');
            rejectNotReturnedBtn.classList.add('hidden');
        }

        function setBadge(state, label) {
            statusBadgeEl.className = 'refund-status-badge';
            if (state === 'under_review') {
                statusBadgeEl.classList.add('refund-status-under-review');
            } else if (state === 'awaiting_return') {
                statusBadgeEl.classList.add('refund-status-awaiting-return');
            } else if (state === 'refunded') {
                statusBadgeEl.classList.add('refund-status-refunded');
            } else {
                statusBadgeEl.classList.add('refund-status-rejected');
            }
            statusBadgeEl.textContent = label;
        }

        function renderTimeline(state) {
            const items = [];
            if (state === 'under_review') {
                items.push(['Request submitted', 'Received and queued', 'done']);
                items.push(['Under review', '', 'done']);
                items.push(['Approved', '', 'pending']);
                items.push(['Refund released', 'Credited to payment', 'pending']);
            } else if (state === 'awaiting_return') {
                items.push(['Request submitted', 'Received and queued', 'done']);
                items.push(['Under review', '', 'done']);
                items.push(['Approved', '', 'done']);
                items.push(['Refund released', 'Waiting item return and inspection', 'pending']);
            } else if (state === 'refunded') {
                items.push(['Request submitted', '', 'done']);
                items.push(['Under review', '', 'done']);
                items.push(['Approved', '', 'done']);
                items.push(['Refund released', 'Credited to payment', 'done']);
            } else {
                items.push(['Request submitted', '', 'done']);
                items.push(['Under review', '', 'done']);
                items.push(['Rejected', 'Did not meet requirements', 'done']);
            }

            timelineEl.innerHTML = items.map(function (item) {
                const dotColor = item[2] === 'done' ? '#800000' : '#d1d5db';
                const titleClass = item[2] === 'done' ? 'text-gray-900 font-semibold' : 'text-gray-500 font-semibold';
                return '<div class="flex items-start gap-2">'
                    + '<span class="refund-timeline-dot" style="background-color:' + dotColor + ';"></span>'
                    + '<div><p class="' + titleClass + '">' + item[0] + '</p>' + (item[1] ? '<p class="text-xs text-gray-600 leading-tight">' + item[1] + '</p>' : '') + '</div>'
                    + '</div>';
            }).join('');
        }

        function renderEvidence(evidence) {
            if (!Array.isArray(evidence) || evidence.length === 0) {
                evidenceWrapEl.textContent = 'No photo provided.';
                return;
            }

            const html = evidence.map(function (item) {
                if (item.is_image) {
                    const imgSrc = item.preview_url || item.url;
                    const fallbackSrc = item.fallback_url || item.url;
                    const openUrl = item.open_url || item.preview_url || item.url;
                    return '<a href="' + openUrl + '" target="_blank" class="inline-block mr-2 mb-2">'
                        + '<img src="' + imgSrc + '" onerror="if(this.dataset.err){this.onerror=null;this.src=\'' + fallbackSrc + '\';return;}this.dataset.err=\'1\';this.src=\'' + item.url + '\';" class="w-20 h-20 object-cover rounded-lg border border-gray-200" alt="Evidence">'
                        + '</a>';
                }
                if (item.is_video) {
                    return '<a href="' + item.url + '" target="_blank" class="inline-flex items-center px-3 py-2 mr-2 mb-2 rounded-lg border border-blue-300 bg-blue-50 text-blue-700 text-xs">View video</a>';
                }
                return '<a href="' + item.url + '" target="_blank" class="inline-flex items-center px-3 py-2 mr-2 mb-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-xs">View file</a>';
            }).join('');

            evidenceWrapEl.innerHTML = html;
        }

        function postTo(url, requireNote) {
            const note = (adminNoteEl.value || '').trim();
            if (requireNote && note === '') {
                actionError.classList.remove('hidden');
                actionError.textContent = 'Admin note is required for this action.';
                adminNoteEl.classList.add('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
                adminNoteEl.focus();
                return;
            }

            actionError.classList.add('hidden');
            actionError.textContent = '';
            adminNoteEl.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            form.appendChild(token);

            const noteInput = document.createElement('input');
            noteInput.type = 'hidden';
            noteInput.name = 'admin_note';
            noteInput.value = note;
            form.appendChild(noteInput);

            document.body.appendChild(form);
            form.submit();
        }

        function openModal(data) {
            payload = data || {};
            refundIdEl.textContent = 'Refund #' + (payload.refund_id || payload.refund_request_id || '-');
            setBadge(payload.status_state, payload.status_label || 'Under review');
            customerEl.textContent = payload.customer || 'N/A';
            orderEl.textContent = payload.order_ref || 'N/A';
            refundTypeEl.textContent = payload.refund_type || 'N/A';
            reasonEl.textContent = payload.reason || 'N/A';
            amountEl.textContent = '₱' + (payload.amount || '0.00');
            refundToEl.textContent = payload.refund_to || 'N/A';
            const reasonText = String(payload.reason || '').replace(/\s+/g, ' ').trim().toLowerCase();
            const customerNoteText = String(payload.customer_note || '').replace(/\s+/g, ' ').trim();
            const customerNoteNormalized = customerNoteText.toLowerCase();
            if (customerNoteText !== '' && customerNoteNormalized !== reasonText) {
                customerNoteWrapEl.classList.remove('hidden');
                customerNoteEl.textContent = customerNoteText;
            } else {
                customerNoteWrapEl.classList.add('hidden');
                customerNoteEl.textContent = '';
            }
            openOrderBtn.href = payload.order_show_url || '#';

            renderEvidence(payload.evidence || []);
            renderTimeline(payload.status_state || 'under_review');
            resetActions();

            if (payload.status_state === 'awaiting_return') {
                approveReleaseBtn.classList.add('hidden');
                requestReturnBtn.classList.add('hidden');
                rejectBtn.classList.add('hidden');
                awaitingHint.classList.remove('hidden');
                awaitingReleaseBtn.classList.remove('hidden');
                rejectNotReturnedBtn.classList.remove('hidden');
            }

            if (payload.status_state === 'refunded') {
                [approveReleaseBtn, requestReturnBtn, rejectBtn, awaitingReleaseBtn, rejectNotReturnedBtn].forEach(function (btn) {
                    btn.classList.add('hidden');
                });
                awaitingHint.classList.add('hidden');
                adminNoteSectionEl.classList.add('hidden');
                readonlyMessageEl.classList.remove('hidden');
                readonlyMessageEl.textContent = '✓ Refund released to customer.';
            }

            if (payload.status_state === 'rejected') {
                [approveReleaseBtn, requestReturnBtn, rejectBtn, awaitingReleaseBtn, rejectNotReturnedBtn].forEach(function (btn) {
                    btn.classList.add('hidden');
                });
                awaitingHint.classList.add('hidden');
                adminNoteSectionEl.classList.add('hidden');
                readonlyMessageEl.classList.remove('hidden');
                readonlyMessageEl.textContent = '✕ Request rejected.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            actionError.classList.add('hidden');
            actionError.textContent = '';
            adminNoteEl.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
        }

        reviewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                let data = {};
                try {
                    data = JSON.parse(button.getAttribute('data-refund') || '{}');
                } catch (error) {
                    data = {};
                }
                openModal(data);
            });
        });

        approveReleaseBtn.addEventListener('click', function () {
            postTo(payload.approve_release_url, false);
        });

        requestReturnBtn.addEventListener('click', function () {
            postTo(payload.request_return_url, false);
        });

        rejectBtn.addEventListener('click', function () {
            postTo(payload.reject_url, true);
        });

        awaitingReleaseBtn.addEventListener('click', function () {
            postTo(payload.approve_release_url, false);
        });

        rejectNotReturnedBtn.addEventListener('click', function () {
            postTo(payload.reject_not_returned_url, true);
        });

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    })();
</script>
@endsection
