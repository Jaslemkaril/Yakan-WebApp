<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Review;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        // Auth handled by route-level AdminCheck middleware
    }

    /**
     * Display the main reports dashboard.
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', '30'); // Default to 30 days
        $startDate = now()->subDays($period);
        
        // Sales Analytics
        $salesData = $this->getSalesAnalytics($startDate);
        
        // Product Performance
        $productData = $this->getProductAnalytics($startDate);
        
        // Customer Analytics
        $customerData = $this->getCustomerAnalytics($startDate);
        
        // Inventory Analytics
        $inventoryData = $this->getInventoryAnalytics();
        
        // Review Analytics
        $reviewData = $this->getReviewAnalytics($startDate);
        
        // Custom Order Analytics
        $customOrderData = $this->getCustomOrderAnalytics($startDate);

        return view('admin.reports.index', compact(
            'salesData',
            'productData', 
            'customerData',
            'inventoryData',
            'reviewData',
            'customOrderData',
            'period'
        ));
    }

    /**
     * Get sales analytics data.
     */
    private function getSalesAnalytics($startDate): array
    {
        $orders = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->get();

        $dailySales = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->orderBy('date')
            ->get();

        $monthlyRevenue = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('month')
            ->selectRaw('MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as orders')
            ->orderBy('month')
            ->get();

        return [
            'totalRevenue' => $orders->sum('total'),
            'totalOrders' => $orders->count(),
            'averageOrderValue' => $orders->avg('total'),
            'dailySales' => $dailySales,
            'monthlyRevenue' => $monthlyRevenue,
            'topSellingProducts' => $this->getTopSellingProducts($startDate),
        ];
    }

    /**
     * Get product analytics data.
     */
    private function getProductAnalytics($startDate): array
    {
        $products = Product::withCount(['orderItems' => function ($query) use ($startDate) {
            $query->whereHas('order', function ($subQuery) use ($startDate) {
                $subQuery->where('created_at', '>=', $startDate)
                        ->where('status', 'completed');
            });
        }])->get();

        return [
            'totalProducts' => $products->count(),
            'activeProducts' => $products->where('is_active', true)->count(),
            'topProducts' => $products->sortByDesc('order_items_count')->take(10),
            'lowStockProducts' => Inventory::whereRaw('quantity <= min_stock_level')->count(),
            'outOfStockProducts' => Inventory::where('quantity', 0)->count(),
        ];
    }

    /**
     * Get customer analytics data.
     */
    private function getCustomerAnalytics($startDate): array
    {
        $newCustomers = User::where('created_at', '>=', $startDate)->count();
        $totalCustomers = User::count();
        $activeCustomers = User::whereHas('orders', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        })->count();

        return [
            'totalCustomers' => $totalCustomers,
            'newCustomers' => $newCustomers,
            'activeCustomers' => $activeCustomers,
            'customerGrowthRate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    /**
     * Get inventory analytics data.
     */
    private function getInventoryAnalytics(): array
    {
        $inventories = Inventory::all();
        $totalValue = $inventories->sum(function ($inv) {
            return $inv->quantity * ($inv->selling_price ?? $inv->product->price);
        });

        return [
            'totalItems' => $inventories->sum('quantity'),
            'totalValue' => $totalValue,
            'lowStockItems' => $inventories->where('low_stock_alert', true)->count(),
            'outOfStockItems' => $inventories->where('quantity', 0)->count(),
            'categories' => $inventories->groupBy(function ($inv) {
                return $inv->product->category->name ?? 'Uncategorized';
            }),
        ];
    }

    /**
     * Get review analytics data.
     */
    private function getReviewAnalytics($startDate): array
    {
        $reviews = Review::where('created_at', '>=', $startDate)->get();
        
        return [
            'totalReviews' => $reviews->count(),
            'averageRating' => $reviews->avg('rating'),
            'ratingDistribution' => $reviews->groupBy('rating')->map->count(),
            'verifiedReviews' => $reviews->where('is_verified', true)->count(),
        ];
    }

    /**
     * Get custom order analytics data.
     */
    private function getCustomOrderAnalytics($startDate): array
    {
        $orders = CustomOrder::where('created_at', '>=', $startDate)->get();
        
        return [
            'totalOrders' => $orders->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'approvedOrders' => $orders->where('status', 'approved')->count(),
            'completedOrders' => $orders->where('status', 'completed')->count(),
            'totalRevenue' => $orders->whereNotNull('final_price')->sum('final_price'),
            'averageOrderValue' => $orders->whereNotNull('final_price')->avg('final_price'),
        ];
    }

    /**
     * Get top selling products.
     */
    private function getTopSellingProducts($startDate)
    {
        return Product::withCount(['orderItems as quantity_sold' => function ($query) use ($startDate) {
                $query->whereHas('order', function ($subQuery) use ($startDate) {
                    $subQuery->where('created_at', '>=', $startDate)
                            ->where('status', 'completed');
                });
            }])
            ->orderBy('quantity_sold', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Export sales report.
     */
    public function exportSales(Request $request): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        $orders = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->with(['user', 'orderItems.product'])
            ->get();

        $csvData = [];
        $csvData[] = ['Order ID', 'Customer', 'Date', 'Total', 'Items', 'Status'];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->id,
                $order->user->name,
                $order->created_at->format('Y-m-d'),
                $order->total,
                $order->orderItems->count(),
                $order->status,
            ];
        }

        $filename = "sales_report_{$period}_days.csv";
        
        return response()->stream(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Export inventory report.
     */
    public function exportInventory(): JsonResponse
    {
        $inventories = Inventory::with('product.category')->get();
        
        $csvData = [];
        $csvData[] = ['Product', 'Category', 'Quantity', 'Min Level', 'Max Level', 'Status', 'Value'];

        foreach ($inventories as $inventory) {
            $csvData[] = [
                $inventory->product->name,
                $inventory->product->category->name ?? 'Uncategorized',
                $inventory->quantity,
                $inventory->min_stock_level,
                $inventory->max_stock_level,
                $inventory->stock_status,
                $inventory->quantity * ($inventory->selling_price ?? $inventory->product->price),
            ];
        }

        $filename = "inventory_report_" . now()->format('Y-m-d') . ".csv";
        
        return response()->stream(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Get real-time dashboard metrics.
     */
    public function realTimeMetrics(): JsonResponse
    {
        return response()->json([
            'todayRevenue' => Order::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total'),
            'todayOrders' => Order::whereDate('created_at', today())->count(),
            'activeUsers' => User::whereDate('last_login_at', today())->count(),
            'lowStockAlerts' => Inventory::whereRaw('quantity <= min_stock_level')->count(),
            'pendingReviews' => Review::where('is_approved', false)->count(),
            'pendingCustomOrders' => CustomOrder::where('status', 'pending')->count(),
        ]);
    }
}
