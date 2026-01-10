<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = \App\Models\CustomOrder::find(12);

echo "=== Order #12 - Detailed Inspection ===\n\n";
echo "ID: " . $order->id . "\n";
echo "Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
echo "Final Price: ₱" . number_format($order->final_price, 2) . "\n\n";

// Check customization settings
if ($order->customization_settings) {
    echo "Customization Settings:\n";
    echo var_export($order->customization_settings, true) . "\n\n";
}

// Check design metadata
if ($order->design_metadata) {
    echo "Design Metadata:\n";
    echo var_export($order->design_metadata, true) . "\n\n";
}

// Check if there are any related order items or line items
$orderItems = \DB::table('order_items')->where('order_id', $order->id)->get();
echo "Order Items Count: " . count($orderItems) . "\n";
if (count($orderItems) > 0) {
    echo "Order Items:\n";
    foreach ($orderItems as $item) {
        echo "  - " . $item->product_id . ": ₱" . $item->price . "\n";
    }
}
