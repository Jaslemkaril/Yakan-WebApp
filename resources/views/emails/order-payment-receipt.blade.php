<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    @php
        $reference = $order->order_ref ?? ('ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT));
        $totalAmount = (float) ($order->total_amount ?? $order->total ?? 0);
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
                        <td style="font-size:13px;color:#6b7280;">Paid At</td>
                        <td style="font-size:13px;text-align:right;">{{ optional($paidAt)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:16px;font-weight:700;color:#166534;border-top:2px solid #16a34a;">Amount Paid</td>
                        <td style="font-size:16px;font-weight:700;color:#166534;text-align:right;border-top:2px solid #16a34a;">₱{{ number_format($totalAmount, 2) }}</td>
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
