<?php

namespace App\Services\Payment;

use App\Models\CustomOrder;
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

        $totalAmount = (float) ($order->total_amount ?? $order->total ?? 0);
        $amountOverride = isset($options['amount_override']) ? (float) $options['amount_override'] : null;
        $isDownpaymentOverride = array_key_exists('is_downpayment_override', $options)
            ? (bool) $options['is_downpayment_override']
            : null;

        $amount = $this->resolveCheckoutAmount($order);
        if (!is_null($amountOverride) && $amountOverride > 0) {
            $cap = $totalAmount > 0 ? $totalAmount : $amountOverride;
            $amount = max(0, min($cap, $amountOverride));
        }

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
        $isDownpayment = is_bool($isDownpaymentOverride)
            ? $isDownpaymentOverride
            : $this->isDownpaymentOrder($order);

        if ($lineItemsTotal !== $orderTotal) {
            $lineItemName = $isDownpayment ? 'Yakan Order Downpayment' : 'Yakan Order';
            $lineItemDescription = $isDownpayment
                ? 'Downpayment for order ' . ($order->order_ref ?? $order->id)
                : 'Order ' . ($order->order_ref ?? $order->id);

            $lineItems = [[
                'currency'    => 'PHP',
                'amount'      => $orderTotal,
                'description' => $lineItemDescription,
                'name'        => $lineItemName,
                'quantity'    => 1,
            ]];
        }

        $paymentDescription = $isDownpayment
            ? 'Downpayment for order ' . ($order->order_ref ?? $order->id)
            : 'Order ' . ($order->order_ref ?? $order->id);

        $payload = [
            'data' => [
                'attributes' => [
                    'billing'              => [
                        'name'  => $order->user->name ?? 'Customer',
                        'email' => $order->user->email ?? '',
                        'phone' => $order->user->phone ?? '',
                    ],
                    'line_items'           => $lineItems,
                    'payment_method_types' => ['card', 'gcash', 'grab_pay'],
                    'success_url'          => $successUrl,
                    'cancel_url'           => $cancelUrl,
                    'description'          => $paymentDescription,
                    'reference_number'     => $order->order_ref ?? (string) $order->id,
                    'send_email_receipt'   => false,
                    'show_description'     => true,
                    'show_line_items'      => true,
                ],
            ],
        ];

        Log::info('PayMongo checkout session creating', [
            'order_id'    => $order->id,
            'total_amount' => $totalAmount,
            'payable_amount' => $amount,
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

    private function isDownpaymentOrder(Order $order): bool
    {
        return strtolower((string) ($order->payment_option ?? 'full')) === 'downpayment';
    }

    private function resolveCheckoutAmount(Order $order): float
    {
        $total = (float) ($order->total_amount ?? $order->total ?? 0);
        if ($total <= 0) {
            return 0;
        }

        if (!$this->isDownpaymentOrder($order)) {
            return $total;
        }

        $downpaymentAmount = (float) ($order->downpayment_amount ?? 0);
        if ($downpaymentAmount <= 0) {
            $rate = (float) ($order->downpayment_rate ?? 50);
            $rate = min(99, max(1, $rate));
            $downpaymentAmount = round($total * ($rate / 100), 2);
        }

        return max(0, min($total, $downpaymentAmount));
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
            data_get($payment, 'attributes.updated_at'),
            data_get($payment, 'attributes.created_at'),
            data_get($checkout, 'attributes.payments.0.attributes.paid_at'),
            data_get($checkout, 'attributes.payments.0.attributes.updated_at'),
            data_get($checkout, 'attributes.payments.0.attributes.created_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.paid_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.updated_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.created_at'),
            data_get($checkout, 'attributes.completed_at'),
            data_get($checkout, 'attributes.updated_at'),
            data_get($checkout, 'attributes.created_at'),
            optional($order->payment_verified_at)->toISOString(),
            optional($order->updated_at)->toISOString(),
            optional($order->created_at)->toISOString(),
        ]);

        $paidAtIso = $this->normalizeTimestamp($paidAtRaw)
            ?? optional($order->payment_verified_at)->toIso8601String()
            ?? optional($order->updated_at)->toIso8601String()
            ?? optional($order->created_at)->toIso8601String()
            ?? now()->toIso8601String();

        $customerName = $this->firstNonEmpty([
            data_get($payment, 'attributes.billing.name'),
            data_get($checkout, 'attributes.billing.name'),
            optional($order->user)->name,
            $order->customer_name,
            'Customer',
        ]);

        $customerEmail = $this->firstNonEmpty([
            data_get($payment, 'attributes.billing.email'),
            data_get($checkout, 'attributes.billing.email'),
            optional($order->user)->email,
            $order->customer_email,
            'N/A',
        ]);

        $deliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
        if ($deliveryType === 'deliver') {
            $deliveryType = 'delivery';
        }

        $shippingCity = trim((string) ($order->shipping_city ?? ''));
        $shippingProvince = trim((string) ($order->shipping_province ?? ''));
        $pickupHint = strtolower(trim($shippingCity . ' ' . $shippingProvince));
        $isPickup = $deliveryType === 'pickup' || str_contains($pickupHint, 'store pickup');

        $shippingLabel = $isPickup ? 'Store Pickup' : 'Home Delivery';
        $shippingAddress = trim((string) ($order->delivery_address ?: $order->shipping_address ?: ''));
        if ($isPickup && $shippingAddress === '') {
            $shippingAddress = 'Yakan Village, Brgy. Upper Calarian, Zamboanga City, Philippines 7000';
        }

        $shippingCityProvince = trim($shippingCity . ($shippingCity !== '' && $shippingProvince !== '' ? ', ' : '') . $shippingProvince);
        if ($isPickup && $shippingCityProvince === '') {
            $shippingCityProvince = 'Zamboanga City, Zamboanga del Sur';
        }

        $recipientName = $this->firstNonEmpty([
            optional($order->userAddress)->full_name,
            $order->customer_name,
            optional($order->user)->name,
            $customerName,
            'Customer',
        ]);

        $recipientPhone = $this->firstNonEmpty([
            optional($order->userAddress)->phone_number,
            $order->customer_phone,
            optional($order->user)->phone,
            'N/A',
        ]);

        $deliveryLatitude = is_numeric($order->delivery_latitude ?? null)
            ? (float) $order->delivery_latitude
            : null;
        $deliveryLongitude = is_numeric($order->delivery_longitude ?? null)
            ? (float) $order->delivery_longitude
            : null;

        $deliveryMapUrl = null;
        if ($deliveryLatitude !== null && $deliveryLongitude !== null) {
            $deliveryMapUrl = 'https://www.google.com/maps?q=' . $deliveryLatitude . ',' . $deliveryLongitude;
        } elseif ($shippingAddress !== '') {
            $deliveryMapUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($shippingAddress);
        }

        return [
            'gateway' => 'PayMongo',
            'verified' => true,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'delivery_type' => $deliveryType,
            'shipping_label' => $shippingLabel,
            'shipping_address' => $shippingAddress !== '' ? $shippingAddress : null,
            'shipping_city_province' => $shippingCityProvince !== '' ? $shippingCityProvince : null,
            'delivery_latitude' => $deliveryLatitude,
            'delivery_longitude' => $deliveryLongitude,
            'delivery_map_url' => $deliveryMapUrl,
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

    /**
     * Build normalized and trusted receipt data for custom orders.
     */
    public function getVerifiedReceiptForCustomOrder(CustomOrder $order): array
    {
        $reference = trim((string) ($order->transaction_id ?? ''));
        $checkout = null;
        $payment = null;

        if ($reference !== '') {
            if (str_starts_with($reference, 'pay_')) {
                $payment = $this->fetchPayment($reference);
            } else {
                try {
                    $checkout = $this->fetchCheckout($reference);
                } catch (\Throwable $exception) {
                    Log::warning('Unable to fetch PayMongo checkout by custom order transaction_id.', [
                        'custom_order_id' => $order->id,
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
                            Log::warning('Unable to fetch PayMongo payment from custom order checkout payload.', [
                                'custom_order_id' => $order->id,
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
            $order->transaction_id,
        ]);

        $gatewayReferenceNumber = $this->firstNonEmpty([
            data_get($checkout, 'id'),
            data_get($checkout, 'attributes.id'),
            data_get($checkout, 'attributes.checkout_id'),
            data_get($checkout, 'attributes.checkoutId'),
            (str_starts_with($reference, 'cs_') ? $reference : null),
            (str_starts_with((string) ($order->transaction_id ?? ''), 'cs_') ? (string) $order->transaction_id : null),
        ]);

        $merchantReferenceNumber = $this->firstNonEmpty([
            data_get($checkout, 'attributes.reference_number'),
            data_get($checkout, 'attributes.referenceNumber'),
            $order->display_ref,
            'CO-' . $order->id,
        ]);

        $referenceNumber = $this->firstNonEmpty([
            $gatewayReferenceNumber,
            $merchantReferenceNumber,
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
            : (float) ($order->final_price ?? $order->estimated_price ?? 0);

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
            $order->payment_method,
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
            data_get($payment, 'attributes.updated_at'),
            data_get($payment, 'attributes.created_at'),
            data_get($checkout, 'attributes.payments.0.attributes.paid_at'),
            data_get($checkout, 'attributes.payments.0.attributes.updated_at'),
            data_get($checkout, 'attributes.payments.0.attributes.created_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.paid_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.updated_at'),
            data_get($checkout, 'attributes.payment_intent.attributes.created_at'),
            data_get($checkout, 'attributes.completed_at'),
            data_get($checkout, 'attributes.updated_at'),
            data_get($checkout, 'attributes.created_at'),
            $order->payment_confirmed_at,
            $order->payment_verified_at,
            $order->paid_at,
            $order->transfer_date,
            optional($order->updated_at)->toISOString(),
            optional($order->created_at)->toISOString(),
        ]);

        $paidAtIso = $this->normalizeTimestamp($paidAtRaw)
            ?? optional($order->updated_at)->toIso8601String()
            ?? optional($order->created_at)->toIso8601String()
            ?? now()->toIso8601String();

        $customerName = $this->firstNonEmpty([
            data_get($payment, 'attributes.billing.name'),
            data_get($checkout, 'attributes.billing.name'),
            optional($order->user)->name,
            'Customer',
        ]);

        $customerEmail = $this->firstNonEmpty([
            data_get($payment, 'attributes.billing.email'),
            data_get($checkout, 'attributes.billing.email'),
            optional($order->user)->email,
            $order->email,
            'N/A',
        ]);

        return [
            'gateway' => 'PayMongo',
            'verified' => true,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
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

    private function normalizeTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;

            // PayMongo values can arrive in milliseconds; convert when needed.
            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            if ($timestamp > 0) {
                return \Carbon\Carbon::createFromTimestampUTC($timestamp)->toIso8601String();
            }

            return null;
        }

        $text = is_scalar($value) ? trim((string) $value) : '';
        if ($text === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($text)->toIso8601String();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
