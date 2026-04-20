<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\CustomOrder;
use App\Models\CustomOrderRefundRequest;
use App\Services\Payment\PayMongoCheckoutService;
use App\Services\TransactionalMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminCustomOrderController extends Controller
{
    private function resolveAdminOrderItemsSubtotal(CustomOrder $order): float
    {
        $breakdown = $order->getPriceBreakdown();
        $breakdownData = $breakdown['breakdown'] ?? [];

        $material = (float) ($breakdownData['material_cost'] ?? 0);
        $pattern = (float) ($breakdownData['pattern_fee'] ?? 0);
        $labor = (float) ($breakdownData['labor_cost'] ?? 0);
        $discount = (float) ($breakdownData['discount'] ?? 0);
        $fromBreakdown = max(($material + $pattern + $labor - $discount), 0);
        if ($fromBreakdown > 0) {
            return $fromBreakdown;
        }

        // Canonical fallback from selected pattern(s) and meters.
        $patternModels = collect();

        $patternIdFromMeta = (int) data_get($order->design_metadata ?? [], 'pattern_id', 0);
        if ($patternIdFromMeta > 0) {
            $metaPattern = \App\Models\YakanPattern::find($patternIdFromMeta);
            if ($metaPattern) {
                $patternModels->push($metaPattern);
            }
        }

        $patternsRaw = $order->patterns;
        $patternsArr = is_array($patternsRaw) ? $patternsRaw : [];
        foreach ($patternsArr as $rawPattern) {
            if (is_numeric($rawPattern)) {
                $p = \App\Models\YakanPattern::find((int) $rawPattern);
            } else {
                $p = !empty($rawPattern) ? \App\Models\YakanPattern::where('name', $rawPattern)->first() : null;
            }
            if ($p) {
                $patternModels->push($p);
            }
        }

        $patternModels = $patternModels->unique('id')->values();
        if ($patternModels->isNotEmpty() && !empty($order->fabric_quantity_meters)) {
            $qtyMultiplier = (float) ($order->quantity ?? 1);
            $patternFeeTotal = (float) $patternModels->sum(fn($p) => (float) ($p->pattern_price ?? 0));
            $pricePerMeter = (float) ($patternModels->first()->price_per_meter ?? 0);
            $materialCost = ((float) $order->fabric_quantity_meters) * $pricePerMeter;
            $canonicalSubtotal = ($materialCost + $patternFeeTotal) * $qtyMultiplier;
            if ($canonicalSubtotal > 0) {
                return $canonicalSubtotal;
            }
        }

        // Last fallback: quoted amount minus one shipping fee (if delivery).
        $quoted = (float) ($order->final_price ?? $order->estimated_price ?? 0);
        $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return $quoted;
        }

        $shipping = (float) ($order->shipping_fee ?? 0);
        return max($quoted - $shipping, 0);
    }

    private function resolveAdminOrderPriceParts(CustomOrder $order): array
    {
        $quoted = (float) ($order->final_price ?? $order->estimated_price ?? 0);
        $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');

        if ($deliveryType === 'pickup') {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $breakdown = $order->getPriceBreakdown();
        $breakdownData = $breakdown['breakdown'] ?? [];

        $material = (float) ($breakdownData['material_cost'] ?? 0);
        $pattern = (float) ($breakdownData['pattern_fee'] ?? 0);
        $labor = (float) ($breakdownData['labor_cost'] ?? 0);
        $discount = (float) ($breakdownData['discount'] ?? 0);
        $deliveryFeeInBreakdown = (float) ($breakdownData['delivery_fee'] ?? 0);
        $itemsSubtotalFromBreakdown = max(($material + $pattern + $labor - $discount), 0);

        if ($deliveryFeeInBreakdown > 0) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $shipping = (float) ($order->shipping_fee ?? 0);

        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - ($itemsSubtotalFromBreakdown + $shipping)) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - $itemsSubtotalFromBreakdown) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => $shipping, 'total' => $quoted + $shipping];
        }

        // Legacy/ambiguous: avoid adding shipping again.
        return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
    }

    private function calculateBatchSharedShipping(
        \Illuminate\Support\Collection $orders
    ): float {
        $eligibleShipping = $orders->map(function (CustomOrder $item) {
            $deliveryType = $item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup');
            if ($deliveryType === 'pickup') {
                return 0.0;
            }

            $shipping = (float) ($item->shipping_fee ?? 0);
            if ($shipping <= 0) {
                $shipping = 0.0;

                $city = strtolower((string) ($item->delivery_city ?? ''));
                $province = strtolower((string) ($item->delivery_province ?? ''));
                $address = strtolower((string) ($item->delivery_address ?? ''));
                $haystack = trim($address . ' ' . $city . ' ' . $province);

                if ($haystack === '' && !empty($item->user_id)) {
                    $defaultAddress = \App\Models\UserAddress::query()
                        ->where('user_id', $item->user_id)
                        ->where('is_default', true)
                        ->first();

                    if ($defaultAddress) {
                        $fallbackCity = strtolower((string) ($defaultAddress->city ?? ''));
                        $fallbackProvince = strtolower((string) ($defaultAddress->province ?? $defaultAddress->region ?? ''));
                        $fallbackAddress = strtolower(implode(' ', array_filter([
                            $defaultAddress->street_name ?? null,
                            $defaultAddress->barangay ?? null,
                            $defaultAddress->city ?? null,
                            $defaultAddress->province ?? ($defaultAddress->region ?? null),
                        ])));

                        $haystack = trim($fallbackAddress . ' ' . $fallbackCity . ' ' . $fallbackProvince);
                    }
                }

                if (
                    str_contains($haystack, 'zamboanga') ||
                    str_contains($haystack, 'barmm') ||
                    str_contains($haystack, 'bangsamoro') ||
                    str_contains($haystack, 'basilan') ||
                    str_contains($haystack, 'sulu') ||
                    str_contains($haystack, 'tawi')
                ) {
                    $shipping = 100.0;
                } elseif (
                    str_contains($haystack, 'mindanao') ||
                    str_contains($haystack, 'davao') ||
                    str_contains($haystack, 'cagayan de oro') ||
                    str_contains($haystack, 'iligan') ||
                    str_contains($haystack, 'cotabato') ||
                    str_contains($haystack, 'caraga') ||
                    str_contains($haystack, 'general santos') ||
                    str_contains($haystack, 'soccsksargen')
                ) {
                    $shipping = 180.0;
                } elseif (
                    str_contains($haystack, 'visaya') ||
                    str_contains($haystack, 'cebu') ||
                    str_contains($haystack, 'iloilo') ||
                    str_contains($haystack, 'bacolod') ||
                    str_contains($haystack, 'tacloban') ||
                    str_contains($haystack, 'leyte') ||
                    str_contains($haystack, 'samar') ||
                    str_contains($haystack, 'bohol') ||
                    str_contains($haystack, 'negros')
                ) {
                    $shipping = 250.0;
                } elseif (
                    str_contains($haystack, 'ncr') ||
                    str_contains($haystack, 'metro manila') ||
                    str_contains($haystack, 'manila') ||
                    str_contains($haystack, 'quezon city') ||
                    str_contains($haystack, 'makati') ||
                    str_contains($haystack, 'calabarzon') ||
                    str_contains($haystack, 'central luzon') ||
                    str_contains($haystack, 'laguna') ||
                    str_contains($haystack, 'cavite') ||
                    str_contains($haystack, 'bulacan')
                ) {
                    $shipping = 300.0;
                } elseif ($haystack !== '') {
                    $shipping = 350.0;
                }
            }

            return $shipping;
        });

        return (float) ($eligibleShipping->max() ?? 0);
    }

    private function calculateOrderDisplayTotal(CustomOrder $order): float
    {
        return (float) ($this->resolveAdminOrderPriceParts($order)['total'] ?? 0);
    }

    private function calculateBatchDisplayTotal(\Illuminate\Support\Collection $orders): float
    {
        if ($orders->count() <= 1) {
            return (float) $orders->sum(fn(CustomOrder $item) => $this->calculateOrderDisplayTotal($item));
        }

        $quotedSubtotal = (float) $orders->sum(
            fn(CustomOrder $item) => (float) $this->resolveAdminOrderItemsSubtotal($item)
        );

        return $quotedSubtotal + $this->calculateBatchSharedShipping($orders);
    }

    private function normalizeOrderReferenceImage(string $rawValue): ?string
    {
        $candidate = trim($rawValue);
        $candidateLower = strtolower($candidate);

        if (
            $candidate === '' ||
            $candidateLower === 'null' ||
            $candidate === '-' ||
            str_contains($candidateLower, 'no-image.svg')
        ) {
            return null;
        }

        if (
            str_starts_with($candidate, 'http://') ||
            str_starts_with($candidate, 'https://') ||
            str_starts_with($candidate, 'data:image')
        ) {
            $parsedPath = (string) (parse_url($candidate, PHP_URL_PATH) ?? '');
            if (preg_match('#^/chat-image/(chats|payments)/(.*)$#', $parsedPath, $matches)) {
                $chatStoragePath = storage_path('app/public/' . $matches[1] . '/' . ltrim((string) ($matches[2] ?? ''), '/'));
                if (!is_file($chatStoragePath)) {
                    return null;
                }
            }

            return $candidate;
        }

        $candidate = ltrim($candidate, '/');
        if ($candidate === '') {
            return null;
        }

        $publicStoragePath = storage_path('app/public/' . $candidate);

        if (str_starts_with($candidate, 'storage/')) {
            if (!is_file($publicStoragePath)) {
                return null;
            }
            return asset($candidate);
        }

        if (str_starts_with($candidate, 'chat-image/')) {
            $chatPath = preg_replace('#^chat-image/#', '', $candidate);
            $chatStoragePath = storage_path('app/public/' . ltrim((string) $chatPath, '/'));

            if (!is_file($chatStoragePath)) {
                return null;
            }

            return url('/' . $candidate);
        }

        if (str_starts_with($candidate, 'chats/') || str_starts_with($candidate, 'payments/')) {
            if (!is_file($publicStoragePath)) {
                return null;
            }
            return url('/chat-image/' . $candidate);
        }

        if (!is_file($publicStoragePath)) {
            return null;
        }

        return asset('storage/' . $candidate);
    }

    private function extractReferenceImagesFromSpecifications(?string $rawSpecifications): array
    {
        if (empty($rawSpecifications)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) $rawSpecifications) ?: [];
        $images = [];

        foreach ($lines as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^-\s*image\s*\d*\s*:\s*(.+)$/i', $trimmed, $matches)) {
                $candidate = trim((string) ($matches[1] ?? ''));
                if ($candidate !== '') {
                    $images[] = $candidate;
                }
            }
        }

        return $images;
    }

    private function resolveOrderReferenceImages(CustomOrder $order): array
    {
        $candidates = [];

        if (!empty($order->design_upload)) {
            $rawUploads = is_string($order->design_upload)
                ? explode(',', $order->design_upload)
                : (array) $order->design_upload;

            foreach ($rawUploads as $upload) {
                $value = trim((string) $upload);
                if ($value !== '') {
                    $candidates[] = $value;
                }
            }
        }

        $candidates = array_merge(
            $candidates,
            $this->extractReferenceImagesFromSpecifications($order->specifications ?? null)
        );

        $resolved = [];
        $seen = [];

        $appendImage = function (string $candidate) use (&$resolved, &$seen) {
            $normalized = $this->normalizeOrderReferenceImage($candidate);
            if (!$normalized) {
                return;
            }

            if (isset($seen[$normalized])) {
                return;
            }

            $seen[$normalized] = true;
            $resolved[] = $normalized;
        };

        foreach ($candidates as $candidate) {
            $appendImage((string) $candidate);
        }

        // Fallback for old chat-generated orders with empty/invalid design_upload.
        if (empty($resolved) && !empty($order->chat_id)) {
            $chatImageQuery = ChatMessage::query()
                ->where('chat_id', $order->chat_id)
                ->where('sender_type', 'user')
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '');

            // Restrict to messages created before the order + 10 min to avoid scanning a long tail.
            if ($order->created_at) {
                $chatImageQuery->where('created_at', '<=', $order->created_at->copy()->addMinutes(10));
            }

            // Query IDs first to minimize memory use during sorting.
            $chatImageMessageIds = $chatImageQuery
                ->orderByDesc('id')
                ->limit(80)
                ->pluck('id');

            $chatImageMessages = collect();
            if ($chatImageMessageIds->isNotEmpty()) {
                $chatImageMessages = ChatMessage::query()
                    ->whereIn('id', $chatImageMessageIds->all())
                    ->get(['id', 'image_path', 'message', 'form_data'])
                    ->sortBy('id')
                    ->values();
            }

            foreach ($chatImageMessages as $chatImageMessage) {
                $message = strtolower(trim((string) ($chatImageMessage->message ?? '')));
                if ($message !== '' && str_contains($message, 'payment proof')) {
                    continue;
                }

                $formData = is_array($chatImageMessage->form_data) ? $chatImageMessage->form_data : [];
                $inlineImage = $formData['inline_image_data'] ?? null;
                if (is_string($inlineImage) && $inlineImage !== '') {
                    $appendImage($inlineImage);
                }

                $appendImage((string) $chatImageMessage->image_path);
            }
        }

        return array_values(array_slice($resolved, 0, 6));
    }

    private function attachResolvedReferenceImages(\Illuminate\Support\Collection $orders): void
    {
        $orders->each(function (CustomOrder $order) {
            $resolvedImages = $this->resolveOrderReferenceImages($order);
            $order->setAttribute('resolved_reference_images', $resolvedImages);
            $order->setAttribute('resolved_reference_image', $resolvedImages[0] ?? null);
        });
    }

    private function resolveBatchOrders(CustomOrder $order): \Illuminate\Support\Collection
    {
        $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

        if ($hasBatchColumn && !empty($order->batch_order_number)) {
            return CustomOrder::where('batch_order_number', $order->batch_order_number)
                ->orderBy('id')
                ->get();
        }

        $minuteKey = optional($order->created_at)->format('Y-m-d H:i');
        if (!$minuteKey || empty($order->user_id)) {
            return collect([$order]);
        }

        $query = CustomOrder::where('user_id', $order->user_id)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$minuteKey]);

        if ($hasBatchColumn) {
            $query->where(function ($q) {
                $q->whereNull('batch_order_number')
                    ->orWhere('batch_order_number', '');
            });
        }

        $rows = $query->orderBy('id')->get();

        return $rows->isNotEmpty() ? $rows : collect([$order]);
    }

    public function index(Request $request)
    {
        try {
            $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

            // Detect implicit same-submission groups (same user + same minute).
            $implicitGroups = DB::table('custom_orders')
                ->select(
                    'user_id',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as minute_key"),
                    DB::raw('MIN(id) as primary_id'),
                    DB::raw('COUNT(*) as cnt'),
                    DB::raw('SUM(COALESCE(final_price, estimated_price, 0)) as implicit_total')
                )
                ->whereNotNull('user_id')
                ->when($hasBatchColumn, function ($q) {
                    $q->where(function ($w) {
                        $w->whereNull('batch_order_number')
                          ->orWhere('batch_order_number', '');
                    });
                })
                ->groupBy('user_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')"))
                ->havingRaw('cnt > 1')
                ->get();

            $implicitCountMap = $implicitGroups->pluck('cnt', 'primary_id')->toArray();
            $implicitMetaMap = [];

            $implicitExcludeIds = collect();
            foreach ($implicitGroups as $group) {
                $memberOrdersQuery = CustomOrder::query()
                    ->where('user_id', $group->user_id)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$group->minute_key]);

                if ($hasBatchColumn) {
                    $memberOrdersQuery->where(function ($w) {
                        $w->whereNull('batch_order_number')
                          ->orWhere('batch_order_number', '');
                    });
                }

                $memberOrders = $memberOrdersQuery->orderBy('id')->get();

                $implicitMetaMap[(int) $group->primary_id] = [
                    'item_count' => (int) $group->cnt,
                    'batch_total' => $this->calculateBatchDisplayTotal($memberOrders),
                ];

                $memberIds = $memberOrders
                    ->pluck('id')
                    ->filter(fn($id) => (int) $id !== (int) $group->primary_id)
                    ->values();

                $implicitExcludeIds = $implicitExcludeIds->merge($memberIds);
            }

            // For named batch orders, only surface the primary (lowest id) row per batch.
            // Secondary siblings will be shown as sub-rows in the view.
            if ($hasBatchColumn) {
                $batchPrimaryIds = CustomOrder::select(DB::raw('MIN(id) as primary_id'))
                    ->whereNotNull('batch_order_number')
                    ->where('batch_order_number', '!=', '')
                    ->groupBy('batch_order_number')
                    ->pluck('primary_id');

                $query = CustomOrder::with(['user', 'product'])
                    ->where(function ($q) use ($batchPrimaryIds) {
                        $q->whereNull('batch_order_number')
                          ->orWhere('batch_order_number', '')
                          ->orWhereIn('id', $batchPrimaryIds);
                    })
                    ->when($implicitExcludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $implicitExcludeIds))
                    ->orderBy('created_at', 'desc');
            } else {
                $query = CustomOrder::with(['user', 'product'])
                    ->when($implicitExcludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $implicitExcludeIds))
                    ->orderBy('created_at', 'desc');
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search by user name, email, or order details
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('specifications', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('id', (int) $search);
                    }
                });
            }

            $orders = $query->paginate($request->get('per_page', 20))->withQueryString();
            $this->attachResolvedReferenceImages($orders->getCollection());

            // Build batch metadata map for all named batches on this page.
            $batchCountMap    = [];
            $batchMetaMap     = [];
            if ($hasBatchColumn) {
                $batchNumbers = $orders->pluck('batch_order_number')->filter()->unique()->values();
                if ($batchNumbers->isNotEmpty()) {
                    $batchCountMap = CustomOrder::select('batch_order_number', DB::raw('COUNT(*) as cnt'))
                        ->whereIn('batch_order_number', $batchNumbers)
                        ->groupBy('batch_order_number')
                        ->pluck('cnt', 'batch_order_number')
                        ->toArray();

                    $batchRows = CustomOrder::query()
                        ->whereIn('batch_order_number', $batchNumbers)
                        ->get()
                        ->groupBy('batch_order_number');

                    $batchMetaMap = $batchRows
                        ->map(function ($rows) {
                            return [
                                'item_count' => $rows->count(),
                                'batch_total' => $this->calculateBatchDisplayTotal($rows),
                            ];
                        })
                        ->toArray();
                }
            }
            $batchSiblingsMap = collect();

            // Stats — always from all orders (unfiltered)
            $totalOrders     = CustomOrder::count();
            $todayOrders     = CustomOrder::whereDate('created_at', today())->count();
            $pendingCount    = CustomOrder::where('status', 'pending')->count();
            $approvedCount   = CustomOrder::where('status', 'approved')->count();
            $inProductionCount = CustomOrder::where('status', 'in_production')->count();
            $totalRevenue    = CustomOrder::where('payment_status', 'paid')->sum('final_price');

            return view('admin.custom_orders.index_enhanced', compact(
                'orders', 'totalOrders', 'todayOrders', 'pendingCount',
                'approvedCount', 'inProductionCount', 'totalRevenue',
                'batchCountMap', 'batchMetaMap', 'implicitCountMap', 'implicitMetaMap', 'batchSiblingsMap'
            ));
        } catch (\Exception $e) {
            \Log::error('Custom Orders Index Error: ' . $e->getMessage());
            return 'Custom Orders Error: ' . $e->getMessage();
        }
    }

    public function show(CustomOrder $order)
    {
        $order->load(['user', 'product']);
        $this->attachResolvedReferenceImages(collect([$order]));

        $latestCustomRefundRequest = null;
        if (Schema::hasTable('custom_order_refund_requests')) {
            $latestCustomRefundRequest = CustomOrderRefundRequest::with(['user', 'reviewer'])
                ->where('custom_order_id', $order->id)
                ->latest()
                ->first();
        }

        // Load all sibling orders in the same batch so the details view can show them
        $batchOrders = collect();
        $isImplicitBatchGroup = false;
        $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

        if ($hasBatchColumn && !empty($order->batch_order_number)) {
            $batchOrders = CustomOrder::with(['user:id,name,email', 'product:id,name,price,image'])
                ->where('batch_order_number', $order->batch_order_number)
                ->where('id', '!=', $order->id)
                ->orderBy('id')
                ->get();
        } else {
            $minuteKey = optional($order->created_at)->format('Y-m-d H:i');
            if ($minuteKey && !empty($order->user_id)) {
                $batchOrdersQuery = CustomOrder::with(['user:id,name,email', 'product:id,name,price,image'])
                    ->where('user_id', $order->user_id)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$minuteKey])
                    ->where('id', '!=', $order->id);

                if ($hasBatchColumn) {
                    $batchOrdersQuery->where(function ($q) {
                        $q->whereNull('batch_order_number')
                          ->orWhere('batch_order_number', '');
                    });
                }

                $batchOrders = $batchOrdersQuery->orderBy('id')->get();
                $isImplicitBatchGroup = $batchOrders->isNotEmpty();
            }
        }

        if ($batchOrders->isNotEmpty()) {
            $this->attachResolvedReferenceImages($batchOrders);
        }

        return view('admin.custom_orders.details', compact('order', 'batchOrders', 'isImplicitBatchGroup', 'latestCustomRefundRequest'));
    }

    /**
     * Render printable invoice for a custom order (single or batch submission).
     */
    public function generateInvoice(CustomOrder $order)
    {
        $order->load(['user', 'product', 'fabricType', 'intendedUse']);

        $invoiceOrders = $this->resolveBatchOrders($order)
            ->load(['user', 'product', 'fabricType', 'intendedUse'])
            ->values();

        if ($invoiceOrders->isEmpty()) {
            $invoiceOrders = collect([$order]);
        }

        return view('admin.custom_orders.invoice', [
            'order' => $order,
            'invoiceOrders' => $invoiceOrders,
        ]);
    }

    /**
     * Approve and process a custom-order refund/return request.
     */
    public function approveRefundRequest(Request $request, CustomOrderRefundRequest $refundRequest)
    {
        $this->ensureCustomRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'admin_decision' => 'nullable|in:recommended,FULL_REFUND,PARTIAL_REFUND,RETURN_REQUIRED,REJECT',
            'admin_note' => 'nullable|string|max:2000',
            'approved_amount' => 'nullable|numeric|min:0',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['pending_review', 'under_review', 'requested'], true)) {
            return $this->customRefundActionErrorResponse($request, 'This refund/return request is not in a reviewable state.');
        }

        $order = $refundRequest->customOrder;
        if (!$order) {
            return $this->customRefundActionErrorResponse($request, 'Associated custom order was not found.');
        }

        $selectedDecision = strtoupper((string) ($validated['admin_decision'] ?? 'recommended'));
        if ($selectedDecision === 'RECOMMENDED') {
            $recommendedDecision = strtoupper((string) ($refundRequest->recommended_decision ?? ''));
            $selectedDecision = $recommendedDecision !== '' ? $recommendedDecision : 'FULL_REFUND';
        }
        if (!in_array($selectedDecision, ['FULL_REFUND', 'PARTIAL_REFUND', 'RETURN_REQUIRED', 'REJECT'], true)) {
            $selectedDecision = 'REJECT';
        }

        $orderTotal = max((float) ($order->final_price ?? $order->estimated_price ?? 0), 0);
        $recommendedAmount = (float) ($refundRequest->recommended_refund_amount ?? 0);
        $approvedAmount = array_key_exists('approved_amount', $validated) && $validated['approved_amount'] !== null
            ? (float) $validated['approved_amount']
            : ($recommendedAmount > 0 ? $recommendedAmount : $orderTotal);
        if ($orderTotal > 0) {
            $approvedAmount = min($approvedAmount, $orderTotal);
        }
        $approvedAmount = max(0, $approvedAmount);

        $refundRequest->status = $selectedDecision === 'REJECT' ? 'rejected' : 'approved';
        if (Schema::hasColumn('custom_order_refund_requests', 'workflow_status')) {
            $refundRequest->workflow_status = match ($selectedDecision) {
                'REJECT' => 'rejected',
                'RETURN_REQUIRED' => 'awaiting_return_shipment',
                default => 'pending_payout',
            };
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'final_decision')) {
            $refundRequest->final_decision = $selectedDecision;
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'refund_amount')) {
            $refundRequest->refund_amount = $selectedDecision === 'REJECT' ? 0 : $approvedAmount;
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'approved_amount')) {
            $refundRequest->approved_amount = $selectedDecision === 'REJECT' ? 0 : $approvedAmount;
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'return_required')) {
            $refundRequest->return_required = $selectedDecision === 'RETURN_REQUIRED';
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'payout_status')) {
            $refundRequest->payout_status = $selectedDecision === 'REJECT' ? 'cancelled' : 'pending';
        }
        $refundRequest->admin_note = $validated['admin_note'] ?? $refundRequest->admin_note;
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();
        if ($selectedDecision === 'REJECT') {
            $refundRequest->processed_at = now();
        }
        $refundRequest->save();

        if ($selectedDecision === 'REJECT') {
            $this->notifyCustomRefundDecision($refundRequest, 'rejected');
            return $this->customRefundActionSuccessResponse($request, $refundRequest, ucfirst($refundRequest->request_type) . ' request has been rejected.');
        }

        if ($selectedDecision === 'RETURN_REQUIRED') {
            $this->notifyCustomRefundWorkflowUpdate(
                $refundRequest,
                'Return Required',
                'Your request has been reviewed and approved with return required. Please send back the item and share return shipment details to continue processing your payout.'
            );

            return $this->customRefundActionSuccessResponse($request, $refundRequest, ucfirst($refundRequest->request_type) . ' review saved. Waiting for customer return shipment details.');
        }

        $this->notifyCustomRefundWorkflowUpdate(
            $refundRequest,
            'Pending Payout',
            'Your request has been reviewed and is now pending payout processing.'
        );

        return $this->customRefundActionSuccessResponse($request, $refundRequest, ucfirst($refundRequest->request_type) . ' review saved. Request is now pending payout processing.');
    }

    /**
     * Reject a custom-order refund/return request.
     */
    public function rejectRefundRequest(Request $request, CustomOrderRefundRequest $refundRequest)
    {
        $request->validate([
            'admin_note' => 'required|string|max:2000',
        ]);

        $request->merge(['admin_decision' => 'REJECT']);
        return $this->approveRefundRequest($request, $refundRequest);
    }

    /**
     * Confirm returned custom-order item receipt and move request to pending payout.
     */
    public function markRefundReturnReceived(Request $request, CustomOrderRefundRequest $refundRequest)
    {
        $this->ensureCustomRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['awaiting_return_shipment', 'return_in_transit'], true)) {
            return redirect()->back()->with('error', 'Return can only be confirmed after return shipment is initiated.');
        }

        $refundRequest->status = 'approved';
        if (Schema::hasColumn('custom_order_refund_requests', 'workflow_status')) {
            $refundRequest->workflow_status = 'pending_payout';
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'return_received_at')) {
            $refundRequest->return_received_at = now();
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'payout_status')) {
            $refundRequest->payout_status = 'pending';
        }
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $existing = trim((string) ($refundRequest->admin_note ?? ''));
            $suffix = trim((string) $validated['admin_note']);
            $refundRequest->admin_note = $existing !== '' ? ($existing . "\n" . $suffix) : $suffix;
        }

        $refundRequest->save();

        $this->notifyCustomRefundWorkflowUpdate(
            $refundRequest,
            'Return Received',
            'We have received the returned item. Your request is now pending payout processing.'
        );

        return redirect()->back()->with('success', 'Return marked as received. Request is now pending payout.');
    }

    /**
     * Execute custom-order refund payout and finalize request.
     */
    public function executeRefundPayout(Request $request, CustomOrderRefundRequest $refundRequest)
    {
        $this->ensureCustomRefundWorkflowColumnsExist();

        $validated = $request->validate([
            'refund_channel' => 'required|string|max:40',
            'refund_reference' => 'required|string|max:120',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $workflowStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($workflowStatus, ['pending_payout', 'return_received', 'approved'], true)) {
            return redirect()->back()->with('error', 'Request is not yet ready for payout processing.');
        }

        $order = $refundRequest->customOrder;
        if (!$order) {
            return redirect()->back()->with('error', 'Associated custom order was not found.');
        }

        if (Schema::hasColumn('custom_order_refund_requests', 'refund_channel')) {
            $refundRequest->refund_channel = trim((string) $validated['refund_channel']);
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'refund_reference')) {
            $refundRequest->refund_reference = trim((string) $validated['refund_reference']);
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'payout_status')) {
            $refundRequest->payout_status = 'completed';
        }
        if (Schema::hasColumn('custom_order_refund_requests', 'workflow_status')) {
            $refundRequest->workflow_status = 'processed';
        }

        $refundRequest->status = 'processed';
        $refundRequest->processed_at = now();
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $refundRequest->admin_note = trim((string) $validated['admin_note']);
        }

        if (Schema::hasColumn('custom_order_refund_requests', 'final_decision') && empty($refundRequest->final_decision)) {
            $refundRequest->final_decision = strtoupper((string) ($refundRequest->recommended_decision ?? 'FULL_REFUND'));
        }

        if (Schema::hasColumn('custom_order_refund_requests', 'refund_amount') && (float) ($refundRequest->refund_amount ?? 0) <= 0) {
            $fallbackAmount = (float) ($refundRequest->approved_amount ?? $refundRequest->recommended_refund_amount ?? $order->final_price ?? 0);
            $refundRequest->refund_amount = max(0, $fallbackAmount);
        }

        $refundRequest->save();

        $reasonText = strtolower((string) ($refundRequest->reason ?? ''));
        $detailsText = strtolower((string) ($refundRequest->details ?? ''));
        $isCancellationRefundFlow = str_contains($reasonText, 'cancel') || str_contains($detailsText, 'cancel');
        if ($isCancellationRefundFlow) {
            $order->status = 'cancelled';
        }

        $payoutSummary = 'Refund payout processed on ' . now()->format('M d, Y h:i A')
            . ' via ' . strtoupper((string) $validated['refund_channel'])
            . ' (Ref: ' . trim((string) $validated['refund_reference']) . ').';

        $existingOrderNotes = trim((string) ($order->admin_notes ?? ''));
        if (!str_contains($existingOrderNotes, $payoutSummary)) {
            $existingOrderNotes = $existingOrderNotes === ''
                ? $payoutSummary
                : ($existingOrderNotes . "\n" . $payoutSummary);
        }

        if (!empty($validated['admin_note'])) {
            $adminNoteLine = 'Refund admin note: ' . trim((string) $validated['admin_note']);
            if (!str_contains($existingOrderNotes, $adminNoteLine)) {
                $existingOrderNotes = $existingOrderNotes === ''
                    ? $adminNoteLine
                    : ($existingOrderNotes . "\n" . $adminNoteLine);
            }
        }

        $order->admin_notes = trim($existingOrderNotes);
        $order->save();

        $this->notifyCustomRefundDecision($refundRequest, 'approved');

        return redirect()->back()->with('success', 'Refund payout recorded successfully.');
    }

    private function customRefundActionSuccessResponse(Request $request, CustomOrderRefundRequest $refundRequest, string $message)
    {
        if ($this->isJsonLikeRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'refund' => $this->buildCustomRefundActionPayload($refundRequest),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function customRefundActionErrorResponse(Request $request, string $message, int $status = 422)
    {
        if ($this->isJsonLikeRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }

    private function buildCustomRefundActionPayload(CustomOrderRefundRequest $refundRequest): array
    {
        $refundRequest->loadMissing('customOrder');

        $status = strtolower((string) $refundRequest->status);
        $workflow = strtolower((string) ($refundRequest->workflow_status ?: $status));
        $statusState = 'under_review';

        if (in_array($workflow, ['awaiting_return_shipment', 'return_in_transit'], true)) {
            $statusState = 'awaiting_return';
        } elseif ($status === 'rejected' || $workflow === 'rejected') {
            $statusState = 'rejected';
        } elseif (in_array($workflow, ['processed'], true)
            || strtolower((string) ($refundRequest->payout_status ?? '')) === 'completed'
            || in_array($status, ['approved', 'processed'], true)) {
            $statusState = 'refunded';
        }

        $statusLabel = match ($statusState) {
            'awaiting_return' => 'Awaiting return',
            'refunded' => 'Refunded',
            'rejected' => 'Rejected',
            default => 'Under review',
        };

        $order = $refundRequest->customOrder;
        $displayAmount = (float) ($refundRequest->approved_amount
            ?? $refundRequest->refund_amount
            ?? $refundRequest->recommended_refund_amount
            ?? $order?->final_price
            ?? 0);

        return [
            'refund_id' => (string) ($refundRequest->refund_reference ?: ('RF-' . str_pad((string) $refundRequest->id, 4, '0', STR_PAD_LEFT))),
            'refund_request_id' => $refundRequest->id,
            'status_state' => $statusState,
            'status_label' => $statusLabel,
            'admin_note' => trim((string) ($refundRequest->admin_note ?? '')),
            'amount' => number_format(max($displayAmount, 0), 2),
        ];
    }

    private function isJsonLikeRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * Ensure custom-order refund workflow columns exist for lagging deployments.
     */
    private function ensureCustomRefundWorkflowColumnsExist(): void
    {
        if (!Schema::hasTable('custom_order_refund_requests')) {
            return;
        }

        try {
            Schema::table('custom_order_refund_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!Schema::hasColumn('custom_order_refund_requests', 'workflow_status')) {
                    $table->string('workflow_status', 60)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'recommended_decision')) {
                    $table->string('recommended_decision', 40)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'recommended_refund_amount')) {
                    $table->decimal('recommended_refund_amount', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'return_required')) {
                    $table->boolean('return_required')->default(false);
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'final_decision')) {
                    $table->string('final_decision', 40)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'approved_amount')) {
                    $table->decimal('approved_amount', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'refund_amount')) {
                    $table->decimal('refund_amount', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'refund_channel')) {
                    $table->string('refund_channel', 40)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'refund_reference')) {
                    $table->string('refund_reference', 120)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'payout_status')) {
                    $table->string('payout_status', 40)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'return_tracking_number')) {
                    $table->string('return_tracking_number', 120)->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'return_shipped_at')) {
                    $table->timestamp('return_shipped_at')->nullable();
                }
                if (!Schema::hasColumn('custom_order_refund_requests', 'return_received_at')) {
                    $table->timestamp('return_received_at')->nullable();
                }
            });
        } catch (\Throwable $exception) {
            Log::warning('Unable to ensure custom refund workflow columns.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Notify customer about in-progress custom-order refund workflow updates.
     */
    private function notifyCustomRefundWorkflowUpdate(CustomOrderRefundRequest $refundRequest, string $stage, string $message): void
    {
        $order = $refundRequest->customOrder;
        if (!$order) {
            return;
        }

        $recipient = $refundRequest->user?->email ?: $order->user?->email;
        if (!$recipient) {
            return;
        }

        $orderRef = method_exists($order, 'getDisplayRefAttribute')
            ? ($order->display_ref ?? ('#' . $order->id))
            : ('#' . $order->id);
        $subject = 'Refund Request Update - ' . $orderRef;

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $refundRequest->user?->name ?: ($order->customer_name ?? 'Customer'),
                    'introText' => $message,
                    'orderRef' => $orderRef,
                    'orderId' => $order->id,
                    'requestType' => ucfirst((string) ($refundRequest->request_type ?: 'refund')) . ' Request',
                    'decision' => $stage,
                    'reason' => $refundRequest->reason,
                    'adminNote' => $refundRequest->admin_note,
                    'approvedAmount' => null,
                    'extraMessage' => null,
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to send custom refund workflow update email', [
                'refund_request_id' => $refundRequest->id,
                'recipient' => $recipient,
                'stage' => $stage,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Notify customer about custom-order refund/return decision.
     */
    private function notifyCustomRefundDecision(CustomOrderRefundRequest $refundRequest, string $decision): void
    {
        $order = $refundRequest->customOrder;
        if (!$order) {
            return;
        }

        $recipient = $refundRequest->user?->email ?: $order->user?->email;
        if (!$recipient) {
            return;
        }

        $decisionLabel = strtolower($decision) === 'approved' ? 'Approved' : 'Rejected';
        $requestType = ucfirst((string) ($refundRequest->request_type ?: 'refund')) . ' Request';
        $orderRef = method_exists($order, 'getDisplayRefAttribute')
            ? ($order->display_ref ?? ('#' . $order->id))
            : ('#' . $order->id);
        $subject = $requestType . ' ' . $decisionLabel . ' - ' . $orderRef;
        $intro = strtolower($decision) === 'approved'
            ? 'Your ' . strtolower($requestType) . ' has been approved and processed.'
            : 'Your ' . strtolower($requestType) . ' has been rejected.';

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $refundRequest->user?->name ?: ($order->customer_name ?? 'Customer'),
                    'introText' => $intro,
                    'orderRef' => $orderRef,
                    'orderId' => $order->id,
                    'requestType' => $requestType,
                    'decision' => $decisionLabel,
                    'reason' => $refundRequest->reason,
                    'adminNote' => $refundRequest->admin_note,
                    'approvedAmount' => null,
                    'extraMessage' => null,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send custom refund/return decision email', [
                'refund_request_id' => $refundRequest->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Serve custom-order refund evidence for admin preview.
     */
    public function viewRefundEvidence(CustomOrderRefundRequest $refundRequest, int $index)
    {
        try {
            $rawEvidence = $refundRequest->evidence_paths;
            if (is_array($rawEvidence)) {
                $evidence = $rawEvidence;
            } elseif (is_string($rawEvidence) && trim($rawEvidence) !== '') {
                $decoded = json_decode($rawEvidence, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $evidence = $decoded;
                } elseif (str_contains($rawEvidence, ',')) {
                    $evidence = array_values(array_filter(array_map('trim', explode(',', $rawEvidence))));
                } else {
                    $evidence = [trim($rawEvidence)];
                }
            } else {
                $evidence = [];
            }

            $evidence = array_values(array_filter(
                is_array($evidence) ? $evidence : [],
                static fn ($value) => $value !== null && $value !== ''
            ));

            if (!array_key_exists($index, $evidence)) {
                Log::warning('Admin custom-order refund evidence index not found', [
                    'refund_request_id' => $refundRequest->id,
                    'requested_index' => $index,
                    'available_count' => count($evidence),
                ]);

                return response('Evidence file not found.', 404)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }

            $entry = $evidence[$index];
            $entryCandidates = [];
            if (is_array($entry)) {
                foreach (['url', 'path', 'secure_url', 'open_url', 'preview_url', 'fallback_url', 'src', 'file', 'value'] as $key) {
                    $candidateValue = $entry[$key] ?? null;
                    if (is_string($candidateValue) && trim($candidateValue) !== '') {
                        $entryCandidates[] = trim($candidateValue);
                    }
                }
            } elseif (is_string($entry) && trim($entry) !== '') {
                $entryCandidates[] = trim($entry);
            }

            foreach ($entryCandidates as $entryCandidate) {
                $entryCandidate = str_replace('\\/', '/', trim($entryCandidate, " \t\n\r\0\x0B\"'"));
                if (str_starts_with($entryCandidate, 'http://') || str_starts_with($entryCandidate, 'https://')) {
                    return redirect()->away($entryCandidate);
                }

                if (str_starts_with($entryCandidate, '//')) {
                    return redirect()->away('https:' . $entryCandidate);
                }
            }

            $path = '';
            $normalizedPath = '';
            foreach ($entryCandidates as $entryCandidate) {
                $entryCandidate = str_replace('\\/', '/', trim($entryCandidate, " \t\n\r\0\x0B\"'"));
                $pathFromUrl = parse_url($entryCandidate, PHP_URL_PATH);
                $candidateNormalizedPath = ltrim(is_string($pathFromUrl) && $pathFromUrl !== '' ? $pathFromUrl : $entryCandidate, '/');
                if ($candidateNormalizedPath !== '') {
                    $path = $entryCandidate;
                    $normalizedPath = $candidateNormalizedPath;
                    break;
                }
            }

            if ($normalizedPath === '') {
                Log::warning('Admin custom-order refund evidence entry has no usable path', [
                    'refund_request_id' => $refundRequest->id,
                    'requested_index' => $index,
                    'entry_type' => gettype($entry),
                    'entry_preview' => is_scalar($entry) ? (string) $entry : json_encode($entry),
                ]);

                return response('Evidence file is unavailable.', 404)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }

            $extractRelative = static function (string $candidate, string $needle): ?string {
                $position = stripos($candidate, $needle);
                if ($position === false) {
                    return null;
                }
                $relative = substr($candidate, $position + strlen($needle));
                return ltrim((string) $relative, '/');
            };

            $storageAppPublicRelative = $extractRelative($normalizedPath, 'storage/app/public/');
            $publicStorageRelative = $extractRelative($normalizedPath, 'public/storage/');

            $stripStoragePrefixes = static function (string $candidate): string {
                $candidate = ltrim($candidate, '/');
                if (str_starts_with($candidate, 'storage/')) {
                    $candidate = substr($candidate, strlen('storage/'));
                }
                if (str_starts_with($candidate, 'public/')) {
                    $candidate = substr($candidate, strlen('public/'));
                }
                if (str_starts_with($candidate, 'app/public/')) {
                    $candidate = substr($candidate, strlen('app/public/'));
                }
                return ltrim($candidate, '/');
            };

            $decodedPath = urldecode($normalizedPath);

            $candidatePaths = array_values(array_unique(array_filter([
                $stripStoragePrefixes($normalizedPath),
                $stripStoragePrefixes($decodedPath),
                $stripStoragePrefixes((string) ltrim($path, '/')),
                $stripStoragePrefixes((string) urldecode(ltrim($path, '/'))),
                $storageAppPublicRelative ? $stripStoragePrefixes($storageAppPublicRelative) : null,
                $publicStorageRelative ? $stripStoragePrefixes($publicStorageRelative) : null,
                $stripStoragePrefixes('public/' . ltrim($normalizedPath, '/')),
                $stripStoragePrefixes('storage/' . ltrim($normalizedPath, '/')),
            ], static fn ($value) => is_string($value) && $value !== '')));

            foreach ($candidatePaths as $candidatePath) {
                $candidatePath = str_replace('\\', '/', trim((string) $candidatePath, '/'));

                if (Storage::disk('public')->exists($candidatePath)) {
                    $absolutePath = Storage::disk('public')->path($candidatePath);
                    $mimeType = $this->resolveEvidenceMimeType(
                        Storage::disk('public')->mimeType($candidatePath),
                        $absolutePath,
                        $path
                    );
                    return response()->file($absolutePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
                        'X-Content-Type-Options' => 'nosniff',
                        'Accept-Ranges' => 'bytes',
                    ]);
                }

                if (Storage::disk('local')->exists($candidatePath)) {
                    $absolutePath = Storage::disk('local')->path($candidatePath);
                    $mimeType = $this->resolveEvidenceMimeType(
                        Storage::disk('local')->mimeType($candidatePath),
                        $absolutePath,
                        $path
                    );
                    return response()->file($absolutePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
                        'X-Content-Type-Options' => 'nosniff',
                        'Accept-Ranges' => 'bytes',
                    ]);
                }

                $publicStorageRelative = ltrim(str_replace(['public/', 'storage/'], '', $candidatePath), '/');
                $publicStoragePath = public_path('storage/' . $publicStorageRelative);
                if (is_file($publicStoragePath)) {
                    $mimeType = $this->resolveEvidenceMimeType(
                        @mime_content_type($publicStoragePath),
                        $publicStoragePath,
                        $path
                    );

                    return response()->file($publicStoragePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . basename($publicStoragePath) . '"',
                        'X-Content-Type-Options' => 'nosniff',
                        'Accept-Ranges' => 'bytes',
                    ]);
                }
            }

            Log::warning('Admin custom-order refund evidence file missing on storage disk', [
                'refund_request_id' => $refundRequest->id,
                'requested_index' => $index,
                'stored_path' => $path,
                'candidate_paths' => $candidatePaths,
            ]);

            return response('Evidence file is unavailable.', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        } catch (\Throwable $exception) {
            Log::warning('Unable to serve custom-order refund evidence file for admin.', [
                'refund_request_id' => $refundRequest->id,
                'index' => $index,
                'error' => $exception->getMessage(),
            ]);

            return response('Evidence file is unavailable.', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Resolve evidence MIME type with extension-aware fallbacks.
     */
    private function resolveEvidenceMimeType(?string $detectedMimeType, string $absolutePath, string $sourcePath): string
    {
        $extensionSource = parse_url($sourcePath, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo(
            is_string($extensionSource) && $extensionSource !== '' ? $extensionSource : $absolutePath,
            PATHINFO_EXTENSION
        ));

        $mimeByExtension = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
        ];

        if (isset($mimeByExtension[$extension])) {
            return $mimeByExtension[$extension];
        }

        $normalizedDetected = strtolower(trim((string) $detectedMimeType));
        $genericTypes = ['application/octet-stream', 'binary/octet-stream', 'text/plain', 'inode/x-empty', ''];

        if (!in_array($normalizedDetected, $genericTypes, true)) {
            return $normalizedDetected;
        }

        $fileInfoMime = strtolower(trim((string) (@mime_content_type($absolutePath) ?: '')));
        if (!in_array($fileInfoMime, $genericTypes, true)) {
            return $fileInfoMime;
        }

        return 'application/octet-stream';
    }

    /**
     * Fetch verified PayMongo receipt details for admin custom order display.
     */
    public function paymongoReceipt(CustomOrder $order, PayMongoCheckoutService $payMongoService): JsonResponse
    {
        $paymentMethod = strtolower((string) ($order->payment_method ?? ''));
        $transactionId = strtolower((string) ($order->transaction_id ?? ''));
        $isPaymongoLike = in_array($paymentMethod, ['paymongo', 'online_banking'], true)
            || str_starts_with($transactionId, 'cs_')
            || str_starts_with($transactionId, 'pay_');

        if (!$isPaymongoLike) {
            return response()->json([
                'success' => false,
                'message' => 'This custom order is not a PayMongo payment.',
            ], 422);
        }

        try {
            $receipt = $payMongoService->getVerifiedReceiptForCustomOrder($order);

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
            ]);
        } catch (\Throwable $exception) {
            \Log::error('Unable to fetch verified PayMongo receipt for custom order.', [
                'custom_order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch receipt from PayMongo right now. Please try again.',
            ], 502);
        }
    }

    public function updateStatus(Request $request, CustomOrder $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,in_production,price_quoted,approved,production_complete,out_for_delivery,delivered,completed,cancelled',
            'final_price' => 'nullable|numeric|min:0'
        ]);

        try {
            $targetStatus = (string) $request->status;
            $batchWorkflowStatuses = ['in_production', 'production_complete', 'out_for_delivery', 'delivered'];
            $applyToBatch = in_array($targetStatus, $batchWorkflowStatuses, true);
            $ordersToUpdate = $applyToBatch
                ? $this->resolveBatchOrders($order)
                : collect([$order]);

            $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
            $updatedCount = 0;

            foreach ($ordersToUpdate as $targetOrder) {
                $oldStatus = $targetOrder->status;
                $wasPaid = (string) $targetOrder->payment_status === 'paid';
                $targetOrder->status = $targetStatus;

                // Final price edits only apply to the selected order.
                if ($request->filled('final_price') && $targetOrder->id === $order->id) {
                    $targetOrder->final_price = $request->final_price;
                }

                // If status changed to price_quoted, set user_notified_at.
                if ($targetStatus === 'price_quoted' && $oldStatus !== 'price_quoted') {
                    $targetOrder->user_notified_at = now();
                }

                // If status changed to processing or in_production, mark payment as paid.
                if (in_array($targetStatus, ['processing', 'in_production'], true) && $targetOrder->payment_status !== 'paid') {
                    $targetOrder->payment_status = 'paid';
                    if (\Schema::hasColumn('custom_orders', 'payment_verified_at')) {
                        $targetOrder->payment_verified_at = now();
                    }
                    if (\Schema::hasColumn('custom_orders', 'payment_confirmed_at')) {
                        $targetOrder->payment_confirmed_at = now();
                    }
                }

                // Set timestamps for workflow statuses.
                if ($targetStatus === 'production_complete' && !$targetOrder->production_completed_at) {
                    $targetOrder->production_completed_at = now();
                }

                if ($targetStatus === 'out_for_delivery' && !$targetOrder->out_for_delivery_at) {
                    $targetOrder->out_for_delivery_at = now();
                }

                if ($targetStatus === 'delivered' && !$targetOrder->delivered_at) {
                    $targetOrder->delivered_at = now();
                }

                $targetOrder->save();
                $updatedCount++;

                if (!$wasPaid && (string) $targetOrder->payment_status === 'paid') {
                    $this->sendCustomOrderPaymentReceiptEmail($targetOrder);
                }

                // Send notifications to user and admin.
                if ($oldStatus !== $targetOrder->status) {
                    $notificationService->notifyCustomOrderStatusChange($targetOrder, $oldStatus, $targetOrder->status);
                }
            }

            $order->refresh();
            $successMessage = ($applyToBatch && $updatedCount > 1)
                ? "Order status updated for {$updatedCount} batch items successfully!"
                : 'Order status updated successfully!';

            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'updated_count' => $updatedCount,
                    'order' => $order
                ]);
            }

            // Otherwise redirect back with success message
            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function quotePrice(Request $request, CustomOrder $order)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'shipping_fee' => 'nullable|numeric|min:0',
            // Price breakdown fields (optional)
            'material_cost' => 'nullable|numeric|min:0',
            'pattern_fee' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            // Build price breakdown if any breakdown fields are provided
            $hasBreakdown = $request->filled('material_cost') || 
                           $request->filled('pattern_fee') || 
                           $request->filled('labor_cost') || 
                           $request->filled('delivery_fee') ||
                           $request->filled('discount');

            $adminNotes = $request->notes;

            if ($hasBreakdown) {
                // Create price breakdown structure
                $breakdown = [];
                
                if ($request->filled('material_cost') && $request->material_cost > 0) {
                    $breakdown['material_cost'] = (float) $request->material_cost;
                }
                if ($request->filled('pattern_fee') && $request->pattern_fee > 0) {
                    $breakdown['pattern_fee'] = (float) $request->pattern_fee;
                }
                if ($request->filled('labor_cost') && $request->labor_cost > 0) {
                    $breakdown['labor_cost'] = (float) $request->labor_cost;
                }
                if ($request->filled('delivery_fee') && $request->delivery_fee > 0) {
                    $breakdown['delivery_fee'] = (float) $request->delivery_fee;
                }
                if ($request->filled('discount') && $request->discount > 0) {
                    $breakdown['discount'] = (float) $request->discount;
                }

                // Store as structured data
                $adminNotes = [
                    'breakdown' => $breakdown,
                    'notes' => $request->notes,
                    'quoted_at' => now()->toISOString(),
                ];
            }

            // Use model method for business logic
            $success = $order->quotePrice($request->price, $adminNotes);
            
            if ($success) {
                if ($request->filled('shipping_fee')) {
                    $order->shipping_fee = (float) $request->shipping_fee;
                }
                // Auto-progression: Update status to price_quoted
                $order->status = 'price_quoted';
                $order->save();
                
                // Notify user
                $order->notifyUser();
                
                \Log::info('Price quoted for custom order (auto-progressed to price_quoted)', [
                    'order_id' => $order->id,
                    'price' => $request->price,
                    'has_breakdown' => $hasBreakdown,
                    'user_id' => $order->user_id
                ]);
                
                // If it's an AJAX/JSON request, return JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Price quoted successfully. Status updated to "Price Quoted".',
                        'order' => $order->fresh()
                    ]);
                }

                // Otherwise redirect back with success message
                return redirect()->back()->with('success', 'Price quoted successfully. Status updated to "Price Quoted".');
            }
            
            // For non-success, respond appropriately based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be quoted in its current status'
                ], 422);
            }

            return redirect()->back()->with('error', 'Order cannot be quoted in its current status');
            
        } catch (\Exception $e) {
            \Log::error('Quote price error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to quote price: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to quote price: ' . $e->getMessage());
        }
    }

    public function updateBatchShipping(Request $request, CustomOrder $order)
    {
        $request->validate([
            'shipping_fee' => 'required|numeric|min:0',
        ]);

        try {
            $shippingFee = (float) $request->shipping_fee;
            $batchOrders = $this->resolveBatchOrders($order);

            foreach ($batchOrders as $batchItem) {
                $deliveryType = $batchItem->delivery_type ?? ($batchItem->delivery_address ? 'delivery' : 'pickup');
                $batchItem->shipping_fee = $deliveryType === 'pickup' ? 0 : $shippingFee;
                $batchItem->save();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batch shipping fee updated successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Batch shipping fee updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update batch shipping fee: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update batch shipping fee: ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request, CustomOrder $order)
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:paid,failed',
                'payment_notes' => 'nullable|string'
            ]);

            $paymentStatus = $request->payment_status;
            $notes = $request->payment_notes ?? '';
            $wasPaid = (string) $order->payment_status === 'paid';

            // Update payment status
            $order->payment_status = $paymentStatus;
            if ($notes) {
                $order->payment_notes = $notes;
            }

            if ($paymentStatus === 'paid') {
                if (\Schema::hasColumn('custom_orders', 'payment_verified_at')) {
                    $order->payment_verified_at = now();
                }
                if (\Schema::hasColumn('custom_orders', 'payment_confirmed_at')) {
                    $order->payment_confirmed_at = now();
                }
            }
            
            // Auto-progression: If payment verified, move to approved status
            if ($paymentStatus === 'paid') {
                $order->status = 'approved';
                \Log::info('Payment verified - Order auto-progressed to approved', [
                    'order_id' => $order->id,
                    'admin_id' => auth('admin')->id() ?? auth()->id()
                ]);
            } elseif ($paymentStatus === 'failed') {
                $order->status = 'cancelled';
            }
            
            $order->save();

            if ($paymentStatus === 'paid' && !$wasPaid) {
                $this->sendCustomOrderPaymentReceiptEmail($order);
            }

            \Log::info('Payment verified for custom order', [
                'order_id' => $order->id,
                'payment_status' => $paymentStatus,
                'new_status' => $order->status,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $paymentStatus === 'paid' 
                        ? 'Payment verified. Order automatically approved and ready for production.'
                        : 'Payment marked as failed.',
                    'order' => $order->fresh()
                ]);
            }

            return redirect()->back()->with('success', 'Payment status updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Payment verification validation error', [
                'order_id' => $order->id,
                'errors' => $e->errors()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Payment verification error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update payment status.');
        }
    }

    public function confirmPayment(CustomOrder $order)
    {
        // Extract auth_token for redirect preservation
        $authToken = request()->input('auth_token') ?? request()->query('auth_token');
        $prevUrl = url()->previous();
        if ($authToken && strpos($prevUrl, 'auth_token=') === false) {
            $redirectUrl = $prevUrl . (strpos($prevUrl, '?') !== false ? '&' : '?') . 'auth_token=' . urlencode($authToken);
        } else {
            $redirectUrl = $prevUrl;
        }
        
        try {
            // Also handle Maya/online_banking pending_verification orders
            if (in_array($order->payment_status, ['pending', 'pending_verification']) && in_array($order->payment_method, ['maya', 'online_banking'])) {
                $order->payment_status       = 'paid';
                $order->payment_confirmed_at = now();
                $order->status              = 'processing';
                $order->save();
                $this->sendCustomOrderPaymentReceiptEmail($order);
                // Append ?paid=1 to ensure feedback survives token-auth session resets
                $sep = strpos($redirectUrl, '?') !== false ? '&' : '?';
                return redirect($redirectUrl . $sep . 'paid=1');
            }

            // Verify the order is in the correct state for payment confirmation
            // Accept both 'approved' and 'processing' statuses (processing is set when payment proof uploaded)
            if (!in_array($order->status, ['approved', 'processing'])) {
                return redirect($redirectUrl)->with('error', 'Order must be in approved or processing status to confirm payment.');
            }

            if ($order->payment_status !== 'paid' || !$order->payment_receipt) {
                return redirect($redirectUrl)->with('error', 'Payment receipt must be uploaded before confirmation.');
            }

            // Confirm payment by adding a timestamp - payment_status stays 'paid'
            // (custom_orders enum only has: pending, paid, failed - no 'verified')
            $order->payment_confirmed_at = now();
            $order->save();
            $this->sendCustomOrderPaymentReceiptEmail($order);

            \Log::info('Payment confirmed for custom order', [
                'order_id' => $order->id,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            return redirect($redirectUrl)->with('success', 'Payment confirmed. You can now start production.');
        } catch (\Exception $e) {
            \Log::error('Payment confirmation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect($redirectUrl)->with('error', 'Failed to confirm payment: ' . $e->getMessage());
        }
    }

    private function sendCustomOrderPaymentReceiptEmail(CustomOrder $order): void
    {
        try {
            $order->loadMissing('user');

            $recipientEmail = trim((string) (
                optional($order->user)->email
                ?: $order->email
            ));

            if ($recipientEmail === '') {
                return;
            }

            $totalAmount = (float) ($order->final_price ?? $order->estimated_price ?? 0);

            TransactionalMailService::sendView(
                $recipientEmail,
                'Custom Order Payment Receipt - ' . $order->display_ref,
                'emails.custom-orders.payment-receipt',
                [
                    'order' => $order,
                    'user' => $order->user,
                    'itemCount' => 1,
                    'totalAmount' => max(0, $totalAmount),
                ]
            );
        } catch (\Throwable $exception) {
            \Log::warning('Admin custom-order payment receipt email failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function rejectPayment(CustomOrder $order)
    {
        // Extract auth_token for redirect preservation
        $authToken = request()->input('auth_token') ?? request()->query('auth_token');
        $redirectUrl = $authToken ? url()->previous() . (strpos(url()->previous(), '?') !== false ? '&' : '?') . 'auth_token=' . urlencode($authToken) : url()->previous();
        
        try {
            // Verify the order is in the correct state for payment rejection
            // Accept both 'approved' and 'processing' statuses (processing is set when payment proof uploaded)
            if (!in_array($order->status, ['approved', 'processing'])) {
                return redirect($redirectUrl)->with('error', 'Order must be in approved or processing status to reject payment.');
            }

            if (!$order->payment_receipt) {
                return redirect($redirectUrl)->with('error', 'No payment receipt found to reject.');
            }

            // Reject payment and set status back for customer to resubmit
            $order->payment_status = 'failed';
            $order->payment_receipt = null; // Clear the rejected receipt
            $order->status = 'price_quoted'; // Revert to quoted status for customer to resubmit
            $order->save();

            \Log::info('Payment rejected for custom order', [
                'order_id' => $order->id,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            return redirect($redirectUrl)->with('success', 'Payment rejected. Customer will need to resubmit payment.');
        } catch (\Exception $e) {
            \Log::error('Payment rejection error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect($redirectUrl)->with('error', 'Failed to reject payment: ' . $e->getMessage());
        }
    }
    
    public function rejectOrder(Request $request, CustomOrder $order)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $oldStatus = $order->status;
            $order->status = 'rejected';
            $order->rejected_at = now();
            $order->rejection_reason = $request->rejection_reason ?? 'Order rejected by admin';
            $order->save();
            
            \Log::info('Custom order rejected', [
                'order_id' => $order->id,
                'reason' => $order->rejection_reason,
                'user_id' => $order->user_id
            ]);

            // Send notifications
            if ($oldStatus !== 'rejected') {
                $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
                $notificationService->notifyCustomOrderStatusChange($order, $oldStatus, 'rejected');
            }
            
            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order rejected successfully. Customer has been notified.',
                    'order' => $order->fresh()
                ]);
            }
            
            // Otherwise redirect back with success message
            return redirect()->back()->with('success', 'Order rejected successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Reject order error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject order: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer about production delay
     */
    public function notifyDelay(Request $request, CustomOrder $order)
    {
        $request->validate([
            'delay_reason' => 'required|string|max:1000'
        ]);
        
        try {
            $order->is_delayed = true;
            $order->delay_reason = $request->delay_reason;
            $order->delay_notified_at = now();
            $order->save();
            
            \Log::info('Production delay notification sent', [
                'order_id' => $order->id,
                'reason' => $order->delay_reason,
                'user_id' => $order->user_id
            ]);

            // Send email notification to customer
            try {
                $user = $order->user;
                if ($user && $user->email) {
                    \Mail::to($user->email)->send(new \App\Mail\CustomOrderDelayNotification($order));
                    \Log::info('Delay email sent', ['order_id' => $order->id, 'email' => $user->email]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send delay email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delay notification sent to customer successfully.',
                    'order' => $order->fresh()
                ]);
            }
            
            return redirect()->back()->with('success', 'Delay notification sent successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Notify delay error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send delay notification: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to send delay notification: ' . $e->getMessage());
        }
    }

    /**
     * Clear delay status
     */
    public function clearDelay(Request $request, CustomOrder $order)
    {
        try {
            $order->is_delayed = false;
            $order->delay_reason = null;
            $order->save();
            
            \Log::info('Delay status cleared', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delay status cleared successfully.',
                    'order' => $order->fresh()
                ]);
            }
            
            return redirect()->back()->with('success', 'Delay status cleared successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Clear delay error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear delay status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to clear delay status: ' . $e->getMessage());
        }
    }

    public function approveOrder(CustomOrder $order)
    {
        try {
            $oldStatus = $order->status;
            $notificationService = new \App\Services\Notification\OrderStatusNotificationService();

            // Approve this order
            $order->status = 'approved';
            $order->approved_at = now();
            $order->save();

            if ($oldStatus !== 'approved') {
                $notificationService->notifyCustomOrderStatusChange($order, $oldStatus, 'approved');
            }

            // If part of a batch, approve all sibling orders too
            if (!empty($order->batch_order_number)) {
                $siblings = \App\Models\CustomOrder::where('batch_order_number', $order->batch_order_number)
                    ->where('id', '!=', $order->id)
                    ->whereNotIn('status', ['approved', 'in_production', 'production_complete', 'out_for_delivery', 'delivered', 'completed', 'cancelled'])
                    ->get();

                foreach ($siblings as $sibling) {
                    $siblingOldStatus = $sibling->status;
                    $sibling->status = 'approved';
                    $sibling->approved_at = now();
                    $sibling->save();

                    $notificationService->notifyCustomOrderStatusChange($sibling, $siblingOldStatus, 'approved');
                }
            }

            // If it's an AJAX request, return JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order approved successfully',
                    'order' => $order
                ]);
            }

            // Otherwise redirect back with success message
            return redirect()->back()->with('success', 'Order approved successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve order: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    public function destroy(CustomOrder $order)
    {
        try {
            // Check if order can be deleted (only pending or cancelled orders)
            if (!in_array($order->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete order that is ' . $order->status
                ], 422);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportOrders(Request $request)
    {
        try {
            $query = CustomOrder::with(['user', 'product']);

            // Apply same filters as index
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $orders = $query->get();

            $csvData = [];
            $csvData[] = ['Order ID', 'Customer Name', 'Customer Email', 'Product', 'Status', 'Price', 'Quantity', 'Created Date'];

            foreach ($orders as $order) {
                $csvData[] = [
                    $order->id,
                    $order->user->name ?? 'Guest',
                    $order->user->email ?? 'N/A',
                    $order->product->name ?? 'Custom Product',
                    ucfirst(str_replace('_', ' ', $order->status)),
                    $order->final_price ? '₱' . number_format($order->final_price, 2) : 'Not set',
                    $order->quantity ?? 1,
                    $order->created_at->format('Y-m-d H:i:s')
                ];
            }

            $filename = 'custom_orders_' . date('Y-m-d_H-i-s') . '.csv';
            
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvData[0]);
            
            for ($i = 1; $i < count($csvData); $i++) {
                fputcsv($handle, $csvData[$i]);
            }
            
            fclose($handle);

            return response()->streamDownload(function() use ($csvData) {
                $output = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($output, $row);
                }
                fclose($output);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show admin custom order creation landing page
     */
    public function create()
    {
        return redirect()->route('admin.custom-orders.create.choice');
    }

    /**
     * Show custom order type choice selection for admin
     */
    public function createChoice(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            return view('admin.custom_orders.wizard.choice');
            
        } catch (\Exception $e) {
            \Log::error('Admin choice selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.index')
                ->with('error', 'Unable to load custom order options. Please try again.');
        }
    }

    /**
     * Show product selection for admin product-based customization
     */
    public function createProductSelection(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            // Get available products for customization
            $products = \App\Models\Product::where('status', 'active')
                ->orderBy('name')
                ->get();

            // Get users for assignment
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();

            return view('admin.custom_orders.wizard.product_selection', compact('products', 'users'));
            
        } catch (\Exception $e) {
            \Log::error('Admin product selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.choice')
                ->with('error', 'Unable to load products. Please try again.');
        }
    }

    /**
     * Store admin product selection and redirect to customization
     */
    public function storeProductSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string',
                'product_category' => 'nullable|string',
                'product_price' => 'nullable|numeric|min:0',
            ]);

            // Store product selection in admin session
            $wizardData = [
                'user_id' => $validated['user_id'],
                'product' => [
                    'id' => $validated['product_id'],
                    'name' => $validated['product_name'],
                    'category' => $validated['product_category'],
                    'price' => $validated['product_price'],
                ],
                'step' => 'product_selected',
                'created_at' => now(),
            ];

            $request->session()->put('admin_wizard', $wizardData);
            \Log::info('Admin product stored in wizard session', ['product' => $validated['product_name']]);

            return redirect()->route('admin.custom-orders.create.product.customize');
            
        } catch (\Exception $e) {
            \Log::error('Admin store product selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.product')
                ->with('error', 'Unable to select product. Please try again.');
        }
    }

    /**
     * Show admin product customization page (patterns and colors)
     */
    public function createProductCustomization(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData || !isset($wizardData['product'])) {
                return redirect()->route('admin.custom-orders.create.product')
                    ->with('error', 'Please select a product first.');
            }

            // Get product details
            $product = \App\Models\Product::find($wizardData['product']['id']);
            $user = \App\Models\User::find($wizardData['user_id']);
            
            return view('admin.custom_orders.wizard.step2', [
                'product' => $product,
                'user' => $user,
                'isAdminFlow' => true // Flag to indicate this is admin flow
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin product customization error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.product')
                ->with('error', 'Unable to load customization options. Please try again.');
        }
    }

    /**
     * Store admin product customization (patterns and colors)
     */
    public function storeProductCustomization(Request $request)
    {
        try {
            $validated = $request->validate([
                'pattern' => 'required|string',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
                'pattern_data' => 'nullable|array',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'estimated_price' => 'nullable|numeric|min:0',
            ]);

            $wizardData = $request->session()->get('admin_wizard');
            $wizardData['pattern'] = $validated['pattern'];
            $wizardData['colors'] = $validated['colors'];
            $wizardData['pattern_data'] = $validated['pattern_data'] ?? [];
            $wizardData['quantity'] = $validated['quantity'];
            $wizardData['notes'] = $validated['notes'] ?? '';
            $wizardData['estimated_price'] = $validated['estimated_price'] ?? 0;
            $wizardData['step'] = 'customization_complete';
            
            $request->session()->put('admin_wizard', $wizardData);
            
            \Log::info('Admin product customization stored', [
                'product' => $wizardData['product']['name'] ?? 'Unknown',
                'pattern' => $validated['pattern']
            ]);

            return redirect()->route('admin.custom-orders.create.review');
            
        } catch (\Exception $e) {
            \Log::error('Admin store product customization error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.product.customize')
                ->with('error', 'Unable to save customization. Please try again.');
        }
    }

    /**
     * Show fabric selection for admin fabric-first flow
     */
    public function createFabricSelection(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            // Get users for assignment
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();

            return view('admin.custom_orders.wizard.fabric_selection', compact('users'));
            
        } catch (\Exception $e) {
            \Log::error('Admin fabric selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.choice')
                ->with('error', 'Unable to load fabric selection. Please try again.');
        }
    }

    /**
     * Store admin fabric selection
     */
    public function storeFabricSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'fabric_type' => 'required|string',
                'fabric_quantity_meters' => 'required|numeric|min:0.5',
                'intended_use' => 'required|string',
            ]);

            $wizardData = [
                'user_id' => $validated['user_id'],
                'fabric' => [
                    'type' => $validated['fabric_type'],
                    'quantity_meters' => $validated['fabric_quantity_meters'],
                    'intended_use' => $validated['intended_use'],
                ],
                'step' => 'fabric_selected',
                'created_at' => now(),
            ];

            $request->session()->put('admin_wizard', $wizardData);

            return redirect()->route('admin.custom-orders.create.pattern');
            
        } catch (\Exception $e) {
            \Log::error('Admin store fabric selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.fabric')
                ->with('error', 'Unable to save fabric selection. Please try again.');
        }
    }

    /**
     * Show pattern selection for admin fabric-first flow
     */
    public function createPatternSelection(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData || !isset($wizardData['fabric'])) {
                return redirect()->route('admin.custom-orders.create.fabric')
                    ->with('error', 'Please select fabric first.');
            }

            $user = \App\Models\User::find($wizardData['user_id']);
            
            // Get active patterns from database with their media
            $patterns = \App\Models\YakanPattern::with('media')
                ->where('is_active', true)
                ->orderBy('popularity_score', 'desc')
                ->orderBy('name', 'asc')
                ->get();
            
            return view('admin.custom_orders.wizard.step2', [
                'user' => $user,
                'patterns' => $patterns,
                'isAdminFlow' => true,
                'isFabricFlow' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin pattern selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.fabric')
                ->with('error', 'Unable to load pattern selection. Please try again.');
        }
    }

    /**
     * Store admin pattern selection
     */
    public function storePatternSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'pattern' => 'required|string',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
                'pattern_data' => 'nullable|array',
                'notes' => 'nullable|string',
                'estimated_price' => 'nullable|numeric|min:0',
            ]);

            $wizardData = $request->session()->get('admin_wizard');
            $wizardData['pattern'] = $validated['pattern'];
            $wizardData['colors'] = $validated['colors'];
            $wizardData['pattern_data'] = $validated['pattern_data'] ?? [];
            $wizardData['notes'] = $validated['notes'] ?? '';
            $wizardData['estimated_price'] = $validated['estimated_price'] ?? 0;
            $wizardData['step'] = 'pattern_complete';
            
            $request->session()->put('admin_wizard', $wizardData);

            return redirect()->route('admin.custom-orders.create.review');
            
        } catch (\Exception $e) {
            \Log::error('Admin store pattern selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.pattern')
                ->with('error', 'Unable to save pattern selection. Please try again.');
        }
    }

    /**
     * Show review and final creation page for admin
     */
    public function createReview(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData) {
                return redirect()->route('admin.custom-orders.create.choice')
                    ->with('error', 'No order data found. Please start over.');
            }

            $user = \App\Models\User::find($wizardData['user_id']);
            
            // Get user's saved addresses
            $userAddresses = \App\Models\UserAddress::where('user_id', $wizardData['user_id'])
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            $defaultAddress = \App\Models\UserAddress::where('user_id', $wizardData['user_id'])
                ->where('is_default', true)
                ->first();
            
            return view('admin.custom_orders.wizard.review', compact('wizardData', 'user', 'userAddresses', 'defaultAddress'));
            
        } catch (\Exception $e) {
            \Log::error('Admin review error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.choice')
                ->with('error', 'Unable to load review page. Please try again.');
        }
    }

    /**
     * Store the complete admin custom order
     */
    public function store(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData) {
                return redirect()->route('admin.custom-orders.create.choice')
                    ->with('error', 'No order data found. Please start over.');
            }

            // Create the custom order
            $customOrder = \App\Models\CustomOrder::create([
                'user_id' => $wizardData['user_id'],
                'product_id' => $wizardData['product']['id'] ?? null,
                'fabric_type' => $wizardData['fabric']['type'] ?? null,
                'fabric_quantity_meters' => $wizardData['fabric']['quantity_meters'] ?? null,
                'intended_use' => $wizardData['fabric']['intended_use'] ?? null,
                'pattern' => $wizardData['pattern'] ?? null,
                'colors' => json_encode($wizardData['colors'] ?? []),
                'pattern_data' => json_encode($wizardData['pattern_data'] ?? []),
                'quantity' => $wizardData['quantity'] ?? 1,
                'notes' => $wizardData['notes'] ?? '',
                'estimated_price' => $wizardData['estimated_price'] ?? 0,
                'status' => 'pending',
                'admin_created' => true,
                'created_by' => auth()->guard('admin')->id(),
            ]);

            // Clear admin wizard session
            $request->session()->forget('admin_wizard');

            \Log::info('Admin custom order created', ['order_id' => $customOrder->id]);

            return redirect()->route('admin.custom-orders.show', $customOrder)
                ->with('success', 'Custom order created successfully!');

        } catch (\Exception $e) {
            \Log::error('Admin store custom order error: ' . $e->getMessage());
            return redirect()->route('admin.custom-orders.create.review')
                ->with('error', 'Unable to create custom order. Please try again.');
        }
    }

    /**
     * Update order details
     */
    public function update(Request $request, CustomOrder $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
                'final_price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $order->update($validated);

            \Log::info('Admin updated order', ['order_id' => $order->id, 'changes' => $validated]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin update order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as completed
     */
    public function markCompleted(Request $request, CustomOrder $order)
    {
        try {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            \Log::info('Admin marked order as completed', ['order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin mark completed error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to mark order as completed'
            ], 500);
        }
    }

    /**
     * Send notification to customer
     */
    public function sendNotification(Request $request, CustomOrder $order)
    {
        try {
            // Send email notification to customer
            if ($order->user && $order->user->email) {
                // Here you would implement email sending logic
                // Mail::to($order->user->email)->send(new OrderNotification($order));
                
                \Log::info('Admin sent notification to customer', ['order_id' => $order->id, 'email' => $order->user->email]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin send notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send notification'
            ], 500);
        }
    }

    /**
     * Bulk operations
     */
    public function bulkApprove(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)
                ->where('status', 'price_quoted')
                ->update(['status' => 'processing']);

            \Log::info('Admin bulk approved orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk approve orders'
            ], 500);
        }
    }

    public function bulkReject(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)
                ->whereIn('status', ['pending', 'price_quoted'])
                ->update(['status' => 'cancelled']);

            \Log::info('Admin bulk rejected orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk reject orders'
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)->delete();

            \Log::info('Admin bulk deleted orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk delete orders'
            ], 500);
        }
    }

    /**
     * Enhanced index with advanced filtering
     */
    public function indexEnhanced(Request $request)
    {
        try {
            $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

            // For named batch orders, only surface the primary (lowest id) row per batch
            $batchPrimaryIds = collect();
            $implicitCountMap = [];
            $implicitExcludeIds = collect();

            if ($hasBatchColumn) {
                $batchPrimaryIds = CustomOrder::select(\DB::raw('MIN(id) as primary_id'))
                    ->whereNotNull('batch_order_number')
                    ->where('batch_order_number', '!=', '')
                    ->groupBy('batch_order_number')
                    ->pluck('primary_id');

                // Detect "implicit batches": same user_id + same minute, no batch_order_number set
                $implicitGroups = \DB::table('custom_orders')
                    ->select(
                        'user_id',
                        \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as minute_key"),
                        \DB::raw('MIN(id) as primary_id'),
                        \DB::raw('COUNT(*) as cnt')
                    )
                    ->where(function ($q) {
                        $q->whereNull('batch_order_number')->orWhere('batch_order_number', '');
                    })
                    ->whereNotNull('user_id')
                    ->groupBy('user_id', \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')"))
                    ->havingRaw('cnt > 1')
                    ->get();

                // Build a map of primary_id => count for implicit batches (for badge display)
                $implicitCountMap = $implicitGroups->pluck('cnt', 'primary_id')->toArray();

                // Collect IDs of non-primary members of implicit batches (to exclude from list)
                foreach ($implicitGroups as $group) {
                    $memberIds = \DB::table('custom_orders')
                        ->where('user_id', $group->user_id)
                        ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$group->minute_key])
                        ->where(function ($q) {
                            $q->whereNull('batch_order_number')->orWhere('batch_order_number', '');
                        })
                        ->where('id', '!=', $group->primary_id)
                        ->pluck('id');
                    $implicitExcludeIds = $implicitExcludeIds->merge($memberIds);
                }
            }

            $query = CustomOrder::with(['user', 'product'])
                ->when($hasBatchColumn && $batchPrimaryIds->isNotEmpty(), function ($q) use ($batchPrimaryIds) {
                    $q->where(function ($inner) use ($batchPrimaryIds) {
                        $inner->whereNull('batch_order_number')
                              ->orWhere('batch_order_number', '')
                              ->orWhereIn('id', $batchPrimaryIds);
                    });
                })
                ->when($implicitExcludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $implicitExcludeIds))
                ->orderBy('created_at', 'desc');

            // Advanced filtering
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('customer_type') && $request->customer_type) {
                if ($request->customer_type === 'admin_created') {
                    $query->where('admin_created', true);
                } elseif ($request->customer_type === 'user_created') {
                    $query->where('admin_created', false);
                }
            }

            if ($request->has('price_range') && $request->price_range) {
                $range = $request->price_range;
                if ($range === '0-1000') {
                    $query->where(function($q) {
                        $q->where('final_price', '<=', 1000)
                          ->orWhere('estimated_price', '<=', 1000);
                    });
                } elseif ($range === '1000-5000') {
                    $query->where(function($q) {
                        $q->whereBetween('final_price', [1000, 5000])
                          ->orWhereBetween('estimated_price', [1000, 5000]);
                    });
                } elseif ($range === '5000-10000') {
                    $query->where(function($q) {
                        $q->whereBetween('final_price', [5000, 10000])
                          ->orWhereBetween('estimated_price', [5000, 10000]);
                    });
                } elseif ($range === '10000+') {
                    $query->where(function($q) {
                        $q->where('final_price', '>', 10000)
                          ->orWhere('estimated_price', '>', 10000);
                    });
                }
            }

            // Sorting
            if ($request->has('sort')) {
                switch ($request->sort) {
                    case 'created_at_asc':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'price_high':
                        $query->orderByRaw('COALESCE(final_price, estimated_price, 0) DESC');
                        break;
                    case 'price_low':
                        $query->orderByRaw('COALESCE(final_price, estimated_price, 0) ASC');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            }

            $perPage = $request->get('per_page', 20);
            $orders = $query->paginate($perPage);
            $this->attachResolvedReferenceImages($orders->getCollection());

            // Build maps for named batches on this page
            $batchCountMap = [];
            $batchMetaMap = [];
            if ($hasBatchColumn) {
                $batchNumbers = $orders->pluck('batch_order_number')->filter()->unique()->values();
                if ($batchNumbers->isNotEmpty()) {
                    $batchCountMap = CustomOrder::select('batch_order_number', \DB::raw('COUNT(*) as cnt'))
                        ->whereIn('batch_order_number', $batchNumbers)
                        ->groupBy('batch_order_number')
                        ->pluck('cnt', 'batch_order_number')
                        ->toArray();

                    $batchRows = CustomOrder::query()
                        ->whereIn('batch_order_number', $batchNumbers)
                        ->get()
                        ->groupBy('batch_order_number');

                    $batchMetaMap = $batchRows
                        ->map(function ($rows) {
                            return [
                                'item_count' => $rows->count(),
                                'batch_total' => $this->calculateBatchDisplayTotal($rows),
                            ];
                        })
                        ->toArray();
                }
            }
            
            // Build sibling map for sub-row display
            $batchSiblingsMap = collect();
            if ($hasBatchColumn && !empty($batchCountMap)) {
                $batchNumbersWithMultiple = collect($batchCountMap)->filter(fn($cnt) => $cnt > 1)->keys();
                if ($batchNumbersWithMultiple->isNotEmpty()) {
                    $primaryIds = $orders->pluck('id')->toArray();
                    $batchSiblingsMap = CustomOrder::with(['user:id,name,email'])
                        ->whereIn('batch_order_number', $batchNumbersWithMultiple)
                        ->whereNotIn('id', $primaryIds)
                        ->orderBy('id')
                        ->get()
                        ->groupBy('batch_order_number');
                }
            }

            // Calculate statistics
            $totalOrders = CustomOrder::count();
            $todayOrders = CustomOrder::whereDate('created_at', today())->count();
            $pendingCount = CustomOrder::where('status', 'pending')->count();
            $totalRevenue = CustomOrder::where('payment_status', 'paid')->sum('final_price');
            
            $stats = compact('totalOrders', 'todayOrders', 'pendingCount', 'totalRevenue');
            
            return view('admin.custom_orders.index_enhanced', compact('orders', 'stats', 'batchCountMap', 'batchMetaMap', 'implicitCountMap', 'batchSiblingsMap'));
            
        } catch (\Exception $e) {
            \Log::error('Enhanced Custom Orders Index Error: ' . $e->getMessage());
            return 'Custom Orders Error: ' . $e->getMessage();
        }
    }

    /**
     * Production dashboard
     */
    public function productionDashboard(Request $request)
    {
        try {
            $stats = [
                'total_orders' => CustomOrder::count(),
                'pending_orders' => CustomOrder::where('status', 'pending')->count(),
                'processing_orders' => CustomOrder::where('status', 'processing')->count(),
                'completed_orders' => CustomOrder::where('status', 'completed')->count(),
                'total_revenue' => CustomOrder::where('status', 'completed')->sum('final_price'),
                'avg_processing_time' => $this->calculateAverageProcessingTime(),
            ];

            $recentOrders = CustomOrder::with(['user', 'product'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('admin.custom_orders.production_dashboard', compact('stats', 'recentOrders'));
            
        } catch (\Exception $e) {
            \Log::error('Production Dashboard Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load production dashboard');
        }
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime()
    {
        try {
            $completedOrders = CustomOrder::where('status', 'completed')
                ->whereNotNull('completed_at')
                ->get();

            if ($completedOrders->isEmpty()) {
                return 0;
            }

            $totalMinutes = $completedOrders->sum(function($order) {
                return $order->created_at->diffInMinutes($order->completed_at);
            });

            return round($totalMinutes / $completedOrders->count(), 2);
            
        } catch (\Exception $e) {
            return 0;
        }
    }
}


