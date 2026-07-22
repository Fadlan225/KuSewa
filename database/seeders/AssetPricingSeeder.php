<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetPricingSeeder extends Seeder
{
    public function run(): void
    {
        $assets = DB::table('assets')->get();

        foreach ($assets as $asset) {
            DB::table('asset_pricings')->insert([
                'asset_id'      => $asset->id,
                'asset_unit_id' => null,
                'price'         => fake()->randomElement([100000, 150000, 250000, 500000]),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}


