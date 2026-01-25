<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

$order = \App\Models\CustomOrder::find(20);

if ($order) {
    echo "=== ORDER #20 - FIXING UNIT PRICE ===\n\n";
    
    echo "BEFORE:\n";
    echo "  Units Ordered: " . $order->quantity . "\n";
    echo "  Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "  Final Price: ₱" . number_format($order->final_price, 2) . "\n\n";
    
    // Correct calculation:
    // Per unit (2 meters):
    // - Fabric: 2m × ₱500 = ₱1,000
    // - Pattern (Laggi/Complex): ₱2,500
    // = ₱3,500 per unit
    // × 2 units = ₱7,000
    
    $fabricCostPerUnit = 1000; // 2 meters × 500
    $patternFeePerUnit = 2500; // Laggi = Complex
    $unitPrice = $fabricCostPerUnit + $patternFeePerUnit; // ₱3,500
    $correctPrice = $unitPrice * $order->quantity; // ₱7,000
    
    $order->update([
        'estimated_price' => $correctPrice,
        'final_price' => $correctPrice
    ]);
    
    echo "CALCULATION:\n";
    echo "  Per Unit (2 meters):\n";
    echo "    - Fabric: 2m × ₱500 = ₱" . number_format($fabricCostPerUnit, 2) . "\n";
    echo "    - Pattern (Laggi-Complex): ₱" . number_format($patternFeePerUnit, 2) . "\n";
    echo "    - Unit Total: ₱" . number_format($unitPrice, 2) . "\n";
    echo "  × 2 Units = ₱" . number_format($correctPrice, 2) . "\n\n";
    
    echo "AFTER:\n";
    echo "  Units Ordered: " . $order->quantity . "\n";
    echo "  Estimated Price: ₱" . number_format($order->estimated_price, 2) . "\n";
    echo "  Final Price: ₱" . number_format($order->final_price, 2) . "\n\n";
    
    echo "✅ Order #20 unit price corrected!\n";
    echo "   Changed from ₱7,200.00 to ₱7,000.00\n";
} else {
    echo "Order not found\n";
}
