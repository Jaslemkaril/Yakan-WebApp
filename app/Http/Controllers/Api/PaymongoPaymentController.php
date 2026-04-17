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
            'order_id'   => 'required|integer|exists:orders,id',
            'success_url' => 'nullable|url',
            'cancel_url'  => 'nullable|url',
            'payment_option' => 'nullable|string|in:full,downpayment',
            'downpayment_rate' => 'nullable|numeric|min:1|max:100',
            'amount_due_now' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|string|in:pickup,deliver,delivery',
            'amount_override' => 'nullable|numeric|min:0',
            'is_downpayment_override' => 'nullable|boolean',
        ]);

        $order = Order::with(['items.product', 'user'])->findOrFail($validated['order_id']);

        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order access.',
            ], 403);
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
            }

            $result = $service->createCheckout($order, $options);

            return response()->json([
                'success' => true,
                'data'    => [
                    'checkout_id'  => $result['checkout_id'],
                    'checkout_url' => $result['checkout_url'],
                    'amount_override_applied' => $options['amount_override'] ?? null,
                    'is_downpayment_override_applied' => $options['is_downpayment_override'] ?? false,
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayMongo API checkout failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PayMongo checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
