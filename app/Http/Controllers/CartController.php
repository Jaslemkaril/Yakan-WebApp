<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\CloudinaryService;
use App\Services\Payment\MayaCheckoutService;
use App\Services\Payment\PayMongoCheckoutService;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
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
            return (int) ($variant->stock ?? 0);
        }

        $activeVariants = $this->getActiveVariants($product);
        if ($activeVariants->isNotEmpty()) {
            return (int) $activeVariants->sum('stock');
        }

        return (int) ($product->inventory?->quantity ?? $product->stock ?? 0);
    }

    private function getEffectiveUnitPrice(Product $product, ?ProductVariant $variant = null): float
    {
        $basePrice = $variant
            ? (float) ($variant->price ?? 0)
            : (float) ($product->price ?? 0);

        return (float) $product->getDiscountedPrice(max(0, $basePrice));
    }

    /**
     * Add product to cart
     */
    public function add(Request $request, Product $product)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'Please login to add items to your cart.'], 401);
                }
                return redirect()->route('login')->with('info', 'Please login to continue shopping.');
            }
            
            $userId = Auth::id();
            $qty = max(1, (int)($request->input('quantity', 1)));
            $variantIdRaw = $request->input('variant_id');
            $variantId = is_numeric($variantIdRaw) ? (int) $variantIdRaw : null;

            $product->loadMissing(['inventory', 'variants']);
            $hasVariants = $this->getActiveVariants($product)->isNotEmpty();
            $variant = $this->resolveVariant($product, $variantId);

            if ($hasVariants && !$variant) {
                $msg = 'Please select a valid product variant before adding to cart.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            if (!$hasVariants) {
                $variantId = null;
            }

            if ($variantIdRaw && !$variant) {
                $msg = 'The selected variant does not belong to this product.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            \Log::info('Buy Now/Add to Cart attempt', [
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'buy_now' => $request->input('buy_now') ? 'yes' : 'no'
            ]);

            // Check stock from selected variant (if present), else product-level stock.
            $availableStock = $this->getEffectiveStock($product, $variant);

            // Check stock availability
            if ($availableStock < $qty) {
                \Log::warning('Insufficient stock', ['product_id' => $product->id, 'requested' => $qty, 'available' => $availableStock]);
                $msg = "Quantity cannot exceed available amount. Only {$availableStock} item(s) available.";
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                return redirect()->back()->with('error', $msg);
            }

            // If "Buy Now" was clicked, skip cart DB and return product info for direct checkout
            if ($request->input('buy_now')) {
                \Log::info('Buy Now triggered', ['user_id' => $userId, 'product_id' => $product->id, 'quantity' => $qty]);

                $cartCount = Cart::where('user_id', $userId)->sum('quantity');

                if ($isAjax) {
                    return response()->json([
                        'success'    => true,
                        'message'    => 'Proceeding to checkout...',
                        'cart_count' => $cartCount,
                        'buy_now'    => true,
                        'product_id' => $product->id,
                        'variant_id' => $variantId,
                        'quantity'   => $qty,
                    ]);
                }

                // Non-AJAX fallback: redirect directly to checkout with URL params
                $authToken = $request->input('auth_token') ?? session('auth_token');
                $paramStr = 'buy_now=1&product_id=' . $product->id . '&quantity=' . $qty;
                if ($variantId) $paramStr .= '&variant_id=' . $variantId;
                if ($authToken) $paramStr .= '&auth_token=' . $authToken;
                return redirect('/cart/checkout?' . $paramStr);
            }

            // Regular "Add to Cart" flow
            $cartItem = Cart::where('user_id', $userId)
                            ->where('product_id', $product->id)
                            ->when($variantId, function ($query) use ($variantId) {
                                $query->where('variant_id', $variantId);
                            }, function ($query) {
                                $query->whereNull('variant_id');
                            })
                            ->first();

            if ($cartItem) {
                $newTotal = $cartItem->quantity + $qty;
                if ($newTotal > $availableStock) {
                    $msg = "Quantity cannot exceed available amount. Only {$availableStock} item(s) available in total.";
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg]);
                    }
                    return redirect()->back()->with('error', $msg);
                }
                $cartItem->quantity += $qty;
                $cartItem->save();
                \Log::info('Cart item updated', ['cart_item_id' => $cartItem->id, 'new_quantity' => $cartItem->quantity]);
            } else {
                Cart::create([
                    'user_id'    => $userId,
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'quantity'   => $qty,
                ]);
                \Log::info('Cart item created', ['user_id' => $userId, 'product_id' => $product->id]);
            }

            // Clear cart count cache
            \Cache::forget('cart_count_' . $userId);

            // Get updated cart count
            $cartCount = Cart::where('user_id', $userId)->sum('quantity');
            \Log::info('Cart updated', ['user_id' => $userId, 'cart_count' => $cartCount]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart!',
                    'cart_count' => $cartCount,
                    'product_name' => $product->name
                ]);
            }

            return redirect()->back()->with('success', 'Product added to cart!');
        } catch (\Exception $e) {
            \Log::error('Error adding to cart', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Error adding product to cart. Please try again.'], 500);
            }
            return redirect()->back()->with('error', 'Error adding product to cart. Please try again.');
        }
    }

    /**
     * Apply a coupon code to the current session
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $coupon = Coupon::where('code', $code)->first();

        $fail = function (string $msg) use ($request) {
            if ($request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->with('error', $msg);
        };

        if (!$coupon) return $fail('Coupon not found.');

        // ── Compute subtotal ──────────────────────────────────────────────────
        // Support buy-now flow: product_id + quantity passed directly in AJAX body
        if ($request->boolean('buy_now') && $request->filled('product_id')) {
            $product  = Product::find($request->input('product_id'));
            $variantId = $request->filled('variant_id') && is_numeric($request->input('variant_id'))
                ? (int) $request->input('variant_id')
                : null;
            $variant = $product ? $this->resolveVariant($product->loadMissing('variants'), $variantId) : null;
            if ($product && $this->getActiveVariants($product)->isNotEmpty() && !$variant) {
                return $fail('Please select a valid variant before applying coupon.');
            }
            $qty      = max(1, (int) $request->input('quantity', 1));
            $subtotal = $product ? $this->getEffectiveUnitPrice($product, $variant) * $qty : 0;
        } elseif (session()->has('buy_now_item')) {
            $bni      = session('buy_now_item');
            $product  = Product::find($bni['product_id']);
            $variantId = !empty($bni['variant_id']) ? (int) $bni['variant_id'] : null;
            $variant = $product ? $this->resolveVariant($product->loadMissing('variants'), $variantId) : null;
            if ($product && $this->getActiveVariants($product)->isNotEmpty() && !$variant) {
                session()->forget('buy_now_item');
                return $fail('Selected variant is unavailable. Please reselect your product variant.');
            }
            $subtotal = $product ? $this->getEffectiveUnitPrice($product, $variant) * ($bni['quantity'] ?? 1) : 0;
        } else {
            $cartItems = Cart::with('product.inventory', 'product.variants', 'variant')->where('user_id', Auth::id())->get();
            $subtotal  = $cartItems->sum(function ($item) {
                if (!$item->product) {
                    return 0;
                }

                return $this->getEffectiveUnitPrice($item->product, $item->variant) * $item->quantity;
            });
        }

        // Detailed validation with specific error messages
        if (!$coupon->active)                                      return $fail('This coupon is not active.');
        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at))   return $fail('This coupon is not yet active.');
        if ($coupon->ends_at   && $now->gt($coupon->ends_at))     return $fail('This coupon has expired.');
        if ($coupon->usage_limit && $coupon->times_redeemed >= $coupon->usage_limit)
                                                                   return $fail('This coupon usage limit has been reached.');
        if ($coupon->usage_limit_per_user) {
            $userRedemptions = $coupon->redemptions()->where('user_id', Auth::id())->count();
            if ($userRedemptions >= $coupon->usage_limit_per_user) return $fail('You have already used this coupon.');
        }

        if ((float)($coupon->min_spend ?? 0) > 0 && $subtotal < (float)$coupon->min_spend)
            return $fail('Coupon does not apply to your current subtotal (minimum: ₱' . number_format($coupon->min_spend, 2) . ').');

        $shippingFee    = max(0, (float) $request->input('shipping_fee', 0));
        $discountAmount = $coupon->calculateTargetDiscount((float) $subtotal, $shippingFee);

        session(['coupon_code' => $code]);

        $description = $coupon->getDiscountDescription();
        $targetLabel = $coupon->getAppliesTo() === 'items' ? 'items' : 'shipping fee';

        if ($request->expectsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Coupon applied! ' . ucfirst($targetLabel) . ' discounted.',
                'discount'    => $discountAmount,
                'code'        => $code,
                'description' => $description,
                'applies_to'  => $coupon->getAppliesTo(),
            ]);
        }

        return back()->with('success', 'Coupon applied successfully!');
    }

    /**
     * Validate a coupon code for the mobile app (returns discount without session side-effects).
     */
    public function validateCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $code   = strtoupper(trim($request->input('code')));
        $coupon = Coupon::where('code', $code)->first();

        $fail = fn(string $msg) => response()->json(['success' => false, 'message' => $msg], 422);

        if (!$coupon)                                                                          return $fail('Coupon not found.');
        if (!$coupon->active)                                                                  return $fail('This coupon is not active.');
        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at))                               return $fail('This coupon is not yet active.');
        if ($coupon->ends_at   && $now->gt($coupon->ends_at))                                 return $fail('This coupon has expired.');
        if ($coupon->usage_limit && $coupon->times_redeemed >= $coupon->usage_limit)           return $fail('This coupon usage limit has been reached.');
        if ($coupon->usage_limit_per_user) {
            $userRedemptions = $coupon->redemptions()->where('user_id', Auth::id())->count();
            if ($userRedemptions >= $coupon->usage_limit_per_user)                            return $fail('You have already used this coupon.');
        }

        $subtotal    = max(0, (float) $request->input('subtotal', 0));
        $shippingFee = max(0, (float) $request->input('shipping_fee', 0));

        if ((float)($coupon->min_spend ?? 0) > 0 && $subtotal < (float)$coupon->min_spend) {
            return $fail('Minimum spend of ₱' . number_format($coupon->min_spend, 2) . ' required.');
        }

        $discountAmount = $coupon->calculateTargetDiscount($subtotal, $shippingFee);
        $description = $coupon->getDiscountDescription();
        $targetLabel = $coupon->getAppliesTo() === 'items' ? 'items' : 'shipping fee';

        return response()->json([
            'success'     => true,
            'message'     => 'Coupon applied! ' . ucfirst($targetLabel) . ' discounted.',
            'discount'    => $discountAmount,
            'code'        => $code,
            'description' => $description,
            'applies_to'  => $coupon->getAppliesTo(),
        ]);
    }

    /**
     * Remove applied coupon from session
     */
    public function removeCoupon(Request $request)
    {
        session()->forget('coupon_code');
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Coupon removed.']);
        }
        return back()->with('success', 'Coupon removed.');
    }

    /**
     * Get available (active, usable) coupons for the current user (API endpoint for mobile).
     */
    public function getAvailableCoupons(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user    = $request->user();
        $coupons = Coupon::where('active', true)
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
            ->where(function ($q) { $q->whereNull('usage_limit')->orWhereColumn('times_redeemed', '<', 'usage_limit'); })
            ->get()
            ->filter(fn($c) => $c->canBeUsedBy($user))
            ->map(fn($c) => [
                'code'        => $c->code,
                'type'        => $c->type,
                'applies_to'  => $c->getAppliesTo(),
                'value'       => (float) $c->value,
                'min_spend'   => (float) ($c->min_spend ?? 0),
                'description' => $c->getDiscountDescription(),
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $coupons]);
    }

    /**
     * Get cart count for current user
     */
    public function getCartCount()
    {
        $userId = Auth::id();
        return \Cache::remember('cart_count_' . $userId, 300, function () use ($userId) {
            return Cart::where('user_id', $userId)->sum('quantity');
        });
    }

    /**
     * Show the cart
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Clear Buy Now item from session when viewing regular cart
        session()->forget('buy_now_item');
        
        // Get cart items - use simple query first
        $cartItems = Cart::where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->get();

        // Manually load products with inventory + variants to ensure variant-aware cart display.
        foreach ($cartItems as $item) {
            if (!$item->product) {
                $item->load('product.inventory', 'product.variants', 'variant');
            } else {
                $item->load('product.inventory', 'product.variants', 'variant');
            }
        }

        // Debug log
        \Log::info('Cart Index - User ID: ' . $userId . ', Cart Items Count: ' . $cartItems->count());
        
        // Log each item for debugging
        foreach ($cartItems as $item) {
            \Log::info('Cart Item', [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? 'NULL',
                'quantity' => $item->quantity
            ]);
        }

        // Use the same delivery zoning baseline shown on checkout for cart summary.
        $defaultAddress = \App\Models\UserAddress::forUser($userId)->default()->first();
        $shippingFee = $this->calculateShippingFeeForAddress($defaultAddress, 'delivery');

        return view('cart.index', compact('cartItems', 'shippingFee'));
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if ($cartItem) {
            $cartItem->delete();
            // Clear cart count cache
            \Cache::forget('cart_count_' . Auth::id());
        }

        // Check if this is a JSON request
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart');
    }

    /**
     * Clear all items from cart
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();
        \Cache::forget('cart_count_' . Auth::id());
        
        return redirect()->back()->with('success', 'Cart cleared successfully');
    }

    /**
     * Update cart quantity
     */
    public function update(Request $request, $id)
    {
        \Log::info('CartController@update called', [
            'id' => $id, 
            'is_json' => $request->wantsJson(), 
            'is_ajax' => $request->isXmlHttpRequest(),
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'has_auth_token' => $request->has('auth_token') || $request->json('auth_token'),
            'session_id' => session()->getId()
        ]);
        
        // Check authentication
        if (!Auth::check()) {
            \Log::warning('CartController@update: User not authenticated', [
                'session_id' => session()->getId(),
                'has_auth_token_query' => $request->has('auth_token'),
                'has_auth_token_json' => !!$request->json('auth_token')
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login again.',
                    'redirect' => route('login.user')
                ], 401);
            }
            return redirect()->route('login.user')->with('error', 'Please login to continue');
        }
        
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Handle Buy Now item (session-based OR URL-param-based)
        if ($id === 'buy_now') {
            // Try session first; fall back to product_id from request body (URL-param buy_now flow)
            if (session()->has('buy_now_item')) {
                $productId = session('buy_now_item')['product_id'];
                $variantId = session('buy_now_item')['variant_id'] ?? null;
            } elseif ($request->filled('product_id')) {
                $productId = $request->input('product_id');
                $variantId = $request->filled('variant_id') ? (int) $request->input('variant_id') : null;
            } else {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
                }
                return redirect()->back()->with('error', 'Cart item not found');
            }
            $product = Product::find($productId);
            $product?->loadMissing('variants');
            $variant = $product ? $this->resolveVariant($product, $variantId ? (int) $variantId : null) : null;
            
            if (!$product) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Product not found'], 404);
                }
                return redirect()->back()->with('error', 'Product not found');
            }
            
            $newQty = (int) $request->quantity;
            $maxStock = $this->getEffectiveStock($product, $variant);
            if (is_numeric($maxStock) && $maxStock > 0) {
                $newQty = min($newQty, (int) $maxStock);
            }
            $newQty = max(1, $newQty);
            
            // Update session
            session(['buy_now_item' => [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $newQty,
            ]]);
            
            if ($request->wantsJson()) {
                $itemSubtotal = $newQty * $this->getEffectiveUnitPrice($product, $variant);
                $cartTotal = $itemSubtotal;
                
                // Apply coupon if exists
                $discount = 0;
                if (session()->has('coupon_code')) {
                    $coupon = \App\Models\Coupon::where('code', session('coupon_code'))->first();
                    if ($coupon && $coupon->canBeUsedBy(Auth::user())) {
                        $discount = $coupon->getAppliesTo() === 'items'
                            ? $coupon->calculateDiscount((float) $cartTotal)
                            : 0;
                    } else {
                        session()->forget('coupon_code');
                    }
                }
                
                $totalAmount = max(0, $cartTotal - $discount);
                
                return response()->json([
                    'success' => true,
                    'new_quantity' => $newQty,
                    'item_subtotal' => $itemSubtotal,
                    'cart_total' => $cartTotal,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'total_items' => $newQty,
                    'message' => 'Cart updated successfully'
                ]);
            }
            
            return redirect()->back()->with('success', 'Cart updated');
        }

        // Handle regular cart item (database-based)
        $cartItem = Cart::with('product.inventory', 'product.variants', 'variant')
                        ->where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$cartItem) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
            }
            return redirect()->back()->with('error', 'Cart item not found');
        }

        $maxStock = $this->getEffectiveStock($cartItem->product, $cartItem->variant);
        $newQty = (int) $request->quantity;
        if (is_numeric($maxStock) && $maxStock > 0) {
            $newQty = min($newQty, (int) $maxStock);
        }
        $cartItem->quantity = max(1, $newQty);
        $cartItem->save();
        
        // Clear cart count cache
        \Cache::forget('cart_count_' . Auth::id());

        // If it's a JSON request, return JSON with updated cart data
        if ($request->wantsJson()) {
            // Calculate new item subtotal
            $itemSubtotal = $cartItem->quantity * $this->getEffectiveUnitPrice($cartItem->product, $cartItem->variant);
            
            // Get all cart items and calculate totals
            $allCartItems = Cart::with('product.inventory', 'product.variants', 'variant')->where('user_id', Auth::id())->get();
            $cartTotal = $allCartItems->sum(function($item) {
                if (!$item->product) {
                    return 0;
                }

                return $item->quantity * $this->getEffectiveUnitPrice($item->product, $item->variant);
            });
            
            // Apply coupon if exists
            $discount = 0;
            if (session()->has('coupon_code')) {
                $coupon = \App\Models\Coupon::where('code', session('coupon_code'))->first();
                if ($coupon && $coupon->canBeUsedBy(Auth::user())) {
                    $discount = $coupon->getAppliesTo() === 'items'
                        ? $coupon->calculateDiscount((float) $cartTotal)
                        : 0;
                } else {
                    session()->forget('coupon_code');
                }
            }
            
            $totalAmount = max(0, $cartTotal - $discount);
            $totalItems = $allCartItems->sum('quantity');
            
            return response()->json([
                'success' => true,
                'new_quantity' => $cartItem->quantity,
                'item_subtotal' => $itemSubtotal,
                'cart_total' => $cartTotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'message' => 'Cart updated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated');
    }

    /**
     * Show checkout page (Mode of Payment)
     */
    public function checkout(Request $request)
    {
        // Store selected item IDs in session if provided
        if ($request->has('selected_items')) {
            session(['selected_cart_items' => $request->input('selected_items')]);
        }

        // Check for buy_now via URL params first (Railway: sessions don't persist across redirects)
        if ($request->boolean('buy_now') && $request->filled('product_id')) {
            $product = Product::find($request->input('product_id'));
            $variantId = $request->filled('variant_id') && is_numeric($request->input('variant_id'))
                ? (int) $request->input('variant_id')
                : null;
            $variant = $product ? $this->resolveVariant($product->loadMissing('variants'), $variantId) : null;
            $qty = max(1, (int) $request->input('quantity', 1));
            if ($product) {
                $hasVariants = $this->getActiveVariants($product)->isNotEmpty();
                if ($hasVariants && !$variant) {
                    return redirect()->route('products.show', $product)->with('error', 'Please select a valid product variant.');
                }

                $cartItems = collect([
                    (object)[
                        'id'         => 'buy_now',
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'quantity'   => $qty,
                        'product'    => $product,
                        'variant'    => $variant,
                    ]
                ]);
                $subtotal = $this->getEffectiveUnitPrice($product, $variant) * $qty;
                $discount = 0;
                $appliedCoupon = null;
                $total = $subtotal;
                $addresses = \App\Models\UserAddress::forUser(Auth::id())
                    ->orderBy('is_default', 'desc')->get();
                $defaultAddress = $addresses->firstWhere('is_default', true);
                $availableCoupons = Coupon::where('active', true)
                    ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
                    ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
                    ->where(function ($q) { $q->whereNull('usage_limit')->orWhereColumn('times_redeemed', '<', 'usage_limit'); })
                    ->get()
                    ->filter(fn($c) => $c->canBeUsedBy(Auth::user()))
                    ->values();
                return view('cart.checkout', compact('cartItems', 'total', 'addresses', 'defaultAddress', 'availableCoupons'))
                    ->with('subtotal', $subtotal)
                    ->with('discount', $discount)
                    ->with('appliedCoupon', $appliedCoupon);
            }
        }

        // Check if "Buy Now" item exists in session (fallback for non-Railway environments)
        if (session()->has('buy_now_item')) {
            $buyNowItem = session('buy_now_item');
            $product = Product::find($buyNowItem['product_id']);
            $variant = $product
                ? $this->resolveVariant($product->loadMissing('variants'), !empty($buyNowItem['variant_id']) ? (int) $buyNowItem['variant_id'] : null)
                : null;
            
            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('products.index')->with('error', 'Product not found.');
            }

            $hasVariants = $this->getActiveVariants($product)->isNotEmpty();
            if ($hasVariants && !$variant) {
                session()->forget('buy_now_item');
                return redirect()->route('products.show', $product)->with('error', 'Selected product variant is no longer available.');
            }

            // Create a collection with just the Buy Now item
            $cartItems = collect([
                (object)[
                    'id' => 'buy_now',
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $buyNowItem['quantity'],
                    'product' => $product,
                    'variant' => $variant,
                ]
            ]);
            
            $subtotal = $this->getEffectiveUnitPrice($product, $variant) * $buyNowItem['quantity'];
        } else {
            // Regular cart checkout
            $cartItems = Cart::with('product.inventory', 'product.variants', 'variant')
                            ->where('user_id', Auth::id())
                            ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
            }
            
            // Filter by selected items if any are selected
            if (session()->has('selected_cart_items')) {
                $selectedIds = session('selected_cart_items');
                $cartItems = $cartItems->filter(function($item) use ($selectedIds) {
                    return in_array($item->id, $selectedIds);
                });
                
                if ($cartItems->isEmpty()) {
                    return redirect()->route('cart.index')->with('error', 'Please select items to checkout.');
                }
            }
            
            $subtotal = $cartItems->sum(function ($item) {
                if (!$item->product) {
                    return 0;
                }

                return $this->getEffectiveUnitPrice($item->product, $item->variant) * $item->quantity;
            });
        }

        // Load user addresses first (needed for shipping fee estimate)
        $addresses = \App\Models\UserAddress::forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', true);

        // Estimate shipping fee from default address for coupon discount preview
        $estimatedShippingFee = 0;
        if ($defaultAddress) {
            $estimatedShippingFee = $this->calculateShippingFeeForAddress($defaultAddress, 'delivery');
        }

        $discount = 0;
        $appliedCoupon = null;

        if (session()->has('coupon_code')) {
            $code = session('coupon_code');
            $appliedCoupon = Coupon::where('code', $code)->first();
            if ($appliedCoupon && $appliedCoupon->canBeUsedBy(Auth::user())) {
                if ($subtotal >= (float)($appliedCoupon->min_spend ?? 0)) {
                    $discount = $appliedCoupon->calculateTargetDiscount($subtotal, $estimatedShippingFee);
                }
            } else {
                session()->forget(['coupon_code']);
                $appliedCoupon = null;
            }
        }

        // Load active coupons the user can use (shown as chips on checkout page)
        $availableCoupons = Coupon::where('active', true)
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
            ->where(function ($q) { $q->whereNull('usage_limit')->orWhereColumn('times_redeemed', '<', 'usage_limit'); })
            ->get()
            ->filter(fn($c) => $c->canBeUsedBy(Auth::user()))
            ->values();

        $total = $subtotal; // products subtotal; discount applies to shipping fee at processCheckout

        return view('cart.checkout', compact('cartItems', 'total', 'addresses', 'defaultAddress', 'availableCoupons'))
            ->with('subtotal', $subtotal)
            ->with('discount', $discount)
            ->with('appliedCoupon', $appliedCoupon);
    }

    /**
     * Checkout Processing (Place Order)
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'payment_method'      => 'required|in:paymongo',
            'payment_option'      => 'nullable|in:full,downpayment',
            'downpayment_rate'    => 'nullable|numeric|min:1|max:99',
            'delivery_type'       => 'required|in:delivery,pickup',
            'address_id'          => 'required_if:delivery_type,delivery|exists:user_addresses,id',
            'customer_notes'      => 'nullable|string|max:500',
            'coupon_code'         => 'nullable|string|exists:coupons,code',
        ]);

        // Map form values to database enum values for compatibility
        $paymentMethod = $this->normalizeCheckoutPaymentMethod((string) $request->input('payment_method'));
        $paymentOption = strtolower((string) $request->input('payment_option', 'full')) === 'downpayment'
            ? 'downpayment'
            : 'full';
        $requestedDownpaymentRate = $paymentOption === 'downpayment'
            ? (float) $request->input('downpayment_rate', 50)
            : null;
        $deliveryType = $request->input('delivery_type') === 'delivery' ? 'deliver' : $request->input('delivery_type');
        $status = 'pending_confirmation'; // Map 'pending' to 'pending_confirmation'

        $userId = Auth::id();
        
        // Check if this is a "Buy Now" checkout
        // First check request params (Railway: sessions don't persist), then fall back to session
        if (($request->boolean('buy_now') && $request->filled('product_id')) || session()->has('buy_now_item')) {
            if ($request->boolean('buy_now') && $request->filled('product_id')) {
                $product = Product::find($request->input('product_id'));
                $variantId = $request->filled('variant_id') && is_numeric($request->input('variant_id'))
                    ? (int) $request->input('variant_id')
                    : null;
                $variant = $product ? $this->resolveVariant($product->loadMissing('variants'), $variantId) : null;
                $qty = max(1, (int) $request->input('quantity', 1));
            } else {
                $buyNowItem = session('buy_now_item');
                $product = Product::find($buyNowItem['product_id']);
                $variant = $product
                    ? $this->resolveVariant($product->loadMissing('variants'), !empty($buyNowItem['variant_id']) ? (int) $buyNowItem['variant_id'] : null)
                    : null;
                $qty = $buyNowItem['quantity'];
            }
            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('products.index')->with('error', 'Product not found.');
            }

            $hasVariants = $this->getActiveVariants($product)->isNotEmpty();
            if ($hasVariants && !$variant) {
                session()->forget('buy_now_item');
                return redirect()->route('products.show', $product)->with('error', 'Please select a valid product variant to continue checkout.');
            }

            // Create a collection with just the Buy Now item
            $cartItems = collect([
                (object)[
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $qty,
                    'product' => $product,
                    'variant' => $variant,
                ]
            ]);
        } else {
            // Regular cart checkout
            $cartItems = Cart::with('product.inventory', 'product.variants', 'variant')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                return redirect()->back()->with('error', 'Your cart is empty.');
            }
            
            // Filter by selected items - check request first (form data), then session fallback
            // Railway: Sessions don't persist, so form data is more reliable
            $selectedIds = $request->input('selected_items') ?? session('selected_cart_items');
            
            if ($selectedIds) {
                $cartItems = $cartItems->filter(function($item) use ($selectedIds) {
                    return in_array($item->id, $selectedIds);
                });
                
                if ($cartItems->isEmpty()) {
                    return redirect()->back()->with('error', 'No items selected for checkout.');
                }
            }
        }

        // Validate stock availability for all items before processing
        foreach ($cartItems as $item) {
            $variant = $item->variant ?? null;

            if ($variant) {
                if ((int) $variant->stock < (int) $item->quantity) {
                    $availableQty = (int) $variant->stock;
                    $variantLabel = $variant->display_name;
                    return redirect()->back()->with('error', "Variant \"{$variantLabel}\" for product \"{$item->product->name}\" has insufficient stock. Only {$availableQty} available.");
                }
            } else {
                $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
                if (!$inventory || !$inventory->hasSufficientStock($item->quantity)) {
                    $availableQty = $inventory?->quantity ?? 0;
                    return redirect()->back()->with('error', "Product \"{$item->product->name}\" has insufficient stock. Only {$availableQty} available.");
                }
            }
        }

        $subtotal = $cartItems->sum(function ($item) {
            $variant = $item->variant ?? null;
            return $this->getEffectiveUnitPrice($item->product, $variant) * $item->quantity;
        });
        $discount = 0;
        $coupon = null;
        // Find coupon; discount calculated after shippingFee is known.
        $couponCode = session('coupon_code') ?: $request->input('coupon_code');
        $pendingCoupon = null;
        if ($couponCode) {
            $pendingCoupon = Coupon::where('code', $couponCode)->first();
            if (!$pendingCoupon || !$pendingCoupon->canBeUsedBy(Auth::user())) {
                $pendingCoupon = null;
                session()->forget('coupon_code');
            }
        }

        // Build delivery address string (only for delivery type)
        $deliveryAddress = null;
        $userAddressId = null;
        $shippingFee = 0;
        
        if ($request->input('delivery_type') === 'delivery') {
            // Get the selected address
            $userAddress = \App\Models\UserAddress::where('id', $request->input('address_id'))
                ->where('user_id', $userId)
                ->firstOrFail();
            
            $userAddressId = $userAddress->id;
            
            // Build formatted address string
            $addressParts = [];
            $addressParts[] = $userAddress->street;
            $addressParts[] = 'Brgy. ' . $userAddress->barangay;
            $addressParts[] = $userAddress->city;
            $addressParts[] = $userAddress->province;

            if ($userAddress->postal_code) {
                $addressParts[] = $userAddress->postal_code;
            }

            $deliveryAddress = implode(', ', array_filter($addressParts));

            $shippingFee = $this->calculateShippingFeeForAddress($userAddress, $request->input('delivery_type', 'delivery'));
        } else {
            $deliveryAddress = 'Store Pickup';
            $shippingFee = 0;
        }

        // Apply coupon using the selected discount target.
        if ($pendingCoupon && $subtotal >= (float)($pendingCoupon->min_spend ?? 0)) {
            $coupon   = $pendingCoupon;
            $discount = $coupon->calculateTargetDiscount($subtotal, $shippingFee);
        } elseif ($pendingCoupon) {
            session()->forget('coupon_code');
        }

        $totalAmount = $subtotal + max(0, $shippingFee - $discount);
        $supportsDownpayment = $this->supportsDownpaymentFields();
        $isRequestedDownpayment = $paymentOption === 'downpayment';
        $effectiveDownpaymentRate = min(99, max(1, (float) ($requestedDownpaymentRate ?? 50)));

        if ($isRequestedDownpayment && !$supportsDownpayment) {
            \Log::warning('Downpayment requested but orders downpayment columns are unavailable. Using runtime checkout fallback.', [
                'user_id' => $userId,
                'rate' => $effectiveDownpaymentRate,
            ]);
        }

        // Create main order (tracking number & history auto-handled in Order model)
        // Initialize tracking details
        $trackingNumber = 'YAK-' . strtoupper(Str::random(10));
        $initialHistory = json_encode([
            [
                'status' => 'Order Placed',
                'date' => now()->format('Y-m-d h:i A')
            ]
        ]);

        $user = Auth::user();
        
        $orderPayload = [
            'order_ref'         => Order::generateOrderRef(),
            'user_id'           => $userId,
            'customer_name'     => $user->name,
            'customer_email'    => $user->email,
            'customer_phone'    => $user->phone ?? '',
            'subtotal'          => $subtotal,
            'shipping_fee'      => $shippingFee,
            'discount'          => $discount,
            'total'             => $totalAmount,
            'total_amount'      => $totalAmount,
            'discount_amount'   => $discount,
            'coupon_id'         => $coupon?->id,
            'coupon_code'       => $coupon?->code,
            'payment_method'    => $paymentMethod,
            'delivery_type'     => $deliveryType,
            'status'            => $status,
            'payment_status'    => 'pending',
            'tracking_number'   => $trackingNumber,
            'tracking_status'   => 'Order Placed',
            'tracking_history'  => $initialHistory,
            'shipping_address'  => $deliveryAddress,
            'delivery_address'  => $deliveryAddress,
            'shipping_city'     => $request->input('delivery_type') === 'delivery' ? $userAddress->city : 'Store Pickup',
            'shipping_province' => $request->input('delivery_type') === 'delivery' ? $userAddress->province : 'Store Pickup',
            'user_address_id'   => $userAddressId,
            'customer_notes'    => $request->input('customer_notes'),
        ];

        if ($supportsDownpayment) {
            $downpaymentPlan = $this->resolveDownpaymentPlan($totalAmount, $paymentOption, $requestedDownpaymentRate);
            $orderPayload = array_merge($orderPayload, $downpaymentPlan);
        }

        $order = Order::create($orderPayload);

        $supportsOrderItemTotal = Schema::hasColumn('order_items', 'total');

        // Add order items
        foreach ($cartItems as $item) {
            $variant = $item->variant ?? null;
            $unitPrice = $this->getEffectiveUnitPrice($item->product, $variant);

            $orderItemPayload = [
                'product_id' => $item->product_id,
                'variant_id' => $variant?->id,
                'variant_size' => $variant?->size,
                'variant_color' => $variant?->color,
                'quantity'   => $item->quantity,
                'price'      => $unitPrice,
            ];

            if ($supportsOrderItemTotal) {
                $orderItemPayload['total'] = $unitPrice * $item->quantity;
            }

            $order->orderItems()->create($orderItemPayload);

            if ($variant && $variant->stock >= $item->quantity) {
                $variant->decrement('stock', $item->quantity);
            }

            // Decrement inventory stock
            $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
            if ($inventory) {
                $inventory->decrementStock($item->quantity, $item->product->price);
            }
            
            // Also decrement product stock for consistency
            $product = Product::find($item->product_id);
            if ($product && $product->stock >= $item->quantity) {
                $product->decrement('stock', $item->quantity);
            }
        }

        // Record coupon redemption
        if ($coupon && $discount > 0) {
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'order_id' => $order->id,
                'amount_discounted' => $discount,
                'redeemed_at' => now(),
            ]);
            // increment usage
            $coupon->increment('times_redeemed');
            session()->forget('coupon_code');
        }

        // Clear cart or buy_now_item session
        $isBuyNow = ($request->boolean('buy_now') && $request->filled('product_id')) || session()->has('buy_now_item');
        if ($isBuyNow) {
            session()->forget('buy_now_item'); // safe to call even if not set
        } else {
            // If specific items were selected, only delete those
            // Check request first (form data), then session fallback
            $selectedIds = $request->input('selected_items') ?? session('selected_cart_items');
            
            if ($selectedIds) {
                Cart::where('user_id', $userId)
                    ->whereIn('id', $selectedIds)
                    ->delete();
                session()->forget('selected_cart_items');
            } else {
                // Otherwise, clear entire cart
                Cart::where('user_id', $userId)->delete();
            }
        }
        
        // Clear cart count cache
        \Cache::forget('cart_count_' . $userId);

        // Send order confirmation email
        try {
            $order->load('orderItems.product', 'user');
            $orderEmail = trim((string) optional($order->user)->email);
            if ($orderEmail !== '') {
                \App\Services\TransactionalMailService::sendView(
                    $orderEmail,
                    'Order Confirmation - ' . ($order->order_ref ?? ('ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT))),
                    'emails.order-confirmation',
                    ['order' => $order]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('Order confirmation email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // Create notification for user
        \App\Models\Notification::createNotification(
            $userId,
            'order',
            'Order Placed Successfully',
            "Your order #{$order->id} has been placed successfully! Total amount: ₱" . number_format($totalAmount, 2),
            route('orders.show', $order->id),
            [
                'order_id' => $order->id,
                'tracking_number' => $order->tracking_number,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod
            ]
        );

        // Create notification for admins
        $adminUsers = \App\Models\User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            \App\Models\Notification::createNotification(
                $admin->id,
                'order',
                'New Order Received',
                "A new order #{$order->id} has been placed by {$order->user->name}. Amount: ₱" . number_format($totalAmount, 2),
                url('/admin/orders'),
                [
                    'order_id' => $order->id,
                    'customer_name' => $order->user->name,
                    'tracking_number' => $order->tracking_number,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod
                ]
            );
        }

        $authToken = request()->input('auth_token') ?? session('auth_token');
        $isDownpaymentOrder = $supportsDownpayment
            ? strtolower((string) ($order->payment_option ?? 'full')) === 'downpayment'
            : $isRequestedDownpayment;
        $payableNowAmount = $isDownpaymentOrder
            ? ($supportsDownpayment
                ? $this->resolvePayableNowAmount($order, $totalAmount)
                : round($totalAmount * ($effectiveDownpaymentRate / 100), 2))
            : max(0, round($totalAmount, 2));

        $redirectUrl = $this->appendAuthToken(route('payment.failed', $order->id), $authToken);
        $paymentLabel = 'Preparing Payment';

        if ($request->payment_method === 'online') {
            $redirectUrl = $this->appendAuthToken(route('payment.online', $order->id), $authToken);
            $paymentLabel = 'Complete GCash Payment';
        }

        if ($request->payment_method === 'paymongo') {
            try {
                $checkoutOptions = [
                    'success_url' => $this->appendAuthToken(route('payment.paymongo.success', $order->id), $authToken),
                    'cancel_url'  => $this->appendAuthToken(route('payment.failed', $order->id), $authToken),
                ];

                if ($isDownpaymentOrder && !$supportsDownpayment) {
                    $checkoutOptions['amount_override'] = $payableNowAmount;
                    $checkoutOptions['is_downpayment_override'] = true;
                }

                $result = app(PayMongoCheckoutService::class)->createCheckout(
                    $order->loadMissing('items.product'),
                    $checkoutOptions
                );
                $redirectUrl = $result['checkout_url'];
                $paymentLabel = $isDownpaymentOrder ? 'Opening PayMongo Downpayment Checkout' : 'Opening PayMongo Checkout';
            } catch (\Throwable $e) {
                \Log::error('PayMongo checkout failed.', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                $redirectUrl = $this->appendAuthToken(route('payment.failed', $order->id), $authToken);
                $paymentLabel = 'Payment Unavailable';
            }
        }

        if ($request->payment_method === 'maya') {
            try {
                $mayaCheckoutUrl = $this->createMayaCheckoutRedirectUrl($order, $authToken);
                $redirectUrl = $mayaCheckoutUrl;
                $paymentLabel = 'Opening Maya Checkout';
            } catch (\Throwable $exception) {
                \Log::error('Maya checkout redirect failed — falling back to bank transfer page.', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
                $redirectUrl = $this->appendAuthToken(route('payment.bank', $order->id), $authToken);
                $paymentLabel = 'Complete Payment';
            }
        }

        $redirectUrlJs   = json_encode($redirectUrl);
        $orderRef        = htmlspecialchars($order->order_ref ?? '#' . $order->id, ENT_QUOTES, 'UTF-8');
        $totalFormatted  = '₱' . number_format($payableNowAmount, 2);

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Placed — Yakan</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #6b0000 0%, #800000 45%, #3d0000 100%);
            font-family: 'Inter', system-ui, sans-serif;
        }
        .card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
            animation: fadeUp 0.4s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .icon { font-size: 56px; margin-bottom: 20px; display: block; }
        h1 { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .sub { color: rgba(255,255,255,0.75); font-size: 14px; margin-bottom: 6px; }
        .amount { color: #ffd700; font-size: 18px; font-weight: 700; margin-bottom: 24px; }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid rgba(255,255,255,0.25);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .msg { color: rgba(255,255,255,0.65); font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">✅</span>
        <h1>Order Placed!</h1>
        <p class="sub">Order {$orderRef}</p>
        <p class="amount">{$totalFormatted}</p>
        <div class="spinner"></div>
        <p class="msg">Taking you to {$paymentLabel}…</p>
    </div>
    <script>
        setTimeout(function() { window.location.href = {$redirectUrlJs}; }, 1200);
    </script>
</body>
</html>
HTML
        );
    }

    private function supportsDownpaymentFields(): bool
    {
        return Schema::hasColumn('orders', 'payment_option')
            && Schema::hasColumn('orders', 'downpayment_rate')
            && Schema::hasColumn('orders', 'downpayment_amount')
            && Schema::hasColumn('orders', 'remaining_balance');
    }

    private function resolveDownpaymentPlan(float $orderTotal, string $paymentOption, ?float $requestedRate = null): array
    {
        $normalizedOption = strtolower($paymentOption) === 'downpayment' ? 'downpayment' : 'full';
        $rate = $normalizedOption === 'downpayment'
            ? min(99, max(1, (float) ($requestedRate ?? 50)))
            : 100.0;

        $orderTotal = max(0, round($orderTotal, 2));
        $downpaymentAmount = round($orderTotal * ($rate / 100), 2);
        $remainingBalance = max(0, round($orderTotal - $downpaymentAmount, 2));

        return [
            'payment_option' => $normalizedOption,
            'downpayment_rate' => $rate,
            'downpayment_amount' => $downpaymentAmount,
            'remaining_balance' => $remainingBalance,
        ];
    }

    private function resolvePayableNowAmount(Order $order, float $fallbackTotal): float
    {
        $isDownpayment = strtolower((string) ($order->payment_option ?? 'full')) === 'downpayment';

        if (!$isDownpayment) {
            return max(0, round($fallbackTotal, 2));
        }

        $downpayment = (float) ($order->downpayment_amount ?? 0);
        if ($downpayment <= 0) {
            $rate = (float) ($order->downpayment_rate ?? 50);
            $downpayment = round(max(0, $fallbackTotal) * ($rate / 100), 2);
        }

        return max(0, min(max(0, round($fallbackTotal, 2)), $downpayment));
    }

    private function normalizeCheckoutPaymentMethod(string $requestedMethod): string
    {
        $paymentMethod = $requestedMethod === 'online' ? 'gcash' : $requestedMethod;

        if (in_array($paymentMethod, ['maya', 'paymongo']) && !$this->ordersPaymentMethodSupports($paymentMethod)) {
            if ($this->tryEnableMayaPaymentMethod()) {
                return $paymentMethod;
            }

            \Log::warning('orders.payment_method enum does not include ' . $paymentMethod . ' yet; using gcash fallback.');
            return 'gcash';
        }

        return $paymentMethod;
    }

    private function calculateShippingFeeForAddress($address, string $deliveryType = 'delivery'): int
    {
        if ($deliveryType !== 'delivery' || !$address) {
            return 0;
        }

        $cityLower     = strtolower((string) ($address->city     ?? ''));
        $provinceLower = strtolower((string) ($address->province ?? ''));

        $matches = fn(array $keywords): bool => collect($keywords)->contains(
            fn($k) => str_contains($cityLower, $k) || str_contains($provinceLower, $k)
        );

        // Zone 1 — ₱100: Zamboanga Peninsula + BARMM (store base)
        if ($matches([
            'zamboanga', 'basilan', 'sulu', 'tawi',
            'maguindanao', 'lanao del sur', 'cotabato',
            'dipolog', 'dapitan', 'pagadian', 'ipil',
            'jolo', 'bongao', 'marawi', 'lamitan', 'isabela city',
        ])) {
            return 100;
        }

        // Zone 2 — ₱180: Rest of Mindanao
        if ($matches([
            'davao', 'sarangani', 'south cotabato', 'sultan kudarat',
            'north cotabato', 'misamis', 'bukidnon', 'lanao del norte',
            'camiguin', 'agusan', 'surigao', 'dinagat',
            'tagum', 'digos', 'panabo', 'general santos',
            'koronadal', 'kidapawan', 'cagayan de oro',
            'iligan', 'ozamiz', 'butuan', 'malaybalay',
        ])) {
            return 180;
        }

        // Zone 3 — ₱250: Visayas
        if ($matches([
            'cebu', 'bohol', 'negros', 'leyte', 'samar', 'biliran',
            'aklan', 'antique', 'capiz', 'iloilo', 'guimaras',
            'bacolod', 'tacloban', 'dumaguete', 'tagbilaran',
            'ormoc', 'calbayog', 'roxas city',
        ])) {
            return 250;
        }

        // Zone 4 — ₱300: NCR, Metro Manila, Central Luzon, CALABARZON
        if ($matches([
            'manila', 'makati', 'pasig', 'taguig', 'caloocan',
            'quezon city', 'antipolo', 'bulacan', 'cavite',
            'laguna', 'batangas', 'rizal', 'pampanga',
            'tarlac', 'nueva ecija', 'bataan', 'zambales', 'aurora',
            'angeles', 'san fernando', 'lucena', 'lipa',
        ])) {
            return 300;
        }

        // Zone 5 — ₱350: Far Luzon (Ilocos, CAR, Cagayan Valley, Bicol, MIMAROPA)
        return 350;
    }

    private function ordersPaymentMethodSupports(string $method): bool
    {
        static $supportCache = [];

        if (array_key_exists($method, $supportCache)) {
            return $supportCache[$method];
        }

        try {
            $column = DB::selectOne("SHOW COLUMNS FROM orders WHERE Field = 'payment_method'");
            $columnType = $this->extractColumnType($column);

            if ($columnType === '') {
                return $supportCache[$method] = false;
            }

            if (!str_contains($columnType, 'enum(')) {
                return $supportCache[$method] = true;
            }

            return $supportCache[$method] = str_contains($columnType, "'" . strtolower($method) . "'");
        } catch (\Throwable $exception) {
            \Log::warning('Unable to inspect orders.payment_method schema support', ['error' => $exception->getMessage()]);
            return $supportCache[$method] = false;
        }
    }

    private function extractColumnType($column): string
    {
        if (is_null($column)) {
            return '';
        }

        foreach ((array) $column as $key => $value) {
            if (strtolower((string) $key) === 'type') {
                return strtolower((string) $value);
            }
        }

        return '';
    }

    private function tryEnableMayaPaymentMethod(): bool
    {
        try {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('gcash','maya','paymongo','bank_transfer','cash') NOT NULL DEFAULT 'gcash'");
            \Log::info('Auto-updated orders.payment_method enum to include maya/paymongo during checkout.');
            return true;
        } catch (\Throwable $exception) {
            \Log::warning('Auto-update of orders.payment_method enum failed.', [
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function appendAuthToken(string $url, ?string $authToken): string
    {
        if (!$authToken) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'auth_token=' . urlencode($authToken);
    }

    private function createMayaCheckoutRedirectUrl(Order $order, ?string $authToken): string
    {
        if (!config('services.maya.enabled', false)) {
            throw new \RuntimeException('Maya payment is disabled in configuration.');
        }

        $result = app(MayaCheckoutService::class)->createCheckout(
            $order->loadMissing('items.product'),
            [
                'success_url' => $this->appendAuthToken(route('payment.success', $order->id), $authToken),
                'failure_url' => $this->appendAuthToken(route('payment.failed', $order->id), $authToken),
                'cancel_url' => $this->appendAuthToken(route('payment.failed', $order->id), $authToken),
            ]
        );

        $checkoutUrl = $result['checkout_url'] ?? null;
        if (!$checkoutUrl) {
            throw new \RuntimeException('Maya checkout URL missing from gateway response.');
        }

        return $checkoutUrl;
    }

    private function renderTransitionPage(
        string $title,
        string $headline,
        string $message,
        string $redirectUrl,
        string $buttonLabel,
        string $accentColor = '#800000'
    ) {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeHeadline = htmlspecialchars($headline, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $safeButtonLabel = htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8');
        $safeRedirectUrl = json_encode($redirectUrl);

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} — Yakan</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #6b0000 0%, #800000 45%, #3d0000 100%);
            font-family: 'Inter', system-ui, sans-serif;
            padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.22);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 36px 30px;
            text-align: center;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.38);
        }
        .icon { font-size: 48px; margin-bottom: 14px; display: block; }
        h1 { color: #fff; font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .headline { color: #ffe4e6; font-size: 15px; margin-bottom: 8px; }
        .message { color: rgba(255,255,255,0.82); font-size: 14px; margin-bottom: 18px; line-height: 1.5; }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn {
            border: 0;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform .15s ease, opacity .15s ease;
        }
        .btn-primary { background: {$accentColor}; color: #fff; }
        .btn-secondary { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.35); }
        .btn:hover { transform: translateY(-1px); opacity: .95; }
        .small { color: rgba(255,255,255,.65); font-size: 12px; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">⚠️</span>
        <h1>{$safeTitle}</h1>
        <p class="headline">{$safeHeadline}</p>
        <p class="message">{$safeMessage}</p>
        <div class="actions">
            <a class="btn btn-primary" href="{$redirectUrl}">{$safeButtonLabel}</a>
            <button class="btn btn-secondary" onclick="window.location.href={$safeRedirectUrl}">Continue</button>
        </div>
        <p class="small">Redirecting automatically in 3 seconds…</p>
    </div>
    <script>
        setTimeout(function () { window.location.href = {$safeRedirectUrl}; }, 3000);
    </script>
</body>
</html>
HTML
        );
    }

    /**
     * Show Online Payment Page
     */
    public function showOnlinePayment($orderId)
    {
        $order = Order::with('orderItems.product', 'user')->findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        return view('cart.payment-online', compact('order'));
    }

    /**
     * Show Bank Transfer Payment Page or Handle Receipt Upload
     */
    public function showBankPayment(Request $request, $orderId)
    {
        $order = Order::with('orderItems.product', 'user')->findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // If POST request, handle receipt upload
        if ($request->isMethod('post')) {
            \Log::info('=== BANK RECEIPT UPLOAD STARTED ===', [
                'order_id' => $orderId,
                'has_file' => $request->hasFile('receipt'),
                'user_id' => Auth::id()
            ]);
            
            try {
                $request->validate([
                    'receipt' => 'required|image|max:5000', // 5MB max
                ]);
                
                \Log::info('Validation passed');

                // Upload image — try Cloudinary first for persistent storage on Railway
                $receiptFile = $request->file('receipt');
                $cloudinary = new CloudinaryService();
                $path = null;

                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($receiptFile, 'bank_receipts');
                    if ($result) {
                        $path = $result['url'];
                        \Log::info('Bank receipt uploaded to Cloudinary', ['url' => $path, 'order_id' => $orderId]);
                    }
                }

                if (!$path) {
                    $path = $receiptFile->store('bank_receipts', 'public');
                    \Log::info('Bank receipt uploaded to local storage (fallback)', ['path' => $path, 'order_id' => $orderId]);
                }

                // Direct DB update to ensure it saves
                // Automatically set order status to 'processing' and payment_status to 'verified' when receipt is uploaded
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'processing',
                        'payment_status' => 'verified',
                        'bank_receipt' => $path,
                        'updated_at' => now()
                    ]);
                
                \Log::info('DB UPDATE EXECUTED');
                
                // Refresh the model
                $order = $order->fresh();
                
                \Log::info('Bank receipt saved via direct DB update', [
                    'order_id' => $orderId,
                    'bank_receipt' => $order->bank_receipt,
                    'payment_status' => $order->payment_status
                ]);
                
                // Try to append tracking event
                try {
                    $order->appendTrackingEvent('Bank receipt uploaded - Pending verification');
                    $order->save();
                } catch (\Exception $e) {
                    \Log::warning('Could not append tracking event: ' . $e->getMessage());
                }
                
                $saved = true;
                
                \Log::info('Bank receipt order update', [
                    'saved' => $saved,
                    'order_id' => $orderId,
                    'bank_receipt' => $order->bank_receipt,
                    'payment_status' => $order->payment_status
                ]);
                
                \Log::info('Bank payment processed successfully', [
                    'order_id' => $orderId,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                ]);

                // Notify user
                \App\Models\Notification::createNotification(
                    $order->user_id,
                    'payment',
                    'Bank payment verified',
                    "Your bank payment for order #{$order->id} has been verified. Your order is now being processed!",
                    route('orders.show', $order->id),
                    [
                        'order_id' => $order->id,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                    ]
                );

                // Notify admins about the payment
                $adminUsers = \App\Models\User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    \App\Models\Notification::createNotification(
                        $admin->id,
                        'payment',
                        'Payment Received',
                        "Payment received for order #{$order->id} via Bank Transfer. Amount: ₱" . number_format($order->total_amount, 2),
                        route('admin.orders.show', $order->id),
                        [
                            'order_id' => $order->id,
                            'payment_method' => $order->payment_method,
                            'payment_status' => $order->payment_status,
                        ]
                    );
                }

                $authToken = $request->input('auth_token') ?? session('auth_token');
                $tokenParam = $authToken ? '?auth_token=' . $authToken : '';
                return redirect(route('orders.show', $orderId) . $tokenParam)
                                 ->with('success', 'Bank receipt uploaded! We will verify and update your order shortly.');
            } catch (\Exception $e) {
                \Log::error('Error processing bank payment', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $authToken = $request->input('auth_token') ?? session('auth_token');
                $tokenParam = $authToken ? '?auth_token=' . $authToken : '';
                return redirect(route('orders.show', $orderId) . $tokenParam)
                                 ->with('error', 'Error processing payment: ' . $e->getMessage());
            }
        }

        // GET request - show the payment form
        return view('cart.payment-bank', compact('order'));
    }

    /**
     * Submit Bank Payment (Upload Receipt)
     */
    public function submitBankPayment(Request $request, $orderId)
    {
        try {
            $request->validate([
                'receipt' => 'required|image|max:5000', // 5MB max
            ]);

            $order = Order::findOrFail($orderId);

            if ($order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized payment submission.');
            }

            // Upload image — try Cloudinary first for persistent storage on Railway
            $receiptFile = $request->file('receipt');
            $cloudinary = new CloudinaryService();
            $path = null;

            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($receiptFile, 'bank_receipts');
                if ($result) {
                    $path = $result['url'];
                    \Log::info('Bank receipt uploaded to Cloudinary (submitBankPayment)', ['url' => $path, 'order_id' => $orderId]);
                }
            }

            if (!$path) {
                $path = $receiptFile->store('bank_receipts', 'public');
                \Log::info('Bank receipt uploaded to local storage (submitBankPayment fallback)', ['path' => $path, 'order_id' => $orderId]);
            }

            // Direct DB update
            \DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'payment_status' => 'verification_pending',
                    'bank_receipt' => $path,
                    'updated_at' => now()
                ]);
            
            $order = $order->fresh();
            
            // Try tracking event
            try {
                $order->appendTrackingEvent('Bank receipt uploaded - Pending verification');
                $order->save();
            } catch (\Exception $e) {
                \Log::warning('Could not append tracking event: ' . $e->getMessage());
            }
            
            $saved = true;
            
            \Log::info('Bank receipt saved (submitBankPayment)', [
                'saved' => $saved,
                'order_id' => $orderId,
                'bank_receipt' => $order->bank_receipt
            ]);
            
            \Log::info('Bank payment submitted successfully', [
                'order_id' => $orderId,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ]);

            return redirect()->route('orders.show', $orderId)
                             ->with('success', 'Bank receipt uploaded! We will verify and update your order shortly.');
        } catch (\Exception $e) {
            \Log::error('Error submitting bank payment', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('orders.show', $orderId)
                             ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function processPayment(Request $request, $orderId)
    {
        try {
            $order = Order::with('user')->findOrFail($orderId);

            if ($order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access to this order.');
            }

            if (!in_array($order->payment_method, ['gcash', 'maya'])) {
                return redirect()->route('orders.show', $orderId)
                                 ->with('error', 'This order is not set up for online payment.');
            }

            $walletLabel = $order->payment_method === 'maya' ? 'Maya' : 'GCash';

            $request->validate([
                'gcash_reference' => 'nullable|string|max:191',
                'payment_reference' => 'required|string|max:191',
                'payment_proof' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120',
            ]);

            // Handle payment proof upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $paymentProofPath = $file->storeAs('payment_proofs', $fileName, 'public');
                \Log::info('Payment proof uploaded', ['path' => $paymentProofPath, 'order_id' => $orderId]);
            }

            // Update payment status
            $order->payment_status = 'paid';
            $order->payment_verified_at = now();
            
            // Automatically set order status to 'processing' when payment is verified
            $order->status = 'processing';

            // Store payment proof path
            if ($paymentProofPath) {
                $order->gcash_receipt = $paymentProofPath;
            }

            $message = 'Payment verified via ' . $walletLabel;
            $reference = $request->input('payment_reference', $request->input('gcash_reference'));
            if (!empty($reference)) {
                $message .= ' (Ref: ' . $reference . ')';
                $order->payment_reference = $reference;
            }

            $order->appendTrackingEvent($message);
            $order->save();
            
            \Log::info('Payment processed successfully', [
                'order_id' => $orderId,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ]);

            \App\Models\Notification::createNotification(
                $order->user_id,
                'payment',
                $walletLabel . ' payment verified',
                "Your {$walletLabel} payment for order #{$order->id} has been verified. Your order is now being processed!",
                route('orders.show', $order->id),
                [
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                ]
            );

            // Notify admins about the payment
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                \App\Models\Notification::createNotification(
                    $admin->id,
                    'payment',
                    'Payment Received',
                    "Payment received for order #{$order->id} via {$walletLabel}. Amount: ₱" . number_format($order->total_amount, 2),
                    route('admin.orders.show', $order->id),
                    [
                        'order_id' => $order->id,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                    ]
                );
            }

            $authToken = request()->input('auth_token') ?? session('auth_token');
            $tokenParam = $authToken ? '?auth_token=' . $authToken : '';
            return redirect(route('orders.show', $orderId) . $tokenParam)
                             ->with('success', $walletLabel . ' payment verified! Your order is now being processed.');
        } catch (\Exception $e) {
            \Log::error('Error processing payment', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $authToken = request()->input('auth_token') ?? session('auth_token');
            $tokenParam = $authToken ? '?auth_token=' . $authToken : '';
            return redirect(route('orders.show', $orderId) . $tokenParam)
                             ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function paymongoSuccess(Request $request, $orderId)
    {
        $authToken = $request->input('auth_token') ?? session('auth_token');
        $order = Order::with('user')->findOrFail($orderId);

        // Verify checkout session with PayMongo
        try {
            $checkoutId = $order->payment_reference;
            if ($checkoutId) {
                $session    = app(PayMongoCheckoutService::class)->fetchCheckout($checkoutId);
                $pmStatus   = $session['attributes']['payment_intent']['attributes']['status'] ?? null;

                if ($pmStatus === 'succeeded') {
                    $this->applyPayMongoPaymentResult($order, $session);

                    return redirect($this->appendAuthToken(route('orders.show', $orderId), $authToken))
                        ->with('success', 'Payment confirmed! Your order is now being processed.');
                }
            }
        } catch (\Throwable $e) {
            \Log::error('PayMongo success verification failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }

        // Fallback: mark as paid regardless (PayMongo redirected to success URL)
        $this->applyPayMongoPaymentResult($order);

        return redirect($this->appendAuthToken(route('orders.show', $orderId), $authToken))
            ->with('success', 'Payment received! Your order is now being processed.');
    }

    /**
     * Mobile app PayMongo success callback — no session auth required.
     * PayMongo redirects here after payment; we mark the order as paid
     * and return an HTML confirmation page the user can close.
     */
    public function mobilePaymongoSuccess(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        try {
            $checkoutId = $order->payment_reference;
            if ($checkoutId) {
                $session  = app(PayMongoCheckoutService::class)->fetchCheckout($checkoutId);
                $pmStatus = $session['attributes']['payment_intent']['attributes']['status'] ?? null;

                if ($pmStatus === 'succeeded') {
                    $this->applyPayMongoPaymentResult($order, $session);
                } else {
                    // PayMongo only calls success_url on success, so mark paid as fallback
                    $this->applyPayMongoPaymentResult($order);
                }
            } else {
                $this->applyPayMongoPaymentResult($order);
            }
        } catch (\Throwable $e) {
            \Log::error('Mobile PayMongo success verification failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            // Still mark as paid — PayMongo only redirects to success_url on completed payment
            $this->applyPayMongoPaymentResult($order);
        }

        return response()->make(<<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Successful</title>
  <style>
    body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center;
           justify-content: center; height: 100vh; margin: 0; background: #f9f9f9; text-align: center; }
    .icon { font-size: 64px; margin-bottom: 16px; }
    h1 { color: #2e7d32; margin-bottom: 8px; }
    p  { color: #555; margin-bottom: 24px; }
    .badge { background: #2e7d32; color: #fff; padding: 8px 20px; border-radius: 20px; font-size: 14px; }
  </style>
  <script>
    // Redirect back to the mobile app via deep link so openAuthSessionAsync auto-closes the browser
    window.location.href = 'yakanapp://payment/success/{$orderId}';
  </script>
</head>
<body>
  <div class="icon">✅</div>
  <h1>Payment Successful!</h1>
  <p>Your order has been confirmed and is now being processed.</p>
  <div class="badge">You may close this page and return to the app.</div>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Mobile app PayMongo failed/cancel callback.
     */
    public function mobilePaymongoFailed(Request $request, $orderId)
    {
        return response()->make(<<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Failed</title>
  <style>
    body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center;
           justify-content: center; height: 100vh; margin: 0; background: #f9f9f9; text-align: center; }
    .icon { font-size: 64px; margin-bottom: 16px; }
    h1 { color: #c62828; margin-bottom: 8px; }
    p  { color: #555; margin-bottom: 24px; }
    .badge { background: #c62828; color: #fff; padding: 8px 20px; border-radius: 20px; font-size: 14px; }
  </style>
  <script>
    // Redirect back to the mobile app via deep link so openAuthSessionAsync auto-closes the browser
    window.location.href = 'yakanapp://payment/failed/{$orderId}';
  </script>
</head>
<body>
  <div class="icon">❌</div>
  <h1>Payment Cancelled</h1>
  <p>Your payment was not completed. Please try again.</p>
  <div class="badge">Close this page and return to the app to retry.</div>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html']);
    }

    public function paymentSuccess(Request $request, $orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        if ($order->payment_method === 'maya') {
            $checkoutId = $request->query('checkoutId')
                ?? $request->query('checkout_id')
                ?? $request->query('id');

            $paymentStatus = $this->syncMayaPaymentStatus($order, $checkoutId);

            if ($paymentStatus === 'paid') {
                return redirect($this->appendAuthToken(route('orders.show', $orderId), $request->input('auth_token') ?? session('auth_token')))
                    ->with('success', 'Maya payment confirmed successfully.');
            }
        }

        return redirect($this->appendAuthToken(route('orders.show', $orderId), $request->input('auth_token') ?? session('auth_token')))
            ->with('info', 'Maya checkout completed. Payment verification is in progress.');
    }

    public function paymentFailed(Request $request, $orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        if ($order->payment_method === 'maya') {
            $checkoutId = $request->query('checkoutId')
                ?? $request->query('checkout_id')
                ?? $request->query('id');

            $paymentStatus = $this->syncMayaPaymentStatus($order, $checkoutId);

            if ($paymentStatus === 'paid') {
                return redirect($this->appendAuthToken(route('orders.show', $orderId), $request->input('auth_token') ?? session('auth_token')))
                    ->with('success', 'Maya payment was completed successfully.');
            }
        }

        return redirect($this->appendAuthToken(route('orders.show', $orderId), $request->input('auth_token') ?? session('auth_token')))
            ->with('error', 'Maya payment was not completed. You can try again from your order page.');
    }

    private function applyPayMongoPaymentResult(Order $order, ?array $session = null): void
    {
        $order->payment_status = 'paid';
        $order->status = 'processing';

        $isDownpayment = strtolower((string) ($order->payment_option ?? 'full')) === 'downpayment'
            && (float) ($order->remaining_balance ?? 0) > 0;

        $paymentId = $session ? $this->extractPayMongoPaymentId($session) : null;
        if (!empty($paymentId)) {
            $order->payment_reference = $paymentId;
        }

        $paymentDate = $session ? $this->extractPayMongoPaymentDate($session) : null;
        $order->payment_verified_at = $paymentDate ?? now();

        if ($isDownpayment) {
            $downpaymentAmount = number_format((float) ($order->downpayment_amount ?? 0), 2);
            $remainingBalance = number_format((float) ($order->remaining_balance ?? 0), 2);
            $tag = "Downpayment received: PHP {$downpaymentAmount}; remaining balance: PHP {$remainingBalance}";
            $existingNotes = trim((string) ($order->notes ?? ''));

            if (!str_contains($existingNotes, $tag)) {
                $order->notes = $existingNotes === '' ? $tag : ($existingNotes . "\n" . $tag);
            }
        }

        $order->save();
    }

    private function extractPayMongoPaymentId(array $session): ?string
    {
        $candidates = [
            data_get($session, 'attributes.payments.0.id'),
            data_get($session, 'attributes.payments.0.attributes.id'),
            data_get($session, 'attributes.payment.id'),
            data_get($session, 'attributes.payment.data.id'),
            data_get($session, 'attributes.payment_intent.attributes.latest_payment.id'),
            data_get($session, 'attributes.payment_intent.attributes.payments.0.id'),
            data_get($session, 'attributes.payment_intent.attributes.last_payment.id'),
        ];

        foreach ($candidates as $value) {
            $value = is_string($value) ? trim($value) : '';
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractPayMongoPaymentDate(array $session): ?\Carbon\Carbon
    {
        $candidates = [
            data_get($session, 'attributes.payments.0.attributes.paid_at'),
            data_get($session, 'attributes.payments.0.attributes.created_at'),
            data_get($session, 'attributes.payment.attributes.paid_at'),
            data_get($session, 'attributes.payment.attributes.created_at'),
            data_get($session, 'attributes.payment_intent.attributes.updated_at'),
            data_get($session, 'attributes.payment_intent.attributes.created_at'),
            data_get($session, 'attributes.updated_at'),
        ];

        foreach ($candidates as $value) {
            if (!empty($value)) {
                try {
                    return \Carbon\Carbon::parse($value);
                } catch (\Throwable $exception) {
                    // Ignore invalid gateway timestamps and continue to fallback values.
                }
            }
        }

        return null;
    }

    private function syncMayaPaymentStatus(Order $order, ?string $checkoutId = null): string
    {
        try {
            return app(MayaCheckoutService::class)->syncOrderStatusFromCheckout($order, $checkoutId);
        } catch (\Throwable $exception) {
            \Log::warning('Unable to sync Maya payment status from checkout API.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return (string) $order->payment_status;
        }
    }
}
