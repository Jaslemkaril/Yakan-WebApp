<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsDashboardController extends Controller
{
    /**
     * Main analytics dashboard view.
     */
    public function index(Request $request)
    {
        try {
            $period = $request->get('period', 'monthly');
            $dateRange = $this->getDateRange($period, $request);

            // ── KPI Card Data ──────────────────────────────────────────
            $totalRevenue      = $this->getTotalRevenue($dateRange);
            $totalSales        = $this->getTotalSales($dateRange);
            $bestSellingProduct = $this->getBestSellingProduct($dateRange);
            $totalUsers        = User::count();
            $newUsersInPeriod  = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();

            // Previous period for comparison
            $prevRange          = $this->getPreviousPeriodRange($period, $dateRange);
            $prevRevenue        = $this->getTotalRevenue($prevRange);
            $prevSales          = $this->getTotalSales($prevRange);
            $revenueChange      = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : ($totalRevenue > 0 ? 100 : 0);
            $salesChange        = $prevSales > 0 ? round((($totalSales - $prevSales) / $prevSales) * 100, 1) : ($totalSales > 0 ? 100 : 0);

            // Average order value
            $averageOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

            // ── Sales Performance Graph Data ───────────────────────────
            $salesGraphData = $this->getSalesGraphData($period, $dateRange);

            // ── Best & Low Selling Products ────────────────────────────
            $topProducts = $this->getTopProducts($dateRange, 10);
            $lowProducts = $this->getLowProducts($dateRange, 10);

            // ── Product Sales Report ───────────────────────────────────
            $productSalesReport = $this->getProductSalesReport($dateRange);

            // ── Transaction History ────────────────────────────────────
            $transactions = $this->getTransactionHistory($dateRange);

            // ── Revenue Graph (daily breakdown) ────────────────────────
            $revenueGraphData = $this->getRevenueGraphData($period, $dateRange);

            // ── User Growth Data ───────────────────────────────────────
            $userGrowthData = $this->getUserGrowthData($period, $dateRange);

            // ── Orders by Status ───────────────────────────────────────
            $ordersByStatus = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            // ── Payment Method Breakdown ───────────────────────────────
            $paymentBreakdown = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('COALESCE(payment_method, "unknown") as method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('method')
                ->get();

            return view('admin.analytics-dashboard.index', compact(
                'period',
                'dateRange',
                'totalRevenue',
                'totalSales',
                'bestSellingProduct',
                'totalUsers',
                'newUsersInPeriod',
                'revenueChange',
                'salesChange',
                'averageOrderValue',
                'salesGraphData',
                'topProducts',
                'lowProducts',
                'productSalesReport',
                'transactions',
                'revenueGraphData',
                'userGrowthData',
                'ordersByStatus',
                'paymentBreakdown'
            ));
        } catch (\Exception $e) {
            \Log::error('Analytics Dashboard error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load analytics dashboard: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for chart data (AJAX refresh).
     */
    public function chartData(Request $request)
    {
        try {
            $period = $request->get('period', 'monthly');
            $dateRange = $this->getDateRange($period, $request);

            $salesGraphData   = $this->getSalesGraphData($period, $dateRange);
            $revenueGraphData = $this->getRevenueGraphData($period, $dateRange);
            $topProducts      = $this->getTopProducts($dateRange, 10);
            $lowProducts      = $this->getLowProducts($dateRange, 10);
            $userGrowthData   = $this->getUserGrowthData($period, $dateRange);

            $ordersByStatus = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $totalRevenue = $this->getTotalRevenue($dateRange);
            $totalSales   = $this->getTotalSales($dateRange);
            $bestSelling  = $this->getBestSellingProduct($dateRange);
            $totalUsers   = User::count();

            return response()->json([
                'success'          => true,
                'salesGraph'       => $salesGraphData,
                'revenueGraph'     => $revenueGraphData,
                'topProducts'      => $topProducts,
                'lowProducts'      => $lowProducts,
                'userGrowth'       => $userGrowthData,
                'ordersByStatus'   => $ordersByStatus,
                'kpi' => [
                    'totalRevenue'      => $totalRevenue,
                    'totalSales'        => $totalSales,
                    'bestSellingProduct' => $bestSelling,
                    'totalUsers'        => $totalUsers,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export reports as CSV.
     */
    public function exportReport(Request $request, $type = 'all')
    {
        try {
            $period    = $request->get('period', 'monthly');
            $dateRange = $this->getDateRange($period, $request);
            $filename  = "yakan_{$type}_report_" . now()->format('Y-m-d_His') . '.csv';

            return response()->streamDownload(function () use ($type, $dateRange, $period) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                if ($type === 'all' || $type === 'revenue') {
                    $this->writeRevenueCsv($file, $dateRange, $period);
                }
                if ($type === 'all' || $type === 'products') {
                    $this->writeProductsCsv($file, $dateRange);
                }
                if ($type === 'all' || $type === 'transactions') {
                    $this->writeTransactionsCsv($file, $dateRange);
                }
                if ($type === 'all' || $type === 'users') {
                    $this->writeUsersCsv($file, $dateRange);
                }

                fclose($file);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function getDateRange(string $period, Request $request): array
    {
        $customStart = $request->get('start_date');
        $customEnd   = $request->get('end_date');

        if ($customStart && $customEnd) {
            return [
                'start' => Carbon::parse($customStart)->startOfDay(),
                'end'   => Carbon::parse($customEnd)->endOfDay(),
            ];
        }

        switch ($period) {
            case 'daily':
                return ['start' => now()->startOfDay(), 'end' => now()->endOfDay()];
            case 'weekly':
                return ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()];
            case 'monthly':
                return ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()];
            case 'yearly':
                return ['start' => now()->startOfYear(), 'end' => now()->endOfYear()];
            case 'all':
                return ['start' => Carbon::create(2020, 1, 1), 'end' => now()->endOfDay()];
            default:
                return ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()];
        }
    }

    private function getPreviousPeriodRange(string $period, array $current): array
    {
        $diff = $current['start']->diffInDays($current['end']);
        return [
            'start' => (clone $current['start'])->subDays($diff + 1),
            'end'   => (clone $current['start'])->subDay(),
        ];
    }

    private function getTotalRevenue(array $range): float
    {
        return (float) Order::whereBetween('created_at', [$range['start'], $range['end']])
            ->whereIn('status', ['completed', 'delivered', 'shipped', 'confirmed', 'processing'])
            ->sum('total_amount');
    }

    private function getTotalSales(array $range): int
    {
        return Order::whereBetween('created_at', [$range['start'], $range['end']])->count();
    }

    private function getBestSellingProduct(array $range): ?array
    {
        $top = OrderItem::whereHas('order', function ($q) use ($range) {
                $q->whereBetween('created_at', [$range['start'], $range['end']]);
            })
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(price * quantity) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product:id,name,image,price')
            ->first();

        if (!$top || !$top->product) {
            return null;
        }

        return [
            'name'      => $top->product->name,
            'image'     => $top->product->image,
            'sold'      => (int) $top->total_sold,
            'revenue'   => (float) $top->total_revenue,
        ];
    }

    private function getTopProducts(array $range, int $limit = 10)
    {
        return OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$range['start'], $range['end']]))
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(price * quantity) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product:id,name,image,price')
            ->take($limit)
            ->get()
            ->map(fn($item) => [
                'name'    => $item->product->name ?? 'Unknown Product',
                'image'   => $item->product->image ?? null,
                'sold'    => (int) $item->total_sold,
                'revenue' => (float) $item->total_revenue,
                'price'   => $item->product->price ?? 0,
            ]);
    }

    private function getLowProducts(array $range, int $limit = 10)
    {
        return OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$range['start'], $range['end']]))
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(price * quantity) as total_revenue')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'asc')
            ->with('product:id,name,image,price')
            ->take($limit)
            ->get()
            ->map(fn($item) => [
                'name'    => $item->product->name ?? 'Unknown Product',
                'image'   => $item->product->image ?? null,
                'sold'    => (int) $item->total_sold,
                'revenue' => (float) $item->total_revenue,
                'price'   => $item->product->price ?? 0,
            ]);
    }

    private function getSalesGraphData(string $period, array $range): array
    {
        $groupFormat = $this->getGroupFormat($period);

        $data = Order::whereBetween('created_at', [$range['start'], $range['end']])
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as label, COUNT(*) as total_orders, SUM(total_amount) as total_revenue")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels'  => $data->pluck('label')->toArray(),
            'orders'  => $data->pluck('total_orders')->toArray(),
            'revenue' => $data->pluck('total_revenue')->map(fn($v) => round((float)$v, 2))->toArray(),
        ];
    }

    private function getRevenueGraphData(string $period, array $range): array
    {
        $groupFormat = $this->getGroupFormat($period);

        $data = Order::whereBetween('created_at', [$range['start'], $range['end']])
            ->whereIn('status', ['completed', 'delivered', 'shipped', 'confirmed', 'processing'])
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as label, SUM(total_amount) as revenue")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels'  => $data->pluck('label')->toArray(),
            'revenue' => $data->pluck('revenue')->map(fn($v) => round((float)$v, 2))->toArray(),
        ];
    }

    private function getUserGrowthData(string $period, array $range): array
    {
        $groupFormat = $this->getGroupFormat($period);

        $data = User::whereBetween('created_at', [$range['start'], $range['end']])
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as label, COUNT(*) as users")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels' => $data->pluck('label')->toArray(),
            'users'  => $data->pluck('users')->toArray(),
        ];
    }

    private function getGroupFormat(string $period): string
    {
        return match ($period) {
            'daily'   => '%Y-%m-%d %H:00',
            'weekly'  => '%Y-%m-%d',
            'monthly' => '%Y-%m-%d',
            'yearly'  => '%Y-%m',
            'all'     => '%Y-%m',
            default   => '%Y-%m-%d',
        };
    }

    private function getProductSalesReport(array $range)
    {
        return OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$range['start'], $range['end']]))
            ->selectRaw('product_id, SUM(quantity) as qty_sold, SUM(price * quantity) as revenue, AVG(price) as avg_price')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->with('product:id,name,price,stock,image')
            ->get();
    }

    private function getTransactionHistory(array $range)
    {
        return Order::with(['items.product:id,name', 'user:id,name,email'])
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->orderByDesc('created_at')
            ->get();
    }

    // ── CSV Writers ────────────────────────────────────────────────

    private function writeRevenueCsv($file, array $range, string $period): void
    {
        fputcsv($file, ['=== TOTAL REVENUE REPORT ===']);
        fputcsv($file, ['Period', ucfirst($period)]);
        fputcsv($file, ['From', $range['start']->format('Y-m-d')]);
        fputcsv($file, ['To', $range['end']->format('Y-m-d')]);
        fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, []);
        fputcsv($file, ['Total Revenue', 'P' . number_format($this->getTotalRevenue($range), 2)]);
        fputcsv($file, ['Total Orders', $this->getTotalSales($range)]);
        fputcsv($file, []);

        $graphData = $this->getSalesGraphData($period, $range);
        fputcsv($file, ['Date', 'Orders', 'Revenue']);
        foreach ($graphData['labels'] as $i => $label) {
            fputcsv($file, [$label, $graphData['orders'][$i], 'P' . number_format($graphData['revenue'][$i], 2)]);
        }
        fputcsv($file, []);
    }

    private function writeProductsCsv($file, array $range): void
    {
        fputcsv($file, ['=== PRODUCT SALES REPORT ===']);
        fputcsv($file, ['Product Name', 'Quantity Sold', 'Revenue', 'Avg Price', 'Current Stock']);
        $products = $this->getProductSalesReport($range);
        foreach ($products as $item) {
            fputcsv($file, [
                $item->product->name ?? 'Unknown',
                $item->qty_sold,
                'P' . number_format($item->revenue, 2),
                'P' . number_format($item->avg_price, 2),
                $item->product->stock ?? 'N/A',
            ]);
        }
        fputcsv($file, []);
    }

    private function writeTransactionsCsv($file, array $range): void
    {
        fputcsv($file, ['=== TRANSACTION HISTORY ===']);
        fputcsv($file, ['Date', 'Order ID', 'Reference', 'Customer', 'Products', 'Payment Method', 'Status', 'Total Amount']);
        $transactions = $this->getTransactionHistory($range);
        foreach ($transactions as $order) {
            $products = $order->items->map(fn($i) => ($i->product->name ?? 'Unknown') . ' x' . $i->quantity)->join('; ');
            fputcsv($file, [
                $order->created_at->format('Y-m-d H:i'),
                $order->id,
                $order->order_ref ?? 'N/A',
                $order->user->name ?? ($order->customer_name ?? 'Guest'),
                $products ?: 'No items',
                ucfirst($order->payment_method ?? 'N/A'),
                ucfirst(str_replace('_', ' ', $order->status ?? 'N/A')),
                'P' . number_format($order->total_amount ?? 0, 2),
            ]);
        }
        fputcsv($file, []);
    }

    private function writeUsersCsv($file, array $range): void
    {
        fputcsv($file, ['=== TOTAL USERS REPORT ===']);
        fputcsv($file, ['Total Users (All-Time)', User::count()]);
        fputcsv($file, ['New Users in Period', User::whereBetween('created_at', [$range['start'], $range['end']])->count()]);
        fputcsv($file, ['Admin Users', User::where('role', 'admin')->count()]);
        fputcsv($file, ['Regular Users', User::where('role', '!=', 'admin')->orWhereNull('role')->count()]);
        fputcsv($file, ['Verified Users', User::whereNotNull('email_verified_at')->count()]);
        fputcsv($file, []);

        fputcsv($file, ['Name', 'Email', 'Role', 'Registered On', 'Verified', 'Total Orders']);
        $users = User::withCount('orders')->orderByDesc('created_at')->get();
        foreach ($users as $user) {
            fputcsv($file, [
                $user->name,
                $user->email,
                $user->role ?? 'user',
                $user->created_at->format('Y-m-d'),
                $user->email_verified_at ? 'Yes' : 'No',
                $user->orders_count,
            ]);
        }
        fputcsv($file, []);
    }
}
