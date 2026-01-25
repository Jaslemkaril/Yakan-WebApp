<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING ALL CUSTOM ORDERS FOR QUANTITY PRICE MISMATCH ===\n\n";

$orders = \App\Models\CustomOrder::all();
$issues = [];

foreach ($orders as $order) {
    if ($order->quantity > 1 && $order->status !== 'cancelled' && $order->status !== 'rejected') {
        // Check if price looks too low for the quantity
        // Expected minimum: (500 * quantity) for fabric alone
        $expectedMinimum = 500 * $order->quantity;
        
        if ($order->final_price < $expectedMinimum) {
            $issues[] = [
                'id' => $order->id,
                'quantity' => $order->quantity,
                'current_price' => $order->final_price,
                'expected_minimum' => $expectedMinimum,
                'status' => $order->status
            ];
        }
    }
}

if (count($issues) > 0) {
    echo "Found " . count($issues) . " orders with potential pricing issues:\n\n";
    foreach ($issues as $issue) {
        echo "Order #" . $issue['id'] . ":\n";
        echo "  Quantity: " . $issue['quantity'] . " meters\n";
        echo "  Current Price: ₱" . number_format($issue['current_price'], 2) . "\n";
        echo "  Expected Minimum: ₱" . number_format($issue['expected_minimum'], 2) . "\n";
        echo "  Status: " . $issue['status'] . "\n";
        echo "  Difference: -₱" . number_format($issue['expected_minimum'] - $issue['current_price'], 2) . "\n\n";
    }
} else {
    echo "✅ No other orders with quantity-based pricing issues found!\n";
}
