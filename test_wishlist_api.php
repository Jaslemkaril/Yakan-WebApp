<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;

echo "=== Wishlist API Test ===\n\n";

// Find first user
$user = User::where('email', 'admin@example.com')->first();
if (!$user) {
    echo "No user found with email admin@example.com\n";
    $user = User::first();
    if (!$user) {
        echo "No users in database!\n";
        exit;
    }
}

echo "User: {$user->name} ({$user->email})\n";

// Get or create wishlist
$wishlist = $user->wishlists()->where('is_default', true)->first();
if (!$wishlist) {
    echo "No default wishlist found, creating one...\n";
    $wishlist = $user->wishlists()->create([
        'name' => 'My Wishlist',
        'is_default' => true
    ]);
}

echo "Wishlist ID: {$wishlist->id}\n";
echo "Wishlist Items: " . $wishlist->items()->count() . "\n\n";

// Show wishlist items
$wishlist->load(['items.item', 'items.item.category']);

if ($wishlist->items->isEmpty()) {
    echo "No items in wishlist!\n";
    
    // Add a sample product if available
    $product = Product::first();
    if ($product) {
        echo "Adding sample product: {$product->name}\n";
        $wishlist->addItem($product);
        echo "Product added!\n\n";
        
        // Reload wishlist
        $wishlist->load(['items.item', 'items.item.category']);
    }
}

echo "Wishlist Items:\n";
foreach ($wishlist->items as $wishlistItem) {
    $item = $wishlistItem->item;
    echo "  - {$item->name} (₱{$item->price})\n";
    echo "    Type: {$wishlistItem->item_type}\n";
    echo "    Image: " . ($item->image_url ?? $item->image ?? 'N/A') . "\n\n";
}

// Format as API response
echo "\n=== API Response Format ===\n";
$formattedItems = $wishlist->items->map(function($wishlistItem) {
    $item = $wishlistItem->item;
    return [
        'id' => $item->id,
        'name' => $item->name,
        'description' => $item->description ?? '',
        'price' => (float) $item->price,
        'image' => $item->image_url ?? $item->image ?? '',
        'category' => $item->category->name ?? '',
        'type' => $wishlistItem->item_type,
    ];
})->values();

echo json_encode([
    'success' => true,
    'data' => $formattedItems,
    'message' => 'Wishlist retrieved successfully'
], JSON_PRETTY_PRINT);
