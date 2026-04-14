# Facebook App Setup - Complete Checklist

## What I've Already Created For You

✅ **Blade Templates (Views):**
- `/resources/views/privacy-policy.blade.php` - Privacy Policy page
- `/resources/views/data-deletion.blade.php` - Data Deletion page  
- `/resources/views/terms-of-service.blade.php` - Terms of Service page

✅ **Routes Added to `routes/web.php`:**
- `/privacy-policy` → Privacy Policy page
- `/data-deletion` → Data Deletion page
- `/terms-of-service` → Terms of Service page

---

## Step-by-Step Setup Process

### Step 1: Test the Pages Locally (Optional but Recommended)

Before adding URLs to Facebook, verify they work:

```bash
# Clear config cache
php artisan config:cache

# Start the server
php artisan serve --host=192.168.1.203

# Test the pages in your browser:
# http://192.168.1.203:8000/privacy-policy
# http://192.168.1.203:8000/data-deletion
# http://192.168.1.203:8000/terms-of-service
```

### Step 2: Create App Icon (1024x1024)

You need a **square PNG image** with your Yakan logo.

**Quick Options:**
1. **Use Canva (Free):**
   - Go to canva.com
   - Create a new "Social Media" design
   - Set to 1024x1024
   - Add your red "Y" logo
   - Download as PNG

2. **Use Your Existing Logo:**
   - Resize to 1024x1024
   - Save as PNG format

3. **Use a Template:**
   - Simple red square with white "Y" letter
   - Rounded corners optional

**File Requirements:**
- Format: PNG
- Size: 1024 x 1024 pixels
- No transparency needed
- File size: Under 5MB

---

### Step 3: Go to Facebook Developer Dashboard

1. Open: https://developers.facebook.com/apps/
2. Select your app "Yakan" (ID: 871447252435350)
3. Go to **Settings > Basic**

---

### Step 4: Fill in Required Fields

#### **App Icon**
1. Click on the App Icon placeholder
2. Upload your 1024x1024 PNG image
3. Click "Save Changes"

#### **Privacy Policy URL**
1. In the "Privacy Policy URL" field, enter:
```
https://yakan-webapp-production.up.railway.app/privacy-policy
```
2. Click "Save Changes"

#### **User Data Deletion URL**
1. Scroll down to "User Data Deletion"
2. Select "Data deletion instructions URL" from dropdown
3. Enter:
```
https://yakan-webapp-production.up.railway.app/data-deletion
```
4. Click "Save Changes"

#### **Category**
1. Click on the "Category" dropdown
2. Select **"Shopping"** (or "Retail")
3. Click "Save Changes"

#### **Terms of Service URL** (Optional)
1. In "Terms of Service URL" field, enter:
```
https://yakan-webapp-production.up.railway.app/terms-of-service
```
2. Click "Save Changes"

---

### Step 5: Configure Facebook Login Product

1. In the left menu, go to **Products**
2. Click **+ Add Product** if Facebook Login isn't there
3. Search for "Facebook Login"
4. Click "Set Up"
5. Choose **Web**

Then configure:

1. Go to **Facebook Login > Settings** (in the sidebar under Products)
2. Under "Valid OAuth Redirect URIs", add:
```
https://yakan-webapp-production.up.railway.app/auth/facebook/callback
```
3. Click "Save Changes"

---

### Step 6: Add App Domains

1. Still in **Settings > Basic**
2. Scroll to "App Domains"
3. Add this domain:
```
yakan-webapp-production.up.railway.app
```
4. Click "Save Changes"

For local testing, also add:
```
localhost
192.168.1.203
```

---

### Step 7: Switch App to Live Mode

⚠️ **IMPORTANT STEP** - This is what was causing "App not active" error!

1. In the left sidebar, click **"Publish"**
2. You'll see "Switch to Live" button (or status showing "In Development")
3. Click "Switch to Live"
4. Confirm the app status at top right should change to **"Live"** (green circle)

---

### Step 8: Add Test Users (For Development Testing)

If you want to test with different Facebook accounts in **Development** mode:

1. Go to **Roles > Test Users** (in left sidebar)
2. Click **"Add"**
3. Enter name and email for test account
4. Click **"Create Test User"**
5. Now that account can log in to your app

---

### Step 9: Deploy to Production (If Not Already)

1. Commit your code changes:
```bash
git add .
git commit -m "Add Facebook OAuth required pages: privacy policy, data deletion, terms of service"
git push
```

2. Railway will auto-deploy the changes
3. Verify pages are live:
   - Visit: https://yakan-webapp-production.up.railway.app/privacy-policy
   - Visit: https://yakan-webapp-production.up.railway.app/data-deletion

---

### Step 10: Test Facebook Login

1. Go to: https://yakan-webapp-production.up.railway.app/login
2. Click "Continue with Facebook"
3. You should now see:
   - Either be asked to log in to Facebook (if not logged in)
   - Or be redirected back to the app (if already logged in)
   - **NOT** the "App not active" or "Can't load URL" errors

---

## Troubleshooting

| Error | Solution |
|-------|----------|
| "App not active" | Make sure app is in **Live** mode (green circle in Publish section) |
| "Can't load URL" | Make sure domain is added to App Domains: `yakan-webapp-production.up.railway.app` |
| Privacy Policy returns 404 | Run `php artisan route:cache` then restart server |
| Pages show blank | Make sure views are in `/resources/views/` folder |
| Different account won't login in Development | Add account as Test User in Roles section |

---

## Final Checklist

Before declaring this complete:

- [ ] 1024x1024 app icon created
- [ ] Icon uploaded to Facebook app
- [ ] Privacy Policy URL entered: `/privacy-policy`
- [ ] Data Deletion URL entered: `/data-deletion`
- [ ] Category set to "Shopping"
- [ ] App Domains include: `yakan-webapp-production.up.railway.app`
- [ ] Facebook Login product configured
- [ ] Valid OAuth Redirect URI added: `/auth/facebook/callback`
- [ ] App switched to **Live** mode (green indicator)
- [ ] Routes added to web.php
- [ ] Changes committed and deployed to Railway
- [ ] Verified pages work in production
- [ ] Test Facebook login works without errors

---

## Quick Reference URLs

After setup, these URLs must be accessible and working:

```
Privacy Policy:
https://yakan-webapp-production.up.railway.app/privacy-policy

Data Deletion:
https://yakan-webapp-production.up.railway.app/data-deletion

Terms of Service:
https://yakan-webapp-production.up.railway.app/terms-of-service

Facebook OAuth Redirect:
https://yakan-webapp-production.up.railway.app/auth/facebook/callback

Facebook Login:
https://yakan-webapp-production.up.railway.app/login
```

---

## Support

If you get stuck on any step:
1. Check the Facebook Developer Docs: https://developers.facebook.com/docs/facebook-login/
2. Review error messages carefully - they usually tell you what's missing
3. Make sure all URLs include `https://` (HTTPS is required)
4. Double-check domain names for typos

Good luck! After completing these steps, your Facebook OAuth should work perfectly.
