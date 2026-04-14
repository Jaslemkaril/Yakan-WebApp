# Production Error Fix Guide

## Current Error
```
file_get_contents(...styles.css): Failed to open stream
```

This is a **display error**, not the real problem.

## Steps to Debug

### 1. Enable Debug Mode
Set in Railway:
```
APP_DEBUG=true
```

Redeploy and visit homepage to see **real error message**.

### 2. Check Database
SQLite file must be writable:
```bash
touch /app/storage/database.sqlite
chmod 666 /app/storage/database.sqlite
```

### 3. Check Logs
In Railway dashboard:
- Go to "Logs" tab
- Look for actual PHP errors
- Search for "SQLSTATE" or "Class not found"

### 4. Common Issues

#### Database Not Created
**Error**: `SQLSTATE[HY000] [14] unable to open database file`
**Fix**: Ensure SQLite file is created in start command

#### Missing Admin User
**Error**: No users in database
**Fix**: Run `php artisan db:seed --class=AdminUserSeeder`

#### Missing Product Data
**Error**: `Trying to get property of non-object`
**Fix**: Seed database with products

#### View Not Found
**Error**: `View [welcome] not found`
**Fix**: Check `resources/views/welcome.blade.php` exists

#### Route Not Found
**Error**: `Route [welcome] not defined`
**Fix**: Check `routes/web.php` for WelcomeController route

## Temporary Workaround

While debugging, you can bypass the error page:

**Add to `app/Exceptions/Handler.php`:**
```php
public function register(): void
{
    $this->renderable(function (\Throwable $e) {
        if (app()->environment('production') && config('app.debug')) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    });
}
```

This shows errors as JSON instead of fancy pages.

## After Finding Real Error

1. Fix the actual issue
2. **Disable debug mode**:
   ```
   APP_DEBUG=false
   ```
3. Redeploy

## Most Likely Issues

Based on your setup:

1. **Database not seeded** - No products/categories
2. **WelcomeController query fails** - Trying to load products that don't exist
3. **Image paths broken** - Product images reference missing files
4. **Session storage** - Session driver misconfigured

## Quick Test

Visit `/health-check` to see system status (after this PR merges).

## Health Check Endpoint

After deploying these changes, visit:
```
https://yakan-webapp-production.up.railway.app/health-check
```

This will show:
- Current environment (production/local)
- Debug mode status
- Database name and connection
- Storage path
- Cache status
- Configuration status

Example response:
```json
{
  "status": "ok",
  "app_env": "production",
  "app_debug": true,
  "database": "database.sqlite",
  "storage_path": "/app/storage",
  "view_cache": true,
  "routes_cached": true,
  "config_cached": true
}
```

## Environment Variables to Set in Railway

Make sure these are set in Railway Dashboard -> Variables:

```
APP_DEBUG=true
APP_ENV=production
APP_KEY=base64:... (generate with `php artisan key:generate --show`)
DB_CONNECTION=sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_LEVEL=debug
LOG_CHANNEL=stack
```

## Deployment Command Breakdown

The updated `nixpacks.toml` start command does:

1. `mkdir -p /app/storage` - Ensure storage directory exists
2. `touch /app/storage/database.sqlite` - Create SQLite database file
3. `chmod 666 /app/storage/database.sqlite` - Make it writable
4. `php artisan config:clear` - Clear any stale cached config
5. `php artisan cache:clear` - Clear application cache
6. `php artisan view:clear` - Clear compiled views
7. `php artisan migrate --force` - Run database migrations
8. `php artisan db:seed --force --class=AdminUserSeeder` - Seed admin user
9. `php artisan storage:link --force` - Create storage symlink
10. `php artisan config:cache` - Cache fresh configuration
11. `php artisan route:cache` - Cache routes
12. `php artisan view:cache` - Cache views
13. `php artisan serve` - Start the application

## Next Steps After Deployment

1. Deploy this PR to Railway
2. Visit the homepage and check for errors
3. If you see a different error (the real one), check the sections above
4. Visit `/health-check` to verify system status
5. Check Railway logs for detailed error messages
6. Fix the actual underlying issue
7. Once fixed, set `APP_DEBUG=false` and redeploy

## Common Real Errors You Might See

### 1. Missing Storage Directory Permissions
```
The stream or file "/app/storage/logs/laravel.log" could not be opened
```
**Fix**: Already handled by `chmod 666` in deployment

### 2. Missing APP_KEY
```
No application encryption key has been specified
```
**Fix**: Generate key with `php artisan key:generate --show` and set in Railway

### 3. Database Connection Failed
```
SQLSTATE[HY000] [14] unable to open database file
```
**Fix**: Already handled by `mkdir -p` and `touch` in deployment

### 4. Seeder Class Not Found
```
Target class [AdminUserSeeder] does not exist
```
**Fix**: Run `composer dump-autoload` in build phase (if needed)

## Support

If you encounter issues not covered here:
1. Check Railway deployment logs
2. Visit `/health-check` endpoint
3. Enable `APP_DEBUG=true` to see detailed errors
4. Search Laravel documentation for specific error messages
