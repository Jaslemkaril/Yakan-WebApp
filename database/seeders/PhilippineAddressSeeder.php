<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\PhilippineRegion;
use App\Models\PhilippineProvince;
use App\Models\PhilippineCity;
use App\Models\PhilippineBarangay;

class PhilippineAddressSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for safe truncation
        Schema::disableForeignKeyConstraints();
        PhilippineBarangay::truncate();
        PhilippineCity::truncate();
        PhilippineProvince::truncate();
        PhilippineRegion::truncate();
        Schema::enableForeignKeyConstraints();

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

        // ===================== REGION IX: ZAMBOANGA PENINSULA =====================
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
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'GUI', 'name' => 'Guipos'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'LAK', 'name' => 'Lakewood'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'LAP', 'name' => 'Lapuyan'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'MID', 'name' => 'Midsalip'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'MOL', 'name' => 'Molave'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'RAC', 'name' => 'Ramon Magsaysay'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'SJU', 'name' => 'San Juan'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'SMP', 'name' => 'San Pablo'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'TAB', 'name' => 'Tabina'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'TAM', 'name' => 'Tambulig'],
            ['province_id' => $zamboangaDelSur->id, 'city_code' => 'TUK', 'name' => 'Tukuran'],
        ];

        foreach ($cities as $city) {
            PhilippineCity::create($city);
        }

        // ZAMBOANGA DEL NORTE CITIES
        $zamboangaDelNorte = PhilippineProvince::where('province_code', 'ZAN')->first();

        $zdnCities = [
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'DPC', 'name' => 'Dipolog City'],
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'DAP', 'name' => 'Dapitan City'],
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'SYN', 'name' => 'Sindangan'],
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'LIL', 'name' => 'Liloy'],
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'PIR', 'name' => 'Pinan'],
            ['province_id' => $zamboangaDelNorte->id, 'city_code' => 'POL', 'name' => 'Polanco'],
        ];

        foreach ($zdnCities as $city) {
            PhilippineCity::create($city);
        }

        // ZAMBOANGA SIBUGAY CITIES
        $zamboangaSibugay = PhilippineProvince::where('province_code', 'ZSI')->first();

        $zsiCities = [
            ['province_id' => $zamboangaSibugay->id, 'city_code' => 'IPC', 'name' => 'Ipil'],
            ['province_id' => $zamboangaSibugay->id, 'city_code' => 'KAB', 'name' => 'Kabasalan'],
            ['province_id' => $zamboangaSibugay->id, 'city_code' => 'SIN', 'name' => 'Siay'],
            ['province_id' => $zamboangaSibugay->id, 'city_code' => 'TIT', 'name' => 'Titay'],
        ];

        foreach ($zsiCities as $city) {
            PhilippineCity::create($city);
        }

        // ZAMBOANGA CITY BARANGAYS (98 barangays - key ones)
        $zamboangaCity = PhilippineCity::where('city_code', 'ZC')->first();

        $barangays = [
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-AYA', 'name' => 'Ayala'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-BAL', 'name' => 'Baliwasan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-BOL', 'name' => 'Bolong'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-BUN', 'name' => 'Bunguiao'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-BUS', 'name' => 'Busay'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CAL', 'name' => 'Calarain'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CAW', 'name' => 'Calarian'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CAM', 'name' => 'Camino Nuevo'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CUL', 'name' => 'Culianan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-CUR', 'name' => 'Curuan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-DIV', 'name' => 'Divisoria'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-GUU', 'name' => 'Guiwan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-GUS', 'name' => 'Guisao'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LAB', 'name' => 'La Paz'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LAM', 'name' => 'Labuan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LAN', 'name' => 'Lanzones'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LAP', 'name' => 'Lapakan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LUM', 'name' => 'Lumbangan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-LUN', 'name' => 'Lunzuran'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-MER', 'name' => 'Mercedes'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-PAT', 'name' => 'Pasonanca'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-PUT', 'name' => 'Putik'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-REC', 'name' => 'Recodo'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SAN', 'name' => 'San Jose Cawa-Cawa'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SNR', 'name' => 'San Jose Gusu'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SRO', 'name' => 'San Roque'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SCA', 'name' => 'Sta. Catalina'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SMA', 'name' => 'Sta. Maria'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-SBC', 'name' => 'Sto. Nino'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TAL', 'name' => 'Talabaan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TLK', 'name' => 'Talisayan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TAM', 'name' => 'Talon-Talon'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TET', 'name' => 'Tetuan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUG', 'name' => 'Tugbungan'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUL', 'name' => 'Tulungatung'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-TUM', 'name' => 'Tumaga'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-VIT', 'name' => 'Vitali'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-VIC', 'name' => 'Victoria'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON1', 'name' => 'Zone I (Poblacion)'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON2', 'name' => 'Zone II (Poblacion)'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON3', 'name' => 'Zone III (Poblacion)'],
            ['city_id' => $zamboangaCity->id, 'barangay_code' => 'ZC-ZON4', 'name' => 'Zone IV (Poblacion)'],
        ];

        foreach ($barangays as $barangay) {
            PhilippineBarangay::create($barangay);
        }

        // ===================== NCR: METRO MANILA =====================
        $ncr = PhilippineRegion::where('region_code', 'NCR')->first();
        
        PhilippineProvince::create(['region_id' => $ncr->id, 'province_code' => 'MNL', 'name' => 'Metro Manila']);
        $metroManila = PhilippineProvince::where('province_code', 'MNL')->first();

        $ncrCities = [
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MNL', 'name' => 'Manila'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-QC', 'name' => 'Quezon City'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MAK', 'name' => 'Makati'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-TAG', 'name' => 'Taguig'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-PAS', 'name' => 'Pasig'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MDL', 'name' => 'Mandaluyong'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-CLO', 'name' => 'Caloocan'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-VAL', 'name' => 'Valenzuela'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-PAR', 'name' => 'Parañaque'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-LPC', 'name' => 'Las Piñas'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MUN', 'name' => 'Muntinlupa'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MAR', 'name' => 'Marikina'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-PSY', 'name' => 'Pasay'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-SJN', 'name' => 'San Juan'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-NAV', 'name' => 'Navotas'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-MAL', 'name' => 'Malabon'],
            ['province_id' => $metroManila->id, 'city_code' => 'NCR-PAT', 'name' => 'Pateros'],
        ];

        foreach ($ncrCities as $city) {
            PhilippineCity::create($city);
        }

        // ===================== REGION VII: CENTRAL VISAYAS =====================
        $centralVisayas = PhilippineRegion::where('region_code', 'VII')->first();

        PhilippineProvince::create(['region_id' => $centralVisayas->id, 'province_code' => 'CEB', 'name' => 'Cebu']);
        PhilippineProvince::create(['region_id' => $centralVisayas->id, 'province_code' => 'BOH', 'name' => 'Bohol']);

        $cebu = PhilippineProvince::where('province_code', 'CEB')->first();
        $cebuCities = [
            ['province_id' => $cebu->id, 'city_code' => 'CEB-CC', 'name' => 'Cebu City'],
            ['province_id' => $cebu->id, 'city_code' => 'CEB-LAP', 'name' => 'Lapu-Lapu City'],
            ['province_id' => $cebu->id, 'city_code' => 'CEB-MAN', 'name' => 'Mandaue City'],
            ['province_id' => $cebu->id, 'city_code' => 'CEB-TAL', 'name' => 'Talisay City'],
        ];

        foreach ($cebuCities as $city) {
            PhilippineCity::create($city);
        }

        // ===================== REGION XI: DAVAO =====================
        $davao = PhilippineRegion::where('region_code', 'XI')->first();

        PhilippineProvince::create(['region_id' => $davao->id, 'province_code' => 'DVO', 'name' => 'Davao del Sur']);
        PhilippineProvince::create(['region_id' => $davao->id, 'province_code' => 'DVN', 'name' => 'Davao del Norte']);

        $davaoDelSur = PhilippineProvince::where('province_code', 'DVO')->first();
        $davaoCities = [
            ['province_id' => $davaoDelSur->id, 'city_code' => 'DVO-DC', 'name' => 'Davao City'],
            ['province_id' => $davaoDelSur->id, 'city_code' => 'DVO-DIG', 'name' => 'Digos City'],
        ];

        foreach ($davaoCities as $city) {
            PhilippineCity::create($city);
        }

        $this->command->info('Philippine address data seeded successfully!');
    }
}
