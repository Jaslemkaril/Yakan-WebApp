<?php
// Fix existing patterns - create media records for SVG files
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\YakanPattern;

$patterns = YakanPattern::whereNotNull('svg_path')
    ->whereDoesntHave('media', function($q) {
        $q->where('type', 'svg');
    })
    ->get();

echo "Found " . $patterns->count() . " patterns without SVG media records\n\n";

foreach ($patterns as $pattern) {
    echo "Processing: {$pattern->name}\n";
    echo "  SVG Path: {$pattern->svg_path}\n";
    
    // Create media record for SVG
    $media = $pattern->media()->create([
        'type' => 'svg',
        'path' => 'patterns/svg/' . $pattern->svg_path,
        'alt_text' => $pattern->name . ' pattern',
        'sort_order' => 0,
    ]);
    
    echo "  ✓ Media record created (ID: {$media->id})\n";
    echo "  URL will be: " . url('uploads/' . $media->path) . "\n\n";
}

echo "\nDone! All patterns now have media records.\n";
