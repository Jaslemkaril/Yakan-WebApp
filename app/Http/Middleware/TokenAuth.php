<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check for auth_token in query parameter or session
        $token = $request->query('auth_token') ?? session('auth_token');
        
        if ($token && !Auth::check()) {
            // Validate token
            $authToken = DB::table('auth_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();
            
            if ($authToken) {
                // Login the user
                $user = \App\Models\User::find($authToken->user_id);
                if ($user) {
                    Auth::login($user);
                    
                    // Store token in session for subsequent requests
                    session(['auth_token' => $token]);
                    
                    // If token was in URL, redirect to clean URL
                    if ($request->query('auth_token')) {
                        return redirect($request->url());
                    }
                }
            }
        }
        
        return $next($request);
    }
}
