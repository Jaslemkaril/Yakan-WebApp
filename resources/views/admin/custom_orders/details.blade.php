@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.custom_orders.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Orders
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }} - Details</h1>
            <p class="text-gray-600 mt-1">Created {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Status Badge --}}
            @php
                $displayStatusLabel = $order->status === 'completed' ? 'Delivered' : ucfirst(str_replace('_', ' ', $order->status));
            @endphp
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                {{ $order->status === 'delivered' || $order->status === 'completed' ? 'bg-green-100 text-green-700' : 
                   ($order->status === 'out_for_delivery' ? 'bg-blue-100 text-blue-700' : 
                   ($order->status === 'production_complete' ? 'bg-purple-100 text-purple-700' : 
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
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content - Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Pattern Preview Section --}}
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
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Pattern Preview
                    @if($order->design_method === 'pattern')
                        <span class="text-sm font-normal text-purple-600">(Customized Pattern: {{ $patternModel->name }})</span>
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
                
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border-2 border-purple-200">
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
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Customization Settings
                            </h3>
                            @foreach($order->design_metadata['customization_settings'] as $key => $value)
                                <div class="bg-white rounded-lg p-3 border-2 border-purple-200 hover:border-purple-400 transition-colors">
                                    <div class="text-xs text-gray-500 uppercase font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                    <div class="text-sm font-bold text-purple-900">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            @elseif($order->design_upload)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Pattern Preview
                    @if($order->design_method === 'pattern')
                        <span class="text-sm font-normal text-purple-600">(Customized Pattern)</span>
                    @endif
                </h2>
                
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border-2 border-purple-200">
                    @if(str_starts_with($order->design_upload, 'data:image'))
                        <img src="{{ $order->design_upload }}" alt="Pattern Preview" 
                             class="w-full max-h-96 object-contain rounded-lg shadow-lg">
                    @else
                        <img src="{{ asset('storage/' . $order->design_upload) }}" alt="Pattern Preview" 
                             class="w-full max-h-96 object-contain rounded-lg shadow-lg">
                    @endif
                </div>
                
                {{-- Customization Settings --}}
                @if($order->design_metadata && is_array($order->design_metadata))
                    @if(isset($order->design_metadata['customization_settings']))
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                            <h3 class="col-span-full text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Customization Settings
                            </h3>
                            @foreach($order->design_metadata['customization_settings'] as $key => $value)
                                <div class="bg-white rounded-lg p-3 border-2 border-purple-200 hover:border-purple-400 transition-colors">
                                    <div class="text-xs text-gray-500 uppercase font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                    <div class="text-sm font-bold text-purple-900">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            @endif

            {{-- Order Details --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Fabric Type --}}
                    @if($order->fabric_type)
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <div class="text-sm text-purple-600 font-semibold mb-1">Fabric Type</div>
                        <div class="text-lg font-bold text-gray-900">{{ $order->fabric_type_name }}</div>
                    </div>
                    @endif
                    
                    {{-- Quantity --}}
                    @if($order->fabric_quantity_meters)
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="text-sm text-blue-600 font-semibold mb-1">Quantity</div>
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
                
                {{-- Specifications --}}
                @if($order->specifications)
                <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="text-sm text-gray-600 font-semibold mb-2">Specifications:</div>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $order->specifications }}</p>
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

            {{-- Pricing Information --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Pricing</h2>
                
                <div class="space-y-3">
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
                                <span class="text-green-600">₱{{ number_format(($totalPatternFee * $order->quantity) + ($fabricCost * $order->quantity) + $shippingFee, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->payment_method)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $order->payment_method === 'online_banking' ? 'GCash' :
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
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
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
                        {{ $order->payment_method === 'online_banking' ? 'GCash' :
                           ($order->payment_method === 'gcash' ? 'GCash' :
                           ($order->payment_method === 'bank_transfer' ? 'Bank Transfer' : ucfirst(str_replace('_', ' ', $order->payment_method)))) }}
                    </div>
                </div>
                @endif

                {{-- Transaction ID --}}
                @if($order->transaction_id)
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Transaction ID</div>
                    <div class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $order->transaction_id }}</div>
                </div>
                @endif

                {{-- Payment Receipt --}}
                @if($order->payment_receipt)
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Payment Receipt</div>
                    @php
                        // All new receipts use public disk (storage/)
                        $receiptUrl = asset('storage/' . $order->payment_receipt);
                    @endphp
                    <a href="{{ $receiptUrl }}" target="_blank" 
                       class="block bg-gray-50 border border-gray-200 rounded-lg p-2 hover:bg-gray-100 transition">
                        <img src="{{ $receiptUrl }}" alt="Payment Receipt" 
                             class="w-full h-32 object-contain rounded">
                        <div class="text-xs text-center text-blue-600 mt-1">Click to view full size</div>
                    </a>
                </div>
                @endif

                {{-- Amount Paid --}}
                @if($order->final_price && $order->payment_status !== 'unpaid')
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Amount {{ $order->payment_status === 'paid' ? 'Paid' : 'Submitted' }}</span>
                        <span class="text-lg font-bold text-green-600">₱{{ number_format($order->final_price, 2) }}</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Admin Actions --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </h2>
                
                <div class="space-y-4" id="adminActions">
                    {{-- 1. Quote Final Price (First) --}}
                    @if($order->status === 'pending' || !$order->final_price)
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Quote Final Price
                        </label>
                        <form id="priceForm" data-order-id="{{ $order->id }}">
                            @csrf
                            <div class="relative mb-2">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₱</span>
                                <input type="number" name="price" step="0.01" min="0" 
                                       value="{{ $order->final_price ?? $order->estimated_price }}"
                                       class="w-full border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 rounded-lg pl-8 pr-4 py-2.5 transition-all font-medium" 
                                       placeholder="0.00" required>
                            </div>
                            <textarea name="notes" rows="2" 
                                      class="w-full border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 rounded-lg px-4 py-2.5 mb-3 transition-all text-sm" 
                                      placeholder="Add pricing notes or details (optional)"></textarea>
                            <button type="submit" id="priceBtn" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Send Quote to Customer</span>
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    {{-- 2. Payment Verification (Only shows when payment proof uploaded) --}}
                    @if($order->payment_receipt && in_array($order->payment_status, ['pending', 'pending_verification']))
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg p-5 border-2 border-yellow-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Payment Verification</h3>
                        </div>
                        
                        <div class="bg-white rounded-lg p-3 mb-4 border border-yellow-200">
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Customer has submitted payment proof</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            {{-- Confirm Payment Button --}}
                            <form action="{{ route('admin.custom_orders.confirmPayment', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 transform hover:scale-[1.02]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Confirm Payment Received</span>
                                </button>
                            </form>
                            
                            {{-- Reject Payment Button --}}
                            <form action="{{ route('admin.custom_orders.rejectPayment', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this payment? Customer will need to resubmit.');">
                                @csrf
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
                    
                    {{-- 3. Smart Workflow Progress (Only shows after quote sent) --}}
                    @if(!empty($order->price_quoted_at))
                    <div class="bg-white rounded-lg p-5 border-2 border-purple-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                'price_quoted' => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'label' => 'Quoted', 'color' => 'maroon'],
                                'approved' => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'label' => 'Paid', 'color' => 'maroon'],
                                'in_production' => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>', 'label' => 'Production', 'color' => 'maroon'],
                                'production_complete' => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'label' => 'Complete', 'color' => 'maroon'],
                                'out_for_delivery' => ['svg' => '<path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>', 'label' => 'Shipping', 'color' => 'maroon'],
                                'delivered' => ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>', 'label' => 'Delivered', 'color' => 'maroon']
                            ];
                            // If order is marked completed, treat it as delivered for display so earlier steps gray out correctly.
                            $currentStatus = $order->status === 'completed' ? 'delivered' : $order->status;
                            $statusKeys = array_keys($statuses);
                            $currentIndex = array_search($currentStatus, $statusKeys);
                        @endphp
                        
                        <div class="flex items-center justify-between mb-5">
                            @foreach($statuses as $key => $status)
                                @php
                                    $index = array_search($key, $statusKeys);
                                    $isComplete = $index <= $currentIndex;
                                    $isCurrent = $key === $currentStatus;
                                @endphp
                                <div class="flex flex-col items-center flex-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-all @if($isCurrent) scale-110 @endif"
                                         style="@if($isComplete) background-color: {{ $colorMap[$status['color']]['bg'] }}; color: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); @else background-color: #E5E7EB; color: #9CA3AF; @endif @if($isCurrent) box-shadow: 0 0 0 4px {{ $colorMap[$status['color']]['ring'] }}; @endif">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $status['svg'] !!}
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium @if($isComplete) text-gray-800 @else text-gray-400 @endif">
                                        {{ $status['label'] }}
                                    </span>
                                </div>
                                @if(!$loop->last)
                                <div class="flex-1 h-1 mx-1 rounded" style="background-color: @if($index < $currentIndex) {{ $colorMap[$status['color']]['line'] }} @else #E5E7EB @endif"></div>
                                @endif
                            @endforeach
                        </div>
                        
                        {{-- Payment Confirmation Buttons (when status is approved and payment receipt exists but NOT yet confirmed) --}}
                        @if($order->status === 'approved' && $order->payment_receipt && $order->payment_status === 'paid' && !$order->payment_confirmed_at)
                        <div class="space-y-3">
                            <form action="{{ route('admin.custom_orders.confirmPayment', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl hover:bg-green-700 flex items-center justify-center gap-3 transform hover:scale-[1.02]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Confirm Payment Received</span>
                                </button>
                            </form>
                            <form action="{{ route('admin.custom_orders.rejectPayment', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this payment? Customer will need to resubmit.');">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl hover:bg-red-700 flex items-center justify-center gap-3 transform hover:scale-[1.02]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Mark Payment as Failed</span>
                                </button>
                            </form>
                        </div>
                        @else
                        {{-- Next Action Button --}}
                        @php
                            // Define color map for workflow buttons
                            $colorMap = [
                                'maroon' => ['bg' => '#800000', 'ring' => '#E0B0B0', 'line' => '#A05050']
                            ];
                            
                            $nextAction = null;
                            // Show Start Production if payment is confirmed (payment_confirmed_at is set)
                            if ($order->status === 'approved' && $order->payment_confirmed_at) {
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
                        <form id="workflowForm" data-order-id="{{ $order->id }}" data-next-status="{{ $nextAction['status'] }}">
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
                        <div class="bg-gradient-to-r from-purple-100 to-pink-100 border-2 border-purple-300 rounded-lg p-4 text-center">
                            <div class="flex justify-center mb-2">
                                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="font-bold text-purple-900">Order Completed!</div>
                            <div class="text-sm text-purple-700">This order has been successfully delivered</div>
                        </div>
                        @else
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-3 text-center text-sm text-blue-800">
                            <strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $order->status)) }}
                        </div>
                        @endif
                        @endif
                    </div>
                    @endif
                    
                    {{-- 4. Production Delay Notification --}}
                    @if(in_array($order->status, ['approved', 'in_production', 'production_complete']))
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
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

{{-- Automated AJAX Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const messageDiv = document.getElementById('actionMessage');
    
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
    
    console.log('=== Workflow Form Initialization ===');
    console.log('Form element found:', workflowForm);
    
    if (workflowForm) {
        console.log('Form data-order-id:', workflowForm.dataset.orderId);
        console.log('Form data-next-status:', workflowForm.dataset.nextStatus);
        
        workflowForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            console.log('=== Workflow Form Submit Event ===');
            console.log('Event triggered at:', new Date().toISOString());
            
            const button = document.getElementById('workflowBtn');
            const orderId = this.dataset.orderId;
            const nextStatus = this.dataset.nextStatus;
            
            console.log('Button element:', button);
            console.log('Order ID:', orderId);
            console.log('Next Status:', nextStatus);
            console.log('Current button text:', button ? button.textContent : 'BUTTON NOT FOUND');
            
            if (!button) {
                console.error('ERROR: workflowBtn button not found!');
                showMessage('❌ Button not found. Please refresh the page.', 'error');
                return;
            }
            
            if (!orderId || !nextStatus) {
                console.error('ERROR: Missing orderId or nextStatus');
                showMessage('❌ Invalid form configuration. Please refresh the page.', 'error');
                return;
            }
            
            setButtonLoading(button, true);
            
            console.log('Sending request to:', `/admin/custom-orders/${orderId}/update-status`);
            console.log('With body:', { status: nextStatus });
            
            try {
                const response = await fetch(`/admin/custom-orders/${orderId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: nextStatus
                    })
                });
                
                console.log('Response received!');
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('Parsed JSON successfully:', data);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Raw response was:', responseText);
                    showMessage('❌ Server returned invalid response', 'error');
                    setButtonLoading(button, false);
                    return;
                }
                
                console.log('Data success:', data.success);
                console.log('Data message:', data.message);
                
                if (data.success) {
                    const statusLabels = {
                        'in_production': 'Production started',
                        'production_complete': 'Production completed',
                        'out_for_delivery': 'Order shipped',
                        'delivered': 'Order delivered'
                    };
                    const message = `${statusLabels[nextStatus] || 'Status updated'} successfully! Customer has been notified.`;
                    console.log('Showing success message:', message);
                    showMessage(message, 'success');
                    
                    console.log('Reloading page in 100ms...');
                    setTimeout(() => {
                        console.log('Executing window.location.reload()');
                        window.location.reload();
                    }, 100);
                } else {
                    console.error('Update failed:', data);
                    showMessage('❌ ' + (data.message || 'Failed to update status'), 'error');
                    setButtonLoading(button, false);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                console.error('Error type:', error.constructor.name);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                showMessage('❌ Network error: ' + error.message, 'error');
                setButtonLoading(button, false);
            }
        });
        
        console.log('Event listener attached successfully');
    } else {
        console.error('ERROR: workflowForm element not found in DOM!');
    }
    
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
                const response = await fetch(`/admin/custom-orders/${orderId}/notify-delay`, {
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
            const response = await fetch(`/admin/custom-orders/${orderId}/clear-delay`, {
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
            
            setButtonLoading(button, true);
            
            try {
                const response = await fetch(`/admin/custom-orders/${orderId}/quote-price`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        price: formData.get('price'),
                        notes: formData.get('notes')
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
            console.log('URL:', `/admin/custom-orders/${orderId}/verify-payment`);
            
            setButtonLoading(button, true);
            
            try {
                const response = await fetch(`/admin/custom-orders/${orderId}/verify-payment`, {
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
                const response = await fetch(`/admin/custom-orders/${orderId}/verify-payment`, {
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
                const response = await fetch(`/admin/custom-orders/${orderId}/reject`, {
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
