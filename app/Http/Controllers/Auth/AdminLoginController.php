<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminLoginController extends Controller
{
    private function ensureDefaultOrderStaffAccount(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $email = 'ordersteffie@gmail.com';
        $now = now();

        $existing = DB::table('users')->where('email', $email)->first();
        $verifiedAt = $existing && !empty($existing->email_verified_at)
            ? $existing->email_verified_at
            : $now;

        $payload = [
            'name' => 'Order Staff',
            'first_name' => 'Order',
            'last_name' => 'Staff',
            'password' => Hash::make('staff12345'),
            'role' => 'order_staff',
            'email_verified_at' => $verifiedAt,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update($payload);
            return;
        }

        DB::table('users')->insert(array_merge($payload, [
            'email' => $email,
            'created_at' => $now,
        ]));
    }

    private function normalizeStaffEmail(string $email): string
    {
        $normalized = strtolower(trim($email));

        $aliases = [
            'prdserfie@gmail.com' => 'ordersteffie@gmail.com',
            'orderstefie@gmail.com' => 'ordersteffie@gmail.com',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    // Show the admin login form
    public function showLoginForm()
    {
        // If already logged in as admin, redirect to admin dashboard
        if (Auth::check() && (string) Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        // If logged in as order staff, redirect to staff dashboard
        if (Auth::check() && (string) Auth::user()->role === 'order_staff') {
            return redirect('/staff/dashboard');
        }
        
        // Keep regular user sessions intact; do not force logout when accessing admin login.
        if (Auth::check()) {
            return redirect('/dashboard')->with('error', 'Admin access required. Please use an authorized admin account.');
        }
        
        return view('auth.admin-login');
    }

    // Handle admin login
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        \Log::info('Admin login attempt', ['email' => $credentials['email']]);

        // Check if user exists and has admin role
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }
        
        if ((string) $user->role !== 'admin') {
            \Log::warning('Login attempt by non-admin user', ['email' => $user->email, 'role' => $user->role]);
            return back()->withErrors(['email' => 'Access denied. Admin privileges required.'])->onlyInput('email');
        }
        
        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        // Generate auth token
        $token = bin2hex(random_bytes(32));
        \DB::table('auth_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Try session auth too
        Auth::login($user);
        $request->session()->regenerate();
        
        \Log::info('Admin login successful', ['user_id' => $user->id]);

        // Return a branded loading page instead of a plain redirect response
        // to avoid the Symfony "Redirecting to..." plain text on Railway.
        $redirectUrl = json_encode('/admin/dashboard?auth_token=' . $token);
        $adminName = htmlspecialchars($user->name ?? 'Admin', ENT_QUOTES, 'UTF-8');

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signing in — Yakan Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #3d0000 0%, #6b0000 45%, #1a0000 100%);
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
            box-shadow: 0 32px 64px rgba(0,0,0,0.45);
            animation: fadeUp 0.4s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #5a0000, #800000);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .logo span { color: white; font-weight: 800; font-size: 26px; }
        .badge {
            background: rgba(255,200,0,0.15);
            border: 1px solid rgba(255,200,0,0.35);
            color: rgba(255,220,100,0.9);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            padding: 3px 10px;
            border-radius: 99px;
            margin-bottom: 16px;
            display: inline-block;
        }
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
        <div class="badge">ADMIN</div>
        <div class="check-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h2>Welcome, {$adminName}!</h2>
        <p class="sub">Admin login successful — loading your dashboard…</p>
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

    // Show the order staff login form
    public function showStaffLoginForm()
    {
        $this->ensureDefaultOrderStaffAccount();

        if (Auth::check() && (string) Auth::user()->role === 'order_staff') {
            return redirect('/staff/dashboard');
        }

        if (Auth::check() && (string) Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        // Keep regular user sessions intact; do not force logout when accessing staff login.
        if (Auth::check()) {
            return redirect('/dashboard')->with('error', 'Staff access required. Please use an authorized staff account.');
        }

        return view('auth.staff-login');
    }

    // Handle order staff login
    public function staffLogin(Request $request)
    {
        $this->ensureDefaultOrderStaffAccount();

        $normalizedEmail = $this->normalizeStaffEmail((string) $request->input('email', ''));
        $request->merge(['email' => $normalizedEmail]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        \Log::info('Order staff login attempt', ['email' => $credentials['email']]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        if ((string) $user->role !== 'order_staff') {
            \Log::warning('Login attempt by non-order-staff user', ['email' => $user->email, 'role' => $user->role]);
            return back()->withErrors(['email' => 'Access denied. Order staff account required.'])->onlyInput('email');
        }

        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $token = bin2hex(random_bytes(32));
        \DB::table('auth_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        \Log::info('Order staff login successful', ['user_id' => $user->id]);

        return redirect('/staff/dashboard?auth_token=' . $token);
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Return branded sign-out page instead of plain "Redirecting to..." text.
        $loginUrl = json_encode(route('admin.login.form'));

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signing out — Yakan Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #3d0000 0%, #6b0000 45%, #1a0000 100%);
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
            box-shadow: 0 32px 64px rgba(0,0,0,0.45);
            animation: fadeUp 0.4s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #5a0000, #800000);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .logo span { color: white; font-weight: 800; font-size: 26px; }
        .badge {
            background: rgba(255,200,0,0.15);
            border: 1px solid rgba(255,200,0,0.35);
            color: rgba(255,220,100,0.9);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            padding: 3px 10px;
            border-radius: 99px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .spinner-ring {
            width: 52px; height: 52px;
            border: 3px solid rgba(255,255,255,0.15);
            border-top-color: rgba(255,255,255,0.8);
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
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
            background: linear-gradient(90deg, rgba(255,255,255,0.4), white);
            border-radius: 2px;
            animation: fill 1.4s ease-out forwards;
        }
        @keyframes fill { from { width: 0%; } to { width: 100%; } }
        .status { color: rgba(255,220,220,0.5); font-size: 0.7rem; margin-top: 12px; letter-spacing: 0.08em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><span>Y</span></div>
        <div class="badge">ADMIN</div>
        <div class="spinner-ring"></div>
        <h2>Signing Out</h2>
        <p class="sub">Your session has ended securely.</p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <p class="status">REDIRECTING TO LOGIN</p>
    </div>
    <script>
        setTimeout(function() { window.location.href = {$loginUrl}; }, 1500);
    </script>
</body>
</html>
HTML);
    }
}
