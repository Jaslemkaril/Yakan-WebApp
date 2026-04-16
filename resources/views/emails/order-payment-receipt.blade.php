<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    @php
        $reference = $order->order_ref ?? ('ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT));
        $orderTotalAmount = (float) ($order->total_amount ?? $order->total ?? 0);
        $paymentOption = strtolower((string) ($order->payment_option ?? 'full'));
        $isDownpayment = $paymentOption === 'downpayment';
        $remainingBalance = (float) ($order->remaining_balance ?? 0);
        $downpaymentAmount = (float) ($order->downpayment_amount ?? 0);
        $amountPaid = ($isDownpayment && $remainingBalance > 0)
            ? ($downpaymentAmount > 0 ? $downpaymentAmount : max(0, $orderTotalAmount - $remainingBalance))
            : $orderTotalAmount;

        $deliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
        if ($deliveryType === 'deliver') {
            $deliveryType = 'delivery';
        }

        $shippingLabel = $deliveryType === 'pickup' ? 'Store Pickup' : 'Home Delivery';
        $shippingAddress = trim((string) ($order->delivery_address ?: $order->shipping_address ?: ''));
        if ($shippingLabel === 'Store Pickup' && $shippingAddress === '') {
            $shippingAddress = 'Yakan Village, Brgy. Upper Calarian, Zamboanga City, Philippines 7000';
        }

        $shippingCity = trim((string) ($order->shipping_city ?? ''));
        $shippingProvince = trim((string) ($order->shipping_province ?? ''));
        $cityProvince = trim($shippingCity . ($shippingCity !== '' && $shippingProvince !== '' ? ', ' : '') . $shippingProvince);
        if ($shippingLabel === 'Store Pickup' && $cityProvince === '') {
            $cityProvince = 'Zamboanga City, Zamboanga del Sur';
        }

        $recipientName = $order->userAddress->full_name ?? $order->customer_name ?? $order->user->name ?? 'Customer';
        $recipientPhone = $order->userAddress->phone_number ?? $order->customer_phone ?? $order->user->phone ?? 'N/A';

        $hasCoordinates = is_numeric($order->delivery_latitude ?? null) && is_numeric($order->delivery_longitude ?? null);
        $coordinates = $hasCoordinates
            ? number_format((float) $order->delivery_latitude, 6) . ', ' . number_format((float) $order->delivery_longitude, 6)
            : null;

        $mapLink = null;
        if ($hasCoordinates) {
            $mapLink = 'https://www.google.com/maps?q=' . (float) $order->delivery_latitude . ',' . (float) $order->delivery_longitude;
        } elseif ($shippingAddress !== '') {
            $mapLink = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($shippingAddress);
        }

        $paidAt = $order->payment_verified_at ?? $order->updated_at;
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <tr>
            <td style="background:linear-gradient(135deg,#166534 0%,#14532d 100%);padding:30px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;">Payment Received</h1>
                <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:14px;">This is your official payment receipt confirmation.</p>
            </td>
        </tr>

        <tr>
            <td style="padding:24px;">
                <p style="font-size:14px;color:#111827;margin-top:0;">Hi {{ $order->user->name ?? $order->customer_name ?? 'Customer' }},</p>
                <p style="font-size:14px;color:#4b5563;line-height:1.6;">Your payment has been confirmed and your order is now queued for processing.</p>

                <table width="100%" cellpadding="8" cellspacing="0" style="background:#f9fafb;border-radius:8px;margin:16px 0;">
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Order Reference</td>
                        <td style="font-size:13px;font-weight:700;text-align:right;">{{ $reference }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Payment Method</td>
                        <td style="font-size:13px;text-align:right;">{{ ucfirst(str_replace('_', ' ', (string) $order->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Payment Reference</td>
                        <td style="font-size:13px;text-align:right;">{{ $order->payment_reference ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Shipping Label</td>
                        <td style="font-size:13px;text-align:right;">{{ $shippingLabel }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Recipient</td>
                        <td style="font-size:13px;text-align:right;">{{ $recipientName }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Contact Number</td>
                        <td style="font-size:13px;text-align:right;">{{ $recipientPhone }}</td>
                    </tr>
                    @if($shippingAddress !== '')
                    <tr>
                        <td style="font-size:13px;color:#6b7280;vertical-align:top;">Shipping Address</td>
                        <td style="font-size:13px;text-align:right;">{{ $shippingAddress }}</td>
                    </tr>
                    @endif
                    @if($cityProvince !== '')
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">City / Province</td>
                        <td style="font-size:13px;text-align:right;">{{ $cityProvince }}</td>
                    </tr>
                    @endif
                    @if($coordinates)
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Location Coordinates</td>
                        <td style="font-size:13px;text-align:right;">{{ $coordinates }}</td>
                    </tr>
                    @endif
                    @if($mapLink)
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Location Map</td>
                        <td style="font-size:13px;text-align:right;"><a href="{{ $mapLink }}" target="_blank" rel="noopener noreferrer" style="color:#166534;font-weight:600;text-decoration:none;">Open Map</a></td>
                    </tr>
                    @endif
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Paid At</td>
                        <td style="font-size:13px;text-align:right;">{{ optional($paidAt)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Payment Option</td>
                        <td style="font-size:13px;text-align:right;">{{ $isDownpayment ? ($remainingBalance > 0 ? 'Downpayment' : 'Downpayment (Settled)') : 'Full Payment' }}</td>
                    </tr>
                    @if($isDownpayment && $remainingBalance > 0)
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Remaining Balance</td>
                        <td style="font-size:13px;text-align:right;">₱{{ number_format($remainingBalance, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="font-size:16px;font-weight:700;color:#166534;border-top:2px solid #16a34a;">Amount Paid</td>
                        <td style="font-size:16px;font-weight:700;color:#166534;text-align:right;border-top:2px solid #16a34a;">₱{{ number_format($amountPaid, 2) }}</td>
                    </tr>
                </table>

                <p style="font-size:13px;color:#6b7280;">You can view your order updates anytime from your account order history.</p>
            </td>
        </tr>

        <tr>
            <td style="background:#f9fafb;padding:18px;text-align:center;border-top:1px solid #e5e7eb;">
                <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} Yakan - Payment Receipt Confirmation</p>
            </td>
        </tr>
    </table>
</body>
</html>
