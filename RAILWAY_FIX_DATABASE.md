# 🔴 URGENT: Fix Database Connection Error

## The Problem
Your app is trying to connect to `${MYSQLHOST}` (literal string) instead of the actual MySQL host.

## Solution: Add MySQL Database in Railway

### Step 1: Add MySQL Service
1. Go to your Railway project dashboard
2. Click **"New"** button (top right)
3. Select **"Database"**
4. Choose **"Add MySQL"**
5. Wait for MySQL to provision (takes ~30 seconds)

### Step 2: Link Database to Your Service
Railway should automatically create these variables:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

### Step 3: Set Required Environment Variables
Click on your **web service** → **Variables** tab → **Raw Editor**, paste:

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

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Yakan E-commerce
```

**IMPORTANT:** Use `${{MYSQLHOST}}` with double curly braces, not `${MYSQLHOST}`

### Step 4: Generate Public Domain
1. Go to **Settings** tab
2. Scroll to **"Networking"**
3. Click **"Generate Domain"**
4. Copy the domain (e.g., `your-app-production-xxxx.up.railway.app`)

### Step 5: Redeploy
Railway will automatically redeploy after saving variables.

## Alternative: Use SQLite (Simpler)

If you don't need MySQL right now, use SQLite instead:

```env
APP_NAME=Yakan E-commerce
APP_ENV=production
APP_KEY=base64:1D63radisbAQWPaa9VUcAULfsnQ216Js5VijBfDr7EU=
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=sqlite
DB_DATABASE=/app/storage/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Yakan E-commerce
```

Then add a volume:
1. Go to **Settings** → **Volumes**
2. Click **"Add Volume"**
3. Mount path: `/app/storage`

## Verify It Works

After redeployment, check **Deploy Logs** for:
- ✅ "Migration table created successfully"
- ✅ "Laravel development server started"
- ✅ No database connection errors

## Still Having Issues?

Check the **Deploy Logs** tab and look for the specific error message.
