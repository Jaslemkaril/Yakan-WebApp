# Railway Environment Variables Checklist

## ⚠️ CRITICAL: Your deployment crashed!

The build succeeded but the app crashed on startup. This is usually due to missing environment variables.

## Required Environment Variables in Railway Dashboard

Go to your Railway project → Click on your service → Variables tab → Add these:

### 1. Application Settings
```env
APP_NAME=Yakan E-commerce
APP_ENV=production
APP_KEY=base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}
```

### 2. Database Settings (CRITICAL!)
Make sure you added MySQL database in Railway, then add:
```env
DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}
```

### 3. Session & Cache
```env
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. Mail (Optional but recommended)
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Yakan E-commerce
```

## Quick Fix Steps:

1. **Add MySQL Database** (if not done):
   - In Railway dashboard: Click "New" → "Database" → "Add MySQL"
   - Wait for it to provision

2. **Set Environment Variables**:
   - Click on your web service
   - Go to "Variables" tab
   - Click "Raw Editor"
   - Paste the variables above
   - Click "Save"

3. **Redeploy**:
   - Railway will auto-redeploy after saving variables
   - Or click "Deploy" → "Redeploy"

4. **Check Logs**:
   - Go to "Logs" tab to see what's happening
   - Look for error messages

## Common Crash Reasons:

1. ❌ **No APP_KEY set** → Set the one above
2. ❌ **No database configured** → Add MySQL service
3. ❌ **Database variables not linked** → Use ${{MYSQLHOST}} format
4. ❌ **Missing .env values** → Copy from .env.railway file

## Verify Setup:

After setting variables, check the logs for:
- ✅ "Application cache cleared!"
- ✅ "Configuration cache cleared!"
- ✅ "Migration table created successfully"
- ✅ "Laravel development server started"

## Still Crashing?

Check the Deploy Logs in Railway for specific error messages and share them.
