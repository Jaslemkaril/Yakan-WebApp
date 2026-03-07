#!/bin/bash
# Railway Session Fix Script
# This ensures the sessions table exists and is properly configured

echo "🔧 Starting Railway Session Fix..."

# Run migrations to create sessions table
echo "📦 Running migrations..."
php artisan migrate --force

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear old sessions from database
echo "🗑️  Clearing old sessions..."
php artisan session:table 2>/dev/null || echo "Session table already exists"

# Test database connection
echo "✅ Testing database connection..."
php artisan tinker --execute="echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'SUCCESS' : 'FAILED') . PHP_EOL;"

# Verify sessions table exists
echo "✅ Verifying sessions table..."
php artisan tinker --execute="echo 'Sessions table exists: ' . (Schema::hasTable('sessions') ? 'YES' : 'NO') . PHP_EOL;"

echo "✅ Railway Session Fix Complete!"
echo ""
echo "Next steps:"
echo "1. Go to Railway Dashboard -> Variables"
echo "2. Update SESSION_DRIVER=database"
echo "3. Redeploy your app"
echo "4. Clear browser cookies and try logging in again"
