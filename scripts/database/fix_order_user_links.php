<?php
/**
 * Fix Order User Links
 * 
 * This script updates orders with NULL user_id by matching customer_email with users table
 * Run this to retroactively link orders to users
 * 
 * Usage: php fix_order_user_links.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "🔍 Finding orders with NULL user_id but valid customer_email...\n\n";

$ordersWithoutUser = Order::whereNull('user_id')
    ->whereNotNull('customer_email')
    ->where('customer_email', '!=', 'mobile@user.com')
    ->get();

echo "Found {$ordersWithoutUser->count()} orders to process\n\n";

$updated = 0;
$skipped = 0;

foreach ($ordersWithoutUser as $order) {
    // Try to find matching user by email
    $user = User::where('email', $order->customer_email)->first();
    
    if ($user) {
        $order->user_id = $user->id;
        $order->save();
        
        echo "✅ Order #{$order->id} linked to User #{$user->id} ({$user->name} - {$user->email})\n";
        $updated++;
    } else {
        echo "⚠️  Order #{$order->id} - No matching user found for {$order->customer_email}\n";
        $skipped++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Summary:\n";
echo "  ✅ Updated: {$updated} orders\n";
echo "  ⚠️  Skipped: {$skipped} orders (no matching user)\n";
echo str_repeat("=", 60) . "\n";
