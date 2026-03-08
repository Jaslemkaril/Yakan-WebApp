<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SendGridService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OtpVerificationController extends Controller
{
    /**
     * Show OTP verification form
     */
    public function showForm(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('register')
                ->with('error', 'Invalid verification request.');
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('register')
                ->with('error', 'User not found.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('success', 'Email already verified. Please login.');
        }

        return view('auth.verify-otp', compact('user'));
    }

    /**
     * Verify OTP code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'User not found.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('success', 'Email already verified. Please login.');
        }

        // Check if OTP is expired
        if ($user->isOtpExpired()) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.',
            ])->withInput();
        }

        // Check if attempts exceeded
        if ($user->isOtpAttemptsExceeded()) {
            return back()->withErrors([
                'otp' => 'Too many failed attempts. Please request a new OTP.',
            ])->withInput();
        }

        // Verify OTP
        if ($user->verifyOtp($request->otp)) {
            // Log the user in
            Auth::login($user);
            
            return redirect()->route('dashboard')
                ->with('success', 'Email verified successfully! Welcome to Yakan.');
        }

        return back()->withErrors([
            'otp' => 'Invalid OTP code. Please try again.',
        ])->withInput();
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return redirect()->route('register');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('register');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        // Generate new OTP
        $otp = $user->generateOtp();
        \Log::info('OTP resend - generated new OTP', ['user_id' => $user->id, 'email' => $email]);

        // Send OTP email via SendGrid HTTP API (SMTP blocked on Railway)
        $emailSent = SendGridService::sendView(
            $user->email,
            'Verify Your Email - Yakan E-commerce',
            'emails.otp-verification',
            ['user' => $user, 'otp' => $otp]
        );
        \Log::info('OTP resend attempt', ['user_id' => $user->id, 'sent' => $emailSent]);

        // Render view directly (no redirect to avoid session loss)
        $message = $emailSent
            ? 'New verification code sent to your email!'
            : 'Email sending failed. Your OTP code is: ' . $otp;

        return view('auth.verify-otp', [
            'user' => $user,
            'success' => $message,
        ]);
    }
}
