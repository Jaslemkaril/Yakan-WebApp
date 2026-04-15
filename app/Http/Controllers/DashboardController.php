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
        
        // Check if user is admin/order_staff and redirect to admin dashboard
        if ($user && in_array((string) $user->role, ['admin', 'order_staff'], true)) {
            \Log::info('DashboardController - Admin/staff detected, redirecting to /admin/dashboard');
            return redirect('/admin/dashboard');
        }
        
        \Log::info('DashboardController - Regular user, showing user dashboard');
        // Regular user dashboard
        return view('dashboard', [
            'user' => $user
        ]);
    }
}
