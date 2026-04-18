@extends('layouts.admin')

@section('title', 'Refund Requests')

@push('styles')
<style>
    .refund-stat-card {
        @apply rounded-xl p-4 bg-white border border-gray-200 shadow-sm;
    }

    .refund-filter-chip {
        @apply px-4 py-2 rounded-lg border text-sm font-semibold transition-colors duration-200;
    }

    .refund-filter-chip-active {
        @apply bg-[#800000] text-white border-[#800000];
    }

    .refund-filter-chip-idle {
        @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-50;
    }

    .refund-status-badge {
        @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold;
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
        @apply w-2.5 h-2.5 rounded-full mt-1;
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
            <a href="{{ route('admin.regular.index') }}" class="px-4 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 border border-gray-300 font-semibold text-sm w-fit">
                Back to Orders
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="refund-stat-card">
                <p class="text-2xl font-bold text-blue-700">{{ $stats['under_review'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Under review</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-2xl font-bold text-amber-700">{{ $stats['awaiting_return'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Awaiting return</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-2xl font-bold text-green-700">{{ $stats['refunded'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Refunded</p>
            </div>
            <div class="refund-stat-card">
                <p class="text-2xl font-bold text-rose-700">{{ $stats['rejected'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Rejected</p>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-3">
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
            <div class="flex gap-2">
                <input type="hidden" name="status" value="{{ $activeFilter }}">
                <input type="text" name="search" value="{{ $searchValue }}" placeholder="Search..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]">
                <button type="submit" class="px-4 py-2 bg-[#800000] text-white rounded-lg font-semibold hover:bg-[#600000]">Search</button>
                <a href="{{ route('admin.orders.refund_requests.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Reset</a>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
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
                    <tbody class="divide-y divide-gray-100">
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

                                $displayAmount = (float) ($refundRequest->refund_amount ?? $refundRequest->approved_amount ?? $refundRequest->recommended_refund_amount ?? $order->total_amount ?? $order->total ?? 0);
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
                                    $evidencePreviews[] = [
                                        'url' => $url,
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
                                <td class="px-4 py-3 font-semibold text-gray-900">#{{ $refundRef }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $customerName }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', (string) ($refundRequest->reason ?? 'Refund'))) }}</td>
                                <td class="px-4 py-3 text-gray-900 font-semibold">₱{{ number_format($displayAmount, 2) }}</td>
                                <td class="px-4 py-3"><span class="refund-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="px-4 py-3">
                                    <button type="button" class="refund-review-btn px-4 py-2 border border-gray-300 rounded-xl font-semibold hover:bg-gray-100" data-refund='@json($modalPayload)'>
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
    <div class="w-full max-w-5xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div class="lg:col-span-2 p-5 border-r border-gray-200">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h2 id="modalRefundId" class="text-2xl font-bold text-gray-900">Refund #</h2>
                        <span id="modalStatusBadge" class="refund-status-badge mt-2"></span>
                    </div>
                    <button id="closeRefundReviewModal" type="button" class="w-10 h-10 border border-gray-300 rounded-xl text-xl text-gray-700 hover:bg-gray-100">×</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Customer</p><p id="modalCustomer" class="font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Order</p><p id="modalOrder" class="font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Refund type</p><p id="modalRefundType" class="font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Reason</p><p id="modalReason" class="font-semibold text-gray-900"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Amount</p><p id="modalAmount" class="font-semibold text-blue-700"></p></div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3"><p class="text-xs text-gray-500">Refund to</p><p id="modalRefundTo" class="font-semibold text-gray-900"></p></div>
                </div>

                <div class="mb-3">
                    <p class="text-xs text-gray-500">Customer note</p>
                    <p id="modalCustomerNote" class="text-sm text-gray-800"></p>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-1">Photo proof</p>
                    <div id="modalEvidenceWrap" class="min-h-[72px] rounded-lg border border-gray-200 p-2 bg-gray-50 text-center text-gray-500 text-sm"></div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                    <p class="text-sm font-semibold text-gray-800 mb-2">Refund timeline</p>
                    <div id="modalTimeline" class="space-y-2"></div>
                </div>
            </div>

            <div class="p-5 bg-gray-50">
                <div id="modalActionSection" class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-800">Choose an action</h3>
                    <p class="text-xs text-gray-600">Review the customer's claim and photo proof before deciding.</p>

                    <button type="button" id="modalApproveReleaseBtn" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 font-semibold">Approve & release refund</button>
                    <button type="button" id="modalRequestReturnBtn" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 font-semibold">Request item return</button>
                    <button type="button" id="modalRejectBtn" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 font-semibold">Reject request</button>

                    <div id="modalAwaitingHint" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Waiting for customer to return the item. Once received and inspected, release the refund.
                    </div>

                    <button type="button" id="modalAwaitingReleaseBtn" class="hidden w-full px-4 py-3 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 font-semibold">Release refund</button>
                    <button type="button" id="modalRejectNotReturnedBtn" class="hidden w-full px-4 py-3 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 font-semibold">Reject (item not returned)</button>

                    <div class="border-t border-gray-200 pt-3">
                        <label for="modalAdminNote" class="text-sm font-semibold text-gray-700">Admin note</label>
                        <textarea id="modalAdminNote" rows="3" class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g. Photo verified, refund approved..."></textarea>
                        <p id="modalActionError" class="hidden mt-2 text-xs text-red-700"></p>
                    </div>

                    <div id="modalReadonlyMessage" class="hidden rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 text-center"></div>
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
        const evidenceWrapEl = document.getElementById('modalEvidenceWrap');
        const timelineEl = document.getElementById('modalTimeline');

        const actionSection = document.getElementById('modalActionSection');
        const actionError = document.getElementById('modalActionError');
        const adminNoteEl = document.getElementById('modalAdminNote');
        const readonlyMessageEl = document.getElementById('modalReadonlyMessage');

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
                items.push(['Under review', 'Admin is checking', 'active']);
                items.push(['Decision', 'Approve, return or reject', 'pending']);
                items.push(['Refund released', 'Credited to payment', 'pending']);
            } else if (state === 'awaiting_return') {
                items.push(['Request submitted', 'Received and queued', 'done']);
                items.push(['Under review', 'Checked by admin', 'done']);
                items.push(['Awaiting return', 'Customer shipping item back', 'active']);
                items.push(['Refund released', 'After item inspection', 'pending']);
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
                const color = item[2] === 'done' ? 'bg-green-700' : (item[2] === 'active' ? 'bg-blue-700' : 'bg-gray-300');
                const titleClass = item[2] === 'active' ? 'text-blue-700 font-semibold' : 'text-gray-800 font-semibold';
                return '<div class="flex items-start gap-2">'
                    + '<span class="refund-timeline-dot ' + color + '"></span>'
                    + '<div><p class="' + titleClass + '">' + item[0] + '</p>' + (item[1] ? '<p class="text-xs text-gray-600">' + item[1] + '</p>' : '') + '</div>'
                    + '</div>';
            }).join('');
        }

        function renderEvidence(evidence) {
            if (!Array.isArray(evidence) || evidence.length === 0) {
                evidenceWrapEl.textContent = 'No photo in demo';
                return;
            }

            const html = evidence.map(function (item) {
                if (item.is_image) {
                    return '<a href="' + item.url + '" target="_blank" class="inline-block mr-2 mb-2"><img src="' + item.url + '" class="w-20 h-20 object-cover rounded-lg border border-gray-200" alt="Evidence"></a>';
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
                adminNoteEl.focus();
                return;
            }

            actionError.classList.add('hidden');
            actionError.textContent = '';

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
            customerNoteEl.textContent = payload.customer_note || 'No customer note in request.';

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
                readonlyMessageEl.classList.remove('hidden');
                readonlyMessageEl.textContent = '✓ Refund released to customer.';
            }

            if (payload.status_state === 'rejected') {
                [approveReleaseBtn, requestReturnBtn, rejectBtn, awaitingReleaseBtn, rejectNotReturnedBtn].forEach(function (btn) {
                    btn.classList.add('hidden');
                });
                awaitingHint.classList.add('hidden');
                readonlyMessageEl.classList.remove('hidden');
                readonlyMessageEl.textContent = '✕ Request rejected.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
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
