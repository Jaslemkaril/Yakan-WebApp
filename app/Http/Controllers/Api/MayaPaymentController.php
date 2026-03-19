<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\MayaCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $order = Order::with('items.product')->findOrFail($validated['order_id']);

        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order access.',
            ], 403);
        }

        try {
            $publicKey = config('services.maya.public_key');
            $baseUrl   = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');
            $amount    = (float) ($order->total_amount ?? $order->total ?? 0);

            if (empty($publicKey)) {
                throw new \RuntimeException('Maya public key is not configured.');
            }
            if ($amount <= 0) {
                throw new \RuntimeException('Invalid order amount.');
            }

            $successUrl = $validated['success_url'] ?? (config('app.url') . '/track-order');
            $failureUrl = $validated['failure_url'] ?? (config('app.url') . '/track-order');
            $cancelUrl  = $validated['cancel_url']  ?? (config('app.url') . '/track-order');

            $items = $order->items->map(function ($item) {
                return [
                    'name'        => (string) ($item->product->name ?? ('Product #' . $item->product_id)),
                    'quantity'    => (int) $item->quantity,
                    'totalAmount' => [
                        'value'    => number_format((float) $item->price * (int) $item->quantity, 2, '.', ''),
                        'currency' => 'PHP',
                    ],
                ];
            })->values()->all();

            $payload = [
                'totalAmount'            => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                'requestReferenceNumber' => $order->order_ref,
                'redirectUrl'            => ['success' => $successUrl, 'failure' => $failureUrl, 'cancel' => $cancelUrl],
                'buyer' => [
                    'firstName' => explode(' ', $order->customer_name ?? 'Customer')[0],
                    'lastName'  => explode(' ', $order->customer_name ?? 'Customer -', 2)[1] ?? '-',
                    'contact'   => ['email' => $order->customer_email ?? ''],
                ],
                'items' => $items ?: [[
                    'name'     => 'Order #' . $order->id,
                    'quantity' => 1,
                    'totalAmount' => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                ]],
            ];

            $response = Http::withBasicAuth($publicKey, '')
                ->acceptJson()
                ->post($baseUrl . '/checkout/v1/checkouts', $payload);

            if (!$response->successful()) {
                \Log::error('Maya mobile checkout failed', [
                    'order_id' => $order->id,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
                throw new \RuntimeException('Maya checkout request failed: HTTP ' . $response->status() . ' — ' . $response->body());
            }

            $data        = $response->json() ?? [];
            $checkoutId  = $data['checkoutId'] ?? $data['id'] ?? null;
            $checkoutUrl = $data['redirectUrl'] ?? $data['checkoutUrl'] ?? $data['url'] ?? null;

            if (!$checkoutId || !$checkoutUrl) {
                throw new \RuntimeException('Unexpected Maya response: ' . json_encode($data));
            }

            $order->payment_method    = 'maya';
            $order->payment_reference = $checkoutId;
            $order->payment_status    = 'pending';
            $order->save();

            return response()->json([
                'success' => true,
                'data'    => ['checkout_id' => $checkoutId, 'checkout_url' => $checkoutUrl],
            ]);

        } catch (\Throwable $exception) {
            \Log::error('Maya mobile checkout exception: ' . $exception->getMessage());
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

        if ($order->payment_method === 'maya' && !empty($order->payment_reference)) {
            try {
                $this->mayaService->syncOrderStatusFromCheckout($order);
                $order->refresh();
            } catch (\Throwable $exception) {
                // Return latest stored status if live sync is temporarily unavailable.
            }
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
                'received_status' => $request->input('status') ?? data_get($request->all(), 'data.attributes.status'),
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
