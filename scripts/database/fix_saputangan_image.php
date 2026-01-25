<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$product = Product::find(1);
$allImages = $product->all_images;

if (!empty($allImages) && isset($allImages[0]['path'])) {
    $product->image = $allImages[0]['path'];
    $product->save();
    echo "✓ Updated Saputangan main image to: " . $allImages[0]['path'] . "\n";
} else {
    echo "✗ No images found in all_images\n";
}

echo "\nCurrent state:\n";
echo "  image: " . $product->image . "\n";
echo "  all_images: " . json_encode($product->all_images, JSON_PRETTY_PRINT) . "\n";
