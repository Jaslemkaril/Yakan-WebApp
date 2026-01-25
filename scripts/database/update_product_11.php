<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$product = App\Models\Product::find(11);
if ($product) {
    $product->image = '1765365395_1_cap.jpg';
    $product->save();
    echo "✓ Updated Yakan Cap (ID: 11) with image: 1765365395_1_cap.jpg\n";
    echo "Image path: uploads/products/1765365395_1_cap.jpg\n";
} else {
    echo "✗ Product not found\n";
}
