<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\MayaCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MayaPaymentController extends Controller
{
    public function __construct(private readonly MayaCheckoutService $mayaService)
    {
    }

    public function createCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'success_url' => 'nullable|url',
            'failure_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        $order = Order::with('items')->findOrFail($validated['order_id']);

        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order access.',
            ], 403);
        }

        try {
            $result = $this->mayaService->createCheckout($order, [
                'success_url' => $validated['success_url'] ?? null,
                'failure_url' => $validated['failure_url'] ?? null,
                'cancel_url' => $validated['cancel_url'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order access.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'payment_reference' => $order->payment_reference,
                'status' => $order->status,
            ],
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Maya-Signature')
            ?? $request->header('X-Paymaya-Signature')
            ?? $request->header('Paymaya-Signature');

        if (!$this->mayaService->verifyWebhookSignature($request->getContent(), $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Maya webhook signature.',
            ], 401);
        }

        $order = $this->mayaService->processWebhook($request->all());

        if (!$order) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook received, no matching order.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed.',
            'data' => [
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ],
        ]);
    }
}
