<?php

/**
 * OrderController
 * 
 * Handles order creation from mobile app and admin order management
 * 
 * Routes:
 * POST   /api/v1/orders                    - Create order (mobile)
 * GET    /api/v1/orders                    - Get user's orders
 * GET    /api/v1/orders/{id}               - Get single order
 * PATCH  /api/v1/admin/orders/{id}/status - Update order status (admin)
 * GET    /api/v1/admin/orders              - Get all orders (admin)
 */

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Create a new order (from mobile app)
     * 
     * POST /api/v1/orders
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'required|string|max:20',
                'shipping_address' => 'required|string',
                'delivery_address' => 'nullable|string',
                'shipping_city' => 'nullable|string',
                'shipping_province' => 'nullable|string',
                'payment_method' => 'required|in:gcash,bank_transfer,cash',
                'payment_status' => 'nullable|in:pending,paid,verified,failed',
                'payment_reference' => 'nullable|string',
                'subtotal' => 'required|numeric|min:0',
                'shipping_fee' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
                'delivery_type' => 'nullable|in:pickup,deliver',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            try {
                // Create order with generated references
                $order = Order::create([
                    'order_ref' => $this->generateOrderRef(),
                    'tracking_number' => $this->generateTrackingNumber(),
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'delivery_address' => $validated['delivery_address'] ?? $validated['shipping_address'],
                    'shipping_city' => $validated['shipping_city'] ?? null,
                    'shipping_province' => $validated['shipping_province'] ?? null,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'] ?? 'pending',
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'subtotal' => $validated['subtotal'],
                    'shipping_fee' => $validated['shipping_fee'] ?? 0,
                    'discount' => $validated['discount'] ?? 0,
                    'total' => $validated['total'] ?? $validated['total_amount'],
                    'total_amount' => $validated['total_amount'] ?? $validated['total'],
                    'delivery_type' => $validated['delivery_type'] ?? 'deliver',
                    'status' => 'pending_confirmation',
                    'notes' => $validated['notes'] ?? null,
                    'source' => 'mobile',
                ]);

                // Add order items
                foreach ($validated['items'] as $item) {
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                DB::commit();

                // 🔔 Trigger notification event
                event(new \App\Events\OrderCreated($order));

                // Log the order creation
                Log::info('Order created from mobile', [
                    'order_id' => $order->id,
                    'order_ref' => $order->order_ref,
                    'tracking_number' => $order->tracking_number,
                    'customer' => $order->customer_name,
                    'total' => $order->total ?? $order->total_amount,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully. Admin will be notified.',
                    'data' => $this->formatOrder($order),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating order items', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's orders
     * 
     * GET /orders (web view)
     * GET /api/v1/orders?status=pending_confirmation&limit=20 (API)
     */
    public function index(Request $request)
    {
        try {
            $query = Order::with('orderItems.product');

            // Filter by authenticated user - show all orders (including guest orders with null user_id)
            // This allows viewing of orders created from mobile app before user login
            if (auth()->check()) {
                $query->where(function($q) {
                    $q->where('user_id', auth()->id())
                      ->orWhereNull('user_id');
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by payment status
            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Pagination
            $limit = $request->query('limit', 20);
            $orders = $query->orderByDesc('created_at')->paginate($limit);

            // Return web view if not API request
            if (!request()->wantsJson()) {
                return view('orders.index', compact('orders'));
            }

            // Return JSON for API requests
            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders', ['error' => $e->getMessage()]);
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch orders',
                ], 500);
            }
            abort(500, 'Failed to fetch orders');
        }
    }

    /**
     * Get single order with items
     * 
     * GET /orders/{id} (web view)
     * GET /api/v1/orders/{id} (API)
     */
    public function show($id)
    {
        try {
            $order = Order::with('orderItems.product')->findOrFail($id);

            // Check authorization
            if ($order->user_id && $order->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this order.');
            }

            // Return web view if not API request
            if (!request()->wantsJson()) {
                return view('orders.show', compact('order'));
            }

            // Return JSON for API requests
            return response()->json([
                'success' => true,
                'data' => $this->formatOrder($order),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }
            abort(404, 'Order not found');
        } catch (\Exception $e) {
            Log::error('Error fetching order', ['error' => $e->getMessage()]);
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch order',
                ], 500);
            }
            abort(500, 'Failed to fetch order');
        }
    }

    /**
     * Get all orders for admin dashboard
     * 
     * GET /api/v1/admin/orders?status=pending_confirmation&limit=50
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $query = Order::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by order ref or customer name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_ref', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            }

            // Filter by date range
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date,
                    $request->to_date,
                ]);
            }

            $limit = $request->query('limit', 50);
            $orders = $query->with('items')->orderByDesc('created_at')->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching admin orders', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
            ], 500);
        }
    }

    /**
     * Update order status (admin only)
     * 
     * PATCH /api/v1/admin/orders/{id}/status
     */
    public function updateStatus($id, Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $validated = $request->validate([
                'status' => 'required|in:confirmed,processing,shipped,delivered,cancelled,refunded',
                'notes' => 'nullable|string',
            ]);

            $order = Order::findOrFail($id);

            // Update status and timestamp
            $order->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['notes'] ?? null,
                $this->getStatusTimestampField($validated['status']) => now(),
            ]);

            // 🔔 Trigger status change event
            event(new \App\Events\OrderStatusChanged($order));

            Log::info('Order status updated by admin', [
                'order_id' => $order->id,
                'new_status' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $this->formatOrder($order),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating order status', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
            ], 500);
        }
    }

    /**
     * Get status timestamp field
     */
    private function getStatusTimestampField(string $status): string
    {
        $fields = [
            'confirmed' => 'confirmed_at',
            'shipped' => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        return $fields[$status] ?? 'updated_at';
    }

    /**
     * Generate a unique order reference (e.g., ORD-YYYYMMDD-XYZ)
     */
    private function generateOrderRef(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        $count = Order::whereDate('created_at', today())->count() + 1;
        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate a tracking number; fallback to order ref when not set
     */
    private function generateTrackingNumber(): string
    {
        $unique = strtoupper(bin2hex(random_bytes(4)));
        return 'TRK-' . now()->format('Ymd') . '-' . $unique;
    }

    /**
     * Format order data for API response
     */
    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'orderRef' => $order->order_ref,
            'customerName' => $order->customer_name,
            'customerEmail' => $order->customer_email,
            'customerPhone' => $order->customer_phone,
            'shippingAddress' => $order->shipping_address,
            'deliveryAddress' => $order->delivery_address,
            'shippingCity' => $order->shipping_city,
            'shippingProvince' => $order->shipping_province,
            'subtotal' => (float) $order->subtotal,
            'shippingFee' => (float) $order->shipping_fee,
            'discount' => (float) $order->discount,
            'total' => (float) ($order->total ?? $order->total_amount ?? 0),
            'deliveryType' => $order->delivery_type,
            'trackingNumber' => $order->tracking_number,
            'paymentMethod' => $order->payment_method,
            'paymentStatus' => $order->payment_status,
            'paymentReference' => $order->payment_reference,
            'paymentProof' => $order->payment_proof_path,
            'status' => $order->status,
            'statusLabel' => $order->status_label,
            'notes' => $order->notes,
            'adminNotes' => $order->admin_notes,
            'items' => $order->items->map(function(OrderItem $item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ];
            }),
            'createdAt' => $order->created_at->toIso8601String(),
            'confirmedAt' => $order->confirmed_at?->toIso8601String(),
            'shippedAt' => $order->shipped_at?->toIso8601String(),
            'deliveredAt' => $order->delivered_at?->toIso8601String(),
        ];
    }

    /**
     * Authorize admin access
     */
    private function authorizeAdmin(): void
    {
        // In a real app, you would check if user has admin role
        // For now, we'll just ensure they're authenticated
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }
        
        // TODO: Add role check
        // if (!auth()->user()->hasRole('admin')) {
        //     abort(403, 'Forbidden');
        // }
    }
}
