<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pattern = \App\Models\YakanPattern::where('name', 'LIKE', '%zigzag%')->first();

if ($pattern) {
    // Create media record for zigzag
    $media = $pattern->media()->create([
        'path' => 'patterns/svg/zigzag-1765352331.svg',
        'type' => 'image',
        'mime_type' => 'image/svg+xml',
        'alt_text' => 'zigzag pattern',
        'sort_order' => 1
    ]);
    
    echo "Media created for zigzag pattern!\n";
    echo "ID: {$media->id}\n";
    echo "Path: {$media->path}\n";
    echo "URL: {$media->url}\n";
} else {
    echo "Pattern not found\n";
}
