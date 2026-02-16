#!/bin/bash
set -e

echo "🚀 Starting Railway deployment..."

# Clear caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force --no-interaction

# Seed Philippine address data
echo "🗺️ Seeding Philippine address data..."
php artisan db:seed --class=PhilippineAddressSeeder --force || echo "⚠️ Seeder already ran or failed, continuing..."

# Create storage link (critical for image visibility)
echo "🔗 Creating storage link..."
# Remove old symlink if it exists
rm -f public/storage || true
# Create new symlink
php artisan storage:link --force || {
    echo "⚠️ Warning: storage:link failed, but continuing..."
    mkdir -p storage/app/public
    ln -sf ../storage/app/public public/storage || true
}

# Cache for performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment complete!"
