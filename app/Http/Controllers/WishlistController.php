<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Models\YakanPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Note: index() uses 'auth' middleware on route for proper login redirect
    // AJAX methods (add, remove, check) rely on TokenAuth middleware
    
    public function index()
    {
        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first() ?: $user->wishlists()->create(['name' => 'My Wishlist', 'is_default' => true]);
        $wishlist->load(['items.item', 'items.item.category']);

        return view('wishlist.index', compact('wishlist'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $wishlist = $user->wishlists()->default()->first() ?: $user->wishlists()->create(['name' => 'My Wishlist', 'is_default' => true]);

        $item = null;
        if ($request->type === 'product') {
            $item = Product::findOrFail($request->id);
        } elseif ($request->type === 'pattern') {
            $item = YakanPattern::findOrFail($request->id);
        }

        if ($item && !$wishlist->hasItem($item)) {
            $wishlist->addItem($item);
            return response()->json(['success' => true, 'message' => 'Added to wishlist!']);
        }

        return response()->json(['success' => false, 'message' => 'Item already in wishlist']);
    }

    public function remove(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:product,pattern',
                'id' => 'required|integer',
            ]);

            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }
            
            $wishlist = $user->wishlists()->default()->first();

            if (!$wishlist) {
                return response()->json(['success' => false, 'message' => 'Wishlist not found.'], 404);
            }

            $item = null;
            if ($request->type === 'product') {
                $item = Product::find($request->id);
            } elseif ($request->type === 'pattern') {
                $item = YakanPattern::find($request->id);
            }

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            if ($wishlist->hasItem($item)) {
                $wishlist->removeItem($item);
                
                \Log::info('Wishlist item removed successfully', [
                    'user_id' => $user->id,
                    'type' => $request->type,
                    'id' => $request->id
                ]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Removed from wishlist!',
                    'remaining_count' => $wishlist->items()->count()
                ]);
            }
            
            return response()->json([
                'success' => false, 
                'message' => 'Item not in wishlist'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Wishlist remove error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the item'
            ], 500);
        }
    }

    public function check(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['in_wishlist' => false]);
        }
        
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            return response()->json(['in_wishlist' => false]);
        }

        $item = null;
        if ($request->type === 'product') {
            $item = Product::find($request->id);
        } elseif ($request->type === 'pattern') {
            $item = YakanPattern::find($request->id);
        }

        $inWishlist = $item ? $wishlist->hasItem($item) : false;

        return response()->json(['in_wishlist' => $inWishlist]);
    }
}
