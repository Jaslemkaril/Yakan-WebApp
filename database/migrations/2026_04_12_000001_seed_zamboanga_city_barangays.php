<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find Zamboanga City by name (reliable across seeder variants)
        $city = DB::table('philippine_cities')
            ->where('name', 'LIKE', '%Zamboanga City%')
            ->orWhere('city_code', 'ZC')
            ->first();

        if (!$city) {
            return; // city not yet seeded, skip safely
        }

        // Skip if barangays already exist for this city
        if (DB::table('philippine_barangays')->where('city_id', $city->id)->exists()) {
            return;
        }

        $now = now();

        $barangays = [
            'Ayala', 'Baliwasan', 'Baluno', 'Bolong', 'Buenavista', 'Bunguiao',
            'Busay', 'Cabaluay', 'Cabatangan', 'Cacao', 'Calabasa', 'Calarain',
            'Calarian', 'Camino Nuevo', 'Campo Islam', 'Canelar', 'Capisan',
            'Cawit', 'Culianan', 'Curuan', 'Dita', 'Divisoria', 'Dulian (Upper)',
            'Dulian (Lower)', 'Estaka', 'Fiesta Barrio', 'Guiwan', 'Guisao',
            'Kasanyangan', 'La Paz', 'Labuan', 'Lanzones', 'Lapakan',
            'Latuan (Curuan)', 'Licomo', 'Limaong', 'Limpapa', 'Lubigan',
            'Lumbangan', 'Lunzuran', 'Maasin', 'Malagutay', 'Mampang',
            'Manalipa', 'Mangusu', 'Manicahan', 'Mariki', 'Mercedes',
            'Muti', 'Pamucutan', 'Pangapuyan', 'Panubigan', 'Pasilmanta',
            'Pasonanca', 'Patalon', 'Pequeño', 'Putik', 'Quiniput', 'Recodo',
            'Rio Hondo', 'Salaan', 'San Jose Cawa-Cawa', 'San Jose Gusu',
            'San Roque', 'Santa Barbara', 'Santa Catalina', 'Santa Maria',
            'Santo Niño', 'Sibulao (Caruan)', 'Sinubung', 'Sinunoc', 'Sta. Cruz',
            'Tagasilay', 'Taguiti', 'Talabaan', 'Talisayan', 'Talon-Talon',
            'Tamban', 'Tandek', 'Tetuan', 'Tigbalabag', 'Tigtabon', 'Tolosa',
            'Tugbungan', 'Tulungatung', 'Tumaga', 'Tumalutab', 'Tumitus',
            'Victoria', 'Vitali', 'Waling-waling', 'Zone I (Poblacion)',
            'Zone II (Poblacion)', 'Zone III (Poblacion)', 'Zone IV (Poblacion)',
        ];

        $rows = array_map(fn($name, $i) => [
            'city_id'       => $city->id,
            'barangay_code' => 'ZC-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            'name'          => $name,
            'created_at'    => $now,
            'updated_at'    => $now,
        ], $barangays, array_keys($barangays));

        DB::table('philippine_barangays')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        $city = DB::table('philippine_cities')
            ->where('name', 'LIKE', '%Zamboanga City%')
            ->orWhere('city_code', 'ZC')
            ->first();

        if ($city) {
            DB::table('philippine_barangays')->where('city_id', $city->id)->delete();
        }
    }
};
