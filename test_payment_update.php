<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomOrder;

$order = CustomOrder::find(2);
if ($order) {
    echo "Before update:\n";
    echo "- status: " . $order->status . "\n";
    echo "- payment_status: " . $order->payment_status . "\n\n";
    
    // Simulate the backend update
    $order->payment_status = 'paid';
    $order->status = 'approved';
    $saved = $order->save();
    
    echo "Save result: " . ($saved ? 'SUCCESS' : 'FAILED') . "\n\n";
    
    // Refresh from database
    $order = $order->fresh();
    echo "After update:\n";
    echo "- status: " . $order->status . "\n";
    echo "- payment_status: " . $order->payment_status . "\n";
} else {
    echo "Order not found\n";
}
