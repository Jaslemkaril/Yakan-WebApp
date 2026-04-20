<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomOrderRefundRequest;
use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Services\OrderKpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $allowedScopes = [
            'recent',
            'pending_confirmation',
            'processing_shipping',
            'refund_eligible',
            'refunded_today',
            'done_orders',
        ];

        $activeScope = (string) $request->query('scope', 'recent');
        if (!in_array($activeScope, $allowedScopes, true)) {
            $activeScope = 'recent';
        }

        $sharedOrderStats = app(OrderKpiService::class)->getStaffDashboardStats();
        $refundEligibleOrdersQuery = $this->refundEligibleOrdersQuery();
        $refundWorkloadCount = $this->refundWorkloadUnderReviewCount();

        $pendingConfirmationCount = (int) $sharedOrderStats['pending_confirmation'];
        $processingCount = (int) $sharedOrderStats['processing_shipping'];
        $readyForRefundCount = $refundWorkloadCount;
        $refundedTodayCount = (int) $sharedOrderStats['refunded_today'];
        $doneOrdersCount = (int) $sharedOrderStats['done_orders'];

        $ordersQuery = Order::with('user');
        $ordersTitle = 'Recent Orders';

        switch ($activeScope) {
            case 'pending_confirmation':
                $ordersQuery->whereIn('status', OrderKpiService::PENDING_CONFIRMATION_STATUSES)
                    ->orderByDesc('created_at');
                $ordersTitle = 'Pending Confirmation Orders';
                break;

            case 'processing_shipping':
                $ordersQuery->whereIn('status', OrderKpiService::PROCESSING_SHIPPING_STATUSES)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Processing / Shipping Orders';
                break;

            case 'refund_eligible':
                $ordersQuery->whereIn('id', (clone $refundEligibleOrdersQuery)->select('id'))
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Refund-Eligible Orders';
                break;

            case 'refunded_today':
                $refundedDateColumn = Schema::hasColumn('orders', 'refunded_at') ? 'refunded_at' : 'updated_at';
                $ordersQuery->where(function ($query) {
                    $query->where('status', 'refunded')
                        ->orWhereRaw('LOWER(COALESCE(payment_status, "")) = ?', ['refunded']);
                })->whereDate($refundedDateColumn, today())
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Refunded Today';
                break;

            case 'done_orders':
                $ordersQuery->whereIn('status', OrderKpiService::DONE_ORDER_STATUSES)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Done Orders';
                break;

            case 'recent':
            default:
                $ordersQuery->orderByDesc('created_at');
                break;
        }

        $recentOrders = $ordersQuery->take(20)->get();

        return view('staff.dashboard', compact(
            'pendingConfirmationCount',
            'processingCount',
            'readyForRefundCount',
            'refundedTodayCount',
            'doneOrdersCount',
            'recentOrders',
            'activeScope',
            'ordersTitle'
        ));
    }

    public function cancelledOrders(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])
            ->where('status', 'cancelled')
            ->orderByDesc('cancelled_at')
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_ref', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('staff.orders.cancelled', compact('orders'));
    }

    public function refundedOrders(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product', 'refundRequests'])
            ->where('status', 'refunded')
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_ref', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('staff.orders.refunded', compact('orders'));
    }

    private function refundEligibleOrdersQuery()
    {
        $query = Order::query()
            ->whereIn('status', OrderKpiService::DONE_ORDER_STATUSES)
            ->whereRaw('LOWER(COALESCE(payment_status, "")) IN (?, ?, ?)', OrderKpiService::PAID_PAYMENT_STATUSES);

        $refundWindowDays = max(1, (int) config('orders.refund_warranty_days', 7));
        $eligibleStartCutoff = now()->subDays($refundWindowDays);
        $query->whereRaw('COALESCE(confirmed_at, delivered_at, created_at) >= ?', [$eligibleStartCutoff]);

        if (Schema::hasTable('order_refund_requests')) {
            $activeStatuses = ['requested', 'under_review', 'approved', 'processed'];
            $activeWorkflowStatuses = [
                'pending_review',
                'under_review',
                'awaiting_return_shipment',
                'return_in_transit',
                'return_received',
                'pending_payout',
                'approved',
                'processed',
            ];
            $hasWorkflowStatus = Schema::hasColumn('order_refund_requests', 'workflow_status');

            $query->whereDoesntHave('refundRequests', function ($refundQuery) use ($activeStatuses, $activeWorkflowStatuses, $hasWorkflowStatus) {
                $refundQuery->whereIn('status', $activeStatuses);

                if ($hasWorkflowStatus) {
                    $refundQuery->orWhereIn('workflow_status', $activeWorkflowStatuses);
                }
            });
        }

        return $query;
    }

    private function refundWorkloadUnderReviewCount(): int
    {
        $total = 0;

        if (Schema::hasTable('order_refund_requests')) {
            $regularQuery = OrderRefundRequest::query()
                ->whereHas('order')
                ->where(function ($query) {
                    $query->whereRaw('LOWER(COALESCE(reason, "")) NOT LIKE ?', ['%cancel%'])
                        ->whereRaw('LOWER(COALESCE(comment, "")) NOT LIKE ?', ['%cancel%'])
                        ->whereRaw('LOWER(COALESCE(details, "")) NOT LIKE ?', ['%cancel%']);
                });

            if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                $regularQuery->where(function ($query) {
                    $query->whereIn('status', ['requested', 'pending_review', 'under_review'])
                        ->orWhereIn('workflow_status', ['requested', 'pending_review', 'under_review']);
                });
            } else {
                $regularQuery->whereIn('status', ['requested', 'pending_review', 'under_review']);
            }

            $total += $regularQuery->count();
        }

        if (Schema::hasTable('custom_order_refund_requests')) {
            $total += CustomOrderRefundRequest::query()
                ->where('request_type', 'refund')
                ->whereNotIn('status', ['approved', 'processed', 'rejected'])
                ->count();
        }

        return $total;
    }
}
