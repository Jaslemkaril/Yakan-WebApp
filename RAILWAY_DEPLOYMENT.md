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

## Railway Free Tier Optimization

### Overview

Railway's free tier has strict resource limits, particularly memory constraints during the build process. This section provides optimization strategies to ensure successful deployments within these limits.

### Common Free Tier Issues

#### Out of Memory (OOM) Errors

**Symptoms:**
- Build process exits with code 137
- Error: `process did not complete successfully: exit code: 137`
- apt-get or composer install failures

**Root Cause:** Exit code 137 indicates the process was killed by the OS due to memory exhaustion. Railway's free tier has limited memory for builds.

### Optimization Strategies

#### 1. Nixpacks Configuration

The repository includes an optimized `nixpacks.toml` that:
- Uses minimal nixPkgs (only essential PHP extensions)
- Installs only git via apt (reduces apt-get memory usage)
- Uses `--no-dev --prefer-dist` flags for composer
- Pre-caches Laravel configurations during build phase

**Key optimizations:**
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.mbstring", "php82Extensions.pdo", "php82Extensions.pdo_mysql"]
aptPkgs = ["git"]  # Only essential packages

[phases.install]
cmds = [
    "composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist"
]
```

#### 2. Build Size Reduction

The `.railwayignore` file excludes unnecessary files from the build:
- Development dependencies (`node_modules/`, `tests/`)
- Documentation files (except README and deployment guides)
- IDE and version control files
- Development configuration files

**Impact:** ~50% reduction in build footprint

#### 3. Deployment Script

The `railway.sh` script handles deployment tasks efficiently:
- Clears old cache before building new cache
- Runs migrations with proper error handling
- Creates storage links
- Optimizes for production with Laravel cache commands

#### 4. PHP Memory Settings

The `php.ini` file sets optimal memory limits:
```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
```

#### 5. Composer Optimization

The `composer.json` includes:
- Platform configuration to lock PHP version
- Optimized autoloader settings
- Prefer dist over source installations
- Railway-specific build scripts

### Memory Usage Tips

1. **Avoid Heavy Dependencies:** Review `composer.json` and remove unused packages
2. **Use File-Based Cache:** Set `CACHE_STORE=file` instead of Redis
3. **Minimize Build Steps:** The nixpacks configuration runs only essential commands
4. **Clear Old Cache:** The railway.sh script clears cache before rebuilding

### Build Process Flow

1. **Setup Phase:** Install PHP and essential extensions via nix
2. **Install Phase:** Run composer install with optimization flags
3. **Build Phase:** Cache Laravel configs (route, view, config)
4. **Start Phase:** Run railway.sh then start the application server

### Environment Variables for Free Tier

**Essential Settings:**
```env
APP_ENV=production
APP_DEBUG=false
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_LEVEL=error
```

**Why These Matter:**
- `CACHE_STORE=file`: Avoids Redis memory overhead
- `SESSION_DRIVER=file`: File-based sessions use less memory
- `QUEUE_CONNECTION=sync`: No background queue workers needed
- `LOG_LEVEL=error`: Reduces log file size

### Troubleshooting Free Tier Builds

#### Build Fails at apt-get

**Error:** `apt-get update && apt-get install -y ... exit code: 137`

**Solution:** The nixpacks.toml now uses minimal aptPkgs. If you need additional packages, add them one at a time and test.

#### Composer Install Times Out

**Error:** `composer install` killed during dependency resolution

**Solutions:**
1. Ensure `--no-dev` flag is used (dev dependencies not needed in production)
2. Use `--prefer-dist` to download pre-built packages instead of cloning repos
3. Remove unused dependencies from composer.json

#### Cache Commands Fail

**Error:** Cache directory not writable

**Solution:** The railway.sh script clears cache before rebuilding. Ensure `storage/` and `bootstrap/cache/` are writable.

#### Migration Failures

**Error:** Migrations fail during deployment

**Solutions:**
1. Verify MySQL database is provisioned and connected
2. Check DB_* environment variables are correctly set
3. Ensure database user has proper permissions
4. Use `--force` flag for production migrations

### Performance Monitoring

After deployment, monitor:
1. **Build Logs:** Check memory usage during build
2. **Deploy Time:** Optimized builds should complete in 2-3 minutes
3. **Application Memory:** Monitor runtime memory usage in Railway dashboard
4. **Response Times:** Ensure cached configs improve performance

### Caching Strategies

The deployment uses three levels of caching:

1. **Configuration Cache:** `php artisan config:cache`
   - Combines all config files into single cached file
   - Reduces file I/O during requests

2. **Route Cache:** `php artisan route:cache`
   - Pre-compiles route definitions
   - Speeds up routing significantly

3. **View Cache:** `php artisan view:cache`
   - Pre-compiles Blade templates
   - Reduces template rendering overhead

### Expected Outcomes

With these optimizations:
- ✅ Build completes within free tier memory limits
- ✅ Deployment time: 2-3 minutes (vs 5+ minutes unoptimized)
- ✅ Memory usage during build: <512MB
- ✅ Runtime memory usage: ~100-200MB
- ✅ Faster application response times due to caching

### When to Upgrade

Consider upgrading from free tier if:
- Multiple deployments per day (free tier has deployment limits)
- Need background workers (queue processing)
- Require Redis for caching
- Need more than 512MB RAM
- Application receives high traffic

---

**Last Updated:** January 2026
