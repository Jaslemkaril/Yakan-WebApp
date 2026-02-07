<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SocialAuthController extends Controller
{
    /**
     * Handle Google OAuth login from mobile app
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photo' => 'nullable|url',
            'google_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $googleId = $request->google_id;
            $email = $request->email;
            $name = $request->name;
            $photo = $request->photo;

            // Find user by Google ID or email
            $user = User::where('provider', 'google')
                       ->where('provider_id', $googleId)
                       ->first();

            if (!$user) {
                // Check if user exists with this email
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {
                    // Link Google to existing account
                    $existingUser->update([
                        'provider' => 'google',
                        'provider_id' => $googleId,
                        'avatar' => $photo,
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'provider' => 'google',
                        'provider_id' => $googleId,
                        'avatar' => $photo,
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                        'role' => 'user',
                    ]);
                }
            } else {
                // Update existing user info
                $user->update([
                    'name' => $name,
                    'avatar' => $photo,
                ]);
            }

            // Generate authentication token
            $token = Str::random(60);
            $user->remember_token = hash('sha256', $token);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'role' => $user->role,
                    ],
                    'token' => $token,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Google OAuth API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Facebook OAuth login from mobile app
     */
    public function facebookLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photo' => 'nullable|url',
            'facebook_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $facebookId = $request->facebook_id;
            $email = $request->email;
            $name = $request->name;
            $photo = $request->photo;

            // Find user by Facebook ID or email
            $user = User::where('provider', 'facebook')
                       ->where('provider_id', $facebookId)
                       ->first();

            if (!$user) {
                // Check if user exists with this email
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {
                    // Link Facebook to existing account
                    $existingUser->update([
                        'provider' => 'facebook',
                        'provider_id' => $facebookId,
                        'avatar' => $photo,
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'provider' => 'facebook',
                        'provider_id' => $facebookId,
                        'avatar' => $photo,
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                        'role' => 'user',
                    ]);
                }
            } else {
                // Update existing user info
                $user->update([
                    'name' => $name,
                    'avatar' => $photo,
                ]);
            }

            // Generate authentication token
            $token = Str::random(60);
            $user->remember_token = hash('sha256', $token);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'role' => $user->role,
                    ],
                    'token' => $token,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Facebook OAuth API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
