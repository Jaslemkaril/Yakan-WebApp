@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        @php
            $orderStatusLower = strtolower((string) ($order->status ?? ''));
            $canCancelOrder = in_array($orderStatusLower, ['pending', 'pending_confirmation', 'confirmed', 'processing'], true);
            $showCancelForm = $errors->has('cancel_reason') || !empty(old('cancel_reason'));

            $cancellationReason = null;
            $cancellationSourceText = trim((string) ($order->notes ?? '')) . "\n" . trim((string) ($order->admin_notes ?? ''));
            $cancellationReasonPatterns = [
                '/Cancellation reason:\s*(.+)$/mi',
                '/Cancelled by admin\.\s*Reason:\s*(.+)$/mi',
                '/Customer cancel request approved:\s*(.+)$/mi',
                '/Customer order cancelled\.\s*Refund pending review\.\s*Reason:\s*(.+)$/mi',
            ];

            foreach ($cancellationReasonPatterns as $pattern) {
                if (preg_match($pattern, $cancellationSourceText, $cancelMatches) === 1) {
                    $cancellationReason = trim((string) $cancelMatches[1]);
                    break;
                }
            }
        @endphp
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Order Details</h1>
                    <p class="text-gray-600">Order #<span class="font-bold text-[#800000]">{{ $order->order_ref }}</span></p>
                    <p class="text-sm text-gray-500 mt-2">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($canCancelOrder)
                        <button id="cancel-order-toggle" type="button" class="inline-flex items-center justify-center px-5 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-all duration-300 shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel Order
                        </button>
                    @endif

                    <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Order Status -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#800000] hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Order Status</p>
                        <p class="text-3xl font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                    </div>
                    <div class="w-14 h-14 bg-[#800000] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#800000] hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Payment Status</p>
                        @php
                            $hasBankReceipt = !empty($order->bank_receipt) && $order->payment_method === 'bank_transfer';
                            $hasProof = $hasBankReceipt || (!empty($order->payment_proof_path) && $order->payment_method === 'bank_transfer');
                            $effectivePaymentStatus = ($hasProof && $order->payment_status === 'pending')
                                ? 'verification_pending'
                                : $order->payment_status;
                            $totalAmountForPayment = (float) ($order->total_amount ?? $order->total ?? 0);
                            $remainingBalance = max(0, (float) ($order->remaining_balance ?? 0));
                            $isDownpaymentOrder = strtolower((string) ($order->payment_option ?? 'full')) === 'downpayment';

                            $notesDownpaymentAmount = 0.0;
                            $notesRemainingBalance = 0.0;
                            if (!$isDownpaymentOrder) {
                                $paymentNotes = (string) ($order->notes ?? '');
                                if (preg_match('/Downpayment received:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)\s*;\s*remaining balance:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)/i', $paymentNotes, $matches) === 1) {
                                    $notesDownpaymentAmount = (float) str_replace(',', '', $matches[1]);
                                    $notesRemainingBalance = (float) str_replace(',', '', $matches[2]);
                                }
                            }

                            $isDownpaymentPartialPaid = in_array($effectivePaymentStatus, ['paid', 'verified'], true)
                                && (
                                    ($isDownpaymentOrder && $remainingBalance > 0)
                                    || (!$isDownpaymentOrder && $notesRemainingBalance > 0)
                                );

                            $downpaymentRate = (float) ($order->downpayment_rate ?? 0);
                            if ($downpaymentRate <= 0 && $totalAmountForPayment > 0) {
                                $downpaymentAmount = (float) ($order->downpayment_amount ?? 0);
                                if ($downpaymentAmount <= 0 && $notesDownpaymentAmount > 0) {
                                    $downpaymentAmount = $notesDownpaymentAmount;
                                }
                                if ($downpaymentAmount > 0) {
                                    $downpaymentRate = ($downpaymentAmount / $totalAmountForPayment) * 100;
                                }
                            }
                            if ($downpaymentRate <= 0) {
                                $downpaymentRate = 50;
                            }
                            $downpaymentRate = max(0, min(100, $downpaymentRate));
                            $downpaymentRateLabel = rtrim(rtrim(number_format($downpaymentRate, 2, '.', ''), '0'), '.');

                            $paymentStatusLabel = [
                                'paid' => $isDownpaymentPartialPaid
                                    ? "Partial Payment ({$downpaymentRateLabel}%)"
                                    : (($isDownpaymentOrder && $remainingBalance <= 0) ? 'Fully Paid ✓' : 'Paid ✓'),
                                'verified' => $isDownpaymentPartialPaid
                                    ? "Partial Payment ({$downpaymentRateLabel}%)"
                                    : (($isDownpaymentOrder && $remainingBalance <= 0) ? 'Fully Paid ✓' : 'Paid ✓'),
                                'verification_pending' => 'Awaiting Verification',
                                'pending' => 'Pending',
                                'failed' => 'Failed',
                            ][$effectivePaymentStatus] ?? ucfirst(str_replace('_', ' ', $effectivePaymentStatus));
                        @endphp
                        <p class="text-3xl font-bold text-gray-900">{{ $paymentStatusLabel }}</p>
                    </div>
                    <div class="w-14 h-14 bg-[#800000] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($orderStatusLower === 'cancelled')
        <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-red-700">Order Cancelled</p>
                    @if(!empty($cancellationReason))
                        <p class="text-sm text-red-700 mt-1"><span class="font-semibold">Reason:</span> {{ $cancellationReason }}</p>
                    @else
                        <p class="text-sm text-red-700 mt-1">This order was cancelled by the store team.</p>
                    @endif
                </div>
            </div>
        </div>
        @elseif($orderStatusLower === 'cancellation_requested')
        <div class="mb-8 bg-amber-50 border border-amber-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-amber-800">Cancellation Requested</p>
                    @if(!empty($cancellationReason))
                        <p class="text-sm text-amber-800 mt-1"><span class="font-semibold">Requested reason:</span> {{ $cancellationReason }}</p>
                    @endif
                    <p class="text-sm text-amber-800 mt-1">Your request is pending admin approval. The order is not cancelled yet.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer Action Buttons -->
        @if($canCancelOrder)
        <div id="cancel-order-card" class="mb-8 bg-white rounded-xl shadow-md p-6 border border-gray-200 {{ $showCancelForm ? '' : 'hidden' }}">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Cancel Order</h3>
            <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation <span class="text-red-600">*</span></label>
                    <select name="cancel_reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        <option value="">-- Select a reason --</option>
                        <option value="Changed my mind" {{ old('cancel_reason') === 'Changed my mind' ? 'selected' : '' }}>Changed my mind</option>
                        <option value="Found a better price elsewhere" {{ old('cancel_reason') === 'Found a better price elsewhere' ? 'selected' : '' }}>Found a better price elsewhere</option>
                        <option value="Ordered by mistake" {{ old('cancel_reason') === 'Ordered by mistake' ? 'selected' : '' }}>Ordered by mistake</option>
                        <option value="Delivery takes too long" {{ old('cancel_reason') === 'Delivery takes too long' ? 'selected' : '' }}>Delivery takes too long</option>
                        <option value="Duplicate order" {{ old('cancel_reason') === 'Duplicate order' ? 'selected' : '' }}>Duplicate order</option>
                        <option value="Want to change items" {{ old('cancel_reason') === 'Want to change items' ? 'selected' : '' }}>Want to change items</option>
                        <option value="Financial reasons" {{ old('cancel_reason') === 'Financial reasons' ? 'selected' : '' }}>Financial reasons</option>
                        <option value="Other" {{ old('cancel_reason') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Order
                </button>
            </form>
        </div>
        @endif

        @if($order->status === 'delivered')
        <div class="mb-8" id="confirm-receipt-card">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-md border-2 border-green-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 bg-green-600 rounded-lg flex items-center justify-center shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Confirm Receipt</h3>
                            <p class="text-xs text-green-700 font-medium">Your order has been marked as delivered</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-5">Have you received all your items in good condition? Confirming lets the seller know your order is complete.</p>
                    <button id="confirm-received-btn"
                        onclick="confirmOrderReceived({{ $order->id }}, '{{ csrf_token() }}')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 active:scale-95 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Yes, I Received My Order
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($order->status === 'completed')
        <div class="mb-8">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl shadow-md border-2 border-emerald-400 p-6 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-emerald-800">Order Completed!</h3>
                    <p class="text-sm text-emerald-700">You have confirmed receipt of this order. Thank you for shopping with Yakan!</p>
                </div>
            </div>
        </div>
        @endif

        @if(($order->status ?? '') === 'completed' || !empty($refundRequest))
        <div class="mb-8 bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Refund Request</h3>
                    <p class="text-sm text-gray-600">
                        @if(!empty($refundRequest))
                            Quick Actions update: Track your latest refund progress and admin decision here.
                        @else
                            Need help with your order? You can request a refund after order received.
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Refund policy: within {{ $refundWarrantyDays ?? 7 }} days after order completion
                        @if(!empty($refundWarrantyDeadline))
                            (until {{ $refundWarrantyDeadline->format('M d, Y h:i A') }})
                        @endif
                    </p>
                </div>
            </div>

            @if(isset($refundRequest) && $refundRequest)
                @php
                    $refundCurrentStatus = $refundRequest->workflow_status ?: $refundRequest->status;
                    $refundStatusMap = [
                        'pending_review' => ['label' => 'Pending Review', 'class' => 'bg-yellow-100 text-yellow-800'],
                        'under_review' => ['label' => 'Under Review', 'class' => 'bg-blue-100 text-blue-800'],
                        'awaiting_return_shipment' => ['label' => 'Waiting For Your Return Shipment', 'class' => 'bg-orange-100 text-orange-800'],
                        'return_in_transit' => ['label' => 'Return In Transit', 'class' => 'bg-amber-100 text-amber-800'],
                        'return_received' => ['label' => 'Return Received', 'class' => 'bg-cyan-100 text-cyan-800'],
                        'pending_payout' => ['label' => 'Pending Payout', 'class' => 'bg-indigo-100 text-indigo-800'],
                        'processed' => ['label' => 'Refund Processed', 'class' => 'bg-green-100 text-green-800'],
                        'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
                        'requested' => ['label' => 'Requested', 'class' => 'bg-yellow-100 text-yellow-800'],
                        'approved' => ['label' => 'Approved', 'class' => 'bg-indigo-100 text-indigo-800'],
                    ];
                    $refundChip = $refundStatusMap[$refundCurrentStatus] ?? ['label' => ucfirst(str_replace('_', ' ', $refundCurrentStatus)), 'class' => 'bg-gray-100 text-gray-800'];
                @endphp

                <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $refundChip['class'] }}">{{ $refundChip['label'] }}</span>
                        <span class="text-xs text-gray-500">Requested {{ optional($refundRequest->requested_at)->format('M d, Y h:i A') ?? $refundRequest->created_at->format('M d, Y h:i A') }}</span>
                    </div>

                    <p class="text-sm text-gray-700"><span class="font-semibold">Reason:</span> {{ $refundRequest->reason }}</p>
                    <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Refund Type:</span> {{ ucfirst(str_replace('_', ' ', $refundRequest->refund_type ?? 'full')) }}</p>
                    <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Comment:</span> {{ $refundRequest->comment ?? $refundRequest->details }}</p>
                    @if(!empty($refundRequest->final_decision))
                        <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Final Decision:</span> {{ strtoupper(str_replace('_', ' ', $refundRequest->final_decision)) }}</p>
                    @endif
                    @if(!is_null($refundRequest->refund_amount))
                        <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Approved Refund:</span> PHP {{ number_format((float) $refundRequest->refund_amount, 2) }}</p>
                    @endif
                    @if(!empty($refundRequest->payout_status))
                        <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Payout Status:</span> {{ ucfirst(str_replace('_', ' ', $refundRequest->payout_status)) }}</p>
                    @endif
                    @if(!empty($refundRequest->reviewed_at))
                        <p class="text-xs text-gray-500 mt-2">Last reviewed: {{ \Carbon\Carbon::parse($refundRequest->reviewed_at)->format('M d, Y h:i A') }}</p>
                    @endif

                    @if(!empty($refundRequest->return_tracking_number))
                        <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Return Tracking:</span> {{ $refundRequest->return_tracking_number }}</p>
                    @endif

                    @php
                        $refundEvidence = is_array($refundRequest->evidence_paths ?? null) ? $refundRequest->evidence_paths : [];
                    @endphp
                    @if(!empty($refundEvidence))
                        <div class="mt-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Uploaded Evidence</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($refundEvidence as $evidencePath)
                                    @php
                                        $evidenceUrl = route('orders.refund-evidence.view', ['refundRequest' => $refundRequest->id, 'index' => $loop->index]);
                                        $ext = strtolower(pathinfo(parse_url($evidencePath, PHP_URL_PATH) ?? $evidencePath, PATHINFO_EXTENSION));
                                        $isImageEvidence = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
                                        $isVideoEvidence = in_array($ext, ['mp4', 'mov', 'webm'], true);
                                    @endphp

                                    @if($isImageEvidence)
                                        <a href="{{ $evidenceUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 bg-white" title="Open full image">
                                            <img src="{{ $evidenceUrl }}" alt="Refund evidence" class="w-24 h-24 object-cover hover:opacity-90 transition-opacity">
                                        </a>
                                    @elseif($isVideoEvidence)
                                        <a href="{{ $evidenceUrl }}" target="_blank" class="inline-flex items-center px-3 py-2 rounded-lg border border-blue-300 text-sm text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            View Video Evidence
                                        </a>
                                    @else
                                        <a href="{{ $evidenceUrl }}" target="_blank" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 bg-white hover:bg-gray-100 transition-colors">
                                            View PDF Evidence
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($refundRequest->admin_note))
                        <div class="mt-3 rounded-md border border-gray-200 bg-white p-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Admin Note</p>
                            <p class="text-sm text-gray-700">{{ $refundRequest->admin_note }}</p>
                        </div>
                    @endif

                    @if($refundCurrentStatus === 'awaiting_return_shipment')
                        <form action="{{ route('orders.refund-return.ship', $refundRequest->id) }}" method="POST" class="mt-4 space-y-2">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Return Tracking Number</label>
                                <input type="text" name="return_tracking_number" required maxlength="120" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Enter courier and tracking number">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Return Note (optional)</label>
                                <textarea name="return_comment" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Share any return shipping details"></textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-semibold" style="background:#800000;">
                                Submit Return Shipment
                            </button>
                        </form>
                    @endif
                </div>
            @elseif(!empty($canRequestRefund))
                <button type="button" id="refund-toggle" class="w-full md:w-auto inline-flex items-center gap-3 px-4 py-3 rounded-xl border border-[#e0b0b0] bg-[#fff5f5] hover:bg-[#feeaea] transition-colors">
                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-[#e0b0b0] bg-white flex items-center justify-center">
                        <svg class="w-7 h-7" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900">Request Refund</p>
                        <p class="text-xs text-gray-600">Click to open request form</p>
                    </div>
                </button>

                <div id="refund-form-wrap" class="hidden mt-4">
                <form id="refund-request-form" method="POST" action="{{ route('orders.refund-request', $order) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reason <span class="text-red-600">*</span></label>
                        <select id="refund-reason-select" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                            <option value="">-- Select reason --</option>
                            <option value="Item not as described">Item not as described</option>
                            <option value="Damaged item">Damaged item</option>
                            <option value="Wrong item received">Wrong item received</option>
                            <option value="Incomplete order">Incomplete order</option>
                            <option value="Changed my mind">Changed my mind</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Refund Type <span class="text-red-600">*</span></label>
                        <select name="refund_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                            <option value="full">Full Refund</option>
                            <option value="partial">Partial Refund</option>
                            <option value="change_of_mind">Change Of Mind</option>
                        </select>
                    </div>

                    <div id="refund-reason-guidance" class="hidden rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <p id="refund-guidance-title" class="text-sm font-semibold text-amber-800"></p>
                        <p id="refund-guidance-details" class="text-sm text-amber-700 mt-1"></p>
                        <p id="refund-guidance-evidence" class="text-xs text-amber-700 mt-2"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Comment <span class="text-red-600">*</span></label>
                        <textarea id="refund-details-input" name="comment" rows="4" maxlength="2000" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent" placeholder="Please explain what happened and what refund support you need."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Evidence <span class="text-red-600">*</span> (up to 5 files)</label>
                        <input id="refund-evidence-input" type="file" name="evidence[]" accept="image/*,video/mp4,video/quicktime,video/webm,.pdf,.mp4,.mov,.webm" multiple required class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#800000] file:text-white hover:file:bg-[#600000]">
                        <p id="refund-evidence-help" class="text-xs text-gray-500 mt-1">Required. Accepted: JPG, PNG, WEBP, PDF, MP4, MOV, WEBM (max 20MB each)</p>
                        <div id="refund-evidence-preview" class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2"></div>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg text-white font-semibold transition-colors" style="background:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Submit Refund Request
                    </button>
                </form>
                </div>
            @elseif(($order->status ?? '') === 'completed' && !empty($isRefundWarrantyExpired) && !isset($refundRequest))
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-700">Refund window expired</p>
                    <p class="text-sm text-red-700 mt-1">Refund requests are only allowed within {{ $refundWarrantyDays ?? 7 }} days after order completion.</p>
                </div>
            @endif
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Order Items <span class="text-[#800000]">({{ $order->orderItems->count() }})</span></h2>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200">
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden border border-gray-300">
                                    @php
                                        // Prefer accessor that handles full URLs, storage, or uploads
                                        $imageUrl = $item->product?->image_url ?? '';
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name ?? 'Product' }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                            <svg class="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900">{{ $item->product->name ?? 'Product' }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">SKU: <span class="font-medium">{{ $item->product->sku ?? 'N/A' }}</span></p>

                                    @if($item->product && $item->product->is_bundle && $item->product->bundleItems->isNotEmpty())
                                        <div class="mt-2 space-y-1">
                                            <p class="text-xs font-semibold text-gray-700">Includes:</p>
                                            @foreach($item->product->bundleItems as $bundleComponent)
                                                <p class="text-xs text-gray-600">
                                                    - {{ $bundleComponent->componentProduct->name ?? 'Item' }}
                                                    (x{{ (int) $bundleComponent->quantity * (int) $item->quantity }})
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-3 mt-3">
                                        <span class="inline-block px-3 py-1 bg-[#fef2f2] text-[#800000] text-xs font-semibold rounded-lg">Qty: {{ $item->quantity }}</span>
                                        <span class="inline-block px-3 py-1 bg-[#fef2f2] text-[#800000] text-xs font-semibold rounded-lg">₱{{ number_format($item->price, 2) }} each</span>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs text-gray-600 mb-1">Subtotal</p>
                                    <p class="text-xl font-bold text-[#800000]">₱{{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Delivery Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Delivery Type</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst($order->delivery_type === 'deliver' ? 'Delivery' : 'Pickup') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Tracking Number</p>
                            <p class="text-lg font-bold text-gray-900 font-mono">{{ $order->tracking_number ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($order->estimated_delivery_date)
                        <div class="pt-4 pb-4 border-t border-gray-200">
                            <div class="flex items-center gap-3 p-4 rounded-xl"
                                 style="background: linear-gradient(135deg, #fff5f5 0%, #ffe4e4 100%); border: 1px solid #f5c6c6;">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#800000;">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Estimated Delivery Date</p>
                                    <p class="text-lg font-bold" style="color:#800000;">
                                        {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('F d, Y') }}
                                    </p>
                                    @if(!in_array($order->status, ['delivered','completed','cancelled','cancellation_requested']))
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->isPast() ? 'Expected soon' : \Carbon\Carbon::parse($order->estimated_delivery_date)->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($order->delivery_type === 'deliver')
                        <div class="pt-6 border-t-2 border-gray-200">
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Delivery Address</p>
                            <p class="text-gray-900">{{ $order->delivery_address }}</p>
                        </div>
                    @endif
                </div>

                <!-- Payment Information -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Payment Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Payment Method</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase mb-2">Payment Status</p>
                            @php
                                $statusChip = match($effectivePaymentStatus) {
                                    'paid', 'verified' => $isDownpaymentPartialPaid
                                        ? ["◐ Partial Payment ({$downpaymentRateLabel}%)", 'bg-amber-100 text-amber-800']
                                        : [($isDownpaymentOrder && $remainingBalance <= 0) ? '✓ Fully Paid' : '✓ Paid', 'bg-green-100 text-green-700'],
                                    'verification_pending' => ['⏳ Verification Pending', 'bg-yellow-100 text-yellow-800'],
                                    'pending' => ['⏳ Pending', 'bg-yellow-100 text-yellow-800'],
                                    default => ['✕ Failed', 'bg-red-100 text-red-700'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusChip[1] }}">
                                {{ $statusChip[0] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 sticky top-6">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-[#800000]">
                        <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        @php
                            $summaryTotalAmount = (float) ($order->total_amount ?? $order->total ?? 0);
                            $summaryDownpaymentAmount = (float) ($order->downpayment_amount ?? 0);
                            if ($summaryDownpaymentAmount <= 0 && !empty($notesDownpaymentAmount ?? 0)) {
                                $summaryDownpaymentAmount = (float) $notesDownpaymentAmount;
                            }

                            $summaryRemainingBalance = max(0, (float) ($order->remaining_balance ?? 0));
                            if ($summaryRemainingBalance <= 0 && !empty($notesRemainingBalance ?? 0)) {
                                $summaryRemainingBalance = (float) $notesRemainingBalance;
                            }

                            if ($summaryDownpaymentAmount <= 0 && !empty($isDownpaymentPartialPaid) && $summaryTotalAmount > 0) {
                                $summaryDownpaymentAmount = round($summaryTotalAmount * (($downpaymentRate ?? 50) / 100), 2);
                                $summaryRemainingBalance = max(0, round($summaryTotalAmount - $summaryDownpaymentAmount, 2));
                            }
                        @endphp
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Subtotal</span>
                            <span class="font-bold">₱{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Shipping</span>
                            <span class="font-bold">₱{{ number_format($order->shipping_fee, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-[#800000]">
                                <span class="font-medium">Discount</span>
                                <span class="font-bold">-₱{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t-2 border-gray-200 pt-4 flex justify-between bg-[#fef2f2] rounded-lg p-3">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-[#800000]">₱{{ number_format($order->total_amount ?? $order->total, 2) }}</span>
                        </div>

                        @if(!empty($isDownpaymentPartialPaid))
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 space-y-2">
                                <div class="flex justify-between text-sm text-amber-900">
                                    <span class="font-semibold">Paid ({{ $downpaymentRateLabel ?? '50' }}%)</span>
                                    <span class="font-bold">₱{{ number_format($summaryDownpaymentAmount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-700 pt-2 border-t border-amber-200">
                                    <span class="font-medium">Remaining Balance</span>
                                    <span class="font-bold">₱{{ number_format($summaryRemainingBalance, 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</p>
                        </div>
                        <p class="font-bold text-gray-900">{{ $order->customer_name }}</p>
                        <p class="text-xs text-gray-600 mt-2">{{ $order->customer_email }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $order->customer_phone }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        @if($order->payment_status === 'pending' && $order->payment_method === 'gcash')
                            <a href="{{ route('payment.online', $order->id) }}" class="block w-full text-center px-4 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                                💳 Complete Payment
                            </a>
                        @elseif($order->payment_method === 'bank_transfer')
                            @php
                                $hasReceipt = !empty($order->bank_receipt) || !empty($order->payment_proof_path);
                                $receiptPath = $order->bank_receipt ?: $order->payment_proof_path;
                                // Support both Cloudinary URLs and local storage paths
                                $receiptUrl = $receiptPath
                                    ? ((str_starts_with($receiptPath, 'http://') || str_starts_with($receiptPath, 'https://'))
                                        ? $receiptPath
                                        : \Illuminate\Support\Facades\Storage::url($receiptPath))
                                    : null;
                                $isCleared = in_array($effectivePaymentStatus, ['paid', 'verified', 'verification_pending'], true);
                            @endphp

                            @if($hasReceipt || $isCleared)
                                <!-- Receipt uploaded or already paid/verified -->
                                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-4 text-center">
                                    <div class="flex items-center justify-center gap-2 mb-2">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="font-bold text-green-800">Receipt on File</p>
                                    </div>
                                    <p class="text-sm text-green-700 mb-3">Awaiting verification. You can view your uploaded receipt below.</p>
                                    @if($hasReceipt && $receiptUrl)
                                        <a href="{{ $receiptUrl }}" target="_blank" class="inline-block px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all text-sm">
                                            👁️ View Receipt
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- Need to upload receipt -->
                                <a href="{{ route('payment.bank', $order->id) }}" class="block w-full text-center px-4 py-3 bg-[#800000] text-white font-semibold rounded-lg hover:bg-[#600000] transition-all duration-300 shadow-md">
                                    📄 Upload Receipt
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- ===== REVIEW SECTION ===== --}}
        @if(in_array($order->status, ['delivered', 'completed']) && auth()->check())
        <div class="mt-10" id="review-section">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <div class="w-10 h-10 bg-[#800000] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                Rate Your Purchase
            </h2>

            <div class="space-y-6">
                @foreach($order->orderItems as $item)
                    @php
                        $existingReview = \App\Models\Review::where('order_item_id', $item->id)
                            ->where('user_id', auth()->id())
                            ->first();
                    @endphp
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        {{-- Item header --}}
                        <div class="flex items-center gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                @if($item->product?->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $item->product->name ?? 'Product' }}</p>
                                <p class="text-sm text-gray-500">Qty: {{ $item->quantity }} &bull; ₱{{ number_format($item->price, 2) }} each</p>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            @if($existingReview)
                                {{-- Show existing review --}}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-1">
                                        @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-6 h-6 {{ $s <= $existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600 font-semibold">{{ $existingReview->rating }}/5</span>
                                        <span class="ml-auto text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">✓ Review Submitted</span>
                                    </div>
                                    @if($existingReview->title)
                                        <p class="font-semibold text-gray-800">"{{ $existingReview->title }}"</p>
                                    @endif
                                    @if($existingReview->comment)
                                        <p class="text-gray-700 text-sm">{{ $existingReview->comment }}</p>
                                    @endif
                                    @if($existingReview->review_images && count($existingReview->review_images) > 0)
                                        <div class="flex gap-2 flex-wrap mt-2">
                                            @foreach($existingReview->review_images as $imgUrl)
                                                <img src="{{ $imgUrl }}" alt="Review photo" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                            @endforeach
                                        </div>
                                    @endif
                                    <p class="text-xs text-gray-400">Submitted {{ $existingReview->created_at->format('M d, Y') }}</p>
                                </div>
                            @else
                                {{-- Review form --}}
                                <form action="{{ route('reviews.store.order-item', $item) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="space-y-4">
                                        {{-- Star Rating --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                                            <div class="flex gap-1" id="stars-{{ $item->id }}">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <button type="button" data-value="{{ $s }}" data-group="{{ $item->id }}"
                                                        class="star-btn w-9 h-9 text-gray-300 hover:text-yellow-400 transition-colors"
                                                        onclick="setRating({{ $item->id }}, {{ $s }})">
                                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    </button>
                                                @endfor
                                                <input type="hidden" name="rating" id="rating-{{ $item->id }}" value="" required>
                                            </div>
                                        </div>

                                        {{-- Title --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Review Title</label>
                                            <input type="text" name="title" maxlength="255" placeholder="Summarize your experience" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                        </div>

                                        {{-- Comment --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Your Review</label>
                                            <textarea name="comment" rows="3" maxlength="1000" placeholder="Share your experience with this product..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent resize-none"></textarea>
                                        </div>

                                        {{-- Photo Upload --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Photos (optional, up to 5)</label>
                                            <input type="file" name="images[]" accept="image/*" multiple
                                                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#800000] file:text-white hover:file:bg-[#600000] cursor-pointer"
                                                onchange="previewImages(this, 'preview-{{ $item->id }}')">
                                            <div id="preview-{{ $item->id }}" class="flex flex-wrap gap-2 mt-2"></div>
                                        </div>

                                        <button type="submit" class="bg-[#800000] hover:bg-[#600000] text-white font-bold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow">
                                            Submit Review
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function setRating(groupId, value) {
    document.getElementById('rating-' + groupId).value = value;
    const stars = document.querySelectorAll('#stars-' + groupId + ' .star-btn');
    stars.forEach(function(btn) {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.toggle('text-yellow-400', v <= value);
        btn.classList.toggle('text-gray-300', v > value);
    });
}

function previewImages(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    const files = Array.from(input.files).slice(0, 5);
    files.forEach(function(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

function previewRefundEvidence(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) {
        return;
    }

    preview.innerHTML = '';

    const files = Array.from(input.files).slice(0, 5);
    files.forEach(function(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext) || file.type.startsWith('image/');
        const isVideo = ['mp4', 'mov', 'webm'].includes(ext) || file.type.startsWith('video/');

        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative rounded-lg overflow-hidden border border-gray-200 bg-white';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-24 object-cover';
                img.alt = file.name;

                wrapper.appendChild(img);
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        } else if (isVideo) {
            const badge = document.createElement('div');
            badge.className = 'inline-flex items-center justify-center text-center px-2 py-3 rounded-lg border border-blue-300 text-xs text-blue-700 bg-blue-50';
            badge.textContent = 'VIDEO: ' + file.name;
            preview.appendChild(badge);
        } else {
            const badge = document.createElement('a');
            badge.href = '#';
            badge.className = 'inline-flex items-center justify-center text-center px-2 py-3 rounded-lg border border-gray-300 text-xs text-gray-700 bg-white';
            badge.textContent = 'PDF: ' + file.name;
            badge.addEventListener('click', function(e) {
                e.preventDefault();
            });
            preview.appendChild(badge);
        }
    });
}

function applyRefundReasonGuidance(reason) {
    const guidanceBox = document.getElementById('refund-reason-guidance');
    const title = document.getElementById('refund-guidance-title');
    const details = document.getElementById('refund-guidance-details');
    const evidence = document.getElementById('refund-guidance-evidence');
    const detailsInput = document.getElementById('refund-details-input');
    const evidenceHelp = document.getElementById('refund-evidence-help');

    const map = {
        'Item not as described': {
            title: 'Describe mismatch clearly',
            details: 'Explain what you expected based on product listing and what actually arrived.',
            evidence: 'Upload clear photos of the delivered item showing differences from the listing.',
            placeholder: 'Describe expected item vs actual received item. Include color/size/material differences.',
            help: 'Required: photos of actual delivered item. Accepted: JPG, PNG, WEBP, PDF, MP4, MOV, WEBM (max 20MB each).'
        },
        'Damaged item': {
            title: 'Damage proof required',
            details: 'Describe where the item is damaged and how severe it is.',
            evidence: 'Upload damage photos plus at least one opening/unboxing video.',
            placeholder: 'Describe exact damage location and condition on arrival.',
            help: 'Required: damage photos and at least one video (MP4/MOV/WEBM). Max 20MB each.'
        },
        'Wrong item received': {
            title: 'Show expected vs actual item',
            details: 'State what item you ordered and what item you received.',
            evidence: 'Upload screenshot of ordered product and photo of the actual delivered product.',
            placeholder: 'Write ordered product details and actual delivered product details.',
            help: 'Required: screenshot of ordered item and photo/video of actual item. Max 20MB each.'
        },
        'Incomplete order': {
            title: 'Show missing parts/items',
            details: 'Specify which item/quantity/parts are missing from your order.',
            evidence: 'Upload screenshot of order items and photo/video of everything received.',
            placeholder: 'List missing items or quantities and compare against your order receipt.',
            help: 'Required: order screenshot + proof of received package contents. Max 20MB each.'
        },
        'Changed my mind': {
            title: 'Change-of-mind return policy',
            details: 'Explain why you changed your mind and confirm if item is still unopened or in good condition.',
            evidence: 'Upload photos/videos of the current item condition. Return shipment may be required before payout.',
            placeholder: 'State why you want to return the item and describe its current condition.',
            help: 'Required: condition photos or videos. Accepted: JPG, PNG, WEBP, PDF, MP4, MOV, WEBM (max 20MB each).'
        },
        'Other': {
            title: 'Provide complete explanation',
            details: 'Explain the issue in detail so the team can evaluate your request quickly.',
            evidence: 'Upload supporting photos/videos/documents relevant to your concern.',
            placeholder: 'Explain your issue clearly and include key details.',
            help: 'Required: supporting proof files. Accepted: JPG, PNG, WEBP, PDF, MP4, MOV, WEBM (max 20MB each).'
        }
    };

    if (!reason || !map[reason]) {
        guidanceBox.classList.add('hidden');
        detailsInput.placeholder = 'Please explain what happened and what refund support you need.';
        evidenceHelp.textContent = 'Required. Accepted: JPG, PNG, WEBP, PDF, MP4, MOV, WEBM (max 20MB each)';
        return;
    }

    const cfg = map[reason];
    guidanceBox.classList.remove('hidden');
    title.textContent = cfg.title;
    details.textContent = cfg.details;
    evidence.textContent = cfg.evidence;
    detailsInput.placeholder = cfg.placeholder;
    evidenceHelp.textContent = cfg.help;
}

function hasRefundVideoEvidence(files) {
    return Array.from(files || []).some(function(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        return ['mp4', 'mov', 'webm'].includes(ext) || (file.type || '').startsWith('video/');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const cancelOrderToggle = document.getElementById('cancel-order-toggle');
    const cancelOrderCard = document.getElementById('cancel-order-card');
    if (cancelOrderToggle && cancelOrderCard) {
        cancelOrderToggle.addEventListener('click', function() {
            cancelOrderCard.classList.toggle('hidden');
            if (!cancelOrderCard.classList.contains('hidden')) {
                cancelOrderCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    const refundToggle = document.getElementById('refund-toggle');
    const refundFormWrap = document.getElementById('refund-form-wrap');
    if (refundToggle && refundFormWrap) {
        refundToggle.addEventListener('click', function() {
            refundFormWrap.classList.toggle('hidden');
        });
    }

    const refundEvidenceInput = document.getElementById('refund-evidence-input');
    if (refundEvidenceInput) {
        refundEvidenceInput.addEventListener('change', function() {
            previewRefundEvidence('refund-evidence-input', 'refund-evidence-preview');
        });
    }

    const refundReasonSelect = document.getElementById('refund-reason-select');
    if (refundReasonSelect) {
        refundReasonSelect.addEventListener('change', function() {
            applyRefundReasonGuidance(refundReasonSelect.value);
        });
        applyRefundReasonGuidance(refundReasonSelect.value || '');
    }

    const refundRequestForm = document.getElementById('refund-request-form');
    if (refundRequestForm) {
        refundRequestForm.addEventListener('submit', function(e) {
            if (!refundReasonSelect || !refundEvidenceInput) {
                return;
            }

            if (refundReasonSelect.value === 'Damaged item' && !hasRefundVideoEvidence(refundEvidenceInput.files)) {
                e.preventDefault();
                alert('For damaged items, please upload at least one opening/unboxing video together with photos.');
            }
        });
    }
});
</script>
@endsection

@push('scripts')
<script>
async function confirmOrderReceived(orderId, csrf) {
    const btn = document.getElementById('confirm-received-btn');
    if (!btn) return;

    const userConfirmed = confirm('Please confirm: Have you already received this order in good condition?');
    if (!userConfirmed) {
        return;
    }

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Confirming…`;

    try {
        const res = await fetch(`/orders/${orderId}/confirm-received`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });

        const data = await res.json();

        if (data.success) {
            // Replace the confirm card with a success banner
            const card = document.getElementById('confirm-receipt-card');
            if (card) {
                card.innerHTML = `
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl shadow-md border-2 border-emerald-400 p-6 flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-emerald-800">✅ Order Completed!</h3>
                            <p class="text-sm text-emerald-700">You have confirmed receipt of this order. Thank you for shopping with Yakan!</p>
                        </div>
                    </div>`;
            }
            // Also update the Order Status card
            const statusEl = document.querySelector('.text-3xl.font-bold.text-gray-900');
            if (statusEl && statusEl.closest('.border-l-4')) {
                statusEl.textContent = 'Completed';
            }
        } else {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Yes, I Received My Order`;
            alert(data.message || 'Could not confirm order. Please try again.');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Yes, I Received My Order`;
        alert('Network error. Please try again.');
    }
}
</script>
@endpush
