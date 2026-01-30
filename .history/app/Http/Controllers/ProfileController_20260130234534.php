<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Upload user's profile avatar/picture.
     */
    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar && strpos($user->avatar, 'storage/') !== false) {
            $oldPath = str_replace('/storage/', 'public/', $user->avatar);
            Storage::delete($oldPath);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = Storage::url($path);
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * Delete user's profile avatar/picture.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Delete avatar from storage
        if ($user->avatar && strpos($user->avatar, 'storage/') !== false) {
            $path = str_replace('/storage/', 'public/', $user->avatar);
            Storage::delete($path);
        }

        // Remove avatar from database
        $user->avatar = null;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
    }

    /**
     * Send email verification link.
     */
    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return Redirect::route('profile.edit')->with('status', 'email-already-verified');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
