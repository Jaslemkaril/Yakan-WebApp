<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomOrderController extends Controller
{
    public function __construct()
    {
        // Auth handled by route-level AdminCheck middleware
    }

    /**
     * Get all custom orders for admin review (enhanced with statistics)
     */
    public function index(Request $request)
    {
        $query = CustomOrder::with(['user:id,name,email', 'product:id,name,price,image'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by user name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate($request->get('per_page', 20));

        // Calculate enhanced statistics efficiently
        $statistics = CustomOrder::selectRaw('
            COUNT(*) as total_orders,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_orders,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = "processing" THEN 1 END) as processing_count,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count,
            COALESCE(SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END), 0) as total_revenue,
            COUNT(CASE WHEN status = "pending" AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 1 END) as urgent_orders
        ')->first();

        $avgProcessingTime = CustomOrder::whereNotNull('approved_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'statistics' => [
                'totalOrders' => $statistics->total_orders,
                'todayOrders' => $statistics->today_orders,
                'pendingCount' => $statistics->pending_count,
                'processingCount' => $statistics->processing_count,
                'completedCount' => $statistics->completed_count,
                'totalRevenue' => $statistics->total_revenue,
                'avgProcessingTime' => round($avgProcessingTime->avg_hours ?? 0, 1),
                'urgentOrders' => $statistics->urgent_orders,
            ],
            'message' => 'Custom orders retrieved successfully'
        ]);
    }

    /**
     * Display the admin custom orders index page with enhanced statistics
     */
    public function showIndex(Request $request)
    {
        $query = CustomOrder::with(['user:id,name,email', 'product:id,name,price,image'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('specifications', 'like', "%{$search}%");
        }

        $orders = $query->paginate($request->get('per_page', 20));

        // Calculate statistics efficiently using a single query
        $statistics = CustomOrder::selectRaw('
            COUNT(*) as total_orders,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_orders,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = "processing" THEN 1 END) as processing_count,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count,
            COALESCE(SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END), 0) as total_revenue
        ')->first();

        // Extract statistics for the view
        $totalOrders = $statistics->total_orders;
        $todayOrders = $statistics->today_orders;
        $pendingCount = $statistics->pending_count;
        $processingCount = $statistics->processing_count;
        $completedCount = $statistics->completed_count;
        $totalRevenue = $statistics->total_revenue;

        return view('admin.custom_orders.index_enhanced', compact(
            'orders',
            'totalOrders',
            'todayOrders', 
            'pendingCount',
            'processingCount',
            'completedCount',
            'totalRevenue'
        ));
    }

    /**
     * Get pending orders that need admin review
     */
    public function getPendingOrders()
    {
        $orders = CustomOrder::with(['user', 'product'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Pending orders retrieved successfully'
        ]);
    }

    /**
     * Get specific custom order details
     */
    public function show($id)
    {
        $order = CustomOrder::with(['user', 'product'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Custom order retrieved successfully'
        ]);
    }

    /**
     * Admin quotes price for custom order
     */
    public function quotePrice(Request $request, $id)
    {
        $request->validate([
            'final_price' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $order = CustomOrder::findOrFail($id);

        if (!$order->isPendingReview()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be priced at this stage'
            ], 400);
        }

        if ($order->quotePrice($request->final_price, $request->admin_notes)) {
            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Price quoted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to quote price'
        ], 500);
    }

    /**
     * Admin rejects custom order
     */
    public function rejectOrder(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $order = CustomOrder::findOrFail($id);

        if (!$order->isPendingReview()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be rejected at this stage'
            ], 400);
        }

        if ($order->reject($request->rejection_reason)) {
            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order rejected successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reject order'
        ], 500);
    }

    /**
     * Admin notifies user about quoted price
     */
    public function notifyUser($id)
    {
        $order = CustomOrder::findOrFail($id);

        if (!$order->isPriceQuoted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot notify user - price not yet quoted'
            ], 400);
        }

        if ($order->notifyUser()) {
            // Here you would typically send a notification (email, push, etc.)
            // For now, we'll just mark it as notified
            
            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'User notified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to notify user'
        ], 500);
    }

    /**
     * Get order statistics for admin dashboard
     */
    public function getStatistics()
    {
        $stats = [
            'total_orders' => CustomOrder::count(),
            'pending_review' => CustomOrder::where('status', 'pending')->count(),
            'price_quoted' => CustomOrder::where('status', 'price_quoted')->count(),
            'awaiting_decision' => CustomOrder::where('status', 'price_quoted')
                                        ->whereNotNull('user_notified_at')
                                        ->count(),
            'approved' => CustomOrder::where('status', 'approved')->count(),
            'processing' => CustomOrder::where('status', 'processing')->count(),
            'completed' => CustomOrder::where('status', 'completed')->count(),
            'rejected' => CustomOrder::where('status', 'rejected')->count(),
            'cancelled' => CustomOrder::where('status', 'cancelled')->count(),
        ];

        // Calculate total value of approved orders
        $stats['total_value'] = CustomOrder::where('status', 'approved')
                                        ->sum('final_price');

        // Average processing time (from pending to approved)
        $avgProcessingTime = CustomOrder::whereNotNull('approved_at')
                                        ->whereNotNull('price_quoted_at')
                                        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
                                        ->first();
        
        $stats['avg_processing_hours'] = $avgProcessingTime->avg_hours ?? 0;

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }

    /**
     * Mark order as processing (after payment received)
     */
    public function markAsProcessing($id)
    {
        $order = CustomOrder::findOrFail($id);

        if ($order->status !== 'approved' || $order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order must be approved and paid before processing'
            ], 400);
        }

        $order->status = 'processing';
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order marked as processing'
        ]);
    }

    /**
     * Mark order as completed
     */
    public function markAsCompleted($id)
    {
        $order = CustomOrder::findOrFail($id);

        if (!$order->markAsCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete order at this stage'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order completed successfully'
        ]);
    }

    /**
     * Bulk quote price for multiple orders
     */
    public function bulkQuotePrice(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:custom_orders,id',
            'final_price' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $updatedOrders = [];
        $failedOrders = [];

        foreach ($request->order_ids as $orderId) {
            $order = CustomOrder::find($orderId);
            if ($order && $order->isPendingReview()) {
                if ($order->quotePrice($request->final_price, $request->admin_notes)) {
                    $updatedOrders[] = $order->id;
                } else {
                    $failedOrders[] = $order->id;
                }
            } else {
                $failedOrders[] = $orderId;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'updated_count' => count($updatedOrders),
                'failed_count' => count($failedOrders),
                'updated_orders' => $updatedOrders,
                'failed_orders' => $failedOrders
            ],
            'message' => "Bulk pricing completed: " . count($updatedOrders) . " updated, " . count($failedOrders) . " failed"
        ]);
    }

    /**
     * Bulk reject orders
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:custom_orders,id',
            'rejection_reason' => 'required|string|max:500'
        ]);

        $updatedOrders = [];
        $failedOrders = [];

        foreach ($request->order_ids as $orderId) {
            $order = CustomOrder::find($orderId);
            if ($order && $order->isPendingReview()) {
                if ($order->reject($request->rejection_reason)) {
                    $updatedOrders[] = $order->id;
                } else {
                    $failedOrders[] = $order->id;
                }
            } else {
                $failedOrders[] = $orderId;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'updated_count' => count($updatedOrders),
                'failed_count' => count($failedOrders),
                'updated_orders' => $updatedOrders,
                'failed_orders' => $failedOrders
            ],
            'message' => "Bulk rejection completed: " . count($updatedOrders) . " updated, " . count($failedOrders) . " failed"
        ]);
    }

    /**
     * Bulk notify users
     */
    public function bulkNotify(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:custom_orders,id'
        ]);

        $updatedOrders = [];
        $failedOrders = [];

        foreach ($request->order_ids as $orderId) {
            $order = CustomOrder::find($orderId);
            if ($order && $order->isPriceQuoted()) {
                if ($order->notifyUser()) {
                    $updatedOrders[] = $order->id;
                    // Here you would typically send email/SMS notification
                    // For now, just mark as notified
                } else {
                    $failedOrders[] = $order->id;
                }
            } else {
                $failedOrders[] = $orderId;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'updated_count' => count($updatedOrders),
                'failed_count' => count($failedOrders),
                'updated_orders' => $updatedOrders,
                'failed_orders' => $failedOrders
            ],
            'message' => "Bulk notification completed: " . count($updatedOrders) . " notified, " . count($failedOrders) . " failed"
        ]);
    }

    /**
     * Export orders to CSV/Excel
     */
    public function exportOrders(Request $request)
    {
        $query = CustomOrder::with(['user', 'product']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('selected_orders') && !empty($request->selected_orders)) {
            $selectedIds = explode(',', $request->selected_orders);
            $query->whereIn('id', $selectedIds);
        }

        $orders = $query->get();

        $csvData = [
            ['Order ID', 'Customer Name', 'Email', 'Product Type', 'Quantity', 'Status', 'Price', 'Payment Status', 'Created Date']
        ];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->id,
                $order->user->name ?? 'N/A',
                $order->user->email ?? 'N/A',
                $order->product_type ?? 'Custom Product',
                $order->quantity,
                $order->status,
                $order->final_price ?? 'Not priced',
                $order->payment_status ?? 'pending',
                $order->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = "custom_orders_export_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate average processing time helper
     */
    private function calculateAverageProcessingTime()
    {
        $avgTime = CustomOrder::whereNotNull('approved_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
            ->first();

        return $avgTime ? round($avgTime->avg_hours, 1) : 0;
    }
}
