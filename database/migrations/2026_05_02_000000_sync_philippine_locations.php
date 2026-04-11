<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Definitive, fully-idempotent sync of all Philippine regions, provinces, and cities.
 * Uses md5-content-addressed codes so they are:
 *   - Deterministic (same result on repeated runs)
 *   - Never collide with any code used by previous migrations or seeders
 * Uses insertOrIgnore (ignores duplicate-key errors silently).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $data = [
            'NCR' => [
                'region_name' => 'National Capital Region (NCR)',
                'provinces' => [
                    ['name' => 'Metro Manila', 'cities' => ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong', 'Caloocan', 'Valenzuela', 'Parañaque', 'Las Piñas', 'Muntinlupa', 'Marikina', 'Pasay', 'San Juan', 'Navotas', 'Malabon', 'Pateros']],
                ],
            ],
            'CAR' => [
                'region_name' => 'Cordillera Administrative Region (CAR)',
                'provinces' => [
                    ['name' => 'Abra',              'cities' => ['Bangued', 'Bucay', 'Lagangilang', 'Dolores', 'Tayum', 'Pidigan', 'Manabo']],
                    ['name' => 'Apayao',            'cities' => ['Kabugao', 'Luna', 'Calanasan', 'Conner', 'Flora', 'Pudtol']],
                    ['name' => 'Benguet',           'cities' => ['Baguio City', 'La Trinidad', 'Itogon', 'Tuba', 'Mankayan', 'Bokod', 'Kabayan', 'Tublay']],
                    ['name' => 'Ifugao',            'cities' => ['Lagawe', 'Banaue', 'Hungduan', 'Alfonso Lista', 'Kiangan', 'Lamut']],
                    ['name' => 'Kalinga',           'cities' => ['Tabuk City', 'Pinukpuk', 'Tanudan', 'Rizal', 'Balbalan', 'Lubuagan']],
                    ['name' => 'Mountain Province', 'cities' => ['Bontoc', 'Sagada', 'Bauko', 'Tadian', 'Besao', 'Sabangan', 'Barlig']],
                ],
            ],
            'I' => [
                'region_name' => 'Ilocos Region (Region I)',
                'provinces' => [
                    ['name' => 'Ilocos Norte',  'cities' => ['Laoag City', 'Batac City', 'Paoay', 'Pagudpud', 'Bangui', 'Burgos', 'Dingras', 'Marcos', 'Sarrat']],
                    ['name' => 'Ilocos Sur',    'cities' => ['Vigan City', 'Candon City', 'Narvacan', 'Santa', 'Bantay', 'Cabugao', 'San Ildefonso', 'Tagudin']],
                    ['name' => 'La Union',      'cities' => ['San Fernando City', 'Bauang', 'Agoo', 'Naguilian', 'Rosario', 'Bacnotan', 'Aringay', 'Bangar']],
                    ['name' => 'Pangasinan',    'cities' => ['Dagupan City', 'Urdaneta City', 'Alaminos City', 'Lingayen', 'San Carlos City', 'Manaoag', 'Binalonan', 'Mangaldan', 'Rosales', 'Bayambang', 'Calasiao', 'Malasiqui']],
                ],
            ],
            'II' => [
                'region_name' => 'Cagayan Valley (Region II)',
                'provinces' => [
                    ['name' => 'Batanes',       'cities' => ['Basco', 'Itbayat', 'Uyugan', 'Ivana', 'Mahatao', 'Sabtang']],
                    ['name' => 'Cagayan',       'cities' => ['Tuguegarao City', 'Aparri', 'Sanchez-Mira', 'Lal-lo', 'Gonzaga', 'Santa Ana', 'Enrile', 'Peñablanca', 'Gattaran']],
                    ['name' => 'Isabela',       'cities' => ['Ilagan City', 'Cauayan City', 'Santiago City', 'Roxas', 'Alicia', 'Cabagan', 'Cordon', 'Echague', 'Tumauini']],
                    ['name' => 'Nueva Vizcaya', 'cities' => ['Bayombong', 'Solano', 'Bambang', 'Kasibu', 'Aritao', 'Bagabag', 'Dupax del Sur']],
                    ['name' => 'Quirino',       'cities' => ['Cabarroguis', 'Diffun', 'Maddela', 'Aglipay', 'Nagtipunan', 'Saguday']],
                ],
            ],
            'III' => [
                'region_name' => 'Central Luzon (Region III)',
                'provinces' => [
                    ['name' => 'Aurora',       'cities' => ['Baler', 'Maria Aurora', 'Dipaculao', 'Dingalan', 'Casiguran', 'Dinalungan', 'Dilasag']],
                    ['name' => 'Bataan',       'cities' => ['Balanga City', 'Mariveles', 'Orani', 'Bagac', 'Dinalupihan', 'Hermosa', 'Limay', 'Pilar', 'Samal', 'Abucay', 'Morong', 'Orion']],
                    ['name' => 'Bulacan',      'cities' => ['Malolos City', 'Meycauayan City', 'San Jose del Monte City', 'Marilao', 'Bocaue', 'Guiguinto', 'Balagtas', 'Bulakan', 'Calumpit', 'Hagonoy', 'Obando', 'Pandi', 'Plaridel', 'Pulilan', 'Santa Maria']],
                    ['name' => 'Nueva Ecija',  'cities' => ['Palayan City', 'Cabanatuan City', 'San Jose City', 'Gapan City', 'Muñoz City', 'Guimba', 'Talavera', 'Cabiao', 'Jaen', 'Zaragoza', 'Santa Rosa', 'Aliaga']],
                    ['name' => 'Pampanga',     'cities' => ['San Fernando City', 'Angeles City', 'Mabalacat City', 'Magalang', 'Arayat', 'Guagua', 'Lubao', 'Mexico', 'Porac', 'Santa Rita', 'Bacolor', 'Floridablanca']],
                    ['name' => 'Tarlac',       'cities' => ['Tarlac City', 'Capas', 'Concepcion', 'Paniqui', 'Gerona', 'Camiling', 'Victoria', 'La Paz', 'Bamban', 'Moncada']],
                    ['name' => 'Zambales',     'cities' => ['Olongapo City', 'Iba', 'San Antonio', 'Subic', 'Botolan', 'Castillejos', 'Masinloc', 'Candelaria', 'Palauig', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz']],
                ],
            ],
            'IV-A' => [
                'region_name' => 'CALABARZON (Region IV-A)',
                'provinces' => [
                    ['name' => 'Batangas',  'cities' => ['Batangas City', 'Lipa City', 'Tanauan City', 'Sto. Tomas City', 'Nasugbu', 'Rosario', 'Balayan', 'Bauan', 'Lemery', 'San Jose', 'Calaca', 'Laurel', 'Mabini', 'Mataas na Kahoy']],
                    ['name' => 'Cavite',    'cities' => ['Cavite City', 'Dasmariñas City', 'Bacoor City', 'Imus City', 'General Trias City', 'Trece Martires City', 'Tagaytay City', 'Silang', 'Kawit', 'Noveleta', 'Rosario', 'Carmona', 'Gen. Mariano Alvarez', 'Tanza', 'Naic', 'Indang']],
                    ['name' => 'Laguna',    'cities' => ['Santa Cruz', 'Calamba City', 'San Pablo City', 'San Pedro City', 'Biñan City', 'Santa Rosa City', 'Cabuyao City', 'Los Baños', 'Bay', 'Pagsanjan', 'Nagcarlan', 'Pila', 'Pakil']],
                    ['name' => 'Quezon',    'cities' => ['Lucena City', 'Tayabas City', 'Gumaca', 'Infanta', 'Candelaria', 'Sariaya', 'Pagbilao', 'Unisan', 'Mulanay', 'Catanauan', 'Lopez', 'Real']],
                    ['name' => 'Rizal',     'cities' => ['Antipolo City', 'Binangonan', 'Cainta', 'Taytay', 'Rodriguez', 'San Mateo', 'Angono', 'Morong', 'Tanay', 'Cardona', 'Teresa', 'Pililla', 'Baras', 'Jala-Jala']],
                ],
            ],
            'IV-B' => [
                'region_name' => 'MIMAROPA (Region IV-B)',
                'provinces' => [
                    ['name' => 'Marinduque',         'cities' => ['Boac', 'Mogpog', 'Santa Cruz', 'Gasan', 'Buenavista', 'Torrijos']],
                    ['name' => 'Occidental Mindoro',  'cities' => ['Mamburao', 'San Jose', 'Calintaan', 'Rizal', 'Sablayan', 'Abra de Ilog', 'Santa Cruz', 'Looc', 'Lubang', 'Paluan', 'Magsaysay']],
                    ['name' => 'Oriental Mindoro',    'cities' => ['Calapan City', 'Puerto Galera', 'Bongabong', 'Pinamalayan', 'Roxas', 'Naujan', 'Victoria', 'Pola', 'Bansud', 'Gloria', 'Mansalay', 'Bulalacao', 'Socorro']],
                    ['name' => 'Palawan',             'cities' => ['Puerto Princesa City', 'El Nido', 'Coron', 'Roxas', 'San Vicente', 'Taytay', "Brooke's Point", 'Narra', 'Aborlan', 'Quezon', 'Balabac']],
                    ['name' => 'Romblon',             'cities' => ['Romblon', 'Odiongan', 'San Fernando', 'Magdiwang', 'Cajidiocan', 'Santa Fe', 'Looc']],
                ],
            ],
            'V' => [
                'region_name' => 'Bicol Region (Region V)',
                'provinces' => [
                    ['name' => 'Albay',           'cities' => ['Legazpi City', 'Ligao City', 'Tabaco City', 'Daraga', 'Camalig', 'Guinobatan', 'Polangui', 'Libon', 'Oas', 'Tiwi']],
                    ['name' => 'Camarines Norte', 'cities' => ['Daet', 'Labo', 'Vinzons', 'Jose Panganiban', 'Mercedes', 'Paracale', 'Talisay', 'Basud']],
                    ['name' => 'Camarines Sur',   'cities' => ['Naga City', 'Iriga City', 'Pili', 'Libmanan', 'Sipocot', 'Nabua', 'Bato', 'Calabanga', 'Goa']],
                    ['name' => 'Catanduanes',     'cities' => ['Virac', 'Viga', 'Bagamanoc', 'Pandan', 'Bato', 'Baras', 'Caramoran']],
                    ['name' => 'Masbate',         'cities' => ['Masbate City', 'Cataingan', 'Milagros', 'Uson', 'Aroroy', 'Mandaon', 'Mobo']],
                    ['name' => 'Sorsogon',        'cities' => ['Sorsogon City', 'Bulusan', 'Casiguran', 'Gubat', 'Irosin', 'Juban', 'Matnog', 'Pilar', 'Bulan', 'Donsol']],
                ],
            ],
            'VI' => [
                'region_name' => 'Western Visayas (Region VI)',
                'provinces' => [
                    ['name' => 'Aklan',              'cities' => ['Kalibo', 'Numancia', 'Ibajay', 'Makato', 'Malay', 'Nabas', 'Batan', 'Altavas']],
                    ['name' => 'Antique',            'cities' => ['San Jose de Buenavista', 'Tibiao', 'Culasi', 'Pandan', 'Sebaste', 'Barbaza', 'Hamtic']],
                    ['name' => 'Capiz',              'cities' => ['Roxas City', 'Pontevedra', 'Panay', 'Sigma', 'Dumalag', 'Jamindan', 'Tapaz']],
                    ['name' => 'Guimaras',           'cities' => ['Jordan', 'Buenavista', 'Nueva Valencia', 'San Lorenzo', 'Sibunag']],
                    ['name' => 'Iloilo',             'cities' => ['Iloilo City', 'Passi City', 'Pototan', 'Dumangas', 'Pavia', 'Sta. Barbara', 'Cabatuan', 'Oton', 'Leganes']],
                    ['name' => 'Negros Occidental',  'cities' => ['Bacolod City', 'San Carlos City', 'Silay City', 'Escalante City', 'Sagay City', 'Cadiz City', 'Victorias City', 'Talisay City', 'Kabankalan City']],
                ],
            ],
            'VII' => [
                'region_name' => 'Central Visayas (Region VII)',
                'provinces' => [
                    ['name' => 'Bohol',           'cities' => ['Tagbilaran City', 'Tubigon', 'Talibon', 'Ubay', 'Jagna', 'Loon', 'Carmen', 'Dauis', 'Panglao', 'Loboc']],
                    ['name' => 'Cebu',            'cities' => ['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City', 'Naga City', 'Toledo City', 'Carcar City', 'Danao City', 'Bogo City', 'Moalboal', 'Oslob', 'Argao']],
                    ['name' => 'Negros Oriental', 'cities' => ['Dumaguete City', 'Bayawan City', 'Tanjay City', 'Bais City', 'Canlaon City', 'Guihulngan City', 'Siaton', 'Zamboanguita']],
                    ['name' => 'Siquijor',        'cities' => ['Siquijor', 'Larena', 'Enrique Villanueva', 'Maria', 'Lazi', 'San Juan']],
                ],
            ],
            'VIII' => [
                'region_name' => 'Eastern Visayas (Region VIII)',
                'provinces' => [
                    ['name' => 'Biliran',         'cities' => ['Naval', 'Caibiran', 'Culaba', 'Kawayan']],
                    ['name' => 'Eastern Samar',   'cities' => ['Borongan City', 'Guiuan', 'Dolores', 'Llorente']],
                    ['name' => 'Leyte',           'cities' => ['Tacloban City', 'Ormoc City', 'Baybay City', 'Palo', 'Carigara', 'Abuyog']],
                    ['name' => 'Northern Samar',  'cities' => ['Catarman', 'Laoang', 'Allen', 'Lavezares']],
                    ['name' => 'Samar',           'cities' => ['Catbalogan City', 'Calbayog City', 'Paranas', 'Jiabong']],
                    ['name' => 'Southern Leyte',  'cities' => ['Maasin City', 'Bontoc', 'Liloan', 'Pintuyan']],
                ],
            ],
            'IX' => [
                'region_name' => 'Zamboanga Peninsula (Region IX)',
                'provinces' => [
                    ['name' => 'Zamboanga del Norte', 'cities' => ['Dipolog City', 'Dapitan City', 'Sindangan', 'Liloy', 'Pinan', 'Polanco']],
                    ['name' => 'Zamboanga del Sur',   'cities' => ['Zamboanga City', 'Pagadian City', 'Aurora', 'Bayog', 'Dumingag', 'Guipos', 'Lakewood', 'Molave', 'Tukuran']],
                    ['name' => 'Zamboanga Sibugay',   'cities' => ['Ipil', 'Kabasalan', 'Siay', 'Titay']],
                ],
            ],
            'X' => [
                'region_name' => 'Northern Mindanao (Region X)',
                'provinces' => [
                    ['name' => 'Bukidnon',           'cities' => ['Malaybalay City', 'Valencia City', 'Maramag', 'Manolo Fortich', 'Quezon']],
                    ['name' => 'Camiguin',            'cities' => ['Mambajao', 'Guinsiliban', 'Mahinog', 'Sagay', 'Catarman']],
                    ['name' => 'Lanao del Norte',     'cities' => ['Iligan City', 'Kapatagan', 'Lala', 'Tubod', 'Bacolod']],
                    ['name' => 'Misamis Occidental',  'cities' => ['Oroquieta City', 'Ozamiz City', 'Tangub City', 'Calamba', 'Jimenez']],
                    ['name' => 'Misamis Oriental',    'cities' => ['Cagayan de Oro City', 'El Salvador City', 'Gingoog City', 'Opol', 'Villanueva']],
                ],
            ],
            'XI' => [
                'region_name' => 'Davao Region (Region XI)',
                'provinces' => [
                    ['name' => 'Davao de Oro',      'cities' => ['Nabunturan', 'Compostela', 'Laak', 'Maragusan', 'Mawab']],
                    ['name' => 'Davao del Norte',   'cities' => ['Tagum City', 'Panabo City', 'Island Garden City of Samal', 'Carmen', 'New Corella']],
                    ['name' => 'Davao del Sur',     'cities' => ['Davao City', 'Digos City', 'Bansalan', 'Hagonoy', 'Magsaysay', 'Malalag', 'Padada', 'Sta. Cruz']],
                    ['name' => 'Davao Occidental',  'cities' => ['Jose Abad Santos', 'Malita', 'Sta. Maria', 'Don Marcelino', 'Sarangani']],
                    ['name' => 'Davao Oriental',    'cities' => ['Mati City', 'Baganga', 'Banaybanay', 'Boston', 'Caraga']],
                ],
            ],
            'XII' => [
                'region_name' => 'SOCCSKSARGEN (Region XII)',
                'provinces' => [
                    ['name' => 'North Cotabato', 'cities' => ['Kidapawan City', 'Midsayap', 'Pigcawayan', 'Carmen', 'Kabacan']],
                    ['name' => 'Sarangani',      'cities' => ['Alabel', 'Malapatan', 'Malungon', 'Kiamba', 'Glan']],
                    ['name' => 'South Cotabato', 'cities' => ['Koronadal City', 'General Santos City', 'Surallah', 'Tupi', 'Lake Sebu']],
                    ['name' => 'Sultan Kudarat', 'cities' => ['Isulan', 'Tacurong City', 'Kalamansig', 'Columbio', 'Lebak']],
                ],
            ],
            'XIII' => [
                'region_name' => 'Caraga (Region XIII)',
                'provinces' => [
                    ['name' => 'Agusan del Norte',  'cities' => ['Butuan City', 'Cabadbaran City', 'Buenavista', 'Carmen', 'Nasipit', 'Santiago']],
                    ['name' => 'Agusan del Sur',    'cities' => ['Bayugan City', 'San Francisco', 'Prosperidad', 'Bunawan', 'La Paz', 'Talacogon', 'Trento']],
                    ['name' => 'Dinagat Islands',   'cities' => ['San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Libjo']],
                    ['name' => 'Surigao del Norte', 'cities' => ['Surigao City', 'Claver', 'Mainit', 'Malimono', 'Placer']],
                    ['name' => 'Surigao del Sur',   'cities' => ['Bislig City', 'Tandag City', 'Barobo', 'Carrascal', 'Cortes', 'Hinatuan', 'Lanuza']],
                ],
            ],
            'BARMM' => [
                'region_name' => 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)',
                'provinces' => [
                    ['name' => 'Basilan',               'cities' => ['Isabela City', 'Lamitan City', 'Tipo-Tipo', 'Al-Barka']],
                    ['name' => 'Lanao del Sur',          'cities' => ['Marawi City', 'Bayang', 'Binidayan', 'Balindong', 'Butig']],
                    ['name' => 'Maguindanao del Norte',  'cities' => ['Cotabato City', 'Datu Odin Sinsuat', 'Sultan Kudarat', 'Upi', 'Barira']],
                    ['name' => 'Maguindanao del Sur',    'cities' => ['Buluan', 'Datu Piang', 'Pagalungan', 'Talayan']],
                    ['name' => 'Sulu',                   'cities' => ['Jolo', 'Patikul', 'Indanan', 'Pangutaran', 'Parang']],
                    ['name' => 'Tawi-Tawi',              'cities' => ['Bongao', 'Panglima Sugala', 'Sibutu', 'Simunul', 'Languyan']],
                ],
            ],
        ];

        foreach ($data as $regionCode => $regionData) {
            // ── Ensure region exists ─────────────────────────────────
            DB::table('philippine_regions')->insertOrIgnore([
                'region_code' => $regionCode,
                'name'        => $regionData['region_name'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $region = DB::table('philippine_regions')
                ->where('region_code', $regionCode)->first();

            if (!$region) {
                continue;
            }

            foreach ($regionData['provinces'] as $prov) {
                // ── Ensure province exists ───────────────────────────
                // md5-based code: deterministic + never clashes with any other migration's codes
                $provinceCode = 'MD5P' . substr(md5($regionCode . '|' . $prov['name']), 0, 15);

                DB::table('philippine_provinces')->insertOrIgnore([
                    'region_id'     => $region->id,
                    'province_code' => $provinceCode,
                    'name'          => $prov['name'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                // Resolve the province id (whether it was just inserted or already existed)
                $province = DB::table('philippine_provinces')
                    ->where('region_id', $region->id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($prov['name'])])
                    ->first();

                if (!$province) {
                    continue;
                }

                // ── Ensure each city exists ──────────────────────────
                foreach ($prov['cities'] as $cityName) {
                    $cityCode = 'MD5C' . substr(md5($regionCode . '|' . $prov['name'] . '|' . $cityName), 0, 15);

                    DB::table('philippine_cities')->insertOrIgnore([
                        'province_id' => $province->id,
                        'city_code'   => $cityCode,
                        'name'        => $cityName,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Non-destructive; no rollback.
    }
};
