<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhilippineRegion;
use App\Models\PhilippineProvince;
use App\Models\PhilippineCity;
use App\Models\PhilippineBarangay;

class PhilippineAddressSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        PhilippineBarangay::truncate();
        PhilippineCity::truncate();
        PhilippineProvince::truncate();
        PhilippineRegion::truncate();

        // REGIONS
        $regions = [
            ['region_code' => 'NCR', 'name' => 'National Capital Region (NCR)'],
            ['region_code' => 'CAR', 'name' => 'Cordillera Administrative Region (CAR)'],
            ['region_code' => 'I', 'name' => 'Ilocos Region (Region I)'],
            ['region_code' => 'II', 'name' => 'Cagayan Valley (Region II)'],
            ['region_code' => 'III', 'name' => 'Central Luzon (Region III)'],
            ['region_code' => 'IV-A', 'name' => 'CALABARZON (Region IV-A)'],
            ['region_code' => 'IV-B', 'name' => 'MIMAROPA (Region IV-B)'],
            ['region_code' => 'V', 'name' => 'Bicol Region (Region V)'],
            ['region_code' => 'VI', 'name' => 'Western Visayas (Region VI)'],
            ['region_code' => 'VII', 'name' => 'Central Visayas (Region VII)'],
            ['region_code' => 'VIII', 'name' => 'Eastern Visayas (Region VIII)'],
            ['region_code' => 'IX', 'name' => 'Zamboanga Peninsula (Region IX)'],
            ['region_code' => 'X', 'name' => 'Northern Mindanao (Region X)'],
            ['region_code' => 'XI', 'name' => 'Davao Region (Region XI)'],
            ['region_code' => 'XII', 'name' => 'SOCCSKSARGEN (Region XII)'],
            ['region_code' => 'XIII', 'name' => 'Caraga (Region XIII)'],
            ['region_code' => 'BARMM', 'name' => 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)'],
        ];

        foreach ($regions as $region) {
            PhilippineRegion::create($region);
        }

        // ZAMBOANGA PENINSULA (REGION IX) - Detailed data
        $zamboangaPeninsula = PhilippineRegion::where('region_code', 'IX')->first();

        $provinces = [
            ['region_id' => $zamboangaPeninsula->id, 'province_code' => 'ZAN', 'name' => 'Zamboanga del Norte'],
            ['region_id' => $zamboangaPeninsula->id, 'province_code' => 'ZAS', 'name' => 'Zamboanga del Sur'],
            ['region_id' => $zamboangaPeninsula->id, 'province_code' => 'ZSI', 'name' => 'Zamboanga Sibugay'],
        ];

        foreach ($provinces as $province) {
            PhilippineProvince::create($province);
        }

        // ZAMBOANGA DEL SUR CITIES
        $zamboangaDelSur = PhilippineProvince::where('province_code', 'ZAS')->first();

        $cities = [
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'ZC', 'name' => 'Zamboanga City'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'PAG', 'name' => 'Pagadian City'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'AUR', 'name' => 'Aurora'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'BAY', 'name' => 'Bayog'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'DUM', 'name' => 'Dumingag'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'GAL', 'name' => 'Guipos'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'LAK', 'name' => 'Lakewood'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'LAP', 'name' => 'Lapuyan'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'MAN', 'name' => 'Midsalip'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'MOL', 'name' => 'Molave'],
        ];

        foreach ($cities as $city) {
            PhilippineCity::create($city);
        }

        // ZAMBOANGA CITY BARANGAYS
        $zamboangaCity = PhilippineCity::where('city_code', 'ZC')->first();

        $barangays = [
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUM', 'name' => 'Tumaga'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-PUE', 'name' => 'Pueente'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-STA', 'name' => 'Sta. Catalina'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SMA', 'name' => 'Sta. Maria'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TET', 'name' => 'Tetuan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CAL', 'name' => 'Calarain'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-AYA', 'name' => 'Ayala'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUL', 'name' => 'Tulungatung'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-GUU', 'name' => 'Guiwan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON', 'name' => 'Zone I'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON2', 'name' => 'Zone II'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON3', 'name' => 'Zone III'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON4', 'name' => 'Zone IV'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-PUT', 'name' => 'Putik'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-BAL', 'name' => 'Baliwasan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CUR', 'name' => 'Culianan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SAN', 'name' => 'San Roque'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUB', 'name' => 'Tugbungan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TAL', 'name' => 'Talabaan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-VIC', 'name' => 'Victoria'],
        ];

        foreach ($barangays as $barangay) {
            PhilippineBarangay::create($barangay);
        }

        // Add sample data for other regions (NCR)
        $ncr = PhilippineRegion::where('region_code', 'NCR')->first();
        
        $ncrProvinces = [
            ['region_id' => $ncr->id, 'province_code' => 'MNL', 'name' => 'Metro Manila'],
        ];

        foreach ($ncrProvinces as $province) {
            PhilippineProvince::create($province);
        }

        $metroManila = PhilippineProvince::where('province_code', 'MNL')->first();

        $ncrCities = [
            ['province_id' => $metroManila->id, 'city_code' => 'MNL-C', 'name' => 'Manila'],
            ['province_id' => $metroManila->id, 'city_code' => 'QC', 'name' => 'Quezon City'],
            ['province_id' => $metroManila->id, 'city_code' => 'MAK', 'name' => 'Makati'],
            ['province_id' => $metroManila->id, 'city_code' => 'TAC', 'name' => 'Taguig'],
            ['province_id' => $metroManila->id, 'city_code' => 'PAS', 'name' => 'Pasig'],
            ['province_id' => $metroManila->id, 'city_code' => 'MAN', 'name' => 'Mandaluyong'],
        ];

        foreach ($ncrCities as $city) {
            PhilippineCity::create($city);
        }

        $this->command->info('Philippine address data seeded successfully!');
    }
}
