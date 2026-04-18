<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CustomOrder;
use Illuminate\Support\Facades\Auth;

class TrackOrderController extends Controller
{
    /**
     * Show track order search page
     */
    public function index()
    {
        $recentOrders  = collect();
        $customOrders  = collect();

        if (Auth::check()) {
            $user = Auth::user();

            // Regular orders — show all non-cancelled orders so users can see pending/processing too
            $recentOrders = Order::with(['user', 'orderItems.product'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('customer_email', $user->email);
                })
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(function ($o) { $o->_order_type = 'regular'; return $o; });

            // Custom orders — all non-cancelled/rejected statuses
            $customOrders = CustomOrder::with('user')
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->latest()
                ->limit(6)
                ->get()
                ->map(function ($o) { $o->_order_type = 'custom'; return $o; });
        }

        // Merge and sort by created_at descending, keep at most 10
        $allOrders = $recentOrders->merge($customOrders)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return view('track-order.index', ['recentOrders' => $allOrders]);
    }

    /**
     * Search and display order tracking
     */
    public function search(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:tracking_number,order_id,email',
            'search_value' => 'required|string',
            'email' => 'required_if:search_type,email|email|nullable',
        ]);

        $orders       = collect();
        $customOrders = collect();

        // ── Regular Orders ───────────────────────────────────────────────
        $query = Order::with(['user', 'orderItems.product']);

        if ($request->search_type === 'tracking_number') {
            $query->where('tracking_number', $request->search_value);
        } elseif ($request->search_type === 'order_id') {
            $query->where('id', $request->search_value);
            if ($request->filled('email')) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', fn ($uq) => $uq->where('email', $request->email))
                      ->orWhere('customer_email', $request->email);
                });
            }
        } elseif ($request->search_type === 'email') {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', fn ($uq) => $uq->where('email', $request->email))
                  ->orWhere('customer_email', $request->email);
            });
        }

        $orders = $query->latest()->get()
            ->map(function ($o) { $o->_order_type = 'regular'; return $o; });

        // ── Custom Orders ────────────────────────────────────────────────
        if ($request->search_type === 'order_id') {
            $customOrders = CustomOrder::with('user')
                ->where('id', $request->search_value)
                ->get()
                ->map(function ($o) { $o->_order_type = 'custom'; return $o; });
        } elseif ($request->search_type === 'email') {
            $customOrders = CustomOrder::with('user')
                ->whereHas('user', fn ($q) => $q->where('email', $request->email))
                ->latest()
                ->get()
                ->map(function ($o) { $o->_order_type = 'custom'; return $o; });
        }

        $allOrders = $orders->merge($customOrders)->sortByDesc('created_at')->values();

        if ($allOrders->isEmpty()) {
            return redirect()->back()->with('error', 'No orders found with the provided information.');
        }

        // If single regular order with tracking number, go straight to show page
        if ($allOrders->count() === 1 && $allOrders->first()->_order_type === 'regular') {
            $single = $allOrders->first();
            if ($single->tracking_number) {
                return view('track-order.show', ['order' => $single]);
            }
        }

        // If single custom order, redirect to its detail page
        if ($allOrders->count() === 1 && $allOrders->first()->_order_type === 'custom') {
            return redirect()->route('custom_orders.show', $allOrders->first()->id)
                ->with('info', 'Custom orders can be tracked on their detail page.');
        }

        $orders = $allOrders;
        return view('track-order.list', compact('orders'));
    }

    /**
     * Show specific order tracking (regular orders only — by tracking number)
     */
    public function show($trackingNumber)
    {
        $order = Order::with(['user', 'orderItems.product.bundleItems.componentProduct'])
                     ->where('tracking_number', $trackingNumber)
                     ->firstOrFail();

        $order->_order_type = 'regular';

        return view('track-order.show', compact('order'));
    }

    /**
     * Get tracking history as JSON
     */
    public function getHistory($trackingNumber)
    {
        $order = Order::where('tracking_number', $trackingNumber)->firstOrFail();

        $history = $order->tracking_history ?? [];

        return response()->json([
            'success'           => true,
            'tracking_number'   => $order->tracking_number,
            'current_status'    => $order->tracking_status,
            'history'           => $history,
            'estimated_delivery'=> $order->estimated_delivery_date,
            'courier' => [
                'name'         => $order->courier_name,
                'contact'      => $order->courier_contact,
                'tracking_url' => $order->courier_tracking_url,
            ]
        ]);
    }
}
