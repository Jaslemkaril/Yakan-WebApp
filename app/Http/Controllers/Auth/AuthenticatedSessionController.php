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

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            \Log::info('Auth::attempt successful', ['user_id' => Auth::id()]);
            
            $request->session()->regenerate();
            
            \Log::info('Session regenerated', ['session_id' => session()->getId()]);
            
            // Clear any intended URL that might be from previous admin login attempts
            $request->session()->forget('url.intended');
            
            // Force redirect to user dashboard
            return redirect('/dashboard')->with('success', 'Welcome back!');
        }

        \Log::warning('Login failed: Invalid password', ['email' => $request->email]);
        
        return back()->withErrors([
            'email' => 'The password is incorrect.'
        ])->withInput($request->only('email'));
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