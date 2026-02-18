#!/bin/bash
# Do NOT use set -e — we want the server to start even if some steps fail

echo "🚀 Starting Railway deployment..."

# Clear ALL caches first to ensure fresh state
echo "🧹 Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force --no-interaction || echo "⚠️ Migration failed, continuing..."

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
