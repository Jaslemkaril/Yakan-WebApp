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
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            Log::error('Email verification notification failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'exception' => $e
            ]);
            return back()->with('error', 'Failed to send verification email. Please try again later.');
        }
    }
}
