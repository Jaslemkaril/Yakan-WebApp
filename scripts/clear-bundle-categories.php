<?php

/**
 * Clear category_id from existing bundles
 * Run: php scripts/clear-bundle-categories.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "Clearing categories from existing bundles...\n";

$bundles = Product::where('is_bundle', 1)
    ->whereNotNull('category_id')
    ->get();

$count = $bundles->count();

if ($count === 0) {
    echo "No bundles with categories found.\n";
    exit(0);
}

echo "Found {$count} bundle(s) with categories.\n";

foreach ($bundles as $bundle) {
    echo "- {$bundle->name} (ID: {$bundle->id}) - Removing category\n";
    $bundle->update(['category_id' => null]);
}

echo "\nDone! Cleared categories from {$count} bundle(s).\n";
