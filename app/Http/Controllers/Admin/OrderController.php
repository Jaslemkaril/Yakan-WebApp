<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\CustomOrder;
use App\Models\CustomOrderRefundRequest;
use App\Services\Payment\PayMongoCheckoutService;
use App\Services\TransactionalMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
{
    // Show all orders
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product', 'orderItems.variant'])->orderByDesc('created_at');

        // Advanced filtering
        if ($request->filled('status')) {
            $statusFilter = strtolower(trim((string) $request->status));
            if ($statusFilter === 'done') {
                $query->whereIn('status', ['delivered', 'completed']);
            } else {
                $query->where('status', $request->status);
            }
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
        $order->load('user', 'userAddress', 'orderItems.product.category', 'orderItems.product.bundleItems.componentProduct', 'orderItems.variant');

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
     * Dedicated cancellation request dashboard.
     */
    public function cancelRequests(Request $request)
    {
        $statusFilter = strtolower((string) $request->query('status', 'all'));
        if (!in_array($statusFilter, ['all', 'pending', 'approved', 'rejected'], true)) {
            $statusFilter = 'all';
        }

        $hasRefundTable = Schema::hasTable('order_refund_requests');
        $hasRefundCommentColumn = $hasRefundTable && Schema::hasColumn('order_refund_requests', 'comment');
        $hasRefundWorkflowColumn = $hasRefundTable && Schema::hasColumn('order_refund_requests', 'workflow_status');

        $baseQuery = Order::query()
            ->where(function ($query) use ($hasRefundTable, $hasRefundCommentColumn) {
                $query->where('status', 'cancellation_requested')
                    ->orWhere('admin_notes', 'like', '%cancel%')
                    ->orWhere('notes', 'like', '%cancel%');

                if ($hasRefundTable) {
                    $query->orWhereHas('refundRequests', function ($refundQuery) use ($hasRefundCommentColumn) {
                        $refundQuery->where('reason', 'like', '%cancel%')
                            ->orWhere('details', 'like', '%cancel%');

                        if ($hasRefundCommentColumn) {
                            $refundQuery->orWhere('comment', 'like', '%cancel%');
                        }
                    });
                }
            });

        $totalRequests = (clone $baseQuery)->count();
        $pendingRequests = (clone $baseQuery)
            ->where('status', 'cancellation_requested')
            ->count();
        $resolvedToday = (clone $baseQuery)
            ->where('status', '!=', 'cancellation_requested')
            ->whereDate('updated_at', today())
            ->count();

        $ordersQuery = Order::with(['user', 'refundRequests' => function ($query) {
            $query->latest();
        }])->where(function ($query) use ($hasRefundTable, $hasRefundCommentColumn) {
            $query->where('status', 'cancellation_requested')
                ->orWhere('admin_notes', 'like', '%cancel%')
                ->orWhere('notes', 'like', '%cancel%');

            if ($hasRefundTable) {
                $query->orWhereHas('refundRequests', function ($refundQuery) use ($hasRefundCommentColumn) {
                    $refundQuery->where('reason', 'like', '%cancel%')
                        ->orWhere('details', 'like', '%cancel%');

                    if ($hasRefundCommentColumn) {
                        $refundQuery->orWhere('comment', 'like', '%cancel%');
                    }
                });
            }
        });

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('order_ref', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter === 'pending') {
            $ordersQuery->where('status', 'cancellation_requested');
        } elseif ($statusFilter === 'approved') {
            $ordersQuery->where(function ($query) use ($hasRefundTable, $hasRefundWorkflowColumn) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereIn('status', ['cancelled', 'refunded'])
                        ->where(function ($notesQuery) {
                            $notesQuery->where('admin_notes', 'like', '%approved%')
                                ->orWhere('notes', 'like', '%approved%');
                        });
                });

                if ($hasRefundTable) {
                    $query->orWhereHas('refundRequests', function ($refundQuery) use ($hasRefundWorkflowColumn) {
                        $refundQuery->whereIn('status', ['approved', 'processed']);

                        if ($hasRefundWorkflowColumn) {
                            $refundQuery->orWhereIn('workflow_status', ['pending_payout', 'approved', 'processed']);
                        }
                    });
                }
            });
        } elseif ($statusFilter === 'rejected') {
            $ordersQuery->where(function ($query) use ($hasRefundTable, $hasRefundWorkflowColumn) {
                $query->where('admin_notes', 'like', '%rejected%')
                    ->orWhere('notes', 'like', '%rejected%');

                if ($hasRefundTable) {
                    $query->orWhereHas('refundRequests', function ($refundQuery) use ($hasRefundWorkflowColumn) {
                        $refundQuery->where('status', 'rejected');

                        if ($hasRefundWorkflowColumn) {
                            $refundQuery->orWhere('workflow_status', 'rejected');
                        }
                    });
                }
            });
        }

        $orders = $ordersQuery
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.cancel-requests', compact(
            'orders',
            'statusFilter',
            'totalRequests',
            'pendingRequests',
            'resolvedToday'
        ));
    }

    /**
     * Unified post-order requests (regular + custom, cancel + refund).
     */
    public function postOrderRequests(Request $request)
    {
        $typeFilter = strtolower((string) $request->query('type', 'all'));
        if (!in_array($typeFilter, ['all', 'cancel', 'refund'], true)) {
            $typeFilter = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        $perPage = max(1, min(50, (int) $request->query('per_page', 15)));
        $page = max(1, (int) $request->query('page', 1));

        $rows = collect();

        // Regular cancellation requests.
        $regularCancelRows = $this->buildRegularCancelRequestRows();

        // Custom cancellation-equivalent requests (request_type=return).
        $customCancelRows = collect();
        if (Schema::hasTable('custom_order_refund_requests')) {
            $customCancelRows = CustomOrderRefundRequest::with(['customOrder.user', 'user'])
                ->where('request_type', 'return')
                ->latest('requested_at')
                ->get()
                ->map(function (CustomOrderRefundRequest $refundRequest) {
                    $order = $refundRequest->customOrder;
                    if (!$order) {
                        return null;
                    }

                    $statusKey = strtolower((string) $refundRequest->status);
                    $normalizedStatus = match ($statusKey) {
                        'approved', 'processed' => 'approved',
                        'rejected' => 'rejected',
                        default => 'pending',
                    };

                    $reason = trim((string) ($refundRequest->reason ?: $refundRequest->details ?: 'Customer requested cancellation'));
                    $reasonShort = Str::limit($reason, 45, '...');

                    $modalPayload = [
                        'order_id' => '#C-' . $order->id,
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'N/A'),
                        'refund_amount' => number_format((float) ($order->final_price ?? 0), 2),
                        'payment_method' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'N/A'))),
                        'order_status' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'N/A'))),
                        'cancel_reason' => $reason,
                        'customer_note' => trim((string) ($refundRequest->details ?: '')),
                        'status_state' => $normalizedStatus,
                        'order_show_url' => route('admin.custom-orders.show', $order),
                        'approve_url' => route('admin.custom-orders.refund_requests.approve', $refundRequest),
                        'reject_url' => route('admin.custom-orders.refund_requests.reject', $refundRequest),
                        'is_custom' => true,
                    ];

                    return [
                        'created_at' => $refundRequest->requested_at ?? $refundRequest->created_at,
                        'row_id' => 'custom-cancel-' . $refundRequest->id,
                        'display_id' => '#C-' . $order->id,
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'N/A'),
                        'type' => 'cancel',
                        'order_type' => 'custom',
                        'amount' => (float) ($order->final_price ?? 0),
                        'status_key' => $normalizedStatus,
                        'status_label' => ucfirst($normalizedStatus),
                        'view_url' => route('admin.custom-orders.show', $order),
                        'action_kind' => 'cancel_modal',
                        'cancel_payload' => $modalPayload,
                        'reason' => $reasonShort,
                    ];
                })
                ->filter();
        }

        $cancelRows = $regularCancelRows->concat($customCancelRows)->values();

        // Regular refund requests.
        $regularRefundRows = collect();
        if (Schema::hasTable('order_refund_requests')) {
            $regularRefundRows = OrderRefundRequest::with(['order.user', 'user'])
                ->whereHas('order')
                ->where(function ($builder) {
                    $builder->whereRaw('LOWER(COALESCE(reason, "")) NOT LIKE ?', ['%cancel%'])
                        ->whereRaw('LOWER(COALESCE(comment, "")) NOT LIKE ?', ['%cancel%'])
                        ->whereRaw('LOWER(COALESCE(details, "")) NOT LIKE ?', ['%cancel%']);
                })
                ->latest('requested_at')
                ->get()
                ->map(function (OrderRefundRequest $refundRequest) {
                    $order = $refundRequest->order;
                    if (!$order) {
                        return null;
                    }

                    $status = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
                    $normalizedStatus = match (true) {
                        in_array($status, ['rejected'], true) => 'rejected',
                        in_array($status, ['pending_payout', 'return_received', 'approved', 'processed'], true)
                            || strtolower((string) $refundRequest->payout_status) === 'completed' => 'refunded',
                        in_array($status, ['awaiting_return_shipment', 'return_in_transit'], true) => 'awaiting_return',
                        default => 'under_review',
                    };

                    $rawEvidence = $refundRequest->evidence_paths;
                    if (is_string($rawEvidence) && $rawEvidence !== '') {
                        $decodedEvidence = json_decode($rawEvidence, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedEvidence)) {
                            $rawEvidence = $decodedEvidence;
                        }
                    }
                    $evidenceList = array_values(array_filter(is_array($rawEvidence) ? $rawEvidence : [], fn ($value) => $value !== null && $value !== ''));
                    $evidencePreviews = [];
                    foreach ($evidenceList as $evidencePath) {
                        $ext = strtolower(pathinfo(parse_url((string) $evidencePath, PHP_URL_PATH) ?? (string) $evidencePath, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
                        $isVideo = in_array($ext, ['mp4', 'mov', 'webm'], true);
                        $url = route('admin.orders.refund_evidence.view', ['refundRequest' => $refundRequest->id, 'index' => count($evidencePreviews)]);

                        // If path is already a full URL (e.g., Cloudinary), use it directly
                        if (str_starts_with((string) $evidencePath, 'http://') || str_starts_with((string) $evidencePath, 'https://')) {
                            $previewUrl = (string) $evidencePath;
                        } else {
                            // For local storage paths, normalize and generate public storage URL
                            $cleanPath = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], (string) $evidencePath), '/');
                            $previewUrl = asset('storage/' . $cleanPath);
                        }

                        $evidencePreviews[] = [
                            'url' => $url,
                            'open_url' => $previewUrl,
                            'preview_url' => $previewUrl,
                            'fallback_url' => $previewUrl,
                            'is_image' => $isImage,
                            'is_video' => $isVideo,
                        ];
                    }

                    $statusLabel = match ($normalizedStatus) {
                        'under_review' => 'Under review',
                        'awaiting_return' => 'Awaiting return',
                        'refunded' => 'Refunded',
                        'rejected' => 'Rejected',
                        default => Str::headline($normalizedStatus),
                    };

                    // Calculate order amount with proper zero-skipping
                    $orderAmount = 0;
                    foreach ([
                        $refundRequest->refund_amount,
                        $refundRequest->approved_amount,
                        $refundRequest->recommended_refund_amount,
                        $order->total_amount,
                        $order->total,
                        (($order->subtotal ?? 0) + ($order->shipping_fee ?? 0)),
                    ] as $candidate) {
                        if ((float) $candidate > 0) {
                            $orderAmount = (float) $candidate;
                            break;
                        }
                    }

                    $modalPayload = [
                        'refund_id' => (string) ($refundRequest->refund_reference ?: ('RF-' . str_pad((string) $refundRequest->id, 4, '0', STR_PAD_LEFT))),
                        'refund_request_id' => $refundRequest->id,
                        'status_state' => $normalizedStatus,
                        'status_label' => $statusLabel,
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'Customer'),
                        'order_ref' => $order->order_ref ?? ('#' . $order->id),
                        'order_show_url' => route('admin.orders.show', $order),
                        'refund_type' => ucfirst(str_replace('_', ' ', (string) ($refundRequest->reason ?? 'Refund'))),
                        'reason' => trim((string) ($refundRequest->comment ?? $refundRequest->details ?? '')),
                        'amount' => number_format($orderAmount, 2),
                        'refund_to' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'GCash'))),
                        'customer_note' => trim((string) ($refundRequest->comment ?? $refundRequest->details ?? '')),
                        'requested_at' => optional($refundRequest->requested_at)->format('M d, h:i A') ?? $refundRequest->created_at->format('M d, h:i A'),
                        'admin_note' => trim((string) ($refundRequest->admin_note ?? '')),
                        'evidence' => $evidencePreviews,
                        'approve_release_url' => route('admin.orders.refund_requests.quick_release', $refundRequest),
                        'request_return_url' => route('admin.orders.refund_requests.request_return', $refundRequest),
                        'reject_url' => route('admin.orders.refund_requests.reject', $refundRequest),
                        'reject_not_returned_url' => route('admin.orders.refund_requests.reject_not_returned', $refundRequest),
                        'is_custom' => false,
                    ];

                    return [
                        'created_at' => $refundRequest->requested_at ?? $refundRequest->created_at,
                        'row_id' => 'regular-refund-' . $refundRequest->id,
                        'display_id' => (string) ($refundRequest->refund_reference ?: ('#RF-' . $refundRequest->id)),
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'N/A'),
                        'type' => 'refund',
                        'order_type' => 'normal',
                        'amount' => $orderAmount,
                        'status_key' => $normalizedStatus,
                        'status_label' => $statusLabel,
                        'view_url' => route('admin.orders.show', $order),
                        'action_kind' => 'refund_modal',
                        'refund_payload' => $modalPayload,
                    ];
                })
                ->filter();
        }

        // Custom refund requests.
        $customRefundRows = collect();
        if (Schema::hasTable('custom_order_refund_requests')) {
            $customRefundRows = CustomOrderRefundRequest::with(['customOrder.user', 'user'])
                ->where('request_type', 'refund')
                ->latest('requested_at')
                ->get()
                ->map(function (CustomOrderRefundRequest $refundRequest) {
                    $order = $refundRequest->customOrder;
                    if (!$order) {
                        return null;
                    }

                    $statusKey = strtolower((string) $refundRequest->status);
                    $normalizedStatus = match ($statusKey) {
                        'approved', 'processed' => 'refunded',
                        'rejected' => 'rejected',
                        default => 'under_review',
                    };

                    $rawEvidence = $refundRequest->evidence_paths;
                    if (is_string($rawEvidence) && $rawEvidence !== '') {
                        $decodedEvidence = json_decode($rawEvidence, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedEvidence)) {
                            $rawEvidence = $decodedEvidence;
                        }
                    }
                    $evidenceList = array_values(array_filter(is_array($rawEvidence) ? $rawEvidence : [], fn ($value) => $value !== null && $value !== ''));
                    $evidencePreviews = [];
                    foreach ($evidenceList as $evidencePath) {
                        $ext = strtolower(pathinfo(parse_url((string) $evidencePath, PHP_URL_PATH) ?? (string) $evidencePath, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
                        $isVideo = in_array($ext, ['mp4', 'mov', 'webm'], true);
                        $url = route('admin.custom-orders.refund_evidence.view', ['refundRequest' => $refundRequest->id, 'index' => count($evidencePreviews)]);

                        // If path is already a full URL (Cloudinary), use it directly
                        if (str_starts_with((string) $evidencePath, 'http://') || str_starts_with((string) $evidencePath, 'https://')) {
                            $previewUrl = (string) $evidencePath;
                        } else {
                            // For local storage paths, normalize and generate public storage URL
                            $cleanPath = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], (string) $evidencePath), '/');
                            $previewUrl = asset('storage/' . $cleanPath);
                        }

                        $evidencePreviews[] = [
                            'url' => $url,
                            'open_url' => $previewUrl,
                            'preview_url' => $previewUrl,
                            'fallback_url' => $previewUrl,
                            'is_image' => $isImage,
                            'is_video' => $isVideo,
                        ];
                    }

                    $statusLabel = match ($normalizedStatus) {
                        'under_review' => 'Under review',
                        'refunded' => 'Refunded',
                        'rejected' => 'Rejected',
                        default => Str::headline($normalizedStatus),
                    };

                    $modalPayload = [
                        'refund_id' => '#RF-' . $refundRequest->id,
                        'refund_request_id' => $refundRequest->id,
                        'status_state' => $normalizedStatus,
                        'status_label' => $statusLabel,
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'Customer'),
                        'order_ref' => $order->display_ref ?? ('#C-' . $order->id),
                        'order_show_url' => route('admin.custom-orders.show', $order),
                        'refund_type' => ucfirst((string) ($refundRequest->request_type ?: 'refund')),
                        'reason' => trim((string) ($refundRequest->reason ?? '')),
                        'amount' => number_format((float) ($order->final_price ?? 0), 2),
                        'refund_to' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'GCash'))),
                        'customer_note' => trim((string) ($refundRequest->details ?? '')),
                        'requested_at' => optional($refundRequest->requested_at)->format('M d, h:i A') ?? $refundRequest->created_at->format('M d, h:i A'),
                        'admin_note' => trim((string) ($refundRequest->admin_note ?? '')),
                        'evidence' => $evidencePreviews,
                        'approve_release_url' => route('admin.custom-orders.refund_requests.approve', $refundRequest),
                        'request_return_url' => null,
                        'reject_url' => route('admin.custom-orders.refund_requests.reject', $refundRequest),
                        'reject_not_returned_url' => null,
                        'is_custom' => true,
                    ];

                    return [
                        'created_at' => $refundRequest->requested_at ?? $refundRequest->created_at,
                        'row_id' => 'custom-refund-' . $refundRequest->id,
                        'display_id' => '#RF-' . $refundRequest->id,
                        'customer' => (string) ($refundRequest->user?->name ?: $order->customer_name ?: $order->user?->name ?: 'N/A'),
                        'type' => 'refund',
                        'order_type' => 'custom',
                        'amount' => (float) ($order->final_price ?? 0),
                        'status_key' => $normalizedStatus,
                        'status_label' => $statusLabel,
                        'view_url' => route('admin.custom-orders.show', $order),
                        'action_kind' => 'refund_modal',
                        'refund_payload' => $modalPayload,
                    ];
                })
                ->filter();
        }

        $refundRows = $regularRefundRows->concat($customRefundRows)->values();

        $stats = [
            'cancel' => [
                'pending' => $cancelRows->where('status_key', 'pending')->count(),
                'approved' => $cancelRows->where('status_key', 'approved')->count(),
                'rejected' => $cancelRows->where('status_key', 'rejected')->count(),
            ],
            'refund' => [
                'under_review' => $refundRows->where('status_key', 'under_review')->count(),
                'refunded' => $refundRows->where('status_key', 'refunded')->count(),
                'rejected' => $refundRows->where('status_key', 'rejected')->count(),
            ],
        ];

        if ($typeFilter === 'cancel') {
            $rows = $cancelRows;
        } elseif ($typeFilter === 'refund') {
            $rows = $refundRows;
        } else {
            $rows = $cancelRows->concat($refundRows)->values();
        }

        if ($search !== '') {
            $needle = Str::lower($search);
            $rows = $rows->filter(function (array $row) use ($needle) {
                return Str::contains(Str::lower((string) $row['display_id']), $needle)
                    || Str::contains(Str::lower((string) $row['customer']), $needle)
                    || Str::contains(Str::lower((string) $row['order_type']), $needle)
                    || Str::contains(Str::lower((string) $row['status_label']), $needle);
            })->values();
        }

        $rows = $rows->sortByDesc(function (array $row) {
            return optional($row['created_at'])->getTimestamp() ?? 0;
        })->values();

        $total = $rows->count();
        $paginatedRows = $rows->forPage($page, $perPage)->values();

        $requests = new LengthAwarePaginator(
            $paginatedRows,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.orders.post-order-requests', [
            'requests' => $requests,
            'stats' => $stats,
            'typeFilter' => $typeFilter,
            'search' => $search,
        ]);
    }

    private function buildRegularCancelRequestRows()
    {
        $hasRefundTable = Schema::hasTable('order_refund_requests');
        $hasRefundCommentColumn = $hasRefundTable && Schema::hasColumn('order_refund_requests', 'comment');
        $hasRefundWorkflowColumn = $hasRefundTable && Schema::hasColumn('order_refund_requests', 'workflow_status');

        $orders = Order::with(['user', 'refundRequests' => function ($query) {
            $query->latest();
        }])->where(function ($query) use ($hasRefundTable, $hasRefundCommentColumn) {
            $query->where('status', 'cancellation_requested')
                ->orWhere('admin_notes', 'like', '%cancel%')
                ->orWhere('notes', 'like', '%cancel%');

            if ($hasRefundTable) {
                $query->orWhereHas('refundRequests', function ($refundQuery) use ($hasRefundCommentColumn) {
                    $refundQuery->where('reason', 'like', '%cancel%')
                        ->orWhere('details', 'like', '%cancel%');

                    if ($hasRefundCommentColumn) {
                        $refundQuery->orWhere('comment', 'like', '%cancel%');
                    }
                });
            }
        })->orderByDesc('updated_at')->get();

        return $orders->map(function (Order $order) use ($hasRefundWorkflowColumn) {
            $cancelRelatedRefund = $order->refundRequests
                ->first(function (OrderRefundRequest $refundRequest) {
                    $haystack = Str::lower(trim((string) ($refundRequest->reason . ' ' . $refundRequest->details . ' ' . $refundRequest->comment)));
                    return Str::contains($haystack, 'cancel');
                });

            $statusKey = 'pending';
            if ($cancelRelatedRefund) {
                $workflow = Str::lower((string) ($cancelRelatedRefund->workflow_status ?: $cancelRelatedRefund->status));
                $isRejected = $workflow === 'rejected' || Str::upper((string) $cancelRelatedRefund->final_decision) === 'REJECT';
                $isApproved = in_array($workflow, ['approved', 'processed', 'pending_payout'], true)
                    || strtolower((string) $cancelRelatedRefund->status) === 'approved';

                if ($isRejected) {
                    $statusKey = 'rejected';
                } elseif ($isApproved) {
                    $statusKey = 'approved';
                }
            }

            if ($order->status === 'cancellation_requested') {
                $statusKey = 'pending';
            } elseif (in_array($order->status, ['cancelled', 'refunded'], true) && $statusKey !== 'rejected') {
                $statusKey = 'approved';
            }

            $notesText = Str::lower((string) ($order->admin_notes . ' ' . $order->notes));
            if ($statusKey === 'pending' && Str::contains($notesText, 'rejected')) {
                $statusKey = 'rejected';
            }

            $requestedAt = $cancelRelatedRefund?->requested_at ?? $order->updated_at;

            $reason = 'Customer requested cancellation';
            if (!empty($cancelRelatedRefund?->details)) {
                $reason = (string) $cancelRelatedRefund->details;
            }
            if (preg_match('/Customer cancellation requested:\s*(.+)$/mi', (string) ($order->admin_notes ?? ''), $matches) === 1) {
                $reason = trim((string) $matches[1]);
            }
            if (preg_match('/Reason:\s*(.+)$/mi', $reason, $reasonFromDetails) === 1) {
                $reason = trim((string) $reasonFromDetails[1]);
            }

            return [
                'created_at' => $requestedAt,
                'row_id' => 'regular-cancel-' . $order->id,
                'display_id' => '#C-' . $order->id,
                'customer' => (string) ($order->customer_name ?: $order->user?->name ?: 'N/A'),
                'type' => 'cancel',
                'order_type' => 'normal',
                'amount' => (float) ($order->total_amount ?? $order->total ?? 0),
                'status_key' => $statusKey,
                'status_label' => ucfirst($statusKey),
                'view_url' => route('admin.orders.show', $order),
                'action_kind' => 'cancel_modal',
                'cancel_payload' => [
                    'order_id' => $order->order_ref ?? ('#' . $order->id),
                    'customer' => $order->user->name ?? $order->customer_name ?? 'N/A',
                    'refund_amount' => number_format((float) ($order->total_amount ?? $order->total ?? 0), 2),
                    'payment_method' => ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'N/A'))),
                    'order_status' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'N/A'))),
                    'cancel_reason' => $reason,
                    'customer_note' => 'No customer note provided.',
                    'status_state' => $statusKey,
                    'order_show_url' => route('admin.orders.show', $order),
                    'approve_url' => route('admin.orders.cancel_requests.approve', $order),
                    'reject_url' => route('admin.orders.cancel_requests.reject', $order),
                    'is_custom' => false,
                ],
            ];
        });
    }

    /**
     * Dedicated refund request dashboard.
     */
    public function refundRequests(Request $request)
    {
        $this->ensureRefundRequestsTableExists();
        $this->ensureRefundWorkflowColumnsExist();

        if (!Schema::hasTable('order_refund_requests')) {
            return redirect()->route('admin.regular.index')->with('error', 'Refund requests table is not available yet.');
        }

        $statusFilter = strtolower((string) $request->query('status', 'all'));
        if (!in_array($statusFilter, ['all', 'under_review', 'awaiting_return', 'refunded', 'rejected'], true)) {
            $statusFilter = 'all';
        }

        $query = OrderRefundRequest::with(['order.user', 'user', 'reviewer'])
            ->whereHas('order')
            ->where(function ($builder) {
                // Keep cancellation requests on their own dashboard.
                $builder->whereRaw('LOWER(COALESCE(reason, "")) NOT LIKE ?', ['%cancel%'])
                    ->whereRaw('LOWER(COALESCE(comment, "")) NOT LIKE ?', ['%cancel%'])
                    ->whereRaw('LOWER(COALESCE(details, "")) NOT LIKE ?', ['%cancel%']);
            });

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('reason', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhere('refund_reference', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_ref', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $underReviewStatuses = ['requested', 'pending_review', 'under_review'];
        $awaitingReturnStatuses = ['awaiting_return_shipment', 'return_in_transit'];
        $refundedStatuses = ['pending_payout', 'return_received', 'approved', 'processed'];

        if ($statusFilter === 'under_review') {
            $query->where(function ($builder) use ($underReviewStatuses) {
                $builder->whereIn('status', $underReviewStatuses)
                    ->orWhereIn('workflow_status', $underReviewStatuses);
            });
        } elseif ($statusFilter === 'awaiting_return') {
            $query->where(function ($builder) use ($awaitingReturnStatuses) {
                $builder->whereIn('status', $awaitingReturnStatuses)
                    ->orWhereIn('workflow_status', $awaitingReturnStatuses);
            });
        } elseif ($statusFilter === 'refunded') {
            $query->where(function ($builder) use ($refundedStatuses) {
                $builder->whereIn('status', $refundedStatuses)
                    ->orWhereIn('workflow_status', $refundedStatuses)
                    ->orWhere('payout_status', 'completed');
            });
        } elseif ($statusFilter === 'rejected') {
            $query->where(function ($builder) {
                $builder->where('status', 'rejected')
                    ->orWhere('workflow_status', 'rejected')
                    ->orWhere('final_decision', 'REJECT');
            });
        }

        $refundRequests = $query->latest()->paginate(15)->withQueryString();

        $baseStatsQuery = OrderRefundRequest::query()
            ->whereHas('order')
            ->where(function ($builder) {
                $builder->whereRaw('LOWER(COALESCE(reason, "")) NOT LIKE ?', ['%cancel%'])
                    ->whereRaw('LOWER(COALESCE(comment, "")) NOT LIKE ?', ['%cancel%'])
                    ->whereRaw('LOWER(COALESCE(details, "")) NOT LIKE ?', ['%cancel%']);
            });

        $stats = [
            'under_review' => (clone $baseStatsQuery)->where(function ($builder) use ($underReviewStatuses) {
                $builder->whereIn('status', $underReviewStatuses)
                    ->orWhereIn('workflow_status', $underReviewStatuses);
            })->count(),
            'awaiting_return' => (clone $baseStatsQuery)->where(function ($builder) use ($awaitingReturnStatuses) {
                $builder->whereIn('status', $awaitingReturnStatuses)
                    ->orWhereIn('workflow_status', $awaitingReturnStatuses);
            })->count(),
            'refunded' => (clone $baseStatsQuery)->where(function ($builder) use ($refundedStatuses) {
                $builder->whereIn('status', $refundedStatuses)
                    ->orWhereIn('workflow_status', $refundedStatuses)
                    ->orWhere('payout_status', 'completed');
            })->count(),
            'rejected' => (clone $baseStatsQuery)->where(function ($builder) {
                $builder->where('status', 'rejected')
                    ->orWhere('workflow_status', 'rejected')
                    ->orWhere('final_decision', 'REJECT');
            })->count(),
        ];

        return view('admin.orders.refund-requests', compact('refundRequests', 'statusFilter', 'stats'));
    }

    /**
     * One-click action: under review -> awaiting return.
     */
    public function requestRefundItemReturn(Request $request, OrderRefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $currentStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($currentStatus, ['requested', 'pending_review', 'under_review'], true)) {
            return $this->refundActionErrorResponse($request, 'This request is no longer in review state.');
        }

        $refundRequest->status = 'approved';
        $refundRequest->workflow_status = 'awaiting_return_shipment';
        $refundRequest->final_decision = 'RETURN_REQUIRED';
        $refundRequest->return_required = true;
        $refundRequest->payout_status = 'pending';
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $refundRequest->admin_note = trim((string) $validated['admin_note']);
        }

        $refundRequest->save();

        $this->notifyRefundWorkflowUpdate(
            $refundRequest,
            'Awaiting Return',
            'Your refund request is approved for return processing. Please return the item so we can continue with refund release.'
        );

        return $this->refundActionSuccessResponse($request, $refundRequest, 'Return request has been sent to the customer.');
    }

    /**
     * One-click action: release refund (under review or awaiting return) with automatic payout record.
     */
    public function quickReleaseRefund(Request $request, OrderRefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $currentStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($currentStatus, ['requested', 'pending_review', 'under_review', 'awaiting_return_shipment', 'return_in_transit', 'pending_payout', 'return_received', 'approved'], true)) {
            return $this->refundActionErrorResponse($request, 'This request is not eligible for quick release.');
        }

        $order = $refundRequest->order;
        if (!$order) {
            return $this->refundActionErrorResponse($request, 'Associated order not found.');
        }

        $this->ensureRefundedPaymentStatusSupported();

        $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
        $releaseAmount = (float) ($refundRequest->refund_amount ?? $refundRequest->approved_amount ?? $refundRequest->recommended_refund_amount ?? 0);
        if ($releaseAmount <= 0) {
            $releaseAmount = $orderTotal;
        }
        $releaseAmount = max(0, min($releaseAmount, $orderTotal));

        $refundRequest->status = 'processed';
        $refundRequest->workflow_status = 'processed';
        $refundRequest->final_decision = in_array(strtoupper((string) $refundRequest->final_decision), ['PARTIAL_REFUND', 'FULL_REFUND'], true)
            ? strtoupper((string) $refundRequest->final_decision)
            : 'FULL_REFUND';
        $refundRequest->refund_amount = $releaseAmount;
        $refundRequest->approved_amount = $releaseAmount;
        $refundRequest->payout_status = 'completed';
        $refundRequest->refund_channel = $refundRequest->refund_channel ?: 'manual_admin';
        $refundRequest->refund_reference = $refundRequest->refund_reference ?: ('RF-AUTO-' . now()->format('YmdHis') . '-' . $refundRequest->id);
        $refundRequest->return_received_at = $refundRequest->return_received_at ?: now();
        $refundRequest->processed_at = now();
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();

        if (!empty($validated['admin_note'])) {
            $refundRequest->admin_note = trim((string) $validated['admin_note']);
        }

        $refundRequest->save();

        $order->status = 'refunded';
        $order->payment_status = 'refunded';
        $order->appendTrackingEvent('Refunded');
        $order->save();

        $this->notifyRefundDecision($refundRequest, 'approved');

        return $this->refundActionSuccessResponse($request, $refundRequest, 'Refund released and recorded successfully.');
    }

    /**
     * One-click action: reject awaiting-return request when item is not returned.
     */
    public function rejectRefundNotReturned(Request $request, OrderRefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'admin_note' => 'required|string|max:2000',
        ]);

        $currentStatus = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        if (!in_array($currentStatus, ['awaiting_return_shipment', 'return_in_transit'], true)) {
            return $this->refundActionErrorResponse($request, 'Only awaiting-return requests can be rejected with this action.');
        }

        $refundRequest->status = 'rejected';
        $refundRequest->workflow_status = 'rejected';
        $refundRequest->final_decision = 'REJECT';
        $refundRequest->refund_amount = 0;
        $refundRequest->approved_amount = 0;
        $refundRequest->payout_status = 'not_applicable';
        $refundRequest->admin_note = trim((string) $validated['admin_note']);
        $refundRequest->reviewed_by = auth()->id();
        $refundRequest->reviewed_at = now();
        $refundRequest->save();

        $this->notifyRefundDecision($refundRequest, 'rejected');

        return $this->refundActionSuccessResponse($request, $refundRequest, 'Refund request rejected due to item not returned.');
    }

    private function refundActionSuccessResponse(Request $request, OrderRefundRequest $refundRequest, string $message)
    {
        if ($this->isJsonLikeRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'refund' => $this->buildRefundActionPayload($refundRequest),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function refundActionErrorResponse(Request $request, string $message, int $status = 422)
    {
        if ($this->isJsonLikeRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }

    private function buildRefundActionPayload(OrderRefundRequest $refundRequest): array
    {
        $workflow = strtolower((string) ($refundRequest->workflow_status ?: $refundRequest->status));
        $statusState = 'under_review';
        $refundRef = (string) ($refundRequest->refund_reference ?? ('RF-' . str_pad((string) $refundRequest->id, 4, '0', STR_PAD_LEFT)));

        $order = $refundRequest->order;
        $rawRefundAmount = $refundRequest->refund_amount;
        $rawApprovedAmount = $refundRequest->approved_amount;
        $rawRecommendedAmount = $refundRequest->recommended_refund_amount;
        $rawOrderAmount = $order->total_amount ?? $order->total ?? 0;

        $displayAmount = 0.0;
        if ($rawApprovedAmount !== null && (float) $rawApprovedAmount > 0) {
            $displayAmount = (float) $rawApprovedAmount;
        } elseif ($rawRefundAmount !== null && (float) $rawRefundAmount > 0) {
            $displayAmount = (float) $rawRefundAmount;
        } elseif ($rawRecommendedAmount !== null && (float) $rawRecommendedAmount > 0) {
            $displayAmount = (float) $rawRecommendedAmount;
        } elseif ($rawOrderAmount !== null && (float) $rawOrderAmount > 0) {
            $displayAmount = (float) $rawOrderAmount;
        } elseif ($rawRefundAmount !== null) {
            $displayAmount = (float) $rawRefundAmount;
        } elseif ($rawApprovedAmount !== null) {
            $displayAmount = (float) $rawApprovedAmount;
        } elseif ($rawRecommendedAmount !== null) {
            $displayAmount = (float) $rawRecommendedAmount;
        } elseif ($rawOrderAmount !== null) {
            $displayAmount = (float) $rawOrderAmount;
        }

        if (in_array($workflow, ['awaiting_return_shipment', 'return_in_transit'], true)) {
            $statusState = 'awaiting_return';
        } elseif (in_array($workflow, ['processed', 'pending_payout', 'return_received', 'approved'], true) || strtolower((string) ($refundRequest->payout_status ?? '')) === 'completed') {
            $statusState = 'refunded';
        } elseif ($workflow === 'rejected' || strtoupper((string) ($refundRequest->final_decision ?? '')) === 'REJECT') {
            $statusState = 'rejected';
        }

        $statusLabel = match ($statusState) {
            'awaiting_return' => 'Awaiting return',
            'refunded' => 'Refunded',
            'rejected' => 'Rejected',
            default => 'Under review',
        };

        return [
            'refund_id' => $refundRef,
            'refund_request_id' => $refundRequest->id,
            'status_state' => $statusState,
            'status_label' => $statusLabel,
            'admin_note' => trim((string) ($refundRequest->admin_note ?? '')),
            'amount' => number_format($displayAmount, 2),
        ];
    }

    private function isJsonLikeRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * Approve cancellation request from dedicated cancellation dashboard.
     */
    public function approveCancellationRequest(Request $request, Order $order)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        if (strtolower((string) $order->status) !== 'cancellation_requested') {
            return redirect()->back()->with('error', 'This cancellation request is no longer pending.');
        }

        $adminNote = trim((string) ($validated['admin_note'] ?? ''));
        $existingAdminNotes = trim((string) ($order->admin_notes ?? ''));

        $latestRefundRequest = null;
        if (Schema::hasTable('order_refund_requests')) {
            $latestRefundRequest = OrderRefundRequest::where('order_id', $order->id)
                ->latest()
                ->first();
        }

        $newStatus = 'cancelled';
        $newPaymentStatus = $order->payment_status;
        $approvalLine = 'Cancellation request approved.';

        if ($latestRefundRequest) {
            $this->ensureRefundWorkflowColumnsExist();
            $this->ensureRefundedPaymentStatusSupported();

            $approvedAmount = (float) ($order->total_amount ?? $order->total ?? 0);
            $latestRefundRequest->status = 'processed';

            if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                $latestRefundRequest->workflow_status = 'processed';
            }
            if (Schema::hasColumn('order_refund_requests', 'final_decision')) {
                $latestRefundRequest->final_decision = 'FULL_REFUND';
            }
            if (Schema::hasColumn('order_refund_requests', 'refund_amount')) {
                $latestRefundRequest->refund_amount = $approvedAmount;
            }
            if (Schema::hasColumn('order_refund_requests', 'approved_amount')) {
                $latestRefundRequest->approved_amount = $approvedAmount;
            }
            if (Schema::hasColumn('order_refund_requests', 'payout_status')) {
                $latestRefundRequest->payout_status = 'completed';
            }
            if (Schema::hasColumn('order_refund_requests', 'refund_channel')) {
                $latestRefundRequest->refund_channel = 'manual';
            }
            if (Schema::hasColumn('order_refund_requests', 'refund_reference')) {
                $latestRefundRequest->refund_reference = 'CNCL-' . now()->format('YmdHis') . '-' . $order->id;
            }

            $latestRefundRequest->reviewed_by = auth()->id();
            $latestRefundRequest->reviewed_at = now();
            $latestRefundRequest->processed_at = now();
            if ($adminNote !== '' && Schema::hasColumn('order_refund_requests', 'admin_note')) {
                $latestRefundRequest->admin_note = $adminNote;
            }
            $latestRefundRequest->save();

            $newStatus = 'cancelled';
            $newPaymentStatus = 'refunded';
            $approvalLine = 'Cancellation request approved. Refund has been processed.';
        }

        $order->status = $newStatus;
        $order->payment_status = $newPaymentStatus;
        $order->cancelled_at = now();

        $noteLines = [$approvalLine];
        if ($adminNote !== '') {
            $noteLines[] = 'Admin note: ' . $adminNote;
        }

        foreach ($noteLines as $noteLine) {
            if (!Str::contains($existingAdminNotes, $noteLine)) {
                $existingAdminNotes = $existingAdminNotes === ''
                    ? $noteLine
                    : ($existingAdminNotes . "\n" . $noteLine);
            }
        }

        $order->admin_notes = trim($existingAdminNotes);
        $order->appendTrackingEvent($newPaymentStatus === 'refunded' ? 'Cancellation Approved (Refunded)' : 'Cancellation Approved');
        $order->save();

        $this->notifyCancellationDecision(
            $order,
            'approved',
            null,
            $adminNote !== '' ? $adminNote : null,
            $newPaymentStatus === 'refunded' ? 'Refunded' : null
        );

        return redirect()->back()->with('success', $approvalLine);
    }

    /**
     * Reject cancellation request from dedicated cancellation dashboard.
     */
    public function rejectCancellationRequest(Request $request, Order $order)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        if (strtolower((string) $order->status) !== 'cancellation_requested') {
            return redirect()->back()->with('error', 'This cancellation request is no longer pending.');
        }

        $rejectionReason = trim((string) ($validated['rejection_reason'] ?? ''));
        $adminNote = trim((string) ($validated['admin_note'] ?? ''));
        $existingAdminNotes = trim((string) ($order->admin_notes ?? ''));

        if (Schema::hasTable('order_refund_requests')) {
            $latestRefundRequest = OrderRefundRequest::where('order_id', $order->id)
                ->latest()
                ->first();

            if ($latestRefundRequest) {
                $latestRefundRequest->status = 'rejected';
                if (Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                    $latestRefundRequest->workflow_status = 'rejected';
                }
                if (Schema::hasColumn('order_refund_requests', 'final_decision')) {
                    $latestRefundRequest->final_decision = 'REJECT';
                }
                if (Schema::hasColumn('order_refund_requests', 'payout_status')) {
                    $latestRefundRequest->payout_status = 'cancelled';
                }
                if (Schema::hasColumn('order_refund_requests', 'admin_note')) {
                    $latestRefundRequest->admin_note = $adminNote !== ''
                        ? ('Rejection reason: ' . $rejectionReason . "\nAdmin note: " . $adminNote)
                        : ('Rejection reason: ' . $rejectionReason);
                }
                $latestRefundRequest->reviewed_by = auth()->id();
                $latestRefundRequest->reviewed_at = now();
                $latestRefundRequest->save();
            }
        }

        $nextStatus = in_array(strtolower((string) $order->payment_status), ['pending', 'failed'], true)
            ? 'pending'
            : 'processing';
        $order->status = $nextStatus;

        $rejectionLine = 'Cancellation request rejected.';
        $rejectionReasonLine = 'Rejection reason: ' . $rejectionReason;
        $adminNoteLine = $adminNote !== '' ? ('Admin note: ' . $adminNote) : null;

        if (!Str::contains($existingAdminNotes, $rejectionLine)) {
            $existingAdminNotes = $existingAdminNotes === ''
                ? $rejectionLine
                : ($existingAdminNotes . "\n" . $rejectionLine);
        }
        if (!Str::contains($existingAdminNotes, $rejectionReasonLine)) {
            $existingAdminNotes = $existingAdminNotes === ''
                ? $rejectionReasonLine
                : ($existingAdminNotes . "\n" . $rejectionReasonLine);
        }
        if ($adminNoteLine && !Str::contains($existingAdminNotes, $adminNoteLine)) {
            $existingAdminNotes = $existingAdminNotes === ''
                ? $adminNoteLine
                : ($existingAdminNotes . "\n" . $adminNoteLine);
        }

        $order->admin_notes = trim($existingAdminNotes);
        $order->appendTrackingEvent('Cancellation Rejected');
        $order->save();

        $this->notifyCancellationDecision(
            $order,
            'rejected',
            $rejectionReason,
            $adminNote !== '' ? $adminNote : null,
            null
        );

        return redirect()->back()->with('success', 'Cancellation request rejected. Order has been returned to active processing.');
    }

    /**
     * Notify customer about cancellation request decision.
     */
    private function notifyCancellationDecision(
        Order $order,
        string $decision,
        ?string $rejectionReason = null,
        ?string $adminNote = null,
        ?string $paymentRefundStatus = null
    ): void {
        $recipient = $order->user?->email ?: ($order->customer_email ?? null);
        if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $decisionNormalized = strtolower(trim($decision));
        $decisionLabel = $decisionNormalized === 'approved' ? 'Approved' : 'Rejected';
        $orderRef = trim((string) ($order->order_ref ?? ''));
        if ($orderRef === '') {
            $orderRef = 'Order #' . $order->id;
        }

        $subject = 'Cancellation Request ' . $decisionLabel . ' - ' . $orderRef;
        $introText = $decisionNormalized === 'approved'
            ? 'Your cancellation request has been approved.'
            : 'Your cancellation request has been rejected.';

        $extraMessage = null;
        if ($decisionNormalized === 'approved' && $paymentRefundStatus !== null) {
            $extraMessage = 'Payment refund status: ' . $paymentRefundStatus . '.';
        } elseif ($decisionNormalized === 'rejected') {
            $currentStatus = ucfirst(str_replace('_', ' ', strtolower((string) ($order->status ?? 'processing'))));
            $extraMessage = 'Your order remains active and will continue processing based on its current fulfillment stage. Current order status: ' . $currentStatus . '. If you have questions, please reply to this email or contact support.';
        }

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $order->user?->name ?: ($order->customer_name ?? 'Customer'),
                    'introText' => $introText,
                    'orderRef' => $orderRef,
                    'orderId' => $order->id,
                    'requestType' => 'Cancellation Request',
                    'decision' => $decisionLabel,
                    'reason' => $decisionNormalized === 'approved' ? null : $rejectionReason,
                    'adminNote' => $adminNote,
                    'approvedAmount' => null,
                    'extraMessage' => $extraMessage,
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to send cancellation decision email.', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'decision' => $decisionNormalized,
                'error' => $exception->getMessage(),
            ]);
        }
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
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'confirm_delivery' => 'nullable|boolean',
        ]);

        $deliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
        if ($deliveryType === 'deliver') {
            $deliveryType = 'delivery';
        }
        $isPickup = $deliveryType === 'pickup';

        $targetStatus = strtolower((string) $request->status);
        if ($isPickup && $targetStatus === 'shipped') {
            // Pickup orders should not pass through the delivery shipping stage.
            $targetStatus = 'delivered';
        }

        if ($targetStatus === 'completed' && !$isPickup) {
            return redirect()->back()->with('error', 'Only pickup orders can be marked as completed by admin.');
        }

        if ($targetStatus === 'delivered' && !$isPickup && !$request->boolean('confirm_delivery')) {
            return redirect()->back()->with('error', 'Please confirm delivery before marking this order as delivered.');
        }

        if (
            $targetStatus === 'cancelled'
            && in_array(strtolower((string) $order->status), ['delivered', 'completed', 'refunded'], true)
        ) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        $oldStatus = $order->status;
        $order->status = $targetStatus;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($targetStatus === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if (in_array($targetStatus, ['delivered', 'completed'], true) && !in_array($order->payment_status, ['paid', 'verified'], true)) {
            $order->payment_status = 'paid';
        }
        
        if ($targetStatus === 'cancelled') {
            $order->payment_status = 'failed';
        }

        if (in_array($targetStatus, ['delivered', 'completed'], true) && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        if ($targetStatus === 'completed' && !$order->confirmed_at) {
            $order->confirmed_at = now();
        }
        
        // sync tracking status and history
        $trackingStatusLabel = match ($targetStatus) {
            'delivered' => $isPickup ? 'Ready for Pickup' : 'Delivered',
            'completed' => $isPickup ? 'Picked Up' : 'Completed',
            default => ucfirst($targetStatus),
        };
        $order->tracking_status = $trackingStatusLabel;
        $order->appendTrackingEvent($trackingStatusLabel);
        $order->save();

        // Send notifications to user and admin
        if ($oldStatus !== $targetStatus) {
            $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
            $notificationService->notifyOrderStatusChange($order, $oldStatus, $targetStatus);
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
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'confirm_delivery' => 'nullable|boolean',
        ]);

        $deliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
        if ($deliveryType === 'deliver') {
            $deliveryType = 'delivery';
        }
        $isPickup = $deliveryType === 'pickup';

        $targetStatus = strtolower((string) $request->status);
        if ($isPickup && $targetStatus === 'shipped') {
            $targetStatus = 'delivered';
        }

        if ($targetStatus === 'completed' && !$isPickup) {
            return redirect()->back()->with('error', 'Only pickup orders can be marked as completed by admin.');
        }

        if ($targetStatus === 'delivered' && !$isPickup && !$request->boolean('confirm_delivery')) {
            return redirect()->back()->with('error', 'Please confirm delivery before marking this order as delivered.');
        }

        if (
            $targetStatus === 'cancelled'
            && in_array(strtolower((string) $order->status), ['delivered', 'completed', 'refunded'], true)
        ) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        $order->status = $targetStatus;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($targetStatus === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if (in_array($targetStatus, ['delivered', 'completed'], true) && !in_array($order->payment_status, ['paid', 'verified'], true)) {
            $order->payment_status = 'paid';
        }
        
        if ($targetStatus === 'cancelled') {
            $order->payment_status = 'failed';
        }

        if (in_array($targetStatus, ['delivered', 'completed'], true) && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        if ($targetStatus === 'completed' && !$order->confirmed_at) {
            $order->confirmed_at = now();
        }
        
        // sync tracking status and history
        $trackingStatusLabel = match ($targetStatus) {
            'delivered' => $isPickup ? 'Ready for Pickup' : 'Delivered',
            'completed' => $isPickup ? 'Picked Up' : 'Completed',
            default => ucfirst($targetStatus),
        };
        $order->tracking_status = $trackingStatusLabel;
        $order->appendTrackingEvent($trackingStatusLabel);
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
        $hasPaymentOptionColumn = Schema::hasColumn('orders', 'payment_option');
        $hasRemainingBalanceColumn = Schema::hasColumn('orders', 'remaining_balance');
        $hasDownpaymentAmountColumn = Schema::hasColumn('orders', 'downpayment_amount');
        $hasDownpaymentRateColumn = Schema::hasColumn('orders', 'downpayment_rate');

        $paymentOption = strtolower((string) ($hasPaymentOptionColumn ? ($order->payment_option ?? 'full') : 'full'));
        $remainingBalance = max(0, (float) ($hasRemainingBalanceColumn ? ($order->remaining_balance ?? 0) : 0));
        $notes = (string) ($order->notes ?? '');
        $legacyMatched = false;

        // Fallback for legacy partial orders where downpayment columns were not populated.
        if (($paymentOption !== 'downpayment' || $remainingBalance <= 0)
            && preg_match_all('/Downpayment received:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)\s*;\s*remaining balance:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)/i', $notes, $matches, PREG_SET_ORDER)
        ) {
            $lastMatch = end($matches);
            $legacyPaidAmount = isset($lastMatch[1]) ? (float) str_replace(',', '', $lastMatch[1]) : 0;
            $legacyRemaining = isset($lastMatch[2]) ? (float) str_replace(',', '', $lastMatch[2]) : 0;

            if ($legacyRemaining > 0) {
                $paymentOption = 'downpayment';
                $remainingBalance = max(0, round($legacyRemaining, 2));

                if ($hasPaymentOptionColumn) {
                    $order->payment_option = 'downpayment';
                }
                if ($hasDownpaymentAmountColumn) {
                    $order->downpayment_amount = max(0, round($legacyPaidAmount, 2));
                }

                $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
                if ($hasDownpaymentRateColumn && $orderTotal > 0 && $legacyPaidAmount > 0) {
                    $order->downpayment_rate = max(1, min(99, round(($legacyPaidAmount / $orderTotal) * 100, 2)));
                }

                if ($hasRemainingBalanceColumn) {
                    $order->remaining_balance = $remainingBalance;
                }
                $legacyMatched = true;
            }
        }

        // Handle inconsistent records where remaining balance exists but payment_option was not saved.
        if ($paymentOption !== 'downpayment' && $remainingBalance > 0) {
            $paymentOption = 'downpayment';
            if ($hasPaymentOptionColumn) {
                $order->payment_option = 'downpayment';
            }
        }

        if ($paymentOption !== 'downpayment') {
            return redirect()->back()->with('error', 'Only downpayment orders can settle a remaining balance.');
        }

        if ($remainingBalance <= 0) {
            return redirect()->back()->with('info', 'This order is already fully paid.');
        }

        $normalizedPaymentStatus = strtolower((string) $order->payment_status);
        if (!in_array($normalizedPaymentStatus, ['paid', 'verified'], true)) {
            return redirect()->back()->with('error', 'Collect and verify the downpayment before settling the remaining balance.');
        }

        if (in_array(strtolower((string) $order->status), ['cancelled', 'refunded'], true)) {
            return redirect()->back()->with('error', 'Cancelled or refunded orders cannot be settled.');
        }

        $totalAmount = (float) ($order->total_amount ?? $order->total ?? 0);
        if ($hasPaymentOptionColumn) {
            $order->payment_option = 'full';
        }
        if ($hasDownpaymentRateColumn) {
            $order->downpayment_rate = $totalAmount > 0 ? 100 : (float) ($order->downpayment_rate ?? 100);
        }
        if ($hasDownpaymentAmountColumn) {
            $order->downpayment_amount = $totalAmount > 0 ? $totalAmount : (float) ($order->downpayment_amount ?? 0);
        }
        if ($hasRemainingBalanceColumn) {
            $order->remaining_balance = 0;
        }

        $order->payment_status = $normalizedPaymentStatus === 'verified' ? 'verified' : 'paid';
        $order->payment_verified_at = now();
        $order->tracking_status = 'Payment Settled';
        $order->appendTrackingEvent('Remaining Balance Settled');

        // Remove legacy partial-payment marker lines so invoice/user badges stop showing partial.
        $normalizedNotes = preg_replace('/\s*Downpayment received:\s*PHP\s*[0-9,]+(?:\.[0-9]{1,2})?\s*;\s*remaining balance:\s*PHP\s*[0-9,]+(?:\.[0-9]{1,2})?\s*/i', "\n", $notes);
        $normalizedNotes = trim(preg_replace('/\n{2,}/', "\n", (string) $normalizedNotes));
        $settledLine = 'Remaining balance settled by admin on ' . now()->format('M d, Y h:i A');
        if (!str_contains($normalizedNotes, $settledLine)) {
            $normalizedNotes = $normalizedNotes === '' ? $settledLine : ($normalizedNotes . "\n" . $settledLine);
        }
        $order->notes = $normalizedNotes;

        if ($legacyMatched) {
            $order->admin_notes = trim((string) $order->admin_notes);
            $legacyLine = 'Legacy partial-payment record normalized during settlement.';
            if (!str_contains((string) $order->admin_notes, $legacyLine)) {
                $order->admin_notes = $order->admin_notes === ''
                    ? $legacyLine
                    : ($order->admin_notes . "\n" . $legacyLine);
            }
        }

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
            return $this->refundActionErrorResponse($request, 'This refund request is not in a reviewable state.');
        }

        $order = $refundRequest->order;
        if (!$order) {
            return $this->refundActionErrorResponse($request, 'Associated order was not found.');
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
            return $this->refundActionSuccessResponse($request, $refundRequest, 'Refund request rejected.');
        }

        if ($selectedDecision === 'RETURN_REQUIRED') {
            $this->notifyRefundWorkflowUpdate(
                $refundRequest,
                'Return Required',
                'Your refund request is approved for return processing. Please submit your return shipment details to continue.'
            );
            return $this->refundActionSuccessResponse($request, $refundRequest, 'Refund review saved. Waiting for customer return shipment details.');
        }

        $this->notifyRefundWorkflowUpdate(
            $refundRequest,
            'Pending Payout',
            'Your refund request has been reviewed and is now pending payout processing.'
        );

        return $this->refundActionSuccessResponse($request, $refundRequest, 'Refund decision saved. Request is now pending payout processing.');
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

        $this->notifyRefundWorkflowUpdate(
            $refundRequest,
            'Return Received',
            'We have received your returned item. Your refund is now pending payout processing.'
        );

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

        $reasonText = strtolower((string) ($refundRequest->reason ?? ''));
        $commentText = strtolower((string) ($refundRequest->comment ?? $refundRequest->details ?? ''));
        $isCancellationRefundFlow = str_contains($reasonText, 'cancel') || str_contains($commentText, 'cancel');

        $order->status = $isCancellationRefundFlow ? 'cancelled' : 'refunded';
        $order->payment_status = 'refunded';
        $order->appendTrackingEvent($isCancellationRefundFlow ? 'Cancellation Refund Processed' : 'Refunded');
        $order->save();

        $this->notifyRefundDecision($refundRequest, 'approved');

        return redirect()->back()->with('success', 'Refund payout recorded. Order is now marked as refunded.');
    }

    /**
     * Serve refund evidence files for admin preview.
     */
    public function viewRefundEvidence(OrderRefundRequest $refundRequest, int $index)
    {
        try {
            $rawEvidence = $refundRequest->evidence_paths;
            if (is_string($rawEvidence) && $rawEvidence !== '') {
                $decoded = json_decode($rawEvidence, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $rawEvidence = $decoded;
                }
            }

            $evidence = array_values(array_filter(
                is_array($rawEvidence) ? $rawEvidence : [],
                static fn ($value) => $value !== null && $value !== ''
            ));

            if (!array_key_exists($index, $evidence)) {
                Log::warning('Admin refund evidence index not found', [
                    'refund_request_id' => $refundRequest->id,
                    'requested_index' => $index,
                    'available_count' => count($evidence),
                ]);

                return response('Evidence file not found.', 404);
            }

            $path = str_replace('\\', '/', trim((string) $evidence[$index], " \t\n\r\0\x0B\"'"));
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return redirect()->away($path);
            }

            $normalizedPath = ltrim($path, '/');
            if (str_starts_with($normalizedPath, 'storage/')) {
                $normalizedPath = substr($normalizedPath, strlen('storage/'));
            }
            if (str_starts_with($normalizedPath, 'public/')) {
                $normalizedPath = substr($normalizedPath, strlen('public/'));
            }

            $candidatePaths = array_values(array_unique(array_filter([
                $normalizedPath,
                ltrim($path, '/'),
                'public/' . ltrim($normalizedPath, '/'),
            ], static fn ($value) => is_string($value) && $value !== '')));

            foreach ($candidatePaths as $candidatePath) {
                $candidatePath = str_replace('\\', '/', trim((string) $candidatePath, '/'));

                if (Storage::disk('public')->exists($candidatePath)) {
                    $absolutePath = Storage::disk('public')->path($candidatePath);
                    $mimeType = Storage::disk('public')->mimeType($candidatePath) ?: 'application/octet-stream';
                    return response()->file($absolutePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
                        'X-Content-Type-Options' => 'nosniff',
                    ]);
                }

                if (Storage::disk('local')->exists($candidatePath)) {
                    $absolutePath = Storage::disk('local')->path($candidatePath);
                    $mimeType = Storage::disk('local')->mimeType($candidatePath) ?: 'application/octet-stream';
                    return response()->file($absolutePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
                        'X-Content-Type-Options' => 'nosniff',
                    ]);
                }

                $publicStorageRelative = ltrim(str_replace(['public/', 'storage/'], '', $candidatePath), '/');
                $publicStoragePath = public_path('storage/' . $publicStorageRelative);
                if (is_file($publicStoragePath)) {
                    return redirect()->to(asset('storage/' . $publicStorageRelative));
                }
            }

            Log::warning('Admin refund evidence file missing on storage disk', [
                'refund_request_id' => $refundRequest->id,
                'requested_index' => $index,
                'stored_path' => $path,
                'candidate_paths' => $candidatePaths,
            ]);

            return response('Evidence file is unavailable.', 404);
        } catch (\Throwable $exception) {
            Log::warning('Unable to serve refund evidence file for admin.', [
                'refund_request_id' => $refundRequest->id,
                'index' => $index,
                'error' => $exception->getMessage(),
            ]);

            return response('Evidence file is unavailable.', 404);
        }
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
    public function cancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $cancelReason = trim((string) $validated['cancel_reason']);
        $currentStatus = strtolower((string) $order->status);

        if (in_array($currentStatus, ['delivered', 'completed', 'refunded'], true)) {
            return redirect()->back()->with('error', 'Delivered or completed orders can no longer be cancelled.');
        }

        if ($currentStatus === 'cancelled') {
            return redirect()->back()->with('info', 'Order is already cancelled.');
        }

        $currentPaymentStatus = strtolower((string) $order->payment_status);
        $wasPaid = in_array($currentPaymentStatus, ['paid', 'verified'], true);
        $refundProcessStarted = false;

        if ($wasPaid) {
            $this->ensureRefundRequestsTableExists();
            $this->ensureRefundWorkflowColumnsExist();

            if (Schema::hasTable('order_refund_requests') && !empty($order->user_id)) {
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
                    ->where('user_id', $order->user_id)
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
                        'user_id' => (int) $order->user_id,
                        'reason' => 'Order cancellation by admin',
                        'details' => 'Auto-generated admin cancellation refund request. Reason: ' . $cancelReason,
                        'status' => 'requested',
                    ];

                    if (Schema::hasColumn('order_refund_requests', 'requested_at')) {
                        $refundPayload['requested_at'] = now();
                    }
                    if (Schema::hasColumn('order_refund_requests', 'refund_type')) {
                        $refundPayload['refund_type'] = 'full';
                    }
                    if (Schema::hasColumn('order_refund_requests', 'comment')) {
                        $refundPayload['comment'] = 'Order cancelled by admin before fulfillment: ' . $cancelReason;
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
            ? ($refundProcessStarted
                ? 'Payment was received. Refund request is now pending admin review and payout processing.'
                : 'Payment was received. Please process refund manually because no customer refund record was created.')
            : 'No completed payment was recorded, so no refund action is needed.';

        $order->status = 'cancelled';
        $order->payment_status = $wasPaid ? $order->payment_status : 'failed';
        $order->cancelled_at = now();
        $order->tracking_status = 'Cancelled';
        $order->appendTrackingEvent('Cancelled - Reason: ' . $cancelReason);

        $existingNotes = trim((string) $order->notes);
        $reasonLine = 'Cancellation reason: ' . $cancelReason;
        if (!str_contains($existingNotes, $reasonLine)) {
            $existingNotes = $existingNotes !== '' ? ($existingNotes . "\n" . $reasonLine) : $reasonLine;
        }
        $order->notes = $existingNotes;

        $existingAdminNotes = trim((string) $order->admin_notes);
        $adminReasonLine = 'Cancelled by admin. Reason: ' . $cancelReason;
        if (!str_contains($existingAdminNotes, $adminReasonLine)) {
            $existingAdminNotes = $existingAdminNotes !== ''
                ? ($existingAdminNotes . "\n" . $adminReasonLine)
                : $adminReasonLine;
        }
        $existingAdminNotes = $existingAdminNotes !== ''
            ? ($existingAdminNotes . "\n" . $paymentNote)
            : $paymentNote;
        $order->admin_notes = trim($existingAdminNotes);

        $order->save();

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        $this->notifyOrderCancellationByAdmin($order, $cancelReason, $paymentNote);

        return redirect()->back()->with('success', ($wasPaid && $refundProcessStarted)
            ? 'Order cancelled. Refund request has started and is now pending review.'
            : ($wasPaid
                ? 'Order cancelled. Payment remains marked as paid until refund payout is processed.'
                : 'Order cancelled successfully.'));
    }

    /**
     * Ensure refund requests table exists for deployments with delayed migrations.
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
            Log::warning('Unable to auto-create order_refund_requests table from admin order controller', [
                'error' => $e->getMessage(),
            ]);
        }
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
     * Notify customer about intermediate refund workflow updates.
     */
    private function notifyRefundWorkflowUpdate(OrderRefundRequest $refundRequest, string $statusLabel, string $message): void
    {
        $order = $refundRequest->order;
        if (!$order) {
            return;
        }

        $recipient = $refundRequest->user?->email ?: $order->user?->email ?: $order->customer_email;
        if (!$recipient) {
            return;
        }

        $subject = 'Refund Request Update - ' . ($order->order_ref ?? ('#' . $order->id));

        try {
            TransactionalMailService::sendViewDetailed(
                $recipient,
                $subject,
                'emails.orders.request-status',
                [
                    'subject' => $subject,
                    'customerName' => $refundRequest->user?->name ?: ($order->customer_name ?: 'Customer'),
                    'introText' => $message,
                    'orderRef' => $order->order_ref,
                    'orderId' => $order->id,
                    'requestType' => 'Refund Request',
                    'decision' => $statusLabel,
                    'reason' => $refundRequest->reason,
                    'adminNote' => $refundRequest->admin_note,
                    'approvedAmount' => null,
                    'extraMessage' => null,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send refund workflow update email', [
                'refund_request_id' => $refundRequest->id,
                'recipient' => $recipient,
                'status_label' => $statusLabel,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify customer when admin cancels an order.
     */
    private function notifyOrderCancellationByAdmin(Order $order, string $cancelReason, string $paymentNote): void
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
                    'reason' => $cancelReason,
                    'adminNote' => $order->admin_notes,
                    'approvedAmount' => null,
                    'extraMessage' => $paymentNote . ' Payment status: ' . strtoupper((string) $order->payment_status),
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
     * Decrement stock for bundle component products
     */
    private function decrementBundleComponentStock(\App\Models\Product $bundleProduct, int $bundleQuantity): void
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
            
            // Check if product is a bundle
            $isBundle = Schema::hasTable('product_bundle_items')
                && $product->bundleItems()->exists();
            
            // Check stock availability
            $availableStock = $isBundle ? $product->available_stock : $product->stock;
            if ($availableStock < $item['quantity']) {
                return redirect()->back()
                    ->with('error', "Insufficient stock for {$product->name}. Available: {$availableStock}, Requested: {$item['quantity']}")
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
            if ($isBundle) {
                // Deduct stock from bundle component products
                $this->decrementBundleComponentStock($product, $item['quantity']);
            } else {
                // Deduct stock from regular product
                $product->stock -= $item['quantity'];
                $product->save();
            }
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