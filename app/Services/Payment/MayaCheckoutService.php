<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MayaCheckoutService
{
    public function createCheckout(Order $order, array $options = []): array
    {
        if (!config('services.maya.enabled', false)) {
            throw new \RuntimeException('Maya payment is currently disabled.');
        }

        $secretKey = config('services.maya.secret_key');
        $baseUrl = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');

        if (empty($secretKey)) {
            throw new \RuntimeException('Maya secret key is not configured.');
        }

        $amount = (float) ($order->total_amount ?? $order->total ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Invalid order amount for Maya checkout.');
        }

        $successUrl = $options['success_url'] ?? (config('app.url') . '/track-order');
        $failureUrl = $options['failure_url'] ?? (config('app.url') . '/track-order');
        $cancelUrl = $options['cancel_url'] ?? (config('app.url') . '/track-order');

        $payload = [
            'totalAmount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'PHP',
            ],
            'requestReferenceNumber' => $order->order_ref,
            'redirectUrl' => [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'cancel' => $cancelUrl,
            ],
            'buyer' => [
                'firstName' => $this->firstName($order->customer_name),
                'lastName' => $this->lastName($order->customer_name),
                'contact' => [
                    'phone' => (string) ($order->customer_phone ?? ''),
                    'email' => (string) ($order->customer_email ?? ''),
                ],
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => (string) ($item->product_name ?? ($item->product->name ?? ('Product #' . $item->product_id))),
                    'quantity' => (int) $item->quantity,
                    'totalAmount' => [
                        'value' => number_format((float) $item->price * (int) $item->quantity, 2, '.', ''),
                        'currency' => 'PHP',
                    ],
                ];
            })->values()->all(),
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->post($baseUrl . '/checkout/v1/checkouts', $payload);

        if (!$response->successful()) {
            Log::error('Maya checkout create failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Maya checkout request failed: HTTP ' . $response->status());
        }

        $data = $response->json() ?? [];
        $checkoutId = $data['checkoutId'] ?? $data['id'] ?? null;
        $checkoutUrl = $data['redirectUrl'] ?? $data['checkoutUrl'] ?? $data['url'] ?? null;

        if (!$checkoutId || !$checkoutUrl) {
            throw new \RuntimeException('Unexpected Maya checkout response payload.');
        }

        $order->payment_reference = $checkoutId;
        $order->payment_method = 'maya';
        $order->payment_status = 'pending';
        $order->save();

        return [
            'checkout_id' => $checkoutId,
            'checkout_url' => $checkoutUrl,
            'raw' => $data,
        ];
    }

    public function syncOrderStatusFromCheckout(Order $order, ?string $checkoutId = null): string
    {
        $reference = trim((string) ($checkoutId ?: $order->payment_reference));

        if ($reference === '') {
            return (string) $order->payment_status;
        }

        $checkout = $this->fetchCheckout($reference);
        $gatewayStatus = strtolower((string) (
            $checkout['status']
            ?? $checkout['paymentStatus']
            ?? data_get($checkout, 'data.attributes.status')
            ?? data_get($checkout, 'data.attributes.paymentStatus')
            ?? 'pending'
        ));

        if (str_contains($gatewayStatus, 'paid') || str_contains($gatewayStatus, 'success') || str_contains($gatewayStatus, 'complete')) {
            $order->payment_status = 'paid';
            if (in_array($order->status, ['pending', 'pending_confirmation', 'confirmed'], true)) {
                $order->status = 'processing';
            }
        } elseif (str_contains($gatewayStatus, 'fail') || str_contains($gatewayStatus, 'cancel') || str_contains($gatewayStatus, 'expire')) {
            $order->payment_status = 'failed';
        } else {
            $order->payment_status = 'pending';
        }

        if (empty($order->payment_reference)) {
            $order->payment_reference = $reference;
        }

        $order->save();

        return (string) $order->payment_status;
    }

    private function fetchCheckout(string $checkoutId): array
    {
        $secretKey = config('services.maya.secret_key');
        $baseUrl = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');

        if (empty($secretKey)) {
            throw new \RuntimeException('Maya secret key is not configured.');
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->get($baseUrl . '/checkout/v1/checkouts/' . urlencode($checkoutId));

        if (!$response->successful()) {
            throw new \RuntimeException('Maya checkout status request failed: HTTP ' . $response->status());
        }

        return $response->json() ?? [];
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = config('services.maya.webhook_secret');
        if (empty($secret) || empty($signature)) {
            return true;
        }

        $calculated = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($calculated, $signature);
    }

    public function processWebhook(array $payload): ?Order
    {
        $checkoutId = $payload['id']
            ?? data_get($payload, 'data.id')
            ?? data_get($payload, 'data.attributes.checkoutId')
            ?? data_get($payload, 'data.attributes.checkout_id');

        if (!$checkoutId) {
            return null;
        }

        $order = Order::where('payment_reference', $checkoutId)->first();
        if (!$order) {
            return null;
        }

        $normalizedStatus = strtolower(
            (string) ($payload['status']
                ?? data_get($payload, 'data.attributes.status')
                ?? data_get($payload, 'event_type')
                ?? 'pending')
        );

        if (str_contains($normalizedStatus, 'paid') || str_contains($normalizedStatus, 'success')) {
            $order->payment_status = 'paid';
            if ($order->status === 'pending') {
                $order->status = 'processing';
            }
        } elseif (str_contains($normalizedStatus, 'fail') || str_contains($normalizedStatus, 'cancel')) {
            $order->payment_status = 'failed';
        } else {
            $order->payment_status = 'pending';
        }

        $order->save();

        return $order;
    }

    private function firstName(?string $fullName): string
    {
        $name = trim((string) $fullName);
        if ($name === '') {
            return 'Customer';
        }

        return explode(' ', $name)[0] ?: 'Customer';
    }

    private function lastName(?string $fullName): string
    {
        $name = trim((string) $fullName);
        if ($name === '') {
            return 'Yakan';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        return count($parts) > 1 ? end($parts) : 'Yakan';
    }
}
