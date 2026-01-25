<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

$settings = [
    'price_per_meter' => \App\Models\SystemSetting::get('price_per_meter', 500),
    'pattern_fee_simple' => \App\Models\SystemSetting::get('pattern_fee_simple', 0),
    'pattern_fee_medium' => \App\Models\SystemSetting::get('pattern_fee_medium', 0),
    'pattern_fee_complex' => \App\Models\SystemSetting::get('pattern_fee_complex', 0),
];
echo "System Settings:\n";
foreach ($settings as $key => $value) {
    echo "$key = $value\n";
}

// Now check pattern 4
$pattern = \App\Models\Pattern::find(4);
if ($pattern) {
    echo "\n\nPattern #4 Details:\n";
    echo "Name: " . $pattern->name . "\n";
    echo "Difficulty: " . $pattern->difficulty_level . "\n";
} else {
    echo "\n\nPattern #4 not found\n";
}
