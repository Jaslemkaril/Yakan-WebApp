<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Helper: get region id by code
        $rid = fn(string $code) => DB::table('philippine_regions')->where('region_code', $code)->value('id');
        // Helper: get province id by code
        $pid = fn(string $code) => DB::table('philippine_provinces')->where('province_code', $code)->value('id');

        // Helper: insert province if not exists
        $addProvince = function (string $regionCode, string $code, string $name) use ($rid, $now) {
            $regionId = $rid($regionCode);
            if (!$regionId) return;
            if (!DB::table('philippine_provinces')->where('province_code', $code)->exists()) {
                DB::table('philippine_provinces')->insert([
                    'region_id'     => $regionId,
                    'province_code' => $code,
                    'name'          => $name,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        };

        // Helper: insert city if not exists
        $addCity = function (string $provinceCode, string $code, string $name) use ($pid, $now) {
            $provinceId = $pid($provinceCode);
            if (!$provinceId) return;
            if (!DB::table('philippine_cities')->where('city_code', $code)->exists()) {
                DB::table('philippine_cities')->insert([
                    'province_id' => $provinceId,
                    'city_code'   => $code,
                    'name'        => $name,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        };

        // ==================== CAR ====================
        $addProvince('CAR', 'CAR-ABR', 'Abra');
        $addProvince('CAR', 'CAR-APA', 'Apayao');
        $addProvince('CAR', 'CAR-BEN', 'Benguet');
        $addProvince('CAR', 'CAR-IFU', 'Ifugao');
        $addProvince('CAR', 'CAR-KAL', 'Kalinga');
        $addProvince('CAR', 'CAR-MTP', 'Mountain Province');

        $addCity('CAR-BEN', 'BGC', 'Baguio City');
        $addCity('CAR-BEN', 'LTR', 'La Trinidad');
        $addCity('CAR-ABR', 'BAN', 'Bangued');
        $addCity('CAR-IFU', 'LAG', 'Lagawe');
        $addCity('CAR-KAL', 'TAB', 'Tabuk City');
        $addCity('CAR-MTP', 'BON', 'Bontoc');
        $addCity('CAR-APA', 'KBG', 'Kabugao');

        // ==================== REGION I ====================
        $addProvince('I', 'I-ILN', 'Ilocos Norte');
        $addProvince('I', 'I-ILS', 'Ilocos Sur');
        $addProvince('I', 'I-LAU', 'La Union');
        $addProvince('I', 'I-PAN', 'Pangasinan');

        $addCity('I-ILN', 'LAO', 'Laoag City');
        $addCity('I-ILN', 'BAT', 'Batac City');
        $addCity('I-ILS', 'VIG', 'Vigan City');
        $addCity('I-ILS', 'CAN', 'Candon City');
        $addCity('I-LAU', 'SFU', 'San Fernando City');
        $addCity('I-PAN', 'DAG', 'Dagupan City');
        $addCity('I-PAN', 'SCC', 'San Carlos City');
        $addCity('I-PAN', 'URD', 'Urdaneta City');
        $addCity('I-PAN', 'LNG', 'Lingayen');

        // ==================== REGION II ====================
        $addProvince('II', 'II-BTN', 'Batanes');
        $addProvince('II', 'II-CAG', 'Cagayan');
        $addProvince('II', 'II-ISA', 'Isabela');
        $addProvince('II', 'II-NVI', 'Nueva Vizcaya');
        $addProvince('II', 'II-QUI', 'Quirino');

        $addCity('II-BTN', 'BAC', 'Basco');
        $addCity('II-CAG', 'TUG', 'Tuguegarao City');
        $addCity('II-CAG', 'APP', 'Aparri');
        $addCity('II-ISA', 'ILG', 'Ilagan City');
        $addCity('II-ISA', 'CAU', 'Cauayan City');
        $addCity('II-ISA', 'SNT', 'Santiago City');
        $addCity('II-NVI', 'BAY2', 'Bayombong');
        $addCity('II-QUI', 'CAB', 'Cabarroguis');

        // ==================== REGION III ====================
        $addProvince('III', 'III-AUR', 'Aurora');
        $addProvince('III', 'III-BAT', 'Bataan');
        $addProvince('III', 'III-BUL', 'Bulacan');
        $addProvince('III', 'III-NEC', 'Nueva Ecija');
        $addProvince('III', 'III-PAM', 'Pampanga');
        $addProvince('III', 'III-TAR', 'Tarlac');
        $addProvince('III', 'III-ZAM', 'Zambales');

        $addCity('III-AUR', 'BAL', 'Baler');
        $addCity('III-BAT', 'BAL2', 'Balanga City');
        $addCity('III-BUL', 'MAL', 'Malolos City');
        $addCity('III-BUL', 'MBP', 'Meycauayan City');
        $addCity('III-BUL', 'SJD', 'San Jose del Monte City');
        $addCity('III-NEC', 'CAB2', 'Cabanatuan City');
        $addCity('III-NEC', 'GPA', 'Gapan City');
        $addCity('III-NEC', 'MUN', 'Muñoz');
        $addCity('III-NEC', 'SCI', 'Science City of Muñoz');
        $addCity('III-PAM', 'ANG', 'Angeles City');
        $addCity('III-PAM', 'SFP', 'San Fernando City');
        $addCity('III-TAR', 'TRC', 'Tarlac City');
        $addCity('III-ZAM', 'OLP', 'Olongapo City');
        $addCity('III-ZAM', 'IBA', 'Iba');

        // ==================== REGION IV-A ====================
        $addProvince('IV-A', 'IVA-BTG', 'Batangas');
        $addProvince('IV-A', 'IVA-CAV', 'Cavite');
        $addProvince('IV-A', 'IVA-LAG', 'Laguna');
        $addProvince('IV-A', 'IVA-QUE', 'Quezon');
        $addProvince('IV-A', 'IVA-RIZ', 'Rizal');

        $addCity('IVA-BTG', 'BAT2', 'Batangas City');
        $addCity('IVA-BTG', 'LIP', 'Lipa City');
        $addCity('IVA-BTG', 'STO', 'Sto. Tomas');
        $addCity('IVA-CAV', 'BCR', 'Bacoor City');
        $addCity('IVA-CAV', 'DAS', 'Dasmariñas City');
        $addCity('IVA-CAV', 'GTR', 'General Trias City');
        $addCity('IVA-CAV', 'IMU', 'Imus City');
        $addCity('IVA-CAV', 'CAV', 'Cavite City');
        $addCity('IVA-LAG', 'CLM', 'Calamba City');
        $addCity('IVA-LAG', 'SRS', 'Santa Rosa City');
        $addCity('IVA-LAG', 'BIN', 'Biñan City');
        $addCity('IVA-LAG', 'SPC', 'San Pablo City');
        $addCity('IVA-QUE', 'LUC', 'Lucena City');
        $addCity('IVA-QUE', 'QUE', 'Quezon City (Lucban)');
        $addCity('IVA-RIZ', 'ANT', 'Antipolo City');
        $addCity('IVA-RIZ', 'CAI', 'Cainta');
        $addCity('IVA-RIZ', 'TAY', 'Taytay');

        // ==================== REGION IV-B ====================
        $addProvince('IV-B', 'IVB-MAR', 'Marinduque');
        $addProvince('IV-B', 'IVB-OCM', 'Occidental Mindoro');
        $addProvince('IV-B', 'IVB-ORM', 'Oriental Mindoro');
        $addProvince('IV-B', 'IVB-PAL', 'Palawan');
        $addProvince('IV-B', 'IVB-ROM', 'Romblon');

        $addCity('IVB-MAR', 'BOA', 'Boac');
        $addCity('IVB-OCM', 'SJS', 'San Jose');
        $addCity('IVB-ORM', 'CAL', 'Calapan City');
        $addCity('IVB-PAL', 'PPC', 'Puerto Princesa City');
        $addCity('IVB-PAL', 'EPN', 'El Nido');
        $addCity('IVB-PAL', 'CBU', 'Coron');
        $addCity('IVB-ROM', 'RMB', 'Romblon');

        // ==================== REGION V ====================
        $addProvince('V', 'V-ALB', 'Albay');
        $addProvince('V', 'V-CMN', 'Camarines Norte');
        $addProvince('V', 'V-CMS', 'Camarines Sur');
        $addProvince('V', 'V-CAT', 'Catanduanes');
        $addProvince('V', 'V-MAS', 'Masbate');
        $addProvince('V', 'V-SOR', 'Sorsogon');

        $addCity('V-ALB', 'LEG', 'Legazpi City');
        $addCity('V-ALB', 'LIG', 'Ligao City');
        $addCity('V-ALB', 'TAB2', 'Tabaco City');
        $addCity('V-CMN', 'DAE', 'Daet');
        $addCity('V-CMS', 'NAG', 'Naga City');
        $addCity('V-CMS', 'IRI', 'Iriga City');
        $addCity('V-CAT', 'VIR', 'Virac');
        $addCity('V-MAS', 'MSB', 'Masbate City');
        $addCity('V-SOR', 'SRS2', 'Sorsogon City');

        // ==================== REGION VI ====================
        $addProvince('VI', 'VI-AKL', 'Aklan');
        $addProvince('VI', 'VI-ANT', 'Antique');
        $addProvince('VI', 'VI-CAP', 'Capiz');
        $addProvince('VI', 'VI-GUI', 'Guimaras');
        $addProvince('VI', 'VI-ILO', 'Iloilo');
        $addProvince('VI', 'VI-NEG', 'Negros Occidental');

        $addCity('VI-AKL', 'KLB', 'Kalibo');
        $addCity('VI-ANT', 'SJB', 'San Jose de Buenavista');
        $addCity('VI-CAP', 'ROX', 'Roxas City');
        $addCity('VI-GUI', 'JOR', 'Jordan');
        $addCity('VI-ILO', 'ILC', 'Iloilo City');
        $addCity('VI-ILO', 'PAP', 'Passi City');
        $addCity('VI-NEG', 'BAC2', 'Bacolod City');
        $addCity('VI-NEG', 'BIN2', 'Bago City');
        $addCity('VI-NEG', 'CAD', 'Cadiz City');
        $addCity('VI-NEG', 'SCC2', 'Sagay City');

        // ==================== REGION VIII ====================
        $addProvince('VIII', 'VIII-BIL', 'Biliran');
        $addProvince('VIII', 'VIII-EAS', 'Eastern Samar');
        $addProvince('VIII', 'VIII-LEY', 'Leyte');
        $addProvince('VIII', 'VIII-NOS', 'Northern Samar');
        $addProvince('VIII', 'VIII-SAM', 'Samar');
        $addProvince('VIII', 'VIII-SLS', 'Southern Leyte');

        $addCity('VIII-BIL', 'NAV', 'Naval');
        $addCity('VIII-EAS', 'BOR', 'Borongan City');
        $addCity('VIII-LEY', 'TAC', 'Tacloban City');
        $addCity('VIII-LEY', 'ORN', 'Ormoc City');
        $addCity('VIII-NOS', 'CAT2', 'Catarman');
        $addCity('VIII-SAM', 'CAB3', 'Catbalogan City');
        $addCity('VIII-SLS', 'MAS2', 'Maasin City');

        // ==================== REGION X ====================
        $addProvince('X', 'X-BUK', 'Bukidnon');
        $addProvince('X', 'X-CAM', 'Camiguin');
        $addProvince('X', 'X-LDN', 'Lanao del Norte');
        $addProvince('X', 'X-MSO', 'Misamis Occidental');
        $addProvince('X', 'X-MSR', 'Misamis Oriental');

        $addCity('X-BUK', 'MLY', 'Malaybalay City');
        $addCity('X-BUK', 'VAL', 'Valencia City');
        $addCity('X-CAM', 'MAM', 'Mambajao');
        $addCity('X-LDN', 'ILG2', 'Iligan City');
        $addCity('X-MSO', 'OZA', 'Ozamiz City');
        $addCity('X-MSO', 'ORQ', 'Oroquieta City');
        $addCity('X-MSO', 'TAN', 'Tangub City');
        $addCity('X-MSR', 'CDO', 'Cagayan de Oro City');
        $addCity('X-MSR', 'GIN', 'Gingoog City');
        $addCity('X-MSR', 'ELC', 'El Salvador City');

        // ==================== REGION XII ====================
        $addProvince('XII', 'XII-COT', 'Cotabato');
        $addProvince('XII', 'XII-SAR', 'Sarangani');
        $addProvince('XII', 'XII-SCO', 'South Cotabato');
        $addProvince('XII', 'XII-SKU', 'Sultan Kudarat');

        $addCity('XII-COT', 'KID', 'Kidapawan City');
        $addCity('XII-SAR', 'ALA', 'Alabel');
        $addCity('XII-SAR', 'GEN', 'General Santos City');
        $addCity('XII-SCO', 'KOR', 'Koronadal City');
        $addCity('XII-SCO', 'TBL', 'Tboli');
        $addCity('XII-SKU', 'ISU', 'Isulan');
        $addCity('XII-SKU', 'TCD', 'Tacurong City');

        // ==================== REGION XIII ====================
        $addProvince('XIII', 'XIII-AGN', 'Agusan del Norte');
        $addProvince('XIII', 'XIII-AGS', 'Agusan del Sur');
        $addProvince('XIII', 'XIII-DIN', 'Dinagat Islands');
        $addProvince('XIII', 'XIII-SUN', 'Surigao del Norte');
        $addProvince('XIII', 'XIII-SUS', 'Surigao del Sur');

        $addCity('XIII-AGN', 'BUT', 'Butuan City');
        $addCity('XIII-AGN', 'CAG2', 'Cabadbaran City');
        $addCity('XIII-AGS', 'BAY3', 'Bayugan City');
        $addCity('XIII-AGS', 'PRO', 'Prosperidad');
        $addCity('XIII-DIN', 'SJE', 'San Jose');
        $addCity('XIII-SUN', 'SUR', 'Surigao City');
        $addCity('XIII-SUS', 'TAO', 'Tandag City');
        $addCity('XIII-SUS', 'BIS', 'Bislig City');

        // ==================== BARMM ====================
        $addProvince('BARMM', 'BARMM-BAS', 'Basilan');
        $addProvince('BARMM', 'BARMM-LDS', 'Lanao del Sur');
        $addProvince('BARMM', 'BARMM-MGN', 'Maguindanao del Norte');
        $addProvince('BARMM', 'BARMM-MGS', 'Maguindanao del Sur');
        $addProvince('BARMM', 'BARMM-SUL', 'Sulu');
        $addProvince('BARMM', 'BARMM-TAW', 'Tawi-Tawi');

        $addCity('BARMM-BAS', 'ISA', 'Isabela City');
        $addCity('BARMM-LDS', 'MAR', 'Marawi City');
        $addCity('BARMM-MGN', 'CTB', 'Cotabato City');
        $addCity('BARMM-MGN', 'UPI', 'Upi');
        $addCity('BARMM-MGS', 'DPT', 'Datu Piang');
        $addCity('BARMM-SUL', 'JOL', 'Jolo');
        $addCity('BARMM-TAW', 'BON2', 'Bongao');
    }

    public function down(): void
    {
        // Remove only the provinces added by this migration (by code prefix)
        $codes = [
            'CAR-','I-','II-','III-','IVA-','IVB-','V-','VI-','VIII-','X-','XII-','XIII-','BARMM-'
        ];
        foreach ($codes as $prefix) {
            $provinceIds = DB::table('philippine_provinces')
                ->where('province_code', 'like', $prefix . '%')
                ->pluck('id');
            $cityIds = DB::table('philippine_cities')
                ->whereIn('province_id', $provinceIds)
                ->pluck('id');
            DB::table('philippine_barangays')->whereIn('city_id', $cityIds)->delete();
            DB::table('philippine_cities')->whereIn('province_id', $provinceIds)->delete();
            DB::table('philippine_provinces')->whereIn('id', $provinceIds)->delete();
        }
    }
};
