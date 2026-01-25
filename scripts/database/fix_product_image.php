<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix product ID 9 (test product)
$product = App\Models\Product::find(9);

if ($product) {
    echo "Fixing product: {$product->name}\n";
    echo "Old image path: {$product->image}\n";
    
    // Extract just the filename
    $filename = basename($product->image);
    $product->image = $filename;
    $product->save();
    
    echo "New image path: {$product->image}\n";
    echo "File exists: " . (file_exists(public_path('uploads/products/' . $filename)) ? 'YES' : 'NO') . "\n";
} else {
    echo "Product not found\n";
}
