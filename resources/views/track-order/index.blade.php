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
                <p class="text-xl text-white/90 max-w-2xl mx-auto">Enter your tracking number, order ID, or email to get real-time updates on your delivery</p>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-20">
        <div class="search-card p-8">
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <form action="{{ route('track-order.search') }}" method="POST" id="trackForm">
                @csrf
                
                <!-- Search Type Selection -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-4">How would you like to track?</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="search-option active rounded-xl p-4 text-center" onclick="selectSearchType('tracking_number')">
                            <input type="radio" name="search_type" value="tracking_number" checked class="hidden">
                            <div class="option-icon text-4xl mb-2">📦</div>
                            <div class="font-semibold">Tracking Number</div>
                            <div class="text-xs mt-1 opacity-75">YAK-XXXXXXXXXX</div>
                        </div>
                        
                        <div class="search-option rounded-xl p-4 text-center" onclick="selectSearchType('order_id')">
                            <input type="radio" name="search_type" value="order_id" class="hidden">
                            <div class="option-icon text-4xl mb-2 text-gray-600">🔢</div>
                            <div class="font-semibold">Order ID</div>
                            <div class="text-xs mt-1 opacity-75">Order #12345</div>
                        </div>
                        
                        <div class="search-option rounded-xl p-4 text-center" onclick="selectSearchType('email')">
                            <input type="radio" name="search_type" value="email" class="hidden">
                            <div class="option-icon text-4xl mb-2 text-gray-600">📧</div>
                            <div class="font-semibold">Email Address</div>
                            <div class="text-xs mt-1 opacity-75">your@email.com</div>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="mb-6">
                    <label for="search_value" class="block text-sm font-semibold text-gray-700 mb-2" id="searchLabel">
                        Enter Tracking Number
                    </label>
                    <input type="text" 
                           name="search_value" 
                           id="search_value" 
                           class="w-full px-6 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 transition-all text-lg"
                           style="--tw-ring-color: rgba(128, 0, 0, 0.2);" onfocus="this.style.borderColor='#800000'"
                           placeholder="YAK-XXXXXXXXXX"
                           required>
                </div>

                <!-- Email Input (conditional) -->
                <div class="mb-6 hidden" id="emailField">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email Address (Optional for Order ID)
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="w-full px-6 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 transition-all"
                           style="--tw-ring-color: rgba(128, 0, 0, 0.2);" onfocus="this.style.borderColor='#800000'"
                           placeholder="your@email.com">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full text-white px-8 py-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl font-semibold text-lg flex items-center justify-center" style="background: linear-gradient(to right, #800000, #600000);" onmouseover="this.style.background='linear-gradient(to right, #600000, #400000)'" onmouseout="this.style.background='linear-gradient(to right, #800000, #600000)'">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Track My Order
                </button>
            </form>

            <!-- Help Text -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Where's my tracking number?</h3>
                        <p class="text-sm text-gray-600">Check your order confirmation email</p>
                    </div>
                    
                    <div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Real-time Updates</h3>
                        <p class="text-sm text-gray-600">Get live tracking information</p>
                    </div>
                    
                    <div>
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background-color: rgba(128, 0, 0, 0.1);">
                            <svg class="w-6 h-6" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Need Help?</h3>
                        <p class="text-sm text-gray-600">Contact our support team</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-gray-50">
        <div class="mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-2">Recently Shipped Orders</h2>
            <p class="text-lg text-gray-600">Quick links to recent order tracking information</p>
        </div>

        @if($recentOrders->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recentOrders as $order)
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden border-l-4 border-[#800000]">
                        <div class="p-6">
                            <!-- Order Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Order ID</p>
                                    <p class="text-2xl font-bold text-gray-900">#{{ $order->id }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'completed')
                                        bg-green-100 text-green-800
                                    @elseif($order->status === 'processing')
                                        bg-blue-100 text-blue-800
                                    @elseif($order->status === 'shipped')
                                        bg-purple-100 text-purple-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>

                            <!-- Customer Info -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Customer</p>
                                <p class="text-gray-900 font-medium">{{ $order->user?->name ?? 'Guest' }}</p>
                            </div>

                            <!-- Order Amount -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Total Amount</p>
                                <p class="text-2xl font-bold text-[#800000]">₱{{ number_format($order->total_amount, 2) }}</p>
                            </div>

                            <!-- Tracking Number -->
                            <div class="mb-4 p-3 bg-gray-50 rounded">
                                <p class="text-xs text-gray-500 mb-1">Tracking #</p>
                                <p class="font-mono text-sm font-bold text-gray-900 break-all">{{ $order->tracking_number }}</p>
                            </div>

                            <!-- Order Date -->
                            <div class="mb-6">
                                <p class="text-xs text-gray-500">Ordered on</p>
                                <p class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y - H:i A') }}</p>
                            </div>

                            <!-- Action Buttons -->
                            @if($order->status === 'delivered')
                                <form action="{{ route('orders.confirm-received', $order->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button type="submit" 
                                            class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                        ✓ Order Received
                                    </button>
                                </form>
                            @elseif($order->status === 'completed')
                                <button type="button" 
                                        class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg opacity-60 cursor-not-allowed font-semibold" disabled>
                                    ✓ Order Received
                                </button>
                            @endif

                            <!-- Track Button -->
                            @if($order->status === 'completed')
                                <a href="{{ route('orders.show', $order->id) }}" 
                                   class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold">
                                    View Order
                                </a>
                            @else
                                <a href="{{ route('track-order.show', $order->tracking_number) }}" 
                                   class="block w-full text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-semibold">
                                    Track This Order
                                </a>
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
                <p class="text-gray-400 mb-4">Orders will appear here once they are placed and shipped</p>
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
    </script>
@endsection
