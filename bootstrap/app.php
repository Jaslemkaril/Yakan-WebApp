<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/setup.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
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
        //
    })->create();