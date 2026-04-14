# Railway Manual Setup Guide

## Your Generated APP_KEY
```
base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=
```
**IMPORTANT: Save this key! You'll need it in Railway.**

---

## Step-by-Step Setup (No CLI Required)

### Step 1: Create Railway Account
1. Go to https://railway.app
2. Click "Start a New Project"
3. Sign up with GitHub (recommended) or email

### Step 2: Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Connect your GitHub account
4. Select your Yakan repository
5. Click "Deploy Now"

### Step 3: Add MySQL Database
1. In your Railway project dashboard
2. Click "New" → "Database" → "Add MySQL"
3. Railway will automatically create these variables:
   - MYSQLHOST
   - MYSQLPORT
   - MYSQLDATABASE
   - MYSQLUSER
   - MYSQLPASSWORD

### Step 4: Configure Environment Variables
1. Click on your web service
2. Go to "Variables" tab
3. Click "Raw Editor"
4. Paste these variables:

```env
APP_NAME=Yakan E-commerce
APP_ENV=production
APP_KEY=base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Yakan E-commerce

FORCE_HTTPS=true
SECURITY_HEADERS_ENABLED=true
MAX_FILE_SIZE=5120
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx
```

5. Click "Save"

### Step 5: Generate Public Domain
1. Go to "Settings" tab
2. Scroll to "Networking"
3. Click "Generate Domain"
4. Copy the domain (e.g., `your-app.up.railway.app`)
5. Update APP_URL in variables with this domain

### Step 6: Deploy
1. Railway will automatically deploy after you save variables
2. Wait for deployment to complete (check "Deployments" tab)
3. Watch the build logs for any errors

### Step 7: Run Migrations
1. In Railway dashboard, click on your service
2. Go to "Settings" tab
3. Scroll to "Deploy"
4. Under "Custom Start Command", add:
```bash
php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT
```
5. Or use the "Shell" tab to run:
```bash
php artisan migrate --force
```

### Step 8: Create Admin User
1. Click "Shell" tab in Railway dashboard
2. Run:
```bash
php artisan tinker
```
3. Then create your admin user with your existing script

---

## Alternative: Install Railway CLI (When Network Works)

### Download Railway CLI Manually
1. Go to: https://github.com/railwayapp/cli/releases
2. Download `railway_windows_amd64.zip`
3. Extract to `C:\Program Files\Railway`
4. Add to PATH environment variable
5. Restart terminal

### Then run:
```cmd
railway login
railway link
railway up
```

---

## Update Your App After Changes

### Method 1: Git Push (Automatic)
```cmd
git add .
git commit -m "Updated features"
git push origin main
```
Railway will auto-deploy on push!

### Method 2: Manual Deploy
1. Push to GitHub
2. Railway will detect changes and redeploy automatically

---

## Verify Deployment

1. Open your Railway domain in browser
2. Check if app loads correctly
3. Test database connection
4. Test file uploads
5. Test authentication

---

## Troubleshooting

### Build Fails
- Check build logs in Railway dashboard
- Verify all environment variables are set
- Ensure APP_KEY is set correctly

### Database Connection Error
- Verify MySQL service is running
- Check database variables are linked correctly
- Run migrations: `php artisan migrate --force`

### 500 Error
- Check APP_KEY is set
- Check APP_DEBUG=false
- View logs in Railway dashboard

### Storage/Upload Issues
1. In Railway dashboard → Settings → Volumes
2. Add volume: `/app/storage/app/public`
3. Run in shell: `php artisan storage:link`

---

## Cost Estimate
- Free tier: $5 credit/month
- Small app: ~$5-10/month
- Monitor usage in Railway dashboard

---

## Quick Reference

**Your APP_KEY:** `base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=`

**Railway Dashboard:** https://railway.app/dashboard

**Deployment Status:** Check "Deployments" tab

**View Logs:** Click on service → "Logs" tab

**Run Commands:** Click on service → "Shell" tab

---

## Next Steps After Deployment

1. ✅ Test the live site
2. ✅ Create admin user
3. ✅ Upload test products
4. ✅ Test ordering system
5. ✅ Configure payment gateways
6. ✅ Set up email notifications
7. ✅ Add custom domain (optional)

---

## Support
- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Railway Status: https://status.railway.app
