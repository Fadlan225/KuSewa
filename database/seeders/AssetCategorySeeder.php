<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hunian'],
            ['name' => 'Komersial'],
            ['name' => 'Lahan'],
            ['name' => 'Event'],
            ['name' => 'Media Iklan'],
        ];

        foreach ($categories as $cat) {
            DB::table('asset_categories')->updateOrInsert(
                ['name' => $cat['name']],
                [
                    'updated_at' => now(),
                    // If inserting new record, created_at should be set
                    // But updateOrInsert doesn't automatically handle created_at like eloquent
                ]
            );
        }
    }
}
