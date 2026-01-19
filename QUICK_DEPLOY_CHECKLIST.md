# ✅ Railway Deployment Checklist

## Your Credentials
- **APP_KEY:** `base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=`
- **Railway URL:** https://railway.app

---

## Pre-Deployment Checklist

- [ ] Push code to GitHub repository
- [ ] Ensure `.env` is in `.gitignore` (already done)
- [ ] All migrations are ready
- [ ] Database seeders prepared (if needed)

---

## Railway Setup Steps

### 1. Create Project (5 minutes)
- [ ] Go to https://railway.app
- [ ] Sign up/Login with GitHub
- [ ] Click "New Project"
- [ ] Select "Deploy from GitHub repo"
- [ ] Choose your Yakan repository

### 2. Add Database (2 minutes)
- [ ] Click "New" → "Database" → "MySQL"
- [ ] Wait for MySQL to provision

### 3. Set Environment Variables (3 minutes)
- [ ] Click on web service → "Variables"
- [ ] Copy from `.env.railway` file
- [ ] **IMPORTANT:** Set APP_KEY to: `base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=`
- [ ] Link MySQL variables (auto-configured)
- [ ] Click "Save"

### 4. Generate Domain (1 minute)
- [ ] Go to "Settings" → "Networking"
- [ ] Click "Generate Domain"
- [ ] Copy domain URL
- [ ] Update APP_URL variable with domain

### 5. Deploy (5-10 minutes)
- [ ] Railway auto-deploys after saving variables
- [ ] Watch "Deployments" tab for progress
- [ ] Check build logs for errors

### 6. Run Migrations (2 minutes)
- [ ] Click "Shell" tab
- [ ] Run: `php artisan migrate --force`
- [ ] Verify tables created

### 7. Create Admin User (2 minutes)
- [ ] In Shell, run: `php artisan tinker`
- [ ] Create admin user
- [ ] Exit tinker

### 8. Test Deployment (5 minutes)
- [ ] Open your Railway domain
- [ ] Test login
- [ ] Test product pages
- [ ] Test cart functionality
- [ ] Test admin panel

---

## Post-Deployment Tasks

### Configure Email
- [ ] Update MAIL_* variables in Railway
- [ ] Test email sending
- [ ] Verify order confirmations work

### Configure Payments
- [ ] Add payment gateway credentials
- [ ] Test payment flow
- [ ] Verify webhooks (if applicable)

### Set Up Storage
- [ ] Add volume in Railway (Settings → Volumes)
- [ ] Mount path: `/app/storage/app/public`
- [ ] Run: `php artisan storage:link`
- [ ] Test file uploads

### Security
- [ ] Verify APP_DEBUG=false
- [ ] Verify FORCE_HTTPS=true
- [ ] Test CSRF protection
- [ ] Review security headers

### Performance
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`
- [ ] Test page load times

---

## Update Workflow (After Deployment)

### Every time you update features:

```cmd
# 1. Make your changes locally
# 2. Test locally
# 3. Commit and push
git add .
git commit -m "Added new feature"
git push origin main

# Railway auto-deploys!
```

### If you need to run commands after deploy:
1. Go to Railway dashboard
2. Click "Shell" tab
3. Run your commands:
   - `php artisan migrate --force`
   - `php artisan cache:clear`
   - `php artisan config:cache`

---

## Monitoring

### Check App Health
- [ ] Railway Dashboard → Logs
- [ ] Monitor CPU/Memory usage
- [ ] Check error rates
- [ ] Review deployment history

### Set Up Alerts (Optional)
- [ ] Railway → Settings → Notifications
- [ ] Enable deployment notifications
- [ ] Enable error alerts

---

## Estimated Timeline
- **Initial Setup:** 20-30 minutes
- **First Deployment:** 10-15 minutes
- **Future Updates:** 2-5 minutes (just git push!)

---

## Common Issues & Solutions

### Build Fails
**Solution:** Check build logs, verify composer.json and package.json

### Database Connection Error
**Solution:** Verify MySQL variables are linked correctly

### 500 Error
**Solution:** Check APP_KEY is set, view logs in Railway

### File Upload Fails
**Solution:** Add volume, run `php artisan storage:link`

### Slow Performance
**Solution:** Run cache commands, consider upgrading Railway plan

---

## Support Resources

- **Railway Docs:** https://docs.railway.app
- **Railway Discord:** https://discord.gg/railway
- **Laravel Docs:** https://laravel.com/docs
- **Your Deployment Guide:** See `RAILWAY_MANUAL_SETUP.md`

---

## Ready to Deploy?

1. Open https://railway.app
2. Follow the checklist above
3. Your app will be live in ~30 minutes!

**Your APP_KEY (don't lose this):**
```
base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=
```
