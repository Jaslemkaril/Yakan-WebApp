<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoCheckoutService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paymongo.com/v1';

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key', '');
    }

    /**
     * Create a PayMongo checkout session and return the checkout URL.
     */
    public function createCheckout(Order $order, array $options = []): array
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('PayMongo secret key is not configured.');
        }

        $amount = (float) ($order->total_amount ?? $order->total ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Order amount must be greater than zero.');
        }

        $successUrl = $options['success_url'] ?? config('app.url') . '/orders';
        $cancelUrl  = $options['cancel_url']  ?? config('app.url') . '/orders';

        // Build line items
        $lineItems = [];
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $lineItems[] = [
                'currency'   => 'PHP',
                'amount'     => (int) round(($item->price ?? 0) * 100), // in centavos
                'description' => $item->product->name ?? 'Product',
                'name'       => $item->product->name ?? 'Product',
                'quantity'   => (int) ($item->quantity ?? 1),
            ];
        }

        // Add shipping as a line item if applicable
        $shipping = (float) ($order->shipping_fee ?? 0);
        if ($shipping > 0) {
            $lineItems[] = [
                'currency'    => 'PHP',
                'amount'      => (int) round($shipping * 100),
                'description' => 'Shipping Fee',
                'name'        => 'Shipping Fee',
                'quantity'    => 1,
            ];
        }

        // If line items don't add up (e.g. discounts), use a single total item
        $lineItemsTotal = collect($lineItems)->sum(fn($i) => $i['amount'] * $i['quantity']);
        $orderTotal     = (int) round($amount * 100);
        if ($lineItemsTotal !== $orderTotal) {
            $lineItems = [[
                'currency'    => 'PHP',
                'amount'      => $orderTotal,
                'description' => 'Order ' . ($order->order_ref ?? $order->id),
                'name'        => 'Yakan Order',
                'quantity'    => 1,
            ]];
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'billing'              => [
                        'name'  => $order->user->name ?? 'Customer',
                        'email' => $order->user->email ?? '',
                        'phone' => $order->user->phone ?? '',
                    ],
                    'line_items'           => $lineItems,
                    'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                    'success_url'          => $successUrl,
                    'cancel_url'           => $cancelUrl,
                    'description'          => 'Order ' . ($order->order_ref ?? $order->id),
                    'reference_number'     => $order->order_ref ?? (string) $order->id,
                    'send_email_receipt'   => false,
                    'show_description'     => true,
                    'show_line_items'      => true,
                ],
            ],
        ];

        Log::info('PayMongo checkout session creating', [
            'order_id'    => $order->id,
            'total_cents' => $orderTotal,
        ]);

        $response = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->post($this->baseUrl . '/checkout_sessions', $payload);

        if (!$response->successful()) {
            Log::error('PayMongo checkout session failed', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \RuntimeException('PayMongo checkout session failed: ' . $response->body());
        }

        $data       = $response->json('data');
        $checkoutId = $data['id'] ?? null;
        $checkoutUrl = $data['attributes']['checkout_url'] ?? null;

        if (!$checkoutUrl) {
            throw new \RuntimeException('PayMongo checkout URL missing from response.');
        }

        // Save checkout session ID on the order
        $order->payment_reference = $checkoutId;
        $order->payment_method    = 'paymongo';
        $order->payment_status    = 'pending';
        $order->save();

        Log::info('PayMongo checkout session created', [
            'order_id'    => $order->id,
            'checkout_id' => $checkoutId,
            'checkout_url'=> $checkoutUrl,
        ]);

        return [
            'checkout_id'  => $checkoutId,
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Fetch a checkout session to verify payment status.
     */
    public function fetchCheckout(string $checkoutId): array
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('PayMongo secret key is not configured.');
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->get($this->baseUrl . '/checkout_sessions/' . $checkoutId);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch PayMongo checkout: ' . $response->body());
        }

        return $response->json('data') ?? [];
    }

    /**
     * Fetch a payment resource from PayMongo.
     */
    public function fetchPayment(string $paymentId): array
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('PayMongo secret key is not configured.');
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->get($this->baseUrl . '/payments/' . $paymentId);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch PayMongo payment: ' . $response->body());
        }

        return $response->json('data') ?? [];
    }

    /**
     * Build normalized and trusted receipt data from PayMongo resources.
     */
    public function getVerifiedReceiptForOrder(Order $order): array
    {
        $reference = trim((string) ($order->payment_reference ?? ''));
        $checkout = null;
        $payment = null;

        if ($reference !== '') {
            if (str_starts_with($reference, 'pay_')) {
                $payment = $this->fetchPayment($reference);
            } else {
                try {
                    $checkout = $this->fetchCheckout($reference);
                } catch (\Throwable $exception) {
                    Log::warning('Unable to fetch PayMongo checkout by payment_reference.', [
                        'order_id' => $order->id,
                        'reference' => $reference,
                        'error' => $exception->getMessage(),
                    ]);
                }

                if ($checkout) {
                    $checkoutPaymentId = $this->extractPaymentIdFromCheckout($checkout);
                    if ($checkoutPaymentId) {
                        try {
                            $payment = $this->fetchPayment($checkoutPaymentId);
                        } catch (\Throwable $exception) {
                            Log::warning('Unable to fetch PayMongo payment from checkout payload.', [
                                'order_id' => $order->id,
                                'payment_id' => $checkoutPaymentId,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }

        $paymentId = $this->firstNonEmpty([
            data_get($payment, 'id'),
            data_get($checkout, 'attributes.payments.0.id'),
            data_get($checkout, 'attributes.payments.0.attributes.id'),
            data_get($checkout, 'attributes.payment.id'),
            data_get($checkout, 'attributes.payment_intent.attributes.latest_payment.id'),
            $order->payment_reference,
        ]);

        $referenceNumber = $this->firstNonEmpty([
            data_get($checkout, 'attributes.reference_number'),
            data_get($checkout, 'attributes.referenceNumber'),
            $order->order_ref,
            'ORDER-' . $order->id,
        ]);

        $status = strtolower((string) $this->firstNonEmpty([
            data_get($payment, 'attributes.status'),
            data_get($checkout, 'attributes.payment_intent.attributes.status'),
            data_get($checkout, 'attributes.payment_intent.attributes.payment_status'),
            data_get($checkout, 'attributes.status'),
            $order->payment_status,
            'unknown',
        ]));

        $amountCents = $this->firstNonEmpty([
            data_get($payment, 'attributes.amount'),
            data_get($checkout, 'attributes.payments.0.attributes.amount'),
            data_get($checkout, 'attributes.amount_total'),
            data_get($checkout, 'attributes.amountTotal'),
        ]);

        $amount = is_numeric($amountCents)
            ? ((float) $amountCents / 100)
            : (float) ($order->total_amount ?? $order->total ?? 0);

        $currency = strtoupper((string) $this->firstNonEmpty([
            data_get($payment, 'attributes.currency'),
            data_get($checkout, 'attributes.payments.0.attributes.currency'),
            data_get($checkout, 'attributes.currency'),
            'PHP',
        ]));

        $methodRaw = strtolower((string) $this->firstNonEmpty([
            data_get($payment, 'attributes.source.type'),
            data_get($payment, 'attributes.source.attributes.type'),
            data_get($checkout, 'attributes.payments.0.attributes.source.type'),
            data_get($checkout, 'attributes.payments.0.attributes.source.attributes.type'),
            data_get($checkout, 'attributes.payment_method_used'),
            data_get($checkout, 'attributes.paymentMethodUsed'),
            'paymongo',
        ]));

        $method = match ($methodRaw) {
            'paymaya', 'maya' => 'Maya',
            'gcash' => 'GCash',
            'card' => 'Card',
            'grab_pay', 'grabpay' => 'GrabPay',
            default => ucfirst(str_replace('_', ' ', $methodRaw)),
        };

        $paidAtRaw = $this->firstNonEmpty([
            data_get($payment, 'attributes.paid_at'),
            data_get($payment, 'attributes.created_at'),
            data_get($checkout, 'attributes.payments.0.attributes.paid_at'),
            data_get($checkout, 'attributes.payments.0.attributes.created_at'),
            data_get($checkout, 'attributes.updated_at'),
            optional($order->payment_verified_at)->toISOString(),
        ]);

        $paidAtIso = null;
        if ($paidAtRaw) {
            try {
                $paidAtIso = \Carbon\Carbon::parse((string) $paidAtRaw)->toIso8601String();
            } catch (\Throwable $exception) {
                $paidAtIso = null;
            }
        }

        return [
            'gateway' => 'PayMongo',
            'verified' => true,
            'reference_number' => $referenceNumber,
            'payment_id' => $paymentId,
            'status' => $status,
            'payment_method' => $method,
            'amount' => round($amount, 2),
            'currency' => $currency,
            'paid_at' => $paidAtIso,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    private function extractPaymentIdFromCheckout(array $checkout): ?string
    {
        return $this->firstNonEmpty([
            data_get($checkout, 'attributes.payments.0.id'),
            data_get($checkout, 'attributes.payments.0.attributes.id'),
            data_get($checkout, 'attributes.payment.id'),
            data_get($checkout, 'attributes.payment_intent.attributes.latest_payment.id'),
        ]);
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $text = is_scalar($value) ? trim((string) $value) : '';
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }
}
