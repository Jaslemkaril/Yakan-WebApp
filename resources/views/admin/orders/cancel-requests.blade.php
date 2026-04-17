@extends('layouts.admin')

@section('title', 'Cancel Requests')

@section('content')
@php
    $activeFilter = strtolower((string) ($statusFilter ?? 'all'));
    $searchValue = (string) request('search', '');
@endphp

<div class="min-h-screen bg-[#f7f7f6] py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-5">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h1 class="text-2xl font-semibold text-gray-800">Cancel requests</h1>
            <div class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-[#f1f0ea] rounded-lg p-4">
                <div class="text-3xl font-semibold text-gray-800 leading-none">{{ $totalRequests }}</div>
                <div class="text-xs text-gray-600 mt-2">Total requests</div>
            </div>
            <div class="bg-[#f1f0ea] rounded-lg p-4">
                <div class="text-3xl font-semibold text-[#b7791f] leading-none">{{ $pendingRequests }}</div>
                <div class="text-xs text-gray-600 mt-2">Pending</div>
            </div>
            <div class="bg-[#f1f0ea] rounded-lg p-4">
                <div class="text-3xl font-semibold text-[#4a7c2d] leading-none">{{ $resolvedToday }}</div>
                <div class="text-xs text-gray-600 mt-2">Resolved today</div>
            </div>
        </div>

        <form method="GET" class="space-y-3">
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
                        class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors {{ $activeFilter === $filterValue ? 'border-gray-500 text-gray-800 bg-[#ecebe5]' : 'border-gray-300 text-gray-700 bg-[#f7f7f6] hover:bg-gray-100' }}"
                    >
                        {{ $filterLabel }}
                    </button>
                @endforeach
            </div>

            <div class="flex gap-2">
                <input
                    type="hidden"
                    name="status"
                    value="{{ $activeFilter }}"
                >
                <input
                    type="text"
                    name="search"
                    value="{{ $searchValue }}"
                    placeholder="Search order or customer..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-[#f7f7f6] focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
                >
                <a
                    href="{{ route('admin.orders.cancel_requests.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="bg-[#f7f7f6] border border-gray-300 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="bg-[#ecebe5] border-b border-gray-300">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Order</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Customer</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Amount</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Reason</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-[#f7f7f6]">
                        @forelse($orders as $order)
                            @php
                                $refundRequest = $order->refundRequests
                                    ->sortByDesc('created_at')
                                    ->first();

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
                                if (strlen($reason) > 35) {
                                    $reason = substr($reason, 0, 35) . '...';
                                }

                                $customerNote = '';
                                if (preg_match('/Customer cancellation note:\s*(.+)$/mi', (string) ($order->admin_notes ?? ''), $noteMatches) === 1) {
                                    $customerNote = trim((string) $noteMatches[1]);
                                }

                                $modalPayload = [
                                    'order_id' => $order->order_ref ?? ('#' . $order->id),
                                    'order_numeric_id' => $order->id,
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
                            <tr class="hover:bg-[#efeee9]">
                                <td class="px-3 py-2.5 font-semibold">{{ $order->order_ref ?? ('#' . $order->id) }}</td>
                                <td class="px-3 py-2.5">{{ $order->user->name ?? $order->customer_name ?? 'N/A' }}</td>
                                <td class="px-3 py-2.5">P{{ number_format((float) ($order->total_amount ?? $order->total ?? 0), 0) }}</td>
                                <td class="px-3 py-2.5" title="{{ $reason }}">{{ $reason }}</td>
                                <td class="px-3 py-2.5">{{ optional($order->updated_at)->format('M d') }}</td>
                                <td class="px-3 py-2.5">
                                    @if($statusState === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-semibold">Pending</span>
                                    @elseif($statusState === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 font-semibold">Approved</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-rose-100 text-rose-700 font-semibold">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <button
                                        type="button"
                                        class="view-request-btn px-4 py-1.5 border border-gray-400 rounded-lg font-semibold hover:bg-gray-100 inline-flex items-center gap-1"
                                        data-request='@json($modalPayload)'
                                    >
                                        <span>View</span>
                                        <span aria-hidden="true">↗</span>
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
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<div id="cancelRequestModal" class="fixed inset-0 bg-black/45 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-4xl bg-[#f7f7f6] rounded-2xl shadow-xl border border-gray-300">
        <div class="px-6 py-5 border-b border-gray-300 flex items-start justify-between">
            <div>
                <h2 id="modalOrderTitle" class="text-4xl font-semibold text-gray-900">Order #</h2>
                <div id="modalStatusBadgeWrap" class="mt-3"></div>
            </div>
            <button id="closeCancelRequestModal" type="button" class="w-12 h-12 border border-gray-400 rounded-xl text-gray-700 hover:bg-gray-100 text-2xl leading-none">×</button>
        </div>

        <div class="p-6 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                    <p class="text-sm text-gray-500">Customer</p>
                    <p id="modalCustomer" class="text-3xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                    <p class="text-sm text-gray-500">Refund amount</p>
                    <p id="modalRefundAmount" class="text-3xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                    <p class="text-sm text-gray-500">Payment method</p>
                    <p id="modalPaymentMethod" class="text-3xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                    <p class="text-sm text-gray-500">Order status</p>
                    <p id="modalOrderStatus" class="text-3xl font-semibold text-gray-900"></p>
                </div>
            </div>

            <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                <p class="text-sm text-gray-500">Cancellation reason</p>
                <p id="modalCancelReason" class="text-4xl font-semibold text-gray-900"></p>
            </div>

            <div class="bg-[#ecebe5] rounded-xl px-4 py-3">
                <p class="text-sm text-gray-500">Customer note</p>
                <p id="modalCustomerNote" class="text-4xl font-semibold text-gray-900"></p>
            </div>

            <form id="modalActionForm" method="POST" class="space-y-3 hidden">
                @csrf
                <input type="hidden" id="modalActionType" value="approve">
                <label for="modalAdminNote" class="block text-xl font-medium text-gray-700">Admin note (optional)</label>
                <textarea id="modalAdminNote" name="admin_note" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-[#f7f7f6] focus:ring-2 focus:ring-gray-400 focus:border-gray-400 text-3xl" placeholder="e.g. Refund initiated via GCash..."></textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" id="approveRequestBtn" class="w-full px-4 py-3 border border-gray-400 rounded-xl font-semibold text-2xl hover:bg-gray-100">Approve & refund</button>
                    <button type="button" id="rejectRequestBtn" class="w-full px-4 py-3 border border-gray-400 rounded-xl font-semibold text-2xl hover:bg-gray-100">Reject request</button>
                </div>
            </form>

            <div id="modalResolvedMessage" class="text-center text-gray-700 text-4xl font-medium py-4 hidden"></div>

            <div class="flex justify-end gap-3 pt-1">
                <a id="modalOpenOrderBtn" href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-sm">Open order details</a>
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
        const adminNoteEl = document.getElementById('modalAdminNote');
        const openOrderBtn = document.getElementById('modalOpenOrderBtn');
        const approveBtn = document.getElementById('approveRequestBtn');
        const rejectBtn = document.getElementById('rejectRequestBtn');

        let currentApproveUrl = '';
        let currentRejectUrl = '';

        const getBadge = function (state) {
            if (state === 'approved') {
                return '<span class="px-3 py-0.5 rounded-full text-xl bg-green-100 text-green-700 font-semibold">Approved</span>';
            }

            if (state === 'rejected') {
                return '<span class="px-3 py-0.5 rounded-full text-xl bg-rose-100 text-rose-700 font-semibold">Rejected</span>';
            }

            return '<span class="px-3 py-0.5 rounded-full text-xl bg-amber-100 text-amber-700 font-semibold">Pending</span>';
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
            adminNoteEl.value = '';

            if (payload.status_state === 'pending') {
                actionForm.classList.remove('hidden');
                resolvedMessageEl.classList.add('hidden');
            } else if (payload.status_state === 'approved') {
                actionForm.classList.add('hidden');
                resolvedMessageEl.classList.remove('hidden');
                resolvedMessageEl.textContent = 'This request was approved and refund was initiated.';
            } else {
                actionForm.classList.add('hidden');
                resolvedMessageEl.classList.remove('hidden');
                resolvedMessageEl.textContent = 'This request was rejected.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const submitAction = function (targetUrl, requireNote) {
            if (!targetUrl) {
                return;
            }

            const noteValue = (adminNoteEl.value || '').trim();
            if (requireNote && noteValue.length === 0) {
                alert('Please add rejection reason.');
                return;
            }

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
