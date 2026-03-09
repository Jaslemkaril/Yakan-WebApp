<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    /**
     * Return public payment info (GCash number/name, bank transfer details).
     * No authentication required — this is read-only store info the mobile app needs.
     */
    public function paymentInfo()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'gcash_number' => SystemSetting::get('gcash_number', ''),
                'gcash_name'   => SystemSetting::get('gcash_name', 'Tuwas Yakan'),
                'bank_name'    => SystemSetting::get('bank_name', 'BDO'),
                'bank_account' => SystemSetting::get('bank_account_number', ''),
                'bank_account_name' => SystemSetting::get('bank_account_name', 'Tuwas Yakan'),
            ],
        ]);
    }
}
