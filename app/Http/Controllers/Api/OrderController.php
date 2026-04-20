<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

    private function hasRefundRequestsTable(): bool
    {
        return Schema::hasTable('order_refund_requests');
    }

    private function activeRefundStatuses(): array
    {
        return ['requested', 'under_review', 'approved'];
    }

    private function activeRefundWorkflowStatuses(): array
    {
        return [
            'pending_review',
            'under_review',
            'awaiting_return_shipment',
            'return_in_transit',
            'return_received',
            'pending_payout',
            'approved',
        ];
    }

    private function hasActiveRefundRequest(Order $order, int $userId): bool
    {
        if (!$this->hasRefundRequestsTable()) {
            return false;
        }

        return OrderRefundRequest::where('order_id', $order->id)
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereIn('status', $this->activeRefundStatuses());

                if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $query->orWhereIn('workflow_status', $this->activeRefundWorkflowStatuses());
                }
            })
            ->exists();
    }

    private function resolveEvidencePublicUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $cleanPath = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $path), '/');

        try {
            $storageUrl = Storage::disk('public')->url($cleanPath);
        } catch (\Throwable $exception) {
            $storageUrl = '/storage/' . $cleanPath;
        }

        if (Str::startsWith($storageUrl, ['http://', 'https://'])) {
            return $storageUrl;
        }

        return url($storageUrl);
    }

    private function buildRefundEvidenceSummary($rawEvidence): array
    {
        $evidencePaths = array_values(array_filter(is_array($rawEvidence) ? $rawEvidence : [], fn ($value) => $value !== null && trim((string) $value) !== ''));

        return array_values(array_map(function ($path) {
            $evidencePath = (string) $path;
            $parsedPath = (string) (parse_url($evidencePath, PHP_URL_PATH) ?? $evidencePath);
            $extension = strtolower(pathinfo($parsedPath, PATHINFO_EXTENSION));
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
            $isVideo = in_array($extension, ['mp4', 'mov', 'webm'], true);

            return [
                'path' => $evidencePath,
                'url' => $this->resolveEvidencePublicUrl($evidencePath),
                'filename' => basename($parsedPath),
                'extension' => $extension,
                'is_image' => $isImage,
                'is_video' => $isVideo,
            ];
        }, $evidencePaths));
    }

    private function buildRefundRequestSummary(?OrderRefundRequest $refundRequest): ?array
    {
        if (!$refundRequest) {
            return null;
        }

        $evidence = $this->buildRefundEvidenceSummary($refundRequest->evidence_paths);

        return [
            'id' => $refundRequest->id,
            'reason' => $refundRequest->reason,
            'refund_type' => $refundRequest->refund_type,
            'comment' => $refundRequest->comment,
            'details' => $refundRequest->details,
            'status' => $refundRequest->status,
            'workflow_status' => $refundRequest->workflow_status,
            'admin_note' => $refundRequest->admin_note,
            'final_decision' => $refundRequest->final_decision,
            'recommended_decision' => $refundRequest->recommended_decision,
            'recommended_refund_amount' => $refundRequest->recommended_refund_amount,
            'payout_status' => $refundRequest->payout_status,
            'refund_amount' => $refundRequest->refund_amount,
            'approved_amount' => $refundRequest->approved_amount,
            'return_required' => $refundRequest->return_required,
            'refund_reference' => $refundRequest->refund_reference,
            'evidence_count' => count($evidence),
            'evidence' => $evidence,
            'requested_at' => $refundRequest->requested_at?->toIso8601String(),
            'reviewed_at' => $refundRequest->reviewed_at?->toIso8601String(),
            'processed_at' => $refundRequest->processed_at?->toIso8601String(),
        ];
    }

    private function enrichOrderForMobileResponse(Order $order, int $userId): void
    {
        $order->setAttribute('amount_due_now', $order->getAmountDueNow());

        $canRequestRefund = $order->canRequestRefund() && ((int) $order->user_id === $userId);
        $order->setAttribute('can_request_refund', $canRequestRefund);
        $order->setAttribute('refund_warranty_days', $order->getRefundWarrantyDays());
        $order->setAttribute('refund_warranty_deadline', $order->getRefundWarrantyDeadline()?->toIso8601String());

        if (!$this->hasRefundRequestsTable()) {
            return;
        }

        $order->loadMissing('refundRequests');
        $latestRefundRequest = $order->refundRequests
            ->sortByDesc(fn ($refundRequest) => optional($refundRequest->requested_at ?? $refundRequest->created_at)->timestamp ?? 0)
            ->first();

        $order->setAttribute('latest_refund_request', $this->buildRefundRequestSummary($latestRefundRequest));

        if ($latestRefundRequest) {
            $refundStatus = strtolower((string) ($latestRefundRequest->status ?? ''));
            $workflowStatus = strtolower((string) ($latestRefundRequest->workflow_status ?? ''));

            $isActive = in_array($refundStatus, $this->activeRefundStatuses(), true)
                || in_array($workflowStatus, $this->activeRefundWorkflowStatuses(), true);

            $order->setAttribute('refund_request_in_progress', $isActive);
        }
    }

    private function diagLog(string $event, array $payload = [], ?int $userId = null): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('diagnostic_events')) {
                \Illuminate\Support\Facades\Schema::create('diagnostic_events', function ($table) {
                    $table->id();
                    $table->string('event', 100);
                    $table->text('payload')->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->timestamp('created_at')->useCurrent();
                    $table->index('event');
                    $table->index('created_at');
                });
            }
            \Illuminate\Support\Facades\DB::table('diagnostic_events')->insert([
                'event' => $event,
                'payload' => json_encode($payload),
                'user_id' => $userId,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore — diagnostics must never break the main flow
        }
        \Log::info($event, $payload);
    }

    /**
     * Decrement stock for bundle component products
     */
    private function decrementBundleComponentStock(Product $bundleProduct, int $bundleQuantity): void
    {
        $bundleProduct->loadMissing([
            'bundleItems.componentProduct.inventory',
            'bundleItems.componentProduct.variants',
        ]);

        foreach ($bundleProduct->bundleItems as $bundleItem) {
            $component = $bundleItem->componentProduct;
            if (!$component) {
                throw new \RuntimeException('A bundle component product is missing.');
            }

            $deductQty = max(1, (int) $bundleItem->quantity) * max(1, $bundleQuantity);
            $activeVariants = $component->relationLoaded('variants')
                ? $component->variants->where('is_active', true)->sortBy('id')->values()
                : $component->variants()->where('is_active', true)->orderBy('id')->get();

            if ($activeVariants->isNotEmpty()) {
                $remaining = $deductQty;

                foreach ($activeVariants as $componentVariant) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $variantStock = max(0, (int) $componentVariant->stock);
                    if ($variantStock <= 0) {
                        continue;
                    }

                    $deductFromVariant = min($variantStock, $remaining);
                    $componentVariant->decrement('stock', $deductFromVariant);
                    $remaining -= $deductFromVariant;
                }

                if ($remaining > 0) {
                    throw new \RuntimeException('Insufficient component variant stock for bundle order.');
                }
            }

            $componentInventory = \App\Models\Inventory::where('product_id', $component->id)->first();
            if ($componentInventory) {
                $componentInventory->decrement('quantity', $deductQty);
            }

            if ((int) $component->stock >= $deductQty) {
                $component->decrement('stock', $deductQty);
            }
        }
    }

    public function store(Request $request)
    {
        $userId = optional($request->user())->id;
        $this->diagLog('OrderStore.request_received', [
            'user_id' => $userId,
            'source' => $request->input('source'),
            'payment_method' => $request->input('payment_method'),
            'item_count' => is_array($request->input('items')) ? count($request->input('items')) : null,
            'client_subtotal' => $request->input('subtotal'),
            'client_total' => $request->input('total'),
            'client_total_amount' => $request->input('total_amount'),
            'client_shipping_fee' => $request->input('shipping_fee'),
            'notes_excerpt' => substr((string) $request->input('notes'), 0, 120),
            'items_raw' => $request->input('items'),
        ], $userId);

        try {
            // Get authenticated user (required now)
            $user = $request->user();
            
            if (!$user) {
                $this->diagLog('OrderStore.rejected_unauth', []);
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
                'downpayment_rate' => 'nullable|numeric|min:1|max:100',
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

                // Diagnostic log: trace how the server computed this item's price
                // so we can spot unexpected discount applications or variant price
                // mismatches between mobile display and server calculation.
                $rawBasePrice = $variant ? (float) $variant->price : (float) $product->price;
                \Log::info('[OrderStore] Item price resolved', [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price_column' => (float) $product->price,
                    'variant_id' => $variant?->id,
                    'variant_price_column' => $variant ? (float) $variant->price : null,
                    'base_price_used' => $rawBasePrice,
                    'has_active_discount' => $product->hasActiveProductDiscount(),
                    'discount_type' => $product->discount_type,
                    'discount_value' => $product->discount_value,
                    'discount_starts_at' => $product->discount_starts_at?->toDateTimeString(),
                    'discount_ends_at' => $product->discount_ends_at?->toDateTimeString(),
                    'effective_unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $unitPrice * $quantity,
                    'client_sent_price' => $item['price'] ?? null,
                ]);

                $serverSubtotal += $unitPrice * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'variant_size' => $variant?->size,
                    'variant_color' => $variant?->color,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                ]);

                // Check if product is a bundle
                $isBundle = \Illuminate\Support\Facades\Schema::hasTable('product_bundle_items')
                    && $product->bundleItems()->exists();

                if ($isBundle) {
                    // Deduct stock from bundle component products
                    $this->decrementBundleComponentStock($product, $quantity);
                } else {
                    // Deduct stock from regular product
                    if ($variant) {
                        $variant->decrement('stock', $quantity);
                    } elseif ($product->inventory) {
                        $product->inventory->decrement('quantity', $quantity);
                    }

                    // Keep aggregate product stock in sync for legacy list views.
                    $product->decrement('stock', $quantity);
                }
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

            $createdOrderPayload = tap($order->load('items'), function (Order $savedOrder): void {
                $savedOrder->setAttribute('amount_due_now', $savedOrder->getAmountDueNow());
            });

            $this->diagLog('OrderStore.success', [
                'order_id' => $createdOrderPayload->id,
                'order_ref' => $createdOrderPayload->order_ref,
                'total_amount' => (float) ($createdOrderPayload->total_amount ?? 0),
                'subtotal' => (float) ($createdOrderPayload->subtotal ?? 0),
                'item_count' => $createdOrderPayload->items->count(),
            ], $userId);

            // Backward-compatible top-level fields for older mobile clients
            // that read create-order response IDs from the first response level.
            return response()->json([
                'success' => true,
                'id' => $createdOrderPayload->id,
                'order_id' => $createdOrderPayload->id,
                'order_ref' => $createdOrderPayload->order_ref,
                'tracking_number' => $createdOrderPayload->tracking_number,
                'data' => $createdOrderPayload,
                'message' => 'Order created successfully'
            ], 201);
        } catch (ValidationException $e) {
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            $this->diagLog('OrderStore.validation_failed', [
                'errors' => $e->errors(),
            ], $userId);
            $flat = collect($e->errors())->flatten()->implode(' ');
            return response()->json([
                'success' => false,
                'message' => $flat ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\RuntimeException $e) {
            // Runtime exceptions (e.g. "Insufficient component variant stock for
            // bundle order." from decrementBundleComponentStock) should be
            // surfaced verbatim so the user knows what to do and so we can
            // diagnose production issues without server logs.
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            $this->diagLog('OrderStore.runtime_error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $userId);
            \Log::error('[OrderStore] Runtime error while creating order', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            $this->diagLog('OrderStore.unexpected_error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect(explode("\n", $e->getTraceAsString()))->take(6)->all(),
            ], $userId);
            \Log::error('[OrderStore] Unexpected error while creating order', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Order could not be created: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $user = request()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $relations = [
            'items.product' => function($query) {
                $query->select('id', 'name', 'image');
            },
            'items.variant:id,size,color',
        ];

        if ($this->hasRefundRequestsTable()) {
            $relations['refundRequests'] = function ($query) {
                $query->latest();
            };
        }

        $orders = Order::with($relations)
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $orders->each(function (Order $order) use ($user): void {
            $this->enrichOrderForMobileResponse($order, (int) $user->id);
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

        $relations = [
            'items.product' => function($query) {
                $query->select('id', 'name', 'image');
            },
            'items.variant:id,size,color',
        ];

        if ($this->hasRefundRequestsTable()) {
            $relations['refundRequests'] = function ($query) {
                $query->latest();
            };
        }

        $order = Order::with($relations)
            ->where('id', $id)
            ->where('user_id', $user?->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $this->enrichOrderForMobileResponse($order, (int) ($user?->id ?? 0));

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
                'reason' => 'nullable|string|max:255',
                'cancel_reason' => 'nullable|string|max:255',
                'cancel_reason_other' => 'nullable|string|max:255',
                'cancel_notes' => 'nullable|string|max:500',
            ]);

            $resolvedReason = trim((string) (
                $validated['reason']
                ?? $validated['cancel_reason']
                ?? ''
            ));

            if ($resolvedReason === '' && !empty($validated['cancel_reason_other'])) {
                $resolvedReason = trim((string) $validated['cancel_reason_other']);
            }

            if (strcasecmp($resolvedReason, 'Other') === 0) {
                $resolvedReason = trim((string) ($validated['cancel_reason_other'] ?? ''));
            }

            if ($resolvedReason === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancellation reason is required.',
                ], 422);
            }

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
                    $resolvedReason,
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

            $existingAdminNotes = trim((string) ($order->admin_notes ?? ''));
            $reasonLine = 'Customer cancellation requested: ' . $resolvedReason;
            if (!str_contains($existingAdminNotes, $reasonLine)) {
                $existingAdminNotes = $existingAdminNotes !== ''
                    ? ($existingAdminNotes . "\n" . $reasonLine)
                    : $reasonLine;
            }

            $cancelNotes = trim((string) ($validated['cancel_notes'] ?? ''));
            if ($cancelNotes !== '') {
                $noteLine = 'Customer cancellation note: ' . $cancelNotes;
                if (!str_contains($existingAdminNotes, $noteLine)) {
                    $existingAdminNotes = $existingAdminNotes !== ''
                        ? ($existingAdminNotes . "\n" . $noteLine)
                        : $noteLine;
                }
            }

            if ($wasPaid) {
                $this->ensureRefundRequestsTableExists();
                $this->ensureRefundWorkflowColumnsExist();

                if ($this->hasRefundRequestsTable() && !$this->hasActiveRefundRequest($order, (int) $user->id)) {
                    $totalAmount = (float) ($order->total_amount ?? $order->total ?? 0);

                    $refundPayload = [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'reason' => 'Order cancellation',
                        'details' => 'Auto-generated cancellation refund request. Reason: ' . $resolvedReason,
                        'status' => 'requested',
                        'requested_at' => now(),
                    ];

                    if (Schema::hasColumn('order_refund_requests', 'refund_type')) {
                        $refundPayload['refund_type'] = 'full';
                    }
                    if (Schema::hasColumn('order_refund_requests', 'comment')) {
                        $refundPayload['comment'] = 'Customer cancelled order before fulfillment: ' . $resolvedReason;
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
            }

            $order->update([
                'status' => 'cancellation_requested',
                'payment_status' => $order->payment_status,
                'cancelled_at' => null,
                'admin_notes' => trim($existingAdminNotes),
            ]);
            $order->appendTrackingEvent('Cancellation Requested');
            $order->save();

            \DB::commit();

            $this->notifyCancelDecision(
                $order,
                'pending',
                $resolvedReason,
                $wasPaid
                    ? 'Payment was received. Cancellation and refund processing will start after admin approval.'
                    : 'Cancellation request submitted and awaiting admin approval.'
            );

            $freshOrder = $order->fresh(['items.product', 'user']);
            $this->enrichOrderForMobileResponse($freshOrder, (int) $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Cancellation request submitted. Awaiting admin approval.',
                'data' => $freshOrder,
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

    public function requestRefund(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $this->ensureRefundRequestsTableExists();
            $this->ensureRefundWorkflowColumnsExist();

            if (!$this->hasRefundRequestsTable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund feature is not ready yet. Please try again shortly.',
                ], 503);
            }

            if (!$order->canRequestRefund()) {
                $status = strtolower((string) $order->status);
                $message = in_array($status, ['delivered', 'completed'], true)
                    ? 'Refund/return window has expired for this order.'
                    : 'You can request refund only after order is delivered/completed.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            if ($this->hasActiveRefundRequest($order, (int) $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A refund request for this order is already in progress.',
                ], 422);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:150',
                'refund_type' => 'nullable|in:full,partial,change_of_mind',
                'preferred_resolution' => 'nullable|in:refund,replacement,return_refund',
                'specific_reason' => 'nullable|string|max:120',
                'comment' => 'nullable|string|max:2000',
                'details' => 'nullable|string|max:2000',
                'evidence' => 'required|array|min:1|max:5',
                'evidence.*' => 'required|file|mimes:jpg,jpeg,png,webp,pdf,mp4,mov,webm|max:20480',
            ]);

            $baseComment = trim((string) ($validated['comment'] ?? $validated['details'] ?? ''));
            if ($baseComment === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a short explanation for your refund request.',
                ], 422);
            }

            if (strtolower((string) $validated['reason']) === 'damaged item' && !$this->hasVideoEvidence($request->file('evidence', []))) {
                return response()->json([
                    'success' => false,
                    'message' => 'For damaged items, please upload at least one opening/unboxing video as proof.',
                ], 422);
            }

            $specificReason = trim((string) ($validated['specific_reason'] ?? ''));
            $preferredResolution = trim((string) ($validated['preferred_resolution'] ?? ''));
            $comment = $baseComment;
            if ($specificReason !== '') {
                $comment = 'Specific reason: ' . $specificReason . "\n" . $baseComment;
            }
            if ($preferredResolution !== '') {
                $comment .= ($comment !== '' ? "\n" : '')
                    . 'Preferred resolution: ' . ucfirst(str_replace('_', ' ', $preferredResolution));
            }

            $refundType = $this->normalizeRefundType($validated['refund_type'] ?? null, $validated['reason']);

            $evidencePaths = [];
            if ($request->hasFile('evidence')) {
                $cloudinary = new \App\Services\CloudinaryService();
                foreach ($request->file('evidence', []) as $file) {
                    if (!$file) {
                        continue;
                    }

                    if ($cloudinary->isEnabled()) {
                        $tempPath = $file->getRealPath();
                        $result = $cloudinary->upload($tempPath, 'refunds/order-' . $order->id);
                        if ($result && !empty($result['url'])) {
                            $evidencePaths[] = $result['url'];
                            \Log::info('Mobile refund evidence uploaded to Cloudinary', [
                                'order' => $order->id,
                                'url' => $result['url'],
                            ]);
                            continue;
                        }

                        \Log::error('Mobile refund evidence Cloudinary upload failed, falling back to local disk', [
                            'order' => $order->id,
                            'file' => $file->getClientOriginalName(),
                        ]);
                    } else {
                        \Log::warning('Cloudinary not enabled for mobile refund evidence, using local disk', [
                            'order' => $order->id,
                        ]);
                    }

                    $evidencePaths[] = $file->store('refunds/order-' . $order->id, 'public');
                }
            }

            \Log::info('Mobile refund evidence paths saved', [
                'order' => $order->id,
                'count' => count($evidencePaths),
                'paths' => $evidencePaths,
            ]);

            $validationSnapshot = $this->buildRefundValidationSnapshot($order, (int) $user->id);
            $recommendation = $this->buildRefundRecommendation(
                $order,
                $refundType,
                (string) $validated['reason'],
                $comment,
                $validationSnapshot
            );

            \DB::beginTransaction();

            $refundPayload = [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? $comment,
                'evidence_paths' => $evidencePaths,
                'status' => 'requested',
                'requested_at' => now(),
            ];

            if (Schema::hasColumn('order_refund_requests', 'refund_type')) {
                $refundPayload['refund_type'] = $refundType;
            }
            if (Schema::hasColumn('order_refund_requests', 'comment')) {
                $refundPayload['comment'] = $comment;
            }
            if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                $refundPayload['workflow_status'] = 'pending_review';
            }
            if (Schema::hasColumn('order_refund_requests', 'system_validation')) {
                $refundPayload['system_validation'] = $validationSnapshot['checks'] ?? [];
            }
            if (Schema::hasColumn('order_refund_requests', 'fraud_flags')) {
                $refundPayload['fraud_flags'] = $validationSnapshot['fraud']['flags'] ?? [];
            }
            if (Schema::hasColumn('order_refund_requests', 'fraud_risk_level')) {
                $refundPayload['fraud_risk_level'] = $validationSnapshot['fraud']['risk_level'] ?? 'low';
            }
            if (Schema::hasColumn('order_refund_requests', 'recommended_decision')) {
                $refundPayload['recommended_decision'] = $recommendation['decision'] ?? 'REJECT';
            }
            if (Schema::hasColumn('order_refund_requests', 'recommended_refund_amount')) {
                $refundPayload['recommended_refund_amount'] = $recommendation['refund_amount'] ?? 0;
            }
            if (Schema::hasColumn('order_refund_requests', 'return_required')) {
                $refundPayload['return_required'] = (bool) ($recommendation['return_required'] ?? false);
            }
            if (Schema::hasColumn('order_refund_requests', 'payout_status')) {
                $refundPayload['payout_status'] = 'pending';
            }

            $refundRequest = OrderRefundRequest::create($refundPayload);

            $order->appendTrackingEvent('Refund Requested');
            $order->save();

            \DB::commit();

            $this->notifyRefundRequestSubmitted($order, $refundRequest);

            $freshOrder = $order->fresh(['items.product', 'user', 'refundRequests']);
            $this->enrichOrderForMobileResponse($freshOrder, (int) $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Refund request submitted successfully. Awaiting admin review.',
                'data' => [
                    'order' => $freshOrder,
                    'refund_request' => $this->buildRefundRequestSummary($refundRequest),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }

            \Log::error('Order refund request error', [
                'order_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit refund request. Please try again.',
            ], 500);
        }
    }

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
        } catch (\Throwable $exception) {
            \Log::warning('Unable to auto-create order_refund_requests table (API)', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

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
        } catch (\Throwable $exception) {
            \Log::warning('Unable to ensure refund workflow columns exist (API)', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function hasVideoEvidence(array $evidenceFiles): bool
    {
        foreach ($evidenceFiles as $file) {
            if (!$file) {
                continue;
            }

            $mimeType = strtolower((string) $file->getMimeType());
            $extension = strtolower((string) $file->getClientOriginalExtension());

            if (Str::startsWith($mimeType, 'video/') || in_array($extension, ['mp4', 'mov', 'webm'], true)) {
                return true;
            }
        }

        return false;
    }

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

    private function buildRefundValidationSnapshot(Order $order, int $userId): array
    {
        $status = strtolower((string) $order->status);
        $paymentStatus = strtolower((string) $order->payment_status);

        $statusAllowed = in_array($status, ['delivered', 'completed'], true);
        $paymentAllowed = in_array($paymentStatus, ['paid', 'verified', 'completed'], true);
        $withinWindow = $order->isRefundWithinWarranty();
        $fraud = $this->evaluateRefundFraudHistory($userId);

        return [
            'is_valid' => $statusAllowed && $paymentAllowed && $withinWindow,
            'checks' => [
                'order_status_ok' => $statusAllowed,
                'payment_status_ok' => $paymentAllowed,
                'refund_window_ok' => $withinWindow,
                'fraud_check_ok' => ($fraud['risk_level'] ?? 'low') !== 'high',
            ],
            'fraud' => $fraud,
        ];
    }

    private function evaluateRefundFraudHistory(int $userId): array
    {
        if (!$this->hasRefundRequestsTable()) {
            return [
                'risk_level' => 'low',
                'flags' => [],
                'recent_request_count' => 0,
                'rejected_count' => 0,
                'open_request_count' => 0,
            ];
        }

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
                    $builder->orWhereIn('workflow_status', ['pending_review', 'under_review', 'awaiting_return_shipment', 'return_in_transit', 'pending_payout', 'approved']);
                }
            })
            ->count();

        $flags = [];
        if ($recentRequestCount >= 3) {
            $flags[] = 'high_recent_refund_volume';
        }
        if ($rejectedCount >= 2) {
            $flags[] = 'multiple_rejected_refunds';
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

    private function buildRefundRecommendation(Order $order, string $refundType, string $reason, string $comment, array $validation): array
    {
        $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
        $reasonNormalized = strtolower(trim($reason));

        if (empty($validation['is_valid'])) {
            return [
                'decision' => 'REJECT',
                'refund_amount' => 0,
                'return_required' => false,
            ];
        }

        if (($validation['fraud']['risk_level'] ?? 'low') === 'high') {
            return [
                'decision' => 'REJECT',
                'refund_amount' => 0,
                'return_required' => false,
            ];
        }

        if ($refundType === 'change_of_mind' || $reasonNormalized === 'changed my mind') {
            $restockingFeeRate = str_contains(strtolower($comment), 'opened') ? 0.20 : 0.10;
            $refundAmount = max(0, round($orderTotal * (1 - $restockingFeeRate), 2));

            return [
                'decision' => 'RETURN_REQUIRED',
                'refund_amount' => $refundAmount,
                'return_required' => true,
            ];
        }

        if ($refundType === 'partial') {
            return [
                'decision' => 'PARTIAL_REFUND',
                'refund_amount' => max(0, round($orderTotal * 0.50, 2)),
                'return_required' => false,
            ];
        }

        $fullRefundReasons = [
            'item not as described',
            'item not received',
            'damaged item',
            'wrong item received',
            'incomplete order',
        ];

        if (in_array($reasonNormalized, $fullRefundReasons, true)) {
            return [
                'decision' => 'FULL_REFUND',
                'refund_amount' => max(0, round($orderTotal, 2)),
                'return_required' => false,
            ];
        }

        return [
            'decision' => 'PARTIAL_REFUND',
            'refund_amount' => max(0, round($orderTotal * 0.30, 2)),
            'return_required' => false,
        ];
    }

    private function notifyRefundRequestSubmitted(Order $order, OrderRefundRequest $refundRequest): void
    {
        $recipient = $order->user?->email ?: $order->customer_email;
        if (!$recipient) {
            return;
        }

        $subject = 'Refund Request Submitted - ' . ($order->order_ref ?? ('#' . $order->id));
        $reason = trim((string) ($refundRequest->reason ?? 'Refund request'));

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $order->user?->name ?: ($order->customer_name ?: 'Customer'),
                    'introText' => 'Your refund request has been submitted and is now pending review.',
                    'orderRef' => $order->order_ref,
                    'orderId' => $order->id,
                    'requestType' => 'Refund Request',
                    'decision' => 'Pending Review',
                    'reason' => $reason,
                    'adminNote' => null,
                    'approvedAmount' => null,
                    'extraMessage' => 'We will review your request within 1-2 business days and update you by email.',
                ]
            );
        } catch (\Throwable $exception) {
            \Log::warning('Failed to send API refund request submitted email', [
                'order_id' => $order->id,
                'refund_request_id' => $refundRequest->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);
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
