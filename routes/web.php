<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

// ============================================================
// DEBUG / SETUP ROUTES — Non-production environments only
// ============================================================
if (!app()->isProduction()) {

// DIAGNOSTIC ROUTE - Check Railway environment (non-production only)
Route::get('/railway-health', function () {
    $info = [];
    
    // Check critical environment variables
    $info['app_key'] = env('APP_KEY') ? 'SET (' . strlen(env('APP_KEY')) . ' chars)' : 'NOT SET - CRITICAL!';
    $info['app_env'] = env('APP_ENV', 'not set');
    $info['session_driver'] = config('session.driver');
    
    // MySQL environment variables check
    $info['mysql_env'] = [
        'MYSQL_URL' => env('MYSQL_URL') ? 'SET' : 'NOT SET',
        'DATABASE_URL' => env('DATABASE_URL') ? 'SET' : 'NOT SET',
        'MYSQLHOST' => env('MYSQLHOST') ? 'SET (' . env('MYSQLHOST') . ')' : 'NOT SET',
        'MYSQLPORT' => env('MYSQLPORT') ? 'SET (' . env('MYSQLPORT') . ')' : 'NOT SET',
        'MYSQLDATABASE' => env('MYSQLDATABASE') ? 'SET' : 'NOT SET',
        'MYSQLUSER' => env('MYSQLUSER') ? 'SET' : 'NOT SET',
        'DB_HOST' => env('DB_HOST') ? 'SET (' . env('DB_HOST') . ')' : 'NOT SET',
    ];
    
    // Actual resolved config
    $info['resolved_config'] = [
        'driver' => config('database.connections.mysql.driver'),
        'host' => config('database.connections.mysql.host'),
        'port' => config('database.connections.mysql.port'),
        'database' => config('database.connections.mysql.database'),
        'unix_socket' => config('database.connections.mysql.unix_socket') ?: '(empty - TCP/IP mode)',
        'url' => config('database.connections.mysql.url') ? 'SET' : 'NOT SET',
    ];
    
    // Test database connection
    try {
        DB::connection()->getPdo();
        $info['db_connection'] = 'OK';
    } catch (\Exception $e) {
        $info['db_connection'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Session debugging
    $info['session_id'] = session()->getId() ?: 'none';
    $info['session_config'] = [
        'driver' => config('session.driver'),
        'lifetime' => config('session.lifetime'),
        'cookie' => config('session.cookie'),
        'domain' => config('session.domain') ?: 'null (correct)',
        'secure' => config('session.secure'),
        'same_site' => config('session.same_site'),
    ];
    
    // Test session write
    session()->put('health_test', 'ok');
    $info['session_write'] = session()->get('health_test') === 'ok' ? 'OK' : 'FAILED';
    
    // Check cached config
    $info['config_cached'] = file_exists(base_path('bootstrap/cache/config.php')) ? 'YES - may cause issues!' : 'NO (good)';
    $info['routes_cached'] = file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'YES' : 'NO';
    
    // Check session storage directory
    $sessionPath = storage_path('framework/sessions');
    $info['session_path'] = $sessionPath;
    $info['session_path_exists'] = is_dir($sessionPath) ? 'YES' : 'NO';
    $info['session_path_writable'] = is_writable($sessionPath) ? 'YES' : 'NO';
    
    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});

// Debug login test route
Route::get('/debug/test-login', function () {
    $user = \App\Models\User::where('email', 'user@yakan.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    // Force login
    \Auth::login($user, true);
    
    // Check if login worked
    $loggedIn = \Auth::check();
    $sessionId = session()->getId();
    
    // Store something in session
    session()->put('test_value', 'hello');
    session()->save();
    
    return response()->json([
        'user_id' => $user->id,
        'user_email' => $user->email,
        'auth_check' => $loggedIn,
        'session_id' => $sessionId,
        'session_driver' => config('session.driver'),
        'test_value' => session()->get('test_value'),
        'message' => $loggedIn ? 'Login successful! Now visit /dashboard' : 'Login failed',
    ]);
});

// Debug session persistence check
Route::get('/debug/check-session', function () {
    return response()->json([
        'authenticated' => \Auth::check(),
        'user' => \Auth::user() ? ['id' => \Auth::id(), 'email' => \Auth::user()->email] : null,
        'session_id' => session()->getId(),
        'test_value' => session()->get('test_value', 'NOT SET'),
        'session_driver' => config('session.driver'),
    ]);
});

// Debug cookie information
Route::get('/debug/cookies', function (\Illuminate\Http\Request $request) {
    $data = [
        'received_cookies' => $request->cookies->all(),
        'session_cookie_name' => config('session.cookie'),
        'session_id' => session()->getId(),
        'session_config' => [
            'driver' => config('session.driver'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
            'domain' => config('session.domain'),
            'path' => config('session.path'),
            'http_only' => config('session.http_only'),
        ],
        'headers' => [
            'host' => $request->header('Host'),
            'x-forwarded-proto' => $request->header('X-Forwarded-Proto'),
            'x-forwarded-for' => $request->header('X-Forwarded-For'),
        ],
        'is_secure' => $request->secure(),
    ];
    
    $response = response()->json($data);
    
    // Add test cookies
    $response->withCookie(cookie('test_cookie_1', 'value1', 60, '/', null, false, false));
    $response->withCookie(cookie('test_cookie_2', 'value2', 60, '/', null, false, true));
    
    return $response;
});

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

// Dedicated route for chat images (more reliable than /storage fallback)
Route::get('/chat-image/{folder}/{filename}', function ($folder, $filename) {
    $filePath = storage_path('app/public/' . $folder . '/' . $filename);
    \Log::info('Chat image request', [
        'folder' => $folder,
        'filename' => $filename,
        'full_path' => $filePath,
        'exists' => file_exists($filePath),
    ]);
    
    if (!file_exists($filePath)) {
        \Log::error('Chat image not found', ['path' => $filePath]);
        abort(404, 'Image not found: ' . $filePath);
    }
    
    $mimeType = mime_content_type($filePath);
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('folder', '(chats|payments)')->where('filename', '.*')->name('chat.image');

// Create sessions table route (for Railway setup)
Route::get('/setup/create-sessions-table', function () {
    // Clear route cache first
    Artisan::call('route:cache');
    
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        DB::statement($sql);
        return "✓ Sessions table created successfully!";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            return "✓ Sessions table already exists!";
        }
        return "✗ Error: " . $e->getMessage();
    }
});

} // end non-production debug/setup routes block

// Privacy Policy and Data Deletion Routes (Required for Facebook OAuth)
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/data-deletion', function () {
    return response()->view('data-deletion');
})->name('data-deletion');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

// ============================================================
// Debug / setup routes continued (non-production only)
// ============================================================
if (!app()->isProduction()) {

// Reset admin password route
Route::get('/setup/reset-admin-password', function () {
    $admin = \App\Models\User::where('email', 'admin@yakan.com')->first();
    
    if (!$admin) {
        return "✗ Admin user not found!";
    }
    
    $admin->password = \Hash::make('admin123');
    $admin->save();
    
    return "✓ Admin password reset to: admin123";
});

// Create admin user route (for Railway setup)
Route::get('/setup/create-admin', function () {
    $admin = \App\Models\User::where('email', 'admin@yakan.com')->first();
    
    if ($admin) {
        return "Admin already exists: " . $admin->email . " (Role: " . $admin->role . ")";
    }
    
    $admin = \App\Models\User::create([
        'name' => 'Administrator',
        'email' => 'admin@yakan.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    
    return "✓ Admin created successfully! Email: admin@yakan.com, Password: admin123";
});

// Debug session route
Route::get('/debug/session', function () {
    return response()->json([
        'authenticated' => \Auth::check(),
        'user' => \Auth::user() ? \Auth::user()->toArray() : null,
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
    ]);
});

// Run database migrations (emergency use)
Route::get('/debug/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response()->json(['status' => 'success', 'output' => $output]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

// Test welcome email
Route::get('/test-welcome-email', function () {
    try {
        // Create a test user object (don't save to DB)
        $testUser = new \App\Models\User([
            'name' => 'Test User',
            'email' => request()->get('email', 'coloresdeartes16@gmail.com'),
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);
        
        \Log::info('Attempting to send test welcome email via configured mail transport', ['email' => $testUser->email]);
        
        // Send the welcome email
        \Mail::to($testUser->email)->send(new \App\Mail\WelcomeEmail($testUser));
        
        \Log::info('Test welcome email sent successfully via configured mail transport');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Welcome email sent successfully via configured mail transport!',
            'sent_to' => $testUser->email,
            'mail_config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'username' => config('mail.mailers.smtp.username'),
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Test welcome email failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send email: ' . $e->getMessage(),
            'mail_config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'username' => config('mail.mailers.smtp.username'),
            ]
        ], 500);
    }
});

// Test registration endpoint
Route::get('/test-registration-access', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Registration endpoint is accessible',
        'database' => \DB::connection()->getPdo() ? 'Connected' : 'Not connected',
        'session' => session()->getId(),
        'csrf' => csrf_token(),
    ]);
});

// Test email via configured Laravel mail transport (SMTP/Brevo)
Route::get('/test-mail', function () {
    $email = request()->get('email', 'coloresdeartes16@gmail.com');
    
    $result = \App\Services\TransactionalMailService::send(
        $email,
        'Yakan - Mail Test Email',
        '<h1>Test Email</h1><p>This is a test email from Yakan E-commerce sent via the configured mail transport at ' . now() . '</p>'
    );
    
    return response()->json([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Test email sent to ' . $email : 'Failed to send email - check logs',
        'method' => 'Configured Laravel mail transport',
        'from' => config('mail.from.address'),
        'smtp_password_set' => !empty(config('mail.mailers.smtp.password')),
    ], $result ? 200 : 500);
});

// Test password reset email specifically
Route::get('/test-password-reset-email', function () {
    $email = request()->get('email', 'coloresdeartes16@gmail.com');
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found with email: ' . $email,
        ], 404);
    }
    
    $resetUrl = 'https://yakan-webapp-production.up.railway.app/reset-password/TEST_TOKEN_123?email=' . urlencode($email);
    
    try {
        $result = \App\Services\TransactionalMailService::sendView(
            $user->email,
            'Reset Your Password - Yakan E-commerce',
            'emails.password-reset',
            [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => 'TEST_TOKEN_123',
            ]
        );
        
        return response()->json([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Password reset email sent to ' . $email : 'Failed to send - check logs',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'reset_url' => $resetUrl,
        ], $result ? 200 : 500);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// Check which DB tables exist
Route::get('/debug/db-tables', function () {
    try {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tableNames = array_map(fn($t) => array_values((array)$t)[0], $tables);
        $hasSessionsTable = in_array('sessions', $tableNames);
        return response()->json([
            'tables' => $tableNames,
            'has_sessions_table' => $hasSessionsTable,
            'has_users_table' => in_array('users', $tableNames),
            'has_carts_table' => in_array('carts', $tableNames) || in_array('cart_items', $tableNames),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Raw cookie test — tests if Set-Cookie header survives the Railway proxy
Route::get('/debug/cookie-test', function (\Illuminate\Http\Request $request) {
    $response = response()->json([
        'message' => 'cookie test',
        'cookies_in_request' => array_keys($_COOKIE),
        'has_test_cookie' => isset($_COOKIE['debug_test']),
        'test_cookie_value' => $_COOKIE['debug_test'] ?? null,
    ]);
    $response->headers->setCookie(
        \Symfony\Component\HttpFoundation\Cookie::create(
            'debug_test', 'hello_' . time(), time() + 3600, '/', null, false, false, false, 'lax'
        )
    );
    // Explicitly prevent caching so Railway edge doesn't strip Set-Cookie
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    return $response;
});

// Deep debug: session, cookies, auth state
Route::get('/debug/session-check', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'authenticated' => \Auth::check(),
        'user_id' => \Auth::id(),
        'session_driver' => config('session.driver'),
        'session_id' => session()->getId(),
        'session_cookie_name' => config('session.cookie'),
        'session_secure' => config('session.secure'),
        'session_same_site' => config('session.same_site'),
        'session_domain' => config('session.domain'),
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),
        'request_secure' => $request->secure(),
        'request_scheme' => $request->getScheme(),
        'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
        'cookies_received' => array_keys($_COOKIE),
        'has_session_cookie' => isset($_COOKIE[config('session.cookie')]),
        'has_auth_token_cookie' => isset($_COOKIE['auth_token']),
        'auth_token_cookie_value' => isset($_COOKIE['auth_token']) ? substr($_COOKIE['auth_token'], 0, 12) . '...' : null,
        'session_auth_token' => session('auth_token') ? substr(session('auth_token'), 0, 12) . '...' : null,
        'remember_token' => \Auth::user()?->remember_token ? 'SET' : 'NOT SET',
        'php_sapi' => php_sapi_name(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    ]);
});

// Debug OAuth redirect URIs
Route::get('/debug/oauth-config', function () {
    return response()->json([
        'google' => [
            'client_id' => config('services.google.client_id'),
            'redirect' => config('services.google.redirect'),
        ],
        'facebook' => [
            'client_id' => config('services.facebook.client_id'),
            'redirect' => config('services.facebook.redirect'),
        ],
    ]);
});

// Debug OAuth callback route
Route::get('/debug/oauth-test', function () {
    $lastError = \Log::getMonolog()->getHandlers()[0]->getLastError();
    return response()->json([
        'authenticated' => \Auth::check(),
        'user' => \Auth::user() ? \Auth::user()->toArray() : null,
        'session_id' => session()->getId(),
        'config_loaded' => [
            'google_client_id' => !empty(config('services.google.client_id')),
            'facebook_client_id' => !empty(config('services.facebook.client_id')),
        ],
    ]);
});

// Reset admin password route
Route::get('/setup/reset-admin-password', function () {
    $admin = \App\Models\User::where('email', 'admin@yakan.com')->first();
    
    if (!$admin) {
        return "Admin user not found!";
    }
    
    $admin->password = \Hash::make('admin123');
    $admin->save();
    
    return "✓ Admin password reset to: admin123";
});

// Debug route to check user accounts (TEMPORARY)
Route::get('/debug/check-users', function () {
    $users = \App\Models\User::select('id', 'email', 'name', 'role', 'email_verified_at')->get();
    return response()->json([
        'total_users' => $users->count(),
        'users' => $users->map(fn($u) => [
            'id' => $u->id,
            'email' => $u->email,
            'name' => $u->name,
            'role' => $u->role,
            'verified' => $u->email_verified_at ? 'yes' : 'no',
        ]),
    ], 200, [], JSON_PRETTY_PRINT);
});

// Debug route to reset user password (TEMPORARY)
Route::get('/setup/reset-user-password', function () {
    $user = \App\Models\User::where('email', 'user@yakan.com')->first();
    
    if (!$user) {
        // Create the user
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user@yakan.com',
            'password' => \Hash::make('user123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        return "✓ User created: user@yakan.com / user123";
    }
    
    $user->password = \Hash::make('user123');
    $user->role = 'user'; // Ensure role is correct
    $user->email_verified_at = $user->email_verified_at ?? now();
    $user->save();
    
    return "✓ User password reset: user@yakan.com / user123 (role: {$user->role})";
});

} // end non-production debug/setup routes block (continued)

// Admin login routes
Route::get('/admin/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'showLoginForm'])->name('admin.login.form');
Route::post('/admin/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'login'])->name('admin.login.submit');

// Order staff login routes
Route::get('/staff/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'showStaffLoginForm'])->name('staff.login.form');
Route::post('/staff/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'staffLogin'])->name('staff.login.submit');

// Admin dashboard route
Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware('admin:admin')->name('admin.dashboard');

// Order staff dashboard route
Route::get('/staff/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->middleware('admin:order_staff')->name('staff.dashboard');

// Regular user login routes
Route::get('/login', function() {
    if (Auth::guard('web')->check()) {
        return redirect('/dashboard');
    }
    
    $controller = new App\Http\Controllers\Auth\AuthenticatedSessionController();
    return $controller->createUser();
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

// API documentation route
Route::get('/api/documentation', function() {
    return response()->json([
        'app' => 'Yakan E-commerce API',
        'version' => '1.0.0',
        'status' => 'online',
        'endpoints' => [
            'products' => url('/api/products'),
            'categories' => url('/api/categories'),
            'orders' => url('/api/orders'),
            'custom_orders' => url('/api/custom-orders'),
        ],
        'documentation' => url('/api/documentation'),
    ]);
})->name('api.documentation');

// Health check route (safe, no sensitive details)
Route::get('/health-check', function() {
    try {
        DB::connection()->getPdo();
        $dbOk = true;
    } catch (\Exception $e) {
        $dbOk = false;
    }
    return response()->json([
        'status' => 'ok',
        'database' => $dbOk ? 'connected' : 'unreachable',
    ]);
})->name('health.check');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/live', [SearchController::class, 'liveSearch'])->name('search.live');
Route::get('/contact', [WelcomeController::class, 'contact'])->name('contact');
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Admin Creation Route (non-production only)
|--------------------------------------------------------------------------
*/
if (!app()->isProduction()) {
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
} // end non-production /create-admin

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
// Mobile app OAuth — opens system browser, then redirects back to yakanapp:// deep link
Route::get('/auth/{provider}/mobile', [SocialAuthController::class, 'mobileRedirect'])->name('auth.mobile.redirect');

// OAuth Sandbox Routes (for testing without real credentials)
Route::get('/auth/{provider}/sandbox', [SocialAuthController::class, 'sandbox'])->name('auth.social.sandbox');
Route::post('/auth/{provider}/sandbox', [SocialAuthController::class, 'sandboxLogin'])->name('auth.social.sandbox.login');

// Debug route for testing OAuth configuration (non-production only)
if (!app()->isProduction()) {
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
} // end non-production /debug/oauth

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
        // Profile picture routes
        Route::post('/avatar/upload', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');
        Route::delete('/avatar/delete', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    });

    // Email Verification
    Route::post('/email/verification-notification', [ProfileController::class, 'sendVerificationEmail'])->name('verification.send');

    // Addresses
    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AddressController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AddressController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\AddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [\App\Http\Controllers\AddressController::class, 'edit'])->name('edit');
        // Accept both PUT and PATCH to cover form submissions and any cached clients
        Route::match(['put', 'patch'], '/{address}', [\App\Http\Controllers\AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [\App\Http\Controllers\AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [\App\Http\Controllers\AddressController::class, 'setDefault'])->name('setDefault');
        
        // API endpoints
        Route::get('/api/default', [\App\Http\Controllers\AddressController::class, 'getDefault'])->name('api.default');
        Route::get('/api/all', [\App\Http\Controllers\AddressController::class, 'getAll'])->name('api.all');
        
        // Philippine address cascading endpoints
        Route::get('/api/regions', [\App\Http\Controllers\AddressController::class, 'getRegions'])->name('api.regions');
        Route::get('/api/provinces/{regionId}', [\App\Http\Controllers\AddressController::class, 'getProvinces'])->name('api.provinces');
        Route::get('/api/cities/{provinceId}', [\App\Http\Controllers\AddressController::class, 'getCities'])->name('api.cities');
        Route::get('/api/barangays/{cityId}', [\App\Http\Controllers\AddressController::class, 'getBarangays'])->name('api.barangays');
    });

    // Cart & Checkout
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('/add/{product}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.destroy');
        Route::patch('/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');

        Route::match(['get', 'post'], '/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
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
        Route::get('/paymongo/success/{order}', [CartController::class, 'paymongoSuccess'])->name('payment.paymongo.success');
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

    // Redirect old colors route to pattern selection
    Route::get('/custom-orders/create/colors', function() {
        return redirect()->route('custom_orders.create.pattern')
            ->with('info', 'The color customization step has been simplified. Please select a pattern instead.');
    });

    // Public Patterns
Route::get('/patterns', [\App\Http\Controllers\PatternController::class, 'index'])->name('patterns.index');
Route::get('/patterns/{pattern}', [\App\Http\Controllers\PatternController::class, 'show'])->name('patterns.show');

// Wishlist (User)
// Index requires auth middleware for page load redirect
// AJAX endpoints (add/remove/check) rely on TokenAuth middleware to avoid logout
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::middleware(['auth'])->get('/', [\App\Http\Controllers\WishlistController::class, 'index'])->name('index');
    Route::post('/add', [\App\Http\Controllers\WishlistController::class, 'add'])->name('add');
    Route::post('/remove', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('remove');
    Route::post('/check', [\App\Http\Controllers\WishlistController::class, 'check'])->name('check');
});

// Notifications (User)
// Index requires auth middleware for page load redirect
// AJAX endpoints (POST/DELETE) rely on TokenAuth middleware to avoid logout
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::middleware(['auth'])->get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::post('/clear', [NotificationController::class, 'clear'])->name('clear');
});

// Test auth debugging
Route::get('/test-auth', function() {
    return 'Auth test - User ID: ' . (auth()->check() ? auth()->id() : 'Not authenticated') . ' - Session ID: ' . session()->getId();
});

// ============================================================================
// CUSTOM ORDER PAYMENT ROUTES - Must be BEFORE the auth middleware group
// These routes handle token-based authentication internally
// ============================================================================
Route::post('/custom-orders/{order}/payment', [\App\Http\Controllers\CustomOrderController::class, 'processPayment'])
    ->name('custom_orders.payment.process')
    ->withoutMiddleware(['auth']);

Route::post('/custom-orders/{order}/payment/simulate-success', [\App\Http\Controllers\CustomOrderController::class, 'simulateMayaSuccess'])
    ->name('custom_orders.payment.simulate')
    ->withoutMiddleware(['auth']);

Route::post('/custom-orders/{order}/payment/confirm', [\App\Http\Controllers\CustomOrderController::class, 'paymentConfirmProcess'])
    ->name('custom_orders.payment.confirm.process')
    ->withoutMiddleware(['auth']);

// AJAX endpoint for payment initiation - returns JSON with Maya checkout URL
Route::post('/api/custom-orders/{order}/initiate-payment', [\App\Http\Controllers\CustomOrderController::class, 'initiatePaymentAjax'])
    ->name('custom_orders.payment.initiate.ajax')
    ->withoutMiddleware(['auth']);


// Custom Orders (Enhanced) - Require Authentication
Route::middleware(['auth'])->prefix('custom-orders')->name('custom_orders.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomOrderController::class, 'userIndex'])->name('index');
    
    // Redirect /create to fabric selection (step 1) — show loading screen
    Route::get('/create', function(\Illuminate\Http\Request $request) {
        $query = ['new_submission' => 1];

        if ($request->filled('auth_token')) {
            $query['auth_token'] = $request->query('auth_token');
        }

        // Keep optional preselection params when entering from pattern pages.
        if ($request->filled('pattern_id')) {
            $query['pattern_id'] = $request->query('pattern_id');
        }

        $url = route('custom_orders.create.step1') . '?' . http_build_query($query);
        return response()->view('custom_orders.loading', ['redirectUrl' => $url]);
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
    Route::get('/restore', [\App\Http\Controllers\CustomOrderController::class, 'restoreWizard'])->name('create.restore');
    Route::get('/create/step3', [\App\Http\Controllers\CustomOrderController::class, 'createStep3'])->name('create.step3');
    Route::post('/create/step3', [\App\Http\Controllers\CustomOrderController::class, 'storeStep3'])->name('store.step3');
    Route::get('/create/step4', [\App\Http\Controllers\CustomOrderController::class, 'createStep4'])->name('create.step4');
    Route::post('/create/complete', [\App\Http\Controllers\CustomOrderController::class, 'completeWizard'])->name('complete.wizard');
    Route::match(['post', 'patch'], '/create/add-to-batch', [\App\Http\Controllers\CustomOrderController::class, 'addToBatch'])->name('add.to.batch');
    Route::delete('/create/batch-item/{index}', [\App\Http\Controllers\CustomOrderController::class, 'removeBatchItem'])->name('remove.batch.item');
    Route::patch('/create/batch-item/{index}', [\App\Http\Controllers\CustomOrderController::class, 'updateBatchItem'])->name('update.batch.item');
    Route::post('/create/current-item', [\App\Http\Controllers\CustomOrderController::class, 'updateCurrentItem'])->name('update.current.item');
    Route::get('/create/edit-batch-item/{index}', [\App\Http\Controllers\CustomOrderController::class, 'editBatchItem'])->name('edit.batch.item');
    Route::get('/success/{order}', [\App\Http\Controllers\CustomOrderController::class, 'success'])->name('success');
    
    // Pattern-Based Approach (Fabric Flow)
    Route::get('/create/pattern', [\App\Http\Controllers\CustomOrderController::class, 'createPatternSelection'])->name('create.pattern');
    Route::post('/create/pattern', [\App\Http\Controllers\CustomOrderController::class, 'storePattern'])->name('store.pattern');
    
    // Edit a pending custom order
    Route::get('/{order}/edit', [\App\Http\Controllers\CustomOrderController::class, 'edit'])->name('edit');
    Route::put('/{order}', [\App\Http\Controllers\CustomOrderController::class, 'update'])->name('update');
    
    Route::get('/{order}', [\App\Http\Controllers\CustomOrderController::class, 'show'])->name('show');
    
    // User decision on quoted price
    Route::post('/{order}/accept', [\App\Http\Controllers\CustomOrderController::class, 'acceptQuote'])->name('accept');
    Route::post('/{order}/reject', [\App\Http\Controllers\CustomOrderController::class, 'rejectQuote'])->name('reject');
    
    // Payment routes - GET routes require auth middleware
    Route::get('/{order}/payment', [\App\Http\Controllers\CustomOrderController::class, 'payment'])->name('payment');
    Route::get('/{order}/payment/instructions', [\App\Http\Controllers\CustomOrderController::class, 'paymentInstructions'])->name('payment.instructions');
    Route::get('/{order}/payment/confirm', [\App\Http\Controllers\CustomOrderController::class, 'paymentConfirm'])->name('payment.confirm');
    Route::get('/{order}/payment/maya-success', [\App\Http\Controllers\CustomOrderController::class, 'mayaPaymentSuccess'])->name('payment.maya.success');
    Route::get('/{order}/payment/maya-failed', [\App\Http\Controllers\CustomOrderController::class, 'mayaPaymentFailed'])->name('payment.maya.failed');
    Route::get('/{order}/payment/paymongo-success', [\App\Http\Controllers\CustomOrderController::class, 'paymongoPaymentSuccess'])->name('payment.paymongo.success');
    Route::get('/{order}/payment/paymongo-failed', [\App\Http\Controllers\CustomOrderController::class, 'paymongoPaymentFailed'])->name('payment.paymongo.failed');
    
    // Confirm order received
    Route::post('/{order}/confirm-received', [\App\Http\Controllers\CustomOrderController::class, 'confirmReceived'])->name('confirm_received');
    
    // Legacy routes
    Route::patch('/{order}/respond', [\App\Http\Controllers\CustomOrderController::class, 'respondToQuote'])->name('respond');
    Route::post('/{order}/cancel', [\App\Http\Controllers\CustomOrderController::class, 'cancel'])->name('cancel');
    Route::get('/load-progress', [\App\Http\Controllers\CustomOrderController::class, 'loadProgress'])->name('custom_orders.load_progress');
    
    // Analytics for users
    Route::get('/analytics', [\App\Http\Controllers\CustomOrderController::class, 'userAnalytics'])->name('custom_orders.user_analytics');
});

});

// Chat Routes - Outside auth middleware to allow TokenAuth to work
// Controllers verify ownership with auth()->id() checks
Route::prefix('chats')->name('chats.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\ChatController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\ChatController::class, 'store'])->name('store');
    Route::get('/{chat}', [\App\Http\Controllers\ChatController::class, 'show'])->name('show');
    Route::post('/{chat}/message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send-message');
    Route::post('/{chat}/close', [\App\Http\Controllers\ChatController::class, 'close'])->name('close');
    Route::post('/{chat}/respond-quote', [\App\Http\Controllers\ChatController::class, 'respondToQuote'])->name('respond-quote');
    Route::post('/{chat}/submit-form-response', [\App\Http\Controllers\ChatController::class, 'submitFormResponse'])->name('submit-form-response');
    
    // Payment routes for in-chat payments
    Route::post('/{chat}/payment/submit', [\App\Http\Controllers\ChatPaymentController::class, 'submitPaymentProof'])->name('payment.submit');
});

// Order payment method selection (for chat-based custom orders)
Route::post('/custom-orders/{customOrder}/set-payment-method', [\App\Http\Controllers\ChatController::class, 'setPaymentMethod'])->name('orders.set_payment_method')->middleware('auth');
Route::post('/custom-orders/{customOrder}/upload-receipt', [\App\Http\Controllers\ChatController::class, 'uploadReceipt'])->name('orders.upload_receipt')->middleware('auth');

// Track Order - Redirect old routes to new implementation
Route::get('/track', function() {
    return redirect()->route('track-order.index');
});
Route::get('/track/{trackingNumber}', function($trackingNumber) {
    return redirect()->route('track-order.show', $trackingNumber);
});

// ============================================================
// Non-production test/debug routes
// ============================================================
if (!app()->isProduction()) {

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

} // end non-production test routes

/*
|--------------------------------------------------------------------------
| Admin Order Staff Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin:admin,order_staff'])->prefix('admin')->name('admin.')->group(function () {

    // Orders (Main Orders Page - Enhanced Custom Orders)
    Route::get('/custom-orders-dashboard', [AdminCustomOrderController::class, 'indexEnhanced'])->name('orders.index');

    // Orders (Regular Orders)
    Route::prefix('orders')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('regular.index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/{order}/paymongo-receipt', [AdminOrderController::class, 'paymongoReceipt'])->name('orders.paymongo_receipt');
        Route::post('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
        Route::post('/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.update_tracking');
        Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
        Route::patch('/{order}/update-notes', [AdminOrderController::class, 'updateNotes'])->name('orders.update-notes');
        Route::get('/{order}/invoice', [AdminOrderController::class, 'generateInvoice'])->name('orders.invoice');

        // Quick update status route (accept POST and PUT)
        Route::match(['post', 'put'], '/{order}/quick-update-status', [AdminOrderController::class, 'quickUpdateStatus'])->name('orders.quickUpdateStatus');
    });

    // Custom Orders - processing routes
    Route::prefix('custom-orders')->name('custom-orders.')->group(function () {
        Route::get('/', [AdminCustomOrderController::class, 'index'])->name('index');
        Route::get('/production-dashboard', [AdminCustomOrderController::class, 'productionDashboard'])->name('production-dashboard');
        Route::get('/export', [AdminCustomOrderController::class, 'exportOrders'])->name('export');
        Route::get('/view/{order}', [AdminCustomOrderController::class, 'show'])->name('show');
        Route::get('/{order}/paymongo-receipt', [AdminCustomOrderController::class, 'paymongoReceipt'])->name('paymongo_receipt');

        Route::post('/{order}/update-status', [AdminCustomOrderController::class, 'updateStatus'])->name('update_status');
        Route::post('/{order}/quote-price', [AdminCustomOrderController::class, 'quotePrice'])->name('quote_price');
        Route::post('/{order}/batch-shipping', [AdminCustomOrderController::class, 'updateBatchShipping'])->name('batch_shipping');
        Route::post('/{order}/verify-payment', [AdminCustomOrderController::class, 'verifyPayment'])->name('verify_payment');
        Route::post('/{order}/confirm-payment', [AdminCustomOrderController::class, 'confirmPayment'])->name('confirmPayment');
        Route::post('/{order}/reject-payment', [AdminCustomOrderController::class, 'rejectPayment'])->name('rejectPayment');
        Route::post('/{order}/reject', [AdminCustomOrderController::class, 'rejectOrder'])->name('reject');
        Route::post('/{order}/approve', [AdminCustomOrderController::class, 'approveOrder'])->name('approve');
        Route::post('/{order}/notify-delay', [AdminCustomOrderController::class, 'notifyDelay'])->name('notifyDelay');
        Route::post('/{order}/clear-delay', [AdminCustomOrderController::class, 'clearDelay'])->name('clearDelay');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
// Admin Routes - Protected by admin authentication and role check
Route::middleware(['admin:admin'])->prefix('admin')->name('admin.')->group(function () {

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
    Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Pattern Management
    Route::resource('patterns', \App\Http\Controllers\Admin\PatternController::class);
    Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulkDelete');
    Route::post('/products/{product}/stock-in', [AdminProductController::class, 'stockIn'])->name('products.stockIn');
    Route::post('/products/{product}/stock-out', [AdminProductController::class, 'stockOut'])->name('products.stockOut');



    // Combined Patterns & Types Management
    Route::get('/patterns-management', [\App\Http\Controllers\Admin\PatternsManagementController::class, 'index'])->name('patterns_management.index');

    // Fabric Types Management
    Route::resource('fabric-types', \App\Http\Controllers\Admin\FabricTypeController::class, ['names' => [
        'index' => 'fabric_types.index',
        'create' => 'fabric_types.create',
        'store' => 'fabric_types.store',
        'show' => 'fabric_types.show',
        'edit' => 'fabric_types.edit',
        'update' => 'fabric_types.update',
        'destroy' => 'fabric_types.destroy',
    ]]);
    Route::patch('/fabric-types/{fabricType}', [\App\Http\Controllers\Admin\FabricTypeController::class, 'update'])->name('fabric_types.patch');
    Route::post('/fabric-types/{fabricType}/toggle-active', [\App\Http\Controllers\Admin\FabricTypeController::class, 'toggleActive'])->name('fabric_types.toggle');

    // Intended Uses Management
    Route::get('/intended-uses', [\App\Http\Controllers\Admin\IntendedUseController::class, 'index'])->name('intended_uses.index');
    Route::get('/intended-uses/create', [\App\Http\Controllers\Admin\IntendedUseController::class, 'create'])->name('intended_uses.create');
    Route::post('/intended-uses', [\App\Http\Controllers\Admin\IntendedUseController::class, 'store'])->name('intended_uses.store');
    Route::get('/intended-uses/{intendedUse}', [\App\Http\Controllers\Admin\IntendedUseController::class, 'show'])->name('intended_uses.show');
    Route::get('/intended-uses/{intendedUse}/edit', [\App\Http\Controllers\Admin\IntendedUseController::class, 'edit'])->name('intended_uses.edit');
    Route::put('/intended-uses/{intendedUse}', [\App\Http\Controllers\Admin\IntendedUseController::class, 'update'])->name('intended_uses.update');
    Route::patch('/intended-uses/{intendedUse}', [\App\Http\Controllers\Admin\IntendedUseController::class, 'update'])->name('intended_uses.patch');
    Route::delete('/intended-uses/{intendedUse}', [\App\Http\Controllers\Admin\IntendedUseController::class, 'destroy'])->name('intended_uses.destroy');
    Route::post('/intended-uses/{intendedUse}/toggle-active', [\App\Http\Controllers\Admin\IntendedUseController::class, 'toggleActive'])->name('intended_uses.toggle');

    // Cultural Heritage Management
    Route::resource('cultural-heritage', \App\Http\Controllers\Admin\CulturalHeritageController::class);
    Route::post('/cultural-heritage/{id}/toggle-status', [\App\Http\Controllers\Admin\CulturalHeritageController::class, 'toggleStatus'])->name('cultural-heritage.toggleStatus');

    // Orders (Regular Orders)
    Route::prefix('orders')->group(function () {
        Route::get('/create', [AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('/', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::post('/bulk-update', [AdminOrderController::class, 'bulkUpdate'])->name('orders.bulkUpdate');
    });

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/history', [InventoryController::class, 'history'])->name('history');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/low-stock', [InventoryController::class, 'lowStockAlerts'])->name('low-stock');
        Route::get('/report', [InventoryController::class, 'report'])->name('report');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::patch('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::patch('/{inventory}/restock', [InventoryController::class, 'restock'])->name('restock');
        Route::patch('/{inventory}/stock-out', [InventoryController::class, 'stockOut'])->name('stockOut');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    // Custom Orders - Admin-only create/edit/delete routes
    Route::prefix('custom-orders')->name('custom-orders.')->group(function () {
        // Create wizard routes
        Route::get('/create', [AdminCustomOrderController::class, 'create'])->name('create');
        Route::get('/create/choice', [AdminCustomOrderController::class, 'createChoice'])->name('create.choice');
        Route::get('/create/product', [AdminCustomOrderController::class, 'createProductSelection'])->name('create.product');
        Route::post('/create/product', [AdminCustomOrderController::class, 'storeProductSelection'])->name('store.product');
        Route::get('/create/product/customize', [AdminCustomOrderController::class, 'createProductCustomization'])->name('create.product.customize');
        Route::post('/create/product/customize', [AdminCustomOrderController::class, 'storeProductCustomization'])->name('store.product.customization');
        Route::get('/create/fabric', [AdminCustomOrderController::class, 'createFabricSelection'])->name('create.fabric');
        Route::post('/create/fabric', [AdminCustomOrderController::class, 'storeFabricSelection'])->name('store.fabric');
        Route::get('/create/pattern', [AdminCustomOrderController::class, 'createPatternSelection'])->name('create.pattern');
        Route::post('/create/pattern', [AdminCustomOrderController::class, 'storePatternSelection'])->name('store.pattern');
        Route::get('/create/review', [AdminCustomOrderController::class, 'createReview'])->name('create.review');
        Route::post('/store', [AdminCustomOrderController::class, 'store'])->name('store');

        Route::delete('/{order}', [AdminCustomOrderController::class, 'destroy'])->name('delete');

        // Edit
        Route::get('/{order}/edit', [AdminCustomOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [AdminCustomOrderController::class, 'update'])->name('update');
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
    
    // Dashboard CSV Export - Direct route
    Route::get('/dashboard/export-csv', [DashboardController::class, 'exportReport'])->name('dashboard.export');

    // Print Report - printable page with selectable sections
    Route::get('/dashboard/print-report', [DashboardController::class, 'printReport'])->name('dashboard.print');

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::match(['patch', 'put'], '/{user}', [UserManagementController::class, 'update'])->name('users.update');
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

    // Chat Management
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('index');
        // Static routes MUST come before wildcard {chat} routes
        Route::get('/unread-count', [\App\Http\Controllers\Admin\ChatController::class, 'unreadCount'])->name('unread-count');
        Route::get('/{chat}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('show');
        Route::post('/{chat}/reply', [\App\Http\Controllers\Admin\ChatController::class, 'sendReply'])->name('reply');
        Route::post('/{chat}/request-details/{messageId}', [\App\Http\Controllers\Admin\ChatController::class, 'requestDetails'])->name('request-details');
        Route::patch('/{chat}/status', [\App\Http\Controllers\Admin\ChatController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{chat}', [\App\Http\Controllers\Admin\ChatController::class, 'destroy'])->name('destroy');
        
        // Payment management routes
        Route::post('/{chat}/payment/send', [\App\Http\Controllers\ChatPaymentController::class, 'sendPaymentRequest'])->name('payment.send');
        Route::patch('/payment/{payment}/verify', [\App\Http\Controllers\ChatPaymentController::class, 'verifyPayment'])->name('payment.verify');
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
// Mobile PayMongo success/failed callbacks (public — no auth, PayMongo redirects here)
Route::get('/mobile/payment/paymongo/success/{orderId}', [App\Http\Controllers\CartController::class, 'mobilePaymongoSuccess'])->name('mobile.payment.paymongo.success');
Route::get('/mobile/payment/paymongo/failed/{orderId}', [App\Http\Controllers\CartController::class, 'mobilePaymongoFailed'])->name('mobile.payment.paymongo.failed');

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

if (!app()->isProduction()) {

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

} // end non-production test routes (bottom block)

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

// Diagnostic routes (only in debug mode)
if (config('app.debug')) {
    Route::get('/debug/db', function() {
        $connection = config('database.default');
        $data = [
            'connection' => $connection,
            'env_db_connection' => env('DB_CONNECTION'),
            'cached' => app()->configurationIsCached(),
        ];

        return response()->json($data);
    });

    Route::get('/debug/seed', function() {
        // Only allow in local/testing environment for safety
        if (!in_array(config('app.env'), ['local', 'testing'])) {
            abort(403, 'Seeding via web interface not allowed in this environment');
        }
        
        Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeded!';
    });
}

// Fallback 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
