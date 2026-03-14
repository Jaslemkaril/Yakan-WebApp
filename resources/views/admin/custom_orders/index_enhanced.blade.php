@extends('layouts.admin')

@section('title', 'Custom Orders Management')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px -5px rgba(128, 0, 0, 0.18);
    }
    .order-row {
        transition: background-color 0.15s;
    }
    .order-row:hover {
        background-color: #fdf8f8;
    }
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.025em;
        white-space: nowrap;
    }
    .pill-pending       { background: #FEF3C7; color: #92400E; }
    .pill-approved      { background: #DBEAFE; color: #1E40AF; }
    .pill-processing    { background: #E0E7FF; color: #3730A3; }
    .pill-in_production { background: #FDE68A; color: #78350F; }
    .pill-price_quoted  { background: #E9D5FF; color: #6B21A8; }
    .pill-production_complete { background: #D1FAE5; color: #065F46; }
    .pill-out_for_delivery    { background: #CFFAFE; color: #155E75; }
    .pill-delivered     { background: #D1FAE5; color: #065F46; }
    .pill-completed     { background: #BBF7D0; color: #14532D; }
    .pill-cancelled     { background: #FEE2E2; color: #991B1B; }
    .pill-rejected      { background: #FEE2E2; color: #991B1B; }
    .pill-paid          { background: #D1FAE5; color: #065F46; }
    .pill-unpaid        { background: #FEF3C7; color: #92400E; }
    .pill-pending_verification { background: #FDE68A; color: #78350F; }
    .pill-failed        { background: #FEE2E2; color: #991B1B; }
    .status-dot {
        width: 6px; height: 6px; border-radius: 50%; display: inline-block;
    }
    .status-dot.pulse { animation: dotPulse 1.5s infinite; }
    @keyframes dotPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px; height: 32px;
        border-radius: 8px;
        transition: all 0.15s;
    }
    .action-btn:hover { transform: scale(1.1); }

    /* Quick-status dropdown */
    .status-dropdown-wrap { position: relative; display: inline-block; }
    .status-dropdown-menu {
        display: none;
        position: absolute;
        right: 0; top: calc(100% + 4px);
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        min-width: 180px;
        z-index: 50;
        overflow: hidden;
    }
    .status-dropdown-menu.open { display: block; }
    .status-dropdown-menu button {
        display: block; width: 100%;
        text-align: left; padding: 9px 14px;
        font-size: 12px; font-weight: 500; color: #374151;
        background: none; border: none; cursor: pointer;
        transition: background 0.1s;
    }
    .status-dropdown-menu button:hover { background: #f3f4f6; }
    .status-dropdown-menu .menu-label {
        font-size: 10px; font-weight: 700; color: #9ca3af;
        padding: 8px 14px 4px; text-transform: uppercase; letter-spacing: 0.06em;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Toast */
    #toast {
        position: fixed; bottom: 24px; right: 24px;
        display: flex; align-items: center; gap: 10px;
        background: #1f2937; color: white;
        padding: 12px 18px; border-radius: 12px;
        font-size: 13px; font-weight: 500;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        transform: translateY(80px); opacity: 0;
        transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
        z-index: 9999;
    }
    #toast.show { transform: translateY(0); opacity: 1; }
    #toast.success { background: #065f46; }
    #toast.error { background: #991b1b; }
</style>
@endpush

@section('content')
@php
    // Ensure these variables always exist regardless of which controller renders this view
    $batchCountMap    = $batchCountMap    ?? ($stats['batchCountMap']    ?? []);
    $batchMetaMap     = $batchMetaMap     ?? ($stats['batchMetaMap']     ?? []);
    $implicitCountMap = $implicitCountMap ?? ($stats['implicitCountMap'] ?? []);
    $implicitMetaMap  = $implicitMetaMap  ?? ($stats['implicitMetaMap']  ?? []);
    $batchSiblingsMap = $batchSiblingsMap ?? collect();
    // Unpack $stats into top-level vars if provided that way
    if (isset($stats)) {
        $totalOrders      = $totalOrders      ?? ($stats['totalOrders']      ?? 0);
        $todayOrders      = $todayOrders      ?? ($stats['todayOrders']      ?? 0);
        $pendingCount     = $pendingCount     ?? ($stats['pendingCount']     ?? 0);
        $approvedCount    = $approvedCount    ?? ($stats['approvedCount']    ?? 0);
        $inProductionCount= $inProductionCount?? ($stats['inProductionCount']?? 0);
        $totalRevenue     = $totalRevenue     ?? ($stats['totalRevenue']     ?? 0);
    }
    $customOrderEstimatedDays = (int) \App\Models\SystemSetting::get('custom_order_estimated_days', 14);
@endphp
<div class="max-w-[1400px] mx-auto px-4 py-6">

    {{-- ========== HEADER ========== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Custom Orders</h1>
            <p class="text-gray-500 mt-1 text-sm">Manage and track all custom weaving orders</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.custom-orders.production-dashboard') }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white font-semibold shadow-md hover:shadow-lg transition-all text-sm"
               style="background: linear-gradient(135deg, #800000 0%, #a00000 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Production Dashboard
            </a>
        </div>
    </div>

    {{-- ========== STAT CARDS ========== --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #FDF2F2;">
                    <svg class="w-5 h-5" fill="none" stroke="#800000" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalOrders ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-50">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Today</p>
                    <p class="text-xl font-bold text-blue-700">{{ $todayOrders ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-amber-50">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Pending</p>
                    <p class="text-xl font-bold text-amber-700">{{ $pendingCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-indigo-50">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Approved</p>
                    <p class="text-xl font-bold text-indigo-700">{{ $approvedCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-50">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">In Production</p>
                    <p class="text-xl font-bold text-orange-700">{{ $inProductionCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-green-50">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Revenue</p>
                    <p class="text-lg font-bold text-green-700">₱{{ number_format($totalRevenue ?? 0, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== FILTERS ========== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ route('admin.custom-orders.index') }}" id="filterForm">
            @if(request('auth_token'))
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, ID..."
                               class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/30 focus:border-[#800000] transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" onchange="document.getElementById('filterForm').submit()"
                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/30 focus:border-[#800000] transition-all">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="price_quoted" {{ request('status') == 'price_quoted' ? 'selected' : '' }}>Price Quoted</option>
                        <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>In Production</option>
                        <option value="production_complete" {{ request('status') == 'production_complete' ? 'selected' : '' }}>Production Complete</option>
                        <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/30 focus:border-[#800000] transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/30 focus:border-[#800000] transition-all">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm text-white font-semibold rounded-lg shadow-sm hover:shadow transition-all" style="background-color: #800000;">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.custom-orders.index') }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}"
                       class="px-3 py-2.5 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-all font-medium">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ========== ORDERS TABLE ========== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">
                Orders
                @if($orders->total() > 0)
                    <span class="ml-2 text-xs font-normal text-gray-400">({{ $orders->total() }} total{{ request('status') ? ', filtered by ' . ucfirst(str_replace('_', ' ', request('status'))) : '' }})</span>
                @endif
            </h2>
            <p class="text-xs text-gray-400 hidden sm:block">Click <strong>⋯</strong> to quick-update status</p>
        </div>

        @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase tracking-wider" style="background-color: #FAFAFA;">
                        <th class="px-5 py-3 text-left font-semibold">Order</th>
                        <th class="px-4 py-3 text-left font-semibold">Customer</th>
                        <th class="px-4 py-3 text-left font-semibold">Preview</th>
                        <th class="px-4 py-3 text-left font-semibold">Fabric</th>
                        <th class="px-4 py-3 text-left font-semibold">Price</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Payment</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="ordersTableBody">
                    @foreach($orders as $order)
                    @php
                        $batchCount = !empty($order->batch_order_number)
                            ? ($batchCountMap[$order->batch_order_number] ?? 1)
                            : ($implicitCountMap[$order->id] ?? 1);
                        $isBatchPrimary  = $batchCount > 1;
                        $isImplicitBatch = $isBatchPrimary && empty($order->batch_order_number);
                    @endphp
                    <tr class="order-row{{ $isBatchPrimary ? ' bg-red-50' : '' }}" id="row-{{ $order->id }}">
                        <td class="px-5 py-3.5">
                            @if($isBatchPrimary)
                                <div class="flex items-center gap-1 mb-0.5">
                                    <span class="font-bold text-gray-900">#{{ $order->id }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold" style="background-color:#f5e6e8; color:#800000;">
                                        {{ $batchCount }} {{ $batchCount === 1 ? 'item' : 'items' }}
                                    </span>
                                </div>
                            @else
                                <span class="font-bold text-gray-900">#{{ $order->id }}</span>
                            @endif
                            <div class="mt-0.5">
                                @if($order->delivery_type === 'pickup')
                                    <span class="inline-flex items-center text-[10px] text-gray-500 gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        Pickup
                                    </span>
                                @elseif($order->delivery_type === 'delivery')
                                    <span class="inline-flex items-center text-[10px] text-gray-500 gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Delivery
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background-color: #800000;">
                                    {{ strtoupper(substr($order->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate max-w-[160px]">{{ $order->user->name ?? 'N/A' }}</p>
                                    <p class="text-[11px] text-gray-400 truncate max-w-[160px]">{{ $order->user->email ?? $order->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            @php
                                $patternModel = null;
                                if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id'])) {
                                    $patternModel = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                                } elseif (!empty($order->patterns) && is_array($order->patterns)) {
                                    if (is_numeric($order->patterns[0] ?? null)) {
                                        $patternModel = \App\Models\YakanPattern::find($order->patterns[0]);
                                    } elseif (!empty($order->patterns[0])) {
                                        $patternModel = \App\Models\YakanPattern::where('name', $order->patterns[0])->first();
                                    }
                                }
                            @endphp

                            @if($patternModel && $patternModel->hasSvg())
                                @php
                                    $c = $order->customization_settings ?? [];
                                    $filterStyle = sprintf(
                                        'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                        $c['hue'] ?? 0, $c['saturation'] ?? 100, $c['brightness'] ?? 100,
                                        $c['opacity'] ?? 1, $c['scale'] ?? 1, $c['rotation'] ?? 0
                                    );
                                @endphp
                                <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center p-1 overflow-hidden">
                                    <div style="{{ $filterStyle }} transform-origin: center; width: 100%; height: 100%;">
                                        {!! $patternModel->getSvgContent() !!}
                                    </div>
                                </div>
                            @elseif($order->design_upload)
                                @php
                                    $designPath = $order->design_upload;
                                    if (str_starts_with($designPath, 'http://') || str_starts_with($designPath, 'https://')) {
                                        $imgSrc = $designPath;
                                    } elseif (str_starts_with($designPath, 'data:image')) {
                                        $imgSrc = $designPath;
                                    } elseif (str_starts_with($designPath, 'storage/')) {
                                        $imgSrc = asset($designPath);
                                    } else {
                                        $imgSrc = asset('storage/' . $designPath);
                                    }
                                @endphp
                                <img src="{{ $imgSrc }}" alt="Preview"
                                     class="w-12 h-12 rounded-lg object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                     onclick="openImagePreview('{{ $imgSrc }}')"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2212%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23d1d5db%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                            @else
                                <div class="w-12 h-12 bg-gray-50 rounded-lg border border-dashed border-gray-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($order->fabric_type)
                                <p class="font-medium text-gray-800 text-sm">{{ $order->fabric_type_name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $order->formatted_fabric_quantity ?? ($order->fabric_quantity_meters . ' meters') }}</p>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @php
                                $itemDisplayPrice = (float) ($order->final_price ?? $order->estimated_price ?? 0);
                                $itemDeliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                                $itemBreakdown = $order->getPriceBreakdown();
                                $itemDeliveryFeeInBreakdown = (float) (($itemBreakdown['breakdown']['delivery_fee'] ?? 0));
                                if ($itemDeliveryType !== 'pickup' && $itemDeliveryFeeInBreakdown <= 0) {
                                    $itemDisplayPrice += (float) ($order->shipping_fee ?? 0);
                                }
                                $currentBatchMeta = null;
                                if (!empty($order->batch_order_number) && !empty($batchMetaMap[$order->batch_order_number])) {
                                    $currentBatchMeta = $batchMetaMap[$order->batch_order_number];
                                } elseif (!empty($implicitMetaMap[$order->id])) {
                                    $currentBatchMeta = $implicitMetaMap[$order->id];
                                }
                            @endphp

                            @if($currentBatchMeta && ($currentBatchMeta['item_count'] ?? 0) > 1)
                                <div class="text-lg font-bold text-gray-900">₱{{ number_format($currentBatchMeta['batch_total'] ?? 0, 2) }}</div>
                                <div class="text-xs text-gray-500">
                                    Batch total ({{ $currentBatchMeta['item_count'] }} items)
                                </div>
                            @else
                                <span class="font-bold text-gray-900">₱{{ number_format($itemDisplayPrice, 2) }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5" id="status-cell-{{ $order->id }}">
                            @php
                                $isActive = in_array($order->status, ['pending', 'processing', 'in_production', 'approved', 'out_for_delivery']);
                                $dotColor = match($order->status) {
                                    'pending' => '#F59E0B',
                                    'approved' => '#3B82F6',
                                    'processing' => '#6366F1',
                                    'in_production' => '#F97316',
                                    'price_quoted' => '#8B5CF6',
                                    'production_complete' => '#10B981',
                                    'out_for_delivery' => '#06B6D4',
                                    'delivered', 'completed' => '#22C55E',
                                    'cancelled', 'rejected' => '#EF4444',
                                    default => '#6B7280',
                                };
                            @endphp
                            <span class="pill pill-{{ $order->status }}">
                                <span class="status-dot {{ $isActive ? 'pulse' : '' }}" style="background-color: {{ $dotColor }};"></span>
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            @php
                                $ps = $order->payment_status ?? 'unpaid';
                                $pillClass = match($ps) {
                                    'paid' => 'pill-paid',
                                    'pending', 'pending_verification' => 'pill-pending_verification',
                                    'failed' => 'pill-failed',
                                    default => 'pill-unpaid',
                                };
                            @endphp
                            <span class="pill {{ $pillClass }}">
                                {{ ucfirst(str_replace('_', ' ', $ps)) }}
                            </span>
                            @if($order->payment_method)
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ match($order->payment_method) {
                                        'online_banking', 'gcash' => 'GCash',
                                        'bank_transfer' => 'Bank Transfer',
                                        default => ucfirst(str_replace('_', ' ', $order->payment_method))
                                    } }}
                                </p>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @php
                                $estimatedCompletionDate = $order->created_at->copy()->addDays($customOrderEstimatedDays);
                                $isFinishedOrder = in_array($order->status, ['completed', 'delivered', 'cancelled', 'rejected']);
                            @endphp
                            <p class="text-sm text-gray-800">{{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-[11px] text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
                            <p class="text-[11px] font-semibold" style="color:#800000;">Est: {{ $customOrderEstimatedDays }} day{{ $customOrderEstimatedDays === 1 ? '' : 's' }}</p>
                            <p class="text-[10px] {{ $isFinishedOrder ? 'text-gray-400' : 'text-[#800000]' }}">
                                {{ $isFinishedOrder ? 'Target was' : 'Target:' }} {{ $estimatedCompletionDate->format('M d, Y') }}
                            </p>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- View --}}
                                <a href="{{ route('admin.custom-orders.show', $order->id) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}"
                                   class="action-btn bg-gray-100 hover:bg-gray-200 text-gray-700" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                @if($order->status === 'pending')
                                    {{-- Approve --}}
                                    <form action="{{ route('admin.custom-orders.approve', $order->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Approve this order?')">
                                        @csrf
                                        @if(request('auth_token'))
                                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                        @endif
                                        <button type="submit" class="action-btn bg-green-50 hover:bg-green-100 text-green-700" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                    {{-- Reject --}}
                                    <form action="{{ route('admin.custom-orders.reject', $order->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Reject this order?')">
                                        @csrf
                                        @if(request('auth_token'))
                                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                        @endif
                                        <button type="submit" class="action-btn bg-red-50 hover:bg-red-100 text-red-600" title="Reject">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Quick status update dropdown --}}
                                @if(!in_array($order->status, ['completed', 'cancelled', 'rejected']))
                                <div class="status-dropdown-wrap" id="wrap-{{ $order->id }}">
                                    <button type="button" title="Quick Update Status"
                                            class="action-btn bg-gray-100 hover:bg-[#800000] hover:text-white text-gray-600"
                                            onclick="toggleDropdown({{ $order->id }})">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                    <div class="status-dropdown-menu hidden absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg min-w-[180px] z-50 overflow-hidden" id="dropdown-{{ $order->id }}">
                                        <div class="menu-label">Change Status</div>
                                        @foreach([
                                            'approved'             => 'Approved',
                                            'processing'           => 'Processing',
                                            'price_quoted'         => 'Price Quoted',
                                            'in_production'        => 'In Production',
                                            'production_complete'  => 'Production Complete',
                                            'out_for_delivery'     => 'Out for Delivery',
                                            'delivered'            => 'Delivered',
                                            'completed'            => 'Completed',
                                            'cancelled'            => 'Cancelled',
                                        ] as $val => $label)
                                            @if($val !== $order->status)
                                            <button onclick="quickUpdateStatus({{ $order->id }}, '{{ $val }}')">
                                                {{ $label }}
                                            </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
            {{ $orders->links() }}
        </div>
        @endif

        @else
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background-color: #FDF2F2;">
                <svg class="w-8 h-8" fill="none" stroke="#800000" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No custom orders found</h3>
            <p class="text-sm text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
        @endif
    </div>
</div>

{{-- Image preview modal --}}
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm" onclick="closeImagePreview()">
    <div class="relative max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
        <button onclick="closeImagePreview()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="modalImg" src="" alt="Design Preview" class="w-full rounded-xl shadow-2xl object-contain max-h-[80vh]">
    </div>
</div>

{{-- Toast --}}
<div id="toast"></div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function getAuthToken() {
    const p = new URLSearchParams(window.location.search);
    return p.get('auth_token') || sessionStorage.getItem('auth_token') || '';
}

// ---- Image preview ----
function openImagePreview(src) {
    document.getElementById('modalImg').src = src;
    const m = document.getElementById('imageModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeImagePreview() {
    const m = document.getElementById('imageModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// ---- Toast ----
let toastTimer;
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `show ${type}`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.className = ''; }, 3000);
}

// ---- Quick status dropdown ----
function toggleDropdown(id) {
    const targetId = 'dropdown-' + id;
    document.querySelectorAll('.status-dropdown-menu').forEach(el => {
        if (el.id !== targetId) {
            el.classList.add('hidden');
            el.classList.remove('open');
        }
    });

    const target = document.getElementById(targetId);
    if (!target) return;

    const willShow = target.classList.contains('hidden');
    target.classList.toggle('hidden');
    target.classList.toggle('open', willShow);
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.status-dropdown-wrap')) {
        document.querySelectorAll('.status-dropdown-menu').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('open');
        });
    }
});

// ---- Quick status update via AJAX ----
function quickUpdateStatus(orderId, newStatus) {
    const dropdown = document.getElementById('dropdown-' + orderId);
    if (dropdown) {
        dropdown.classList.add('hidden');
        dropdown.classList.remove('open');
    }

    const t = getAuthToken();
    const url = `/admin/custom-orders/${orderId}/status` + (t ? '?auth_token=' + encodeURIComponent(t) : '');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update status pill in DOM
            const cell = document.getElementById('status-cell-' + orderId);
            if (cell) {
                const label = newStatus.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                const dotColors = {
                    pending: '#F59E0B', approved: '#3B82F6', processing: '#6366F1',
                    in_production: '#F97316', price_quoted: '#8B5CF6',
                    production_complete: '#10B981', out_for_delivery: '#06B6D4',
                    delivered: '#22C55E', completed: '#22C55E',
                    cancelled: '#EF4444', rejected: '#EF4444'
                };
                const active = ['pending','processing','in_production','approved','out_for_delivery'].includes(newStatus);
                cell.innerHTML = `<span class="pill pill-${newStatus}">
                    <span class="status-dot ${active ? 'pulse' : ''}" style="background-color:${dotColors[newStatus] || '#6B7280'};"></span>
                    ${label}
                </span>`;

                // Remove approve/reject buttons and dropdown if now completed/cancelled/rejected
                if (['completed','cancelled','rejected'].includes(newStatus)) {
                    document.getElementById('wrap-' + orderId)?.remove();
                }
            }
            showToast(`Order #${orderId} → ${newStatus.replace(/_/g,' ')}`, 'success');
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
}
</script>
@endpush
