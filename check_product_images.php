<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$products = Product::all();

echo "=== Product Image Check ===\n\n";

foreach ($products as $product) {
    echo "Product: {$product->name} (ID: {$product->id})\n";
    echo "  Primary Image: " . ($product->image ? $product->image : "NULL") . "\n";
    echo "  All Images: " . ($product->all_images ? json_encode($product->all_images) : "NULL") . "\n";
    
    // Check if primary image file exists
    if ($product->image) {
        $path = public_path('storage/' . $product->image);
        $exists = file_exists($path) ? "✓ EXISTS" : "✗ MISSING";
        echo "  File Status: $exists ($path)\n";
    }
    
    // Check all_images
    if ($product->all_images && is_array($product->all_images)) {
        echo "  Image count: " . count($product->all_images) . "\n";
        foreach ($product->all_images as $img) {
            $path = public_path('storage/' . $img);
            $exists = file_exists($path) ? "✓" : "✗";
            echo "    - $exists $img\n";
        }
    }
    
    echo "\n";
}
