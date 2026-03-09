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
     * Uses manual validation + explicit redirect so Railway (no persistent session)
     * never gets a 500 from validateWithBag trying to call back() with no _previous.url.
     */
    public function update(Request $request): RedirectResponse
    {
        $authToken  = $request->input('auth_token') ?? $request->query('auth_token');
        $profileUrl = route('profile.edit') . ($authToken ? '?auth_token=' . urlencode($authToken) : '');

        $user = $request->user();

        // ── Manual validation ─────────────────────────────────────────────
        $errors = [];

        $currentPassword = $request->input('current_password', '');
        $newPassword     = $request->input('password', '');
        $confirmation    = $request->input('password_confirmation', '');

        // Current password
        if (empty($currentPassword)) {
            $errors['current_password'] = ['The current password field is required.'];
        } elseif (empty($user->getAuthPassword())) {
            // OAuth account with no local password set — skip the check
            // but inform clearly (they should just set a new password)
        } elseif (!Hash::check($currentPassword, $user->getAuthPassword())) {
            $errors['current_password'] = ['The current password is incorrect.'];
        }

        // New password
        if (empty($newPassword)) {
            $errors['password'] = ['The new password field is required.'];
        } elseif (strlen($newPassword) < 8) {
            $errors['password'] = ['The new password must be at least 8 characters.'];
        } elseif ($newPassword !== $confirmation) {
            $errors['password_confirmation'] = ['The password confirmation does not match.'];
        }

        if (!empty($errors)) {
            return redirect()->to($profileUrl)
                ->withErrors($errors, 'updatePassword')
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        // ── Save new password ─────────────────────────────────────────────
        $user->forceFill(['password' => Hash::make($newPassword)])->save();

        return redirect()->to($profileUrl)->with('status', 'password-updated');
    }
}

