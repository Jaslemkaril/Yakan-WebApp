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
                        'name' => $user->first_name . ' ' . $user->last_name,
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
            \Log::info('Login attempt', [
                'email' => $request->email,
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
            ]);

            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('api-token')->plainTextToken;
                
                \Log::info('Login successful', ['user_id' => $user->id]);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'created_at' => $user->created_at,
                        ],
                        'token' => $token
                    ]
                ]);
            }

            \Log::warning('Login failed - invalid credentials', ['email' => $request->email]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
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
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]
        ]);
    }
}
