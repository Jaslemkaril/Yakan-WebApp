# Google & Facebook OAuth Fix Guide

## Problem
Google OAuth was giving `redirect_uri_mismatch` error because the redirect URIs weren't registered properly in Google Cloud Console.

## What Was Done

### 1. Updated Local `.env` File
Added the OAuth credentials and redirect URIs to your local `.env`:
```
GOOGLE_CLIENT_ID=915673616124-toenvmcr97t350n208kdpnvjtqiq8r7t.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT_URI=https://yakan-webapp-production.up.railway.app/auth/google/callback

FACEBOOK_CLIENT_ID=871447252435350
FACEBOOK_CLIENT_SECRET=5fd340d63112a5748a81fabce55f317f
FACEBOOK_REDIRECT_URI=https://yakan-webapp-production.up.railway.app/auth/facebook/callback
```

### 2. Added Debug Route
Added `/debug/oauth-config` route to verify that the redirect URIs are loaded correctly:
```
https://yakan-webapp-production.up.railway.app/debug/oauth-config
```

### 3. Railway Variables (Already Confirmed)
✓ GOOGLE_CLIENT_ID
✓ GOOGLE_CLIENT_SECRET
✓ FACEBOOK_CLIENT_ID
✓ FACEBOOK_CLIENT_SECRET

## What You Need To Do

### Step 1: Update Google Cloud Console

1. Go to https://console.cloud.google.com/apis/credentials
2. Find your OAuth 2.0 Client ID: `915673616124-toenvmcr97t350n208kdpnvjtqiq8r7t.apps.googleusercontent.com`
3. Click Edit or the pencil icon
4. Under "Authorized redirect URIs", ADD THIS:
   ```
   https://yakan-webapp-production.up.railway.app/auth/google/callback
   ```
5. **IMPORTANT**: Remove or keep the old `https://yourdomain.com/auth/google/callback` URIs
6. Save changes

### Step 2: Update Facebook Developer Console

1. Go to https://developers.facebook.com/
2. Find your app: `871447252435350`
3. Go to Settings → Basic
4. Under "App Domains", make sure you have: `yakan-webapp-production.up.railway.app`
5. Go to Settings → Advanced
6. Under "Valid OAuth Redirect URIs", ADD THIS:
   ```
   https://yakan-webapp-production.up.railway.app/auth/facebook/callback
   ```
7. Save changes

### Step 3: Add Railway Variables (If Not Already Done)

If you haven't already added these to Railway, add them now:

**Variable 1:**
- Key: `GOOGLE_REDIRECT_URI`
- Value: `https://yakan-webapp-production.up.railway.app/auth/google/callback`

**Variable 2:**
- Key: `FACEBOOK_REDIRECT_URI`
- Value: `https://yakan-webapp-production.up.railway.app/auth/facebook/callback`

### Step 4: Clear Cache and Redeploy on Railway

After adding variables:

1. In Railway, go to "Deployments"
2. Click "Deploy" to redeploy your app
3. OR manually run:
   ```
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

### Step 5: Verify Configuration

Test the debug route to confirm redirect URIs are loaded:

**Local (after Laravel restart):**
```
http://192.168.1.203:8000/debug/oauth-config
```

**Railway:**
```
https://yakan-webapp-production.up.railway.app/debug/oauth-config
```

Should show:
```json
{
  "google": {
    "client_id": "915673616124-toenvmcr97t350n208kdpnvjtqiq8r7t.apps.googleusercontent.com",
    "redirect": "https://yakan-webapp-production.up.railway.app/auth/google/callback"
  },
  "facebook": {
    "client_id": "871447252435350",
    "redirect": "https://yakan-webapp-production.up.railway.app/auth/facebook/callback"
  }
}
```

### Step 6: Test OAuth Login

1. Go to your user login page on Railway
2. Click "Continue with Google" or "Continue with Facebook"
3. You should be able to log in without the `redirect_uri_mismatch` error

## Troubleshooting

### Still Getting redirect_uri_mismatch?
- Check that the redirect URI in Google/Facebook Console **EXACTLY** matches what shows in `/debug/oauth-config`
- Don't forget the `/callback` at the end
- Use HTTPS, not HTTP
- Wait 5-10 minutes for Google/Facebook to sync changes

### Need to Change Domain Later?
- Update `GOOGLE_REDIRECT_URI` and `FACEBOOK_REDIRECT_URI` in `.env` (local)
- Add `GOOGLE_REDIRECT_URI` and `FACEBOOK_REDIRECT_URI` to Railway Variables
- Update Google Cloud Console and Facebook Developer Console with the new URLs
- Redeploy or clear cache

## Files Modified
- `.env` - Added OAuth credentials and redirect URIs
- `routes/web.php` - Added `/debug/oauth-config` route for verification
- `config/services.php` - Already configured to use environment variables (no changes needed)

## Key Points
✓ Redirect URIs must use HTTPS in production
✓ Redirect URIs must be exact - case-sensitive, no trailing slashes
✓ Config cache must be cleared after environment variable changes
✓ Give Google/Facebook a few minutes to sync configuration changes
