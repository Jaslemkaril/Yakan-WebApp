<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function getPriceMeta(Product $product, ?ProductVariant $variant = null): array
    {
        if ($variant) {
            $basePrice = (float) $variant->price;
        } else {
            $activeVariants = $this->getActiveVariants($product);
            $basePrice = $activeVariants->isNotEmpty()
                ? (float) $activeVariants->min('price')
                : (float) $product->price;
        }

        $basePrice = max(0, $basePrice);
        $effectivePrice = (float) $product->getDiscountedPrice($basePrice);
        $discountAmount = (float) $product->getDiscountAmount($basePrice);

        return [
            'price' => $effectivePrice,
            'original_price' => round($basePrice, 2),
            'discount_amount' => $discountAmount,
            'has_product_discount' => $discountAmount > 0,
        ];
    }

    private function getActiveVariants(Product $product)
    {
        if ($product->relationLoaded('variants')) {
            return $product->variants->where('is_active', true)->values();
        }

        return $product->variants()->where('is_active', true)->get();
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if (!$variantId) {
            return null;
        }

        return ProductVariant::where('id', $variantId)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->first();
    }

    private function getEffectiveStock(Product $product, ?ProductVariant $variant = null): int
    {
        if ($variant) {
            return (int) $variant->stock;
        }

        $activeVariants = $this->getActiveVariants($product);
        if ($activeVariants->isNotEmpty()) {
            return (int) $activeVariants->sum('stock');
        }

        return (int) ($product->inventory?->quantity ?? $product->stock ?? 0);
    }

    private function getEffectivePrice(Product $product, ?ProductVariant $variant = null): float
    {
        return $this->getPriceMeta($product, $variant)['price'];
    }

    /**
     * Remove coupon (no-op for stateless API — client just discards)
     */
    public function removeCoupon()
    {
        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    /**
     * Return all active coupons the current user can apply.
     */
    public function getAvailableCoupons(Request $request)
    {
        $user = $request->user();

        $coupons = Coupon::where('active', true)
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
            ->where(function ($q) { $q->whereNull('usage_limit')->orWhereColumn('times_redeemed', '<', 'usage_limit'); })
            ->get()
            ->filter(fn($c) => $c->canBeUsedBy($user))
            ->map(fn($c) => [
                'code'        => $c->code,
                'type'        => $c->type,
                'value'       => (float) $c->value,
                'min_spend'   => (float) ($c->min_spend ?? 0),
                'description' => $c->type === 'percent'
                    ? (int)$c->value . '% off shipping'
                    : '₱' . number_format($c->value, 2) . ' off shipping',
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $coupons]);
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
            $cartItems = Cart::with('product.variants', 'variant')->where('user_id', Auth::id())->get();
            $subtotal  = $cartItems->sum(function ($item) {
                if (!$item->product) {
                    return 0;
                }

                return $this->getEffectivePrice($item->product, $item->variant) * $item->quantity;
            });
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
        $cartItems = Cart::with('product.inventory', 'product.variants', 'variant')
                        ->where('user_id', Auth::id())
                        ->get()
                        ->map(function ($item) {
                            $variant = $item->variant;
                            $effectiveStock = $this->getEffectiveStock($item->product, $variant);
                            $priceMeta = $this->getPriceMeta($item->product, $variant);
                            $variantPriceMeta = $variant ? $this->getPriceMeta($item->product, $variant) : null;

                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'variant_id' => $variant?->id,
                                'quantity' => $item->quantity,
                                'variant' => $variant ? [
                                    'id' => $variant->id,
                                    'size' => $variant->size,
                                    'color' => $variant->color,
                                    'price' => $variantPriceMeta['price'],
                                    'original_price' => $variantPriceMeta['original_price'],
                                    'discount_amount' => $variantPriceMeta['discount_amount'],
                                    'has_product_discount' => $variantPriceMeta['has_product_discount'],
                                    'stock' => (int) $variant->stock,
                                ] : null,
                                'product' => [
                                    'id' => $item->product->id,
                                    'name' => $item->product->name,
                                    'price' => $priceMeta['price'],
                                    'original_price' => $priceMeta['original_price'],
                                    'discount_amount' => $priceMeta['discount_amount'],
                                    'has_product_discount' => $priceMeta['has_product_discount'],
                                    'image' => $item->product->image,
                                    'stock' => $effectiveStock,
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
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;
        $variantId = $request->input('variant_id');
        $quantity = $request->quantity;

        // Check if product exists and has stock
        $product = Product::with('inventory', 'variants')->findOrFail($productId);
        $hasVariants = $this->getActiveVariants($product)->isNotEmpty();
        $variant = $this->resolveVariant($product, $variantId ? (int) $variantId : null);

        if (!empty($variantId) && !$variant) {
            return response()->json([
                'success' => false,
                'message' => 'The selected variant does not belong to this product.'
            ], 422);
        }

        if ($hasVariants && !$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid product variant before adding to cart.'
            ], 422);
        }

        if (!$hasVariants) {
            $variantId = null;
        }

        $availableStock = $this->getEffectiveStock($product, $variant);

        if ($availableStock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        // Check if item already in cart
        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->where('variant_id', $variantId)
                        ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($availableStock < $newQuantity) {
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
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        // Return the updated cart item
        $cartItem->load('product.inventory', 'product.variants', 'variant');
        $priceMeta = $this->getPriceMeta($cartItem->product, $cartItem->variant);
        $variantPriceMeta = $cartItem->variant ? $this->getPriceMeta($cartItem->product, $cartItem->variant) : null;
        $effectiveStock = $this->getEffectiveStock($cartItem->product, $cartItem->variant);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'variant_id' => $cartItem->variant?->id,
                'quantity' => $cartItem->quantity,
                'variant' => $cartItem->variant ? [
                    'id' => $cartItem->variant->id,
                    'size' => $cartItem->variant->size,
                    'color' => $cartItem->variant->color,
                    'price' => $variantPriceMeta['price'],
                    'original_price' => $variantPriceMeta['original_price'],
                    'discount_amount' => $variantPriceMeta['discount_amount'],
                    'has_product_discount' => $variantPriceMeta['has_product_discount'],
                    'stock' => (int) $cartItem->variant->stock,
                ] : null,
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'price' => $priceMeta['price'],
                    'original_price' => $priceMeta['original_price'],
                    'discount_amount' => $priceMeta['discount_amount'],
                    'has_product_discount' => $priceMeta['has_product_discount'],
                    'image' => $cartItem->product->image,
                    'stock' => $effectiveStock,
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

        $cartItem = Cart::with('product.inventory', 'product.variants', 'variant')
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
        $variant = $cartItem->variant;
        $availableStock = $this->getEffectiveStock($product, $variant);
        if ($availableStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $priceMeta = $this->getPriceMeta($product, $variant);
        $variantPriceMeta = $variant ? $this->getPriceMeta($product, $variant) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'variant_id' => $variant?->id,
                'quantity' => $cartItem->quantity,
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'price' => $variantPriceMeta['price'],
                    'original_price' => $variantPriceMeta['original_price'],
                    'discount_amount' => $variantPriceMeta['discount_amount'],
                    'has_product_discount' => $variantPriceMeta['has_product_discount'],
                    'stock' => (int) $variant->stock,
                ] : null,
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'price' => $priceMeta['price'],
                    'original_price' => $priceMeta['original_price'],
                    'discount_amount' => $priceMeta['discount_amount'],
                    'has_product_discount' => $priceMeta['has_product_discount'],
                    'image' => $cartItem->product->image,
                    'stock' => $this->getEffectiveStock($cartItem->product, $variant),
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
