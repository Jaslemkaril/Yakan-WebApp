<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Correcting Order #12 Price ===\n\n";

$order = \App\Models\CustomOrder::find(12);

if ($order) {
    echo "Before:\n";
    echo "  Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "  Final Price: ₱" . number_format($order->final_price, 2) . "\n\n";
    
    // Calculate correct price
    $pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 500);
    $patternFeeSimple = \App\Models\SystemSetting::get('pattern_fee_simple', 0);
    $patternFeeMedium = \App\Models\SystemSetting::get('pattern_fee_medium', 0);
    $patternFeeComplex = \App\Models\SystemSetting::get('pattern_fee_complex', 0);
    
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
    
    // Update the order
    $order->update([
        'estimated_price' => $correctPrice,
        'final_price' => $correctPrice
    ]);
    
    echo "After:\n";
    echo "  Estimated Price: ₱" . number_format($correctPrice, 2) . "\n";
    echo "  Final Price: ₱" . number_format($correctPrice, 2) . "\n\n";
    
    echo "Breakdown:\n";
    echo "  Pattern Fees: ₱" . number_format($totalPatternFee, 2) . "\n";
    echo "  Fabric Cost: ₱" . number_format($fabricCost, 2) . "\n";
    echo "  Shipping: ₱" . number_format($shippingFee, 2) . "\n";
    echo "  ---\n";
    echo "  Total: ₱" . number_format($correctPrice, 2) . "\n\n";
    
    echo "✅ Order #12 price corrected!\n";
} else {
    echo "Order not found\n";
}
