<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = \App\Models\CustomOrder::find(12);
$user = $order->user;

if ($user) {
    // Get default address
    $defaultAddress = $user->addresses->where('is_default', true)->first();
    
    if ($defaultAddress) {
        echo "=== Updating Order #12 with Default Address ===\n\n";
        echo "Address: " . $defaultAddress->formatted_address . "\n\n";
        
        // Update the order
        $order->update([
            'delivery_address' => $defaultAddress->formatted_address
        ]);
        
        echo "✅ Order #12 delivery address updated!\n";
        echo "\nNew Address: " . $order->delivery_address . "\n";
    } else {
        echo "No default address found for user\n";
    }
} else {
    echo "Order or user not found\n";
}
