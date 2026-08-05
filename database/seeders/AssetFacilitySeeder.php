<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetFacilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_facilities')->truncate();
        DB::table('asset_unit_facilities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');

        $assets = DB::table('assets')->select('id', 'asset_type_id')->get();
        $assetFacilitiesData = [];
        $assetUnitFacilitiesData = [];

        $typeFacilitiesAsset = DB::table('asset_type_facilities')
            ->where('scope', 'asset')
            ->get()
            ->groupBy('asset_type_id');

        $typeFacilitiesUnit = DB::table('asset_type_facilities')
            ->where('scope', 'unit')
            ->get()
            ->groupBy('asset_type_id');

        $totalAssetFacilities = 0;
        foreach ($assets as $asset) {
            $allowedFacs = $typeFacilitiesAsset->get($asset->asset_type_id, collect());
            foreach ($allowedFacs as $fac) {
                // Attach random subset of facilities, ~80%
                if ($faker->boolean(80)) {
                    $assetFacilitiesData[] = [
                        'asset_id' => $asset->id,
                        'facility_id' => $fac->facility_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalAssetFacilities++;
                }
            }
        }

        foreach (array_chunk($assetFacilitiesData, 1000) as $chunk) {
            DB::table('asset_facilities')->insert($chunk);
        }

        $units = DB::table('asset_units')
            ->join('assets', 'asset_units.asset_id', '=', 'assets.id')
            ->select('asset_units.id', 'assets.asset_type_id')
            ->get();
            
        $totalUnitFacilities = 0;
        foreach ($units as $unit) {
            $allowedFacs = $typeFacilitiesUnit->get($unit->asset_type_id, collect());
            foreach ($allowedFacs as $fac) {
                if ($faker->boolean(80)) {
                    $assetUnitFacilitiesData[] = [
                        'asset_unit_id' => $unit->id,
                        'facility_id' => $fac->facility_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalUnitFacilities++;
                }
            }
        }

        foreach (array_chunk($assetUnitFacilitiesData, 1000) as $chunk) {
            DB::table('asset_unit_facilities')->insert($chunk);
        }

        $total = $totalAssetFacilities + $totalUnitFacilities;
        $this->command->info("✓ {$total} Asset Facilities berhasil dibuat! ({$totalAssetFacilities} asset, {$totalUnitFacilities} unit)");
    }
}
