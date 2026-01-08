<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class TrackOrderController extends Controller
{
    /**
     * Show track order search page
     */
    public function index()
    {
        // Only show recent orders if user is logged in (for their own orders)
        // Guest users should not see any orders until they search
        $recentOrders = collect(); // Empty collection
        
        if (Auth::check()) {
            // Show only logged-in user's own recent orders
            // Include orders with matching user_id OR matching customer_email
            $user = Auth::user();
            $recentOrders = Order::with(['user', 'items.product'])
                                ->where(function($query) use ($user) {
                                    $query->where('user_id', $user->id)
                                          ->orWhere('customer_email', $user->email);
                                })
                                ->where('payment_status', 'paid')
                                ->whereNotNull('tracking_number')
                                ->latest()
                                ->limit(10)
                                ->get();
        }
        
        return view('track-order.index', compact('recentOrders'));
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

        $query = Order::with(['user', 'items.product']);

        if ($request->search_type === 'tracking_number') {
            $query->where('tracking_number', $request->search_value);
        } elseif ($request->search_type === 'order_id') {
            $query->where('id', $request->search_value);
            if ($request->filled('email')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('email', $request->email);
                });
            }
        } elseif ($request->search_type === 'email') {
            // Search by user email OR customer_email field
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function($userQuery) use ($request) {
                    $userQuery->where('email', $request->email);
                })->orWhere('customer_email', $request->email);
            });
        }

        $orders = $query->latest()->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No orders found with the provided information.');
        }

        // If single order, show tracking details
        if ($orders->count() === 1) {
            return view('track-order.show', ['order' => $orders->first()]);
        }

        // Multiple orders, show list
        return view('track-order.list', compact('orders'));
    }

    /**
     * Show specific order tracking
     */
    public function show($trackingNumber)
    {
        $order = Order::with(['user', 'items.product'])
                     ->where('tracking_number', $trackingNumber)
                     ->firstOrFail();

        return view('track-order.show', compact('order'));
    }

    /**
     * Get tracking history as JSON
     */
    public function getHistory($trackingNumber)
    {
        $order = Order::where('tracking_number', $trackingNumber)->firstOrFail();
        
        // The model's getter already handles conversion to array
        $history = $order->tracking_history ?? [];

        return response()->json([
            'success' => true,
            'tracking_number' => $order->tracking_number,
            'current_status' => $order->tracking_status,
            'history' => $history,
            'estimated_delivery' => $order->estimated_delivery_date,
            'courier' => [
                'name' => $order->courier_name,
                'contact' => $order->courier_contact,
                'tracking_url' => $order->courier_tracking_url,
            ]
        ]);
    }
}
