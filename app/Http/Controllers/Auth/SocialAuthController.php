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
        // Debug: Log that this method is being called
        \Log::info('OAuth redirect method called', ['provider' => $provider]);
        
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'Unsupported authentication provider.');
        }

        try {
            // Debug: Check if credentials are loaded
            $clientId = config('services.' . $provider . '.client_id');
            $clientSecret = config('services.' . $provider . '.client_secret');
            $redirectUri = config('services.' . $provider . '.redirect');
            
            \Log::info('OAuth config check', [
                'provider' => $provider,
                'client_id' => $clientId ? 'SET' : 'NOT SET',
                'client_secret' => $clientSecret ? 'SET' : 'NOT SET',
                'redirect_uri' => $redirectUri
            ]);
            
            if (empty($clientId) || empty($clientSecret)) {
                // Redirect to sandbox mode if credentials not configured
                return redirect()->route('auth.social.sandbox', ['provider' => $provider]);
            }

            \Log::info('Attempting Socialite redirect', ['provider' => $provider]);
            
            // Get the redirect URL from Socialite and do an explicit redirect
            // This avoids Socialite's redirect response being rendered as text
            $driver = Socialite::driver($provider);
            $redirectUrl = $driver->redirect()->getTargetUrl();
            
            \Log::info('OAuth redirect URL generated', ['url' => substr($redirectUrl, 0, 100) . '...']);
            
            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            \Log::error('OAuth redirect exception', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to sandbox mode on error
            return redirect()->route('auth.social.sandbox', ['provider' => $provider]);
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

            $socialUser = Socialite::driver($provider)->user();

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

            // Login the user
            Auth::login($user, true);

            // Explicitly regenerate and save session to ensure persistence
            session()->regenerate();
            session()->save();

            // Generate auth token (fallback for session/cookie issues on Railway)
            $token = bin2hex(random_bytes(32));
            \DB::table('auth_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'token' => $token,
                    'expires_at' => now()->addHours(24),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Store token in session as additional fallback
            session(['auth_token' => $token]);

            \Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'provider' => $provider,
                'authenticated' => Auth::check(),
                'session_id' => session()->getId(),
            ]);

            // Redirect with auth_token fallback (same pattern as regular login)
            $redirectUrl = route('welcome') . '?auth_token=' . $token;
            return redirect($redirectUrl)
                ->with('success', 'Successfully logged in with ' . ucfirst($provider) . '!');

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
        $url = Socialite::driver('google')->redirect()->getTargetUrl();
        return redirect()->away($url);
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
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
        $url = Socialite::driver('facebook')->redirect()->getTargetUrl();
        return redirect()->away($url);
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
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