<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order {{ $order->order_ref ?? ('#' . $order->id) }}</title>
    <style>
        :root {
            --brand: #800000;
            --brand-soft: #fdf2f2;
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --bg: #f9fafb;
            --ok: #065f46;
            --ok-bg: #d1fae5;
            --warn: #92400e;
            --warn-bg: #fef3c7;
            --danger: #991b1b;
            --danger-bg: #fee2e2;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .sheet {
            max-width: 980px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }

        .toolbar {
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .print-btn {
            border: 0;
            background: var(--brand);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #630000;
        }

        .invoice {
            padding: 28px;
        }

        .head {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--brand);
        }

        .brand h1 {
            margin: 0 0 8px;
            font-size: 30px;
            color: var(--brand);
            letter-spacing: 0.3px;
        }

        .brand p {
            margin: 3px 0;
            color: var(--muted);
            font-size: 13px;
        }

        .meta {
            text-align: right;
        }

        .meta h2 {
            margin: 0 0 10px;
            font-size: 25px;
        }

        .meta p {
            margin: 5px 0;
            font-size: 13px;
            color: #374151;
        }

        .pill {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .pill-ok { background: var(--ok-bg); color: var(--ok); }
        .pill-warn { background: var(--warn-bg); color: var(--warn); }
        .pill-danger { background: var(--danger-bg); color: var(--danger); }
        .pill-muted { background: #f3f4f6; color: #374151; }

        .cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin: 18px 0;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
        }

        .card h3 {
            margin: 0 0 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--brand);
        }

        .kv {
            margin: 4px 0;
            font-size: 13px;
            color: #374151;
            line-height: 1.45;
        }

        .kv strong { color: #111827; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead th {
            text-align: left;
            font-size: 12px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #374151;
            background: #f3f4f6;
            padding: 11px 10px;
            border-bottom: 1px solid var(--line);
        }

        tbody td {
            padding: 11px 10px;
            border-bottom: 1px solid #eef2f7;
            font-size: 13px;
            vertical-align: top;
        }

        .num { text-align: right; white-space: nowrap; }

        .desc .title {
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }

        .desc .sub {
            color: #6b7280;
            font-size: 12px;
        }

        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 360px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            background: #fcfcfd;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #374151;
            padding: 5px 0;
        }

        .row.total {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 2px solid var(--brand);
            color: var(--brand);
            font-size: 18px;
            font-weight: 800;
        }

        .payment-note {
            margin-top: 14px;
            background: var(--brand-soft);
            border: 1px solid #f6d1d1;
            border-radius: 10px;
            color: #6e1111;
            font-size: 12px;
            padding: 10px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 22px;
            border-top: 1px dashed #d1d5db;
            padding-top: 14px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .sheet { border: 0; border-radius: 0; }
            .toolbar { display: none !important; }
            .invoice { padding: 16px; }
        }
    </style>
</head>
<body>
@php
    $resolveStatusClass = function ($status) {
        return match (strtolower((string) $status)) {
            'paid', 'verified', 'completed', 'delivered' => 'pill-ok',
            'pending', 'pending_confirmation', 'confirmed', 'processing', 'shipped' => 'pill-warn',
            'failed', 'cancelled', 'rejected', 'refunded' => 'pill-danger',
            default => 'pill-muted',
        };
    };

    $resolveStatusLabel = function ($status) {
        $value = strtolower((string) $status);
        if ($value === '') return 'N/A';
        return ucwords(str_replace('_', ' ', $value));
    };

    $resolvePaymentMethod = function ($method) {
        $value = strtolower((string) $method);
        return match ($value) {
            'paymongo' => 'PayMongo',
            'maya' => 'Maya',
            'online', 'online_banking' => 'Online Banking',
            'bank_transfer' => 'Bank Transfer',
            'gcash' => 'GCash',
            'cash' => 'Cash',
            default => $value ? ucwords(str_replace('_', ' ', $value)) : 'N/A',
        };
    };

    $itemsSubtotal = (float) ($order->subtotal ?? 0);
    if ($itemsSubtotal <= 0) {
        $itemsSubtotal = (float) $order->orderItems->sum(function ($item) {
            $lineTotal = (float) ($item->total ?? 0);
            if ($lineTotal > 0) {
                return $lineTotal;
            }
            return ((float) ($item->price ?? 0)) * ((int) ($item->quantity ?? 0));
        });
    }

    $shippingFee = max((float) ($order->shipping_fee ?? 0), 0);
    $discountAmount = max((float) ($order->discount_amount ?? $order->discount ?? 0), 0);

    $calculatedTotal = max($itemsSubtotal + $shippingFee - $discountAmount, 0);
    $storedTotal = (float) ($order->total_amount ?? $order->total ?? 0);
    $grandTotal = $storedTotal > 0 ? $storedTotal : $calculatedTotal;

    $paymentOption = strtolower((string) ($order->payment_option ?? ''));
    $downpaymentRate = (float) ($order->downpayment_rate ?? 0);
    $downpaymentAmount = max((float) ($order->downpayment_amount ?? 0), 0);

    $rawPaymentStatus = strtolower((string) ($order->payment_status ?? ''));
    $isPaidStatus = in_array($rawPaymentStatus, ['paid', 'verified'], true);

    $notesText = (string) ($order->notes ?? '');
    $legacyPartialPaid = 0.0;
    $legacyPartialRemaining = 0.0;
    if (preg_match_all('/Downpayment received:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)\s*;\s*remaining balance:\s*PHP\s*([0-9,]+(?:\.[0-9]{1,2})?)/i', $notesText, $legacyMatches, PREG_SET_ORDER)) {
        $legacyLastMatch = end($legacyMatches);
        $legacyPartialPaid = isset($legacyLastMatch[1]) ? (float) str_replace(',', '', $legacyLastMatch[1]) : 0.0;
        $legacyPartialRemaining = isset($legacyLastMatch[2]) ? (float) str_replace(',', '', $legacyLastMatch[2]) : 0.0;
    }
    $legacySettlementRecorded = stripos($notesText, 'Remaining balance settled by admin') !== false;

    $remainingBalance = max((float) ($order->remaining_balance ?? 0), 0);
    if ($remainingBalance <= 0 && !$legacySettlementRecorded && $legacyPartialRemaining > 0) {
        $remainingBalance = max(0, round($legacyPartialRemaining, 2));
    }

    if ($paymentOption !== 'downpayment' && $remainingBalance > 0) {
        $paymentOption = 'downpayment';
    }

    if ($downpaymentAmount <= 0 && $legacyPartialPaid > 0 && $remainingBalance > 0) {
        $downpaymentAmount = max(0, round($legacyPartialPaid, 2));
    }

    if ($remainingBalance > 0) {
        $amountPaid = max($grandTotal - $remainingBalance, 0);
    } else {
        $amountPaid = $isPaidStatus ? $grandTotal : max($downpaymentAmount, 0);
    }
    if ($grandTotal > 0) {
        $amountPaid = min($amountPaid, $grandTotal);
    }

    $paymentPlanLabel = 'Full Payment';
    if ($remainingBalance > 0) {
        $resolvedRate = $downpaymentRate > 0
            ? $downpaymentRate
            : ($grandTotal > 0 ? (($amountPaid / $grandTotal) * 100) : 50);
        $paymentPlanLabel = rtrim(rtrim(number_format(max(1, min(99, $resolvedRate)), 2), '0'), '.') . '% Downpayment';
    }

    $invoicePaymentStatusClass = $resolveStatusClass($order->payment_status ?? null);
    $invoicePaymentStatusLabel = $resolveStatusLabel($order->payment_status ?? null);
    if ($remainingBalance > 0 && $isPaidStatus) {
        $invoicePaymentStatusClass = 'pill-warn';
        $invoicePaymentStatusLabel = 'Partial Payment';
    } elseif ($remainingBalance <= 0 && $isPaidStatus) {
        $invoicePaymentStatusClass = 'pill-ok';
        $invoicePaymentStatusLabel = 'Paid';
    }

    $invoiceDeliveryType = strtolower((string) ($order->delivery_type ?? 'delivery'));
    if ($invoiceDeliveryType === 'deliver') {
        $invoiceDeliveryType = 'delivery';
    }
    $shippingLabel = $invoiceDeliveryType === 'pickup' ? 'Store Pickup' : 'Home Delivery';

    $shippingAddress = trim((string) ($order->delivery_address ?: $order->shipping_address ?: ''));
    if ($invoiceDeliveryType === 'pickup' && $shippingAddress === '') {
        $shippingAddress = 'Yakan Village, Brgy. Upper Calarian, Zamboanga City, Philippines 7000';
    }

    $city = trim((string) ($order->shipping_city ?: $order->delivery_city ?: ''));
    $province = trim((string) ($order->shipping_province ?: $order->delivery_province ?: ''));
    $cityProvince = trim($city . ($city !== '' && $province !== '' ? ', ' : '') . $province);

    $invoiceNumber = 'INV-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
@endphp

<div class="sheet">
    <div class="toolbar">
        <div style="font-size:13px;color:#6b7280;">Printable sales invoice</div>
        <button type="button" class="print-btn" onclick="window.print()">Print Invoice</button>
    </div>

    <div class="invoice">
        <div class="head">
            <div class="brand">
                <h1>Yakan E-commerce</h1>
                <p>Traditional Yakan Crafts and Textiles</p>
                <p>Email: info@yakan-ecommerce.com</p>
                <p>Support: support@yakan-ecommerce.com</p>
            </div>
            <div class="meta">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoiceNumber }}</p>
                <p><strong>Order Ref:</strong> {{ $order->order_ref ?: ('ORD-' . $order->id) }}</p>
                <p><strong>Date:</strong> {{ optional($order->created_at)->format('F d, Y') }}</p>
                <p>
                    <strong>Order Status:</strong>
                    <span class="pill {{ $resolveStatusClass($order->status ?? null) }}">{{ $resolveStatusLabel($order->status ?? null) }}</span>
                </p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Bill To</h3>
                <p class="kv"><strong>{{ $order->customer_name ?: ($order->user->name ?? 'Guest Customer') }}</strong></p>
                <p class="kv">{{ $order->customer_email ?: ($order->user->email ?? 'No email provided') }}</p>
                @if(!empty($order->customer_phone))
                    <p class="kv">{{ $order->customer_phone }}</p>
                @elseif(!empty($order->user?->phone))
                    <p class="kv">{{ $order->user->phone }}</p>
                @endif
                @if($shippingAddress !== '')
                    <p class="kv"><strong>Address:</strong> {{ $shippingAddress }}</p>
                @endif
            </div>

            <div class="card">
                <h3>Payment and Delivery</h3>
                <p class="kv"><strong>Payment Method:</strong> {{ $resolvePaymentMethod($order->payment_method ?? null) }}</p>
                <p class="kv"><strong>Payment Plan:</strong> {{ $paymentPlanLabel }}</p>
                <p class="kv">
                    <strong>Payment Status:</strong>
                    <span class="pill {{ $invoicePaymentStatusClass }}">{{ $invoicePaymentStatusLabel }}</span>
                </p>
                <p class="kv"><strong>Delivery:</strong> {{ $shippingLabel }}</p>
                @if($cityProvince !== '')
                    <p class="kv"><strong>City / Province:</strong> {{ $cityProvince }}</p>
                @endif
                @if(!empty($order->tracking_number))
                    <p class="kv"><strong>Tracking #:</strong> {{ $order->tracking_number }}</p>
                @endif
                @if(!empty($order->payment_reference))
                    <p class="kv"><strong>Payment Ref:</strong> {{ $order->payment_reference }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:40%;">Item</th>
                    <th>SKU / Variant</th>
                    <th style="width:14%;" class="num">Price</th>
                    <th style="width:10%;" class="num">Qty</th>
                    <th style="width:16%;" class="num">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->orderItems as $item)
                    @php
                        $lineTotal = (float) ($item->total ?? 0);
                        if ($lineTotal <= 0) {
                            $lineTotal = ((float) ($item->price ?? 0)) * ((int) ($item->quantity ?? 0));
                        }

                        $variantParts = array_filter([
                            !empty($item->variant_size) ? 'Size: ' . $item->variant_size : null,
                            !empty($item->variant_color) ? 'Color: ' . $item->variant_color : null,
                        ]);
                        $variantLabel = !empty($variantParts)
                            ? implode(' | ', $variantParts)
                            : (!empty($item->variant_id) ? ('Variant #' . $item->variant_id) : 'N/A');
                    @endphp
                    <tr>
                        <td class="desc">
                            <div class="title">{{ $item->product_name ?: ($item->product->name ?? 'Product') }}</div>
                            <div class="sub">Product ID: {{ $item->product_id ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $item->product->sku ?? 'N/A' }}<br><span style="color:#6b7280;font-size:12px;">{{ $variantLabel }}</span></td>
                        <td class="num">PHP {{ number_format((float) ($item->price ?? 0), 2) }}</td>
                        <td class="num">{{ (int) ($item->quantity ?? 0) }}</td>
                        <td class="num">PHP {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#6b7280;">No line items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>Items Subtotal</span>
                <strong>PHP {{ number_format($itemsSubtotal, 2) }}</strong>
            </div>
            <div class="row">
                <span>Shipping Fee</span>
                <strong>PHP {{ number_format($shippingFee, 2) }}</strong>
            </div>
            @if($discountAmount > 0)
                <div class="row" style="color:#065f46;">
                    <span>Discount @if(!empty($order->coupon_code))({{ $order->coupon_code }})@endif</span>
                    <strong>- PHP {{ number_format($discountAmount, 2) }}</strong>
                </div>
            @endif
            <div class="row total">
                <span>Grand Total</span>
                <span>PHP {{ number_format($grandTotal, 2) }}</span>
            </div>
            <div class="row" style="margin-top:8px;">
                <span>Amount Paid</span>
                <strong>PHP {{ number_format($amountPaid, 2) }}</strong>
            </div>
            <div class="row">
                <span>Remaining Balance</span>
                <strong>PHP {{ number_format($remainingBalance, 2) }}</strong>
            </div>
        </div>

        @if($paymentPlanLabel !== 'Full Payment' || $remainingBalance > 0)
            <div class="payment-note">
                This invoice reflects a partial-payment workflow. Processing continues according to your selected payment plan, and remaining balance must be settled before final fulfillment.
            </div>
        @endif

        <div class="footer">
            <div><strong>Thank you for your purchase.</strong></div>
            <div>This is a system-generated invoice and does not require a signature.</div>
        </div>
    </div>
</div>
</body>
</html>
