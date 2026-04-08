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
        $response = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->get($this->baseUrl . '/checkout_sessions/' . $checkoutId);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch PayMongo checkout: ' . $response->body());
        }

        return $response->json('data') ?? [];
    }
}
