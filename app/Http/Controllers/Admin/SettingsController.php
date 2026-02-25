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
                'quality_check_days' => SystemSetting::get('quality_check_days', 1),
            ];
        } catch (\Exception $e) {
            // If system_settings table doesn't exist yet, use defaults
            $settings = [
                'quality_check_days' => 1,
            ];
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'quality_check_days' => 'required|integer|min:1|max:30',
        ]);

        try {
            foreach ($validated as $key => $value) {
                SystemSetting::set($key, $value);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Failed to save settings. The system_settings table may need to be created.');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }
}
