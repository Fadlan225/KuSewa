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
            'Eksterior',
            'Lobby',
            'Resepsionis',
            'Koridor',
            'Ruang Tamu',
            'Ruang Keluarga',
            'Kamar Tidur',
            'Kamar Mandi',
            'Dapur',
            'Ruang Makan',
            'Balkon',
            'Ruang Kerja',
            'Ruang Bersama',
            'Lounge',
            'Kolam Renang',
            'Gym',
            'Taman',
            'Area Parkir',
            'Laundry',
            'Area Jemur',
            'Ruang Utama',
            'Panggung',
            'Ruang Ganti',
            'Ruang Makeup',
            'Control Room',
            'Recording Room',
            'Area Studio',
            'Area Gudang',
            'Area Bongkar Muat',
            'Halaman',
            'Lahan',
            'Lingkungan Sekitar',
            'Pemandangan',
            'Denah',
            'Tampak Depan',
            'Area Sekitar',
            'Tampilan Malam Hari',
            'Akses Jalan',
        ];

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
