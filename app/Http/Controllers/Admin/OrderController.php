<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Services\Payment\PayMongoCheckoutService;
use App\Services\TransactionalMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // Show all orders
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])->orderByDesc('created_at');

        // Advanced filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $orders = $query->paginate($perPage)->appends($request->all());

        $supportsDownpayment = Schema::hasColumn('orders', 'payment_option')
            && Schema::hasColumn('orders', 'downpayment_amount')
            && Schema::hasColumn('orders', 'remaining_balance')
            && Schema::hasColumn('orders', 'total_amount');
        $paidRevenueExpr = $supportsDownpayment
            ? "CASE WHEN payment_option = 'downpayment' AND COALESCE(remaining_balance, 0) > 0 THEN downpayment_amount ELSE total_amount END"
            : 'total_amount';

        // Calculate statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereRaw('LOWER(status) = ?', ['pending'])->count(),
            'processing_orders' => Order::whereRaw('LOWER(status) = ?', ['processing'])->count(),
            'shipped_orders' => Order::whereRaw('LOWER(status) = ?', ['shipped'])->count(),
            'delivered_orders' => Order::whereRaw('LOWER(status) = ?', ['delivered'])->count(),
            'total_revenue' => Order::whereIn('payment_status', ['paid', 'completed', 'verified'])
                ->sum(DB::raw($paidRevenueExpr)),
            'pending_revenue' => Order::where('payment_status', 'pending')
                ->sum(DB::raw($paidRevenueExpr)),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->whereIn('payment_status', ['paid', 'completed', 'verified'])
                ->sum(DB::raw($paidRevenueExpr)),
        ];

        if ($request->ajax()) {
            return view('admin.orders.partials.orders-rows', compact('orders'))->render();
        }

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    // Show single order
    public function show(Order $order)
    {
        $order->load('user', 'userAddress', 'orderItems.product.category');

        $latestRefundRequest = null;
        if (Schema::hasTable('order_refund_requests')) {
            $latestRefundRequest = OrderRefundRequest::with(['user', 'reviewer'])
                ->where('order_id', $order->id)
                ->latest()
                ->first();
        }

        return view('admin.orders.show', compact('order', 'latestRefundRequest'));
    }

    /**
     * Fetch verified PayMongo receipt details for admin display.
     */
    public function paymongoReceipt(Order $order, PayMongoCheckoutService $payMongoService): JsonResponse
    {
        if (strtolower((string) $order->payment_method) !== 'paymongo') {
            return response()->json([
                'success' => false,
                'message' => 'This order is not a PayMongo payment.',
            ], 422);
        }

        try {
            $receipt = $payMongoService->getVerifiedReceiptForOrder($order);

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unable to fetch verified PayMongo receipt for admin.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch receipt from PayMongo right now. Please try again.',
            ], 502);
        }
    }

    // Update order status
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'confirm_delivery' => 'nullable|boolean',
        ]);

        if ($request->status === 'delivered' && !$request->boolean('confirm_delivery')) {
            return redirect()->back()->with('error', 'Please confirm delivery before marking this order as delivered.');
        }

        if (
            $request->status === 'cancelled'
            && in_array(strtolower((string) $order->status), ['delivered', 'completed', 'refunded'], true)
        ) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        $oldStatus = $order->status;
        $order->status = $request->status;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($request->status === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'cancelled') {
            $order->payment_status = 'failed';
        }

        if ($request->status === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        // sync tracking status and history
        $order->tracking_status = ucfirst($request->status);
        $order->appendTrackingEvent(ucfirst($request->status));
        $order->save();

        // Send notifications to user and admin
        if ($oldStatus !== $request->status) {
            $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
            $notificationService->notifyOrderStatusChange($order, $oldStatus, $request->status);
        }

        return redirect()->back()->with('success', 'Order and payment status updated successfully!');
    }

    // Update tracking information
    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_status' => 'nullable|string|max:255',
            'confirm_delivery' => 'nullable|boolean',
            'courier_name' => 'nullable|string|max:255',
            'courier_contact' => 'nullable|string|max:255',
            'courier_tracking_url' => 'nullable|url|max:500',
            'estimated_delivery_date' => 'nullable|date',
            'tracking_notes' => 'nullable|string|max:1000',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $isDeliveredTrackingStatus = strcasecmp((string) $request->tracking_status, 'Delivered') === 0;
        if ($isDeliveredTrackingStatus && !$request->boolean('confirm_delivery')) {
            return redirect()->back()->with('error', 'Please confirm delivery before setting tracking status to Delivered.');
        }

        // Update tracking fields
        if ($request->filled('tracking_status')) {
            $order->tracking_status = $request->tracking_status;
            
            // Add to tracking history
            $history = $order->tracking_history ?? [];
            
            // Decode JSON if it's a string
            if (is_string($history)) {
                $history = json_decode($history, true) ?? [];
            }
            
            // Ensure it's an array
            if (!is_array($history)) {
                $history = [];
            }
            
            array_unshift($history, [
                'status' => $request->tracking_status,
                'date' => now()->format('M d, Y h:i A'),
                'note' => $request->tracking_notes
            ]);
            $order->tracking_history = json_encode($history);
        }

        $order->courier_name = $request->courier_name;
        $order->courier_contact = $request->courier_contact;
        $order->courier_tracking_url = $request->courier_tracking_url;
        $order->estimated_delivery_date = $request->estimated_delivery_date;
        $order->tracking_notes = $request->tracking_notes;
        $order->delivery_address = $request->delivery_address;
        $order->delivery_latitude = $request->delivery_latitude;
        $order->delivery_longitude = $request->delivery_longitude;

        // If status is delivered, set delivered_at
        if ($isDeliveredTrackingStatus) {
            if (!$order->delivered_at) {
                $order->delivered_at = now();
            }

            if ($order->status !== 'completed') {
                $order->status = 'delivered';
            }
        }

        $order->save();

        return redirect()->back()->with('success', 'Tracking information updated successfully!');
    }

    // Quick update order status
    public function quickUpdateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'confirm_delivery' => 'nullable|boolean',
        ]);

        if ($request->status === 'delivered' && !$request->boolean('confirm_delivery')) {
            return redirect()->back()->with('error', 'Please confirm delivery before marking this order as delivered.');
        }

        if (
            $request->status === 'cancelled'
            && in_array(strtolower((string) $order->status), ['delivered', 'completed', 'refunded'], true)
        ) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        $order->status = $request->status;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($request->status === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'cancelled') {
            $order->payment_status = 'failed';
        }

        if ($request->status === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        // sync tracking status and history
        $order->tracking_status = ucfirst($request->status);
        $order->appendTrackingEvent(ucfirst($request->status));
        $order->save();

        return redirect()->back()->with('success', 'Order status updated!');
    }

    // Update admin notes
    public function updateNotes(Request $request, Order $order)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order->admin_notes = $request->admin_notes;
        $order->save();

        return redirect()->back()->with('success', 'Admin notes updated successfully!');
    }

    /**
     * Mark the remaining balance of a downpayment order as settled.
     */
    public function settleRemainingBalance(Order $order)
    {
        if (!Schema::hasColumn('orders', 'payment_option') || !Schema::hasColumn('orders', 'remaining_balance')) {
            return redirect()->back()->with('error', 'Downpayment fields are not available in this deployment yet.');
        }

        $paymentOption = strtolower((string) ($order->payment_option ?? 'full'));
        if ($paymentOption !== 'downpayment') {
            return redirect()->back()->with('error', 'Only downpayment orders can settle a remaining balance.');
        }

        $remainingBalance = (float) ($order->remaining_balance ?? 0);
        if ($remainingBalance <= 0) {
            return redirect()->back()->with('info', 'This order is already fully paid.');
        }

        if (!in_array($order->payment_status, ['paid', 'verified'], true)) {
            return redirect()->back()->with('error', 'Collect and verify the downpayment before settling the remaining balance.');
        }

        if (in_array(strtolower((string) $order->status), ['cancelled', 'refunded'], true)) {
            return redirect()->back()->with('error', 'Cancelled or refunded orders cannot be settled.');
        }

        $order->remaining_balance = 0;
        $order->payment_verified_at = now();
        $order->appendTrackingEvent('Remaining Balance Settled');
        $order->save();

        return redirect()->back()->with('success', 'Remaining balance marked as settled. This order is now fully paid.');
    }

    // Refund order
    public function refund(Request $request, Order $order)
    {
        if (!in_array($order->status, ['completed', 'delivered'])) {
            return redirect()->back()->with('error', 'Only completed or delivered orders can be refunded.');
        }

        $this->ensureRefundedPaymentStatusSupported();

        $order->status = 'refunded';
        $order->payment_status = 'refunded';
        $order->save();

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $product->stock += $item->quantity;
            $product->save();
        }

        return redirect()->back()->with('success', 'Order refunded successfully.');
    }

    /**
     * Review refund request and move it through workflow states.
     */
    public function approveRefundRequest(Request $request, OrderRefundRequest $refundRequest)
    {
        $this->ensureRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'admin_decision' => 'nullable|in:recommended,FULL_REFUND,PARTIAL_REFUND,RETURN_REQUIRED,REJECT',
            'admin_note' => 'nullable|string|max:2000',
            'approved_amount' => 'nullable|numeric|min:0',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['pending_review', 'under_review', 'requested'], true)) {
            return redirect()->back()->with('error', 'This refund request is not in a reviewable state.');
        }

        $order = $refundRequest->order;
        if (!$order) {
            return redirect()->back()->with('error', 'Associated order was not found.');
        }

        $selectedDecision = strtoupper((string) ($validated['admin_decision'] ?? 'recommended'));
        if ($selectedDecision === 'RECOMMENDED') {
            $selectedDecision = strtoupper((string) ($refundRequest->recommended_decision ?? 'REJECT'));
        }
        if (!in_array($selectedDecision, ['FULL_REFUND', 'PARTIAL_REFUND', 'RETURN_REQUIRED', 'REJECT'], true)) {
            $selectedDecision = 'REJECT';
        }

        $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
        $recommendedAmount = (float) ($refundRequest->recommended_refund_amount ?? 0);
        $approvedAmount = array_key_exists('approved_amount', $validated) && $validated['approved_amount'] !== null
            ? (float) $validated['approved_amount']
            : ($recommendedAmount > 0 ? $recommendedAmount : $orderTotal);
        $approvedAmount = max(0, min($approvedAmount, $orderTotal));

        $refundRequest->status = $selectedDecision === 'REJECT' ? 'rejected' : 'approved';
        $refundRequest->workflow_status = match ($selectedDecision) {
            'REJECT' => 'rejected',
            'RETURN_REQUIRED' => 'awaiting_return_shipment',
            default => 'pending_payout',
        };
        $refundRequest->final_decision = $selectedDecision;
        $refundRequest->refund_amount = $selectedDecision === 'REJECT' ? 0 : $approvedAmount;
        $refundRequest->approved_amount = $selectedDecision === 'REJECT' ? 0 : $approvedAmount;
        $refundRequest->return_required = $selectedDecision === 'RETURN_REQUIRED';
        $refundRequest->payout_status = $selectedDecision === 'REJECT' ? 'not_applicable' : 'pending';
        $refundRequest->admin_note = $validated['admin_note'] ?? $refundRequest->admin_note;
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();
        $refundRequest->save();

        if ($selectedDecision === 'REJECT') {
            $this->notifyRefundDecision($refundRequest, 'rejected');
            return redirect()->back()->with('success', 'Refund request rejected.');
        }

        if ($selectedDecision === 'RETURN_REQUIRED') {
            return redirect()->back()->with('success', 'Refund review saved. Waiting for customer return shipment details.');
        }

        return redirect()->back()->with('success', 'Refund decision saved. Request is now pending payout processing.');
    }

    /**
     * Reject a user refund request.
     */
    public function rejectRefundRequest(Request $request, OrderRefundRequest $refundRequest)
    {
        $request->validate([
            'admin_note' => 'required|string|max:2000',
        ]);

        $request->merge(['admin_decision' => 'REJECT']);

        return $this->approveRefundRequest($request, $refundRequest);
    }

    /**
     * Confirm returned item receipt and move request to pending payout.
     */
    public function markRefundReturnReceived(Request $request, OrderRefundRequest $refundRequest)
    {
        $this->ensureRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['awaiting_return_shipment', 'return_in_transit'], true)) {
            return redirect()->back()->with('error', 'Return can only be confirmed after a return shipment is initiated.');
        }

        $refundRequest->workflow_status = 'pending_payout';
        $refundRequest->status = 'approved';
        $refundRequest->return_received_at = now();
        $refundRequest->payout_status = 'pending';
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $existing = trim((string) ($refundRequest->admin_note ?? ''));
            $suffix = trim((string) $validated['admin_note']);
            $refundRequest->admin_note = $existing !== '' ? ($existing . "\n" . $suffix) : $suffix;
        }

        $refundRequest->save();

        return redirect()->back()->with('success', 'Return marked as received. Refund is now pending payout.');
    }

    /**
     * Execute payout and finalize refund request + order status.
     */
    public function executeRefundPayout(Request $request, OrderRefundRequest $refundRequest)
    {
        $this->ensureRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'refund_channel' => 'required|string|max:40',
            'refund_reference' => 'required|string|max:120',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['pending_payout', 'return_received', 'approved'], true)) {
            return redirect()->back()->with('error', 'Refund is not yet ready for payout processing.');
        }

        $order = $refundRequest->order;
        if (!$order) {
            return redirect()->back()->with('error', 'Associated order was not found.');
        }

        $this->ensureRefundedPaymentStatusSupported();

        $refundRequest->refund_channel = trim((string) $validated['refund_channel']);
        $refundRequest->refund_reference = trim((string) $validated['refund_reference']);
        $refundRequest->payout_status = 'completed';
        $refundRequest->workflow_status = 'processed';
        $refundRequest->status = 'processed';
        $refundRequest->processed_at = now();
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $refundRequest->admin_note = $validated['admin_note'];
        }

        if (empty($refundRequest->final_decision)) {
            $refundRequest->final_decision = strtoupper((string) ($refundRequest->recommended_decision ?? 'FULL_REFUND'));
        }
        if ((float) ($refundRequest->refund_amount ?? 0) <= 0) {
            $fallbackAmount = (float) ($refundRequest->approved_amount ?? $refundRequest->recommended_refund_amount ?? 0);
            $refundRequest->refund_amount = max(0, $fallbackAmount);
        }

        $refundRequest->save();

        $order->status = 'refunded';
        $order->payment_status = 'refunded';
        $order->appendTrackingEvent('Refunded');
        $order->save();

        $this->notifyRefundDecision($refundRequest, 'approved');

        return redirect()->back()->with('success', 'Refund payout recorded. Order is now marked as refunded.');
    }

    /**
     * Serve refund evidence files for admin preview.
     */
    public function viewRefundEvidence(OrderRefundRequest $refundRequest, int $index)
    {
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
     * Make sure orders.payment_status ENUM includes `refunded` on older deployments.
     */
    private function ensureRefundedPaymentStatusSupported(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment_status')) {
            return;
        }

        try {
            \DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending','paid','verified','failed','refunded') NOT NULL DEFAULT 'pending'");
        } catch (\Throwable $e) {
            Log::warning('Unable to ensure payment_status supports refunded', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ensure refund workflow columns exist for deployments with lagging migrations.
     */
    private function ensureRefundWorkflowColumnsExist(): void
    {
        if (!Schema::hasTable('order_refund_requests')) {
            return;
        }

        try {
            Schema::table('order_refund_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $table->string('workflow_status', 60)->nullable()->after('status');
                }
                if (!Schema::hasColumn('order_refund_requests', 'final_decision')) {
                    $table->string('final_decision', 40)->nullable()->after('workflow_status');
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
            Log::warning('Unable to ensure admin refund workflow columns exist', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // Cancel order
    public function cancel(Order $order)
    {
        $currentStatus = strtolower((string) $order->status);

        if (in_array($currentStatus, ['delivered', 'completed', 'refunded'], true)) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        if ($currentStatus === 'cancelled') {
            return redirect()->back()->with('info', 'Order is already cancelled.');
        }

        $currentPaymentStatus = strtolower((string) $order->payment_status);
        $wasPaid = in_array($currentPaymentStatus, ['paid', 'verified'], true);

        $order->status = 'cancelled';
        $order->payment_status = $wasPaid ? $order->payment_status : 'failed';
        $order->cancelled_at = now();
        $order->tracking_status = 'Cancelled';
        $order->appendTrackingEvent('Cancelled');
        if ($wasPaid) {
            $order->admin_notes = trim((string) $order->admin_notes);
            $order->admin_notes = ($order->admin_notes !== '' ? $order->admin_notes . "\n" : '')
                . 'Order cancelled by admin. Payment received; refund must be processed separately.';
        }
        $order->save();

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        $this->notifyOrderCancellationByAdmin($order);

        return redirect()->back()->with('success', $wasPaid
            ? 'Order cancelled. Payment remains marked as paid until refund payout is processed.'
            : 'Order cancelled successfully.');
    }

    /**
     * Notify customer about refund request decision (approved/rejected).
     */
    private function notifyRefundDecision(OrderRefundRequest $refundRequest, string $decision): void
    {
        $order = $refundRequest->order;
        if (!$order) {
            return;
        }

        $recipient = $refundRequest->user?->email ?: $order->user?->email ?: $order->customer_email;
        if (!$recipient) {
            return;
        }

        $decisionLabel = strtolower($decision) === 'approved' ? 'Approved' : 'Rejected';
        $subject = 'Refund Request ' . $decisionLabel . ' - ' . ($order->order_ref ?? ('#' . $order->id));
        $intro = strtolower($decision) === 'approved'
            ? 'Your refund request has been approved and processed.'
            : 'Your refund request has been rejected.';

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $refundRequest->user?->name ?: ($order->customer_name ?: 'Customer'),
                    'introText' => $intro,
                    'orderRef' => $order->order_ref,
                    'orderId' => $order->id,
                    'requestType' => 'Refund Request',
                    'decision' => $decisionLabel,
                    'reason' => $refundRequest->reason,
                    'adminNote' => $refundRequest->admin_note,
                    'approvedAmount' => strtolower($decision) === 'approved' ? $refundRequest->approved_amount : null,
                    'extraMessage' => null,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send refund decision email', [
                'refund_request_id' => $refundRequest->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify customer when admin cancels an order.
     */
    private function notifyOrderCancellationByAdmin(Order $order): void
    {
        $recipient = $order->user?->email ?: $order->customer_email;
        if (!$recipient) {
            return;
        }

        $subject = 'Order Cancel Request Approved - ' . ($order->order_ref ?? ('#' . $order->id));

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $order->user?->name ?: ($order->customer_name ?: 'Customer'),
                    'introText' => 'Your order cancellation request has been approved by our team.',
                    'orderRef' => $order->order_ref,
                    'orderId' => $order->id,
                    'requestType' => 'Order Cancellation',
                    'decision' => 'Approved',
                    'reason' => null,
                    'adminNote' => $order->admin_notes,
                    'approvedAmount' => null,
                    'extraMessage' => 'Payment status: ' . strtoupper((string) $order->payment_status),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin cancellation email', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $users = \App\Models\User::all();
        $products = \App\Models\Product::where('status', 'active')->get();
        
        return view('admin.orders.create', compact('users', 'products'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = \App\Models\User::find($validated['user_id']);
        $totalAmount = 0;

        // Create the order
        $order = \App\Models\Order::create([
            'user_id' => $validated['user_id'],
            'total_amount' => 0, // Will be calculated below
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Add order items
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            
            // Check stock availability
            if ($product->stock < $item['quantity']) {
                return redirect()->back()
                    ->with('error', "Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}")
                    ->withInput();
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            // Create order item
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            // Update product stock
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Update order total
        $order->total_amount = $totalAmount;
        $order->save();

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order created successfully!');
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $users = \App\Models\User::all();
        $products = \App\Models\Product::where('status', 'active')->get();
        
        return view('admin.orders.edit', compact('order', 'users', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Only allow editing if order is still pending
        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending orders can be edited.');
        }

        // Restore original stock
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $product->stock += $item->quantity;
            $product->save();
        }

        // Delete existing order items
        $order->orderItems()->delete();

        $totalAmount = 0;

        // Add new order items
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            
            // Check stock availability
            if ($product->stock < $item['quantity']) {
                return redirect()->back()
                    ->with('error', "Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}")
                    ->withInput();
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            // Create order item
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            // Update product stock
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Update order
        $order->update([
            'user_id' => $validated['user_id'],
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order updated successfully!');
    }

    // Optional: Place order (depends on your cart logic)
    public function placeOrder(Request $request)
    {
        // Implement your order placing logic here
    }

    // Generate invoice for an order
    public function generateInvoice(Order $order)
    {
        $order->load(['user', 'orderItems.product']);

        return view('admin.orders.invoice', compact('order'));
    }
}