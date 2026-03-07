<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // API Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // Attempt login
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Invalid email or password'
                ], 401);
            }

            $user = Auth::user();
            
            // Update last login timestamp
            if ($user) {
                $user->update(['last_login_at' => now()]);
            }

            if (!$user) {
                Log::error('Login failed: Auth::user() returned null', [
                    'input' => $request->all()
                ]);
                return response()->json([
                    'message' => 'User not found'
                ], 500);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Please verify your email before logging in'
                ], 403);
            }

            // Create token (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            if (!$token) {
                Log::error('Login failed: createToken returned null', [
                    'user' => $user
                ]);
                return response()->json([
                    'message' => 'Token generation failed'
                ], 500);
            }

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return response()->json([
                'message' => 'Server error, check logs'
            ], 500);
        }
    }

    // API Logout
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete(); // Revoke all tokens
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
