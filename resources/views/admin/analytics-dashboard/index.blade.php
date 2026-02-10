@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   ANALYTICS DASHBOARD STYLES
   ═══════════════════════════════════════════════════ */

/* KPI Cards */
.kpi-card {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}
.kpi-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    border-color: rgba(128, 0, 0, 0.3);
}
.kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #800000, #b91c1c);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}
.kpi-card:hover::after {
    transform: scaleX(1);
}
.kpi-card.active {
    border-color: #800000;
    box-shadow: 0 8px 25px rgba(128,0,0,0.15);
}
.kpi-card.active::after {
    transform: scaleX(1);
}

/* Change Indicators */
.change-up { color: #059669; }
.change-down { color: #dc2626; }

/* Section Cards */
.report-section {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}
.report-section:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* Chart Containers */
.chart-container {
    position: relative;
    width: 100%;
    min-height: 320px;
}

/* Period Toggle */
.period-btn {
    padding: 0.5rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #d1d5db;
    background: white;
    color: #6b7280;
}
.period-btn:hover {
    background: #f3f4f6;
    border-color: #800000;
    color: #800000;
}
.period-btn.active {
    background: #800000;
    color: white;
    border-color: #800000;
    box-shadow: 0 2px 8px rgba(128,0,0,0.3);
}

/* Print Checkboxes */
.print-checkbox {
    accent-color: #800000;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Table Styles */
.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.data-table thead th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 5;
}
.data-table tbody tr {
    transition: background-color 0.15s ease;
}
.data-table tbody tr:hover {
    background-color: #fef2f2;
}
.data-table tbody td {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
}

/* Smooth scroll for section navigation */
html {
    scroll-behavior: smooth;
}

/* Badge styles */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

/* Animate sections on scroll */
.fade-in-section {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════════════
   PRINT STYLES
   ═══════════════════════════════════════════════════ */
@media print {
    /* Hide non-printable elements */
    .no-print,
    aside,
    .sidebar-mobile,
    .sidebar-overlay,
    nav:not(.print-nav),
    .mobile-header,
    header,
    .period-filter-bar,
    .print-controls,
    .kpi-card,
    [x-data],
    .fixed,
    button:not(.print-keep),
    .scroll-to-top {
        display: none !important;
    }

    /* Show only selected print sections */
    .print-section {
        display: none !important;
    }
    .print-section.print-selected {
        display: block !important;
    }

    /* Layout resets */
    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 11pt !important;
        line-height: 1.5 !important;
    }
    main, .main-content, .min-h-screen, .flex.min-h-screen > div {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 10px !important;
    }
    .flex { display: block !important; }
    [class*="ml-"] { margin-left: 0 !important; }

    @page {
        margin: 1.5cm;
        size: A4 portrait;
    }

    /* Print header */
    .print-report-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #800000;
    }
    .print-report-header h1 {
        font-size: 22pt;
        color: #800000;
        margin-bottom: 5px;
    }
    .print-report-header p {
        font-size: 10pt;
        color: #666;
    }

    /* Cards and borders */
    .report-section, .bg-white, [class*="rounded"] {
        box-shadow: none !important;
        transform: none !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
        margin-bottom: 12px !important;
    }

    /* Remove backgrounds */
    .bg-gradient-to-br, .bg-gradient-to-b, .bg-gradient-to-r, [class*="bg-gradient"] {
        background: white !important;
        color: black !important;
    }

    /* Tables */
    table { width: 100% !important; border-collapse: collapse !important; font-size: 9pt !important; }
    th, td { border: 1px solid #ccc !important; padding: 6px 8px !important; text-align: left !important; }
    th { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Charts - show at reasonable size */
    .chart-container { min-height: 250px !important; max-height: 350px !important; page-break-inside: avoid; }
    canvas { max-height: 300px !important; }

    /* Page breaks */
    .page-break-before { page-break-before: always; }
    h2, h3 { page-break-after: avoid; }

    /* Disable animations */
    * { animation: none !important; transition: none !important; }

    /* Grid keep */
    .grid { display: grid !important; }
}
</style>
@endpush

@section('content')
<div id="analytics-app" class="space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         PRINT HEADER (only visible when printing)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="print-report-header hidden">
        <h1>YAKAN Analytics Report</h1>
        <p>Period: {{ ucfirst($period) }} &mdash; {{ $dateRange['start']->format('M d, Y') }} to {{ $dateRange['end']->format('M d, Y') }}</p>
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         HEADER & FILTERS
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print bg-gradient-to-r from-[#800000] to-[#b91c1c] rounded-2xl p-6 md:p-8 text-white shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1">
                    <i class="fas fa-chart-pie mr-2"></i>Analytics Dashboard
                </h1>
                <p class="text-red-100 text-sm md:text-base">Comprehensive sales, products, and user insights</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Export Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-download"></i> Export
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50">
                        <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'all', 'period' => $period]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-file-csv mr-2 text-green-600"></i>Export All (CSV)</a>
                        <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'revenue', 'period' => $period]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-coins mr-2 text-yellow-600"></i>Revenue Report</a>
                        <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'products', 'period' => $period]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-box mr-2 text-blue-600"></i>Product Sales</a>
                        <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'transactions', 'period' => $period]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-receipt mr-2 text-purple-600"></i>Transactions</a>
                        <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'users', 'period' => $period]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-users mr-2 text-indigo-600"></i>Users Report</a>
                    </div>
                </div>
                {{-- Print Button --}}
                <button onclick="openPrintDialog()" class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-print"></i> Print Reports
                </button>
            </div>
        </div>
    </div>

    {{-- ── Period Filter Bar ──────────────────────────────────── --}}
    <div class="no-print period-filter-bar bg-white rounded-xl shadow-sm border p-4">
        <form method="GET" action="{{ route('admin.analytics-dashboard.index') }}" id="filterForm" class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                <i class="fas fa-calendar-alt text-[#800000]"></i> Filter by:
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['daily' => 'Today', 'weekly' => 'This Week', 'monthly' => 'This Month', 'yearly' => 'This Year', 'all' => 'All Time'] as $key => $label)
                    <button type="submit" name="period" value="{{ $key }}"
                        class="period-btn {{ $period === $key ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2 ml-auto">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#800000] focus:border-transparent" placeholder="Start">
                <span class="text-gray-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#800000] focus:border-transparent" placeholder="End">
                <button type="submit" class="px-4 py-2 bg-[#800000] text-white text-sm rounded-lg hover:bg-[#600000] transition">
                    <i class="fas fa-filter mr-1"></i> Apply
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         KPI CARDS (clickable → navigates to related section)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 no-print">
        {{-- Total Revenue --}}
        <div class="kpi-card bg-white rounded-xl shadow-sm p-6" onclick="scrollToSection('section-revenue')" id="kpi-revenue">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Revenue</p>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</h3>
                    <div class="flex items-center mt-2 text-sm">
                        @if($revenueChange >= 0)
                            <span class="change-up flex items-center"><i class="fas fa-arrow-up mr-1"></i>{{ $revenueChange }}%</span>
                        @else
                            <span class="change-down flex items-center"><i class="fas fa-arrow-down mr-1"></i>{{ abs($revenueChange) }}%</span>
                        @endif
                        <span class="text-gray-400 ml-2">vs prev period</span>
                    </div>
                </div>
                <div class="p-3 bg-green-100 rounded-xl">
                    <i class="fas fa-peso-sign text-xl text-green-600"></i>
                </div>
            </div>
        </div>

        {{-- Total Sales --}}
        <div class="kpi-card bg-white rounded-xl shadow-sm p-6" onclick="scrollToSection('section-transactions')" id="kpi-sales">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Sales</p>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($totalSales) }}</h3>
                    <div class="flex items-center mt-2 text-sm">
                        @if($salesChange >= 0)
                            <span class="change-up flex items-center"><i class="fas fa-arrow-up mr-1"></i>{{ $salesChange }}%</span>
                        @else
                            <span class="change-down flex items-center"><i class="fas fa-arrow-down mr-1"></i>{{ abs($salesChange) }}%</span>
                        @endif
                        <span class="text-gray-400 ml-2">vs prev period</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-100 rounded-xl">
                    <i class="fas fa-shopping-cart text-xl text-blue-600"></i>
                </div>
            </div>
        </div>

        {{-- Best-Selling Product --}}
        <div class="kpi-card bg-white rounded-xl shadow-sm p-6" onclick="scrollToSection('section-products')" id="kpi-bestseller">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 mb-1">Best-Selling Product</p>
                    @if($bestSellingProduct)
                        <h3 class="text-lg font-bold text-gray-900 truncate" title="{{ $bestSellingProduct['name'] }}">{{ $bestSellingProduct['name'] }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $bestSellingProduct['sold'] }} sold &middot; ₱{{ number_format($bestSellingProduct['revenue'], 2) }}</p>
                    @else
                        <h3 class="text-lg font-bold text-gray-400">No sales yet</h3>
                    @endif
                </div>
                <div class="p-3 bg-amber-100 rounded-xl flex-shrink-0">
                    <i class="fas fa-trophy text-xl text-amber-600"></i>
                </div>
            </div>
        </div>

        {{-- Total Users --}}
        <div class="kpi-card bg-white rounded-xl shadow-sm p-6" onclick="scrollToSection('section-users')" id="kpi-users">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Users</p>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($totalUsers) }}</h3>
                    <p class="text-sm text-gray-500 mt-2">
                        <span class="text-green-600 font-semibold">+{{ $newUsersInPeriod }}</span> new this period
                    </p>
                </div>
                <div class="p-3 bg-purple-100 rounded-xl">
                    <i class="fas fa-users text-xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         PRINT DIALOG (modal)
         ═══════════════════════════════════════════════════════════ --}}
    <div id="printDialog" class="no-print fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" onclick="if(event.target===this)closePrintDialog()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-[#800000] to-[#b91c1c] px-6 py-4 text-white">
                <h2 class="text-lg font-bold"><i class="fas fa-print mr-2"></i>Print Reports</h2>
                <p class="text-red-100 text-sm">Select reports to include in your printout</p>
            </div>
            <div class="p-6 space-y-4">
                <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition">
                    <input type="checkbox" class="print-checkbox" id="print-select-all" onchange="toggleAllPrintSections(this.checked)" checked>
                    <div>
                        <span class="font-semibold text-gray-800">Select All / Deselect All</span>
                        <p class="text-xs text-gray-500">Toggle all reports at once</p>
                    </div>
                </label>
                <hr class="border-gray-200">
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="print-checkbox print-section-toggle" data-section="section-revenue" checked>
                    <div>
                        <span class="font-medium text-gray-700"><i class="fas fa-chart-line mr-2 text-green-600"></i>Total Revenue</span>
                        <p class="text-xs text-gray-500">Revenue graph and summary</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="print-checkbox print-section-toggle" data-section="section-products" checked>
                    <div>
                        <span class="font-medium text-gray-700"><i class="fas fa-box mr-2 text-blue-600"></i>Product Sales Report</span>
                        <p class="text-xs text-gray-500">Sales per product with quantities and revenue</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="print-checkbox print-section-toggle" data-section="section-transactions" checked>
                    <div>
                        <span class="font-medium text-gray-700"><i class="fas fa-receipt mr-2 text-purple-600"></i>Transaction History</span>
                        <p class="text-xs text-gray-500">Date, transaction ID, products, total amount</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="print-checkbox print-section-toggle" data-section="section-users" checked>
                    <div>
                        <span class="font-medium text-gray-700"><i class="fas fa-users mr-2 text-indigo-600"></i>Total Users</span>
                        <p class="text-xs text-gray-500">User count summary and breakdown</p>
                    </div>
                </label>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
                <button onclick="closePrintDialog()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                <button onclick="executePrint()" class="px-6 py-2 text-sm font-medium text-white bg-[#800000] rounded-lg hover:bg-[#600000] transition shadow-sm">
                    <i class="fas fa-print mr-2"></i>Print Selected
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 1: SALES PERFORMANCE GRAPHS
         ═══════════════════════════════════════════════════════════ --}}
    <div id="section-sales-graph" class="print-section print-selected fade-in-section">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Sales & Orders Chart --}}
            <div class="report-section bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-chart-bar mr-2 text-[#800000]"></i>Sales Performance
                    </h2>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">{{ ucfirst($period) }}</span>
                </div>
                <div class="chart-container">
                    <canvas id="salesPerformanceChart"></canvas>
                </div>
            </div>

            {{-- Orders by Status (Doughnut) --}}
            <div class="report-section bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-chart-pie mr-2 text-[#800000]"></i>Orders by Status
                    </h2>
                </div>
                <div class="chart-container flex items-center justify-center">
                    <canvas id="orderStatusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Best-Selling vs Low-Selling Products Graph --}}
    <div class="print-section print-selected fade-in-section">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Best Sellers --}}
            <div class="report-section bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-fire mr-2 text-orange-500"></i>Best-Selling Products
                    </h2>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold">Top 10</span>
                </div>
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            {{-- Low Sellers --}}
            <div class="report-section bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>Low-Selling Products
                    </h2>
                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full font-semibold">Bottom 10</span>
                </div>
                <div class="chart-container">
                    <canvas id="lowProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 2: TOTAL REVENUE REPORT
         ═══════════════════════════════════════════════════════════ --}}
    <div id="section-revenue" class="print-section print-selected fade-in-section">
        <div class="report-section bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-coins mr-2 text-yellow-500"></i>Total Revenue Report
                </h2>
                <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'revenue', 'period' => $period]) }}"
                   class="no-print text-sm text-[#800000] hover:underline font-medium">
                    <i class="fas fa-download mr-1"></i>Export CSV
                </a>
            </div>

            {{-- Revenue Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <p class="text-sm text-green-700 font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-800">₱{{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <p class="text-sm text-blue-700 font-medium">Total Orders</p>
                    <p class="text-2xl font-bold text-blue-800">{{ number_format($totalSales) }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <p class="text-sm text-purple-700 font-medium">Avg Order Value</p>
                    <p class="text-2xl font-bold text-purple-800">₱{{ number_format($averageOrderValue, 2) }}</p>
                </div>
            </div>

            {{-- Revenue Line Chart --}}
            <div class="chart-container mb-6">
                <canvas id="revenueLineChart"></canvas>
            </div>

            {{-- Payment Method Breakdown --}}
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Payment Method Breakdown</h3>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th class="text-center">Orders</th>
                            <th class="text-right">Total Amount</th>
                            <th class="text-right">% of Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentBreakdown as $pm)
                        <tr>
                            <td class="font-medium">
                                <span class="inline-flex items-center">
                                    @if(strtolower($pm->method) === 'gcash')
                                        <i class="fas fa-mobile-alt mr-2 text-blue-500"></i>
                                    @elseif(strtolower($pm->method) === 'cod')
                                        <i class="fas fa-truck mr-2 text-orange-500"></i>
                                    @else
                                        <i class="fas fa-credit-card mr-2 text-gray-500"></i>
                                    @endif
                                    {{ ucfirst($pm->method) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $pm->count }}</td>
                            <td class="text-right font-semibold">₱{{ number_format($pm->total ?? 0, 2) }}</td>
                            <td class="text-right text-gray-500">
                                {{ $totalRevenue > 0 ? number_format((($pm->total ?? 0) / $totalRevenue) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-8">No payment data available</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 3: PRODUCT SALES REPORT
         ═══════════════════════════════════════════════════════════ --}}
    <div id="section-products" class="print-section print-selected fade-in-section">
        <div class="report-section bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-box-open mr-2 text-blue-500"></i>Product Sales Report
                </h2>
                <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'products', 'period' => $period]) }}"
                   class="no-print text-sm text-[#800000] hover:underline font-medium">
                    <i class="fas fa-download mr-1"></i>Export CSV
                </a>
            </div>

            <div class="overflow-x-auto" style="max-height: 600px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-center">Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productSalesReport as $i => $item)
                        <tr>
                            <td class="text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="" class="w-10 h-10 rounded-lg object-cover border no-print" onerror="this.style.display='none'">
                                    @endif
                                    <span class="font-medium">{{ $item->product->name ?? 'Unknown Product' }}</span>
                                </div>
                            </td>
                            <td class="text-center font-semibold">{{ number_format($item->qty_sold) }}</td>
                            <td class="text-right">₱{{ number_format($item->avg_price, 2) }}</td>
                            <td class="text-right font-semibold text-green-700">₱{{ number_format($item->revenue, 2) }}</td>
                            <td class="text-center">
                                @if($item->product)
                                    @if($item->product->stock <= 0)
                                        <span class="status-badge bg-red-100 text-red-800">Out of Stock</span>
                                    @elseif($item->product->stock <= 5)
                                        <span class="status-badge bg-yellow-100 text-yellow-800">{{ $item->product->stock }}</span>
                                    @else
                                        <span class="status-badge bg-green-100 text-green-800">{{ $item->product->stock }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($i === 0)
                                    <span class="text-yellow-500"><i class="fas fa-trophy"></i></span>
                                @elseif($i === 1)
                                    <span class="text-gray-400"><i class="fas fa-medal"></i></span>
                                @elseif($i === 2)
                                    <span class="text-amber-700"><i class="fas fa-medal"></i></span>
                                @else
                                    <span class="text-gray-300">#{{ $i + 1 }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-8"><i class="fas fa-inbox text-3xl mb-2 block"></i>No product sales data available</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 4: TRANSACTION HISTORY
         ═══════════════════════════════════════════════════════════ --}}
    <div id="section-transactions" class="print-section print-selected fade-in-section">
        <div class="report-section bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-receipt mr-2 text-purple-500"></i>Transaction History
                </h2>
                <div class="flex items-center gap-3 no-print">
                    <div class="relative">
                        <input type="text" id="transactionSearch" placeholder="Search transactions..."
                            class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent w-64"
                            onkeyup="filterTransactions(this.value)">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                    <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'transactions', 'period' => $period]) }}"
                       class="text-sm text-[#800000] hover:underline font-medium">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto" style="max-height: 700px; overflow-y: auto;">
                <table class="data-table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order ID</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $order)
                        <tr class="transaction-row">
                            <td class="whitespace-nowrap text-sm">{{ $order->created_at->format('M d, Y') }}<br><span class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</span></td>
                            <td class="font-mono font-semibold text-sm">#{{ $order->id }}</td>
                            <td class="text-sm text-gray-600">{{ $order->order_ref ?? '—' }}</td>
                            <td class="text-sm">{{ $order->user->name ?? ($order->customer_name ?? 'Guest') }}</td>
                            <td class="text-sm max-w-xs">
                                <div class="truncate" title="{{ $order->items->map(fn($i) => ($i->product->name ?? 'Unknown') . ' x' . $i->quantity)->join(', ') }}">
                                    @foreach($order->items->take(2) as $item)
                                        <span class="inline-block bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ ($item->product->name ?? 'Unknown') }} &times;{{ $item->quantity }}</span>
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <span class="text-xs text-gray-400">+{{ $order->items->count() - 2 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-sm">
                                <span class="status-badge bg-gray-100 text-gray-700">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'pending_confirmation' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-indigo-100 text-indigo-800',
                                        'shipped' => 'bg-cyan-100 text-cyan-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $color = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="status-badge {{ $color }}">{{ ucfirst(str_replace('_', ' ', $order->status ?? 'N/A')) }}</span>
                            </td>
                            <td class="text-right font-semibold whitespace-nowrap">₱{{ number_format($order->total_amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-gray-400 py-8"><i class="fas fa-inbox text-3xl mb-2 block"></i>No transactions found for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->count() > 0)
            <div class="mt-4 pt-4 border-t text-sm text-gray-500 flex items-center justify-between">
                <span>Showing {{ $transactions->count() }} transactions</span>
                <span class="font-semibold text-gray-800">
                    Total: ₱{{ number_format($transactions->sum('total_amount'), 2) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 5: TOTAL USERS
         ═══════════════════════════════════════════════════════════ --}}
    <div id="section-users" class="print-section print-selected fade-in-section">
        <div class="report-section bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-users mr-2 text-indigo-500"></i>Total Users
                </h2>
                <a href="{{ route('admin.analytics-dashboard.export', ['type' => 'users', 'period' => $period]) }}"
                   class="no-print text-sm text-[#800000] hover:underline font-medium">
                    <i class="fas fa-download mr-1"></i>Export CSV
                </a>
            </div>

            {{-- Users Summary Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200 text-center">
                    <p class="text-2xl font-bold text-indigo-800">{{ $totalUsers }}</p>
                    <p class="text-xs text-indigo-600 font-medium mt-1">Total Users</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200 text-center">
                    <p class="text-2xl font-bold text-green-800">{{ $newUsersInPeriod }}</p>
                    <p class="text-xs text-green-600 font-medium mt-1">New This Period</p>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200 text-center">
                    <p class="text-2xl font-bold text-amber-800">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                    <p class="text-xs text-amber-600 font-medium mt-1">Admins</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl p-4 border border-cyan-200 text-center">
                    <p class="text-2xl font-bold text-cyan-800">{{ \App\Models\User::whereNotNull('email_verified_at')->count() }}</p>
                    <p class="text-xs text-cyan-600 font-medium mt-1">Verified</p>
                </div>
            </div>

            {{-- User Growth Chart --}}
            <div class="chart-container mb-6" style="min-height: 280px;">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ═══════════════════════════════════════════════════════════
    //  CHART DATA (from PHP)
    // ═══════════════════════════════════════════════════════════
    const salesGraphData   = @json($salesGraphData);
    const revenueGraphData = @json($revenueGraphData);
    const topProducts      = @json($topProducts);
    const lowProducts      = @json($lowProducts);
    const userGrowthData   = @json($userGrowthData);
    const ordersByStatus   = @json($ordersByStatus);

    const maroon     = '#800000';
    const maroonLight = 'rgba(128, 0, 0, 0.1)';

    // ═══════════════════════════════════════════════════════════
    //  1. SALES PERFORMANCE CHART (Bar + Line combo)
    // ═══════════════════════════════════════════════════════════
    const spCtx = document.getElementById('salesPerformanceChart');
    if (spCtx) {
        new Chart(spCtx, {
            type: 'bar',
            data: {
                labels: salesGraphData.labels,
                datasets: [
                    {
                        label: 'Revenue (₱)',
                        data: salesGraphData.revenue,
                        backgroundColor: 'rgba(128, 0, 0, 0.7)',
                        borderColor: maroon,
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        label: 'Orders',
                        data: salesGraphData.orders,
                        type: 'line',
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563eb',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.label.includes('Revenue')) return '₱' + Number(ctx.raw).toLocaleString();
                                return ctx.raw + ' orders';
                            }
                        }
                    }
                },
                scales: {
                    y:  { position: 'left',  title: { display: true, text: 'Revenue (₱)' }, ticks: { callback: v => '₱' + v.toLocaleString() }, grid: { color: '#f3f4f6' } },
                    y1: { position: 'right', title: { display: true, text: 'Orders' }, grid: { drawOnChartArea: false } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  2. ORDER STATUS DOUGHNUT
    // ═══════════════════════════════════════════════════════════
    const osCtx = document.getElementById('orderStatusChart');
    if (osCtx) {
        const statusLabels = Object.keys(ordersByStatus).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
        const statusValues = Object.values(ordersByStatus);
        const statusColors = ['#f59e0b', '#3b82f6', '#6366f1', '#06b6d4', '#10b981', '#ef4444', '#9ca3af', '#a855f7'];

        new Chart(osCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors.slice(0, statusValues.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 12 } } },
                    tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' orders' } }
                },
                cutout: '55%',
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  3. TOP PRODUCTS HORIZONTAL BAR
    // ═══════════════════════════════════════════════════════════
    const tpCtx = document.getElementById('topProductsChart');
    if (tpCtx && topProducts.length > 0) {
        new Chart(tpCtx, {
            type: 'bar',
            data: {
                labels: topProducts.map(p => p.name.length > 25 ? p.name.substring(0, 25) + '…' : p.name),
                datasets: [{
                    label: 'Units Sold',
                    data: topProducts.map(p => p.sold),
                    backgroundColor: createGradientColors(tpCtx, topProducts.length, '#059669', '#10b981'),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { afterLabel: ctx => '₱' + topProducts[ctx.dataIndex].revenue.toLocaleString() + ' revenue' } }
                },
                scales: {
                    x: { grid: { color: '#f3f4f6' }, title: { display: true, text: 'Units Sold' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  4. LOW PRODUCTS HORIZONTAL BAR
    // ═══════════════════════════════════════════════════════════
    const lpCtx = document.getElementById('lowProductsChart');
    if (lpCtx && lowProducts.length > 0) {
        new Chart(lpCtx, {
            type: 'bar',
            data: {
                labels: lowProducts.map(p => p.name.length > 25 ? p.name.substring(0, 25) + '…' : p.name),
                datasets: [{
                    label: 'Units Sold',
                    data: lowProducts.map(p => p.sold),
                    backgroundColor: createGradientColors(lpCtx, lowProducts.length, '#dc2626', '#f59e0b'),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { afterLabel: ctx => '₱' + lowProducts[ctx.dataIndex].revenue.toLocaleString() + ' revenue' } }
                },
                scales: {
                    x: { grid: { color: '#f3f4f6' }, title: { display: true, text: 'Units Sold' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  5. REVENUE LINE CHART
    // ═══════════════════════════════════════════════════════════
    const rlCtx = document.getElementById('revenueLineChart');
    if (rlCtx) {
        new Chart(rlCtx, {
            type: 'line',
            data: {
                labels: revenueGraphData.labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: revenueGraphData.revenue,
                    borderColor: maroon,
                    backgroundColor: maroonLight,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: maroon,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => '₱' + Number(ctx.raw).toLocaleString() } }
                },
                scales: {
                    y: { ticks: { callback: v => '₱' + v.toLocaleString() }, grid: { color: '#f9fafb' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  6. USER GROWTH CHART
    // ═══════════════════════════════════════════════════════════
    const ugCtx = document.getElementById('userGrowthChart');
    if (ugCtx) {
        new Chart(ugCtx, {
            type: 'bar',
            data: {
                labels: userGrowthData.labels,
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData.users,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.raw + ' new users' } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f9fafb' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  HELPER: Create gradient colors for bar charts
    // ═══════════════════════════════════════════════════════════
    function createGradientColors(canvas, count, startColor, endColor) {
        const colors = [];
        for (let i = 0; i < count; i++) {
            const ratio = count > 1 ? i / (count - 1) : 0;
            colors.push(interpolateColor(startColor, endColor, ratio));
        }
        return colors;
    }

    function interpolateColor(c1, c2, ratio) {
        const hex = c => parseInt(c.slice(1), 16);
        const r1 = hex(c1) >> 16, g1 = (hex(c1) >> 8) & 0xff, b1 = hex(c1) & 0xff;
        const r2 = hex(c2) >> 16, g2 = (hex(c2) >> 8) & 0xff, b2 = hex(c2) & 0xff;
        const r = Math.round(r1 + (r2 - r1) * ratio);
        const g = Math.round(g1 + (g2 - g1) * ratio);
        const b = Math.round(b1 + (b2 - b1) * ratio);
        return `rgba(${r}, ${g}, ${b}, 0.75)`;
    }
});

// ═══════════════════════════════════════════════════════════
//  KPI CARD NAVIGATION
// ═══════════════════════════════════════════════════════════
function scrollToSection(sectionId) {
    // Remove active from all KPI cards
    document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active'));
    // Add active to clicked card
    const kpiMap = {
        'section-revenue': 'kpi-revenue',
        'section-transactions': 'kpi-sales',
        'section-products': 'kpi-bestseller',
        'section-users': 'kpi-users',
    };
    const kpiId = kpiMap[sectionId];
    if (kpiId) document.getElementById(kpiId)?.classList.add('active');

    // Scroll to section
    const el = document.getElementById(sectionId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        el.classList.add('ring-2', 'ring-[#800000]', 'ring-offset-2', 'rounded-xl');
        setTimeout(() => el.classList.remove('ring-2', 'ring-[#800000]', 'ring-offset-2', 'rounded-xl'), 2000);
    }
}

// ═══════════════════════════════════════════════════════════
//  PRINT CONTROLS
// ═══════════════════════════════════════════════════════════
function openPrintDialog() {
    const dialog = document.getElementById('printDialog');
    dialog.classList.remove('hidden');
    dialog.classList.add('flex');
}

function closePrintDialog() {
    const dialog = document.getElementById('printDialog');
    dialog.classList.add('hidden');
    dialog.classList.remove('flex');
}

function toggleAllPrintSections(checked) {
    document.querySelectorAll('.print-section-toggle').forEach(cb => {
        cb.checked = checked;
    });
}

function executePrint() {
    // Mark sections for printing
    document.querySelectorAll('.print-section').forEach(section => {
        section.classList.remove('print-selected');
    });

    document.querySelectorAll('.print-section-toggle').forEach(cb => {
        if (cb.checked) {
            const sectionId = cb.dataset.section;
            const section = document.getElementById(sectionId);
            if (section) section.classList.add('print-selected');
        }
    });

    // Also include the sales graph section if revenue is selected
    const revenueChecked = document.querySelector('[data-section="section-revenue"]')?.checked;
    const salesGraphSection = document.getElementById('section-sales-graph');
    if (revenueChecked && salesGraphSection) {
        salesGraphSection.classList.add('print-selected');
    }

    closePrintDialog();

    // Small delay to allow DOM updates before print
    setTimeout(() => window.print(), 300);
}

// ═══════════════════════════════════════════════════════════
//  TRANSACTION SEARCH FILTER
// ═══════════════════════════════════════════════════════════
function filterTransactions(query) {
    const rows = document.querySelectorAll('.transaction-row');
    const q = query.toLowerCase();
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endpush
