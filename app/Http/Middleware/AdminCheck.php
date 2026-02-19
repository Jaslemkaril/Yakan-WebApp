<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminCheck
{
    public function handle($request, Closure $next)
    {
        // First, try token authentication if not already authenticated
        if (!Auth::check()) {
            $token = $request->query('auth_token');
            
            if ($token) {
                // Validate token
                $authToken = DB::table('auth_tokens')
                    ->where('token', $token)
                    ->where('expires_at', '>', now())
                    ->first();
                
                if ($authToken) {
                    $user = \App\Models\User::find($authToken->user_id);
                    if ($user) {
                        Auth::login($user, true);
                        \Log::info('AdminCheck: Authenticated via token', ['user_id' => $user->id]);
                    }
                }
            }
        }
        
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
            
            // If token is present, append it to all redirects and links
            if ($request->query('auth_token')) {
                $request->merge(['_auth_token' => $request->query('auth_token')]);
            }
            
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
