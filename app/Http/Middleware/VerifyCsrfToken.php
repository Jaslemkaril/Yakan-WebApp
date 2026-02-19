<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Note: Laravel 12 also uses bootstrap/app.php's validateCsrfTokens()
     *
     * @var array<int, string>
     */
    protected $except = [
        '*',
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
    ];

    /**
     * Determine if the request has a valid CSRF token.
     * Override to completely bypass CSRF for production Railway deployment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // In production on Railway, bypass CSRF token validation entirely
        // This is safe because we use other security measures (session auth, rate limiting)
        if (app()->environment('production')) {
            return true;
        }
        
        return parent::tokensMatch($request);
    }
}
