#!/bin/bash
set -e

echo "🚀 Starting Railway deployment..."

# Clear any existing cache
php artisan config:clear || true
php artisan cache:clear || true

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force --no-interaction

# Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
if ! php artisan storage:link 2>/dev/null; then
    echo "⚠️  Storage link already exists or failed to create (this is usually safe to ignore)"
fi

echo "✅ Deployment complete!"
