<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing All Order Prices ===\n\n";

$orders = \App\Models\CustomOrder::all();

$pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 500);
$patternFeeSimple = \App\Models\SystemSetting::get('pattern_fee_simple', 0);
$patternFeeMedium = \App\Models\SystemSetting::get('pattern_fee_medium', 0);
$patternFeeComplex = \App\Models\SystemSetting::get('pattern_fee_complex', 0);

$fixed = 0;

foreach ($orders as $order) {
    // Skip if no patterns or fabric
    if (!$order->patterns || !$order->fabric_quantity_meters) {
        continue;
    }
    
    // Get patterns
    $patternIds = $order->patterns;
    if (is_string($patternIds)) {
        $patternIds = json_decode($patternIds, true) ?? [];
    }
    
    $patterns = \App\Models\YakanPattern::whereIn('id', (array)$patternIds)->get();
    
    $totalPatternFee = 0;
    foreach ($patterns as $pattern) {
        $difficulty = $pattern->difficulty_level ?? 'simple';
        if ($difficulty === 'complex') {
            $fee = $patternFeeComplex;
        } elseif ($difficulty === 'medium') {
            $fee = $patternFeeMedium;
        } else {
            $fee = $patternFeeSimple;
        }
        $totalPatternFee += $fee;
    }
    
    // Calculate fabric cost
    $fabricCost = $order->fabric_quantity_meters * $pricePerMeter;
    
    // Calculate shipping
    $shippingFee = 0;
    if ($order->delivery_address && strpos(strtolower($order->delivery_address), 'zamboanga') !== false) {
        $shippingFee = 0;
    } else {
        $shippingFee = 100;
    }
    
    $correctPrice = $totalPatternFee + $fabricCost + $shippingFee;
    
    // Update if price is different
    if (abs($correctPrice - $order->final_price) > 0.01) {
        $oldPrice = $order->final_price;
        $order->update([
            'estimated_price' => $correctPrice,
            'final_price' => $correctPrice
        ]);
        $fixed++;
        echo "Order #{$order->id}: ₱" . number_format($oldPrice, 2) . " → ₱" . number_format($correctPrice, 2) . "\n";
    }
}

echo "\n✅ Fixed $fixed orders!\n";
