<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$products = App\Models\Product::whereNull('sku')->orWhere('sku', '')->get();

echo "Updating " . $products->count() . " products with SKUs...\n\n";

foreach ($products as $product) {
    $product->sku = 'YKN-' . strtoupper(substr(uniqid(), -8));
    $product->save();
    
    echo "Product ID {$product->id} ({$product->name}): {$product->sku}\n";
}

echo "\nDone!\n";
