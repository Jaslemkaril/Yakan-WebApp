<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Orders 13 and 14 ===\n\n";

$orders = \App\Models\CustomOrder::whereIn('id', [13, 14])->get();

foreach ($orders as $order) {
    echo "Order #" . $order->id . ":\n";
    echo "  Before: ₱" . number_format($order->final_price, 2) . "\n";
    
    $order->update([
        'estimated_price' => 1900,
        'final_price' => 1900
    ]);
    
    echo "  After: ₱1,900.00\n\n";
}

echo "✅ Done!\n";
