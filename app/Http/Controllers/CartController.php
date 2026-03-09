<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
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

            \Log::info('Buy Now/Add to Cart attempt', [
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'buy_now' => $request->input('buy_now') ? 'yes' : 'no'
            ]);

            // Check stock — use inventory table if available, fall back to product.stock
            $availableStock = $product->stock ?? 0;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('inventory')) {
                    $inventory = \App\Models\Inventory::where('product_id', $product->id)->first();
                    if (!$inventory) {
                        $inventory = \App\Models\Inventory::create([
                            'product_id'      => $product->id,
                            'quantity'        => $product->stock ?? 0,
                            'min_stock_level' => 5,
                            'max_stock_level' => 100,
                            'cost_price'      => ($product->price ?? 0) * 0.6,
                            'selling_price'   => $product->price ?? 0,
                        ]);
                        \Log::info('Auto-created inventory', ['product_id' => $product->id]);
                    }
                    $availableStock = $inventory->quantity;
                }
            } catch (\Exception $invEx) {
                \Log::warning('Inventory check skipped, using product.stock', ['error' => $invEx->getMessage()]);
            }

            // Check stock availability
            if ($availableStock < $qty) {
                \Log::warning('Insufficient stock', ['product_id' => $product->id, 'requested' => $qty, 'available' => $availableStock]);
                $msg = "Insufficient stock. Only {$availableStock} item(s) available.";
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
                        'quantity'   => $qty,
                    ]);
                }

                // Non-AJAX fallback: redirect directly to checkout with URL params
                $authToken = $request->input('auth_token') ?? session('auth_token');
                $paramStr = 'buy_now=1&product_id=' . $product->id . '&quantity=' . $qty;
                if ($authToken) $paramStr .= '&auth_token=' . $authToken;
                return redirect('/cart/checkout?' . $paramStr);
            }

            // Regular "Add to Cart" flow
            $cartItem = Cart::where('user_id', $userId)
                            ->where('product_id', $product->id)
                            ->first();

            if ($cartItem) {
                $newTotal = $cartItem->quantity + $qty;
                if ($newTotal > $availableStock) {
                    $msg = "Cannot add more. Only {$availableStock} item(s) available in total.";
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

        if (!$coupon) {
            return back()->with('error', 'Coupon not found.');
        }

        // compute subtotal to validate min spend
        $cartItems = Cart::with('product.inventory')->where('user_id', Auth::id())->get();
        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        // Detailed validation with specific error messages
        if (!$coupon->active) {
            return back()->with('error', 'This coupon is not active.');
        }

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return back()->with('error', 'This coupon is not yet active.');
        }

        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            return back()->with('error', 'This coupon has expired.');
        }

        if ($coupon->usage_limit && $coupon->times_redeemed >= $coupon->usage_limit) {
            return back()->with('error', 'This coupon usage limit has been reached.');
        }

        if ($coupon->usage_limit_per_user) {
            $userRedemptions = $coupon->redemptions()->where('user_id', Auth::id())->count();
            if ($userRedemptions >= $coupon->usage_limit_per_user) {
                return back()->with('error', 'You have already used this coupon.');
            }
        }

        if ($coupon->calculateDiscount((float)$subtotal) <= 0) {
            return back()->with('error', 'Coupon does not apply to your current subtotal (minimum: ₱' . number_format($coupon->min_spend, 2) . ').');
        }

        session(['coupon_code' => $code]);
        return back()->with('success', 'Coupon applied successfully!');
    }

    /**
     * Remove applied coupon from session
     */
    public function removeCoupon()
    {
        session()->forget('coupon_code');
        return back()->with('success', 'Coupon removed.');
    }

    /**
     * Get cart count for current user
     */
    public function getCartCount()
    {
        $userId = Auth::id();
        return Cache::remember('cart_count_' . $userId, 300, function () use ($userId) {
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

        // Manually load products with inventory to ensure they're loaded with stock data
        foreach ($cartItems as $item) {
            if (!$item->product) {
                $item->load('product.inventory');
            } else {
                $item->load('product.inventory');
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

        return view('cart.index', compact('cartItems'));
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

        // Handle Buy Now item (session-based)
        if ($id === 'buy_now' && session()->has('buy_now_item')) {
            $buyNowItem = session('buy_now_item');
            $product = Product::find($buyNowItem['product_id']);
            
            if (!$product) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Product not found'], 404);
                }
                return redirect()->back()->with('error', 'Product not found');
            }
            
            $newQty = (int) $request->quantity;
            $maxStock = $product->stock;
            if (is_numeric($maxStock) && $maxStock > 0) {
                $newQty = min($newQty, (int) $maxStock);
            }
            $newQty = max(1, $newQty);
            
            // Update session
            session(['buy_now_item' => [
                'product_id' => $product->id,
                'quantity' => $newQty,
            ]]);
            
            if ($request->wantsJson()) {
                $itemSubtotal = $newQty * $product->price;
                $cartTotal = $itemSubtotal;
                
                // Apply coupon if exists
                $discount = 0;
                if (session()->has('coupon_code')) {
                    $coupon = \App\Models\Coupon::where('code', session('coupon_code'))->first();
                    if ($coupon) {
                        $discount = $coupon->discount_type === 'fixed' 
                            ? $coupon->discount_amount 
                            : ($cartTotal * ($coupon->discount_amount / 100));
                    }
                }
                
                $totalAmount = $cartTotal - $discount;
                
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
        $cartItem = Cart::with('product.inventory')
                        ->where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$cartItem) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
            }
            return redirect()->back()->with('error', 'Cart item not found');
        }

        $maxStock = $cartItem->product?->stock;
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
            $itemSubtotal = $cartItem->quantity * $cartItem->product->price;
            
            // Get all cart items and calculate totals
            $allCartItems = Cart::with('product.inventory')->where('user_id', Auth::id())->get();
            $cartTotal = $allCartItems->sum(function($item) {
                return $item->quantity * $item->product->price;
            });
            
            // Apply coupon if exists
            $discount = 0;
            if (session()->has('coupon_code')) {
                $coupon = \App\Models\Coupon::where('code', session('coupon_code'))->first();
                if ($coupon) {
                    $discount = $coupon->discount_type === 'fixed' 
                        ? $coupon->discount_amount 
                        : ($cartTotal * ($coupon->discount_amount / 100));
                }
            }
            
            $totalAmount = $cartTotal - $discount;
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
            $qty = max(1, (int) $request->input('quantity', 1));
            if ($product) {
                $cartItems = collect([
                    (object)[
                        'id'         => 'buy_now',
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'product'    => $product,
                    ]
                ]);
                $subtotal = $product->price * $qty;
                $discount = 0;
                $appliedCoupon = null;
                $total = $subtotal;
                $addresses = \App\Models\UserAddress::forUser(Auth::id())
                    ->orderBy('is_default', 'desc')->get();
                $defaultAddress = $addresses->firstWhere('is_default', true);
                return view('cart.checkout', compact('cartItems', 'total', 'addresses', 'defaultAddress'))
                    ->with('subtotal', $subtotal)
                    ->with('discount', $discount)
                    ->with('appliedCoupon', $appliedCoupon);
            }
        }

        // Check if "Buy Now" item exists in session (fallback for non-Railway environments)
        if (session()->has('buy_now_item')) {
            $buyNowItem = session('buy_now_item');
            $product = Product::find($buyNowItem['product_id']);
            
            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('products.index')->with('error', 'Product not found.');
            }

            // Create a collection with just the Buy Now item
            $cartItems = collect([
                (object)[
                    'id' => 'buy_now',
                    'product_id' => $product->id,
                    'quantity' => $buyNowItem['quantity'],
                    'product' => $product,
                ]
            ]);
            
            $subtotal = $product->price * $buyNowItem['quantity'];
        } else {
            // Regular cart checkout
            $cartItems = Cart::with('product.inventory')
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
            
            $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        }

        $discount = 0;
        $appliedCoupon = null;

        if (session()->has('coupon_code')) {
            $code = session('coupon_code');
            $appliedCoupon = Coupon::where('code', $code)->first();
            if ($appliedCoupon && $appliedCoupon->canBeUsedBy(Auth::user())) {
                $discount = $appliedCoupon->calculateDiscount((float)$subtotal);
            } else {
                // Invalid or unusable coupon, clear it
                session()->forget(['coupon_code']);
                $appliedCoupon = null;
            }
        }

        $total = max(0, $subtotal - $discount);

        // Load user addresses
        $addresses = \App\Models\UserAddress::forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->get();
        
        $defaultAddress = $addresses->firstWhere('is_default', true);

        return view('cart.checkout', compact('cartItems', 'total', 'addresses', 'defaultAddress'))
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
            'payment_method'      => 'required|in:online,bank_transfer',
            'delivery_type'       => 'required|in:delivery,pickup',
            'address_id'          => 'required_if:delivery_type,delivery|exists:user_addresses,id',
            'customer_notes'      => 'nullable|string|max:500',
        ]);

        // Map form values to database enum values for compatibility
        $paymentMethod = $request->input('payment_method') === 'online' ? 'gcash' : $request->input('payment_method');
        $deliveryType = $request->input('delivery_type') === 'delivery' ? 'deliver' : $request->input('delivery_type');
        $status = 'pending_confirmation'; // Map 'pending' to 'pending_confirmation'

        $userId = Auth::id();
        
        // Check if this is a "Buy Now" checkout
        // First check request params (Railway: sessions don't persist), then fall back to session
        if (($request->boolean('buy_now') && $request->filled('product_id')) || session()->has('buy_now_item')) {
            if ($request->boolean('buy_now') && $request->filled('product_id')) {
                $product = Product::find($request->input('product_id'));
                $qty = max(1, (int) $request->input('quantity', 1));
            } else {
                $buyNowItem = session('buy_now_item');
                $product = Product::find($buyNowItem['product_id']);
                $qty = $buyNowItem['quantity'];
            }
            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('products.index')->with('error', 'Product not found.');
            }

            // Create a collection with just the Buy Now item
            $cartItems = collect([
                (object)[
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'product' => $product,
                ]
            ]);
        } else {
            // Regular cart checkout
            $cartItems = Cart::with('product.inventory')->where('user_id', $userId)->get();

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
            $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
            if (!$inventory || !$inventory->hasSufficientStock($item->quantity)) {
                $availableQty = $inventory?->quantity ?? 0;
                return redirect()->back()->with('error', "Product \"{$item->product->name}\" has insufficient stock. Only {$availableQty} available.");
            }
        }

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $discount = 0;
        $coupon = null;
        if (session()->has('coupon_code')) {
            $coupon = Coupon::where('code', session('coupon_code'))->first();
            if ($coupon && $coupon->canBeUsedBy(Auth::user())) {
                $discount = $coupon->calculateDiscount((float)$subtotal);
            } else {
                $coupon = null;
                session()->forget('coupon_code');
            }
        }

        $totalBeforeShipping = max(0, $subtotal - $discount);

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

            // Server-side shipping fee calculation (mirror frontend tiers)
            $cityLower = strtolower($userAddress->city ?? '');
            $regionLower = strtolower($userAddress->province ?? $userAddress->region ?? '');
            $postalCode = $userAddress->postal_code ?? '';

            // Zone 1 — ₱100: Zamboanga City + Peninsula + BARMM
            if (str_contains($cityLower, 'zamboanga') || str_contains($regionLower, 'zamboanga') || str_contains($regionLower, 'barmm') || str_contains($regionLower, 'bangsamoro') ||
                      in_array($cityLower, ['dipolog city', 'dapitan city', 'pagadian city', 'isabela city',
                                            'zamboanga del norte', 'zamboanga del sur', 'zamboanga sibugay',
                                            'ipil', 'jolo', 'bongao', 'cotabato city', 'marawi city', 'lamitan city'])) {
                $shippingFee = 100;
            // Zone 2 — ₱180: Other Mindanao
            } elseif (str_contains($regionLower, 'mindanao') || str_contains($regionLower, 'davao') ||
                      str_contains($regionLower, 'soccsksargen') || str_contains($regionLower, 'caraga') ||
                      str_contains($regionLower, 'northern mindanao') ||
                      in_array($cityLower, ['davao city', 'digos city', 'tagum city', 'panabo city',
                                            'general santos city', 'koronadal city', 'kidapawan city',
                                            'cagayan de oro city', 'iligan city', 'ozamiz city',
                                            'butuan city', 'surigao city', 'malaybalay city'])) {
                $shippingFee = 180;
            // Zone 3 — ₱250: Visayas
            } elseif (str_contains($regionLower, 'visayas') ||
                      in_array($cityLower, ['cebu city', 'iloilo city', 'bacolod city',
                                            'tacloban city', 'dumaguete city', 'tagbilaran city',
                                            'ormoc city', 'calbayog city', 'roxas city'])) {
                $shippingFee = 250;
            // Zone 4 — ₱300: NCR + nearby Luzon
            } elseif (str_contains($regionLower, 'ncr') || str_contains($regionLower, 'metro manila') ||
                      str_contains($cityLower, 'manila') || str_contains($regionLower, 'calabarzon') ||
                      str_contains($regionLower, 'central luzon') ||
                      in_array($cityLower, ['quezon city', 'makati city', 'pasig city', 'taguig city',
                                            'caloocan city', 'antipolo city', 'angeles city',
                                            'san fernando city', 'batangas city', 'lucena city'])) {
                $shippingFee = 300;
            // Zone 5 — ₱350: Far Luzon / remote
            } else {
                $shippingFee = 350;
            }
        } else {
            $deliveryAddress = 'Store Pickup';
            $shippingFee = 0;
        }

        $totalAmount = $totalBeforeShipping + $shippingFee;

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
        
        $order = Order::create([
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
        ]);

        // Add order items
        foreach ($cartItems as $item) {
            $order->orderItems()->create([
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price,
            ]);

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

        // Get auth_token to pass through redirects (cookies are stripped by Railway edge proxy)
        $authToken = request()->input('auth_token') ?? session('auth_token');
        $tokenParam = $authToken ? '?auth_token=' . $authToken : '';

        // Redirect based on payment method — use JS redirect to avoid Railway's broken
        // plain "Redirecting to..." HTML body that PHP redirect() produces.
        $redirectUrl = $request->payment_method === 'online'
            ? route('payment.online', $order->id) . $tokenParam
            : route('payment.bank', $order->id) . $tokenParam;

        $redirectUrlJs   = json_encode($redirectUrl);
        $orderRef        = htmlspecialchars($order->order_ref ?? '#' . $order->id, ENT_QUOTES, 'UTF-8');
        $totalFormatted  = '₱' . number_format($totalAmount, 2);
        $paymentLabel    = $request->payment_method === 'online' ? 'Complete GCash Payment' : 'Complete Bank Transfer';

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

                // Upload image
                $path = $request->file('receipt')->store('bank_receipts', 'public');
                \Log::info('Bank receipt file uploaded', [
                    'path' => $path,
                    'order_id' => $orderId,
                    'full_path' => storage_path('app/public/' . $path)
                ]);

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

            // Upload image
            $path = $request->file('receipt')->store('bank_receipts', 'public');
            \Log::info('Bank receipt uploaded', ['path' => $path, 'order_id' => $orderId]);

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

            if ($order->payment_method !== 'gcash') {
                return redirect()->route('orders.show', $orderId)
                                 ->with('error', 'This order is not set up for online payment.');
            }

            $request->validate([
                'gcash_reference' => 'nullable|string|max:191',
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

            $message = 'Payment verified via GCash';
            if ($request->filled('gcash_reference')) {
                $message .= ' (Ref: ' . $request->input('gcash_reference') . ')';
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
                'GCash payment verified',
                "Your GCash payment for order #{$order->id} has been verified. Your order is now being processed!",
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
                    "Payment received for order #{$order->id} via GCash. Amount: ₱" . number_format($order->total_amount, 2),
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
                             ->with('success', 'GCash payment verified! Your order is now being processed.');
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
}
