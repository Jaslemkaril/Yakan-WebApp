@extends('layouts.admin')

@section('title', 'Cancel Requests')

@push('styles')
<style>
    .cancel-stat-card {
        @apply rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 bg-white border border-gray-200;
        border-left: 4px solid #800000;
    }

    .cancel-filter-section {
        @apply bg-white rounded-xl shadow-lg p-6 border border-gray-200;
    }

    .cancel-table-wrap {
        @apply bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden;
    }

    .cancel-status-chip {
        @apply px-4 py-2 rounded-lg border text-sm font-semibold transition-all duration-200;
    }

    .cancel-status-chip-active {
        @apply text-white;
        background-color: #800000;
        border-color: #800000;
    }

    .cancel-status-chip-idle {
        @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-100;
    }

    .cancel-badge {
        @apply px-3 py-1 rounded-full text-xs font-semibold;
    }

    .cancel-badge-pending {
        @apply bg-amber-100 text-amber-700;
    }

    .cancel-badge-approved {
        @apply bg-green-100 text-green-700;
    }

    .cancel-badge-rejected {
        @apply bg-rose-100 text-rose-700;
    }
</style>
@endpush

@section('content')
@php
    $activeFilter = strtolower((string) ($statusFilter ?? 'all'));
    $searchValue = (string) request('search', '');
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Cancel Requests</h1>
                <p class="text-gray-600">Review and process customer cancellation requests.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</span>
                <a href="{{ route('admin.regular.index') }}" class="px-4 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 border border-gray-300 font-semibold text-sm">
                    Back to Orders
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="cancel-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Total Requests</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalRequests }}</p>
                <p class="text-xs text-gray-500 mt-1">All cancellation requests</p>
            </div>
            <div class="cancel-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Pending</p>
                <p class="text-3xl font-bold text-amber-600">{{ $pendingRequests }}</p>
                <p class="text-xs text-gray-500 mt-1">Awaiting review</p>
            </div>
            <div class="cancel-stat-card">
                <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mb-1">Resolved Today</p>
                <p class="text-3xl font-bold text-green-600">{{ $resolvedToday }}</p>
                <p class="text-xs text-gray-500 mt-1">Approved or rejected</p>
            </div>
        </div>

        <form method="GET" class="cancel-filter-section space-y-4">
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        'all' => 'All',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                @foreach($filters as $filterValue => $filterLabel)
                    <button
                        type="submit"
                        name="status"
                        value="{{ $filterValue }}"
                        class="cancel-status-chip {{ $activeFilter === $filterValue ? 'cancel-status-chip-active' : 'cancel-status-chip-idle' }}"
                    >
                        {{ $filterLabel }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $activeFilter }}">
                <input
                    type="text"
                    name="search"
                    value="{{ $searchValue }}"
                    placeholder="Search order or customer..."
                    class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]"
                >
                <button type="submit" class="px-5 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] font-semibold">Search</button>
                <a href="{{ route('admin.orders.cancel_requests.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-center">Reset</a>
            </div>
        </form>

        <div class="cancel-table-wrap">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Order</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Customer</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Reason</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($orders as $order)
                            @php
                                $refundRequest = $order->refundRequests->sortByDesc('created_at')->first();

                                $statusState = 'pending';
                                if ($order->status === 'cancellation_requested') {
                                    $statusState = 'pending';
                                } elseif (
                                    (string) ($refundRequest->status ?? '') === 'rejected'
                                    || str_contains(strtolower((string) ($order->admin_notes ?? '')), 'rejected')
                                ) {
                                    $statusState = 'rejected';
                                } else {
                                    $statusState = 'approved';
                                }

                                $reason = 'Customer requested cancellation';
                                if (!empty($refundRequest?->details)) {
                                    $reason = (string) $refundRequest->details;
                                }
                                if (preg_match('/Customer cancellation requested:\s*(.+)$/mi', (string) ($order->admin_notes ?? ''), $matches) === 1) {
                                    $reason = trim((string) $matches[1]);
                                }
                                if (preg_match('/Reason:\s*(.+)$/mi', $reason, $reasonFromDetails) === 1) {
                                    $reason = trim((string) $reasonFromDetails[1]);
                                }
                                if (strlen($reason) > 45) {
                                    $reason = substr($reason, 0, 45) . '...';
                                }

                                $customerNote = '';
                                if (preg_match('/Customer cancellation note:\s*(.+)$/mi', (string) ($order->admin_notes ?? ''), $noteMatches) === 1) {
                                    $customerNote = trim((string) $noteMatches[1]);
                                }

                                $modalPayload = [
                                    'order_id' => $order->order_ref ?? ('#' . $order->id),
                                    'customer' => $order->user->name ?? $order->customer_name ?? 'N/A',
                                    'refund_amount' => number_format((float) ($order->total_amount ?? $order->total ?? 0), 2),
                                    'payment_method' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'N/A'))),
                                    'order_status' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'N/A'))),
                                    'cancel_reason' => $reason,
                                    'customer_note' => $customerNote !== '' ? $customerNote : 'No customer note provided.',
                                    'status_state' => $statusState,
                                    'order_show_url' => route('admin.orders.show', $order),
                                    'approve_url' => route('admin.orders.cancel_requests.approve', $order),
                                    'reject_url' => route('admin.orders.cancel_requests.reject', $order),
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-[#800000]">{{ $order->order_ref ?? ('#' . $order->id) }}</td>
                                <td class="px-4 py-3">{{ $order->user->name ?? $order->customer_name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">P{{ number_format((float) ($order->total_amount ?? $order->total ?? 0), 2) }}</td>
                                <td class="px-4 py-3" title="{{ $reason }}">{{ $reason }}</td>
                                <td class="px-4 py-3">{{ optional($order->updated_at)->format('M d') }}</td>
                                <td class="px-4 py-3">
                                    @if($statusState === 'pending')
                                        <span class="cancel-badge cancel-badge-pending">Pending</span>
                                    @elseif($statusState === 'approved')
                                        <span class="cancel-badge cancel-badge-approved">Approved</span>
                                    @else
                                        <span class="cancel-badge cancel-badge-rejected">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        type="button"
                                        class="view-request-btn px-4 py-2 border border-gray-300 rounded-lg font-semibold hover:bg-gray-100"
                                        data-request='@json($modalPayload)'
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No cancellation requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($orders, 'links'))
            <div>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<div id="cancelRequestModal" class="fixed inset-0 bg-black/55 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 id="modalOrderTitle" class="text-xl font-bold text-gray-900">Order #</h2>
                <div id="modalStatusBadgeWrap" class="mt-2"></div>
            </div>
            <button id="closeCancelRequestModal" type="button" class="w-11 h-11 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 text-xl" aria-label="Close modal">×</button>
        </div>

        <div class="p-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-sm text-gray-500">Customer</p>
                    <p id="modalCustomer" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-sm text-gray-500">Refund amount</p>
                    <p id="modalRefundAmount" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-sm text-gray-500">Payment method</p>
                    <p id="modalPaymentMethod" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-sm text-gray-500">Order status</p>
                    <p id="modalOrderStatus" class="text-lg font-semibold text-gray-900"></p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-sm text-gray-500">Cancellation reason</p>
                <p id="modalCancelReason" class="text-lg font-semibold text-gray-900"></p>
            </div>

            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-sm text-gray-500">Customer note</p>
                <p id="modalCustomerNote" class="text-lg font-semibold text-gray-900"></p>
            </div>

            <form id="modalActionForm" method="POST" class="space-y-3 hidden">
                @csrf
                <label for="modalRejectionReason" class="block text-sm font-semibold text-gray-700">Rejection reason <span class="text-rose-600">(required for reject)</span></label>
                <textarea id="modalRejectionReason" name="rejection_reason" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y" placeholder="e.g. Order already prepared and out for delivery"></textarea>
                <label for="modalAdminNote" class="block text-sm font-semibold text-gray-700">Admin note (optional)</label>
                <textarea id="modalAdminNote" name="admin_note" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000] resize-y" placeholder="e.g. Refund initiated via GCash..."></textarea>
                <div id="modalFormError" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" id="approveRequestBtn" class="w-full px-4 py-3 bg-[#800000] text-white rounded-lg font-semibold hover:bg-[#600000] transition-colors">Approve & refund</button>
                    <button type="button" id="rejectRequestBtn" class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Reject request</button>
                </div>
            </form>

            <div id="modalResolvedMessage" class="text-center rounded-xl px-4 py-4 font-medium hidden"></div>

            <div class="flex justify-end gap-3 pt-1">
                <a id="modalOpenOrderBtn" href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Open order details</a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('cancelRequestModal');
        const closeBtn = document.getElementById('closeCancelRequestModal');
        const viewButtons = document.querySelectorAll('.view-request-btn');
        const orderTitle = document.getElementById('modalOrderTitle');
        const statusBadgeWrap = document.getElementById('modalStatusBadgeWrap');
        const customerEl = document.getElementById('modalCustomer');
        const refundAmountEl = document.getElementById('modalRefundAmount');
        const paymentMethodEl = document.getElementById('modalPaymentMethod');
        const orderStatusEl = document.getElementById('modalOrderStatus');
        const cancelReasonEl = document.getElementById('modalCancelReason');
        const customerNoteEl = document.getElementById('modalCustomerNote');
        const resolvedMessageEl = document.getElementById('modalResolvedMessage');
        const actionForm = document.getElementById('modalActionForm');
        const rejectionReasonEl = document.getElementById('modalRejectionReason');
        const adminNoteEl = document.getElementById('modalAdminNote');
        const formErrorEl = document.getElementById('modalFormError');
        const openOrderBtn = document.getElementById('modalOpenOrderBtn');
        const approveBtn = document.getElementById('approveRequestBtn');
        const rejectBtn = document.getElementById('rejectRequestBtn');

        let currentApproveUrl = '';
        let currentRejectUrl = '';

        const getBadge = function (state) {
            if (state === 'approved') {
                return '<span class="cancel-badge cancel-badge-approved">Approved</span>';
            }

            if (state === 'rejected') {
                return '<span class="cancel-badge cancel-badge-rejected">Rejected</span>';
            }

            return '<span class="cancel-badge cancel-badge-pending">Pending</span>';
        };

        const openModal = function (payload) {
            orderTitle.textContent = 'Order ' + payload.order_id;
            statusBadgeWrap.innerHTML = getBadge(payload.status_state);
            customerEl.textContent = payload.customer || 'N/A';
            refundAmountEl.textContent = 'P' + (payload.refund_amount || '0.00');
            paymentMethodEl.textContent = payload.payment_method || 'N/A';
            orderStatusEl.textContent = payload.order_status || 'N/A';
            cancelReasonEl.textContent = payload.cancel_reason || 'N/A';
            customerNoteEl.textContent = payload.customer_note || 'No customer note provided.';
            openOrderBtn.href = payload.order_show_url || '#';

            currentApproveUrl = payload.approve_url || '';
            currentRejectUrl = payload.reject_url || '';
            rejectionReasonEl.value = '';
            adminNoteEl.value = '';
            formErrorEl.classList.add('hidden');
            formErrorEl.textContent = '';
            rejectionReasonEl.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');

            if (payload.status_state === 'pending') {
                actionForm.classList.remove('hidden');
                resolvedMessageEl.classList.add('hidden');
            } else if (payload.status_state === 'approved') {
                actionForm.classList.add('hidden');
                resolvedMessageEl.classList.remove('hidden');
                resolvedMessageEl.className = 'text-center rounded-xl px-4 py-4 font-medium bg-green-50 text-green-700 border border-green-100';
                resolvedMessageEl.textContent = 'This request was approved and refund was initiated.';
            } else {
                actionForm.classList.add('hidden');
                resolvedMessageEl.classList.remove('hidden');
                resolvedMessageEl.className = 'text-center rounded-xl px-4 py-4 font-medium bg-rose-50 text-rose-700 border border-rose-100';
                resolvedMessageEl.textContent = 'This request was rejected.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            formErrorEl.classList.add('hidden');
            formErrorEl.textContent = '';
            rejectionReasonEl.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
        };

        const submitAction = function (targetUrl, requireRejectionReason) {
            if (!targetUrl) {
                return;
            }

            const rejectionReason = (rejectionReasonEl.value || '').trim();
            const noteValue = (adminNoteEl.value || '').trim();
            if (requireRejectionReason && rejectionReason.length === 0) {
                formErrorEl.classList.remove('hidden');
                formErrorEl.textContent = 'Rejection reason is required when rejecting a cancellation request.';
                rejectionReasonEl.classList.add('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
                return;
            }
            formErrorEl.classList.add('hidden');
            formErrorEl.textContent = '';
            rejectionReasonEl.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = targetUrl;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const noteInput = document.createElement('input');
            noteInput.type = 'hidden';
            noteInput.name = 'admin_note';
            noteInput.value = noteValue;
            form.appendChild(noteInput);

            const rejectionReasonInput = document.createElement('input');
            rejectionReasonInput.type = 'hidden';
            rejectionReasonInput.name = 'rejection_reason';
            rejectionReasonInput.value = rejectionReason;
            form.appendChild(rejectionReasonInput);

            document.body.appendChild(form);
            form.submit();
        };

        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                let payload = {};
                try {
                    payload = JSON.parse(button.getAttribute('data-request') || '{}');
                } catch (error) {
                    payload = {};
                }

                openModal(payload);
            });
        });

        approveBtn.addEventListener('click', function () {
            submitAction(currentApproveUrl, false);
        });

        rejectBtn.addEventListener('click', function () {
            submitAction(currentRejectUrl, true);
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
