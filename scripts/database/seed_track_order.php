<?php

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Create a sample user
$user = \App\Models\User::firstOrCreate(
    ['email' => 'customer@example.com'],
    [
        'name' => 'John Doe', 
        'phone' => '09123456789',
        'password' => bcrypt('password')
    ]
);

// Create a sample product if it doesn't exist
$product = \App\Models\Product::firstOrCreate(
    ['name' => 'Sample Product'],
    [
        'price' => 1000, 
        'stock' => 10,
        'description' => 'Sample product for testing'
    ]
);

// Create a sample order
$order = \App\Models\Order::create([
    'user_id' => $user->id,
    'total_amount' => 1000,
    'payment_status' => 'paid',
    'order_status' => 'processing',
    'tracking_number' => 'YAKAN-2026-001',
    'tracking_status' => 'processing',
    'courier_name' => 'JNT',
    'courier_contact' => '1800-555-5555'
]);

// Create order item
\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'quantity' => 1,
    'price' => 1000
]);

echo "✓ Sample order created: {$order->tracking_number}\n";
echo "✓ User: {$user->email}\n";
echo "✓ Product: {$product->name}\n";
