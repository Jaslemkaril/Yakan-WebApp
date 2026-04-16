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
use App\Models\OrderRefundRequest;
use App\Models\Product;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    private function orderRelationsForUserViews(): array
    {
        $relations = ['orderItems.product', 'user'];

        if (Schema::hasTable('product_bundle_items')) {
            $relations = ['orderItems.product.bundleItems.componentProduct', 'user'];
        }

        return $relations;
    }

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
     * GET /api/v1/orders?status=pending_confirmation&limit=20
     */
    public function index(Request $request)
    {
        try {
            // Get orders with matching user_id OR matching customer_email
            $user = Auth::user();
                        $query = Order::with($this->orderRelationsForUserViews())
                ->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('customer_email', $user->email);
                });

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

            // Return JSON for API callers, Blade view for web
            if ($request->wantsJson() || $request->is('api/*')) {
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
            }

            // Return Blade view for web requests
            return view('orders.index', ['orders' => $orders]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders', ['error' => $e->getMessage()]);
            
            if ($request->wantsJson() || $request->is('api/*')) {
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
     * GET /api/v1/orders/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $order = Order::with($this->orderRelationsForUserViews())->find($id);

            if (!$order) {
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found',
                    ], 404);
                }
                abort(404);
            }

            // Return JSON for API callers, Blade view for web
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $this->formatOrder($order),
                ]);
            }

            $refundRequest = null;
            $canRequestRefund = $order->canRequestRefund()
                && (int) $order->user_id === (int) auth()->id();
            $refundWarrantyDays = $order->getRefundWarrantyDays();
            $refundWarrantyDeadline = $order->getRefundWarrantyDeadline();
            $isRefundWarrantyExpired = $order->status === 'completed' && !$order->isRefundWithinWarranty();

            if (Schema::hasTable('order_refund_requests')) {
                $refundRequest = OrderRefundRequest::where('order_id', $order->id)
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->first();
            }

            return view('orders.show', compact('order', 'refundRequest', 'canRequestRefund', 'refundWarrantyDays', 'refundWarrantyDeadline', 'isRefundWarrantyExpired'));
        } catch (\Exception $e) {
            Log::error('Error fetching order', ['error' => $e->getMessage()]);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch order',
                ], 500);
            }
            abort(500, 'Failed to load order');
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
                'status' => 'required|in:confirmed,processing,shipped,delivered,cancellation_requested,cancelled,refunded',
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
     * Cancel an order (customer-initiated)
     */
    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:255',
            'cancel_reason_other' => 'nullable|string|max:255',
            'cancel_notes' => 'nullable|string|max:500',
        ]);

        $resolvedCancelReason = trim((string) $validated['cancel_reason']);
        if (strcasecmp($resolvedCancelReason, 'Other') === 0) {
            $otherReason = trim((string) ($validated['cancel_reason_other'] ?? ''));
            if ($otherReason === '') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['cancel_reason_other' => 'Please specify your cancellation reason.']);
            }

            $resolvedCancelReason = $otherReason;
        }

        $cancelNotes = trim((string) ($validated['cancel_notes'] ?? ''));

        $currentStatus = strtolower((string) $order->status);
        $cancellableStatuses = ['pending', 'pending_confirmation', 'confirmed', 'processing'];

        if ($currentStatus === 'cancellation_requested') {
            return redirect()->back()->with('info', 'Your cancellation request is already pending admin review.');
        }

        if (!in_array($currentStatus, $cancellableStatuses, true)) {
            $message = 'This order can no longer be cancelled because it is already in ' . strtoupper($order->status) . ' status.';
            $this->notifyCancelDecision($order, 'rejected', $resolvedCancelReason, $message);
            return redirect()->back()->with('error', $message);
        }

        $this->ensureCancellationRequestedStatusSupported();

        $currentPaymentStatus = strtolower((string) $order->payment_status);
        $wasPaid = in_array($currentPaymentStatus, ['paid', 'verified'], true);
        $newPaymentStatus = $wasPaid ? $order->payment_status : $order->payment_status;
        $refundProcessStarted = false;

        if ($wasPaid) {
            $this->ensureRefundRequestsTableExists();
            $this->ensureRefundWorkflowColumnsExist();

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

                $existingRefund = OrderRefundRequest::where('order_id', $order->id)
                    ->where('user_id', auth()->id())
                    ->where(function ($query) use ($activeStatuses, $activeWorkflowStatuses) {
                        $query->whereIn('status', $activeStatuses);

                        if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                            $query->orWhereIn('workflow_status', $activeWorkflowStatuses);
                        }
                    })
                    ->latest()
                    ->first();

                if (!$existingRefund) {
                    $totalAmount = (float) ($order->total_amount ?? $order->total ?? 0);

                    $refundPayload = [
                        'order_id' => $order->id,
                        'user_id' => auth()->id(),
                        'reason' => 'Order cancellation',
                        'details' => 'Auto-generated cancellation refund request. Reason: ' . $validated['cancel_reason'],
                        'status' => 'requested',
                        'requested_at' => now(),
                    ];

                    if (Schema::hasColumn('order_refund_requests', 'refund_type')) {
                        $refundPayload['refund_type'] = 'full';
                    }
                    if (Schema::hasColumn('order_refund_requests', 'comment')) {
                        $refundPayload['comment'] = 'Customer cancelled order before fulfillment: ' . $validated['cancel_reason'];
                    }
                    if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                        $refundPayload['workflow_status'] = 'pending_review';
                    }
                    if (Schema::hasColumn('order_refund_requests', 'recommended_decision')) {
                        $refundPayload['recommended_decision'] = 'FULL_REFUND';
                    }
                    if (Schema::hasColumn('order_refund_requests', 'recommended_refund_amount')) {
                        $refundPayload['recommended_refund_amount'] = $totalAmount;
                    }
                    if (Schema::hasColumn('order_refund_requests', 'return_required')) {
                        $refundPayload['return_required'] = false;
                    }
                    if (Schema::hasColumn('order_refund_requests', 'payout_status')) {
                        $refundPayload['payout_status'] = 'pending';
                    }

                    OrderRefundRequest::create($refundPayload);
                }

                $refundProcessStarted = true;
            }
        }

        $paymentNote = $wasPaid
            ? 'Payment was received. Cancellation and refund request are pending admin review.'
            : 'Cancellation request is pending admin review.';

        $existingAdminNotes = trim((string) $order->admin_notes);
        $requestReasonLine = 'Customer cancellation requested: ' . $resolvedCancelReason;
        if (!str_contains($existingAdminNotes, $requestReasonLine)) {
            $existingAdminNotes = $existingAdminNotes !== ''
                ? ($existingAdminNotes . "\n" . $requestReasonLine)
                : $requestReasonLine;
        }
        if ($cancelNotes !== '') {
            $requestNoteLine = 'Customer cancellation note: ' . $cancelNotes;
            if (!str_contains($existingAdminNotes, $requestNoteLine)) {
                $existingAdminNotes = $existingAdminNotes !== ''
                    ? ($existingAdminNotes . "\n" . $requestNoteLine)
                    : $requestNoteLine;
            }
        }
        if (!str_contains($existingAdminNotes, $paymentNote)) {
            $existingAdminNotes = $existingAdminNotes !== ''
                ? ($existingAdminNotes . "\n" . $paymentNote)
                : $paymentNote;
        }

        $order->update([
            'status' => 'cancellation_requested',
            'payment_status' => $newPaymentStatus,
            'cancelled_at' => null,
            'admin_notes' => trim($existingAdminNotes),
        ]);

        $order->appendTrackingEvent('Cancellation Requested');
        $order->save();

        $this->notifyCancelDecision($order, 'pending', $resolvedCancelReason, $paymentNote);

        return redirect()->back()->with('success', $refundProcessStarted
            ? 'Cancellation requested. Refund request is now pending admin review.'
            : 'Cancellation requested. Please wait for admin approval.');
    }

    /**
     * Confirm order received by customer
     * 
     * POST /orders/{order}/confirm-received
     */
    public function confirmReceived(Order $order)
    {
        try {
            // Verify the order belongs to the authenticated user (cast to int to avoid type mismatch)
            if ((int) $order->user_id !== (int) auth()->id()) {
                $msg = 'Unauthorized action.';
                if (request()->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 403);
                return redirect()->back()->with('error', $msg);
            }

            // Check if order is in delivered status
            if ($order->status !== 'delivered') {
                $msg = 'Order must be delivered before confirmation.';
                if (request()->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                return redirect()->back()->with('error', $msg);
            }

            // Ensure 'completed' is a valid ENUM value before updating
            try {
                \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','pending_confirmation','confirmed','processing','shipped','delivered','completed','cancellation_requested','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");
            } catch (\Exception $e) {
                // ENUM already includes 'completed', safe to continue
            }

            // Finalize order only when customer confirms receipt.
            $order->status = 'completed';
            if (!$order->delivered_at) {
                $order->delivered_at = now();
            }
            $order->confirmed_at = now();
            $order->appendTrackingEvent('Order Received');
            $order->save();

            \Log::info('Order marked as received by customer', [
                'order_id' => $order->id,
                'user_id'  => auth()->id(),
            ]);

            $msg = 'Order marked as received. Thank you for your confirmation!';
            if (request()->expectsJson()) return response()->json(['success' => true, 'message' => $msg]);
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('Error confirming order received', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $msg = 'Failed to confirm order received. Please try again.';
            if (request()->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 500);
            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Submit a refund request for a completed order.
     */
    public function requestRefund(Request $request, Order $order)
    {
        $this->ensureRefundRequestsTableExists();
        $this->ensureRefundWorkflowColumnsExist();

        if (!Schema::hasTable('order_refund_requests')) {
            return redirect()->back()->with('error', 'Refund feature is not ready yet. Please try again shortly.');
        }

        if ((int) $order->user_id !== (int) auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

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

        $existing = OrderRefundRequest::where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->where(function ($query) use ($activeStatuses, $activeWorkflowStatuses) {
                $query->whereIn('status', $activeStatuses);

                if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $query->orWhereIn('workflow_status', $activeWorkflowStatuses);
                }
            })
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'A refund request for this order is already in progress.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:150',
            'refund_type' => 'nullable|in:full,partial,change_of_mind',
            'comment' => 'nullable|string|max:2000',
            'details' => 'nullable|string|max:2000',
            'evidence' => 'required|array|min:1|max:5',
            'evidence.*' => 'required|file|mimes:jpg,jpeg,png,webp,pdf,mp4,mov,webm|max:20480',
        ]);

        $comment = trim((string) ($validated['comment'] ?? $validated['details'] ?? ''));
        if ($comment === '') {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'comment' => 'Please provide a short explanation for your refund request.',
                ]);
        }

        if (strtolower((string) $validated['reason']) === 'damaged item' && !$this->hasVideoEvidence($request->file('evidence', []))) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'evidence' => 'For damaged items, please upload at least one opening/unboxing video as proof.',
                ]);
        }

        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence', []) as $file) {
                $evidencePaths[] = $file->store('refunds/order-' . $order->id, 'public');
            }
        }

        $refundType = $this->normalizeRefundType($validated['refund_type'] ?? null, $validated['reason']);

        // Step 2: create refund request record as pending review.
        $refundRequest = OrderRefundRequest::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'refund_type' => $refundType,
            'reason' => $validated['reason'],
            'comment' => $comment,
            'details' => $validated['details'] ?? $comment,
            'evidence_paths' => $evidencePaths,
            'status' => 'requested',
            'workflow_status' => 'pending_review',
            'requested_at' => now(),
            'payout_status' => 'pending',
        ]);

        // Step 3 + 4 + 5: system validation + decision engine recommendation.
        $systemValidation = $this->buildRefundValidationSnapshot($order, (int) auth()->id());
        $recommendation = $this->buildRefundRecommendation($order, $refundType, $validated['reason'], $comment, $systemValidation);

        $refundRequest->system_validation = $systemValidation['checks'] ?? [];
        $refundRequest->fraud_flags = $systemValidation['fraud']['flags'] ?? [];
        $refundRequest->fraud_risk_level = $systemValidation['fraud']['risk_level'] ?? 'low';
        $refundRequest->recommended_decision = $recommendation['decision'] ?? 'REJECT';
        $refundRequest->recommended_refund_amount = $recommendation['refund_amount'] ?? 0;
        $refundRequest->return_required = (bool) ($recommendation['return_required'] ?? false);
        $refundRequest->save();

        return redirect()->back()->with(
            'success',
            'Refund request submitted successfully. Awaiting admin review.'
        );
    }

    /**
     * Buyer submits return shipment details when return is required.
     */
    public function shipRefundReturn(Request $request, OrderRefundRequest $refundRequest)
    {
        if ((int) $refundRequest->user_id !== (int) auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $currentWorkflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if ($currentWorkflowStatus !== 'awaiting_return_shipment') {
            return redirect()->back()->with('error', 'Return shipment details are not required at this stage.');
        }

        $validated = $request->validate([
            'return_tracking_number' => 'required|string|max:120',
            'return_comment' => 'nullable|string|max:1000',
        ]);

        $refundRequest->return_tracking_number = trim((string) $validated['return_tracking_number']);
        $refundRequest->return_shipped_at = now();
        $refundRequest->workflow_status = 'return_in_transit';
        $refundRequest->status = 'approved';

        if (!empty($validated['return_comment'])) {
            $existingNote = trim((string) ($refundRequest->admin_note ?? ''));
            $suffix = 'Buyer return note: ' . trim((string) $validated['return_comment']);
            $refundRequest->admin_note = $existingNote !== '' ? ($existingNote . "\n" . $suffix) : $suffix;
        }

        $refundRequest->save();

        return redirect()->back()->with('success', 'Return shipment details submitted. Seller will verify the returned item upon receipt.');
    }

    /**
     * Serve refund evidence files for the requesting customer.
     */
    public function viewRefundEvidence(OrderRefundRequest $refundRequest, int $index)
    {
        if ((int) $refundRequest->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $evidence = is_array($refundRequest->evidence_paths ?? null) ? $refundRequest->evidence_paths : [];
        if (!array_key_exists($index, $evidence)) {
            abort(404);
        }

        $path = (string) $evidence[$index];
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404);
    }

    /**
     * Ensure refund requests table exists in environments where migrations may lag behind deployments.
     */
    private function ensureRefundRequestsTableExists(): void
    {
        if (Schema::hasTable('order_refund_requests')) {
            return;
        }

        try {
            Schema::create('order_refund_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('refund_type', 40)->nullable();
                $table->string('reason', 150);
                $table->text('comment')->nullable();
                $table->text('details')->nullable();
                $table->json('evidence_paths')->nullable();
                $table->enum('status', ['requested', 'under_review', 'approved', 'rejected', 'processed'])
                    ->default('requested');
                $table->string('workflow_status', 60)->nullable();
                $table->json('system_validation')->nullable();
                $table->json('fraud_flags')->nullable();
                $table->string('fraud_risk_level', 20)->nullable();
                $table->string('recommended_decision', 40)->nullable();
                $table->decimal('recommended_refund_amount', 12, 2)->nullable();
                $table->boolean('return_required')->default(false);
                $table->string('final_decision', 40)->nullable();
                $table->decimal('refund_amount', 12, 2)->nullable();
                $table->string('refund_channel', 40)->nullable();
                $table->string('refund_reference', 120)->nullable();
                $table->string('payout_status', 40)->nullable();
                $table->string('return_tracking_number', 120)->nullable();
                $table->timestamp('return_shipped_at')->nullable();
                $table->timestamp('return_received_at')->nullable();
                $table->text('admin_note')->nullable();
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        } catch (\Throwable $e) {
            Log::warning('Unable to auto-create order_refund_requests table', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ensure newer refund-workflow columns exist on lagging deployments.
     */
    private function ensureRefundWorkflowColumnsExist(): void
    {
        if (!Schema::hasTable('order_refund_requests')) {
            return;
        }

        try {
            Schema::table('order_refund_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!Schema::hasColumn('order_refund_requests', 'refund_type')) {
                    $table->string('refund_type', 40)->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('order_refund_requests', 'comment')) {
                    $table->text('comment')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $table->string('workflow_status', 60)->nullable()->after('status');
                }
                if (!Schema::hasColumn('order_refund_requests', 'system_validation')) {
                    $table->json('system_validation')->nullable()->after('workflow_status');
                }
                if (!Schema::hasColumn('order_refund_requests', 'fraud_flags')) {
                    $table->json('fraud_flags')->nullable()->after('system_validation');
                }
                if (!Schema::hasColumn('order_refund_requests', 'fraud_risk_level')) {
                    $table->string('fraud_risk_level', 20)->nullable()->after('fraud_flags');
                }
                if (!Schema::hasColumn('order_refund_requests', 'recommended_decision')) {
                    $table->string('recommended_decision', 40)->nullable()->after('fraud_risk_level');
                }
                if (!Schema::hasColumn('order_refund_requests', 'recommended_refund_amount')) {
                    $table->decimal('recommended_refund_amount', 12, 2)->nullable()->after('recommended_decision');
                }
                if (!Schema::hasColumn('order_refund_requests', 'return_required')) {
                    $table->boolean('return_required')->default(false)->after('recommended_refund_amount');
                }
                if (!Schema::hasColumn('order_refund_requests', 'final_decision')) {
                    $table->string('final_decision', 40)->nullable()->after('return_required');
                }
                if (!Schema::hasColumn('order_refund_requests', 'refund_amount')) {
                    $table->decimal('refund_amount', 12, 2)->nullable()->after('final_decision');
                }
                if (!Schema::hasColumn('order_refund_requests', 'refund_channel')) {
                    $table->string('refund_channel', 40)->nullable()->after('refund_amount');
                }
                if (!Schema::hasColumn('order_refund_requests', 'refund_reference')) {
                    $table->string('refund_reference', 120)->nullable()->after('refund_channel');
                }
                if (!Schema::hasColumn('order_refund_requests', 'payout_status')) {
                    $table->string('payout_status', 40)->nullable()->after('refund_reference');
                }
                if (!Schema::hasColumn('order_refund_requests', 'return_tracking_number')) {
                    $table->string('return_tracking_number', 120)->nullable()->after('payout_status');
                }
                if (!Schema::hasColumn('order_refund_requests', 'return_shipped_at')) {
                    $table->timestamp('return_shipped_at')->nullable()->after('return_tracking_number');
                }
                if (!Schema::hasColumn('order_refund_requests', 'return_received_at')) {
                    $table->timestamp('return_received_at')->nullable()->after('return_shipped_at');
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Unable to ensure refund workflow columns exist', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize user-selected refund type with reason fallback.
     */
    private function normalizeRefundType(?string $refundType, string $reason): string
    {
        $normalized = strtolower(trim((string) $refundType));
        if (in_array($normalized, ['full', 'partial', 'change_of_mind'], true)) {
            return $normalized;
        }

        if (strtolower(trim($reason)) === 'changed my mind') {
            return 'change_of_mind';
        }

        return 'full';
    }

    /**
     * Build system validation snapshot for the refund request.
     */
    private function buildRefundValidationSnapshot(Order $order, int $userId): array
    {
        $status = strtolower((string) $order->status);
        $paymentStatus = strtolower((string) $order->payment_status);

        $statusAllowed = in_array($status, ['delivered', 'completed'], true);
        $paymentAllowed = in_array($paymentStatus, ['paid', 'verified', 'completed'], true);
        $withinWindow = $order->isRefundWithinWarranty();
        $fraud = $this->evaluateRefundFraudHistory($order, $userId);

        return [
            'is_valid' => $statusAllowed && $paymentAllowed && $withinWindow,
            'checks' => [
                'order_status_ok' => $statusAllowed,
                'payment_status_ok' => $paymentAllowed,
                'refund_window_ok' => $withinWindow,
                'fraud_check_ok' => $fraud['risk_level'] !== 'high',
            ],
            'fraud' => $fraud,
        ];
    }

    /**
     * Evaluate potential refund abuse signals.
     */
    private function evaluateRefundFraudHistory(Order $order, int $userId): array
    {
        $query = OrderRefundRequest::query()->where('user_id', $userId);

        $recentRequestCount = (clone $query)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $rejectedCount = (clone $query)
            ->where(function ($builder) {
                $builder->where('status', 'rejected');
                if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $builder->orWhere('workflow_status', 'rejected');
                }
            })
            ->count();

        $activeCount = (clone $query)
            ->where(function ($builder) {
                $builder->whereIn('status', ['requested', 'under_review', 'approved']);
                if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $builder->orWhereIn('workflow_status', [
                        'pending_review',
                        'under_review',
                        'awaiting_return_shipment',
                        'return_in_transit',
                        'return_received',
                        'pending_payout',
                        'approved',
                    ]);
                }
            })
            ->count();

        $flags = [];
        if ($recentRequestCount >= 3) {
            $flags[] = 'high_request_frequency';
        }
        if ($rejectedCount >= 2) {
            $flags[] = 'multiple_rejected_requests';
        }
        if ($activeCount >= 2) {
            $flags[] = 'multiple_open_refunds';
        }

        $riskLevel = 'low';
        if ($recentRequestCount >= 4 || $rejectedCount >= 3 || $activeCount >= 3) {
            $riskLevel = 'high';
        } elseif (!empty($flags)) {
            $riskLevel = 'medium';
        }

        return [
            'risk_level' => $riskLevel,
            'flags' => $flags,
            'recent_request_count' => $recentRequestCount,
            'rejected_count' => $rejectedCount,
            'open_request_count' => $activeCount,
        ];
    }

    /**
     * Decision engine for full/partial/return-required/reject recommendations.
     */
    private function buildRefundRecommendation(Order $order, string $refundType, string $reason, string $comment, array $validation): array
    {
        $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
        $reasonNormalized = strtolower(trim($reason));

        if (empty($validation['is_valid'])) {
            return [
                'decision' => 'REJECT',
                'refund_amount' => 0,
                'return_required' => false,
                'why' => 'Validation checks failed',
            ];
        }

        if (($validation['fraud']['risk_level'] ?? 'low') === 'high') {
            return [
                'decision' => 'REJECT',
                'refund_amount' => 0,
                'return_required' => false,
                'why' => 'High fraud risk',
            ];
        }

        if ($refundType === 'change_of_mind' || $reasonNormalized === 'changed my mind') {
            // Change-of-mind is accepted conditionally, with return requirement and restocking fee.
            $restockingFeeRate = str_contains(strtolower($comment), 'opened') ? 0.20 : 0.10;
            $refundAmount = max(0, round($orderTotal * (1 - $restockingFeeRate), 2));

            return [
                'decision' => 'RETURN_REQUIRED',
                'refund_amount' => $refundAmount,
                'return_required' => true,
                'why' => 'Change-of-mind policy requires return before refund',
            ];
        }

        if ($refundType === 'partial') {
            $refundAmount = max(0, round($orderTotal * 0.50, 2));
            return [
                'decision' => 'PARTIAL_REFUND',
                'refund_amount' => $refundAmount,
                'return_required' => false,
                'why' => 'Partial refund policy',
            ];
        }

        $fullRefundReasons = [
            'item not as described',
            'damaged item',
            'wrong item received',
            'incomplete order',
        ];

        if (in_array($reasonNormalized, $fullRefundReasons, true)) {
            return [
                'decision' => 'FULL_REFUND',
                'refund_amount' => max(0, round($orderTotal, 2)),
                'return_required' => false,
                'why' => 'Eligible quality/delivery issue',
            ];
        }

        return [
            'decision' => 'PARTIAL_REFUND',
            'refund_amount' => max(0, round($orderTotal * 0.30, 2)),
            'return_required' => false,
            'why' => 'Default partial recommendation',
        ];
    }

    /**
     * Check if at least one uploaded file is a video.
     */
    private function hasVideoEvidence(array $files): bool
    {
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $mime = strtolower((string) $file->getMimeType());
            $ext = strtolower((string) $file->getClientOriginalExtension());

            if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'mov', 'webm'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Email customer when cancel request is approved or rejected.
     */
    private function notifyCancelDecision(Order $order, string $decision, string $reason, string $message): void
    {
        $recipient = $order->user?->email ?: $order->customer_email;
        if (!$recipient) {
            return;
        }

        $decisionNormalized = strtolower($decision);
        $decisionLabel = match ($decisionNormalized) {
            'approved' => 'Approved',
            'pending' => 'Pending Review',
            default => 'Rejected',
        };
        $subject = 'Order Cancel Request ' . $decisionLabel . ' - ' . ($order->order_ref ?? ('#' . $order->id));
        $intro = match ($decisionNormalized) {
            'approved' => 'Your order cancellation request has been approved.',
            'pending' => 'Your order cancellation request has been submitted and is now pending admin review.',
            default => 'Your order cancellation request has been rejected.',
        };

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $order->user?->name ?: ($order->customer_name ?: 'Customer'),
                    'introText' => $intro,
                    'orderRef' => $order->order_ref,
                    'orderId' => $order->id,
                    'requestType' => 'Order Cancellation',
                    'decision' => $decisionLabel,
                    'reason' => $reason,
                    'adminNote' => null,
                    'approvedAmount' => null,
                    'extraMessage' => $message,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send cancellation decision email', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
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

    /**
     * Ensure orders.status ENUM supports cancellation_requested.
     */
    private function ensureCancellationRequestedStatusSupported(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        try {
            \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','pending_confirmation','confirmed','processing','shipped','delivered','completed','cancellation_requested','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");
        } catch (\Throwable $e) {
            Log::warning('Unable to ensure status supports cancellation_requested', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
