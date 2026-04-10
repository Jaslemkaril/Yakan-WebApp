@extends('layouts.app')

@section('title', 'Review Your Order - Custom Order')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Enhanced Progress Bar -->
    <div class="bg-white shadow-lg border-b-2" style="border-bottom-color:#e0b0b0;">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-center space-x-4 md:space-x-6">
                <div class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">✓</div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Fabric</span>
                </div>
                <div class="w-8 md:w-20 h-1 rounded-full" style="background-color:#800000;"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">✓</div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Pattern</span>
                </div>
                <div class="w-8 md:w-20 h-1 rounded-full" style="background-color:#800000;"></div>
                <div class="flex items-center group cursor-pointer">
                    <div class="relative">
                        <div class="w-10 h-10 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 group-hover:scale-110" style="background-color:#800000;">3</div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <span class="ml-2 md:ml-3 font-bold text-xs md:text-base" style="color:#800000;">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Review Your Order</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Confirm your custom design and submit your order to our master craftsmen</p>
            </div>
        </div>
    </div>

    <!-- Enhanced Review Content -->
    <div class="container mx-auto px-4 py-8">

        @if ($errors->any())
            <div class="max-w-6xl mx-auto mb-6 bg-maroon-50 border border-maroon-200 text-maroon-800 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Please fix the following before submitting your custom order:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Calculate pricing early so it's available for all sections --}}
        @php
            $patternCount = isset($selectedPatterns) ? $selectedPatterns->count() : 0;
            
            // Get price per meter and pattern fee from the first selected pattern
            $pricePerMeterValue = isset($pricePerMeter) ? (float) $pricePerMeter : 500; // use system setting
            $patternFee = 0;
            
            if ($patternCount > 0 && isset($selectedPatterns)) {
                foreach ($selectedPatterns as $pattern) {
                    if (!is_null($pattern->price_per_meter)) {
                        $pricePerMeterValue = (float) $pattern->price_per_meter;
                    }
                    $patternFee += ($pattern->pattern_price ?? 0);
                }
            }
            
            // Calculate fabric cost
            $fabricCost = 0;
            if (isset($wizardData['fabric']['quantity_meters'])) {
                $fabricMeters = (float) $wizardData['fabric']['quantity_meters'];
                $fabricCost = $fabricMeters * $pricePerMeterValue;
            }
            
            // Get fabric type name from ID
            $fabricTypeName = '—';
            if (isset($wizardData['fabric']['type'])) {
                $fabricTypeId = $wizardData['fabric']['type'];
                $fabricType = \App\Models\FabricType::find($fabricTypeId);
                $fabricTypeName = $fabricType ? $fabricType->name : $fabricTypeId;
            }
            
            // Get intended use name from ID
            $intendedUseName = '—';
            if (isset($wizardData['fabric']['intended_use'])) {
                $intendedUseId = $wizardData['fabric']['intended_use'];
                $intendedUse = \App\Models\IntendedUse::find($intendedUseId);
                $intendedUseName = $intendedUse ? $intendedUse->name : $intendedUseId;
            }
            
            // Get production days from selected pattern
            $productionDays = 7; // default fallback
            if ($patternCount > 0 && isset($selectedPatterns)) {
                // Get the production days from the first selected pattern
                $firstPattern = $selectedPatterns->first();
                $productionDays = $firstPattern->production_days ?? 7;
            }
            
            // Apply priority production multiplier if addon is selected
            $hasPriorityProduction = session('wizard.details.addons') && in_array('priority_production', session('wizard.details.addons'));
            $actualProductionDays = $hasPriorityProduction ? ceil($productionDays * 0.5) : $productionDays;
            
            // Get quality check days from system settings
            $qualityCheckDays = \App\Models\SystemSetting::get('quality_check_days', 1);
            
            // Calculate shipping days based on delivery address (zone-based from Zamboanga City)
            $shippingDays = 2; // default fallback
            $defaultAddress = auth()->user()->addresses->where('is_default', true)->first();
            
            if ($defaultAddress) {
                $city = strtolower($defaultAddress->city ?? '');
                $region = strtolower($defaultAddress->province ?? $defaultAddress->region ?? '');
                $postalCode = $defaultAddress->postal_code ?? '';
                
                // Shipping days from Zamboanga City origin
                if (str_contains($city, 'zamboanga') && str_starts_with($postalCode, '7')) {
                    $shippingDays = 1; // Zamboanga City proper
                }
                elseif (str_contains($region, 'zamboanga') || 
                        in_array($city, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                    $shippingDays = 2; // Zamboanga Peninsula (nearby)
                }
                elseif (in_array($city, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) ||
                        str_contains($region, 'barmm') || str_contains($region, 'armm')) {
                    $shippingDays = 3; // Western Mindanao
                }
                elseif (str_contains($region, 'mindanao') ||
                        in_array($city, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                    $shippingDays = 4; // Other Mindanao regions
                }
                elseif (str_contains($region, 'visayas') ||
                        in_array($city, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                    $shippingDays = 4; // Visayas
                }
                elseif (str_contains($city, 'manila') || str_contains($region, 'ncr') ||
                        in_array($city, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                    $shippingDays = 5; // Metro Manila & nearby (closer to Luzon)
                }
                elseif (str_contains($region, 'luzon') ||
                        in_array($city, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                    $shippingDays = 6; // Northern Luzon (farthest)
                }
                else {
                    $shippingDays = 7; // Remote islands & far areas (absolute farthest)
                }
            }
            
            // Calculate pattern fees based on difficulty and admin settings
            // REMOVED - now using each pattern's individual pattern_price which is set above
        @endphp

        @php
            // Resolve selected address first (wizard-selected > default)
            $selectedAddressId = (int) (old('address_id', data_get($wizardData, 'details.address_id') ?? ($defaultAddress->id ?? 0)));
            $selectedAddressForShipping = $userAddresses->firstWhere('id', $selectedAddressId) ?? $defaultAddress;

            $deliveryTypeForPricing = old('delivery_type', data_get($wizardData, 'details.delivery_type') ?? 'delivery');
            $shippingFee = 0;
            $shippingZoneLabel = 'Store pickup (no shipping fee)';

            if ($deliveryTypeForPricing !== 'pickup') {
                $shippingFee = 350;
                $shippingZoneLabel = 'Far Luzon / Remote';

                $addressHaystack = strtolower(trim(implode(' ', array_filter([
                    $selectedAddressForShipping->city ?? null,
                    $selectedAddressForShipping->province ?? null,
                    $selectedAddressForShipping->region ?? null,
                    $selectedAddressForShipping->barangay ?? null,
                    $selectedAddressForShipping->street_name ?? null,
                    $selectedAddressForShipping->landmark ?? null,
                ]))));

                if ($addressHaystack === '') {
                    $shippingFee = 100;
                    $shippingZoneLabel = 'Within Zamboanga City';
                } elseif (str_contains($addressHaystack, 'zamboanga')) {
                    $shippingFee = 100;
                    $shippingZoneLabel = 'Within Zamboanga City / Zamboanga Peninsula';
                } elseif (
                    str_contains($addressHaystack, 'barmm') ||
                    str_contains($addressHaystack, 'bangsamoro') ||
                    str_contains($addressHaystack, 'basilan') ||
                    str_contains($addressHaystack, 'sulu') ||
                    str_contains($addressHaystack, 'tawi')
                ) {
                    $shippingFee = 100;
                    $shippingZoneLabel = 'Zamboanga Peninsula + BARMM';
                } elseif (
                    str_contains($addressHaystack, 'mindanao') ||
                    str_contains($addressHaystack, 'davao') ||
                    str_contains($addressHaystack, 'cagayan de oro') ||
                    str_contains($addressHaystack, 'iligan') ||
                    str_contains($addressHaystack, 'cotabato') ||
                    str_contains($addressHaystack, 'caraga') ||
                    str_contains($addressHaystack, 'general santos') ||
                    str_contains($addressHaystack, 'soccsksargen')
                ) {
                    $shippingFee = 180;
                    $shippingZoneLabel = 'Other Mindanao Regions';
                } elseif (
                    str_contains($addressHaystack, 'visaya') ||
                    str_contains($addressHaystack, 'cebu') ||
                    str_contains($addressHaystack, 'iloilo') ||
                    str_contains($addressHaystack, 'bacolod') ||
                    str_contains($addressHaystack, 'tacloban') ||
                    str_contains($addressHaystack, 'leyte') ||
                    str_contains($addressHaystack, 'samar') ||
                    str_contains($addressHaystack, 'bohol') ||
                    str_contains($addressHaystack, 'negros')
                ) {
                    $shippingFee = 250;
                    $shippingZoneLabel = 'Visayas';
                } elseif (
                    str_contains($addressHaystack, 'ncr') ||
                    str_contains($addressHaystack, 'metro manila') ||
                    str_contains($addressHaystack, 'manila') ||
                    str_contains($addressHaystack, 'quezon city') ||
                    str_contains($addressHaystack, 'makati') ||
                    str_contains($addressHaystack, 'calabarzon') ||
                    str_contains($addressHaystack, 'central luzon') ||
                    str_contains($addressHaystack, 'laguna') ||
                    str_contains($addressHaystack, 'cavite') ||
                    str_contains($addressHaystack, 'bulacan')
                ) {
                    $shippingFee = 300;
                    $shippingZoneLabel = 'NCR + Nearby Luzon';
                }
            }

            $addons = session('wizard.details.addons') ?? [];
            $addonsTotal = collect($addons)->sum(function($addon) {
                return $addon == 'priority_production' ? 500 : ($addon == 'gift_wrapping' ? 150 : ($addon == 'extra_patterns' ? 200 : 100));
            });
            $finalTotal = $patternFee + $fabricCost + $shippingFee + $addonsTotal;
        @endphp

        <form method="POST" action="{{ route('custom_orders.complete.wizard') }}" id="submitOrderForm">
        @csrf
        <input type="hidden" name="auth_token" id="step4AuthToken" value="{{ request('auth_token') }}">

        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Order Summary -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Enhanced Order Details -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Order Details</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Product Info -->
                        <div class="flex items-start space-x-4 pb-6 border-b-2 border-gray-200">
                            <div class="w-24 h-24 bg-gradient-to-br from-maroon-100 to-maroon-200 rounded-xl flex items-center justify-center shadow-lg">
                                @if(isset($product) && $product->image)
                                    <img src="{{ $product->image_src }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <span class="text-3xl font-bold" style="color:#800000;">{{ isset($product) ? substr($product->name, 0, 1) : 'Y' }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-xl text-gray-900">{{ isset($product) ? $product->name : 'Custom Yakan Fabric' }}</h4>
                                <p class="text-sm text-gray-600 mt-2">{{ isset($product) ? $product->description : 'Premium fabric with authentic Yakan patterns' }}</p>
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-sm text-gray-500 font-medium">Pattern Fee:</span>
                                    <span class="font-bold text-lg" style="color:#800000;">₱{{ isset($patternFee) ? number_format($patternFee, 2) : '0.00' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fabric Details (from Step 1) -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-5l-2-2H6a2 2 0 00-2 2v6"/>
                                </svg>
                                Fabric Details
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Type:</span><span class="ml-2 text-gray-900">{{ $fabricTypeName ?? '—' }}</span></div>
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Meters:</span><span class="ml-2 text-gray-900">{{ $wizardData['fabric']['quantity_meters'] ?? '—' }} m</span></div>
                                <div class="flex items-center"><span class="text-gray-500 font-medium w-28">Use:</span><span class="ml-2 text-gray-900">{{ $intendedUseName ?? '—' }}</span></div>
                            </div>
                        </div>

                        <!-- Patterns Selected -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                </svg>
                                Patterns Applied
                            </h5>
                            
                            <!-- Pattern Preview Display -->
                            @if(isset($selectedPatterns) && $selectedPatterns->count() > 0)
                            @php
                                $activePatternName = $selectedPatterns->pluck('name')->implode(', ');
                                $activePreview = null;
                                $activeFirstPattern = $selectedPatterns->first();
                                if ($activeFirstPattern) {
                                    if (method_exists($activeFirstPattern, 'hasSvg') && $activeFirstPattern->hasSvg()) {
                                        $activeSvg = $activeFirstPattern->getSvgContent();
                                        if (!empty($activeSvg)) {
                                            $activePreview = 'data:image/svg+xml;base64,' . base64_encode($activeSvg);
                                        }
                                    }
                                    if (empty($activePreview)) {
                                        $activePreview = optional($activeFirstPattern->media->first())->url;
                                    }
                                }
                                $activePreview = $activePreview
                                    ?? ($previewImage ?? null)
                                    ?? ($wizardData['pattern']['preview_image'] ?? ($wizardData['design']['image'] ?? null));

                                $activeCustomization = $wizardData['pattern']['customization_settings'] ?? [];
                                $activeScale = $activeCustomization['scale'] ?? 1;
                                $activeRotation = $activeCustomization['rotation'] ?? 0;
                                $activeOpacity = round(($activeCustomization['opacity'] ?? 0.85) * 100);
                            @endphp
                            <div class="mb-6">
                                <div class="rounded-xl p-4 border-2 shadow-lg" style="background: linear-gradient(to bottom right, #f5e6e8, #e8ccd1); border-color:#8b3a56;">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="text-sm font-semibold text-gray-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Your Customized Pattern
                                        </h6>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Final Preview</span>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 shadow-inner">
                                        <div class="text-xs font-semibold text-gray-600 mb-2" id="step4ActivePatternName">{{ $activePatternName }}</div>
                                        <div class="w-full rounded-lg border-2 shadow-md overflow-hidden" style="border-color:#e0b0b0; max-height: 350px;">
                                            <div class="w-full h-64 flex items-center justify-center bg-gray-50 p-4">
                                                @if(!empty($activePreview))
                                                    <img id="step4ActivePreviewImage" src="{{ $activePreview }}" alt="Selected pattern preview" class="max-h-full max-w-full object-contain">
                                                    <span id="step4ActivePreviewEmpty" class="hidden text-gray-400">No preview available</span>
                                                @else
                                                    <img id="step4ActivePreviewImage" src="" alt="Selected pattern preview" class="hidden max-h-full max-w-full object-contain">
                                                    <span id="step4ActivePreviewEmpty" class="text-gray-400">No preview available</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-3 gap-3">
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border-2" style="border-color:#d9a3b3;">
                                            <div class="text-xs font-semibold text-gray-600 mb-1">Scale</div>
                                            <div class="font-bold text-lg" style="color:#8b3a56;" id="step4ActiveScale">{{ $activeScale }}x</div>
                                        </div>
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border-2" style="border-color:#d9a3b3;">
                                            <div class="text-xs font-semibold text-gray-600 mb-1">Rotation</div>
                                            <div class="font-bold text-lg" style="color:#8b3a56;" id="step4ActiveRotation">{{ $activeRotation }}°</div>
                                        </div>
                                        <div class="bg-white rounded-lg px-3 py-2 text-center border-2" style="border-color:#d9a3b3;">
                                            <div class="text-xs font-semibold text-gray-600 mb-1">Opacity</div>
                                            <div class="font-bold text-lg" style="color:#8b3a56;" id="step4ActiveOpacity">{{ $activeOpacity }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse($selectedPatterns as $p)
                                    <div class="flex items-center space-x-3 p-3 rounded-lg border" style="background-color:#f5e6e8; border-color:#d9a3b3;">
                                        @php
                                            $thumb = null;
                                            if (method_exists($p, 'hasSvg') && $p->hasSvg()) {
                                                $svg = $p->getSvgContent();
                                                if (!empty($svg)) {
                                                    $thumb = 'data:image/svg+xml;base64,' . base64_encode($svg);
                                                }
                                            }
                                            if (empty($thumb)) {
                                                $thumb = optional($p->media->first())->url;
                                            }
                                        @endphp
                                        @if($thumb)
                                            <img src="{{ $thumb }}" alt="{{ $p->name }}" class="w-8 h-8 rounded object-cover"/>
                                        @else
                                            <div class="w-8 h-8 rounded" style="background: linear-gradient(to bottom right, #800000, #a00000);"></div>
                                        @endif
                                        <span class="text-sm font-medium text-gray-700">{{ $p->name }}</span>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No patterns selected.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="pb-6 border-b-2 border-gray-200">
                            <h5 class="font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Customer Information
                            </h5>

                            @php
                                $user = auth()->user();
                                $fullName = $user->name;
                                if ($user->first_name && $user->last_name) {
                                    $fullName = trim($user->first_name . ' ' . ($user->middle_initial ? $user->middle_initial . '. ' : '') . $user->last_name);
                                }
                                $customerName  = $fullName;
                                $customerEmail = $user->email;
                                $customerPhone = data_get($wizardData, 'details.customer_phone') ?? ($defaultAddress ? $defaultAddress->phone_number : null);
                                $deliveryType  = data_get($wizardData, 'details.delivery_type') ?? 'delivery';
                            @endphp

                            <!-- Account row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm mb-4">
                                <div class="flex flex-col bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Full Name</span>
                                    <span class="font-semibold text-gray-900">{{ $customerName }}</span>
                                </div>
                                <div class="flex flex-col bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Email</span>
                                    <span class="font-semibold text-gray-900 break-all">{{ $customerEmail }}</span>
                                </div>
                                @if($customerPhone)
                                <div class="flex flex-col bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Phone</span>
                                    <span class="font-semibold text-gray-900">{{ $customerPhone }}</span>
                                </div>
                                @endif
                                <div class="flex flex-col bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Delivery Method</span>
                                    <span class="font-semibold text-gray-900" id="deliveryTypeDisplay">
                                        @if($deliveryType === 'pickup') 🏪 Store Pickup @else 🚚 Delivery to Address @endif
                                    </span>
                                </div>
                            </div>

                            @if($deliveryType === 'delivery' && $defaultAddress)
                            <!-- Full address card -->
                            <div class="rounded-xl border-2 p-4" style="border-color:#d0a0a0; background: linear-gradient(135deg,#fff8f8,#fff0f0);" id="deliveryAddressRow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="text-sm font-bold" style="color:#800000;">Delivery Address</span>
                                        @if($defaultAddress->label)
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold" style="background:#f5e6e8;color:#800000;">{{ $defaultAddress->label }}</span>
                                        @endif
                                    </div>
                                    @if($defaultAddress->is_default)
                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Default</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    @if($defaultAddress->full_name)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">Recipient</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->full_name }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->phone_number)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">Contact No.</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->phone_number }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->house_number || $defaultAddress->street_name)
                                    <div class="flex flex-col sm:col-span-2">
                                        <span class="text-xs text-gray-500 font-medium">House / Street</span>
                                        <span class="font-semibold text-gray-800">{{ trim(($defaultAddress->house_number ?? '') . ' ' . ($defaultAddress->street_name ?? '')) }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->barangay)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">Barangay</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->barangay }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->city)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">City / Municipality</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->city }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->province)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">Province</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->province }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->zip_code ?? $defaultAddress->postal_code ?? null)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium">ZIP Code</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->zip_code ?? $defaultAddress->postal_code }}</span>
                                    </div>
                                    @endif
                                    @if($defaultAddress->landmark)
                                    <div class="flex flex-col sm:col-span-2">
                                        <span class="text-xs text-gray-500 font-medium">Landmark</span>
                                        <span class="font-semibold text-gray-800">{{ $defaultAddress->landmark }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @elseif($deliveryType === 'delivery')
                            <div class="hidden" id="deliveryAddressRow"></div>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Finalize Details -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mb-2">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M5 8h14M5 16h.01M5 12h.01"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-900">Finalize Details</h3>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" min="1" id="quantity" name="quantity" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2" style="--tw-ring-color:#800000;" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Option *</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <label class="inline-flex items-center px-4 py-3 rounded-lg border-2 text-sm cursor-pointer transition-all duration-200 hover:border-maroon-800 hover:bg-maroon-50" style="border-color:#8b3a56;" id="delivery-option-label">
                                <input type="radio" name="delivery_type" value="delivery" class="mr-3 w-4 h-4 delivery-radio" checked style="accent-color: #8b3a56;">
                                <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="font-medium">Delivery to Address</span>
                                </div>
                            </label>
                            <label class="inline-flex items-center px-4 py-3 rounded-lg border-2 text-sm cursor-pointer transition-all duration-200 hover:border-maroon-800 hover:bg-maroon-50" style="border-color:#8b3a56;" id="pickup-option-label">
                                <input type="radio" name="delivery_type" value="pickup" class="mr-3 w-4 h-4 delivery-radio" style="accent-color: #8b3a56;">
                                <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="font-medium">Store Pickup</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Address Selection -->
                    <div id="delivery-address-section">
                            @if($userAddresses->count() > 0)
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Select Delivery Address *
                            </label>
                            <div class="space-y-3 mb-4">
                                    @foreach($userAddresses as $address)
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md" style="border-color: {{ $address->id === ($selectedAddressForShipping->id ?? null) ? '#8b3a56' : '#d1d5db' }}; background-color: {{ $address->id === ($selectedAddressForShipping->id ?? null) ? '#f5e6e8' : 'white' }};">
                                <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1 mr-3 w-4 h-4 flex-shrink-0" style="accent-color: #8b3a56;" {{ $address->id === ($selectedAddressForShipping->id ?? null) ? 'checked' : '' }} required />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <p class="font-bold text-gray-900 text-base">
                                                @if($address->label)
                                                <span class="text-maroon-700">{{ $address->label }}</span>
                                                @endif
                                        </p>
                                            @if($address->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full whitespace-nowrap" style="background-color: #800000; color: white;">Default Address</span>
                                            @endif
                                    </div>
                                    <p class="text-sm text-gray-800 leading-relaxed">
                                            {{ $address->house_number }}{{ $address->street_name ? ', ' . $address->street_name : '' }}{{ $address->barangay ? ', ' . $address->barangay : '' }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                            {{ $address->city }}{{ $address->province ? ', ' . $address->province : '' }}
                                            @if($address->region && $address->region !== $address->province)
                                                , {{ $address->region }}
                                            @endif
                                            @if($address->zip_code)
                                                {{ $address->zip_code }}
                                            @endif
                                    </p>
                                        @if($address->landmark)
                                        <p class="text-xs text-gray-500 mt-2 flex items-start">
                                            <svg class="w-3.5 h-3.5 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>{{ $address->landmark }}</span>
                                        </p>
                                        @endif
                                </div>
                            </label>
                                    @endforeach
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                Need to add or edit an address?
                                </p>
                                <a href="{{ route('addresses.index') }}" class="inline-flex items-center text-sm text-maroon-600 hover:text-maroon-700 font-semibold transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                                Manage your addresses
                                </a>
                            </div>
                            @else
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Delivery Address *
                                <span class="text-xs text-gray-500 font-normal">(Please provide complete address for delivery)</span>
                            </label>
                            <div class="space-y-3">
                                <input type="text" name="delivery_house" id="delivery_house" placeholder="House / Unit / Building No. *" value="{{ data_get($wizardData, 'details.delivery_house', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                <input type="text" name="delivery_street" id="delivery_street" placeholder="Street Name *" value="{{ data_get($wizardData, 'details.delivery_street', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="delivery_barangay" id="delivery_barangay" placeholder="Barangay *" value="{{ data_get($wizardData, 'details.delivery_barangay', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                            <input type="text" name="delivery_city" id="delivery_city" placeholder="City / Municipality *" value="{{ data_get($wizardData, 'details.delivery_city', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="delivery_province" id="delivery_province" placeholder="Province *" value="{{ data_get($wizardData, 'details.delivery_province', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" required />
                            <input type="text" name="delivery_zip" id="delivery_zip" placeholder="ZIP Code (optional)" value="{{ data_get($wizardData, 'details.delivery_zip', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" />
                                </div>
                                <input type="text" name="delivery_landmark" id="delivery_landmark" placeholder="Landmark (e.g., near SM Mall, beside gas station)" value="{{ data_get($wizardData, 'details.delivery_landmark', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-maroon-800" style="--tw-ring-color:#8b3a56;" />
                                <div class="bg-maroon-50 border border-maroon-200 rounded-lg p-3">
                            <p class="text-xs text-maroon-800 flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color: #8b3a56;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                    Please provide as much detail as possible so our courier can find your location easily. Landmark is very helpful!
                            </p>
                                </div>
                            </div>
                            @endif
                    </div>

                    <!-- Shipping Zone Reference (delivery only) -->
                    <div id="shipping-zone-section" class="mt-4">
                        <div class="p-4 rounded-xl border-2 border-gray-200" style="background:#f9fafb;">
                            <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                    Delivery Location &amp; Shipping Fee
                                <span class="ml-2 text-xs text-gray-400 font-normal">(from Zamboanga City)</span>
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs mb-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-blue-700">&#8369;100</div>
                            <div class="text-blue-600">Within Zamboanga City</div>
                                </div>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-blue-700">&#8369;100</div>
                            <div class="text-blue-600">Zamboanga Peninsula + BARMM</div>
                                </div>
                                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-indigo-700">&#8369;180</div>
                            <div class="text-indigo-600">Other Mindanao Regions</div>
                                </div>
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-purple-700">&#8369;250</div>
                            <div class="text-purple-600">Visayas</div>
                                </div>
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-orange-700">&#8369;300</div>
                            <div class="text-orange-600">NCR + Nearby Luzon</div>
                                </div>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-2 text-center">
                            <div class="font-bold text-red-700">&#8369;350</div>
                            <div class="text-red-600">Far Luzon / Remote</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border-2 border-gray-300" id="step4ShippingFeeBox">
                                <div>
                            <div class="text-sm font-semibold text-gray-700">Estimated Shipping Fee</div>
                            <div class="text-xs text-gray-500" id="step4ShippingZoneLabel">{{ $shippingZoneLabel }}</div>
                                </div>
                                <div class="text-xl font-bold" style="color:#800000;" id="step4ShippingFeeDisplay">{{ $shippingFee > 0 ? ('₱' . number_format($shippingFee, 2)) : 'FREE' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Store Pickup Information -->
                    <div id="pickup-info-section" class="hidden">
                        <div class="bg-gradient-to-br from-maroon-50 to-maroon-100 border-2 border-maroon-200 rounded-xl p-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div class="flex-1">
                            <h4 class="font-bold text-maroon-900 mb-2 text-lg">Store Pickup Location</h4>
                            <p class="text-sm text-maroon-800 mb-3">Pick up your order at our Yakan weaving center:</p>
                            <div class="bg-white rounded-lg p-4 border border-maroon-300">
                                <p class="font-semibold text-gray-900">Tuwas Yakan Weaving Center</p>
                                <p class="text-sm text-gray-700 mt-2">
                                        Yakan Village, Upper Calarian<br>
                                        Labuan-Limpapa Road, National Road<br>
                                        Zamboanga City, Philippines 7000
                                </p>
                                <p class="text-xs text-gray-600 mt-3">
                                    <strong>Operating Hours:</strong><br>
                                        Monday - Saturday: 8:00 AM - 6:00 PM<br>
                                        Sunday: Closed
                                </p>
                                <p class="text-xs text-gray-600 mt-2">
                                    <strong>Contact:</strong> 0935 569 0272
                                </p>
                            </div>
                            <p class="text-xs text-maroon-700 mt-3 flex items-start">
                                <svg class="w-4 h-4 mr-1 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" style="color: #8b3a56;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                    We will notify you when your order is ready for pickup. Please bring a valid ID.
                            </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="specifications" class="block text-sm font-medium text-gray-700 mb-1">Special Requests / Notes</label>
                        <textarea id="specifications" name="specifications" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2" style="--tw-ring-color:#800000;" placeholder="Tell us any specific requests (e.g., sizing, placement, extra details)"></textarea>
                    </div>
                </div>
                </div>
            </div>

            <!-- Right Column - Pricing & Actions -->
            <div class="space-y-8">
                
                <!-- Enhanced Pricing Breakdown -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Pricing Breakdown</h3>
                    </div>
                    
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100" id="patternFeeRow">
                            <span class="text-gray-600 font-medium">Pattern Fee</span>
                            <span class="font-medium text-gray-900" id="patternFeeDisplay">₱{{ number_format($patternFee, 2) }}</span>
                        </div>
                        @if($fabricCost > 0)
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100" id="fabricCostRow">
                            <span class="text-gray-600 font-medium" id="fabricCostLabel">Fabric Cost ({{ $wizardData['fabric']['quantity_meters'] }}m × ₱{{ number_format($pricePerMeterValue, 2) }})</span>
                            <span class="font-medium text-gray-900" id="fabricCostDisplay">₱{{ number_format($fabricCost, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100" id="shippingFeeRow">
                            <span class="text-gray-600 font-medium">Shipping Fee</span>
                            @if($shippingFee == 0)
                                <span class="font-medium text-green-600" id="shippingFeeDisplay">FREE</span>
                            @else
                                <span class="font-medium text-gray-900" id="shippingFeeDisplay">₱{{ number_format($shippingFee, 2) }}</span>
                            @endif
                        </div>
                        @if(!empty($addons))
                            @foreach($addons as $addon)
                                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                    <span class="text-gray-600 font-medium">{{ $addon == 'priority_production' ? '⚡ Priority Production' : ($addon == 'gift_wrapping' ? '🎁 Gift Wrapping' : ($addon == 'extra_patterns' ? '🎨 Extra Patterns' : '🛡️ Shipping Insurance')) }}</span>
                                    <span class="font-medium text-gray-900">₱{{ number_format($addon == 'priority_production' ? 500 : ($addon == 'gift_wrapping' ? 150 : ($addon == 'extra_patterns' ? 200 : 100)), 2) }}</span>
                                </div>
                            @endforeach
                        @endif
                        <div class="border-t-2 border-gray-200 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Estimated Total</span>
                                <span class="text-2xl font-bold" style="color:#800000;" id="finalTotalDisplay">₱{{ number_format($finalTotal, 2) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Subject to admin review. Final quoted price may change.</p>
                        </div>
                    </div>
                    
                    <!-- Store base prices for JavaScript calculations -->
                    <div style="display: none;" id="priceData" 
                        data-base-price="{{ $patternFee }}"
                        data-fabric-cost-base="{{ $fabricCost }}"
                        data-shipping-fee="{{ $shippingFee }}"
                        data-delivery-shipping-fee="{{ $shippingFee }}"
                        data-addons-total="{{ $addonsTotal }}"
                        data-price-per-meter="{{ $pricePerMeterValue }}"
                        data-fabric-meters="{{ $wizardData['fabric']['quantity_meters'] ?? 0 }}">
                    </div>
                </div>

                <!-- Enhanced Production Timeline -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-3" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Production Timeline</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg" style="background-color:#800000;">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Order Confirmed</p>
                                <p class="text-sm text-gray-600">Today</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg" style="background-color:#a00000;">
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Design Production</p>
                                <p class="text-sm text-gray-600">{{ $actualProductionDays }} day{{ $actualProductionDays != 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Quality Check</p>
                                <p class="text-sm text-gray-600">{{ $qualityCheckDays }} day{{ $qualityCheckDays != 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Shipping</p>
                                <p class="text-sm text-gray-600">{{ $shippingDays }} day{{ $shippingDays != 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 rounded-xl border-2" style="background: linear-gradient(to right, #f5e6e8, #e8ccd1); border-color:#8b3a56;">
                        <p class="text-sm font-bold" style="color:#8b3a56;">Estimated Delivery</p>
                        <p class="text-xl font-bold" style="color:#8b3a56;">{{ date('M d, Y', strtotime('+' . ($actualProductionDays + $qualityCheckDays + $shippingDays) . ' days')) }}</p>
                    </div>
                </div>

                <!-- Enhanced Submit Actions -->
                <div class="space-y-4">

                    {{-- All Submission Items Card (queued + current) --}}
                    @php
                        $batchItems = $batchItems ?? [];
                        $currentPatternName = isset($selectedPatterns) && $selectedPatterns->count() > 0
                            ? $selectedPatterns->pluck('name')->implode(', ')
                            : ($wizardData['pattern']['name'] ?? '—');
                        $currentPatternMedia = null;
                        if (isset($selectedPatterns) && $selectedPatterns->count() > 0) {
                            $firstSelectedPattern = $selectedPatterns->first();
                            if ($firstSelectedPattern) {
                                if (method_exists($firstSelectedPattern, 'hasSvg') && $firstSelectedPattern->hasSvg()) {
                                    $svg = $firstSelectedPattern->getSvgContent();
                                    if (!empty($svg)) {
                                        $currentPatternMedia = 'data:image/svg+xml;base64,' . base64_encode($svg);
                                    }
                                }

                                if (empty($currentPatternMedia)) {
                                    $currentPatternMedia = $firstSelectedPattern->media->first()->url ?? null;
                                }
                            }
                        }
                        $currentPreview = $currentPatternMedia
                            ?? $previewImage
                            ?? ($wizardData['pattern']['preview_image'] ?? ($wizardData['design']['image'] ?? null));
                        $currentQty = (int) old('quantity', 1);
                        $totalSubmissionItems = count($batchItems) + 1;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-xl border-2 p-6" style="border-color:#e0b0b0;">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 flex-shrink-0" style="background-color:#f5e6e8;">
                                <svg class="w-5 h-5" style="color:#800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    All Items in This Submission
                                    <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full text-white" style="background-color:#800000;">{{ $totalSubmissionItems }}</span>
                                </h3>
                                <p class="text-xs text-gray-500">All items below will be submitted together under one order number.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($batchItems as $bIdx => $bItem)
                            @php
                                $bWizard = $bItem['wizard_data'] ?? [];
                                $bPatternNames = collect();
                                $bPreview = null;
                                if (!empty($bWizard['pattern']['selected_ids']) && is_array($bWizard['pattern']['selected_ids'])) {
                                    $bSelectedPatterns = \App\Models\YakanPattern::with('media')->whereIn('id', $bWizard['pattern']['selected_ids'])->get();
                                    $bPatternNames = $bSelectedPatterns->pluck('name');

                                    $bFirstPattern = $bSelectedPatterns->first();
                                    if ($bFirstPattern) {
                                        if (method_exists($bFirstPattern, 'hasSvg') && $bFirstPattern->hasSvg()) {
                                            $bSvg = $bFirstPattern->getSvgContent();
                                            if (!empty($bSvg)) {
                                                $bPreview = 'data:image/svg+xml;base64,' . base64_encode($bSvg);
                                            }
                                        }

                                        if (empty($bPreview)) {
                                            $bPreview = optional($bFirstPattern->media->first())->url;
                                        }
                                    }
                                }
                                $bPatternName = $bPatternNames->isNotEmpty() ? $bPatternNames->implode(', ') : ($bWizard['pattern']['name'] ?? '—');
                                $bPreview = $bPreview ?? ($bWizard['pattern']['preview_image'] ?? ($bWizard['design']['image'] ?? null));
                                $bAuthToken = request('auth_token');
                                $bEditUrl = route('custom_orders.edit.batch.item', $bIdx) . ($bAuthToken ? '?auth_token=' . urlencode($bAuthToken) : '');
                                $bCustomization = $bWizard['pattern']['customization_settings'] ?? [];
                                $bScale = $bCustomization['scale'] ?? 1;
                                $bRotation = $bCustomization['rotation'] ?? 0;
                                $bOpacity = round(($bCustomization['opacity'] ?? 0.85) * 100);
                                $bFabricTypeName = '—';
                                if (!empty($bWizard['fabric']['type'])) {
                                    $bFt = \App\Models\FabricType::find($bWizard['fabric']['type']);
                                    $bFabricTypeName = $bFt ? $bFt->name : $bWizard['fabric']['type'];
                                }
                                $bMeters = $bWizard['fabric']['quantity_meters'] ?? null;
                                $bQty = (int) ($bItem['form_data']['quantity'] ?? 1);
                            @endphp
                            <div class="rounded-xl px-4 py-3 submission-item-card cursor-pointer" style="background-color:#fff5f5; border:1px solid #e0b0b0;"
                                 data-preview="{{ e((string) ($bPreview ?? '')) }}"
                                 data-pattern-name="{{ e((string) $bPatternName) }}"
                                 data-scale="{{ $bScale }}"
                                 data-rotation="{{ $bRotation }}"
                                 data-opacity="{{ $bOpacity }}"
                                 data-item-label="Item {{ $bIdx + 1 }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start min-w-0">
                                        <div class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center mr-3 flex-shrink-0" style="background-color:#800000;">{{ $bIdx + 1 }}</div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $bItem['summary'] ?? 'Custom Item ' . ($bIdx + 1) }}</p>
                                            <p class="text-xs text-gray-500">Pattern: <span class="font-semibold text-gray-700">{{ $bPatternName }}</span></p>
                                            <p class="text-xs text-gray-500">Fabric: <span class="font-semibold text-gray-700">{{ $bFabricTypeName }}</span></p>
                                            <div class="mt-1">
                                                <a href="{{ $bEditUrl }}"
                                                   class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold transition-colors"
                                                   style="border-color:#8b3a56; color:#8b3a56; background-color:#fff5f5;"
                                                   title="Edit this item">
                                                    Edit
                                                </a>
                                            </div>
                                            <form method="POST" action="{{ route('custom_orders.update.batch.item', $bIdx) }}" class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-1 batch-item-form" data-index="{{ $bIdx }}">
                                                @csrf
                                                @method('PATCH')
                                                @if(request('auth_token'))<input type="hidden" name="auth_token" value="{{ request('auth_token') }}">@endif
                                                <label class="text-xs text-gray-500">Meters:
                                                    <input type="number" name="quantity_meters" value="{{ $bMeters ?? '' }}" min="1" step="1"
                                                        class="w-14 border border-gray-300 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 ml-1" style="--tw-ring-color:#800000;">
                                                </label>
                                                <label class="text-xs text-gray-500">Qty:
                                                    <input type="number" name="quantity" value="{{ $bQty }}" min="1"
                                                        class="w-12 border border-gray-300 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 ml-1" style="--tw-ring-color:#800000;">
                                                </label>
                                            </form>
                                            <p class="text-xs text-gray-500 mt-0.5">Added {{ $bItem['added_at'] ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2 flex-shrink-0">
                                        @if(!empty($bPreview))
                                            <img src="{{ $bPreview }}" alt="Pattern preview" class="w-14 h-14 rounded-md object-cover border" style="border-color:#e0b0b0;">
                                        @endif
                                        <form method="POST" action="{{ route('custom_orders.remove.batch.item', $bIdx) }}" onsubmit="return confirm('Remove this item from your order batch?');">
                                            @csrf
                                            @method('DELETE')
                                            @if(request('auth_token'))<input type="hidden" name="auth_token" value="{{ request('auth_token') }}">@endif
                                            <button type="submit" class="p-1 rounded-lg hover:bg-red-100 transition-colors" title="Remove item">
                                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            {{-- Current item being reviewed --}}
                            @php
                                $currentCustomization = $wizardData['pattern']['customization_settings'] ?? [];
                                $currentScale = $currentCustomization['scale'] ?? 1;
                                $currentRotation = $currentCustomization['rotation'] ?? 0;
                                $currentOpacity = round(($currentCustomization['opacity'] ?? 0.85) * 100);
                            @endphp
                            <div class="rounded-xl px-4 py-3 border-2 submission-item-card" style="background-color:#fff5f5; border-color:#c88f9f;"
                                 data-preview="{{ e((string) ($currentPreview ?? '')) }}"
                                 data-pattern-name="{{ e((string) $currentPatternName) }}"
                                 data-scale="{{ $currentScale }}"
                                 data-rotation="{{ $currentRotation }}"
                                 data-opacity="{{ $currentOpacity }}"
                                 data-item-label="Current Item"
                                 data-default-active="true">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start min-w-0">
                                        <div class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center mr-3 flex-shrink-0" style="background-color:#800000;">{{ count($batchItems) + 1 }}</div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">Current Item (Ready to submit)</p>
                                            <p class="text-xs text-gray-500">Pattern: <span class="font-semibold text-gray-700">{{ $currentPatternName }}</span></p>
                                            <p class="text-xs text-gray-500">Fabric: <span class="font-semibold text-gray-700">{{ $fabricTypeName }}</span></p>
                                            <form method="POST" action="{{ route('custom_orders.update.current.item') }}" id="currentItemForm" class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-1">
                                                @csrf
                                                @if(request('auth_token'))<input type="hidden" name="auth_token" value="{{ request('auth_token') }}">@endif
                                                <label class="text-xs text-gray-500">Meters:
                                                    <input type="number" name="quantity_meters" id="currentItemMeters" value="{{ $wizardData['fabric']['quantity_meters'] ?? '' }}" min="1" step="1"
                                                        class="w-14 border border-gray-300 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 ml-1" style="--tw-ring-color:#800000;">
                                                </label>
                                                <label class="text-xs text-gray-500">Qty:
                                                    <input type="number" name="_current_qty_sync" id="currentItemQty" value="{{ $currentQty }}" min="1"
                                                        class="w-12 border border-gray-300 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 ml-1" style="--tw-ring-color:#800000;"
                                                        oninput="var q=document.getElementById('quantity');if(q){q.value=this.value;q.dispatchEvent(new Event('input'));}">
                                                </label>
                                            </form>
                                        </div>
                                    </div>
                                    @if(!empty($currentPreview))
                                        <img src="{{ $currentPreview }}" alt="Current pattern preview" class="w-14 h-14 rounded-md object-cover border flex-shrink-0" style="border-color:#e0b0b0;">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t" style="border-color:#e0b0b0;">
                            <p class="text-sm font-semibold" style="color:#800000;">
                                Total items to submit: {{ $totalSubmissionItems }}
                                <span class="font-normal text-gray-600">({{ count($batchItems) }} queued + current item)</span>
                            </p>
                        </div>
                    </div>


                        {{-- Add Another Custom Item button --}}
                        <button type="button" id="addAnotherBtn"
                            class="group w-full px-8 py-3 text-white rounded-xl font-bold transition-all duration-300 shadow-md hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 mb-3"
                            style="background-color:#1d4ed8;"
                            onclick="submitAddToBatch()">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Another Custom Item to This Order
                            </span>
                        </button>

                        <button type="submit" id="submitBtn" class="group relative w-full px-8 py-4 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color:#8b3a56 !important;" onmouseover="this.style.backgroundColor='#7a3350 !important'" onmouseout="this.style.backgroundColor='#8b3a56 !important'">
                            <span class="flex items-center justify-center" id="submitBtnText">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"/>
                                </svg>
                                @if(count($batchItems) > 0)
                                    Place All {{ count($batchItems) + 1 }} Items
                                @else
                                    Place Custom Order
                                @endif
                            </span>
                            <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-20 transition-opacity duration-300" style="background-color:#8b3a56;"></div>
                        </button>
                    
                    <script>
                    document.getElementById('submitOrderForm').addEventListener('submit', function(e) {
                        const form = document.getElementById('submitOrderForm');
                        const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value;
                        const quantity = document.getElementById('quantity').value;
                        
                        // Validate delivery type
                        if (!deliveryType) {
                            e.preventDefault();
                            alert('Please select a delivery option (Delivery or Pickup)');
                            return false;
                        }
                        
                        // If delivery type is 'delivery', validate address selection
                        if (deliveryType === 'delivery') {
                            const selectedAddress = document.querySelector('input[name="address_id"]:checked');
                            const hasUserAddresses = {{ $userAddresses->count() > 0 ? 'true' : 'false' }};
                            
                            if (hasUserAddresses && !selectedAddress) {
                                e.preventDefault();
                                alert('Please select a delivery address from your saved addresses');
                                return false;
                            }
                            
                            if (!hasUserAddresses) {
                                // Manual address form - validate all required fields
                                const house = document.getElementById('delivery_house')?.value?.trim();
                                const street = document.getElementById('delivery_street')?.value?.trim();
                                const barangay = document.getElementById('delivery_barangay')?.value?.trim();
                                const city = document.getElementById('delivery_city')?.value?.trim();
                                const province = document.getElementById('delivery_province')?.value?.trim();
                                
                                if (!house || !street || !barangay || !city || !province) {
                                    e.preventDefault();
                                    alert('Please fill in all required delivery address fields');
                                    return false;
                                }
                            }
                        }
                        
                        // Validate quantity
                        if (!quantity || quantity < 1) {
                            e.preventDefault();
                            alert('Please enter a valid quantity (minimum 1)');
                            return false;
                        }
                        
                        // All validation passed - use AJAX submit with loading overlay
                        e.preventDefault();
                        // Guard against accidental action changes from other flows.
                        if (form) {
                            form.action = '{{ route("custom_orders.complete.wizard") }}';
                        }
                        var urlToken = new URLSearchParams(window.location.search).get('auth_token');
                        if (urlToken) document.getElementById('step4AuthToken').value = urlToken;
                        submitWizardForm('submitOrderForm', 'Submitting your order...', 'Creating your custom order...');
                    });

                    /**
                     * submitAddToBatch — validates the current item and posts it to the
                     * addToBatch endpoint so it gets queued, then the wizard resets for
                     * the user to configure the next item.
                     */
                    function submitAddToBatch() {
                        const form = document.getElementById('submitOrderForm');
                        if (!form) {
                            return;
                        }
                        const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value;
                        const quantity = document.getElementById('quantity').value;

                        if (!deliveryType) {
                            alert('Please select a delivery option (Delivery or Pickup)');
                            return;
                        }
                        if (deliveryType === 'delivery') {
                            const selectedAddress = document.querySelector('input[name="address_id"]:checked');
                            const hasUserAddresses = {{ $userAddresses->count() > 0 ? 'true' : 'false' }};
                            if (hasUserAddresses && !selectedAddress) {
                                alert('Please select a delivery address from your saved addresses');
                                return;
                            }
                            if (!hasUserAddresses) {
                                const house    = document.getElementById('delivery_house')?.value?.trim();
                                const street   = document.getElementById('delivery_street')?.value?.trim();
                                const barangay = document.getElementById('delivery_barangay')?.value?.trim();
                                const city     = document.getElementById('delivery_city')?.value?.trim();
                                const province = document.getElementById('delivery_province')?.value?.trim();
                                if (!house || !street || !barangay || !city || !province) {
                                    alert('Please fill in all required delivery address fields');
                                    return;
                                }
                            }
                        }
                        if (!quantity || quantity < 1) {
                            alert('Please enter a valid quantity (minimum 1)');
                            return;
                        }

                        // Inject auth_token if present in URL
                        var urlToken = new URLSearchParams(window.location.search).get('auth_token');
                        if (urlToken) document.getElementById('step4AuthToken').value = urlToken;

                        // Force POST for add-to-batch and remove any leaked method spoofing input.
                        form.action = '{{ route("custom_orders.add.to.batch") }}';
                        form.method = 'POST';
                        form.querySelectorAll('input[name="_method"]').forEach(function(input) {
                            if (input && input.parentNode) {
                                input.parentNode.removeChild(input);
                            }
                        });

                        const formData = new FormData(form);
                        formData.delete('_method');

                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function(response) {
                            if (response.redirected && response.url) {
                                window.location.href = response.url;
                                return;
                            }
                            return response.text().then(function(html) {
                                document.open();
                                document.write(html);
                                document.close();
                            });
                        })
                        .catch(function() {
                            // Fallback to classic form submit as plain POST.
                            form.submit();
                        });
                    }
                    </script>
                    
                    <button type="button" onclick="window.history.back()" class="group block w-full text-center px-8 py-3 bg-white border-2 text-maroon-700 rounded-xl font-bold hover:bg-maroon-50 transition-all duration-300 transform hover:scale-105" style="border-color:#8b3a56;">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back to Order Details
                        </span>
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load SVG design from session/localStorage
    loadSVGDesignPreview();

    // Click any submission card to view its pattern in the preview panel.
    initSubmissionItemPreviewSwitcher();
    
    // Initialize delivery/pickup toggle
    initDeliveryToggle();
});

function initSubmissionItemPreviewSwitcher() {
    const cards = Array.from(document.querySelectorAll('.submission-item-card'));
    const previewImg = document.getElementById('step4ActivePreviewImage');
    const previewEmpty = document.getElementById('step4ActivePreviewEmpty');
    const patternNameEl = document.getElementById('step4ActivePatternName');
    const scaleEl = document.getElementById('step4ActiveScale');
    const rotationEl = document.getElementById('step4ActiveRotation');
    const opacityEl = document.getElementById('step4ActiveOpacity');

    if (!cards.length || !patternNameEl || !scaleEl || !rotationEl || !opacityEl) {
        return;
    }

    function setActiveCard(card) {
        cards.forEach(function(c) {
            c.classList.remove('ring-2', 'ring-red-300', 'ring-offset-1');
        });
        card.classList.add('ring-2', 'ring-red-300', 'ring-offset-1');

        const patternName = card.dataset.patternName || 'No pattern selected';
        const preview = card.dataset.preview || '';
        const scale = card.dataset.scale || '1';
        const rotation = card.dataset.rotation || '0';
        const opacity = card.dataset.opacity || '100';

        patternNameEl.textContent = patternName;
        scaleEl.textContent = scale + 'x';
        rotationEl.textContent = rotation + '°';
        opacityEl.textContent = opacity + '%';

        if (previewImg) {
            if (preview) {
                previewImg.src = preview;
                previewImg.classList.remove('hidden');
                if (previewEmpty) previewEmpty.classList.add('hidden');
            } else {
                previewImg.src = '';
                previewImg.classList.add('hidden');
                if (previewEmpty) previewEmpty.classList.remove('hidden');
            }
        }
    }

    const ignoreSelectors = 'a, button, input, select, textarea, label';

    cards.forEach(function(card) {
        card.addEventListener('click', function(event) {
            if (event.target.closest(ignoreSelectors)) {
                return;
            }
            setActiveCard(card);
        });
    });

    const defaultCard = cards.find(function(card) {
        return card.dataset.defaultActive === 'true';
    }) || cards[0];

    setActiveCard(defaultCard);
}

function initDeliveryToggle() {
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const deliverySection = document.getElementById('delivery-address-section');
    const pickupSection = document.getElementById('pickup-info-section');
    const shippingZoneSection = document.getElementById('shipping-zone-section');
    const deliveryOptionLabel = document.getElementById('delivery-option-label');
    const pickupOptionLabel = document.getElementById('pickup-option-label');
    
    // Get delivery address fields
    const deliveryFields = [
        document.getElementById('delivery_house'),
        document.getElementById('delivery_street'),
        document.getElementById('delivery_barangay'),
        document.getElementById('delivery_city'),
        document.getElementById('delivery_province')
    ];
    
    function toggleDeliveryPickup() {
        const selectedValue = document.querySelector('input[name="delivery_type"]:checked').value;
        
        if (selectedValue === 'delivery') {
            // Show delivery fields, hide pickup info
            deliverySection.classList.remove('hidden');
            pickupSection.classList.add('hidden');
            if (shippingZoneSection) shippingZoneSection.classList.remove('hidden');
            
            // Make delivery fields required
            deliveryFields.forEach(field => {
                if (field) field.setAttribute('required', 'required');
            });
            
            // Update label styling
            deliveryOptionLabel.classList.add('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            deliveryOptionLabel.classList.remove('border-gray-300');
            pickupOptionLabel.classList.remove('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            pickupOptionLabel.classList.add('border-gray-300');
            
        } else {
            // Show pickup info, hide delivery fields
            deliverySection.classList.add('hidden');
            pickupSection.classList.remove('hidden');
            if (shippingZoneSection) shippingZoneSection.classList.add('hidden');
            
            // Remove required from delivery fields
            deliveryFields.forEach(field => {
                if (field) field.removeAttribute('required');
            });
            
            // Update label styling
            pickupOptionLabel.classList.add('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            pickupOptionLabel.classList.remove('border-gray-300');
            deliveryOptionLabel.classList.remove('border-red-800', 'bg-red-50', 'ring-2', 'ring-red-200');
            deliveryOptionLabel.classList.add('border-gray-300');
        }
    }
    
    const SHIPPING_ZONES = {
        0: { label: 'Within Zamboanga City', fee: 100 },
        1: { label: 'Zamboanga Peninsula + BARMM', fee: 100 },
        2: { label: 'Other Mindanao Regions', fee: 180 },
        3: { label: 'Visayas', fee: 250 },
        4: { label: 'NCR + Nearby Luzon', fee: 300 },
        5: { label: 'Far Luzon / Remote', fee: 350 }
    };

    function detectShippingZone(addressText) {
        const text = (addressText || '').toLowerCase();
        if (!text) return 0;
        if (text.includes('zamboanga')) return 0;
        if (text.includes('barmm') || text.includes('bangsamoro') || text.includes('basilan') || text.includes('sulu') || text.includes('tawi')) return 1;
        if (text.includes('mindanao') || text.includes('davao') || text.includes('cagayan de oro') || text.includes('iligan') || text.includes('cotabato') || text.includes('caraga') || text.includes('general santos') || text.includes('soccsksargen')) return 2;
        if (text.includes('visaya') || text.includes('cebu') || text.includes('iloilo') || text.includes('bacolod') || text.includes('tacloban') || text.includes('leyte') || text.includes('samar') || text.includes('bohol') || text.includes('negros')) return 3;
        if (text.includes('ncr') || text.includes('metro manila') || text.includes('manila') || text.includes('quezon city') || text.includes('makati') || text.includes('calabarzon') || text.includes('central luzon') || text.includes('laguna') || text.includes('cavite') || text.includes('bulacan')) return 4;
        return 5;
    }

    function updateStep4ShippingAndTotal() {
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value || 'delivery';
        const feeDisplay = document.getElementById('step4ShippingFeeDisplay');
        const zoneLabel = document.getElementById('step4ShippingZoneLabel');
        const shippingFeeDisplay = document.getElementById('shippingFeeDisplay');
        const finalTotalDisplay = document.getElementById('finalTotalDisplay');
        const priceData = document.getElementById('priceData');

        let shippingFee = 0;
        let label = 'Store pickup (no shipping fee)';

        if (deliveryType === 'delivery') {
            const selectedAddressRadio = document.querySelector('input[name="address_id"]:checked');
            const selectedAddressLabel = selectedAddressRadio ? selectedAddressRadio.closest('label') : null;
            const selectedAddressText = selectedAddressLabel ? selectedAddressLabel.textContent : '';
            const zone = detectShippingZone(selectedAddressText);
            const info = SHIPPING_ZONES[zone];
            shippingFee = info.fee;
            label = info.label;
        }

        if (feeDisplay) feeDisplay.textContent = shippingFee > 0 ? ('₱' + shippingFee.toFixed(2)) : 'FREE';
        if (zoneLabel) zoneLabel.textContent = label;
        if (shippingFeeDisplay) shippingFeeDisplay.textContent = shippingFee > 0 ? ('₱' + shippingFee.toFixed(2)) : 'FREE';

        if (finalTotalDisplay && priceData) {
            const base = parseFloat(priceData.dataset.basePrice || 0);
            const fabric = parseFloat(priceData.dataset.fabricCostBase || 0);
            const addons = parseFloat(priceData.dataset.addonsTotal || 0);
            const total = base + fabric + shippingFee + addons;
            finalTotalDisplay.textContent = '\u20B1' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // Update shipping fee display when address selection changes
    document.querySelectorAll('input[name="address_id"]').forEach(function(radio) {
        radio.addEventListener('change', updateStep4ShippingAndTotal);
    });

    // Add event listeners
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleDeliveryPickup();
            updateStep4ShippingAndTotal();
        });
    });
    
    // Set initial state
    toggleDeliveryPickup();
    updateStep4ShippingAndTotal();
}

function loadSVGDesignPreview() {
    // Load saved design from localStorage
    const savedPattern = localStorage.getItem('selectedYakanPattern');
    const savedColor = localStorage.getItem('selectedYakanColor');
    
    if (savedPattern && savedColor) {
        // Update the SVG preview with saved design
        updateSVGPreview(savedPattern, savedColor);
    }
}

function updateSVGPreview(patternType, color) {
    const svgContainer = document.getElementById('svgPreviewContainer');
    if (!svgContainer) return;
    
    // Create SVG based on pattern type and color
    const svgPatterns = {
        'sussuh': `
            <svg width="100%" height="400" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="reviewPattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <path d="M50,10 L90,50 L50,90 L10,50 Z" fill="${color}" stroke="#ffffff" stroke-width="2"/>
                        <path d="M50,30 L70,50 L50,70 L30,50 Z" fill="#FFD700" stroke="#ffffff" stroke-width="1"/>
                        <circle cx="50" cy="50" r="8" fill="${color}"/>
                    </pattern>
                </defs>
                <rect width="600" height="400" fill="url(#reviewPattern)" color="${color}"/>
                <text x="300" y="200" text-anchor="middle" font-size="24" fill="#666" font-family="Arial">Sussuh Diamond Pattern</text>
            </svg>
        `,
        'banga': `
            <svg width="100%" height="400" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="reviewPattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="50" cy="50" r="35" fill="${color}" stroke="#ffffff" stroke-width="2"/>
                        <circle cx="50" cy="50" r="25" fill="#FFD700" stroke="#ffffff" stroke-width="1"/>
                        <circle cx="50" cy="50" r="12" fill="${color}"/>
                    </pattern>
                </defs>
                <rect width="600" height="400" fill="url(#reviewPattern)" color="${color}"/>
                <text x="300" y="200" text-anchor="middle" font-size="24" fill="#666" font-family="Arial">Banga Circle Pattern</text>
            </svg>
        `
    };
    
    svgContainer.innerHTML = svgPatterns[patternType] || svgPatterns['sussuh'];
}

function exportDesign() {
    const previewImg = document.getElementById('reviewPreviewImg');
    if (previewImg) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
            canvas.width = img.naturalWidth || 1200;
            canvas.height = img.naturalHeight || 800;
            ctx.drawImage(img, 0, 0);
            const link = document.createElement('a');
            link.download = 'custom-yakan-design.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showNotification('Design exported successfully!', 'success');
        };
        img.src = previewImg.src;
        return;
    }

    // Fallback: export inline SVG if present
    const svgElement = document.querySelector('#svgPreviewContainer svg');
    if (svgElement) {
        const svgData = new XMLSerializer().serializeToString(svgElement);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.onload = function () {
            canvas.width = 600;
            canvas.height = 400;
            ctx.drawImage(img, 0, 0);
            const link = document.createElement('a');
            link.download = 'custom-yakan-design.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showNotification('Design exported successfully!', 'success');
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
    }
}

function editDesign() {
    // Navigate back to design step based on flow
    @if(isset($product))
        window.location.href = "{{ route('custom_orders.create.product.customize') }}";
    @else
        window.location.href = "{{ route('custom_orders.create.pattern') }}";
    @endif
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        warning: 'bg-yellow-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    notification.classList.add(...colors[type].split(' '));
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Handle quantity changes for dynamic pricing
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const currentItemQty = document.getElementById('currentItemQty');
    const currentItemMeters = document.getElementById('currentItemMeters');

    // Two-way sync: main qty ↔ card qty
    if (quantityInput && currentItemQty) {
        quantityInput.addEventListener('input', function() {
            currentItemQty.value = this.value;
        });
    }

    const priceData = document.getElementById('priceData');

    // AJAX helpers
    function ajaxPost(url, formEl) {
        const data = new FormData(formEl);
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        }).catch(() => {}); // silent fail; UI already updated
    }

    function ajaxPatch(url, formEl) {
        const data = new FormData(formEl);
        // FormData with _method=PATCH — Laravel will handle it
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        }).catch(() => {});
    }

    // Auto-save current item meters (debounced)
    if (currentItemMeters) {
        let metersTimer;
        currentItemMeters.addEventListener('input', function() {
            updatePricesFromInputs();
            clearTimeout(metersTimer);
            metersTimer = setTimeout(function() {
                const form = document.getElementById('currentItemForm');
                if (form) ajaxPost(form.action, form);
            }, 700);
        });
    }

    // Auto-save queued batch items (debounced)
    document.querySelectorAll('.batch-item-form').forEach(function(form) {
        let timer;
        form.querySelectorAll('input[type="number"]').forEach(function(inp) {
            inp.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    ajaxPatch(form.action, form);
                }, 700);
            });
        });
    });

    if (priceData) {
        const pricePerMeter = parseFloat(priceData.dataset.pricePerMeter) || 0;
        const basePatternFee = parseFloat(priceData.dataset.basePrice) || 0;
        const shippingFee = parseFloat(priceData.dataset.shippingFee) || 0;
        const addonsTotal = parseFloat(priceData.dataset.addonsTotal) || 0;

        // Listen to quantity input changes
        if (quantityInput) {
            quantityInput.addEventListener('change', updatePricesFromInputs);
            quantityInput.addEventListener('input', updatePricesFromInputs);
        }

        function formatCurrency(amount) {
            return '₱' + parseFloat(amount).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updatePricesFromInputs() {
            const quantity = parseInt(quantityInput ? quantityInput.value : 1) || 1;
            const meters = parseInt(currentItemMeters ? currentItemMeters.value : (priceData.dataset.fabricMeters || 0)) || 0;

            const newFabricCostPerUnit = meters * pricePerMeter;
            const newPatternFee = basePatternFee * quantity;
            const newFabricCost = newFabricCostPerUnit * quantity;
            const newFinalTotal = newPatternFee + newFabricCost + shippingFee + addonsTotal;

            const patternFeeDisplay = document.getElementById('patternFeeDisplay');
            if (patternFeeDisplay) patternFeeDisplay.textContent = formatCurrency(newPatternFee);

            const fabricCostDisplay = document.getElementById('fabricCostDisplay');
            if (fabricCostDisplay && newFabricCost > 0) fabricCostDisplay.textContent = formatCurrency(newFabricCost);

            const fabricCostLabel = document.getElementById('fabricCostLabel');
            if (fabricCostLabel && meters > 0) {
                fabricCostLabel.textContent = 'Fabric Cost (' + meters + 'm × ₱' + pricePerMeter.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
            }

            const fabricCostRow = document.getElementById('fabricCostRow');
            if (fabricCostRow) fabricCostRow.style.display = newFabricCost > 0 ? '' : 'none';

            const finalTotalDisplay = document.getElementById('finalTotalDisplay');
            if (finalTotalDisplay) finalTotalDisplay.textContent = formatCurrency(newFinalTotal);
        }

        // Run once on load to sync
        updatePricesFromInputs();
    }

    // Make updatePricesFromInputs accessible for inline oninput handlers
    window.updatePricesFromInputs = window.updatePricesFromInputs || function() {};
    if (typeof updatePricesFromInputs === 'function') {
        window.updatePricesFromInputs = updatePricesFromInputs;
    }
    
    // Handle delivery option toggle to update Customer Information display
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const deliveryTypeDisplay = document.getElementById('deliveryTypeDisplay');
    const deliveryAddressRow = document.getElementById('deliveryAddressRow');
    
    if (deliveryRadios.length > 0) {
        const priceDataEl = document.getElementById('priceData');
        const deliveryShippingFee = priceDataEl ? parseFloat(priceDataEl.dataset.deliveryShippingFee) || 100 : 100;
        const addonsTotal = priceDataEl ? parseFloat(priceDataEl.dataset.addonsTotal) || 0 : 0;
        const basePrice = priceDataEl ? parseFloat(priceDataEl.dataset.basePrice) || 0 : 0;
        const fabricCostBase = priceDataEl ? parseFloat(priceDataEl.dataset.fabricCostBase) || 0 : 0;

        function updateShippingAndTotal(isPickup) {
            const shippingRow = document.getElementById('shippingFeeRow');
            const shippingDisplay = document.getElementById('shippingFeeDisplay');
            const finalTotalDisplay = document.getElementById('finalTotalDisplay');
            const qty = parseInt(document.getElementById('quantity')?.value) || 1;
            const fee = isPickup ? 0 : deliveryShippingFee;

            if (priceDataEl) priceDataEl.dataset.shippingFee = fee;

            if (shippingDisplay) {
                if (isPickup) {
                    shippingDisplay.textContent = 'FREE';
                    shippingDisplay.className = 'font-medium text-green-600';
                } else {
                    shippingDisplay.textContent = '₱' + fee.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    shippingDisplay.className = 'font-medium text-gray-900';
                }
            }

            if (finalTotalDisplay) {
                const newTotal = (basePrice + fabricCostBase) * qty + fee + addonsTotal;
                finalTotalDisplay.textContent = '₱' + newTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        deliveryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const isPickup = this.value === 'pickup';

                if (deliveryTypeDisplay) {
                    deliveryTypeDisplay.innerHTML = isPickup ? '🏪 Store Pickup' : '🚚 Delivery';
                }

                if (deliveryAddressRow) {
                    deliveryAddressRow.classList.toggle('hidden', isPickup);
                }

                updateShippingAndTotal(isPickup);
            });
        });
    }
});

</script>

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.color-circle {
    transition: all 0.3s ease;
}

.color-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.pattern-badge {
    transition: all 0.3s ease;
}

.pattern-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>
@endpush

<div id="wizardLoadingOverlay" style="display:none;position:fixed;inset:0;z-index:9999;background:linear-gradient(135deg,#800000 0%,#500000 60%,#300000 100%);align-items:center;justify-content:center;flex-direction:column;">
    <div style="text-align:center;animation:wlFadeIn 0.4s ease;color:white;padding:20px;">
        <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:40px;">
            <div style="width:52px;height:52px;background:white;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#800000;">Y</div>
            <span style="font-size:28px;font-weight:800;letter-spacing:-0.5px;">Yakan</span>
        </div>
        <div style="width:56px;height:56px;border:4px solid rgba(255,255,255,0.3);border-top-color:white;border-radius:50%;animation:wlSpin 0.8s linear infinite;margin:0 auto 32px;"></div>
        <p id="wlTitle" style="font-size:22px;font-weight:700;margin:0 0 10px;">Submitting your order...</p>
        <p id="wlSubtitle" style="font-size:15px;opacity:0.75;margin:0;">Creating your custom order...</p>
        <div style="height:3px;background:rgba(255,255,255,0.15);border-radius:2px;overflow:hidden;width:200px;margin:20px auto 0;"><div id="wlProgress" style="height:100%;background:rgba(255,255,255,0.75);border-radius:2px;width:0%;"></div></div>
    </div>
</div>
<style>
@keyframes wlSpin { to { transform: rotate(360deg); } }
@keyframes wlFadeIn { from { opacity:0;transform:translateY(20px); } to { opacity:1;transform:translateY(0); } }
</style>
<script>
function showWizardLoading(title, subtitle) {
    var overlay = document.getElementById('wizardLoadingOverlay');
    if (overlay) {
        document.getElementById('wlTitle').textContent = title || 'Saving your progress...';
        document.getElementById('wlSubtitle').textContent = subtitle || 'Please wait a moment';
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        var prog = document.getElementById('wlProgress');
        if (prog) { prog.style.transition = 'none'; prog.style.width = '0%'; void prog.offsetHeight; prog.style.transition = 'width 20s cubic-bezier(0.1,0.4,0.2,1)'; prog.style.width = '88%'; }
        if (window._wlTimer) clearTimeout(window._wlTimer);
        window._wlTimer = setTimeout(function() {
            var sub = document.getElementById('wlSubtitle');
            if (sub && overlay.style.display === 'flex') sub.textContent = 'Still processing\u2026 almost there';
        }, 15000);
    }
}
function hideWizardLoading() {
    var overlay = document.getElementById('wizardLoadingOverlay');
    if (overlay) { overlay.style.display = 'none'; document.body.style.overflow = ''; }
    if (window._wlTimer) { clearTimeout(window._wlTimer); window._wlTimer = null; }
}
function getWizardAuthToken() {
    return new URLSearchParams(window.location.search).get('auth_token') ||
           localStorage.getItem('yakan_auth_token') || '';
}
function submitWizardForm(formId, title, subtitle) {
    showWizardLoading(title || 'Saving your progress...', subtitle || 'Please wait a moment');
    var form = document.getElementById(formId);
    if (!form) { hideWizardLoading(); return; }
    var token = getWizardAuthToken();
    if (token) {
        var at = form.querySelector('input[name="auth_token"]');
        if (at) at.value = token;
    }
    var formData = new FormData(form);
    // Final submit endpoint only accepts POST; remove leaked method overrides.
    formData.delete('_method');
    var url = form.action;
    if (token && url.indexOf('auth_token') === -1) {
        url += (url.indexOf('?') >= 0 ? '&' : '?') + 'auth_token=' + encodeURIComponent(token);
    }
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
        } else if (data.success) {
            location.reload();
        } else {
            hideWizardLoading();
            alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(function(err) {
        console.error('Submit error:', err);
        hideWizardLoading();
        // Keep fallback submit as plain POST by removing method spoofing inputs.
        form.querySelectorAll('input[name="_method"]').forEach(function(input) {
            if (input && input.parentNode) {
                input.parentNode.removeChild(input);
            }
        });
        form.submit();
    });
}
</script>

@endsection
