<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

/**
 * Handles Orders for both Admin and Users
 */
class OrderController extends Controller
{
    /**
     * User: List only the authenticated user's orders
     */
    public function index()
    {
        $user = auth()->user();
        $orders = Order::with('orderItems.product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Admin: Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,refunded,failed',
        ]);

        $order->status = $request->status;
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }

        $order->save();

        return response()->json(['message' => 'Order updated successfully']);
    }

    /**
     * User: Place an order
     */
    public function placeOrder(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 400);
        }

        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $request->total_amount,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
            'shipping_address' => $request->shipping_address ?? null,
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'order_id' => $order->id,
        ]);
    }

    /**
     * User: Show a specific order
     */
    public function show($id)
    {
        $order = Order::with('orderItems.product', 'user')->findOrFail($id);

        // Only allow the owner or admin
        if (auth()->user()->role !== 'admin' && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        return view('orders.show', compact('order'));
    }

    /**
     * User: Confirm order received (mark as completed)
     */
    public function confirmReceived($id)
    {
        $order = Order::findOrFail($id);

        // Only allow the order owner to confirm receipt
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow confirmation if order is delivered
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Only delivered orders can be confirmed as received.');
        }

        // Update order status to completed
        $order->status = 'completed';
        $order->appendTrackingEvent('Order received and completed by customer');
        $order->save();

        // Notify user
        \App\Models\Notification::createNotification(
            $order->user_id,
            'order',
            'Order Completed',
            "Thank you for confirming receipt of order #{$order->id}. We hope you enjoy your purchase!",
            route('orders.show', $order->id),
            [
                'order_id' => $order->id,
                'status' => 'completed',
            ]
        );

        // Notify admins
        $adminUsers = \App\Models\User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            \App\Models\Notification::createNotification(
                $admin->id,
                'order',
                'Order Received - Confirmed by Customer',
                "Order #{$order->id} (Tracking: {$order->tracking_number}) has been confirmed as received by {$order->user->name}. Order is now completed.",
                '/admin/orders/' . $order->id,
                [
                    'order_id' => $order->id,
                    'customer_name' => $order->user->name,
                    'tracking_number' => $order->tracking_number,
                    'status' => 'completed',
                ]
            );
        }

        return back()->with('success', 'Thank you! Your order has been marked as received and completed.');
    }
}
