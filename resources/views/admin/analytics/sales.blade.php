@extends('layouts.admin')

@section('title', 'Sales Report')

@push('styles')
<style>
    .metric-card { transition: transform 0.2s, box-shadow 0.2s; }
    .metric-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(128,0,0,0.12); }
    .chart-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #f1f5f9; }
    .period-btn { transition: all 0.15s; }
    .period-btn.active { background: #800000; color: white !important; }
</style>
@endpush

@section('content')
@php
    $dailyLabels  = $salesData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray();
    $dailyOrders  = $salesData->pluck('orders')->map(fn($v) => (int)$v)->toArray();
    $dailyRevenue = $salesData->pluck('revenue')->map(fn($v) => (float)$v)->toArray();

    $monthlyLabels  = $monthlyRevenue->map(fn($m) => \Carbon\Carbon::createFromDate($m->year, $m->month, 1)->format('M Y'))->toArray();
    $monthlyOrders  = $monthlyRevenue->pluck('orders')->map(fn($v) => (int)$v)->toArray();
    $monthlyRev     = $monthlyRevenue->pluck('revenue')->map(fn($v) => (float)$v)->toArray();

    $pmLabels  = $paymentMethods->map(fn($p) => ($p->display_name ?? match($p->payment_method) {
        'online', 'online_banking', 'gcash' => 'GCash',
        'maya' => 'Maya',
        'bank_transfer' => 'Bank Transfer',
        default => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Unknown')),
    }))->toArray();
    $pmTotals  = $paymentMethods->pluck('total')->map(fn($v) => (float)$v)->toArray();
    $pmCounts  = $paymentMethods->pluck('count')->map(fn($v) => (int)$v)->toArray();

    $tpLabels  = $topProducts->map(fn($p) => $p->product->name ?? 'Unknown')->toArray();
    $tpRevenue = $topProducts->pluck('revenue')->map(fn($v) => (float)$v)->toArray();
    $tpSold    = $topProducts->pluck('sold')->map(fn($v) => (int)$v)->toArray();

    $stLabels = $statusBreakdown->pluck('status')->map(fn($s) => ucfirst(str_replace('_',' ',$s)))->toArray();
    $stCounts = $statusBreakdown->pluck('count')->map(fn($v) => (int)$v)->toArray();
@endphp

<div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#a52a2a] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-1">Sales Report</h1>
                <p class="text-red-100 text-sm">Detailed breakdown of revenue and sales performance</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex bg-white/20 rounded-xl p-1 gap-1">
                    @foreach([7 => '7D', 30 => '30D', 90 => '90D'] as $days => $label)
                        <a href="?period={{ $days }}{{ request('auth_token') ? '&auth_token='.request('auth_token') : '' }}"
                           class="period-btn px-3 py-1.5 rounded-lg text-white text-sm font-medium {{ $period == $days ? 'active' : 'hover:bg-white/20' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('admin.dashboard') }}{{ request('auth_token') ? '?auth_token='.request('auth_token') : '' }}"
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-white text-sm font-medium transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $metrics = [
                ['label' => 'Total Revenue',    'value' => '₱'.number_format($totalRevenue, 2),     'sub' => 'All orders',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'grad' => 'from-[#800000] to-[#a52a2a]'],
                ['label' => 'Total Orders',     'value' => number_format($totalOrders),              'sub' => 'All time',    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',                              'grad' => 'from-blue-600 to-blue-400'],
                ['label' => 'Completed Orders', 'value' => number_format($completedOrders),          'sub' => ($totalOrders > 0 ? round($completedOrders/$totalOrders*100) : 0).'% fulfilment', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'grad' => 'from-green-600 to-green-400'],
                ['label' => 'Avg Order Value',  'value' => '₱'.number_format($averageOrderValue, 2), 'sub' => 'Per order',   'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',                                       'grad' => 'from-orange-500 to-amber-400'],
            ];
        @endphp
        @foreach($metrics as $m)
        <div class="metric-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $m['grad'] }} flex items-center justify-center shadow-md mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $m['icon'] }}"/></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $m['value'] }}</p>
            <p class="text-sm font-semibold text-gray-700 mt-0.5">{{ $m['label'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $m['sub'] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Daily Sales Chart -->
    <div class="chart-card">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Daily Sales — Last {{ $period }} Days</h3>
                <p class="text-xs text-gray-400 mt-0.5">Revenue (bars) and order count (line) per day</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm inline-block" style="background:#800000"></span>Revenue</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm inline-block bg-blue-400"></span>Orders</span>
            </div>
        </div>
        @if($salesData->count() > 0)
            <div style="height:280px;"><canvas id="dailySalesChart"></canvas></div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-14 h-14 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="font-medium">No orders in the last {{ $period }} days</p>
            </div>
        @endif
    </div>

    <!-- Monthly Revenue + Order Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="chart-card">
            <div class="mb-5">
                <h3 class="text-lg font-bold text-gray-900">Monthly Revenue</h3>
                <p class="text-xs text-gray-400 mt-0.5">Last 12 months</p>
            </div>
            @if($monthlyRevenue->count() > 0)
                <div style="height:240px;"><canvas id="monthlyRevenueChart"></canvas></div>
            @else
                <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                    <p class="font-medium">No monthly data yet</p>
                </div>
            @endif
        </div>

        <div class="chart-card">
            <div class="mb-5">
                <h3 class="text-lg font-bold text-gray-900">Order Status Breakdown</h3>
                <p class="text-xs text-gray-400 mt-0.5">All orders by status</p>
            </div>
            @if($statusBreakdown->count() > 0)
                <div class="flex items-center gap-4">
                    <div style="width:180px;height:180px;flex-shrink:0;"><canvas id="statusChart"></canvas></div>
                    <div class="space-y-1.5 flex-1 min-w-0" id="statusLegend"></div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                    <p class="font-medium">No order data</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Methods + Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="chart-card">
            <div class="mb-5">
                <h3 class="text-lg font-bold text-gray-900">Payment Methods</h3>
                <p class="text-xs text-gray-400 mt-0.5">Revenue per payment method</p>
            </div>
            @if($paymentMethods->count() > 0)
                <div style="height:200px;"><canvas id="paymentChart"></canvas></div>
                <div class="mt-4 space-y-1">
                    @foreach($paymentMethods as $method)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="font-medium text-gray-800 text-sm">{{ $method->display_name ?? match($method->payment_method) {
                                'online', 'online_banking', 'gcash' => 'GCash',
                                'maya' => 'Maya',
                                'bank_transfer' => 'Bank Transfer',
                                default => ucwords(str_replace('_', ' ', $method->payment_method ?? 'Unknown')),
                            } }}</p>
                            <p class="text-xs text-gray-400">{{ $method->count }} orders</p>
                        </div>
                        <span class="font-bold text-[#800000] text-sm">₱{{ number_format($method->total ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                    <p class="font-medium">No payment data yet</p>
                </div>
            @endif
        </div>

        <div class="chart-card">
            <div class="mb-5">
                <h3 class="text-lg font-bold text-gray-900">Top Products by Revenue</h3>
                <p class="text-xs text-gray-400 mt-0.5">Best performing products</p>
            </div>
            @if($topProducts->count() > 0)
                <div style="height:200px;"><canvas id="topProductsChart"></canvas></div>
                <div class="mt-4 space-y-1">
                    @foreach($topProducts as $i => $item)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                        <span class="w-5 h-5 rounded-full bg-[#800000] text-white text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 text-sm truncate">{{ $item->product->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $item->sold }} sold</p>
                        </div>
                        <span class="font-bold text-[#800000] text-sm">₱{{ number_format($item->revenue ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                    <p class="font-medium">No product sales data yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.color = '#6b7280';
const MAROON = '#800000', MAROON_A = 'rgba(128,0,0,0.13)';

// Daily Sales
@if($salesData->count() > 0)
(function(){
    const labels  = @json($dailyLabels);
    const revenue = @json($dailyRevenue);
    const orders  = @json($dailyOrders);
    new Chart(document.getElementById('dailySalesChart'), {
        data: { labels,
            datasets: [
                { type:'bar',  label:'Revenue (₱)', data:revenue, backgroundColor:MAROON_A, borderColor:MAROON, borderWidth:2, borderRadius:5, yAxisID:'yRev' },
                { type:'line', label:'Orders',       data:orders,  borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.07)', borderWidth:2.5,
                  pointBackgroundColor:'#3b82f6', pointRadius:4, tension:0.35, fill:true, yAxisID:'yOrd' }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            interaction:{ mode:'index', intersect:false },
            plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: c => c.dataset.yAxisID==='yRev' ? ' ₱'+c.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2}) : ' '+c.parsed.y+' orders' } } },
            scales:{
                x:{ grid:{display:false} },
                yRev:{ position:'left',  grid:{color:'#f3f4f6'}, ticks:{callback:v=>'₱'+v.toLocaleString()} },
                yOrd:{ position:'right', grid:{display:false},   ticks:{stepSize:1} }
            }
        }
    });
})();
@endif

// Monthly Revenue
@if($monthlyRevenue->count() > 0)
(function(){
    const labels  = @json($monthlyLabels);
    const revenue = @json($monthlyRev);
    const orders  = @json($monthlyOrders);
    new Chart(document.getElementById('monthlyRevenueChart'), {
        type:'bar',
        data:{ labels, datasets:[
            { label:'Revenue (₱)', data:revenue, backgroundColor:MAROON, borderRadius:7, borderSkipped:false },
            { label:'Orders',      data:orders,  backgroundColor:'#bfdbfe', borderRadius:7, borderSkipped:false }
        ]},
        options:{
            responsive:true, maintainAspectRatio:false,
            interaction:{mode:'index',intersect:false},
            plugins:{ legend:{position:'bottom',labels:{usePointStyle:true,padding:14}}, tooltip:{ callbacks:{ label:c => c.datasetIndex===0 ? ' ₱'+c.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2}) : ' '+c.parsed.y+' orders' } } },
            scales:{ x:{grid:{display:false}}, y:{grid:{color:'#f3f4f6'},ticks:{callback:v=>'₱'+v.toLocaleString()}} }
        }
    });
})();
@endif

// Status Doughnut
@if($statusBreakdown->count() > 0)
(function(){
    const labels = @json($stLabels);
    const counts = @json($stCounts);
    const pal = ['#800000','#3b82f6','#f97316','#8b5cf6','#10b981','#06b6d4','#f59e0b','#ef4444','#6b7280'];
    new Chart(document.getElementById('statusChart'), {
        type:'doughnut',
        data:{ labels, datasets:[{ data:counts, backgroundColor:pal, borderWidth:0, hoverOffset:8 }] },
        options:{ responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.label}: ${c.parsed} orders`}} } }
    });
    const leg = document.getElementById('statusLegend');
    labels.forEach((l,i)=>{
        const d = document.createElement('div');
        d.className='flex items-center justify-between text-sm py-0.5';
        d.innerHTML=`<span class="flex items-center gap-2"><span style="background:${pal[i]};width:10px;height:10px;border-radius:3px;display:inline-block"></span><span class="text-gray-700 truncate max-w-[120px]">${l}</span></span><span class="font-semibold text-gray-800">${counts[i]}</span>`;
        leg.appendChild(d);
    });
})();
@endif

// Payment Methods
@if($paymentMethods->count() > 0)
(function(){
    const labels = @json($pmLabels);
    const totals = @json($pmTotals);
    new Chart(document.getElementById('paymentChart'), {
        type:'bar',
        data:{ labels, datasets:[{ label:'Revenue (₱)', data:totals, backgroundColor:['#800000','#3b82f6','#f97316','#10b981','#8b5cf6'], borderRadius:7, borderSkipped:false }] },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>' ₱'+c.parsed.x.toLocaleString('en-PH',{minimumFractionDigits:2})}} },
            scales:{ x:{grid:{color:'#f3f4f6'},ticks:{callback:v=>'₱'+v.toLocaleString()}}, y:{grid:{display:false}} }
        }
    });
})();
@endif

// Top Products
@if($topProducts->count() > 0)
(function(){
    const labels  = @json($tpLabels);
    const revenue = @json($tpRevenue);
    new Chart(document.getElementById('topProductsChart'), {
        type:'bar',
        data:{ labels, datasets:[{ label:'Revenue (₱)', data:revenue, backgroundColor:MAROON_A, borderColor:MAROON, borderWidth:2, borderRadius:6, borderSkipped:false }] },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>' ₱'+c.parsed.x.toLocaleString('en-PH',{minimumFractionDigits:2})}} },
            scales:{ x:{grid:{color:'#f3f4f6'},ticks:{callback:v=>'₱'+v.toLocaleString()}}, y:{grid:{display:false},ticks:{font:{size:11}}} }
        }
    });
})();
@endif
</script>
@endpush
