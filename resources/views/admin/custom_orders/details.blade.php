@extends('layouts.admin')

@section('content')
@php
    $batchOrders = $batchOrders ?? collect();
    $customOrderEstimatedDays = (int) \App\Models\SystemSetting::get('custom_order_estimated_days', 14);
    $estimatedCompletionDate = $order->created_at ? $order->created_at->copy()->addDays($customOrderEstimatedDays) : null;
    $resolveShippingFromAddress = function ($item) {
        $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return 0.0;
        }

        $city = strtolower((string) ($item->delivery_city ?? ''));
        $province = strtolower((string) ($item->delivery_province ?? ''));
        $address = strtolower((string) ($item->delivery_address ?? ''));
        $haystack = trim($address . ' ' . $city . ' ' . $province);

        // Older orders may miss delivery fields; fallback to user's default address.
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

        // Zone 0: Zamboanga area (₱100)
        if (str_contains($haystack, 'zamboanga')) {
            return 100.0;
        }

        // Zone 1: BARMM (₱100)
        if (
            str_contains($haystack, 'barmm') ||
            str_contains($haystack, 'bangsamoro') ||
            str_contains($haystack, 'basilan') ||
            str_contains($haystack, 'sulu') ||
            str_contains($haystack, 'tawi')
        ) {
            return 100.0;
        }

        if (
            str_contains($haystack, 'mindanao') ||
            str_contains($haystack, 'davao') ||
            str_contains($haystack, 'cagayan de oro') ||
            str_contains($haystack, 'iligan') ||
            str_contains($haystack, 'cotabato') ||
            str_contains($haystack, 'caraga') ||
            str_contains($haystack, 'general santos') ||
            str_contains($haystack, 'soccsksargen')
        ) {
            return 180.0;
        }

        if (
            str_contains($haystack, 'visaya') ||
            str_contains($haystack, 'cebu') ||
            str_contains($haystack, 'iloilo') ||
            str_contains($haystack, 'bacolod') ||
            str_contains($haystack, 'tacloban') ||
            str_contains($haystack, 'leyte') ||
            str_contains($haystack, 'samar') ||
            str_contains($haystack, 'bohol') ||
            str_contains($haystack, 'negros')
        ) {
            return 250.0;
        }

        if (
            str_contains($haystack, 'ncr') ||
            str_contains($haystack, 'metro manila') ||
            str_contains($haystack, 'manila') ||
            str_contains($haystack, 'quezon city') ||
            str_contains($haystack, 'makati') ||
            str_contains($haystack, 'calabarzon') ||
            str_contains($haystack, 'central luzon') ||
            str_contains($haystack, 'laguna') ||
            str_contains($haystack, 'cavite') ||
            str_contains($haystack, 'bulacan')
        ) {
            return 300.0;
        }

        return 350.0;
    };
    $getAdminPriceParts = function ($item) use ($resolveShippingFromAddress) {
        $quoted = (float) ($item->final_price ?? $item->estimated_price ?? 0);
        $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $breakdown = method_exists($item, 'getPriceBreakdown') ? ($item->getPriceBreakdown() ?? []) : [];
        $breakdownData = $breakdown['breakdown'] ?? [];

        $material = (float) ($breakdownData['material_cost'] ?? 0);
        $pattern = (float) ($breakdownData['pattern_fee'] ?? 0);
        $labor = (float) ($breakdownData['labor_cost'] ?? 0);
        $discount = (float) ($breakdownData['discount'] ?? 0);
        $deliveryFeeInBreakdown = (float) ($breakdownData['delivery_fee'] ?? 0);

        $itemsSubtotalFromBreakdown = max(($material + $pattern + $labor - $discount), 0);

        // If delivery fee is explicitly in breakdown, quoted amount is already complete.
        if ($deliveryFeeInBreakdown > 0) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        // Recalculate shipping from address and avoid adding it twice if quoted already includes it.
        $shipping = $resolveShippingFromAddress($item);
        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - ($itemsSubtotalFromBreakdown + $shipping)) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - $itemsSubtotalFromBreakdown) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => $shipping, 'total' => $quoted + $shipping];
        }

        // Legacy fallback: if we cannot infer inclusion from breakdown, treat quoted as total.
        if ($itemsSubtotalFromBreakdown <= 0) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        return ['quoted' => $quoted, 'shipping' => $shipping, 'total' => $quoted + $shipping];
    };

    $calculateAdminDisplayTotal = fn($item) => ($getAdminPriceParts($item)['total'] ?? 0);
    $isNonChatPatternOrder = fn($item) => empty($item->chat_id) && (($item->design_method ?? null) === 'pattern');
    $mainOrderSkipsReview = $isNonChatPatternOrder($order);

    $batchItems = collect([$order])->merge($batchOrders)->sortBy('id')->values();
    $batchQuotedSubtotal = (float) $batchItems->sum(fn($item) => $getAdminPriceParts($item)['quoted'] ?? 0);
    $batchShippingTotal = (float) $batchItems
        ->map(function ($item) use ($getAdminPriceParts) {
            $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
            if ($deliveryType === 'pickup') {
                return 0;
            }
            return (float) ($getAdminPriceParts($item)['shipping'] ?? 0);
        })
        ->sum();
    $batchGrandTotal = $batchQuotedSubtotal + $batchShippingTotal;
    $batchPaidCount = (int) $batchItems->filter(fn($item) => (($item->payment_status ?? '') === 'paid') || !empty($item->payment_confirmed_at))->count();
@endphp
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-3">
        <div class="min-w-0">
            <a href="{{ route('admin.custom-orders.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Orders
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">Order {{ $order->display_ref }} - Details</h1>
            <p class="text-gray-600 mt-1">Created {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
            <p class="text-sm font-semibold mt-1" style="color:#800000;">
                Estimated Turnaround: {{ $customOrderEstimatedDays }} day{{ $customOrderEstimatedDays === 1 ? '' : 's' }}
                @if($estimatedCompletionDate)
                    &bull; Target Date: {{ $estimatedCompletionDate->format('M d, Y') }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
            {{-- Status Badge --}}
            @php
                $displayStatusLabel = $order->status === 'delivered'
                    ? 'Delivered (Awaiting Customer Confirmation)'
                    : ucfirst(str_replace('_', ' ', $order->status));
            @endphp
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                {{ $order->status === 'delivered' || $order->status === 'completed' ? 'bg-green-100 text-green-700' : 
                   ($order->status === 'out_for_delivery' ? 'bg-blue-100 text-blue-700' : 
                   ($order->status === 'production_complete' ? 'bg-purple-100 text-[#800000]' : 
                   ($order->status === 'processing' || $order->status === 'in_production' ? 'bg-orange-100 text-orange-700' : 
                   ($order->status === 'cancelled' || $order->status === 'rejected' ? 'bg-red-100 text-red-700' : 
                   'bg-yellow-100 text-yellow-700')))) }}">
                {{ $displayStatusLabel }}
            </span>
            
            {{-- Payment Status Badge --}}
            @php
                $paymentClass = match($order->payment_status) {
                    'paid' => 'bg-green-100 text-green-700',
                    'pending' => 'bg-orange-100 text-orange-700',
                    'failed' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
                $paymentLabel = match($order->payment_status) {
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'failed' => 'Failed',
                    default => 'Unpaid',
                };
            @endphp
            <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $paymentClass }}">
                {{ $paymentLabel }}
            </span>

            <a href="{{ route('admin.custom-orders.invoice', $order) }}{{ request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '' }}"
               target="_blank"
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold text-white"
               style="background-color:#111827;"
               onmouseover="this.style.backgroundColor='#1f2937'"
               onmouseout="this.style.backgroundColor='#111827'">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2m2 4h6m-3-4v4m-4-8h8"/>
                </svg>
                Print Invoice
            </a>
        </div>
    </div>

    {{-- Payment confirmed banner (shown via URL param to survive token-auth session resets) --}}
    @if(request('paid') == '1')
    <div class="mb-4 bg-green-100 border border-green-400 text-green-800 rounded-lg px-4 py-3 flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span class="font-semibold">Maya payment marked as paid. Order is now processing.</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content - Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ===== Batch/Submission Panel (Image-1 Style) ===== --}}
            @if($batchItems->count() > 1)
            <div class="border rounded-xl p-3" style="background:#fdf9f9; border-color:#e9bfc5;">
                <h3 class="text-xl font-extrabold mb-2" style="color:#800000;">All Items From This Submission</h3>

                <div class="space-y-2.5">
                    @foreach($batchItems as $index => $item)
                        @php
                            $itemPrice = $getAdminPriceParts($item);
                            $statusPill = $item->status ?? 'pending';
                            $itemDeliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');

                            $patternLabel = 'N/A';
                            $thumbPatternModel = null;
                            $rawPatterns = $item->patterns;
                            $patternArr = is_array($rawPatterns) ? $rawPatterns : [];
                            if (!empty($patternArr)) {
                                $firstPattern = $patternArr[0] ?? null;
                                if (is_numeric($firstPattern)) {
                                    $thumbPatternModel = \App\Models\YakanPattern::find((int) $firstPattern);
                                    $patternLabel = $thumbPatternModel?->name ?? ('Pattern #' . (int) $firstPattern);
                                } else {
                                    $patternLabel = (string) $firstPattern;
                                    $thumbPatternModel = \App\Models\YakanPattern::where('name', $patternLabel)->first();
                                }
                            }

                            $itemDesignMeta = is_array($item->design_metadata) ? $item->design_metadata : [];
                            $itemCustomization = [];
                            if (isset($itemDesignMeta['customization_settings']) && is_array($itemDesignMeta['customization_settings'])) {
                                $itemCustomization = $itemDesignMeta['customization_settings'];
                            } elseif (is_array($item->customization_settings ?? null)) {
                                $itemCustomization = $item->customization_settings;
                            }

                            $previewImageUrls = [];
                            if (!empty($item->design_upload)) {
                                $rawUploads = is_string($item->design_upload) ? explode(',', $item->design_upload) : [$item->design_upload];
                                foreach ($rawUploads as $upload) {
                                    $cleanUpload = trim((string) $upload);
                                    if ($cleanUpload === '') {
                                        continue;
                                    }
                                    $previewImageUrls[] = (str_starts_with($cleanUpload, 'http://') || str_starts_with($cleanUpload, 'https://') || str_starts_with($cleanUpload, 'data:image'))
                                        ? $cleanUpload
                                        : asset('storage/' . ltrim($cleanUpload, '/'));
                                }
                            }
                        @endphp

                        <div class="rounded-xl border p-3" style="background:#fff; border-color:#e9bfc5;">
                            <div class="flex items-start gap-2 mb-2">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white font-bold text-xs" style="background:#800000;">{{ $index + 1 }}</div>
                                    <div class="min-w-0">
                                        <div class="text-lg md:text-xl font-extrabold truncate" style="color:#800000;">Custom Order ID: {{ $item->display_ref ?? ('CO-' . str_pad((string) $item->id, 5, '0', STR_PAD_LEFT)) }}</div>
                                        <div class="text-xs text-gray-600">{{ optional($item->created_at)->format('M d, Y g:i A') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-2">
                                <div>
                                    <div class="text-gray-500 text-xs">Pattern</div>
                                    <div class="font-bold text-base text-gray-900">{{ $patternLabel }}</div>
                                    @if($thumbPatternModel && $thumbPatternModel->hasSvg())
                                        <div class="mt-1.5 w-14 h-14 rounded border border-[#e9bfc5] bg-white flex items-center justify-center overflow-hidden">
                                            <div style="max-width:50px; max-height:50px;">{!! $thumbPatternModel->getSvgContent() !!}</div>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Fabric Type</div>
                                    <div class="font-bold text-base text-gray-900">{{ $item->fabric_type_name ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Intended Use</div>
                                    <div class="font-bold text-base text-gray-900">{{ $item->intended_use_label ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Quantity</div>
                                    <div class="font-bold text-base text-gray-900">{{ number_format((float) ($item->fabric_quantity_meters ?? 0), 2) }} meters</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs">Est. Price</div>
                                    <div class="font-bold text-base" style="color:#800000;">₱{{ number_format((float) ($itemPrice['quoted'] ?? 0), 2) }}</div>
                                </div>
                            </div>

                            <div class="border-t pt-2.5 flex items-center justify-between" style="border-color:#efcfd3;">
                                <div class="text-sm text-gray-700">
                                    Delivery:
                                    <span class="font-bold text-gray-900">{{ $itemDeliveryType === 'pickup' ? 'Pickup' : 'Delivery' }}</span>
                                </div>
                                <a href="#" onclick="event.preventDefault(); toggleBatchItemDetails({{ $item->id }});" class="text-sm font-extrabold" style="color:#800000;">
                                    View Full Details→
                                </a>
                            </div>

                            <div id="batch-item-details-{{ $item->id }}" class="hidden mt-3 rounded-xl border p-3" style="border-color:#efcfd3; background:#fff9f9;">
                                <div class="mb-4">
                                    <div class="text-[#800000] font-bold text-sm mb-2">Pattern Preview</div>
                                    <div class="rounded-xl border border-[#e9bfc5] bg-white p-3">
                                        @if($thumbPatternModel && $thumbPatternModel->hasSvg())
                                            <div class="w-full h-72 flex items-center justify-center overflow-hidden rounded-lg bg-[#fff7f7]">
                                                <div style="max-width:95%; max-height:95%;">{!! $thumbPatternModel->getSvgContent() !!}</div>
                                            </div>
                                        @elseif(!empty($previewImageUrls))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($previewImageUrls as $imgUrl)
                                                    <div class="w-full h-72 rounded-lg bg-[#fff7f7] border border-[#efcfd3] overflow-hidden flex items-center justify-center">
                                                        <img src="{{ $imgUrl }}" alt="Design Preview" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="w-full h-40 rounded-lg bg-[#fff7f7] border border-dashed border-[#efcfd3] flex items-center justify-center text-sm text-gray-500">
                                                No pattern preview available.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="text-[#800000] font-bold text-sm mb-2">Customization Settings</div>
                                    @if(!empty($itemCustomization))
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                            @foreach($itemCustomization as $settingKey => $settingValue)
                                                <div class="rounded-lg border border-[#efcfd3] bg-white p-2">
                                                    <div class="text-[11px] text-gray-500 uppercase">{{ ucfirst(str_replace('_', ' ', (string) $settingKey)) }}</div>
                                                    <div class="text-sm font-semibold text-gray-900">{{ is_scalar($settingValue) ? $settingValue : json_encode($settingValue) }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded-lg border border-dashed border-[#efcfd3] bg-white p-3 text-sm text-gray-500">
                                            No customization settings available.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($batchItems->count() <= 1)
            {{-- Pattern Preview Section (single-order only) --}}
            @php
                // Load pattern for SVG display
                $patternModel = null;
                if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id'])) {
                    $patternModel = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                } elseif (!empty($order->patterns) && is_array($order->patterns)) {
                    if (!empty($order->patterns) && is_numeric($order->patterns[0])) {
                        $patternModel = \App\Models\YakanPattern::find($order->patterns[0]);
                    } elseif (!empty($order->patterns)) {
                        $patternModel = \App\Models\YakanPattern::where('name', $order->patterns[0])->first();
                    }
                }
            @endphp

            @if($patternModel && $patternModel->hasSvg())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Pattern Preview
                    @if($order->design_method === 'pattern')
                        <span class="text-sm font-normal text-[#800000]">(Customized Pattern: {{ $patternModel->name }})</span>
                    @endif
                </h2>

                @php
                    // Get customization settings from order
                    $customization = $order->customization_settings ?? [];
                    $filterStyle = '';
                    if (!empty($customization)) {
                        $hue = $customization['hue'] ?? 0;
                        $saturation = $customization['saturation'] ?? 100;
                        $brightness = $customization['brightness'] ?? 100;
                        $scale = $customization['scale'] ?? 1;
                        $rotation = $customization['rotation'] ?? 0;
                        $opacity = $customization['opacity'] ?? 1;

                        $filterStyle = sprintf(
                            'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                            $hue,
                            $saturation,
                            $brightness,
                            $opacity,
                            $scale,
                            $rotation
                        );
                    }
                @endphp

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-2 border-red-200">
                    <div class="w-full max-w-2xl mx-auto rounded-lg shadow-lg bg-white p-4 overflow-hidden">
                        <div class="w-full h-96 flex items-center justify-center">
                            <div style="{{ $filterStyle }} transform-origin: center; max-width: 100%; max-height: 100%;">
                                {!! $patternModel->getSvgContent() !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customization Settings --}}
                @if($order->design_metadata && is_array($order->design_metadata))
                    @if(isset($order->design_metadata['customization_settings']))
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                            <h3 class="col-span-full text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Customization Settings
                            </h3>
                            @foreach($order->design_metadata['customization_settings'] as $key => $value)
                                <div class="bg-white rounded-lg p-3 border-2 border-red-200 hover:border-[#800000] transition-colors">
                                    <div class="text-xs text-gray-500 uppercase font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                    <div class="text-sm font-bold text-[#800000]">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            @elseif($order->design_upload)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Design References
                </h2>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-2 border-red-200">
                    @php
                        // Handle multiple images (comma-separated)
                        $designImages = is_string($order->design_upload) ? explode(',', $order->design_upload) : [$order->design_upload];
                        $designImages = array_filter(array_map('trim', $designImages));
                    @endphp

                    @if(count($designImages) > 1)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($designImages as $imageUrl)
                                <div class="bg-white rounded-lg p-2 shadow-md">
                                    @if(str_starts_with($imageUrl, 'http'))
                                        <img src="{{ $imageUrl }}" alt="Design Reference"
                                             class="w-full h-64 object-contain rounded cursor-pointer hover:scale-105 transition-transform"
                                             onclick="window.open('{{ $imageUrl }}', '_blank')">
                                    @elseif(str_starts_with($imageUrl, 'data:image'))
                                        <img src="{{ $imageUrl }}" alt="Design Reference"
                                             class="w-full h-64 object-contain rounded">
                                    @else
                                        <img src="{{ asset('storage/' . $imageUrl) }}" alt="Design Reference"
                                             class="w-full h-64 object-contain rounded cursor-pointer hover:scale-105 transition-transform"
                                             onclick="window.open('{{ asset('storage/' . $imageUrl) }}', '_blank')">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php
                            $singleImage = $designImages[0] ?? $order->design_upload;
                        @endphp
                        @if(str_starts_with($singleImage, 'http'))
                            <img src="{{ $singleImage }}" alt="Design Reference"
                                 class="w-full max-h-96 object-contain rounded-lg shadow-lg cursor-pointer hover:scale-105 transition-transform"
                                 onclick="window.open('{{ $singleImage }}', '_blank')">
                        @elseif(str_starts_with($singleImage, 'data:image'))
                            <img src="{{ $singleImage }}" alt="Design Reference"
                                 class="w-full max-h-96 object-contain rounded-lg shadow-lg">
                        @else
                            <img src="{{ asset('storage/' . $singleImage) }}" alt="Design Reference"
                                 class="w-full max-h-96 object-contain rounded-lg shadow-lg cursor-pointer hover:scale-105 transition-transform"
                                 onclick="window.open('{{ asset('storage/' . $singleImage) }}', '_blank')">
                        @endif
                    @endif
                </div>

                @if(count($designImages) > 1)
                    <p class="text-sm text-gray-600 mt-2">
                        <svg class="w-4 h-4 inline text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Click on any image to view in full size
                    </p>
                @endif

                {{-- Customization Settings --}}
                @if($order->design_metadata && is_array($order->design_metadata))
                    @if(isset($order->design_metadata['customization_settings']))
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                            <h3 class="col-span-full text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Customization Settings
                            </h3>
                            @foreach($order->design_metadata['customization_settings'] as $key => $value)
                                <div class="bg-white rounded-lg p-3 border-2 border-red-200 hover:border-[#800000] transition-colors">
                                    <div class="text-xs text-gray-500 uppercase font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                    <div class="text-sm font-bold text-[#800000]">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            @endif

            {{-- Order Details (single-order only) --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Fabric Type --}}
                    @if($order->fabric_type)
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="text-sm text-[#800000] font-semibold mb-1">Fabric Type</div>
                        <div class="text-lg font-bold text-gray-900">{{ $order->fabric_type_name }}</div>
                    </div>
                    @endif

                    {{-- Quantity --}}
                    @if($order->fabric_quantity_meters)
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="text-sm text-[#800000] font-semibold mb-1">Quantity</div>
                        <div class="text-lg font-bold text-gray-900">{{ $order->fabric_quantity_meters }} meters</div>
                    </div>
                    @endif

                    {{-- Intended Use --}}
                    @if($order->intended_use)
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="text-sm text-green-600 font-semibold mb-1">Intended Use</div>
                        <div class="text-lg font-bold text-gray-900">{{ $order->intended_use_label }}</div>
                    </div>
                    @endif

                    {{-- Design Method --}}
                    @if($order->design_method)
                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                        <div class="text-sm text-indigo-600 font-semibold mb-1">Design Method</div>
                        <div class="text-lg font-bold text-gray-900">{{ ucfirst($order->design_method) }}</div>
                    </div>
                    @endif
                </div>

                @php
                    $rawSpecifications = trim((string) ($order->specifications ?? ''));
                    $specLines = preg_split('/\r\n|\r|\n/', $rawSpecifications) ?: [];
                    $meaningfulSpecLines = collect($specLines)
                        ->map(fn($line) => trim((string) $line))
                        ->filter(fn($line) => $line !== '')
                        ->reject(function ($line) {
                            $lower = strtolower($line);
                            return $lower === 'custom fabric order'
                                || str_starts_with($lower, 'fabric type:')
                                || str_starts_with($lower, 'quantity:')
                                || str_starts_with($lower, 'intended use:');
                        })
                        ->values();
                    $hasMeaningfulSpecifications = $meaningfulSpecLines->isNotEmpty();
                @endphp

                {{-- Specifications --}}
                @if($hasMeaningfulSpecifications)
                <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="text-sm text-gray-600 font-semibold mb-2">Specifications:</div>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $meaningfulSpecLines->implode("\n") }}</p>
                </div>
                @endif

                {{-- Special Requirements --}}
                @if($order->special_requirements)
                <div class="mt-4 bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="text-sm text-yellow-800 font-semibold mb-2">Special Requirements:</div>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $order->special_requirements }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Pricing Information --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Pricing</h2>
                
                @php
                    $materialCost = 0;
                    $patternFee = 0;
                    $shippingFee = 0;
                    $isFromChat = !empty($order->chat_id);
                    $breakdownParsed = false;
                    
                    if ($isFromChat) {
                        // Chat custom order - parse quote from chat messages
                        $quoteMessage = \App\Models\ChatMessage::where('chat_id', $order->chat_id)
                            ->where('sender_type', 'admin')
                            ->where('message', 'like', '%PRICE QUOTE%')
                            ->latest()
                            ->first();
                        
                        if ($quoteMessage) {
                            // Parse Material Cost
                            if (preg_match('/Material Cost:\s*₱?([\d,]+\.?\d*)/i', $quoteMessage->message, $matches)) {
                                $materialCost = floatval(str_replace(',', '', $matches[1]));
                                $breakdownParsed = true;
                            }
                            
                            // Parse Pattern Fee
                            if (preg_match('/Pattern Fee:\s*₱?([\d,]+\.?\d*)/i', $quoteMessage->message, $matches)) {
                                $patternFee = floatval(str_replace(',', '', $matches[1]));
                                $breakdownParsed = true;
                            }
                        }
                        
                        // Parse shipping from additional_notes or default to 0
                        if (preg_match('/Shipping Fee:\s*₱?([\d,]+\.?\d*)/i', $order->additional_notes ?? '', $matches)) {
                            $shippingFee = floatval(str_replace(',', '', $matches[1]));
                        }
                    } else {
                        // Pattern customizer order - calculate from patterns
                        $patternIds = $order->patterns;
                        if (is_string($patternIds)) {
                            $patternIds = json_decode($patternIds, true) ?? [];
                        }
                        
                        $patterns = \App\Models\YakanPattern::whereIn('id', (array)$patternIds)->get();
                        
                        $totalPatternFee = 0;
                        $pricePerMeter = 0;
                        foreach ($patterns as $pattern) {
                            $totalPatternFee += ($pattern->pattern_price ?? 0);
                            $pricePerMeter = $pattern->price_per_meter ?? 0;
                        }
                        
                        $patternFee = $totalPatternFee * ($order->quantity ?? 1);
                        $materialCost = ($order->fabric_quantity_meters ?? 0) * $pricePerMeter * ($order->quantity ?? 1);
                        
                        // Calculate shipping
                        $shippingFee = 100;
                        $address = strtolower($order->delivery_address ?? '');
                        $city = '';
                        $province = '';
                        
                        if (!$order->delivery_address && $order->user) {
                            $userDefaultAddr = $order->user->addresses()->where('is_default', true)->first();
                            if ($userDefaultAddr) {
                                $address = strtolower($userDefaultAddr->formatted_address ?? $userDefaultAddr->city . ', ' . $userDefaultAddr->province);
                                $city = strtolower($userDefaultAddr->city ?? '');
                                $province = strtolower($userDefaultAddr->province ?? '');
                            }
                        }
                        
                        if ($address || $city) {
                            if (str_contains($address, 'zamboanga') || str_contains($city, 'zamboanga')) {
                                $shippingFee = 0;
                            } elseif (str_contains($province, 'zamboanga') || in_array($city, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                                $shippingFee = 100;
                            } elseif (in_array($city, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) || str_contains($province, 'barmm') || str_contains($province, 'armm')) {
                                $shippingFee = 120;
                            } elseif (str_contains($province, 'mindanao') || in_array($city, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                                $shippingFee = 150;
                            } elseif (str_contains($province, 'visayas') || in_array($city, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                                $shippingFee = 180;
                            } elseif (str_contains($city, 'manila') || str_contains($province, 'ncr') || in_array($city, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                                $shippingFee = 220;
                            } elseif (str_contains($province, 'luzon') || in_array($city, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                                $shippingFee = 250;
                            } else {
                                $shippingFee = 280;
                            }
                        }
                        
                        $breakdownParsed = true;
                    }
                    
                    $quotedPrice = (float) ($order->final_price ?? $order->estimated_price ?? 0);
                    $orderDeliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                    $shippingFee = $orderDeliveryType === 'pickup' ? 0 : (float) ($order->shipping_fee ?? 0);
                    $subtotal = $materialCost + $patternFee;
                    $totalCalculated = $subtotal + $shippingFee;
                    $isGroupedContext = $batchItems->count() > 1;
                    $pricingCardQuoted = $isGroupedContext ? $batchQuotedSubtotal : $quotedPrice;
                    $pricingCardShipping = $isGroupedContext ? $batchShippingTotal : $shippingFee;
                    $pricingCardTotal = $isGroupedContext ? $batchGrandTotal : $totalCalculated;
                @endphp
                
                <div class="space-y-3">
                    @if($isGroupedContext)
                        @php
                            $groupPriceRows = $batchItems->map(function ($item) use ($getAdminPriceParts, $resolveShippingFromAddress) {
                                $quoted = (float) ($item->final_price ?? $item->estimated_price ?? 0);
                                $breakdownData = method_exists($item, 'getPriceBreakdown') ? ($item->getPriceBreakdown() ?? []) : [];
                                $breakdown = $breakdownData['breakdown'] ?? [];

                                $material = (float) ($breakdown['material_cost'] ?? 0);
                                $pattern = (float) ($breakdown['pattern_fee'] ?? 0);
                                $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
                                $shippingDisplay = $deliveryType === 'pickup' ? 0.0 : (float) $resolveShippingFromAddress($item);

                                // Always collect pattern models for display names.
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
                                $patternNames = $patternModels->pluck('name')->filter()->implode(', ');

                                // Fallback for pending/batch rows where admin_notes breakdown is unavailable.
                                if (($material + $pattern) <= 0.0) {
                                    $patternFallback = (float) $patternModels->sum(fn($p) => (float) ($p->pattern_price ?? 0));
                                    $pricePerMeter = (float) (($patternModels->first()->price_per_meter ?? 0));
                                    $meters = (float) ($item->fabric_quantity_meters ?? 0);
                                    $materialFallback = ($meters > 0 && $pricePerMeter > 0) ? ($meters * $pricePerMeter) : 0.0;

                                    if ($materialFallback > 0 || $patternFallback > 0) {
                                        $material = $materialFallback;
                                        $pattern = $patternFallback;
                                    }
                                }

                                $itemSubtotal = $material + $pattern;
                                if ($itemSubtotal <= 0.0) {
                                    $itemSubtotal = ($deliveryType !== 'pickup' && $quoted > $shippingDisplay)
                                        ? max($quoted - $shippingDisplay, 0)
                                        : $quoted;
                                }

                                if ($material <= 0.0 && $pattern <= 0.0 && $itemSubtotal > 0.0) {
                                    $material = $itemSubtotal;
                                }

                                // Shipping to display in grouped breakdown should reflect address-based fee, not only "to-add" state.
                                $itemShipping = $shippingDisplay;

                                return [
                                    'id' => $item->id,
                                    'material' => $material,
                                    'pattern' => $pattern,
                                    'pattern_names' => $patternNames ?? '',
                                    'subtotal' => $itemSubtotal,
                                    'shipping' => $itemShipping,
                                ];
                            });

                            $groupItemsSubtotal = (float) $groupPriceRows->sum('subtotal');
                            $groupShippingByAddress = (float) ($groupPriceRows->max('shipping') ?? 0);
                            $groupFinalTotal = $groupItemsSubtotal + $groupShippingByAddress;
                        @endphp

                        <div class="mt-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border-2 border-gray-300">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                💰 Price Breakdown
                            </h3>

                            <div class="space-y-3 text-sm">
                                @foreach($groupPriceRows as $row)
                                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                                        <div class="font-bold text-[#800000] mb-2">Custom Order #{{ $row['id'] }}</div>
                                        <div class="space-y-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-700">Material Cost:</span>
                                                <span class="font-semibold text-gray-900">₱{{ number_format($row['material'], 2) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-700">Pattern Fee{{ !empty($row['pattern_names']) ? ' (' . $row['pattern_names'] . ')' : '' }}:</span>
                                                <span class="font-semibold text-gray-900">₱{{ number_format($row['pattern'], 2) }}</span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-1 flex justify-between items-center">
                                                <span class="text-gray-700 font-medium">Subtotal:</span>
                                                <span class="font-bold text-gray-900">₱{{ number_format($row['subtotal'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="border-t border-gray-300 pt-2 flex justify-between items-center">
                                    <span class="text-gray-700 font-medium">Subtotal (All Items):</span>
                                    <span class="font-bold text-gray-900">₱{{ number_format($groupItemsSubtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Shipping Fee (Based on Delivery Address):</span>
                                    <span class="font-semibold {{ $groupShippingByAddress == 0 ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ $groupShippingByAddress == 0 ? 'FREE DELIVERY! 🎉' : '₱' . number_format($groupShippingByAddress, 2) }}
                                    </span>
                                </div>
                                <div class="border-t-2 border-[#800000] pt-2 mt-2 flex justify-between items-center">
                                    <span class="text-gray-900 font-bold text-base">TOTAL PRICE (ALL ITEMS):</span>
                                    <span class="font-bold text-lg text-green-600">₱{{ number_format($groupFinalTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif($breakdownParsed && ($materialCost > 0 || $patternFee > 0))
                        {{-- Itemized Breakdown --}}
                        <div class="mt-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border-2 border-gray-300">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                💰 Price Breakdown
                            </h3>
                            <div class="space-y-2 text-sm">
                                @if($materialCost > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Material Cost:</span>
                                    <span class="font-semibold text-gray-900">₱{{ number_format($materialCost, 2) }}</span>
                                </div>
                                @endif
                                @if($patternFee > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Pattern Fee:</span>
                                    <span class="font-semibold text-gray-900">₱{{ number_format($patternFee, 2) }}</span>
                                </div>
                                @endif
                                <div class="border-t border-gray-300 pt-2 flex justify-between items-center">
                                    <span class="text-gray-700 font-medium">Subtotal:</span>
                                    <span class="font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Shipping Fee:</span>
                                    <span class="font-semibold {{ $shippingFee == 0 ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ $shippingFee == 0 ? 'FREE DELIVERY! 🎉' : '₱' . number_format($shippingFee, 2) }}
                                    </span>
                                </div>
                                <div class="border-t-2 border-[#800000] pt-2 mt-2 flex justify-between items-center">
                                    <span class="text-gray-900 font-bold text-base">TOTAL TO PAY:</span>
                                    <span class="font-bold text-lg text-green-600">₱{{ number_format($totalCalculated, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Price Breakdown from getPriceBreakdown() method --}}
                        @php
                            $priceBreakdown = $order->getPriceBreakdown();
                            $breakdown = $priceBreakdown['breakdown'] ?? [];
                            $hasBreakdown = !empty($breakdown);
                        @endphp
                        
                        @if($hasBreakdown)
                            <div class="mt-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border-2 border-blue-300">
                                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    💰 Price Breakdown
                                </h3>
                                <div class="space-y-2 text-sm">
                                    {{-- Material Cost --}}
                                    @if(isset($breakdown['material_cost']) && $breakdown['material_cost'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Material Cost:</span>
                                            <span class="font-semibold text-gray-900">₱{{ number_format($breakdown['material_cost'], 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Pattern/Design Fee --}}
                                    @if(isset($breakdown['pattern_fee']) && $breakdown['pattern_fee'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Pattern/Design Fee:</span>
                                            <span class="font-semibold text-gray-900">₱{{ number_format($breakdown['pattern_fee'], 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Labor Cost --}}
                                    @if(isset($breakdown['labor_cost']) && $breakdown['labor_cost'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Labor Cost:</span>
                                            <span class="font-semibold text-gray-900">₱{{ number_format($breakdown['labor_cost'], 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Delivery Fee --}}
                                    @if(isset($breakdown['delivery_fee']) && $breakdown['delivery_fee'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Delivery Fee:</span>
                                            <span class="font-semibold text-gray-900">₱{{ number_format($breakdown['delivery_fee'], 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Discount (if applicable) --}}
                                    @if(isset($breakdown['discount']) && $breakdown['discount'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Discount:</span>
                                            <span class="font-semibold text-red-600">- ₱{{ number_format($breakdown['discount'], 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="border-t-2 border-[#800000] pt-2 mt-2 flex justify-between items-center">
                                        <span class="text-gray-900 font-bold text-base">TOTAL:</span>
                                        <span class="font-bold text-lg text-green-600">₱{{ number_format($quotedPrice + ($orderDeliveryType === 'pickup' ? 0 : (float) ($order->shipping_fee ?? 0)), 2) }}</span>
                                    </div>
                                </div>
                                
                                {{-- Admin Pricing Notes --}}
                                @if(!empty($priceBreakdown['notes']))
                                    <div class="mt-3 p-3 bg-white rounded-lg border border-blue-300">
                                        <p class="text-xs text-gray-600 font-medium mb-1">📝 Pricing Notes:</p>
                                        <p class="text-sm text-gray-800">{{ $priceBreakdown['notes'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Fallback when no breakdown available --}}
                            @if($order->estimated_price)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Estimated Price:</span>
                                <span class="text-lg font-semibold text-gray-900">₱{{ number_format($order->estimated_price, 2) }}</span>
                            </div>
                            @endif
                            
                            @if($order->final_price)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Final Price:</span>
                                <span class="text-2xl font-bold text-green-600">₱{{ number_format($order->final_price, 2) }}</span>
                            </div>
                            @endif
                        @endif
                    @endif
                    
                    @if($order->payment_method)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ in_array($order->payment_method, ['online', 'online_banking', 'maya']) ? 'Maya' :
                               ($order->payment_method === 'gcash' ? 'GCash' :
                               ($order->payment_method === 'bank_transfer' ? 'Bank Transfer' : ucfirst(str_replace('_', ' ', $order->payment_method)))) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Sidebar - Right Column --}}
        <div class="space-y-6">
            
            {{-- Customer Information --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Customer</h2>
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#600000] rounded-full flex items-center justify-center">
                        <span class="text-lg font-bold text-white">{{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $order->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-600">{{ $order->user->email ?? 'N/A' }}</div>
                    </div>
                </div>
                
                @if($order->phone)
                <div class="text-sm text-gray-600 mb-2">
                    <span class="font-semibold">Phone:</span> {{ $order->phone }}
                </div>
                @endif

                <div class="text-sm text-gray-600 mb-2">
                    <span class="font-semibold">Delivery:</span>
                    <span class="ml-1 font-medium text-gray-800">
                        @if($order->delivery_type === 'pickup')
                            Store Pickup
                        @elseif($order->delivery_type === 'delivery')
                            Delivery
                        @else
                            Not specified
                        @endif
                    </span>
                </div>
                
                @if($order->delivery_address)
                <div class="text-sm text-gray-600">
                    <span class="font-semibold">Address:</span>
                    <p class="mt-1 text-gray-700 whitespace-pre-line">{{ $order->delivery_address }}</p>
                </div>
                @endif
            </div>

            {{-- Payment Information --}}
            @if($order->payment_method || $order->payment_status !== 'unpaid')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Information</h2>
                @php
                    $normalizedPaymentMethod = strtolower((string) ($order->payment_method ?? ''));
                    $normalizedTransactionId = strtolower((string) ($order->transaction_id ?? ''));
                    $isPaymongoPayment = in_array($normalizedPaymentMethod, ['paymongo', 'online_banking'], true)
                        || str_starts_with($normalizedTransactionId, 'cs_')
                        || str_starts_with($normalizedTransactionId, 'pay_');

                    $paymongoReceiptUrl = route('admin.custom-orders.paymongo_receipt', $order);
                    $authToken = request()->query('auth_token');
                    if (!empty($authToken)) {
                        $paymongoReceiptUrl .= (str_contains($paymongoReceiptUrl, '?') ? '&' : '?') . 'auth_token=' . urlencode($authToken);
                    }

                    $paymentReferenceNumber = $isPaymongoPayment
                        ? (!empty($order->transaction_id) ? $order->transaction_id : 'N/A')
                        : (!empty($order->display_ref) ? $order->display_ref : ('CO-' . $order->id));
                    $paymentDateRaw = $order->payment_confirmed_at
                        ?? $order->payment_verified_at
                        ?? $order->paid_at
                        ?? $order->transfer_date
                        ?? (($order->payment_status ?? '') === 'paid' ? $order->updated_at : null)
                        ?? null;
                    $paymentDateDisplay = 'N/A';
                    if (!empty($paymentDateRaw)) {
                        try {
                            $paymentDateDisplay = \Carbon\Carbon::parse($paymentDateRaw)->format('M d, Y h:i A');
                        } catch (\Throwable $exception) {
                            $paymentDateDisplay = (string) $paymentDateRaw;
                        }
                    }
                @endphp
                
                {{-- Payment Status --}}
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Payment Status</div>
                    @php
                        $paymentStatusClass = match($order->payment_status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-orange-100 text-orange-700',
                            'failed' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };

                        $paymentStatusLabel = match($order->payment_status) {
                            'paid' => '✓ Paid',
                            'pending' => '⏳ Pending Verification',
                            'failed' => '✗ Failed',
                            default => 'Unpaid',
                        };
                    @endphp
                    <span class="px-3 py-1.5 rounded-full text-sm font-semibold inline-block {{ $paymentStatusClass }}">
                        {{ $paymentStatusLabel }}
                    </span>
                </div>

                {{-- Payment Method --}}
                @if($order->payment_method)
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Payment Method</div>
                    <div class="text-sm text-gray-900">
                        {{ in_array($order->payment_method, ['online', 'online_banking', 'maya']) ? 'Maya' :
                           ($order->payment_method === 'gcash' ? 'GCash' :
                           ($order->payment_method === 'bank_transfer' ? 'Bank Transfer' : ucfirst(str_replace('_', ' ', $order->payment_method)))) }}
                    </div>
                </div>
                @endif

                {{-- PayMongo Metadata --}}
                @if($isPaymongoPayment)
                <div id="coPaymongoSync" class="hidden" data-endpoint="{{ $paymongoReceiptUrl }}"></div>
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Reference Number</div>
                    <div id="coPmSummaryRef" class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $paymentReferenceNumber }}</div>
                </div>
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Payment ID</div>
                    <div id="coPmSummaryPaymentId" class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $order->transaction_id ?: 'N/A' }}</div>
                </div>
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Payment Date</div>
                    <div id="coPmSummaryPaidAt" class="text-sm text-gray-900">{{ $paymentDateDisplay }}</div>
                </div>
                <div class="mb-4">
                    <button type="button" onclick="viewPaymongoReceipt('{{ $paymongoReceiptUrl }}')"
                        class="inline-flex items-center px-3 py-2 text-white rounded-lg transition-colors text-sm font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        View Verified Receipt
                    </button>
                </div>
                @elseif($order->transaction_id)
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Transaction ID</div>
                    <div class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $order->transaction_id }}</div>
                </div>
                @endif

                {{-- Payment Receipt --}}
                @if($order->payment_receipt)
                <div class="mb-4">
                    @php
                        $receiptLabel = match($order->payment_method) {
                            'maya' => 'Maya Receipt',
                            'gcash' => 'GCash Receipt',
                            'online', 'online_banking' => 'GCash Receipt',
                            'bank_transfer' => 'Bank Transfer Receipt',
                            default => 'Payment Receipt',
                        };
                        // Support both Cloudinary URLs and local storage
                        $receiptUrl = (str_starts_with($order->payment_receipt, 'http://') || str_starts_with($order->payment_receipt, 'https://'))
                            ? $order->payment_receipt
                            : asset('storage/' . $order->payment_receipt);
                    @endphp
                    <div class="text-sm font-semibold text-gray-700 mb-2">{{ $receiptLabel }}</div>
                    <a href="{{ $receiptUrl }}" target="_blank" 
                       class="block bg-gray-50 border border-gray-200 rounded-lg p-2 hover:bg-gray-100 transition">
                        <img src="{{ $receiptUrl }}" alt="{{ $receiptLabel }}" 
                             class="w-full h-32 object-contain rounded">
                        <div class="text-xs text-center text-[#800000] mt-1">Click to view full size</div>
                    </a>
                </div>
                @endif

            </div>
            @endif

            {{-- Mark Maya Payment as Paid (show when Maya/online_banking payment is pending) --}}
            @if(in_array($order->payment_status, ['pending', 'unpaid']) && in_array($order->payment_method, ['maya', 'online_banking']) && $order->transaction_id)
            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4">
                <p class="text-sm font-bold text-blue-800 mb-2">⚠️ Maya payment completed but not confirmed</p>
                <form action="{{ route('admin.custom-orders.confirmPayment', $order) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST" onsubmit="return confirm('Mark this Maya payment as paid?')">
                    @csrf
                    @if(request('auth_token'))
                    <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                    @endif
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg">
                        ✓ Mark Maya Payment as Paid
                    </button>
                </form>
            </div>
            @endif

            {{-- Admin Actions --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </h2>
                
                <div class="space-y-4" id="adminActions">
                    @if(isset($latestCustomRefundRequest) && $latestCustomRefundRequest)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-900">Customer {{ ucfirst($latestCustomRefundRequest->request_type) }} Request</p>
                            @php
                                $customAdminRefundStatusMap = [
                                    'requested' => 'bg-yellow-100 text-yellow-800',
                                    'under_review' => 'bg-blue-100 text-blue-800',
                                    'approved' => 'bg-indigo-100 text-indigo-800',
                                    'processed' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                                $customAdminRefundClass = $customAdminRefundStatusMap[$latestCustomRefundRequest->status] ?? 'bg-gray-100 text-gray-800';
                                $customAdminRefundEvidence = is_array($latestCustomRefundRequest->evidence_paths ?? null) ? $latestCustomRefundRequest->evidence_paths : [];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $customAdminRefundClass }}">{{ ucfirst(str_replace('_', ' ', $latestCustomRefundRequest->status)) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Requested by {{ $latestCustomRefundRequest->user->name ?? 'Customer' }} on {{ optional($latestCustomRefundRequest->requested_at)->format('M d, Y h:i A') ?? $latestCustomRefundRequest->created_at->format('M d, Y h:i A') }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Reason:</span> {{ $latestCustomRefundRequest->reason }}</p>
                        <p class="text-sm text-gray-700 mt-1"><span class="font-semibold">Details:</span> {{ $latestCustomRefundRequest->details }}</p>

                        @if(!empty($customAdminRefundEvidence))
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Evidence</p>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $customAdminAuthToken = request('auth_token') ?? request()->attributes->get('admin_auth_token');
                                    @endphp
                                    @foreach($customAdminRefundEvidence as $evidencePath)
                                        @php
                                            $customAdminEvidenceUrl = route('admin.custom-orders.refund_evidence.view', array_filter([
                                                'refundRequest' => $latestCustomRefundRequest->id,
                                                'index' => $loop->index,
                                                'auth_token' => $customAdminAuthToken,
                                            ]));
                                            $customAdminExt = strtolower(pathinfo(parse_url($evidencePath, PHP_URL_PATH) ?? $evidencePath, PATHINFO_EXTENSION));
                                            $customAdminIsImage = in_array($customAdminExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                                            $customAdminIsVideo = in_array($customAdminExt, ['mp4', 'mov', 'webm'], true);
                                            $customAdminVideoMime = match ($customAdminExt) {
                                                'mov' => 'video/quicktime',
                                                'webm' => 'video/webm',
                                                default => 'video/mp4',
                                            };
                                        @endphp
                                        @if($customAdminIsImage)
                                            <a href="{{ $customAdminEvidenceUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 bg-white" title="Open full image">
                                                <img src="{{ $customAdminEvidenceUrl }}" alt="Refund evidence" class="w-24 h-24 object-cover">
                                            </a>
                                        @elseif($customAdminIsVideo)
                                            <div class="rounded-lg overflow-hidden border border-blue-200 bg-black">
                                                <video controls preload="metadata" class="w-40 h-24 object-cover">
                                                    <source src="{{ $customAdminEvidenceUrl }}" type="{{ $customAdminVideoMime }}">
                                                    Your browser does not support video playback.
                                                </video>
                                                <div class="px-2 py-1 bg-white border-t border-blue-100">
                                                    <a href="{{ $customAdminEvidenceUrl }}" target="_blank" class="text-xs text-blue-700 hover:underline">Open video in new tab</a>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ $customAdminEvidenceUrl }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded border border-gray-300 text-xs text-gray-700 bg-white hover:bg-gray-100 transition-colors">View PDF</a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($latestCustomRefundRequest->admin_note))
                            <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Admin Note:</span> {{ $latestCustomRefundRequest->admin_note }}</p>
                        @endif
                    </div>

                    @if(in_array($latestCustomRefundRequest->status, ['requested', 'under_review']))
                    <form action="{{ route('admin.custom-orders.refund_requests.approve', $latestCustomRefundRequest->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <textarea name="admin_note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Optional note for customer"></textarea>
                        <button type="submit" onclick="return confirm('Approve and process this request?');" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium flex items-center justify-center">Approve & Process {{ ucfirst($latestCustomRefundRequest->request_type) }}</button>
                    </form>

                    <form action="{{ route('admin.custom-orders.refund_requests.reject', $latestCustomRefundRequest->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <textarea name="admin_note" rows="2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Reason for rejection (required)"></textarea>
                        <button type="submit" onclick="return confirm('Reject this request?');" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium flex items-center justify-center">Reject {{ ucfirst($latestCustomRefundRequest->request_type) }} Request</button>
                    </form>
                    @endif
                    @endif

                    {{-- 1. Quote Final Price (Show for pending, or allow editing for price_quoted status) --}}
                    @if(in_array($order->status, ['pending', 'price_quoted']) && !$mainOrderSkipsReview)
                    @php
                        $existingBreakdown = $order->getPriceBreakdown();
                        $breakdown = $existingBreakdown['breakdown'] ?? [];
                    @endphp
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $order->final_price ? 'Update Price Breakdown' : 'Quote Price Breakdown' }}
                        </label>
                        <form id="priceForm" data-order-id="{{ $order->id }}">
                            @csrf
                            
                            {{-- Price Breakdown Fields --}}
                            <div class="space-y-2 mb-3">
                                {{-- Material Cost --}}
                                <div class="flex items-center gap-2">
                                    <label class="text-xs text-gray-600 w-28 flex-shrink-0">Material Cost</label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                        <input type="number" name="material_cost" id="material_cost" step="0.01" min="0" 
                                               value="{{ $breakdown['material_cost'] ?? '' }}"
                                               class="w-full border border-gray-300 focus:border-green-500 focus:ring-1 focus:ring-green-200 rounded-lg pl-6 pr-3 py-2 text-sm transition-all" 
                                               placeholder="0.00" onchange="calculateTotal()" oninput="calculateTotal()">
                                    </div>
                                </div>
                                
                                {{-- Pattern/Customization Fee --}}
                                <div class="flex items-center gap-2">
                                    <label class="text-xs text-gray-600 w-28 flex-shrink-0">Pattern Fee</label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                        <input type="number" name="pattern_fee" id="pattern_fee" step="0.01" min="0" 
                                               value="{{ $breakdown['pattern_fee'] ?? '' }}"
                                               class="w-full border border-gray-300 focus:border-green-500 focus:ring-1 focus:ring-green-200 rounded-lg pl-6 pr-3 py-2 text-sm transition-all" 
                                               placeholder="0.00" onchange="calculateTotal()" oninput="calculateTotal()">
                                    </div>
                                </div>
                                
                                {{-- Discount (optional) --}}
                                <div class="flex items-center gap-2">
                                    <label class="text-xs text-gray-600 w-28 flex-shrink-0">Discount</label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-red-400 text-sm">-₱</span>
                                        <input type="number" name="discount" id="discount" step="0.01" min="0" 
                                               value="{{ $breakdown['discount'] ?? '' }}"
                                               class="w-full border border-gray-300 focus:border-red-400 focus:ring-1 focus:ring-red-200 rounded-lg pl-7 pr-3 py-2 text-sm transition-all text-red-600" 
                                               placeholder="0.00" onchange="calculateTotal()" oninput="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Total (Auto-calculated) --}}
                            <div class="bg-white rounded-lg p-3 mb-3 border-2 border-green-300">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-gray-700">Total Quoted Price</span>
                                    <span class="text-2xl font-bold text-green-600" id="totalDisplay">₱{{ $order->final_price ? number_format($order->final_price, 2) : '0.00' }}</span>
                                </div>
                                <input type="hidden" name="price" id="price" value="{{ $order->final_price ?? 0 }}">
                            </div>
                            
                            <textarea name="notes" rows="2" 
                                      class="w-full border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 rounded-lg px-4 py-2.5 mb-3 transition-all text-sm" 
                                      placeholder="Add pricing notes or details (optional)">{{ $existingBreakdown['notes'] ?? $order->getAdminNotesText() }}</textarea>
                            <button type="submit" id="priceBtn" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $order->final_price ? 'Update Quote' : 'Send Quote to Customer' }}</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- 1.5 Approve Custom Order (Pending only) --}}
                    @if($order->status === 'pending' && !$mainOrderSkipsReview)
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg p-4 border border-emerald-200">
                        <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approve Custom Order
                        </label>
                        <form action="{{ route('admin.custom-orders.approve', $order) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST">
                            @csrf
                            @if(request('auth_token'))
                                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                            @endif
                            <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Approve Custom Order</span>
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    {{-- 2. Maya Pending Verification fix --}}
                    @if(in_array($order->payment_status, ['pending', 'pending_verification']) && $order->payment_method === 'maya')
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border-2 border-blue-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Maya Payment Pending</h3>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">Customer completed Maya checkout. Confirm to mark as paid.</p>
                        <form action="{{ route('admin.custom-orders.confirmPayment', $order) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST">
                            @csrf
                            @if(request('auth_token'))
                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                            @endif
                            <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Mark Maya Payment as Paid</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- 2. Payment Verification (Only shows when payment proof uploaded, and only for chat-based orders) --}}
                    @if($order->payment_receipt && $order->payment_status === 'paid' && empty($order->payment_confirmed_at) && !empty($order->chat_id))
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg p-5 border-2 border-yellow-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Payment Verification</h3>
                        </div>
                        
                        <div class="bg-white rounded-lg p-3 mb-4 border border-yellow-200">
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="w-4 h-4 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Customer has submitted payment proof</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            {{-- Confirm Payment Button --}}
                            <form action="{{ route('admin.custom-orders.confirmPayment', $order) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST">
                                @csrf
                                @if(request('auth_token'))
                                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                @endif
                                <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 transform hover:scale-[1.02]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Confirm Payment Received</span>
                                </button>
                            </form>
                            
                            {{-- Reject Payment Button --}}
                            <form action="{{ route('admin.custom-orders.rejectPayment', $order) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this payment? Customer will need to resubmit.');">
                                @csrf
                                @if(request('auth_token'))
                                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                @endif
                                <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 transform hover:scale-[1.01]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Mark Payment as Failed</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                    
                    {{-- 3. Smart Workflow Progress (Shows after quote sent OR payment confirmed for chat orders) --}}
                    @if(!empty($order->price_quoted_at) || $order->payment_status === 'paid' || !empty($order->payment_confirmed_at))
                    <div class="bg-white rounded-lg p-5 border-2 border-red-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Order Progress</h3>
                        </div>
                        
                        {{-- Progress Tracker --}}
                        @php
                            $colorMap = [
                                'maroon' => ['bg' => '#800000', 'ring' => '#E0B0B0', 'line' => '#A05050']
                            ];
                            
                            $statuses = [
                                'price_quoted'       => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',  'label' => 'Quoted',     'color' => 'maroon'],
                                'approved'           => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                                                                                                                                                                                                                                                                                       'label' => 'Paid',       'color' => 'maroon'],
                                'in_production'      => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',                                                                                                                                              'label' => 'Prod.',      'color' => 'maroon'],
                                'production_complete'=> ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                                                                                                                                                                                                                                                                                       'label' => 'Done',       'color' => 'maroon'],
                                'out_for_delivery'   => ['svg' => '<path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',                                                                                                                                                   'label' => 'Shipped',    'color' => 'maroon'],
                                'delivered'          => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',                                                                                                                                                                                                                                                                                                                                                                                                                                                               'label' => 'Delivered',  'color' => 'maroon'],
                            ];
                            // 'completed' is treated the same as 'delivered' for display
                            if (in_array($order->status, ['delivered', 'completed'])) {
                                $currentStatus = 'delivered';
                            } elseif ($order->status === 'processing' && ($order->payment_confirmed_at || $order->payment_status === 'paid')) {
                                $currentStatus = 'approved';
                            } else {
                                $currentStatus = $order->status;
                            }
                            $statusKeys    = array_keys($statuses);
                            $currentIndex  = array_search($currentStatus, $statusKeys);
                        @endphp
                        
                        {{-- Horizontally scrollable stepper so labels never get clipped --}}
                        <div class="overflow-x-auto -mx-1 px-1 pb-1">
                            <div class="flex items-center justify-between min-w-[320px] mb-5">
                            @foreach($statuses as $key => $status)
                                @php
                                    $index      = array_search($key, $statusKeys);
                                    $isComplete = $index <= $currentIndex;
                                    $isCurrent  = $key === $currentStatus;
                                @endphp
                                <div class="flex flex-col items-center" style="min-width:44px;">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center mb-1 transition-all {{ $isCurrent ? 'scale-110' : '' }}"
                                         style="{{ $isComplete ? 'background-color:'.$colorMap[$status['color']]['bg'].';color:white;box-shadow:0 2px 6px rgba(0,0,0,.18);' : 'background-color:#E5E7EB;color:#9CA3AF;' }}{{ $isCurrent ? 'box-shadow:0 0 0 3px '.$colorMap[$status['color']]['ring'].';' : '' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $status['svg'] !!}
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-medium text-center leading-tight {{ $isComplete ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ $status['label'] }}
                                    </span>
                                </div>
                                @if(!$loop->last)
                                <div class="flex-1 h-0.5 mx-0.5 rounded" style="background-color:{{ $index < $currentIndex ? $colorMap[$status['color']]['line'] : '#E5E7EB' }};min-width:8px;"></div>
                                @endif
                            @endforeach
                            </div>
                        </div>
                        
                        {{-- Next Action Button --}}
                        @php
                            // Define color map for workflow buttons
                            $colorMap = [
                                'maroon' => ['bg' => '#800000', 'ring' => '#E0B0B0', 'line' => '#A05050']
                            ];
                            
                            $nextAction = null;
                            // Show Start Production if payment is confirmed (for both approved and processing status with payment)
                            if (($order->status === 'approved' || $order->status === 'processing') && ($order->payment_confirmed_at || $order->payment_status === 'paid')) {
                                $nextAction = ['status' => 'in_production', 'label' => 'Start Production', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'maroon'];
                            } elseif ($order->status === 'in_production') {
                                $nextAction = ['status' => 'production_complete', 'label' => 'Complete Production', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'maroon'];
                            } elseif ($order->status === 'production_complete') {
                                $nextAction = ['status' => 'out_for_delivery', 'label' => 'Ship Order', 'svg' => '<path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>', 'color' => 'maroon'];
                            } elseif ($order->status === 'out_for_delivery') {
                                $nextAction = ['status' => 'delivered', 'label' => 'Mark as Delivered', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>', 'color' => 'maroon'];
                            }
                        @endphp
                        
                        @if($nextAction)
                        <form id="workflowForm" data-order-id="{{ $order->id }}" data-next-status="{{ $nextAction['status'] }}" data-batch-order-ids="{{ $batchItems->pluck('id')->implode(',') }}">
                            @csrf
                            <button type="submit" id="workflowBtn" class="workflow-btn w-full text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-3 transform hover:scale-[1.02]" style="background: linear-gradient(to right, {{ $colorMap[$nextAction['color']]['bg'] }}, {{ $colorMap[$nextAction['color']]['bg'] }}dd);">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $nextAction['svg'] !!}
                                </svg>
                                <span>{{ $nextAction['label'] }}</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        </form>
                        @elseif($order->status === 'delivered')
                        <div class="bg-gradient-to-r from-amber-50 to-yellow-100 border-2 border-amber-300 rounded-lg p-4 text-center">
                            <div class="flex justify-center mb-2">
                                <svg class="w-12 h-12 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="font-bold text-amber-800">Delivered - Waiting for Customer Confirmation</div>
                            <div class="text-sm text-amber-800">The order is delivered. Completion happens only after the customer confirms receipt.</div>
                        </div>
                        @elseif($order->status === 'completed')
                        <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-300 rounded-lg p-4 text-center">
                            <div class="flex justify-center mb-2">
                                <svg class="w-12 h-12 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="font-bold text-[#800000]">Order Completed!</div>
                            <div class="text-sm text-[#800000]">Customer has confirmed order received.</div>
                        </div>
                        @else
                        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-3 text-center text-sm text-[#800000]">
                            <strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $order->status)) }}
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    {{-- 4. Production Delay Notification --}}
                    @if(in_array($order->status, ['approved', 'in_production', 'production_complete']) || ($order->status === 'processing' && ($order->payment_confirmed_at || $order->payment_status === 'paid')))
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg p-4 border border-yellow-200">
                        <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @if($order->is_delayed)
                                Update Delay Notification
                            @else
                                Notify Customer of Delay
                            @endif
                        </label>
                        
                        @if($order->is_delayed)
                        <div class="mb-3 p-3 bg-yellow-100 border border-yellow-300 rounded-lg">
                            <p class="text-xs font-semibold text-yellow-800 mb-1">Current Delay Reason:</p>
                            <p class="text-sm text-yellow-900">{{ $order->delay_reason }}</p>
                            <p class="text-xs text-yellow-700 mt-1">Notified: {{ $order->delay_notified_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @endif
                        
                        <form id="delayForm" data-order-id="{{ $order->id }}">
                            @csrf
                            <textarea name="delay_reason" 
                                      id="delayReason"
                                      rows="3" 
                                      class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-sm mb-3" 
                                      placeholder="Enter reason for delay (e.g., Material shortage, Custom design adjustments needed, etc.)"
                                      required>{{ $order->delay_reason ?? '' }}</textarea>
                            <button type="submit" 
                                    id="delayBtn"
                                    class="w-full text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 transform hover:scale-[1.01]" style="background: linear-gradient(to right, #A05050, #C08080); &:hover { background: linear-gradient(to right, #800000, #A05050); }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>{{ $order->is_delayed ? 'Update Delay Notification' : 'Send Delay Notification' }}</span>
                            </button>
                            
                            @if($order->is_delayed)
                            <button type="button" 
                                    id="clearDelayBtn"
                                    onclick="clearDelay({{ $order->id }})"
                                    class="w-full mt-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-2.5 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 transform hover:scale-[1.01]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Clear Delay Status</span>
                            </button>
                            @endif
                        </form>
                    </div>
                    @endif
                    
                    {{-- 5. Reject Order (Last) --}}
                    @if($order->status === 'pending')
                    <div class="bg-gradient-to-r from-red-50 to-rose-50 rounded-lg p-4 border border-red-200">
                        <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Reject Order
                        </label>
                        <form id="rejectForm" data-order-id="{{ $order->id }}">
                            @csrf
                            <textarea name="rejection_reason" rows="2" 
                                      class="w-full border-2 border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 rounded-lg px-4 py-2.5 mb-3 transition-all text-sm" 
                                      placeholder="Explain why this order is being rejected (required)" required></textarea>
                            <button type="submit" id="rejectBtn" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Reject Order</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- Success/Error Messages --}}
                    <div id="actionMessage" class="hidden rounded-lg p-4 transition-all"></div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Order Timeline
                </h2>
                
                <div class="relative">
                    {{-- Timeline line --}}
                    <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gradient-to-b from-red-200 via-red-300 to-gray-200"></div>
                    
                    <div class="space-y-6 relative">
                        {{-- Order Created --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">📝 Order Created</span>
                                </div>
                                <div class="text-sm text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ $order->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        
                        @if($order->price_quoted_at)
                        {{-- Price Quoted --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">💰 Price Quoted</span>
                                    @if($order->final_price)
                                    <span class="text-xs text-white px-2 py-0.5 rounded-full" style="background-color: #800000;">₱{{ number_format($order->final_price, 2) }}</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->price_quoted_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($order->price_quoted_at)->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->approved_at)
                        {{-- Approved --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">✅ Order Approved</span>
                                </div>
                                <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->approved_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($order->approved_at)->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->rejected_at)
                        {{-- Rejected --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 bg-red-50 rounded-lg p-3 border border-red-200">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-red-900">❌ Order Rejected</span>
                                </div>
                                <div class="text-sm text-red-700">{{ \Carbon\Carbon::parse($order->rejected_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-red-600 mt-1">{{ \Carbon\Carbon::parse($order->rejected_at)->diffForHumans() }}</div>
                                @if($order->rejection_reason)
                                <div class="mt-2 p-2 bg-red-100 rounded text-xs text-red-800 border-l-4 border-red-500">
                                    <strong>Reason:</strong> {{ $order->rejection_reason }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($order->status === 'in_production' || $order->production_completed_at)
                        {{-- In Production --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 {{ $order->production_completed_at ? '' : 'animate-pulse' }} rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">🔨 {{ $order->production_completed_at ? 'Production Finished' : 'Currently in Production' }}</span>
                                    @if(!$order->production_completed_at && $order->status === 'in_production')
                                    <span class="flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full opacity-75" style="background-color: #A05050;"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2" style="background-color: #800000;"></span>
                                    </span>
                                    @endif
                                </div>
                                @if($order->production_completed_at)
                                <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->production_completed_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($order->production_completed_at)->diffForHumans() }}</div>
                                @else
                                <div class="text-xs text-gray-700">Active</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($order->out_for_delivery_at)
                        {{-- Out for Delivery --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">🚚 Out for Delivery</span>
                                </div>
                                <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->out_for_delivery_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($order->out_for_delivery_at)->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->status === 'delivered' || $order->status === 'completed')
                        {{-- Delivered --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">🎉 Successfully Delivered</span>
                                </div>
                                @if($order->delivered_at)
                                <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($order->delivered_at)->diffForHumans() }}</div>
                                @else
                                <div class="text-xs text-gray-700">Completed</div>
                                @endif
                                @if($order->status === 'delivered')
                                <div class="text-xs font-semibold mt-2" style="color: #8b3a56;">Waiting for customer confirmation.</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($order->status === 'completed' && $order->status !== 'delivered')
                        {{-- Completed --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shadow-lg" style="background-color: #800000;">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg p-3 border" style="background-color: #fff5f5; border-color: #e0b0b0;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold" style="color: #800000;">🎉 Order Completed</span>
                                </div>
                                <div class="text-xs text-gray-700">Successfully delivered</div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Last Updated --}}
                        <div class="flex items-start gap-4">
                            <div class="relative z-10">
                                <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center shadow">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-gray-700">Last Updated</span>
                                </div>
                                <div class="text-sm text-gray-600">{{ $order->updated_at->format('M d, Y h:i A') }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $order->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- PayMongo Verified Receipt Modal --}}
<div id="paymongoReceiptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Verified PayMongo Receipt</h3>
            <button type="button" onclick="closePaymongoReceiptModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div id="paymongoReceiptLoading" class="text-center py-8">
                <div class="inline-flex items-center gap-3 text-gray-600">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Fetching verified receipt from PayMongo...</span>
                </div>
            </div>

            <div id="paymongoReceiptError" class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"></div>

            <div id="paymongoReceiptBody" class="hidden">
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 text-white" style="background: linear-gradient(135deg, #800000 0%, #5a0000 100%);">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-bold">Yakan Payment Receipt</h4>
                                <p class="text-xs text-red-100">Verified directly from PayMongo API</p>
                            </div>
                            <p id="pmFetchedAt" class="text-xs text-red-100 text-right"></p>
                        </div>
                    </div>

                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Reference Number</p>
                            <p id="pmRefNumber" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Customer Name</p>
                            <p id="pmCustomerName" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Customer Email</p>
                            <p id="pmCustomerEmail" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment ID</p>
                            <p id="pmPaymentId" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p id="pmPaymentMethod" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Payment Date</p>
                            <p id="pmPaidAt" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-dashed border-gray-200 pb-3">
                            <p class="text-sm text-gray-500">Status</p>
                            <p id="pmStatus" class="text-sm font-semibold text-gray-900 text-right">-</p>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <p class="text-sm text-gray-500">Amount</p>
                            <p id="pmAmount" class="text-xl font-bold" style="color:#800000;">-</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <p class="text-xs text-gray-500">This receipt is generated from PayMongo API data and is safe for admin verification.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closePaymongoReceiptModal()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold">
                    Close
                </button>
                <button type="button" id="paymongoPrintBtn" onclick="printPaymongoReceipt()" class="hidden px-6 py-3 text-white rounded-lg transition-colors font-semibold" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#A05050'" onmouseout="this.style.backgroundColor='#800000'">
                    Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Automated AJAX Scripts --}}
<script>
function closePaymongoReceiptModal() {
    const modal = document.getElementById('paymongoReceiptModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function formatPaymongoStatus(status) {
    if (!status) return 'N/A';
    return String(status).replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
}

function safeText(value, fallback) {
    if (value === null || value === undefined) return fallback;
    const text = String(value).trim();
    return text === '' ? fallback : text;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatReceiptDate(isoText) {
    if (!isoText) return 'N/A';
    const dateObj = new Date(isoText);
    if (Number.isNaN(dateObj.getTime())) return safeText(isoText, 'N/A');
    return dateObj.toLocaleString();
}

function toggleBatchItemDetails(itemId) {
    const detailsPanel = document.getElementById(`batch-item-details-${itemId}`);
    if (!detailsPanel) {
        return;
    }

    detailsPanel.classList.toggle('hidden');
}

function printPaymongoReceipt() {
    const receipt = window.__paymongoReceiptData;
    if (!receipt) {
        return;
    }

    const printWindow = window.open('', '_blank', 'width=860,height=900');
    if (!printWindow) {
        alert('Please allow pop-ups to print the receipt.');
        return;
    }

    const amountText = `${safeText(receipt.currency, 'PHP')} ${Number(receipt.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verified PayMongo Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; color: #1f2937; }
        .receipt { border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
        .header { background: #800000; color: #fff; padding: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 6px 0 0 0; font-size: 12px; opacity: 0.92; }
        .section { padding: 16px 20px; border-top: 1px solid #f0f0f0; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px dashed #d1d5db; }
        .row:last-child { border-bottom: none; }
        .label { font-size: 12px; color: #6b7280; }
        .value { font-size: 14px; font-weight: 700; text-align: right; word-break: break-word; }
        .amount { font-size: 24px; color: #800000; font-weight: 700; }
        .footer { background: #f9fafb; font-size: 11px; color: #6b7280; padding: 14px 20px; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Yakan Payment Receipt</h1>
            <p>Verified directly from PayMongo API</p>
            <p>Fetched at ${escapeHtml(formatReceiptDate(receipt.fetched_at))}</p>
        </div>
        <div class="section">
            <div class="row"><div class="label">Reference Number</div><div class="value">${escapeHtml(safeText(receipt.reference_number, 'N/A'))}</div></div>
            <div class="row"><div class="label">Customer Name</div><div class="value">${escapeHtml(safeText(receipt.customer_name, 'N/A'))}</div></div>
            <div class="row"><div class="label">Customer Email</div><div class="value">${escapeHtml(safeText(receipt.customer_email, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment ID</div><div class="value">${escapeHtml(safeText(receipt.payment_id, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment Method</div><div class="value">${escapeHtml(safeText(receipt.payment_method, 'N/A'))}</div></div>
            <div class="row"><div class="label">Payment Date</div><div class="value">${escapeHtml(formatReceiptDate(receipt.paid_at))}</div></div>
            <div class="row"><div class="label">Status</div><div class="value">${escapeHtml(formatPaymongoStatus(receipt.status))}</div></div>
            <div class="row"><div class="label">Amount</div><div class="value amount">${escapeHtml(amountText)}</div></div>
        </div>
        <div class="footer">This document is generated from trusted gateway data and is intended for admin verification.</div>
    </div>
    <script>window.onload = function() { window.print(); }<\/script>
</body>
</html>`;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
}

async function viewPaymongoReceipt(endpointUrl) {
    const modal = document.getElementById('paymongoReceiptModal');
    const loading = document.getElementById('paymongoReceiptLoading');
    const error = document.getElementById('paymongoReceiptError');
    const body = document.getElementById('paymongoReceiptBody');
    const printBtn = document.getElementById('paymongoPrintBtn');

    if (!modal || !loading || !error || !body || !printBtn) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    body.classList.add('hidden');
    printBtn.classList.add('hidden');
    error.classList.add('hidden');
    error.textContent = '';

    try {
        const receipt = await fetchPaymongoReceiptPayload(endpointUrl);
        window.__paymongoReceiptData = receipt;
        applyPaymongoReceiptToSummary(receipt);

        document.getElementById('pmRefNumber').textContent = safeText(receipt.reference_number, 'N/A');
        document.getElementById('pmCustomerName').textContent = safeText(receipt.customer_name, 'N/A');
        document.getElementById('pmCustomerEmail').textContent = safeText(receipt.customer_email, 'N/A');
        document.getElementById('pmPaymentId').textContent = safeText(receipt.payment_id, 'N/A');
        document.getElementById('pmPaymentMethod').textContent = safeText(receipt.payment_method, 'N/A');
        document.getElementById('pmStatus').textContent = formatPaymongoStatus(receipt.status);
        document.getElementById('pmAmount').textContent = (receipt.currency || 'PHP') + ' ' + Number(receipt.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('pmPaidAt').textContent = formatReceiptDate(receipt.paid_at);
        document.getElementById('pmFetchedAt').textContent = receipt.fetched_at
            ? ('Fetched at ' + formatReceiptDate(receipt.fetched_at))
            : '';

        loading.classList.add('hidden');
        body.classList.remove('hidden');
        printBtn.classList.remove('hidden');
    } catch (fetchError) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = fetchError.message || 'Unable to load verified receipt from PayMongo.';
    }
}

async function fetchPaymongoReceiptPayload(endpointUrl) {
    const response = await fetch(endpointUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    });

    const responseText = await response.text();
    let payload = null;

    try {
        payload = responseText ? JSON.parse(responseText) : null;
    } catch (_) {
        payload = null;
    }

    if (!payload || typeof payload !== 'object') {
        const looksLikeHtml = /^\s*</.test(responseText || '');
        if (response.status === 401 || response.status === 403 || response.status === 419 || response.redirected || looksLikeHtml) {
            throw new Error('Admin session expired. Please refresh the page and login again, then retry.');
        }

        const snippet = responseText ? responseText.slice(0, 120).replace(/\s+/g, ' ').trim() : '';
        throw new Error(snippet || 'Server returned an unexpected response while loading the verified receipt.');
    }

    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Unable to load verified receipt from PayMongo.');
    }

    return payload.receipt || {};
}

function applyPaymongoReceiptToSummary(receipt) {
    const refEl = document.getElementById('coPmSummaryRef');
    const paymentIdEl = document.getElementById('coPmSummaryPaymentId');
    const paidAtEl = document.getElementById('coPmSummaryPaidAt');

    if (refEl) {
        refEl.textContent = safeText(receipt.reference_number, refEl.textContent || 'N/A');
    }
    if (paymentIdEl) {
        paymentIdEl.textContent = safeText(receipt.payment_id, paymentIdEl.textContent || 'N/A');
    }
    if (paidAtEl) {
        paidAtEl.textContent = formatReceiptDate(receipt.paid_at);
    }
}

async function syncPaymongoSummaryFromVerifiedReceipt() {
    const syncNode = document.getElementById('coPaymongoSync');
    if (!syncNode) {
        return;
    }

    const endpointUrl = syncNode.getAttribute('data-endpoint') || '';
    if (!endpointUrl) {
        return;
    }

    try {
        const receipt = await fetchPaymongoReceiptPayload(endpointUrl);
        window.__paymongoReceiptData = receipt;
        applyPaymongoReceiptToSummary(receipt);
    } catch (error) {
        // Keep server-rendered fallback values when prefetch fails.
        console.warn('Unable to prefetch verified PayMongo receipt for summary:', error?.message || error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const messageDiv = document.getElementById('actionMessage');
    const paymongoModal = document.getElementById('paymongoReceiptModal');

    if (paymongoModal) {
        paymongoModal.addEventListener('click', function(e) {
            if (e.target === paymongoModal) {
                closePaymongoReceiptModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePaymongoReceiptModal();
        }
    });

    syncPaymongoSummaryFromVerifiedReceipt();
    
    // Show message helper
    function showMessage(message, type = 'success') {
        messageDiv.className = `rounded-lg p-4 transition-all mb-4 ${
            type === 'success' 
                ? 'bg-green-100 border-2 border-green-500 text-green-800' 
                : 'bg-red-100 border-2 border-red-500 text-red-800'
        }`;
        messageDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    }
                </svg>
                <span class="font-semibold">${message}</span>
            </div>
        `;
        messageDiv.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 5000);
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Button loading state
    function setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = `
                <svg class="animate-spin h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2">Processing...</span>
            `;
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml;
        }
    }
    
    // Smart Workflow Form (replaces old status dropdown)
    const workflowForm = document.getElementById('workflowForm');
    
    if (workflowForm) {
        workflowForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const button = document.getElementById('workflowBtn');
            const orderId = this.dataset.orderId;
            const nextStatus = this.dataset.nextStatus;
            const batchOrderIds = (this.dataset.batchOrderIds || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);
            
            if (!button) {
                showMessage('❌ Button not found. Please refresh the page.', 'error');
                return;
            }
            
            if (!orderId || !nextStatus) {
                showMessage('❌ Invalid form configuration. Please refresh the page.', 'error');
                return;
            }
            
            setButtonLoading(button, true);
            
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const authToken = urlParams.get('auth_token');
                let success = true;
                let data = null;
                const batchWorkflowStatuses = ['in_production', 'production_complete', 'out_for_delivery', 'delivered'];

                if (batchWorkflowStatuses.includes(nextStatus) && batchOrderIds.length > 1) {
                    const requests = batchOrderIds.map((id) => {
                        const statusUrl = `/admin/custom-orders/${id}/update-status${authToken ? '?auth_token=' + authToken : ''}`;
                        return fetch(statusUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ status: nextStatus })
                        });
                    });

                    const responses = await Promise.all(requests);
                    const payloads = await Promise.all(responses.map(async (r) => {
                        const txt = await r.text();
                        try { return JSON.parse(txt); } catch { return { success: false, message: 'Invalid response' }; }
                    }));

                    success = responses.every((r, idx) => r.ok && payloads[idx]?.success);
                    data = payloads[0] || { success };
                } else {
                    const statusUrl = `/admin/custom-orders/${orderId}/update-status${authToken ? '?auth_token=' + authToken : ''}`;
                    const response = await fetch(statusUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ status: nextStatus })
                    });

                    const responseText = await response.text();
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        showMessage('❌ Server returned invalid response', 'error');
                        setButtonLoading(button, false);
                        return;
                    }
                    success = response.ok && data.success;
                }

                if (success) {
                    const statusLabels = {
                        'in_production': 'Production started',
                        'production_complete': 'Production completed',
                        'out_for_delivery': 'Order shipped',
                        'delivered': 'Order delivered'
                    };
                    const suffix = (batchWorkflowStatuses.includes(nextStatus) && batchOrderIds.length > 1)
                        ? ` for ${batchOrderIds.length} items`
                        : '';
                    const message = `${statusLabels[nextStatus] || 'Status updated'}${suffix} successfully! Customer has been notified.`;
                    showMessage(message, 'success');
                    setTimeout(() => { window.location.reload(); }, 250);
                } else {
                    showMessage('❌ ' + (data.message || 'Failed to update status'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Workflow update error:', error.message);
                showMessage('❌ Network error: ' + error.message, 'error');
                setButtonLoading(button, false);
            }
        });
    }
    // workflowForm is conditionally rendered only when a next workflow action exists for this order
    
    // Delay Notification Form
    const delayForm = document.getElementById('delayForm');
    if (delayForm) {
        delayForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('delayBtn');
            const orderId = this.dataset.orderId;
            const formData = new FormData(this);
            
            setButtonLoading(button, true);
            
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const authToken = urlParams.get('auth_token');
                const delayUrl = `/admin/custom-orders/${orderId}/notify-delay${authToken ? '?auth_token=' + authToken : ''}`;
                const response = await fetch(delayUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        delay_reason: formData.get('delay_reason')
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('✅ Delay notification sent to customer successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('❌ ' + (data.message || 'Failed to send delay notification'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('❌ Network error. Please try again.', 'error');
                setButtonLoading(button, false);
            }
        });
    }
    
    // Clear Delay Function
    window.clearDelay = async function(orderId) {
        if (!confirm('Are you sure you want to clear the delay status?')) {
            return;
        }
        
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const authToken = urlParams.get('auth_token');
            const clearUrl = `/admin/custom-orders/${orderId}/clear-delay${authToken ? '?auth_token=' + authToken : ''}`;
            const response = await fetch(clearUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage('✅ Delay status cleared successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showMessage('❌ ' + (data.message || 'Failed to clear delay status'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('❌ Network error. Please try again.', 'error');
        }
    };
    
    // Quote Price Form (with auto-progression)
    const priceForm = document.getElementById('priceForm');
    if (priceForm) {
        priceForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('priceBtn');
            const orderId = this.dataset.orderId;
            const formData = new FormData(this);
            
            // Validate that at least one price field has a value
            const totalPrice = parseFloat(formData.get('price')) || 0;
            if (totalPrice <= 0) {
                showMessage('❌ Please enter at least one cost component to calculate the total price.', 'error');
                return;
            }
            
            setButtonLoading(button, true);
            
            // Get auth_token from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            const authToken = urlParams.get('auth_token');
            const quoteUrl = `/admin/custom-orders/${orderId}/quote-price${authToken ? '?auth_token=' + authToken : ''}`;
            
            try {
                const response = await fetch(quoteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        price: formData.get('price'),
                        notes: formData.get('notes'),
                        material_cost: formData.get('material_cost') || 0,
                        pattern_fee: formData.get('pattern_fee') || 0,
                        discount: formData.get('discount') || 0
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('✅ Price quote sent successfully! Order status updated to "Price Quoted".', 'success');
                    
                    // Reload page to show workflow progress
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('❌ ' + (data.message || 'Failed to send price quote'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('❌ Network error. Please try again.', 'error');
                setButtonLoading(button, false);
            }
        });
    }

    // Submission-level batch actions
    const batchActionButtons = document.querySelectorAll('.batch-action-btn');
    if (batchActionButtons.length > 0) {
        batchActionButtons.forEach((btn) => {
            btn.addEventListener('click', async function() {
                const action = this.dataset.batchAction;
                const rawIds = this.dataset.orderIds || '';
                const orderIds = rawIds.split(',').map(id => id.trim()).filter(Boolean);

                if (!orderIds.length) {
                    showMessage('❌ No batch items found for this action.', 'error');
                    return;
                }

                const prompts = {
                    'approve-all': `Approve all ${orderIds.length} items in this submission?`,
                    'ready-payment': `Mark all ${orderIds.length} items as ready for payment?`
                };

                if (!confirm(prompts[action] || 'Proceed with batch action?')) {
                    return;
                }

                setButtonLoading(this, true);

                const urlParams = new URLSearchParams(window.location.search);
                const authToken = urlParams.get('auth_token');

                try {
                    const requests = orderIds.map((id) => {
                        const routePath = action === 'approve-all'
                            ? `/admin/custom-orders/${id}/approve`
                            : `/admin/custom-orders/${id}/update-status`;
                        const url = `${routePath}${authToken ? '?auth_token=' + authToken : ''}`;
                        const body = action === 'approve-all'
                            ? {}
                            : { status: 'approved' };

                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(body)
                        });
                    });

                    const responses = await Promise.all(requests);
                    const failed = responses.filter((r) => !r.ok);

                    if (failed.length > 0) {
                        showMessage(`❌ Batch action completed with ${failed.length} failed update(s).`, 'error');
                        setButtonLoading(this, false);
                        return;
                    }

                    showMessage('✅ Batch action completed successfully.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    console.error('Batch action error:', error);
                    showMessage('❌ Network error while running batch action.', 'error');
                    setButtonLoading(this, false);
                }
            });
        });
    }
    
    // Calculate Total Price from breakdown fields
    window.calculateTotal = function() {
        const materialCost = parseFloat(document.getElementById('material_cost')?.value) || 0;
        const patternFee = parseFloat(document.getElementById('pattern_fee')?.value) || 0;
        const discount = parseFloat(document.getElementById('discount')?.value) || 0;
        
        const total = materialCost + patternFee - discount;
        const finalTotal = Math.max(0, total); // Ensure non-negative
        
        // Update display
        const totalDisplay = document.getElementById('totalDisplay');
        const priceInput = document.getElementById('price');
        
        if (totalDisplay) {
            totalDisplay.textContent = '₱' + finalTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        if (priceInput) {
            priceInput.value = finalTotal.toFixed(2);
        }
    };
    
    // Initialize total calculation on page load
    if (document.getElementById('priceForm')) {
        calculateTotal();
    }
    
    // Payment Verification - Confirm Payment Button
    const confirmPaymentForm = document.getElementById('confirmPaymentForm');
    if (confirmPaymentForm) {
        confirmPaymentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = this.querySelector('button[type="submit"]');
            const orderId = this.dataset.orderId;
            
            console.log('=== Payment Verification Debug ===');
            console.log('Order ID:', orderId);
            console.log('CSRF Token:', csrfToken);
            
            const urlParams = new URLSearchParams(window.location.search);
            const authToken = urlParams.get('auth_token');
            const verifyUrl = `/admin/custom-orders/${orderId}/verify-payment${authToken ? '?auth_token=' + authToken : ''}`;
            console.log('URL:', verifyUrl);
            
            setButtonLoading(button, true);
            
            try {
                const response = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_status: 'paid'
                    })
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showMessage('❌ Server returned invalid response', 'error');
                    setButtonLoading(button, false);
                    return;
                }
                
                console.log('Parsed data:', data);
                
                if (data.success) {
                    showMessage('Payment verified! Order automatically approved and ready for production.', 'success');
                    // Force reload immediately to show updated status
                    window.location.reload();
                } else {
                    console.error('Verification failed:', data);
                    showMessage('❌ ' + (data.message || 'Failed to verify payment'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showMessage('❌ Network error: ' + error.message, 'error');
                setButtonLoading(button, false);
            }
        });
    }
    
    // Payment Verification - Reject Payment Button
    const rejectPaymentForm = document.getElementById('rejectPaymentForm');
    if (rejectPaymentForm) {
        rejectPaymentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = this.querySelector('button[type="submit"]');
            const orderId = this.dataset.orderId;
            
            // Confirm before marking as failed
            if (!confirm('Are you sure you want to mark this payment as failed? This will cancel the order.')) {
                return;
            }
            
            setButtonLoading(button, true);
            
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const authToken = urlParams.get('auth_token');
                const verifyUrl = `/admin/custom-orders/${orderId}/verify-payment${authToken ? '?auth_token=' + authToken : ''}`;
                const response = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_status: 'failed'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('⚠️ Payment marked as failed. Order has been cancelled.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('❌ ' + (data.message || 'Failed to update payment status'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('❌ Network error. Please try again.', 'error');
                setButtonLoading(button, false);
            }
        });
    }
    
    // Reject Order Form
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Confirmation dialog
            const confirmed = confirm('⚠️ Are you sure you want to reject this order? This action will notify the customer.');
            if (!confirmed) return;
            
            const button = document.getElementById('rejectBtn');
            const orderId = this.dataset.orderId;
            const formData = new FormData(this);
            
            setButtonLoading(button, true);
            
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const authToken = urlParams.get('auth_token');
                const rejectUrl = `/admin/custom-orders/${orderId}/reject${authToken ? '?auth_token=' + authToken : ''}`;
                const response = await fetch(rejectUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rejection_reason: formData.get('rejection_reason')
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('✅ Order rejected. Customer has been notified with the reason provided.', 'success');
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showMessage('❌ ' + (data.message || 'Failed to reject order'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('❌ Network error. Please try again.', 'error');
                setButtonLoading(button, false);
            }
        });
    }
    
    // Auto-dismiss Laravel flash messages
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
});
</script>
@endsection

