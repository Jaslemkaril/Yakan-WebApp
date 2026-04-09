<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SendGridService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
    * Handle an incoming password reset link request.
    * Uses the configured Laravel mail transport (Brevo/SMTP ready).
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Render view directly with error (no redirect) 
            return view('auth.forgot-password')
                ->withErrors(['email' => 'We could not find a user with that email address.'])
                ->with('_old_input', $request->only('email'));
        }

        // Generate password reset token
        $token = Str::random(64);

        // Delete any existing password reset tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Store the new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Generate reset URL
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ], false));

        \Log::info('Password reset link generated', [
            'email' => $user->email,
            'url' => $resetUrl,
        ]);

        // Send email via configured mail transport
        $emailSent = SendGridService::sendView(
            $user->email,
            'Reset Your Password - Yakan E-commerce',
            'emails.password-reset',
            [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => $token,
            ]
        );

        \Log::info('Password reset email send attempt', [
            'email' => $user->email,
            'sent' => $emailSent,
        ]);

        // Render view directly instead of redirect (sessions don't persist on Railway)
        if ($emailSent) {
            return view('auth.forgot-password', [
                'status' => 'We have emailed your password reset link! Please check your inbox (including spam folder).',
                'resetUrl' => $resetUrl,
            ]);
        } else {
            // Email failed - show the reset link directly as fallback
            return view('auth.forgot-password', [
                'status' => 'We could not send the email, but you can use the link below to reset your password.',
                'resetUrl' => $resetUrl,
            ]);
        }
    }
}
