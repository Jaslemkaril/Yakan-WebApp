<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        // Try token authentication if not already authenticated
        if (!Auth::check()) {
            $token = $request->input('auth_token') ?? $request->query('auth_token');
            
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
                        \Log::info('Authenticate: User authenticated via token', ['user_id' => $user->id]);
                    }
                }
            }
        }
        
        return parent::handle($request, $next, ...$guards);
    }
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // If the request is for admin routes, redirect to admin login
            if ($request->is('admin/*')) {
                return route('admin.login.form');
            }

            // Otherwise, redirect to user login
            return route('login.user.form');
        }
    }
}
