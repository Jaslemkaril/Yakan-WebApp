<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Inventory;

echo "Syncing product stock with inventory...\n\n";

$products = Product::all();

foreach ($products as $product) {
    $inventory = Inventory::where('product_id', $product->id)->first();
    
    if ($inventory) {
        // Sync product stock with inventory quantity
        $product->stock = $inventory->quantity;
        $product->save();
        echo "Product #{$product->id} ({$product->name}): Stock updated to {$inventory->quantity}\n";
    } else {
        // Create inventory record if it doesn't exist
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => $product->stock,
            'low_stock_threshold' => 10,
        ]);
        echo "Product #{$product->id} ({$product->name}): Created inventory with stock {$product->stock}\n";
    }
}

echo "\nSync complete!\n";
