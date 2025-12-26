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
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Add product to cart
     */
    public function add(Request $request, Product $product)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
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

            // Check or create inventory record
            $inventory = \App\Models\Inventory::where('product_id', $product->id)->first();
            
            if (!$inventory) {
                // Auto-create inventory from product stock
                $inventory = \App\Models\Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $product->stock,
                    'min_stock_level' => 5,
                    'max_stock_level' => 100,
                    'cost_price' => $product->price * 0.6,
                    'selling_price' => $product->price,
                ]);
                \Log::info('Auto-created inventory', ['product_id' => $product->id, 'quantity' => $product->stock]);
            }
            
            // Check inventory stock
            if (!$inventory->hasSufficientStock($qty)) {
                $availableQty = $inventory->quantity;
                \Log::warning('Insufficient stock', ['product_id' => $product->id, 'requested' => $qty, 'available' => $availableQty]);
                return redirect()->back()->with('error', "Insufficient stock. Only {$availableQty} item(s) available.");
            }

            // If "Buy Now" was clicked, store in session and redirect directly to checkout
            if ($request->input('buy_now')) {
                \Log::info('Buy Now triggered', ['user_id' => $userId, 'product_id' => $product->id, 'quantity' => $qty]);
                
                // Store Buy Now item in session (not in cart)
                session(['buy_now_item' => [
                    'product_id' => $product->id,
                    'quantity' => $qty
                ]]);
                
                \Log::info('Buy Now item stored in session', ['product_id' => $product->id, 'quantity' => $qty]);
                return redirect()->route('cart.checkout')->with('success', 'Proceeding to checkout!');
            }

            // Regular "Add to Cart" flow
            $cartItem = Cart::where('user_id', $userId)
                            ->where('product_id', $product->id)
                            ->first();

            if ($cartItem) {
                // Check if new total exceeds available stock
                $newTotal = $cartItem->quantity + $qty;
                
                if (!$inventory->hasSufficientStock($newTotal)) {
                    $availableQty = $inventory->quantity;
                    return redirect()->back()->with('error', "Cannot add more. Only {$availableQty} item(s) available in total.");
                }
                $cartItem->quantity += $qty;
            $cartItem->save();
            \Log::info('Cart item updated', ['cart_item_id' => $cartItem->id, 'new_quantity' => $cartItem->quantity]);
        } else {
            $newItem = Cart::create([
                'user_id'    => $userId,
                'product_id' => $product->id,
                'quantity'   => $qty,
            ]);
            \Log::info('Cart item created', ['cart_item_id' => $newItem->id, 'user_id' => $userId, 'product_id' => $product->id]);
        }

        // Clear cart count cache
        \Cache::forget('cart_count_' . $userId);

        // Get updated cart count
        $cartCount = Cart::where('user_id', $userId)->sum('quantity');
        
        \Log::info('Cart updated', ['user_id' => $userId, 'cart_count' => $cartCount]);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
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
        $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
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

        // Manually load products to ensure they're loaded
        foreach ($cartItems as $item) {
            if (!$item->product) {
                $item->load('product');
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
        \Log::info('CartController@update called', ['id' => $id, 'is_json' => $request->wantsJson(), 'is_ajax' => $request->isXmlHttpRequest()]);
        
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
        $cartItem = Cart::with('product')
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
            $allCartItems = Cart::with('product')->where('user_id', Auth::id())->get();
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
    public function checkout()
    {
        // Check if "Buy Now" item exists in session
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
            $cartItems = Cart::with('product')
                            ->where('user_id', Auth::id())
                            ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
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
                    'product_id' => $product->id,
                    'quantity' => $buyNowItem['quantity'],
                    'product' => $product,
                ]
            ]);
        } else {
            // Regular cart checkout
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                return redirect()->back()->with('error', 'Your cart is empty.');
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

        $totalAmount = max(0, $subtotal - $discount);

        // Build delivery address string (only for delivery type)
        $deliveryAddress = null;
        $userAddressId = null;
        
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
        } else {
            $deliveryAddress = 'Store Pickup';
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
        
        $order = Order::create([
            'user_id'           => $userId,
            'customer_name'     => $user->name,
            'customer_email'    => $user->email,
            'customer_phone'    => $user->phone ?? '',
            'subtotal'          => $subtotal,
            'shipping_fee'      => 0,
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
        if (session()->has('buy_now_item')) {
            session()->forget('buy_now_item');
        } else {
            Cart::where('user_id', $userId)->delete();
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

        // Redirect based on payment method
        if ($request->payment_method === 'online') {
            return redirect()->route('payment.online', $order->id)
                             ->with('success', 'Order placed! Complete payment online.');
        }

        return redirect()->route('payment.bank', $order->id)
                         ->with('success', 'Order placed! Complete bank payment.');
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
            try {
                $request->validate([
                    'receipt' => 'required|image|max:5000', // 5MB max
                ]);

                // Upload image
                $path = $request->file('receipt')->store('bank_receipts', 'uploads');
                \Log::info('Bank receipt uploaded', ['path' => $path, 'order_id' => $orderId]);

                $order->payment_status = 'paid';
                $order->payment_verified_at = now();
                $order->bank_receipt = $path;
                
                if ($order->status === 'pending_confirmation') {
                    $order->status = 'confirmed';
                }
                
                $order->appendTrackingEvent('Bank receipt uploaded - Payment verified');
                $order->save();
                
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

                return redirect()->route('orders.show', $orderId)
                                 ->with('success', 'Bank payment verified! Your order is now being processed.');
            } catch (\Exception $e) {
                \Log::error('Error processing bank payment', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return redirect()->route('orders.show', $orderId)
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
            $path = $request->file('receipt')->store('bank_receipts', 'uploads');
            \Log::info('Bank receipt uploaded', ['path' => $path, 'order_id' => $orderId]);

            $order->payment_status = 'paid';
            $order->payment_verified_at = now();
            $order->bank_receipt = $path;
            
            if ($order->status === 'pending_confirmation') {
                $order->status = 'confirmed';
            }
            
            $order->appendTrackingEvent('Bank receipt uploaded - Payment verified');
            $order->save();
            
            \Log::info('Bank payment submitted successfully', [
                'order_id' => $orderId,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ]);

            return redirect()->route('orders.show', $orderId)
                             ->with('success', 'Bank payment verified! Your order is now being processed.');
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
            
            // Update order status if pending
            if ($order->status === 'pending_confirmation') {
                $order->status = 'confirmed';
            }

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

            return redirect()->route('orders.show', $orderId)
                             ->with('success', 'GCash payment verified! Your order is now being processed.');
        } catch (\Exception $e) {
            \Log::error('Error processing payment', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('orders.show', $orderId)
                             ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
}
