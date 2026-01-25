<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

echo "=== CHECKING ALL CUSTOM ORDERS FOR QUANTITY/PRICE ISSUES ===\n\n";

$orders = \App\Models\CustomOrder::where('quantity', '>', 1)
    ->where('status', '!=', 'rejected')
    ->orderBy('id', 'desc')
    ->get();

if ($orders->count() > 0) {
    echo "Found " . $orders->count() . " custom orders with quantity > 1 meter:\n\n";
    
    foreach ($orders as $order) {
        $quantity = $order->quantity;
        $estimatedPrice = $order->estimated_price;
        $patterns = $order->patterns ?? [];
        
        echo "Order #" . $order->id . ":\n";
        echo "  Quantity: " . $quantity . " meters\n";
        echo "  Price: ₱" . number_format($estimatedPrice, 2) . "\n";
        echo "  Patterns: " . implode(', ', $patterns) . "\n";
        echo "  Status: " . $order->status . "\n";
        
        // Check if price might not be quantity-adjusted
        if ($quantity > 1) {
            $pricePerMeter = $estimatedPrice / $quantity;
            echo "  Implied price/meter: ₱" . number_format($pricePerMeter, 2) . "\n";
        }
        echo "\n";
    }
} else {
    echo "No custom orders with quantity > 1 found.\n";
}
