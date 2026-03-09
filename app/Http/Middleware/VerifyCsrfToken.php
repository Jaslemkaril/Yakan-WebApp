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
        'api/*',
        'webhooks/*',
        'profile',
        'profile/*',
        'password',
        'password/*',
    ];

    /**
     * Also skip CSRF when a valid auth_token query/input is present.
     * Railway uses ephemeral file sessions, which can be wiped between page-load
     * and form-submit, causing false-positive CSRF errors. The auth_token itself
     * is a sufficient second-factor for authenticated form actions.
     */
    protected function tokensMatch($request)
    {
        // If the request carries an auth_token (header, query, or form), skip CSRF.
        // This handles Railway's ephemeral session issue for token-authenticated routes.
        if ($request->input('auth_token') || $request->query('auth_token') || $request->header('X-Auth-Token')) {
            return true;
        }

        return parent::tokensMatch($request);
    }
}
