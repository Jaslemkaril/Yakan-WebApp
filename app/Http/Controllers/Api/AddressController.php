<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Get all addresses for authenticated user
     */
    public function index()
    {
        $addresses = UserAddress::forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Get default address
     */
    public function getDefault()
    {
        $address = UserAddress::forUser(Auth::id())
            ->default()
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'No default address set',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    /**
     * Create a new address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // If this is the first address or marked as default, set it as default
        $existingCount = UserAddress::forUser(Auth::id())->count();
        if ($existingCount === 0 || $request->boolean('is_default')) {
            $validated['is_default'] = true;
            // Remove default from other addresses
            UserAddress::forUser(Auth::id())->update(['is_default' => false]);
        }

        $address = UserAddress::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Update an address
     */
    public function update(Request $request, UserAddress $address)
    {
        // Check authorization
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Delete an address
     */
    public function destroy(UserAddress $address)
    {
        // Check authorization
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = UserAddress::forUser(Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Set an address as default
     */
    public function setDefault(UserAddress $address)
    {
        // Check authorization
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Default address updated',
            'data' => $address,
        ]);
    }
}
