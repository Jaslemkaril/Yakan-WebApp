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
}
