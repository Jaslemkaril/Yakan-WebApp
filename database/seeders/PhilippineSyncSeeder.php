<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent seeder — ensures every Philippine region has provinces and cities.
 * Safe to run on every deploy. Uses insertOrIgnore + md5 codes so it never
 * fails on duplicate keys and never overwrites existing data.
 */
class PhilippineSyncSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            'NCR'   => ['National Capital Region (NCR)', [
                ['Metro Manila', ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong', 'Caloocan', 'Valenzuela', 'Parañaque', 'Las Piñas', 'Muntinlupa', 'Marikina', 'Pasay', 'San Juan', 'Navotas', 'Malabon', 'Pateros']],
            ]],
            'CAR'   => ['Cordillera Administrative Region (CAR)', [
                ['Abra',              ['Bangued', 'Bucay', 'Lagangilang', 'Dolores', 'Tayum', 'Pidigan', 'Manabo']],
                ['Apayao',            ['Kabugao', 'Luna', 'Calanasan', 'Conner', 'Flora', 'Pudtol']],
                ['Benguet',           ['Baguio City', 'La Trinidad', 'Itogon', 'Tuba', 'Mankayan', 'Bokod', 'Tublay']],
                ['Ifugao',            ['Lagawe', 'Banaue', 'Hungduan', 'Alfonso Lista', 'Kiangan', 'Lamut']],
                ['Kalinga',           ['Tabuk City', 'Pinukpuk', 'Tanudan', 'Rizal', 'Balbalan', 'Lubuagan']],
                ['Mountain Province', ['Bontoc', 'Sagada', 'Bauko', 'Tadian', 'Besao', 'Sabangan', 'Barlig']],
            ]],
            'I'     => ['Ilocos Region (Region I)', [
                ['Ilocos Norte', ['Laoag City', 'Batac City', 'Paoay', 'Pagudpud', 'Bangui', 'Burgos', 'Dingras', 'Sarrat']],
                ['Ilocos Sur',   ['Vigan City', 'Candon City', 'Narvacan', 'Santa', 'Bantay', 'Cabugao', 'Tagudin']],
                ['La Union',     ['San Fernando City', 'Bauang', 'Agoo', 'Naguilian', 'Rosario', 'Bacnotan', 'Aringay', 'Bangar']],
                ['Pangasinan',   ['Dagupan City', 'Urdaneta City', 'Alaminos City', 'Lingayen', 'San Carlos City', 'Manaoag', 'Binalonan', 'Mangaldan', 'Rosales', 'Bayambang', 'Calasiao']],
            ]],
            'II'    => ['Cagayan Valley (Region II)', [
                ['Batanes',       ['Basco', 'Itbayat', 'Uyugan', 'Ivana', 'Mahatao', 'Sabtang']],
                ['Cagayan',       ['Tuguegarao City', 'Aparri', 'Sanchez-Mira', 'Lal-lo', 'Gonzaga', 'Santa Ana', 'Enrile', 'Gattaran']],
                ['Isabela',       ['Ilagan City', 'Cauayan City', 'Santiago City', 'Roxas', 'Alicia', 'Cabagan', 'Cordon', 'Echague', 'Tumauini']],
                ['Nueva Vizcaya', ['Bayombong', 'Solano', 'Bambang', 'Kasibu', 'Aritao', 'Bagabag', 'Dupax del Sur']],
                ['Quirino',       ['Cabarroguis', 'Diffun', 'Maddela', 'Aglipay', 'Nagtipunan', 'Saguday']],
            ]],
            'III'   => ['Central Luzon (Region III)', [
                ['Aurora',      ['Baler', 'Maria Aurora', 'Dipaculao', 'Dingalan', 'Casiguran', 'Dinalungan']],
                ['Bataan',      ['Balanga City', 'Mariveles', 'Orani', 'Bagac', 'Dinalupihan', 'Hermosa', 'Limay', 'Pilar', 'Samal', 'Abucay', 'Morong', 'Orion']],
                ['Bulacan',     ['Malolos City', 'Meycauayan City', 'San Jose del Monte City', 'Marilao', 'Bocaue', 'Guiguinto', 'Balagtas', 'Bulakan', 'Calumpit', 'Hagonoy', 'Obando', 'Pandi', 'Plaridel', 'Pulilan', 'Santa Maria']],
                ['Nueva Ecija', ['Palayan City', 'Cabanatuan City', 'San Jose City', 'Gapan City', 'Munoz City', 'Guimba', 'Talavera', 'Cabiao', 'Jaen', 'Zaragoza', 'Santa Rosa', 'Aliaga']],
                ['Pampanga',    ['San Fernando City', 'Angeles City', 'Mabalacat City', 'Magalang', 'Arayat', 'Guagua', 'Lubao', 'Mexico', 'Porac', 'Santa Rita', 'Bacolor', 'Floridablanca']],
                ['Tarlac',      ['Tarlac City', 'Capas', 'Concepcion', 'Paniqui', 'Gerona', 'Camiling', 'Victoria', 'La Paz', 'Bamban', 'Moncada']],
                ['Zambales',    ['Olongapo City', 'Iba', 'San Antonio', 'Subic', 'Botolan', 'Castillejos', 'Masinloc', 'Candelaria', 'Palauig', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz']],
            ]],
            'IV-A'  => ['CALABARZON (Region IV-A)', [
                ['Batangas', ['Batangas City', 'Lipa City', 'Tanauan City', 'Sto. Tomas City', 'Nasugbu', 'Rosario', 'Balayan', 'Bauan', 'Lemery', 'San Jose', 'Calaca', 'Mabini', 'Mataas na Kahoy']],
                ['Cavite',   ['Cavite City', 'Dasmariñas City', 'Bacoor City', 'Imus City', 'General Trias City', 'Trece Martires City', 'Tagaytay City', 'Silang', 'Kawit', 'Noveleta', 'Carmona', 'Tanza', 'Naic', 'Indang']],
                ['Laguna',   ['Santa Cruz', 'Calamba City', 'San Pablo City', 'San Pedro City', 'Biñan City', 'Santa Rosa City', 'Cabuyao City', 'Los Baños', 'Bay', 'Pagsanjan', 'Nagcarlan', 'Pila', 'Pakil']],
                ['Quezon',   ['Lucena City', 'Tayabas City', 'Gumaca', 'Infanta', 'Candelaria', 'Sariaya', 'Pagbilao', 'Unisan', 'Mulanay', 'Catanauan', 'Lopez', 'Real']],
                ['Rizal',    ['Antipolo City', 'Binangonan', 'Cainta', 'Taytay', 'Rodriguez', 'San Mateo', 'Angono', 'Morong', 'Tanay', 'Cardona', 'Teresa', 'Pililla', 'Baras', 'Jala-Jala']],
            ]],
            'IV-B'  => ['MIMAROPA (Region IV-B)', [
                ['Marinduque',        ['Boac', 'Mogpog', 'Santa Cruz', 'Gasan', 'Buenavista', 'Torrijos']],
                ['Occidental Mindoro', ['Mamburao', 'San Jose', 'Calintaan', 'Rizal', 'Sablayan', 'Abra de Ilog', 'Looc', 'Lubang', 'Paluan', 'Magsaysay']],
                ['Oriental Mindoro',  ['Calapan City', 'Puerto Galera', 'Bongabong', 'Pinamalayan', 'Roxas', 'Naujan', 'Victoria', 'Pola', 'Bansud', 'Gloria', 'Mansalay', 'Bulalacao', 'Socorro']],
                ['Palawan',           ['Puerto Princesa City', 'El Nido', 'Coron', 'Roxas', 'San Vicente', 'Taytay', 'Narra', 'Aborlan', 'Quezon', 'Balabac']],
                ['Romblon',           ['Romblon', 'Odiongan', 'San Fernando', 'Magdiwang', 'Cajidiocan', 'Santa Fe', 'Looc']],
            ]],
            'V'     => ['Bicol Region (Region V)', [
                ['Albay',           ['Legazpi City', 'Ligao City', 'Tabaco City', 'Daraga', 'Camalig', 'Guinobatan', 'Polangui', 'Libon', 'Oas', 'Tiwi']],
                ['Camarines Norte', ['Daet', 'Labo', 'Vinzons', 'Jose Panganiban', 'Mercedes', 'Paracale', 'Talisay', 'Basud']],
                ['Camarines Sur',   ['Naga City', 'Iriga City', 'Pili', 'Libmanan', 'Sipocot', 'Nabua', 'Bato', 'Calabanga', 'Goa']],
                ['Catanduanes',     ['Virac', 'Viga', 'Bagamanoc', 'Pandan', 'Bato', 'Baras', 'Caramoran']],
                ['Masbate',         ['Masbate City', 'Cataingan', 'Milagros', 'Uson', 'Aroroy', 'Mandaon', 'Mobo']],
                ['Sorsogon',        ['Sorsogon City', 'Bulusan', 'Casiguran', 'Gubat', 'Irosin', 'Juban', 'Matnog', 'Pilar', 'Bulan', 'Donsol']],
            ]],
            'VI'    => ['Western Visayas (Region VI)', [
                ['Aklan',             ['Kalibo', 'Numancia', 'Ibajay', 'Makato', 'Malay', 'Nabas', 'Batan', 'Altavas']],
                ['Antique',           ['San Jose de Buenavista', 'Tibiao', 'Culasi', 'Pandan', 'Sebaste', 'Barbaza', 'Hamtic']],
                ['Capiz',             ['Roxas City', 'Pontevedra', 'Panay', 'Sigma', 'Dumalag', 'Jamindan', 'Tapaz']],
                ['Guimaras',          ['Jordan', 'Buenavista', 'Nueva Valencia', 'San Lorenzo', 'Sibunag']],
                ['Iloilo',            ['Iloilo City', 'Passi City', 'Pototan', 'Dumangas', 'Pavia', 'Sta. Barbara', 'Cabatuan', 'Oton', 'Leganes']],
                ['Negros Occidental', ['Bacolod City', 'San Carlos City', 'Silay City', 'Escalante City', 'Sagay City', 'Cadiz City', 'Victorias City', 'Talisay City', 'Kabankalan City']],
            ]],
            'VII'   => ['Central Visayas (Region VII)', [
                ['Bohol',          ['Tagbilaran City', 'Tubigon', 'Talibon', 'Ubay', 'Jagna', 'Loon', 'Carmen', 'Dauis', 'Panglao', 'Loboc']],
                ['Cebu',           ['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City', 'Naga City', 'Toledo City', 'Carcar City', 'Danao City', 'Bogo City', 'Moalboal', 'Oslob', 'Argao']],
                ['Negros Oriental', ['Dumaguete City', 'Bayawan City', 'Tanjay City', 'Bais City', 'Canlaon City', 'Guihulngan City', 'Siaton', 'Zamboanguita']],
                ['Siquijor',       ['Siquijor', 'Larena', 'Enrique Villanueva', 'Maria', 'Lazi', 'San Juan']],
            ]],
            'VIII'  => ['Eastern Visayas (Region VIII)', [
                ['Biliran',        ['Naval', 'Caibiran', 'Culaba', 'Kawayan']],
                ['Eastern Samar',  ['Borongan City', 'Guiuan', 'Dolores', 'Llorente']],
                ['Leyte',          ['Tacloban City', 'Ormoc City', 'Baybay City', 'Palo', 'Carigara', 'Abuyog']],
                ['Northern Samar', ['Catarman', 'Laoang', 'Allen', 'Lavezares']],
                ['Samar',          ['Catbalogan City', 'Calbayog City', 'Paranas', 'Jiabong']],
                ['Southern Leyte', ['Maasin City', 'Bontoc', 'Liloan', 'Pintuyan']],
            ]],
            'IX'    => ['Zamboanga Peninsula (Region IX)', [
                ['Zamboanga del Norte', ['Dipolog City', 'Dapitan City', 'Sindangan', 'Liloy', 'Pinan', 'Polanco']],
                ['Zamboanga del Sur',   ['Zamboanga City', 'Pagadian City', 'Aurora', 'Bayog', 'Dumingag', 'Guipos', 'Lakewood', 'Molave', 'Tukuran']],
                ['Zamboanga Sibugay',   ['Ipil', 'Kabasalan', 'Siay', 'Titay']],
            ]],
            'X'     => ['Northern Mindanao (Region X)', [
                ['Bukidnon',          ['Malaybalay City', 'Valencia City', 'Maramag', 'Manolo Fortich', 'Quezon']],
                ['Camiguin',           ['Mambajao', 'Guinsiliban', 'Mahinog', 'Sagay', 'Catarman']],
                ['Lanao del Norte',    ['Iligan City', 'Kapatagan', 'Lala', 'Tubod', 'Bacolod']],
                ['Misamis Occidental', ['Oroquieta City', 'Ozamiz City', 'Tangub City', 'Calamba', 'Jimenez']],
                ['Misamis Oriental',   ['Cagayan de Oro City', 'El Salvador City', 'Gingoog City', 'Opol', 'Villanueva']],
            ]],
            'XI'    => ['Davao Region (Region XI)', [
                ['Davao de Oro',     ['Nabunturan', 'Compostela', 'Laak', 'Maragusan', 'Mawab']],
                ['Davao del Norte',  ['Tagum City', 'Panabo City', 'Island Garden City of Samal', 'Carmen', 'New Corella']],
                ['Davao del Sur',    ['Davao City', 'Digos City', 'Bansalan', 'Hagonoy', 'Magsaysay', 'Malalag', 'Padada', 'Sta. Cruz']],
                ['Davao Occidental', ['Jose Abad Santos', 'Malita', 'Sta. Maria', 'Don Marcelino', 'Sarangani']],
                ['Davao Oriental',   ['Mati City', 'Baganga', 'Banaybanay', 'Boston', 'Caraga']],
            ]],
            'XII'   => ['SOCCSKSARGEN (Region XII)', [
                ['North Cotabato', ['Kidapawan City', 'Midsayap', 'Pigcawayan', 'Carmen', 'Kabacan']],
                ['Sarangani',      ['Alabel', 'Malapatan', 'Malungon', 'Kiamba', 'Glan']],
                ['South Cotabato', ['Koronadal City', 'General Santos City', 'Surallah', 'Tupi', 'Lake Sebu']],
                ['Sultan Kudarat', ['Isulan', 'Tacurong City', 'Kalamansig', 'Columbio', 'Lebak']],
            ]],
            'XIII'  => ['Caraga (Region XIII)', [
                ['Agusan del Norte',  ['Butuan City', 'Cabadbaran City', 'Buenavista', 'Carmen', 'Nasipit', 'Santiago']],
                ['Agusan del Sur',    ['Bayugan City', 'San Francisco', 'Prosperidad', 'Bunawan', 'La Paz', 'Talacogon', 'Trento']],
                ['Dinagat Islands',   ['San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Libjo']],
                ['Surigao del Norte', ['Surigao City', 'Claver', 'Mainit', 'Malimono', 'Placer']],
                ['Surigao del Sur',   ['Bislig City', 'Tandag City', 'Barobo', 'Carrascal', 'Cortes', 'Hinatuan', 'Lanuza']],
            ]],
            'BARMM' => ['Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)', [
                ['Basilan',              ['Isabela City', 'Lamitan City', 'Tipo-Tipo', 'Al-Barka']],
                ['Lanao del Sur',        ['Marawi City', 'Bayang', 'Binidayan', 'Balindong', 'Butig']],
                ['Maguindanao del Norte', ['Cotabato City', 'Datu Odin Sinsuat', 'Sultan Kudarat', 'Upi', 'Barira']],
                ['Maguindanao del Sur',  ['Buluan', 'Datu Piang', 'Pagalungan', 'Talayan']],
                ['Sulu',                 ['Jolo', 'Patikul', 'Indanan', 'Pangutaran', 'Parang']],
                ['Tawi-Tawi',            ['Bongao', 'Panglima Sugala', 'Sibutu', 'Simunul', 'Languyan']],
            ]],
        ];

        foreach ($data as $regionCode => [$regionName, $provinces]) {
            // Ensure region exists
            DB::table('philippine_regions')->insertOrIgnore([
                'region_code' => $regionCode,
                'name'        => $regionName,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $region = DB::table('philippine_regions')
                ->where('region_code', $regionCode)->first();

            if (!$region) continue;

            foreach ($provinces as [$provName, $cities]) {
                // Use md5-based code: deterministic, never collides with previous migrations
                $provinceCode = 'SNC' . substr(md5($regionCode . $provName), 0, 17);

                DB::table('philippine_provinces')->insertOrIgnore([
                    'region_id'     => $region->id,
                    'province_code' => $provinceCode,
                    'name'          => $provName,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                $province = DB::table('philippine_provinces')
                    ->where('region_id', $region->id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($provName)])
                    ->first();

                if (!$province) continue;

                foreach ($cities as $cityName) {
                    $cityCode = 'SNC' . substr(md5($regionCode . $provName . $cityName), 0, 17);

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
}
