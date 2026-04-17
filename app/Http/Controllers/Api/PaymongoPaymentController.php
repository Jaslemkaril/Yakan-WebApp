<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PayMongoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PaymongoPaymentController extends Controller
{
    public function createCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'   => 'nullable|integer|exists:orders,id',
            'checkout_reference' => 'nullable|string|max:120',
            'success_url' => 'nullable|url',
            'cancel_url'  => 'nullable|url',
            'payment_option' => 'nullable|string|in:full,downpayment',
            'downpayment_rate' => 'nullable|numeric|min:1|max:100',
            'order_ref' => 'nullable|string|max:120',
            'amount_due_now' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|string|in:pickup,deliver,delivery',
            'amount_override' => 'nullable|numeric|min:0',
            'is_downpayment_override' => 'nullable|boolean',
        ]);

        $requestedOrderId = isset($validated['order_id']) ? (int) $validated['order_id'] : null;
        $requestedOrderRef = trim((string) ($validated['order_ref'] ?? ''));
        $requestedCheckoutReference = trim((string) ($validated['checkout_reference'] ?? ''));
        $hintTotalAmount = isset($validated['total_amount'])
            ? (float) $validated['total_amount']
            : null;
        $hintAmountDueNow = isset($validated['amount_due_now'])
            ? (float) $validated['amount_due_now']
            : null;

        if (is_null($requestedOrderId) && $requestedOrderRef === '' && $requestedCheckoutReference === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing checkout identity. Provide order_id, order_ref, or checkout_reference.',
                'errors' => [
                    'order_id' => ['At least one checkout identifier is required.'],
                ],
            ], 422);
        }

        $authUserId = (int) $request->user()->id;
        $order = null;

        if (!is_null($requestedOrderId)) {
            $order = Order::with(['items.product', 'user'])->find($requestedOrderId);
        } elseif ($requestedOrderRef !== '') {
            $order = Order::with(['items.product', 'user'])
                ->where('user_id', $authUserId)
                ->where(function ($query) use ($requestedOrderRef) {
                    $query->where('order_ref', $requestedOrderRef)
                        ->orWhere('tracking_number', $requestedOrderRef);
                })
                ->latest()
                ->first();
        } elseif ($requestedCheckoutReference !== '') {
            $order = Order::with(['items.product', 'user'])
                ->where('user_id', $authUserId)
                ->where(function ($query) use ($requestedCheckoutReference) {
                    $query->where('payment_reference', $requestedCheckoutReference)
                        ->orWhere('notes', 'like', '%[checkout_ref:' . $requestedCheckoutReference . ']%');
                })
                ->latest()
                ->first();
        }

        $targetAmount = null;
        if (!is_null($hintTotalAmount) && $hintTotalAmount > 0) {
            $targetAmount = $hintTotalAmount;
        } elseif (!is_null($hintAmountDueNow) && $hintAmountDueNow > 0) {
            $targetAmount = $hintAmountDueNow;
        }

        $recentOrders = collect();
        if (!$order) {
            $recentOrders = Order::with(['items.product', 'user'])
                ->where('user_id', $authUserId)
                ->latest()
                ->limit(50)
                ->get();

            if (!is_null($targetAmount)) {
                // Prefer exact total match first.
                $order = $recentOrders->first(function (Order $candidate) use ($targetAmount) {
                    $candidateTotal = (float) ($candidate->total_amount ?? $candidate->total ?? 0);
                    return $candidateTotal > 0 && abs($candidateTotal - $targetAmount) <= 0.01;
                });
            }

            if (!$order) {
                // Fallback: most recent pending PayMongo order.
                $order = $recentOrders->first(function (Order $candidate) {
                    $paymentMethod = strtolower((string) ($candidate->payment_method ?? ''));
                    $paymentStatus = strtolower((string) ($candidate->payment_status ?? ''));
                    $orderStatus = strtolower((string) ($candidate->status ?? ''));

                    return $paymentMethod === 'paymongo'
                        && in_array($paymentStatus, ['pending', 'unpaid', 'pending_payment', ''], true)
                        && in_array($orderStatus, ['pending', 'pending_confirmation', 'confirmed', 'processing'], true);
                });
            }
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found for the provided checkout identity.',
                'data' => [
                    'user_id' => $authUserId,
                    'received' => [
                        'order_id' => $requestedOrderId,
                        'order_ref' => $requestedOrderRef,
                        'checkout_reference' => $requestedCheckoutReference,
                        'total_amount' => $hintTotalAmount,
                        'amount_due_now' => $hintAmountDueNow,
                    ],
                    'recent_orders_checked' => $recentOrders->count(),
                ],
            ], 404);
        }

        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order access.',
            ], 403);
        }

        if ($requestedOrderRef !== '' && strcasecmp((string) ($order->order_ref ?? ''), $requestedOrderRef) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Order identity mismatch. Please refresh checkout and try again.',
                'errors' => [
                    'order_ref' => ['Provided order reference does not match the selected order ID.'],
                ],
            ], 422);
        }

        if ($requestedCheckoutReference !== '' && strcasecmp((string) ($order->payment_reference ?? ''), $requestedCheckoutReference) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout reference mismatch. Please return to checkout and try again.',
                'errors' => [
                    'checkout_reference' => ['Provided checkout reference does not match the selected order.'],
                ],
            ], 422);
        }

        try {
            $service = new PayMongoCheckoutService();

            $options = [
                'success_url' => $validated['success_url'] ?? (config('app.url') . '/mobile/payment/paymongo/success/' . $order->id),
                'cancel_url'  => $validated['cancel_url']  ?? (config('app.url') . '/mobile/payment/paymongo/failed/' . $order->id),
            ];

            $orderTotal = (float) ($order->total_amount ?? $order->total ?? 0);
            $deliveryType = strtolower((string) ($validated['delivery_type'] ?? $order->delivery_type ?? 'deliver'));
            if ($deliveryType === 'delivery') {
                $deliveryType = 'deliver';
            }

            $requestedPaymentOption = strtolower((string) ($validated['payment_option'] ?? $order->payment_option ?? 'full'));
            $requestedAmountDueNow = isset($validated['amount_due_now'])
                ? (float) $validated['amount_due_now']
                : null;
            $requestedAmountOverride = isset($validated['amount_override'])
                ? (float) $validated['amount_override']
                : null;
            $explicitDownpaymentOverride = array_key_exists('is_downpayment_override', $validated)
                ? (bool) $validated['is_downpayment_override']
                : null;

            $requestedDownpaymentRate = isset($validated['downpayment_rate'])
                ? (float) $validated['downpayment_rate']
                : null;

            $clientRequestedAmount = null;
            if (!is_null($requestedAmountOverride) && $requestedAmountOverride > 0) {
                $clientRequestedAmount = $requestedAmountOverride;
            } elseif (!is_null($requestedAmountDueNow) && $requestedAmountDueNow > 0) {
                $clientRequestedAmount = $requestedAmountDueNow;
            }

            if (!is_null($clientRequestedAmount)) {
                $cap = $orderTotal > 0 ? $orderTotal : $clientRequestedAmount;
                $clientRequestedAmount = max(0, min($cap, $clientRequestedAmount));
            }

            $isRequestedDownpayment = $requestedPaymentOption === 'downpayment'
                || (!is_null($clientRequestedAmount) && $orderTotal > 0 && ($clientRequestedAmount + 0.01) < $orderTotal);

            if (!is_null($explicitDownpaymentOverride)) {
                $isRequestedDownpayment = $explicitDownpaymentOverride;
            }

            $payableNowAmount = $order->getAmountDueNow();

            if (!is_null($clientRequestedAmount)) {
                $payableNowAmount = $clientRequestedAmount;
            }

            if ($isRequestedDownpayment) {
                if ($payableNowAmount <= 0 && $orderTotal > 0) {
                    $rate = $requestedDownpaymentRate ?? (float) ($order->downpayment_rate ?? 50);
                    $rate = min(99, max(1, $rate));
                    $payableNowAmount = round($orderTotal * ($rate / 100), 2);
                }

                if ($payableNowAmount > 0 && $orderTotal > 0 && ($payableNowAmount + 0.01) < $orderTotal) {
                    $options['amount_override'] = max(0, min($orderTotal, $payableNowAmount));
                    $options['is_downpayment_override'] = true;

                    if (Schema::hasColumn('orders', 'payment_option')) {
                        $order->payment_option = 'downpayment';
                    }

                    if (Schema::hasColumn('orders', 'downpayment_rate')) {
                        $computedRate = $orderTotal > 0
                            ? round(($options['amount_override'] / $orderTotal) * 100, 2)
                            : ($requestedDownpaymentRate ?? 50);
                        $order->downpayment_rate = min(99, max(1, $computedRate));
                    }

                    if (Schema::hasColumn('orders', 'downpayment_amount')) {
                        $order->downpayment_amount = $options['amount_override'];
                    }

                    if (Schema::hasColumn('orders', 'remaining_balance')) {
                        $order->remaining_balance = max(0, round($orderTotal - $options['amount_override'], 2));
                    }

                    $order->save();
                }
            } elseif (!is_null($clientRequestedAmount) && $orderTotal > 0) {
                $options['amount_override'] = max(0, min($orderTotal, $clientRequestedAmount));
                $options['is_downpayment_override'] = false;
            }

            $result = $service->createCheckout($order, $options);

            return response()->json([
                'success' => true,
                'data'    => [
                    'order_id' => $order->id,
                    'order_ref' => $order->order_ref,
                    'checkout_reference' => $order->payment_reference,
                    'checkout_id'  => $result['checkout_id'],
                    'checkout_url' => $result['checkout_url'],
                    'amount_override_applied' => $options['amount_override'] ?? null,
                    'is_downpayment_override_applied' => $options['is_downpayment_override'] ?? false,
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayMongo API checkout failed', [
                'order_id' => $order?->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PayMongo checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
