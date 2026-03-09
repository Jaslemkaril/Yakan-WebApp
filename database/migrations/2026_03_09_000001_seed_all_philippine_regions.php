<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert provinces and cities for regions that have no province data yet.
        // Uses insertOrIgnore / updateOrInsert so this is safe to run multiple times.

        $this->seedRegion('CAR', [
            ['code' => 'ABR', 'name' => 'Abra',             'cities' => ['Bangued', 'Bucay', 'Lagangilang']],
            ['code' => 'APA', 'name' => 'Apayao',           'cities' => ['Kabugao', 'Luna', 'Calanasan']],
            ['code' => 'BNG', 'name' => 'Benguet',          'cities' => ['Baguio City', 'La Trinidad', 'Itogon', 'Tuba']],
            ['code' => 'IFG', 'name' => 'Ifugao',           'cities' => ['Lagawe', 'Banaue', 'Hungduan']],
            ['code' => 'KLG', 'name' => 'Kalinga',          'cities' => ['Tabuk City', 'Pinukpuk', 'Tanudan']],
            ['code' => 'MTN', 'name' => 'Mountain Province','cities' => ['Bontoc', 'Sagada', 'Bauko']],
        ]);

        $this->seedRegion('I', [
            ['code' => 'ILN', 'name' => 'Ilocos Norte',    'cities' => ['Laoag City', 'Batac City', 'Paoay', 'Pagudpud']],
            ['code' => 'ILS', 'name' => 'Ilocos Sur',      'cities' => ['Vigan City', 'Candon City', 'Narvacan', 'Santa']],
            ['code' => 'LUN', 'name' => 'La Union',         'cities' => ['San Fernando City', 'Bauang', 'Agoo', 'Urdaneta']],
            ['code' => 'PAN', 'name' => 'Pangasinan',       'cities' => ['Dagupan City', 'Urdaneta City', 'Alaminos City', 'Lingayen', 'San Carlos City', 'Manaoag']],
        ]);

        $this->seedRegion('II', [
            ['code' => 'BTN', 'name' => 'Batanes',          'cities' => ['Basco', 'Itbayat', 'Uyugan']],
            ['code' => 'CAG', 'name' => 'Cagayan',          'cities' => ['Tuguegarao City', 'Aparri', 'Sanchez-Mira', 'Lal-lo']],
            ['code' => 'ISA', 'name' => 'Isabela',          'cities' => ['Ilagan City', 'Cauayan City', 'Santiago City', 'Roxas', 'Alicia']],
            ['code' => 'NVE', 'name' => 'Nueva Vizcaya',    'cities' => ['Bayombong', 'Solano', 'Bambang', 'Kasibu']],
            ['code' => 'QUI', 'name' => 'Quirino',          'cities' => ['Cabarroguis', 'Diffun', 'Maddela', 'Aglipay']],
        ]);

        $this->seedRegion('III', [
            ['code' => 'AUR', 'name' => 'Aurora',           'cities' => ['Baler', 'Maria Aurora', 'Dipaculao', 'Dingalan']],
            ['code' => 'BAT', 'name' => 'Bataan',           'cities' => ['Balanga City', 'Mariveles', 'Orani', 'Bagac']],
            ['code' => 'BUL', 'name' => 'Bulacan',          'cities' => ['Malolos City', 'Meycauayan City', 'San Jose del Monte City', 'Marilao', 'Bocaue', 'Guiguinto']],
            ['code' => 'NVE2','name' => 'Nueva Ecija',      'cities' => ['Palayan City', 'Cabanatuan City', 'San Jose City', 'Gapan City', 'Muñoz City']],
            ['code' => 'PAM', 'name' => 'Pampanga',         'cities' => ['San Fernando City', 'Angeles City', 'Mabalacat City', 'Magalang', 'Arayat']],
            ['code' => 'TAR', 'name' => 'Tarlac',           'cities' => ['Tarlac City', 'Capas', 'Concepcion', 'Paniqui']],
            ['code' => 'ZMB', 'name' => 'Zambales',         'cities' => ['Olongapo City', 'Iba', 'San Antonio', 'Subic']],
        ]);

        $this->seedRegion('IV-A', [
            ['code' => 'BTG', 'name' => 'Batangas',         'cities' => ['Batangas City', 'Lipa City', 'Tanauan City', 'Sto. Tomas City', 'Nasugbu', 'Rosario']],
            ['code' => 'CAV', 'name' => 'Cavite',           'cities' => ['Cavite City', 'Dasmariñas City', 'Bacoor City', 'Imus City', 'General Trias City', 'Trece Martires City', 'Tagaytay City']],
            ['code' => 'LAG', 'name' => 'Laguna',           'cities' => ['Santa Cruz', 'Calamba City', 'San Pablo City', 'San Pedro City', 'Biñan City', 'Santa Rosa City', 'Cabuyao City']],
            ['code' => 'QUE', 'name' => 'Quezon',           'cities' => ['Lucena City', 'Tayabas City', 'Gumaca', 'Infanta', 'Candelaria']],
            ['code' => 'RZL', 'name' => 'Rizal',            'cities' => ['Antipolo City', 'Binangonan', 'Cainta', 'Taytay', 'Rodriguez', 'San Mateo']],
        ]);

        $this->seedRegion('IV-B', [
            ['code' => 'MAR', 'name' => 'Marinduque',       'cities' => ['Boac', 'Mogpog', 'Santa Cruz', 'Gasan']],
            ['code' => 'OCM', 'name' => 'Occidental Mindoro','cities' => ['Mamburao', 'San Jose', 'Calintaan', 'Rizal']],
            ['code' => 'ORM', 'name' => 'Oriental Mindoro', 'cities' => ['Calapan City', 'Puerto Galera', 'Bongabong', 'Pinamalayan']],
            ['code' => 'PLW', 'name' => 'Palawan',          'cities' => ['Puerto Princesa City', 'El Nido', 'Coron', 'Roxas', 'San Vicente']],
            ['code' => 'ROM', 'name' => 'Romblon',          'cities' => ['Romblon', 'Odiongan', 'San Fernando', 'Magdiwang']],
        ]);

        $this->seedRegion('V', [
            ['code' => 'ALB', 'name' => 'Albay',            'cities' => ['Legazpi City', 'Ligao City', 'Tabaco City', 'Daraga', 'Camalig']],
            ['code' => 'CAN', 'name' => 'Camarines Norte',  'cities' => ['Daet', 'Labo', 'Vinzons', 'Jose Panganiban']],
            ['code' => 'CAS', 'name' => 'Camarines Sur',    'cities' => ['Naga City', 'Iriga City', 'Pili', 'Libmanan', 'Sipocot']],
            ['code' => 'CAT', 'name' => 'Catanduanes',      'cities' => ['Virac', 'Viga', 'Bagamanoc', 'Pandan']],
            ['code' => 'MAS', 'name' => 'Masbate',          'cities' => ['Masbate City', 'Cataingan', 'Milagros', 'Uson']],
            ['code' => 'SOR', 'name' => 'Sorsogon',         'cities' => ['Sorsogon City', 'Bulusan', 'Casiguran', 'Gubat']],
        ]);

        $this->seedRegion('VI', [
            ['code' => 'AKL', 'name' => 'Aklan',            'cities' => ['Kalibo', 'Numancia', 'Ibajay', 'Makato']],
            ['code' => 'ANT', 'name' => 'Antique',          'cities' => ['San Jose de Buenavista', 'Tibiao', 'Culasi', 'Pandan']],
            ['code' => 'CAP', 'name' => 'Capiz',            'cities' => ['Roxas City', 'Pontevedra', 'Panay', 'Sigma']],
            ['code' => 'GUI', 'name' => 'Guimaras',         'cities' => ['Jordan', 'Buenavista', 'McLain', 'Nueva Valencia']],
            ['code' => 'ILO', 'name' => 'Iloilo',           'cities' => ['Iloilo City', 'Passi City', 'Pototan', 'Dumangas', 'Pavia', 'Sta. Barbara']],
            ['code' => 'NEG', 'name' => 'Negros Occidental','cities' => ['Bacolod City', 'San Carlos City', 'Silay City', 'Escalante City', 'Sagay City', 'Cadiz City', 'Victorias City', 'Talisay City']],
        ]);

        $this->seedRegion('VIII', [
            ['code' => 'BIL', 'name' => 'Biliran',          'cities' => ['Naval', 'Caibiran', 'Culaba', 'Kawayan']],
            ['code' => 'EAS', 'name' => 'Eastern Samar',    'cities' => ['Borongan City', 'Guiuan', 'Dolores', 'Llorente']],
            ['code' => 'LEY', 'name' => 'Leyte',            'cities' => ['Tacloban City', 'Ormoc City', 'Baybay City', 'Palo', 'Carigara', 'Abuyog']],
            ['code' => 'NOR', 'name' => 'Northern Samar',   'cities' => ['Catarman', 'Laoang', 'Allen', 'Lavezares']],
            ['code' => 'SAM', 'name' => 'Samar',            'cities' => ['Catbalogan City', 'Calbayog City', 'Paranas', 'Jiabong']],
            ['code' => 'SLE', 'name' => 'Southern Leyte',   'cities' => ['Maasin City', 'Bontoc', 'Liloan', 'Pintuyan']],
        ]);

        $this->seedRegion('X', [
            ['code' => 'BUK', 'name' => 'Bukidnon',         'cities' => ['Malaybalay City', 'Valencia City', 'Maramag', 'Manolo Fortich', 'Quezon']],
            ['code' => 'CAM', 'name' => 'Camiguin',         'cities' => ['Mambajao', 'Guinsiliban', 'Mahinog', 'Sagay', 'Catarman']],
            ['code' => 'LDN', 'name' => 'Lanao del Norte',  'cities' => ['Iligan City', 'Kapatagan', 'Lala', 'Tubod', 'Bacolod']],
            ['code' => 'MSC', 'name' => 'Misamis Occidental','cities' => ['Oroquieta City', 'Ozamiz City', 'Tangub City', 'Calamba', 'Jimenez']],
            ['code' => 'MSR', 'name' => 'Misamis Oriental', 'cities' => ['Cagayan de Oro City', 'El Salvador City', 'Gingoog City', 'Opol', 'Villanueva']],
        ]);

        $this->seedRegion('XII', [
            ['code' => 'NCO', 'name' => 'North Cotabato',   'cities' => ['Kidapawan City', 'Midsayap', 'Pigcawayan', 'Carmen', 'Kabacan']],
            ['code' => 'SAR', 'name' => 'Sarangani',        'cities' => ['Alabel', 'Malapatan', 'Malungon', 'Kiamba', 'Glan']],
            ['code' => 'SCO', 'name' => 'South Cotabato',   'cities' => ['Koronadal City', 'General Santos City', 'Surallah', 'Tupi', 'Lake Sebu']],
            ['code' => 'SKD', 'name' => 'Sultan Kudarat',   'cities' => ['Isulan', 'Tacurong City', 'Kalamansig', 'Columbio', 'Lebak']],
        ]);

        $this->seedRegion('XIII', [
            ['code' => 'AGN', 'name' => 'Agusan del Norte', 'cities' => ['Butuan City', 'Cabadbaran City', 'Buenavista', 'Carmen', 'Nasipit', 'Santiago']],
            ['code' => 'AGS', 'name' => 'Agusan del Sur',   'cities' => ['Bayugan City', 'San Francisco', 'Prosperidad', 'Bunawan', 'La Paz', 'Talacogon', 'Trento']],
            ['code' => 'DIN', 'name' => 'Dinagat Islands',  'cities' => ['San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Libjo']],
            ['code' => 'SUN', 'name' => 'Surigao del Norte','cities' => ['Surigao City', 'Claver', 'Mainit', 'Malimono', 'Placer', 'Tandag', 'Tubod']],
            ['code' => 'SUR', 'name' => 'Surigao del Sur',  'cities' => ['Bislig City', 'Tandag City', 'Barobo', 'Carrascal', 'Cortes', 'Hinatuan', 'Lanuza']],
        ]);

        $this->seedRegion('BARMM', [
            ['code' => 'BAS', 'name' => 'Basilan',          'cities' => ['Isabela City', 'Lamitan City', 'Tipo-Tipo', 'Hadji Mohammad Ajul', 'Al-Barka']],
            ['code' => 'LDS', 'name' => 'Lanao del Sur',    'cities' => ['Marawi City', 'Bayang', 'Binidayan', 'Balindong', 'Butig']],
            ['code' => 'MGD', 'name' => 'Maguindanao del Norte','cities' => ['Cotabato City', 'Datu Odin Sinsuat', 'Sultan Kudarat', 'Upi', 'Barira']],
            ['code' => 'MGS', 'name' => 'Maguindanao del Sur','cities' => ['Buluan', 'Datu Piang', 'Pagalungan', 'Talayan', 'General Salipada K. Pendatun']],
            ['code' => 'SLU', 'name' => 'Sulu',             'cities' => ['Jolo', 'Patikul', 'Indanan', 'Pangutaran', 'Parang']],
            ['code' => 'TWI', 'name' => 'Tawi-Tawi',        'cities' => ['Bongao', 'Panglima Sugala', 'Sibutu', 'Simunul', 'Languyan']],
        ]);

        // For Region XI (Davao), add provinces that are missing
        $this->seedRegionExtra('XI', [
            ['code' => 'DDO', 'name' => 'Davao de Oro',     'cities' => ['Nabunturan', 'Compostela', 'Laak', 'Maragusan', 'Mawab']],
            ['code' => 'DVN2','name' => 'Davao del Norte',  'cities' => ['Tagum City', 'Panabo City', 'Island Garden City of Samal', 'Carmen', 'New Corella']],
            ['code' => 'DVO2','name' => 'Davao Occidental', 'cities' => ['Jose Abad Santos', 'Malita', 'Sta. Maria', 'Don Marcelino', 'Sarangani']],
            ['code' => 'DOR', 'name' => 'Davao Oriental',   'cities' => ['Mati City', 'Baganga', 'Banaybanay', 'Boston', 'Caraga']],
        ]);
    }

    public function down(): void
    {
        // Non-destructive migration; no rollback needed.
    }

    // -----------------------------------------------------------------------
    private function seedRegion(string $regionCode, array $provinces): void
    {
        $region = DB::table('philippine_regions')->where('region_code', $regionCode)->first();
        if (!$region) {
            return; // Region table not seeded yet – skip
        }

        // Skip if this region already has provinces
        $existing = DB::table('philippine_provinces')->where('region_id', $region->id)->count();
        if ($existing > 0) {
            return;
        }

        foreach ($provinces as $prov) {
            $provinceId = DB::table('philippine_provinces')->insertGetId([
                'region_id'     => $region->id,
                'province_code' => $prov['code'],
                'name'          => $prov['name'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($prov['cities'] as $idx => $cityName) {
                DB::table('philippine_cities')->insert([
                    'province_id' => $provinceId,
                    'city_code'   => $prov['code'] . '-C' . ($idx + 1),
                    'name'        => $cityName,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    private function seedRegionExtra(string $regionCode, array $provinces): void
    {
        $region = DB::table('philippine_regions')->where('region_code', $regionCode)->first();
        if (!$region) {
            return;
        }

        foreach ($provinces as $prov) {
            // Only insert province if not already present
            $existingProv = DB::table('philippine_provinces')
                ->where('province_code', $prov['code'])->first();
            if ($existingProv) {
                continue;
            }

            $provinceId = DB::table('philippine_provinces')->insertGetId([
                'region_id'     => $region->id,
                'province_code' => $prov['code'],
                'name'          => $prov['name'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($prov['cities'] as $idx => $cityName) {
                DB::table('philippine_cities')->insert([
                    'province_id' => $provinceId,
                    'city_code'   => $prov['code'] . '-C' . ($idx + 1),
                    'name'        => $cityName,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
};
