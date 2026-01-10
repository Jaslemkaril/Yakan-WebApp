<?php

namespace App\Http\Controllers\Admin;

use App\Models\FabricType;
use App\Models\IntendedUse;
use Illuminate\Routing\Controller;

class PatternsManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $fabricTypes = FabricType::latest()->get();
        $intendedUses = IntendedUse::latest()->get();
        
        return view('admin.patterns_management.index', compact('fabricTypes', 'intendedUses'));
    }
}
