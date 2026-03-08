#!/bin/bash
# Railway startup script - optimized for fast boot
# PHP server must start ASAP to pass Railway's health check

echo "🚀 Starting Railway deployment..."

# ── 1. Clear cached files (instant) ──────────────────────────────────────────
rm -f bootstrap/cache/config.php \
      bootstrap/cache/routes-v7.php \
      bootstrap/cache/services.php \
      bootstrap/cache/packages.php 2>/dev/null || true
rm -rf storage/framework/cache/data/* \
       storage/framework/views/* 2>/dev/null || true

# ── 2. Create required directories & set permissions (instant) ────────────────
mkdir -p storage/framework/sessions \
         storage/framework/cache/data \
         storage/framework/views \
         storage/logs \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ── 3. Artisan cache clear (fast) ─────────────────────────────────────────────
php artisan config:clear 2>/dev/null || true
php artisan cache:clear  2>/dev/null || true
php artisan route:clear  2>/dev/null || true
php artisan view:clear   2>/dev/null || true

# ── 4. Run database migrations (needed before serving requests) ───────────────
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ Migrations failed, continuing..."

# ── 5. Storage symlink (fast) ─────────────────────────────────────────────────
echo "🔗 Creating storage link..."
rm -f public/storage 2>/dev/null || true
php artisan storage:link --force 2>/dev/null || ln -sf ../storage/app/public public/storage 2>/dev/null || true

# ── 6. Heavy/slow tasks in BACKGROUND (don't block PHP server startup) ────────
echo "⏳ Running heavy tasks in background..."
(
    php artisan session:table 2>/dev/null || true
    php artisan migrate --force --no-interaction 2>/dev/null || true
    php artisan db:seed --class=PhilippineAddressSeeder --force 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    echo "✅ Background tasks complete."
) &

echo "✅ Startup complete — starting PHP server..."
