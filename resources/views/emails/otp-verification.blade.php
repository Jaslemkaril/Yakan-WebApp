<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Yakan</title>
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
        .otp-container {
            background: linear-gradient(135deg, #800000, #600000);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 20px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
        }
        .otp-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .content {
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #800000, #600000);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
        }
        .security-tips {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .security-tips h4 {
            margin-top: 0;
            color: #800000;
        }
        .security-tips ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .security-tips li {
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
            <h1 style="color: #800000; margin: 0;">Email Verification</h1>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->first_name ?? $user->name }}</strong>,</p>
            
            <p>Thank you for registering with Yakan! To complete your account setup and ensure the security of your account, please verify your email address using the OTP code below.</p>
        </div>

        <div class="otp-container">
            <div class="otp-label">Your Verification Code</div>
            <div class="otp-code">{{ $otp }}</div>
            <p style="margin: 0; font-size: 14px; opacity: 0.9;">
                This code expires in 10 minutes
            </p>
        </div>

        <div class="content">
            <p>Enter this code on the verification page to activate your account and start shopping for authentic Yakan products.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('verification.notice') }}" class="button">
                    Verify My Account
                </a>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Security Notice:</strong> If you didn't create an account with Yakan, please ignore this email. Your email address will not be used without verification.
        </div>

        <div class="security-tips">
            <h4>🔒 Security Tips:</h4>
            <ul>
                <li>Never share your OTP code with anyone</li>
                <li>Yakan staff will never ask for your OTP via phone or email</li>
                <li>This code expires in 10 minutes for your security</li>
                <li>You can request a new code if this one expires</li>
            </ul>
        </div>

        <div class="footer">
            <p>
                <strong>Yakan E-commerce</strong><br>
                Authentic Traditional Products<br>
                <a href="{{ config('app.url') }}" style="color: #800000;">{{ config('app.url') }}</a>
            </p>
            <p style="font-size: 12px; color: #999;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>