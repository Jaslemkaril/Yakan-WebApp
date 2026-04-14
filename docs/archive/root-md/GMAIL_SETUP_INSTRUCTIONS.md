# Gmail SMTP Setup Instructions for OTP Email Verification

## 🔧 Gmail Configuration Steps

### Step 1: Enable 2-Factor Authentication
1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google", click "2-Step Verification"
4. Follow the steps to enable 2FA if not already enabled

### Step 2: Generate App Password
1. In Google Account Security settings
2. Under "Signing in to Google", click "App passwords"
3. Select "Mail" as the app
4. Select "Other (Custom name)" as the device
5. Enter "Yakan E-commerce" as the name
6. Click "Generate"
7. **Copy the 16-character app password** (you'll need this)

### Step 3: Update .env File
Replace the email configuration in your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail-address@gmail.com
MAIL_FROM_NAME="Yakan E-commerce"
```

### Step 4: Example Configuration
```env
# Example (replace with your actual credentials)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yakanstore@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yakanstore@gmail.com
MAIL_FROM_NAME="Yakan E-commerce"
```

## 🧪 Testing the Setup

### Test 1: Check Configuration
Visit: `http://127.0.0.1:8000/test-otp-email`

This will:
- Create a test user if needed
- Generate an OTP
- Send the email
- Show you the OTP code for testing

### Test 2: Full Registration Flow
1. Go to: `http://127.0.0.1:8000/register`
2. Fill out the registration form
3. Submit the form
4. Check your email for the OTP
5. Enter the OTP on the verification page

## 🔍 Troubleshooting

### Common Issues:

**1. "Authentication failed" error:**
- Make sure you're using an App Password, not your regular Gmail password
- Verify 2FA is enabled on your Google account

**2. "Connection refused" error:**
- Check your internet connection
- Verify MAIL_HOST is `smtp.gmail.com`
- Verify MAIL_PORT is `587`

**3. "Invalid credentials" error:**
- Double-check your Gmail address in MAIL_USERNAME
- Verify the App Password is correct (16 characters, no spaces)

**4. Email not received:**
- Check spam/junk folder
- Verify the recipient email address
- Try the test route first: `/test-otp-email`

### For Development/Testing Only:
If you want to test without actual emails, change in `.env`:
```env
MAIL_MAILER=log
```
This will log emails to `storage/logs/laravel.log` instead of sending them.

## 📧 Email Features

The OTP email includes:
- ✅ Professional Yakan branding
- ✅ 6-digit OTP code
- ✅ 10-minute expiration timer
- ✅ Security tips and warnings
- ✅ Direct verification link
- ✅ Mobile-responsive design

## 🔐 Security Features

- ✅ OTP expires in 10 minutes
- ✅ Maximum 3 attempts per OTP
- ✅ New OTP invalidates previous ones
- ✅ Secure random 6-digit generation
- ✅ Rate limiting on resend requests

## 🚀 Next Steps

After email verification works:
1. Users will be automatically logged in after verification
2. They'll be redirected to the dashboard
3. Email verification status is tracked in the database
4. Verified users can access all features

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Gmail settings are correct
3. Test with the `/test-otp-email` route first
4. Make sure your Gmail account has 2FA enabled