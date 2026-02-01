# Facebook OAuth Login Fix Guide

## Issues Identified

Based on the error messages shown, there are two main issues:

### Issue 1: "App not active"
The Facebook app (ID: 871447252435350) is currently **not active/deactivated** and needs to be reactivated.

### Issue 2: "Can't load URL" 
The domain `yakan-webapp-production.up.railway.app` is **not added to the Facebook App's allowed domains**.

---

## Fix Steps

### Step 1: Reactivate Facebook App

1. Go to [Facebook Developers Dashboard](https://developers.facebook.com/apps)
2. Select your app "Yakan E-commerce" (App ID: 871447252435350)
3. In the left sidebar, click **Settings > Basic**
4. Look for the "App Status" indicator (top right)
5. If the app is in "Development" mode, you need to:
   - Click the **Status dropdown** or **Edit App Domains** button
   - Make sure the app is set to **"Live"** mode (not just Development)
   - If needed, complete the app review process

### Step 2: Add App Domains

1. In the Facebook App Dashboard, go to **Settings > Basic**
2. Scroll down to **App Domains** section
3. Add these domains:
   ```
   yakan-webapp-production.up.railway.app
   ```
4. If you're testing locally, also add:
   ```
   localhost
   192.168.1.203
   ```
5. Click **Save Changes**

### Step 3: Configure Facebook Login Product

1. In your app dashboard, go to **Products**
2. Add or ensure **Facebook Login** is enabled
3. Go to **Facebook Login > Settings**
4. Under "Valid OAuth Redirect URIs", add:
   ```
   https://yakan-webapp-production.up.railway.app/auth/facebook/callback
   ```
   For local testing:
   ```
   http://localhost:8000/auth/facebook/callback
   http://192.168.1.203:8000/auth/facebook/callback
   ```
5. Click **Save Changes**

### Step 4: Verify Environment Configuration

Your `.env` file currently has:
```env
FACEBOOK_CLIENT_ID=871447252435350
FACEBOOK_CLIENT_SECRET=5fd340d63112a5748a81fabce55f317f
FACEBOOK_REDIRECT_URI=https://yakan-webapp-production.up.railway.app/auth/facebook/callback
```

**For Local Testing**, update to:
```env
FACEBOOK_REDIRECT_URI=http://192.168.1.203:8000/auth/facebook/callback
```

Then update back to production URL when deploying.

---

## Important: Development vs Live Mode

- **Development Mode**: Only you and other admins can test the app
- **Live Mode**: All users can authenticate with Facebook

If you're testing with a different Facebook account:
1. Add that account as a **Test User** or **Developer** in your app settings
2. Or switch the app to **Live mode** (if review is complete)

---

## Verify Configuration

After making changes, you can test with:
```bash
# Clear Laravel config cache
php artisan config:cache

# Restart the server
php artisan serve --host=192.168.1.203
```

Then try logging in with Facebook again.

---

## Troubleshooting

| Error | Solution |
|-------|----------|
| "App not active" | Reactivate app in Facebook Developers > Settings > Basic |
| "Can't load URL" | Add domain to App Domains in Facebook Developers settings |
| "Invalid OAuth Redirect" | Ensure redirect URL matches exactly in Facebook Login Settings |
| "Still not working with different account" | Add account as Test User or switch app to Live mode |

---

## Quick Checklist

- [ ] Facebook App is in "Live" mode (not Development)
- [ ] Domain added to App Domains: `yakan-webapp-production.up.railway.app`
- [ ] Redirect URI added to Facebook Login Valid Redirect URIs
- [ ] Test user account added to your app (for testing in Dev mode)
- [ ] `.env` file has correct redirect URI for current environment
- [ ] Config cache cleared: `php artisan config:cache`
- [ ] Server restarted

---

## Current Configuration in Code

The OAuth integration is handled by:
- [SocialAuthController.php](app/Http/Controllers/Auth/SocialAuthController.php)
- [services.php](config/services.php) - Loads credentials from `.env`

All configuration looks correct on the Laravel side. The issue is purely in Facebook Developer settings.
