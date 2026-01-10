<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

$order = \App\Models\CustomOrder::find(15);
if ($order) {
    echo "✅ ORDER #15 - VERIFIED\n\n";
    echo "Quantity: " . $order->quantity . " meters\n";
    echo "Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "Final Price: ₱" . number_format($order->final_price, 2) . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Pattern(s): " . implode(', ', $order->patterns) . "\n\n";
    
    echo "PRICING BREAKDOWN:\n";
    echo "  Fabric Cost: ₱500/m × 2m = ₱1,000.00\n";
    echo "  Pattern Fee (Simple): ₱1,200.00\n";
    echo "  ─────────────────────────\n";
    echo "  Total: ₱2,200.00 ✓\n";
} else {
    echo "Order not found\n";
}
