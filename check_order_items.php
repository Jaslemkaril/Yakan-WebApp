<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$order = Order::with('items.product')->find(9);

echo "Order #9 Items:\n\n";

foreach ($order->items as $item) {
    echo "Item: {$item->product_name}\n";
    echo "  - product_id: {$item->product_id}\n";
    echo "  - product_name: {$item->product_name}\n";
    echo "  - product_image: " . ($item->product_image ?? 'NULL') . "\n";
    
    if ($item->product) {
        echo "  - product->image: " . ($item->product->image ?? 'NULL') . "\n";
        echo "  - product->image_url: " . ($item->product->image_url ?? 'NULL') . "\n";
    } else {
        echo "  - product: NULL (not found)\n";
    }
    echo "\n";
}
