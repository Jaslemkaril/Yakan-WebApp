<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try token authentication if not already authenticated
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
                        \Log::info('EnsureEmailIsVerified: User authenticated via token', ['user_id' => $user->id]);
                    }
                }
            }
        }
        
        if (! $request->user() ||
            ($request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            
            return $request->expectsJson()
                    ? response()->json(['message' => 'Your email address is not verified.'], 409)
                    : Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
