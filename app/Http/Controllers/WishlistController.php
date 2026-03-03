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
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
        $wishlist = $user->wishlists()->default()->first();

        if (!$wishlist) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Wishlist not found.']);
            }
            return redirect()->route('wishlist.index')->with('error', 'Wishlist not found.');
        }

        $item = null;
        if ($request->type === 'product') {
            $item = Product::findOrFail($request->id);
        } elseif ($request->type === 'pattern') {
            $item = YakanPattern::findOrFail($request->id);
        }

        if ($item && $wishlist->hasItem($item)) {
            $wishlist->removeItem($item);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Removed from wishlist!']);
            }
            return redirect()->route('wishlist.index')->with('success', 'Removed from wishlist!');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Item not in wishlist']);
        }
        return redirect()->route('wishlist.index')->with('error', 'Item not in wishlist');
    }

    public function check(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,pattern',
            'id' => 'required|integer',
        ]);

        $user = Auth::user();
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
