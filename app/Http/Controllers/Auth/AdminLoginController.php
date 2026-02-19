<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    // Show the admin login form
    public function showLoginForm()
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        
        // Logout any non-admin user
        if (Auth::check()) {
            Auth::logout();
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
        
        if ($user->role !== 'admin') {
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
        
        return redirect('/admin/dashboard?auth_token=' . $token)->with('success', 'Welcome back, Admin!');
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login.form');
    }
}
