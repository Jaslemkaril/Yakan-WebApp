<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$product = Product::find(1);
echo "Product ID 1 (Saputangan):\n";
echo "  image: " . $product->image . "\n";
echo "  all_images: " . json_encode($product->all_images, JSON_PRETTY_PRINT) . "\n";
