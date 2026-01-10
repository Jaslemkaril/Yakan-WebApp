@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('custom_orders.index') }}" 
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
                    <h1 class="text-2xl font-bold text-gray-900">Order Details</h1>
                    <p class="text-gray-600">Custom Order #{{ $order->id }}</p>
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
                                    'description' => 'Payment accepted, order in production'
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
                        $deliveryLabel = 'Order in Production';
                        $deliveryDescription = 'Your custom order is currently being processed.';
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Product Information</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start">
                            <div class="w-20 h-20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 overflow-hidden shadow-md border-2" style="background-color:#fff5f5; border-color:#800000;">
                                @php
                                    // Load pattern model if available
                                    $patternModel = null;
                                    if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id'])) {
                                        $patternModel = \App\Models\YakanPattern::find($order->design_metadata['pattern_id']);
                                    } elseif (!empty($order->patterns) && is_array($order->patterns)) {
                                        if (is_numeric($order->patterns[0])) {
                                            $patternModel = \App\Models\YakanPattern::find($order->patterns[0]);
                                        } else {
                                            $patternModel = \App\Models\YakanPattern::where('name', $order->patterns[0])->first();
                                        }
                                    }

                                    // Fallback to design_upload image
                                    $designUrl = null;
                                    if (!$patternModel && $order->design_upload) {
                                        if (str_starts_with($order->design_upload, 'data:image')) {
                                            $designUrl = $order->design_upload;
                                        } elseif (str_starts_with($order->design_upload, 'custom_orders/') || str_starts_with($order->design_upload, 'custom_designs/')) {
                                            $designUrl = asset('storage/' . $order->design_upload);
                                        } else {
                                            $designUrl = asset('storage/' . ltrim($order->design_upload, '/'));
                                        }
                                    }
                                @endphp

                                @if($patternModel && $patternModel->hasSvg())
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
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div style="{{ $filterStyle }} transform-origin: center;">
                                            {!! $patternModel->getSvgContent() !!}
                                        </div>
                                    </div>
                                @elseif($designUrl)
                                    <img src="{{ $designUrl }}" alt="Custom design preview" class="w-full h-full object-cover">
                                @elseif(isset($order->product) && $order->product->image)
                                    <img src="{{ asset('storage/' . $order->product->image) }}" alt="{{ $order->product->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-10 h-10" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-500 mb-1">Product / Design</p>
                                <p class="text-lg font-bold text-gray-900">{{ $order->product->name ?? 'Custom Fabric Order' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 shadow-sm" style="background-color:#fff5f5;">
                                    <svg class="w-6 h-6" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-500 mb-1">Quantity</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $order->quantity }} units</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specifications Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Specifications</h2>
                    </div>
                    <div class="p-6">
                        @if($order->specifications)
                            <div class="rounded-xl p-5 border-2" style="background-color:#fff5f5; border-color:#f0d0d0;">
                                <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $order->specifications }}</p>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color:#fff5f5;">
                                    <svg class="w-8 h-8" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">No specifications provided</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Patterns Card -->
                @if($order->patterns && is_array($order->patterns) && count($order->patterns) > 0)
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

                <!-- Quantity Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="px-6 py-4" style="background-color:#800000;">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </div>
                            Quantity Details
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="rounded-xl p-4 border-2" style="background-color:#fff5f5; border-color:#f0d0d0;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color:#800000;">
                                        <span class="text-xl font-bold text-white">{{ $order->quantity }}</span>
                                    </div>
                                    <div>
                                        <p class="text-lg font-semibold text-gray-800">Units Ordered</p>
                                        <p class="text-sm text-gray-500">Total quantity for this product</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($order->final_price)
                                        <div class="text-sm text-gray-600">Unit Price:</div>
                                        <div class="text-lg font-bold" style="color:#800000;">₱{{ number_format($order->final_price / $order->quantity, 2) }}</div>
                                        <div class="text-sm text-gray-500 mt-1">Total: <span class="font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</span></div>
                                    @else
                                        <div class="text-xs text-gray-500">Price upon quote</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Design Upload Card -->
                @if($order->design_upload)
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
                            if (str_starts_with($order->design_upload, 'data:image')) {
                                $designUrl = $order->design_upload;
                            } elseif (str_starts_with($order->design_upload, 'custom_orders/') || str_starts_with($order->design_upload, 'custom_designs/')) {
                                $designUrl = asset('storage/' . $order->design_upload);
                            } else {
                                $designUrl = asset('storage/' . ltrim($order->design_upload, '/'));
                            }
                        @endphp
                        <img src="{{ $designUrl }}" 
                             alt="Design Upload" 
                             class="w-full rounded-xl shadow-lg border-2 border-gray-200">
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Order Summary Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden sticky top-6 border border-gray-100">
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
                            <span class="text-sm font-bold text-gray-900">#{{ $order->id }}</span>
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
                                        
                                        {{-- Pricing Breakdown --}}
                                        @php
                                            // Get patterns
                                            $patternIds = $order->patterns;
                                            if (is_string($patternIds)) {
                                                $patternIds = json_decode($patternIds, true) ?? [];
                                            }
                                            
                                            $patterns = \App\Models\YakanPattern::whereIn('id', (array)$patternIds)->get();
                                            
                                            // Use each pattern's individual price
                                            $totalPatternFee = 0;
                                            $pricePerMeter = 0;
                                            foreach ($patterns as $pattern) {
                                                $totalPatternFee += ($pattern->pattern_price ?? 0);
                                                $pricePerMeter = $pattern->price_per_meter ?? 0; // Use first pattern's rate
                                            }
                                            
                                            $fabricCost = ($order->fabric_quantity_meters ?? 0) * $pricePerMeter;
                                            
                                            // Calculate shipping based on delivery address (zone-based from Zamboanga City)
                                            $shippingFee = 100; // default
                                            $address = strtolower($order->delivery_address ?? '');
                                            $city = '';
                                            $province = '';
                                            
                                            // If no delivery address, try user's default address
                                            if (!$order->delivery_address && $order->user) {
                                                $userDefaultAddr = $order->user->addresses()->where('is_default', true)->first();
                                                if ($userDefaultAddr) {
                                                    $address = strtolower($userDefaultAddr->formatted_address ?? $userDefaultAddr->city . ', ' . $userDefaultAddr->province);
                                                    $city = strtolower($userDefaultAddr->city ?? '');
                                                    $province = strtolower($userDefaultAddr->province ?? '');
                                                }
                                            }
                                            
                                            if ($address || $city) {
                                                // ₱0 - Zamboanga City proper
                                                if (str_contains($address, 'zamboanga') || str_contains($city, 'zamboanga')) {
                                                    $shippingFee = 0;
                                                }
                                                // ₱100 - Zamboanga Peninsula & nearby
                                                elseif (str_contains($province, 'zamboanga') || 
                                                        in_array($city, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                                                    $shippingFee = 100;
                                                }
                                                // ₱120 - Western Mindanao
                                                elseif (in_array($city, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) ||
                                                        str_contains($province, 'barmm') || str_contains($province, 'armm')) {
                                                    $shippingFee = 120;
                                                }
                                                // ₱150 - Other Mindanao regions
                                                elseif (str_contains($province, 'mindanao') ||
                                                        in_array($city, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                                                    $shippingFee = 150;
                                                }
                                                // ₱180 - Visayas
                                                elseif (str_contains($province, 'visayas') ||
                                                        in_array($city, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                                                    $shippingFee = 180;
                                                }
                                                // ₱220 - Metro Manila & nearby
                                                elseif (str_contains($city, 'manila') || str_contains($province, 'ncr') ||
                                                        in_array($city, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                                                    $shippingFee = 220;
                                                }
                                                // ₱250 - Northern Luzon
                                                elseif (str_contains($province, 'luzon') ||
                                                        in_array($city, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                                                    $shippingFee = 250;
                                                }
                                                // ₱280 - Remote islands & far areas
                                                else {
                                                    $shippingFee = 280;
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="mt-3 bg-gray-50 rounded-lg p-3 border border-gray-200 text-xs">
                                            <div class="space-y-1 text-gray-700">
                                                <div class="flex justify-between">
                                                    <span>Pattern Fee{{ $order->quantity > 1 ? ' (× ' . $order->quantity . ')' : '' }}:</span>
                                                    <span class="font-semibold">₱{{ number_format($totalPatternFee * $order->quantity, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Fabric ({{ $order->fabric_quantity_meters }}m × ₱{{ number_format($pricePerMeter, 2) }}){{ $order->quantity > 1 ? ' × ' . $order->quantity : '' }}:</span>
                                                    <span class="font-semibold">₱{{ number_format($fabricCost * $order->quantity, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Shipping:</span>
                                                    <span class="font-semibold">{{ $shippingFee == 0 ? 'FREE' : '₱' . number_format($shippingFee, 2) }}</span>
                                                </div>
                                                <div class="border-t border-gray-300 pt-1 mt-1 flex justify-between font-bold text-gray-900">
                                                    <span>Total:</span>
                                                    <span style="color:#800000;">₱{{ number_format(($totalPatternFee * $order->quantity) + ($fabricCost * $order->quantity) + $shippingFee, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($order->status === 'approved' && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Agreed Price</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                                        <p class="text-xs mt-1 font-semibold text-emerald-600">✓ Quote accepted</p>
                                        
                                        {{-- Pricing Breakdown for Approved Orders --}}
                                        @php
                                            // Get patterns
                                            $approvedPatternIds = $order->patterns;
                                            if (is_string($approvedPatternIds)) {
                                                $approvedPatternIds = json_decode($approvedPatternIds, true) ?? [];
                                            }
                                            
                                            $approvedPatterns = \App\Models\YakanPattern::whereIn('id', (array)$approvedPatternIds)->get();
                                            
                                            // Use each pattern's individual price
                                            $approvedTotalPatternFee = 0;
                                            $approvedPricePerMeter = 0;
                                            foreach ($approvedPatterns as $pattern) {
                                                $approvedTotalPatternFee += ($pattern->pattern_price ?? 0);
                                                $approvedPricePerMeter = $pattern->price_per_meter ?? 0; // Use first pattern's rate
                                            }
                                            
                                            $approvedFabricCost = ($order->fabric_quantity_meters ?? 0) * $approvedPricePerMeter;
                                            
                                            // Calculate shipping for approved order
                                            $approvedShippingFee = 100; // default
                                            $approvedAddress = strtolower($order->delivery_address ?? '');
                                            $approvedCity = '';
                                            $approvedProvince = '';
                                            
                                            // If no delivery address, try user's default address
                                            if (!$order->delivery_address && $order->user) {
                                                $userDefaultAddr = $order->user->addresses()->where('is_default', true)->first();
                                                if ($userDefaultAddr) {
                                                    $approvedAddress = strtolower($userDefaultAddr->formatted_address ?? $userDefaultAddr->city . ', ' . $userDefaultAddr->province);
                                                    $approvedCity = strtolower($userDefaultAddr->city ?? '');
                                                    $approvedProvince = strtolower($userDefaultAddr->province ?? '');
                                                }
                                            }
                                            
                                            if ($approvedAddress || $approvedCity) {
                                                if (str_contains($approvedAddress, 'zamboanga') || str_contains($approvedCity, 'zamboanga')) {
                                                    $approvedShippingFee = 0;
                                                } elseif (str_contains($approvedProvince, 'zamboanga') || in_array($approvedCity, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                                                    $approvedShippingFee = 100;
                                                } elseif (in_array($approvedCity, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) || str_contains($approvedProvince, 'barmm') || str_contains($approvedProvince, 'armm')) {
                                                    $approvedShippingFee = 120;
                                                } elseif (str_contains($approvedProvince, 'mindanao') || in_array($approvedCity, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                                                    $approvedShippingFee = 150;
                                                } elseif (str_contains($approvedProvince, 'visayas') || in_array($approvedCity, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                                                    $approvedShippingFee = 180;
                                                } elseif (str_contains($approvedCity, 'manila') || str_contains($approvedProvince, 'ncr') || in_array($approvedCity, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                                                    $approvedShippingFee = 220;
                                                } elseif (str_contains($approvedProvince, 'luzon') || in_array($approvedCity, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                                                    $approvedShippingFee = 250;
                                                } else {
                                                    $approvedShippingFee = 280;
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="mt-3 bg-gray-50 rounded-lg p-3 border border-gray-200 text-xs">
                                            <div class="space-y-1 text-gray-700">
                                                <div class="flex justify-between">
                                                    <span>Pattern Fee{{ $order->quantity > 1 ? ' (× ' . $order->quantity . ')' : '' }}:</span>
                                                    <span class="font-semibold">₱{{ number_format($approvedTotalPatternFee * $order->quantity, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Fabric ({{ $order->fabric_quantity_meters }}m × ₱{{ number_format($approvedPricePerMeter, 2) }}){{ $order->quantity > 1 ? ' × ' . $order->quantity : '' }}:</span>
                                                    <span class="font-semibold">₱{{ number_format($approvedFabricCost * $order->quantity, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Shipping:</span>
                                                    <span class="font-semibold">{{ $approvedShippingFee == 0 ? 'FREE' : '₱' . number_format($approvedShippingFee, 2) }}</span>
                                                </div>
                                                <div class="border-t border-gray-300 pt-1 mt-1 flex justify-between font-bold text-gray-900">
                                                    <span>Subtotal:</span>
                                                    <span>₱{{ number_format(($approvedTotalPatternFee * $order->quantity) + ($approvedFabricCost * $order->quantity) + $approvedShippingFee, 2) }}</span>
                                                </div>
                                                <div class="border-t-2 border-gray-400 pt-1 mt-1 flex justify-between font-bold" style="color:#800000;">
                                                    <span>Final Quoted Price:</span>
                                                    <span>₱{{ number_format($order->final_price, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Admin Notes --}}
                                        @if($order->admin_notes || $order->approved_at)
                                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 7a1 1 0 000 2h6a1 1 0 000-2H8zm0 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-blue-900 mb-1">Admin Notes on Quote</p>
                                                    @if($order->admin_notes)
                                                        <p class="text-blue-800 mb-2 italic">{{ $order->admin_notes }}</p>
                                                    @endif
                                                    @if($order->approved_at)
                                                        <p class="text-xs text-blue-700">Quote provided on {{ $order->approved_at->format('M d, Y \a\t h:i A') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @elseif($order->status === 'in_production' && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Final Price</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                                        <p class="text-xs text-emerald-600 mt-1 font-semibold">Payment accepted</p>
                                    </div>
                                @elseif(in_array($order->status, ['production_complete', 'out_for_delivery', 'delivered']) && $order->final_price)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Total Paid</p>
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
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
                                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                                        <p class="text-xs text-emerald-600 mt-1 font-semibold">Order completed</p>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-500">Price Not Set</p>
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
                                // Map old 'completed' status to new 'delivered' for timeline display
                                $displayStatus = $order->status === 'completed' ? 'delivered' : $order->status;
                                $statuses = ['pending', 'approved', 'in_production', 'production_complete', 'out_for_delivery', 'delivered'];
                                $currentIndex = array_search($displayStatus, $statuses);
                                if ($currentIndex === false) $currentIndex = -1;
                            @endphp

                            @foreach(['pending' => 'Order Placed', 'price_quoted' => 'Price Quoted', 'approved' => 'Quote Accepted', 'in_production' => 'In Production', 'production_complete' => 'Production Complete', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered'] as $status => $label)
                                @php
                                    $timelineStatuses = ['pending', 'price_quoted', 'approved', 'in_production', 'production_complete', 'out_for_delivery', 'delivered'];
                                    $statusIndex = array_search($status, $timelineStatuses);
                                    $currentTimelineIndex = array_search($displayStatus, $timelineStatuses);
                                    if ($currentTimelineIndex === false) $currentTimelineIndex = -1;
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
        <div class="mt-8">
            
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
                        <div class="text-center mb-4">
                            <p class="text-sm font-medium text-gray-600 mb-2">Quoted Amount</p>
                            <p class="text-5xl font-extrabold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                        </div>
                        
                        @if($order->admin_notes)
                            <div class="mt-4 rounded-lg p-4 border-2" style="background-color:#fff5f5; border-color:#e0b0b0;">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold mb-1" style="color:#800000;">Requirements from Admin:</p>
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $order->admin_notes }}</p>
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
            @elseif($order->status === 'approved' && !in_array($order->payment_status, ['paid', 'pending', 'pending_verification']))
                <div class="w-full rounded-2xl p-8 shadow-2xl border-2" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border-color:#800000;">
                    <div class="w-24 h-24 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg animate-pulse" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold mb-3" style="color:#800000;">🎉 Quote Accepted!</h3>
                    <p class="text-gray-700 mb-6 max-w-2xl mx-auto text-lg">Congratulations! You've accepted the quote. Complete your payment now to start production of your custom Yakan masterpiece!</p>
                    
                    @if($order->final_price)
                        <div class="bg-white rounded-2xl p-6 border-2 mb-6 max-w-md mx-auto shadow-lg" style="border-color:#e0b0b0;">
                            <p class="text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide">Total Amount to Pay</p>
                            <p class="text-5xl font-black mb-3" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                            <p class="text-xs text-gray-500">Order #{{ $order->id }}</p>
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
                                    <p class="text-sm text-gray-600">Choose your preferred payment method (GCash or Bank Transfer)</p>
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
                        <a href="{{ route('custom_orders.payment', $order) }}" class="inline-flex items-center justify-center text-white font-bold py-5 px-12 rounded-xl transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 hover:scale-105 text-lg" style="background: linear-gradient(135deg, #800000 0%, #600000 100%);">
                            <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Proceed to Payment
                        </a>
                        <p class="text-sm text-gray-500 mt-2">Secure payment • Your order will start production once verified</p>
                    </div>
                </div>
            
            {{-- Approved Status - Payment Pending Verification --}}
            @elseif($order->status === 'approved' && in_array($order->payment_status, ['pending', 'pending_verification']))
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
                            <p class="text-xs text-gray-500">Order #{{ $order->id }}</p>
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
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
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
                        <p class="text-3xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
                    </div>
                    @endif
                    <div>
                        <a href="{{ route('custom_orders.payment', $order) }}" class="inline-flex items-center justify-center text-white font-bold py-4 px-10 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
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
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
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
                        <p class="text-2xl font-bold" style="color:#800000;">₱{{ number_format($order->final_price, 2) }}</p>
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
                <a href="{{ route('custom_orders.index') }}" 
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