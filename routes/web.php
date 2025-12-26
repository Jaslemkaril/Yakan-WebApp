<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Fallback route for storage files when symlink doesn't exist
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('storage.fallback');

// Admin login routes
Route::get('/admin/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'showLoginForm'])->name('admin.login.form');
Route::post('/admin/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'login'])->name('admin.login.submit');

// Admin dashboard route
Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware('auth:admin')->name('admin.dashboard');

// Regular user login routes
Route::get('/login', function() {
    if (Auth::guard('web')->check()) {
        return redirect('/dashboard');
    }
    
    $controller = new App\Http\Controllers\Auth\AuthenticatedSessionController();
    return $controller->create();
})->name('login');

Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'storeUser']);

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;

// Auth Controllers
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Admin Controllers  
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AdminCustomOrderController;
use App\Http\Controllers\Admin\CustomOrderAnalyticsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Welcome / Landing Page
|--------------------------------------------------------------------------
*/
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/live', [SearchController::class, 'liveSearch'])->name('search.live');
Route::get('/contact', [WelcomeController::class, 'contact'])->name('contact');
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Admin Creation Route (Keep for initial setup)
|--------------------------------------------------------------------------
*/
Route::get('/create-admin', function () {
    $existingAdmin = \App\Models\User::where('email', 'admin@yakan.com')->first();
    
    if ($existingAdmin) {
        return response()->json([
            'message' => 'Admin user already exists!',
            'credentials' => [
                'email' => 'admin@yakan.com',
                'password' => 'admin123',
                'login_url' => route('admin.login.form')
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
        'message' => 'Admin created successfully!',
        'credentials' => [
            'email' => 'admin@yakan.com',
            'password' => 'admin123',
            'login_url' => route('admin.login.form')
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    // User Login
    Route::get('/login-user', fn() => view('auth.user-login'))->name('login.user.form');
    Route::post('/login-user', [AuthenticatedSessionController::class, 'storeUser'])->name('login.user.submit');

    // User Registration
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    // OTP Verification Routes
    Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpVerificationController::class, 'showForm'])->name('verification.otp.form');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpVerificationController::class, 'verify'])->name('verification.otp.verify');
    Route::post('/resend-otp', [\App\Http\Controllers\Auth\OtpVerificationController::class, 'resend'])->name('verification.otp.resend');

    /*
    |--------------------------------------------------------------------------
    | Password Reset Routes
    |--------------------------------------------------------------------------
    */
});

// Admin Authentication (accessible even if logged in as a regular user)
Route::middleware('guest:admin')->group(function () {
    // Legacy/alternative path redirect to admin login
    Route::redirect('/login/admin', '/admin/login')->name('admin.login.legacy');
});

// Logout
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->middleware('auth:admin')->name('admin.logout');

/*
|--------------------------------------------------------------------------
| OAuth Routes
|--------------------------------------------------------------------------
*/
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.callback');

// OAuth Sandbox Routes (for testing without real credentials)
Route::get('/auth/{provider}/sandbox', [SocialAuthController::class, 'sandbox'])->name('auth.social.sandbox');
Route::post('/auth/{provider}/sandbox', [SocialAuthController::class, 'sandboxLogin'])->name('auth.social.sandbox.login');

// Debug route for testing OAuth configuration
Route::get('/debug/oauth', function() {
    return response()->json([
        'google_client_id' => config('services.google.client_id'),
        'google_client_secret' => config('services.google.client_secret') ? 'SET' : 'NOT SET',
        'google_redirect' => config('services.google.redirect'),
        'facebook_client_id' => config('services.facebook.client_id'),
        'facebook_client_secret' => config('services.facebook.client_secret') ? 'SET' : 'NOT SET',
        'facebook_redirect' => config('services.facebook.redirect'),
    ]);
});

// Legacy routes for backward compatibility
Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/auth/facebook', [SocialAuthController::class, 'redirect'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [SocialAuthController::class, 'callback'])->name('auth.facebook.callback');

/*
|--------------------------------------------------------------------------
| Dashboard Redirect - OPTIMIZED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Addresses
    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AddressController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AddressController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\AddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [\App\Http\Controllers\AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [\App\Http\Controllers\AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [\App\Http\Controllers\AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [\App\Http\Controllers\AddressController::class, 'setDefault'])->name('setDefault');
        
        // API endpoints
        Route::get('/api/default', [\App\Http\Controllers\AddressController::class, 'getDefault'])->name('api.default');
        Route::get('/api/all', [\App\Http\Controllers\AddressController::class, 'getAll'])->name('api.all');
    });

    // Cart & Checkout
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('/add/{product}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.destroy');
        Route::patch('/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');

        Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::match(['post','patch'],'/checkout/process', [CartController::class, 'processCheckout'])->name('cart.checkout.process');

        // Coupons
        Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
        Route::delete('/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
    });

    // Payments
    Route::prefix('payment')->group(function () {
        Route::get('/online/{order}', [CartController::class, 'showOnlinePayment'])->name('payment.online');
        Route::match(['get', 'post'], '/bank/{order}', [CartController::class, 'showBankPayment'])->name('payment.bank');
        Route::post('/process/{order}', [CartController::class, 'processPayment'])->name('payment.process');
        Route::get('/success/{order}', [CartController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/failed/{order}', [CartController::class, 'paymentFailed'])->name('payment.failed');
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/{order}/confirm-received', [OrderController::class, 'confirmReceived'])->name('orders.confirm-received');
        Route::get('/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('orders.invoice');
    });

    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/order/{order}', [ReviewController::class, 'createForOrder'])->name('create.order');
        Route::post('/order-item/{orderItem}', [ReviewController::class, 'storeForOrderItem'])->name('store.order-item');
        Route::get('/custom-order/{customOrder}', [ReviewController::class, 'createForCustomOrder'])->name('create.custom-order');
        Route::post('/custom-order/{customOrder}', [ReviewController::class, 'storeForCustomOrder'])->name('store.custom-order');
        Route::get('/product/{product}', [ReviewController::class, 'showProductReviews'])->name('product');
        Route::post('/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('helpful');
        Route::post('/{review}/unhelpful', [ReviewController::class, 'markUnhelpful'])->name('unhelpful');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [NotificationController::class, 'clear'])->name('clear');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });

    // Chat
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ChatController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ChatController::class, 'store'])->name('store');
        Route::get('/{chat}', [\App\Http\Controllers\ChatController::class, 'show'])->name('show');
        Route::post('/{chat}/message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send-message');
        Route::post('/{chat}/close', [\App\Http\Controllers\ChatController::class, 'close'])->name('close');
    });

    // Redirect old colors route to pattern selection
    Route::get('/custom-orders/create/colors', function() {
        return redirect()->route('custom_orders.create.pattern')
            ->with('info', 'The color customization step has been simplified. Please select a pattern instead.');
    });

    // Public Patterns
Route::get('/patterns', [\App\Http\Controllers\PatternController::class, 'index'])->name('patterns.index');
Route::get('/patterns/{pattern}', [\App\Http\Controllers\PatternController::class, 'show'])->name('patterns.show');

// Wishlist (User)
Route::middleware(['auth'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [\App\Http\Controllers\WishlistController::class, 'index'])->name('index');
    Route::post('/add', [\App\Http\Controllers\WishlistController::class, 'add'])->name('add');
    Route::post('/remove', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('remove');
    Route::post('/check', [\App\Http\Controllers\WishlistController::class, 'check'])->name('check');
});

// Test auth debugging
Route::get('/test-auth', function() {
    return 'Auth test - User ID: ' . (auth()->check() ? auth()->id() : 'Not authenticated') . ' - Session ID: ' . session()->getId();
});



// Custom Orders (Enhanced) - Require Authentication
Route::middleware(['auth'])->prefix('custom-orders')->name('custom_orders.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomOrderController::class, 'userIndex'])->name('index');
    
    // Redirect /create to fabric selection (step 1)
    Route::get('/create', function() {
        return redirect()->route('custom_orders.create.step1');
    })->name('create');
    
    // Test route for debugging auth
    Route::get('/test-auth', function() {
        return 'Auth test works! User ID: ' . auth()->id();
    });
    
    // Test route for debugging 419 error
    Route::post('/test-csrf', function() {
        return response()->json(['success' => true, 'message' => 'CSRF test passed']);
    })->name('test.csrf');
    
    // Test GET route to bypass CSRF
    Route::get('/test-get', function() {
        return response()->json(['success' => true, 'message' => 'GET test passed']);
    })->name('test.get');
    
    // Pattern/Fabric Design Flow
    Route::get('/create/step1', [\App\Http\Controllers\CustomOrderController::class, 'createStep1'])->name('create.step1');
    Route::post('/create/step1', [\App\Http\Controllers\CustomOrderController::class, 'storeStep1'])->name('store.step1');
    
    // NEW: Image Upload Step (Step 2)
    Route::get('/create/image-upload', [\App\Http\Controllers\CustomOrderController::class, 'createImageUpload'])->name('create.image');
    Route::post('/create/image-upload', [\App\Http\Controllers\CustomOrderController::class, 'storeImage'])->name('store.image');
    
    Route::get('/create/step2', [\App\Http\Controllers\CustomOrderController::class, 'createStep2'])->name('create.step2');
    Route::post('/create/step2', [\App\Http\Controllers\CustomOrderController::class, 'storeStep2'])->name('store.step2');
    Route::get('/create/step3', [\App\Http\Controllers\CustomOrderController::class, 'createStep3'])->name('create.step3');
    Route::post('/create/step3', [\App\Http\Controllers\CustomOrderController::class, 'storeStep3'])->name('store.step3');
    Route::get('/restore', [\App\Http\Controllers\CustomOrderController::class, 'restoreWizard'])->name('create.restore');
    Route::get('/create/step4', [\App\Http\Controllers\CustomOrderController::class, 'createStep4'])->name('create.step4');
    Route::post('/create/complete', [\App\Http\Controllers\CustomOrderController::class, 'completeWizard'])->name('complete.wizard');
    Route::get('/success/{order}', [\App\Http\Controllers\CustomOrderController::class, 'success'])->name('success');
    
    // Pattern-Based Approach (Fabric Flow)
    Route::get('/create/pattern', [\App\Http\Controllers\CustomOrderController::class, 'createPatternSelection'])->name('create.pattern');
    Route::post('/create/pattern', [\App\Http\Controllers\CustomOrderController::class, 'storePattern'])->name('store.pattern');
    
    
    Route::get('/{order}', [\App\Http\Controllers\CustomOrderController::class, 'show'])->name('show');
    
    // User decision on quoted price
    Route::post('/{order}/accept', [\App\Http\Controllers\CustomOrderController::class, 'acceptQuote'])->name('accept');
    Route::post('/{order}/reject', [\App\Http\Controllers\CustomOrderController::class, 'rejectQuote'])->name('reject');
    
    // Payment routes
    Route::get('/{order}/payment', [\App\Http\Controllers\CustomOrderController::class, 'payment'])->name('payment');
    Route::post('/{order}/payment', [\App\Http\Controllers\CustomOrderController::class, 'processPayment'])->name('payment.process');
    Route::get('/{order}/payment/instructions', [\App\Http\Controllers\CustomOrderController::class, 'paymentInstructions'])->name('payment.instructions');
    Route::get('/{order}/payment/confirm', [\App\Http\Controllers\CustomOrderController::class, 'paymentConfirm'])->name('payment.confirm');
    
    // Confirm order received
    Route::post('/{order}/confirm-received', [\App\Http\Controllers\CustomOrderController::class, 'confirmReceived'])->name('confirm_received');
    Route::post('/{order}/payment/confirm', [\App\Http\Controllers\CustomOrderController::class, 'paymentConfirmProcess'])->name('payment.confirm.process');
    
    // Legacy routes
    Route::patch('/{order}/respond', [\App\Http\Controllers\CustomOrderController::class, 'respondToQuote'])->name('respond');
    Route::post('/{order}/cancel', [\App\Http\Controllers\CustomOrderController::class, 'cancel'])->name('cancel');
    Route::get('/load-progress', [\App\Http\Controllers\CustomOrderController::class, 'loadProgress'])->name('custom_orders.load_progress');
    
    // Analytics for users
    Route::get('/analytics', [\App\Http\Controllers\CustomOrderController::class, 'userAnalytics'])->name('custom_orders.user_analytics');
});

    // Reviews
Route::prefix('products/{product}/reviews')->name('reviews.')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::get('/create', [ReviewController::class, 'create'])->name('create');
    Route::post('/', [ReviewController::class, 'store'])->name('store');
    Route::patch('/{review}/helpful', [ReviewController::class, 'helpful'])->name('helpful');
    Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
    Route::patch('/{review}', [ReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
});

});

// Track Order - Redirect old routes to new implementation
Route::get('/track', function() {
    return redirect()->route('track-order.index');
});
Route::get('/track/{trackingNumber}', function($trackingNumber) {
    return redirect()->route('track-order.show', $trackingNumber);
});

// Simple test route at the top level (no middleware)
Route::get('/simple-test', function() {
    return response()->json(['success' => true, 'message' => 'Simple test works']);
});

// Isolated test route - no middleware, no admin prefix
Route::get('/isolated-test', function() {
    return 'Isolated test works!';
});

// Debug route for admin authentication
Route::get('/debug-admin-auth', function () {
    return response()->json([
        'web_authenticated' => Auth::guard('web')->check(),
        'admin_authenticated' => Auth::guard('admin')->check(),
        'web_user' => Auth::guard('web')->user(),
        'admin_user' => Auth::guard('admin')->user(),
        'session_id' => session()->getId(),
        'all_session' => session()->all()
    ]);
});

// Test admin authentication
Route::get('/test-admin-auth', function () {
    return response()->json([
        'admin_guard_check' => Auth::guard('admin')->check(),
        'web_guard_check' => Auth::guard('web')->check(),
        'admin_user' => Auth::guard('admin')->user(),
        'web_user' => Auth::guard('web')->user(),
        'session_data' => session()->all()
    ]);
});

// Test admin dashboard access
Route::get('/test-admin-dashboard', function () {
    if (!Auth::guard('admin')->check()) {
        return 'Admin not authenticated';
    }
    
    $user = Auth::guard('admin')->user();
    return 'Admin authenticated: ' . $user->email . ' (Role: ' . $user->role . ')';
})->middleware('auth:admin');

// Test dashboard without auth
Route::get('/test-dashboard-view', function() {
    try {
        return view('admin.dashboard', [
            'totalOrders' => 0,
            'pendingOrders' => 0,
            'completedOrders' => 0,
            'totalUsers' => 0,
            'totalRevenue' => 0,
            'recentOrders' => collect([]),
            'recentUsers' => collect(),
            'topProducts' => collect(),
            'ordersByStatus' => [],
            'totalProducts' => 0,
            'allSalesData' => collect()
        ]);
    } catch (\Exception $e) {
        return 'View Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine();
    }
});

// Debug route for dashboard without auth
Route::get('/debug-dashboard', function() {
    try {
        return view('admin.dashboard', [
            'totalOrders' => 10,
            'pendingOrders' => 3,
            'completedOrders' => 7,
            'totalUsers' => 25,
            'totalRevenue' => 15000,
            'recentOrders' => collect([
                (object)['id' => 1, 'user_name' => 'John Doe', 'amount' => 500, 'status' => 'completed', 'created_at' => '2 hours ago'],
                (object)['id' => 2, 'user_name' => 'Jane Smith', 'amount' => 300, 'status' => 'pending', 'created_at' => '5 hours ago'],
            ]),
            'recentUsers' => collect(),
            'topProducts' => collect(),
            'ordersByStatus' => [],
            'totalProducts' => 15,
            'allSalesData' => collect()
        ]);
    } catch (\Exception $e) {
        return 'Debug Dashboard Error: ' . $e->getMessage() . '<br>File: ' . $e->getFile() . '<br>Line: ' . $e->getLine();
    }
});

// Test custom orders without auth (temporary)
Route::get('/test-custom-orders', 'App\Http\Controllers\Admin\AdminCustomOrderController@index');

// Simple test to verify controller
Route::get('/test-controller', function() {
    try {
        $controller = new App\Http\Controllers\Admin\AdminCustomOrderController();
        return 'Controller loaded successfully';
    } catch (\Exception $e) {
        return 'Controller error: ' . $e->getMessage();
    }
});

// Test with DashboardController directly
Route::get('/direct-test', [App\Http\Controllers\Admin\DashboardController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
// Admin Routes - Protected by admin authentication and role check
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

    // Test route for debugging
    Route::get('/test', function() {
        return 'Admin test route works!';
    })->name('admin.test');
    
        Route::get('/test-dashboard', [DashboardController::class, 'test'])->name('admin.test_dashboard');

    // **Metrics API for Axios charts**
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');

    // Product Management
    Route::resource('products', AdminProductController::class);
    
    // Category Management (AJAX)
    Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    
    // Pattern Management
    Route::resource('patterns', \App\Http\Controllers\Admin\PatternController::class);
    Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulkDelete');

    // Cultural Heritage Management
    Route::resource('cultural-heritage', \App\Http\Controllers\Admin\CulturalHeritageController::class);
    Route::post('/cultural-heritage/{id}/toggle-status', [\App\Http\Controllers\Admin\CulturalHeritageController::class, 'toggleStatus'])->name('cultural-heritage.toggleStatus');

    // Orders (Main Orders Page - Enhanced Custom Orders)
    Route::get('/custom-orders-dashboard', [AdminCustomOrderController::class, 'indexEnhanced'])->name('orders.index');

    // Orders (Regular Orders)
    Route::prefix('orders')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('regular.index');
        Route::get('/create', [AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('/', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
        Route::post('/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.update_tracking');
        Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
        Route::patch('/{order}/update-notes', [AdminOrderController::class, 'updateNotes'])->name('orders.update-notes');
        Route::get('/{order}/invoice', [AdminOrderController::class, 'generateInvoice'])->name('orders.invoice');
        Route::post('/bulk-update', [AdminOrderController::class, 'bulkUpdate'])->name('orders.bulkUpdate');

        // Quick update status route (accept POST and PUT)
        Route::match(['post', 'put'], '/{order}/quick-update-status', [AdminOrderController::class, 'quickUpdateStatus'])->name('orders.quickUpdateStatus');

        });

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::patch('/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::patch('/{inventory}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::get('/low-stock', [InventoryController::class, 'lowStockAlerts'])->name('inventory.low-stock');
        Route::get('/report', [InventoryController::class, 'report'])->name('inventory.report');
    });

    // Custom Orders - Clean Implementation
    Route::prefix('custom-orders')->name('custom-orders.')->group(function () {
        // Main index page
        Route::get('/', [AdminCustomOrderController::class, 'index'])->name('index');
        
        // Create new custom order
        Route::get('/create', [AdminCustomOrderController::class, 'create'])->name('create');
        Route::post('/create', [AdminCustomOrderController::class, 'store'])->name('store');
        
        // View and edit individual orders
        Route::get('/{order}', [AdminCustomOrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [AdminCustomOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [AdminCustomOrderController::class, 'update'])->name('update');
        
        // Order status management
        Route::post('/{order}/approve', [AdminCustomOrderController::class, 'approve'])->name('approve');
        Route::post('/{order}/reject', [AdminCustomOrderController::class, 'reject'])->name('reject');
        Route::post('/{order}/complete', [AdminCustomOrderController::class, 'markCompleted'])->name('complete');
        
        // Price and payment
        Route::post('/{order}/set-price', [AdminCustomOrderController::class, 'setPrice'])->name('set-price');
        Route::post('/{order}/verify-payment', [AdminCustomOrderController::class, 'verifyPayment'])->name('verify-payment');
        
        // Bulk operations
        Route::post('/bulk-approve', [AdminCustomOrderController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [AdminCustomOrderController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/bulk-delete', [AdminCustomOrderController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Export and analytics
        Route::get('/export', [AdminCustomOrderController::class, 'exportOrders'])->name('export');
        Route::get('/analytics', [CustomOrderAnalyticsController::class, 'index'])->name('analytics');
    });

    // Reports & Analytics
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/export/sales', [ReportController::class, 'exportSales'])->name('reports.export.sales');
        Route::get('/export/inventory', [ReportController::class, 'exportInventory'])->name('reports.export.inventory');
        Route::get('/metrics', [ReportController::class, 'realTimeMetrics'])->name('reports.metrics');
    });

    // Promotions - Coupons
    Route::resource('coupons', AdminCouponController::class)->names('coupons');
    Route::post('coupons/{coupon}/toggle', [AdminCouponController::class, 'toggle'])->name('coupons.toggle');

    // Analytics (using dashboard controller)
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::patch('/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
    });

    // Analytics & Reports
    Route::prefix('analytics')->group(function () {
        Route::get('/', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/sales', [DashboardController::class, 'salesReport'])->name('analytics.sales');
        Route::get('/products', [DashboardController::class, 'productsReport'])->name('analytics.products');
        Route::get('/users', [DashboardController::class, 'usersReport'])->name('analytics.users');
        Route::get('/export/{type}', [DashboardController::class, 'exportReport'])->name('analytics.export');
    });

    // System Settings
    Route::prefix('settings')->group(function () {
        Route::get('/general', [DashboardController::class, 'generalSettings'])->name('settings.general');
        Route::post('/general', [DashboardController::class, 'updateGeneralSettings'])->name('settings.general.update');
        Route::get('/payment', [DashboardController::class, 'paymentSettings'])->name('settings.payment');
        Route::post('/payment', [DashboardController::class, 'updatePaymentSettings'])->name('settings.payment.update');
        Route::get('/email', [DashboardController::class, 'emailSettings'])->name('settings.email');
        Route::post('/email', [DashboardController::class, 'updateEmailSettings'])->name('settings.email.update');
    });

    // Custom Orders Management
    Route::get('custom-orders', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'index'])->name('custom_orders.index');
    
    // View individual order - ADMIN SPECIFIC PATH
    Route::get('custom-orders/view/{order}', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'show'])->name('custom_orders.show');
    
    // Production Dashboard (must be before dynamic routes)
    Route::get('custom-orders/production-dashboard', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'productionDashboard'])->name('custom_orders.production-dashboard');
    
    // Export (must be before dynamic routes)
    Route::get('custom-orders/export', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'exportOrders'])->name('custom_orders.export');
    
    // Dynamic routes
    Route::post('custom-orders/{order}/update-status', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'updateStatus'])->name('custom_orders.update_status');
    Route::post('custom-orders/{order}/quote-price', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'quotePrice'])->name('custom_orders.quote_price');
    Route::post('custom-orders/{order}/verify-payment', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'verifyPayment'])->name('custom_orders.verify_payment');
    Route::post('custom-orders/{order}/reject', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'rejectOrder'])->name('custom_orders.reject');
    Route::post('custom-orders/{order}/approve', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'approveOrder'])->name('custom_orders.approve');
    Route::delete('custom-orders/{order}', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'destroy'])->name('custom_orders.delete');

    // Custom Order Creation
    Route::get('custom-orders/create', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'create'])->name('custom_orders.create');
    Route::get('custom-orders/create/choice', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createChoice'])->name('custom_orders.create.choice');
    Route::get('custom-orders/create/product', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createProductSelection'])->name('custom_orders.create.product');
    Route::get('custom-orders/create/fabric', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createFabricSelection'])->name('custom_orders.create.fabric');
    Route::post('custom-orders/create/product', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'storeProductSelection'])->name('custom_orders.store.product');
    Route::get('custom-orders/create/product/customize', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createProductCustomization'])->name('custom_orders.create.product.customize');
    Route::post('custom-orders/create/product/customize', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'storeProductCustomization'])->name('custom_orders.store.product.customization');
    Route::post('custom-orders/create/fabric', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'storeFabricSelection'])->name('custom_orders.store.fabric');
    Route::get('custom-orders/create/pattern', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createPatternSelection'])->name('custom_orders.create.pattern');
    Route::post('custom-orders/create/pattern', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'storePatternSelection'])->name('custom_orders.store.pattern');
    Route::get('custom-orders/create/review', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'createReview'])->name('custom_orders.create.review');
    Route::post('custom-orders', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'store'])->name('custom_orders.store');
    
    // Production Dashboard
    Route::get('custom-orders/production-dashboard', [App\Http\Controllers\Admin\AdminCustomOrderController::class, 'productionDashboard'])->name('custom_orders.production-dashboard');

    // Chat Management
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('index');
        Route::get('/{chat}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('show');
        Route::post('/{chat}/reply', [\App\Http\Controllers\Admin\ChatController::class, 'sendReply'])->name('reply');
        Route::patch('/{chat}/status', [\App\Http\Controllers\Admin\ChatController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{chat}', [\App\Http\Controllers\Admin\ChatController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [\App\Http\Controllers\Admin\ChatController::class, 'unreadCount'])->name('unread-count');
    });
});

/*
|--------------------------------------------------------------------------
| Public Shop Routes
|--------------------------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'shopIndex'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/category/{category}', [ProductController::class, 'byCategory'])->name('products.category');

/*
|--------------------------------------------------------------------------
| Cultural Heritage Routes
|--------------------------------------------------------------------------
*/
Route::get('/cultural-heritage', [\App\Http\Controllers\CulturalHeritageController::class, 'index'])->name('cultural-heritage.index');
Route::get('/cultural-heritage/{slug}', [\App\Http\Controllers\CulturalHeritageController::class, 'show'])->name('cultural-heritage.show');

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks')->group(function () {
    Route::post('/paymongo', [App\Http\Controllers\Webhooks\PayMongoWebhookController::class, 'handleWebhook'])->name('webhooks.paymongo');
    Route::post('/gcash', [App\Http\Controllers\Webhooks\PayMongoWebhookController::class, 'handleWebhook'])->name('webhooks.gcash');
});

/*
|--------------------------------------------------------------------------
| File Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('files')->group(function () {
    Route::get('/download/{path}', [App\Http\Controllers\FileController::class, 'download'])->name('files.download');
    Route::post('/upload', [App\Http\Controllers\FileController::class, 'upload'])->name('files.upload');
    Route::delete('/{path}', [App\Http\Controllers\FileController::class, 'delete'])->name('files.delete');
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/
Route::prefix('payment')->group(function () {
    Route::get('/return/{gateway}', [PaymentController::class, 'paymentReturn'])->name('payment.return');
    Route::post('/webhook/{gateway}', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');
    Route::get('/status/{order}', [PaymentController::class, 'checkPaymentStatus'])->name('payment.status');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

// Debug route for wizard/step-2 requests
Route::get('/custom-orders/wizard/step-2', function (Illuminate\Http\Request $request) {
    \Log::error('Debug: Received request to old wizard/step-2 URL', [
        'url' => $request->fullUrl(),
        'referer' => $request->header('referer'),
        'user_agent' => $request->header('user-agent'),
        'ip' => $request->ip(),
        'method' => $request->method(),
        'all_headers' => $request->headers->all()
    ]);
    
    return redirect('/custom-orders/create/step2', 301);
});

// Simple test route
Route::get('/simple-test', function() {
    return 'Simple test works!';
});

// Test success route
Route::get('/test-success/{orderId}', function($orderId) {
    try {
        $order = \App\Models\CustomOrder::findOrFail($orderId);
        return view('custom_orders.success', compact('order'));
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Test notification route
Route::get('/test-notification', function() {
    if (!auth()->check()) {
        return 'Please login to test notifications';
    }
    
    \App\Models\Notification::createNotification(
        auth()->id(),
        'system',
        'Test Notification',
        'This is a test notification to verify the system is working!',
        '/notifications',
        ['test' => true]
    );
    
    return 'Test notification created! Check your notifications.';
});

// Test routes for debugging
Route::get('/test-custom-orders', function() {
    try {
        return app('App\Http\Controllers\Admin\AdminCustomOrderController')->index(request());
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/test-controller', function() {
    try {
        $controller = new App\Http\Controllers\Admin\AdminCustomOrderController();
        return 'Controller loaded successfully';
    } catch (\Exception $e) {
        return 'Controller error: ' . $e->getMessage();
    }
});

Route::get('/test-sandbox', function() {
    try {
        $controller = new App\Http\Controllers\SandboxPaymentController(app(App\Services\Payment\SandboxPaymentService::class));
        return 'Sandbox controller loaded successfully';
    } catch (\Exception $e) {
        return 'Sandbox controller error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    }
});

Route::get('/test-sandbox-simple', function() {
    try {
        return 'Testing sandbox service...';
        $service = new App\Services\Payment\SandboxPaymentService();
        return 'Sandbox service created successfully';
    } catch (\Exception $e) {
        return 'Sandbox service error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    }
});

// Payment Sandbox Routes
Route::prefix('payment/sandbox')->name('payment.sandbox.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SandboxPaymentController::class, 'dashboard'])->name('dashboard');
    Route::post('/create/{order}', [App\Http\Controllers\SandboxPaymentController::class, 'createPayment'])->name('create');
    Route::post('/simulate', [App\Http\Controllers\SandboxPaymentController::class, 'simulatePayment'])->name('simulate');
    Route::post('/gcash/simulate', [App\Http\Controllers\SandboxPaymentController::class, 'simulateGCashPayment'])->name('gcash.simulate');
    Route::post('/card/simulate', [App\Http\Controllers\SandboxPaymentController::class, 'simulateCardPayment'])->name('card.simulate');
    Route::post('/webhook/{gateway}', [App\Http\Controllers\SandboxPaymentController::class, 'handleWebhook'])->name('webhook');
    Route::get('/redirect/{method}', [App\Http\Controllers\SandboxPaymentController::class, 'handleRedirect'])->name('redirect');
    Route::post('/bank-instructions/{order}', [App\Http\Controllers\SandboxPaymentController::class, 'generateBankInstructions'])->name('bank.instructions');
    Route::post('/bank-verify', [App\Http\Controllers\SandboxPaymentController::class, 'verifyBankTransfer'])->name('bank.verify');
    Route::post('/generate-test-data', [App\Http\Controllers\SandboxPaymentController::class, 'generateTestData'])->name('generate-data');
    Route::delete('/clear', [App\Http\Controllers\SandboxPaymentController::class, 'clearSandboxData'])->name('clear');
});

// Track Order (Public - No Auth Required)
Route::prefix('track-order')->name('track-order.')->group(function () {
    Route::get('/', [App\Http\Controllers\TrackOrderController::class, 'index'])->name('index');
    Route::post('/search', [App\Http\Controllers\TrackOrderController::class, 'search'])->name('search');
    Route::get('/{trackingNumber}', [App\Http\Controllers\TrackOrderController::class, 'show'])->name('show');
    Route::get('/{trackingNumber}/history', [App\Http\Controllers\TrackOrderController::class, 'getHistory'])->name('history');
});

// Fallback 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
