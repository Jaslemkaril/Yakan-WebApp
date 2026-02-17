@extends('layouts.app')

@section('title', $chat->subject)

@section('content')
<style>
    .chat-container { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); }
    .chat-header { background: white; border-bottom: 2px solid #f3f4f6; }
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
</style>
<div class="min-h-screen chat-container py-8">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Header with Chat Info -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-start gap-6">
                <div class="flex-1">
                    <a href="{{ route('chats.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-[#8B0000] font-semibold mb-4 transition hover:gap-3 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Chats
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $chat->subject }}</h1>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold {{ $chat->status === 'closed' ? 'status-closed' : 'status-badge' }}">
                            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $chat->status === 'closed' ? 'bg-red-600' : 'bg-green-600' }}"></span>
                            {{ ucfirst($chat->status) }}
                        </span>
                        <span class="text-gray-600 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Started {{ $chat->created_at->diffForHumans() }}
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
                        <button type="submit" class="btn-outline px-6 py-2.5 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2 shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Close Chat
                        </button>
                    </form>
                @endif
            </div>
        </div>

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
                                            $chatImageUrl = $imagePath;
                                        } elseif (str_starts_with($imagePath, 'data:image')) {
                                            $chatImageUrl = $imagePath;
                                        } else {
                                            // Try both storage and public paths
                                            $chatImageUrl = asset($imagePath);
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
                                <p class="break-words leading-relaxed whitespace-pre-line">{{ $message->message }}</p>
                            </div>
                            
                            @if($message->sender_type === 'admin' && str_contains($message->message, 'PRICE QUOTE'))
                                @php
                                    $hasResponse = \App\Models\ChatMessage::where('chat_id', $chat->id)
                                        ->where('sender_type', 'user')
                                        ->where('message', 'like', '%accepted the price quote%')
                                        ->orWhere('message', 'like', '%declined the price quote%')
                                        ->where('created_at', '>', $message->created_at)
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
                                    
                                    // Calculate shipping fee based on address (same logic as CartController)
                                    $shippingFee = 0;
                                    $shippingLabel = '';
                                    
                                    if ($userDefaultAddress) {
                                        $cityLower = strtolower($userDefaultAddress->city ?? '');
                                        $regionLower = strtolower($userDefaultAddress->province ?? '');
                                        $postalCode = $userDefaultAddress->postal_code ?? '';
                                        
                                        if (str_contains($cityLower, 'zamboanga') && str_starts_with($postalCode, '7')) {
                                            $shippingFee = 0;
                                            $shippingLabel = 'FREE DELIVERY! 🎉';
                                        } elseif (str_contains($regionLower, 'zamboanga') || in_array($cityLower, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                                            $shippingFee = 80;
                                            $shippingLabel = 'Zamboanga Peninsula';
                                        } elseif (in_array($cityLower, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) || str_contains($regionLower, 'barmm') || str_contains($regionLower, 'armm')) {
                                            $shippingFee = 120;
                                            $shippingLabel = 'BARMM Region';
                                        } elseif (str_contains($regionLower, 'mindanao') || in_array($cityLower, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                                            $shippingFee = 150;
                                            $shippingLabel = 'Mindanao';
                                        } elseif (str_contains($regionLower, 'visayas') || in_array($cityLower, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                                            $shippingFee = 180;
                                            $shippingLabel = 'Visayas';
                                        } elseif (str_contains($cityLower, 'manila') || str_contains($regionLower, 'ncr') || in_array($cityLower, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                                            $shippingFee = 220;
                                            $shippingLabel = 'NCR/Metro Manila';
                                        } elseif (str_contains($regionLower, 'luzon') || in_array($cityLower, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                                            $shippingFee = 250;
                                            $shippingLabel = 'Luzon';
                                        } else {
                                            $shippingFee = 280;
                                            $shippingLabel = 'Remote Area';
                                        }
                                    }
                                    
                                    $totalWithShipping = $quotedPrice + $shippingFee;
                                @endphp
                                
                                @if(!$hasResponse)
                                    {{-- Shipping Fee & Total Breakdown --}}
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4 mt-3 mx-2">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 bg-blue-400 rounded-full p-2">
                                                <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-gray-900 text-sm mb-3">💰 Payment Breakdown</h4>
                                                
                                                @if($userDefaultAddress)
                                                    {{-- Delivery Address --}}
                                                    <div class="bg-white border border-blue-200 rounded-lg p-3 mb-3">
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
                                                    
                                                    {{-- Price Breakdown --}}
                                                    <div class="bg-white border border-blue-200 rounded-lg p-3 mb-2">
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-center text-sm">
                                                                <span class="text-gray-700">Quoted Price:</span>
                                                                <span class="font-semibold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center text-sm">
                                                                <span class="text-gray-700">Shipping Fee:</span>
                                                                <span class="font-semibold {{ $shippingFee == 0 ? 'text-green-600' : 'text-blue-700' }}">
                                                                    @if($shippingFee == 0)
                                                                        FREE 🎉
                                                                    @else
                                                                        ₱{{ number_format($shippingFee, 2) }}
                                                                    @endif
                                                                    @if($shippingLabel)
                                                                        <span class="text-xs text-gray-500">({{ $shippingLabel }})</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="border-t-2 border-dashed border-gray-300 pt-2 mt-2">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-base font-bold text-gray-900">TOTAL TO PAY:</span>
                                                                    <span class="text-xl font-bold text-green-600">₱{{ number_format($totalWithShipping, 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <button onclick="openAddressModal()" type="button" class="inline-flex items-center gap-2 text-xs font-semibold text-[#8B0000] hover:text-[#6B0000] transition-colors mt-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Change delivery address
                                                    </button>
                                                @else
                                                    {{-- No Address Warning --}}
                                                    <div class="bg-white border-2 border-red-300 rounded-lg p-4 mb-3">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                            </svg>
                                                            <p class="text-sm font-bold text-red-900">No delivery address set</p>
                                                        </div>
                                                        <p class="text-xs text-gray-700 mb-3">Please add your delivery address to see the shipping fee and total amount to pay.</p>
                                                        <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-2">
                                                            <div class="flex justify-between items-center text-sm">
                                                                <span class="text-gray-700">Quoted Price:</span>
                                                                <span class="font-semibold text-gray-900">₱{{ number_format($quotedPrice, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center text-sm mt-1">
                                                                <span class="text-gray-700">Shipping Fee:</span>
                                                                <span class="text-xs text-gray-500 italic">Add address to calculate</span>
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
                                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
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
                            @if($message->sender_type === 'user' && str_contains($message->message, 'accepted the price quote'))
                                @php
                                    // Find the order created for this chat
                                    $chatOrder = \App\Models\Order::where('user_id', auth()->id())
                                        ->where('source', 'chat')
                                        ->where('customer_notes', 'LIKE', '%chat ID: ' . $chat->id . '%')
                                        ->latest()
                                        ->first();
                                @endphp
                                
                                @if($chatOrder)
                                    {{-- Show payment summary and method buttons if no payment method chosen yet --}}
                                    @if(!$chatOrder->payment_method)
                                        <div class="mt-4 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-5 border-2 border-green-300 shadow-lg max-w-md">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-green-900 text-lg">Quote Accepted!</h3>
                                                    <p class="text-xs text-green-700">Order #{{{ $chatOrder->order_ref }}}</p>
                                                </div>
                                            </div>
                                            
                                            {{-- Payment Summary --}}
                                            <div class="bg-white rounded-xl p-4 mb-4 border border-green-200">
                                                <p class="text-xs font-semibold text-gray-600 mb-2">PAYMENT BREAKDOWN</p>
                                                <div class="space-y-2 text-sm">
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-700">Quoted Price:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($chatOrder->subtotal, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-700">Shipping Fee:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($chatOrder->shipping_fee, 2) }}</span>
                                                    </div>
                                                    <div class="border-t border-gray-200 pt-2 flex justify-between">
                                                        <span class="font-bold text-gray-900">TOTAL TO PAY:</span>
                                                        <span class="font-bold text-green-600 text-lg">₱{{ number_format($chatOrder->total_amount, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Payment Method Buttons --}}
                                            <p class="text-sm font-semibold text-gray-800 mb-3">Choose your payment method:</p>
                                            <div class="grid grid-cols-2 gap-3">
                                                <button onclick="selectPaymentMethod('{{ $chatOrder->id }}', 'gcash')" class="payment-method-btn flex flex-col items-center gap-2 bg-white hover:bg-blue-50 border-2 border-blue-300 hover:border-blue-500 rounded-xl p-4 transition-all hover:shadow-md">
                                                    <span class="text-3xl">📱</span>
                                                    <span class="font-bold text-sm text-gray-900">GCash</span>
                                                </button>
                                                <button onclick="selectPaymentMethod('{{ $chatOrder->id }}', 'bank_transfer')" class="payment-method-btn flex flex-col items-center gap-2 bg-white hover:bg-green-50 border-2 border-green-300 hover:border-green-500 rounded-xl p-4 transition-all hover:shadow-md">
                                                    <span class="text-3xl">🏦</span>
                                                    <span class="font-bold text-sm text-gray-900">Bank Transfer</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Payment Details Display (after method selected) --}}
                                    @if($chatOrder && $chatOrder->payment_method && $chatOrder->payment_status !== 'paid')
                                        <div class="mt-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-5 shadow-md border-2 border-blue-200">
                                            {{-- Order Reference --}}
                                            <div class="mb-4 pb-3 border-b border-blue-200">
                                                <p class="text-xs font-semibold text-gray-600 mb-1">Order Reference:</p>
                                                <p class="text-lg font-bold text-gray-900">{{ $chatOrder->order_ref }}</p>
                                            </div>
                                            
                                            @if($chatOrder->payment_method === 'gcash')
                                                {{-- GCash Payment Details --}}
                                                <div class="bg-white/70 backdrop-blur rounded-xl p-4 mb-4">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <span class="text-2xl">📱</span>
                                                        <h4 class="text-lg font-bold text-gray-900">GCash Payment</h4>
                                                    </div>
                                                    
                                                    <div class="space-y-2">
                                                        <div class="flex justify-between items-center bg-blue-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">GCash Number:</span>
                                                            <span class="font-bold text-blue-600 text-lg">09123456789</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-blue-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Account Name:</span>
                                                            <span class="font-bold text-gray-900">Tuwas Yakan</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-100 p-3 rounded-lg border-2 border-green-300">
                                                            <span class="text-sm font-bold text-gray-800">Amount to Pay:</span>
                                                            <span class="font-bold text-green-700 text-xl">₱{{ number_format($chatOrder->total_amount, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                        <p class="text-xs font-semibold text-yellow-800 mb-1">📝 Instructions:</p>
                                                        <ol class="text-xs text-yellow-900 space-y-1 list-decimal list-inside">
                                                            <li>Open your GCash app</li>
                                                            <li>Select "Send Money"</li>
                                                            <li>Enter the GCash number above</li>
                                                            <li>Send the exact amount shown</li>
                                                            <li>Take a screenshot of the receipt</li>
                                                            <li>Upload it below</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            @elseif($chatOrder->payment_method === 'bank_transfer')
                                                {{-- Bank Transfer Payment Details --}}
                                                <div class="bg-white/70 backdrop-blur rounded-xl p-4 mb-4">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <span class="text-2xl">🏦</span>
                                                        <h4 class="text-lg font-bold text-gray-900">Bank Transfer Payment</h4>
                                                    </div>
                                                    
                                                    <div class="space-y-2">
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Bank Name:</span>
                                                            <span class="font-bold text-gray-900">Sample Bank</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Account Number:</span>
                                                            <span class="font-bold text-green-600 text-lg">1234567890</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                                            <span class="text-sm font-semibold text-gray-700">Account Name:</span>
                                                            <span class="font-bold text-gray-900">Tuwas Yakan</span>
                                                        </div>
                                                        <div class="flex justify-between items-center bg-green-100 p-3 rounded-lg border-2 border-green-300">
                                                            <span class="text-sm font-bold text-gray-800">Amount to Pay:</span>
                                                            <span class="font-bold text-green-700 text-xl">₱{{ number_format($chatOrder->total_amount, 2) }}</span>
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
                                            @endif
                                            
                                            {{-- Receipt Upload Form --}}
                                            <form action="{{ route('orders.upload_receipt', $chatOrder) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                
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
                                        </div>
                                    @endif
                                    
                                    {{-- Payment Submitted Message --}}
                                    @if($chatOrder && $chatOrder->payment_proof && $chatOrder->payment_status === 'paid')
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
                                                <p class="text-xs font-semibold text-gray-600 mb-1">Order Reference:</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $chatOrder->order_ref }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endif
                            
                            <p class="text-xs text-gray-400 mt-2 px-2">
                                {{ $message->created_at->format('M d, Y H:i') }}
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
                <form action="{{ route('chats.send-message', $chat) }}" method="POST" enctype="multipart/form-data">
                    @csrf

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
                            <button type="submit" class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-[#8B0000] to-[#6B0000] hover:from-[#6B0000] hover:to-[#5B0000] border-none rounded-full cursor-pointer transition-all duration-200 flex-shrink-0 p-0 shadow-md hover:shadow-lg" title="Send message">
                                <svg class="w-4 h-4 text-white block" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
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
                                            <!-- GCash -->
                                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition {{ old('payment_method') === 'online_banking' ? 'border-blue-500 bg-blue-50' : '' }}">
                                                <input type="radio" name="payment_method" value="online_banking" {{ old('payment_method') === 'online_banking' ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                                <span class="ml-3 flex-1">
                                                    <span class="font-bold text-gray-900">💳 GCash</span>
                                                    <p class="text-sm text-gray-600">Pay using GCash e-wallet - Fast & Secure</p>
                                                </span>
                                            </label>
                                            
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
    
    // Payment Method Selection
    function selectPaymentMethod(orderId, method) {
        // Update order with selected payment method
        fetch(`/orders/${orderId}/set-payment-method`, {
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
                // Reload page to show payment details
                window.location.reload();
            } else {
                alert('Failed to set payment method. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
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
