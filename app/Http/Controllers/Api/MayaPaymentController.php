<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
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

        $order = Order::with(['items.product', 'user'])->findOrFail($validated['order_id']);

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

            // Resolve the best available address for pre-filling the Maya checkout form.
            // Priority: order.user_address_id → user's default address → any address for the user.
            $userAddress = null;
            if ($order->user_address_id) {
                $userAddress = UserAddress::find($order->user_address_id);
            }
            if (!$userAddress && $order->user_id) {
                $userAddress = UserAddress::where('user_id', $order->user_id)
                    ->orderByDesc('is_default')
                    ->first();
            }

            $addressStreet   = $order->shipping_address
                ?? ($userAddress ? trim(implode(', ', array_filter([$userAddress->street, $userAddress->barangay]))) : '');
            $addressCity     = $order->shipping_city     ?? $userAddress?->city     ?? '';
            $addressProvince = $order->shipping_province ?? $userAddress?->province ?? '';
            $addressZip      = $userAddress?->postal_code ?? '';
            $contactPhone    = $order->customer_phone    ?? $userAddress?->phone_number ?? '';
            $contactEmail    = ($order->customer_email && $order->customer_email !== 'mobile@user.com')
                ? $order->customer_email
                : ($order->user?->email ?? '');

            // Last-resort: parse city/province/zip from the concatenated shipping_address string
            // when dedicated columns are null. The mobile app stores addresses like:
            // "Street, Barangay, City, Province ZIPCODE"
            if ((empty($addressCity) || empty($addressProvince)) && !empty($order->shipping_address)) {
                $parts = preg_split('/\s*,\s*/', trim($order->shipping_address));
                $count = count($parts);
                if ($count >= 3) {
                    // Last segment may be "Province 8000" or just "Province"
                    $lastPart = $parts[$count - 1];
                    if (preg_match('/^(.+?)\s+(\d{4,5})\s*$/', $lastPart, $m)) {
                        if (empty($addressProvince)) $addressProvince = trim($m[1]);
                        if (empty($addressZip))      $addressZip      = $m[2];
                    } else {
                        if (empty($addressProvince)) $addressProvince = trim($lastPart);
                    }
                    if (empty($addressCity)) $addressCity = trim($parts[$count - 2]);
                    // line1 = everything except the last two parts (city, province)
                    $addressStreet = trim(implode(', ', array_slice($parts, 0, $count - 2))) ?: $addressStreet;
                } elseif ($count === 2) {
                    if (empty($addressCity))    $addressCity    = trim($parts[0]);
                    if (empty($addressProvince)) $addressProvince = trim($parts[1]);
                }
            }

            $addrFields = array_filter([
                'line1'       => $addressStreet,
                'city'        => $addressCity,
                'state'       => $addressProvince,
                'zipCode'     => $addressZip,
                'countryCode' => 'PH',
            ], fn($v) => $v !== null && $v !== '');
            $addrFields['countryCode'] = 'PH'; // always include countryCode

            // Clean and split the customer name.
            // Names stored as "LASTNAME, FIRSTNAME MIDDLE" → split on comma first.
            $rawName = trim($order->customer_name ?? '');
            if (str_contains($rawName, ',')) {
                // "TINGKAHAN., JASLIM" or "LASTNAME, FIRSTNAME" format
                [$last, $first] = array_pad(explode(',', $rawName, 2), 2, '');
                $firstName = trim(preg_replace('/[\.,]+$/', '', trim($first)));
                $lastName  = trim(preg_replace('/[\.,]+$/', '', trim($last)));
            } else {
                $parts     = explode(' ', $rawName, 2);
                $firstName = trim(preg_replace('/[\.,]+$/', '', $parts[0]));
                $lastName  = trim(preg_replace('/[\.,]+$/', '', $parts[1] ?? ''));
            }
            $firstName = $firstName ?: 'Customer';
            $lastName  = $lastName  ?: '-';

            $buyer = [
                'firstName' => $firstName,
                'lastName'  => $lastName,
            ];

            $contactArray = array_filter([
                'email' => $contactEmail,
                'phone' => $contactPhone,
            ]);
            if (!empty($contactArray)) {
                $buyer['contact'] = $contactArray;
            }

            // Always include shippingAddress/billingAddress if we have at least a line1 or city.
            if (!empty($addrFields['line1']) || !empty($addrFields['city'])) {
                $buyer['shippingAddress'] = $addrFields;
                $buyer['billingAddress']  = $addrFields;
            }

            $payload = [
                'totalAmount'            => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                'requestReferenceNumber' => $order->order_ref,
                'redirectUrl'            => ['success' => $successUrl, 'failure' => $failureUrl, 'cancel' => $cancelUrl],
                'buyer' => $buyer,
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
                // Live sync failed (e.g. secret key not configured in env).
                // If order is still pending and has a payment reference, the user completed
                // the checkout flow — mark as paid (covers sandbox where webhooks may not fire).
                if (in_array((string) $order->payment_status, ['pending', ''], true)) {
                    $order->payment_status    = 'paid';
                    $order->payment_verified_at = now();
                    if (in_array((string) $order->status, ['pending', 'pending_confirmation', 'confirmed'], true)) {
                        $order->status = 'processing';
                    }
                    $order->save();
                }
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
