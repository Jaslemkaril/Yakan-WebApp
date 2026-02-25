<?php

namespace App\Http\Controllers\Admin;

use App\Models\IntendedUse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class IntendedUseController extends Controller
{
    public function __construct()
    {
        // Auth handled by route-level AdminCheck middleware
    }

    public function index()
    {
        $intendedUses = IntendedUse::latest()->get();
        return view('admin.intended_uses.index', compact('intendedUses'));
    }

    public function create()
    {
        return view('admin.intended_uses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:intended_uses,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        IntendedUse::create($validated);

        return redirect()->route('admin.intended_uses.index')
            ->with('success', 'Intended use created successfully.');
    }

    public function edit(IntendedUse $intendedUse)
    {
        return view('admin.intended_uses.edit', compact('intendedUse'));
    }

    public function update(Request $request, IntendedUse $intendedUse)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:intended_uses,name,' . $intendedUse->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $intendedUse->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Intended use updated successfully.',
                'data' => $intendedUse
            ]);
        }

        return redirect()->route('admin.intended_uses.index')
            ->with('success', 'Intended use updated successfully.');
    }

    public function destroy(IntendedUse $intendedUse, Request $request)
    {
        $intendedUse->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Intended use deleted successfully.'
            ]);
        }
        
        return redirect()->route('admin.intended_uses.index')
            ->with('success', 'Intended use deleted successfully.');
    }

    public function toggleActive(IntendedUse $intendedUse)
    {
        $intendedUse->update(['is_active' => !$intendedUse->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $intendedUse->is_active,
            'message' => $intendedUse->is_active ? 'Intended use activated.' : 'Intended use deactivated.'
        ]);
    }
}
