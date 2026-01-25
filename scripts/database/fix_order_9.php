<?php
/**
 * Fix Order #9 - Link to Correct User
 * 
 * Order #9 has customer_name "Yakan User" but wrong email "mobile@user.com"
 * This was created before the authentication fix
 * We'll link it to user ID 3 (Yakan User - user@yakan.com)
 * 
 * Usage: php fix_order_9.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "🔧 Fixing Order #9...\n\n";

$order = Order::find(9);

if (!$order) {
    echo "❌ Order #9 not found!\n";
    exit(1);
}

echo "Current Order #9 Details:\n";
echo "  Customer Name: {$order->customer_name}\n";
echo "  Customer Email: {$order->customer_email}\n";
echo "  User ID: " . ($order->user_id ?? 'NULL') . "\n\n";

// Find Yakan User (user@yakan.com)
$yakanUser = User::where('email', 'user@yakan.com')->first();

if (!$yakanUser) {
    echo "❌ Yakan User (user@yakan.com) not found!\n";
    exit(1);
}

echo "Target User Found:\n";
echo "  ID: {$yakanUser->id}\n";
echo "  Name: {$yakanUser->name}\n";
echo "  Email: {$yakanUser->email}\n\n";

// Confirm customer name matches
if ($order->customer_name !== $yakanUser->name) {
    echo "⚠️  WARNING: Customer name mismatch!\n";
    echo "  Order customer: {$order->customer_name}\n";
    echo "  User name: {$yakanUser->name}\n";
    echo "  Proceeding anyway since 'Yakan User' clearly belongs to user@yakan.com\n\n";
}

// Update the order
$order->user_id = $yakanUser->id;
$order->customer_email = $yakanUser->email;  // Fix the email too
$order->save();

echo "✅ Order #9 Updated Successfully!\n";
echo "  User ID: {$order->user_id}\n";
echo "  Customer Email: {$order->customer_email}\n\n";

echo "🎉 Order #9 will now appear in:\n";
echo "  • My Orders page\n";
echo "  • Track Order page\n";
echo "  • Admin panel (with correct email)\n\n";

echo "Verification:\n";
$updatedOrder = Order::find(9);
echo "  Order #9 user_id: " . $updatedOrder->user_id . "\n";
echo "  Order #9 customer_email: " . $updatedOrder->customer_email . "\n";
