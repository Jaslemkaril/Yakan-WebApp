#!/bin/bash
# Railway deployment startup script
# Do NOT use set -e — we want the server to start even if some steps fail

echo "🚀 Starting Railway deployment..."

# CRITICAL: Force delete ALL cached files to ensure fresh state
echo "🧹 Force clearing ALL cached files..."
rm -f bootstrap/cache/*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true
rm -f bootstrap/cache/routes-v7.php 2>/dev/null || true
rm -f bootstrap/cache/services.php 2>/dev/null || true
rm -f bootstrap/cache/packages.php 2>/dev/null || true
rm -rf storage/framework/cache/data/* 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true

# Also clear via artisan (belt and suspenders)
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Debug: Show environment variable status (not values!)
echo "🔍 Environment check:"
echo "  APP_KEY: $([ -n \"$APP_KEY\" ] && echo 'SET' || echo 'NOT SET - CRITICAL!')"
echo "  APP_ENV: ${APP_ENV:-not set}"
echo "  DB_HOST: $([ -n \"$DB_HOST\" ] && echo 'SET' || echo 'NOT SET')"
echo "  MYSQLHOST: $([ -n \"$MYSQLHOST\" ] && echo 'SET' || echo 'NOT SET')"
echo "  MYSQL_URL: $([ -n \"$MYSQL_URL\" ] && echo 'SET' || echo 'NOT SET')"
echo "  DATABASE_URL: $([ -n \"$DATABASE_URL\" ] && echo 'SET' || echo 'NOT SET')"
echo "  MYSQLPORT: $([ -n \"$MYSQLPORT\" ] && echo 'SET' || echo 'NOT SET')"
echo "  MYSQLDATABASE: $([ -n \"$MYSQLDATABASE\" ] && echo 'SET' || echo 'NOT SET')"
echo "  MYSQLUSER: $([ -n \"$MYSQLUSER\" ] && echo 'SET' || echo 'NOT SET')"

# Test MySQL connection
echo "🔌 Testing MySQL connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'SUCCESS: Connected to MySQL'; } catch (Exception \$e) { echo 'FAILED: ' . \$e->getMessage(); }" 2>/dev/null || echo "Connection test skipped"

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force --no-interaction || echo "⚠️ Migration failed, continuing..."

# Ensure sessions table exists and storage permissions are correct
echo "🔧 Setting up session storage..."
mkdir -p storage/framework/sessions storage/framework/cache storage/framework/views
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Seed Philippine address data
echo "🗺️ Seeding Philippine address data..."
php artisan db:seed --class=PhilippineAddressSeeder --force 2>/dev/null || echo "⚠️ Seeder already ran or failed, continuing..."

# Create storage link (critical for image visibility)
echo "🔗 Creating storage link..."
rm -f public/storage 2>/dev/null || true
php artisan storage:link --force 2>/dev/null || {
    echo "⚠️ Warning: storage:link failed, creating manually..."
    mkdir -p storage/app/public
    ln -sf ../storage/app/public public/storage || true
}

# NOTE: Do NOT use config:cache on Railway - it breaks environment variables
# NOTE: route:cache CANNOT be used - routes/web.php contains Closure-based routes

# View cache (already done in build phase, but refresh just in case)
php artisan view:cache 2>/dev/null || echo "⚠️ view:cache failed, views will compile on-the-fly"

echo "✅ Deployment startup complete!"
