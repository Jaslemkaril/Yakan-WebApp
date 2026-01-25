<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\YakanPattern;

$patterns = YakanPattern::all();

foreach ($patterns as $pattern) {
    // Generate a simple colorful SVG for each pattern
    $colors = [
        'Sinaluan' => ['#8B0000', '#FFD700', '#4B0082'],
        'Bunga Sama' => ['#FF6347', '#FFD700', '#228B22'],
        'Pinalantikan' => ['#4B0082', '#FFD700', '#8B0000'],
        'Suhul' => ['#4682B4', '#00CED1', '#FFD700'],
        'Kabkaban' => ['#8B4513', '#D2691E', '#DEB887'],
        'Laggi' => ['#FFD700', '#8B0000', '#FF8C00'],
        'Bennig' => ['#FF69B4', '#4B0082', '#FFD700'],
        'Pangapun' => ['#228B22', '#8B4513', '#FFD700'],
        'Sarang Kayu' => ['#654321', '#D2691E', '#8B0000'],
        'Ikan Mas' => ['#FF8C00', '#FFD700', '#4682B4'],
        'Kalasag' => ['#8B0000', '#FFD700', '#4B0082'],
    ];
    
    $patternColors = $colors[$pattern->name] ?? ['#8B0000', '#FFD700', '#4B0082'];
    
    $svg = '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">';
    $svg .= '<rect width="200" height="200" fill="' . $patternColors[0] . '"/>';
    $svg .= '<circle cx="100" cy="100" r="60" fill="' . $patternColors[1] . '" opacity="0.8"/>';
    $svg .= '<polygon points="100,60 130,100 100,140 70,100" fill="' . $patternColors[2] . '" opacity="0.9"/>';
    $svg .= '<circle cx="100" cy="100" r="20" fill="white" opacity="0.7"/>';
    $svg .= '<text x="100" y="110" text-anchor="middle" font-size="14" fill="white" font-weight="bold">' . substr($pattern->name, 0, 1) . '</text>';
    $svg .= '</svg>';
    
    $pattern->pattern_data = $svg;
    $pattern->save();
    
    echo "Updated: {$pattern->name}\n";
}

echo "\nAll patterns updated with SVG images!\n";
