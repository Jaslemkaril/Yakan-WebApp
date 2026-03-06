<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Preserve auth_token for Railway deployment
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        if ($authToken) {
            $redirectUrl = route('profile.edit') . '?auth_token=' . urlencode($authToken);
            return redirect()->to($redirectUrl)->with('status', 'password-updated');
        }

        return back()->with('status', 'password-updated');
    }
}
