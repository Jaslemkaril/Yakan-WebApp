@extends('layouts.app')

@section('title', 'Track Order - ' . $order->tracking_number)

@push('styles')
<style>
    .tracking-hero {
        background: linear-gradient(135deg, #800000 0%, #500000 100%);
        position: relative;
        overflow: hidden;
    }
    .tracking-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }

    .tracking-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        border: 1px solid #f0f0f0;
    }

    /* Progress Stepper */
    .stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
    }
    .stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: #e5e7eb;
        z-index: 0;
    }
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        z-index: 1;
    }
    .step-connector {
        position: absolute;
        top: 20px;
        left: calc(-50% + 21px);
        right: calc(50% + 21px);
        height: 3px;
        z-index: 0;
    }
    .step-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #e5e7eb;
        background: white;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    .step.completed .step-icon {
        background: #22c55e;
        border-color: #22c55e;
        color: white;
    }
    .step.active .step-icon {
        background: #800000;
        border-color: #800000;
        color: white;
        box-shadow: 0 0 0 6px rgba(128,0,0,0.12);
        animation: stepPulse 2s infinite;
    }
    .step.pending .step-icon {
        background: #f9fafb;
        border-color: #d1d5db;
        color: #9ca3af;
    }
    .step-label {
        margin-top: 8px;
        font-size: 11px;
        text-align: center;
        font-weight: 600;
        line-height: 1.3;
        max-width: 72px;
        color: #6b7280;
    }
    .step.completed .step-label { color: #22c55e; }
    .step.active .step-label { color: #800000; }
    .step-date {
        font-size: 9px;
        color: #9ca3af;
        text-align: center;
        margin-top: 2px;
        max-width: 72px;
        line-height: 1.2;
    }
    .step.completed .step-date { color: #6b7280; }
    .step.active .step-date { color: #800000; opacity: 0.8; }

    @keyframes stepPulse {
        0%, 100% { box-shadow: 0 0 0 6px rgba(128,0,0,0.12); }
        50%       { box-shadow: 0 0 0 10px rgba(128,0,0,0.06); }
    }

    /* Timeline */
    .tl-item {
        display: flex;
        gap: 16px;
        padding-bottom: 28px;
        position: relative;
    }
    .tl-item:last-child { padding-bottom: 0; }
    .tl-item::before {
        content: '';
        position: absolute;
        left: 19px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .tl-item:last-child::before { display: none; }
    .tl-item.tl-current::before { background: rgba(128,0,0,0.2); }
    .tl-dot {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    .tl-dot-completed { background:#dcfce7; color:#16a34a; border:2px solid #22c55e; }
    .tl-dot-current   { background:#800000; color:white;   border:2px solid #800000; box-shadow:0 0 0 4px rgba(128,0,0,0.15); }
    .tl-dot-pending   { background:#f3f4f6; color:#9ca3af; border:2px solid #e5e7eb; }

    /* Status badges */
    .status-badge {
        display: inline-flex; align-items: center;
        padding: 6px 14px; border-radius: 20px;
        font-size: 13px; font-weight: 600;
    }
    .s-pending, .s-pending_confirmation { background:#fef3c7; color:#92400e; }
    .s-verification_pending             { background:#ffedd5; color:#9a3412; }
    .s-confirmed                        { background:#d1fae5; color:#065f46; }
    .s-processing                       { background:#dbeafe; color:#1e40af; }
    .s-packed                           { background:#ede9fe; color:#5b21b6; }
    .s-shipped                          { background:#e0e7ff; color:#4338ca; }
    .s-out_for_delivery                 { background:#fce7f3; color:#9d174d; }
    .s-delivered                        { background:#ccfbf1; color:#0f766e; }
    .s-completed, .s-order_received     { background:#d1fae5; color:#065f46; }
    .s-cancelled                        { background:#fee2e2; color:#991b1b; }

    .pulse-dot {
        width:10px; height:10px; border-radius:50%; background:#800000;
        animation: pulseDot 1.5s infinite;
        display: inline-block; margin-right: 6px;
    }
    @keyframes pulseDot {
        0%, 100% { transform: scale(1); opacity:1; }
        50% { transform: scale(1.4); opacity:0.6; }
    }

    /* Bundle Styles for Track Order */
    .track-bundle-badge {
        display: inline-block;
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        margin-left: 6px;
        vertical-align: middle;
        box-shadow: 0 2px 4px rgba(128, 0, 0, 0.2);
    }

    .track-bundle-toggle {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 2px solid #800000;
        border-radius: 8px;
        padding: 6px 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        font-size: 11px;
        font-weight: 600;
        color: #800000;
    }

    .track-bundle-toggle:hover {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-color: #600000;
        transform: translateX(2px);
        box-shadow: 0 2px 6px rgba(128, 0, 0, 0.15);
    }

    .track-bundle-toggle-icon {
        transition: transform 0.3s ease;
        color: #800000;
    }

    .track-bundle-toggle-icon.expanded {
        transform: rotate(180deg);
    }

    .track-bundle-items-list {
        max-height: 0 !important;
        overflow: hidden !important;
        transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
        opacity: 0 !important;
        margin-top: 0;
        visibility: hidden;
    }

    .track-bundle-items-list.expanded {
        max-height: 500px !important;
        opacity: 1 !important;
        margin-top: 8px;
        visibility: visible;
    }

    .track-bundle-item-card {
        background: white;
        border-radius: 6px;
        padding: 8px 10px;
        margin-bottom: 6px;
        border: 1px solid #fee2e2;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }

    .track-bundle-item-card:hover {
        background: #fef2f2;
        border-color: #fecaca;
        box-shadow: 0 2px 4px rgba(128, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')

@php
    $orderStatus = $order->status;
    $trackStatus = $order->tracking_status; // null if not set — do NOT fallback here
    $isCompleted = in_array($orderStatus, ['completed', 'delivered']);
    $isCancelled = in_array($orderStatus, ['cancelled', 'refunded']);

    $stepIndex = match(true) {
        $orderStatus === 'completed'                                               => 5,
        $orderStatus === 'delivered' || $trackStatus === 'Delivered'               => 5,
        $trackStatus === 'Out for Delivery'                                        => 4,
        in_array($trackStatus, ['Shipped']) || $orderStatus === 'shipped'          => 3,
        in_array($trackStatus, ['Packed','Processing']) || $orderStatus === 'processing' => 2,
        in_array($orderStatus, ['confirmed','verification_pending'])               => 1,
        default                                                                    => 0,
    };

    $steps = [
        ['label'=>'Order Placed',     'short'=>'Placed',    'date'=>$order->created_at],
        ['label'=>'Confirmed',        'short'=>'Confirmed', 'date'=>$order->confirmed_at],
        ['label'=>'Processing',       'short'=>'Processing','date'=>null],
        ['label'=>'Shipped',          'short'=>'Shipped',   'date'=>$order->shipped_at],
        ['label'=>'Out for Delivery', 'short'=>'Delivery',  'date'=>null],
        ['label'=>'Delivered',        'short'=>'Delivered', 'date'=>$order->delivered_at],
    ];

    $statusLabel = match($orderStatus) {
        'pending_confirmation' => 'Pending Confirmation',
        'verification_pending' => 'Payment Verification',
        'completed'            => 'Order Received',
        'processing'           => 'Processing',
        'confirmed'            => 'Confirmed',
        'shipped'              => 'Shipped',
        'delivered'            => 'Delivered',
        default                => ucfirst($orderStatus),
    };
    // Only override with tracking_status if admin has explicitly set it
    if ($order->tracking_status && !in_array($orderStatus, ['completed','cancelled','refunded'])) {
        $statusLabel = $order->tracking_status;
    }
    $statusSlug = strtolower(str_replace([' ','/'], ['_','_'], $statusLabel));

    $rawHistory = is_array($order->tracking_history)
        ? $order->tracking_history
        : (is_string($order->tracking_history) ? json_decode($order->tracking_history, true) : []);
    $rawHistory = $rawHistory ?? [];

    $timelineEvents = collect($rawHistory);
    if ($timelineEvents->isEmpty()) {
        $timelineEvents = collect();
        $timelineEvents->push(['status'=>'Order Placed','date'=>$order->created_at->format('M d, Y h:i A'),'note'=>'Your order has been received and is awaiting confirmation.','type'=>'placed']);
        if ($order->confirmed_at) {
            $timelineEvents->push(['status'=>'Order Confirmed','date'=>\Carbon\Carbon::parse($order->confirmed_at)->format('M d, Y h:i A'),'note'=>'Your order has been confirmed and will begin processing soon.','type'=>'confirmed']);
        }
        if (in_array($orderStatus,['processing']) || in_array($trackStatus,['Processing','Packed'])) {
            $noteText = $trackStatus === 'Packed' ? 'Your items have been carefully packed and are ready for shipping.' : 'Your order is being prepared and items are being packed.';
            $timelineEvents->push(['status'=>($trackStatus === 'Packed' ? 'Packed' : 'Processing'),'date'=>$order->updated_at->format('M d, Y'),'note'=>$noteText,'type'=>'processing']);
        }
        if ($order->shipped_at || $orderStatus === 'shipped' || in_array($trackStatus,['Shipped','Out for Delivery','Delivered'])) {
            $shipDate = $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('M d, Y h:i A') : $order->updated_at->format('M d, Y');
            $timelineEvents->push(['status'=>'Shipped','date'=>$shipDate,'note'=>'Your order has been handed over to the courier for delivery.','type'=>'shipped']);
        }
        if (in_array($trackStatus,['Out for Delivery','Delivered']) || $isCompleted) {
            $timelineEvents->push(['status'=>'Out for Delivery','date'=>$order->updated_at->format('M d, Y'),'note'=>'Your order is out for delivery and will arrive today.','type'=>'out_for_delivery']);
        }
        if ($order->delivered_at || $isCompleted) {
            $delDate = $order->delivered_at ? \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y h:i A') : $order->updated_at->format('M d, Y h:i A');
            $delNote = $orderStatus === 'completed' ? 'Order received and confirmed by customer. Thank you for shopping with us!' : 'Your order has arrived at the delivery address.';
            $timelineEvents->push(['status'=>'Delivered','date'=>$delDate,'note'=>$delNote,'type'=>'delivered']);
        }
    }
    $timelineEvents = $timelineEvents->reverse()->values();

    $shippingParts = array_filter([$order->shipping_address, $order->shipping_city, $order->shipping_province]);
    $fullAddress   = implode(', ', $shippingParts) ?: ($order->delivery_address ?? null);

    $shippingFee = (float)($order->shipping_fee ?? 0);
    if (($order->delivery_type ?? 'delivery') === 'pickup') $shippingFee = 0;
    $subtotal    = $order->subtotal !== null ? (float)$order->subtotal : max(((float)($order->total_amount ?? 0)) - $shippingFee, 0);
    $discountAmt = (float)($order->discount_amount ?? $order->discount ?? 0);
@endphp

{{-- HERO --}}
<section class="tracking-hero py-10 relative">
    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="text-white">
                <p class="text-sm font-medium opacity-75 mb-1 uppercase tracking-wider">Order Tracking</p>
                <h1 class="text-3xl lg:text-4xl font-bold mb-1">{{ $order->tracking_number }}</h1>
                <p class="text-white/70 text-sm">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
            <div class="flex flex-col items-start sm:items-end gap-3">
                <span class="status-badge s-{{ $statusSlug }} flex items-center">
                    @if(!$isCancelled && !$isCompleted)
                        <span class="pulse-dot"></span>
                    @elseif($isCompleted)
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @endif
                    {{ $statusLabel }}
                </span>
                <a href="{{ route('track-order.index') }}" class="text-white/70 hover:text-white text-sm underline-offset-2 hover:underline transition-colors">
                    &larr; Back to My Orders
                </a>
            </div>
        </div>
    </div>
</section>

{{-- PROGRESS STEPPER --}}
@if(!$isCancelled)
<div class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <div class="stepper">
            @foreach($steps as $i => $step)
                @php
                    $stepState = $i < $stepIndex ? 'completed' : ($i === $stepIndex ? 'active' : 'pending');
                    $stepDate  = $step['date'] ? \Carbon\Carbon::parse($step['date'])->format('M d') : null;
                @endphp
                <div class="step {{ $stepState }}">
                    @if($i > 0)
                        <div class="step-connector">
                            <div style="height:3px; background: {{ $i <= $stepIndex ? '#800000' : '#e5e7eb' }}; width:100%;"></div>
                        </div>
                    @endif
                    <div class="step-icon">
                        @if($stepState === 'completed')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @elseif($step['label'] === 'Order Placed')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @elseif($step['label'] === 'Confirmed')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($step['label'] === 'Processing')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        @elseif($step['label'] === 'Shipped')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        @elseif($step['label'] === 'Out for Delivery')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        @endif
                    </div>
                    <span class="step-label">{{ $step['short'] }}</span>
                    @if($stepDate && $stepState !== 'pending')
                        <span class="step-date">{{ $stepDate }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- CANCELLED BANNER --}}
@if($isCancelled)
<div class="max-w-5xl mx-auto px-4 sm:px-6 mt-6">
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-red-800">Order {{ ucfirst($orderStatus) }}</h3>
            <p class="text-sm text-red-600 mt-1">This order has been {{ $orderStatus }}. If you have questions, please contact our support team.</p>
        </div>
    </div>
</div>
@endif

{{-- MAIN CONTENT --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- LEFT: Status + Timeline + Items --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Current Status Card --}}
            <div class="tracking-card p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center" style="background: rgba(128,0,0,0.08);">
                        @if($isCompleted)
                            <svg class="w-7 h-7" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($trackStatus === 'Shipped' || $orderStatus === 'shipped')
                            <svg class="w-7 h-7" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        @elseif($trackStatus === 'Out for Delivery')
                            <svg class="w-7 h-7" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        @else
                            <svg class="w-7 h-7" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-xl font-bold text-gray-900">{{ $statusLabel }}</h2>
                            @if(!$isCancelled && !$isCompleted)
                                <span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Live</span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            @switch(true)
                                @case($orderStatus === 'completed')
                                    Your order has been delivered and confirmed. Thank you for shopping with Yakan!
                                    @break
                                @case($trackStatus === 'Delivered')
                                    Your order has been delivered! Please confirm receipt if you have not already.
                                    @break
                                @case($trackStatus === 'Out for Delivery')
                                    Your order is on its way and will arrive today. Please be available to receive it.
                                    @break
                                @case($trackStatus === 'Shipped')
                                    Your order has been shipped and is in transit.
                                    @if($order->estimated_delivery_date)
                                        Expected delivery: <strong>{{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('F d, Y') }}</strong>.
                                    @endif
                                    @break
                                @case(in_array($trackStatus, ['Packed', 'Processing']) || $orderStatus === 'processing')
                                    Your items are being carefully prepared and packed for shipment.
                                    @break
                                @case($orderStatus === 'confirmed')
                                    Your order has been confirmed and will enter production/processing shortly.
                                    @break
                                @case($orderStatus === 'verification_pending')
                                    Your payment is being verified. We will confirm your order once verified.
                                    @break
                                @default
                                    Your order has been received. We will update you as it progresses.
                            @endswitch
                        </p>
                        @if($order->estimated_delivery_date && !$isCompleted && $trackStatus !== 'Shipped')
                            <div class="mt-3 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm font-medium text-amber-800">Est. Delivery: {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('F d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Order Progress Timeline --}}
            <div class="tracking-card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Order History
                </h3>
                <div class="space-y-0">
                    @foreach($timelineEvents as $idx => $event)
                        @php
                            $isCurrent = $idx === 0;
                            $dotClass  = $isCurrent ? 'tl-dot-current' : 'tl-dot-completed';
                            $eventType = $event['type'] ?? 'update';
                        @endphp
                        <div class="tl-item {{ $isCurrent ? 'tl-current' : '' }}">
                            <div class="tl-dot {{ $dotClass }}">
                                @if($eventType === 'delivered')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @elseif($eventType === 'out_for_delivery')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                @elseif($eventType === 'shipped')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                @elseif($eventType === 'placed')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                            </div>
                            <div class="pb-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold {{ $isCurrent ? 'text-gray-900' : 'text-gray-700' }} text-sm">{{ $event['status'] ?? 'Update' }}</h4>
                                    @if($isCurrent && !$isCancelled)
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full font-semibold">Current</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">@php
                                try { echo $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('M d, Y h:i A') : ''; }
                                catch(\Exception $e) { echo $event['date'] ?? ''; }
                            @endphp</p>
                                @if(!empty($event['note']))
                                    <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">{{ $event['note'] }}</p>
                                @endif
                                @if($isCurrent && $order->tracking_notes)
                                    <div class="mt-2 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                                        <p class="text-xs text-blue-700 font-medium">Note from Yakan:</p>
                                        <p class="text-sm text-blue-800 mt-0.5">{{ $order->tracking_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Items --}}
            <div class="tracking-card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Items Ordered
                    <span class="ml-auto text-sm font-normal text-gray-500">{{ $order->orderItems->count() }} item(s)</span>
                </h3>
                <div class="space-y-3">
                    @foreach($order->orderItems as $item)
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors">
                            @if($item->product && $item->product->hasImage())
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-16 h-16 object-cover rounded-xl border border-gray-200 flex-shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-xl border border-gray-200 bg-white flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 truncate">
                                    {{ $item->product->name ?? $item->product_name ?? 'Product' }}
                                    @if($item->product && $item->product->is_bundle)
                                        <span class="track-bundle-badge">Bundle</span>
                                    @endif
                                </h4>
                                @if($item->product && $item->product->category && !$item->product->is_bundle)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->product->category->name }}</p>
                                @endif
                                @if($item->product && $item->product->is_bundle && $item->product->bundleItems && $item->product->bundleItems->count() > 0)
                                    <div class="track-bundle-toggle" onclick="toggleTrackBundleItems({{ $item->id }})">
                                        <span>📦</span>
                                        <span>{{ $item->product->bundleItems->count() }} items · Tap to see details</span>
                                        <svg class="track-bundle-toggle-icon" id="track-bundle-icon-{{ $item->id }}" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                    <div class="track-bundle-items-list" id="track-bundle-list-{{ $item->id }}" style="display: none;">
                                        @foreach($item->product->bundleItems as $bundleItem)
                                            @if($bundleItem->componentProduct)
                                                <div class="track-bundle-item-card">
                                                    <img src="{{ $bundleItem->componentProduct->image_src }}" 
                                                         alt="{{ $bundleItem->componentProduct->name }}" 
                                                         class="w-10 h-10 object-cover rounded border border-gray-300">
                                                    <div class="flex-1 text-sm">
                                                        <div class="font-semibold text-gray-900">{{ $bundleItem->componentProduct->name }}</div>
                                                        <div class="text-gray-600 text-xs mt-0.5">Qty: {{ (int) $bundleItem->quantity * (int) $item->quantity }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="text-xs bg-gray-200 text-gray-700 rounded px-2 py-0.5 font-medium">Qty: {{ $item->quantity }}</span>
                                    <span class="text-xs text-gray-500">&#8369;{{ number_format($item->price, 2) }} each</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-gray-900">&#8369;{{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 pt-5 border-t border-gray-100 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900">&#8369;{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($discountAmt > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
                            <span class="font-medium">-&#8369;{{ number_format($discountAmt, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Shipping Fee</span>
                        <span class="font-medium text-gray-900">
                            @if($shippingFee > 0)
                                &#8369;{{ number_format($shippingFee, 2) }}
                            @else
                                <span class="text-green-600">Free</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="font-bold text-lg" style="color:#800000;">&#8369;{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-5">

            {{-- Confirm Received CTA --}}
            @if($order->tracking_status === 'Out for Delivery' && $orderStatus !== 'completed' && $orderStatus !== 'delivered')
                <div class="tracking-card p-5 bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1">Order Arriving Today!</h3>
                        <p class="text-sm text-gray-600 mb-4">Received your order? Confirm and leave a review.</p>
                        <a href="{{ route('reviews.create.order', $order->id) }}" class="block w-full px-4 py-3 text-white font-semibold rounded-xl hover:opacity-90 transition-opacity text-center" style="background-color: #22c55e;">
                            &#10003; Confirm Receipt &amp; Review
                        </a>
                    </div>
                </div>
            @endif

            @if($orderStatus === 'delivered')
                <div class="tracking-card p-5 bg-gradient-to-br from-teal-50 to-green-50 border border-teal-200">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1">Order Delivered!</h3>
                        <p class="text-sm text-gray-600 mb-4">Confirm you have received it and share your experience.</p>
                        <a href="{{ route('reviews.create.order', $order->id) }}" class="block w-full px-4 py-3 text-white font-semibold rounded-xl hover:opacity-90 transition-opacity text-center" style="background-color: #800000;">
                            Confirm &amp; Leave a Review
                        </a>
                    </div>
                </div>
            @endif

            {{-- Order Details --}}
            <div class="tracking-card p-5">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Order Details
                </h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Order ID</dt>
                        <dd class="font-semibold text-gray-900 font-mono text-xs">{{ $order->order_ref ?? '#'.$order->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tracking #</dt>
                        <dd class="font-semibold text-gray-900 font-mono text-xs">{{ $order->tracking_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Order Date</dt>
                        <dd class="font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Delivery Type</dt>
                        <dd class="font-semibold text-gray-900">
                            {!! ($order->delivery_type ?? 'delivery') === 'pickup' ? '🏬 Store Pickup' : '🚚 Delivery' !!}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Payment</dt>
                        <dd class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500">Payment Status</dt>
                        <dd>
                            @php $ps = $order->payment_status ?? 'pending'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ps === 'paid' ? 'bg-green-100 text-green-700' : ($ps === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($ps) }}
                            </span>
                        </dd>
                    </div>
                    @if($order->payment_verified_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Verified At</dt>
                            <dd class="font-semibold text-gray-900 text-xs">{{ \Carbon\Carbon::parse($order->payment_verified_at)->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                    @if($order->delivered_at && $isCompleted)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Delivered On</dt>
                            <dd class="font-semibold text-green-700 text-xs">{{ \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Courier Info --}}
            @if($order->courier_name)
                <div class="tracking-card p-5">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        Courier
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 text-xs">Courier Name</dt>
                            <dd class="font-bold text-gray-900 text-base mt-0.5">{{ $order->courier_name }}</dd>
                        </div>
                        @if($order->courier_contact)
                            <div>
                                <dt class="text-gray-500 text-xs">Contact</dt>
                                <dd class="font-semibold text-gray-900 mt-0.5">{{ $order->courier_contact }}</dd>
                            </div>
                        @endif
                        @if($order->courier_tracking_url)
                            <a href="{{ $order->courier_tracking_url }}" target="_blank" rel="noopener noreferrer"
                               class="mt-2 flex items-center justify-center gap-2 w-full px-4 py-2.5 text-white text-sm font-semibold rounded-xl hover:opacity-90 transition-opacity"
                               style="background-color:#800000;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Track on Courier Site
                            </a>
                        @endif
                    </dl>
                </div>
            @endif

            {{-- Delivery Address --}}
            <div class="tracking-card p-5">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ ($order->delivery_type ?? 'delivery') === 'pickup' ? 'Pickup Details' : 'Delivery Address' }}
                </h3>
                <div class="text-sm space-y-1.5">
                    <p class="font-semibold text-gray-900">{{ $order->user->name ?? $order->customer_name ?? 'Customer' }}</p>
                    @php $email = $order->user->email ?? $order->customer_email ?? null; @endphp
                    @if($email)
                        <p class="text-gray-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $email }}
                        </p>
                    @endif
                    @php $phone = $order->user->phone ?? $order->customer_phone ?? null; @endphp
                    @if($phone)
                        <p class="text-gray-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $phone }}
                        </p>
                    @endif
                    @if($fullAddress)
                        <div class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <p class="text-gray-700 leading-relaxed">{{ $fullAddress }}</p>
                        </div>
                    @elseif(($order->delivery_type ?? 'delivery') === 'pickup')
                        <div class="mt-2 p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <p class="text-amber-800 font-medium text-xs">Store Pickup</p>
                            <p class="text-amber-700 text-sm mt-0.5">Please pick up your order at our store.</p>
                        </div>
                    @else
                        <p class="text-gray-400 text-xs mt-1 italic">Address details not available.</p>
                    @endif
                </div>
            </div>

            {{-- Need Help --}}
            <div class="tracking-card p-5 border-l-4" style="border-left-color:#800000;">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:#800000" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1 text-sm">Need Help?</h4>
                        <p class="text-xs text-gray-600 mb-3">Questions about your order? Our team is happy to assist.</p>
                        <a href="{{ route('chats.index') }}" class="text-sm font-semibold hover:underline" style="color:#800000;">
                            Contact Support &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- View All Orders --}}
            <a href="{{ route('track-order.index') }}"
               class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl border-2 font-semibold text-sm hover:bg-gray-50 transition-colors"
               style="border-color:#800000; color:#800000;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                View All My Orders
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTrackBundleItems(itemId) {
    const list = document.getElementById('track-bundle-list-' + itemId);
    const icon = document.getElementById('track-bundle-icon-' + itemId);
    
    if (list && icon) {
        const isExpanded = list.classList.contains('expanded');
        
        if (isExpanded) {
            list.classList.remove('expanded');
            icon.classList.remove('expanded');
            list.style.display = 'none';
        } else {
            list.style.display = 'block';
            list.classList.add('expanded');
            icon.classList.add('expanded');
        }
    }
}
</script>
@endpush
