<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('asset_categories')->insert([
            [
                'name' => 'Baliho',
                'icon' => 'fa-solid fa-sign-hanging',
                'allow_units' => false,
            ],
            [
                'name' => 'Lahan',
                'icon' => 'fa-solid fa-map',
                'allow_units' => false,
            ],
            [
                'name' => 'Villa',
                'icon' => 'fa-solid fa-house',
                'allow_units' => false,
            ],
            [
                'name' => 'Rumah',
                'icon' => 'fa-solid fa-house-chimney',
                'allow_units' => false,
            ],
            [
                'name' => 'Kos',
                'icon' => 'fa-solid fa-building',
                'allow_units' => true,
            ],
            [
                'name' => 'Gedung',
                'icon' => 'fa-solid fa-city',
                'allow_units' => false,
            ],
            [
                'name' => 'Gudang',
                'icon' => 'fa-solid fa-warehouse',
                'allow_units' => false,
            ],
        ]);
    }
}
