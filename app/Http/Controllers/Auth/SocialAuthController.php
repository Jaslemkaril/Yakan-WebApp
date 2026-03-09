<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
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

        try {
            $clientId     = config('services.' . $provider . '.client_id');
            $clientSecret = config('services.' . $provider . '.client_secret');

            if (empty($clientId) || empty($clientSecret)) {
                return redirect()->route('auth.social.sandbox', ['provider' => $provider]);
            }

            $targetUrl    = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
            $providerName = ucfirst($provider);
            $icon = $provider === 'google'
                ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="28" height="28"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';

            $targetUrlJson = json_encode($targetUrl);

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
            background: white; border-radius: 12px;
            padding: 12px 20px; margin-bottom: 28px;
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
        setTimeout(function() { window.location.href = {$targetUrlJson}; }, 800);
    </script>
</body>
</html>
HTML);

        } catch (\Exception $e) {
            \Log::error('OAuth redirect exception', ['provider' => $provider, 'error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Handle the callback from the OAuth provider.
     */
    public function callback($provider)
    {
        try {
            // Validate provider
            if (!in_array($provider, ['google', 'facebook'])) {
                \Log::error('Invalid provider', ['provider' => $provider]);
                return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
            }

            \Log::info('Starting OAuth callback', ['provider' => $provider]);

            // Use stateless() to match the redirect — skips state validation
            $socialUser = Socialite::driver($provider)->stateless()->user();

            \Log::info('Social user retrieved', [
                'provider' => $provider,
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
            ]);

            // Find or create user
            $user = User::where('provider', $provider)
                        ->where('provider_id', $socialUser->getId())
                        ->first();

            if ($user) {
                \Log::info('Existing user found', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                ]);
                
                // Update user info if needed
                $user->update([
                    'avatar' => $socialUser->getAvatar(),
                    'provider_token' => $socialUser->token,
                ]);
            } else {
                \Log::info('User not found with provider, checking by email', [
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                ]);

                // Check if user exists with this email
                $existingUser = User::where('email', $socialUser->getEmail())->first();

                if ($existingUser) {
                    \Log::info('Linking provider to existing user', [
                        'user_id' => $existingUser->id,
                        'email' => $socialUser->getEmail(),
                        'provider' => $provider,
                    ]);

                    // Link this provider to existing account
                    $existingUser->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'provider_token' => $socialUser->token,
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                    $user = $existingUser;
                } else {
                    \Log::info('Creating new user', [
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'provider' => $provider,
                    ]);

                    // Create new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'provider_token' => $socialUser->token,
                        'avatar' => $socialUser->getAvatar(),
                        'password' => bcrypt(Str::random(24)),
                        'email_verified_at' => now(),
                        'role' => 'user',
                    ]);

                    \Log::info('New user created', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'provider' => $provider,
                    ]);
                }
            }

            // Login the user with remember=true for persistent cookie
            Auth::login($user, true);
            
            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Regenerate session ID to prevent session fixation
            // Do NOT call session()->save() — let StartSession middleware handle persistence
            session()->regenerate();

            // Generate auth token as fallback for Railway cookie issues
            $token = bin2hex(random_bytes(32));
            \DB::table('auth_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'token' => $token,
                    'expires_at' => now()->addDays(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Store token in session for TokenAuth middleware fallback
            session(['auth_token' => $token]);

            \Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'provider' => $provider,
                'authenticated' => Auth::check(),
                'session_id' => session()->getId(),
            ]);

            // Return a branded loading page instead of a plain redirect response.
            // PHP redirect() produces a plain "Redirecting to..." HTML body; using JS
            // redirect avoids that and gives users a proper loading screen.
            $redirectUrl = route('welcome') . '?auth_token=' . $token;
            $providerName = ucfirst($provider);
            $userName = htmlspecialchars($user->name ?? 'there', ENT_QUOTES, 'UTF-8');
            $redirectUrlJs = json_encode($redirectUrl);

            return response(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signed in — Yakan</title>
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
            background: rgba(255,255,255,0.09);
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
        .check-circle {
            width: 52px; height: 52px;
            background: rgba(16,185,129,0.2);
            border: 2px solid rgba(16,185,129,0.5);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            animation: pop 0.4s ease-out 0.2s both;
        }
        @keyframes pop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .check-circle svg { width: 26px; height: 26px; }
        h2 { color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 6px; }
        .sub { color: rgba(255,220,220,0.75); font-size: 0.875rem; margin-bottom: 28px; }
        .progress-bar {
            height: 3px;
            background: rgba(255,255,255,0.15);
            border-radius: 2px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.6), white);
            border-radius: 2px;
            animation: fill 1.2s ease-out forwards;
        }
        @keyframes fill { from { width: 0%; } to { width: 100%; } }
        .status { color: rgba(255,220,220,0.6); font-size: 0.75rem; margin-top: 12px; letter-spacing: 0.05em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><span>Y</span></div>
        <div class="check-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h2>Welcome, {$userName}!</h2>
        <p class="sub">Signed in with {$providerName} — loading your account…</p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <p class="status">REDIRECTING</p>
    </div>
    <script>
        setTimeout(function() { window.location.href = {$redirectUrlJs}; }, 1200);
    </script>
</body>
</html>
HTML);

        } catch (\Exception $e) {
            \Log::error('OAuth callback error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return redirect()->away($url);
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = $this->findOrCreateUser($googleUser, 'google');
            
            Auth::login($user, true);
            
            return redirect()->intended(route('welcome'))
                ->with('status', 'Successfully logged in with Google!');
                
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Failed to login with Google. Please try again.']);
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return redirect()->away($url);
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            
            $user = $this->findOrCreateUser($facebookUser, 'facebook');
            
            Auth::login($user, true);
            
            return redirect()->intended(route('welcome'))
                ->with('status', 'Successfully logged in with Facebook!');
                
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Failed to login with Facebook. Please try again.']);
        }
    }

    /**
     * Find or create user from social provider
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // Check if user already exists with this provider ID
        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if ($user) {
            // Update user info if needed
            $user->update([
                'avatar' => $socialUser->getAvatar(),
                'provider_token' => $socialUser->token,
            ]);
            return $user;
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Link this provider to existing account
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $existingUser;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'avatar' => $socialUser->getAvatar(),
            'password' => Hash::make(Str::random(24)), // Random password for OAuth users
            'email_verified_at' => now(), // Auto-verify OAuth users
        ]);
    }

    /**
     * Show sandbox login page for testing social authentication
     */
    public function sandbox($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
        }

        return view('auth.social-sandbox', [
            'provider' => $provider
        ]);
    }

    /**
     * Handle sandbox login (simulates OAuth callback)
     */
    public function sandboxLogin($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
        }

        $name = request('name');
        $email = request('email');

        if (empty($name) || empty($email)) {
            return redirect()->back()->with('error', 'Name and email are required.');
        }

        // Create a fake provider ID based on email
        $providerId = 'sandbox_' . md5($email . $provider);

        try {
            // Find or create user
            $user = User::firstOrCreate([
                'provider' => $provider,
                'provider_id' => $providerId,
            ], [
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
                'role' => 'user',
                'avatar' => $provider === 'google' 
                    ? 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4285F4&color=fff'
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1877F2&color=fff',
                'provider_token' => 'sandbox_token_' . Str::random(40),
            ]);

            // Update existing user if needed
            if (!$user->wasRecentlyCreated) {
                $user->update([
                    'name' => $name,
                    'avatar' => $provider === 'google' 
                        ? 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4285F4&color=fff'
                        : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1877F2&color=fff',
                ]);
            }

            // Login the user
            Auth::login($user, true);

            \Log::info('Sandbox login successful', [
                'user_id' => $user->id,
                'provider' => $provider,
                'email' => $email
            ]);

            return redirect()->intended(route('welcome'))
                ->with('success', '🧪 Sandbox Mode: Successfully logged in with ' . ucfirst($provider) . '!');

        } catch (\Exception $e) {
            \Log::error('Sandbox login error', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Sandbox login failed. Please try again.');
        }
    }
}