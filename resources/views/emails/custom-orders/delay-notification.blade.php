<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Delay Notice</title>
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
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #fff9f0;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .delay-box {
            background: white;
            border: 2px solid #ff9800;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ff9800;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">⏱️</div>
        <h1>Production Delay Notice</h1>
        <p>Important Update About Your Custom Order</p>
    </div>

    <div class="content">
        <p>Dear {{ $order->user->name ?? 'Valued Customer' }},</p>
        
        <p>We wanted to inform you about a delay in the production of your custom order <strong>#{{ $order->id }}</strong>.</p>
        
        <div class="delay-box">
            <h3 style="color: #ff9800; margin-top: 0;">Reason for Delay:</h3>
            <p style="font-size: 16px; line-height: 1.8;">{{ $delayReason }}</p>
        </div>

        <div class="highlight">
            <strong>🙏 We sincerely apologize</strong>
            <p>We understand how important your order is to you, and we're doing everything we can to minimize this delay. Our team is working diligently to resolve the issue and complete your custom piece with the quality you expect from Yakan craftsmanship.</p>
        </div>

        <h3>What happens next?</h3>
        <ul>
            <li>Our production team is actively addressing the issue</li>
            <li>We'll keep you updated on the progress</li>
            <li>Your order remains a priority for our craftsmen</li>
            <li>We'll notify you immediately once production resumes</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('custom_orders.show', $order->id) }}" class="btn">
                📦 View Order Status
            </a>
        </div>

        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            If you have any questions or concerns, please don't hesitate to contact us. We appreciate your patience and understanding.
        </p>

        <div class="footer">
            <p><strong>Thank you for choosing Yakan Weaving</strong></p>
            <p>Preserving tradition through authentic craftsmanship</p>
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            <p style="font-size: 12px;">
                This is an automated notification from Yakan E-commerce.<br>
                Please do not reply directly to this email.
            </p>
        </div>
    </div>
</body>
</html>
