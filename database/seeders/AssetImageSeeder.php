<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('asset_images')->truncate();

        $imagePath = public_path('assets/images');

        $assets = DB::table('assets')
            ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->select(
                'assets.id',
                'asset_types.name as type_name'
            )
            ->get();


        foreach ($assets as $asset) {

            // folder berdasarkan tipe
            $folder = strtolower($asset->type_name);

            $files = glob($imagePath . '/' . $folder . '/*');

            if (empty($files)) {
                continue;
            }


            // misal setiap asset punya 10 foto
            $selectedImages = collect($files)
                ->shuffle()
                ->take(10);


            foreach ($selectedImages as $file) {

                DB::table('asset_images')->insert([
                    'asset_id' => $asset->id,

                    'image' =>
                        'assets/images/'
                        . $folder
                        . '/'
                        . basename($file),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
