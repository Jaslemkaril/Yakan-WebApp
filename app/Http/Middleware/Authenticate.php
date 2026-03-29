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
        $resolvedToken = $request->input('auth_token')
            ?? $request->query('auth_token')
            ?? $request->cookie('auth_token')
            ?? $request->header('X-Auth-Token')
            ?? $request->bearerToken();

        // Only switch session driver when the token comes from the HTTP request itself
        // (not from a stored session value) to avoid destroying the regular user session.
        $tokenFromRequest = $resolvedToken !== null;

        if (!$resolvedToken) {
            $resolvedToken = session('auth_token');
        }

        // For auth_token-based requests, avoid DB-backed sessions before auth middleware completes.
        // But ONLY when the token was explicitly passed in the request, not hydrated from session —
        // switching the driver when a form POST comes in destroys the regular session and logs users out.
        if ($tokenFromRequest && $resolvedToken && config('session.driver') === 'database') {
            config(['session.driver' => 'cookie']);
        }

        // Try token authentication if not already authenticated
        if (!Auth::check()) {
            $token = $resolvedToken;
            
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
                        session(['auth_token' => $token]);
                        $resolvedToken = $token;

                        // Keep token alive while active to reduce unexpected login redirects.
                        DB::table('auth_tokens')
                            ->where('token', $token)
                            ->update(['expires_at' => now()->addDays(30), 'updated_at' => now()]);

                        \Log::info('Authenticate: User authenticated via token', ['user_id' => $user->id]);
                    }
                }
            }
        }

        // If already authenticated but token wasn't present in request/session, hydrate from DB.
        if (Auth::check() && !$resolvedToken) {
            $resolvedToken = DB::table('auth_tokens')
                ->where('user_id', Auth::id())
                ->where('expires_at', '>', now())
                ->orderByDesc('updated_at')
                ->value('token');

            if ($resolvedToken) {
                session(['auth_token' => $resolvedToken]);
            }
        }

        $response = parent::handle($request, $next, ...$guards);

        // Persist token in cookie for page refreshes where query params may be missing.
        $tokenForCookie = $resolvedToken ?? session('auth_token');

        if ($tokenForCookie && Auth::check()) {
            cookie()->queue(cookie('auth_token', $tokenForCookie, 60 * 24, '/', null, null, true, false, 'Lax'));
        }

        return $response;
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
