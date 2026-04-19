<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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

        $pendingConfirmationCount = Order::whereIn('status', ['pending_confirmation', 'pending'])->count();
        $processingCount = Order::whereIn('status', ['confirmed', 'processing', 'shipped'])->count();
        $readyForRefundCount = Order::whereIn('status', ['delivered', 'completed'])->count();
        $refundedTodayCount = Order::where('status', 'refunded')->whereDate('updated_at', today())->count();
        $doneOrdersCount = Order::whereIn('status', ['delivered', 'completed'])->count();

        $ordersQuery = Order::with('user');
        $ordersTitle = 'Recent Orders';

        switch ($activeScope) {
            case 'pending_confirmation':
                $ordersQuery->whereIn('status', ['pending_confirmation', 'pending'])
                    ->orderByDesc('created_at');
                $ordersTitle = 'Pending Confirmation Orders';
                break;

            case 'processing_shipping':
                $ordersQuery->whereIn('status', ['confirmed', 'processing', 'shipped'])
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Processing / Shipping Orders';
                break;

            case 'refund_eligible':
                $ordersQuery->whereIn('status', ['delivered', 'completed'])
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Refund-Eligible Orders';
                break;

            case 'refunded_today':
                $ordersQuery->where('status', 'refunded')
                    ->whereDate('updated_at', today())
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at');
                $ordersTitle = 'Refunded Today';
                break;

            case 'done_orders':
                $ordersQuery->whereIn('status', ['delivered', 'completed'])
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
}
