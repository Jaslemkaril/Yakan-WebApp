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
        // When in production with debug mode enabled, show detailed JSON errors
        $exceptions->renderable(function (\Throwable $e) {
            if (app()->environment('production') && config('app.debug')) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }
        });
    })->create();