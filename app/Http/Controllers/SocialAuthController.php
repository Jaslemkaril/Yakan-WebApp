<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the OAuth provider authentication page.
     */
    public function redirect($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
        }

        return redirect()->away(Socialite::driver($provider)->redirect()->getTargetUrl());
    }

    /**
     * Handle the callback from the OAuth provider.
     */
    public function callback($provider)
    {
        try {
            // Validate provider
            if (!in_array($provider, ['google', 'facebook'])) {
                return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
            }

            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = User::firstOrCreate([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ], [
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)), // Random password
                'email_verified_at' => now(), // Social accounts are considered verified
                'role' => 'user', // Default role
            ]);

            // Login the user
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Successfully logged in with ' . ucfirst($provider) . '!');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again or use another login method.');
        }
    }

    /**
     * Show available social login options.
     */
    public function showOptions()
    {
        return view('auth.social-login');
    }
}
