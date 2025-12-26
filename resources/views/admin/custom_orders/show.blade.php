@extends('layouts.admin')

@section('content')
<div class="container mx-auto py-6">

    {{-- Back Button --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.orders') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#800000] to-[#600000] text-white rounded-lg hover:from-[#600000] hover:to-[#400000] transition-all duration-300 font-semibold shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to Orders</span>
        </a>
        <div class="text-sm text-gray-600 font-medium">Created {{ $order->created_at->format('M d, Y h:i A') }}</div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg mb-4 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-2xl border-2 border-[#e0b0b0]">
        <div class="px-6 py-6 border-b-2 border-[#e0b0b0] bg-gradient-to-r from-[#fef2f2] to-white flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#800000]">Custom Order #{{ $order->id }}</h1>
                <p class="text-gray-600 mt-1">Manage and track this custom order</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Order Status --}}
                <span class="px-4 py-2 rounded-full text-sm font-bold
                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : ($order->status === 'processing' ? 'bg-yellow-100 text-yellow-700' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-[#fef2f2] text-[#800000] border-2 border-[#e0b0b0]')) }}">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
                
                {{-- Payment Status --}}
                <span class="px-4 py-2 rounded-full text-sm font-bold
                    {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($order->payment_status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-[#fef2f2] text-[#800000] border-2 border-[#e0b0b0]')) }}">
                    {{ $order->payment_status === 'pending' ? 'Pending' : ucfirst($order->payment_status) }}
                </span>
                
                {{-- Payment Method --}}
                @if($order->payment_method)
                    <span class="px-4 py-2 rounded-full text-sm font-bold bg-[#800000] text-white">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="rounded-xl border-2 border-[#e0b0b0] bg-gradient-to-br from-white to-[#faf5f5] p-5 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#800000] to-[#600000] rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="text-xs uppercase tracking-wider font-bold text-[#800000]">Customer</div>
                        </div>
                        <div class="mt-2 text-gray-900 font-bold text-lg">{{ $order->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $order->user->email ?? 'N/A' }}</div>
                        @if($order->phone)
                            <div class="text-sm text-gray-700 mt-2 font-medium">
                                📱 {{ $order->phone }}
                            </div>
                        @endif
                        <div class="text-sm text-gray-700 mt-3 font-medium">
                            @if($order->delivery_type === 'pickup')
                                🏪 Store Pickup
                            @elseif($order->delivery_type === 'delivery')
                                🚚 Delivery
                            @else
                                Not specified
                            @endif
                        </div>
                        @if($order->delivery_address)
                            <div class="mt-3 text-sm text-gray-700 bg-[#fef2f2] p-3 rounded-lg border border-[#e0b0b0]">
                                <span class="font-bold text-[#800000]">📍 Address:</span>
                                <p class="mt-1 whitespace-pre-line">{{ $order->delivery_address }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border-2 border-[#e0b0b0] bg-gradient-to-br from-white to-[#faf5f5] p-5 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#800000] to-[#600000] rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <div class="text-xs uppercase tracking-wider font-bold text-[#800000]">Quantity</div>
                        </div>
                        <div class="mt-2 text-gray-900 font-bold text-3xl">
                            @if($order->isFabricOrder())
                                {{ $order->formatted_fabric_quantity }}
                            @else
                                {{ $order->quantity }}
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-2">
                            @if($order->isFabricOrder())
                                Fabric Order
                            @else
                                Items
                            @endif
                        </p>
                    </div>
                    <div class="sm:col-span-2 rounded-xl border-2 border-[#e0b0b0] bg-gradient-to-br from-white to-[#faf5f5] p-5 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#800000] to-[#600000] rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="text-xs uppercase tracking-wider font-bold text-[#800000]">
                                @if($order->isFabricOrder())
                                    Fabric Details
                                @else
                                    Specifications
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 text-gray-800">
                            @if($order->isFabricOrder())
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                                        <span class="text-sm font-bold text-[#800000]">Fabric Type:</span>
                                        <span class="text-sm text-gray-800 font-semibold">{{ $order->fabric_type }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                                        <span class="text-sm font-bold text-[#800000]">Quantity:</span>
                                        <span class="text-sm text-gray-800 font-semibold bg-[#fef2f2] px-3 py-1 rounded-full">{{ $order->formatted_fabric_quantity }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                                        <span class="text-sm font-bold text-[#800000]">Intended Use:</span>
                                        <span class="text-sm text-gray-800 font-semibold">{{ $order->intended_use_label }}</span>
                                    </div>
                                    @if($order->fabric_specifications)
                                        <div class="py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                                            <span class="text-sm font-bold text-[#800000] block mb-2">Specifications:</span>
                                            <p class="text-sm text-gray-700">{{ $order->fabric_specifications }}</p>
                                        </div>
                                    @endif
                                    @if($order->special_requirements)
                                        <div class="py-3 px-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                            <span class="text-sm font-bold text-yellow-800 block mb-2">⚠️ Special Requirements:</span>
                                            <p class="text-sm text-yellow-900">{{ $order->special_requirements }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="whitespace-pre-line text-gray-800 bg-white p-3 rounded-lg border border-[#e0b0b0]">{{ $order->specifications }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payment Details --}}
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-[#800000] to-[#600000] rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-[#800000]">Payment Details</h2>
                    </div>
                    <div class="rounded-xl border-2 border-[#e0b0b0] bg-gradient-to-br from-white to-[#faf5f5] p-5 space-y-4">
                        <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700">Payment Method</span>
                            <span class="font-bold text-[#800000]">
                                @if($order->payment_method)
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                @else
                                    <span class="text-gray-400">Not selected</span>
                                @endif
                            </span>
                        </div>
                        @if($order->final_price)
                        <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700">Quoted Price</span>
                            <span class="font-bold text-2xl text-[#800000]">₱{{ number_format($order->final_price, 2) }}</span>
                        </div>
                        @endif
                        @if($order->transaction_id)
                        <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700">Transaction ID</span>
                            <span class="font-mono text-sm font-bold text-[#800000]">{{ $order->transaction_id }}</span>
                        </div>
                        @endif
                        @if($order->paid_at)
                        <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700">Paid At</span>
                            <span class="font-medium text-gray-800">
                                @if(is_string($order->paid_at))
                                    {{ \Carbon\Carbon::parse($order->paid_at)->format('M d, Y h:i A') }}
                                @else
                                    {{ $order->paid_at->format('M d, Y h:i A') }}
                                @endif
                            </span>
                        </div>
                        @endif
                        @if($order->transfer_date)
                        <div class="flex justify-between items-center py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700">Transfer Date</span>
                            <span class="font-medium text-gray-800">
                                @if(is_string($order->transfer_date))
                                    {{ \Carbon\Carbon::parse($order->transfer_date)->format('M d, Y') }}
                                @else
                                    {{ $order->transfer_date->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                        @endif
                        @if($order->payment_notes)
                        <div class="py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700 block mb-2">Payment Notes</span>
                            <p class="text-sm text-gray-800">{{ $order->payment_notes }}</p>
                        </div>
                        @endif
                        @if($order->payment_receipt)
                        <div class="py-3 px-3 bg-white rounded-lg border border-[#e0b0b0]">
                            <span class="text-sm font-semibold text-gray-700 block mb-2">Payment Receipt</span>
                            <div class="bg-[#faf5f5] rounded-lg p-3 border border-[#e0b0b0]">
                                @php
                                    // Check if path starts with 'payment_receipts/' (new uploads disk)
                                    $receiptUrl = str_starts_with($order->payment_receipt, 'payment_receipts/') 
                                        ? asset('uploads/' . $order->payment_receipt)
                                        : asset('storage/' . $order->payment_receipt);
                                @endphp
                                <a href="{{ $receiptUrl }}" target="_blank" class="block">
                                    @php
                                        $extension = pathinfo($order->payment_receipt, PATHINFO_EXTENSION);
                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp
                                    @if($isImage)
                                        <img src="{{ $receiptUrl }}" 
                                             alt="Payment Receipt" 
                                             class="w-full max-w-md h-auto rounded-lg shadow-md border-2 border-gray-300 hover:border-blue-400 transition-colors"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22><rect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2216%22 fill=%22%23666%22>Image not found</text></svg>';">
                                    @else
                                        <div class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="font-medium">View PDF Receipt</span>
                                        </div>
                                    @endif
                                </a>
                                <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                                    <span>Click to view full size</span>
                                    <a href="{{ $receiptUrl }}" download class="text-blue-600 hover:underline">Download</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Payment Receipt Image --}}
                @if($order->payment_receipt)
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Payment Receipt
                    </h2>
                    <div class="rounded-xl border border-gray-200 p-4 bg-gradient-to-br from-green-50 to-emerald-50">
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-semibold text-green-700">Uploaded by Customer</span>
                                @if($order->paid_at)
                                <span class="block text-xs text-gray-500 mt-1">
                                    Submitted on {{ is_string($order->paid_at) ? \Carbon\Carbon::parse($order->paid_at)->format('M d, Y \a\t h:i A') : $order->paid_at->format('M d, Y \a\t h:i A') }}
                                </span>
                                @endif
                            </p>
                        </div>
                        
                        @php
                            // Check if path starts with 'payment_receipts/' (new uploads disk)
                            $receiptUrl = str_starts_with($order->payment_receipt, 'payment_receipts/') 
                                ? asset('uploads/' . $order->payment_receipt)
                                : asset('storage/' . $order->payment_receipt);
                        @endphp
                        
                        <a href="{{ $receiptUrl }}" target="_blank" class="block">
                            @php
                                $extension = pathinfo($order->payment_receipt, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <img src="{{ $receiptUrl }}" 
                                     alt="Payment Receipt" 
                                     class="w-full max-w-lg h-auto rounded-lg shadow-lg border-2 border-green-300 hover:border-green-500 transition-all duration-200"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-red-50 border-2 border-red-300 rounded-lg p-6 text-center\'><svg class=\'w-12 h-12 text-red-400 mx-auto mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg><p class=\'text-red-700 font-medium\'>Image not found or failed to load</p><p class=\'text-sm text-red-600 mt-1\'>Path: {{ $order->payment_receipt }}</p></div>';">
                            @else
                                <div class="flex items-center gap-3 bg-white rounded-lg p-4 border-2 border-gray-300 hover:border-blue-400 transition-colors">
                                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">PDF Receipt</p>
                                        <p class="text-sm text-gray-600">Click to view</p>
                                    </div>
                                </div>
                            @endif
                        </a>
                        
                        <div class="mt-3 flex items-center gap-4">
                            <a href="{{ $receiptUrl }}" target="_blank" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                View Full Size
                            </a>
                            <a href="{{ $receiptUrl }}" download class="text-sm text-green-600 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Receipt
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Pattern Preview & Design --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        @if($order->design_method === 'pattern')
                            Pattern Preview (Live Canvas)
                        @else
                            Design Upload
                        @endif
                    </h2>
                    @if($order->design_upload)
                        <div class="rounded-xl border border-gray-200 p-4 bg-gray-50">
                            <div class="mb-3">
                                @if($order->design_method === 'pattern')
                                    <p class="text-sm text-gray-600 mb-2">
                                        <span class="font-semibold text-purple-700">Pattern Customization Preview</span>
                                        <span class="block text-xs text-gray-500 mt-1">This image shows how the user customized the pattern on the fabric</span>
                                    </p>
                                @endif
                            </div>
                            
                            {{-- Check if it's a base64 image or file path --}}
                            @if(str_starts_with($order->design_upload, 'data:image'))
                                {{-- Base64 encoded image --}}
                                <a href="{{ $order->design_upload }}" target="_blank" class="block">
                                    <img src="{{ $order->design_upload }}" alt="Pattern Preview" class="w-full max-w-lg h-auto rounded-lg shadow-md border-2 border-purple-200">
                                </a>
                            @else
                                {{-- File path --}}
                                <a href="{{ asset('storage/' . $order->design_upload) }}" target="_blank" class="block">
                                    <img src="{{ asset('storage/' . $order->design_upload) }}" alt="Pattern Preview" class="w-full max-w-lg h-auto rounded-lg shadow-md border-2 border-purple-200">
                                </a>
                            @endif
                            
                            <div class="mt-3 flex items-center gap-4">
                                @if(str_starts_with($order->design_upload, 'data:image'))
                                    <a href="{{ $order->design_upload }}" download="pattern-preview-{{ $order->id }}.png" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download Preview
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $order->design_upload) }}" download class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </a>
                                @endif
                                <a href="{{ $order->design_upload }}" target="_blank" class="text-sm text-purple-600 hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                    View Full Size
                                </a>
                            </div>
                            
                            {{-- Pattern Customization Settings --}}
                            @if($order->design_method === 'pattern' && $order->design_metadata)
                                @php
                                    $metadata = is_string($order->design_metadata) ? json_decode($order->design_metadata, true) : $order->design_metadata;
                                @endphp
                                @if($metadata)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Customization Settings:</h3>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            @if(isset($metadata['scale']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Scale:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ number_format($metadata['scale'] * 100, 0) }}%</span>
                                                </div>
                                            @endif
                                            @if(isset($metadata['rotation']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Rotation:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ $metadata['rotation'] }}°</span>
                                                </div>
                                            @endif
                                            @if(isset($metadata['opacity']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Opacity:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ number_format($metadata['opacity'] * 100, 0) }}%</span>
                                                </div>
                                            @endif
                                            @if(isset($metadata['brightness']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Brightness:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ $metadata['brightness'] }}%</span>
                                                </div>
                                            @endif
                                            @if(isset($metadata['saturation']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Saturation:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ $metadata['saturation'] }}%</span>
                                                </div>
                                            @endif
                                            @if(isset($metadata['hue']))
                                                <div class="bg-white px-3 py-2 rounded border border-gray-200">
                                                    <span class="text-gray-500">Hue:</span>
                                                    <span class="font-semibold text-gray-800 ml-1">{{ $metadata['hue'] }}°</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="w-72 h-72 bg-gray-100 flex items-center justify-center rounded-xl border border-dashed border-gray-300 text-gray-500">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">No Pattern Preview Available</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1 sticky top-6 self-start">
                {{-- Update Status Form --}}
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <h2 class="text-lg font-semibold mb-3 text-gray-900">Update Status</h2>
                    <form action="{{ route('admin.custom_orders.update_status', $order->id) }}" method="POST" class="flex flex-col gap-3">
                        @csrf
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending" @selected($order->status === 'pending')>Pending</option>
                            <option value="processing" @selected($order->status === 'processing')>Processing</option>
                            <option value="completed" @selected($order->status === 'completed')>Completed</option>
                            <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <span>Update</span>
                        </button>
                    </form>
                </div>

                {{-- Set Price for Customer Approval --}}
                @if($order->status === 'pending')
                <div class="bg-white mt-4 p-4 rounded-xl border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold mb-2 text-gray-900">📋 Review & Quote Price</h2>
                    <p class="text-sm text-gray-600 mb-4">Review the order details and provide a quote to the customer.</p>
                    
                    <form id="quotePriceForm" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quoted Amount (₱) *</label>
                            <input type="number" name="price" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="0.00" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Requirements/Notes to Customer</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="e.g., We need exact measurements, Expected completion in 5 days..."></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Send Quote to Customer
                        </button>
                        <p class="text-xs text-gray-500">✓ Customer will be notified and can accept or reject this quote.</p>
                    </form>
                </div>
                
                {{-- Reject Order --}}
                <div class="bg-white mt-4 p-4 rounded-xl border border-red-200 shadow-sm">
                    <h2 class="text-lg font-semibold mb-2 text-red-700">❌ Reject Order</h2>
                    <p class="text-sm text-gray-600 mb-4">If you cannot fulfill this order, provide a reason for rejection.</p>
                    
                    <form id="rejectOrderForm" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                            <textarea name="rejection_reason" rows="3" class="w-full border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="e.g., Cannot source the requested fabric, Timeline too short..." required></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 w-full font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject Order
                        </button>
                        <p class="text-xs text-red-600">⚠️ This action will notify the customer and cancel the order.</p>
                    </form>
                </div>
                @elseif($order->status === 'price_quoted')
                <div class="bg-blue-50 mt-4 p-4 rounded-xl border border-blue-200">
                    <h2 class="text-lg font-semibold mb-2 text-blue-800">⏳ Waiting for Customer Decision</h2>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">
                            <span class="font-semibold">Quoted Price:</span> ₱{{ number_format($order->final_price, 2) }}
                        </p>
                        @if($order->admin_notes)
                        <p class="text-gray-700">
                            <span class="font-semibold">Your Notes:</span> {{ $order->admin_notes }}
                        </p>
                        @endif
                        <p class="text-gray-700">
                            <span class="font-semibold">Quoted At:</span> {{ $order->price_quoted_at->format('M d, Y h:i A') }}
                        </p>
                        @if($order->user_notified_at)
                        <p class="text-gray-700">
                            <span class="font-semibold">Customer Notified:</span> {{ $order->user_notified_at->format('M d, Y h:i A') }}
                        </p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600 mt-3">Customer can accept or reject your quote from their dashboard.</p>
                </div>
                @elseif($order->status === 'approved')
                <div class="bg-green-50 mt-4 p-4 rounded-xl border border-green-200">
                    <h2 class="text-lg font-semibold mb-2 text-green-800">✅ Customer Accepted Quote</h2>
                    <p class="text-sm text-gray-700 mb-2">
                        <span class="font-semibold">Agreed Price:</span> ₱{{ number_format($order->final_price, 2) }}
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Accepted At:</span> {{ $order->approved_at->format('M d, Y h:i A') }}
                    </p>
                    <p class="text-xs text-gray-600 mt-3">Waiting for customer payment to start production.</p>
                </div>
                @elseif($order->status === 'rejected')
                <div class="bg-red-50 mt-4 p-4 rounded-xl border border-red-200">
                    <h2 class="text-lg font-semibold mb-2 text-red-800">❌ Order Rejected</h2>
                    @if($order->rejection_reason)
                    <p class="text-sm text-gray-700 mb-2">
                        <span class="font-semibold">Reason:</span> {{ $order->rejection_reason }}
                    </p>
                    @endif
                    @if($order->rejected_at)
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Rejected At:</span> {{ $order->rejected_at->format('M d, Y h:i A') }}
                    </p>
                    @endif
                </div>
                @endif

                {{-- Payment Verification --}}
                @if($order->payment_status === 'pending_verification')
                    <div class="bg-yellow-50 border border-yellow-200 mt-4 p-4 rounded-xl">
                        <h2 class="text-lg font-semibold mb-2 text-yellow-800">Payment Verification</h2>
                        @if($order->transaction_id)
                            <p class="text-sm text-gray-700 mb-1">
                                Transaction ID: <span class="font-mono font-semibold">{{ $order->transaction_id }}</span>
                            </p>
                        @endif
                        @if($order->payment_receipt)
                            @php
                                $receiptUrl = str_starts_with($order->payment_receipt, 'payment_receipts/') 
                                    ? asset('uploads/' . $order->payment_receipt)
                                    : asset('storage/' . $order->payment_receipt);
                            @endphp
                            <p class="text-sm text-gray-700 mb-2">
                                Receipt uploaded:
                                <a href="{{ $receiptUrl }}" target="_blank" class="text-blue-600 hover:underline ml-1">View</a>
                            </p>
                        @endif
                        @if($order->payment_notes)
                            <p class="text-sm text-gray-700 mb-3">
                                Notes: {{ $order->payment_notes }}
                            </p>
                        @endif
                        @if($order->transfer_date)
                            <p class="text-sm text-gray-700 mb-3">
                                Transfer Date: 
                                @if(is_string($order->transfer_date))
                                    {{ \Carbon\Carbon::parse($order->transfer_date)->format('M d, Y') }}
                                @else
                                    {{ $order->transfer_date->format('M d, Y') }}
                                @endif
                            </p>
                        @endif
                        <form action="{{ route('admin.custom_orders.verify_payment', $order->id) }}" method="POST" class="space-y-3 mt-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                <select name="payment_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="paid">Mark as Paid</option>
                                    <option value="failed">Mark as Failed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea name="payment_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Add verification notes..."></textarea>
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                                Update Payment Status
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Payment Verification for Processing Orders --}}
                @if($order->status === 'processing' && in_array($order->payment_status, ['unpaid', 'pending', 'pending_verification']))
                    <div class="bg-yellow-50 border border-yellow-200 mt-4 p-4 rounded-xl">
                        <h2 class="text-lg font-semibold mb-2 text-yellow-800">Payment Verification Required</h2>
                        <p class="text-sm text-gray-700 mb-3">
                            Customer has accepted the quote and should complete payment. Verify payment when received.
                        </p>
                        @if($order->final_price)
                            <p class="text-sm text-gray-700 mb-3">
                                Expected Amount: <span class="font-semibold text-green-600">₱{{ number_format($order->final_price, 2) }}</span>
                            </p>
                        @endif
                        <form action="{{ route('admin.custom_orders.verify_payment', $order->id) }}" method="POST" class="space-y-3 mt-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                <select name="payment_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="paid">Mark as Paid</option>
                                    <option value="failed">Mark as Failed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea name="payment_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Add verification notes..."></textarea>
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                                Update Payment Status
                            </button>
                        </form>
                    </div>
                @endif

                {{-- API Payment Status Check --}}
                @if(in_array($order->payment_method, ['gcash', 'online_banking']) && in_array($order->payment_status, ['pending', 'unpaid']))
                    <div class="bg-blue-50 border border-blue-200 mt-4 p-4 rounded-xl">
                        <h2 class="text-lg font-semibold mb-2 text-blue-800">API Payment Status</h2>
                        <p class="text-sm text-gray-700 mb-3">
                            This payment was processed via {{ ucfirst($order->payment_method) }}. Status will be updated automatically via webhook.
                        </p>
                        @if($order->transaction_id)
                            <form action="{{ route('admin.custom_orders.check_payment', $order->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                    Check Status Manually
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quote Price Form
    const quotePriceForm = document.getElementById('quotePriceForm');
    if (quotePriceForm) {
        quotePriceForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("admin.custom-orders.set-price", $order) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                    // Show success message
                    alert('✅ ' + data.message);
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Failed to send quote. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // Reject Order Form
    const rejectOrderForm = document.getElementById('rejectOrderForm');
    if (rejectOrderForm) {
        rejectOrderForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!confirm('⚠️ Are you sure you want to reject this order? This action cannot be undone.')) {
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Rejecting...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("admin.custom-orders.reject", $order) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rejection_reason: formData.get('rejection_reason')
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    alert('✅ ' + data.message);
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Failed to reject order. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});
</script>
@endpush

@endsection
