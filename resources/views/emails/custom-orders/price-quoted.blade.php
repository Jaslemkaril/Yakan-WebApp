<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order Price Quoted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .price-box {
            background: white;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .price {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn-accept {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        .btn-reject {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎨 Your Custom Order Has Been Priced!</h1>
        <p>Good news! Our team has reviewed your custom Yakan order</p>
    </div>

    <div class="content">
        <p>Dear {{ $user->name }},</p>
        
        <p>Thank you for your patience! Our master craftsmen have carefully reviewed your custom order specifications and calculated the final price.</p>
        
        <div class="price-box">
            <h3>Order {{ $order->batch_order_number ?? '#'.$order->id }}</h3>
            <div class="price">₱{{ $finalPrice }}</div>
            <p>This price includes materials, craftsmanship, and delivery</p>
        </div>

        @if($adminNotes)
        <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>📝 Notes from our team:</strong>
            <p>{{ $adminNotes }}</p>
        </div>
        @endif

        <h3>What's Next?</h3>
        <p>You can now choose to:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('custom_orders.accept', $order->id) }}" class="btn btn-accept">
                ✅ Accept & Proceed to Payment
            </a>
            <a href="{{ route('custom_orders.reject', $order->id) }}" class="btn btn-reject">
                ❌ Decline This Quote
            </a>
        </div>

        <p><strong>Important:</strong> Please make your decision within 7 days. After that, this quote will expire.</p>

        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>💡 Why choose Yakan custom orders?</strong>
            <ul>
                <li>Authentic traditional craftsmanship</li>
                <li>Premium quality materials</li>
                <li>Unique designs tailored to you</li>
                <li>Supporting local artisans</li>
            </ul>
        </div>

        <p>If you have any questions or need modifications, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        The Yakan E-commerce Team</p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Yakan E-commerce. Preserving tradition through modern craftsmanship.</p>
        <p>If you no longer wish to receive these emails, please <a href="{{ route('unsubscribe') }}">unsubscribe</a>.</p>
    </div>
</body>
</html>
