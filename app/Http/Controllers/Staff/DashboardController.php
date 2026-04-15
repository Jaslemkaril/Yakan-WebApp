<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $pendingConfirmationCount = Order::whereIn('status', ['pending_confirmation', 'pending'])->count();
        $processingCount = Order::whereIn('status', ['confirmed', 'processing', 'shipped'])->count();
        $readyForRefundCount = Order::whereIn('status', ['delivered', 'completed'])->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->count();
        $refundedOrdersCount = Order::where('status', 'refunded')->count();
        $refundedTodayCount = Order::where('status', 'refunded')->whereDate('updated_at', today())->count();

        $recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('staff.dashboard', compact(
            'pendingConfirmationCount',
            'processingCount',
            'readyForRefundCount',
            'cancelledOrdersCount',
            'refundedOrdersCount',
            'refundedTodayCount',
            'recentOrders'
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
