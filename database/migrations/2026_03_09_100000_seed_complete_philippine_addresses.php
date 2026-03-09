<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Comprehensive, idempotent migration that ensures EVERY Philippine region
 * has its provinces and cities populated.  Uses firstOrCreate-style logic
 * so it is safe to run on databases that already have partial data.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── NCR ──────────────────────────────────────────────────────
        $this->seed('NCR', [
            ['name' => 'Metro Manila', 'cities' => [
                'Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong',
                'Caloocan', 'Valenzuela', 'Parañaque', 'Las Piñas', 'Muntinlupa',
                'Marikina', 'Pasay', 'San Juan', 'Navotas', 'Malabon', 'Pateros',
            ]],
        ]);

        // ── CAR ──────────────────────────────────────────────────────
        $this->seed('CAR', [
            ['name' => 'Abra',             'cities' => ['Bangued', 'Bucay', 'Lagangilang', 'Dolores', 'Tayum', 'Pidigan', 'Manabo']],
            ['name' => 'Apayao',           'cities' => ['Kabugao', 'Luna', 'Calanasan', 'Conner', 'Flora', 'Pudtol']],
            ['name' => 'Benguet',          'cities' => ['Baguio City', 'La Trinidad', 'Itogon', 'Tuba', 'Mankayan', 'Bokod', 'Kabayan', 'Tublay']],
            ['name' => 'Ifugao',           'cities' => ['Lagawe', 'Banaue', 'Hungduan', 'Alfonso Lista', 'Kiangan', 'Lamut']],
            ['name' => 'Kalinga',          'cities' => ['Tabuk City', 'Pinukpuk', 'Tanudan', 'Rizal', 'Balbalan', 'Lubuagan']],
            ['name' => 'Mountain Province','cities' => ['Bontoc', 'Sagada', 'Bauko', 'Tadian', 'Besao', 'Sabangan', 'Barlig']],
        ]);

        // ── Region I – Ilocos ────────────────────────────────────────
        $this->seed('I', [
            ['name' => 'Ilocos Norte',  'cities' => ['Laoag City', 'Batac City', 'Paoay', 'Pagudpud', 'Bangui', 'Burgos', 'Dingras', 'Marcos', 'Sarrat']],
            ['name' => 'Ilocos Sur',    'cities' => ['Vigan City', 'Candon City', 'Narvacan', 'Santa', 'Bantay', 'Cabugao', 'San Ildefonso', 'Tagudin']],
            ['name' => 'La Union',      'cities' => ['San Fernando City', 'Bauang', 'Agoo', 'Naguilian', 'Rosario', 'Bacnotan', 'Aringay', 'Bangar']],
            ['name' => 'Pangasinan',    'cities' => ['Dagupan City', 'Urdaneta City', 'Alaminos City', 'Lingayen', 'San Carlos City', 'Manaoag', 'Binalonan', 'Mangaldan', 'Rosales', 'Bayambang', 'Calasiao', 'Malasiqui']],
        ]);

        // ── Region II – Cagayan Valley ───────────────────────────────
        $this->seed('II', [
            ['name' => 'Batanes',       'cities' => ['Basco', 'Itbayat', 'Uyugan', 'Ivana', 'Mahatao', 'Sabtang']],
            ['name' => 'Cagayan',       'cities' => ['Tuguegarao City', 'Aparri', 'Sanchez-Mira', 'Lal-lo', 'Gonzaga', 'Santa Ana', 'Enrile', 'Peñablanca', 'Gattaran']],
            ['name' => 'Isabela',       'cities' => ['Ilagan City', 'Cauayan City', 'Santiago City', 'Roxas', 'Alicia', 'Cabagan', 'Cordon', 'Echague', 'Tumauini']],
            ['name' => 'Nueva Vizcaya', 'cities' => ['Bayombong', 'Solano', 'Bambang', 'Kasibu', 'Aritao', 'Bagabag', 'Dupax del Sur']],
            ['name' => 'Quirino',       'cities' => ['Cabarroguis', 'Diffun', 'Maddela', 'Aglipay', 'Nagtipunan', 'Saguday']],
        ]);

        // ── Region III – Central Luzon ───────────────────────────────
        $this->seed('III', [
            ['name' => 'Aurora',       'cities' => ['Baler', 'Maria Aurora', 'Dipaculao', 'Dingalan', 'Casiguran', 'Dinalungan', 'Dilasag']],
            ['name' => 'Bataan',       'cities' => ['Balanga City', 'Mariveles', 'Orani', 'Bagac', 'Dinalupihan', 'Hermosa', 'Limay', 'Pilar', 'Samal', 'Abucay', 'Morong', 'Orion']],
            ['name' => 'Bulacan',      'cities' => ['Malolos City', 'Meycauayan City', 'San Jose del Monte City', 'Marilao', 'Bocaue', 'Guiguinto', 'Balagtas', 'Bulakan', 'Calumpit', 'Hagonoy', 'Obando', 'Pandi', 'Plaridel', 'Pulilan', 'Santa Maria']],
            ['name' => 'Nueva Ecija',  'cities' => ['Palayan City', 'Cabanatuan City', 'San Jose City', 'Gapan City', 'Muñoz City', 'Guimba', 'Talavera', 'Cabiao', 'Jaen', 'Zaragoza', 'Santa Rosa', 'Aliaga']],
            ['name' => 'Pampanga',     'cities' => ['San Fernando City', 'Angeles City', 'Mabalacat City', 'Magalang', 'Arayat', 'Guagua', 'Lubao', 'Mexico', 'Porac', 'Santa Rita', 'Bacolor', 'Floridablanca']],
            ['name' => 'Tarlac',       'cities' => ['Tarlac City', 'Capas', 'Concepcion', 'Paniqui', 'Gerona', 'Camiling', 'Victoria', 'La Paz', 'Bamban', 'Moncada']],
            ['name' => 'Zambales',     'cities' => ['Olongapo City', 'Iba', 'San Antonio', 'Subic', 'Botolan', 'Castillejos', 'Masinloc', 'Candelaria', 'Palauig', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz']],
        ]);

        // ── Region IV-A – CALABARZON ─────────────────────────────────
        $this->seed('IV-A', [
            ['name' => 'Batangas',  'cities' => ['Batangas City', 'Lipa City', 'Tanauan City', 'Sto. Tomas City', 'Nasugbu', 'Rosario', 'Balayan', 'Bauan', 'Lemery', 'San Jose', 'Calaca', 'Laurel', 'Mabini', 'Mataas na Kahoy']],
            ['name' => 'Cavite',    'cities' => ['Cavite City', 'Dasmariñas City', 'Bacoor City', 'Imus City', 'General Trias City', 'Trece Martires City', 'Tagaytay City', 'Silang', 'Kawit', 'Noveleta', 'Rosario', 'Carmona', 'Gen. Mariano Alvarez', 'Tanza', 'Naic', 'Indang']],
            ['name' => 'Laguna',    'cities' => ['Santa Cruz', 'Calamba City', 'San Pablo City', 'San Pedro City', 'Biñan City', 'Santa Rosa City', 'Cabuyao City', 'Los Baños', 'Bay', 'Pagsanjan', 'Nagcarlan', 'Pila', 'Pakil']],
            ['name' => 'Quezon',    'cities' => ['Lucena City', 'Tayabas City', 'Gumaca', 'Infanta', 'Candelaria', 'Sariaya', 'Pagbilao', 'Unisan', 'Mulanay', 'Catanauan', 'Lopez', 'Real']],
            ['name' => 'Rizal',     'cities' => ['Antipolo City', 'Binangonan', 'Cainta', 'Taytay', 'Rodriguez', 'San Mateo', 'Angono', 'Morong', 'Tanay', 'Cardona', 'Teresa', 'Pililla', 'Baras', 'Jala-Jala']],
        ]);

        // ── Region IV-B – MIMAROPA ───────────────────────────────────
        $this->seed('IV-B', [
            ['name' => 'Marinduque',        'cities' => ['Boac', 'Mogpog', 'Santa Cruz', 'Gasan', 'Buenavista', 'Torrijos']],
            ['name' => 'Occidental Mindoro', 'cities' => ['Mamburao', 'San Jose', 'Calintaan', 'Rizal', 'Sablayan', 'Abra de Ilog', 'Santa Cruz', 'Looc', 'Lubang', 'Paluan', 'Magsaysay']],
            ['name' => 'Oriental Mindoro',   'cities' => ['Calapan City', 'Puerto Galera', 'Bongabong', 'Pinamalayan', 'Roxas', 'Naujan', 'Victoria', 'Pola', 'Bansud', 'Gloria', 'Mansalay', 'Bulalacao', 'Socorro']],
            ['name' => 'Palawan',            'cities' => ['Puerto Princesa City', 'El Nido', 'Coron', 'Roxas', 'San Vicente', 'Taytay', 'Brooke\'s Point', 'Narra', 'Aborlan', 'Quezon', 'Balabac', 'Sofronio Española', 'Bataraza', 'Busuanga', 'Culion', 'Linapacan']],
            ['name' => 'Romblon',            'cities' => ['Romblon', 'Odiongan', 'San Fernando', 'Magdiwang', 'Cajidiocan', 'Santa Fe', 'Looc', 'San Agustin', 'Alcantara', 'Banton', 'Corcuera', 'Concepcion', 'Ferrol', 'San Andres', 'Santa Maria']],
        ]);

        // ── Region V – Bicol ─────────────────────────────────────────
        $this->seed('V', [
            ['name' => 'Albay',           'cities' => ['Legazpi City', 'Ligao City', 'Tabaco City', 'Daraga', 'Camalig', 'Guinobatan', 'Polangui', 'Libon', 'Oas', 'Tiwi', 'Malilipot', 'Manito', 'Rapu-Rapu', 'Bacacay', 'Malinao', 'Sto. Domingo', 'Jovellar']],
            ['name' => 'Camarines Norte', 'cities' => ['Daet', 'Labo', 'Vinzons', 'Jose Panganiban', 'Mercedes', 'Paracale', 'Talisay', 'Basud', 'Capalonga', 'San Lorenzo Ruiz', 'San Vicente', 'Santa Elena']],
            ['name' => 'Camarines Sur',   'cities' => ['Naga City', 'Iriga City', 'Pili', 'Libmanan', 'Sipocot', 'Nabua', 'Bato', 'Calabanga', 'Goa', 'Del Gallego', 'Ragay', 'Tigaon', 'Camaligan', 'Gainza', 'Magarao', 'Milaor', 'Minalabac', 'Ocampo', 'Pasacao', 'San Fernando', 'Tinambac']],
            ['name' => 'Catanduanes',     'cities' => ['Virac', 'Viga', 'Bagamanoc', 'Pandan', 'Bato', 'Baras', 'Caramoran', 'Gigmoto', 'Panganiban', 'San Andres', 'San Miguel']],
            ['name' => 'Masbate',         'cities' => ['Masbate City', 'Cataingan', 'Milagros', 'Uson', 'Aroroy', 'Mandaon', 'Mobo', 'Balud', 'Dimasalang', 'Esperanza', 'Placer', 'Cawayan', 'Palanas']],
            ['name' => 'Sorsogon',        'cities' => ['Sorsogon City', 'Bulusan', 'Casiguran', 'Gubat', 'Irosin', 'Juban', 'Matnog', 'Pilar', 'Bulan', 'Donsol', 'Castilla', 'Magallanes', 'Prieto Diaz', 'Santa Magdalena', 'Barcelona']],
        ]);

        // ── Region VI – Western Visayas ──────────────────────────────
        $this->seed('VI', [
            ['name' => 'Aklan',             'cities' => ['Kalibo', 'Numancia', 'Ibajay', 'Makato', 'Malay', 'Nabas', 'Batan', 'Altavas', 'Libacao', 'Madalag', 'New Washington', 'Lezo', 'Tangalan', 'Banga', 'Balete', 'Malinao', 'Buruanga']],
            ['name' => 'Antique',           'cities' => ['San Jose de Buenavista', 'Tibiao', 'Culasi', 'Pandan', 'Sebaste', 'Barbaza', 'Hamtic', 'Tobias Fornier', 'Anini-y', 'Patnongon', 'Belison', 'Bugasong', 'Caluya', 'Laua-an', 'Libertad', 'Sibalom', 'Valderrama']],
            ['name' => 'Capiz',             'cities' => ['Roxas City', 'Pontevedra', 'Panay', 'Sigma', 'Dumalag', 'Jamindan', 'Tapaz', 'Mambusao', 'Sapian', 'Pilar', 'Dao', 'Ivisan', 'Cuartero', 'Panitan', 'President Roxas', 'Ma-ayon']],
            ['name' => 'Guimaras',          'cities' => ['Jordan', 'Buenavista', 'Nueva Valencia', 'San Lorenzo', 'Sibunag']],
            ['name' => 'Iloilo',            'cities' => ['Iloilo City', 'Passi City', 'Pototan', 'Dumangas', 'Pavia', 'Sta. Barbara', 'Cabatuan', 'Oton', 'Leganes', 'San Miguel', 'Jaro', 'Molo', 'Arevalo', 'La Paz', 'Mandurriao', 'Barotac Nuevo', 'Dingle', 'Mina', 'Alimodian', 'Leon']],
            ['name' => 'Negros Occidental', 'cities' => ['Bacolod City', 'San Carlos City', 'Silay City', 'Escalante City', 'Sagay City', 'Cadiz City', 'Victorias City', 'Talisay City', 'Kabankalan City', 'La Carlota City', 'Himamaylan City', 'Bago City', 'Murcia', 'Pulupandan', 'Valladolid', 'Manapla']],
        ]);

        // ── Region VII – Central Visayas ─────────────────────────────
        $this->seed('VII', [
            ['name' => 'Cebu',            'cities' => ['Cebu City', 'Lapu-Lapu City', 'Mandaue City', 'Talisay City', 'Danao City', 'Toledo City', 'Naga City', 'Carcar City', 'Bogo City', 'Minglanilla', 'Consolacion', 'Liloan', 'Compostela', 'Cordova', 'Argao', 'Moalboal', 'Oslob', 'Bantayan', 'Dalaguete', 'Ginatilan', 'Pinamungajan', 'San Fernando', 'Sogod', 'Tabuelan']],
            ['name' => 'Bohol',           'cities' => ['Tagbilaran City', 'Panglao', 'Dauis', 'Baclayon', 'Loboc', 'Carmen', 'Jagna', 'Talibon', 'Ubay', 'Tubigon', 'Loon', 'Anda', 'Guindulman', 'Garcia Hernandez', 'Alburquerque', 'Calape', 'Catigbian', 'Clarin', 'Cortes', 'Dimiao', 'Duero', 'Inabanga', 'Lila', 'Loay', 'Maribojoc', 'Pilar', 'Sierra Bullones', 'Trinidad', 'Valencia']],
            ['name' => 'Negros Oriental', 'cities' => ['Dumaguete City', 'Bayawan City', 'Bais City', 'Tanjay City', 'Canlaon City', 'Guihulngan City', 'Sibulan', 'Bacong', 'Dauin', 'Valencia', 'Amlan', 'San Jose', 'Zamboanguita', 'Siaton', 'Tayasan', 'La Libertad', 'Jimalalud', 'Ayungon', 'Bindoy', 'Mabinay', 'Manjuyod', 'Santa Catalina', 'Basay']],
            ['name' => 'Siquijor',        'cities' => ['Siquijor', 'Larena', 'Lazi', 'San Juan', 'Enrique Villanueva', 'Maria']],
        ]);

        // ── Region VIII – Eastern Visayas ────────────────────────────
        $this->seed('VIII', [
            ['name' => 'Biliran',        'cities' => ['Naval', 'Caibiran', 'Culaba', 'Kawayan', 'Almeria', 'Biliran', 'Cabucgayan', 'Maripipi']],
            ['name' => 'Eastern Samar',  'cities' => ['Borongan City', 'Guiuan', 'Dolores', 'Llorente', 'Oras', 'Taft', 'Can-avid', 'Arteche', 'Balangiga', 'Balangkayan', 'Gen. MacArthur', 'Giporlos', 'Hernani', 'Jipapad', 'Lawaan', 'Maslog', 'Maydolong', 'Mercedes', 'Quinapondan', 'Salcedo', 'San Julian', 'San Policarpo', 'Sulat']],
            ['name' => 'Leyte',          'cities' => ['Tacloban City', 'Ormoc City', 'Baybay City', 'Palo', 'Carigara', 'Abuyog', 'Alangalang', 'Burauen', 'Dagami', 'Dulag', 'Hilongos', 'Hindang', 'Isabel', 'Javier', 'Julita', 'Kananga', 'La Paz', 'Leyte', 'MacArthur', 'Mahaplag', 'Matalom', 'Merida', 'Pastrana', 'Santa Fe', 'Tabontabon', 'Tanauan', 'Tolosa', 'Tunga', 'Villaba']],
            ['name' => 'Northern Samar', 'cities' => ['Catarman', 'Laoang', 'Allen', 'Lavezares', 'Bobon', 'Biri', 'Capul', 'Catubig', 'Gamay', 'Lapinig', 'Las Navas', 'Lope de Vega', 'Mapanas', 'Mondragon', 'Palapag', 'Pambujan', 'Rosario', 'San Antonio', 'San Jose', 'San Roque', 'San Vicente', 'Silvino Lobos', 'Victoria']],
            ['name' => 'Samar',          'cities' => ['Catbalogan City', 'Calbayog City', 'Paranas', 'Jiabong', 'Basey', 'Calbiga', 'Daram', 'Gandara', 'Hinabangan', 'Marabut', 'Motiong', 'Pinabacdao', 'San Jorge', 'San Jose de Buan', 'San Sebastian', 'Santa Margarita', 'Santa Rita', 'Tarangnan', 'Villareal', 'Wright', 'Zumarraga']],
            ['name' => 'Southern Leyte', 'cities' => ['Maasin City', 'Bontoc', 'Liloan', 'Pintuyan', 'Hinunangan', 'Hinundayan', 'Limasawa', 'Macrohon', 'Malitbog', 'Padre Burgos', 'Saint Bernard', 'San Francisco', 'San Juan', 'San Ricardo', 'Silago', 'Sogod', 'Tomas Oppus', 'Libagon', 'Anahawan']],
        ]);

        // ── Region IX – Zamboanga Peninsula ──────────────────────────
        $this->seed('IX', [
            ['name' => 'Zamboanga del Norte', 'cities' => ['Dipolog City', 'Dapitan City', 'Sindangan', 'Liloy', 'Pinan', 'Polanco', 'Katipunan', 'Godod', 'Gutalac', 'Jose Dalman', 'Kalawit', 'Labason', 'Leon B. Postigo', 'Manukan', 'Mutia', 'Rizal', 'Roxas', 'Salug', 'Sergio Osmeña Sr.', 'Siayan', 'Sibuco', 'Sibutad', 'Siocon', 'Tampilisan']],
            ['name' => 'Zamboanga del Sur',   'cities' => ['Zamboanga City', 'Pagadian City', 'Aurora', 'Bayog', 'Dumingag', 'Guipos', 'Lakewood', 'Lapuyan', 'Midsalip', 'Molave', 'Ramon Magsaysay', 'San Juan', 'San Pablo', 'Tabina', 'Tambulig', 'Tukuran', 'Dimataling', 'Dinas', 'Dumalinao', 'Josefina', 'Kumalarang', 'Labangan', 'Mahayag', 'Margosatubig', 'Pitogo', 'Sominot', 'Tigbao', 'Vincenzo A. Sagun']],
            ['name' => 'Zamboanga Sibugay',   'cities' => ['Ipil', 'Kabasalan', 'Siay', 'Titay', 'Buug', 'Diplahan', 'Imelda', 'Mabuhay', 'Malangas', 'Naga', 'Olutanga', 'Payao', 'Roseller T. Lim', 'Talusan', 'Tungawan', 'Alicia']],
        ]);

        // ── Region X – Northern Mindanao ─────────────────────────────
        $this->seed('X', [
            ['name' => 'Bukidnon',           'cities' => ['Malaybalay City', 'Valencia City', 'Maramag', 'Manolo Fortich', 'Quezon', 'Don Carlos', 'Dangcagan', 'Kalilangan', 'Kibawe', 'Kitaotao', 'Lantapan', 'Libona', 'Malitbog', 'Cabanglasan', 'Damulog', 'Impasugong', 'Kadingilan', 'Pangantucan', 'San Fernando', 'Sumilao', 'Talakag']],
            ['name' => 'Camiguin',           'cities' => ['Mambajao', 'Guinsiliban', 'Mahinog', 'Sagay', 'Catarman']],
            ['name' => 'Lanao del Norte',    'cities' => ['Iligan City', 'Kapatagan', 'Lala', 'Tubod', 'Bacolod', 'Kolambugan', 'Linamon', 'Maigo', 'Matungao', 'Munai', 'Nunungan', 'Pantao Ragat', 'Pantar', 'Poona Piagapo', 'Salvador', 'Sapad', 'Sultan Naga Dimaporo', 'Tagoloan', 'Tangcal', 'Baroy', 'Kauswagan', 'Magsaysay']],
            ['name' => 'Misamis Occidental', 'cities' => ['Oroquieta City', 'Ozamiz City', 'Tangub City', 'Calamba', 'Jimenez', 'Aloran', 'Baliangao', 'Bonifacio', 'Clarin', 'Concepcion', 'Don Victoriano Chiongbian', 'Lopez Jaena', 'Panaon', 'Plaridel', 'Sapang Dalaga', 'Sinacaban', 'Tudela']],
            ['name' => 'Misamis Oriental',   'cities' => ['Cagayan de Oro City', 'El Salvador City', 'Gingoog City', 'Opol', 'Villanueva', 'Alubijid', 'Balingasag', 'Balingoan', 'Binuangan', 'Claveria', 'Gitagum', 'Initao', 'Jasaan', 'Kinoguitan', 'Lagonglong', 'Laguindingan', 'Libertad', 'Lugait', 'Magsaysay', 'Manticao', 'Medina', 'Naawan', 'Salay', 'Sugbongcogon', 'Tagoloan', 'Talisayan']],
        ]);

        // ── Region XI – Davao ────────────────────────────────────────
        $this->seed('XI', [
            ['name' => 'Davao del Sur',    'cities' => ['Davao City', 'Digos City', 'Bansalan', 'Hagonoy', 'Kiblawan', 'Magsaysay', 'Malalag', 'Matanao', 'Padada', 'Santa Cruz', 'Sulop']],
            ['name' => 'Davao del Norte',  'cities' => ['Tagum City', 'Panabo City', 'Island Garden City of Samal', 'Carmen', 'New Corella', 'Asuncion', 'Kapalong', 'San Isidro', 'Santo Tomas', 'Talaingod', 'Braulio E. Dujali']],
            ['name' => 'Davao de Oro',     'cities' => ['Nabunturan', 'Compostela', 'Laak', 'Maragusan', 'Mawab', 'Monkayo', 'Montevista', 'New Bataan', 'Pantukan', 'Mabini', 'Maco']],
            ['name' => 'Davao Occidental', 'cities' => ['Jose Abad Santos', 'Malita', 'Sta. Maria', 'Don Marcelino', 'Sarangani']],
            ['name' => 'Davao Oriental',   'cities' => ['Mati City', 'Baganga', 'Banaybanay', 'Boston', 'Caraga', 'Cateel', 'Governor Generoso', 'Lupon', 'Manay', 'San Isidro', 'Tarragona']],
        ]);

        // ── Region XII – SOCCSKSARGEN ────────────────────────────────
        $this->seed('XII', [
            ['name' => 'North Cotabato',  'cities' => ['Kidapawan City', 'Midsayap', 'Pigcawayan', 'Carmen', 'Kabacan', 'Aleosan', 'Alamada', 'Antipas', 'Arakan', 'Banisilan', 'Libungan', 'Magpet', 'Makilala', 'Matalam', 'M\'lang', 'Pikit', 'President Roxas', 'Tulunan']],
            ['name' => 'Sarangani',       'cities' => ['Alabel', 'Malapatan', 'Malungon', 'Kiamba', 'Glan', 'Maasim', 'Maitum']],
            ['name' => 'South Cotabato',  'cities' => ['Koronadal City', 'General Santos City', 'Surallah', 'Tupi', 'Lake Sebu', 'Banga', 'Norala', 'Polomolok', 'Santo Niño', 'Tampakan', 'Tantangan', 'T\'boli']],
            ['name' => 'Sultan Kudarat',  'cities' => ['Isulan', 'Tacurong City', 'Kalamansig', 'Columbio', 'Lebak', 'Bagumbayan', 'Esperanza', 'Lambayong', 'Lutayan', 'Palimbang', 'President Quirino', 'Sen. Ninoy Aquino']],
        ]);

        // ── Region XIII – Caraga ─────────────────────────────────────
        $this->seed('XIII', [
            ['name' => 'Agusan del Norte', 'cities' => ['Butuan City', 'Cabadbaran City', 'Buenavista', 'Carmen', 'Nasipit', 'Santiago', 'Jabonga', 'Kitcharao', 'Las Nieves', 'Magallanes', 'Remedios T. Romualdez', 'Tubay']],
            ['name' => 'Agusan del Sur',   'cities' => ['Bayugan City', 'San Francisco', 'Prosperidad', 'Bunawan', 'La Paz', 'Talacogon', 'Trento', 'Esperanza', 'Loreto', 'Rosario', 'San Luis', 'Santa Josefa', 'Sibagat', 'Veruela']],
            ['name' => 'Dinagat Islands',  'cities' => ['San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Libjo', 'Loreto', 'Tubajon']],
            ['name' => 'Surigao del Norte','cities' => ['Surigao City', 'Claver', 'Mainit', 'Malimono', 'Placer', 'Alegria', 'Bacuag', 'Burgos', 'Dapa', 'Del Carmen', 'General Luna', 'Gigaquit', 'Pilar', 'San Benito', 'San Francisco', 'San Isidro', 'Santa Monica', 'Sison', 'Socorro', 'Tagana-an', 'Tubod']],
            ['name' => 'Surigao del Sur',  'cities' => ['Bislig City', 'Tandag City', 'Barobo', 'Carrascal', 'Cortes', 'Hinatuan', 'Lanuza', 'Cagwait', 'Cantilan', 'Carmen', 'Lemon', 'Lianga', 'Lingig', 'Madrid', 'Marihatag', 'San Agustin', 'San Miguel', 'Tagbina', 'Tago']],
        ]);

        // ── BARMM ────────────────────────────────────────────────────
        $this->seed('BARMM', [
            ['name' => 'Basilan',               'cities' => ['Isabela City', 'Lamitan City', 'Tipo-Tipo', 'Hadji Mohammad Ajul', 'Al-Barka', 'Akbar', 'Hadji Muhtamad', 'Maluso', 'Sumisip', 'Tabuan-Lasa', 'Tuburan', 'Ungkaya Pukan']],
            ['name' => 'Lanao del Sur',          'cities' => ['Marawi City', 'Bayang', 'Binidayan', 'Balindong', 'Butig', 'Bubong', 'Calanogas', 'Ditsaan-Ramain', 'Ganassi', 'Kapai', 'Kapatagan', 'Lumba-Bayabao', 'Lumbatan', 'Madalum', 'Madamba', 'Maguing', 'Marantao', 'Marogong', 'Masiu', 'Mulondo', 'Pagayawan', 'Piagapo', 'Poona Bayabao', 'Saguiaran', 'Sultan Dumalondong', 'Picong', 'Tamparan', 'Taraka', 'Tugaya', 'Wao']],
            ['name' => 'Maguindanao del Norte',  'cities' => ['Cotabato City', 'Datu Odin Sinsuat', 'Sultan Kudarat', 'Upi', 'Barira', 'Buldon', 'Kabuntalan', 'Matanog', 'Northern Kabuntalan', 'Parang', 'Sultan Mastura', 'Talitay']],
            ['name' => 'Maguindanao del Sur',    'cities' => ['Buluan', 'Datu Piang', 'Pagalungan', 'Talayan', 'General Salipada K. Pendatun', 'Ampatuan', 'Datu Abdulla Sangki', 'Datu Anggal Midtimbang', 'Datu Blah T. Sinsuat', 'Datu Hoffer Ampatuan', 'Datu Montawal', 'Datu Paglas', 'Datu Saudi-Ampatuan', 'Datu Unsay', 'Gen. S. K. Pendatun', 'Guindulungan', 'Mangudadatu', 'Mamasapano', 'Pandag', 'Paglat', 'Rajah Buayan', 'Shariff Aguak', 'Shariff Saydona Mustapha', 'South Upi', 'Sultan sa Barongis']],
            ['name' => 'Sulu',                   'cities' => ['Jolo', 'Patikul', 'Indanan', 'Pangutaran', 'Parang', 'Hadji Panglima Tahil', 'Kalingalan Caluang', 'Lugus', 'Luuk', 'Maimbung', 'Old Panamao', 'Omar', 'Pandami', 'Panglima Estino', 'Pata', 'Siasi', 'Talipao', 'Tapul', 'Tongkil']],
            ['name' => 'Tawi-Tawi',              'cities' => ['Bongao', 'Panglima Sugala', 'Sibutu', 'Simunul', 'Languyan', 'Mapun', 'Sapa-Sapa', 'Sitangkai', 'South Ubian', 'Tandubas', 'Turtle Islands']],
        ]);
    }

    public function down(): void
    {
        // Non-destructive — no rollback needed
    }

    // ─── helpers ─────────────────────────────────────────────────────
    private function seed(string $regionCode, array $provinces): void
    {
        $region = DB::table('philippine_regions')
            ->where('region_code', $regionCode)
            ->first();

        if (!$region) {
            return; // region row doesn't exist yet
        }

        foreach ($provinces as $prov) {
            // Find or create province (match by name + region)
            $existing = DB::table('philippine_provinces')
                ->where('region_id', $region->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($prov['name'])])
                ->first();

            if ($existing) {
                $provinceId = $existing->id;
            } else {
                $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $prov['name']), 0, 4))
                      . '-' . $regionCode;
                $provinceId = DB::table('philippine_provinces')->insertGetId([
                    'region_id'     => $region->id,
                    'province_code' => $code,
                    'name'          => $prov['name'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Insert only missing cities
            foreach ($prov['cities'] as $cityName) {
                $cityExists = DB::table('philippine_cities')
                    ->where('province_id', $provinceId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
                    ->exists();

                if (!$cityExists) {
                    $ccode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $cityName), 0, 4))
                           . '-' . $provinceId;
                    DB::table('philippine_cities')->insert([
                        'province_id' => $provinceId,
                        'city_code'   => $ccode,
                        'name'        => $cityName,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }
};
