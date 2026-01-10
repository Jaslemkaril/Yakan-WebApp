<?php
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all fabric orders with invalid specifications
$orders = \App\Models\CustomOrder::whereNotNull('fabric_type')
    ->whereNotNull('fabric_quantity_meters')
    ->get();

echo "Found " . count($orders) . " fabric orders\n";

$updated = 0;
foreach ($orders as $order) {
    // Check if specifications contains the ID instead of name
    if (strpos($order->specifications, 'Fabric Type: 1') !== false || 
        strpos($order->specifications, 'Intended Use: 1') !== false) {
        
        // Get fabric type name
        $fabricTypeName = 'N/A';
        if ($order->fabric_type) {
            $fabricType = \App\Models\FabricType::find($order->fabric_type);
            $fabricTypeName = $fabricType ? $fabricType->name : $order->fabric_type;
        }
        
        // Get intended use name
        $intendedUseName = 'N/A';
        if ($order->intended_use) {
            $intendedUse = \App\Models\IntendedUse::find($order->intended_use);
            $intendedUseName = $intendedUse ? $intendedUse->name : $order->intended_use;
        }
        
        // Regenerate specifications
        $specifications = "Custom Fabric Order\n";
        $specifications .= "Fabric Type: " . $fabricTypeName . "\n";
        $specifications .= "Quantity: " . ($order->fabric_quantity_meters ?? 0) . " meters\n";
        $specifications .= "Intended Use: " . $intendedUseName;
        
        $order->update(['specifications' => $specifications]);
        $updated++;
        
        echo "Updated Order #" . $order->id . " - Fabric: $fabricTypeName, Use: $intendedUseName\n";
    }
}

echo "\nTotal updated: $updated orders\n";
