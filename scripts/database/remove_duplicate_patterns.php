<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Removing duplicate Yakan patterns..." . PHP_EOL;
echo str_repeat('=', 80) . PHP_EOL;

// Get all patterns ordered by name and ID (keeping the first occurrence)
$patterns = App\Models\YakanPattern::orderBy('name')->orderBy('id')->get();

$seen = [];
$toDelete = [];

foreach ($patterns as $pattern) {
    if (isset($seen[$pattern->name])) {
        // This is a duplicate - mark for deletion
        $toDelete[] = $pattern->id;
        echo "  ❌ Marking for deletion: ID {$pattern->id} - {$pattern->name} (duplicate)" . PHP_EOL;
    } else {
        // First occurrence - keep it
        $seen[$pattern->name] = $pattern->id;
        echo "  ✓ Keeping: ID {$pattern->id} - {$pattern->name}" . PHP_EOL;
    }
}

echo PHP_EOL . str_repeat('=', 80) . PHP_EOL;
echo "Summary:" . PHP_EOL;
echo "  Total patterns: " . $patterns->count() . PHP_EOL;
echo "  Unique patterns: " . count($seen) . PHP_EOL;
echo "  Duplicates to delete: " . count($toDelete) . PHP_EOL;
echo str_repeat('=', 80) . PHP_EOL;

if (count($toDelete) > 0) {
    echo PHP_EOL . "Deleting duplicates..." . PHP_EOL;
    $deleted = App\Models\YakanPattern::whereIn('id', $toDelete)->delete();
    echo "  ✓ Deleted {$deleted} duplicate pattern(s)" . PHP_EOL;
    
    // Verify
    $remaining = App\Models\YakanPattern::count();
    echo "  ✓ Remaining patterns: {$remaining}" . PHP_EOL;
} else {
    echo PHP_EOL . "No duplicates found!" . PHP_EOL;
}

echo PHP_EOL . "Done!" . PHP_EOL;
