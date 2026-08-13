<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetUnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');
        
        $assets = DB::table('assets')
            ->join('asset_types', 'assets.asset_type_id', '=', 'asset_types.id')
            ->select('assets.id', 'assets.title', 'asset_types.name as type_name', 'asset_types.allow_units')
            ->where('asset_types.allow_units', 1)
            ->get();

        $units = [];
        $totalUnits = 0;

        foreach ($assets as $asset) {
            $unitCount = 0;
            $type = $asset->type_name;

            if ($type == 'Hotel') {
                $unitCount = $faker->numberBetween(10, 30);
                $unitNames = ['Deluxe Room', 'Superior Room', 'Suite Room', 'Standard Room', 'Presidential Suite'];
            } elseif ($type == 'Apartemen') {
                $unitCount = $faker->numberBetween(5, 15);
                $unitNames = ['Studio Type', '1BR Unit', '2BR Unit', 'Penthouse'];
            } elseif ($type == 'Kos') {
                $unitCount = $faker->numberBetween(15, 40);
                $unitNames = ['Kamar Standar', 'Kamar AC', 'Kamar Ekstra Luas', 'Kamar VIP'];
            } elseif ($type == 'Studio') {
                $unitCount = $faker->numberBetween(2, 6);
                $unitNames = ['Studio A', 'Studio B', 'Studio C', 'VIP Studio'];
            } elseif (in_array($type, ['Homestay', 'Guest House'])) {
                $unitCount = $faker->numberBetween(3, 10);
                $unitNames = ['Room 1', 'Room 2', 'Room 3', 'Family Room', 'Deluxe Room'];
            } elseif ($type == 'Kios') {
                $unitCount = $faker->numberBetween(3, 10);
                $unitNames = ['Kios Depan', 'Kios Dalam', 'Kios Sudut'];
            } elseif ($type == 'Resort') {
                $unitCount = $faker->numberBetween(5, 20);
                $unitNames = ['Villa A', 'Villa B', 'Ocean View Room', 'Garden View Room'];
            } else {
                $unitCount = $faker->numberBetween(2, 5);
                $unitNames = ['Unit A', 'Unit B', 'Unit C'];
            }

            for ($i = 0; $i < $unitCount; $i++) {
                $unitName = $faker->randomElement($unitNames) . ' ' . $faker->numberBetween(1, 100);
                
                $units[] = [
                    'asset_id' => $asset->id,
                    'name' => $unitName,
                    'description' => $faker->paragraph(2),
                    'detail' => json_encode($this->generateUnitDetail($type, $faker)),
                    'quantity' => $faker->numberBetween(1, 5),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalUnits++;
            }
        }

        $chunks = array_chunk($units, 500);
        foreach ($chunks as $chunk) {
            DB::table('asset_units')->insert($chunk);
        }
        
        $this->command->info("✓ {$totalUnits} Asset Units berhasil dibuat!");
    }

    private function generateUnitDetail($typeName, $faker)
    {
        $detail = [];
        if (in_array($typeName, ['Hotel', 'Resort', 'Homestay', 'Guest House'])) {
            $detail['bed_type'] = $faker->randomElement(['Single Bed', 'Twin Bed', 'Queen Bed', 'King Bed']);
            $detail['max_guest'] = $faker->numberBetween(1, 4) . ' Orang';
            $detail['bathroom'] = 'Inside';
            $detail['smoking'] = $faker->boolean;
        } elseif ($typeName == 'Apartemen') {
            $detail['bedroom'] = $faker->numberBetween(1, 3);
            $detail['bathroom'] = $faker->numberBetween(1, 2);
            $detail['balcony'] = $faker->boolean;
            $detail['furnished'] = $faker->boolean;
        } elseif ($typeName == 'Kos') {
            $detail['ac'] = $faker->boolean;
            $detail['wifi'] = true;
            $detail['bathroom_inside'] = $faker->boolean;
        } elseif ($typeName == 'Studio') {
            $detail['luas_ruangan'] = $faker->numberBetween(15, 40) . ' m2';
            $detail['soundproof'] = true;
            $detail['drum_included'] = $faker->boolean;
        } elseif ($typeName == 'Kios') {
            $detail['luas_kios'] = $faker->numberBetween(4, 12) . ' m2';
            $detail['rolling_door'] = true;
        } else {
            $detail['info'] = 'Fasilitas standar';
        }
        return $detail;
    }
}
