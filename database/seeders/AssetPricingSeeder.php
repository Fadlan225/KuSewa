<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetPricingSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_pricings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');

        $assets = DB::table('assets')
            ->join('asset_types', 'assets.asset_type_id', '=', 'asset_types.id')
            ->select('assets.id', 'assets.asset_type_id', 'asset_types.name as type_name', 'asset_types.allow_units')
            ->get();

        $unitsByAsset = DB::table('asset_units')
            ->select('id', 'asset_id')
            ->get()
            ->groupBy('asset_id');

        $batch = [];
        $totalPricings = 0;

        foreach ($assets as $asset) {
            $type = $asset->type_name;

            if ($asset->allow_units) {
                // Pricing for units
                $units = $unitsByAsset->get($asset->id, collect());
                foreach ($units as $unit) {
                    $price = $this->generatePrice($type, $faker);
                    $batch[] = [
                        'asset_id' => $asset->id,
                        'asset_unit_id' => $unit->id,
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalPricings++;
                }
            } else {
                // Pricing for asset
                $price = $this->generatePrice($type, $faker);
                $batch[] = [
                    'asset_id' => $asset->id,
                    'asset_unit_id' => null,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalPricings++;
            }

            if (count($batch) >= 1000) {
                DB::table('asset_pricings')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('asset_pricings')->insert($batch);
        }

        $this->command->info("✓ {$totalPricings} Asset Pricings berhasil dibuat!");
    }

    private function generatePrice($typeName, $faker)
    {
        if ($typeName == 'Rumah') {
            return $faker->randomElement([500000, 800000, 1000000, 1500000, 2000000, 2500000]);
        } elseif ($typeName == 'Villa') {
            return $faker->randomElement([800000, 1200000, 2000000, 3500000, 5000000]);
        } elseif ($typeName == 'Hotel' || $typeName == 'Resort') {
            return $faker->randomElement([300000, 500000, 800000, 1200000, 2000000]);
        } elseif ($typeName == 'Kos') {
            return $faker->randomElement([700000, 1000000, 1500000, 2500000, 3500000]);
        } elseif ($typeName == 'Gudang') {
            return $faker->randomElement([5000000, 10000000, 15000000, 20000000, 25000000]);
        } elseif ($typeName == 'Studio') {
            return $faker->randomElement([100000, 150000, 250000, 350000, 500000]);
        } elseif (strpos($typeName, 'Baliho') !== false) {
            return $faker->randomElement([8000000, 15000000, 25000000, 35000000, 50000000]);
        } elseif ($typeName == 'Apartemen') {
            return $faker->randomElement([400000, 600000, 900000, 1500000, 2500000]);
        } elseif (in_array($typeName, ['Homestay', 'Guest House'])) {
            return $faker->randomElement([150000, 250000, 350000, 500000, 750000]);
        } else {
            return $faker->randomElement([1000000, 2000000, 3000000, 5000000]);
        }
    }
}
