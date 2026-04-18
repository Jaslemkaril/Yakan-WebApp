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
php artisan migrate --force --no-interaction || echo "⚠️ Base migrate failed, attempting critical fallback migrations..."

# Critical fallback: ensure product variants table exists for admin product flows.
php artisan migrate --force --no-interaction --path=database/migrations/2026_04_16_160000_create_product_variants_table.php || echo "⚠️ product_variants fallback migration failed."

# Critical fallback: ensure dependent variant foreign-key columns are applied.
php artisan migrate --force --no-interaction --path=database/migrations/2026_04_16_160100_add_variant_id_to_carts_table.php || echo "⚠️ carts.variant_id fallback migration failed."
php artisan migrate --force --no-interaction --path=database/migrations/2026_04_16_160200_add_variant_columns_to_order_items_table.php || echo "⚠️ order_items.variant columns fallback migration failed."

# Critical fallback: ensure downpayment/partial-payment columns exist so mobile
# downpayment orders are correctly persisted and surfaced in the admin UI.
php artisan migrate --force --no-interaction --path=database/migrations/2026_04_16_190000_add_downpayment_fields_to_orders_table.php || echo "⚠️ orders downpayment fields fallback migration failed."

# ── 4b. Ensure Philippine address data is populated (idempotent) ─────────────
echo "🗺️ Syncing Philippine address data..."
php artisan db:seed --class=PhilippineSyncSeeder --force 2>/dev/null || echo "⚠️ Address sync failed, continuing..."

# ── 5. Storage symlink (fast) ─────────────────────────────────────────────────
echo "🔗 Creating storage link..."
rm -f public/storage 2>/dev/null || true
php artisan storage:link --force 2>/dev/null || ln -sf ../storage/app/public public/storage 2>/dev/null || true

# ── 6. Heavy/slow tasks in BACKGROUND (don't block PHP server startup) ────────
echo "⏳ Running heavy tasks in background..."
(
    php artisan session:table 2>/dev/null || true
    php artisan migrate --force --no-interaction 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    echo "✅ Background tasks complete."
) &

echo "✅ Startup complete — starting PHP server..."
