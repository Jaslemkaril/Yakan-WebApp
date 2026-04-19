@extends('layouts.app')

@section('title', 'Track Your Order')

@push('styles')
<style>
    .track-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .track-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .search-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }

    .search-card:hover {
        transform: translateY(-4px);
    }

    .search-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }

    .search-option:hover {
        border-color: #800000;
        background: #fff5f5;
    }

    .search-option.active {
        border-color: #800000;
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
    }

    .search-option.active .option-icon {
        color: white;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="track-hero py-20 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="animate-fade-in-up">
                <div class="inline-block mb-6">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-lg rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-white mb-4">Track Your Order</h1>
                <p class="text-xl text-white/90 max-w-2xl mx-auto">Click below to view real-time updates on your orders and deliveries</p>
            </div>
        </div>
    </section>

    <!-- Recent Orders Section -->
    <section id="recent-orders" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-gray-50">
        <div class="mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-2">Your Recent Orders</h2>
            <p class="text-lg text-gray-600">Quick links to your orders and tracking information</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <strong>{{ session('success') }}</strong>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <strong>{{ session('error') }}</strong>
                </div>
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <strong>{{ session('info') }}</strong>
                </div>
            </div>
        @endif

        @if($recentOrders->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recentOrders as $order)
                    @php
                        $isCustom  = ($order->_order_type ?? '') === 'custom';
                        
                        // Check for cancellation/refund for custom orders
                        $orderRefundRequest = null;
                        $isOrderCancelled = false;
                        if ($isCustom) {
                            $orderRefundRequest = \App\Models\CustomOrderRefundRequest::where('custom_order_id', $order->id)
                                ->where('request_type', 'return')
                                ->first();
                            $isOrderCancelled = !empty($orderRefundRequest) && in_array($orderRefundRequest->status, ['approved', 'processed']);
                        }
                        
                        $status    = $isOrderCancelled ? 'cancelled' : $order->status;
                        // Normalise label for display
                        $statusLabel = match($status) {
                            'pending_confirmation' => 'Pending Confirmation',
                            'price_quoted'         => 'Price Quoted',
                            'in_production'        => 'In Production',
                            default                => ucfirst($status),
                        };
                        // Badge colour per status
                        $badgeClass = match($status) {
                            'pending', 'pending_confirmation'          => 'bg-yellow-100 text-yellow-800',
                            'confirmed', 'approved', 'completed'       => 'bg-green-100 text-green-800',
                            'processing', 'price_quoted'               => 'bg-blue-100 text-blue-800',
                            'in_production'                            => 'bg-purple-100 text-purple-800',
                            'shipped'                                  => 'bg-indigo-100 text-indigo-800',
                            'delivered'                                => 'bg-teal-100 text-teal-800',
                            'verification_pending'                     => 'bg-orange-100 text-orange-800',
                            'cancelled', 'rejected', 'refunded'        => 'bg-red-100 text-red-800',
                            default                                    => 'bg-gray-100 text-gray-800',
                        };
                        // Amount to show
                        $displayAmount = $isCustom
                            ? ($order->final_price ?? $order->estimated_price ?? 0)
                            : ($order->total_amount ?? 0);
                    @endphp
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden border-l-4 {{ $isCustom ? 'border-purple-500' : 'border-[#800000]' }}">
                        <div class="p-6">
                            <!-- Order Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                        @if($isCustom)
                                            <span class="inline-block px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-semibold">🎨 Custom</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-red-50 text-[#800000] rounded text-xs font-semibold">🛒 Order</span>
                                        @endif
                                        &nbsp;{{ $order->order_ref ?? '#'.$order->id }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <!-- Customer Info -->
                            <div class="mb-3">
                                <p class="text-xs text-gray-500">Customer</p>
                                <p class="text-gray-900 font-medium text-sm">{{ $order->user?->name ?? ($order->customer_name ?? 'Guest') }}</p>
                            </div>

                            <!-- Order Amount -->
                            <div class="mb-3">
                                <p class="text-xs text-gray-500">{{ $isCustom ? 'Price' : 'Total Amount' }}</p>
                                <p class="text-xl font-bold {{ $isCustom ? 'text-purple-700' : 'text-[#800000]' }}">
                                    @if($displayAmount)
                                        ₱{{ number_format($displayAmount, 2) }}
                                    @else
                                        <span class="text-sm text-gray-400 font-normal">Awaiting quote</span>
                                    @endif
                                </p>
                            </div>

                            @if(!$isCustom && $order->tracking_number)
                                <!-- Tracking Number (regular orders only) -->
                                <div class="mb-3 p-3 bg-gray-50 rounded">
                                    <p class="text-xs text-gray-500 mb-1">Tracking #</p>
                                    <p class="font-mono text-sm font-bold text-gray-900 break-all">{{ $order->tracking_number }}</p>
                                </div>
                            @elseif($isCustom)
                                <!-- Production status type indicator -->
                                <div class="mb-3 p-3 bg-purple-50 rounded">
                                    <p class="text-xs text-purple-500 mb-1">Order Type</p>
                                    <p class="text-sm font-semibold text-purple-700">Custom / Made-to-Order</p>
                                </div>
                            @endif

                            <!-- Order Date -->
                            <div class="mb-5">
                                <p class="text-xs text-gray-500">Ordered on</p>
                                <p class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y - h:i A') }}</p>
                            </div>

                            <!-- Action Buttons -->
                            @if($isCustom)
                                {{-- Custom order buttons --}}
                                @if($status === 'delivered')
                                    <form action="{{ route('custom_orders.confirm_received', $order->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <button type="submit"
                                                class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm">
                                            ✓ Confirm Received
                                        </button>
                                    </form>
                                @elseif($status === 'completed')
                                    <button type="button"
                                            class="block w-full text-center px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-semibold text-sm mb-2" disabled>
                                        ✓ Order Received
                                    </button>
                                @endif
                                <a href="{{ route('custom_orders.show', $order->id) }}"
                                   class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold text-sm">
                                    View Custom Order
                                </a>
                            @else
                                {{-- Regular order buttons --}}
                                @if(in_array($status, ['completed']))
                                    <button type="button"
                                            class="block w-full text-center px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed font-semibold text-sm mb-2" disabled>
                                        ✓ Order Received
                                    </button>
                                    <a href="{{ route('orders.show', $order->id) }}"
                                       class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold text-sm">
                                        View Order
                                    </a>
                                                                @elseif($status === 'delivered')
                                    <form action="{{ route('orders.confirm-received', $order->id) }}" method="POST" class="mb-2"
                                          onsubmit="return confirmOrderReceived(this, {{ $order->id }})">
                                        @csrf
                                        <button type="submit"
                                                id="confirm-btn-{{ $order->id }}"
                                                class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm">
                                            ✓ Confirm Received
                                        </button>
                                    </form>
                                    @if($order->tracking_number)
                                        <a href="{{ route('track-order.show', $order->tracking_number) }}"
                                           class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold text-sm">
                                            Track This Order
                                        </a>
                                    @endif
                                @elseif($order->tracking_number)
                                    <a href="{{ route('track-order.show', $order->tracking_number) }}"
                                       class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold text-sm">
                                        Track This Order
                                    </a>
                                @else
                                    <a href="{{ route('orders.show', $order->id) }}"
                                       class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold text-sm">
                                        View Order Details
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m0 0v10l-8 4m0-10l-8 4"/>
                </svg>
                <p class="text-gray-500 text-lg">No recent orders yet</p>
                <p class="text-gray-400 mb-4">Your orders will appear here once placed</p>
            </div>
        @endif
    </section>

    <script>
        function selectSearchType(type) {
            // Remove active class from all options
            document.querySelectorAll('.search-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            event.currentTarget.classList.add('active');
            
            // Update radio button
            document.querySelector(`input[value="${type}"]`).checked = true;
            
            // Update label and placeholder
            const searchInput = document.getElementById('search_value');
            const searchLabel = document.getElementById('searchLabel');
            const emailField = document.getElementById('emailField');
            
            if (type === 'tracking_number') {
                searchLabel.textContent = 'Enter Tracking Number';
                searchInput.placeholder = 'YAK-XXXXXXXXXX';
                emailField.classList.add('hidden');
            } else if (type === 'order_id') {
                searchLabel.textContent = 'Enter Order ID';
                searchInput.placeholder = '12345';
                emailField.classList.remove('hidden');
            } else if (type === 'email') {
                searchLabel.textContent = 'Enter Email Address';
                searchInput.placeholder = 'your@email.com';
                emailField.classList.add('hidden');
            }
        }

        // Confirm order received - immediate UI feedback
        function confirmOrderReceived(form, orderId) {
            const userConfirmed = confirm('Please confirm: Have you already received this order in good condition?');
            if (!userConfirmed) {
                return false;
            }

            const button = document.getElementById('confirm-btn-' + orderId);
            
            // Disable button immediately
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            button.classList.remove('bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-gray-400', 'cursor-not-allowed');
            
            // Allow form to submit
            return true;
        }
    </script>
@endsection
