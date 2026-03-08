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
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
        }

        $targetUrl = Socialite::driver($provider)->redirect()->getTargetUrl();
        $providerName = ucfirst($provider);
        $icon = $provider === 'google'
            ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="28" height="28"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';

        return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signing in with {$providerName}...</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #6b0000 0%, #8b0000 45%, #3d0000 100%);
            font-family: 'Inter', system-ui, sans-serif;
        }
        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.35);
            animation: fadeUp 0.4s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #800000, #a00000);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .logo span { color: white; font-weight: 800; font-size: 26px; }
        h2 { color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 6px; }
        .sub { color: rgba(255,220,220,0.8); font-size: 0.875rem; margin-bottom: 32px; }
        .provider-row {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: white;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 28px;
        }
        .provider-row span { font-size: 0.9rem; font-weight: 600; color: #333; }
        .spinner {
            width: 36px; height: 36px;
            border: 3px solid rgba(255,255,255,0.2);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .status { color: rgba(255,220,220,0.75); font-size: 0.8rem; letter-spacing: 0.06em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><span>Y</span></div>
        <h2>Signing you in</h2>
        <p class="sub">You're being securely redirected</p>
        <div class="provider-row">
            {$icon}
            <span>Continue with {$providerName}</span>
        </div>
        <div class="spinner"></div>
        <p class="status">Connecting to {$providerName}...</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = {json_encode($targetUrl)};
        }, 800);
    </script>
</body>
</html>
HTML);
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
