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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            \Log::info('User login attempt - Email: ' . $request->email . ', Role: ' . ($user ? $user->role : 'null'));
            
            // Check if user exists and has correct role
            if (!$user) {
                return back()->withErrors([
                    'email' => 'Invalid credentials.'
                ]);
            }
            
            if ($user->role !== 'user') {
                \Log::info('User role check failed - Role: ' . $user->role . ', Expected: user');
                Auth::logout();
                return back()->withErrors([
                    'email' => 'This account is not authorized for user access.'
                ]);
            }
            
            \Log::info('User login successful - Redirecting to /dashboard');
            $request->session()->regenerate();
            
            // Clear any intended URL that might be from previous admin login attempts
            $request->session()->forget('url.intended');
            
            // Force redirect to user dashboard
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.'
        ]);
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}