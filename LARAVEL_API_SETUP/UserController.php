<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get current user profile
     */
    public function getProfile()
    {
        try {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'profile_image' => 'nullable|image|max:2048',
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profiles', 'public');
                $validated['profile_image'] = $imagePath;
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all saved addresses for user
     */
    public function getSavedAddresses()
    {
        try {
            $user = auth()->user();

            $addresses = Address::where('user_id', $user->id)
                               ->orderBy('is_default', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->get();

            return response()->json([
                'success' => true,
                'message' => 'Addresses retrieved successfully',
                'data' => $addresses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new saved address
     */
    public function createAddress(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'label' => 'required|string|max:255', // Home, Office, etc.
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'street_address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'required|string|max:10',
                'is_default' => 'boolean',
            ]);

            // If this is default, unset others
            if ($validated['is_default'] ?? false) {
                Address::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $address = Address::create([
                'user_id' => $user->id,
                ...$validated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => $address,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update saved address
     */
    public function updateAddress(Request $request, $addressId)
    {
        try {
            $user = auth()->user();

            $address = Address::where('id', $addressId)
                             ->where('user_id', $user->id)
                             ->firstOrFail();

            $validated = $request->validate([
                'label' => 'nullable|string|max:255',
                'full_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'street_address' => 'nullable|string|max:255',
                'barangay' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'is_default' => 'boolean',
            ]);

            // If this is default, unset others
            if ($validated['is_default'] ?? false) {
                Address::where('user_id', $user->id)
                       ->where('id', '!=', $addressId)
                       ->update(['is_default' => false]);
            }

            $address->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete saved address
     */
    public function deleteAddress($addressId)
    {
        try {
            $user = auth()->user();

            $address = Address::where('id', $addressId)
                             ->where('user_id', $user->id)
                             ->firstOrFail();

            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address: ' . $e->getMessage(),
            ], 500);
        }
    }
}
