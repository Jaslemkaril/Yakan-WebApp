<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        try {
            $settings = [
                'custom_order_estimated_days' => SystemSetting::get('custom_order_estimated_days', 14),
                'quality_check_days'   => SystemSetting::get('quality_check_days', 1),
                'gcash_name'           => SystemSetting::get('gcash_name', 'Tuwas Yakan'),
                'gcash_number'         => SystemSetting::get('gcash_number', ''),
                'bank_name'            => SystemSetting::get('bank_name', ''),
                'bank_account_name'    => SystemSetting::get('bank_account_name', 'Tuwas Yakan'),
                'bank_account_number'  => SystemSetting::get('bank_account_number', ''),
                'bank_branch'          => SystemSetting::get('bank_branch', ''),
            ];
        } catch (\Exception $e) {
            $settings = [
                'custom_order_estimated_days' => 14,
                'quality_check_days'  => 1,
                'gcash_name'          => 'Tuwas Yakan',
                'gcash_number'        => '',
                'bank_name'           => '',
                'bank_account_name'   => 'Tuwas Yakan',
                'bank_account_number' => '',
                'bank_branch'         => '',
            ];
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'custom_order_estimated_days' => 'required|integer|min:1|max:90',
            'quality_check_days'  => 'required|integer|min:1|max:30',
            'gcash_name'          => 'nullable|string|max:255',
            'gcash_number'        => 'nullable|string|max:20',
            'bank_name'           => 'nullable|string|max:255',
            'bank_account_name'   => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_branch'         => 'nullable|string|max:255',
        ]);

        try {
            foreach ($validated as $key => $value) {
                SystemSetting::set($key, $value ?? '');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully!',
        ]);
    }
}
