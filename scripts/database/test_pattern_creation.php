<?php

require 'bootstrap/app.php';
$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\YakanPattern;

try {
    $pattern = YakanPattern::create([
        'name' => 'Test Pattern Fix',
        'description' => 'Testing the base_price_multiplier default',
        'category' => 'traditional',
        'difficulty_level' => 'simple',
        'production_days' => 5,
        'base_color' => 'blue',
        'svg_path' => 'test-fix.svg',
        'is_active' => 1
    ]);

    echo "✓ Pattern created successfully!\n";
    echo "  Pattern ID: " . $pattern->id . "\n";
    echo "  Base Price Multiplier: " . $pattern->base_price_multiplier . "\n";
    
    // Clean up test data
    $pattern->delete();
    echo "✓ Test pattern cleaned up\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
