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
        // Check for auth_token in multiple places
        // For JSON requests, check the JSON body as well
        $token = $request->input('auth_token') 
            ?? $request->json('auth_token')  // Add JSON body support
            ?? $request->query('auth_token') 
            ?? $request->cookie('auth_token')
            ?? session('auth_token')
            ?? $request->header('X-Auth-Token') // Also check headers
            ?? $request->bearerToken(); // Check Authorization: Bearer header
        
        // Log the request for debugging
        if (!Auth::check()) {
            \Log::info('TokenAuth: Auth check', [
                'path' => $request->path(),
                'method' => $request->method(),
                'has_token' => !empty($token),
                'token_source' => $token ? ($request->json('auth_token') ? 'json' : ($request->query('auth_token') ? 'query' : ($request->bearerToken() ? 'bearer' : ($request->cookie('auth_token') ? 'cookie' : 'session')))) : 'none',
                'session_id' => session()->getId(),
                'is_guest' => Auth::guest()
            ]);
        }
        
        if ($token && !Auth::check()) {
            \Log::info('TokenAuth: Processing token', ['token' => substr($token, 0, 8) . '...']);
            
            // Validate token against database
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
                    
                    \Log::info('TokenAuth: User authenticated', [
                        'user_id' => $user->id,
                        'role' => $user->role
                    ]);
                }
            } else {
                \Log::warning('TokenAuth: Invalid or expired token', ['token' => substr($token, 0, 8) . '...']);
                // Clear invalid cookie
                cookie()->queue(cookie()->forget('auth_token'));
            }
        }
        
        $response = $next($request);
        
        // If user just authenticated via query param, set a persistent cookie
        // Use secure=null to auto-match the request protocol (avoids hardcoded mismatch)
        if ($request->query('auth_token') && Auth::check()) {
            $response->headers->setCookie(
                cookie('auth_token', $request->query('auth_token'), 60 * 24, '/', null, null, true, false, 'Lax')
            );
        }
        
        return $response;
    }
}
