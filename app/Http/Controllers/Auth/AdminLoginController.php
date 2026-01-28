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
        // Clear any intended URL that might be from previous attempts
        session()->forget('url.intended');
        return view('auth.admin-login'); // Make sure this blade exists
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

        // Attempt login using the 'admin' guard
        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            
            // Check if user has admin role
            if ($user && $user->role === 'admin') {
                $request->session()->regenerate();
                
                // Set the intended URL explicitly to admin dashboard
                session()->put('url.intended', '/admin/dashboard');
                
                // Force redirect to admin dashboard
                \Log::info('Admin login successful, redirecting to: /admin/dashboard');
                \Log::info('Current auth guards: admin=' . (Auth::guard('admin')->check() ? 'true' : 'false'));
                return redirect('/admin/dashboard')->with('success', 'Welcome back, Admin!');
            }
            
            // Logout if not admin
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Access denied. Admin privileges required.',
            ])->onlyInput('email');
        }

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
