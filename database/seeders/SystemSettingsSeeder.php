<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::set('price_per_meter', 500, 'Price per meter of fabric');
        SystemSetting::set('pattern_fee_simple', 0, 'Fee for simple patterns');
        SystemSetting::set('pattern_fee_medium', 0, 'Fee for medium patterns');
        SystemSetting::set('pattern_fee_complex', 0, 'Fee for complex patterns');
    }
}
