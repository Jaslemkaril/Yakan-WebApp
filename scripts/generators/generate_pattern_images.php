<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\YakanPattern;
use App\Models\PatternMedia;

echo "=== Generating Pattern Images from SVG Data ===\n\n";

$patterns = YakanPattern::whereNotNull('pattern_data')->get();

foreach ($patterns as $pattern) {
    // Check if pattern already has media
    if ($pattern->media->count() > 0) {
        echo "Pattern '{$pattern->name}' already has media. Skipping...\n";
        continue;
    }
    
    // Check if pattern has SVG data
    $patternData = $pattern->pattern_data;
    if (!isset($patternData['svg']) || empty($patternData['svg'])) {
        echo "Pattern '{$pattern->name}' has no SVG data. Skipping...\n";
        continue;
    }
    
    $svg = $patternData['svg'];
    
    // Create filename
    $filename = \Illuminate\Support\Str::slug($pattern->name) . '.svg';
    $directory = 'patterns';
    $fullDirectory = public_path('uploads/' . $directory);
    
    // Create directory if it doesn't exist
    if (!file_exists($fullDirectory)) {
        mkdir($fullDirectory, 0755, true);
    }
    
    $filepath = $fullDirectory . '/' . $filename;
    $relativePath = $directory . '/' . $filename;
    
    // Save SVG file
    file_put_contents($filepath, $svg);
    
    // Create media record
    PatternMedia::create([
        'yakan_pattern_id' => $pattern->id,
        'type' => 'image',
        'path' => $relativePath,
        'alt_text' => $pattern->name . ' pattern',
        'sort_order' => 0,
    ]);
    
    echo "✓ Created media for '{$pattern->name}' - {$relativePath}\n";
}

echo "\n=== Done! ===\n";
