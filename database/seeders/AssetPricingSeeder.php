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
            $tiers = $this->getPricingTiers($asset->type_name, $faker);

            if ($asset->allow_units) {
                $units = $unitsByAsset->get($asset->id, collect());
                foreach ($units as $unit) {
                    $tier = $tiers[0];
                    $batch[] = [
                        'asset_id'      => $asset->id,
                        'asset_unit_id' => $unit->id,
                        'duration'      => $tier['duration'],
                        'rental_unit'   => $tier['rental_unit'],
                        'price'         => (int) round($tier['price']),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                    $totalPricings++;
                }
            } else {
                foreach ($tiers as $tier) {
                    $batch[] = [
                        'asset_id'      => $asset->id,
                        'asset_unit_id' => null,
                        'duration'      => $tier['duration'],
                        'rental_unit'   => $tier['rental_unit'],
                        'price'         => (int) round($tier['price']),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                    $totalPricings++;
                }
            }

            if (count($batch) >= 500) {
                DB::table('asset_pricings')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('asset_pricings')->insert($batch);
        }

        $this->command->info("✓ {$totalPricings} Asset Pricings berhasil dibuat!");
    }

    private function getPricingTiers($typeName, $faker): array
    {
        $base = $this->getBasePrice($typeName, $faker);
        
        $rentalUnit = 'month'; // default
        if (in_array($typeName, ['Hotel', 'Villa', 'Apartemen', 'Homestay', 'Guest House', 'Resort'])) {
            $rentalUnit = 'night';
        } elseif (in_array($typeName, ['Gedung', 'Aula'])) {
            $rentalUnit = 'day';
        } elseif (in_array($typeName, ['Ruang Meeting', 'Studio'])) {
            $rentalUnit = 'hour';
        }

        if ($rentalUnit === 'hour') {
            return [
                ['duration' => 1, 'rental_unit' => 'hour', 'price' => $base],
                ['duration' => 3, 'rental_unit' => 'hour', 'price' => $base * 2.7],
                ['duration' => 8, 'rental_unit' => 'hour', 'price' => $base * 6.5],
            ];
        } elseif ($rentalUnit === 'night') {
            return [
                ['duration' => 1, 'rental_unit' => 'night', 'price' => $base],
                ['duration' => 3, 'rental_unit' => 'night', 'price' => $base * 2.7],
                ['duration' => 7, 'rental_unit' => 'night', 'price' => $base * 6],
            ];
        } elseif ($rentalUnit === 'day') {
            return [
                ['duration' => 1, 'rental_unit' => 'day', 'price' => $base],
                ['duration' => 3, 'rental_unit' => 'day', 'price' => $base * 2.7],
                ['duration' => 7, 'rental_unit' => 'day', 'price' => $base * 6],
            ];
        } elseif ($rentalUnit === 'month') {
            return [
                ['duration' => 1,  'rental_unit' => 'month', 'price' => $base],
                ['duration' => 3,  'rental_unit' => 'month', 'price' => $base * 2.7],
                ['duration' => 6,  'rental_unit' => 'month', 'price' => $base * 5],
                ['duration' => 12, 'rental_unit' => 'month', 'price' => $base * 9],
            ];
        }

        return [
            ['duration' => 1, 'rental_unit' => $rentalUnit, 'price' => $base],
        ];
    }

    private function getBasePrice($typeName, $faker): int
    {
        if ($typeName === 'Rumah') {
            return $faker->randomElement([500000, 800000, 1000000, 1500000, 2000000]);
        } elseif ($typeName === 'Villa') {
            return $faker->randomElement([800000, 1200000, 2000000, 3500000, 5000000]);
        } elseif (in_array($typeName, ['Hotel', 'Resort'])) {
            return $faker->randomElement([300000, 500000, 800000, 1200000, 2000000]);
        } elseif ($typeName === 'Kos') {
            return $faker->randomElement([700000, 1000000, 1500000, 2500000]);
        } elseif ($typeName === 'Gudang') {
            return $faker->randomElement([5000000, 10000000, 15000000]);
        } elseif ($typeName === 'Studio') {
            return $faker->randomElement([100000, 150000, 250000, 350000]);
        } elseif (str_contains($typeName, 'Baliho')) {
            return $faker->randomElement([3000000, 5000000, 8000000, 10000000]);
        } elseif ($typeName === 'Apartemen') {
            return $faker->randomElement([400000, 600000, 900000, 1500000]);
        } elseif (in_array($typeName, ['Homestay', 'Guest House'])) {
            return $faker->randomElement([150000, 250000, 350000, 500000]);
        } elseif (in_array($typeName, ['Ruang Meeting', 'Aula', 'Gedung'])) {
            return $faker->randomElement([200000, 500000, 1000000, 2000000]);
        } else {
            return $faker->randomElement([1000000, 2000000, 3000000, 5000000]);
        }
    }
}
