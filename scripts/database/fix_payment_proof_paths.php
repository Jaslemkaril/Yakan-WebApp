<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "Fixing payment_proof_path URLs...\n\n";

$orders = Order::whereNotNull('payment_proof_path')->get();

foreach ($orders as $order) {
    $originalPath = $order->payment_proof_path;
    
    // Check if it's a full URL (contains http://)
    if (strpos($originalPath, 'http://') !== false || strpos($originalPath, 'https://') !== false) {
        // Extract just the path after /storage/
        if (preg_match('#/storage/(.+)$#', $originalPath, $matches)) {
            $relativePath = $matches[1];
            $order->payment_proof_path = $relativePath;
            $order->save();
            
            echo "✓ Order #{$order->id}:\n";
            echo "  FROM: {$originalPath}\n";
            echo "  TO:   {$relativePath}\n\n";
        } else {
            echo "⚠ Order #{$order->id}: Could not extract path from: {$originalPath}\n\n";
        }
    } else {
        echo "→ Order #{$order->id}: Already relative path: {$originalPath}\n\n";
    }
}

echo "Done!\n";
