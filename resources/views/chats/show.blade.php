@extends('layouts.app')

@section('title', $chat->subject)

@section('content')
<style>
    .chat-container { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); }
    .chat-header { background: white; border-bottom: 2px solid #f3f4f6; }
    .chat-header-sticky {
        position: sticky;
        top: 84px;
        z-index: 30;
        backdrop-filter: blur(6px);
        background: rgba(255, 255, 255, 0.95);
    }
    .message-user { 
        background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%); 
        color: white;
        box-shadow: 0 2px 8px rgba(139, 0, 0, 0.15);
    }
    .message-support { 
        background: white;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    .status-badge {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    .status-closed {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .btn-maroon {
        background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%);
        color: white;
        transition: all 0.3s ease;
    }
    .btn-maroon:hover {
        background: linear-gradient(135deg, #6B0000 0%, #5B0000 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 0, 0, 0.25);
    }
    .btn-outline {
        background: white;
        color: #8B0000;
        border: 2px solid #8B0000;
    }
    .btn-outline:hover {
        background: #8B0000;
        color: white;
    }

    @media (max-width: 768px) {
        .chat-header-sticky {
            top: 76px;
        }
    }
</style>
<div class="min-h-screen chat-container py-5">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Header with Chat Info -->
        <div class="chat-header-sticky rounded-2xl shadow-lg border border-gray-200 p-3 md:p-4 mb-4">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-[#8B0000] text-sm font-semibold mb-2 transition hover:gap-3 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Chats
                    </a>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-8 h-8 bg-gradient-to-br from-[#8B0000] to-[#6B0000] rounded-lg flex items-center justify-center text-white text-xs">
                            <i class="fas fa-comments"></i>
                        </span>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900 truncate">{{ $chat->subject }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 md:gap-4">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold {{ $chat->status === 'closed' ? 'status-closed' : 'status-badge' }}">
                            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $chat->status === 'closed' ? 'bg-red-600' : 'bg-green-600' }}"></span>
                            {{ ucfirst($chat->status) }}
                        </span>
                        <span class="text-gray-600 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Started {{ $chat->created_at?->diffForHumans() ?? 'N/A' }}
                        </span>
                        <span class="text-gray-600 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            {{ count($messages) }} message{{ count($messages) !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                @if($chat->status !== 'closed')
                    <form action="{{ route('chats.close', $chat) }}" method="POST" onsubmit="return confirm('Close this chat? You won\'t be able to send new messages.');">
                        @csrf
                        @if(request()->get('auth_token') || session('auth_token'))
                            <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') ?? session('auth_token') }}">
                        @endif
                        <button type="submit" class="btn-outline px-4 py-1.5 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2 shadow-sm hover:shadow-md whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Close Chat
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <!-- Error/Success Message Display -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 mb-6 shadow-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-red-900 font-bold text-lg mb-1">❌ Order Creation Failed!</h3>
                        <p class="text-red-800 font-mono text-sm break-all whitespace-pre-wrap">{{ session('error') }}</p>
                        <p class="text-red-700 text-xs mt-2">⚠️ Please screenshot this and send to developer!</p>
                    </div>
                </div>
            </div>
        @endif
        
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                <p class="text-green-800 font-semibold">✓ {{ session('success') }}</p>
            </div>
        @endif

        <!-- Messages Container -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col" style="height: 600px;">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50" id="messagesContainer">
                @forelse($messages as $message)
                    <div class="flex {{ $message->sender_type === 'user' ? 'justify-end' : 'justify-start' }} animate-fadeIn">
                        <div class="flex flex-col {{ $message->sender_type === 'user' ? 'items-end' : 'items-start' }} max-w-xs">
                            <p class="text-xs font-semibold text-gray-500 mb-2 px-2">
                                {{ $message->sender_type === 'user' ? 'You' : 'Support Team' }}
                            </p>
                            <div class="rounded-2xl px-5 py-3 {{ $message->sender_type === 'user' ? 'message-user' : 'message-support' }}">
                                @if($message->image_path)
                                    @php
                                        // Handle different image path formats
                                        $imagePath = $message->image_path;
                                        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                            // Full URL (Cloudinary or external)
                                            $chatImageUrl = $imagePath;
                                        } elseif (str_starts_with($imagePath, 'data:image')) {
                                            // Base64 data URL
                                            $chatImageUrl = $imagePath;
                                        } elseif (str_starts_with($imagePath, 'storage/')) {
                                            // Storage path
                                            $chatImageUrl = asset($imagePath);
                                        } elseif (str_starts_with($imagePath, 'chat_images/')) {
                                            // Old chat images format
                                            $chatImageUrl = asset('storage/' . $imagePath);
                                        } else {
                                            // Default fallback - try as asset
                                            $chatImageUrl = asset('storage/' . $imagePath);
                                        }
                                    @endphp
                                    <a href="{{ $chatImageUrl }}" target="_blank" class="block mb-3">
                                        <img src="{{ $chatImageUrl }}" 
                                             alt="Attached image" 
                                             class="max-w-full rounded-xl shadow-md border-2 {{ $message->sender_type === 'user' ? 'border-white/30' : 'border-gray-200' }}"
                                             style="max-height: 250px; object-fit: contain; cursor: pointer; display: block;"
                                             loading="lazy"
                                             onerror="console.error('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden items-center gap-2 px-4 py-3 bg-red-50 rounded-lg text-red-700 text-sm">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            🖼️ Image unavailable (URL: <code style="font-size: 10px; word-break: break-all;">{{ substr($chatImageUrl, 0, 50) }}...</code>)
                                        </div>
                                    </a>
                                @endif
                                
                                {{-- Display reference images for quotes --}}
                                @if($message->sender_type === 'admin' && str_contains($message->message, 'PRICE QUOTE') && !empty($message->reference_images))
                                    <div class="mt-3 pt-3 border-t border-gray-200/50">
                                        <p class="text-xs font-semibold text-gray-600 mb-2 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Your Design Reference:
                                        </p>
                                        <div class="grid gap-2 {{ count($message->reference_images) > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                                            @foreach($message->reference_images as $refImg)
                                                <a href="{{ $refImg }}" target="_blank" class="block">
                                                    <img src="{{ $refImg }}" 
                                                         alt="Design reference" 
                                                         class="w-full rounded-lg shadow-md border-2 border-gray-300 hover:border-[#8B0000] transition-all"
                                                         style="max-height: 180px; object-fit: cover; cursor: pointer;"
                                                         loading="lazy"
                                                         onerror="this.style.display='none';">
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <p class="break-words leading-relaxed whitespace-pre-line">{{ $message->message }}</p>
                                
                                {{-- Display custom order details form if this is a form_request --}}
                                @if(isset($message->message_type) && $message->message_type === 'form_request' && !empty($message->form_data))
                                    @php
                                        // Check if customer has already responded to this form
                                        $hasResponded = \App\Models\ChatMessage::where('chat_id', $chat->id)
                                            ->where('sender_type', 'user')
                                            ->where('message_type', 'form_response')
                                            ->where('form_data->original_message_id', $message->id)
                                            ->exists();
                                    @endphp
                                    
                                    @if(!$hasResponded)
                                        <form action="{{ route('chats.submit-form-response', $chat) }}" method="POST" class="mt-4 space-y-3" id="detailsForm{{ $message->id }}">
                                            @csrf
                                            <input type="hidden" name="original_message_id" value="{{ $message->id }}">
                                            
                                            @foreach($message->form_data['fields'] as $field)
                                                <div class="form-group">
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                        {{ $field['label'] }}
                                                        @if($field['required'])
                                                            <span class="text-red-500">*</span>
                                                        @endif
                                                    </label>
                                                    
                                                    @if($field['type'] === 'textarea')
                                                        <textarea 
                                                            name="{{ $field['name'] }}" 
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 text-sm"
                                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                                            rows="3"
                                                            {{ $field['required'] ? 'required' : '' }}></textarea>
                                                    @elseif($field['type'] === 'number')
                                                        <input 
                                                            type="number" 
                                                            name="{{ $field['name'] }}" 
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 text-sm"
                                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                                            min="{{ $field['min'] ?? 0 }}"
                                                            step="{{ $field['step'] ?? 1 }}"
                                                            {{ $field['required'] ? 'required' : '' }}>
                                                    @else
                                                        <input 
                                                            type="text" 
                                                            name="{{ $field['name'] }}" 
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 text-sm"
                                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                                            {{ $field['required'] ? 'required' : '' }}>
                                                    @endif
                                                </div>
                                            @endforeach
                                            
                                            <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                                Submit Details
                                            </button>
                                        </form>
                                    @else
                                        <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                                            ✅ You've already submitted the details for this request.
                                        </div>
                                    @endif
                                @endif
                            </div>
                            
                            @if($message->sender_type === 'admin' && str_contains($message->message, 'PRICE QUOTE'))
                                @php
                    // Check if THIS specific quote has been responded to
                    $hasResponse = \App\Models\ChatMessage::where('chat_id', $chat->id)
                        ->where('sender_type', 'user')
                        ->where('created_at', '>', $message->created_at)
                        ->where(function($query) {
                            $query->where('message', 'like', '%accepted the price quote%')
                                  ->orWhere('message', 'like', '%declined the price quote%');
                        })
                        ->exists();
                    
                    // Get user's default address and all addresses
                    $userDefaultAddress = auth()->check() ? \App\Models\UserAddress::where('user_id', auth()->id())
                                        ->where('is_default', true)
                                        ->first() : null;
                                    
                                    $userAddresses = auth()->check() ? \App\Models\UserAddress::where('user_id', auth()->id())
                                        ->orderBy('is_default', 'desc')
                                        ->orderBy('created_at', 'desc')
                                        ->get() : collect();
                                    
                                    // Parse quoted price from message (extract Total: ₱1,500.00)
                                    $quotedPrice = 0;
                                    if (preg_match('/Total:\s*₱?([\d,]+\.?\d*)/i', $message->message, $matches)) {
                                        $quotedPrice = floatval(str_replace(',', '', $matches[1]));
                                    }

                                    // Parse price breakdown line items from the quote message
                                    $quoteBreakdownItems = [];
                                    if (preg_match('/Material Cost:\s*₱?([\d,]+\.?\d*)/i', $message->message, $m)) {
                                        $quoteBreakdownItems['Material Cost'] = floatval(str_replace(',', '', $m[1]));
                                    }
                                    if (preg_match('/Pattern Fee:\s*₱?([\d,]+\.?\d*)/i', $message->message, $m)) {
                                        $quoteBreakdownItems['Pattern Fee'] = floatval(str_replace(',', '', $m[1]));
                                    }
                                    if (preg_match('/Labor Cost:\s*₱?([\d,]+\.?\d*)/i', $message->message, $m)) {
                                        $quoteBreakdownItems['Labor Cost'] = floatval(str_replace(',', '', $m[1]));
                                    }
                                    if (preg_match('/Discount:\s*-?₱?([\d,]+\.?\d*)/i', $message->message, $m)) {
                                        $quoteBreakdownItems['Discount'] = floatval(str_replace(',', '', $m[1]));
                                    }
                                    
                                    // Calculate shipping fee based on canonical custom-order zone rules
                                    $shippingFee = 0;
                                    $shippingLabel = '';
                                    
                                    if ($userDefaultAddress) {
                                        $cityLower = strtolower($userDefaultAddress->city ?? '');
                                        $regionLower = strtolower($userDefaultAddress->province ?? '');
                                        $addrLower = strtolower(implode(' ', array_filter([
                                            $userDefaultAddress->street_name ?? null,
                                            $userDefaultAddress->barangay ?? null,
                                            $userDefaultAddress->city ?? null,
                                            $userDefaultAddress->province ?? ($userDefaultAddress->region ?? null),
                                        ])));
                                        $haystack = trim($addrLower . ' ' . $cityLower . ' ' . $regionLower);

                                        if (str_contains($haystack, 'zamboanga') ||
                                            str_contains($regionLower, 'barmm') || str_contains($regionLower, 'bangsamoro') ||
                                            in_array($cityLower, ['dipolog city', 'dapitan city', 'pagadian city', 'isabela city', 'jolo', 'bongao', 'cotabato city', 'marawi city', 'lamitan'])) {
                                            $shippingFee = 100;
                                            $shippingLabel = 'Zamboanga/BARMM';
                                        } elseif (str_contains($haystack, 'mindanao') || str_contains($haystack, 'davao') ||
                                                  str_contains($haystack, 'soccsksargen') || str_contains($haystack, 'caraga') ||
                                                  str_contains($haystack, 'northern mindanao') || str_contains($haystack, 'cagayan de oro') ||
                                                  str_contains($haystack, 'general santos')) {
                                            $shippingFee = 180;
                                            $shippingLabel = 'Mindanao';
                                        } elseif (str_contains($haystack, 'visayas') || str_contains($haystack, 'cebu') ||
                                                  str_contains($haystack, 'iloilo') || str_contains($haystack, 'bacolod') ||
                                                  str_contains($haystack, 'tacloban') || str_contains($haystack, 'leyte')) {
                                            $shippingFee = 250;
                                            $shippingLabel = 'Visayas';
                                        } elseif (str_contains($haystack, 'ncr') || str_contains($haystack, 'metro manila') ||
                                                  str_contains($haystack, 'manila') || str_contains($haystack, 'calabarzon') ||
                                                  str_contains($haystack, 'central luzon')) {
                                            $shippingFee = 300;
                                            $shippingLabel = 'NCR/Near Luzon';
                                        } else {
                                            $shippingFee = 350;
                                            $shippingLabel = 'Remote Area';
                                        }
                                    }
                                    
                                    $totalWithShipping = $quotedPrice + $shippingFee;
                                    $defaultQuoteDeliveryType = $userDefaultAddress ? 'delivery' : 'pickup';
                                @endphp
                                
                                @if(!$hasResponse)
                                    {{-- Shipping Fee & Total Breakdown --}}
                                    <div id="quoteBreakdown-{{ $message->id }}" data-quoted="{{ $quotedPrice }}" data-delivery-fee="{{ $shippingFee }}" data-shipping-label="{{ $shippingLabel }}" data-has-default-address="{{ $userDefaultAddress ? '1' : '0' }}" class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4 mt-3 mx-2">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 bg-blue-400 rounded-full p-2">
                                                <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-gray-900 text-sm mb-3">💰 Payment Breakdown</h4>

                                                <div class="bg-white border border-blue-200 rounded-lg p-3 mb-3">
                                                    <p class="text-xs font-semibold text-gray-900 mb-2">Delivery Option</p>
                                                    <div class="flex items-center gap-4">
                                                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                                                            <input type="radio" name="quote_delivery_type_{{ $message->id }}" value="delivery" {{ $defaultQuoteDeliveryType === 'delivery' ? 'checked' : '' }} onchange="updateQuoteDeliveryType('{{ $message->id }}')">
                                                            Deliver
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                                                            <input type="radio" name="quote_delivery_type_{{ $message->id }}" value="pickup" {{ $defaultQuoteDeliveryType === 'pickup' ? 'checked' : '' }} onchange="updateQuoteDeliveryType('{{ $message->id }}')">
                                                            Pick up
                                                        </label>
                                                    </div>
                                                    <p id="quoteDeliveryNote-{{ $message->id }}" class="text-xs text-gray-500 mt-2"></p>
                                                </div>
                                                
                                                @if($userDefaultAddress)
                                                    {{-- Delivery Address --}}
                                                    <div id="quoteDeliveryAddress-{{ $message->id }}" class="bg-white border border-blue-200 rounded-lg p-3 mb-3">
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs font-semibold text-gray-900 mb-0.5">Delivering to:</p>
                                                                <p class="text-xs text-gray-700 break-words">{{ $userDefaultAddress->formatted_address }}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="quotePickupAddress-{{ $message->id }}" class="hidden bg-white border border-blue-200 rounded-lg p-3 mb-3">
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-[#8B0000] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.05 11a7 7 0 1113.9 0M12 13v8m0 0l-3-3m3 3l3-3"/>
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs font-semibold text-gray-900 mb-0.5">Pickup Location:</p>
                                                                <p class="text-xs text-gray-700 break-words">Tuwas Yakan Weaving Center, Yakan Village, Upper Calarian, Labuan-Limpapa Road, National Road, Zamboanga City, Philippines 7000</p>
                                                                <p class="text-[11px] text-gray-500 mt-1">Mon-Sat 8:00 AM - 6:00 PM</p>
                                                                <p class="text-[11px] text-gray-500">Contact: 0935 569 0272</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Price Breakdown --}}
                                                    <div class="bg-white border border-blue-200 rounded-lg p-3 mb-2">
                                                        <div class="space-y-2">
                                                            @if(count($quoteBreakdownItems) > 0)
                                                                <p class="text-xs font-bold text-gray-700 mb-1">Price Breakdown:</p>
                                                                @foreach($quoteBreakdownItems as $label => $amount)
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="text-gray-600">{{ $label }}:</span>
                                                                        <span class="font-semibold {{ $label === 'Discount' ? 'text-green-600' : 'text-gray-900' }}">{{ $label === 'Discount' ? '-' : '' }}₱{{ number_format($amount, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                                <div class="border-t border-gray-200 pt-1 mt-1">
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="font-semibold text-gray-700">Subtotal:</span>
                                                                        <span class="font-bold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="flex justify-between items-center text-sm">
                                                                    <span class="text-gray-700">Quoted Price:</span>
                                                                    <span class="font-semibold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                                </div>
                                                            @endif
                                                            <div class="flex justify-between items-center text-sm">
                                                                <span class="text-gray-700">Shipping Fee:</span>
                                                                <span id="quoteShippingValue-{{ $message->id }}" class="font-semibold {{ $shippingFee == 0 ? 'text-green-600' : 'text-blue-700' }}">
                                                                    ₱{{ number_format($shippingFee, 2) }} @if($shippingLabel)<span class="text-xs text-gray-500">({{ $shippingLabel }})</span>@endif
                                                                </span>
                                                            </div>
                                                            <div class="border-t-2 border-dashed border-gray-300 pt-2 mt-2">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-base font-bold text-gray-900">TOTAL TO PAY:</span>
                                                                    <span id="quoteTotalValue-{{ $message->id }}" class="text-xl font-bold text-green-600">₱{{ number_format($defaultQuoteDeliveryType === 'pickup' ? $quotedPrice : $totalWithShipping, 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <button id="quoteChangeAddressBtn-{{ $message->id }}" onclick="openAddressModal()" type="button" class="inline-flex items-center gap-2 text-xs font-semibold text-[#8B0000] hover:text-[#6B0000] transition-colors mt-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Change delivery address
                                                    </button>
                                                @else
                                                    {{-- No Address Warning --}}
                                                    <div id="quoteNoAddressWarning-{{ $message->id }}" class="bg-white border-2 border-red-300 rounded-lg p-4 mb-3">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                            </svg>
                                                            <p class="text-sm font-bold text-red-900">No delivery address set</p>
                                                        </div>
                                                        <p class="text-xs text-gray-700 mb-3">Please add your delivery address to see the shipping fee and total amount to pay.</p>
                                                        <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-2">
                                                            @if(count($quoteBreakdownItems) > 0)
                                                                <p class="text-xs font-bold text-gray-700 mb-1">Price Breakdown:</p>
                                                                @foreach($quoteBreakdownItems as $label => $amount)
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="text-gray-600">{{ $label }}:</span>
                                                                        <span class="font-semibold {{ $label === 'Discount' ? 'text-green-600' : 'text-gray-900' }}">{{ $label === 'Discount' ? '-' : '' }}₱{{ number_format($amount, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                                <div class="border-t border-yellow-200 pt-1 mt-1">
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="font-semibold text-gray-700">Subtotal:</span>
                                                                        <span class="font-bold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="flex justify-between items-center text-sm">
                                                                    <span class="text-gray-700">Quoted Price:</span>
                                                                    <span class="font-semibold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                                </div>
                                                            @endif
                                                            <div class="flex justify-between items-center text-sm mt-1">
                                                                <span class="text-gray-700">Shipping Fee:</span>
                                                                <span id="quoteShippingValue-{{ $message->id }}" class="text-xs text-gray-500 italic">Add address to calculate</span>
                                                            </div>
                                                            <div class="flex justify-between items-center text-sm mt-1 border-t border-yellow-200 pt-1">
                                                                <span class="text-gray-700 font-semibold">Total To Pay:</span>
                                                                <span id="quoteTotalValue-{{ $message->id }}" class="font-semibold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="quotePickupAddress-{{ $message->id }}" class="hidden bg-white border border-blue-200 rounded-lg p-3 mb-3">
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-[#8B0000] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.05 11a7 7 0 1113.9 0M12 13v8m0 0l-3-3m3 3l3-3"/>
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs font-semibold text-gray-900 mb-0.5">Pickup Location:</p>
                                                                <p class="text-xs text-gray-700 break-words">Tuwas Yakan Weaving Center, Yakan Village, Upper Calarian, Labuan-Limpapa Road, National Road, Zamboanga City, Philippines 7000</p>
                                                                <p class="text-[11px] text-gray-500 mt-1">Mon-Sat 8:00 AM - 6:00 PM</p>
                                                                <p class="text-[11px] text-gray-500">Contact: 0935 569 0272</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <a href="{{ route('addresses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#8B0000] hover:bg-[#6B0000] text-white text-sm font-semibold rounded-lg transition-all shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Add Delivery Address Now
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Accept/Decline Buttons --}}
                                    <div class="flex gap-2 mt-3 px-2">
                                        <form action="{{ route('chats.respond-quote', $chat) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="response" value="accepted">
                                            <input type="hidden" name="quote_message_id" value="{{ $message->id }}">
                                            <input type="hidden" id="acceptedDeliveryType-{{ $message->id }}" name="delivery_type" value="{{ $defaultQuoteDeliveryType }}">
                                            <button type="submit" onclick="return validateQuoteAcceptance('{{ $message->id }}', {{ $userDefaultAddress ? 'true' : 'false' }})" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                ✓ Accept Price
                                            </button>
                                        </form>
                                        <form action="{{ route('chats.respond-quote', $chat) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="response" value="declined">
                                            <input type="hidden" name="quote_message_id" value="{{ $message->id }}">
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                ✗ Decline
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                            
                            {{-- Payment Method Selection after Quote Accepted --}}
                            @if($message->sender_type === 'user' && (str_contains(strtolower($message->message), 'accepted the price quote') || str_contains(strtolower($message->message), 'customer accepted')))
                                @php
                                    // Find the custom order created for this chat
                                    $chatOrder = \App\Models\CustomOrder::where('chat_id', $chat->id)
                                        ->where('user_id', auth()->id())
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                                    $shippingFee = $chatOrder ? (float) ($chatOrder->shipping_fee ?? 0) : 0;
                                    $acceptedTotal = $chatOrder
                                        ? ((float) ($chatOrder->final_price ?? ((float) ($chatOrder->estimated_price ?? 0) + $shippingFee)))
                                        : 0;
                                @endphp
                                
                                @if($chatOrder)
                                    {{-- Show payment summary and method buttons if payment hasn't been chosen yet --}}
                                    @if(in_array($chatOrder->status, ['price_quoted', 'approved']) && $chatOrder->payment_status === 'pending' && empty($chatOrder->payment_method))
                                        <div class="mt-4 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-5 border-2 border-green-300 shadow-lg max-w-md">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-green-900 text-lg">Quote Accepted!</h3>
                                                    <p class="text-xs text-green-700">Custom Order {{ $chatOrder->order_ref ?? '#'.$chatOrder->id }}</p>
                                                </div>
                                            </div>
                                            
                                            {{-- Payment Summary --}}
                                            <div class="bg-white rounded-xl p-4 mb-4 border border-green-200">
                                                <p class="text-xs font-semibold text-gray-600 mb-2">PAYMENT BREAKDOWN</p>
                                                <div class="space-y-2 text-sm">
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-700">Quoted Price:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($chatOrder->estimated_price, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-700">Shipping Fee:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($shippingFee, 2) }}</span>
                                                    </div>
                                                    <div class="border-t border-gray-200 pt-2 flex justify-between">
                                                        <span class="font-bold text-gray-900">TOTAL TO PAY:</span>
                                                        <span class="font-bold text-green-600 text-lg">₱{{ number_format($acceptedTotal, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            @php
                                                $proceedPaymentUrl = route('custom_orders.payment', $chatOrder) . (request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '');
                                            @endphp
                                            <a href="{{ $proceedPaymentUrl }}" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#8B0000] to-[#6B0000] hover:from-[#6B0000] hover:to-[#500000] text-white font-bold py-3 px-4 rounded-xl transition shadow-md hover:shadow-lg">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                                <span>Proceed to Payment</span>
                                            </a>
                                        </div>
                                    @endif
                                    
                                    {{-- Payment Details Display (after method selected) --}}
                                    @if($chatOrder && $chatOrder->status === 'approved' && $chatOrder->payment_status !== 'paid' && !empty($chatOrder->payment_method))
                                        <div class="mt-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-5 shadow-md border-2 border-blue-200">
                                            {{-- Order Reference --}}
                                            <div class="mb-4 pb-3 border-b border-blue-200">
                                                <p class="text-xs font-semibold text-gray-600 mb-1">Custom Order:</p>
                                                <p class="text-lg font-bold text-gray-900">{{ $chatOrder->display_ref }}</p>
                                            </div>
                                            
                                            @if($chatOrder->payment_method === 'bank_transfer')
                                                {{-- Bank Transfer Payment Details --}}
                                                <div class="bg-white/70 backdrop-blur rounded-xl p-4 mb-4">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <span class="text-2xl">🏦</span>
                                                        <h4 class="text-lg font-bold text-gray-900">Bank Transfer Payment</h4>
                                                    </div>
                                                    
                                                    <div class="space-y-2">
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Bank Name:</span>
                                                            <span class="font-bold text-gray-900">{{ \App\Models\SystemSetting::get('bank_name', '—') }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Account Number:</span>
                                                            <span class="font-bold text-green-600 text-lg">{{ \App\Models\SystemSetting::get('bank_account_number', '—') }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Account Name:</span>
                                                            <span class="font-bold text-gray-900">{{ \App\Models\SystemSetting::get('bank_account_name', 'Tuwas Yakan') }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-100 p-3 rounded-lg border-2 border-green-300">
                                                            <span class="text-sm font-bold text-gray-800">Amount to Pay:</span>
                                                            <span class="font-bold text-green-700 text-xl">₱{{ number_format($acceptedTotal, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                        <p class="text-xs font-semibold text-yellow-800 mb-1">📝 Instructions:</p>
                                                        <ol class="text-xs text-yellow-900 space-y-1 list-decimal list-inside">
                                                            <li>Go to your bank or use online banking</li>
                                                            <li>Transfer to the account details above</li>
                                                            <li>Send the exact amount shown</li>
                                                            <li>Get your transfer receipt/confirmation</li>
                                                            <li>Take a photo of the receipt</li>
                                                            <li>Upload it below</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            @elseif($chatOrder->payment_method === 'maya')
                                                {{-- Maya Payment Details --}}
                                                @php
                                                    $mayaPaymentUrl = route('custom_orders.payment', $chatOrder) . (request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '');
                                                @endphp
                                                <div class="bg-white/70 backdrop-blur rounded-xl p-4 mb-4">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <span class="text-2xl">💳</span>
                                                        <h4 class="text-lg font-bold text-gray-900">Maya Checkout</h4>
                                                    </div>

                                                    <div class="bg-green-100 p-3 rounded-lg border-2 border-green-300 mb-3 flex justify-between items-center">
                                                        <span class="text-sm font-bold text-gray-800">Amount to Pay:</span>
                                                        <span class="font-bold text-green-700 text-xl">₱{{ number_format($acceptedTotal, 2) }}</span>
                                                    </div>

                                                    <p class="text-xs text-gray-700 mb-3">You will be redirected to Maya secure checkout to complete payment.</p>

                                                    <a href="{{ $mayaPaymentUrl }}" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md hover:shadow-lg">
                                                        <span>Proceed to Maya Payment</span>
                                                    </a>
                                                </div>
                                            @endif
                                            
                                            {{-- Receipt Upload Form --}}
                                            @if($chatOrder->payment_method === 'bank_transfer')
                                            <form action="{{ route('orders.upload_receipt', $chatOrder) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @if(request('auth_token'))
                                                    <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                                                @endif
                                                
                                                <div class="bg-white/70 backdrop-blur rounded-xl p-4">
                                                    <label class="block text-sm font-bold text-gray-800 mb-3">📸 Upload Payment Receipt</label>
                                                    
                                                    {{-- Image Preview --}}
                                                    <div id="receiptPreview" class="hidden mb-3">
                                                        <img id="receiptPreviewImg" src="" alt="Receipt Preview" class="max-h-48 max-w-full rounded-lg shadow-md border-2 border-gray-300 mx-auto">
                                                    </div>
                                                    
                                                    {{-- File Input --}}
                                                    <div class="flex items-center justify-center w-full mb-3">
                                                        <label for="receipt_upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                                <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                                </svg>
                                                                <p class="mb-1 text-sm text-gray-700"><span class="font-semibold">Click to upload</span> receipt</p>
                                                                <p class="text-xs text-gray-500">PNG, JPG, JPEG (MAX. 5MB)</p>
                                                            </div>
                                                            <input id="receipt_upload" name="payment_proof" type="file" accept="image/*" class="hidden" onchange="previewReceipt(event)" required />
                                                        </label>
                                                    </div>
                                                    
                                                    {{-- Submit Button --}}
                                                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md hover:shadow-lg">
                                                        ✅ Submit Payment Proof
                                                    </button>
                                                </div>
                                            </form>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    {{-- Payment Submitted Message --}}
                                    @if($chatOrder && $chatOrder->payment_receipt && $chatOrder->payment_status === 'paid')
                                        <div class="mt-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-5 shadow-md border-2 border-green-300">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="bg-green-500 rounded-full p-2">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-lg font-bold text-green-900">Payment Submitted!</h4>
                                                    <p class="text-sm text-green-700">We're verifying your payment. You'll be notified once confirmed.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white/60 rounded-lg p-3">
                                                <p class="text-xs font-semibold text-gray-600 mb-1">Custom Order:</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $chatOrder->display_ref }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    {{-- Debug: Order not found --}}
                                    <div class="mt-4 bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4">
                                        <p class="text-sm text-yellow-800">⚠️ <strong>Order not found.</strong> Please refresh the page or contact support.</p>
                                        <p class="text-xs text-yellow-700 mt-1">Chat ID: {{ $chat->id }} | User: {{ auth()->id() }}</p>
                                    </div>
                                @endif
                            @endif
                            
                            <p class="text-xs text-gray-400 mt-2 px-2">
                                {{ $message->created_at?->format('M d, Y H:i') ?? '' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="inline-flex p-4 bg-gray-100 rounded-2xl mb-4">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-gray-900 font-semibold">No messages yet</p>
                            <p class="text-gray-500 text-sm">Start the conversation below</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Message Form -->
        @if($chat->status !== 'closed')
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mt-6">
                <form id="chatMessageForm" action="{{ route('chats.send-message', $chat) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(request()->get('auth_token') || session('auth_token'))
                        <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') ?? session('auth_token') }}">
                    @endif

                    <div class="mb-5">
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Your Message
                        </label>
                        
                        <!-- Image Preview (shows above input when image is selected) -->
                        <div id="imagePreview" class="hidden mb-4 p-3 bg-gray-100 rounded-lg">
                            <p class="text-xs text-gray-600 mb-2 font-semibold">Selected Image:</p>
                            <div class="relative inline-block">
                                <img id="previewImg" src="" alt="Preview" class="max-h-52 max-w-full rounded-lg shadow-md border-2 border-gray-300" style="display: block;">
                                <button type="button" onclick="clearImage()" class="absolute -top-3 -right-3 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition border-2 border-white">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Horizontal Input Layout -->
                        <div class="flex items-center gap-3 bg-gray-50 border-2 border-gray-300 rounded-3xl px-4 py-3 transition-all duration-200 focus-within:border-[#8B0000] focus-within:bg-white">
                            <!-- Attach Image Button (+ icon) -->
                            <label for="image" class="cursor-pointer flex items-center justify-center w-10 h-10 bg-white border-2 border-gray-200 rounded-full hover:bg-[#8B0000] hover:border-[#8B0000] transition-all duration-200 flex-shrink-0 group" title="Attach image">
                                <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="updateImagePreview(this)">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors duration-200 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </label>

                            <!-- Message Input -->
                            <textarea id="message" name="message" rows="1" class="flex-1 border-none bg-transparent resize-none outline-none text-sm text-gray-900 placeholder-gray-400 px-2 py-1" placeholder="Type your message here..." style="min-height: 24px; max-height: 120px; overflow-y: auto;" oninput="autoResize(this)"></textarea>

                            <!-- Send Button -->
                            <button type="submit" id="sendButton" class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-[#8B0000] to-[#6B0000] hover:from-[#6B0000] hover:to-[#5B0000] border-none rounded-full cursor-pointer transition-all duration-200 flex-shrink-0 p-0 shadow-md hover:shadow-lg" title="Send message">
                                <svg id="sendIcon" class="w-4 h-4 text-white block" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                                </svg>
                                <svg id="sendingIcon" class="w-4 h-4 text-white hidden animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>

                        @error('message')
                            <p class="text-red-600 text-sm mt-2 flex items-center gap-1 pl-3">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                        @error('image')
                            <p class="text-red-600 text-sm mt-2 flex items-center gap-1 pl-3">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </form>
                
                <!-- Payment Request Section -->
                @php
                    $pendingPayment = $chat->pendingPayment();
                @endphp
                
                @if($pendingPayment)
                    <div class="mt-6 bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-blue-900 mb-2">Payment Request</h3>
                                <p class="text-blue-800 font-semibold mb-4">Amount Due: <span class="text-2xl text-blue-600">₱{{ number_format($pendingPayment->amount, 2) }}</span></p>
                                
                                <form action="{{ route('chats.payment.submit', $chat) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $pendingPayment->id }}">
                                    
                                    <!-- Payment Method Selection -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-semibold text-gray-700 mb-3">Select Payment Method</label>
                                        <div class="space-y-3">
                                            <!-- Bank Transfer -->
                                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition {{ old('payment_method') === 'bank_transfer' ? 'border-blue-500 bg-blue-50' : '' }}">
                                                <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                                <span class="ml-3 flex-1">
                                                    <span class="font-bold text-gray-900">🏦 Bank Transfer</span>
                                                    <p class="text-sm text-gray-600">Direct transfer to our bank account - Secure & Reliable</p>
                                                </span>
                                            </label>
                                        </div>
                                        @error('payment_method')
                                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Payment Proof Upload -->
                                    <div class="mb-5">
                                        <label for="payment_proof" class="block text-sm font-semibold text-gray-700 mb-3">Upload Payment Proof</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 hover:bg-blue-50 transition cursor-pointer" onclick="document.getElementById('payment_proof').click()">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-gray-900 font-semibold">Click to upload payment proof</p>
                                            <p class="text-sm text-gray-600">PNG, JPG up to 5MB</p>
                                            <input type="file" id="payment_proof" name="payment_proof" accept="image/*" class="hidden" required onchange="showPaymentProofPreview(this)">
                                        </div>
                                        <div id="paymentProofPreview" class="hidden mt-4">
                                            <img id="paymentProofImg" src="" alt="Preview" class="max-h-40 rounded-lg shadow-md mx-auto">
                                        </div>
                                        @error('payment_proof')
                                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        Submit Payment Proof
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200 p-8 text-center mt-6">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="text-gray-900 font-semibold text-lg">This chat is closed</p>
                <p class="text-gray-600 text-sm mt-1">You cannot send new messages. <a href="{{ route('chats.create') }}" class="text-[#8B0000] hover:text-[#6B0000] font-semibold">Start a new chat</a></p>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    #messagesContainer::-webkit-scrollbar {
        width: 8px;
    }
    
    #messagesContainer::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>

<script>
    document.body.classList.add('chat-page');

    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // AJAX Form Submission for Chat Messages
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('chatMessageForm');
        const messagesContainer = document.getElementById('messagesContainer');
        const messageInput = document.getElementById('message');
        const sendButton = document.getElementById('sendButton');
        const sendIcon = document.getElementById('sendIcon');
        const sendingIcon = document.getElementById('sendingIcon');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const messageText = messageInput.value.trim();
                const hasImage = document.getElementById('image').files.length > 0;
                
                // Require either message or image
                if (!messageText && !hasImage) {
                    return;
                }
                
                // Disable form during submission
                sendButton.disabled = true;
                sendIcon.classList.add('hidden');
                sendingIcon.classList.remove('hidden');
                messageInput.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add new message to chat
                        if (data.message) {
                            const messageHtml = createMessageElement(data.message);
                            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                        
                        // Clear form
                        messageInput.value = '';
                        messageInput.style.height = 'auto';
                        clearImage();
                        
                        // Re-enable form
                        sendButton.disabled = false;
                        sendIcon.classList.remove('hidden');
                        sendingIcon.classList.add('hidden');
                        messageInput.disabled = false;
                        messageInput.focus();
                    } else {
                        alert(data.message || 'Failed to send message');
                        sendButton.disabled = false;
                        sendIcon.classList.remove('hidden');
                        sendingIcon.classList.add('hidden');
                        messageInput.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to send message. Please try again.');
                    sendButton.disabled = false;
                    sendIcon.classList.remove('hidden');
                    sendingIcon.classList.add('hidden');
                    messageInput.disabled = false;
                });
            });
        }
    });

    function createMessageElement(message) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        
        let html = `
            <div class="flex justify-end mb-4 animate-fadeIn">
                <div class="max-w-[70%]">
                    <div class="bg-gradient-to-br from-[#8B0000] to-[#6B0000] text-white rounded-2xl rounded-tr-none px-5 py-3 shadow-md">
        `;
        
        if (message.message) {
            html += `<p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escapeHtml(message.message)}</p>`;
        }
        
        if (message.image_path) {
            html += `
                <div class="mt-3">
                    <img src="${escapeHtml(message.image_path)}" alt="Chat image" class="max-w-full rounded-lg shadow-md border-2 border-white/30" style="max-height: 300px;">
                </div>
            `;
        }
        
        html += `
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-xs text-gray-500">${dateStr} ${timeStr}</span>
                        <span class="text-xs text-gray-400 ml-2">You</span>
                    </div>
                </div>
            </div>
        `;
        
        return html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updateImagePreview(input) {
        console.log('updateImagePreview called', input, input.files);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                console.log('Image loaded, setting preview');
                const img = document.getElementById('previewImg');
                const preview = document.getElementById('imagePreview');
                console.log('Preview elements:', img, preview);
                if (img) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                if (preview) preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            console.log('No files selected');
        }
    }

    function clearImage() {
        console.log('clearImage called');
        const imageInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        console.log('Elements:', imageInput, preview);
        if (imageInput) imageInput.value = '';
        if (preview) preview.classList.add('hidden');
    }

    function showPaymentProofPreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('paymentProofImg').src = e.target.result;
                document.getElementById('paymentProofPreview').classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Address Modal Functions
    function openAddressModal() {
        document.getElementById('addressModal').classList.remove('hidden');
    }
    
    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }
    
    function openAddNewAddressModal() {
        closeAddressModal();
        document.getElementById('addNewAddressModal').classList.remove('hidden');
        loadRegions();
    }
    
    function closeAddNewAddressModal() {
        document.getElementById('addNewAddressModal').classList.add('hidden');
    }
    
    function selectAddress(addressId) {
        // Update form action and submit
        const form = document.getElementById('changeAddressForm');
        form.action = `/addresses/${addressId}/set-default`;
        form.submit();
    }

    function formatPeso(value) {
        const amount = Number(value || 0);
        return '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateQuoteDeliveryType(messageId) {
        const block = document.getElementById(`quoteBreakdown-${messageId}`);
        if (!block) return;

        const quoted = Number(block.dataset.quoted || 0);
        const deliveryFee = Number(block.dataset.deliveryFee || 0);
        const shippingLabel = block.dataset.shippingLabel || '';

        const selected = document.querySelector(`input[name="quote_delivery_type_${messageId}"]:checked`);
        const deliveryType = selected ? selected.value : 'delivery';

        const hiddenDeliveryType = document.getElementById(`acceptedDeliveryType-${messageId}`);
        if (hiddenDeliveryType) {
            hiddenDeliveryType.value = deliveryType;
        }

        const shippingEl = document.getElementById(`quoteShippingValue-${messageId}`);
        const totalEl = document.getElementById(`quoteTotalValue-${messageId}`);
        const noteEl = document.getElementById(`quoteDeliveryNote-${messageId}`);
        const hasDefaultAddress = block.dataset.hasDefaultAddress === '1';
        const deliveryAddressEl = document.getElementById(`quoteDeliveryAddress-${messageId}`);
        const pickupAddressEl = document.getElementById(`quotePickupAddress-${messageId}`);
        const noAddressEl = document.getElementById(`quoteNoAddressWarning-${messageId}`);
        const changeAddressBtn = document.getElementById(`quoteChangeAddressBtn-${messageId}`);

        if (deliveryType === 'pickup') {
            if (deliveryAddressEl) deliveryAddressEl.classList.add('hidden');
            if (noAddressEl) noAddressEl.classList.add('hidden');
            if (changeAddressBtn) changeAddressBtn.classList.add('hidden');
            if (pickupAddressEl) pickupAddressEl.classList.remove('hidden');

            if (shippingEl) {
                shippingEl.innerHTML = '<span class="text-green-600">FREE (Pick up)</span>';
            }
            if (totalEl) {
                totalEl.textContent = formatPeso(quoted);
            }
            if (noteEl) {
                noteEl.textContent = 'Pick up selected: no delivery fee will be added.';
            }
        } else {
            if (pickupAddressEl) pickupAddressEl.classList.add('hidden');
            if (hasDefaultAddress) {
                if (deliveryAddressEl) deliveryAddressEl.classList.remove('hidden');
                if (changeAddressBtn) changeAddressBtn.classList.remove('hidden');
                if (noAddressEl) noAddressEl.classList.add('hidden');
            } else {
                if (deliveryAddressEl) deliveryAddressEl.classList.add('hidden');
                if (changeAddressBtn) changeAddressBtn.classList.add('hidden');
                if (noAddressEl) noAddressEl.classList.remove('hidden');
            }

            if (shippingEl) {
                if (!hasDefaultAddress && deliveryFee <= 0) {
                    shippingEl.innerHTML = '<span class="text-xs text-gray-500 italic">Add address to calculate</span>';
                } else {
                    const feeLabel = shippingLabel ? ` <span class="text-xs text-gray-500">(${shippingLabel})</span>` : '';
                    shippingEl.innerHTML = `${formatPeso(deliveryFee)}${feeLabel}`;
                }
            }
            if (totalEl) {
                totalEl.textContent = formatPeso((!hasDefaultAddress && deliveryFee <= 0) ? quoted : (quoted + deliveryFee));
            }
            if (noteEl) {
                noteEl.textContent = 'Delivery selected: shipping fee is based on your delivery address.';
            }
        }
    }

    function validateQuoteAcceptance(messageId, hasDefaultAddress) {
        const selected = document.querySelector(`input[name="quote_delivery_type_${messageId}"]:checked`);
        const deliveryType = selected ? selected.value : 'delivery';

        const hiddenDeliveryType = document.getElementById(`acceptedDeliveryType-${messageId}`);
        if (hiddenDeliveryType) {
            hiddenDeliveryType.value = deliveryType;
        }

        if (deliveryType === 'delivery' && !hasDefaultAddress) {
            alert('Please add or select a delivery address first, or choose Pick up.');
            return false;
        }

        return true;
    }
    
    // Payment Method Selection
    function selectPaymentMethod(orderId, method) {
        // Extract auth_token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('auth_token');
        
        // Build URL with auth_token if present
        let url = `/custom-orders/${orderId}/set-payment-method`;
        if (authToken) {
            url += `?auth_token=${encodeURIComponent(authToken)}`;
        }
        
        // Update order with selected payment method
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ payment_method: method })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show payment details with auth_token preserved
                if (authToken) {
                    window.location.href = window.location.pathname + '?auth_token=' + encodeURIComponent(authToken);
                } else {
                    window.location.reload();
                }
            } else {
                alert('Failed to set payment method. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    // Receipt Preview
    function previewReceipt(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('receiptPreviewImg').src = e.target.result;
                document.getElementById('receiptPreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id^="quoteBreakdown-"]').forEach((el) => {
            const parts = String(el.id).split('-');
            const messageId = parts[parts.length - 1];
            if (messageId) {
                updateQuoteDeliveryType(messageId);
            }
        });
    });
</script>

<!-- Address Selection Modal -->
<div id="addressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Select Delivery Address</h3>
            <button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
            @if(isset($userAddresses) && $userAddresses->count() > 0)
                <div class="space-y-3">
                    @foreach($userAddresses as $address)
                        <div class="border-2 {{ $address->is_default ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-blue-300' }} rounded-lg p-4 transition-all cursor-pointer" onclick="selectAddress({{ $address->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3 flex-1">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-5 h-5 {{ $address->is_default ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-gray-900">{{ $address->label }}</span>
                                            @if($address->is_default)
                                                <span class="px-2 py-0.5 bg-green-600 text-white text-xs font-bold rounded-full">CURRENT</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mb-1">{{ $address->full_name }} • {{ $address->phone_number }}</p>
                                        <p class="text-sm text-gray-700">{{ $address->formatted_address }}</p>
                                    </div>
                                </div>
                                @if(!$address->is_default)
                                    <button onclick="event.stopPropagation(); selectAddress({{ $address->id }})" class="ml-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all">
                                        Use This
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-gray-600 font-semibold mb-2">No saved addresses</p>
                    <p class="text-gray-500 text-sm mb-4">Add a delivery address to continue</p>
                </div>
            @endif
        </div>
        
        <div class="flex gap-3 p-6 border-t border-gray-200">
            <button onclick="openAddNewAddressModal()" type="button" class="flex-1 px-6 py-3 bg-[#8B0000] hover:bg-[#6B0000] text-white font-semibold rounded-lg transition-all text-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Address
            </button>
            <button onclick="closeAddressModal()" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Add New Address Modal -->
<div id="addNewAddressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Add New Delivery Address</h3>
            <button onclick="closeAddNewAddressModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('addresses.store') }}" method="POST" class="overflow-y-auto max-h-[calc(90vh-160px)]">
            @csrf
            <input type="hidden" name="from_chat" value="1">
            
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm fond-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" name="phone_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Region *</label>
                    <select name="region_id" id="chat_region_id" required onchange="loadProvinces(this.value, 'chat')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent">
                        <option value="">-- Select Region --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Province *</label>
                    <select name="province_id" id="chat_province_id" required disabled onchange="loadCities(this.value, 'chat')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent bg-gray-100">
                        <option value="">-- Select Province --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality *</label>
                    <select name="city_id" id="chat_city_id" required disabled onchange="loadBarangays(this.value, 'chat')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent bg-gray-100">
                        <option value="">-- Select City/Municipality --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Barangay *</label>
                    <select name="barangay_id" id="chat_barangay_id" required disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent bg-gray-100">
                        <option value="">-- Select Barangay --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                    <input type="text" name="postal_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Name, Building, House No. *</label>
                    <input type="text" name="formatted_address" placeholder="Complete address details" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B0000] focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Label As: *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center">
                            <input type="radio" name="label" value="Home" checked class="mr-2 text-[#8B0000] focus:ring-[#8B0000]">
                            <span class="text-gray-700">Home</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="label" value="Work" class="mr-2 text-[#8B0000] focus:ring-[#8B0000]">
                            <span class="text-gray-700">Work</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="label" value="Other" class="mr-2 text-[#8B0000] focus:ring-[#8B0000]">
                            <span class="text-gray-700">Other</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 p-6 border-t border-gray-200">
                <button type="button" onclick="closeAddNewAddressModal()" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-[#8B0000] hover:bg-[#6B0000] text-white font-semibold rounded-lg transition-colors">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Philippine Address API Functions
function loadRegions() {
    fetch('/addresses/api/regions')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('chat_region_id');
                select.innerHTML = '<option value="">-- Select Region --</option>';
                data.data.forEach(region => {
                    select.innerHTML += `<option value="${region.id}">${region.name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading regions:', error));
}

function loadProvinces(regionId, prefix) {
    const provinceSelect = document.getElementById(`${prefix}_province_id`);
    const citySelect = document.getElementById(`${prefix}_city_id`);
    const barangaySelect = document.getElementById(`${prefix}_barangay_id`);
    
    provinceSelect.disabled = true;
    citySelect.disabled = true;
    barangaySelect.disabled = true;
    
    if (!regionId) return;
    
    fetch(`/addresses/api/provinces/${regionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                data.data.forEach(province => {
                    provinceSelect.innerHTML += `<option value="${province.id}">${province.name}</option>`;
                });
                provinceSelect.disabled = false;
            }
        })
        .catch(error => console.error('Error loading provinces:', error));
}

function loadCities(provinceId, prefix) {
    const citySelect = document.getElementById(`${prefix}_city_id`);
    const barangaySelect = document.getElementById(`${prefix}_barangay_id`);
    
    citySelect.disabled = true;
    barangaySelect.disabled = true;
    
    if (!provinceId) return;
    
    fetch(`/addresses/api/cities/${provinceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                data.data.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                });
                citySelect.disabled = false;
            }
        })
        .catch(error => console.error('Error loading cities:', error));
}

function loadBarangays(cityId, prefix) {
    const barangaySelect = document.getElementById(`${prefix}_barangay_id`);
    
    barangaySelect.disabled = true;
    
    if (!cityId) return;
    
    fetch(`/addresses/api/barangays/${cityId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                data.data.forEach(barangay => {
                    barangaySelect.innerHTML += `<option value="${barangay.id}">${barangay.name}</option>`;
                });
                barangaySelect.disabled = false;
            }
        })
        .catch(error => console.error('Error loading barangays:', error));
}
</script>

<!-- Hidden form for changing address -->
<form id="changeAddressForm" action="{{ route('addresses.setDefault', 0) }}" method="POST" class="hidden">
    @csrf
</form>

@endsection
