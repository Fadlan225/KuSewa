<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks sementara agar TRUNCATE bisa berjalan
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_categories')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('asset_categories')->insert([
            [
                'name'       => 'Hunian',
                'icon'       => 'fa-solid fa-house',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Komersial',
                'icon'       => 'fa-solid fa-store',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Lahan',
                'icon'       => 'fa-solid fa-map',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Event',
                'icon'       => 'fa-solid fa-calendar-star',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Media Iklan',
                'icon'       => 'fa-solid fa-sign-hanging',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
