<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Fabric Type IDs ===\n\n";

// Find all orders with string fabric_type instead of ID
$orders = \App\Models\CustomOrder::whereNotNull('fabric_type')
    ->where(function($q) {
        $q->where('fabric_type', 'cotton')
          ->orWhere('fabric_type', 'Cotton')
          ->orWhere('fabric_type', 'COTTON');
    })
    ->get();

echo "Found " . count($orders) . " orders with string 'cotton'\n";

// Get the ID for 'Cotton' fabric type
$cottonFabricType = \App\Models\FabricType::where('name', 'Cotton')->first();

if ($cottonFabricType) {
    $updated = 0;
    foreach ($orders as $order) {
        $order->update(['fabric_type' => $cottonFabricType->id]);
        $updated++;
        echo "Updated Order #{$order->id}: fabric_type changed to ID {$cottonFabricType->id}\n";
    }
    echo "\nTotal updated: $updated orders\n";
} else {
    echo "ERROR: Cotton fabric type not found in database!\n";
}
