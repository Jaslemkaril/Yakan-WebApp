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
            $pendingOrders = (clone $dateQuery)->where('status', 'pending')->count();
            $completedOrders = (clone $dateQuery)->where('status', 'completed')->count();
            $totalUsers = \App\Models\User::count();
            $totalRevenue = (float) (clone $dateQuery)->where('status', 'completed')->sum('total_amount');

            // New analytics
            $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;
            $ordersWithNotes = (clone $dateQuery)->whereNotNull('customer_notes')->where('customer_notes', '!=', '')->count();
            $todayOrders = \App\Models\Order::whereDate('created_at', today())->count();
            $todayRevenue = (float) \App\Models\Order::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount');
            $shippedOrders = (clone $dateQuery)->where('status', 'shipped')->count();
            $deliveredOrders = (clone $dateQuery)->where('status', 'delivered')->count();
            
            // Payment method breakdown
            $paymentMethods = (clone $dateQuery)->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();

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
            
            // Out-of-stock items
            $outOfStockItems = \App\Models\Product::where('stock', '<=', 0)->get();
            $outOfStockCount = $outOfStockItems->count();

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
                ->where('status', 'completed')
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
            $totalRevenue = (float) \App\Models\Order::where('status', 'completed')->sum('total_amount');

            $series = \App\Models\Order::where('created_at', '>=', $start)
                ->where('status', 'completed')
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
            
            $salesData = Order::where('status', 'completed')
                ->where('created_at', '>=', now()->subDays($period))
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $totalRevenue = (float) Order::where('status', 'completed')->sum('total_amount');
            $totalOrders = Order::count();
            $completedOrders = Order::where('status', 'completed')->count();
            $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

            // Monthly revenue for the past 12 months
            $monthlyRevenue = Order::where('status', 'completed')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Payment method breakdown
            $paymentMethods = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();

            // Top products by revenue
            $topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as sold, SUM(price * quantity) as revenue')
                ->groupBy('product_id')
                ->orderByDesc('revenue')
                ->with('product')
                ->take(10)
                ->get();

            return view('admin.analytics.sales', compact(
                'salesData',
                'totalRevenue',
                'totalOrders',
                'completedOrders',
                'averageOrderValue',
                'monthlyRevenue',
                'paymentMethods',
                'topProducts',
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
            $outOfStockCount = Product::where('stock', '<=', 0)->count();
            $outOfStockItems = Product::where('stock', '<=', 0)->get();

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
     * Export dashboard report to CSV
     */
    public function exportReport(Request $request, $type = null)
    {
        try {
            $period = $request->get('period', 'all');
            
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

            // Get all dashboard data
            $totalOrders = (clone $dateQuery)->count();
            $pendingOrders = (clone $dateQuery)->where('status', 'pending')->count();
            $completedOrders = (clone $dateQuery)->where('status', 'completed')->count();
            $shippedOrders = (clone $dateQuery)->where('status', 'shipped')->count();
            $deliveredOrders = (clone $dateQuery)->where('status', 'delivered')->count();
            $totalRevenue = (float) (clone $dateQuery)->where('status', 'completed')->sum('total_amount');
            $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

            // Get orders with details
            $orders = (clone $dateQuery)->with(['user', 'items.product'])
                ->orderByDesc('created_at')
                ->get();

            // Get top products
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

            // Payment method breakdown
            $paymentMethods = (clone $dateQuery)->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();

            // Prepare CSV output
            $filename = 'dashboard_report_' . $period . '_' . date('Y-m-d_His') . '.csv';

            return response()->streamDownload(function() use ($totalOrders, $pendingOrders, $completedOrders, $shippedOrders, $deliveredOrders, $totalRevenue, $averageOrderValue, $orders, $topProducts, $paymentMethods, $period) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Dashboard Summary Section
                fputcsv($file, ['DASHBOARD SUMMARY']);
                fputcsv($file, ['Period', ucfirst($period)]);
                fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
                fputcsv($file, []);
                
                fputcsv($file, ['OVERVIEW METRICS']);
                fputcsv($file, ['Metric', 'Value']);
                fputcsv($file, ['Total Orders', $totalOrders]);
                fputcsv($file, ['Pending Orders', $pendingOrders]);
                fputcsv($file, ['Completed Orders', $completedOrders]);
                fputcsv($file, ['Shipped Orders', $shippedOrders]);
                fputcsv($file, ['Delivered Orders', $deliveredOrders]);
                fputcsv($file, ['Total Revenue', 'P' . number_format($totalRevenue, 2)]);
                fputcsv($file, ['Average Order Value', 'P' . number_format($averageOrderValue, 2)]);
                fputcsv($file, []);

                // Payment Methods Section
                fputcsv($file, ['PAYMENT METHODS']);
                fputcsv($file, ['Payment Method', 'Orders', 'Total Amount']);
                foreach ($paymentMethods as $method) {
                    fputcsv($file, [
                        ucfirst($method->payment_method ?? 'Unknown'),
                        $method->count,
                        'P' . number_format($method->total ?? 0, 2)
                    ]);
                }
                fputcsv($file, []);

                // Top Products Section
                fputcsv($file, ['TOP SELLING PRODUCTS']);
                fputcsv($file, ['Product Name', 'Quantity Sold', 'Revenue']);
                foreach ($topProducts as $item) {
                    fputcsv($file, [
                        $item->product->name ?? 'Unknown Product',
                        $item->sold,
                        'P' . number_format($item->revenue ?? 0, 2)
                    ]);
                }
                fputcsv($file, []);

                // Orders Detail Section
                fputcsv($file, ['ALL ORDERS']);
                fputcsv($file, ['Order ID', 'Customer', 'Date', 'Status', 'Payment Method', 'Delivery Type', 'Total Amount', 'Items']);
                foreach ($orders as $order) {
                    $items = $order->items->map(function($item) {
                        return ($item->product->name ?? 'Unknown') . ' (x' . $item->quantity . ')';
                    })->join('; ');
                    
                    fputcsv($file, [
                        $order->id,
                        $order->user->name ?? ($order->customer_name ?? 'Guest'),
                        $order->created_at->format('Y-m-d H:i:s'),
                        ucfirst($order->status ?? 'Unknown'),
                        ucfirst($order->payment_method ?? 'N/A'),
                        ucfirst($order->delivery_type ?? 'N/A'),
                        'P' . number_format($order->total_amount ?? 0, 2),
                        $items ?: 'No items'
                    ]);
                }

                fclose($file);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);

        } catch (\Exception $e) {
            \Log::error('Export report error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to export report: ' . $e->getMessage());
        }
    }
}
