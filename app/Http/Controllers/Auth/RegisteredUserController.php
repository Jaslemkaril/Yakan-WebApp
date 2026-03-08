<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\OtpVerificationMail;
use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_initial' => 'nullable|string|max:1',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[a-z]/',      // at least one lowercase
                    'regex:/[A-Z]/',      // at least one uppercase
                    'regex:/[0-9]/',      // at least one number
                    'regex:/[@$!%*#?&]/', // at least one special character
                ],
                'terms' => 'accepted', // Add terms validation
            ], [
                'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character (@$!%*#?&).',
                'password.min' => 'Password must be at least 8 characters long.',
                'password.confirmed' => 'Password confirmation does not match.',
                'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
            ]);
        
            \Log::info('Registration attempt', ['email' => $validated['email']]);
        
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                // Remove auto-verification - require email verification
            ]);
            
            \Log::info('User created successfully', ['user_id' => $user->id]);
            
            // Generate OTP and send email
            $otp = $user->generateOtp();
            
            // Try to send email with timeout handling
            $emailSent = false;
            try {
                \Log::info('Attempting to send OTP email', ['user_id' => $user->id, 'email' => $user->email]);
                
                // Send OTP email with timeout protection
                Mail::to($user->email)->send(new OtpVerificationMail($user, $otp));
                
                $emailSent = true;
                \Log::info('OTP email sent successfully', ['user_id' => $user->id]);
                
            } catch (\Exception $emailError) {
                \Log::error('Email sending failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $emailError->getMessage()
                ]);
                
                // Continue anyway - user can request OTP resend later
                $emailSent = false;
            }
        
            // Send welcome email asynchronously (don't block registration if it fails)
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
                \Log::info('Welcome email sent', ['user_id' => $user->id]);
            } catch (\Exception $welcomeError) {
                \Log::error('Welcome email failed', [
                    'user_id' => $user->id,
                    'error' => $welcomeError->getMessage()
                ]);
            }
        
            // Redirect to OTP verification page regardless of email status
            $message = $emailSent 
                ? 'Account created successfully! Please check your email for the verification code.'
                : 'Account created! Email sending failed - your OTP code is: ' . $otp . ' (save this!)';
                
            return redirect()->route('verification.otp.form', ['email' => $user->email])
                ->with($emailSent ? 'success' : 'warning', $message);
                
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }
    
}
