@extends('layouts.admin')

@section('title', 'Cancel Requests')

@section('content')
@php
    $activeFilter = strtolower((string) ($statusFilter ?? 'all'));
    $searchValue = (string) request('search', '');
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Cancel Requests</h1>
                <p class="text-gray-600 mt-1">Review and resolve customer cancellation requests.</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ now()->format('M d, Y') }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-3xl font-bold text-gray-900">{{ $totalRequests }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide mt-1">Total requests</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-3xl font-bold text-amber-600">{{ $pendingRequests }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide mt-1">Pending</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-3xl font-bold text-green-600">{{ $resolvedToday }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide mt-1">Resolved today</div>
            </div>
        </div>

        <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-4">
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
                        class="px-4 py-2 rounded-lg border text-sm font-semibold transition-colors {{ $activeFilter === $filterValue ? 'border-[#800000] text-[#800000] bg-[#fdf2f2]' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50' }}"
                    >
                        {{ $filterLabel }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
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
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]"
                >
                <button
                    type="submit"
                    class="px-5 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] font-semibold"
                >
                    Search
                </button>
                <a
                    href="{{ route('admin.orders.cancel_requests.index') }}"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-center"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
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
                    <tbody class="divide-y divide-gray-100">
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
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-[#800000]">{{ $order->order_ref ?? ('#' . $order->id) }}</td>
                                <td class="px-4 py-3">{{ $order->user->name ?? $order->customer_name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">P{{ number_format((float) ($order->total_amount ?? $order->total ?? 0), 2) }}</td>
                                <td class="px-4 py-3 max-w-[200px] truncate" title="{{ $reason }}">{{ $reason }}</td>
                                <td class="px-4 py-3">{{ optional($order->updated_at)->format('M d') }}</td>
                                <td class="px-4 py-3">
                                    @if($statusState === 'pending')
                                        <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 font-semibold">Pending</span>
                                    @elseif($statusState === 'approved')
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-semibold">Approved</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-700 font-semibold">Rejected</span>
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
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<div id="cancelRequestModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 id="modalOrderTitle" class="text-3xl font-bold text-gray-900">Order #</h2>
                <div id="modalStatusBadgeWrap" class="mt-2"></div>
            </div>
            <button id="closeCancelRequestModal" type="button" class="w-11 h-11 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 text-xl">x</button>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-100 rounded-xl p-3">
                    <p class="text-sm text-gray-500">Customer</p>
                    <p id="modalCustomer" class="text-xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-100 rounded-xl p-3">
                    <p class="text-sm text-gray-500">Refund amount</p>
                    <p id="modalRefundAmount" class="text-xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-100 rounded-xl p-3">
                    <p class="text-sm text-gray-500">Payment method</p>
                    <p id="modalPaymentMethod" class="text-xl font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-100 rounded-xl p-3">
                    <p class="text-sm text-gray-500">Order status</p>
                    <p id="modalOrderStatus" class="text-xl font-semibold text-gray-900"></p>
                </div>
            </div>

            <div class="bg-gray-100 rounded-xl p-3">
                <p class="text-sm text-gray-500">Cancellation reason</p>
                <p id="modalCancelReason" class="text-xl font-semibold text-gray-900"></p>
            </div>

            <div class="bg-gray-100 rounded-xl p-3">
                <p class="text-sm text-gray-500">Customer note</p>
                <p id="modalCustomerNote" class="text-xl font-semibold text-gray-900"></p>
            </div>

            <form id="modalActionForm" method="POST" class="space-y-3 hidden">
                @csrf
                <input type="hidden" id="modalActionType" value="approve">
                <label for="modalAdminNote" class="block text-sm font-semibold text-gray-700">Admin note (optional)</label>
                <textarea id="modalAdminNote" name="admin_note" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-[#800000]" placeholder="e.g. Refund initiated via GCash..."></textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" id="approveRequestBtn" class="w-full px-4 py-3 border border-gray-300 rounded-lg font-semibold hover:bg-gray-100">Approve & refund</button>
                    <button type="button" id="rejectRequestBtn" class="w-full px-4 py-3 border border-gray-300 rounded-lg font-semibold hover:bg-gray-100">Reject request</button>
                </div>
            </form>

            <div id="modalResolvedMessage" class="text-center text-gray-700 text-2xl font-medium hidden"></div>

            <div class="flex justify-between gap-3 pt-2">
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
        const adminNoteEl = document.getElementById('modalAdminNote');
        const openOrderBtn = document.getElementById('modalOpenOrderBtn');
        const approveBtn = document.getElementById('approveRequestBtn');
        const rejectBtn = document.getElementById('rejectRequestBtn');

        let currentApproveUrl = '';
        let currentRejectUrl = '';

        const getBadge = function (state) {
            if (state === 'approved') {
                return '<span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700 font-semibold">Approved</span>';
            }

            if (state === 'rejected') {
                return '<span class="px-3 py-1 rounded-full text-sm bg-rose-100 text-rose-700 font-semibold">Rejected</span>';
            }

            return '<span class="px-3 py-1 rounded-full text-sm bg-amber-100 text-amber-700 font-semibold">Pending</span>';
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
