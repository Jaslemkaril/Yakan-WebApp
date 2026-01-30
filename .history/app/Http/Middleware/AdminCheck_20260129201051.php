<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated and is an admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }
        
        // Log unauthorized attempt
        \Log::warning('Unauthorized admin access attempt', [
            'url' => $request->fullUrl(),
            'authenticated' => Auth::check(),
            'user_role' => Auth::check() ? Auth::user()->role : null,
            'user_email' => Auth::check() ? Auth::user()->email : null,
        ]);
        
        return redirect()->route('admin.login.form')->with('error', 'Please login as admin to continue');
    }
}
