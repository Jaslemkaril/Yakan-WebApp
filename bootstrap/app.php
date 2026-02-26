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
            'password/*',
            'forgot-password',
            'reset-password',
            'api/*',
            'sanctum/csrf-cookie',
            'chats/*/message',
            'chats/*/close',
            'cart/*',
            'wishlist/*',
            'addresses/*',
            'addresses',
            'payment/*',
            'orders/*',
            'custom-orders/*',
            'admin/*',
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
        // Let AuthenticationException use Laravel's default handling (redirect to login)
        // Do NOT catch it here or users see an error page instead of the login form
        
        $exceptions->renderable(function (\Throwable $e, $request) {
            // Skip exceptions that Laravel handles well on its own
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null; // Let Laravel handle → redirect to login
            }
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->back()->with('error', 'Session expired. Please try again.');
            }
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'The requested item was not found.'], 404);
                }
                return null; // Let Laravel show 404 page
            }

            \Log::error('Global exception caught: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : 500;
            
            // JSON response for API/AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage(),
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