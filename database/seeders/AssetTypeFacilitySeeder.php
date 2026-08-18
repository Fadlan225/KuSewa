<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypeFacilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('asset_type_facilities')->truncate();

        // Ambil ID asset_type dan facility berdasarkan nama
        $types      = DB::table('asset_types')->pluck('id', 'name');
        $facilities = DB::table('facilities')->pluck('id', 'name');

        if ($types->isEmpty() || $facilities->isEmpty()) {
            $this->command->error('Asset types atau facilities tidak ditemukan. Jalankan seeder terkait dulu.');
            return;
        }

        /**
         * Format: ['type' => 'NamaType', 'facility' => 'NamaFasilitas', 'scope' => 'asset'|'unit']
         *
         * scope = 'asset'  → fasilitas milik aset secara keseluruhan (diwarisi semua unit)
         * scope = 'unit'   → fasilitas tambahan yang hanya ada di unit tertentu
         */
        $rules = [
            // ─────────────────────────────────────────────────────────────────
            // HOTEL (allow_units = true)
            // ─────────────────────────────────────────────────────────────────
            // Level asset (hotel secara keseluruhan)
            ['type' => 'Hotel', 'facility' => 'WiFi',               'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Lift',               'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Kolam Renang',       'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Gym',                'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Restoran',           'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Kafe',               'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Parkir Mobil',       'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Parkir Motor',       'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'CCTV',               'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Satpam 24 Jam',      'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Resepsionis 24 Jam', 'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Sarapan Gratis',     'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Ruang Meeting',      'scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Antar Jemput Bandara','scope' => 'asset'],
            ['type' => 'Hotel', 'facility' => 'Layanan Laundry',    'scope' => 'asset'],
            // Level unit (kamar hotel)
            ['type' => 'Hotel', 'facility' => 'AC',                 'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'TV',                 'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'TV Kabel',           'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Hair Dryer',         'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Brankas',            'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Mini Bar',           'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Bathtub',            'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Shower',             'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Water Heater',       'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Balkon',             'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Sofa',               'scope' => 'unit'],
            ['type' => 'Hotel', 'facility' => 'Meja Kerja',         'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // RESORT (allow_units = true)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Resort', 'facility' => 'WiFi',              'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Kolam Renang',      'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Gym',               'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Restoran',          'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'BBQ Area',          'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Taman',             'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Parkir Mobil',      'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'CCTV',              'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Sarapan Gratis',    'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'Jacuzzi',           'scope' => 'asset'],
            ['type' => 'Resort', 'facility' => 'AC',                'scope' => 'unit'],
            ['type' => 'Resort', 'facility' => 'TV',                'scope' => 'unit'],
            ['type' => 'Resort', 'facility' => 'Hair Dryer',        'scope' => 'unit'],
            ['type' => 'Resort', 'facility' => 'Bathtub',           'scope' => 'unit'],
            ['type' => 'Resort', 'facility' => 'Balkon',            'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // VILLA (allow_units = false) — disewakan langsung sebagai satu unit
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Villa', 'facility' => 'WiFi',               'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'AC',                 'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Kolam Renang',       'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'TV',                 'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Dapur Bersama',      'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Parkir Mobil',       'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'BBQ Area',           'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Taman',              'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'CCTV',               'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Mesin Cuci',         'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Kulkas',             'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Kompor',             'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Gazebo',             'scope' => 'asset'],
            ['type' => 'Villa', 'facility' => 'Pet Friendly',       'scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // APARTEMEN (allow_units = true)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Apartemen', 'facility' => 'WiFi',           'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Lift',           'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Kolam Renang',   'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Gym',            'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Parkir Mobil',   'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Parkir Motor',   'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'CCTV',           'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Satpam 24 Jam',  'scope' => 'asset'],
            ['type' => 'Apartemen', 'facility' => 'Akses Disabilitas','scope' => 'asset'],
            // Level unit
            ['type' => 'Apartemen', 'facility' => 'AC',             'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'TV',             'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'Kulkas',         'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'Kompor',         'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'Mesin Cuci',     'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'Water Heater',   'scope' => 'unit'],
            ['type' => 'Apartemen', 'facility' => 'Balkon',         'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // KOS (allow_units = true)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Kos', 'facility' => 'WiFi',                 'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'Parkir Motor',         'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'Parkir Mobil',         'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'CCTV',                 'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'Dapur Bersama',        'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'Layanan Laundry',      'scope' => 'asset'],
            ['type' => 'Kos', 'facility' => 'Dispenser Air',        'scope' => 'asset'],
            // Level unit (kamar kos)
            ['type' => 'Kos', 'facility' => 'AC',                   'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Kipas Angin',          'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Kamar Mandi Dalam',    'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Kamar Mandi Bersama',  'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Lemari Pakaian',       'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Meja Kerja',           'scope' => 'unit'],
            ['type' => 'Kos', 'facility' => 'Water Heater',         'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // HOMESTAY & GUEST HOUSE (allow_units = true)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Homestay', 'facility' => 'WiFi',            'scope' => 'asset'],
            ['type' => 'Homestay', 'facility' => 'Dapur Bersama',   'scope' => 'asset'],
            ['type' => 'Homestay', 'facility' => 'Parkir Motor',    'scope' => 'asset'],
            ['type' => 'Homestay', 'facility' => 'Parkir Mobil',    'scope' => 'asset'],
            ['type' => 'Homestay', 'facility' => 'AC',              'scope' => 'unit'],
            ['type' => 'Homestay', 'facility' => 'TV',              'scope' => 'unit'],
            ['type' => 'Homestay', 'facility' => 'Kamar Mandi Dalam','scope' => 'unit'],
            ['type' => 'Homestay', 'facility' => 'Water Heater',    'scope' => 'unit'],

            ['type' => 'Guest House', 'facility' => 'WiFi',         'scope' => 'asset'],
            ['type' => 'Guest House', 'facility' => 'Dapur Bersama','scope' => 'asset'],
            ['type' => 'Guest House', 'facility' => 'Parkir Motor', 'scope' => 'asset'],
            ['type' => 'Guest House', 'facility' => 'Parkir Mobil', 'scope' => 'asset'],
            ['type' => 'Guest House', 'facility' => 'CCTV',         'scope' => 'asset'],
            ['type' => 'Guest House', 'facility' => 'AC',           'scope' => 'unit'],
            ['type' => 'Guest House', 'facility' => 'TV',           'scope' => 'unit'],
            ['type' => 'Guest House', 'facility' => 'Kamar Mandi Dalam', 'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // KONTRAKAN (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Kontrakan', 'facility' => 'WiFi',           'scope' => 'asset'],
            ['type' => 'Kontrakan', 'facility' => 'Parkir Motor',   'scope' => 'asset'],
            ['type' => 'Kontrakan', 'facility' => 'Parkir Mobil',   'scope' => 'asset'],
            ['type' => 'Kontrakan', 'facility' => 'Air PDAM',       'scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // GUDANG (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Gudang', 'facility' => 'CCTV',              'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Satpam 24 Jam',     'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Parkir Mobil',      'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Parkir Bus',        'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Loading Dock',      'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Rak Penyimpanan',   'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Alat Pemadam Api',  'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Alarm Kebakaran',   'scope' => 'asset'],
            ['type' => 'Gudang', 'facility' => 'Akses Disabilitas', 'scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // RUKO & TOKO (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Ruko', 'facility' => 'WiFi',                'scope' => 'asset'],
            ['type' => 'Ruko', 'facility' => 'AC',                  'scope' => 'asset'],
            ['type' => 'Ruko', 'facility' => 'Parkir Motor',        'scope' => 'asset'],
            ['type' => 'Ruko', 'facility' => 'Parkir Mobil',        'scope' => 'asset'],
            ['type' => 'Ruko', 'facility' => 'CCTV',                'scope' => 'asset'],
            ['type' => 'Ruko', 'facility' => 'Lift',                'scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // GEDUNG & AULA (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Gedung', 'facility' => 'WiFi',              'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'AC',                'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Lift',              'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Parkir Mobil',      'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Parkir Motor',      'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'CCTV',              'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Satpam 24 Jam',     'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Ruang Meeting',     'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Proyektor',         'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Sound System',      'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Alat Pemadam Api',  'scope' => 'asset'],
            ['type' => 'Gedung', 'facility' => 'Akses Disabilitas', 'scope' => 'asset'],

            ['type' => 'Aula', 'facility' => 'WiFi',                'scope' => 'asset'],
            ['type' => 'Aula', 'facility' => 'AC',                  'scope' => 'asset'],
            ['type' => 'Aula', 'facility' => 'Parkir Mobil',        'scope' => 'asset'],
            ['type' => 'Aula', 'facility' => 'Sound System',        'scope' => 'asset'],
            ['type' => 'Aula', 'facility' => 'Proyektor',           'scope' => 'asset'],
            ['type' => 'Aula', 'facility' => 'Whiteboard',          'scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // RUANG MEETING (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Ruang Meeting', 'facility' => 'WiFi',       'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'AC',         'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Proyektor',  'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Whiteboard', 'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Sound System','scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'TV',         'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Printer',    'scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Parkir Motor','scope' => 'asset'],
            ['type' => 'Ruang Meeting', 'facility' => 'Parkir Mobil','scope' => 'asset'],

            // ─────────────────────────────────────────────────────────────────
            // STUDIO (allow_units = true) — Studio Musik, Foto, dll
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Studio', 'facility' => 'WiFi',              'scope' => 'asset'],
            ['type' => 'Studio', 'facility' => 'AC',                'scope' => 'asset'],
            ['type' => 'Studio', 'facility' => 'Parkir Motor',      'scope' => 'asset'],
            ['type' => 'Studio', 'facility' => 'CCTV',              'scope' => 'asset'],
            // Level unit (per studio)
            ['type' => 'Studio', 'facility' => 'Drum',              'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Gitar Listrik',     'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Gitar Akustik',     'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Bass',              'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Keyboard / Piano',  'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Mixer',             'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Soundproof Room',   'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Ruang Rekaman',     'scope' => 'unit'],
            ['type' => 'Studio', 'facility' => 'Sound System',      'scope' => 'unit'],

            // ─────────────────────────────────────────────────────────────────
            // LAHAN (allow_units = false)
            // ─────────────────────────────────────────────────────────────────
            ['type' => 'Lahan', 'facility' => 'Pagar',       'scope' => 'asset'],
            ['type' => 'Lahan', 'facility' => 'CCTV',        'scope' => 'asset'],
            ['type' => 'Lahan', 'facility' => 'Satpam 24 Jam','scope' => 'asset'],
        ];

        // Filter hanya baris yang type dan facility-nya ada di database
        $rows = [];
        $skipped = 0;

        foreach ($rules as $rule) {
            if (!isset($types[$rule['type']])) {
                $this->command->warn("  ⚠ Asset type '{$rule['type']}' tidak ditemukan, dilewati.");
                $skipped++;
                continue;
            }
            if (!isset($facilities[$rule['facility']])) {
                $this->command->warn("  ⚠ Facility '{$rule['facility']}' tidak ditemukan, dilewati.");
                $skipped++;
                continue;
            }

            $rows[] = [
                'asset_type_id' => $types[$rule['type']],
                'facility_id'   => $facilities[$rule['facility']],
                'scope'         => $rule['scope'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        DB::table('asset_type_facilities')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' asset type facility rules berhasil dibuat!' . ($skipped ? " ($skipped dilewati)" : ''));
    }
}
