<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\CustomOrder::find(20);

if ($order) {
    echo "=== ORDER #20 - ADDING DELIVERY INFORMATION ===\n\n";
    
    echo "BEFORE:\n";
    echo "  Delivery Type: " . $order->delivery_type . "\n";
    echo "  Delivery Address: " . ($order->delivery_address ?: "NOT SET") . "\n";
    echo "  Phone: " . ($order->phone ?: "NOT SET") . "\n";
    echo "  Email: " . ($order->email ?: "NOT SET") . "\n\n";
    
    // Update with delivery information - ACTUAL USER SAVED ADDRESS
    $order->update([
        'delivery_address' => 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur, 7000',
        'phone' => '09656923753',
        'email' => $order->user->email ?? 'user@yakan.com'
    ]);
    
    echo "AFTER:\n";
    echo "  Delivery Type: " . $order->delivery_type . "\n";
    echo "  Delivery Address: " . $order->delivery_address . "\n";
    echo "  Phone: " . $order->phone . "\n";
    echo "  Email: " . $order->email . "\n\n";
    
    echo "✅ Order #20 delivery information added successfully!\n";
} else {
    echo "Order not found\n";
}
