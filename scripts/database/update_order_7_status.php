<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

try {
    $order = Order::find(7);
    
    if ($order) {
        echo "Order #7 found\n";
        echo "Current status: {$order->status}\n";
        echo "Delivered at: " . ($order->delivered_at ?? 'NULL') . "\n\n";
        
        // Update status to completed
        $order->update([
            'status' => 'completed'
        ]);
        
        echo "Updated successfully!\n";
        echo "New status: {$order->status}\n";
    } else {
        echo "Order #7 not found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
