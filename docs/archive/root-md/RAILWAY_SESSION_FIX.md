# Railway Session/Login Fix - URGENT

## Problem
Users keep getting logged out or redirected to login page on Railway production.

## Solution
Update Railway environment variables to fix session persistence.

## Steps to Fix

### 1. Go to Railway Dashboard
1. Open https://railway.app
2. Select your `Yakan-WebApp` project
3. Click on your **web service** (not the database)
4. Go to the **Variables** tab

### 2. Update These Environment Variables

Find and **UPDATE** the following variables (or add if missing):

```bash
# Force HTTPS detection
FORCE_HTTPS=true
ASSET_URL=https://yakan-webapp-production.up.railway.app

# Session Configuration - CRITICAL CHANGES
SESSION_DRIVER=file
SESSION_LIFETIME=525600
SESSION_SECURE_COOKIE=
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=
SESSION_ENCRYPT=false
SESSION_PATH=/
```

### 3. Key Changes Explained

| Variable | Old Value | New Value | Why |
|----------|-----------|-----------|-----|
| `SESSION_DRIVER` | `database` | `file` | File sessions are more reliable on Railway |
| `SESSION_SECURE_COOKIE` | `true` | *(empty)* | Let Laravel auto-detect HTTPS from proxy |
| `SESSION_PATH` | *(not set)* | `/` | Ensure cookie works for all paths |
| `FORCE_HTTPS` | *(not set)* | `true` | Force HTTPS URL generation |
| `ASSET_URL` | *(not set)* | `https://...` | Ensure assets load over HTTPS |

### 4. After Updating Variables

Railway will **automatically redeploy** your app. Wait 1-2 minutes, then:

1. Clear your browser cookies for the site
2. Try logging in again
3. Session should now persist properly

## What Was Fixed

### Code Changes (Already Deployed):
✅ Switched to file-based sessions (more reliable than database)
✅ Improved HTTPS detection with `FORCE_HTTPS` flag
✅ Enhanced secure cookie detection to auto-detect from proxy headers
✅ Added `SESSION_PATH=/` to ensure cookie works site-wide
✅ Cart AJAX requests now include proper authentication
✅ Session lifetime increased to 1 year (525,600 minutes)

### Why This Fixes the Issue:

1. **File Sessions**: More reliable than database sessions on Railway's ephemeral filesystem with proper proxy setup
2. **Auto-detect Secure Cookie**: Instead of hardcoding `true`, let Laravel detect HTTPS from Railway's proxy headers
3. **Trust Proxies**: Already configured with `trustProxies('*')` to read `X-Forwarded-Proto` header
4. **Auth Token Fallback**: JavaScript automatically stores and includes `auth_token` in requests

## Testing

After the changes:
1. Login to your account
2. Navigate to different pages
3. Add items to cart
4. Change quantities in cart
5. Should stay logged in throughout

## If Still Having Issues

Check browser console (F12) for errors and look for:
- Cookie warnings
- 401 Unauthorized errors
- Session errors

The auth_token system will work as a fallback if cookies still fail.

## Quick Rollback (If Needed)

If something breaks:
```bash
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
```

Then redeploy.
