<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric',
                'shipping_address' => 'required|array',
                'shipping_address.full_name' => 'required|string',
                'shipping_address.phone_number' => 'required|string',
                'shipping_address.region' => 'required|string',
                'shipping_address.province' => 'required|string',
                'shipping_address.city' => 'required|string',
                'shipping_address.barangay' => 'required|string',
                'shipping_address.postal_code' => 'required|string',
                'shipping_address.street' => 'required|string',
                'payment_method' => 'nullable|string',
                'special_notes' => 'nullable|string',
            ]);

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shippingFee = 50.00;
            $total = $subtotal + $shippingFee;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_ref' => 'ORD-' . time(),
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'status' => 'pending_confirmation',
                'payment_method' => $validated['payment_method'] ?? 'gcash',
                'payment_status' => 'pending',
                'shipping_address' => json_encode($validated['shipping_address']),
                'special_notes' => $validated['special_notes'] ?? '',
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Load relationship for response
            $order->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            $query = Order::where('user_id', $user->id);

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sort = $request->get('sort', '-created_at');
            if ($sort === '-created_at') {
                $query->orderByDesc('created_at');
            } else {
                $query->orderBy($sort);
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => [
                    'orders' => $orders->items(),
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single order details
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)
                          ->with('items')
                          ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update order
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            $validated = $request->validate([
                'status' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'special_notes' => 'nullable|string',
            ]);

            $order->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel($id, Request $request)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            if ($order->status !== 'pending_confirmation') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only cancel pending orders',
                ], 400);
            }

            $order->update([
                'status' => 'cancelled',
                'special_notes' => $request->get('reason', '') . ' ' . $order->special_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order status
     */
    public function getStatus($id)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Order status retrieved successfully',
                'data' => [
                    'id' => $order->id,
                    'order_ref' => $order->order_ref,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found: ' . $e->getMessage(),
            ], 404);
        }
    }
}
