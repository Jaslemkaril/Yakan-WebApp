<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds barangays for all major Philippine cities.
 * Fully idempotent — inserts only for cities that have no barangays yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $data = $this->barangayData();

        foreach ($data as $cityName => $barangays) {
            $city = DB::table('philippine_cities')->where('name', $cityName)->first();
            if (!$city) continue;
            if (DB::table('philippine_barangays')->where('city_id', $city->id)->exists()) continue;

            $rows = [];
            foreach ($barangays as $i => $name) {
                $rows[] = [
                    'city_id'       => $city->id,
                    'barangay_code' => substr(md5($cityName . '-' . $name), 0, 20),
                    'name'          => $name,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
            if (!empty($rows)) {
                DB::table('philippine_barangays')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void {}

    private function barangayData(): array
    {
        return [
            // ── ZAMBOANGA CITY ────────────────────────────────────────
            'Zamboanga City' => [
                'Ayala','Baliwasan','Baluno','Bolong','Buenavista','Bunguiao','Busay',
                'Cabaluay','Cabatangan','Cacao','Calabasa','Calarain','Calarian',
                'Camino Nuevo','Campo Islam','Canelar','Capisan','Cawit','Culianan',
                'Curuan','Dita','Divisoria','Dulian (Upper)','Dulian (Lower)','Estaka',
                'Guiwan','Guisao','Kasanyangan','La Paz','Labuan','Lanzones','Lapakan',
                'Licomo','Limaong','Limpapa','Lubigan','Lumbangan','Lunzuran','Maasin',
                'Malagutay','Mampang','Manalipa','Mangusu','Manicahan','Mariki',
                'Mercedes','Muti','Pamucutan','Pangapuyan','Panubigan','Pasilmanta',
                'Pasonanca','Patalon','Pequeño','Putik','Quiniput','Recodo','Rio Hondo',
                'Salaan','San Jose Cawa-Cawa','San Jose Gusu','San Roque',
                'Santa Barbara','Santa Catalina','Santa Maria','Santo Niño',
                'Sibulao (Caruan)','Sinubung','Sinunoc','Sta. Cruz','Tagasilay',
                'Taguiti','Talabaan','Talisayan','Talon-Talon','Tamban','Tandek',
                'Tetuan','Tigbalabag','Tigtabon','Tolosa','Tugbungan','Tulungatung',
                'Tumaga','Tumalutab','Tumitus','Victoria','Vitali','Waling-waling',
                'Zone I (Poblacion)','Zone II (Poblacion)','Zone III (Poblacion)','Zone IV (Poblacion)',
            ],
            // ── PAGADIAN CITY ─────────────────────────────────────────
            'Pagadian City' => [
                'Balangasan','Bañadero','Bogo','Bomba','Buenavista','Bulatok',
                'Buton','Dao','Datagan','Dumagoc','Kawit','Lenienza','Lian',
                'Limpyo','Lumbia','Mabini','Manga','Mercedes','Napolan',
                'Natividad','New Alegria','New Ipil','New Oroquieta','Ong Yiu',
                'Pag-asa','Pasobolong','Pedidan','Piso','Poloyagan','Puerto','San Francisco',
                'San Jose','San Pedro','Santiago','Santo Niño','Tiguma','Tulangan',
                'Tuburan','Tukolan','Upper Sibacon',
            ],
            // ── MANILA ───────────────────────────────────────────────
            'Manila' => [
                'Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5',
                'Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10',
                'Barangay 11','Barangay 12','Barangay 13','Barangay 14','Barangay 15',
                'Barangay 16','Barangay 17','Barangay 18','Barangay 19','Barangay 20',
                'Barangay 21','Barangay 22','Barangay 23','Barangay 24','Barangay 25',
                'Barangay 26','Barangay 27','Barangay 28','Barangay 29','Barangay 30',
                'Barangay 31','Barangay 32','Barangay 33','Barangay 34','Barangay 35',
                'Barangay 36','Barangay 37','Barangay 38','Barangay 39','Barangay 40',
                'Barangay 41','Barangay 42','Barangay 43','Barangay 44','Barangay 45',
                'Barangay 46','Barangay 47','Barangay 48','Barangay 49','Barangay 50',
                'Barangay 51','Barangay 52','Barangay 53','Barangay 54','Barangay 55',
                'Barangay 56','Barangay 57','Barangay 58','Barangay 59','Barangay 60',
                'Barangay 61','Barangay 62','Barangay 63','Barangay 64','Barangay 65',
                'Barangay 66','Barangay 67','Barangay 68','Barangay 69','Barangay 70',
                'Barangay 71','Barangay 72','Barangay 73','Barangay 74','Barangay 75',
                'Barangay 76','Barangay 77','Barangay 78','Barangay 79','Barangay 80',
                'Barangay 81','Barangay 82','Barangay 83','Barangay 84','Barangay 85',
                'Barangay 86','Barangay 87','Barangay 88','Barangay 89','Barangay 90',
                'Barangay 91','Barangay 92','Barangay 93','Barangay 94','Barangay 95',
                'Barangay 96','Barangay 97','Barangay 98','Barangay 99','Barangay 100',
            ],
            // ── QUEZON CITY ──────────────────────────────────────────
            'Quezon City' => [
                'Alicia','Amihan','Apolonio Samson','Aurora','Baesa','Bagbag',
                'Bagong Pag-asa','Bagong Silangan','Bagumbayan','Bagumbuhay',
                'Bahay Toro','Balingasa','Batasan Hills','Bayanihan','Blue Ridge A',
                'Blue Ridge B','Botocan','Bungad','Camp Aguinaldo','Central',
                'Claro','Commonwealth','Culiat','Damar','Damayang Lagi',
                'Del Monte','Dioquino Zobel','Don Manuel','Doña Aurora','Doña Fausta',
                'Doña Imelda','Doña Josefa','Duyan-duyan','E. Rodriguez','East Kamias',
                'Escopa I','Escopa II','Escopa III','Escopa IV','Fairview',
                'Greater Lagro','Gulod','Holy Spirit','Horseshoe','Immaculate Concepcion',
                'Kaligayahan','Kalusugan','Kamuning','Katipunan','Kaunlaran',
                'La Loma','Laging Handa','Libis','Lourdes','Loyola Heights',
                'Maharlika','Malaya','Manresa','Mariana','Mariblo','Marilag',
                'Masagana','Matandang Balara','Milagrosa','New Era','Novaliches Proper',
                'Obrero','Old Capitol Site','Paang Bundok','Pag-ibig sa Nayon',
                'Paltok','Pansol','Paraiso','Pasong Putik','Payatas','Phil-Am',
                'Quirino 2-A','Quirino 2-B','Quirino 2-C','Quirino 3-A',
                'Ramon Magsaysay','Roxas','Sacred Heart','Saint Ignatius',
                'San Agustin','San Antonio','San Bartolome','San Isidro Labrador',
                'San Jose','San Martin de Porres','San Roque','Santa Cruz',
                'Santa Lucia','Santa Monica','Santa Teresita','Santo Cristo',
                'Santo Domingo','Sauyo','Siena','South Triangle','Tandang Sora',
                'Tatalon','Teachers Village East','Teachers Village West','Triangle',
                'Ugong Norte','University of the Philippines','Vasra','Veterans Village',
                'Villa Maria Clara','West Kamias','West Triangle','White Plains',
            ],
            // ── MAKATI ───────────────────────────────────────────────
            'Makati' => [
                'Bangkal','Bel-Air','Carmona','Cembo','Comembo','Dasmariñas',
                'East Rembo','Forbes Park','Guadalupe Nuevo','Guadalupe Viejo',
                'Kasilawan','La Paz','Magallanes','Olympia','Palanan',
                'Pembo','Pinagkaisahan','Pio del Pilar','Pitogo','Post Proper Northside',
                'Post Proper Southside','Rizal','San Antonio','San Isidro',
                'San Lorenzo','Santa Cruz','Singkamas','South Cembo','Tejeros',
                'Urdaneta','Valenzuela','West Rembo',
            ],
            // ── DAVAO CITY ───────────────────────────────────────────
            'Davao City' => [
                'Acacia','Agdao','Alambre','Atan-awe','Bago Aplaya','Bago Gallera',
                'Bago Oshiro','Baguio','Balengaeng','Baliok','Bangkas Heights',
                'Bantol','Baracatan','Barangay 1-A','Barangay 2-A','Barangay 3-A',
                'Barangay 4-A','Barangay 5-A','Barangay 6-A','Barangay 7-A',
                'Barangay 8-A','Barangay 9-A','Barangay 10-A','Barangay 11-B',
                'Barangay 12-B','Barangay 13-B','Barangay 14-B','Barangay 15-B',
                'Barangay 16-B','Barangay 17-B','Barangay 18-B','Barangay 19-B',
                'Barangay 20-B','Barangay 21-C','Barangay 22-C','Barangay 23-C',
                'Barangay 24-C','Barangay 25-C','Barangay 26-C','Barangay 27-C',
                'Barangay 28-C','Barangay 29-C','Barangay 30-C','Barangay 31-D',
                'Barangay 32-D','Barangay 33-D','Barangay 34-D','Barangay 35-D',
                'Barangay 36-D','Barangay 37-D','Barangay 38-D','Barangay 39-D',
                'Barangay 40-D','Buhangin','Bunawan','Cabantian','Calinan',
                'Callawa','Catalunan Grande','Catalunan Pequeño','Communal',
                'Cugman','Daliao','Datu Salumay','Dumoy','Eden','Fatima',
                'Gatungan','Gumalang','Indangan','Lacson','Lamanan','Langub',
                'Lasang','Lizada','Los Amigos','Lubogan','Lumiad','Ma-a',
                'Maa','Magsaysay','Mahayag','Malabog','Malagos','Manambulan',
                'Mandug','Manuel Guianga','Mapula','Marapangi','Marilog',
                'Matina Aplaya','Matina Crossing','Matina Pangi','Megkawayan',
                'Mintal','Mudiang','Mulig','New Carmen','New Valencia',
                'Pampanga','Pañabo','Paquibato','Riverside','Salapawan',
                'Saloy','San Antonio','Sasa','Sirib','Suawan','Subasta',
                'Tacunan','Tagakpan','Tagluno','Tagurano','Talomo','Tamayong',
                'Tambobong','Tamugan','Tapak','Tawan-tawan','Tibuloy','Tibungco',
                'Tigatto','Toril','Totolan','Tugbok','Tungkalan','Ubalde',
                'Ula','Vicente Hizon','Waan','Wangan','Wilfredo Aquino',
                'Wines',
            ],
            // ── CEBU CITY ───────────────────────────────────────────
            'Cebu City' => [
                'Adlaon','Agsungot','Apas','Babag','Bacayan','Banilad','Basak Pardo',
                'Basak San Nicolas','Binaliw','Buot-Taup','Busay','Calamba','Cambinocot',
                'Capitol Site','Carreta','Central','Cogon Pardo','Cogon Ramos',
                'Day-as','Duljo','Ermita','Escario','Guadalupe','Guba','Hippodromo',
                'Inayawan','Kalubihan','Kalunasan','Kamagayan','Kasambagan',
                'Kinasang-an','Labangon','Lahug','Lorega-San Miguel','Lusaran',
                'Luz','Mabini','Mabolo','Malubog','Mambaling','Mining',
                'Montalban','Nga-an','Odlot','Pardo','Pari-an','Pasil','Pit-os',
                'Poblacion Pardo','Pulangbato','Quiot Pardo','Sambag I',
                'Sambag II','San Antonio','San Jose','San Nicolas Proper',
                'San Roque','Santa Cruz','Santo Niño','Sawang Calero',
                'Sinsin','Sirao','Suba Pasil','Sudlon I','Sudlon II',
                'T. Padilla','Tabunan','Tagbak','Tisa','To-ong Pardo',
                'Tuburan','Tungkop','Zapatera',
            ],
            // ── CAGAYAN DE ORO ───────────────────────────────────────
            'Cagayan de Oro City' => [
                'Agusan','Baikingon','Balubal','Bayabas','Besigan','Bonbon',
                'Bugo','Bulua','Cugman','Dansolihon','Gusa','Indahag',
                'Iponan','Kauswagan','Lapasan','Lumbia','Macabalan',
                'Macasandig','Mambuaya','Nazareth','Pagalungan','Pagatpat',
                'Patag','Pigsag-an','Puerto','Puntod','San Simon','Tablon',
                'Taglimao','Tagpangi','Tignapoloan','Tumpagon',
                'Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5',
                'Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10',
                'Barangay 11','Barangay 12','Barangay 13','Barangay 14','Barangay 15',
                'Barangay 16','Barangay 17','Barangay 18','Barangay 19','Barangay 20',
                'Barangay 21','Barangay 22','Barangay 23','Barangay 24','Barangay 25',
                'Barangay 26','Barangay 27','Barangay 28','Barangay 29','Barangay 30',
                'Barangay 31','Barangay 32','Barangay 33','Barangay 34','Barangay 35',
                'Barangay 36','Barangay 37','Barangay 38','Barangay 39','Barangay 40',
            ],
            // ── BAGUIO CITY ─────────────────────────────────────────
            'Baguio City' => [
                'Abanao-Zandueta-Kayong-Chugum-Otek','Alfonso Tabora','Ambuclao',
                'Ambrosio Domingo','Andres Bonifacio','Atok Trail','Aurora Hill Proper',
                'Abanao','Bakakeng Central','Bakakeng Norte','Balsigan','Banay-Banay',
                'Bayan Park East','Bayan Park Village','Bayan Park West','BGH Compound',
                'Brookside','Brookspoint','Cabinet Hill-Teacher\'s Camp','Camdas Subdivision',
                'Camp 7','Camp 8','Camp Allen','Camp John Hay','Carantes',
                'Casilagan','City Camp Central','City Camp Proper','Country Club Village',
                'Cresencia Village','Dagupan-Natuer','Dizon Subdivision','Dominican Hill-Mirador',
                'Dontogan','DPS Area','Engineer\'s Hill','Fairview Village','Ferdinand',
                'Fort del Pilar','Gabriela Silang','Garcia Hernandez','Gref-Ave','Guisad Central',
                'Guisad Sorong','Happyland','Harrison-Claudio Carantes','Happy Homes',
                'Hillside','Holy Ghost Extension','Holy Ghost Proper','Honeymoon',
                'Imelda R. Marcos','Irisan','Kayang-Hilltop','Kayang-Kayang',
                'Kias','Legarda-Burnham-Kisad','Loakan Proper','Lualhati',
                'Luk-ok','Magsaysay Private Road','Manuel A. Roxas','Market Subdivision',
                'Middle Quezon Hill Subdivision','Military Cut-off','Mines View',
                'Modern Site East','Modern Site West','MRR-Queen of Peace',
                'New Lucban','Outlook Drive','Pacdal','Padre Burgos','Padre Zamora',
                'Palma-Urbano','Phil-Am','Pinget','Poliwes','Pucsusan',
                'Quezon Hill Proper','Quezon Hill Upper','Rock Quarry Lower',
                'Rock Quarry Upper','Quirino Hill East','Quirino Hill Lower',
                'Quirino Hill Middle','Quirino Hill Proper','Quirino Hill West',
                'Residencia','San Antonio Village','San Luis Village','San Roque Village',
                'San Vicente','Sanitary Camp North','Sanitary Camp South',
                'Santa Escolastica','Santo Rosario-Ines','Santo Tomas Proper',
                'Santo Tomas School Area','Scout Barrio','Session Road Area',
                'Slaughter House Area','South Drive','Teodora Alonzo',
                'Trancoville','Victoria Village',
            ],
            // ── ILOILO CITY ─────────────────────────────────────────
            'Iloilo City' => [
                'Abeto Mirasol Taft South (Quirino Abeto)','Aguinaldo',
                'Airport (Tabucan)','Alegria','Alexandria','Arevalo','Balabago',
                'Bakhaw','Balantang','Bengco','Bolilao','Bonifacio (Arevalo)',
                'Bonifacio (City Proper)','Burgos-Mabini-Plaza Libertad',
                'Calahunan','Calaparan','Calubihan','Calumpang','Camalig',
                'Cochero','Compañia','Concepcion-Montes','Cor-Jesus','Cuartero',
                'Danao','Democracia','Desamparados','Divinagracia','Dungon A',
                'Dungon B','Dungon C','East Baluarte','East Timawa','Edganzon',
                'El 98 Sunlife (Estradera)','Fajardo','Flores','Foothill',
                'Forestry','Gellido','General Hughes-Montes','Gloria',
                'Gustilo','Hibao-an Norte','Hibao-an Sur','Hinactacan',
                'Hipodromo','Inday','Infante','Jalandoni Estate',
                'Jalandoni-Wilson','Jaro I','Jaro II','Kahirupan','Kauswagan',
                'Lanit','Lapaz I','Lapaz II','Lapuz Norte','Lapuz Sur',
                'Legaspi dela Rama','Leon Rojas','Libertad','Liberation Road',
                'Loboc','Lobong','Lopez Jaena Norte','Lopez Jaena Sur',
                'Luna','Lungib','M. V. Hechanova','Macarthur','Magdalo',
                'Magsaysay','Mahipon','Malipayon-Delgado','Mansaya-Lapaz',
                'Mansaya','Maria Clara','Mision','Mohon','Monterey',
                'Muelle Loney-Montes','Nabitasan','Navais','Nihao','North Avanceña',
                'North Baluarte','North Fundidor','North San Jose','Obreros',
                'Old Airport (Lapuz)','Ortiz','Osmeña','Our Lady Of Fatima',
                'Pale Oval','Palumpong','Pantalan Navais','Panuypuyan',
                'Paraiso','Paraw','Pena-ploridel','Poblacion Molo',
                'President Roxas','Progreso','Punong','Quezon','Quintin Salas',
                'Rizal (Lapaz)','Rizal Estanzuela','Rizal Palapala I',
                'Rizal Palapala II','Rizal Pala-pala I','Rizal Pala-pala II',
                'Rizal-Estanzuela','Rojas','Sambag','San Agustin',
                'San Antonio','San Felix','San Jose (Arevalo)',
                'San Jose (City Proper)','San Jose (Jaro)','San Jose (Lapaz)',
                'San Nicolas','San Pedro','Santa Cruz','Santo Niño Norte',
                'Santo Niño Sur','Santo Tomas','Simon Ledesma',
                'So-oc','South Fundidor','South San Jose','Taft North',
                'Taft South','Tap-oc','Taytay Zone II','Timawa Tanza I',
                'Timawa Tanza II','Ticud (Lapaz)','Union','Villanueva',
                'West Habog-habog','West Timawa',
            ],
            // ── GENERAL SANTOS CITY ───────────────────────────────────
            'General Santos City' => [
                'Apopong','Baluan','Batomelong','Buayan','Bula','Calumpang',
                'City Heights','Conel','Dadiangas East','Dadiangas North',
                'Dadiangas South','Dadiangas West','Katangawan','Labangal',
                'Lagao (1st & 3rd)','Lagao (2nd)','Ligaya','Mabuhay',
                'Olympog','San Isidro','San Jose','Sinawal','Tambler',
                'Tinagacan','Upper Labay',
            ],
            // ── DIPOLOG CITY ─────────────────────────────────────────
            'Dipolog City' => [
                'Central','Cogon','Dicayas','Diwan','Estaca','Galas','Gulayon',
                'Lugdungan','Minaog','Miputak','Olingan','Punta','Sicayab',
                'Sicayab Bucana','Sinaman','Turno',
            ],
            // ── DAPITAN CITY ─────────────────────────────────────────
            'Dapitan City' => [
                'Bagting','Banbanan','Banonong','Bituogan','Boyog','Canlucani',
                'Dampalan','Dapitan Poblacion','Guimputlan','Hilaan','Ilaya',
                'Kauswagan','La Paz','Larayan','Libuton','Linabo','Looc',
                'Loyola','Maria Cristina','Punta','San Francisco','San Nicolas',
                'San Pedro','Santa Cruz','Sibutan','Sicayab-Bocana','Sigayan',
                'Silinog','Sinawilan','Suba','Sulangon','Tag-olo','Taguilon','Tamion',
            ],
            // ── SINDANGAN ────────────────────────────────────────────
            'Sindangan' => [
                'Balakan','Balukbalacan','Banao','Banisilon','Bantayan',
                'Bitoon','Camul','Colón','Disod','Estipona','Goaw',
                'Guson','Imao','Lintangan','Lipay North','Lipay South',
                'Mejo','Meogan','Misok','Muñoz','Oloog','Pampang',
                'Parangan','Poblacion','Salibo','San Juan','San Isidro',
                'Siay','Sibuguey','Taguilon','Tiguha',
            ],
        ];
    }
};
