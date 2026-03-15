<?php

namespace App\Services\Payment;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
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

        if (empty($order->payment_reference)) {
            $order->payment_reference = $reference;
        }

        $this->applyGatewayStatusToOrder($order, $gatewayStatus, 'checkout_sync');

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

        if (empty($secret)) {
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $signature = trim((string) $signature);
        $normalized = $signature;

        if (str_contains($signature, '=')) {
            $parts = explode('=', $signature, 2);
            if (!empty($parts[1])) {
                $normalized = trim($parts[1]);
            }
        }

        $hexSignature = hash_hmac('sha256', $rawBody, $secret);
        $base64Signature = base64_encode(hex2bin($hexSignature));

        foreach (array_filter(array_unique([$signature, $normalized])) as $incoming) {
            if (hash_equals($hexSignature, $incoming) || hash_equals($base64Signature, $incoming)) {
                return true;
            }
        }

        return false;
    }

    public function processWebhook(array $payload): ?Order
    {
        $checkoutId = $payload['id']
            ?? data_get($payload, 'data.id')
            ?? data_get($payload, 'data.attributes.checkoutId')
            ?? data_get($payload, 'data.attributes.checkout_id');

        $referenceNumber = $payload['requestReferenceNumber']
            ?? data_get($payload, 'requestReferenceNumber')
            ?? data_get($payload, 'data.attributes.requestReferenceNumber')
            ?? data_get($payload, 'data.attributes.request_reference_number');

        if (!$checkoutId && !$referenceNumber) {
            return null;
        }

        $order = null;

        if ($checkoutId) {
            $order = Order::where('payment_reference', $checkoutId)->first();
        }

        if (!$order && $referenceNumber) {
            $order = Order::where('order_ref', $referenceNumber)->first();
        }

        if (!$order) {
            return null;
        }

        if (!empty($checkoutId) && empty($order->payment_reference)) {
            $order->payment_reference = $checkoutId;
        }

        $normalizedStatus = strtolower(
            (string) ($payload['status']
                ?? data_get($payload, 'data.attributes.status')
                ?? data_get($payload, 'event_type')
                ?? 'pending')
        );

        $this->applyGatewayStatusToOrder($order, $normalizedStatus, 'webhook');

        return $order;
    }

    private function applyGatewayStatusToOrder(Order $order, string $gatewayStatus, string $source): void
    {
        $previousPaymentStatus = (string) $order->payment_status;
        $previousOrderStatus = (string) $order->status;

        $nextPaymentStatus = $this->resolvePaymentStatus($gatewayStatus);
        $order->payment_status = $nextPaymentStatus;

        if ($nextPaymentStatus === 'paid') {
            if (in_array((string) $order->status, ['pending', 'pending_confirmation', 'confirmed'], true)) {
                $order->status = 'processing';
            }

            if (empty($order->payment_verified_at)) {
                $order->payment_verified_at = now();
            }
        }

        $order->save();

        $this->afterStatusTransition($order, $previousPaymentStatus, $previousOrderStatus, $source, $gatewayStatus);
    }

    private function resolvePaymentStatus(string $gatewayStatus): string
    {
        $status = strtolower($gatewayStatus);

        if (str_contains($status, 'paid') || str_contains($status, 'success') || str_contains($status, 'complete') || str_contains($status, 'capture')) {
            return 'paid';
        }

        if (str_contains($status, 'fail') || str_contains($status, 'cancel') || str_contains($status, 'expire') || str_contains($status, 'declin')) {
            return 'failed';
        }

        return 'pending';
    }

    private function afterStatusTransition(
        Order $order,
        string $previousPaymentStatus,
        string $previousOrderStatus,
        string $source,
        string $gatewayStatus
    ): void {
        if ($previousPaymentStatus === (string) $order->payment_status && $previousOrderStatus === (string) $order->status) {
            return;
        }

        if ((string) $order->payment_status === 'paid' && $previousPaymentStatus !== 'paid') {
            $order->appendTrackingEvent('Maya payment confirmed (' . str_replace('_', ' ', $source) . ').');
            $order->save();

            Notification::createNotification(
                $order->user_id,
                'payment',
                'Maya payment confirmed',
                'Your Maya payment for order #' . $order->id . ' has been confirmed.',
                url('/orders/' . $order->id),
                [
                    'order_id' => $order->id,
                    'payment_method' => 'maya',
                    'payment_status' => 'paid',
                    'source' => $source,
                ]
            );

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'payment',
                    'Maya payment received',
                    'Order #' . $order->id . ' was paid via Maya. Amount: ₱' . number_format((float) ($order->total_amount ?? $order->total ?? 0), 2),
                    url('/admin/orders/' . $order->id),
                    [
                        'order_id' => $order->id,
                        'payment_method' => 'maya',
                        'payment_status' => 'paid',
                        'source' => $source,
                    ]
                );
            }
        }

        if ((string) $order->payment_status === 'failed' && $previousPaymentStatus !== 'failed') {
            $order->appendTrackingEvent('Maya payment failed or cancelled (' . str_replace('_', ' ', $source) . ').');
            $order->save();

            Notification::createNotification(
                $order->user_id,
                'payment',
                'Maya payment not completed',
                'Your Maya payment for order #' . $order->id . ' was not completed (' . $gatewayStatus . ').',
                url('/orders/' . $order->id),
                [
                    'order_id' => $order->id,
                    'payment_method' => 'maya',
                    'payment_status' => 'failed',
                    'source' => $source,
                ]
            );
        }
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
