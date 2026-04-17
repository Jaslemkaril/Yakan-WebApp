<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private function supportsDownpaymentFields(): bool
    {
        return Schema::hasColumn('orders', 'payment_option')
            && Schema::hasColumn('orders', 'downpayment_rate')
            && Schema::hasColumn('orders', 'downpayment_amount')
            && Schema::hasColumn('orders', 'remaining_balance');
    }

    private function resolveDownpaymentPlan(float $orderTotal, string $paymentOption, ?float $requestedRate = null): array
    {
        $normalizedOption = strtolower($paymentOption) === 'downpayment' ? 'downpayment' : 'full';
        $rate = $normalizedOption === 'downpayment'
            ? min(99, max(1, (float) ($requestedRate ?? 50)))
            : 100.0;

        $orderTotal = max(0, round($orderTotal, 2));
        $downpaymentAmount = round($orderTotal * ($rate / 100), 2);
        $remainingBalance = max(0, round($orderTotal - $downpaymentAmount, 2));

        return [
            'payment_option' => $normalizedOption,
            'downpayment_rate' => $rate,
            'downpayment_amount' => $downpaymentAmount,
            'remaining_balance' => $remainingBalance,
        ];
    }

    private function getActiveVariants(Product $product)
    {
        if ($product->relationLoaded('variants')) {
            return $product->variants->where('is_active', true)->values();
        }

        return $product->variants()->where('is_active', true)->get();
    }

    private function getEffectiveStock(Product $product, ?ProductVariant $variant = null): int
    {
        if ($variant) {
            return (int) $variant->stock;
        }

        return (int) ($product->inventory?->quantity ?? $product->stock ?? 0);
    }

    private function getEffectivePrice(Product $product, ?ProductVariant $variant = null): float
    {
        $basePrice = $variant
            ? (float) $variant->price
            : (float) $product->price;

        return (float) $product->getDiscountedPrice($basePrice);
    }

    public function store(Request $request)
    {
        try {
            // Get authenticated user (required now)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to create an order'
                ], 401);
            }

            $validated = $request->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'required|string|max:20',
                'shipping_address' => 'required|string',
                'delivery_address' => 'required|string',
                'shipping_city' => 'nullable|string|max:100',
                'shipping_province' => 'nullable|string|max:100',
                'shipping_zip' => 'nullable|string|max:20',
                'shipping_barangay' => 'nullable|string|max:150',
                'shipping_street' => 'nullable|string|max:255',
                'payment_method' => 'required|string|in:gcash,maya,bank_transfer,cash,online_banking,paymongo',
                'payment_option' => 'nullable|string|in:full,downpayment',
                'downpayment_rate' => 'nullable|numeric|min:1|max:99',
                'downpayment_amount' => 'nullable|numeric|min:0',
                'remaining_balance' => 'nullable|numeric|min:0',
                'payment_status' => 'nullable|string|in:pending,paid,verified,failed',
                'payment_reference' => 'nullable|string',
                'subtotal' => 'required|numeric|min:0',
                'shipping_fee' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
                'delivery_type' => 'nullable|string|in:pickup,deliver',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'gcash_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'bank_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            // Handle receipt file uploads
            $gcashReceiptPath = null;
            $bankReceiptPath = null;

            if ($request->hasFile('gcash_receipt')) {
                $gcashReceiptPath = $request->file('gcash_receipt')->store('receipts', 'public');
            }

            if ($request->hasFile('bank_receipt')) {
                $bankReceiptPath = $request->file('bank_receipt')->store('receipts', 'public');
            }

            $isPrepaid = in_array($validated['payment_method'], ['gcash', 'bank_transfer']);
            $autoMarkPaid = $isPrepaid && strtolower((string) ($validated['payment_option'] ?? 'full')) !== 'downpayment';
            $paymentStatus = ($validated['payment_status'] ?? null) === 'paid'
                ? 'paid'
                : ($autoMarkPaid ? 'paid' : ($validated['payment_status'] ?? 'pending'));
            $paymentOption = $validated['payment_option'] ?? 'full';
            $requestedDownpaymentRate = isset($validated['downpayment_rate'])
                ? (float) $validated['downpayment_rate']
                : null;

            // Normalise payment_method: 'online_banking' → 'maya'
            $dbPaymentMethod = $validated['payment_method'] === 'online_banking' ? 'maya' : $validated['payment_method'];

            // status ENUM: pending_confirmation, confirmed, processing, shipped, delivered, cancelled, refunded
            $orderStatus = $paymentStatus === 'paid' ? 'processing' : 'pending_confirmation';

            $orderRef = 'ORD-' . strtoupper(Str::random(12));

            // Use authenticated user's info (always available now)
            $customerName = $validated['customer_name'] ?? $user->name;
            $customerEmail = $validated['customer_email'] ?? $user->email;

            \DB::beginTransaction();

            $orderData = [
                'order_ref' => $orderRef,
                'tracking_number' => $orderRef,
                'user_id' => $user->id,  // Always link to authenticated user
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'delivery_address' => $validated['delivery_address'],
                'shipping_city' => $validated['shipping_city'] ?? null,
                'shipping_province' => $validated['shipping_province'] ?? null,
                'payment_method' => $dbPaymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $validated['payment_reference'] ?? null,
                // Totals are recalculated server-side after item prices are resolved
                'subtotal' => 0,
                'shipping_fee' => $validated['shipping_fee'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'total_amount' => 0,
                'delivery_type' => $validated['delivery_type'] ?? 'deliver',
                'status' => $orderStatus,
                'notes' => $validated['notes'] ?? null,
                'source' => 'mobile',
                'gcash_receipt' => $gcashReceiptPath,
                'bank_receipt' => $bankReceiptPath,
            ];

            if ($this->supportsDownpaymentFields()) {
                $initialPlan = $this->resolveDownpaymentPlan(0, $paymentOption, $requestedDownpaymentRate);
                $orderData = array_merge($orderData, $initialPlan);
            }

            if (Schema::hasColumn('orders', 'total')) {
                $orderData['total'] = 0;
            }

            $order = Order::create($orderData);

            // Resolve server-side prices and stock — never trust client data
            $serverSubtotal = 0;
            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $product = Product::with(['inventory', 'variants'])
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                $activeVariants = $this->getActiveVariants($product);
                $variant = null;
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::where('id', $item['variant_id'])
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'items' => ['One or more selected product variants are no longer available.'],
                        ]);
                    }
                }

                if ($activeVariants->isNotEmpty() && !$variant) {
                    throw ValidationException::withMessages([
                        'items' => ["Please select a variant for {$product->name}."],
                    ]);
                }

                $availableStock = $this->getEffectiveStock($product, $variant);
                if ($quantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for {$product->name}. Requested {$quantity}, available {$availableStock}."],
                    ]);
                }

                $unitPrice = $this->getEffectivePrice($product, $variant);
                $serverSubtotal += $unitPrice * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'variant_size' => $variant?->size,
                    'variant_color' => $variant?->color,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                ]);

                if ($variant) {
                    $variant->decrement('stock', $quantity);
                } elseif ($product->inventory) {
                    $product->inventory->decrement('quantity', $quantity);
                }

                // Keep aggregate product stock in sync for legacy list views.
                $product->decrement('stock', $quantity);
            }

            $shippingFee = $validated['shipping_fee'] ?? 0;
            $discount    = $validated['discount'] ?? 0;
            $orderTotal = max(0, $serverSubtotal + $shippingFee - $discount);

            $deliveryTypeForPlan = strtolower((string) ($validated['delivery_type'] ?? 'deliver'));
            $paymentOptionForPlan = strtolower((string) $paymentOption) === 'downpayment' ? 'downpayment' : 'full';
            if ($deliveryTypeForPlan !== 'pickup') {
                $paymentOptionForPlan = 'full';
            }

            $resolvedPlan = $this->resolveDownpaymentPlan($orderTotal, $paymentOptionForPlan, $requestedDownpaymentRate);

            // Persist a portable payment-plan snapshot in notes so downstream
            // checkout (e.g. PayMongo API path) can still resolve payable amount
            // even when DB downpayment columns are unavailable in some environments.
            $existingNotes = (string) ($order->notes ?? '');
            $existingNotes = preg_replace('/\s*\[MOBILE_PAYMENT_PLAN\]\s*\{.*?\}\s*/s', "\n", $existingNotes) ?? $existingNotes;
            $existingNotes = trim($existingNotes);
            $paymentPlanSnapshot = [
                'payment_option' => $resolvedPlan['payment_option'],
                'downpayment_rate' => $resolvedPlan['downpayment_rate'],
                'downpayment_amount' => $resolvedPlan['downpayment_amount'],
                'remaining_balance' => $resolvedPlan['remaining_balance'],
                'total_amount' => $orderTotal,
                'delivery_type' => $deliveryTypeForPlan,
            ];
            $notesWithPlan = trim($existingNotes . "\n" . '[MOBILE_PAYMENT_PLAN]' . json_encode($paymentPlanSnapshot));

            $orderUpdateData = [
                'subtotal'     => $serverSubtotal,
                'total_amount' => $orderTotal,
                'notes' => $notesWithPlan,
            ];

            if (Schema::hasColumn('orders', 'total')) {
                $orderUpdateData['total'] = $orderTotal;
            }

            if ($this->supportsDownpaymentFields()) {
                $orderUpdateData = array_merge($orderUpdateData, $resolvedPlan);
            }

            $order->update($orderUpdateData);

            \DB::commit();

            // Keep mobile behavior aligned with website checkout by sending
            // an order confirmation email immediately after successful creation.
            try {
                $order->loadMissing('user', 'items.product', 'items.variant');
                $orderEmail = trim((string) (optional($order->user)->email ?: $order->customer_email));

                if ($orderEmail !== '') {
                    TransactionalMailService::sendView(
                        $orderEmail,
                        'Order Confirmation - ' . ($order->order_ref ?? ('ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT))),
                        'emails.order-confirmation',
                        ['order' => $order]
                    );
                }
            } catch (\Throwable $mailException) {
                \Log::warning('Mobile API order confirmation email failed', [
                    'order_id' => $order->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => tap($order->load('items'), function (Order $savedOrder): void {
                    $savedOrder->setAttribute('amount_due_now', $savedOrder->getAmountDueNow());
                }),
                'message' => 'Order created successfully'
            ], 201);
        } catch (ValidationException $e) {
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            \Log::error('Order store error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Order could not be created. Please try again.'
            ], 500);
        }
    }

    public function index()
    {
        $user = request()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $orders = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }, 'items.variant:id,size,color'])->where('user_id', $user->id)->latest()->get();

        $orders->each(function (Order $order): void {
            $order->setAttribute('amount_due_now', $order->getAmountDueNow());
        });
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders fetched successfully'
        ]);
    }

    public function show($id)
    {
        $user = request()->user();

        $order = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }, 'items.variant:id,size,color'])->where('id', $id)->where('user_id', $user?->id)->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->setAttribute('amount_due_now', $order->getAmountDueNow());

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order fetched successfully'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,completed,cancellation_requested,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }

    public function uploadReceipt(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $request->validate([
            'gcash_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'bank_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $updateData = [];
        $cloudinary = new \App\Services\CloudinaryService();

        if ($request->hasFile('gcash_receipt')) {
            $file = $request->file('gcash_receipt');
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($file, 'receipts');
                $updateData['gcash_receipt'] = $result ? $result['url'] : $file->store('receipts', 'public');
            } else {
                $updateData['gcash_receipt'] = $file->store('receipts', 'public');
            }
        }

        if ($request->hasFile('bank_receipt')) {
            $file = $request->file('bank_receipt');
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($file, 'receipts');
                $updateData['bank_receipt'] = $result ? $result['url'] : $file->store('receipts', 'public');
            } else {
                $updateData['bank_receipt'] = $file->store('receipts', 'public');
            }
        }

        if (!empty($updateData)) {
            // Update payment status to paid when receipt is uploaded
            $updateData['payment_status'] = 'paid';
            $updateData['status'] = 'processing';
            
            $order->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $order->fresh(),
                'message' => 'Receipt uploaded successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No receipt file provided'
        ], 400);
    }

    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            $order = Order::where('id', $id)->where('user_id', $user->id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $currentStatus = strtolower((string) $order->status);
            $cancellableStatuses = ['pending', 'pending_confirmation', 'confirmed', 'processing'];

            if ($currentStatus === 'cancellation_requested') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cancellation request is already pending admin review.',
                    'data' => $order->fresh(['items.product', 'user']),
                ]);
            }

            if (!in_array($currentStatus, $cancellableStatuses, true)) {
                $this->notifyCancelDecision(
                    $order,
                    'rejected',
                    $validated['reason'],
                    'This order can no longer be cancelled because it is already in ' . strtoupper((string) $order->status) . ' status.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'This order can no longer be cancelled.',
                ], 422);
            }

            $this->ensureCancellationRequestedStatusSupported();

            \DB::beginTransaction();

            $currentPaymentStatus = strtolower((string) $order->payment_status);
            $wasPaid = in_array($currentPaymentStatus, ['paid', 'verified'], true);

            $order->update([
                'status' => 'cancellation_requested',
                'payment_status' => $order->payment_status,
                'cancelled_at' => null,
                'admin_notes' => 'Customer cancellation requested: ' . $validated['reason'],
            ]);
            $order->appendTrackingEvent('Cancellation Requested');
            $order->save();

            \DB::commit();

            $this->notifyCancelDecision(
                $order,
                'pending',
                $validated['reason'],
                $wasPaid
                    ? 'Payment was received. Cancellation and refund processing will start after admin approval.'
                    : 'Cancellation request submitted and awaiting admin approval.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Cancellation request submitted. Awaiting admin approval.',
                'data' => $order->fresh(['items.product', 'user']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Order cancel error', ['message' => $e->getMessage(), 'order_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order. Please try again.',
            ], 500);
        }
    }

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
        } catch (\Throwable $exception) {
            \Log::warning('Failed to send API cancellation decision email', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureCancellationRequestedStatusSupported(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        try {
            \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','pending_confirmation','confirmed','processing','shipped','delivered','completed','cancellation_requested','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");
        } catch (\Throwable $exception) {
            \Log::warning('Unable to ensure API order status supports cancellation_requested', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function adminIndex()
    {
        $orders = Order::with('items', 'user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Admin orders fetched successfully'
        ]);
    }
}
