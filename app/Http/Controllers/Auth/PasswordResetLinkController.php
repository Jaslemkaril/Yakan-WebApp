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
     * Using SendGrid HTTP API instead of SMTP (blocked on Railway).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
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

        // Send email via SendGrid HTTP API
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

        if ($emailSent) {
            return back()->with('status', 'We have emailed your password reset link!');
        } else {
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again or contact support.']);
        }
    }
}
