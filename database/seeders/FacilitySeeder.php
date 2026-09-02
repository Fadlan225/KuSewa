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

            // ── Perlengkapan Kamar ──────────────────────────────────────────────
            ['category' => 'Perlengkapan Kamar',   'name' => 'Kasur',                 'sort_order' => 1],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Bantal',                'sort_order' => 2],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Guling',                'sort_order' => 3],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Lemari Pakaian',        'sort_order' => 4],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Meja',                  'sort_order' => 5],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Meja Rias',             'sort_order' => 6],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Kursi',                 'sort_order' => 7],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Sofa',                  'sort_order' => 8],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Cermin',                'sort_order' => 9],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Brankas',               'sort_order' => 10],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Hair Dryer',            'sort_order' => 11],
            ['category' => 'Perlengkapan Kamar',   'name' => 'Mini Bar',              'sort_order' => 12],

            // ── Sirkulasi Udara ───────────────────────────────────────────────
            ['category' => 'Sirkulasi Udara',      'name' => 'AC',                    'sort_order' => 1],
            ['category' => 'Sirkulasi Udara',      'name' => 'Kipas Angin',           'sort_order' => 2],
            ['category' => 'Sirkulasi Udara',      'name' => 'Ventilasi',             'sort_order' => 3],
            ['category' => 'Sirkulasi Udara',      'name' => 'Jendela',               'sort_order' => 4],

            // ── Dapur ─────────────────────────────────────────────────────────
            ['category' => 'Dapur',              'name' => 'Kulkas',                'sort_order' => 1],
            ['category' => 'Dapur',              'name' => 'Kompor',                'sort_order' => 2],
            ['category' => 'Dapur',              'name' => 'Microwave',             'sort_order' => 3],
            ['category' => 'Dapur',              'name' => 'Peralatan Masak',       'sort_order' => 4],
            ['category' => 'Dapur',              'name' => 'Dapur Bersama',         'sort_order' => 5],
            ['category' => 'Dapur',              'name' => 'Dispenser Air',         'sort_order' => 6],
            ['category' => 'Dapur',              'name' => 'Kulkas Bersama',        'sort_order' => 7],

            // ── Kamar Mandi ───────────────────────────────────────────────────
            ['category' => 'Kamar Mandi',        'name' => 'Bathtub',               'sort_order' => 1],
            ['category' => 'Kamar Mandi',        'name' => 'Shower',                'sort_order' => 2],
            ['category' => 'Kamar Mandi',        'name' => 'Water Heater',          'sort_order' => 3],
            ['category' => 'Kamar Mandi',        'name' => 'Kamar Mandi Dalam',     'sort_order' => 4],
            ['category' => 'Kamar Mandi',        'name' => 'Kamar Mandi Bersama',   'sort_order' => 5],
            ['category' => 'Kamar Mandi',        'name' => 'Kamar Mandi Luar',      'sort_order' => 6],
            ['category' => 'Kamar Mandi',        'name' => 'Toiletries',            'sort_order' => 7],

            // ── Keamanan ──────────────────────────────────────────────────────
            ['category' => 'Keamanan',           'name' => 'CCTV',                  'sort_order' => 1],
            ['category' => 'Keamanan',           'name' => 'Satpam 24 Jam',         'sort_order' => 2],
            ['category' => 'Keamanan',           'name' => 'Pagar',                 'sort_order' => 3],
            ['category' => 'Keamanan',           'name' => 'Intercom',              'sort_order' => 4],
            ['category' => 'Keamanan',           'name' => 'Alarm Kebakaran',       'sort_order' => 5],
            ['category' => 'Keamanan',           'name' => 'Alat Pemadam Api',      'sort_order' => 6],
            ['category' => 'Keamanan',           'name' => 'Kunci Gerbang',         'sort_order' => 7],
            ['category' => 'Keamanan',           'name' => 'Penjaga Kos',           'sort_order' => 8],
            ['category' => 'Keamanan',           'name' => 'Pengurus Kos',          'sort_order' => 9],
            ['category' => 'Keamanan',           'name' => 'Kartu Akses Masuk',     'sort_order' => 10],

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

            // ── Indoor ────────────────────────────────────────────────────────
            ['category' => 'Indoor',             'name' => 'Ruang Santai',          'sort_order' => 1],
            ['category' => 'Indoor',             'name' => 'Ruang Tamu',            'sort_order' => 2],

            // ── Outdoor ───────────────────────────────────────────────────────
            ['category' => 'Outdoor',            'name' => 'Taman',                 'sort_order' => 1],
            ['category' => 'Outdoor',            'name' => 'Gazebo',                'sort_order' => 2],
            ['category' => 'Outdoor',            'name' => 'Area Outdoor',          'sort_order' => 3],
            ['category' => 'Outdoor',            'name' => 'Pemandangan Laut',      'sort_order' => 4],
            ['category' => 'Outdoor',            'name' => 'Pemandangan Gunung',    'sort_order' => 5],

            // ── Laundry & Cuci ────────────────────────────────────────────────
            ['category' => 'Laundry',            'name' => 'Mesin Cuci',            'sort_order' => 1],
            ['category' => 'Laundry',            'name' => 'Layanan Laundry',       'sort_order' => 2],
            ['category' => 'Laundry',            'name' => 'Ruang Cuci',            'sort_order' => 3],
            ['category' => 'Laundry',            'name' => 'Jemuran',               'sort_order' => 4],

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
