<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        \Log::info('=== REGISTRATION START ===', [
            'ip' => $request->ip(),
            'data' => $request->except(['password', 'password_confirmation'])
        ]);

        // Manual validation so we can render view directly instead of redirect
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character (@$!%*#?&).',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            \Log::info('Validation failed', ['errors' => $validator->errors()->all()]);
            // Render the register view directly with errors (no redirect needed)
            return view('auth.register')
                ->withErrors($validator)
                ->with('_old_input', $request->except('password', 'password_confirmation'));
        }

        try {
            $validated = $validator->validated();
            \Log::info('Validation passed', ['email' => $validated['email']]);

            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            \Log::info('User created successfully', ['user_id' => $user->id]);

            // Generate OTP
            $otp = $user->generateOtp();
            \Log::info('OTP generated', ['user_id' => $user->id]);

            // Send OTP email via the configured mail transport (Brevo/SMTP ready)
            $sendResult = TransactionalMailService::sendViewDetailed(
                $user->email,
                'Verify Your Email - Yakan E-commerce',
                'emails.otp-verification',
                ['user' => $user, 'otp' => $otp]
            );

            if (!($sendResult['success'] ?? false)) {
                \Log::warning('OTP email first attempt failed, retrying once', [
                    'user_id' => $user->id,
                    'status' => $sendResult['status'] ?? null,
                    'error' => $sendResult['error'] ?? null,
                ]);

                $sendResult = TransactionalMailService::sendViewDetailed(
                    $user->email,
                    'Verify Your Email - Yakan E-commerce',
                    'emails.otp-verification',
                    ['user' => $user, 'otp' => $otp]
                );
            }

            $emailSent = (bool) ($sendResult['success'] ?? false);
            \Log::info('OTP email send final result', [
                'user_id' => $user->id,
                'sent' => $emailSent,
                'status' => $sendResult['status'] ?? null,
                'message_id' => $sendResult['message_id'] ?? null,
            ]);

            // Send welcome email (non-blocking)
            TransactionalMailService::sendView(
                $user->email,
                'Welcome to Yakan E-commerce!',
                'emails.welcome',
                ['user' => $user]
            );

            \Log::info('=== REGISTRATION SUCCESS - Rendering OTP page ===', ['email' => $user->email]);

            // Render OTP view directly instead of redirect (sessions don't persist on Railway)
            $message = $emailSent
                ? 'Account created successfully! Please check your email for the verification code.'
                : 'Account created, but we could not send your OTP email right now. Please click "Send New Code" in a few seconds.';

            return view('auth.verify-otp', [
                'user' => $user,
                $emailSent ? 'success' : 'error' => $message,
                'emailSent' => $emailSent,
            ]);

        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Render register view directly with error (no redirect)
            return view('auth.register')
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->with('_old_input', $request->except('password', 'password_confirmation'));
        }
    }
}
