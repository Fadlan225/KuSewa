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

        /**
         * Kategori galeri foto bersifat GLOBAL.
         * Tidak terikat ke asset_type tertentu maupun scope (asset/unit).
         * Owner bebas memilih kategori mana saja saat upload foto.
         */
        $categories = [
            'Tampak Depan',
            'Lobby',
            'Kamar',
            'Kamar Mandi',
            'Tampak Baliho',
            'Area Sekitar',
            'Akses Jalan',
            'Ruang Utama',
            'Area Outdoor',
            'Tampak Gedung',
            'Ruang Unit',
            'Area Bersama',
            'Exterior',
            'Interior',
            'Akses Parkir',
            'Area Gudang',
            'Akses Kendaraan',
            'Loading Area',
            'Keseluruhan Lahan',
            'Akses Masuk',
            'Lingkungan Sekitar',
            'Meja & Kursi',
            'Fasilitas Presentasi',
            'Ruang Keseluruhan',
            'Peralatan',
            'Eksterior',
            'Resepsionis',
            'Koridor',
            'Ruang Tamu',
            'Ruang Keluarga',
            'Kamar Tidur',
            'Dapur',
            'Ruang Makan',
            'Balkon',
            'Ruang Kerja',
            'Lounge',
            'Kolam Renang',
            'Gym',
            'Taman',
            'Area Parkir',
            'Laundry',
            'Area Jemur',
            'Panggung',
            'Ruang Ganti',
            'Ruang Makeup',
            'Control Room',
            'Recording Room',
            'Area Studio',
            'Area Bongkar Muat',
            'Halaman',
            'Lahan',
            'Pemandangan',
            'Denah',
            'Tampilan Malam Hari',
        ];

        $categories = array_unique($categories);

        $now = now();
        $rows = array_map(fn ($name) => [
            'name'       => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ], $categories);

        DB::table('galery_categories')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' Galery Categories berhasil dibuat (global)!');
    }
}
