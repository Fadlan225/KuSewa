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

        $types = DB::table('asset_types')->get();
        $allCategories = [];

        foreach ($types as $type) {
            $name = $type->name;
            $cats = [];
            
            if ($name == 'Hotel' || $name == 'Resort') {
                $cats[] = ['name' => 'Exterior', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Lobby', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Restaurant', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Swimming Pool', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Room', 'applies_to' => 'unit'];
                $cats[] = ['name' => 'Bathroom', 'applies_to' => 'unit'];
            } elseif ($name == 'Rumah' || $name == 'Villa') {
                $cats[] = ['name' => 'Tampak Depan', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Ruang Tamu', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Kamar Tidur', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Dapur', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Halaman', 'applies_to' => 'asset'];
            } elseif ($name == 'Gudang') {
                $cats[] = ['name' => 'Exterior', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Area Gudang', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Loading Dock', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Office', 'applies_to' => 'asset'];
            } elseif ($name == 'Studio') {
                $cats[] = ['name' => 'Control Room', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Studio Room', 'applies_to' => 'unit'];
                $cats[] = ['name' => 'Equipment', 'applies_to' => 'unit'];
            } elseif (strpos($name, 'Baliho') !== false) {
                $cats[] = ['name' => 'Tampak Depan', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Sudut Jalan', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Lingkungan Sekitar', 'applies_to' => 'asset'];
            } elseif ($type->allow_units) {
                // Default for other types with units (Apartemen, Kos, dll)
                $cats[] = ['name' => 'Exterior', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Fasilitas Umum', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Tampak Dalam Unit', 'applies_to' => 'unit'];
                $cats[] = ['name' => 'Kamar Mandi', 'applies_to' => 'unit'];
            } else {
                // Default for types without units
                $cats[] = ['name' => 'Exterior', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Interior', 'applies_to' => 'asset'];
                $cats[] = ['name' => 'Fasilitas Khusus', 'applies_to' => 'asset'];
            }

            foreach ($cats as $cat) {
                $cat['asset_type_id'] = $type->id;
                $cat['created_at'] = now();
                $cat['updated_at'] = now();
                $allCategories[] = $cat;
            }
        }

        DB::table('galery_categories')->insert($allCategories);
        $this->command->info("✓ " . count($allCategories) . " Gallery Categories berhasil dibuat!");
    }
}
