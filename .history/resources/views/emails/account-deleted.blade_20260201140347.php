<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #8B0000; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .info-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Deletion Confirmation</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $name }},</p>
            
            <p>This email confirms that your Yakan account has been successfully deleted.</p>
            
            <div class="info-box">
                <strong>Deletion Date & Time:</strong><br>
                {{ $deletion_date }}
            </div>
            
            <h3>What Was Deleted</h3>
            <ul>
                <li>Your personal profile information</li>
                <li>Account credentials and login data</li>
                <li>Wishlist and saved items</li>
                <li>Addresses and contact information</li>
                <li>Social media authentication links</li>
            </ul>
            
            <h3>What We Retained</h3>
            <p>
                For legal and compliance purposes, we have retained anonymized transaction records only. These records cannot be linked back to your identity.
            </p>
            
            <h3>Recovery</h3>
            <p>
                <strong>Important:</strong> Your account cannot be recovered after deletion. If you wish to use Yakan again, you will need to create a new account.
            </p>
            
            <h3>Questions?</h3>
            <p>
                If you have any questions about your account deletion, please contact our support team at:
            </p>
            <p>
                <strong>Email:</strong> <a href="mailto:{{ $support_email }}">{{ $support_email }}</a>
            </p>
            
            <p>Thank you for being part of Yakan.</p>
            
            <p>Best regards,<br>
            <strong>Yakan E-commerce Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Yakan E-commerce Platform. All rights reserved.</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
