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
}
