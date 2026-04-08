<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PayMongoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymongoPaymentController extends Controller
{
    public function createCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'   => 'required|integer|exists:orders,id',
            'success_url' => 'nullable|url',
            'cancel_url'  => 'nullable|url',
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
                'success_url' => $validated['success_url'] ?? (config('app.url') . '/orders'),
                'cancel_url'  => $validated['cancel_url']  ?? (config('app.url') . '/orders'),
            ];

            $result = $service->createCheckout($order, $options);

            return response()->json([
                'success' => true,
                'data'    => [
                    'checkout_id'  => $result['checkout_id'],
                    'checkout_url' => $result['checkout_url'],
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
