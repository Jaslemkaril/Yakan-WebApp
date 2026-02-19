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
        // Check for auth_token in query parameter, POST data, or session
        $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
        
        if ($token && !Auth::check()) {
            \Log::info('TokenAuth: Processing token', ['token' => substr($token, 0, 8) . '...']);
            
            // Validate token
            $authToken = DB::table('auth_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();
            
            if ($authToken) {
                // Login the user
                $user = \App\Models\User::find($authToken->user_id);
                if ($user) {
                    Auth::login($user, true); // Remember = true
                    
                    // Store token in session for subsequent requests
                    session(['auth_token' => $token]);
                    session()->save(); // Force save
                    
                    \Log::info('TokenAuth: User authenticated', [
                        'user_id' => $user->id,
                        'role' => $user->role
                    ]);
                }
            } else {
                \Log::warning('TokenAuth: Invalid or expired token');
            }
        }
        
        return $next($request);
    }
}
