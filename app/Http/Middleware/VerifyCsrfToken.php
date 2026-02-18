<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'test-generate-pattern',
        'test-pattern-status/*',
        '/test-generate-pattern',
        '/test-pattern-status/*',
        // Temporarily exempt ALL login/auth routes - URGENT FIX
        'login',
        '/login',
        'login-user',
        '/login-user', 
        'admin/login',
        '/admin/login',
        'register',
        '/register',
        'admin/*',
    ];
}
