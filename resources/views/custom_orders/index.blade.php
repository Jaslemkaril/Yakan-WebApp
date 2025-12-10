@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <!-- Enhanced Page Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">My Custom Orders</h1>
                    <p class="text-gray-600">Track and manage your personalized product orders</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="checkForUpdates()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Check Updates
                    </button>
                    <a href="{{ route('custom_orders.create') }}"
                       class="inline-flex items-center px-6 py-3 bg-red-700 hover:bg-red-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Custom Order
                    </a>
                </div>
            </div>
        </div>

        <!-- Notification Area -->
        <div id="notificationArea" class="mb-6"></div>

        @if($orders->count() > 0)

            <!-- Enhanced Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                @php
                    $totalOrders = $orders->total();
                    $pendingCount = \App\Models\CustomOrder::where('user_id', auth()->id())->where('status', 'pending')->count();
                    $completedCount = \App\Models\CustomOrder::where('user_id', auth()->id())->where('status', 'completed')->count();
                    $processingCount = \App\Models\CustomOrder::where('user_id', auth()->id())->where('status', 'processing')->count();
                    $awaitingDecision = \App\Models\CustomOrder::where('user_id', auth()->id())->where('status', 'price_quoted')->whereNotNull('user_notified_at')->count();
                @endphp
                
                <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-red-700 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $totalOrders }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-yellow-600 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pending Review</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $pendingCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-red-700 hover:shadow-lg transition-shadow relative">
                    @if($awaitingDecision > 0)
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-red-700 rounded-full flex items-center justify-center animate-pulse">
                            <span class="text-white text-xs font-bold">{{ $awaitingDecision }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Awaiting Decision</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $awaitingDecision }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-green-600 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Completed</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $completedCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search Bar -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Status Filter Tabs -->
                    <div class="flex flex-wrap gap-2 filter-buttons">
                        <button data-status="all" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-red-100 text-red-800">
                            All Orders
                        </button>
                        <button data-status="pending" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-red-50">
                            Pending
                        </button>
                        <button data-status="price_quoted" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-red-50">
                            Price Quoted
                        </button>
                        <button data-status="in_production" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-red-50">
                            In Production
                        </button>
                        <button data-status="completed" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-red-50">
                            Completed
                        </button>
                    </div>

                    <div class="relative">
                        <input type="text" id="userSearchInput" placeholder="Search orders..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-700">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Orders Table Card -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Payment
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                            <tr class="hover:bg-red-50 transition-colors duration-150 order-row" data-status="{{ $order->status }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-red-700 to-red-800 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-white font-bold text-sm">#{{ substr($order->id, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-gray-900">#{{ $order->id }}</span>
                                            @if($order->status === 'price_quoted' && $order->user_notified_at)
                                                <div class="text-xs text-red-700 font-medium">New Price!</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    @if($order->design_upload)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $order->design_upload) }}" 
                                                 alt="Custom pattern preview"
                                                 class="w-16 h-16 rounded-lg object-cover border border-gray-200 shadow-sm">
                                        </div>
                                    @endif

                                    @if($order->isFabricOrder())
                                        <!-- Fabric Order Display -->
                                        <div class="text-sm font-medium text-gray-900">Custom Fabric Order</div>
                                        <div class="text-xs text-red-700 mt-1">
                                            {{ $order->fabric_type }} • {{ $order->formatted_fabric_quantity }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Use: {{ $order->intended_use_label }}
                                        </div>
                                        @if($order->patterns && is_array($order->patterns))
                                            @php
                                                $patternNames = [];
                                                foreach ($order->patterns as $pattern) {
                                                    if (is_string($pattern)) {
                                                        $patternNames[] = ucfirst($pattern);
                                                    } elseif (is_array($pattern) && isset($pattern['name'])) {
                                                        $patternNames[] = ucfirst($pattern['name']);
                                                    }
                                                }
                                            @endphp
                                            @if(!empty($patternNames))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Patterns: {{ implode(', ', array_slice($patternNames, 0, 2)) }}{{ count($patternNames) > 2 ? '...' : '' }}
                                                </div>
                                            @endif
                                        @endif
                                    @elseif($order->product_id && $order->product && $order->product->name)
                                        <!-- Legacy Product Order Display -->
                                        <div class="text-sm font-medium text-gray-900">{{ $order->product->name }}</div>
                                        @if($order->patterns && is_array($order->patterns))
                                            @php
                                                $patternNames = [];
                                                foreach ($order->patterns as $pattern) {
                                                    if (is_string($pattern)) {
                                                        $patternNames[] = ucfirst($pattern);
                                                    } elseif (is_array($pattern) && isset($pattern['name'])) {
                                                        $patternNames[] = ucfirst($pattern['name']);
                                                    }
                                                }
                                            @endphp
                                            @if(!empty($patternNames))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Patterns: {{ implode(', ', array_slice($patternNames, 0, 2)) }}{{ count($patternNames) > 2 ? '...' : '' }}
                                                </div>
                                            @endif
                                        @endif
                                    @elseif($order->product_id)
                                        @php
                                            $product = \App\Models\Product::find($order->product_id);
                                        @endphp
                                        @if($product)
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">Product #{{ $order->product_id }} (Not Found)</div>
                                        @endif
                                    @elseif($order->specifications)
                                        @php
                                            $specData = json_decode($order->specifications, true);
                                            $productName = null;
                                            if (is_array($specData)) {
                                                $productName = $specData['product_name'] ?? $specData['name'] ?? null;
                                            }
                                            if (!$productName && is_string($order->specifications) && strlen($order->specifications) < 50) {
                                                $productName = $order->specifications;
                                            }
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900">{{ $productName ?? 'Custom Product' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ Str::limit($order->specifications, 30) }}</div>
                                    @else
                                        <div class="text-sm font-medium text-gray-900">Custom Product</div>
                                    @endif
                                    
                                    @if($order->patterns && is_array($order->patterns))
                                        @php
                                            $patternCount = count($order->patterns);
                                        @endphp
                                        @if($patternCount > 0)
                                            <div class="flex items-center mt-2">
                                                <span class="inline-flex items-center px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                                    </svg>
                                                    {{ $patternCount }} pattern{{ $patternCount != 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                        </svg>
                                        <span class="text-sm font-semibold text-gray-900">{{ $order->quantity }}</span>
                                    </div>
                                </td>

                                <!-- Enhanced Status Badge -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'pending' => [
                                                'bg' => 'bg-yellow-100',
                                                'text' => 'text-yellow-800',
                                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'Waiting for admin review'
                                            ],
                                            'price_quoted' => [
                                                'bg' => 'bg-blue-100',
                                                'text' => 'text-blue-800',
                                                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'Admin has sent you a price quote'
                                            ],
                                            'approved' => [
                                                'bg' => 'bg-green-100',
                                                'text' => 'text-green-800',
                                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'You approved the quoted price'
                                            ],
                                            'processing' => [
                                                'bg' => 'bg-purple-100',
                                                'text' => 'text-purple-800',
                                                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                                'description' => 'Order in production'
                                            ],
                                            'completed' => [
                                                'bg' => 'bg-green-100',
                                                'text' => 'text-green-800',
                                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'Order completed'
                                            ],
                                            'rejected' => [
                                                'bg' => 'bg-red-100',
                                                'text' => 'text-red-800',
                                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'Order rejected'
                                            ],
                                            'cancelled' => [
                                                'bg' => 'bg-red-100',
                                                'text' => 'text-red-800',
                                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                'description' => 'Order cancelled'
                                            ],
                                        ];

                                        $config = $statusConfig[$order->status] ?? [
                                            'bg' => 'bg-gray-100',
                                            'text' => 'text-gray-800',
                                            'icon' => '',
                                            'description' => 'Unknown status'
                                        ];

                                        $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
                                        $deliveryHint = null;
                                        if ($deliveryType === 'delivery' && $order->status === 'delivered') {
                                            $deliveryHint = 'Out for Delivery';
                                        } elseif ($deliveryType === 'pickup' && $order->status === 'delivered') {
                                            $deliveryHint = 'Ready for Pickup';
                                        } elseif ($order->status === 'completed') {
                                            $deliveryHint = 'Order Received';
                                        }
                                    @endphp
                                    <div class="space-y-1">
                                        <div class="relative group">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }} cursor-help">
                                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                                                </svg>
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                            
                                            <!-- Tooltip -->
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                                {{ $config['description'] }}
                                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                                    <div class="border-4 border-transparent border-t-gray-900"></div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($deliveryHint)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold">
                                                {{ $deliveryHint }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Enhanced Payment Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->final_price)
                                        <div>
                                            <span class="text-lg font-bold text-gray-900">₱{{ number_format($order->final_price, 0) }}</span>
                                            @if($order->payment_status)
                                                @php
                                                    $paymentConfig = [
                                                        // Treat both 'pending' (DB default) and 'unpaid' as the same visual state
                                                        'pending' => [
                                                            'bg' => 'bg-red-100',
                                                            'text' => 'text-red-800',
                                                            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                                        ],
                                                        'unpaid' => [
                                                            'bg' => 'bg-red-100',
                                                            'text' => 'text-red-800',
                                                            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                                        ],
                                                        'paid' => [
                                                            'bg' => 'bg-green-100',
                                                            'text' => 'text-green-800',
                                                            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                                        ],
                                                        'pending_verification' => [
                                                            'bg' => 'bg-yellow-100',
                                                            'text' => 'text-yellow-800',
                                                            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                                                        ]
                                                    ];
                                                    $payConfig = $paymentConfig[$order->payment_status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => ''];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $payConfig['bg'] }} {{ $payConfig['text'] }} ml-2">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $payConfig['icon'] }}"/>
                                                    </svg>
                                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">Not priced</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                                    @if($order->status === 'pending' && $order->created_at->diffInDays(now()) > 2)
                                        <div class="text-xs text-red-600 font-medium mt-1">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $order->created_at->diffInDays(now()) }} days pending
                                        </div>
                                    @endif
                                </td>

                                <!-- Enhanced Actions -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('custom_orders.show', $order->id) }}"
                                           class="inline-flex items-center px-3 py-2 bg-red-700 hover:bg-red-800 text-white text-sm font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        
                                        <!-- Quick Actions -->
                                        @if($order->status === 'price_quoted' && $order->user_notified_at)
                                            <div class="flex gap-1">
                                                <button onclick="acceptPrice({{ $order->id }})" 
                                                        class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                                                        title="Accept Price">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                                <button onclick="rejectPrice({{ $order->id }})" 
                                                        class="p-2 bg-red-700 hover:bg-red-800 text-white rounded-lg transition-colors"
                                                        title="Reject Price">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                        
                                        @if($order->status === 'approved' && !in_array($order->payment_status, ['paid', 'pending_verification']))
                                            <a href="{{ route('custom_orders.payment', $order->id) }}"
                                               class="inline-flex items-center px-3 py-2 bg-red-700 hover:bg-red-800 text-white text-sm font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pay
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-red-700 to-red-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-3">No Custom Orders Yet</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">Start creating personalized products tailored to your exact specifications and requirements.</p>
                
                </div>
        @endif

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

{{-- Enhanced JavaScript for User Interface --}}
<script>
// Global variables
let currentFilter = 'all';
let notificationTimeout;

// Initialize the interface
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    checkForNotifications();
    startAutoRefresh();
});

function initializeEventListeners() {
    // Search functionality
    document.getElementById('userSearchInput').addEventListener('input', handleUserSearch);

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status || 'all';
            filterUserOrders(status);
        });
    });
}

function handleUserSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    
    document.querySelectorAll('.order-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterUserOrders(status) {
    currentFilter = status;

    // Update filter button active styles
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const isActive = (btn.dataset.status || 'all') === status;
        btn.classList.remove('bg-red-100', 'text-red-800');
        btn.classList.remove('bg-purple-100', 'text-purple-700');
        btn.classList.remove('hover:bg-red-50', 'hover:bg-gray-100');

        if (isActive) {
            btn.classList.add('bg-red-100', 'text-red-800');
        } else {
            btn.classList.add('hover:bg-red-50');
        }
    });

    // Filter orders
    document.querySelectorAll('.order-row').forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function checkForUpdates() {
    const refreshBtn = event.target;
    const originalContent = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Checking...';
    refreshBtn.disabled = true;
    
    // API call to check for updates
    fetch('/api/v1/custom-orders/status-check', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'No new updates', 'info');
            if (data.has_updates) {
                // Update the page with new data
                location.reload();
            }
        } else {
            showNotification('Failed to check for updates', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error while checking updates', 'error');
    })
    .finally(() => {
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    });
}

function checkForNotifications() {
    // Check for orders that need user attention
    const awaitingDecision = document.querySelectorAll('[data-status="price_quoted"]');
    if (awaitingDecision.length > 0) {
        showNotification(`You have ${awaitingDecision.length} order(s) awaiting your decision!`, 'warning', 10000);
    }
}

function acceptPrice(orderId) {
    if (confirm('Are you sure you want to accept this price?')) {
        submitPriceDecision(orderId, 'accept');
    }
}

function rejectPrice(orderId) {
    const reason = prompt('Please provide a reason for rejecting this price:');
    if (reason) {
        submitPriceDecision(orderId, 'reject', reason);
    }
}

function submitPriceDecision(orderId, decision, reason = '') {
    const url = `/api/custom-orders/${orderId}/${decision}-price`;
    const payload = decision === 'reject' ? { rejection_reason: reason } : {};
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Price ${decision}ed successfully!`, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to submit decision', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error', 'error');
    });
}

function showNotification(message, type = 'info', duration = 5000) {
    const notificationArea = document.getElementById('notificationArea');
    
    // Clear existing timeout
    if (notificationTimeout) {
        clearTimeout(notificationTimeout);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `p-4 rounded-lg shadow-lg border-l-4 mb-4 transform transition-all duration-500 ${
        type === 'error' ? 'bg-red-50 border-red-500 text-red-800' :
        type === 'success' ? 'bg-green-50 border-green-500 text-green-800' :
        type === 'warning' ? 'bg-yellow-50 border-yellow-500 text-yellow-800' :
        'bg-blue-50 border-blue-500 text-blue-800'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 ${
                    type === 'error' ? 'text-red-500' :
                    type === 'success' ? 'text-green-500' :
                    type === 'warning' ? 'text-yellow-500' :
                    'text-blue-500'
                }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${
                        type === 'error' ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' :
                        type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' :
                        type === 'warning' ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z' :
                        'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                    }"/>
                </svg>
                <div>
                    <p class="font-semibold">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                    <p class="text-sm">${message}</p>
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    notificationArea.appendChild(notification);
    
    // Auto-remove after duration
    notificationTimeout = setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, duration);
}

function startAutoRefresh() {
    // Auto-refresh every 60 seconds
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            checkForUpdates();
        }
    }, 60000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch(event.key) {
            case 'r':
                event.preventDefault();
                checkForUpdates();
                break;
            case 'f':
                event.preventDefault();
                document.getElementById('userSearchInput').focus();
                break;
        }
    }
});

// Real-time order status updates (WebSocket simulation)
function simulateRealTimeUpdates() {
    // This would normally use WebSockets or Server-Sent Events
    // For now, we'll simulate with periodic polling
    setInterval(() => {
        fetch('/api/v1/custom-orders/real-time-updates', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.updates && data.data.updates.length > 0) {
                data.data.updates.forEach(update => {
                    showNotification(`Order #${update.order_id} status updated to: ${update.status}`, 'info', 8000);
                });
                // Optionally refresh the page or update specific elements
                if (data.data.page_refresh) {
                    location.reload();
                }
            }
        })
        .catch(error => {
            console.log('Real-time updates check failed:', error);
        });
    }, 30000); // Check every 30 seconds
}

// Initialize real-time updates
simulateRealTimeUpdates();
</script>

<style>
/* Additional styles for enhanced interface */
.order-row {
    transition: all 0.3s ease;
}

.order-row:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Notification animations */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-enter {
    animation: slideInRight 0.3s ease-out;
}

/* Pulse animation for urgent indicators */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
}

.animate-pulse {
    animation: pulse 2s infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-container {
        overflow-x: auto;
    }
    
    .filter-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-btn {
        width: 100%;
    }
}

/* Loading spinner */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>