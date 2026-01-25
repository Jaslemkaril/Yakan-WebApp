<?php
/**
 * Cleanup Test Data Script
 * Run this to remove test orders and products from the database
 * Usage: php cleanup-database.php
 */

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Cleanup Test Data from Database\n";
echo "========================================\n\n";

echo "WARNING: This will delete all orders, products, and test data!\n";
echo "Continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "Cancelled.\n";
    exit(0);
}
fclose($handle);

echo "\n[1/12] Deleting custom orders...\n";
DB::table('custom_orders')->delete();
echo "✓ Deleted custom orders\n";

echo "[2/12] Deleting order items...\n";
DB::table('order_items')->delete();
echo "✓ Deleted order items\n";

echo "[3/12] Deleting orders...\n";
DB::table('orders')->delete();
echo "✓ Deleted orders\n";

echo "[4/12] Deleting cart items...\n";
DB::table('carts')->delete();
echo "✓ Deleted carts\n";

echo "[5/12] Deleting products...\n";
DB::table('products')->delete();
echo "✓ Deleted products\n";

echo "[6/12] Deleting inventory...\n";
DB::table('inventory')->delete();
echo "✓ Deleted inventory\n";

echo "[7/12] Deleting reviews...\n";
DB::table('reviews')->delete();
echo "✓ Deleted reviews\n";

echo "[8/12] Deleting notifications...\n";
DB::table('notifications')->delete();
echo "✓ Deleted notifications\n";

echo "[9/12] Deleting coupon redemptions...\n";
DB::table('coupon_redemptions')->delete();
echo "✓ Deleted coupon redemptions\n";

echo "[10/12] Deleting coupons...\n";
DB::table('coupons')->delete();
echo "✓ Deleted coupons\n";

echo "[11/12] Deleting wishlist items...\n";
DB::table('wishlist_items')->delete();
echo "✓ Deleted wishlist items\n";

echo "[12/12] Deleting contact messages...\n";
DB::table('contact_messages')->delete();
echo "✓ Deleted contact messages\n";

echo "\n========================================\n";
echo "✓ Database Cleanup Complete!\n";
echo "========================================\n\n";

echo "Remaining data:\n";
echo "Users: " . DB::table('users')->count() . "\n";
echo "Admins: " . DB::table('admins')->count() . "\n";
echo "Categories: " . DB::table('categories')->count() . "\n";
echo "Products: " . DB::table('products')->count() . "\n";
echo "Orders: " . DB::table('orders')->count() . "\n";
echo "Custom Orders: " . DB::table('custom_orders')->count() . "\n";
echo "\nDone!\n";
