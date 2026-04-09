<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order Payment Receipt</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
    @php
        $customerName = $user->name ?? $order->email ?? 'Customer';
        $paidAt = $order->payment_confirmed_at ?? $order->payment_verified_at ?? $order->updated_at;
        $method = ucfirst(str_replace('_', ' ', (string) ($order->payment_method ?? 'Payment')));
        $reference = $order->transaction_id ?: 'N/A';
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;margin:20px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.08);">
        <tr>
            <td style="background:linear-gradient(135deg,#065f46 0%,#047857 100%);padding:28px;text-align:center;">
                <h1 style="margin:0;font-size:24px;color:#fff;">Custom Order Payment Confirmed</h1>
                <p style="margin:8px 0 0;color:rgba(255,255,255,0.92);font-size:14px;">Your payment receipt has been recorded successfully.</p>
            </td>
        </tr>

        <tr>
            <td style="padding:24px;">
                <p style="font-size:14px;color:#111827;margin:0 0 12px;">Hi {{ $customerName }},</p>
                <p style="font-size:14px;color:#4b5563;line-height:1.6;margin:0 0 16px;">Thank you for your purchase. This email serves as confirmation of your payment for your custom order.</p>

                <table width="100%" cellpadding="8" cellspacing="0" style="background:#f9fafb;border-radius:8px;">
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Order Reference</td>
                        <td style="font-size:13px;font-weight:700;text-align:right;">{{ $order->display_ref }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Items Covered</td>
                        <td style="font-size:13px;text-align:right;">{{ max(1, (int) $itemCount) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Payment Method</td>
                        <td style="font-size:13px;text-align:right;">{{ $method }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Transaction Reference</td>
                        <td style="font-size:13px;text-align:right;">{{ $reference }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Confirmed At</td>
                        <td style="font-size:13px;text-align:right;">{{ optional($paidAt)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:16px;font-weight:700;color:#065f46;border-top:2px solid #10b981;">Amount Paid</td>
                        <td style="font-size:16px;font-weight:700;color:#065f46;text-align:right;border-top:2px solid #10b981;">₱{{ number_format((float) $totalAmount, 2) }}</td>
                    </tr>
                </table>

                <p style="font-size:13px;color:#6b7280;margin:16px 0 0;">We will continue updating you as your custom order progresses.</p>
            </td>
        </tr>

        <tr>
            <td style="background:#f9fafb;padding:16px;text-align:center;border-top:1px solid #e5e7eb;">
                <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} Yakan - Custom Order Receipt Confirmation</p>
            </td>
        </tr>
    </table>
</body>
</html>
