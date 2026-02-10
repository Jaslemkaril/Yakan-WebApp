<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yakan WebApp - Print Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body { 
                font-size: 11pt; 
                line-height: 1.4; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            @page { margin: 1.5cm; size: A4 portrait; }
            .print-section { page-break-inside: avoid; }
            canvas { max-height: 280px !important; }
        }
        @media screen {
            body { background: #f3f4f6; }
            .print-page { 
                max-width: 900px; 
                margin: 0 auto; 
                background: white;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
                border-radius: 12px;
                padding: 40px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Action Bar (no-print) -->
    <div class="no-print bg-[#800000] text-white py-4 px-6 sticky top-0 z-50 shadow-lg">
        <div class="max-w-[900px] mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-file-alt text-xl"></i>
                <div>
                    <h1 class="text-lg font-bold">Print Report Preview</h1>
                    <p class="text-sm text-red-200">
                        {{ ucfirst($period) }} Report &bull; 
                        {{ count($sections) }} section(s) selected
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-white text-[#800000] rounded-lg font-semibold hover:bg-gray-100 transition-colors flex items-center space-x-2">
                    <i class="fas fa-print"></i>
                    <span>Print Now</span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 bg-white/20 text-white rounded-lg font-medium hover:bg-white/30 transition-colors flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </div>

    <div class="print-page my-8 mx-4 sm:mx-auto p-6 sm:p-10">
        
        <!-- Report Header -->
        <div class="text-center mb-8 pb-6 border-b-2 border-[#800000]">
            <div class="flex items-center justify-center space-x-3 mb-3">
                <div class="w-12 h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-[#800000]">Yakan WebApp</h1>
                    <p class="text-xs text-gray-500 tracking-widest uppercase">Cultural E-Commerce Platform</p>
                </div>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mt-4">
                @if(count($sections) == 4)
                    Complete Business Report
                @elseif(count($sections) == 1)
                    @if(in_array('revenue', $sections)) Revenue Report
                    @elseif(in_array('product_sales', $sections)) Product Sales Report
                    @elseif(in_array('transactions', $sections)) Transaction History Report
                    @elseif(in_array('users', $sections)) Users Report
                    @endif
                @else
                    Custom Business Report
                @endif
            </h2>
            <div class="flex items-center justify-center space-x-6 mt-3 text-sm text-gray-500">
                <span><i class="fas fa-calendar mr-1"></i> Period: <strong>{{ ucfirst($period) }}</strong></span>
                <span><i class="fas fa-clock mr-1"></i> Generated: <strong>{{ now()->format('M d, Y h:i A') }}</strong></span>
            </div>
        </div>

        {{-- ===== SECTION 1: TOTAL REVENUE ===== --}}
        @if(in_array('revenue', $sections))
        <div class="print-section mb-10">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-8 h-8 bg-[#800000] rounded-lg flex items-center justify-center">
                    <i class="fas fa-peso-sign text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Total Revenue Summary</h3>
            </div>

            <!-- Revenue KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Revenue</p>
                    <p class="text-xl font-bold text-[#800000]">₱{{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Orders</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $totalOrders }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Completed</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $completedOrders }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Avg. Order Value</p>
                    <p class="text-xl font-bold text-[#800000]">₱{{ number_format($averageOrderValue, 2) }}</p>
                </div>
            </div>

            <!-- Revenue Chart -->
            @if(isset($dailyRevenue) && $dailyRevenue->count() > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-chart-line mr-2 text-[#800000]"></i>Revenue Trend
                </h4>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Monthly Revenue Table -->
            @if(isset($monthlyRevenue) && $monthlyRevenue->count() > 0)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-table mr-2 text-[#800000]"></i>Monthly Revenue Breakdown
                </h4>
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#800000] text-white">
                            <th class="text-left py-2.5 px-4 rounded-tl-lg">Month</th>
                            <th class="text-right py-2.5 px-4">Orders</th>
                            <th class="text-right py-2.5 px-4 rounded-tr-lg">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningTotal = 0; @endphp
                        @foreach($monthlyRevenue as $month)
                            @php 
                                $runningTotal += $month->revenue;
                                $monthName = \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y');
                            @endphp
                            <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="py-2.5 px-4 font-medium">{{ $monthName }}</td>
                                <td class="py-2.5 px-4 text-right">{{ $month->orders }}</td>
                                <td class="py-2.5 px-4 text-right font-semibold">₱{{ number_format($month->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-[#fef2f2] font-bold">
                            <td class="py-2.5 px-4 rounded-bl-lg">Total</td>
                            <td class="py-2.5 px-4 text-right">{{ $monthlyRevenue->sum('orders') }}</td>
                            <td class="py-2.5 px-4 text-right text-[#800000] rounded-br-lg">₱{{ number_format($runningTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Payment Method Breakdown -->
            @if(isset($paymentMethods) && $paymentMethods->count() > 0)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-credit-card mr-2 text-[#800000]"></i>Payment Method Breakdown
                </h4>
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#800000] text-white">
                            <th class="text-left py-2.5 px-4 rounded-tl-lg">Payment Method</th>
                            <th class="text-right py-2.5 px-4">Orders</th>
                            <th class="text-right py-2.5 px-4">Amount</th>
                            <th class="text-right py-2.5 px-4 rounded-tr-lg">% of Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $method)
                            <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="py-2.5 px-4 font-medium">
                                    <i class="fas {{ $method->payment_method === 'online' ? 'fa-mobile-alt' : 'fa-university' }} mr-2 text-[#800000]"></i>
                                    {{ $method->payment_method === 'online' ? 'GCash' : ucfirst($method->payment_method ?? 'Unknown') }}
                                </td>
                                <td class="py-2.5 px-4 text-right">{{ $method->count }}</td>
                                <td class="py-2.5 px-4 text-right font-semibold">₱{{ number_format($method->total ?? 0, 2) }}</td>
                                <td class="py-2.5 px-4 text-right">{{ $totalRevenue > 0 ? number_format(($method->total / $totalRevenue) * 100, 1) : 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        @if(count($sections) > 1 && !$loop ?? true)
            <div class="page-break"></div>
        @endif
        @endif

        {{-- ===== SECTION 2: PRODUCT SALES REPORT ===== --}}
        @if(in_array('product_sales', $sections))
        <div class="print-section mb-10 {{ in_array('revenue', $sections) ? 'page-break' : '' }}">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-8 h-8 bg-[#800000] rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Product Sales Report</h3>
            </div>

            <!-- Product Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Products</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $totalProducts }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Products Sold</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $productSales->count() }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Out of Stock</p>
                    <p class="text-xl font-bold text-red-600">{{ $outOfStockCount }}</p>
                </div>
            </div>

            <!-- Top Products Chart -->
            @if($productSales->count() > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-chart-bar mr-2 text-[#800000]"></i>Product Sales Performance
                </h4>
                <div class="h-64">
                    <canvas id="productSalesChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Product Sales Table -->
            @if($productSales->count() > 0)
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-[#800000] text-white">
                        <th class="text-left py-2.5 px-4 rounded-tl-lg">#</th>
                        <th class="text-left py-2.5 px-4">Product Name</th>
                        <th class="text-right py-2.5 px-4">Qty Sold</th>
                        <th class="text-right py-2.5 px-4">Unit Price</th>
                        <th class="text-right py-2.5 px-4 rounded-tr-lg">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productSales as $index => $item)
                        <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="py-2.5 px-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-4 font-medium">{{ $item->product->name ?? 'Unknown Product' }}</td>
                            <td class="py-2.5 px-4 text-right">{{ number_format($item->sold) }}</td>
                            <td class="py-2.5 px-4 text-right">₱{{ number_format($item->product->price ?? 0, 2) }}</td>
                            <td class="py-2.5 px-4 text-right font-semibold">₱{{ number_format($item->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-[#fef2f2] font-bold">
                        <td colspan="2" class="py-2.5 px-4 rounded-bl-lg">Total</td>
                        <td class="py-2.5 px-4 text-right">{{ number_format($productSales->sum('sold')) }}</td>
                        <td class="py-2.5 px-4 text-right">—</td>
                        <td class="py-2.5 px-4 text-right text-[#800000] rounded-br-lg">₱{{ number_format($productSales->sum('revenue'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box-open text-3xl mb-3"></i>
                <p>No product sales data available for this period.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ===== SECTION 3: TRANSACTION HISTORY ===== --}}
        @if(in_array('transactions', $sections))
        <div class="print-section mb-10 {{ in_array('revenue', $sections) || in_array('product_sales', $sections) ? 'page-break' : '' }}">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-8 h-8 bg-[#800000] rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Transaction History</h3>
                <span class="text-sm text-gray-500">({{ $transactions->count() }} transactions)</span>
            </div>

            @if($transactions->count() > 0)
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-[#800000] text-white">
                        <th class="text-left py-2.5 px-3 rounded-tl-lg">Date</th>
                        <th class="text-left py-2.5 px-3">Order ID</th>
                        <th class="text-left py-2.5 px-3">Customer</th>
                        <th class="text-left py-2.5 px-3">Products</th>
                        <th class="text-center py-2.5 px-3">Status</th>
                        <th class="text-right py-2.5 px-3 rounded-tr-lg">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $order)
                        <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="py-2 px-3 whitespace-nowrap">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="py-2 px-3 font-mono font-medium">#{{ $order->order_ref ?? $order->id }}</td>
                            <td class="py-2 px-3">{{ $order->user->name ?? ($order->customer_name ?? 'Guest') }}</td>
                            <td class="py-2 px-3">
                                @foreach($order->items->take(3) as $item)
                                    <span class="inline-block">{{ $item->product->name ?? 'Item' }} (x{{ $item->quantity }}){{ !$loop->last ? ',' : '' }}</span>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <span class="text-gray-400">+{{ $order->items->count() - 3 }} more</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($order->status == 'completed') bg-green-100 text-green-700
                                    @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($order->status == 'shipped') bg-blue-100 text-blue-700
                                    @elseif($order->status == 'cancelled') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="py-2 px-3 text-right font-semibold whitespace-nowrap">₱{{ number_format($order->total_amount ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-[#fef2f2] font-bold text-sm">
                        <td colspan="5" class="py-2.5 px-3 rounded-bl-lg">Grand Total</td>
                        <td class="py-2.5 px-3 text-right text-[#800000] rounded-br-lg">₱{{ number_format($transactions->sum('total_amount'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-receipt text-3xl mb-3"></i>
                <p>No transactions found for this period.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ===== SECTION 4: TOTAL USERS ===== --}}
        @if(in_array('users', $sections))
        <div class="print-section mb-10 {{ in_array('revenue', $sections) || in_array('product_sales', $sections) || in_array('transactions', $sections) ? 'page-break' : '' }}">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-8 h-8 bg-[#800000] rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Total Users Summary</h3>
            </div>

            <!-- User KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Users</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $totalUsers }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Admins</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $adminUsers }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Customers</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $customerUsers }}</p>
                </div>
                <div class="bg-[#fef2f2] border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">New This Month</p>
                    <p class="text-xl font-bold text-[#800000]">{{ $newUsersThisMonth }}</p>
                </div>
            </div>

            <!-- User Growth Chart -->
            @if(isset($userGrowth) && $userGrowth->count() > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-chart-area mr-2 text-[#800000]"></i>User Growth (Last 12 Months)
                </h4>
                <div class="h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
            @endif

            <!-- User Breakdown Table -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-chart-pie mr-2 text-[#800000]"></i>User Role Breakdown
                    </h4>
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-[#800000] text-white">
                                <th class="text-left py-2.5 px-4 rounded-tl-lg">Role</th>
                                <th class="text-right py-2.5 px-4">Count</th>
                                <th class="text-right py-2.5 px-4 rounded-tr-lg">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="py-2.5 px-4">
                                    <i class="fas fa-user-shield mr-2 text-[#800000]"></i>Administrators
                                </td>
                                <td class="py-2.5 px-4 text-right font-semibold">{{ $adminUsers }}</td>
                                <td class="py-2.5 px-4 text-right">{{ $totalUsers > 0 ? number_format(($adminUsers / $totalUsers) * 100, 1) : 0 }}%</td>
                            </tr>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <td class="py-2.5 px-4">
                                    <i class="fas fa-user mr-2 text-[#800000]"></i>Customers
                                </td>
                                <td class="py-2.5 px-4 text-right font-semibold">{{ $customerUsers }}</td>
                                <td class="py-2.5 px-4 text-right">{{ $totalUsers > 0 ? number_format(($customerUsers / $totalUsers) * 100, 1) : 0 }}%</td>
                            </tr>
                            <tr class="bg-[#fef2f2] font-bold">
                                <td class="py-2.5 px-4 rounded-bl-lg">Total</td>
                                <td class="py-2.5 px-4 text-right text-[#800000]">{{ $totalUsers }}</td>
                                <td class="py-2.5 px-4 text-right rounded-br-lg">100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-user-plus mr-2 text-[#800000]"></i>Recent Activity
                    </h4>
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-[#800000] text-white">
                                <th class="text-left py-2.5 px-4 rounded-tl-lg">Period</th>
                                <th class="text-right py-2.5 px-4 rounded-tr-lg">New Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="py-2.5 px-4">This Week</td>
                                <td class="py-2.5 px-4 text-right font-semibold">{{ $newUsersThisWeek }}</td>
                            </tr>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <td class="py-2.5 px-4">This Month</td>
                                <td class="py-2.5 px-4 text-right font-semibold">{{ $newUsersThisMonth }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Customers -->
            @if(isset($topCustomers) && $topCustomers->count() > 0)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-trophy mr-2 text-[#800000]"></i>Top Customers by Orders
                </h4>
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#800000] text-white">
                            <th class="text-left py-2.5 px-4 rounded-tl-lg">#</th>
                            <th class="text-left py-2.5 px-4">Customer Name</th>
                            <th class="text-left py-2.5 px-4">Email</th>
                            <th class="text-right py-2.5 px-4 rounded-tr-lg">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
                            <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="py-2.5 px-4 text-gray-500">{{ $index + 1 }}</td>
                                <td class="py-2.5 px-4 font-medium">{{ $customer->name }}</td>
                                <td class="py-2.5 px-4 text-gray-600">{{ $customer->email }}</td>
                                <td class="py-2.5 px-4 text-right font-semibold">{{ $customer->orders_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Report Footer -->
        <div class="mt-10 pt-6 border-t-2 border-[#800000]">
            <div class="flex items-center justify-between text-xs text-gray-400">
                <p>
                    <i class="fas fa-file-alt mr-1"></i>
                    Report generated by Yakan WebApp Admin Dashboard
                </p>
                <p>
                    Page generated on {{ now()->format('F d, Y \a\t h:i:s A') }}
                </p>
            </div>
            <div class="text-center mt-3 text-xs text-gray-400">
                <p>This report is auto-generated and is confidential. For authorized use only.</p>
            </div>
        </div>
    </div>

    <!-- Charts Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const maroon = '#800000';
        const maroonLight = 'rgba(128, 0, 0, 0.15)';
        const maroonMid = 'rgba(128, 0, 0, 0.6)';

        @if(in_array('revenue', $sections) && isset($dailyRevenue) && $dailyRevenue->count() > 0)
        // Revenue Trend Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'Revenue (₱)',
                    data: {!! json_encode($dailyRevenue->pluck('revenue')) !!},
                    borderColor: maroon,
                    backgroundColor: maroonLight,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: maroon,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '₱' + v.toLocaleString() }
                    }
                }
            }
        });
        @endif

        @if(in_array('product_sales', $sections) && isset($productSales) && $productSales->count() > 0)
        // Product Sales Chart
        new Chart(document.getElementById('productSalesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($productSales->take(10)->map(fn($i) => \Illuminate\Support\Str::limit($i->product->name ?? 'Unknown', 15))) !!},
                datasets: [{
                    label: 'Quantity Sold',
                    data: {!! json_encode($productSales->take(10)->pluck('sold')) !!},
                    backgroundColor: maroonMid,
                    borderColor: maroon,
                    borderWidth: 1,
                    borderRadius: 6,
                }, {
                    label: 'Revenue (₱)',
                    data: {!! json_encode($productSales->take(10)->pluck('revenue')) !!},
                    backgroundColor: 'rgba(128, 0, 0, 0.2)',
                    borderColor: 'rgba(128, 0, 0, 0.5)',
                    borderWidth: 1,
                    borderRadius: 6,
                    hidden: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
        @endif

        @if(in_array('users', $sections) && isset($userGrowth) && $userGrowth->count() > 0)
        // User Growth Chart
        new Chart(document.getElementById('userGrowthChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($userGrowth->map(fn($g) => \Carbon\Carbon::createFromDate($g->year, $g->month, 1)->format('M Y'))) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode($userGrowth->pluck('users')) !!},
                    backgroundColor: maroonMid,
                    borderColor: maroon,
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
        @endif
    });
    </script>
</body>
</html>
