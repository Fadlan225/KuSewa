<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GaleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('galery_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Ambil semua tipe asset berdasarkan nama
        $types = DB::table('asset_types')->pluck('id', 'name');

        if ($types->isEmpty()) {
            $this->command->error('Asset types belum ada. Jalankan AssetTypeSeeder dulu.');
            return;
        }

        /**
         * Definisi kategori galeri per tipe.
         * applies_to: 'asset' = untuk aset utama, 'unit' = untuk asset unit
         */
        $definitions = [

            // ──────────────────────────────────────────────
            // HUNIAN
            // ──────────────────────────────────────────────
            'Rumah' => [
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Ruang Tamu',        'applies_to' => 'asset'],
                ['name' => 'Kamar Tidur',       'applies_to' => 'asset'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'asset'],
                ['name' => 'Dapur',             'applies_to' => 'asset'],
                ['name' => 'Halaman',           'applies_to' => 'asset'],
                ['name' => 'Garasi',            'applies_to' => 'asset'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'asset'],
            ],
            'Villa' => [
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Ruang Tamu',        'applies_to' => 'asset'],
                ['name' => 'Kamar Tidur',       'applies_to' => 'asset'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'asset'],
                ['name' => 'Dapur',             'applies_to' => 'asset'],
                ['name' => 'Kolam Renang',      'applies_to' => 'asset'],
                ['name' => 'Taman',             'applies_to' => 'asset'],
                ['name' => 'Pemandangan',       'applies_to' => 'asset'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'asset'],
            ],
            'Apartemen' => [
                // Asset (gedung)
                ['name' => 'Gedung',            'applies_to' => 'asset'],
                ['name' => 'Lobby',             'applies_to' => 'asset'],
                ['name' => 'Fasilitas Bersama', 'applies_to' => 'asset'],
                // Unit
                ['name' => 'Ruang Utama',       'applies_to' => 'unit'],
                ['name' => 'Kamar Tidur',       'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Dapur',             'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Pemandangan',       'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Homestay' => [
                // Asset
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Area Bersama',      'applies_to' => 'asset'],
                // Unit
                ['name' => 'Kamar',             'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Pemandangan',       'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Guest House' => [
                // Asset
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Lobby',             'applies_to' => 'asset'],
                ['name' => 'Area Bersama',      'applies_to' => 'asset'],
                // Unit
                ['name' => 'Kamar',             'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Kos' => [
                // Asset
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Area Bersama',      'applies_to' => 'asset'],
                ['name' => 'Parkir',            'applies_to' => 'asset'],
                // Unit
                ['name' => 'Kamar',             'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Dapur',             'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Hotel' => [
                // Asset
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Lobby',             'applies_to' => 'asset'],
                ['name' => 'Restoran',          'applies_to' => 'asset'],
                ['name' => 'Kolam Renang',      'applies_to' => 'asset'],
                ['name' => 'Gym',               'applies_to' => 'asset'],
                ['name' => 'Area Parkir',       'applies_to' => 'asset'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'asset'],
                // Unit
                ['name' => 'Kamar',             'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Pemandangan',       'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Resort' => [
                // Asset
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Lobby',             'applies_to' => 'asset'],
                ['name' => 'Pantai',            'applies_to' => 'asset'],
                ['name' => 'Kolam Renang',      'applies_to' => 'asset'],
                ['name' => 'Restoran',          'applies_to' => 'asset'],
                ['name' => 'Area Rekreasi',     'applies_to' => 'asset'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'asset'],
                // Unit
                ['name' => 'Kamar',             'applies_to' => 'unit'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'unit'],
                ['name' => 'Balkon',            'applies_to' => 'unit'],
                ['name' => 'Pemandangan',       'applies_to' => 'unit'],
            ],
            'Kontrakan' => [
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Ruang Tamu',        'applies_to' => 'asset'],
                ['name' => 'Kamar Tidur',       'applies_to' => 'asset'],
                ['name' => 'Kamar Mandi',       'applies_to' => 'asset'],
                ['name' => 'Dapur',             'applies_to' => 'asset'],
                ['name' => 'Halaman',           'applies_to' => 'asset'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'asset'],
            ],

            // ──────────────────────────────────────────────
            // KOMERSIAL
            // ──────────────────────────────────────────────
            'Ruko' => [
                ['name' => 'Tampak Depan',      'applies_to' => 'asset'],
                ['name' => 'Area Lantai 1',     'applies_to' => 'asset'],
                ['name' => 'Area Lantai 2',     'applies_to' => 'asset'],
                ['name' => 'Toilet',            'applies_to' => 'asset'],
                ['name' => 'Gudang',            'applies_to' => 'asset'],
                ['name' => 'Parkir',            'applies_to' => 'asset'],
            ],
            'Toko' => [
                ['name' => 'Tampak Depan',      'applies_to' => 'asset'],
                ['name' => 'Area Penjualan',    'applies_to' => 'asset'],
                ['name' => 'Gudang',            'applies_to' => 'asset'],
                ['name' => 'Toilet',            'applies_to' => 'asset'],
                ['name' => 'Parkir',            'applies_to' => 'asset'],
            ],
            'Kios' => [
                // Asset
                ['name' => 'Area Pasar/Mall',   'applies_to' => 'asset'],
                ['name' => 'Koridor',           'applies_to' => 'asset'],
                // Unit
                ['name' => 'Tampak Depan',      'applies_to' => 'unit'],
                ['name' => 'Area Dalam',        'applies_to' => 'unit'],
                ['name' => 'Gudang',            'applies_to' => 'unit'],
                ['name' => 'Fasilitas Lainnya', 'applies_to' => 'unit'],
            ],
            'Gudang' => [
                ['name' => 'Eksterior',         'applies_to' => 'asset'],
                ['name' => 'Area Gudang',       'applies_to' => 'asset'],
                ['name' => 'Loading Dock',      'applies_to' => 'asset'],
                ['name' => 'Kantor',            'applies_to' => 'asset'],
                ['name' => 'Toilet',            'applies_to' => 'asset'],
                ['name' => 'Area Parkir',       'applies_to' => 'asset'],
            ],

            // ──────────────────────────────────────────────
            // LAHAN
            // ──────────────────────────────────────────────
            'Lahan Kosong' => [
                ['name' => 'Tampak Depan',       'applies_to' => 'asset'],
                ['name' => 'Area Lahan',         'applies_to' => 'asset'],
                ['name' => 'Akses Jalan',        'applies_to' => 'asset'],
                ['name' => 'Lingkungan Sekitar', 'applies_to' => 'asset'],
            ],
            'Lahan Parkir' => [
                ['name' => 'Tampak Depan',       'applies_to' => 'asset'],
                ['name' => 'Area Parkir',        'applies_to' => 'asset'],
                ['name' => 'Pos Jaga',           'applies_to' => 'asset'],
                ['name' => 'Akses Jalan',        'applies_to' => 'asset'],
            ],
            'Lahan Pertanian' => [
                ['name' => 'Area Lahan',         'applies_to' => 'asset'],
                ['name' => 'Tanaman',            'applies_to' => 'asset'],
                ['name' => 'Irigasi',            'applies_to' => 'asset'],
                ['name' => 'Akses Jalan',        'applies_to' => 'asset'],
            ],

            // ──────────────────────────────────────────────
            // EVENT
            // ──────────────────────────────────────────────
            'Gedung' => [
                ['name' => 'Tampak Depan',          'applies_to' => 'asset'],
                ['name' => 'Lobby',                 'applies_to' => 'asset'],
                ['name' => 'Ruang Utama',           'applies_to' => 'asset'],
                ['name' => 'Panggung',              'applies_to' => 'asset'],
                ['name' => 'Toilet',                'applies_to' => 'asset'],
                ['name' => 'Area Parkir',           'applies_to' => 'asset'],
            ],
            'Aula' => [
                ['name' => 'Tampak Depan',          'applies_to' => 'asset'],
                ['name' => 'Ruang Aula',            'applies_to' => 'asset'],
                ['name' => 'Panggung',              'applies_to' => 'asset'],
                ['name' => 'Toilet',                'applies_to' => 'asset'],
                ['name' => 'Parkir',                'applies_to' => 'asset'],
            ],
            'Ruang Meeting' => [
                ['name' => 'Tampak Depan',            'applies_to' => 'asset'],
                ['name' => 'Ruang Meeting',           'applies_to' => 'asset'],
                ['name' => 'Meja & Kursi',            'applies_to' => 'asset'],
                ['name' => 'Peralatan Presentasi',    'applies_to' => 'asset'],
                ['name' => 'Pantry',                  'applies_to' => 'asset'],
                ['name' => 'Toilet',                  'applies_to' => 'asset'],
            ],
            'Studio' => [
                // Asset
                ['name' => 'Eksterior',             'applies_to' => 'asset'],
                ['name' => 'Lobby',                 'applies_to' => 'asset'],
                ['name' => 'Ruang Tunggu',          'applies_to' => 'asset'],
                // Unit
                ['name' => 'Area Studio',           'applies_to' => 'unit'],
                ['name' => 'Peralatan',             'applies_to' => 'unit'],
                ['name' => 'Ruang Ganti',           'applies_to' => 'unit'],
                ['name' => 'Toilet',                'applies_to' => 'unit'],
            ],

            // ──────────────────────────────────────────────
            // MEDIA IKLAN
            // ──────────────────────────────────────────────
            'Baliho Digital' => [
                ['name' => 'Tampak Depan',          'applies_to' => 'asset'],
                ['name' => 'Tampak Samping',        'applies_to' => 'asset'],
                ['name' => 'Lingkungan Sekitar',    'applies_to' => 'asset'],
                ['name' => 'Sudut Pandang Jalan',   'applies_to' => 'asset'],
                ['name' => 'Kondisi Siang',         'applies_to' => 'asset'],
                ['name' => 'Kondisi Malam',         'applies_to' => 'asset'],
            ],
            'Baliho Konvensional' => [
                ['name' => 'Tampak Depan',          'applies_to' => 'asset'],
                ['name' => 'Tampak Samping',        'applies_to' => 'asset'],
                ['name' => 'Lingkungan Sekitar',    'applies_to' => 'asset'],
                ['name' => 'Sudut Pandang Jalan',   'applies_to' => 'asset'],
            ],
        ];

        $rows = [];
        foreach ($definitions as $typeName => $categories) {
            if (!isset($types[$typeName])) {
                $this->command->warn("  ⚠ Tipe '{$typeName}' tidak ditemukan di database, dilewati.");
                continue;
            }

            $typeId = $types[$typeName];
            foreach ($categories as $cat) {
                $rows[] = [
                    'asset_type_id' => $typeId,
                    'name'          => $cat['name'],
                    'applies_to'    => $cat['applies_to'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Insert dalam batch untuk performa
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('galery_categories')->insert($chunk);
        }

        $this->command->info('✓ ' . count($rows) . ' galery categories berhasil dibuat!');
    }
}
