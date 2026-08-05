<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');
        $imagePath = public_path('assets');

        $galeryCategories = DB::table('galery_categories')->get();
        if ($galeryCategories->isEmpty()) {
            $this->command->error('Galery categories belum ada. Jalankan GaleryCategorySeeder dulu.');
            return;
        }

        $catsByTypeAndScope = [];
        foreach ($galeryCategories as $cat) {
            $catsByTypeAndScope[$cat->asset_type_id][$cat->applies_to][] = $cat;
        }

        $assets = DB::table('assets')
            ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->select('assets.id', 'assets.asset_type_id', 'asset_types.name as type_name', 'asset_types.allow_units')
            ->get();

        $unitsByAsset = DB::table('asset_units')
            ->select('id', 'asset_id')
            ->get()
            ->groupBy('asset_id');

        $batch = [];
        $totalImages = 0;

        foreach ($assets as $asset) {
            $typeId = $asset->asset_type_id;
            $folder = $asset->type_name;
            $files  = glob($imagePath . '/' . $folder . '/*') ?: [];
            
            $imagesPool = collect($files)->shuffle()->values();
            $poolSize   = $imagesPool->count();
            
            // 1. Gambar untuk ASSET (5-10 gambar per asset)
            $assetCats = $catsByTypeAndScope[$typeId]['asset'] ?? [];
            if (!empty($assetCats)) {
                $numAssetImages = $faker->numberBetween(5, 10);
                for ($i = 0; $i < $numAssetImages; $i++) {
                    $cat = $faker->randomElement($assetCats);
                    $imageStr = 'assets/' . $folder . '/placeholder.jpg';
                    if ($poolSize > 0) {
                        $imageStr = 'assets/' . $folder . '/' . basename($imagesPool[$i % $poolSize]);
                    }
                    
                    $batch[] = [
                        'asset_id' => $asset->id,
                        'asset_unit_id' => null,
                        'gallery_category_id' => $cat->id,
                        'image' => $imageStr,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalImages++;
                }
            }

            // 2. Gambar untuk UNIT (3-6 gambar per unit)
            if ($asset->allow_units) {
                $unitCats = $catsByTypeAndScope[$typeId]['unit'] ?? [];
                $units = $unitsByAsset->get($asset->id, collect());
                
                foreach ($units as $unit) {
                    if (!empty($unitCats)) {
                        $numUnitImages = $faker->numberBetween(3, 6);
                        for ($i = 0; $i < $numUnitImages; $i++) {
                            $cat = $faker->randomElement($unitCats);
                            $imageStr = 'assets/' . $folder . '/placeholder.jpg';
                            if ($poolSize > 0) {
                                // Randomize index for units so they don't look identically sorted
                                $imageStr = 'assets/' . $folder . '/' . basename($imagesPool[rand(0, $poolSize - 1)]);
                            }
                            
                            $batch[] = [
                                'asset_id' => null,
                                'asset_unit_id' => $unit->id,
                                'gallery_category_id' => $cat->id,
                                'image' => $imageStr,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $totalImages++;
                        }
                    }
                }
            }

            if (count($batch) >= 1000) {
                DB::table('asset_images')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('asset_images')->insert($batch);
        }

        $this->command->info("✓ {$totalImages} Asset Images berhasil dibuat!");
    }
}
