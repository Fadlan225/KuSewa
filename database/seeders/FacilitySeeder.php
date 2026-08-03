<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('facilities')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Ambil ID kategori berdasarkan nama
        $cats = DB::table('facility_categories')->pluck('id', 'name');

        if ($cats->isEmpty()) {
            $this->command->error('Facility categories tidak ditemukan. Jalankan FacilityCategorySeeder dulu.');
            return;
        }

        $facilities = [
            // ── Internet ──────────────────────────────────────────────────────
            ['category' => 'Internet',           'name' => 'WiFi',                  'sort_order' => 1],
            ['category' => 'Internet',           'name' => 'WiFi Kecepatan Tinggi', 'sort_order' => 2],

            // ── Hiburan ───────────────────────────────────────────────────────
            ['category' => 'Hiburan',            'name' => 'TV',                    'sort_order' => 1],
            ['category' => 'Hiburan',            'name' => 'TV Kabel',              'sort_order' => 2],
            ['category' => 'Hiburan',            'name' => 'Netflix',               'sort_order' => 3],
            ['category' => 'Hiburan',            'name' => 'Karaoke',               'sort_order' => 4],
            ['category' => 'Hiburan',            'name' => 'Billiard',              'sort_order' => 5],

            // ── Kenyamanan Kamar ──────────────────────────────────────────────
            ['category' => 'Kenyamanan Kamar',   'name' => 'AC',                    'sort_order' => 1],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Kipas Angin',           'sort_order' => 2],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Lemari Pakaian',        'sort_order' => 3],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Meja Kerja',            'sort_order' => 4],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Brankas',               'sort_order' => 5],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Hair Dryer',            'sort_order' => 6],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Mini Bar',              'sort_order' => 7],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Sofa',                  'sort_order' => 8],
            ['category' => 'Kenyamanan Kamar',   'name' => 'Balkon',                'sort_order' => 9],

            // ── Dapur ─────────────────────────────────────────────────────────
            ['category' => 'Dapur',              'name' => 'Kulkas',                'sort_order' => 1],
            ['category' => 'Dapur',              'name' => 'Kompor',                'sort_order' => 2],
            ['category' => 'Dapur',              'name' => 'Microwave',             'sort_order' => 3],
            ['category' => 'Dapur',              'name' => 'Peralatan Masak',       'sort_order' => 4],
            ['category' => 'Dapur',              'name' => 'Dapur Bersama',         'sort_order' => 5],
            ['category' => 'Dapur',              'name' => 'Dispenser Air',         'sort_order' => 6],

            // ── Kamar Mandi ───────────────────────────────────────────────────
            ['category' => 'Kamar Mandi',        'name' => 'Bathtub',               'sort_order' => 1],
            ['category' => 'Kamar Mandi',        'name' => 'Shower',                'sort_order' => 2],
            ['category' => 'Kamar Mandi',        'name' => 'Water Heater',          'sort_order' => 3],
            ['category' => 'Kamar Mandi',        'name' => 'Kamar Mandi Dalam',     'sort_order' => 4],
            ['category' => 'Kamar Mandi',        'name' => 'Kamar Mandi Bersama',   'sort_order' => 5],
            ['category' => 'Kamar Mandi',        'name' => 'Toiletries',            'sort_order' => 6],

            // ── Keamanan ──────────────────────────────────────────────────────
            ['category' => 'Keamanan',           'name' => 'CCTV',                  'sort_order' => 1],
            ['category' => 'Keamanan',           'name' => 'Satpam 24 Jam',         'sort_order' => 2],
            ['category' => 'Keamanan',           'name' => 'Pagar',                 'sort_order' => 3],
            ['category' => 'Keamanan',           'name' => 'Intercom',              'sort_order' => 4],
            ['category' => 'Keamanan',           'name' => 'Alarm Kebakaran',       'sort_order' => 5],
            ['category' => 'Keamanan',           'name' => 'Alat Pemadam Api',      'sort_order' => 6],

            // ── Parkir ────────────────────────────────────────────────────────
            ['category' => 'Parkir',             'name' => 'Parkir Motor',          'sort_order' => 1],
            ['category' => 'Parkir',             'name' => 'Parkir Mobil',          'sort_order' => 2],
            ['category' => 'Parkir',             'name' => 'Parkir Bus',            'sort_order' => 3],
            ['category' => 'Parkir',             'name' => 'Parkir Gratis',         'sort_order' => 4],

            // ── Akses & Mobilitas ─────────────────────────────────────────────
            ['category' => 'Akses & Mobilitas',  'name' => 'Lift',                  'sort_order' => 1],
            ['category' => 'Akses & Mobilitas',  'name' => 'Akses Disabilitas',     'sort_order' => 2],
            ['category' => 'Akses & Mobilitas',  'name' => 'Resepsionis 24 Jam',    'sort_order' => 3],
            ['category' => 'Akses & Mobilitas',  'name' => 'Antar Jemput Bandara',  'sort_order' => 4],

            // ── Olahraga & Rekreasi ───────────────────────────────────────────
            ['category' => 'Olahraga & Rekreasi','name' => 'Kolam Renang',          'sort_order' => 1],
            ['category' => 'Olahraga & Rekreasi','name' => 'Gym',                   'sort_order' => 2],
            ['category' => 'Olahraga & Rekreasi','name' => 'Sauna',                 'sort_order' => 3],
            ['category' => 'Olahraga & Rekreasi','name' => 'Jacuzzi',               'sort_order' => 4],
            ['category' => 'Olahraga & Rekreasi','name' => 'Lapangan Badminton',    'sort_order' => 5],
            ['category' => 'Olahraga & Rekreasi','name' => 'Lapangan Tenis',        'sort_order' => 6],
            ['category' => 'Olahraga & Rekreasi','name' => 'Jogging Track',         'sort_order' => 7],

            // ── F&B ───────────────────────────────────────────────────────────
            ['category' => 'F&B',                'name' => 'Restoran',              'sort_order' => 1],
            ['category' => 'F&B',                'name' => 'Kafe',                  'sort_order' => 2],
            ['category' => 'F&B',                'name' => 'Minimarket',            'sort_order' => 3],
            ['category' => 'F&B',                'name' => 'Sarapan Gratis',        'sort_order' => 4],
            ['category' => 'F&B',                'name' => 'BBQ Area',              'sort_order' => 5],

            // ── Bisnis ────────────────────────────────────────────────────────
            ['category' => 'Bisnis',             'name' => 'Ruang Meeting',         'sort_order' => 1],
            ['category' => 'Bisnis',             'name' => 'Proyektor',             'sort_order' => 2],
            ['category' => 'Bisnis',             'name' => 'Whiteboard',            'sort_order' => 3],
            ['category' => 'Bisnis',             'name' => 'Sound System',          'sort_order' => 4],
            ['category' => 'Bisnis',             'name' => 'Printer',               'sort_order' => 5],

            // ── Peralatan Musik ───────────────────────────────────────────────
            ['category' => 'Peralatan Musik',    'name' => 'Drum',                  'sort_order' => 1],
            ['category' => 'Peralatan Musik',    'name' => 'Gitar Listrik',         'sort_order' => 2],
            ['category' => 'Peralatan Musik',    'name' => 'Gitar Akustik',         'sort_order' => 3],
            ['category' => 'Peralatan Musik',    'name' => 'Bass',                  'sort_order' => 4],
            ['category' => 'Peralatan Musik',    'name' => 'Keyboard / Piano',      'sort_order' => 5],
            ['category' => 'Peralatan Musik',    'name' => 'Mixer',                 'sort_order' => 6],
            ['category' => 'Peralatan Musik',    'name' => 'Soundproof Room',       'sort_order' => 7],
            ['category' => 'Peralatan Musik',    'name' => 'Ruang Rekaman',         'sort_order' => 8],

            // ── Outdoor ───────────────────────────────────────────────────────
            ['category' => 'Outdoor',            'name' => 'Taman',                 'sort_order' => 1],
            ['category' => 'Outdoor',            'name' => 'Gazebo',                'sort_order' => 2],
            ['category' => 'Outdoor',            'name' => 'Area Outdoor',          'sort_order' => 3],
            ['category' => 'Outdoor',            'name' => 'Pemandangan Laut',      'sort_order' => 4],
            ['category' => 'Outdoor',            'name' => 'Pemandangan Gunung',    'sort_order' => 5],

            // ── Laundry ───────────────────────────────────────────────────────
            ['category' => 'Laundry',            'name' => 'Mesin Cuci',            'sort_order' => 1],
            ['category' => 'Laundry',            'name' => 'Layanan Laundry',       'sort_order' => 2],

            // ── Penyimpanan ───────────────────────────────────────────────────
            ['category' => 'Penyimpanan',        'name' => 'Gudang Penyimpanan',    'sort_order' => 1],
            ['category' => 'Penyimpanan',        'name' => 'Rak Penyimpanan',       'sort_order' => 2],
            ['category' => 'Penyimpanan',        'name' => 'Loading Dock',          'sort_order' => 3],

            // ── Lainnya ───────────────────────────────────────────────────────
            ['category' => 'Lainnya',            'name' => 'Smoking Area',          'sort_order' => 1],
            ['category' => 'Lainnya',            'name' => 'Pet Friendly',          'sort_order' => 2],
            ['category' => 'Lainnya',            'name' => 'Ramah Anak',            'sort_order' => 3],
            ['category' => 'Lainnya',            'name' => 'Air PDAM',              'sort_order' => 4],
        ];

        $rows = array_map(fn($f) => [
            'facility_category_id' => $cats[$f['category']],
            'name'       => $f['name'],
            'slug'       => Str::slug($f['name']),
            'is_active'  => true,
            'sort_order' => $f['sort_order'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $facilities);

        DB::table('facilities')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' facilities berhasil dibuat!');
    }
}
