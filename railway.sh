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

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Cache for performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment complete!"
