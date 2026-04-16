<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order Invoice - {{ $order->display_ref }}</title>
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
    $invoiceItems = ($invoiceOrders ?? collect([$order]))->sortBy('id')->values();
    if ($invoiceItems->isEmpty()) {
        $invoiceItems = collect([$order]);
    }

    $resolveStatusClass = function ($status) {
        return match (strtolower((string) $status)) {
            'paid', 'completed', 'delivered' => 'pill-ok',
            'pending', 'price_quoted', 'approved', 'processing', 'in_production', 'out_for_delivery', 'production_complete' => 'pill-warn',
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
            default => $value ? ucwords(str_replace('_', ' ', $value)) : 'N/A',
        };
    };

    $resolvePriceParts = function ($item) {
        $quoted = (float) ($item->final_price ?? $item->estimated_price ?? 0);
        $deliveryType = strtolower((string) ($item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup')));
        if ($deliveryType === 'pickup') {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $breakdown = method_exists($item, 'getPriceBreakdown') ? ($item->getPriceBreakdown() ?? []) : [];
        $bd = $breakdown['breakdown'] ?? [];

        $material = (float) ($bd['material_cost'] ?? 0);
        $pattern = (float) ($bd['pattern_fee'] ?? 0);
        $labor = (float) ($bd['labor_cost'] ?? 0);
        $discount = (float) ($bd['discount'] ?? 0);
        $deliveryFeeInBreakdown = (float) ($bd['delivery_fee'] ?? 0);
        $itemsSubtotalFromBreakdown = max(($material + $pattern + $labor - $discount), 0);

        if ($deliveryFeeInBreakdown > 0) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        $shipping = max((float) ($item->shipping_fee ?? 0), 0.0);
        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - ($itemsSubtotalFromBreakdown + $shipping)) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
        }

        if ($itemsSubtotalFromBreakdown > 0 && abs($quoted - $itemsSubtotalFromBreakdown) < 0.01) {
            return ['quoted' => $quoted, 'shipping' => $shipping, 'total' => $quoted + $shipping];
        }

        return ['quoted' => $quoted, 'shipping' => 0.0, 'total' => $quoted];
    };

    $partsById = $invoiceItems->mapWithKeys(function ($item) use ($resolvePriceParts) {
        return [$item->id => $resolvePriceParts($item)];
    });

    $quotedSubtotal = (float) $invoiceItems->sum(fn($item) => (float) ($partsById[$item->id]['quoted'] ?? 0));

    $candidateShipping = $invoiceItems
        ->map(fn($item) => (float) ($partsById[$item->id]['shipping'] ?? 0))
        ->filter(fn($amount) => $amount > 0)
        ->values();

    $shippingFee = $invoiceItems->count() > 1
        ? (float) ($candidateShipping->max() ?? 0)
        : (float) ($candidateShipping->first() ?? 0);

    $grandTotal = max($quotedSubtotal + $shippingFee, 0);

    $allPaid = $invoiceItems->every(fn($item) => in_array(strtolower((string) ($item->payment_status ?? '')), ['paid', 'verified'], true));
    $amountPaid = $allPaid ? $grandTotal : 0.0;
    $remainingBalance = max($grandTotal - $amountPaid, 0);

    $primaryPaymentMethod = $resolvePaymentMethod($order->payment_method ?? null);
    $orderStatusLabel = $resolveStatusLabel($order->status ?? null);
    $paymentStatusLabel = $resolveStatusLabel($order->payment_status ?? null);

    $deliveryTypeRaw = strtolower((string) ($order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup')));
    $deliveryLabel = $deliveryTypeRaw === 'pickup' ? 'Store Pickup' : 'Home Delivery';

    $city = (string) ($order->delivery_city ?? '');
    $province = (string) ($order->delivery_province ?? '');
    $cityProvince = trim($city . ($city !== '' && $province !== '' ? ', ' : '') . $province);

    $invoiceNumber = 'CINV-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
@endphp

<div class="sheet">
    <div class="toolbar">
        <div style="font-size:13px;color:#6b7280;">Printable custom order invoice</div>
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
                <h2>CUSTOM INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoiceNumber }}</p>
                <p><strong>Order Ref:</strong> {{ $order->display_ref }}</p>
                <p><strong>Created:</strong> {{ optional($order->created_at)->format('F d, Y') }}</p>
                <p>
                    <strong>Status:</strong>
                    <span class="pill {{ $resolveStatusClass($order->status ?? null) }}">{{ $orderStatusLabel }}</span>
                </p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Bill To</h3>
                <p class="kv"><strong>{{ $order->user->name ?? 'Guest Customer' }}</strong></p>
                <p class="kv">{{ $order->user->email ?? ($order->email ?? 'No email provided') }}</p>
                @if(!empty($order->phone))
                    <p class="kv">{{ $order->phone }}</p>
                @endif
                @if(!empty($order->delivery_address))
                    <p class="kv"><strong>Address:</strong> {{ $order->delivery_address }}</p>
                @endif
            </div>

            <div class="card">
                <h3>Payment and Delivery</h3>
                <p class="kv"><strong>Payment Method:</strong> {{ $primaryPaymentMethod }}</p>
                <p class="kv">
                    <strong>Payment Status:</strong>
                    <span class="pill {{ $resolveStatusClass($order->payment_status ?? null) }}">{{ $paymentStatusLabel }}</span>
                </p>
                <p class="kv"><strong>Delivery:</strong> {{ $deliveryLabel }}</p>
                @if($cityProvince !== '')
                    <p class="kv"><strong>City / Province:</strong> {{ $cityProvince }}</p>
                @endif
                @if(!empty($order->transaction_id))
                    <p class="kv"><strong>Transaction ID:</strong> {{ $order->transaction_id }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:24%;">Order Ref</th>
                    <th>Description</th>
                    <th style="width:10%;" class="num">Qty</th>
                    <th style="width:16%;" class="num">Quoted</th>
                    <th style="width:14%;" class="num">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceItems as $item)
                    @php
                        $parts = $partsById[$item->id] ?? ['quoted' => 0, 'shipping' => 0, 'total' => 0];
                        $itemName = $item->product->name ?? ($item->specifications['order_name'] ?? ('Custom Order #' . $item->id));
                        $itemStatus = $resolveStatusLabel($item->status ?? null);
                        $itemMethod = $resolvePaymentMethod($item->payment_method ?? null);
                        $itemDelivery = strtolower((string) ($item->delivery_type ?? ($item->delivery_address ? 'delivery' : 'pickup')));
                        $itemDeliveryLabel = $itemDelivery === 'pickup' ? 'Pickup' : 'Delivery';
                    @endphp
                    <tr>
                        <td><strong>{{ $item->display_ref }}</strong></td>
                        <td class="desc">
                            <div class="title">{{ $itemName }}</div>
                            <div class="sub">{{ $itemDeliveryLabel }} • {{ $itemMethod }}</div>
                        </td>
                        <td class="num">{{ (int) ($item->quantity ?? 1) }}</td>
                        <td class="num">PHP {{ number_format((float) ($parts['quoted'] ?? 0), 2) }}</td>
                        <td class="num">{{ $itemStatus }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>Quoted Subtotal</span>
                <strong>PHP {{ number_format($quotedSubtotal, 2) }}</strong>
            </div>
            <div class="row">
                <span>Shipping Fee</span>
                <strong>PHP {{ number_format($shippingFee, 2) }}</strong>
            </div>
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

        <div class="footer">
            <div><strong>Thank you for supporting Yakan artisans.</strong></div>
            <div>This is a system-generated invoice for your custom order and does not require a signature.</div>
        </div>
    </div>
</div>
</body>
</html>
