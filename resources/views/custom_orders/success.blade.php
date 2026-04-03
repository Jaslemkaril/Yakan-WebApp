@extends('layouts.app')

@section('title', 'Order Submitted Successfully - Custom Order')

@section('content')
@php
    $batchOrders = $batchOrders ?? collect([$order]);
    $isBatch     = $batchOrders->count() > 1;
    $batchNumber = $order->batch_order_number ?? null;
@endphp
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-rose-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4" style="background-color:#f5e6e8;">
                    <svg class="w-10 h-10" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    @if($isBatch)
                        {{ $batchOrders->count() }} Custom Orders Submitted!
                    @else
                        Order Submitted Successfully!
                    @endif
                </h1>
                <p class="text-xl text-gray-600">
                    @if($isBatch)
                        All {{ $batchOrders->count() }} items have been received and are now pending admin review.
                    @else
                        Your custom order has been received and is now pending admin review.
                    @endif
                </p>
                @if($isBatch && $batchNumber)
                <div class="mt-4 inline-flex items-center px-5 py-2 rounded-full text-white font-bold text-sm shadow" style="background-color:#800000;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    Batch Order: {{ $batchNumber }}
                </div>
                @endif
            </div>

            @if($isBatch)
            {{-- ====== BATCH: show all order items as a summary list ====== --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 p-8 mb-8" style="border-color:#e0b0b0;">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background-color:#f5e6e8;">
                        <svg class="w-5 h-5" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">
                        All Items in This Order
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full text-white" style="background-color:#800000;">{{ $batchOrders->count() }}</span>
                    </h3>
                </div>

                <div class="space-y-4">
                    @foreach($batchOrders as $idx => $bOrder)
                    @php
                        $bPatterns = collect();
                        if (!empty($bOrder->design_metadata['pattern_id'])) {
                            $bp = \App\Models\YakanPattern::find($bOrder->design_metadata['pattern_id']);
                            if ($bp) $bPatterns->push($bp);
                        }
                        if ($bPatterns->isEmpty() && !empty($bOrder->patterns) && is_array($bOrder->patterns)) {
                            $bPatterns = is_numeric($bOrder->patterns[0] ?? null)
                                ? \App\Models\YakanPattern::whereIn('id', $bOrder->patterns)->get()
                                : \App\Models\YakanPattern::whereIn('name', $bOrder->patterns)->get();
                        }
                    @endphp
                    <div class="rounded-xl border-2 p-5" style="border-color:#e0b0b0; background-color:#fff5f5;">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full text-white text-sm font-bold flex items-center justify-center mr-3 flex-shrink-0" style="background-color:#800000;">{{ $idx + 1 }}</div>
                                <div>
                                    <p class="font-bold text-gray-900">Order {{ $bOrder->batch_order_number ?? '#'.$bOrder->id }}</p>
                                    <p class="text-xs text-gray-500">{{ $bOrder->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                {{ $bOrder->getStatusDescription() }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                            @if($bPatterns->count() > 0)
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Pattern</p>
                                <p class="font-semibold text-gray-900">{{ $bPatterns->pluck('name')->implode(', ') }}</p>
                                @php $firstPattern = $bPatterns->first(); @endphp
                                @if($firstPattern && $firstPattern->hasSvg())
                                    @php
                                        $custom = $bOrder->customization_settings ?? [];
                                        $style = sprintf(
                                            'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                            $custom['hue'] ?? 0,
                                            $custom['saturation'] ?? 100,
                                            $custom['brightness'] ?? 100,
                                            $custom['opacity'] ?? 1,
                                            $custom['scale'] ?? 1,
                                            $custom['rotation'] ?? 0
                                        );
                                    @endphp
                                    <div class="mt-2 w-20 h-20 rounded-md border bg-white overflow-hidden p-1" style="border-color:#e0b0b0;">
                                        <div style="{{ $style }} transform-origin:center; width:100%; height:100%;">
                                            {!! $firstPattern->getSvgContent() !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endif
                            @if($bOrder->isFabricOrder())
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Fabric Type</p>
                                <p class="font-semibold text-gray-900">{{ $bOrder->fabric_type_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Quantity</p>
                                <p class="font-semibold text-gray-900">{{ $bOrder->formatted_fabric_quantity }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Items (qty)</p>
                                <p class="font-semibold text-gray-900">{{ $bOrder->quantity ?? 1 }}</p>
                            </div>
                            @if($bOrder->estimated_price)
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Est. Price</p>
                                <p class="font-semibold" style="color:#800000;">?{{ number_format($bOrder->estimated_price, 2) }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Delivery</p>
                                <p class="font-semibold text-gray-900">{{ ($bOrder->delivery_type ?? 'delivery') === 'pickup' ? 'Store Pickup' : 'Delivery' }}</p>
                            </div>
                        </div>
                        @if($bOrder->delivery_address)
                        <div class="mt-2 pt-2 border-t" style="border-color:#e0b0b0;">
                            <p class="text-xs text-gray-500">Address: <span class="text-gray-700">{{ $bOrder->delivery_address }}</span></p>
                        </div>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('custom_orders.show', $bOrder->id) }}" class="text-xs font-semibold hover:underline" style="color:#800000;">
                                View Order Details ?
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php $totalEstimated = $batchOrders->sum('estimated_price'); @endphp
                @if($totalEstimated > 0)
                <div class="mt-5 pt-4 border-t flex justify-between items-center" style="border-color:#e0b0b0;">
                    <span class="text-sm font-semibold text-gray-700">Combined Estimated Total:</span>
                    <span class="text-xl font-bold" style="color:#800000;">?{{ number_format($totalEstimated, 2) }}</span>
                </div>
                @endif
            </div>

            @else
            {{-- ====== SINGLE ORDER: original display ====== --}}
            <!-- Order Details Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 mb-8">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900">Order Details</h3>
                </div>

                @php
                    $contactEmail = $order->email ?? optional($order->user)->email;
                    $contactPhone = $order->phone;

                    // Load patterns - check multiple sources
                    $selectedPatterns = collect();
                    
                    // 1. Check design_metadata for pattern_id
                    if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id']) && $order->design_metadata['pattern_id']) {
                        $pattern = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                        if ($pattern) {
                            $selectedPatterns->push($pattern);
                        }
                    }
                    
                    // 2. Check patterns array - could be IDs (integers) or names (strings)
                    if ($selectedPatterns->isEmpty() && !empty($order->patterns) && is_array($order->patterns)) {
                        // Check if first element is numeric (ID) or string (name)
                        if (!empty($order->patterns) && is_numeric($order->patterns[0])) {
                            // Pattern IDs
                            $selectedPatterns = \App\Models\YakanPattern::whereIn('id', $order->patterns)->get();
                        } else {
                            // Pattern names
                            $selectedPatterns = \App\Models\YakanPattern::whereIn('name', $order->patterns)->get();
                        }
                    }
                @endphp

                @if($selectedPatterns->count() > 0)
                    <div class="mb-6">
                        <div class="rounded-xl p-4 border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Your Customized Pattern
                            </h4>
                            <div class="bg-white rounded-lg p-3 shadow-inner max-w-xl mx-auto">
                                @foreach($selectedPatterns as $pattern)
                                <div class="mb-4 last:mb-0">
                                    <div class="text-xs font-semibold text-gray-600 mb-2">{{ $pattern->name }}</div>
                                    @if($pattern->hasSvg())
                                    @php
                                        $customization = $order->customization_settings ?? [];
                                        $scale = $customization['scale'] ?? 1;
                                        $rotation = $customization['rotation'] ?? 0;
                                        $opacity = $customization['opacity'] ?? 1;
                                        $hue = $customization['hue'] ?? 0;
                                        $saturation = $customization['saturation'] ?? 100;
                                        $brightness = $customization['brightness'] ?? 100;
                                        
                                        // Build CSS filter string for color customization
                                        $filterStyle = sprintf(
                                            'filter: hue-rotate(%ddeg) saturate(%d%%) brightness(%d%%); opacity: %s; transform: scale(%s) rotate(%ddeg);',
                                            $hue,
                                            $saturation,
                                            $brightness,
                                            $opacity,
                                            $scale,
                                            $rotation
                                        );
                                    @endphp
                                    <div class="w-full rounded-lg border-2 shadow-md overflow-hidden" style="border-color:#e0b0b0; max-height: 350px;">
                                        <div class="w-full h-64 flex items-center justify-center bg-gray-50 p-4">
                                            <div style="{{ $filterStyle }} transform-origin: center; transition: all 0.3s ease;">
                                                {!! $pattern->getSvgContent() !!}
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="w-full h-64 rounded-lg border-2 shadow-md flex items-center justify-center bg-gray-100" style="border-color:#e0b0b0;">
                                        <span class="text-gray-400">No preview available</span>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                    @endphp

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Delivery Option</p>
                        <p class="text-base font-semibold text-gray-900">{{ $deliveryType === 'pickup' ? 'Store Pickup' : 'Delivery' }}</p>
                    </div>

                    @if($order->delivery_address)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500 mb-1">Delivery Address</p>
                            <p class="text-base text-gray-900 whitespace-pre-line">{{ $order->delivery_address }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Order Number</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->display_ref }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Status</p>
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800">
                            {{ $order->getStatusDescription() }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Submitted Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->created_at->format('M d, Y - g:i A') }}</p>
                    </div>

                    @if($order->isFabricOrder())
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Fabric Type</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $order->fabric_type_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Fabric Quantity</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $order->formatted_fabric_quantity }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Intended Use</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $order->intended_use_label }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Quantity (Items)</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->quantity ?? 1 }}</p>
                    </div>

                    @if($contactPhone)
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Contact Phone</p>
                            <p class="text-base font-semibold text-gray-900">{{ $contactPhone }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif {{-- end single/batch conditional --}}

            <!-- Next Steps -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">What Happens Next?</h3>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-blue-600 font-semibold text-sm">1</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Admin Review</h4>
                            <p class="text-gray-600">Our admin team will review your custom order details and requirements.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-blue-600 font-semibold text-sm">2</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Price Confirmation</h4>
                            <p class="text-gray-600">You'll receive a price quote based on your design complexity and requirements.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-blue-600 font-semibold text-sm">3</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Production Begins</h4>
                            <p class="text-gray-600">Once you approve the price, our master craftsmen will begin creating your custom order.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('custom_orders.index') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-gray-600 text-white rounded-xl font-bold hover:bg-gray-700 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    View My Orders
                </a>
                
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105"
                   style="background-color:#800000;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
