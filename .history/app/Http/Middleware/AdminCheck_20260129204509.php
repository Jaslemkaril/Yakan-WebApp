<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    public function handle($request, Closure $next)
    {
        \Log::info('AdminCheck: Checking access', [
            'path' => $request->path(),
            'auth_check' => Auth::check(),
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'user_role' => Auth::check() ? Auth::user()->role : null,
            'user_email' => Auth::check() ? Auth::user()->email : null,
        ]);

        // Check if user is authenticated and is an admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            \Log::info('AdminCheck: Access GRANTED for admin user');
            return $next($request);
        }
        
        // Log unauthorized attempt
        \Log::warning('AdminCheck: Access DENIED', [
            'url' => $request->fullUrl(),
            'authenticated' => Auth::check(),
            'user_role' => Auth::check() ? Auth::user()->role : 'not_authenticated',
            'user_email' => Auth::check() ? Auth::user()->email : null,
        ]);
        
        return redirect()->route('admin.login.form')->with('error', 'Please login as admin to continue');
    }
}
