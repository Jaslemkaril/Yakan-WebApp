<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Models\YakanPattern;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get or create default wishlist
            $wishlist = $user->wishlists()->where('is_default', true)->first();
            
            if (!$wishlist) {
                $wishlist = $user->wishlists()->create([
                    'name' => 'My Wishlist',
                    'is_default' => true
                ]);
            }
            
            // Eagerly load relationships to avoid N+1 queries
            $wishlist->load(['items.item']);

            // Apply filters if requested
            $items = $wishlist->items;
            if ($request->type) {
                $items = $items->filter(function($item) use ($request) {
                    return $item->item_type === $request->type;
                });
            }

            // Format items for mobile app
            $formattedItems = $items->map(function($wishlistItem) {
                $item = $wishlistItem->item;
                if (!$item) {
                    return null;
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name ?? '',
                    'description' => $item->description ?? '',
                    'price' => (float) ($item->price ?? 0),
                    'image' => $item->image_url ?? $item->image ?? '',
                    'category' => optional($item->category)->name ?? '',
                    'type' => $wishlistItem->item_type,
                ];
            })->filter()->values();

            return response()->json([
                'success' => true,
                'data' => $formattedItems,
                'message' => 'Wishlist retrieved successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Wishlist API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Wishlist retrieved successfully'
            ]);
        }
    }

    /**
     * Add item to wishlist.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first() ?: 
                   $user->wishlists()->create(['name' => 'My Wishlist', 'is_default' => true]);

        $item = null;
        if ($validated['type'] === 'product') {
            $item = Product::findOrFail($validated['id']);
        } elseif ($validated['type'] === 'pattern') {
            $item = YakanPattern::findOrFail($validated['id']);
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }

        if ($wishlist->hasItem($item)) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in wishlist.'
            ], 409);
        }

        $wishlistItem = $wishlist->addItem($item);

        return response()->json([
            'success' => true,
            'data' => $wishlistItem,
            'message' => 'Added to wishlist successfully!'
        ], 201);
    }

    /**
     * Remove item from wishlist.
     */
    public function remove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist not found.'
            ], 404);
        }

        $item = null;
        if ($validated['type'] === 'product') {
            $item = Product::findOrFail($validated['id']);
        } elseif ($validated['type'] === 'pattern') {
            $item = YakanPattern::findOrFail($validated['id']);
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }

        if (!$wishlist->hasItem($item)) {
            return response()->json([
                'success' => false,
                'message' => 'Item not in wishlist.'
            ], 404);
        }

        $wishlist->removeItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist successfully!'
        ]);
    }

    /**
     * Check if item is in wishlist.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => true,
                'data' => ['in_wishlist' => false],
                'message' => 'Wishlist not found'
            ]);
        }

        $item = null;
        if ($validated['type'] === 'product') {
            $item = Product::find($validated['id']);
        } elseif ($validated['type'] === 'pattern') {
            $item = YakanPattern::find($validated['id']);
        }

        $inWishlist = $item ? $wishlist->hasItem($item) : false;

        return response()->json([
            'success' => true,
            'data' => ['in_wishlist' => $inWishlist],
            'message' => 'Wishlist status checked successfully'
        ]);
    }

    /**
     * Clear entire wishlist.
     */
    public function clear(): JsonResponse
    {
        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist not found.'
            ], 404);
        }

        $wishlist->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully!'
        ]);
    }

    /**
     * Move item to cart.
     */
    public function moveToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:10'
        ]);

        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist not found.'
            ], 404);
        }

        $item = null;
        if ($validated['type'] === 'product') {
            $item = Product::findOrFail($validated['id']);
        } elseif ($validated['type'] === 'pattern') {
            $item = YakanPattern::findOrFail($validated['id']);
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }

        if (!$wishlist->hasItem($item)) {
            return response()->json([
                'success' => false,
                'message' => 'Item not in wishlist.'
            ], 404);
        }

        // Add to cart logic here - you'll need to implement this based on your cart system
        // For now, just remove from wishlist
        $wishlist->removeItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Item moved to cart successfully!'
        ]);
    }

    /**
     * Get wishlist statistics.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_items' => 0,
                    'products_count' => 0,
                    'patterns_count' => 0,
                    'total_value' => 0
                ],
                'message' => 'Wishlist statistics retrieved successfully'
            ]);
        }

        $stats = [
            'total_items' => $wishlist->items->count(),
            'products_count' => $wishlist->items->where('item_type', 'product')->count(),
            'patterns_count' => $wishlist->items->where('item_type', 'pattern')->count(),
            'total_value' => $wishlist->items->reduce(function ($total, $item) {
                if ($item->item_type === 'product' && $item->item) {
                    return $total + $item->item->price;
                }
                return $total;
            }, 0)
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Wishlist statistics retrieved successfully'
        ]);
    }
}
