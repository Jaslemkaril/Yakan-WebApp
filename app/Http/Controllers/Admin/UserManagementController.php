<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display a listing of all users
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Search by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status (active/inactive based on last login)
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->whereNotNull('last_login_at')
                          ->where('last_login_at', '>', now()->subDays(30));
                } elseif ($request->status === 'inactive') {
                    $query->where(function($q) {
                        $q->whereNull('last_login_at')
                          ->orWhere('last_login_at', '<=', now()->subDays(30));
                    });
                }
            }

            $users = $query->orderByDesc('created_at')
                           ->paginate(15)
                           ->withQueryString();

            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            // Log the error and return a simple fallback
            \Log::error('UserManagementController index error: ' . $e->getMessage());
            
            // Fallback to simple user query
            $users = User::orderByDesc('created_at')->paginate(15);
            return view('admin.users.index', compact('users'));
        }
    }

    /**
     * Show detailed user information
     */
    public function show(User $user)
    {
        try {
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            \Log::error('UserManagementController show error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Unable to load user details.');
        }
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|in:admin,user',
            ]);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            return redirect()->back()->with('success', 'User information updated successfully!');
        } catch (\Exception $e) {
            \Log::error('UserManagementController update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update user information.');
        }
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        try {
            return view('admin.users.edit', compact('user'));
        } catch (\Exception $e) {
            \Log::error('UserManagementController edit error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Unable to load edit form.');
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        try {
            return view('admin.users.create');
        } catch (\Exception $e) {
            \Log::error('UserManagementController create error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Unable to load create form.');
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => 'required|in:user,admin',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $validated['password'] = bcrypt($validated['password']);
            $validated['email_verified_at'] = now();

            User::create($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            \Log::error('UserManagementController store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create user.');
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        try {
            if ($user->last_login_at) {
                $user->last_login_at = null;
                $message = 'User deactivated successfully!';
            } else {
                $user->last_login_at = now();
                $message = 'User activated successfully!';
            }
            $user->save();

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('UserManagementController toggleStatus error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to toggle user status.');
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(Request $request, User $user)
    {
        try {
            // Prevent deletion of the currently authenticated admin
            if (auth()->guard('admin')->id() && $user->id === auth()->guard('admin')->id()) {
                return redirect()->back()->with('error', 'You cannot delete your own account.');
            }

            $user->delete();

            // Ensure session is saved before redirect
            $request->session()->save();
            
            // Get auth_token if present to append to redirect
            $authToken = $request->input('auth_token') ?? $request->attributes->get('admin_auth_token');
            $redirectUrl = route('admin.users.index');
            if ($authToken) {
                $redirectUrl .= '?auth_token=' . $authToken;
            }

            return redirect($redirectUrl)->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('UserManagementController destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete user.');
        }
    }
}
