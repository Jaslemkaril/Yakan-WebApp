<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\CustomOrder::find(1);

if (!$order) {
    echo "Order not found\n";
    exit(1);
}

echo "Order ID: " . $order->id . "\n";
echo "Patterns field: ";
print_r($order->patterns);
echo "\n";

echo "Design Metadata: ";
print_r($order->design_metadata);
echo "\n";

if (!empty($order->design_metadata) && isset($order->design_metadata['pattern_id'])) {
    $patternId = $order->design_metadata['pattern_id'];
    echo "Pattern ID from metadata: " . $patternId . "\n";
    
    $pattern = \App\Models\YakanPattern::find($patternId);
    if ($pattern) {
        echo "Pattern found: " . $pattern->name . "\n";
        echo "Has SVG: " . ($pattern->hasSvg() ? 'YES' : 'NO') . "\n";
    } else {
        echo "Pattern NOT found in database\n";
    }
}
