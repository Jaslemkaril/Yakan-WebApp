<?php

namespace App\Http\Controllers\Admin;

use App\Models\FabricType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FabricTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $fabricTypes = FabricType::orderBy('sort_order')->get();
        return view('admin.fabric_types.index', compact('fabricTypes'));
    }

    public function create()
    {
        return view('admin.fabric_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:fabric_types,name',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'base_price_per_meter' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['base_price_per_meter'] = $validated['base_price_per_meter'] ?? 0;
        $fabricType = FabricType::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id' => $fabricType->id,
                'message' => 'Fabric type created successfully.'
            ]);
        }

        return redirect()->route('admin.fabric_types.index')
            ->with('success', 'Fabric type created successfully.');
    }

    public function edit(FabricType $fabricType)
    {
        return view('admin.fabric_types.edit', compact('fabricType'));
    }

    public function update(Request $request, FabricType $fabricType)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:fabric_types,name,' . $fabricType->id,
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'base_price_per_meter' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $fabricType->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fabric type updated successfully.',
                'data' => $fabricType
            ]);
        }

        return redirect()->route('admin.fabric_types.index')
            ->with('success', 'Fabric type updated successfully.');
    }

    public function destroy(FabricType $fabricType, Request $request)
    {
        $fabricType->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fabric type deleted successfully.'
            ]);
        }
        
        return redirect()->route('admin.fabric_types.index')
            ->with('success', 'Fabric type deleted successfully.');
    }

    public function toggleActive(FabricType $fabricType)
    {
        $fabricType->update(['is_active' => !$fabricType->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $fabricType->is_active,
            'message' => $fabricType->is_active ? 'Fabric type activated.' : 'Fabric type deactivated.'
        ]);
    }
}
