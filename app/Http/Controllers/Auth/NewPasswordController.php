<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     * Renders views directly instead of redirect (sessions don't persist on Railway).
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Manually validate token since we store tokens manually in PasswordResetLinkController
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return view('auth.reset-password', ['request' => $request])
                ->withErrors(['email' => 'This password reset token is invalid.'])
                ->with('_old_input', $request->only('email'));
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            return view('auth.reset-password', ['request' => $request])
                ->withErrors(['email' => 'This password reset token has expired. Please request a new one.'])
                ->with('_old_input', $request->only('email'));
        }

        // Find and update the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return view('auth.reset-password', ['request' => $request])
                ->withErrors(['email' => 'We could not find a user with that email address.'])
                ->with('_old_input', $request->only('email'));
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        event(new PasswordReset($user));

        return view('auth.user-login', [
            'status' => 'Your password has been reset successfully! You can now login with your new password.',
        ]);
    }
}
