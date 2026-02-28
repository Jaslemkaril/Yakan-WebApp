@extends('layouts.admin')

@section('title', 'Custom Orders Management')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(128, 0, 0, 0.15);
    }
    .order-row {
        transition: background-color 0.15s;
    }
    .order-row:hover {
        background-color: #fdf8f8;
    }
    .preview-hover {
        position: relative;
    }
    .preview-hover .preview-popup {
        display: none;
        position: absolute;
        left: 55px;
        top: -30px;
        z-index: 9999;
        pointer-events: none;
    }
    .preview-hover:hover .preview-popup {
        display: block;
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
    .pill-pending     { background: #FEF3C7; color: #92400E; }
    .pill-approved    { background: #DBEAFE; color: #1E40AF; }
    .pill-processing  { background: #E0E7FF; color: #3730A3; }
    .pill-in_production { background: #FDE68A; color: #78350F; }
    .pill-price_quoted { background: #E9D5FF; color: #6B21A8; }
    .pill-production_complete { background: #D1FAE5; color: #065F46; }
    .pill-out_for_delivery { background: #CFFAFE; color: #155E75; }
    .pill-delivered   { background: #D1FAE5; color: #065F46; }
    .pill-completed   { background: #BBF7D0; color: #14532D; }
    .pill-cancelled   { background: #FEE2E2; color: #991B1B; }
    .pill-rejected    { background: #FEE2E2; color: #991B1B; }
    .pill-paid        { background: #D1FAE5; color: #065F46; }
    .pill-unpaid      { background: #FEF3C7; color: #92400E; }
    .pill-pending_verification { background: #FDE68A; color: #78350F; }
    .pill-failed      { background: #FEE2E2; color: #991B1B; }
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
</style>
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto px-4 py-6">

    {{-- ========== HEADER ========== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Custom Orders</h1>
            <p class="text-gray-500 mt-1 text-sm">Manage and track all custom weaving orders</p>
        </div>
        <a href="{{ route('admin.custom_orders.production-dashboard') }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-semibold shadow-md hover:shadow-lg transition-all text-sm"
           style="background: linear-gradient(135deg, #800000 0%, #a00000 100%);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Production Dashboard
        </a>
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
        <form method="GET" action="{{ route('admin.custom_orders.index') }}" id="filterForm">
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
                    <a href="{{ route('admin.custom_orders.index') }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" 
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
                <tbody class="divide-y divide-gray-50">
                    @foreach($orders as $order)
                    <tr class="order-row">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-gray-900">#{{ $order->id }}</span>
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
                                <div class="preview-hover">
                                    <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center p-1 overflow-hidden">
                                        <div style="{{ $filterStyle }} transform-origin: center; width: 100%; height: 100%;">
                                            {!! $patternModel->getSvgContent() !!}
                                        </div>
                                    </div>
                                    <div class="preview-popup w-48 h-48 bg-white rounded-xl shadow-2xl border border-gray-200 p-2">
                                        <div class="w-full h-40 bg-gray-50 rounded-lg flex items-center justify-center p-3 overflow-hidden">
                                            <div style="{{ $filterStyle }} transform-origin: center; width: 100%; height: 100%;">
                                                {!! $patternModel->getSvgContent() !!}
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-center font-semibold text-gray-600 mt-1">{{ $patternModel->name }}</p>
                                    </div>
                                </div>
                            @elseif($order->design_upload)
                                <div class="preview-hover">
                                    @php $imgSrc = str_starts_with($order->design_upload, 'data:image') ? $order->design_upload : asset('storage/' . $order->design_upload); @endphp
                                    <img src="{{ $imgSrc }}" alt="Preview" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                    <div class="preview-popup w-48 h-48 bg-white rounded-xl shadow-2xl border border-gray-200 p-2">
                                        <img src="{{ $imgSrc }}" alt="Preview" class="w-full h-full object-contain rounded-lg">
                                    </div>
                                </div>
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
                            <span class="font-bold text-gray-900">₱{{ number_format($order->final_price ?? $order->estimated_price ?? 0, 2) }}</span>
                        </td>

                        <td class="px-4 py-3.5">
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
                            <p class="text-sm text-gray-800">{{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-[11px] text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.custom_orders.show', $order->id) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}"
                                   class="action-btn bg-gray-100 hover:bg-gray-200 text-gray-700" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($order->status === 'pending')
                                    <form action="{{ route('admin.custom_orders.approve', $order->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Approve this order?')">
                                        @csrf
                                        @if(request('auth_token'))
                                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                        @endif
                                        <button type="submit" class="action-btn bg-green-50 hover:bg-green-100 text-green-700" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.custom_orders.reject', $order->id) }}" method="POST" class="inline"
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
@endsection

@push('scripts')
<script>
function getAuthToken() {
    const p = new URLSearchParams(window.location.search);
    return p.get('auth_token') || sessionStorage.getItem('auth_token') || '';
}
function adminUrl(path) {
    const t = getAuthToken();
    return t ? path + (path.includes('?') ? '&' : '?') + 'auth_token=' + encodeURIComponent(t) : path;
}
function updateOrderStatus(orderId, newStatus) {
    if (confirm('Are you sure you want to update this order status?')) {
        fetch(adminUrl(`/admin/custom-orders/${orderId}/status`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + (data.message || 'Unknown'));
        })
        .catch(e => { console.error(e); alert('Error updating status'); });
    }
}
</script>
@endpush
