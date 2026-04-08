<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background:linear-gradient(135deg,#800000 0%,#600000 100%);padding:30px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;">Order Confirmed!</h1>
                <p style="color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;">Thank you for your order, {{ $order->user->name ?? 'Valued Customer' }}</p>
            </td>
        </tr>

        <!-- Order Info -->
        <tr>
            <td style="padding:24px;">
                <table width="100%" cellpadding="8" cellspacing="0" style="background:#f9fafb;border-radius:8px;margin-bottom:20px;">
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Order Reference</td>
                        <td style="font-size:13px;font-weight:bold;text-align:right;">{{ $order->order_ref ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Date</td>
                        <td style="font-size:13px;text-align:right;">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Payment Method</td>
                        <td style="font-size:13px;text-align:right;">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Delivery</td>
                        <td style="font-size:13px;text-align:right;">{{ $order->delivery_type === 'deliver' ? 'Home Delivery' : 'Store Pickup' }}</td>
                    </tr>
                </table>

                <!-- Items -->
                <h3 style="font-size:16px;color:#111827;margin:0 0 12px;">Order Items</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-top:2px solid #e5e7eb;">
                    <tr style="background:#f9fafb;">
                        <td style="font-size:12px;font-weight:bold;color:#6b7280;text-transform:uppercase;">Item</td>
                        <td style="font-size:12px;font-weight:bold;color:#6b7280;text-align:center;text-transform:uppercase;">Qty</td>
                        <td style="font-size:12px;font-weight:bold;color:#6b7280;text-align:right;text-transform:uppercase;">Amount</td>
                    </tr>
                    @foreach($order->orderItems as $item)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="font-size:13px;color:#111827;">{{ $item->product->name ?? 'Product' }}</td>
                        <td style="font-size:13px;color:#6b7280;text-align:center;">{{ $item->quantity }}</td>
                        <td style="font-size:13px;color:#111827;text-align:right;">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </table>

                <!-- Totals -->
                <table width="100%" cellpadding="6" cellspacing="0" style="margin-top:16px;">
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Subtotal</td>
                        <td style="font-size:13px;text-align:right;">₱{{ number_format($order->subtotal ?? $order->orderItems->sum(fn($i) => $i->price * $i->quantity), 2) }}</td>
                    </tr>
                    @if(($order->discount_amount ?? $order->discount ?? 0) > 0)
                    <tr>
                        <td style="font-size:13px;color:#16a34a;">Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</td>
                        <td style="font-size:13px;color:#16a34a;text-align:right;">-₱{{ number_format($order->discount_amount ?? $order->discount ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if($order->shipping_fee > 0)
                    <tr>
                        <td style="font-size:13px;color:#6b7280;">Shipping</td>
                        <td style="font-size:13px;text-align:right;">₱{{ number_format($order->shipping_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="font-size:16px;font-weight:bold;color:#800000;padding-top:12px;border-top:2px solid #800000;">Total</td>
                        <td style="font-size:16px;font-weight:bold;color:#800000;text-align:right;padding-top:12px;border-top:2px solid #800000;">₱{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>

                @if($order->delivery_address)
                <div style="margin-top:20px;padding:12px;background:#f9fafb;border-radius:8px;">
                    <p style="font-size:12px;font-weight:bold;color:#6b7280;text-transform:uppercase;margin:0 0 4px;">Delivery Address</p>
                    <p style="font-size:13px;color:#111827;margin:0;">{{ $order->delivery_address }}</p>
                </div>
                @endif
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f9fafb;padding:20px;text-align:center;border-top:1px solid #e5e7eb;">
                <p style="margin:0;font-size:13px;color:#6b7280;">You can track your order at any time on our website.</p>
                <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} Yakan — Weaving Through Generations</p>
            </td>
        </tr>
    </table>
</body>
</html>
