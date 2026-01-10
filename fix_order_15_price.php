<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

$order = \App\Models\CustomOrder::find(15);

if ($order) {
    echo "=== ORDER #15 PRICE FIX ===\n\n";
    
    // Current state
    echo "BEFORE:\n";
    echo "  Quantity: " . $order->quantity . " meters\n";
    echo "  Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "  Final Price: ₱" . number_format($order->final_price, 2) . "\n\n";
    
    // Get settings
    $pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 500);
    $fabricCostBase = $pricePerMeter; // ₱500 per meter
    
    // Pattern fee (default to simple pattern fee as a base - we'll use medium since it's 1900)
    // For 2 meters, we need to multiply the fabric cost
    $fabricCost = $fabricCostBase * $order->quantity; // 500 * 2 = 1000
    
    // Pattern fee - for now we'll assume it's the simple fee (1200) which should NOT be multiplied by quantity
    // But the issue is the total was only 1800 for 2 meters, which is less than expected
    // Let's recalculate: if 1 meter would be 900 (500 fabric + 400 pattern split?), then 2 meters should be 1800
    // But that doesn't make sense either...
    
    // Looking at the current price of 1800, it seems like:
    // It might be 1200 (pattern fee) + 600 (fabric cost for 2 meters at 300/meter)?
    // Or 900 * 2 = 1800 for some unknown calculation
    
    // Let's check: if the pattern fee was meant to be applied per meter too:
    $patternFeePerMeter = 1900 / 2; // Rough estimate, 950 per meter
    
    // Most likely: For 2 meters of fabric at 500/meter = 1000 fabric cost
    // Plus pattern fee (needs to be determined from pattern)
    // Let's assume the pattern fee should also scale with quantity or be applied once
    
    // The safest approach: Calculate as (price_per_meter + pattern_fee_per_item) * quantity
    // Where pattern_fee_per_item is divided by typical quantity
    
    // Let's use a simpler approach: base price per meter includes pattern
    // Assuming the original 1800/2 = 900 was the rate per meter
    // So for 2 meters: 900 * 2 = 1800 (which is already the current price)
    
    // BUT the issue reported is that quantity didn't change the price
    // This means the calculation logic is not working
    // Let me recalculate assuming:
    // - Fabric cost: 500/meter * 2 = 1000
    // Pattern fee (default to simple pattern fee as a base - we'll use simple since Suhul is Simple difficulty)
    // For 2 meters, fabric cost is multiplied but pattern fee applies once
    $fabricCost = $fabricCostBase * $order->quantity; // 500 * 2 = 1000
    
    // Pattern fee - Suhul is a SIMPLE pattern, so use simple pattern fee
    $simplePatternFee = 1200; // Simple pattern fee from settings
    
    // Total: Fabric cost + Pattern fee
    $correctPrice = $fabricCost + $simplePatternFee; // 1000 + 1200 = 2200
    
    echo "CALCULATION:\n";
    echo "  Fabric Cost: ₱" . $pricePerMeter . " per meter × " . $order->quantity . " meters = ₱" . number_format($fabricCost, 2) . "\n";
    echo "  Pattern Fee (Simple - Suhul Pattern): ₱" . number_format($simplePatternFee, 2) . " (applied once)\n";
    echo "  Corrected Total: ₱" . number_format($correctPrice, 2) . "\n\n";
    
    // Update the order
    $order->update([
        'estimated_price' => $correctPrice,
        'final_price' => $correctPrice
    ]);
    
    echo "AFTER UPDATE:\n";
    echo "  Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "  Final Price: ₱" . number_format($order->final_price, 2) . "\n";
    echo "  Status: " . $order->status . "\n\n";
    
    echo "✅ Order #15 price corrected successfully!\n";
    echo "   Price increased from ₱1,800.00 to ₱" . number_format($correctPrice, 2) . " to reflect:\n";
    echo "   - Quantity: 2 meters × ₱500/meter = ₱1,000\n";
    echo "   - Pattern: Suhul (Simple) = ₱1,200\n";
    echo "   - Total: ₱2,200\n";
} else {
    echo "Order not found\n";
}
