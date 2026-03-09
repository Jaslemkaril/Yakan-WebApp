<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/setup.php'));

            // Admin API routes (notifications, etc.)
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/admin_api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Railway's reverse proxy (required for HTTPS detection & sessions)
        $middleware->trustProxies(at: '*');
        
        // Exclude auth_token cookie from encryption
        $middleware->encryptCookies(except: ['auth_token']);
        
        // Append token-based auth fallback to web middleware group
        // NOTE: EncryptCookies, AddQueuedCookiesToResponse, and StartSession are already
        // included in Laravel's default web group — do NOT add them again or sessions
        // will be re-loaded mid-request, breaking OAuth state validation.
        $middleware->web(append: [
            \App\Http\Middleware\TokenAuth::class, // Token-based auth fallback
        ]);
        
        // Exclude routes from CSRF verification
        // Session cookies are unreliable on Railway's edge proxy with PHP built-in server,
        // so CSRF tokens won't match. Auth is handled by TokenAuth middleware instead.
        $middleware->validateCsrfTokens(except: [
            'login',
            'login-user',
            'admin/login',
            'register',
            'logout',
            'admin/logout',
            'verify-otp',
            'resend-otp',
            'password',
            'password/*',
            'forgot-password',
            'reset-password',
            'api/*',
            'sanctum/csrf-cookie',
            'chats',
            'chats/*',
            'cart/*',
            'wishlist/*',
            'notifications/*',
            'addresses/*',
            'addresses',
            'payment/*',
            'orders/*',
            'custom-orders/*',
            'profile',
            'profile/*',
            'admin/*',
            'reviews',
            'reviews/*',
        ]);
        
        $middleware->api(prepend: [
            // Only add stateful middleware for web requests, not mobile API
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // \App\Http\Middleware\ForceHttps::class, // Disabled for local development
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'auth.rate.limit' => \App\Http\Middleware\AuthRateLimit::class,
            'account.lockout' => \App\Http\Middleware\AccountLockout::class,
            '2fa.required' => \App\Http\Middleware\RequireTwoFactor::class,
            'rate.limit' => \App\Http\Middleware\RateLimit::class,
            'admin' => \App\Http\Middleware\AdminCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Throwable $e, $request) {
            // Skip exceptions that Laravel handles well on its own
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                // For AJAX / API requests keep the default 401 JSON response
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }
                // For regular web requests return a branded loading page that redirects to
                // the login form, instead of Symfony's plain "Redirecting to [link]" body.
                // Pass the referring page as `redirect_to` so users return after login.
                $intended = $request->header('referer', '');
                // For POST requests the referer is the *form page* — send user back there after login.
                // For GET requests the current URL is the intended destination.
                if ($request->isMethod('get')) {
                    $intended = $request->fullUrl();
                }
                // Strip the domain and only keep the path+query, and avoid auth paths
                $intendedPath = '';
                try {
                    $parsedPath = parse_url($intended, PHP_URL_PATH) ?? '';
                    $parsedQuery = parse_url($intended, PHP_URL_QUERY) ?? '';
                    $authPaths = ['/login', '/logout', '/register', '/login-user', '/admin/login', '/verify-otp'];
                    $isAuthPath = array_filter($authPaths, fn($p) => str_starts_with($parsedPath, $p));
                    if (!$isAuthPath && $parsedPath && $parsedPath !== '/') {
                        $intendedPath = $parsedPath . ($parsedQuery ? '?' . $parsedQuery : '');
                    }
                } catch (\Throwable) {}

                if ($request->is('admin/*')) {
                    $loginUrl = json_encode(route('admin.login.form'));
                } else {
                    $baseLoginUrl = route('login.user.form');
                    $loginUrl = $intendedPath
                        ? json_encode($baseLoginUrl . '?redirect_to=' . urlencode($intendedPath))
                        : json_encode($baseLoginUrl);
                }
                return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Please Log In — Yakan</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(160deg,#6b0000 0%,#8b0000 45%,#3d0000 100%);font-family:'Inter',system-ui,sans-serif;}
        .card{background:rgba(255,255,255,0.09);border:1px solid rgba(255,255,255,0.18);backdrop-filter:blur(20px);border-radius:24px;padding:48px 40px;text-align:center;width:100%;max-width:360px;box-shadow:0 32px 64px rgba(0,0,0,0.35);animation:fadeUp 0.4s ease-out;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .logo{width:56px;height:56px;background:linear-gradient(135deg,#800000,#a00000);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(0,0,0,0.3);}
        .logo span{color:white;font-weight:800;font-size:26px;}
        .spinner-ring{width:44px;height:44px;border:3px solid rgba(255,255,255,0.2);border-top-color:rgba(255,255,255,0.85);border-radius:50%;animation:spin 0.75s linear infinite;margin:0 auto 20px;}
        @keyframes spin{to{transform:rotate(360deg)}}
        h2{color:white;font-size:1.25rem;font-weight:700;margin-bottom:6px;}
        .sub{color:rgba(255,220,220,0.75);font-size:0.875rem;margin-bottom:28px;}
        .progress-bar{height:3px;background:rgba(255,255,255,0.15);border-radius:2px;overflow:hidden;}
        .progress-fill{height:100%;background:linear-gradient(90deg,rgba(255,255,255,0.4),rgba(255,255,255,0.8));border-radius:2px;animation:fill 1.4s ease-out forwards;}
        @keyframes fill{from{width:0%}to{width:100%}}
        .status{color:rgba(255,220,220,0.5);font-size:0.75rem;margin-top:12px;letter-spacing:0.05em;}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><span>Y</span></div>
        <div class="spinner-ring"></div>
        <h2>Please Log In</h2>
        <p class="sub">Redirecting you to the login page&hellip;</p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <p class="status">REDIRECTING TO LOGIN</p>
    </div>
    <script>setTimeout(function(){window.location.href={$loginUrl};},1400);<\/script>
</body>
</html>
HTML);
            }
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->back()->with('error', 'Session expired. Please try again.');
            }
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'The requested item was not found.'], 404);
                }
                return null; // Let Laravel show 404 page
            }

            \Log::error('Global exception caught: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString(),
            ]);

            $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : 500;
            
            // Force JSON response for AJAX/API requests (check multiple indicators)
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->is('api/*') || $request->is('admin/chats/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], $statusCode);
            }

            // Show detailed error page for non-JSON
            return response(
                "<!DOCTYPE html>" .
                "<html><head><title>Application Error</title>" .
                "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5}h1{color:#c00}pre{background:#fff;padding:15px;border-radius:5px;overflow:auto}.info{background:#fff;padding:15px;margin:10px 0;border-left:4px solid #800000}</style>" .
                "</head><body>" .
                "<h1>⚠️ Application Error</h1>" .
                "<div class='info'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>" .
                "<div class='info'><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</div>" .
                "<div class='info'><strong>URL:</strong> " . htmlspecialchars($request->fullUrl()) . "</div>" .
                "<h2>Stack Trace:</h2>" .
                "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>" .
                "<a href='/' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#800000;color:white;text-decoration:none;border-radius:5px'>Go to Home</a>" .
                "</body></html>",
                $statusCode
            );
        });
    })->create();