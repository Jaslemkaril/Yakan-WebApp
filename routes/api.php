<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CulturalHeritageController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\MayaPaymentController;
use App\Http\Controllers\Api\PaymongoPaymentController;

Route::prefix('v1')->group(function () {
    // ===================== PUBLIC SETTINGS =====================
    Route::get('/settings/payment-info', [SettingsController::class, 'paymentInfo']);
    Route::post('/payments/maya/webhook', [MayaPaymentController::class, 'webhook']);

    // ===================== AUTHENTICATION (Public) =====================
    // Rate limited: 5 attempts per minute from the same IP
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/login-guest', [AuthController::class, 'loginGuest']);
    });
    // Register: 3 attempts per 5 minutes
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,5');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:6,1');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Social Authentication
    Route::post('/auth/google', [SocialAuthController::class, 'googleLogin'])->middleware('throttle:5,1');
    Route::post('/auth/facebook', [SocialAuthController::class, 'facebookLogin'])->middleware('throttle:5,1');

    // ===================== DIAGNOSTIC (Public, temporary) =====================
    Route::get('/diagnostic/logs', function (\Illuminate\Http\Request $request) {
        $lines = (int) ($request->query('lines', 200));
        $lines = max(10, min(1000, $lines));
        $filter = (string) $request->query('filter', '');

        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) {
            return response()->json(['success' => false, 'message' => 'No log file found', 'path' => $logPath]);
        }

        $fileSize = filesize($logPath);
        $chunkSize = min($fileSize, 500 * 1024); // last ~500KB
        $fp = fopen($logPath, 'r');
        fseek($fp, max(0, $fileSize - $chunkSize));
        $content = fread($fp, $chunkSize);
        fclose($fp);

        $rows = array_filter(explode("\n", $content));
        if ($filter !== '') {
            $rows = array_values(array_filter($rows, fn($r) => stripos($r, $filter) !== false));
        }
        $rows = array_slice($rows, -$lines);

        return response()->json([
            'success' => true,
            'file_size_bytes' => $fileSize,
            'log_path' => $logPath,
            'line_count' => count($rows),
            'filter_applied' => $filter,
            'lines' => $rows,
        ]);
    });

    // Latest orders across ALL users — catches the case where the mobile
    // is placing orders under a different auth user than expected.
    Route::get('/diagnostic/latest-orders', function () {
        $orders = \App\Models\Order::with('items.product', 'user')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'server_time' => now()->toDateTimeString(),
            'orders' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_ref' => $o->order_ref,
                'user_id' => $o->user_id,
                'user_email' => $o->user?->email,
                'created_at' => $o->created_at?->toDateTimeString(),
                'subtotal' => (float) ($o->subtotal ?? 0),
                'total_amount' => (float) ($o->total_amount ?? 0),
                'payment_method' => $o->payment_method,
                'payment_status' => $o->payment_status,
                'status' => $o->status,
                'source' => $o->source,
                'notes_excerpt' => substr((string) ($o->notes ?? ''), 0, 200),
                'items_count' => $o->items->count(),
                'items' => $o->items->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'product_name' => $i->product?->name,
                    'price' => (float) $i->price,
                    'quantity' => $i->quantity,
                ])->values(),
            ])->values(),
        ]);
    });

    // Log event to DB for diagnostic purposes (written via custom channel elsewhere).
    Route::get('/diagnostic/events', function (\Illuminate\Http\Request $request) {
        if (!\Illuminate\Support\Facades\Schema::hasTable('diagnostic_events')) {
            return response()->json(['success' => false, 'message' => 'diagnostic_events table does not exist']);
        }
        $filter = (string) $request->query('filter', '');
        $q = \Illuminate\Support\Facades\DB::table('diagnostic_events')->latest();
        if ($filter !== '') {
            $q->where('event', 'like', '%' . $filter . '%');
        }
        $events = $q->limit(100)->get();
        return response()->json([
            'success' => true,
            'count' => $events->count(),
            'events' => $events,
        ]);
    });

    Route::get('/diagnostic/recent-orders/{userId}', function ($userId) {
        $orders = \App\Models\Order::with('items.product')
            ->where('user_id', (int) $userId)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'orders' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_ref' => $o->order_ref,
                'created_at' => $o->created_at?->toDateTimeString(),
                'subtotal' => (float) ($o->subtotal ?? 0),
                'shipping_fee' => (float) ($o->shipping_fee ?? 0),
                'discount' => (float) ($o->discount ?? 0),
                'total_amount' => (float) ($o->total_amount ?? 0),
                'payment_method' => $o->payment_method,
                'payment_status' => $o->payment_status,
                'status' => $o->status,
                'payment_reference' => $o->payment_reference,
                'notes' => $o->notes,
                'items' => $o->items->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'product_name' => $i->product?->name,
                    'variant_id' => $i->variant_id,
                    'price' => (float) $i->price,
                    'quantity' => $i->quantity,
                    'line_total' => (float) $i->price * $i->quantity,
                ])->values(),
            ])->values(),
        ]);
    });

    Route::get('/diagnostic/product-discounts', function () {
        $products = \App\Models\Product::with('variants')->get();
        $result = $products->map(function ($p) {
            $basePrice = (float) $p->price;
            $effective = (float) $p->getDiscountedPrice($basePrice);
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price_column' => $basePrice,
                'discount_type' => $p->discount_type,
                'discount_value' => $p->discount_value ? (float) $p->discount_value : null,
                'discount_starts_at' => $p->discount_starts_at?->toDateTimeString(),
                'discount_ends_at' => $p->discount_ends_at?->toDateTimeString(),
                'has_active_discount' => $p->hasActiveProductDiscount(),
                'effective_unit_price' => $effective,
                'server_now' => now()->toDateTimeString(),
                'variants' => $p->variants->map(fn($v) => [
                    'id' => $v->id,
                    'size' => $v->size,
                    'color' => $v->color,
                    'price_column' => (float) $v->price,
                    'effective_price' => (float) $p->getDiscountedPrice((float) $v->price),
                    'is_active' => (bool) $v->is_active,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'server_time' => now()->toDateTimeString(),
            'products' => $result,
        ]);
    });

    // ===================== PRODUCTS (Public) =====================
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // ===================== CULTURAL HERITAGE (Public) =====================
    Route::get('/cultural-heritage', [CulturalHeritageController::class, 'index']);
    Route::get('/cultural-heritage/categories', [CulturalHeritageController::class, 'categories']);
    Route::get('/cultural-heritage/{slug}', [CulturalHeritageController::class, 'show']);

    // ===================== AUTHENTICATED ROUTES =====================
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Product Reviews (Auth required to post)
        Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
        Route::put('/products/{product}/reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('/products/{product}/reviews/{review}', [ReviewController::class, 'destroy']);

        // Orders (Auth required)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{id}/upload-receipt', [OrderController::class, 'uploadReceipt']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        
        // Payment proof upload (mobile/web)
        Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
        Route::post('/payments/maya/checkout', [MayaPaymentController::class, 'createCheckout']);
        Route::get('/payments/maya/{order}/status', [MayaPaymentController::class, 'status']);
        Route::post('/payments/paymongo/checkout', [PaymongoPaymentController::class, 'createCheckout']);

        // Wishlist
        Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/wishlist/add', [\App\Http\Controllers\Api\WishlistController::class, 'add']);
        Route::post('/wishlist/remove', [\App\Http\Controllers\Api\WishlistController::class, 'remove']);
        Route::post('/wishlist/check', [\App\Http\Controllers\Api\WishlistController::class, 'check']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'add']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Coupon
        Route::get('/coupons/available', [CartController::class, 'getAvailableCoupons']);
        Route::post('/coupon/validate', [CartController::class, 'validateCoupon']);
        Route::post('/coupon/remove', [CartController::class, 'removeCoupon']);

        // Addresses (for mobile app)
        Route::get('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'index']);
        Route::get('/addresses/default', [\App\Http\Controllers\Api\AddressController::class, 'getDefault']);
        Route::post('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'store']);
        Route::put('/addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
        Route::post('/addresses/{address}/set-default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);

        // Chats (for mobile app)
        Route::get('/chats', [ChatController::class, 'index']);
        Route::get('/chats/{id}', [ChatController::class, 'show']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/chats/{id}/respond-quote', [ChatController::class, 'respondToQuote']);
        Route::patch('/chats/{id}/status', [ChatController::class, 'updateStatus']);

        // Custom Orders (for mobile app)
        Route::prefix('custom-orders')->name('custom-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\CustomOrderController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\CustomOrderController::class, 'store'])->name('store');
            Route::get('/{customOrder}', [\App\Http\Controllers\Api\CustomOrderController::class, 'show'])->name('show');
            Route::put('/{customOrder}', [\App\Http\Controllers\Api\CustomOrderController::class, 'update'])->name('update');
            Route::post('/{customOrder}/cancel', [\App\Http\Controllers\Api\CustomOrderController::class, 'cancel'])->name('cancel');
        });

        // Admin Orders
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
    });
});


// ===================== SETUP ROUTES (Remove after initial setup) =====================
Route::get('/setup/create-admin', function () {
    try {
        $existingAdmin = \App\Models\User::where('email', 'admin@yakan.com')->first();
        
        if ($existingAdmin) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Admin user already exists!',
                'credentials' => [
                    'email' => 'admin@yakan.com',
                    'note' => 'Use your existing password'
                ]
            ]);
        }
        
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@yakan.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin user created successfully!',
            'credentials' => [
                'email' => 'admin@yakan.com',
                'password' => 'admin123',
                'note' => 'Please change this password after first login!'
            ],
            'login_url' => url('/admin/login')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create admin',
            'error' => $e->getMessage()
        ], 500);
    }
});
