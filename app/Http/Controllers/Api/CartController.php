<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Remove coupon (no-op for stateless API — client just discards)
     */
    public function removeCoupon()
    {
        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    /**
     * Validate a coupon code and return the discount amount.
     * Accepts optional subtotal directly (for buy-now or pre-calculated carts).
     */
    public function validateCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $code   = strtoupper(trim($request->input('code')));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Coupon not found.'], 422);
        }

        // Compute subtotal
        if ($request->filled('subtotal')) {
            $subtotal = (float) $request->input('subtotal');
        } else {
            $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
            $subtotal  = $cartItems->sum(fn($i) => $i->product->price * $i->quantity);
        }

        $now = now();
        if (!$coupon->active)                                       return response()->json(['success'=>false,'message'=>'This coupon is not active.'], 422);
        if ($coupon->starts_at && $now->lt($coupon->starts_at))    return response()->json(['success'=>false,'message'=>'This coupon is not yet active.'], 422);
        if ($coupon->ends_at   && $now->gt($coupon->ends_at))      return response()->json(['success'=>false,'message'=>'This coupon has expired.'], 422);
        if ($coupon->usage_limit && $coupon->times_redeemed >= $coupon->usage_limit)
                                                                    return response()->json(['success'=>false,'message'=>'Coupon usage limit reached.'], 422);
        if ($coupon->usage_limit_per_user) {
            $used = $coupon->redemptions()->where('user_id', Auth::id())->count();
            if ($used >= $coupon->usage_limit_per_user)             return response()->json(['success'=>false,'message'=>'You have already used this coupon.'], 422);
        }

        $discountAmount = $coupon->calculateDiscount((float) $subtotal);
        if ($discountAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon does not meet the minimum spend (₱' . number_format($coupon->min_spend, 2) . ').',
            ], 422);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Coupon applied!',
            'code'        => $code,
            'discount'    => $discountAmount,
            'description' => $coupon->description ?? ('₱' . number_format($discountAmount, 2) . ' off'),
            'type'        => $coupon->type ?? 'fixed',
        ]);
    }

    /**
     * Get user's cart items
     */
    public function index()
    {
        $cartItems = Cart::with('product')
                        ->where('user_id', Auth::id())
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'product' => [
                                    'id' => $item->product->id,
                                    'name' => $item->product->name,
                                    'price' => $item->product->price,
                                    'image' => $item->product->image,
                                    'stock' => $item->product->stock,
                                ],
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                            ];
                        });

        return response()->json([
            'success' => true,
            'data' => $cartItems
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Check if product exists and has stock
        $product = Product::findOrFail($productId);
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        // Check if item already in cart
        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for requested quantity'
                ], 400);
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        // Return the updated cart item
        $cartItem->load('product');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'price' => $cartItem->product->price,
                    'image' => $cartItem->product->image,
                ],
            ],
            'message' => 'Product added to cart successfully'
        ]);
    }

    /**
     * Add product to cart (alternative endpoint)
     */
    public function store(Request $request)
    {
        return $this->add($request);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::with('product')
                        ->where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $product = $cartItem->product;
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'price' => $cartItem->product->price,
                    'image' => $cartItem->product->image,
                ],
            ],
            'message' => 'Cart item updated successfully'
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    /**
     * Remove item from cart (destroy method for DELETE route)
     */
    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }
}
