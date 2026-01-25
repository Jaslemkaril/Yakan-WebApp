<?php
/**
 * Simple Database Cleanup Script
 * Removes test data while preserving users
 */

// Load Laravel
require __DIR__ . '/../../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
} catch (Exception $e) {
    echo "❌ Error: Failed to load Laravel application\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "- You are running this from the project root directory\n";
    echo "- Composer dependencies are installed (run: composer install)\n";
    echo "- .env file is configured properly\n";
    exit(1);
}

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Cleanup Test Data from Database\n";
echo "========================================\n\n";

echo "Connected to database.\n\n";
echo "WARNING: This will delete all orders, products, and test data!\n";
echo "Users, admins, categories, and patterns will be preserved.\n";
echo "Continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "Cancelled.\n";
    exit(0);
}
fclose($handle);

echo "\nStarting cleanup...\n\n";

try {
    echo "[1/12] Deleting custom orders...\n";
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
    $users = DB::table('users')->count();
    $admins = DB::table('admins')->count();
    $categories = DB::table('categories')->count();
    $products = DB::table('products')->count();
    $orders = DB::table('orders')->count();
    $customOrders = DB::table('custom_orders')->count();

    echo "Users: $users\n";
    echo "Admins: $admins\n";
    echo "Categories: $categories\n";
    echo "Products: $products\n";
    echo "Orders: $orders\n";
    echo "Custom Orders: $customOrders\n";
    echo "\n✓ All done! Your database is clean and GitHub-ready.\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
