<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\CustomOrder;

class DashboardController extends Controller
{
    /**
     * Test method to isolate controller issues
     */
    public function test()
    {
        return 'DashboardController test method works!';
    }

    /**
     * Show the admin dashboard page.
     */
    public function index(Request $request)
    {
        try {
            // Get filter parameters
            $period = $request->get('period', 'all'); // daily, weekly, yearly, all
            $stockFilter = $request->get('stock', 'all'); // all, out-of-stock, in-stock
            
            // Date filtering based on period
            $dateQuery = \App\Models\Order::query();
            switch ($period) {
                case 'daily':
                    $dateQuery->whereDate('created_at', today());
                    break;
                case 'weekly':
                    $dateQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'yearly':
                    $dateQuery->whereYear('created_at', now()->year);
                    break;
                default:
                    // all time
                    break;
            }
            
            $totalOrders = (clone $dateQuery)->count();
            // Real statuses: pending_confirmation, confirmed, processing, shipped, delivered, cancelled, refunded
            $pendingOrders = (clone $dateQuery)->whereIn('status', ['pending_confirmation', 'pending'])->count();
            $completedOrders = (clone $dateQuery)->whereIn('status', ['delivered', 'completed'])->count();
            $totalUsers = \App\Models\User::count();
            // Revenue counts ALL orders (all statuses)
            $totalRevenue = (float) (clone $dateQuery)->sum('total_amount');

            // New analytics
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            // Check both customer_notes and notes columns
            $ordersWithNotes = (clone $dateQuery)->where(function($q) {
                $q->where(function($q2) {
                    $q2->whereNotNull('customer_notes')->where('customer_notes', '!=', '');
                })->orWhere(function($q2) {
                    $q2->whereNotNull('notes')->where('notes', '!=', '');
                });
            })->count();
            // Use a UTC range for "today" in Manila timezone to avoid date mismatch
            // (Manila is UTC+8; at 11 PM PH time the UTC date is still the previous day)
            $todayStart = \Carbon\Carbon::today('Asia/Manila');
            $todayEnd   = \Carbon\Carbon::tomorrow('Asia/Manila');
            $todayOrders  = \App\Models\Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();
            $todayRevenue = (float) \App\Models\Order::whereBetween('created_at', [$todayStart, $todayEnd])->sum('total_amount');
            $shippedOrders   = (clone $dateQuery)->where('status', 'shipped')->count();
            $deliveredOrders = (clone $dateQuery)->where('status', 'delivered')->count();
            
            // Payment method breakdown - normalize payment methods
            $rawPaymentMethods = (clone $dateQuery)->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();

            $paymentMethods = $this->normalizePaymentMethods($rawPaymentMethods)
                ->filter(function ($method) {
                    return $method->count > 0;
                });


            // Delivery type breakdown
            $deliveryTypes = (clone $dateQuery)->selectRaw('delivery_type, COUNT(*) as count')
                ->groupBy('delivery_type')
                ->get();

            $recentOrders = (clone $dateQuery)->with('user')
                ->orderByDesc('created_at')
                ->take(10)
                ->get();

            $recentUsers = \App\Models\User::orderByDesc('created_at')->take(10)->get();

            $ordersByStatus = (clone $dateQuery)->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $totalProducts = \App\Models\Product::count();
            
            // Out-of-stock items (check inventory.quantity first, fall back to products.stock)
            $outOfStockItems = \App\Models\Product::leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
                ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 0')
                ->with('category', 'inventory')
                ->select('products.*')
                ->get();
            $outOfStockCount = $outOfStockItems->count();

            // Inventory stats
            $lowStockCount = \App\Models\Inventory::whereRaw('quantity <= min_stock_level AND quantity > 0')->count();
            $totalInventoryValue = (int) (\App\Models\Inventory::selectRaw('SUM(quantity * selling_price) as total')->value('total') ?? 0);
            $stockInToday = 0;
            if (\Schema::hasTable('stock_logs')) {
                $stockInToday = \App\Models\StockLog::where('quantity', '>', 0)
                    ->whereDate('created_at', today())
                    ->sum('quantity');
            }

            // Top products by sold quantity (Best Sellers)
            $topProductsQuery = \App\Models\OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')
                ->orderByDesc('sold')
                ->with('product');
                
            if ($period !== 'all') {
                $topProductsQuery->whereHas('order', function($q) use ($dateQuery) {
                    $q->whereIn('id', (clone $dateQuery)->pluck('id'));
                });
            }
            
            $topProducts = $topProductsQuery->take(10)->get();
            
            // Low sales products (bottom 10)
            $lowSalesProductsQuery = \App\Models\OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')
                ->orderBy('sold', 'asc')
                ->with('product');
                
            if ($period !== 'all') {
                $lowSalesProductsQuery->whereHas('order', function($q) use ($dateQuery) {
                    $q->whereIn('id', (clone $dateQuery)->pluck('id'));
                });
            }
            
            $lowSalesProducts = $lowSalesProductsQuery->take(10)->get();

            // Basic sales data
            $salesDataRange = 30;
            if ($period == 'yearly') {
                $salesDataRange = 365;
            } elseif ($period == 'weekly') {
                $salesDataRange = 7;
            } elseif ($period == 'daily') {
                $salesDataRange = 1;
            }
            
            $allSalesData = \App\Models\Order::where('created_at', '>=', now()->subDays($salesDataRange))
                ->whereIn('status', ['delivered', 'completed'])
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return view('admin.dashboard', compact(
                'totalOrders',
                'pendingOrders',
                'completedOrders',
                'totalUsers',
                'totalRevenue',
                'averageOrderValue',
                'ordersWithNotes',
                'todayOrders',
                'todayRevenue',
                'shippedOrders',
                'deliveredOrders',
                'paymentMethods',
                'deliveryTypes',
                'recentOrders',
                'recentUsers',
                'ordersByStatus',
                'totalProducts',
                'topProducts',
                'lowSalesProducts',
                'outOfStockItems',
                'outOfStockCount',
                'lowStockCount',
                'totalInventoryValue',
                'stockInToday',
                'allSalesData',
                'period',
                'stockFilter'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard index error: ' . $e->getMessage());
            
            // Return simple error message
            return 'Dashboard error: ' . $e->getMessage();
        }
    }

    /**
     * Return metrics JSON for dashboard charts (Axios API).
     */
    public function metrics(\Illuminate\Http\Request $request)
    {
        try {
            $period = (int) ($request->get('period', 30));
            $start = now()->subDays($period);

            $totalOrders = \App\Models\Order::count();
            $ordersByStatus = \App\Models\Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $topProducts = \App\Models\OrderItem::selectRaw('product_id, SUM(quantity) as sold')
                ->groupBy('product_id')
                ->orderByDesc('sold')
                ->with('product:id,name')
                ->take(5)
                ->get()
                ->map(function ($row) {
                    return [
                        'name' => optional($row->product)->name ?? 'Product '.$row->product_id,
                        'sold' => (int) $row->sold,
                    ];
                });
            $totalUsers = \App\Models\User::count();
            $totalRevenue = (float) \App\Models\Order::sum('total_amount');

            $series = \App\Models\Order::where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'totalOrders' => $totalOrders,
                'ordersByStatus' => $ordersByStatus,
                'topProducts' => $topProducts,
                'totalUsers' => $totalUsers,
                'totalRevenue' => $totalRevenue,
                'salesLabels' => $series->pluck('date'),
                'salesValues' => $series->pluck('revenue'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard metrics error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load metrics'], 500);
        }
    }

    /**
     * Analytics & Reports page
     */
    public function analytics()
    {
        try {
            return view('admin.analytics.index', [
                'salesData' => collect([]),
                'userGrowth' => collect([]),
                'productPerformance' => collect([])
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load analytics.');
        }
    }

    /**
     * Sales Report page
     */
    public function salesReport(Request $request)
    {
        try {
            $period = $request->get('period', 30);

            // Daily sales for the chosen period (all orders, not just completed)
            $salesData = Order::where('created_at', '>=', now()->subDays($period))
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $totalRevenue    = (float) Order::sum('total_amount');
            $totalOrders     = Order::count();
            $completedOrders = Order::where('status', 'completed')->count();
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            // Monthly revenue for the past 12 months (all orders)
            $monthlyRevenue = Order::where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Payment method breakdown
            $rawPaymentMethods = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();
            $paymentMethods = $this->normalizePaymentMethods($rawPaymentMethods);

            // Top products by revenue
            $topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')
                ->orderByDesc('revenue')
                ->with('product')
                ->take(10)
                ->get();

            // Order status breakdown
            $statusBreakdown = Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            // Day-of-week sales breakdown (all orders ever)
            $dayOfWeekStats = Order::selectRaw(
                    'DAYOFWEEK(created_at) as dow, DAYNAME(created_at) as day_name, COUNT(*) as orders, SUM(total_amount) as revenue'
                )
                ->groupBy('dow', 'day_name')
                ->orderBy('dow')
                ->get();

            // Peak month (last 12 months)
            $peakMonth = $monthlyRevenue->sortByDesc('revenue')->first();

            return view('admin.analytics.sales', compact(
                'salesData',
                'totalRevenue',
                'totalOrders',
                'completedOrders',
                'averageOrderValue',
                'monthlyRevenue',
                'paymentMethods',
                'topProducts',
                'statusBreakdown',
                'dayOfWeekStats',
                'peakMonth',
                'period'
            ));
        } catch (\Exception $e) {
            \Log::error('Sales report error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load sales report.');
        }
    }

    /**
     * Products Report page
     */
    public function productsReport()
    {
        try {
            $topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')
                ->orderByDesc('sold')
                ->with('product')
                ->take(20)
                ->get();

            $totalProducts = Product::count();
            $outOfStockItems = Product::leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
                ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 0')
                ->with('category', 'inventory')
                ->select('products.*')
                ->get();
            $outOfStockCount = $outOfStockItems->count();

            return view('admin.analytics.products', compact(
                'topProducts',
                'totalProducts',
                'outOfStockCount',
                'outOfStockItems'
            ));
        } catch (\Exception $e) {
            \Log::error('Products report error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load products report.');
        }
    }

    /**
     * Users Report page
     */
    public function usersReport()
    {
        try {
            $totalUsers = User::count();
            $newUsersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();
            $newUsersThisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();
            
            // User growth by month
            $userGrowth = User::where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as users')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Top customers by orders
            $topCustomers = User::withCount('orders')
                ->orderByDesc('orders_count')
                ->take(10)
                ->get();

            $recentUsers = User::orderByDesc('created_at')->take(10)->get();

            return view('admin.analytics.users', compact(
                'totalUsers',
                'newUsersThisMonth',
                'newUsersThisWeek',
                'userGrowth',
                'topCustomers',
                'recentUsers'
            ));
        } catch (\Exception $e) {
            \Log::error('Users report error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load users report.');
        }
    }

    /**
     * Print Report - renders a printable page with selectable report sections
     */
    public function printReport(Request $request)
    {
        try {
            $period = $request->get('period', 'all');
            $sections = $request->get('sections', []);

            // If no sections selected, show all
            if (empty($sections)) {
                $sections = ['revenue', 'product_sales', 'transactions', 'users'];
            }

            // Date filtering
            $dateQuery = Order::query();
            switch ($period) {
                case 'daily':
                    $dateQuery->whereDate('created_at', today());
                    break;
                case 'weekly':
                    $dateQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'yearly':
                    $dateQuery->whereYear('created_at', now()->year);
                    break;
            }

            $data = ['period' => $period, 'sections' => $sections];

            // --- Total Revenue ---
            if (in_array('revenue', $sections)) {
                $completedStatuses = ['completed', 'delivered'];
                $data['totalRevenue'] = (float) (clone $dateQuery)->whereIn('status', $completedStatuses)->sum('total_amount');
                $data['totalOrders'] = (clone $dateQuery)->count();
                $data['completedOrders'] = (clone $dateQuery)->whereIn('status', $completedStatuses)->count();
                $data['pendingOrders'] = (clone $dateQuery)->where('status', 'pending')->count();
                $data['averageOrderValue'] = $data['completedOrders'] > 0 ? $data['totalRevenue'] / $data['completedOrders'] : 0;

                // Monthly revenue for chart (last 12 months)
                $data['monthlyRevenue'] = Order::whereIn('status', $completedStatuses)
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as revenue, COUNT(*) as orders')
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                // Daily revenue for the selected period
                $daysRange = match($period) {
                    'daily' => 1,
                    'weekly' => 7,
                    'yearly' => 365,
                    default => 30,
                };
                $data['dailyRevenue'] = Order::whereIn('status', $completedStatuses)
                    ->where('created_at', '>=', now()->subDays($daysRange))
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                // Payment method breakdown (all orders, not just completed)
                $rawPaymentMethods = (clone $dateQuery)->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                    ->groupBy('payment_method')
                    ->get();
                $data['paymentMethods'] = $this->normalizePaymentMethods($rawPaymentMethods);
            }

            // --- Product Sales Report ---
            if (in_array('product_sales', $sections)) {
                $topProductsQuery = OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                    ->groupBy('product_id')
                    ->orderByDesc('sold')
                    ->with('product');

                if ($period !== 'all') {
                    $topProductsQuery->whereHas('order', function($q) use ($dateQuery) {
                        $q->whereIn('id', (clone $dateQuery)->pluck('id'));
                    });
                }

                $data['productSales'] = $topProductsQuery->get();
                $data['totalProducts'] = Product::count();
                $data['outOfStockCount'] = Product::leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
                    ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 0')
                    ->count('products.id');
            }

            // --- Transaction History ---
            if (in_array('transactions', $sections)) {
                $data['transactions'] = (clone $dateQuery)->with(['user', 'items.product'])
                    ->orderByDesc('created_at')
                    ->get();
            }

            // --- Total Users ---
            if (in_array('users', $sections)) {
                $data['totalUsers'] = User::count();
                $data['adminUsers'] = User::where('role', 'admin')->count();
                $data['customerUsers'] = User::where('role', '!=', 'admin')->count();
                $data['newUsersThisMonth'] = User::where('created_at', '>=', now()->startOfMonth())->count();
                $data['newUsersThisWeek'] = User::where('created_at', '>=', now()->startOfWeek())->count();

                // User growth by month (last 12 months)
                $data['userGrowth'] = User::where('created_at', '>=', now()->subMonths(12))
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as users')
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                // Top customers
                $data['topCustomers'] = User::withCount('orders')
                    ->orderByDesc('orders_count')
                    ->take(10)
                    ->get();
            }

            return view('admin.print-report', $data);
        } catch (\Exception $e) {
            \Log::error('Print report error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to generate print report: ' . $e->getMessage());
        }
    }

    /**
     * Export dashboard report to CSV
     */
    public function exportReport(Request $request, $type = null)
    {
        // Send headers immediately so the browser treats this as a download
        $period = $request->get('period', 'all');

        $periodLabel = 'All Time';
        $dateQuery = \App\Models\Order::query();
        switch ($period) {
            case 'daily':
                $dateQuery->whereDate('created_at', today());
                $periodLabel = 'Daily – ' . today()->format('F d, Y');
                break;
            case 'weekly':
                $dateQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                $periodLabel = 'Weekly – ' . now()->startOfWeek()->format('M d') . ' to ' . now()->endOfWeek()->format('M d, Y');
                break;
            case 'monthly':
                $dateQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                $periodLabel = 'Monthly – ' . now()->format('F Y');
                break;
            case 'yearly':
                $dateQuery->whereYear('created_at', now()->year);
                $periodLabel = 'Yearly – ' . now()->year;
                break;
        }

        // Detect available columns on orders table
        $orderCols = \Schema::getColumnListing('orders');
        $hasShipping = in_array('shipping_fee', $orderCols);
        $hasDeliveryType = in_array('delivery_type', $orderCols);

        // Core metrics — safe, simple counts
        $totalOrders      = (clone $dateQuery)->count();
        $pendingOrders    = (clone $dateQuery)->where('status', 'pending')->count();
        $processingOrders = (clone $dateQuery)->where('status', 'processing')->count();
        $completedOrders  = (clone $dateQuery)->where('status', 'completed')->count();
        $shippedOrders    = (clone $dateQuery)->where('status', 'shipped')->count();
        $deliveredOrders  = (clone $dateQuery)->where('status', 'delivered')->count();
        $cancelledOrders  = (clone $dateQuery)->whereIn('status', ['cancelled', 'refunded'])->count();
        $totalRevenue     = (float)(clone $dateQuery)->whereIn('status', ['completed', 'delivered'])->sum('total_amount');
        $pendingRevenue   = (float)(clone $dateQuery)->whereIn('status', ['pending', 'processing', 'shipped'])->sum('total_amount');
        $avgOrderValue    = ($completedOrders + $deliveredOrders) > 0 ? $totalRevenue / ($completedOrders + $deliveredOrders) : 0;

        // Orders with details
        $orders = (clone $dateQuery)->with(['user', 'items.product'])->orderByDesc('created_at')->get();

        // Top products
        $topProducts = collect();
        try {
            $tpq = \App\Models\OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')->orderByDesc('sold')->with('product');
            if ($period !== 'all') {
                $tpq->whereIn('order_id', (clone $dateQuery)->pluck('id'));
            }
            $topProducts = $tpq->take(15)->get();
        } catch (\Throwable $e) { \Log::warning('CSV export top products: ' . $e->getMessage()); }

        // Payment methods
        $rawPaymentMethods = (clone $dateQuery)->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')->groupBy('payment_method')->get();
        $paymentMethods = $this->normalizePaymentMethods($rawPaymentMethods);

        // Monthly revenue trend
        $monthlyRevenue = collect();
        try {
            $monthlyRevenue = \App\Models\Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as orders, SUM(total_amount) as revenue')
                ->whereIn('status', ['completed', 'delivered'])
                ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at), MONTH(created_at)')
                ->get();
        } catch (\Throwable $e) { \Log::warning('CSV export monthly: ' . $e->getMessage()); }

        // Custom orders summary
        $customOrderStats = collect();
        try {
            $coCols = \Schema::getColumnListing('custom_orders');
            $coRevCol = in_array('total_price', $coCols) ? 'total_price' : (in_array('total_amount', $coCols) ? 'total_amount' : null);
            $coSel = $coRevCol ? "status, payment_status, COUNT(*) as count, SUM({$coRevCol}) as revenue" : 'status, payment_status, COUNT(*) as count';
            $customOrderStats = \App\Models\CustomOrder::selectRaw($coSel)->groupBy('status', 'payment_status')->get();
        } catch (\Throwable $e) { \Log::warning('CSV export custom orders: ' . $e->getMessage()); }

        // Delivery breakdown
        $deliveryBreakdown = collect();
        if ($hasDeliveryType) {
            try {
                $deliveryBreakdown = (clone $dateQuery)->selectRaw('delivery_type, COUNT(*) as count, SUM(total_amount) as total')->groupBy('delivery_type')->get();
            } catch (\Throwable $e) { \Log::warning('CSV export delivery: ' . $e->getMessage()); }
        }

        // Build CSV
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
        $sep = [''];

        fputcsv($output, ['YAKAN E-COMMERCE – DASHBOARD REPORT']);
        fputcsv($output, ['Period:', $periodLabel]);
        fputcsv($output, ['Generated:', now()->format('F d, Y  H:i:s')]);
        fputcsv($output, $sep);

        fputcsv($output, ['=== OVERVIEW METRICS ===']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', $totalOrders]);
        fputcsv($output, ['Pending', $pendingOrders]);
        fputcsv($output, ['Processing', $processingOrders]);
        fputcsv($output, ['Shipped', $shippedOrders]);
        fputcsv($output, ['Delivered', $deliveredOrders]);
        fputcsv($output, ['Completed', $completedOrders]);
        fputcsv($output, ['Cancelled/Refunded', $cancelledOrders]);
        fputcsv($output, ['Confirmed Revenue', 'PHP ' . number_format($totalRevenue, 2)]);
        fputcsv($output, ['Pending Revenue', 'PHP ' . number_format($pendingRevenue, 2)]);
        fputcsv($output, ['Average Order Value', 'PHP ' . number_format($avgOrderValue, 2)]);
        fputcsv($output, $sep);

        fputcsv($output, ['=== PAYMENT METHODS ===']);
        fputcsv($output, ['Payment Method', 'Orders', 'Total (PHP)', '% of Orders']);
        foreach ($paymentMethods as $method) {
            $pct = $totalOrders > 0 ? round(($method->count / $totalOrders) * 100, 1) : 0;
            fputcsv($output, [
                $method->display_name ?? $this->paymentMethodDisplayName($method->payment_method ?? null),
                $method->count,
                number_format($method->total ?? 0, 2),
                $pct . '%',
            ]);
        }
        fputcsv($output, $sep);

        if ($deliveryBreakdown->isNotEmpty()) {
            fputcsv($output, ['=== DELIVERY TYPE BREAKDOWN ===']);
            fputcsv($output, ['Delivery Type', 'Orders', 'Total (PHP)']);
            foreach ($deliveryBreakdown as $row) {
                fputcsv($output, [ucfirst($row->delivery_type ?? 'N/A'), $row->count, number_format($row->total ?? 0, 2)]);
            }
            fputcsv($output, $sep);
        }

        if ($topProducts->isNotEmpty()) {
            fputcsv($output, ['=== TOP SELLING PRODUCTS ===']);
            fputcsv($output, ['Rank', 'Product Name', 'Qty Sold', 'Revenue (PHP)']);
            foreach ($topProducts as $i => $item) {
                fputcsv($output, [$i + 1, $item->product->name ?? 'Unknown', $item->sold, number_format($item->revenue ?? 0, 2)]);
            }
            fputcsv($output, $sep);
        }

        if ($monthlyRevenue->isNotEmpty()) {
            fputcsv($output, ['=== MONTHLY REVENUE TREND (Last 12 Months) ===']);
            fputcsv($output, ['Month', 'Orders', 'Revenue (PHP)']);
            foreach ($monthlyRevenue as $row) {
                fputcsv($output, [date('F Y', mktime(0, 0, 0, $row->month, 1, $row->year)), $row->orders, number_format($row->revenue ?? 0, 2)]);
            }
            fputcsv($output, $sep);
        }

        if ($customOrderStats->isNotEmpty()) {
            fputcsv($output, ['=== CUSTOM ORDERS SUMMARY ===']);
            fputcsv($output, ['Status', 'Payment Status', 'Count', 'Revenue (PHP)']);
            foreach ($customOrderStats as $row) {
                fputcsv($output, [ucfirst($row->status ?? 'unknown'), ucfirst($row->payment_status ?? 'unpaid'), $row->count, number_format($row->revenue ?? 0, 2)]);
            }
            fputcsv($output, $sep);
        }

        fputcsv($output, ['=== ALL ORDERS DETAIL ===']);
        fputcsv($output, array_filter([
            'Order ID', 'Customer Name', 'Email', 'Date', 'Status',
            'Payment Method', $hasDeliveryType ? 'Delivery Type' : null,
            $hasShipping ? 'Shipping (PHP)' : null, 'Total Amount (PHP)', 'Items'
        ]));
        foreach ($orders as $order) {
            $items = $order->items->map(fn($item) =>
                ($item->product->name ?? 'Unknown') . ' x' . $item->quantity . ' @ PHP' . number_format($item->price ?? 0, 2)
            )->join(' | ');
            $row = [
                'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                $order->user->name ?? ($order->customer_name ?? 'Guest'),
                $order->user->email ?? '',
                $order->created_at->format('Y-m-d H:i'),
                ucfirst($order->status ?? 'unknown'),
                $this->paymentMethodDisplayName($order->payment_method ?? null),
            ];
            if ($hasDeliveryType) $row[] = ucfirst($order->delivery_type ?? 'N/A');
            if ($hasShipping)     $row[] = number_format($order->shipping_fee ?? 0, 2);
            $row[] = number_format($order->total_amount ?? 0, 2);
            $row[] = $items ?: 'No items';
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        $filename = 'Yakan_Dashboard_' . ucfirst($period) . '_' . date('Y-m-d') . '.csv';

        return response($csvContent, 200, [
            'Content-Type'              => 'application/octet-stream',
            'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
            'Content-Length'            => strlen($csvContent),
            'Cache-Control'             => 'no-store, no-cache, must-revalidate',
            'Pragma'                    => 'no-cache',
            'X-Accel-Buffering'         => 'no',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
    }

    private function canonicalPaymentMethod(?string $method): string
    {
        $normalized = strtolower(trim((string) $method));

        return match ($normalized) {
            'online', 'online_banking', 'gcash' => 'gcash',
            'maya' => 'maya',
            'bank_transfer' => 'bank_transfer',
            'cash' => 'cash',
            '', null => 'unknown',
            default => $normalized,
        };
    }

    private function paymentMethodDisplayName(?string $method): string
    {
        $canonical = $this->canonicalPaymentMethod($method);

        return match ($canonical) {
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash on Delivery',
            'unknown' => 'Unknown',
            default => ucwords(str_replace('_', ' ', $canonical)),
        };
    }

    private function normalizePaymentMethods($paymentMethods)
    {
        return collect($paymentMethods)
            ->groupBy(function ($method) {
                return $this->canonicalPaymentMethod($method->payment_method ?? null);
            })
            ->map(function ($methods, $paymentMethod) {
                return (object) [
                    'payment_method' => $paymentMethod,
                    'display_name' => $this->paymentMethodDisplayName($paymentMethod),
                    'count' => (int) $methods->sum('count'),
                    'total' => (float) $methods->sum('total'),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }
}
