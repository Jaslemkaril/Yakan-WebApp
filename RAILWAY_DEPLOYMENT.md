# Railway Deployment Guide for Yakan E-commerce

This guide provides step-by-step instructions for deploying the Yakan E-commerce application on Railway with a MySQL database.

## Prerequisites

- Railway account (sign up at [railway.app](https://railway.app))
- GitHub repository access
- Basic understanding of environment variables

## Quick Start

### 1. Create Railway Project

1. Log in to your Railway account
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Choose the `Jaslemkaril/Yakan-WebApp` repository
5. Railway will automatically detect it's a Laravel application

### 2. Add MySQL Database

**Important:** The application requires MySQL for production deployments. SQLite is not suitable for Railway due to ephemeral filesystem.

1. In your Railway project dashboard, click **"New"** (top right)
2. Select **"Database"**
3. Choose **"Add MySQL"**
4. Wait for MySQL to provision (~30-60 seconds)

Railway will automatically create these environment variables:
- `MYSQLHOST` - MySQL server hostname
- `MYSQLPORT` - MySQL server port (usually 3306)
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database username
- `MYSQLPASSWORD` - Database password

### 3. Configure Environment Variables

Click on your **web service** → **Variables** tab → **Raw Editor**, and paste the following configuration:

```env
# Application
APP_NAME="Yakan E-commerce"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

# Database - Use Railway MySQL variables
DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

# Cache & Session (File-based for Railway)
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Broadcasting & Filesystem
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Mail Configuration (Optional - configure as needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# OAuth Services (Optional - configure as needed)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=${{RAILWAY_PUBLIC_DOMAIN}}/auth/google/callback

FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI=${{RAILWAY_PUBLIC_DOMAIN}}/auth/facebook/callback

# Security
FORCE_HTTPS=true
SECURITY_HEADERS_ENABLED=true
RATE_LIMITING_ENABLED=true
```

**Critical Notes:**
- Use `${{VARIABLE}}` with **double curly braces** for Railway variable references
- Replace `YOUR_APP_KEY_HERE` with your actual Laravel app key (generate using `php artisan key:generate --show`)
- The `RAILWAY_PUBLIC_DOMAIN` variable is automatically provided by Railway

### 4. Generate Application Key

If you don't have an app key yet:

```bash
# Run locally
php artisan key:generate --show
```

Copy the generated key (including `base64:` prefix) and paste it as the `APP_KEY` value in Railway.

### 5. Generate Public Domain

1. Go to your web service → **Settings** tab
2. Scroll to **"Networking"** section
3. Click **"Generate Domain"**
4. Railway will provide a domain like: `yakan-webapp-production.up.railway.app`

### 6. Deploy and Run Migrations

Railway will automatically trigger a deployment after you save the environment variables.

**First Time Deployment:**

After the initial deployment, you need to run migrations:

1. Go to your web service → **Deployments** tab
2. Click on the latest deployment
3. Open the **Deploy Logs** tab to monitor progress
4. Once deployed, you can run migrations via Railway CLI or add a build script

**Option A: Using Railway CLI**

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Run migrations
railway run php artisan migrate --force
```

**Option B: Add to Build Process (Advanced)**

**Warning:** Running migrations automatically during build can cause issues if the database isn't ready yet or if migrations fail. Use this approach with caution.

Update your `composer.json` to include migrations in the deployment:

```json
{
  "scripts": {
    "post-install-cmd": [
      "php artisan migrate --force",
      "php artisan config:cache",
      "php artisan route:cache",
      "php artisan view:cache"
    ]
  }
}
```

**Note:** Railway's deployment hooks are a safer alternative for running migrations.

### 7. Verify Deployment

After deployment completes:

1. Check **Deploy Logs** for any errors
2. Visit your generated domain
3. Verify the application loads correctly
4. Test key functionality:
   - User registration/login
   - Product browsing
   - Cart operations
   - Order placement

## Troubleshooting

### Database Connection Errors

**Error:** `Database file at path [/app/database/database.sqlite] does not exist`

**Solution:** Ensure you've added MySQL database and configured the environment variables correctly. The app should use MySQL, not SQLite.

### Migration Errors

**Error:** `could not find driver`

**Solution:** Railway's PHP buildpack includes MySQL drivers. Ensure `DB_CONNECTION=mysql` in your environment variables.

### Cache/Session Errors

**Error:** `Redis connection refused` or `Cache store [redis] is not defined`

**Solution:** Use `CACHE_STORE=file` and `SESSION_DRIVER=file` instead of Redis. Railway doesn't provide Redis by default.

### App Key Not Set

**Error:** `No application encryption key has been specified`

**Solution:** Generate a new key using `php artisan key:generate --show` and add it to `APP_KEY` environment variable.

### 500 Internal Server Error

**Solutions:**
1. Check Deploy Logs for specific error messages
2. **If absolutely necessary for troubleshooting**, temporarily set `APP_DEBUG=true` to see detailed errors, then **immediately** set it back to `false` after identifying the issue (never leave debug mode enabled in production as it exposes sensitive information)
3. Verify all required environment variables are set
4. Check file permissions for storage and cache directories

## Environment Differences

### Production (Railway)
- Uses MySQL database
- File-based cache and sessions
- Error logging only
- HTTPS enforced
- Optimized autoloader

### Local Development
- Can use SQLite or MySQL
- Any cache/session driver
- Debug mode enabled
- HTTP allowed
- Standard autoloader

## Updating the Application

When you push changes to GitHub:

1. Railway automatically detects the changes
2. Triggers a new deployment
3. Runs the build process
4. Deploys the updated application

**Manual Redeploy:**
- Go to Deployments → Click **"Deploy"** button

## Best Practices

1. **Environment Variables:** Never commit `.env` file to repository
2. **Database Backups:** Regularly backup your MySQL database from Railway dashboard
3. **Logging:** Monitor Deploy Logs regularly for errors
4. **Caching:** Use `php artisan config:cache` in production for better performance
5. **Security:** Keep `APP_DEBUG=false` in production
6. **SSL:** Railway provides SSL automatically for generated domains

## Additional Resources

- [Railway Documentation](https://docs.railway.app/)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Railway Community Forum](https://help.railway.app/)

## Support

If you encounter issues:
1. Check the Deploy Logs in Railway dashboard
2. Review this documentation
3. Consult Laravel documentation for framework-specific issues
4. Check Railway status page for platform issues

---

**Last Updated:** January 2026
