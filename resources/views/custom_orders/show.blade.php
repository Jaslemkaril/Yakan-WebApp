@extends('layouts.app')

@section('content')
@php
    $batchOrders = $batchOrders ?? collect([$order]);
    $isBatchOrder = $isBatchOrder ?? ($batchOrders->count() > 1);
    $batchUnpaidOrders = $batchOrders->where('payment_status', '!=', 'paid')->values();
    $batchPaidOrders = $batchOrders->where('payment_status', 'paid')->values();
    
    // Helper function to calculate shipping from address with user default fallback (same as admin)
    $calculateShippingFromAddress = function ($item) {
        $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return 0.0;
        }

        $city = strtolower((string) ($item->delivery_city ?? ''));
        $province = strtolower((string) ($item->delivery_province ?? ''));
        $address = strtolower((string) ($item->delivery_address ?? ''));
        $haystack = trim($address . ' ' . $city . ' ' . $province);

        // Fallback to user's default address if order has no address data
        if ($haystack === '' && !empty($item->user_id)) {
            $defaultAddress = \App\Models\UserAddress::query()
                ->where('user_id', $item->user_id)
                ->where('is_default', true)
                ->first();

            if ($defaultAddress) {
                $fallbackCity = strtolower((string) ($defaultAddress->city ?? ''));
                $fallbackProvince = strtolower((string) ($defaultAddress->province ?? $defaultAddress->region ?? ''));
                $fallbackAddress = strtolower(implode(' ', array_filter([
                    $defaultAddress->street_name ?? null,
                    $defaultAddress->barangay ?? null,
                    $defaultAddress->city ?? null,
                    $defaultAddress->province ?? ($defaultAddress->region ?? null),
                ])));
                $haystack = trim($fallbackAddress . ' ' . $fallbackCity . ' ' . $fallbackProvince);
            }
        }
        
        // Default to ₱100 if still no address
        if ($haystack === '') {
            return 100.0;
        }
        
        // Zone-based shipping
        if (str_contains($haystack, 'zamboanga') || str_contains($haystack, 'barmm') || str_contains($haystack, 'bangsamoro') || str_contains($haystack, 'basilan') || str_contains($haystack, 'sulu') || str_contains($haystack, 'tawi')) {
            return 100.0;
        } elseif (str_contains($haystack, 'mindanao') || str_contains($haystack, 'davao') || str_contains($haystack, 'cagayan de oro') || str_contains($haystack, 'general santos') || str_contains($haystack, 'caraga') || str_contains($haystack, 'soccsksargen') || str_contains($haystack, 'iligan') || str_contains($haystack, 'cotabato')) {
            return 180.0;
        } elseif (str_contains($haystack, 'visaya') || str_contains($haystack, 'cebu') || str_contains($haystack, 'iloilo') || str_contains($haystack, 'bacolod') || str_contains($haystack, 'tacloban') || str_contains($haystack, 'leyte') || str_contains($haystack, 'samar') || str_contains($haystack, 'bohol') || str_contains($haystack, 'negros')) {
            return 250.0;
        } elseif (str_contains($haystack, 'ncr') || str_contains($haystack, 'metro manila') || str_contains($haystack, 'manila') || str_contains($haystack, 'quezon city') || str_contains($haystack, 'makati') || str_contains($haystack, 'calabarzon') || str_contains($haystack, 'central luzon') || str_contains($haystack, 'laguna') || str_contains($haystack, 'cavite') || str_contains($haystack, 'bulacan')) {
            return 300.0;
        }
        return 350.0;
    };
    
    // Get price parts for each order (same logic as admin, with shipping inclusion detection)
    $getPriceParts = function ($item) use ($calculateShippingFromAddress) {
        $quoted = (float) ($item->final_price ?? $item->estimated_price ?? 0);
        $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return ['quoted' => $quoted, 'items_subtotal' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $breakdownWrap = method_exists($item, 'getPriceBreakdown') ? ($item->getPriceBreakdown() ?? []) : [];
        $breakdown = $breakdownWrap['breakdown'] ?? [];
        $material = (float) ($breakdown['material_cost'] ?? 0);
        $pattern = (float) ($breakdown['pattern_fee'] ?? 0);
        $labor = (float) ($breakdown['labor_cost'] ?? 0);
        $discount = (float) ($breakdown['discount'] ?? 0);
        $deliveryFeeInBreakdown = (float) ($breakdown['delivery_fee'] ?? 0);
        $itemsSubtotal = max(($material + $pattern + $labor - $discount), 0);

        if ($deliveryFeeInBreakdown > 0) {
            $subtotalFromBreakdown = $itemsSubtotal > 0 ? $itemsSubtotal : max(($quoted - $deliveryFeeInBreakdown), 0);
            return ['quoted' => $quoted, 'items_subtotal' => $subtotalFromBreakdown, 'shipping' => 0.0, 'total' => $quoted];
        }

        $shipping = $calculateShippingFromAddress($item);

        // quoted already includes shipping
        if ($itemsSubtotal > 0 && abs($quoted - ($itemsSubtotal + $shipping)) < 0.01) {
            return ['quoted' => $quoted, 'items_subtotal' => $itemsSubtotal, 'shipping' => 0.0, 'total' => $quoted];
        }

        // quoted is items subtotal only
        if ($itemsSubtotal > 0 && abs($quoted - $itemsSubtotal) < 0.01) {
            return ['quoted' => $quoted, 'items_subtotal' => $itemsSubtotal, 'shipping' => $shipping, 'total' => $quoted + $shipping];
        }

        // Chat-origin rows can have no breakdown data; use quoted base + shipping once.
        if ($itemsSubtotal <= 0) {
            if (!empty($item->chat_id)) {
                $baseQuoted = (float) ($item->estimated_price ?? 0);
                if ($baseQuoted > 0) {
                    $rowShipping = (float) ($item->shipping_fee ?? 0);
                    if ($rowShipping <= 0) {
                        $rowShipping = $shipping;
                    }
                    return [
                        'quoted' => $baseQuoted,
                        'items_subtotal' => $baseQuoted,
                        'shipping' => $rowShipping,
                        'total' => $baseQuoted + $rowShipping,
                    ];
                }
            }

            // fallback for legacy rows
            return ['quoted' => $quoted, 'items_subtotal' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        return ['quoted' => $quoted, 'items_subtotal' => $itemsSubtotal, 'shipping' => $shipping, 'total' => $itemsSubtotal + $shipping];
    };

    // Build display-ready batch row parts (same fallback behavior as admin details).
    $getBatchDisplayRowParts = function ($item) use ($calculateShippingFromAddress) {
        $quoted = (float) ($item->final_price ?? $item->estimated_price ?? 0);
        $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
        $shippingDisplay = $deliveryType === 'pickup' ? 0.0 : (float) $calculateShippingFromAddress($item);

        $breakdownWrap = method_exists($item, 'getPriceBreakdown') ? ($item->getPriceBreakdown() ?? []) : [];
        $breakdown = $breakdownWrap['breakdown'] ?? [];
        $material = (float) ($breakdown['material_cost'] ?? 0);
        $pattern = (float) ($breakdown['pattern_fee'] ?? 0);

        if (($material + $pattern) <= 0.0) {
            $patternModels = collect();

            $patternIdFromMeta = (int) data_get($item->design_metadata ?? [], 'pattern_id', 0);
            if ($patternIdFromMeta > 0) {
                $metaPattern = \App\Models\YakanPattern::find($patternIdFromMeta);
                if ($metaPattern) {
                    $patternModels->push($metaPattern);
                }
            }

            $patternsRaw = $item->patterns;
            $patternsArr = is_array($patternsRaw) ? $patternsRaw : [];
            foreach ($patternsArr as $rawPattern) {
                if (is_numeric($rawPattern)) {
                    $p = \App\Models\YakanPattern::find((int) $rawPattern);
                } else {
                    $p = !empty($rawPattern) ? \App\Models\YakanPattern::where('name', $rawPattern)->first() : null;
                }
                if ($p) {
                    $patternModels->push($p);
                }
            }

            $patternModels = $patternModels->unique('id')->values();
            $patternFallback = (float) $patternModels->sum(fn($p) => (float) ($p->pattern_price ?? 0));
            $pricePerMeter = (float) (($patternModels->first()->price_per_meter ?? 0));
            $meters = (float) ($item->fabric_quantity_meters ?? 0);
            $materialFallback = ($meters > 0 && $pricePerMeter > 0) ? ($meters * $pricePerMeter) : 0.0;

            if ($materialFallback > 0 || $patternFallback > 0) {
                $material = $materialFallback;
                $pattern = $patternFallback;
            }
        }

        $subtotal = $material + $pattern;
        if ($subtotal <= 0.0) {
            $subtotal = ($deliveryType !== 'pickup' && $quoted > $shippingDisplay)
                ? max($quoted - $shippingDisplay, 0)
                : $quoted;
        }

        if ($material <= 0.0 && $pattern <= 0.0 && $subtotal > 0.0) {
            $material = $subtotal;
        }

        return [
            'material' => $material,
            'pattern' => $pattern,
            'subtotal' => $subtotal,
            'shipping_display' => $shippingDisplay,
        ];
    };
    
    // Calculate batch totals
    $batchDisplayRows = $batchUnpaidOrders->map(fn($item) => $getBatchDisplayRowParts($item));
    $batchItemsSubtotal = (float) $batchDisplayRows->sum('subtotal');
    $batchShippingFee = (float) ($batchDisplayRows->max('shipping_display') ?? 0);
    $batchPaymentTotal = $batchItemsSubtotal + $batchShippingFee;

    $batchPaidDisplayRows = $batchPaidOrders->map(fn($item) => $getBatchDisplayRowParts($item));
    $batchPaidItemsSubtotal = (float) $batchPaidDisplayRows->sum('subtotal');
    $batchPaidShippingFee = (float) ($batchPaidDisplayRows->max('shipping_display') ?? 0);
    $batchPaidTotal = $batchPaidItemsSubtotal + $batchPaidShippingFee;

    // Canonical single-order split for display refinements.
    $currentOrderItemsSubtotal = null;
    $currentPatternIds = $order->patterns;
    if (is_string($currentPatternIds)) {
        $currentPatternIds = json_decode($currentPatternIds, true) ?? [];
    }
    if (is_array($currentPatternIds) && !empty($currentPatternIds) && !empty($order->fabric_quantity_meters)) {
        $currentPatterns = \App\Models\YakanPattern::whereIn('id', array_map('intval', $currentPatternIds))->get();
        if ($currentPatterns->isNotEmpty()) {
            $currentQtyMultiplier = (float) ($order->quantity ?? 1);
            $currentPatternFeeTotal = (float) $currentPatterns->sum(function ($pattern) {
                return (float) ($pattern->pattern_price ?? 0);
            });
            $currentPricePerMeter = (float) ($currentPatterns->first()->price_per_meter ?? 0);
            $currentMaterialCost = ((float) $order->fabric_quantity_meters) * $currentPricePerMeter;
            $currentOrderItemsSubtotal = ($currentMaterialCost + $currentPatternFeeTotal) * $currentQtyMultiplier;
        }
    }

    $currentOrderPriceParts = $getPriceParts($order);
    if ($currentOrderItemsSubtotal === null || $currentOrderItemsSubtotal <= 0) {
        $currentOrderItemsSubtotal = (float) ($currentOrderPriceParts['items_subtotal'] ?? ($order->final_price ?? $order->estimated_price ?? 0));
    }

    $currentOrderShippingFee = (($order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup')) === 'pickup')
        ? 0.0
        : (float) ($order->shipping_fee ?? 0);
    if ($currentOrderShippingFee <= 0) {
        $currentOrderShippingFee = (float) ($currentOrderPriceParts['shipping'] ?? 0);
    }
    $currentOrderDisplayTotal = $currentOrderItemsSubtotal + $currentOrderShippingFee;
    $displayPaidTotal = ($isBatchOrder && $batchPaidTotal > 0)
        ? $batchPaidTotal
        : $currentOrderDisplayTotal;
    
    $customOrderEstimatedDays = (int) \App\Models\SystemSetting::get('custom_order_estimated_days', 14);
    $estimatedCompletionDate = $order->created_at ? $order->created_at->copy()->addDays($customOrderEstimatedDays) : null;
    $authToken = request('auth_token') ?? session('auth_token') ?? request()->cookie('auth_token');
@endphp
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Back Button -->
        <div class="mb-6">
                <a href="{{ route('custom_orders.index', ['auth_token' => $authToken]) }}" 
               class="inline-flex items-center px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200 text-gray-700 hover:text-white hover:border-transparent transition-all duration-200 group" style="hover:background-color:#800000;">
                <svg class="w-5 h-5 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-semibold">Back to My Orders</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color:#800000;">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Custom Order Details</h1>
                    <p class="text-gray-600">Custom Order {{ $order->display_ref }}</p>
                    <p class="text-sm font-semibold mt-1" style="color:#800000;">
                        Estimated Turnaround: {{ $customOrderEstimatedDays }} day{{ $customOrderEstimatedDays === 1 ? '' : 's' }}
                        @if($estimatedCompletionDate)
                            • Target Date: {{ $estimatedCompletionDate->format('M d, Y') }}
                        @endif
                    </p>
                    @if($isBatchOrder)
                        <p class="text-sm text-blue-700 font-semibold mt-1">
                            @if(!empty($order->batch_order_number))
                                Batch {{ $order->batch_order_number }} • {{ $batchOrders->count() }} custom items
                            @else
                                Same submission • {{ $batchOrders->count() }} custom items
                            @endif
                        </p>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-2">
                    <div class="flex items-center gap-2">
                        @php
                            $statusConfig = [
                                'pending' => [
                                    'bg' => 'bg-amber-100',
                                    'text' => 'text-amber-800',
                                    'border' => 'border-amber-300',
                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Waiting for admin review'
                                ],
                                'price_quoted' => [
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                    'border' => 'border-red-300',
                                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                                    'description' => 'Price quoted - awaiting your decision'
                                ],
                                'approved' => [
                                    'bg' => 'bg-emerald-100',
                                    'text' => 'text-emerald-800',
                                    'border' => 'border-emerald-300',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Quote accepted - ready for payment'
                                ],
                                'processing' => [
                                    'bg' => 'bg-indigo-100',
                                    'text' => 'text-indigo-800',
                                    'border' => 'border-indigo-300',
                                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                    'description' => 'Payment accepted - waiting for production start'
                                ],
                                'in_production' => [
                                    'bg' => 'bg-indigo-100',
                                    'text' => 'text-indigo-800',
                                    'border' => 'border-indigo-300',
                                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                    'description' => 'Order is being produced'
                                ],
                                'production_complete' => [
                                    'bg' => 'bg-purple-100',
                                    'text' => 'text-purple-800',
                                    'border' => 'border-purple-300',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Production completed, preparing for delivery'
                                ],
                                'out_for_delivery' => [
                                    'bg' => 'bg-blue-100',
                                    'text' => 'text-blue-800',
                                    'border' => 'border-blue-300',
                                    'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
                                    'description' => 'Order is out for delivery'
                                ],
                                'delivered' => [
                                    'bg' => 'bg-green-100',
                                    'text' => 'text-green-800',
                                    'border' => 'border-green-300',
                                    'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
                                    'description' => 'Order delivered - please confirm receipt'
                                ],
                                'completed' => [
                                    'bg' => 'bg-emerald-100',
                                    'text' => 'text-emerald-800',
                                    'border' => 'border-emerald-300',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Order received and completed'
                                ],
                                'cancelled' => [
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                    'border' => 'border-red-300',
                                    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Order was cancelled'
                                ]
                            ];
                            $config = $statusConfig[$order->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-300', 'icon' => '', 'description' => 'Unknown status'];
                            
                            $paymentConfig = [
                                'paid' => [
                                    'bg' => 'bg-green-100',
                                    'text' => 'text-green-800',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'label' => '✓ Paid'
                                ],
                                // Database value for submitted but not yet verified payments
                                'pending' => [
                                    'bg' => 'bg-orange-100',
                                    'text' => 'text-orange-800',
                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'label' => '⏳ Pending Verification'
                                ],
                                // Legacy/unexpected unpaid state (should not normally be stored in DB)
                                'unpaid' => [
                                    'bg' => 'bg-gray-100',
                                    'text' => 'text-gray-800',
                                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0-2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'label' => 'Unpaid'
                                ],
                                'failed' => [
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'label' => '✗ Failed'
                                ]
                            ];
                            $payConfig = $paymentConfig[$order->payment_status ?? 'pending'] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => '', 'label' => 'Unpaid'];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                        @if(in_array($order->status, ['approved', 'processing', 'in_production', 'completed']) || $order->payment_status)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $payConfig['bg'] }} {{ $payConfig['text'] }}">
                                {{ $payConfig['label'] }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600">{{ $config['description'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

        @if($isBatchOrder)
            <div class="mb-6 rounded-xl border-2 p-5" style="border-color:#e0b0b0; background-color:#fff5f5;">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h2 class="text-sm font-bold" style="color:#800000;">
                        {{ !empty($order->batch_order_number) ? 'All Items Under This Order Number' : 'All Items From This Submission' }}
                    </h2>
                    @if($batchPaymentTotal > 0)
                        <span class="text-sm font-semibold" style="color:#800000;">
                            Combined Unpaid Total: ₱{{ number_format($batchPaymentTotal, 2) }}
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach($batchOrders as $idx => $item)
                        @php
                            $itemDisplayParts = $getBatchDisplayRowParts($item);
                            $itemPatterns = collect();
                            if (!empty($item->design_metadata['pattern_id'])) {
                                $p = \App\Models\YakanPattern::find($item->design_metadata['pattern_id']);
                                if ($p) $itemPatterns->push($p);
                            }
                            if ($itemPatterns->isEmpty() && !empty($item->patterns) && is_array($item->patterns)) {
                                $itemPatterns = is_numeric($item->patterns[0] ?? null)
                                    ? \App\Models\YakanPattern::whereIn('id', $item->patterns)->get()
                                    : \App\Models\YakanPattern::whereIn('name', $item->patterns)->get();
                            }
                        @endphp

                        <div class="rounded-xl border p-4 cursor-pointer hover:shadow-md transition-shadow" style="border-color:#e0b0b0; background-color:#fff;" onclick="window.location.href='{{ route('custom_orders.show', ['order' => $item->id, 'auth_token' => $authToken]) }}'">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center mr-2.5" style="background-color:#800000;">{{ $idx + 1 }}</div>
                                    <div>
                                        <a href="{{ route('custom_orders.show', ['order' => $item->id, 'auth_token' => $authToken]) }}" class="font-bold text-sm hover:underline" style="color:#800000;" onclick="event.stopPropagation();">Custom Order ID: CO-{{ str_pad((string) $item->id, 5, '0', STR_PAD_LEFT) }}</a>
                                        <p class="text-xs text-gray-500">{{ $item->created_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex px-2 py-1 text-[11px] font-medium rounded-full {{ ($item->payment_status ?? 'pending') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($item->status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Pattern</p>
                                    <p class="font-semibold text-gray-900">{{ $itemPatterns->pluck('name')->implode(', ') ?: 'N/A' }}</p>
                                    @php $firstPattern = $itemPatterns->first(); @endphp
                                    @if($firstPattern && $firstPattern->hasSvg())
                                        @php
                                            $custom = $item->customization_settings ?? [];
                                            $itemStyle = sprintf(
                                                'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                                $custom['hue'] ?? 0,
                                                $custom['saturation'] ?? 100,
                                                $custom['brightness'] ?? 100,
                                                $custom['opacity'] ?? 1,
                                                $custom['scale'] ?? 1,
                                                $custom['rotation'] ?? 0
                                            );
                                        @endphp
                                        <div class="mt-2 w-16 h-16 rounded-md border bg-white overflow-hidden p-1" style="border-color:#e0b0b0;">
                                            <div style="{{ $itemStyle }} transform-origin:center; width:100%; height:100%;">
                                                {!! $firstPattern->getSvgContent() !!}
                                            </div>
                                        </div>
                                    @elseif(!empty($item->design_upload))
                                        @php
                                            $itemDesignPath = $item->design_upload;
                                            if (str_starts_with($itemDesignPath, 'http://') || str_starts_with($itemDesignPath, 'https://') || str_starts_with($itemDesignPath, 'data:image')) {
                                                $itemDesignUrl = $itemDesignPath;
                                            } elseif (str_starts_with($itemDesignPath, 'storage/')) {
                                                $itemDesignUrl = asset($itemDesignPath);
                                            } else {
                                                $itemDesignUrl = asset('storage/' . ltrim($itemDesignPath, '/'));
                                            }
                                        @endphp
                                        <img src="{{ $itemDesignUrl }}" alt="Order #{{ $item->id }} preview" class="mt-2 w-16 h-16 rounded-md border object-cover" style="border-color:#e0b0b0;" />
                                    @endif
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Fabric Type</p>
                                    <p class="font-semibold text-gray-900">{{ $item->fabric_type_name ?? ($item->fabric_type ?? 'N/A') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Quantity</p>
                                    <p class="font-semibold text-gray-900">{{ $item->formatted_fabric_quantity ?? (($item->quantity ?? 1) . ' unit' . (($item->quantity ?? 1) > 1 ? 's' : '')) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Est. Price</p>
                                    <p class="font-semibold" style="color:#800000;">₱{{ number_format((float) ($itemDisplayParts['subtotal'] ?? ($item->final_price ?? $item->estimated_price ?? 0)), 2) }}</p>
                                </div>
                            </div>

                            <div class="mt-2 pt-2 border-t flex items-center justify-between" style="border-color:#f1d2d2;">
                                <p class="text-xs text-gray-600">Delivery: <span class="font-semibold text-gray-800">{{ ($item->delivery_type ?? 'delivery') === 'pickup' ? 'Store Pickup' : 'Delivery' }}</span></p>
                                <a href="{{ route('custom_orders.show', ['order' => $item->id, 'auth_token' => $authToken]) }}"
                                   class="text-xs font-semibold hover:underline"
                                   style="color:#800000;"
                                   onclick="event.stopPropagation();">
                                    View Full Details →
                                </a>
                            </div>

                            <div id="item-details-{{ $item->id }}" class="hidden mt-3 rounded-lg border p-3" style="border-color:#e0b0b0; background-color:#fff7f7;">
                                <h4 class="text-sm font-bold mb-2" style="color:#800000;">Traditional Yakan Patterns ({{ max(1, $itemPatterns->count()) }})</h4>

                                @if($itemPatterns->count() > 0)
                                    @php
                                        $detailPattern = $itemPatterns->first();
                                        $detailCustom = $item->customization_settings ?? [];
                                        $detailStyle = sprintf(
                                            'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                            $detailCustom['hue'] ?? 0,
                                            $detailCustom['saturation'] ?? 100,
                                            $detailCustom['brightness'] ?? 100,
                                            $detailCustom['opacity'] ?? 1,
                                            $detailCustom['scale'] ?? 1,
                                            $detailCustom['rotation'] ?? 0
                                        );
                                    @endphp

                                    <div class="rounded-lg border p-2.5" style="border-color:#e0b0b0; background-color:#fff5f5;">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Your Customized Pattern Preview - {{ $detailPattern->name ?? 'Pattern' }}</p>
                                        <div class="bg-white rounded-lg p-2 shadow-inner flex items-center justify-center overflow-hidden" style="min-height: 120px; max-height: 220px;">
                                            @if($detailPattern && $detailPattern->hasSvg())
                                                <div style="{{ $detailStyle }} transform-origin:center; max-width:100%; max-height:100%;">
                                                    {!! $detailPattern->getSvgContent() !!}
                                                </div>
                                            @else
                                                @php
                                                    $detailDesignPath = $item->design_upload;
                                                    if ($detailDesignPath && (str_starts_with($detailDesignPath, 'http://') || str_starts_with($detailDesignPath, 'https://') || str_starts_with($detailDesignPath, 'data:image'))) {
                                                        $detailDesignUrl = $detailDesignPath;
                                                    } elseif ($detailDesignPath && str_starts_with($detailDesignPath, 'storage/')) {
                                                        $detailDesignUrl = asset($detailDesignPath);
                                                    } elseif ($detailDesignPath) {
                                                        $detailDesignUrl = asset('storage/' . ltrim($detailDesignPath, '/'));
                                                    } else {
                                                        $detailDesignUrl = null;
                                                    }
                                                @endphp
                                                @if($detailDesignUrl)
                                                    <img src="{{ $detailDesignUrl }}" alt="Custom Order {{ $item->id }} preview" class="max-w-full max-h-[200px] object-contain" />
                                                @else
                                                    <span class="text-xs text-gray-500">No preview available</span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                                            <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Scale:</span> <span class="font-semibold text-gray-800">{{ $detailCustom['scale'] ?? 1 }}x</span></div>
                                            <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Rotation:</span> <span class="font-semibold text-gray-800">{{ $detailCustom['rotation'] ?? 0 }}°</span></div>
                                            <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Opacity:</span> <span class="font-semibold text-gray-800">{{ round(($detailCustom['opacity'] ?? 1) * 100) }}%</span></div>
                                        </div>
                                    </div>

                                    <div class="mt-2 rounded-lg border p-2.5 flex items-start justify-between" style="border-color:#e0b0b0; background-color:#fff5f5;">
                                        <div class="flex items-start gap-2">
                                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold" style="background:#fde2e2; color:#800000;">1</span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $detailPattern->name ?? $itemPatterns->pluck('name')->first() }}</p>
                                                <p class="text-xs text-gray-600">Traditional Yakan motif</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Pattern #1</p>
                                            <p class="text-xs font-semibold text-amber-600">Selected</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-600">No pattern details available for this item.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Production Delay Alert - MOVED TO TOP for better visibility --}}
        @if($order->is_delayed && $order->delay_reason)
            <div class="w-full rounded-lg p-3 mb-4 shadow-md border-2" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-color:#ef5350;">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color:#f44336;">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold mb-1" style="color:#c62828;">Production Delay Notice</h3>
                        <div class="bg-white bg-opacity-70 rounded p-2 border border-red-400">
                            <p class="text-xs font-semibold text-gray-800 mb-0.5">Reason for Delay:</p>
                            <p class="text-xs text-gray-700">{{ $order->delay_reason }}</p>
                            @if($order->delay_notified_at)
                                <p class="text-xs text-gray-600 mt-1 pt-1 border-t border-red-300">
                                    Notified on {{ $order->delay_notified_at->format('M d, Y \a\t h:i A') }}
                                </p>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mt-1 italic">We apologize for the inconvenience. Our team is working to resolve this as quickly as possible.</p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
            $showDeliveryBanner = false;
            $deliveryLabel = null;
            $deliveryDescription = null;
            $deliveryIcon = null;

            if ($deliveryType === 'delivery') {
                if ($order->status === 'out_for_delivery') {
                    $showDeliveryBanner = true;
                    $deliveryLabel = 'Out for Delivery';
                    $deliveryDescription = 'Your custom order has been handed to our courier and is on the way to you.';
                    $deliveryIcon = '🚛';
                } elseif ($order->status === 'delivered') {
                    $showDeliveryBanner = true;
                    $deliveryLabel = 'Delivered';
                    $deliveryDescription = 'Your custom order has been delivered. Please confirm receipt below.';
                    $deliveryIcon = '📦';
                } elseif ($order->status === 'completed') {
                    $showDeliveryBanner = true;
                    $deliveryLabel = 'Order Received';
                    $deliveryDescription = 'Thank you for confirming! Your order is now complete.';
                    $deliveryIcon = '✅';
                } elseif (in_array($order->status, ['processing', 'in_production', 'production_complete'])) {
                    $showDeliveryBanner = true;
                    if ($order->status === 'processing') {
                        $deliveryLabel = 'Payment Accepted';
                        $deliveryDescription = 'Your payment is confirmed. Production will start once our team begins work on your order.';
                        $deliveryIcon = '⚙️';
                    } elseif ($order->status === 'in_production') {
                        $deliveryLabel = 'In Production';
                        $deliveryDescription = 'Your custom order is being produced by our artisans.';
                        $deliveryIcon = '👨‍🎨';
                    } elseif ($order->status === 'production_complete') {
                        $deliveryLabel = 'Preparing for Delivery';
                        $deliveryDescription = 'Production completed! Your custom order is being prepared and will be handed to our courier soon.';
                        $deliveryIcon = '📦';
                    }
                }
            } elseif ($deliveryType === 'pickup') {
                if ($order->status === 'delivered') {
                    $showDeliveryBanner = true;
                    $deliveryLabel = 'Ready for Pickup';
                    $deliveryDescription = 'Your custom order is ready for pickup at our store. Please confirm when picked up.';
                    $deliveryIcon = '🏬';
                } elseif ($order->status === 'completed') {
                    $showDeliveryBanner = true;
                    $deliveryLabel = 'Order Received';
                    $deliveryDescription = 'Thank you for confirming! Your order is now complete.';
                    $deliveryIcon = '✅';
                }
            }
        @endphp

        @if($showDeliveryBanner)
            <div class="mb-4 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-300 rounded-lg p-3 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-800 text-white flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ $deliveryLabel }}</p>
                    <p class="text-xs text-gray-700 mt-0.5">{{ $deliveryDescription }}</p>
                    @if($order->delivery_address && $deliveryType === 'delivery')
                        <p class="text-xs text-gray-600 mt-0.5">Destination: <span class="font-medium">{{ $order->delivery_address }}</span></p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

                @if(!$isBatchOrder)
                @php
                    $singlePatterns = collect();
                    if (!empty($order->design_metadata['pattern_id'])) {
                        $sp = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                        if ($sp) {
                            $singlePatterns->push($sp);
                        }
                    }
                    if ($singlePatterns->isEmpty() && !empty($order->patterns) && is_array($order->patterns)) {
                        $singlePatterns = is_numeric($order->patterns[0] ?? null)
                            ? \App\Models\YakanPattern::whereIn('id', $order->patterns)->get()
                            : \App\Models\YakanPattern::whereIn('name', $order->patterns)->get();
                    }
                    $singlePrimaryPattern = $singlePatterns->first();
                @endphp

                <div class="mb-6 rounded-xl border-2 p-5" style="border-color:#e0b0b0; background-color:#fff5f5;">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center mr-2.5" style="background-color:#800000;">1</div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Custom Order ID: CO-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-[11px] font-medium rounded-full {{ ($order->payment_status ?? 'pending') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($order->status ?? 'pending') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Pattern</p>
                            <p class="font-semibold text-gray-900">{{ $singlePatterns->pluck('name')->implode(', ') ?: 'N/A' }}</p>
                            @if($singlePrimaryPattern && $singlePrimaryPattern->hasSvg())
                                @php
                                    $singlePreviewCustom = $order->customization_settings ?? [];
                                    $singlePreviewStyle = sprintf(
                                        'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                        $singlePreviewCustom['hue'] ?? 0,
                                        $singlePreviewCustom['saturation'] ?? 100,
                                        $singlePreviewCustom['brightness'] ?? 100,
                                        $singlePreviewCustom['opacity'] ?? 1,
                                        $singlePreviewCustom['scale'] ?? 1,
                                        $singlePreviewCustom['rotation'] ?? 0
                                    );
                                @endphp
                                <div class="mt-2 w-16 h-16 rounded-md border bg-white overflow-hidden p-1" style="border-color:#e0b0b0;">
                                    <div style="{{ $singlePreviewStyle }} transform-origin:center; width:100%; height:100%;">
                                        {!! $singlePrimaryPattern->getSvgContent() !!}
                                    </div>
                                </div>
                            @elseif(!empty($order->design_upload))
                                @php
                                    $singleDesignPath = $order->design_upload;
                                    if (str_starts_with($singleDesignPath, 'http://') || str_starts_with($singleDesignPath, 'https://') || str_starts_with($singleDesignPath, 'data:image')) {
                                        $singleDesignUrl = $singleDesignPath;
                                    } elseif (str_starts_with($singleDesignPath, 'storage/')) {
                                        $singleDesignUrl = asset($singleDesignPath);
                                    } else {
                                        $singleDesignUrl = asset('storage/' . ltrim($singleDesignPath, '/'));
                                    }
                                @endphp
                                <img src="{{ $singleDesignUrl }}" alt="Order #{{ $order->id }} preview" class="mt-2 w-16 h-16 rounded-md border object-cover" style="border-color:#e0b0b0;" />
                            @endif
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Fabric Type</p>
                            <p class="font-semibold text-gray-900">{{ $order->fabric_type_name ?? ($order->fabric_type ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Quantity</p>
                            <p class="font-semibold text-gray-900">{{ $order->formatted_fabric_quantity ?? (($order->quantity ?? 1) . ' unit' . (($order->quantity ?? 1) > 1 ? 's' : '')) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Est. Price</p>
                            <p class="font-semibold" style="color:#800000;">₱{{ number_format($currentOrderItemsSubtotal ?? ($order->final_price ?? $order->estimated_price ?? 0), 2) }}</p>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-t" style="border-color:#f1d2d2;">
                        <p class="text-xs text-gray-600">Delivery: <span class="font-semibold text-gray-800">{{ ($order->delivery_type ?? 'delivery') === 'pickup' ? 'Store Pickup' : 'Delivery' }}</span></p>
                    </div>

                    <div class="mt-3 rounded-lg border p-3" style="border-color:#e0b0b0; background-color:#fff7f7;">
                        <h4 class="text-sm font-bold mb-2" style="color:#800000;">Traditional Yakan Patterns ({{ max(1, $singlePatterns->count()) }})</h4>

                        @if($singlePatterns->count() > 0)
                            @php
                                $singleDetailPattern = $singlePatterns->first();
                                $singleDetailCustom = $order->customization_settings ?? [];
                                $singleDetailStyle = sprintf(
                                    'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                    $singleDetailCustom['hue'] ?? 0,
                                    $singleDetailCustom['saturation'] ?? 100,
                                    $singleDetailCustom['brightness'] ?? 100,
                                    $singleDetailCustom['opacity'] ?? 1,
                                    $singleDetailCustom['scale'] ?? 1,
                                    $singleDetailCustom['rotation'] ?? 0
                                );
                            @endphp

                            <div class="rounded-lg border p-2.5" style="border-color:#e0b0b0; background-color:#fff5f5;">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Your Customized Pattern Preview - {{ $singleDetailPattern->name ?? 'Pattern' }}</p>
                                <div class="bg-white rounded-lg p-2 shadow-inner flex items-center justify-center overflow-hidden" style="min-height: 120px; max-height: 220px;">
                                    @if($singleDetailPattern && $singleDetailPattern->hasSvg())
                                        <div style="{{ $singleDetailStyle }} transform-origin:center; max-width:100%; max-height:100%;">
                                            {!! $singleDetailPattern->getSvgContent() !!}
                                        </div>
                                    @else
                                        @php
                                            $singleDetailDesignPath = $order->design_upload;
                                            if ($singleDetailDesignPath && (str_starts_with($singleDetailDesignPath, 'http://') || str_starts_with($singleDetailDesignPath, 'https://') || str_starts_with($singleDetailDesignPath, 'data:image'))) {
                                                $singleDetailDesignUrl = $singleDetailDesignPath;
                                            } elseif ($singleDetailDesignPath && str_starts_with($singleDetailDesignPath, 'storage/')) {
                                                $singleDetailDesignUrl = asset($singleDetailDesignPath);
                                            } elseif ($singleDetailDesignPath) {
                                                $singleDetailDesignUrl = asset('storage/' . ltrim($singleDetailDesignPath, '/'));
                                            } else {
                                                $singleDetailDesignUrl = null;
                                            }
                                        @endphp
                                        @if($singleDetailDesignUrl)
                                            <img src="{{ $singleDetailDesignUrl }}" alt="Custom Order {{ $order->id }} preview" class="max-w-full max-h-[200px] object-contain" />
                                        @else
                                            <span class="text-xs text-gray-500">No preview available</span>
                                        @endif
                                    @endif
                                </div>

                                <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                                    <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Scale:</span> <span class="font-semibold text-gray-800">{{ $singleDetailCustom['scale'] ?? 1 }}x</span></div>
                                    <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Rotation:</span> <span class="font-semibold text-gray-800">{{ $singleDetailCustom['rotation'] ?? 0 }}°</span></div>
                                    <div class="bg-white rounded px-2 py-1"><span class="text-gray-500">Opacity:</span> <span class="font-semibold text-gray-800">{{ round(($singleDetailCustom['opacity'] ?? 1) * 100) }}%</span></div>
                                </div>
                            </div>

                            <div class="mt-2 rounded-lg border p-2.5 flex items-start justify-between" style="border-color:#e0b0b0; background-color:#fff5f5;">
                                <div class="flex items-start gap-2">
                                    <span class="w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold" style="background:#fde2e2; color:#800000;">1</span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $singleDetailPattern->name ?? $singlePatterns->pluck('name')->first() }}</p>
                                        <p class="text-xs text-gray-600">Traditional Yakan motif</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Pattern #1</p>
                                    <p class="text-xs font-semibold text-amber-600">Selected</p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-600">No pattern details available for this item.</p>
                        @endif
                    </div>
                </div>
                @endif


                <!-- Patterns Card -->
                @if(false && !$isBatchOrder && $order->patterns && is_array($order->patterns) && count($order->patterns) > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="px-6 py-4" style="background-color:#800000;">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                </svg>
                            </div>
                            Traditional Yakan Patterns ({{ count($order->patterns) }})
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Pattern Preview -->
                        @php
                            // Load pattern model for SVG display
                            $patternPreviewModel = null;
                            if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id'])) {
                                $patternPreviewModel = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                            } elseif (!empty($order->patterns) && is_array($order->patterns)) {
                                if (is_numeric($order->patterns[0])) {
                                    $patternPreviewModel = \App\Models\YakanPattern::find($order->patterns[0]);
                                } else {
                                    $patternPreviewModel = \App\Models\YakanPattern::where('name', $order->patterns[0])->first();
                                }
                            }

                            // Fallback to preview_image or design_upload
                            $previewUrl = null;
                            if (!$patternPreviewModel) {
                                $candidate = $order->preview_image ?? null;
                                if ($candidate) {
                                    if (str_starts_with($candidate, 'data:image')) {
                                        $previewUrl = $candidate;
                                    } elseif (str_starts_with($candidate, 'custom_orders/') || str_starts_with($candidate, 'custom_designs/')) {
                                        $previewUrl = asset('storage/' . $candidate);
                                    } elseif (str_starts_with($candidate, 'http')) {
                                        $previewUrl = $candidate;
                                    }
                                }

                                if (!$previewUrl && $order->design_upload) {
                                    if (str_starts_with($order->design_upload, 'data:image')) {
                                        $previewUrl = $order->design_upload;
                                    } elseif (str_starts_with($order->design_upload, 'custom_orders/') || str_starts_with($order->design_upload, 'custom_designs/')) {
                                        $previewUrl = asset('storage/' . $order->design_upload);
                                    } else {
                                        $previewUrl = asset('storage/' . ltrim($order->design_upload, '/'));
                                    }
                                }
                            }
                        @endphp

                        @if($patternPreviewModel && $patternPreviewModel->hasSvg())
                        <div class="mb-6">
                            <div class="rounded-xl p-4 border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Your Customized Pattern Preview - {{ $patternPreviewModel->name }}
                                </h3>
                                @php
                                    $customization = $order->customization_settings ?? [];
                                    $scale = $customization['scale'] ?? 1;
                                    $rotation = $customization['rotation'] ?? 0;
                                    $opacity = $customization['opacity'] ?? 1;
                                    $hue = $customization['hue'] ?? 0;
                                    $saturation = $customization['saturation'] ?? 100;
                                    $brightness = $customization['brightness'] ?? 100;
                                    
                                    $filterStyle = sprintf(
                                        'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                        $hue, $saturation, $brightness, $opacity, $scale, $rotation
                                    );
                                @endphp
                                <div class="bg-white rounded-lg p-3 shadow-inner flex items-center justify-center overflow-hidden" style="max-height: 400px; position: relative;">
                                    <div style="{{ $filterStyle }} transform-origin: center; max-width: 100%; max-height: 100%;">
                                        {!! $patternPreviewModel->getSvgContent() !!}
                                    </div>
                                </div>
                                @if(isset($order->customization_settings) && is_array($order->customization_settings))
                                <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Scale:</span>
                                        <span class="font-semibold text-gray-800">{{ $order->customization_settings['scale'] ?? 1 }}x</span>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Rotation:</span>
                                        <span class="font-semibold text-gray-800">{{ $order->customization_settings['rotation'] ?? 0 }}°</span>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Opacity:</span>
                                        <span class="font-semibold text-gray-800">{{ round(($order->customization_settings['opacity'] ?? 0.85) * 100) }}%</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @elseif($previewUrl)
                        <div class="mb-6">
                            <div class="rounded-xl p-4 border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Your Customized Pattern Preview
                                </h3>
                                <div class="bg-white rounded-lg p-3 shadow-inner">
                                     <img src="{{ $previewUrl }}" 
                                         alt="Pattern Preview" 
                                         class="w-full h-auto rounded-lg border-2 border-gray-200"
                                         style="max-height: 400px; object-fit: contain;">
                                </div>
                                @if(isset($order->customization_settings) && is_array($order->customization_settings))
                                <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Scale:</span>
                                        <span class="font-semibold text-gray-800">{{ $order->customization_settings['scale'] ?? 1 }}x</span>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Rotation:</span>
                                        <span class="font-semibold text-gray-800">{{ $order->customization_settings['rotation'] ?? 0 }}°</span>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1">
                                        <span class="text-gray-500">Opacity:</span>
                                        <span class="font-semibold text-gray-800">{{ round(($order->customization_settings['opacity'] ?? 0.85) * 100) }}%</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <div class="space-y-4">
                            @foreach($order->patterns as $index => $pattern)
                                @php
                                    // Try to get the actual pattern model for this pattern
                                    $patternModel = null;
                                    $patternName = 'Unknown Pattern';
                                    
                                    if (is_array($pattern) && isset($pattern['name'])) {
                                        $patternName = $pattern['name'];
                                        $patternModel = \App\Models\YakanPattern::where('name', $pattern['name'])->first();
                                    } elseif (is_numeric($pattern)) {
                                        $patternModel = \App\Models\YakanPattern::find($pattern);
                                    } elseif (is_string($pattern)) {
                                        $patternModel = \App\Models\YakanPattern::where('name', $pattern)->first();
                                    }
                                    
                                    if ($patternModel) {
                                        $patternName = $patternModel->name;
                                    }
                                @endphp
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-8 h-8 bg-gradient-to-br from-red-100 to-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-bold text-red-700">{{ $index + 1 }}</span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-800 capitalize">{{ $patternName }}</div>
                                                <div class="text-sm text-gray-600 mt-1">Traditional Yakan motif</div>
                                                @if(isset($pattern['colors']) && is_array($pattern['colors']) && count($pattern['colors']) > 0)
                                                    <div class="flex items-center mt-3 space-x-3">
                                                        @foreach($pattern['colors'] as $color)
                                                            <div class="flex items-center space-x-1">
                                                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: {{ $color }}" title="{{ $color }}"></div>
                                                                <span class="text-xs text-gray-600 font-mono">{{ $color }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Pattern #{{ $index + 1 }}</div>
                                            <div class="text-xs text-amber-600 font-medium mt-1">Selected</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Design Upload Card -->
                @if(!$isBatchOrder && $order->design_upload)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="px-6 py-4" style="background-color:#800000;">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            Design Upload
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $designPath = $order->design_upload;
                            if (str_starts_with($designPath, 'http://') || str_starts_with($designPath, 'https://')) {
                                $fullDesignUrl = $designPath; // Cloudinary URL
                            } elseif (str_starts_with($designPath, 'data:image')) {
                                $fullDesignUrl = $designPath; // Data URL
                            } elseif (str_starts_with($designPath, 'storage/')) {
                                $fullDesignUrl = asset($designPath); // Already has storage/ prefix
                            } else {
                                $fullDesignUrl = asset('storage/' . ltrim($designPath, '/')); // Add storage/ prefix
                            }
                        @endphp
                        <img src="{{ $fullDesignUrl }}" 
                             alt="Design Upload" 
                             class="w-full rounded-xl shadow-lg border-2 border-gray-200"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22><rect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/><text fill=%22%239ca3af%22 font-size=%2218%22 x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22>Image not available</text></svg>'">
                    </div>
                </div>
                @endif

                <div id="left-action-anchor"></div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:sticky lg:top-6 self-start">
                
                <!-- Order Summary Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4" style="background-color:#800000;">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            Order Summary
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <!-- Order ID -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Order ID</span>
                            <span class="text-sm font-bold text-gray-900">{{ $order->display_ref }}</span>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Order Status</span>
                            @php
                            $statusConfig = [
                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                'price_quoted' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                'in_production' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                'production_complete' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                                'out_for_delivery' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                'delivered' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                'processing' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800']
                            ];
                            $config = $statusConfig[$order->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                            $displayStatusLabel = $order->status === 'completed' ? 'Delivered' : ucfirst(str_replace('_', ' ', $order->status));
                        @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                                {{ $displayStatusLabel }}
                            </span>
                        </div>

                        <!-- Payment Status -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Payment</span>
                            @php
                                $paymentConfig = [
                                    'paid' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Paid'],
                                    'pending' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Pending Verification'],
                                    'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Failed'],
                                    'unpaid' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Unpaid'],
                                ];
                                $payConfig = $paymentConfig[$order->payment_status ?? 'pending'] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Unpaid'];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $payConfig['bg'] }} {{ $payConfig['text'] }}">
                                {{ $payConfig['label'] }}
                            </span>
                        </div>

                        @php
                            $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                        @endphp
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Delivery Option</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $deliveryType === 'pickup' ? 'Store Pickup' : 'Delivery' }}
                            </span>
                        </div>

                        <!-- Pricing Section -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="space-y-3">
                                @if($order->status === 'pending')
                                    <div class="text-center py-4">
                                        <svg class="w-12 h-12 text-yellow-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700">Price Pending</p>
                                        <p class="text-xs text-gray-500 mt-1">Admin is reviewing your order</p>
                                    </div>
                                @elseif($order->status === 'price_quoted' && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Quoted Price</p>
                                        <p class="text-3xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                                        <p class="text-xs mt-1 font-semibold" style="color:#800000;">⏳ Awaiting your decision</p>
                                        
                                        {{-- Price Change Notice --}}
                                        @if($order->previous_price && $order->previous_price != $order->final_price)
                                        <div class="mt-3 p-3 rounded-lg border-2 border-amber-400 bg-amber-50">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-bold text-amber-800">Price Updated</p>
                                                    <p class="text-xs text-amber-700 mt-1">
                                                        Previous price: <span class="line-through">₱{{ number_format($order->previous_price, 2) }}</span>
                                                        → New price: <span class="font-bold">₱{{ number_format($order->final_price, 2) }}</span>
                                                        @if($order->final_price > $order->previous_price)
                                                            <span class="text-red-600">(+₱{{ number_format($order->final_price - $order->previous_price, 2) }})</span>
                                                        @else
                                                            <span class="text-green-600">(-₱{{ number_format($order->previous_price - $order->final_price, 2) }})</span>
                                                        @endif
                                                    </p>
                                                    @if($order->price_change_reason)
                                                    <p class="text-xs text-amber-700 mt-1"><strong>Reason:</strong> {{ $order->price_change_reason }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Price Breakdown from Admin --}}
                                        @php
                                            $priceBreakdown = $order->getPriceBreakdown();
                                        @endphp
                                        
                                        @if($priceBreakdown && isset($priceBreakdown['breakdown']) && count($priceBreakdown['breakdown']) > 0)
                                        <div class="mt-3 bg-gray-50 rounded-lg p-3 border border-gray-200 text-xs">
                                            <p class="font-semibold text-gray-700 mb-2">Price Breakdown:</p>
                                            <div class="space-y-1 text-gray-600">
                                                @if(isset($priceBreakdown['breakdown']['material_cost']) && $priceBreakdown['breakdown']['material_cost'] > 0)
                                                <div class="flex justify-between">
                                                    <span>Material Cost:</span>
                                                    <span class="font-semibold">₱{{ number_format($priceBreakdown['breakdown']['material_cost'], 2) }}</span>
                                                </div>
                                                @endif
                                                @if(isset($priceBreakdown['breakdown']['pattern_fee']) && $priceBreakdown['breakdown']['pattern_fee'] > 0)
                                                <div class="flex justify-between">
                                                    <span>Pattern Fee:</span>
                                                    <span class="font-semibold">₱{{ number_format($priceBreakdown['breakdown']['pattern_fee'], 2) }}</span>
                                                </div>
                                                @endif
                                                @if(isset($priceBreakdown['breakdown']['labor_cost']) && $priceBreakdown['breakdown']['labor_cost'] > 0)
                                                <div class="flex justify-between">
                                                    <span>Labor Cost:</span>
                                                    <span class="font-semibold">₱{{ number_format($priceBreakdown['breakdown']['labor_cost'], 2) }}</span>
                                                </div>
                                                @endif
                                                @if(isset($priceBreakdown['breakdown']['delivery_fee']) && $priceBreakdown['breakdown']['delivery_fee'] > 0)
                                                <div class="flex justify-between">
                                                    <span>Delivery Fee:</span>
                                                    <span class="font-semibold">₱{{ number_format($priceBreakdown['breakdown']['delivery_fee'], 2) }}</span>
                                                </div>
                                                @endif
                                                @if(isset($priceBreakdown['breakdown']['discount']) && $priceBreakdown['breakdown']['discount'] > 0)
                                                <div class="flex justify-between text-green-600">
                                                    <span>Discount:</span>
                                                    <span class="font-semibold">-₱{{ number_format($priceBreakdown['breakdown']['discount'], 2) }}</span>
                                                </div>
                                                @endif
                                                <div class="border-t border-gray-300 pt-1 mt-1 flex justify-between font-bold" style="color:#800000;">
                                                    <span>Total:</span>
                                                    <span>₱{{ number_format($order->final_price, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @elseif($order->status === 'approved' && $order->final_price)
                                    <div>
                                        @php
                                            // Use batch totals if this is a batch order
                                            if ($isBatchOrder) {
                                                $summaryShippingFee = $deliveryType === 'pickup' ? 0 : $batchShippingFee;
                                                $summarySubtotal = $batchItemsSubtotal;
                                                $summaryTotal = $deliveryType === 'pickup' ? $summarySubtotal : $batchPaymentTotal;
                                            } else {
                                                // Canonical subtotal (same basis as admin details view)
                                                $canonicalSubtotal = null;
                                                $canonicalPatternIds = $order->patterns;
                                                if (is_string($canonicalPatternIds)) {
                                                    $canonicalPatternIds = json_decode($canonicalPatternIds, true) ?? [];
                                                }
                                                if (is_array($canonicalPatternIds) && !empty($canonicalPatternIds) && !empty($order->fabric_quantity_meters)) {
                                                    $canonicalPatterns = \App\Models\YakanPattern::whereIn('id', array_map('intval', $canonicalPatternIds))->get();
                                                    if ($canonicalPatterns->isNotEmpty()) {
                                                        $canonicalQty = (float) ($order->quantity ?? 1);
                                                        $canonicalPatternFee = (float) $canonicalPatterns->sum(function ($p) {
                                                            return (float) ($p->pattern_price ?? 0);
                                                        });
                                                        $canonicalPricePerMeter = (float) ($canonicalPatterns->first()->price_per_meter ?? 0);
                                                        $canonicalMaterialCost = ((float) $order->fabric_quantity_meters) * $canonicalPricePerMeter;
                                                        $canonicalSubtotal = ($canonicalMaterialCost + $canonicalPatternFee) * $canonicalQty;
                                                    }
                                                }

                                                $singleBreakdown = $order->getPriceBreakdown();
                                                $singleBreakdownData = $singleBreakdown['breakdown'] ?? [];

                                                $singleMaterial = (float) ($singleBreakdownData['material_cost'] ?? 0);
                                                $singlePattern = (float) ($singleBreakdownData['pattern_fee'] ?? 0);
                                                $singleLabor = (float) ($singleBreakdownData['labor_cost'] ?? 0);
                                                $singleDiscount = (float) ($singleBreakdownData['discount'] ?? 0);
                                                $singleItemsSubtotal = max(($singleMaterial + $singlePattern + $singleLabor - $singleDiscount), 0);

                                                $singleShipping = 0.0;
                                                if ($deliveryType !== 'pickup') {
                                                    $singleShipping = (float) ($order->shipping_fee ?? 0);
                                                    if ($singleShipping <= 0) {
                                                        $singleShipping = (float) $calculateShippingFromAddress($order);
                                                    }
                                                }

                                                // Prefer canonical split for display; fallback to quoted when breakdown is unavailable.
                                                if ($canonicalSubtotal !== null && $canonicalSubtotal > 0) {
                                                    $summarySubtotal = $canonicalSubtotal;
                                                    $summaryShippingFee = $singleShipping;
                                                    $summaryTotal = $summarySubtotal + $summaryShippingFee;
                                                } elseif ($singleItemsSubtotal > 0) {
                                                    $summarySubtotal = $singleItemsSubtotal;
                                                    $summaryShippingFee = $singleShipping;
                                                    $summaryTotal = $summarySubtotal + $summaryShippingFee;
                                                } else {
                                                    $singlePriceParts = $getPriceParts($order);
                                                    $summaryShippingFee = $deliveryType === 'pickup' ? 0 : (float) ($singlePriceParts['shipping'] ?? 0);
                                                    $summarySubtotal = (float) ($singlePriceParts['items_subtotal'] ?? ($order->final_price ?? 0));
                                                    $summaryTotal = (float) ($singlePriceParts['total'] ?? ($summarySubtotal + $summaryShippingFee));
                                                }
                                            }
                                        @endphp
                                        
                                        @if($isBatchOrder)
                                        <p class="text-sm font-medium text-gray-700 mb-1">Batch Order ({{ count($batchOrders) }} items)</p>
                                        <p class="text-xl font-bold" style="color:#800000;">₱{{ number_format($summaryTotal, 2) }}</p>
                                        <p class="text-xs mt-1 font-semibold text-emerald-600">✓ Quote accepted</p>
                                        @else
                                        <p class="text-sm font-medium text-gray-700 mb-1">Agreed Price</p>
                                        <p class="text-xl font-bold" style="color:#800000;">₱{{ number_format($summaryTotal, 2) }}</p>
                                        <p class="text-xs mt-1 font-semibold text-emerald-600">✓ Quote accepted</p>
                                        @endif

                                        {{-- Professional Admin-Style Price Breakdown --}}
                                        <div class="mt-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-3 border-2 border-gray-300">
                                            <h3 class="text-xs font-bold text-gray-800 mb-2 flex items-center gap-1">
                                                <svg class="w-3 h-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                💰 Price Breakdown
                                            </h3>

                                            <div class="space-y-2 text-xs">
                                                @if($isBatchOrder)
                                                    @foreach($batchOrders as $batchItem)
                                                        @php
                                                            $batchRowParts = $getBatchDisplayRowParts($batchItem);
                                                            $itemMaterial = (float) ($batchRowParts['material'] ?? 0);
                                                            $itemPattern = (float) ($batchRowParts['pattern'] ?? 0);
                                                            $itemSubtotal = (float) ($batchRowParts['subtotal'] ?? 0);
                                                        @endphp
                                                        <div class="rounded-lg border border-gray-200 bg-white p-2">
                                                            <div class="font-bold mb-1" style="color:#800000;">Custom Order #{{ $batchItem->id }}</div>
                                                            <div class="space-y-0.5">
                                                                @if($itemMaterial > 0)
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-gray-600">Material Cost:</span>
                                                                    <span class="font-semibold text-gray-900">₱{{ number_format($itemMaterial, 2) }}</span>
                                                                </div>
                                                                @endif
                                                                @if($itemPattern > 0)
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-gray-600">Pattern Fee:</span>
                                                                    <span class="font-semibold text-gray-900">₱{{ number_format($itemPattern, 2) }}</span>
                                                                </div>
                                                                @endif
                                                                <div class="border-t border-gray-200 pt-0.5 flex justify-between items-center">
                                                                    <span class="text-gray-600 font-medium">Subtotal:</span>
                                                                    <span class="font-bold text-gray-900">₱{{ number_format($itemSubtotal, 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    <div class="border-t border-gray-300 pt-1 flex justify-between items-center">
                                                        <span class="text-gray-600 font-medium">Subtotal (All Items):</span>
                                                        <span class="font-bold text-gray-900">₱{{ number_format($summarySubtotal, 2) }}</span>
                                                    </div>
                                                @else
                                                    {{-- Single order breakdown --}}
                                                    @php
                                                        $singleBreakdown = $order->getPriceBreakdown();
                                                        $singleBreakdownData = $singleBreakdown['breakdown'] ?? [];
                                                        $singleMaterial = (float) ($singleBreakdownData['material_cost'] ?? 0);
                                                        $singlePattern = (float) ($singleBreakdownData['pattern_fee'] ?? 0);
                                                    @endphp
                                                    @if($singleMaterial > 0)
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-gray-600">Material Cost:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($singleMaterial, 2) }}</span>
                                                    </div>
                                                    @endif
                                                    @if($singlePattern > 0)
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-gray-600">Pattern Fee:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($singlePattern, 2) }}</span>
                                                    </div>
                                                    @endif
                                                    <div class="border-t border-gray-200 pt-1 flex justify-between items-center">
                                                        <span class="text-gray-600 font-medium">Items Subtotal:</span>
                                                        <span class="font-bold text-gray-900">₱{{ number_format($summarySubtotal, 2) }}</span>
                                                    </div>
                                                @endif
                                                
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Shipping Fee:</span>
                                                    <span class="font-semibold text-gray-900">₱{{ number_format($summaryShippingFee, 2) }}</span>
                                                </div>
                                                <div class="border-t-2 pt-1 mt-1 flex justify-between items-center" style="border-color:#800000;">
                                                    <span class="text-gray-900 font-bold">Total:</span>
                                                    <span class="font-bold text-green-600">₱{{ number_format($summaryTotal, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Admin Notes --}}
                                        @if($order->getAdminNotesText() || $order->price_quoted_at || $order->approved_at)
                                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 7a1 1 0 000 2h6a1 1 0 000-2H8zm0 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-blue-900 mb-1">Admin Notes on Quote</p>
                                                    @if($order->getAdminNotesText())
                                                        <p class="text-blue-800 mb-2 italic">{{ $order->getAdminNotesText() }}</p>
                                                    @endif
                                                    @if($order->price_quoted_at)
                                                        <p class="text-xs text-blue-700">Quote provided on {{ $order->price_quoted_at->format('M d, Y \a\t h:i A') }}</p>
                                                    @elseif($order->approved_at)
                                                        <p class="text-xs text-blue-700">Quote provided on {{ $order->approved_at->format('M d, Y \a\t h:i A') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @elseif($order->status === 'processing' && $order->final_price)
                                    <div>
                                        @if($isBatchOrder && $batchPaidOrders->count() > 0)
                                        <div class="mt-2 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-3 border border-gray-300">
                                            <p class="text-xs font-bold text-gray-800 mb-2">💰 Price Breakdown</p>
                                            <div class="space-y-2 text-xs">
                                                @foreach($batchPaidOrders as $batchItem)
                                                    @php
                                                        $paidRow = $getBatchDisplayRowParts($batchItem);
                                                    @endphp
                                                    <div class="rounded border border-gray-200 bg-white p-2">
                                                        <div class="font-bold mb-1" style="color:#800000;">Custom Order #{{ $batchItem->id }}</div>
                                                        <div class="space-y-0.5">
                                                            <div class="flex justify-between"><span class="text-gray-600">Material Cost:</span><span class="font-semibold text-gray-900">₱{{ number_format((float) ($paidRow['material'] ?? 0), 2) }}</span></div>
                                                            <div class="flex justify-between"><span class="text-gray-600">Pattern Fee:</span><span class="font-semibold text-gray-900">₱{{ number_format((float) ($paidRow['pattern'] ?? 0), 2) }}</span></div>
                                                            <div class="border-t border-gray-200 pt-0.5 flex justify-between"><span class="text-gray-600 font-medium">Subtotal:</span><span class="font-bold text-gray-900">₱{{ number_format((float) ($paidRow['subtotal'] ?? 0), 2) }}</span></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-2 text-xs space-y-1">
                                            <div class="flex justify-between"><span class="text-gray-600">Items Subtotal:</span><span class="font-semibold text-gray-900">₱{{ number_format(($isBatchOrder && $batchPaidTotal > 0) ? $batchPaidItemsSubtotal : $currentOrderItemsSubtotal, 2) }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Shipping Fee:</span><span class="font-semibold text-gray-900">₱{{ number_format(($isBatchOrder && $batchPaidTotal > 0) ? $batchPaidShippingFee : $currentOrderShippingFee, 2) }}</span></div>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 mt-2 mb-1">Total Paid</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                                        <p class="text-xs text-indigo-600 mt-1 font-semibold">Payment accepted, waiting for production start</p>
                                    </div>
                                @elseif($order->status === 'in_production' && $order->final_price)
                                    <div>
                                        @if($isBatchOrder && $batchPaidOrders->count() > 0)
                                        <div class="mt-2 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-3 border border-gray-300">
                                            <p class="text-xs font-bold text-gray-800 mb-2">💰 Price Breakdown</p>
                                            <div class="space-y-2 text-xs">
                                                @foreach($batchPaidOrders as $batchItem)
                                                    @php
                                                        $paidRow = $getBatchDisplayRowParts($batchItem);
                                                    @endphp
                                                    <div class="rounded border border-gray-200 bg-white p-2">
                                                        <div class="font-bold mb-1" style="color:#800000;">Custom Order #{{ $batchItem->id }}</div>
                                                        <div class="space-y-0.5">
                                                            <div class="flex justify-between"><span class="text-gray-600">Material Cost:</span><span class="font-semibold text-gray-900">₱{{ number_format((float) ($paidRow['material'] ?? 0), 2) }}</span></div>
                                                            <div class="flex justify-between"><span class="text-gray-600">Pattern Fee:</span><span class="font-semibold text-gray-900">₱{{ number_format((float) ($paidRow['pattern'] ?? 0), 2) }}</span></div>
                                                            <div class="border-t border-gray-200 pt-0.5 flex justify-between"><span class="text-gray-600 font-medium">Subtotal:</span><span class="font-bold text-gray-900">₱{{ number_format((float) ($paidRow['subtotal'] ?? 0), 2) }}</span></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-2 text-xs space-y-1">
                                            <div class="flex justify-between"><span class="text-gray-600">Items Subtotal:</span><span class="font-semibold text-gray-900">₱{{ number_format(($isBatchOrder && $batchPaidTotal > 0) ? $batchPaidItemsSubtotal : $currentOrderItemsSubtotal, 2) }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Shipping Fee:</span><span class="font-semibold text-gray-900">₱{{ number_format(($isBatchOrder && $batchPaidTotal > 0) ? $batchPaidShippingFee : $currentOrderShippingFee, 2) }}</span></div>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 mt-2 mb-1">Final Price</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                                        <p class="text-xs text-emerald-600 mt-1 font-semibold">Payment accepted</p>
                                    </div>
                                @elseif(in_array($order->status, ['production_complete', 'out_for_delivery', 'delivered']) && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Total Paid</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                                        <p class="text-xs text-emerald-600 mt-1 font-semibold">
                                            @if($order->status === 'delivered')
                                                ✓ Delivered
                                            @elseif($order->status === 'out_for_delivery')
                                                🚚 Out for delivery
                                            @else
                                                ✓ Ready for delivery
                                            @endif
                                        </p>
                                    </div>
                                @elseif($order->status === 'completed' && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Total Paid</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                                        <p class="text-xs text-emerald-600 mt-1 font-semibold">Order completed</p>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-500">Amount Not Available Yet</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Created Date -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Created</span>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</p>
                            </div>
                        </div>

                        @if($order->isFabricOrder())
                            <div class="pt-3 border-t border-gray-200 mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Fabric Type</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $order->fabric_type_name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Fabric Quantity</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $order->formatted_fabric_quantity }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Intended Use</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $order->intended_use_label }}</span>
                                </div>
                            </div>
                        @endif

                        @if($order->delivery_address)
                            <div class="pt-3 border-t border-gray-200 mt-3">
                                <p class="text-sm font-medium text-gray-600 mb-1">Delivery Address</p>
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $order->delivery_address }}</p>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4" style="background-color:#800000;">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Order Timeline
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @php
                                $timelineStatuses = ['pending', 'price_quoted', 'approved', 'in_production', 'production_complete', 'out_for_delivery', 'delivered'];

                                // Normalize runtime order states to timeline stages.
                                $displayStatus = $order->status;
                                if ($displayStatus === 'completed') {
                                    $displayStatus = 'delivered';
                                } elseif ($displayStatus === 'processing') {
                                    // Paid/processing means payment accepted but admin may not have started production yet.
                                    $displayStatus = 'approved';
                                } elseif ($displayStatus === 'approved' && ($order->payment_status ?? null) === 'paid') {
                                    $displayStatus = 'approved';
                                }

                                if (!in_array($displayStatus, $timelineStatuses, true)) {
                                    $displayStatus = ($order->payment_status ?? null) === 'paid' ? 'approved' : 'pending';
                                }

                                $currentTimelineIndex = array_search($displayStatus, $timelineStatuses, true);
                                if ($currentTimelineIndex === false) {
                                    $currentTimelineIndex = 0;
                                }
                            @endphp

                            @foreach(['pending' => 'Order Placed', 'price_quoted' => 'Price Quoted', 'approved' => 'Quote Accepted', 'in_production' => 'In Production', 'production_complete' => 'Production Complete', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered'] as $status => $label)
                                @php
                                    $statusIndex = array_search($status, $timelineStatuses, true);
                                    $isActive = $statusIndex <= $currentTimelineIndex;
                                    $isCurrent = $status === $displayStatus;
                                @endphp
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-4">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isActive ? '' : 'bg-gray-300' }}" @if($isActive) style="background-color:#800000;" @endif>
                                            @if($isActive)
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <div class="w-3 h-3 bg-white rounded-full"></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold {{ $isActive ? 'text-gray-900' : 'text-gray-500' }}">{{ $label }}</p>
                                        @if($isCurrent)
                                            <p class="text-xs font-semibold mt-0.5" style="color:#800000;">Current Status</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Action Buttons -->
        <div id="action-buttons-block" class="mt-8 lg:w-2/3 lg:pr-6">
            
            {{-- Pending Status - Waiting for Admin --}}
            @if($order->status === 'pending')
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">Under Review</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">Your custom order is being reviewed by our admin team. You'll receive a price quote soon.</p>
                    <div class="bg-white rounded-xl p-4 border-2 inline-block" style="border-color:#e0b0b0;">
                        <p class="text-sm font-semibold" style="color:#800000;">⏱️ Typical review time: 1-2 business days</p>
                    </div>
                </div>

            {{-- Price Quoted Status - Show Quote for Acceptance --}}
            @elseif($order->status === 'price_quoted' && $order->final_price)
                <div class="w-full rounded-2xl border-2 p-8 shadow-xl" style="background-color:#fff5f5; border-color:#c08080;">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full flex items-center justify-center shadow-lg" style="background-color:#800000;">
                            <span class="text-5xl font-extrabold text-white">₱</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-2" style="color:#800000;">💰 Price Quote Ready!</h3>
                        <p class="text-gray-700">Our admin has reviewed your order and provided a quote.</p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-md border" style="border-color:#e0b0b0;">
                        @php
                            $priceBreakdown = $order->getPriceBreakdown();
                            $adminNotesText = $order->getAdminNotesText();
                        @endphp
                        
                        @if($priceBreakdown && isset($priceBreakdown['breakdown']) && count($priceBreakdown['breakdown']) > 0)
                            {{-- Price Breakdown Display --}}
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-gray-600 mb-3 text-center">Price Breakdown</p>
                                <div class="space-y-2">
                                    @if(isset($priceBreakdown['breakdown']['material_cost']) && $priceBreakdown['breakdown']['material_cost'] > 0)
                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Material Cost</span>
                                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($priceBreakdown['breakdown']['material_cost'], 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    @if(isset($priceBreakdown['breakdown']['pattern_fee']) && $priceBreakdown['breakdown']['pattern_fee'] > 0)
                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Pattern / Customization Fee</span>
                                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($priceBreakdown['breakdown']['pattern_fee'], 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    @if(isset($priceBreakdown['breakdown']['labor_cost']) && $priceBreakdown['breakdown']['labor_cost'] > 0)
                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Labor / Production Cost</span>
                                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($priceBreakdown['breakdown']['labor_cost'], 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    @if(isset($priceBreakdown['breakdown']['delivery_fee']) && $priceBreakdown['breakdown']['delivery_fee'] > 0)
                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Delivery Fee</span>
                                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($priceBreakdown['breakdown']['delivery_fee'], 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    @if(isset($priceBreakdown['breakdown']['discount']) && $priceBreakdown['breakdown']['discount'] > 0)
                                    <div class="flex justify-between items-center py-2 px-3 bg-green-50 rounded-lg">
                                        <span class="text-sm text-green-700">Discount</span>
                                        <span class="text-sm font-semibold text-green-600">-₱{{ number_format($priceBreakdown['breakdown']['discount'], 2) }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                {{-- Total --}}
                                <div class="mt-3 pt-3 border-t-2" style="border-color:#e0b0b0;">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold" style="color:#800000;">Total</span>
                                        <span class="text-3xl font-extrabold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Simple price display (no breakdown) --}}
                            <div class="text-center mb-4">
                                <p class="text-sm font-medium text-gray-600 mb-2">Quoted Amount</p>
                                <p class="text-5xl font-extrabold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                            </div>
                        @endif
                        
                        @if($adminNotesText)
                            <div class="mt-4 rounded-lg p-4 border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold mb-1" style="color:#800000;">Notes from Admin:</p>
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $adminNotesText }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($order->price_quoted_at)
                            <div class="mt-3 text-center text-xs text-gray-500">
                                Quoted on {{ $order->price_quoted_at->format('M d, Y \a\t h:i A') }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('custom_orders.accept', $order) }}" id="acceptForm">
                            @csrf
                            <button type="submit" class="w-full text-white font-bold py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Accept Quote & Proceed to Payment
                            </button>
                        </form>
                        
                        <button type="button" onclick="document.getElementById('rejectForm').classList.toggle('hidden')" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-xl transition-colors duration-200">
                            ✗ Reject Quote
                        </button>
                        
                        <form id="rejectForm" method="POST" action="{{ route('custom_orders.reject', $order) }}" class="hidden mt-4 bg-white rounded-xl p-4 border-2" style="border-color:#c08080;">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Why are you rejecting this quote? (Optional)</label>
                                <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2" style="--tw-ring-color:#800000;" placeholder="e.g., Price is too high, Timeline doesn't work for me..."></textarea>
                            </div>
                            <button type="submit" class="w-full text-white font-bold py-3 rounded-xl transition-colors duration-200" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                Confirm Rejection
                            </button>
                        </form>
                    </div>
                    
                    <div class="mt-4 text-center text-xs text-gray-600">
                        <p>⚠️ Once you accept, you'll be redirected to payment.</p>
                        <p>Rejecting will cancel this order.</p>
                    </div>
                </div>
            
            {{-- Approved Status - Waiting for Payment --}}
            @elseif($order->status === 'approved' && !in_array($order->payment_status, ['paid', 'pending_verification']))
                <div class="w-full rounded-2xl p-8 shadow-2xl border-2" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border-color:#800000;">
                    <div class="w-24 h-24 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg animate-pulse" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold mb-3" style="color:#800000;">🎉 Quote Accepted!</h3>
                    <p class="text-gray-700 mb-6 max-w-2xl mx-auto text-lg">Congratulations! You've accepted the quote. Complete your payment now to start production of your custom Yakan masterpiece!</p>
                    
                    @if($order->final_price)
                        @php
                            $approvedSingleParts = $getPriceParts($order);
                            $approvedSingleTotal = (float) ($approvedSingleParts['total'] ?? ($order->final_price ?? 0));
                        @endphp
                        <div class="bg-white rounded-2xl p-6 border-2 mb-6 max-w-md mx-auto shadow-lg" style="border-color:#e0b0b0;">
                            <p class="text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide">Total Amount to Pay</p>
                            <p class="text-5xl font-black mb-3" style="color:#800000;">₱{{ number_format($isBatchOrder ? $batchPaymentTotal : $approvedSingleTotal, 2) }}</p>
                            <p class="text-xs text-gray-500">
                                @if($isBatchOrder)
                                    Batch Order ({{ $batchUnpaidOrders->count() }} items)
                                @else
                                    Order {{ $order->display_ref }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Next Steps Guide -->
                    <div class="bg-white rounded-2xl p-6 border-2 mb-8 max-w-3xl mx-auto text-left shadow-lg" style="border-color:#e0b0b0;">
                        <h4 class="font-bold text-lg mb-4 flex items-center gap-2" style="color:#800000;">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Next Steps (Susunod na Gawin):
                        </h4>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color:#800000;">1</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Click "Proceed to Payment" button</p>
                                    <p class="text-sm text-gray-600">Choose your preferred payment method (Maya or Bank Transfer)</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color:#800000;">2</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Follow the payment instructions</p>
                                    <p class="text-sm text-gray-600">You'll see payment details and account information</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color:#800000;">3</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Upload your payment receipt</p>
                                    <p class="text-sm text-gray-600">Take a screenshot or photo of your payment confirmation</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color:#800000;">4</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Wait for admin verification</p>
                                    <p class="text-sm text-gray-600">We'll verify your payment and start production (usually within 24 hours)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('custom_orders.payment', ['order' => $order->id, 'auth_token' => $authToken]) }}" class="inline-flex items-center justify-center text-white font-bold py-5 px-12 rounded-xl transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 hover:scale-105 text-lg" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);">
                            <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Proceed to Payment
                        </a>
                        <p class="text-sm text-gray-500 mt-2">Secure payment • Your order will start production once verified</p>
                    </div>
                </div>
            
            {{-- Approved Status - Payment Pending Verification --}}
            @elseif($order->status === 'approved' && $order->payment_status === 'pending_verification')
                <div class="w-full rounded-2xl p-8 text-center shadow-2xl border-2" style="background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border-color:#f59e0b;">
                    <div class="w-24 h-24 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold mb-3 text-amber-900">✅ Payment Receipt Submitted!</h3>
                    <p class="text-gray-700 mb-6 max-w-2xl mx-auto text-lg">Thank you! Your payment proof has been received and is currently being verified by our admin team. You'll receive a notification once approved.</p>
                    
                    @if($order->final_price)
                        <div class="bg-white rounded-2xl p-6 border-2 mb-6 max-w-md mx-auto shadow-lg" style="border-color:#fbbf24;">
                            <p class="text-sm font-semibold text-amber-700 mb-2 uppercase tracking-wide">Amount Submitted</p>
                            <p class="text-5xl font-black mb-3 text-amber-600">₱{{ number_format($order->final_price, 2) }}</p>
                            <p class="text-xs text-gray-500">Order {{ $order->display_ref }}</p>
                        </div>
                    @endif

                    <!-- What's Happening Now -->
                    <div class="bg-white rounded-2xl p-6 border-2 mb-6 max-w-3xl mx-auto text-left shadow-lg" style="border-color:#fbbf24;">
                        <h4 class="font-bold text-lg mb-4 flex items-center gap-2 text-amber-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            What Happens Next?
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Admin is verifying your payment</p>
                                    <p class="text-sm text-gray-600">We're checking your receipt and transaction details</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">You'll get notified</p>
                                    <p class="text-sm text-gray-600">Once verified, we'll send you an email and app notification</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Production will begin</p>
                                    <p class="text-sm text-gray-600">Our master craftsmen will start weaving your custom Yakan fabric</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg max-w-2xl mx-auto">
                        <p class="text-sm text-amber-800 flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Estimated verification time:</strong> Within 24 hours (usually much faster!)</span>
                        </p>
                    </div>
                </div>

            {{-- Processing Status - Payment Accepted --}}
            @elseif($order->status === 'processing' && $order->payment_status === 'paid')
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">Payment Accepted</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">Your payment has been received and your order is now in production!</p>
                    @if($order->final_price)
                    <div class="bg-white rounded-xl p-4 border-2 inline-block" style="border-color:#e0b0b0;">
                        <p class="text-sm text-gray-600 mb-1">Amount Paid</p>
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                    </div>
                    @endif
                </div>

            {{-- Processing Status - Awaiting Payment --}}
            @elseif($order->status === 'processing' && $order->payment_status !== 'paid')
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">Payment Required</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">You've accepted the quote! Please complete your payment to proceed with production.</p>
                    @if($order->final_price)
                    <div class="bg-white rounded-xl p-4 border-2 mb-6 inline-block" style="border-color:#e0b0b0;">
                        <p class="text-sm text-gray-600 mb-1">Amount Due</p>
                        <p class="text-3xl font-bold" style="color:#800000;">₱{{ number_format($displayOrderTotal ?? ($order->final_price ?? 0), 2) }}</p>
                    </div>
                    @endif
                    <div>
                        <a href="{{ route('custom_orders.payment', ['order' => $order->id, 'auth_token' => $authToken]) }}" class="inline-flex items-center justify-center text-white font-bold py-4 px-10 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            Complete Payment
                        </a>
                    </div>
                </div>

            {{-- Delivered Status - Waiting for Customer Confirmation --}}
            @elseif($order->status === 'delivered')
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">📦 Order Delivered!</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">Your order has been delivered. Please confirm that you've received it.</p>
                    @if($order->final_price)
                    <div class="bg-white rounded-xl p-4 border-2 mb-6 inline-block" style="border-color:#e0b0b0;">
                        <p class="text-sm text-gray-600 mb-1">Total Paid</p>
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                    </div>
                    @endif
                    <form method="POST" action="{{ route('custom_orders.confirm_received', $order) }}">
                        @csrf
                        <button type="submit" class="w-full max-w-md mx-auto text-white font-bold py-4 px-10 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Confirm Order Received
                        </button>
                    </form>
                    <p class="mt-4 text-xs text-gray-600">Click to confirm you've received your order</p>
                </div>

            {{-- Completed Status - Order Received by Customer --}}
            @elseif($order->status === 'completed')
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">✅ Order Received & Completed</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">Thank you for confirming! Your order is now complete.</p>
                    @if($order->final_price)
                    <div class="bg-white rounded-xl p-4 border-2 inline-block" style="border-color:#e0b0b0;">
                        <p class="text-sm text-gray-600 mb-1">Total Paid</p>
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($displayPaidTotal ?? ($displayOrderTotal ?? ($order->final_price ?? 0)), 2) }}</p>
                    </div>
                    @endif
                </div>

            {{-- Cancelled/Rejected Status --}}
            @elseif(in_array($order->status, ['cancelled', 'rejected']))
                <div class="w-full rounded-2xl p-8 text-center shadow-lg border-2" style="background-color:#fff5f5; border-color:#c08080;">
                    <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center shadow-md" style="background-color:#800000;">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2" style="color:#800000;">{{ $order->status === 'rejected' ? 'Order Rejected' : 'Order Cancelled' }}</h3>
                    <p class="text-gray-700 mb-6 max-w-md mx-auto">This order has been {{ $order->status === 'rejected' ? 'rejected' : 'cancelled' }}.</p>
                    @if($order->rejection_reason)
                        <div class="bg-white rounded-xl p-4 border-2 inline-block" style="border-color:#e0b0b0;">
                            <p class="text-sm font-semibold mb-1" style="color:#800000;">Reason:</p>
                            <p class="text-sm text-gray-700">{{ $order->rejection_reason }}</p>
                        </div>
                    @endif
                    @if($order->rejected_at)
                        <div class="mt-4 text-xs text-gray-600">
                            {{ $order->status === 'rejected' ? 'Rejected' : 'Cancelled' }} on {{ $order->rejected_at->format('M d, Y \a\t h:i A') }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                     <a href="{{ route('custom_orders.index', ['auth_token' => $authToken]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-white border-2 text-gray-700 font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200" style="border-color:#c08080;" onmouseover="this.style.backgroundColor='#fff5f5'" onmouseout="this.style.backgroundColor='white'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Orders
                </a>
                @if($order->design_upload)
                @php
                    // Use same logic for download button
                    if (str_starts_with($order->design_upload, 'data:image')) {
                        $downloadUrl = $order->design_upload;
                    } elseif (str_starts_with($order->design_upload, 'custom_orders/')) {
                        $downloadUrl = asset('uploads/' . $order->design_upload);
                    } else {
                        $downloadUrl = asset('storage/' . $order->design_upload);
                    }
                @endphp
                <a href="{{ $downloadUrl }}" 
                   download
                   class="flex-1 inline-flex items-center justify-center px-6 py-4 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Design
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var anchor = document.getElementById('left-action-anchor');
    var actionBlock = document.getElementById('action-buttons-block');

    if (!anchor || !actionBlock) {
        return;
    }

    if (actionBlock.parentElement !== anchor) {
        actionBlock.classList.remove('lg:w-2/3', 'lg:pr-6');
        actionBlock.classList.add('w-full', 'mt-6');
        anchor.appendChild(actionBlock);
    }
});
</script>

{{-- ===== REVIEW SECTION ===== --}}
@if(in_array($order->status, ['delivered', 'completed']) && auth()->check())
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 pb-12" id="review-section">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color:#800000;">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        Rate Your Custom Order
    </h2>

    @php
        $customReview = \App\Models\Review::where('custom_order_id', $order->id)
            ->where('user_id', auth()->id())
            ->first();
    @endphp

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200" style="background-color:#fff5f5;">
            <p class="font-semibold text-gray-800">Custom Order {{ $order->display_ref }} — {{ $order->product->name ?? 'Custom Fabric' }}</p>
            <p class="text-sm text-gray-500">Share your experience with this custom order</p>
        </div>

        <div class="px-6 py-5">
            @if($customReview)
                {{-- Show existing review --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-1 flex-wrap">
                        @for($s = 1; $s <= 5; $s++)
                            <svg class="w-7 h-7 {{ $s <= $customReview->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="ml-2 text-sm text-gray-600 font-semibold">{{ $customReview->rating }}/5</span>
                        <span class="ml-auto text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">✓ Review Submitted</span>
                    </div>
                    @if($customReview->title)
                        <p class="font-semibold text-gray-800">"{{ $customReview->title }}"</p>
                    @endif
                    @if($customReview->comment)
                        <p class="text-gray-700 text-sm">{{ $customReview->comment }}</p>
                    @endif
                    @if($customReview->review_images && count($customReview->review_images) > 0)
                        <div class="flex gap-2 flex-wrap mt-2">
                            @foreach($customReview->review_images as $imgUrl)
                                <img src="{{ $imgUrl }}" alt="Review photo" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                            @endforeach
                        </div>
                    @endif
                    <p class="text-xs text-gray-400">Submitted {{ $customReview->created_at->format('M d, Y') }}</p>
                </div>
            @else
                {{-- Review form --}}
                <form action="{{ route('reviews.store.custom-order', $order) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        {{-- Star Rating --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                            <div class="flex gap-1" id="custom-stars">
                                @for($s = 1; $s <= 5; $s++)
                                    <button type="button" data-value="{{ $s }}"
                                        class="custom-star-btn w-10 h-10 text-gray-300 hover:text-yellow-400 transition-colors"
                                        onclick="setCustomRating({{ $s }})">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" id="custom-rating" value="" required>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Review Title</label>
                            <input type="text" name="title" maxlength="255" placeholder="Summarize your experience" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color:#800000;">
                        </div>

                        {{-- Comment --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Your Review</label>
                            <textarea name="comment" rows="3" maxlength="1000" placeholder="Share your experience with this custom order..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent resize-none" style="--tw-ring-color:#800000;"></textarea>
                        </div>

                        {{-- Photo Upload --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Photos (optional, up to 5)</label>
                            <input type="file" name="images[]" accept="image/*" multiple
                                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#800000] file:text-white hover:file:bg-[#600000] cursor-pointer"
                                onchange="previewCustomImages(this)">
                            <div id="custom-review-preview" class="flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <button type="submit" class="text-white font-bold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            Submit Review
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
function setCustomRating(value) {
    document.getElementById('custom-rating').value = value;
    const stars = document.querySelectorAll('#custom-stars .custom-star-btn');
    stars.forEach(function(btn) {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.toggle('text-yellow-400', v <= value);
        btn.classList.toggle('text-gray-300', v > value);
    });
}

function previewCustomImages(input) {
    const preview = document.getElementById('custom-review-preview');
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
</script>
@endif

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
@endsection