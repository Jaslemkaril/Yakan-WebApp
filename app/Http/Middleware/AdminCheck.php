<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminCheck
{
    public function handle($request, Closure $next, ...$allowedRoles)
    {
        $allowedRoles = !empty($allowedRoles) ? $allowedRoles : ['admin'];

        // First, try token authentication if not already authenticated
        if (!Auth::check()) {
            $token = $request->post('auth_token')
                ?? $request->query('auth_token')
                ?? $request->header('X-Admin-Auth-Token');
            
            if ($token) {
                // Validate token
                $authToken = DB::table('auth_tokens')
                    ->where('token', $token)
                    ->where('expires_at', '>', now())
                    ->first();
                
                if ($authToken) {
                    $user = \App\Models\User::find($authToken->user_id);
                    if ($user && in_array($user->role, ['admin', 'order_staff'], true)) {
                        // Login with remember flag to persist session
                        Auth::login($user, true);
                        
                        // CRITICAL: Force save session immediately
                        $request->session()->save();
                        
                        // Store auth_token in session as backup
                        $request->session()->put('admin_auth_token', $token);
                        $request->session()->put('admin_authenticated', true);
                        $request->session()->save();
                        
                        \Log::info('AdminCheck: Authenticated via token', [
                            'user_id' => $user->id,
                            'session_id' => $request->session()->getId(),
                            'session_saved' => true
                        ]);
                    }
                }
            }
        } else {
            // User is already authenticated via session, refresh it
            $request->session()->put('admin_authenticated', true);
        }
        
        \Log::info('AdminCheck: Checking access', [
            'path' => $request->path(),
            'method' => $request->method(),
            'auth_check' => Auth::check(),
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'user_role' => Auth::check() ? Auth::user()->role : null,
            'session_id' => $request->session()->getId(),
        ]);

        // Check if user is authenticated and has one of the allowed roles.
        if (Auth::check() && in_array((string) Auth::user()->role, $allowedRoles, true)) {
            \Log::info('AdminCheck: Access GRANTED for admin user', [
                'user_id' => Auth::user()->id,
                'user_role' => Auth::user()->role,
                'allowed_roles' => $allowedRoles,
                'session_id' => $request->session()->getId(),
                'has_session_flag' => $request->session()->has('admin_authenticated')
            ]);
            
            // Retrieve auth_token from session only (never from URL)
            $authToken = $request->session()->get('admin_auth_token');
            if ($authToken) {
                $request->attributes->set('admin_auth_token', $authToken);
            }
            
            return $next($request);
        }
        
        // Log unauthorized attempt
        \Log::warning('AdminCheck: Access DENIED', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'authenticated' => Auth::check(),
            'user_role' => Auth::check() ? Auth::user()->role : 'not_authenticated',
            'allowed_roles' => $allowedRoles,
            'user_email' => Auth::check() ? Auth::user()->email : null,
        ]);
        
        $loginRoute = in_array('order_staff', $allowedRoles, true) && !in_array('admin', $allowedRoles, true)
            ? 'staff.login.form'
            : 'admin.login.form';

        return redirect()->route($loginRoute)->with('error', 'Please login with authorized staff account to continue');
    }
}
