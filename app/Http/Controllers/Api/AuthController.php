<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransactionalMailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

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
                'middle_initial' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $validationTime = microtime(true);

            $middlePart = $validated['middle_initial'] ?? null;
            $fullName = trim($validated['first_name'] . ' ' . 
                           ($middlePart ? $middlePart . ' ' : '') . 
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

            // Keep mobile registration consistent with web registration by generating OTP
            // and attempting to send a verification email immediately.
            $otpSent = false;
            try {
                $otp = $user->generateOtp();

                $otpSent = TransactionalMailService::sendView(
                    $user->email,
                    'Verify Your Email - Yakan E-commerce',
                    'emails.otp-verification',
                    ['user' => $user, 'otp' => $otp]
                );

                if (!$otpSent) {
                    \Log::warning('API register: OTP email send failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
            } catch (\Throwable $otpException) {
                \Log::warning('API register: OTP generation/send exception', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $otpException->getMessage(),
                ]);
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
                    'token' => $token,
                    'otp_sent' => $otpSent,
                    'otp_required' => true,
                    'email_verified' => (bool) $user->hasVerifiedEmail(),
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string|size:6',
            ]);

            $email = strtolower(trim($validated['email']));
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Email already verified.',
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
                        'token' => $token,
                    ],
                ]);
            }

            if ($user->isOtpExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                ], 422);
            }

            if ($user->isOtpAttemptsExceeded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new OTP.',
                ], 429);
            }

            if (!$user->verifyOtp($validated['otp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code. Please try again.',
                ], 422);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
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
                    'token' => $token,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('OTP verify error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.',
            ], 500);
        }
    }

    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = strtolower(trim($validated['email']));
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already verified. Please log in.',
                ], 400);
            }

            $otp = $user->generateOtp();
            $otpSent = TransactionalMailService::sendView(
                $user->email,
                'Verify Your Email - Yakan E-commerce',
                'emails.otp-verification',
                ['user' => $user, 'otp' => $otp]
            );

            return response()->json([
                'success' => true,
                'message' => $otpSent
                    ? 'New verification code sent to your email.'
                    : 'OTP regenerated, but email delivery failed. Please try again.',
                'data' => [
                    'otp_sent' => (bool) $otpSent,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('OTP resend error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.',
            ], 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $email = strtolower(trim($validated['email']));
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'We could not find a user with that email address.',
                ], 404);
            }

            $token = Str::random(64);

            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $email,
            ], false));

            $emailSent = TransactionalMailService::sendView(
                $user->email,
                'Reset Your Password - Yakan E-commerce',
                'emails.password-reset',
                [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                    'token' => $token,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $emailSent
                    ? 'We have emailed your password reset link! Please check your inbox.'
                    : 'Password reset request created, but email delivery failed. Please try again.',
                'data' => [
                    'email_sent' => (bool) $emailSent,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Forgot password error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process password reset request. Please try again.',
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $email = strtolower(trim($validated['email']));
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (!$resetRecord || !Hash::check($validated['token'], $resetRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This password reset token is invalid.',
                ], 422);
            }

            if (now()->diffInMinutes($resetRecord->created_at) > 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'This password reset token has expired. Please request a new one.',
                ], 422);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'We could not find a user with that email address.',
                ], 404);
            }

            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('password_reset_tokens')->where('email', $email)->delete();
            event(new PasswordReset($user));

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully. You can now log in.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Reset password error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
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
