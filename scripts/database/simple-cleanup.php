<?php
/**
 * Simple Database Cleanup Script
 * Removes test data while preserving users
 */

echo "========================================\n";
echo "Cleanup Test Data from Database\n";
echo "========================================\n\n";

// Connect to SQLite database
$db = new PDO('sqlite:database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $db->exec("DELETE FROM custom_orders");
    echo "✓ Deleted custom orders\n";

    echo "[2/12] Deleting order items...\n";
    $db->exec("DELETE FROM order_items");
    echo "✓ Deleted order items\n";

    echo "[3/12] Deleting orders...\n";
    $db->exec("DELETE FROM orders");
    echo "✓ Deleted orders\n";

    echo "[4/12] Deleting cart items...\n";
    $db->exec("DELETE FROM carts");
    echo "✓ Deleted carts\n";

    echo "[5/12] Deleting products...\n";
    $db->exec("DELETE FROM products");
    echo "✓ Deleted products\n";

    echo "[6/12] Deleting inventory...\n";
    $db->exec("DELETE FROM inventory");
    echo "✓ Deleted inventory\n";

    echo "[7/12] Deleting reviews...\n";
    $db->exec("DELETE FROM reviews");
    echo "✓ Deleted reviews\n";

    echo "[8/12] Deleting notifications...\n";
    $db->exec("DELETE FROM notifications");
    echo "✓ Deleted notifications\n";

    echo "[9/12] Deleting coupon redemptions...\n";
    $db->exec("DELETE FROM coupon_redemptions");
    echo "✓ Deleted coupon redemptions\n";

    echo "[10/12] Deleting coupons...\n";
    $db->exec("DELETE FROM coupons");
    echo "✓ Deleted coupons\n";

    echo "[11/12] Deleting wishlist items...\n";
    $db->exec("DELETE FROM wishlist_items");
    echo "✓ Deleted wishlist items\n";

    echo "[12/12] Deleting contact messages...\n";
    $db->exec("DELETE FROM contact_messages");
    echo "✓ Deleted contact messages\n";

    echo "\n========================================\n";
    echo "✓ Database Cleanup Complete!\n";
    echo "========================================\n\n";

    echo "Remaining data:\n";
    $users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $admins = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $customOrders = $db->query("SELECT COUNT(*) FROM custom_orders")->fetchColumn();

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
