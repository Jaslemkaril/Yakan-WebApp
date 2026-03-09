@extends('layouts.app')

@push('styles')
<style>
    .payment-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }
    .payment-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    .payment-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
    }
    .payment-card:hover {
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.15);
        border-color: #800000;
    }
    .payment-method-option {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-method-option:hover {
        border-color: #800000;
        background-color: #fef2f2;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.1);
    }
    .payment-method-option.selected {
        border-color: #800000;
        background-color: #fef2f2;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.15);
    }
    .payment-btn {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(128, 0, 0, 0.2);
    }
    .payment-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 0, 0.3);
    }
    .payment-btn:active { transform: translateY(0); }
    .order-summary-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .order-summary-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .order-summary-icon svg { width: 24px; height: 24px; color: white; }
    .summary-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; }
    .summary-row.total { border-top: 2px solid #800000; padding-top: 1rem; margin-top: 1rem; font-size: 1.125rem; }
    .summary-row.total .label { font-weight: 700; color: #1f2937; }
    .summary-row.total .value { font-weight: 700; color: #800000; font-size: 1.5rem; }
    .delivery-badge {
        display: inline-block;
        background: linear-gradient(135deg, #fef2f2 0%, #fde8e8 100%);
        color: #800000;
        padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 700;
        border: 2px solid #800000;
    }
    .back-link {
        display: inline-flex; align-items: center; gap: 0.5rem;
        color: #800000; font-weight: 700; transition: all 0.3s ease; text-decoration: none;
    }
    .back-link:hover { color: #600000; transform: translateX(-4px); }
    .dropdown-field {
        width: 100%; border: 2px solid #d1d5db; border-radius: 0.5rem;
        padding: 0.75rem 1rem; font-size: 0.95rem; outline: none;
        transition: border-color 0.2s;
    }
    .dropdown-field:focus { border-color: #800000; box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
</style>
@endpush

@section('content')
@php
    $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
    $isDelivery = $deliveryType !== 'pickup';

    // Determine shipping fee
    // Priority: stored shipping_fee > calculate from stored city/province > 0 (pickup)
    $storedCity = $order->delivery_city ?? '';
    $storedProvince = $order->delivery_province ?? '';
    $storedShippingFee = (float) ($order->shipping_fee ?? 0);

    if (!$isDelivery) {
        $calcShippingFee = 0;
    } elseif ($storedShippingFee > 0) {
        $calcShippingFee = $storedShippingFee;
    } elseif ($storedCity || $storedProvince) {
        // Calculate from stored city/province using same zone logic
        $cityL = strtolower($storedCity);
        $regionL = strtolower($storedProvince);
        if (str_contains($cityL, 'zamboanga') || str_contains($regionL, 'zamboanga') ||
            str_contains($regionL, 'barmm') || str_contains($regionL, 'bangsamoro') ||
            in_array($cityL, ['dipolog city','dapitan city','pagadian city','isabela city',
                              'zamboanga del norte (dipolog city)','jolo (sulu)','bongao (tawi-tawi)',
                              'cotabato city','marawi city','lamitan city (basilan)'])) {
            $calcShippingFee = 100;
        } elseif (str_contains($regionL, 'mindanao') || str_contains($regionL, 'davao') ||
                  str_contains($regionL, 'soccsksargen') || str_contains($regionL, 'caraga') ||
                  str_contains($regionL, 'northern mindanao')) {
            $calcShippingFee = 180;
        } elseif (str_contains($regionL, 'visayas')) {
            $calcShippingFee = 250;
        } elseif (str_contains($regionL, 'ncr') || str_contains($regionL, 'metro manila') ||
                  str_contains($regionL, 'calabarzon') || str_contains($regionL, 'central luzon')) {
            $calcShippingFee = 300;
        } else {
            $calcShippingFee = 350;
        }
    } else {
        $calcShippingFee = null; // unknown — user must select region
    }

    // Check if admin already included delivery_fee in price breakdown
    $priceBreakdown = $order->getPriceBreakdown();
    $breakdown = $priceBreakdown['breakdown'] ?? [];
    $adminDeliveryFee = (float) ($breakdown['delivery_fee'] ?? 0);
    $hasBreakdown = !empty($breakdown);

    // Base price (= admin-set final_price before we add shipping)
    // If shipping_fee was already added previously, don't double-count
    $baseOrderPrice = (float) $order->final_price;
@endphp

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="payment-hero py-12 relative rounded-2xl mb-8">
            <div class="relative z-10 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-2 flex items-center justify-center gap-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Complete Payment
                </h1>
                <p class="text-lg text-gray-100">Order #{{ $order->id }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Order Summary Card -->
        <div class="payment-card p-8 mb-6">
            <div class="order-summary-header">
                <div class="order-summary-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Order Summary</h2>
            </div>

            <div class="space-y-1">
                <!-- Customer -->
                <div class="summary-row">
                    <span class="text-gray-600 font-medium">Customer</span>
                    <span class="font-semibold text-gray-900">{{ $order->user->name ?? auth()->user()->name ?? 'N/A' }}</span>
                </div>

                <!-- Delivery Option -->
                <div class="summary-row">
                    <span class="text-gray-600 font-medium">Delivery Option</span>
                    <span class="delivery-badge">
                        {{ $isDelivery ? '🚚 Delivery' : '🏪 Store Pickup' }}
                    </span>
                </div>

                @if($order->delivery_address)
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg p-4 border-2 border-red-200 my-2">
                        <span class="text-gray-700 text-sm font-bold block mb-1">📍 Delivery Address</span>
                        <span class="font-medium text-gray-900 text-sm block">{{ $order->delivery_address }}</span>
                        @if($storedCity || $storedProvince)
                            <span class="text-xs text-gray-500 mt-1 block">{{ $storedCity }}@if($storedCity && $storedProvince), @endif{{ $storedProvince }}</span>
                        @endif
                    </div>
                @endif

                <!-- Product Details -->
                @if(method_exists($order, 'isFabricOrder') && $order->isFabricOrder())
                    <div class="summary-row">
                        <span class="text-gray-600 font-medium">Product</span>
                        <span class="font-semibold text-gray-900">Custom Fabric Order</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-gray-600 font-medium">Fabric Type</span>
                        <span class="font-semibold text-gray-900">{{ $order->fabric_type ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-gray-600 font-medium">Quantity</span>
                        <span class="font-semibold text-gray-900">{{ $order->formatted_fabric_quantity ?? ($order->fabric_quantity_meters . ' m') }}</span>
                    </div>
                    @if(!empty($order->intended_use_label))
                        <div class="summary-row">
                            <span class="text-gray-600 font-medium">Intended Use</span>
                            <span class="font-semibold text-gray-900">{{ $order->intended_use_label }}</span>
                        </div>
                    @endif
                @else
                    <div class="summary-row">
                        <span class="text-gray-600 font-medium">Product</span>
                        <span class="font-semibold text-gray-900">{{ $order->product->name ?? 'Custom Product' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-gray-600 font-medium">Quantity</span>
                        <span class="font-semibold text-gray-900">{{ $order->quantity }}</span>
                    </div>
                @endif

                @if($hasBreakdown)
                    <div class="border-t-2 border-gray-200 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">📋 Price Breakdown (by Admin)</h3>
                        @if(isset($breakdown['material_cost']) && $breakdown['material_cost'] > 0)
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-gray-600">Material Cost</span>
                                <span class="font-semibold">₱{{ number_format($breakdown['material_cost'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($breakdown['pattern_fee']) && $breakdown['pattern_fee'] > 0)
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-gray-600">Pattern/Design Fee</span>
                                <span class="font-semibold">₱{{ number_format($breakdown['pattern_fee'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($breakdown['labor_cost']) && $breakdown['labor_cost'] > 0)
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-gray-600">Labor Cost</span>
                                <span class="font-semibold">₱{{ number_format($breakdown['labor_cost'], 2) }}</span>
                            </div>
                        @endif
                        @if($adminDeliveryFee > 0)
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-gray-600">Delivery Fee (Admin)</span>
                                <span class="font-semibold">₱{{ number_format($adminDeliveryFee, 2) }}</span>
                            </div>
                        @endif
                        @if(isset($breakdown['discount']) && $breakdown['discount'] > 0)
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-red-600">Discount</span>
                                <span class="font-semibold text-red-600">-₱{{ number_format($breakdown['discount'], 2) }}</span>
                            </div>
                        @endif
                        @if(!empty($priceBreakdown['notes']))
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-xs text-gray-600 font-medium mb-1">📝 Admin Note:</p>
                                <p class="text-sm text-gray-800">{{ $priceBreakdown['notes'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Order Price (before shipping) -->
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-gray-700 font-medium">Order Price</span>
                    <span class="font-bold text-gray-900">₱{{ number_format($baseOrderPrice, 2) }}</span>
                </div>

                <!-- Shipping Fee row -->
                @if($isDelivery)
                    <div class="flex justify-between py-3 border-b border-gray-200 items-center">
                        <span class="text-gray-700 font-medium">Shipping Fee</span>
                        <span class="font-bold text-lg" id="payShippingDisplay">
                            @if($adminDeliveryFee > 0)
                                <span class="text-gray-500 text-sm">(included above)</span>
                            @elseif($calcShippingFee === null)
                                <span class="text-orange-600">— select region below</span>
                            @elseif($calcShippingFee == 0)
                                <span class="text-green-600">FREE</span>
                            @else
                                <span class="text-gray-900">₱{{ number_format($calcShippingFee, 2) }}</span>
                            @endif
                        </span>
                    </div>
                @endif

                <!-- Grand Total -->
                <div class="summary-row total">
                    <span class="label">Total Amount</span>
                    <span class="value" id="payGrandTotal">
                        @php
                            if ($adminDeliveryFee > 0 || !$isDelivery || $calcShippingFee === null) {
                                $grandTotal = $baseOrderPrice;
                            } else {
                                $grandTotal = $baseOrderPrice + $calcShippingFee;
                            }
                        @endphp
                        ₱{{ number_format($grandTotal, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ===== PAYMENT FORM ===== --}}
        <form method="POST" action="{{ route('custom_orders.payment.process', $order->id) }}" id="customPaymentForm" class="payment-card p-8 mb-8">
            @csrf
            <!-- Hidden shipping fields -->
            <input type="hidden" name="shipping_fee"      id="hiddenShippingFee"      value="{{ $calcShippingFee ?? 0 }}">
            <input type="hidden" name="delivery_city"     id="hiddenDeliveryCity"     value="{{ $storedCity }}">
            <input type="hidden" name="delivery_province" id="hiddenDeliveryProvince" value="{{ $storedProvince }}">

            <div class="order-summary-header mb-8">
                <div class="order-summary-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Select Payment Method</h2>
            </div>

            <div class="space-y-4 mb-8">
                <!-- GCash -->
                <label class="payment-method-option {{ old('payment_method') === 'online_banking' ? 'selected' : '' }}">
                    <input type="radio" name="payment_method" value="online_banking" {{ old('payment_method') === 'online_banking' ? 'checked' : '' }} class="w-5 h-5" style="accent-color:#800000;">
                    <div class="payment-method-content flex-1 ml-4">
                        <div class="font-bold text-lg">💳 GCash</div>
                        <p class="text-sm text-gray-600 mt-1">Pay using GCash e-wallet — Fast &amp; Secure</p>
                    </div>
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </label>

                <!-- Bank Transfer -->
                <label class="payment-method-option {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}">
                    <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }} class="w-5 h-5" style="accent-color:#800000;">
                    <div class="payment-method-content flex-1 ml-4">
                        <div class="font-bold text-lg">🏦 Bank Transfer</div>
                        <p class="text-sm text-gray-600 mt-1">Direct transfer to our bank account — Secure &amp; Reliable</p>
                    </div>
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </label>
            </div>

            @error('payment_method')
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ $message }}</p>
                </div>
            @enderror

            <button type="submit" class="payment-btn w-full text-lg py-4 justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Continue to Payment
            </button>
        </form>

        <!-- Back Link -->
        <div class="text-center mb-8">
            <a href="{{ route('custom_orders.show', $order) }}" class="back-link text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Order Details
            </a>
        </div>

    </div>
</div>

<script>
// (Shipping location selection was moved to Step 4)
const PH_LOCATIONS = {
    "Zamboanga Peninsula (Region IX)": {
        shippingZone: 0,
        cities: {
            "Zamboanga City":                       { postal: "7000", barangays: ["Ayala","Baliwasan","Baluno","Boalan","Buenavista","Cabatangan","Calarian","Campo Islam","Campo Uno","Canelar","Capisan","Cawa-Cawa","Culianan","Dita","Divisoria","Dulian (Upper Bunguiao)","Guiwan","Kasanyangan","La Paz","Labuan","Lamisahan","Lapakan","Latawan","Licomo","Ligaw","Lumayang","Lumbangan","Lunzuran","Maasin","Mampang","Manicahan","Mercedes","Pababag","Pagatpat","Pamucutan","Panubigan","Pasonanca","Putik","San Jose Cawa-Cawa","San Jose Gusu","San Roque","Santa Barbara","Santa Catalina","Santa Maria","Santo Niño","Sibulao","Sinunoc","Taluksangay","Tawagan Norte","Tawagan Sur","Taytay","Tetuan","Tigbalabag","Tolosa","Tugbungan","Tumaga","Tukuran","Tulungatung","Upper Calarian","Vitali","Zambowood","Other"] },
            "Zamboanga del Norte (Dipolog City)":   { postal: "7100", barangays: [] },
            "Dapitan City":                         { postal: "7101", barangays: [] },
            "Pagadian City":                        { postal: "7016", barangays: [] },
            "Zamboanga del Sur (Molave)":           { postal: "7023", barangays: [] },
            "Ipil (Zamboanga Sibugay)":             { postal: "7001", barangays: [] },
            "Isabela City (Basilan)":               { postal: "7300", barangays: [] }
        }
    },
    "BARMM (Bangsamoro)": {
        shippingZone: 1,
        cities: {
            "Jolo (Sulu)":              { postal: "7400", barangays: [] },
            "Bongao (Tawi-Tawi)":       { postal: "7500", barangays: [] },
            "Cotabato City":            { postal: "9600", barangays: [] },
            "Marawi City":              { postal: "9700", barangays: [] },
            "Lamitan City (Basilan)":   { postal: "7302", barangays: [] },
            "Parang (Maguindanao)":     { postal: "9607", barangays: [] }
        }
    },
    "Davao Region (Region XI)": {
        shippingZone: 2,
        cities: {
            "Davao City":   { postal: "8000", barangays: [] },
            "Digos City":   { postal: "8002", barangays: [] },
            "Tagum City":   { postal: "8100", barangays: [] },
            "Panabo City":  { postal: "8105", barangays: [] },
            "Mati City":    { postal: "8200", barangays: [] }
        }
    },
    "SOCCSKSARGEN (Region XII)": {
        shippingZone: 2,
        cities: {
            "General Santos City":      { postal: "9500", barangays: [] },
            "Koronadal City":           { postal: "9506", barangays: [] },
            "Kidapawan City":           { postal: "9400", barangays: [] },
            "Tacurong City":            { postal: "9800", barangays: [] },
            "Isulan (Sultan Kudarat)":  { postal: "9805", barangays: [] }
        }
    },
    "Northern Mindanao (Region X)": {
        shippingZone: 2,
        cities: {
            "Cagayan de Oro City":  { postal: "9000", barangays: [] },
            "Iligan City":          { postal: "9200", barangays: [] },
            "Ozamiz City":          { postal: "7200", barangays: [] },
            "Oroquieta City":       { postal: "7207", barangays: [] },
            "Gingoog City":         { postal: "9014", barangays: [] },
            "Malaybalay City":      { postal: "8700", barangays: [] },
            "Valencia City":        { postal: "8709", barangays: [] }
        }
    },
    "Caraga (Region XIII)": {
        shippingZone: 2,
        cities: {
            "Butuan City":  { postal: "8600", barangays: [] },
            "Surigao City": { postal: "8400", barangays: [] },
            "Bayugan City": { postal: "8502", barangays: [] },
            "Tandag City":  { postal: "8300", barangays: [] }
        }
    },
    "Central Visayas (Region VII)": {
        shippingZone: 3,
        cities: {
            "Cebu City":        { postal: "6000", barangays: [] },
            "Mandaue City":     { postal: "6014", barangays: [] },
            "Lapu-Lapu City":   { postal: "6015", barangays: [] },
            "Dumaguete City":   { postal: "6200", barangays: [] },
            "Tagbilaran City":  { postal: "6300", barangays: [] }
        }
    },
    "Western Visayas (Region VI)": {
        shippingZone: 3,
        cities: {
            "Iloilo City":  { postal: "5000", barangays: [] },
            "Bacolod City": { postal: "6100", barangays: [] },
            "Roxas City":   { postal: "5800", barangays: [] },
            "Kalibo (Aklan)":{ postal: "5600", barangays: [] }
        }
    },
    "Eastern Visayas (Region VIII)": {
        shippingZone: 3,
        cities: {
            "Tacloban City":    { postal: "6500", barangays: [] },
            "Ormoc City":       { postal: "6541", barangays: [] },
            "Calbayog City":    { postal: "6710", barangays: [] },
            "Catbalogan City":  { postal: "6700", barangays: [] }
        }
    },
    "NCR (Metro Manila)": {
        shippingZone: 4,
        cities: {
            "Manila":               { postal: "1000", barangays: [] },
            "Quezon City":          { postal: "1100", barangays: [] },
            "Makati City":          { postal: "1200", barangays: [] },
            "Pasig City":           { postal: "1600", barangays: [] },
            "Taguig City":          { postal: "1630", barangays: [] },
            "Caloocan City":        { postal: "1400", barangays: [] },
            "Mandaluyong City":     { postal: "1550", barangays: [] },
            "Marikina City":        { postal: "1800", barangays: [] },
            "Parañaque City":       { postal: "1700", barangays: [] },
            "Las Piñas City":       { postal: "1750", barangays: [] },
            "Muntinlupa City":      { postal: "1770", barangays: [] },
            "Valenzuela City":      { postal: "1440", barangays: [] },
            "Malabon City":         { postal: "1470", barangays: [] },
            "Navotas City":         { postal: "1485", barangays: [] },
            "San Juan City":        { postal: "1500", barangays: [] },
            "Pasay City":           { postal: "1300", barangays: [] }
        }
    },
    "CALABARZON (Region IV-A)": {
        shippingZone: 4,
        cities: {
            "Antipolo City":    { postal: "1870", barangays: [] },
            "Lucena City":      { postal: "4301", barangays: [] },
            "Calamba City":     { postal: "4027", barangays: [] },
            "Santa Rosa City":  { postal: "4026", barangays: [] },
            "Batangas City":    { postal: "4200", barangays: [] },
            "Lipa City":        { postal: "4217", barangays: [] },
            "Cavite City":      { postal: "4100", barangays: [] },
            "Bacoor City":      { postal: "4102", barangays: [] }
        }
    },
    "Central Luzon (Region III)": {
        shippingZone: 4,
        cities: {
            "San Fernando City (Pampanga)": { postal: "2000", barangays: [] },
            "Angeles City":     { postal: "2009", barangays: [] },
            "Olongapo City":    { postal: "2200", barangays: [] },
            "Malolos City":     { postal: "3000", barangays: [] },
            "Cabanatuan City":  { postal: "3100", barangays: [] },
            "Tarlac City":      { postal: "2300", barangays: [] }
        }
    },
    "Ilocos Region (Region I)": {
        shippingZone: 5,
        cities: {
            "Laoag City":       { postal: "2900", barangays: [] },
            "Vigan City":       { postal: "2700", barangays: [] },
            "San Fernando City (La Union)": { postal: "2500", barangays: [] },
            "Dagupan City":     { postal: "2400", barangays: [] }
        }
    },
    "Cagayan Valley (Region II)": {
        shippingZone: 5,
        cities: {
            "Tuguegarao City":  { postal: "3500", barangays: [] },
            "Santiago City":    { postal: "3311", barangays: [] },
            "Ilagan City":      { postal: "3300", barangays: [] }
        }
    },
    "CAR (Cordillera)": {
        shippingZone: 5,
        cities: {
            "Baguio City":  { postal: "2600", barangays: [] },
            "Tabuk City":   { postal: "3800", barangays: [] }
        }
    },
    "Bicol Region (Region V)": {
        shippingZone: 5,
        cities: {
            "Legazpi City": { postal: "4500", barangays: [] },
            "Naga City":    { postal: "4400", barangays: [] },
            "Sorsogon City":{ postal: "4700", barangays: [] }
        }
    },
    "MIMAROPA (Region IV-B)": {
        shippingZone: 5,
        cities: {
            "Puerto Princesa City": { postal: "5300", barangays: [] },
            "Calapan City":         { postal: "5200", barangays: [] },
            "Romblon (Romblon)":    { postal: "5500", barangays: [] }
        }
    }
};

const SHIPPING_ZONES = { 0: 0, 1: 100, 2: 180, 3: 250, 4: 300, 5: 350 };
const BASE_ORDER_PRICE = {{ $baseOrderPrice }};

function payPopulateRegion() {
    const sel = document.getElementById('payRegion');
    if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = '<option value="">-- Select Region --</option>';
    for (const r in PH_LOCATIONS) {
        const opt = new Option(r, r);
        if (r === cur) opt.selected = true;
        sel.appendChild(opt);
    }
    payPopulateCity();
}

function payPopulateCity() {
    const regionSel = document.getElementById('payRegion');
    const citySel   = document.getElementById('payCity');
    if (!regionSel || !citySel) return;
    const region = regionSel.value;
    citySel.innerHTML = '<option value="">-- Select City --</option>';
    if (region && PH_LOCATIONS[region]) {
        for (const city in PH_LOCATIONS[region].cities) {
            citySel.appendChild(new Option(city, city));
        }
    }
    payPopulateBarangay();
}

function payPopulateBarangay() {
    const regionSel  = document.getElementById('payRegion');
    const citySel    = document.getElementById('payCity');
    const container  = document.getElementById('payBarangayContainer');
    if (!regionSel || !citySel || !container) return;
    const region = regionSel.value;
    const city   = citySel.value;
    const cls = 'dropdown-field';

    if (region && city && PH_LOCATIONS[region] && PH_LOCATIONS[region].cities[city]) {
        const barangays = PH_LOCATIONS[region].cities[city].barangays;
        if (barangays.length > 0) {
            const sel = document.createElement('select');
            sel.id = 'payBarangay'; sel.name = 'barangay'; sel.className = cls;
            sel.innerHTML = '<option value="">-- Select Barangay --</option>';
            barangays.forEach(b => sel.appendChild(new Option(b, b)));
            container.innerHTML = ''; container.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = 'payBarangay'; inp.name = 'barangay';
            inp.placeholder = 'Enter your barangay / district name'; inp.className = cls;
            container.innerHTML = ''; container.appendChild(inp);
        }
    } else {
        const sel = document.createElement('select');
        sel.id = 'payBarangay'; sel.name = 'barangay'; sel.className = cls;
        sel.innerHTML = '<option value="">-- Select Barangay --</option>';
        container.innerHTML = ''; container.appendChild(sel);
    }
}

function payCalcShipping() {
    const regionSel = document.getElementById('payRegion');
    const citySel   = document.getElementById('payCity');
    if (!regionSel) return;

    const region = regionSel.value;
    const city   = citySel ? citySel.value : '';

    let fee = null;
    if (region && PH_LOCATIONS[region]) {
        const zone = PH_LOCATIONS[region].shippingZone;
        fee = SHIPPING_ZONES[zone] ?? 350;
    }

    // Update hidden inputs
    const hiddenFee      = document.getElementById('hiddenShippingFee');
    const hiddenCity     = document.getElementById('hiddenDeliveryCity');
    const hiddenProvince = document.getElementById('hiddenDeliveryProvince');
    if (hiddenFee)      hiddenFee.value      = fee ?? 0;
    if (hiddenCity)     hiddenCity.value     = city;
    if (hiddenProvince) hiddenProvince.value = region;

    // Update shipping display in order summary
    const summaryDisplay = document.getElementById('payShippingDisplay');
    const feeResultVal   = document.getElementById('shippingFeeResultVal');
    const grandTotalEl   = document.getElementById('payGrandTotal');

    let feeText = '— select region below';
    let feeTextResult = '—';
    if (fee !== null) {
        feeText      = fee === 0 ? '<span class="text-green-600 font-bold text-lg">FREE</span>' : '<span class="font-bold text-lg text-gray-900">₱' + fee.toFixed(2) + '</span>';
        feeTextResult= fee === 0 ? 'FREE' : '₱' + fee.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = '₱' + (BASE_ORDER_PRICE + fee).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    if (summaryDisplay) summaryDisplay.innerHTML = feeText;
    if (feeResultVal)   feeResultVal.textContent = feeTextResult;

    // Indicate shipping zone in the result box
    const resultBox = document.getElementById('shippingFeeResult');
    if (resultBox && fee !== null) {
        const colors = { 0:'border-green-400 bg-green-50', 100:'border-blue-400 bg-blue-50', 180:'border-indigo-400 bg-indigo-50', 250:'border-purple-400 bg-purple-50', 300:'border-orange-400 bg-orange-50', 350:'border-red-400 bg-red-50' };
        resultBox.className = 'mt-5 p-4 rounded-xl border-2 flex items-center justify-between ' + (colors[fee] || 'border-gray-200 bg-gray-50');
    }

    payPopulateBarangay();
}

// Pre-select existing region/city if stored
document.addEventListener('DOMContentLoaded', function () {
    payPopulateRegion();

    const storedProvince = @json($storedProvince);
    const storedCity     = @json($storedCity);

    if (storedProvince) {
        const regionSel = document.getElementById('payRegion');
        if (regionSel) {
            regionSel.value = storedProvince;
            payPopulateCity();
        }
    }
    if (storedCity) {
        const citySel = document.getElementById('payCity');
        if (citySel) {
            citySel.value = storedCity;
            payCalcShipping();
            payPopulateBarangay();
        }
    }
    if (storedProvince || storedCity) payCalcShipping();
});
</script>
@endsection
