<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountDeletionController extends Controller
{
    /**
     * Show the data deletion page
     */
    public function show()
    {
        return view('data-deletion');
    }

    /**
     * Delete the authenticated user's account
     */
    public function delete(Request $request)
    {
        // Must be authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to delete your account.');
        }

        // Validate password
        $request->validate([
            'password' => 'required|string',
            'confirm' => 'required|accepted',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password. Please try again.');
        }

        // Get user email before deletion
        $userEmail = $user->email;
        $userName = $user->name;

        try {
            // Store deletion data for audit log
            $deletionData = [
                'user_id' => $user->id,
                'email' => $userEmail,
                'name' => $userName,
                'deleted_at' => now(),
            ];

            // Send deletion confirmation email (before deleting)
            $this->sendDeletionEmail($userEmail, $userName);

            // Delete user's orders (optionally anonymize instead of delete)
            if (method_exists($user, 'orders')) {
                $user->orders()->each(function ($order) {
                    $order->update([
                        'user_id' => null,
                        'customer_email' => null,
                        'customer_name' => 'Deleted User',
                    ]);
                });
            }

            // Delete custom orders
            if (method_exists($user, 'customOrders')) {
                $user->customOrders()->each(function ($order) {
                    $order->update([
                        'user_id' => null,
                        'customer_email' => null,
                        'customer_name' => 'Deleted User',
                    ]);
                });
            }

            // Delete wishlist items
            if (method_exists($user, 'wishlists')) {
                $user->wishlists()->delete();
            }

            // Delete addresses
            if (method_exists($user, 'addresses')) {
                $user->addresses()->delete();
            }

            // Delete cart items
            if (method_exists($user, 'cartItems')) {
                $user->cartItems()->delete();
            }

            // Delete notifications
            if (method_exists($user, 'notifications')) {
                $user->notifications()->delete();
            }

            // Delete the user
            $user->delete();

            // Log the deletion
            \Log::info('User account deleted', $deletionData);

            // Logout the user
            Auth::logout();

            return redirect()->route('welcome')
                ->with('success', 'Your account has been successfully deleted. Confirmation email has been sent to ' . $userEmail);

        } catch (\Exception $e) {
            \Log::error('Error deleting user account', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An error occurred while deleting your account. Please contact support.');
        }
    }

    /**
     * Send deletion confirmation email
     */
    private function sendDeletionEmail($email, $name)
    {
        try {
            $emailView = view('emails.account-deleted', [
                'name' => $name,
                'deletion_date' => now()->format('F d, Y H:i:s'),
                'support_email' => 'eh202202743@wmsu.edu.ph',
            ])->render();

            \Mail::raw($emailView, function ($message) use ($email, $name) {
                $message->to($email)
                    ->subject('Your Yakan Account Has Been Deleted');
            });
        } catch (\Exception $e) {
            \Log::warning('Could not send deletion email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the deletion if email fails
        }
    }
}
