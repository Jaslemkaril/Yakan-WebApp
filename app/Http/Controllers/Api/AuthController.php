<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            // Simplified validation for better performance
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_initial' => 'nullable|string|max:2',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed', // Simplified password rules
            ]);

            $validationTime = microtime(true);

            $fullName = trim($validated['first_name'] . ' ' . 
                           ($validated['middle_initial'] ?? null ? $validated['middle_initial'] . '. ' : '') . 
                           $validated['last_name']);

            $user = \App\Models\User::create([
                'name' => $fullName,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);
            
            $createUserTime = microtime(true);

            // Check if user was created successfully
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user account'
                ], 500);
            }

            // Create token
            try {
                $token = $user->createToken('api-token')->plainTextToken;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create authentication token: ' . $e->getMessage()
                ], 500);
            }
            
            $tokenTime = microtime(true);
            
            // Log performance metrics
            $totalTime = microtime(true) - $startTime;
            \Log::info('API Registration performance (optimized)', [
                'total_time' => round($totalTime * 1000, 2) . 'ms',
                'validation' => round(($validationTime - $startTime) * 1000, 2) . 'ms',
                'user_creation' => round(($createUserTime - $validationTime) * 1000, 2) . 'ms',
                'token_creation' => round(($tokenTime - $createUserTime) * 1000, 2) . 'ms',
                'response' => round((microtime(true) - $tokenTime) * 1000, 2) . 'ms',
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? 'User'),
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $email    = strtolower(trim($credentials['email']));
            $throttleKey = 'login:' . $email . '|' . $request->ip();

            // Check if this email+IP has been locked out (10 failed attempts = 15 min lock)
            if (\Cache::has($throttleKey . ':locked')) {
                $seconds = \Cache::get($throttleKey . ':locked');
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed login attempts. Try again in ' . ceil($seconds / 60) . ' minute(s).',
                ], 429);
            }

            $credentials['email'] = $email;

            if (Auth::attempt($credentials)) {
                // Clear failed attempt counter on success
                \Cache::forget($throttleKey . ':attempts');
                \Cache::forget($throttleKey . ':locked');

                $user  = Auth::user();
                $token = $user->createToken('api-token')->plainTextToken;

                \Log::info('API Login successful', ['user_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id'         => $user->id,
                            'first_name' => $user->first_name,
                            'last_name'  => $user->last_name,
                            'name'       => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                            'email'      => $user->email,
                            'role'       => $user->role,
                            'created_at' => $user->created_at,
                        ],
                        'token' => $token,
                    ],
                ]);
            }

            // Track failed attempts
            $attempts = (int) \Cache::get($throttleKey . ':attempts', 0) + 1;
            \Cache::put($throttleKey . ':attempts', $attempts, now()->addMinutes(15));

            if ($attempts >= 10) {
                \Cache::put($throttleKey . ':locked', 900, now()->addMinutes(15)); // 15 min lockout
                \Log::warning('API account locked after failed attempts', ['email' => $email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Account temporarily locked after too many failed attempts. Try again in 15 minutes.',
                ], 429);
            }

            $remaining = 10 - $attempts;
            \Log::warning('API Login failed', ['email' => $email, 'attempts' => $attempts]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.' . ($remaining <= 3 ? ' ' . $remaining . ' attempt(s) remaining before lockout.' : ''),
            ], 401);
            
        } catch (\Exception $e) {
            \Log::error('Login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? 'User'),
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
}
