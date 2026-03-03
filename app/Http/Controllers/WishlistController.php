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
        // Force JSON response for validation errors
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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
        // Force JSON response for validation errors
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Log request details for debugging
        \Log::info('Wishlist remove request', [
            'type' => $request->type,
            'id' => $request->id,
            'wantsJson' => $request->wantsJson(),
            'ajax' => $request->ajax(),
            'accept_header' => $request->header('Accept'),
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'expects_json' => $request->expectsJson(),
            'authenticated' => Auth::check(),
            'user_id' => Auth::id()
        ]);

        $user = Auth::user();
        
        if (!$user) {
            \Log::warning('Wishlist remove: No authenticated user');
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            \Log::warning('Wishlist remove: Wishlist not found for user', ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => 'Wishlist not found.'], 404);
        }

        $item = null;
        if ($request->type === 'product') {
            $item = Product::findOrFail($request->id);
        } elseif ($request->type === 'pattern') {
            $item = YakanPattern::findOrFail($request->id);
        }

        if ($item && $wishlist->hasItem($item)) {
            $wishlist->removeItem($item);
            
            \Log::info('Wishlist remove: Item removed successfully', [
                'type' => $request->type,
                'id' => $request->id,
                'returning_json' => true
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Removed from wishlist!'
            ])->header('Content-Type', 'application/json');
        }

        \Log::warning('Wishlist remove: Item not in wishlist', [
            'type' => $request->type,
            'id' => $request->id
        ]);
        
        return response()->json([
            'success' => false, 
            'message' => 'Item not in wishlist'
        ], 404)->header('Content-Type', 'application/json');
    }

    public function check(Request $request)
    {
        // Force JSON response for validation errors
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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
