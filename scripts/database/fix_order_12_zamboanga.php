<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Recalculating Order #12 Price with Correct Shipping ===\n\n";

$order = \App\Models\CustomOrder::find(12);

$pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 500);
$patternFeeSimple = \App\Models\SystemSetting::get('pattern_fee_simple', 0);

// Get patterns
$patternIds = $order->patterns;
if (is_string($patternIds)) {
    $patternIds = json_decode($patternIds, true) ?? [];
}

$patterns = \App\Models\YakanPattern::whereIn('id', (array)$patternIds)->get();

$totalPatternFee = 0;
foreach ($patterns as $pattern) {
    $difficulty = $pattern->difficulty_level ?? 'simple';
    $totalPatternFee += $patternFeeSimple;
}

// Calculate fabric cost
$fabricCost = $order->fabric_quantity_meters * $pricePerMeter;

// Shipping fee - Zamboanga City is FREE
$shippingFee = 0;

$correctPrice = $totalPatternFee + $fabricCost + $shippingFee;

echo "Breakdown:\n";
echo "  Pattern Fees: ₱" . number_format($totalPatternFee, 2) . "\n";
echo "  Fabric Cost (2m × ₱$pricePerMeter): ₱" . number_format($fabricCost, 2) . "\n";
echo "  Shipping (Zamboanga City): ₱" . number_format($shippingFee, 2) . " (FREE)\n";
echo "  ---\n";
echo "  Correct Total: ₱" . number_format($correctPrice, 2) . "\n\n";

echo "Before: ₱" . number_format($order->final_price, 2) . "\n";

// Update the order
$order->update([
    'estimated_price' => $correctPrice,
    'final_price' => $correctPrice
]);

echo "After: ₱" . number_format($correctPrice, 2) . "\n\n";
echo "✅ Order #12 price updated with FREE Zamboanga City shipping!\n";
