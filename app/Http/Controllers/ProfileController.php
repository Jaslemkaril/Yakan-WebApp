<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        // Only update name — email is locked in the UI and cannot be changed
        $request->user()->name = $request->validated()['name'];
        $request->user()->save();

        // Preserve auth_token for Railway deployment
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = $authToken 
            ? route('profile.edit') . '?auth_token=' . urlencode($authToken)
            : route('profile.edit');

        return Redirect::to($redirectUrl)->with('status', 'profile-updated');
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
        $this->deleteAvatarFile($user->avatar);

        // Store new avatar — prefer Cloudinary (persistent on Railway)
        $cloudinary = new CloudinaryService();
        $avatarUrl = null;

        if ($cloudinary->isEnabled()) {
            $result = $cloudinary->uploadFile($request->file('avatar'), 'avatars');
            if ($result) {
                $avatarUrl = $result['url'];
                Log::info('Avatar uploaded to Cloudinary', ['user_id' => $user->id, 'url' => $avatarUrl]);
            }
        }

        // Fallback to local storage
        if (!$avatarUrl) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $avatarUrl = Storage::url($path);
        }

        $user->avatar = $avatarUrl;
        $user->save();

        // Preserve auth_token for Railway deployment
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = $authToken 
            ? route('profile.edit') . '?auth_token=' . urlencode($authToken)
            : route('profile.edit');

        return Redirect::to($redirectUrl)->with('status', 'avatar-updated');
    }

    /**
     * Delete user's profile avatar/picture.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Delete avatar from storage (Cloudinary or local)
        $this->deleteAvatarFile($user->avatar);

        // Remove avatar from database
        $user->avatar = null;
        $user->save();

        // Preserve auth_token for Railway deployment
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = $authToken 
            ? route('profile.edit') . '?auth_token=' . urlencode($authToken)
            : route('profile.edit');

        return Redirect::to($redirectUrl)->with('status', 'avatar-deleted');
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

        try {
            $user->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            Log::error('Email verification notification failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception' => $e
            ]);
            return back()->with('error', 'Failed to send verification email. Please try again later.');
        }
    }

    /**
     * Delete an avatar file from Cloudinary or local storage.
     */
    protected function deleteAvatarFile(?string $avatarUrl): void
    {
        if (!$avatarUrl) {
            return;
        }

        // Cloudinary URL — extract public_id and delete via API
        if (str_contains($avatarUrl, 'cloudinary.com')) {
            // Extract public_id: everything after /upload/ (and optional version), without extension
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+)\.\w+$/', $avatarUrl, $matches)) {
                $cloudinary = new CloudinaryService();
                $cloudinary->delete($matches[1]);
            }
            return;
        }

        // Local storage
        if (str_contains($avatarUrl, '/storage/')) {
            $path = str_replace('/storage/', 'public/', $avatarUrl);
            Storage::delete($path);
        }
    }
}
