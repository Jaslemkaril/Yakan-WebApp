<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Yakan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #800000, #600000);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(135deg, #800000, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #800000, #600000);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .welcome-banner h1 {
            margin: 0;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .welcome-banner p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            margin: 20px 0;
        }
        .content h2 {
            color: #800000;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .benefits {
            background: #f8f9fa;
            border-left: 4px solid #800000;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .benefits ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .benefits li {
            margin: 8px 0;
            color: #555;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #800000, #600000);
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">Y</div>
                <div class="logo-text">Yakan</div>
            </div>
        </div>

        <div class="welcome-banner">
            <h1>🎉 Welcome to the Yakan Family!</h1>
            <p>We're thrilled to have you join our community</p>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            <p>Thank you for creating an account with Yakan! You've just taken the first step towards discovering authentic Yakan cultural products and crafts.</p>

            <h2>What's Next?</h2>
            
            <div class="benefits">
                <strong>As a member, you now have access to:</strong>
                <ul>
                    <li>✨ <strong>Personalized recommendations</strong> tailored to your interests</li>
                    <li>💰 <strong>Exclusive member discounts</strong> on select products</li>
                    <li>🎁 <strong>Early access</strong> to new product launches</li>
                    <li>📦 <strong>Multiple shipping addresses</strong> for your convenience</li>
                    <li>📊 <strong>Order history tracking</strong> made simple</li>
                    <li>🎨 <strong>Custom order requests</strong> for personalized items</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}" class="cta-button">Start Shopping Now</a>
            </div>

            <h2>Explore Our Collections</h2>
            <p>Discover authentic Yakan weaving, traditional textiles, cultural accessories, and more. Each product tells a story of rich heritage and craftsmanship.</p>

            <h2>Need Help?</h2>
            <p>Our support team is here to help! If you have any questions or need assistance, feel free to reach out to us at <a href="mailto:{{ config('mail.from.address') }}" style="color: #800000;">{{ config('mail.from.address') }}</a>.</p>

            <p style="margin-top: 30px;">Welcome aboard!</p>
            <p><strong>The Yakan Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Yakan E-commerce. All rights reserved.</p>
            <p style="margin-top: 10px;">
                This email was sent to {{ $user->email }} because you registered for an account.
            </p>
        </div>
    </div>
</body>
</html>
