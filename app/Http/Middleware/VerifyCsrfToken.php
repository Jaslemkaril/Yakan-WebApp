<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // TEMPORARY: Disable all CSRF checks for Railway deployment
    protected $except = [
        '*'
    ];
}
