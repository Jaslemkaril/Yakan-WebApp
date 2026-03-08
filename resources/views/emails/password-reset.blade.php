<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Yakan</title>
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
        .reset-banner {
            background: linear-gradient(135deg, #800000, #600000);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .reset-banner h1 {
            margin: 0;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .reset-banner p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            margin: 20px 0;
        }
        .content p {
            margin-bottom: 15px;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        .expiry-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .expiry-notice p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .security-notice {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .security-notice h3 {
            margin-top: 0;
            color: #800000;
            font-size: 16px;
        }
        .security-notice ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .security-notice li {
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #e5e7eb;
        }
        .footer p {
            color: #9ca3af;
            font-size: 13px;
            margin: 5px 0;
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

        <div class="reset-banner">
            <h1>Password Reset</h1>
            <p>We received a request to reset your password</p>
        </div>

        <div class="content">
            <p>Hello {{ $user->first_name }},</p>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <p>Click the button below to reset your password:</p>
        </div>

        <div class="button-container">
            <a href="{{ $resetUrl }}" class="reset-button">
                Reset Password
            </a>
        </div>

        <div class="expiry-notice">
            <p><strong>⏰ This password reset link will expire in 60 minutes.</strong></p>
        </div>

        <div class="content">
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p style="word-break: break-all; color: #800000; font-size: 13px;">{{ $resetUrl }}</p>
        </div>

        <div class="security-notice">
            <h3>🔒 Security Tips:</h3>
            <ul>
                <li>If you didn't request a password reset, please ignore this email or contact support if you have concerns.</li>
                <li>Never share your password with anyone.</li>
                <li>Use a strong, unique password for your Yakan account.</li>
                <li>This link can only be used once.</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>Yakan E-commerce</strong></p>
            <p>Premium quality products and custom orders tailored to your needs.</p>
            <p style="margin-top: 15px;">
                This email was sent to {{ $user->email }}<br>
                If you have questions, contact us at support@yakan.com
            </p>
        </div>
    </div>
</body>
</html>
