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
        
        // Configure web middleware group explicitly
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\TokenAuth::class, // Token-based auth fallback
        ]);
        
        // Exclude authentication routes from CSRF verification to fix 419 errors
        $middleware->validateCsrfTokens(except: [
            'login',
            'login-user',
            'admin/login',
            'register',
            'logout',
            'admin/logout',
            'verify-otp',
            'resend-otp',
            'password/*',
            'forgot-password',
            'reset-password',
            'api/*',
            'sanctum/csrf-cookie',
            'chats/*/message',
            'chats/*/close',
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
        // Always show detailed errors for debugging
        $exceptions->renderable(function (\Throwable $e, $request) {
            \Log::error('Global exception caught: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Show detailed error page
            if (!$request->expectsJson()) {
                return response(
                    "<!DOCTYPE html>" .
                    "<html><head><title>Application Error</title>" .
                    "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5}h1{color:#c00}pre{background:#fff;padding:15px;border-radius:5px;overflow:auto}.info{background:#fff;padding:15px;margin:10px 0;border-left:4px solid #800000}</style>" .
                    "</head><body>" .
                    "<h1>⚠️ Application Error</h1>" .
                    "<div class='info'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>" .
                    "<div class='info'><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</div>" .
                    "<div class='info'><strong>URL:</strong> " . htmlspecialchars($request->fullUrl()) . "</div>" .
                    "<div class='info'><strong>Auth:</strong> " . (auth()->check() ? 'Authenticated (User ID: ' . auth()->id() . ')' : 'Not Authenticated') . "</div>" .
                    "<div class='info'><strong>Has Token:</strong> " . ($request->has('auth_token') ? 'Yes' : 'No') . "</div>" .
                    "<h2>Stack Trace:</h2>" .
                    "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>" .
                    "<a href='/' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#800000;color:white;text-decoration:none;border-radius:5px'>Go to Home</a>" .
                    "</body></html>",
                    500
                );
            }
            
            // JSON response for API requests
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        });
    })->create();