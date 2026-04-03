<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order {{ $order->order_ref ?? '#'.$order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3b82f6;
        }
        
        .company-info h1 {
            color: #3b82f6;
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .company-info p {
            color: #666;
            font-size: 14px;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .invoice-info p {
            font-size: 14px;
            color: #666;
        }
        
        .details-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .bill-to, .order-details {
            width: 48%;
        }
        
        .bill-to h3, .order-details h3 {
            font-size: 16px;
            color: #3b82f6;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .bill-to p, .order-details p {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        table thead {
            background: #f3f4f6;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #3b82f6;
        }
        
        table td {
            padding: 12px;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table tbody tr:hover {
            background: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            margin-left: auto;
            width: 300px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .totals-row.subtotal {
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
        
        .totals-row.discount {
            color: #10b981;
        }
        
        .totals-row.total {
            border-top: 2px solid #3b82f6;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-shipped {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-container {
                border: none;
                padding: 20px;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .print-button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Print Button -->
        <button onclick="window.print()" class="print-button no-print">Print Invoice</button>
        
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>Yakan E-commerce</h1>
                <p>Traditional Yakan Crafts & Textiles</p>
                <p>Email: info@yakan-ecommerce.com</p>
                <p>Phone: +63 XXX XXX XXXX</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> INV-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $order->status }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
        </div>
        
        <!-- Details Section -->
        <div class="details-section">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <p><strong>{{ $order->user->name ?? 'Guest Customer' }}</strong></p>
                <p>{{ $order->user->email ?? 'No email provided' }}</p>
                @if($order->user && $order->user->phone)
                <p>{{ $order->user->phone }}</p>
                @endif
                @if($order->delivery_address)
                <p style="margin-top: 10px;">{{ $order->delivery_address }}</p>
                @endif
            </div>
            
            <div class="order-details">
                <h3>Order Details:</h3>
                <p><strong>Payment Method:</strong> 
                    {{ $order->payment_method === 'online_banking' ? 'GCash' : 
                       ($order->payment_method === 'online' ? 'GCash' : 
                      ($order->payment_method === 'maya' ? 'Maya' : 
                       ($order->payment_method === 'gcash' ? 'GCash' : 
                      ($order->payment_method === 'bank_transfer' ? 'Bank Transfer' : ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')))))) }}
                </p>
                <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
                @if($order->tracking_number)
                <p><strong>Tracking #:</strong> {{ $order->tracking_number }}</p>
                @endif
                @if($order->courier_name)
                <p><strong>Courier:</strong> {{ $order->courier_name }}</p>
                @endif
            </div>
        </div>
        
        <!-- Order Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Product' }}</td>
                    <td>{{ $item->product->sku ?? 'N/A' }}</td>
                    <td class="text-right">₱{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals">
            <div class="totals-row subtotal">
                <span>Subtotal:</span>
                <span>₱{{ number_format($order->total_amount + ($order->discount_amount ?? 0), 2) }}</span>
            </div>
            
            @if($order->discount_amount && $order->discount_amount > 0)
            <div class="totals-row discount">
                <span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif:</span>
                <span>-₱{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="totals-row total">
                <span>Total:</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p style="margin-top: 10px;">For any queries, please contact us at support@yakan-ecommerce.com</p>
        </div>
    </div>
    
    <script>
        // Auto-print on load if needed
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
