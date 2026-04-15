<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        \Log::info('DashboardController - User: ' . ($user ? $user->email : 'null') . ', Role: ' . ($user ? $user->role : 'null'));
        
        if ($user && (string) $user->role === 'admin') {
            \Log::info('DashboardController - Admin detected, redirecting to /admin/dashboard');
            return redirect('/admin/dashboard');
        }

        if ($user && (string) $user->role === 'order_staff') {
            \Log::info('DashboardController - Order staff detected, redirecting to /staff/dashboard');
            return redirect('/staff/dashboard');
        }
        
        \Log::info('DashboardController - Regular user, showing user dashboard');
        // Regular user dashboard
        return view('dashboard', [
            'user' => $user
        ]);
    }
}
