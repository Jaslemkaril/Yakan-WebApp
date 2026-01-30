<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            Log::info('Attempting to send verification email', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'mail_driver' => env('MAIL_MAILER'),
                'mail_host' => env('MAIL_HOST'),
            ]);

            $request->user()->sendEmailVerificationNotification();
            
            Log::info('Verification email sent successfully', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
            ]);
            
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            Log::error('Email verification notification failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_driver' => env('MAIL_MAILER'),
                'mail_host' => env('MAIL_HOST'),
            ]);
            return back()->with('error', 'Failed to send verification email. Please try again later. Error: ' . $e->getMessage());
        }
    }
}
