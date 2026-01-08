<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Display all addresses for the authenticated user
     */
    public function index()
    {
        $addresses = UserAddress::forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address
     */
    public function create()
    {
        return view('addresses.create');
    }

    /**
     * Store a newly created address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'formatted_address' => 'required|string|max:255',
            'region' => 'required|string|max:500',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        // Parse region field (format: "Mindanao, Zamboanga Del Sur, Zamboanga City, Tumaga")
        $regionParts = array_map('trim', explode(',', $validated['region']));
        
        // Map form fields to database columns
        $addressData = [
            'label' => $validated['label'],
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'street' => $validated['formatted_address'],
            'barangay' => $regionParts[3] ?? null,
            'city' => $regionParts[2] ?? '',
            'province' => $regionParts[1] ?? '',
            'postal_code' => $validated['postal_code'],
            'user_id' => Auth::id(),
        ];

        // If this is the first address or marked as default, set it as default
        $existingCount = UserAddress::forUser(Auth::id())->count();
        if ($existingCount === 0 || $request->boolean('is_default')) {
            $addressData['is_default'] = true;
            // Remove default from other addresses
            UserAddress::forUser(Auth::id())->update(['is_default' => false]);
        }

        $address = UserAddress::create($addressData);

        // Check if request came from checkout
        if ($request->has('from_checkout') || str_contains($request->header('referer', ''), 'checkout')) {
            return redirect()->route('cart.checkout')
                ->with('success', 'Address added successfully!');
        }

        return redirect()->route('addresses.index')
            ->with('success', 'Address added successfully!');
    }

    /**
     * Show the form for editing an address
     */
    public function edit(UserAddress $address)
    {
        $this->authorize('update', $address);
        
        // Return just the form partial for the modal
        return view('addresses.edit', compact('address'));
    }

    /**
     * Update the specified address
     */
    public function update(Request $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'formatted_address' => 'required|string|max:255',
            'region' => 'required|string|max:500',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        // Parse region field (format: "Mindanao, Zamboanga Del Sur, Zamboanga City, Tumaga")
        $regionParts = array_map('trim', explode(',', $validated['region']));

        // Map form fields to database columns
        $addressData = [
            'label' => $validated['label'],
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'street' => $validated['formatted_address'],
            'barangay' => $regionParts[3] ?? null,
            'city' => $regionParts[2] ?? '',
            'province' => $regionParts[1] ?? '',
            'postal_code' => $validated['postal_code'],
        ];

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        $address->update($addressData);

        // Check if request came from checkout
        if ($request->has('from_checkout') || str_contains($request->header('referer', ''), 'checkout')) {
            return redirect()->route('cart.checkout')
                ->with('success', 'Address updated successfully!');
        }

        return redirect()->route('addresses.index')
            ->with('success', 'Address updated successfully!');
    }

    /**
     * Delete the specified address
     */
    public function destroy(UserAddress $address)
    {
        $this->authorize('delete', $address);

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = UserAddress::forUser(Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return redirect()->route('addresses.index')
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default
     */
    public function setDefault(UserAddress $address)
    {
        $this->authorize('update', $address);
        $address->setAsDefault();

        return redirect()->route('addresses.index')
            ->with('success', 'Default address updated!');
    }

    /**
     * Get user's default address (API endpoint)
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
     * Get all user addresses (API endpoint)
     */
    public function getAll()
    {
        $addresses = UserAddress::forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }
}
