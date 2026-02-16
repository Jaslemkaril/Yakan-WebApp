<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Models\PhilippineRegion;
use App\Models\PhilippineProvince;
use App\Models\PhilippineCity;
use App\Models\PhilippineBarangay;
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
            'region_id' => 'required|exists:philippine_regions,id',
            'province_id' => 'required|exists:philippine_provinces,id',
            'city_id' => 'required|exists:philippine_cities,id',
            'barangay_id' => 'required|exists:philippine_barangays,id',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        // Get the actual names from the database
        $region = PhilippineRegion::find($validated['region_id']);
        $province = PhilippineProvince::find($validated['province_id']);
        $city = PhilippineCity::find($validated['city_id']);
        $barangay = PhilippineBarangay::find($validated['barangay_id']);
        
        // Map form fields to database columns
        $addressData = [
            'label' => $validated['label'],
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'street' => $validated['formatted_address'],
            'barangay' => $barangay->name,
            'city' => $city->name,
            'province' => $province->name,
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
            'region_id' => 'required|exists:philippine_regions,id',
            'province_id' => 'required|exists:philippine_provinces,id',
            'city_id' => 'required|exists:philippine_cities,id',
            'barangay_id' => 'required|exists:philippine_barangays,id',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        // Get the actual names from the database
        $region = PhilippineRegion::find($validated['region_id']);
        $province = PhilippineProvince::find($validated['province_id']);
        $city = PhilippineCity::find($validated['city_id']);
        $barangay = PhilippineBarangay::find($validated['barangay_id']);

        // Map form fields to database columns
        $addressData = [
            'label' => $validated['label'],
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'street' => $validated['formatted_address'],
            'barangay' => $barangay->name,
            'city' => $city->name,
            'province' => $province->name,
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

    /**
     * Get all Philippine regions
     */
    public function getRegions()
    {
        $regions = PhilippineRegion::orderBy('name')->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $regions,
        ]);
    }

    /**
     * Get provinces by region
     */
    public function getProvinces($regionId)
    {
        $provinces = PhilippineProvince::where('region_id', $regionId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Get cities by province
     */
    public function getCities($provinceId)
    {
        $cities = PhilippineCity::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get barangays by city
     */
    public function getBarangays($cityId)
    {
        $barangays = PhilippineBarangay::where('city_id', $cityId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $barangays,
        ]);
    }
}
