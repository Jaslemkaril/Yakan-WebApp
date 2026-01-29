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

        // Attempt login using the 'web' guard instead of 'admin'
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            \Log::info('Auth attempt successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Check if user has admin role
            if ($user && $user->role === 'admin') {
                $request->session()->regenerate();
                
                \Log::info('Admin login successful', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => session()->getId(),
                ]);
                
                return redirect('/admin/dashboard')->with('success', 'Welcome back, Admin!');
            }
            
            // Logout if not admin
            \Log::warning('Login attempt by non-admin user', [
                'email' => $user->email,
                'role' => $user->role,
            ]);
            
            Auth::logout();
            return back()->withErrors([
                'email' => 'Access denied. Admin privileges required.',
            ])->onlyInput('email');
        }

        \Log::warning('Admin login failed - invalid credentials', ['email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
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
