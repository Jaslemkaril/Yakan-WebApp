<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

$order = \App\Models\CustomOrder::find(20);

if ($order) {
    echo "=== ORDER #20 - DETAILS ===\n\n";
    echo "Order ID: #" . $order->id . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Payment Status: " . $order->payment_status . "\n";
    echo "Quantity: " . $order->quantity . " units\n";
    echo "Price: ₱" . number_format($order->final_price, 2) . "\n\n";
    
    echo "DELIVERY INFORMATION:\n";
    echo "Delivery Type: " . $order->delivery_type . "\n";
    echo "Delivery Address: " . ($order->delivery_address ?: 'NOT SET') . "\n";
    echo "Phone: " . ($order->phone ?: 'NOT SET') . "\n";
    echo "Email: " . ($order->email ?: 'NOT SET') . "\n\n";
    
    echo "ORDER ITEMS:\n";
    echo "Specifications: " . $order->specifications . "\n";
    if ($order->patterns) {
        echo "Patterns: " . implode(', ', $order->patterns) . "\n";
    }
} else {
    echo "Order not found\n";
}
