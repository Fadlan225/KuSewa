<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori berdasarkan nama
        $categories = DB::table('asset_categories')->pluck('id', 'name');

        if ($categories->isEmpty()) {
            $this->command->error('Kategori tidak ditemukan. Jalankan AssetCategorySeeder dulu.');
            return;
        }

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_types')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $types = [
            // ── Hunian ──────────────────────────────────────────────────
            ['category' => 'Hunian',     'name' => 'Rumah',       'allow_units' => false],
            ['category' => 'Hunian',     'name' => 'Villa',       'allow_units' => false],
            ['category' => 'Hunian',     'name' => 'Apartemen',   'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Homestay',    'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Guest House', 'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Kos',         'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Hotel',       'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Resort',      'allow_units' => true],
            ['category' => 'Hunian',     'name' => 'Kontrakan',   'allow_units' => false],

            // ── Komersial ───────────────────────────────────────────────
            ['category' => 'Komersial',  'name' => 'Ruko',        'allow_units' => false],
            ['category' => 'Komersial',  'name' => 'Toko',        'allow_units' => false],
            ['category' => 'Komersial',  'name' => 'Kios',        'allow_units' => true],
            ['category' => 'Komersial',  'name' => 'Gudang',      'allow_units' => false],

            // ── Lahan ───────────────────────────────────────────────────
            ['category' => 'Lahan',      'name' => 'Lahan Kosong',    'allow_units' => false],
            ['category' => 'Lahan',      'name' => 'Lahan Parkir',    'allow_units' => false],
            ['category' => 'Lahan',      'name' => 'Lahan Pertanian', 'allow_units' => false],

            // ── Event ───────────────────────────────────────────────────
            ['category' => 'Event',      'name' => 'Gedung',       'allow_units' => false],
            ['category' => 'Event',      'name' => 'Aula',         'allow_units' => false],
            ['category' => 'Event',      'name' => 'Ruang Meeting', 'allow_units' => false],
            ['category' => 'Event',      'name' => 'Studio',       'allow_units' => true],

            // ── Media Iklan ─────────────────────────────────────────────
            ['category' => 'Media Iklan', 'name' => 'Baliho Digital',      'allow_units' => false],
            ['category' => 'Media Iklan', 'name' => 'Baliho Konvensional', 'allow_units' => false],
        ];

        $rows = array_map(function ($type) use ($categories) {
            return [
                'category_id' => $categories[$type['category']],
                'name'        => $type['name'],
                'allow_units' => $type['allow_units'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }, $types);

        DB::table('asset_types')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' asset types berhasil dibuat!');
    }
}
