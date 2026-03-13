<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // Show user login form
    public function createUser()
    {
        return view('auth.user-login'); // Add 'auth.' prefix
    }

    // Process user login
    public function storeUser(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        \Log::info('User login attempt starting', ['email' => $request->email]);

        // First check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            \Log::warning('Login failed: User not found', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'No account found with this email address.'
            ])->withInput($request->only('email'));
        }

        \Log::info('User found', ['email' => $user->email, 'role' => $user->role]);

        // Check role before attempting login
        if ($user->role !== 'user') {
            \Log::warning('Login denied: Wrong role for user login', ['email' => $user->email, 'role' => $user->role]);
            return back()->withErrors([
                'email' => 'This account is registered as admin. Please use the admin login page.'
            ])->withInput($request->only('email'));
        }

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            \Log::warning('Login failed: Invalid password', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'The password is incorrect.'
            ])->withInput($request->only('email'));
        }

        // Generate auth token (fallback for cookie issues)
        $token = bin2hex(random_bytes(32));
        \DB::table('auth_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token' => $token,
                'expires_at' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Also try session-based auth
        Auth::attempt($credentials, $request->boolean('remember'));
        $request->session()->regenerate();
        
        // Update last login timestamp
        if ($user) {
            $user->update(['last_login_at' => now()]);
        }
        
        \Log::info('User login successful', ['user_id' => $user->id]);

        // Honor intended URL (e.g., the page that triggered auth failure)
        $intended = $request->input('redirect_to');
        $baseRedirect = ($intended && str_starts_with($intended, '/') && !str_contains($intended, '//') && !in_array($intended, ['/login', '/logout', '/register', '/login-user']))
            ? $intended
            : '/dashboard';

        // Return a branded loading page instead of a plain redirect response
        // to avoid the Symfony "Redirecting to..." plain text on Railway.
        $redirectUrl = json_encode($baseRedirect . (str_contains($baseRedirect, '?') ? '&' : '?') . 'auth_token=' . $token);
        $userName = htmlspecialchars($user->name ?? 'there', ENT_QUOTES, 'UTF-8');

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signing in — Yakan</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #6b0000 0%, #8b0000 45%, #3d0000 100%);
            font-family: 'Inter', system-ui, sans-serif;
        }
        .card {
            background: rgba(255,255,255,0.09);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.35);
            animation: fadeUp 0.4s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #800000, #a00000);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .logo span { color: white; font-weight: 800; font-size: 26px; }
        .check-circle {
            width: 52px; height: 52px;
            background: rgba(16,185,129,0.2);
            border: 2px solid rgba(16,185,129,0.5);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            animation: pop 0.4s ease-out 0.2s both;
        }
        @keyframes pop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .check-circle svg { width: 26px; height: 26px; }
        h2 { color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 6px; }
        .sub { color: rgba(255,220,220,0.75); font-size: 0.875rem; margin-bottom: 28px; }
        .progress-bar {
            height: 3px;
            background: rgba(255,255,255,0.15);
            border-radius: 2px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.6), white);
            border-radius: 2px;
            animation: fill 1.2s ease-out forwards;
        }
        @keyframes fill { from { width: 0%; } to { width: 100%; } }
        .status { color: rgba(255,220,220,0.6); font-size: 0.75rem; margin-top: 12px; letter-spacing: 0.05em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><span>Y</span></div>
        <div class="check-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h2>Welcome back, {$userName}!</h2>
        <p class="sub">Login successful — loading your dashboard…</p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <p class="status">REDIRECTING</p>
    </div>
    <script>
        setTimeout(function() { window.location.href = {$redirectUrl}; }, 1200);
    </script>
</body>
</html>
HTML);
    }

    // Show admin login form
    public function createAdmin()
    {
        return view('auth.admin-login'); // Add 'auth.' prefix
    }

    // Process admin login
    public function storeAdmin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            
            if ($user && $user->role === 'admin') {
                $request->session()->regenerate();
                
                return redirect('/admin/dashboard')->with('success', 'Admin login successful!');
            }
            
            // Logout if not admin
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Access denied. Admin privileges required.'
            ]);
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or you are not an admin.'
        ]);
    }

    // Logout
    public function destroy(Request $request)
    {
        // Delete auth_token from DB so it can't be replayed from localStorage after logout
        if (Auth::check()) {
            \DB::table('auth_tokens')->where('user_id', Auth::id())->delete();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.user.form')->with('status', 'Signed out successfully.');
    }
}