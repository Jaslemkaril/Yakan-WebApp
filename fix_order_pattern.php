<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the Suhul pattern
$pattern = \App\Models\YakanPattern::where('name', 'Suhul')->first();

if (!$pattern) {
    echo "Suhul pattern not found!\n";
    exit(1);
}

echo "Found pattern: {$pattern->name} (ID: {$pattern->id})\n";

// Update order #1
$order = \App\Models\CustomOrder::find(1);
if (!$order) {
    echo "Order #1 not found!\n";
    exit(1);
}

// Update the design_metadata with pattern_id
$metadata = $order->design_metadata ?? [];
$metadata['pattern_id'] = $pattern->id;
$metadata['pattern_name'] = $pattern->name;

$order->design_metadata = $metadata;
$order->patterns = [$pattern->id]; // Store pattern ID in patterns array
$order->save();

echo "✓ Order #1 updated successfully!\n";
echo "Pattern ID: {$pattern->id}\n";
echo "Pattern Name: {$pattern->name}\n";
echo "Metadata: " . json_encode($order->design_metadata) . "\n";
echo "Patterns array: " . json_encode($order->patterns) . "\n";
