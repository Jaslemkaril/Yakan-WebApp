<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\CustomOrder::find(1);

echo "Order #1:\n";
echo "estimated_price: ₱" . number_format($order->estimated_price, 2) . "\n";
echo "final_price: ₱" . number_format($order->final_price, 2) . "\n";
echo "fabric_quantity: " . $order->fabric_quantity_meters . "m\n";
echo "fabric_type: " . $order->fabric_type . "\n";

// Calculate expected price
$basePrice = 1300; // Base product price
$fabricMeters = 2;
$fabricCost = $fabricMeters * 500; // ₱500 per meter
$patternFee = 200; // ₱200 for 1 pattern
$expectedTotal = $basePrice + $fabricCost + $patternFee;

echo "\nExpected calculation:\n";
echo "Base: ₱" . number_format($basePrice, 2) . "\n";
echo "Fabric (2m × ₱500): ₱" . number_format($fabricCost, 2) . "\n";
echo "Pattern fee: ₱" . number_format($patternFee, 2) . "\n";
echo "Expected total: ₱" . number_format($expectedTotal, 2) . "\n";
