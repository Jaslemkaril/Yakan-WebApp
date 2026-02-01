# Facebook App Required Fields - Complete Setup

## Required Fields to Fill In

### 1. **App Icon (1024 x 1024)**
**What to do:**
- Create a 1024x1024 PNG image with your "Y" logo (Yakan brand)
- You can use any design tool (Canva, Photoshop, etc.)
- Must be a square image with your brand logo

**Why:** Facebook requires a recognizable app icon so users know what they're logging into.

**Quick Option:** Use your existing "Y" logo and resize it to 1024x1024

---

### 2. **Privacy Policy URL**
**Recommended URL:**
```
https://yakan-webapp-production.up.railway.app/privacy-policy
```

**What to do:**
Create this page in your Laravel app. Add this route to `routes/web.php`:

```php
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});
```

Then create `resources/views/privacy-policy.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="container py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8">Privacy Policy</h1>
        
        <div class="prose prose-lg">
            <h2>1. Information We Collect</h2>
            <p>
                We collect information you provide when you:
            </p>
            <ul>
                <li>Create an account</li>
                <li>Place an order</li>
                <li>Use our mobile app</li>
                <li>Contact our customer support</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>
                Your information is used to:
            </p>
            <ul>
                <li>Process and fulfill your orders</li>
                <li>Send order updates and notifications</li>
                <li>Improve our services</li>
                <li>Comply with legal obligations</li>
            </ul>

            <h2>3. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal data against unauthorized processing.
            </p>

            <h2>4. Your Rights</h2>
            <p>
                You have the right to:
            </p>
            <ul>
                <li>Access your personal data</li>
                <li>Request correction of inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Withdraw consent for processing</li>
            </ul>

            <h2>5. Contact Us</h2>
            <p>
                For privacy concerns, email us at: <strong>eh202202743@wmsu.edu.ph</strong>
            </p>

            <p class="text-gray-600 text-sm mt-8">
                Last updated: {{ now()->format('F d, Y') }}
            </p>
        </div>
    </div>
</div>
@endsection
```

---

### 3. **User Data Deletion URL**
**Recommended URL:**
```
https://yakan-webapp-production.up.railway.app/data-deletion
```

**What to do:**
Add this route to `routes/web.php`:

```php
Route::get('/data-deletion', function () {
    return view('data-deletion');
});
```

Then create `resources/views/data-deletion.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="container py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8">Data Deletion Instructions</h1>
        
        <div class="prose prose-lg">
            <h2>How to Delete Your Account and Data</h2>
            
            <h3>Option 1: Self-Service Deletion</h3>
            <ol>
                <li>Log in to your Yakan account</li>
                <li>Go to Settings → Account Settings</li>
                <li>Scroll to the bottom</li>
                <li>Click "Delete My Account and Data"</li>
                <li>Confirm your password</li>
                <li>Click "Permanently Delete"</li>
            </ol>

            <h3>Option 2: Request via Email</h3>
            <p>
                Send an email to <strong>eh202202743@wmsu.edu.ph</strong> with:
            </p>
            <ul>
                <li>Your full name</li>
                <li>Email address associated with your account</li>
                <li>Subject: "Data Deletion Request"</li>
            </ul>
            <p>
                We will process your request within 30 days and confirm deletion via email.
            </p>

            <h2>What Gets Deleted</h2>
            <ul>
                <li>Personal profile information</li>
                <li>Account credentials</li>
                <li>Order history (anonymized)</li>
                <li>Payment information</li>
                <li>Wishlist and saved items</li>
                <li>Session data</li>
            </ul>

            <h2>What We Keep</h2>
            <p>
                For legal and compliance reasons, we may retain:
            </p>
            <ul>
                <li>Anonymized transaction records</li>
                <li>Tax and accounting information</li>
                <li>Fraud prevention data</li>
            </ul>

            <p class="text-gray-600 mt-8">
                Questions? Contact: <strong>eh202202743@wmsu.edu.ph</strong>
            </p>
        </div>
    </div>
</div>
@endsection
```

---

### 4. **Terms of Service URL** (Optional but recommended)
**Recommended URL:**
```
https://yakan-webapp-production.up.railway.app/terms-of-service
```

---

## Step-by-Step Setup

### For Local Testing (192.168.1.203:8000):

1. Add the routes to `routes/web.php`
2. Create the blade templates
3. Run: `php artisan serve --host=192.168.1.203`
4. Test locally at: `http://192.168.1.203:8000/privacy-policy`

### For Production (Railway):

1. Push your code to GitHub
2. Railway will auto-deploy
3. Use these URLs in Facebook app settings:
   - Privacy Policy: `https://yakan-webapp-production.up.railway.app/privacy-policy`
   - Data Deletion: `https://yakan-webapp-production.up.railway.app/data-deletion`

---

## Category Selection

For your **Yakan E-commerce** app, select:
- **Shopping** (most appropriate for e-commerce)

Or if not available:
- **Business & Commerce**
- **Retail & E-commerce**

---

## Complete Checklist

- [ ] Create 1024x1024 app icon (PNG)
- [ ] Add Privacy Policy route and blade template
- [ ] Add Data Deletion route and blade template
- [ ] Add Terms of Service URL (optional)
- [ ] Select Category: "Shopping"
- [ ] Upload all to Facebook app settings
- [ ] Click "Publish" → "Switch to Live"

---

## Testing URLs Locally

If you want to test the app in development mode first:

Update `.env` for local testing:
```env
APP_URL=http://192.168.1.203:8000
FACEBOOK_REDIRECT_URI=http://192.168.1.203:8000/auth/facebook/callback
```

Then run:
```bash
php artisan serve --host=192.168.1.203
```

Visit: `http://192.168.1.203:8000/privacy-policy`

---

## Quick Reference

| Field | Value |
|-------|-------|
| App Icon | 1024x1024 PNG with "Y" logo |
| Display Name | Yakan |
| Privacy Policy | https://yakan-webapp-production.up.railway.app/privacy-policy |
| Data Deletion URL | https://yakan-webapp-production.up.railway.app/data-deletion |
| Terms of Service | https://yakan-webapp-production.up.railway.app/terms-of-service |
| Category | Shopping |
| Contact Email | eh202202743@wmsu.edu.ph |

