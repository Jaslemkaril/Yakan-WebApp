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
        // Support both ID-based (dedicated address page) and text-based (checkout modals)
        $useIds = $request->filled('region_id');

        $rules = [
            'label'             => 'required|string|max:50',
            'full_name'         => 'required|string|max:255',
            'phone_number'      => 'required|string|max:20',
            'formatted_address' => 'required|string|max:255',
            'postal_code'       => 'required|string|max:10',
            'is_default'        => 'boolean',
        ];

        if ($useIds) {
            $rules['region_id']   = 'required|exists:philippine_regions,id';
            $rules['province_id'] = 'required|exists:philippine_provinces,id';
            $rules['city_id']     = 'required|exists:philippine_cities,id';
            $rules['barangay_id'] = 'nullable|exists:philippine_barangays,id';
        } else {
            $rules['region'] = 'required|string|max:255';
            $rules['city']   = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($useIds) {
            $region   = PhilippineRegion::find($validated['region_id']);
            $province = PhilippineProvince::find($validated['province_id']);
            $city     = PhilippineCity::find($validated['city_id']);
            $barangay = !empty($validated['barangay_id']) ? PhilippineBarangay::find($validated['barangay_id']) : null;
            $cityName     = $city->name;
            $provinceName = $province->name;
            $barangayName = $barangay ? $barangay->name : '';
        } else {
            $cityName     = $validated['city'];
            $provinceName = $validated['region'];
            $barangayName = $request->input('barangay', '');
        }

        // Map form fields to database columns
        $addressData = [
            'label'       => $validated['label'],
            'full_name'   => $validated['full_name'],
            'phone_number'=> $validated['phone_number'],
            'street'      => $validated['formatted_address'],
            'barangay'    => $barangayName,
            'city'        => $cityName,
            'province'    => $provinceName,
            'postal_code' => $validated['postal_code'],
            'user_id'     => Auth::id(),
        ];

        // If this is the first address or marked as default, set it as default
        $existingCount = UserAddress::forUser(Auth::id())->count();
        if ($existingCount === 0 || $request->boolean('is_default')) {
            $addressData['is_default'] = true;
            // Remove default from other addresses
            UserAddress::forUser(Auth::id())->update(['is_default' => false]);
        }

        $address = UserAddress::create($addressData);

        // Check if request came from chat
        $referer = request()->headers->get('referer');
        if ($request->has('from_chat') || ($referer && str_contains($referer, '/chats/'))) {
            return redirect($referer)->with('success', 'Address added successfully!');
        }

        // Check if request came from checkout
        if ($request->has('from_checkout') || str_contains($request->header('referer', ''), 'checkout')) {
            $url = route('cart.checkout');
            return response("<!DOCTYPE html><html><head><title>Yakan</title><meta http-equiv='refresh' content='0;url={$url}'></head><body style='margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;background:#fff5f5;font-family:sans-serif;gap:16px;'><div style='width:48px;height:48px;border:4px solid #f3d4d4;border-top:4px solid #800000;border-radius:50%;animation:spin 1s linear infinite;'></div><p style='color:#800000;font-size:18px;font-weight:600;'>Saving address...</p><style>@keyframes spin{to{transform:rotate(360deg)}}</style><script>setTimeout(function(){window.location.href='{$url}';},600);</script></body></html>");
        }

        $token = $request->input('auth_token') ?? $request->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('addresses.index', $params)
            ->with('success', 'Address added successfully!');
    }

    /**
     * Show the form for editing an address
     */
    public function edit(UserAddress $address)
    {
        $this->authorize('update', $address);

        // --- Step 1: resolve city (case-insensitive, with fallback partial match) ---
        $citySearch = strtolower(trim($address->city ?? ''));
        $city = $citySearch
            ? PhilippineCity::whereRaw('LOWER(name) = ?', [$citySearch])->first()
            : null;
        // Partial match: "San Fernando City" stored as "San Fernando" or vice-versa
        if (!$city && $citySearch) {
            $city = PhilippineCity::whereRaw('LOWER(name) LIKE ?', ['%' . $citySearch . '%'])->first();
        }
        if (!$city && $citySearch) {
            $city = PhilippineCity::whereRaw('? LIKE CONCAT(\'%\', LOWER(name), \'%\')', [$citySearch])->first();
        }

        // --- Step 2: resolve province ---
        $province = null;
        if ($city) {
            $province = PhilippineProvince::find($city->province_id);
        } else {
            // Try exact, then partial match on stored province/region text
            $province = PhilippineProvince::whereRaw('LOWER(name) = ?', [strtolower(trim($address->province))])->first();
            if (!$province && $address->province) {
                $base     = trim(explode('(', $address->province)[0]);
                $province = PhilippineProvince::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($base) . '%'])->first();
            }
        }

        // --- Step 3: resolve region ---
        $region = null;
        if ($province) {
            $region = PhilippineRegion::find($province->region_id);
        } else {
            // Last resort: match stored province text against region names
            if ($address->province) {
                $base   = trim(explode('(', $address->province)[0]);
                $region = PhilippineRegion::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($base) . '%'])->first();
            }
            // Also try matching the city column against region names
            if (!$region && $address->city) {
                $region = PhilippineRegion::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($address->city)) . '%'])->first();
            }
        }

        // --- Step 4: resolve barangay ---
        $barangay = null;
        if ($city && $address->barangay) {
            $barangay = PhilippineBarangay::whereRaw('LOWER(name) = ?', [strtolower(trim($address->barangay))])
                ->where('city_id', $city->id)->first();
        }

        return view('addresses.edit', compact('address', 'region', 'province', 'city', 'barangay'));
    }

    /**
     * Update the specified address
     */
    public function update(Request $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        // Support both ID-based (dedicated address page) and text-based (checkout modals)
        $useIds = $request->filled('region_id');

        $rules = [
            'label'             => 'required|string|max:50',
            'full_name'         => 'required|string|max:255',
            'phone_number'      => 'required|string|max:20',
            'formatted_address' => 'required|string|max:255',
            'postal_code'       => 'required|string|max:10',
            'is_default'        => 'boolean',
        ];

        if ($useIds) {
            $rules['region_id']   = 'required|exists:philippine_regions,id';
            $rules['province_id'] = 'required|exists:philippine_provinces,id';
            $rules['city_id']     = 'required|exists:philippine_cities,id';
            $rules['barangay_id'] = 'nullable|exists:philippine_barangays,id';
        } else {
            $rules['region'] = 'required|string|max:255';
            $rules['city']   = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($useIds) {
            $region   = PhilippineRegion::find($validated['region_id']);
            $province = PhilippineProvince::find($validated['province_id']);
            $city     = PhilippineCity::find($validated['city_id']);
            $barangay = !empty($validated['barangay_id']) ? PhilippineBarangay::find($validated['barangay_id']) : null;
            $cityName     = $city->name;
            $provinceName = $province->name;
            $barangayName = $barangay ? $barangay->name : '';
        } else {
            $cityName     = $validated['city'];
            $provinceName = $validated['region'];
            $barangayName = $request->input('barangay', '');
        }

        // Map form fields to database columns
        $addressData = [
            'label'        => $validated['label'],
            'full_name'    => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'street'       => $validated['formatted_address'],
            'barangay'     => $barangayName,
            'city'         => $cityName,
            'province'     => $provinceName,
            'postal_code'  => $validated['postal_code'],
        ];

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        $address->update($addressData);

        // Check if request came from checkout
        if ($request->has('from_checkout') || str_contains($request->header('referer', ''), 'checkout')) {
            $url = route('cart.checkout');
            return response("<!DOCTYPE html><html><head><title>Yakan</title><meta http-equiv='refresh' content='0;url={$url}'></head><body style='margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;background:#fff5f5;font-family:sans-serif;gap:16px;'><div style='width:48px;height:48px;border:4px solid #f3d4d4;border-top:4px solid #800000;border-radius:50%;animation:spin 1s linear infinite;'></div><p style='color:#800000;font-size:18px;font-weight:600;'>Updating address...</p><style>@keyframes spin{to{transform:rotate(360deg)}}</style><script>setTimeout(function(){window.location.href='{$url}';},600);</script></body></html>");
        }

        $token = $request->input('auth_token') ?? $request->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('addresses.index', $params)
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

        $token = request()->input('auth_token') ?? request()->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('addresses.index', $params)
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default
     */
    public function setDefault(UserAddress $address)
    {
        $this->authorize('update', $address);
        $address->setAsDefault();

        // Check if request came from chat, redirect back there
        $referer = request()->headers->get('referer');
        if ($referer && str_contains($referer, '/chats/')) {
            return redirect($referer)->with('success', 'Delivery address updated!');
        }

        $token = request()->input('auth_token') ?? request()->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('addresses.index', $params)
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
