<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetPricingSeeder extends Seeder
{
    // Rentang harga realistis per tipe aset (min, max, period)
    private array $pricingMap = [
        'Rumah'               => [1500000, 8000000,  'month'],
        'Villa'               => [500000,  5000000,  'day'],
        'Apartemen'           => [3000000, 15000000, 'month'],
        'Homestay'            => [150000,  500000,   'day'],
        'Guest House'         => [200000,  800000,   'day'],
        'Kos'                 => [500000,  2000000,  'month'],
        'Hotel'               => [300000,  3000000,  'day'],
        'Resort'              => [1500000, 10000000, 'day'],
        'Kontrakan'           => [800000,  5000000,  'month'],
        'Ruko'                => [5000000, 30000000, 'month'],
        'Toko'                => [2000000, 15000000, 'month'],
        'Kios'                => [500000,  5000000,  'month'],
        'Gudang'              => [3000000, 20000000, 'month'],
        'Lahan Kosong'        => [5000000, 50000000, 'year'],
        'Lahan Parkir'        => [1000000, 10000000, 'month'],
        'Lahan Pertanian'     => [2000000, 20000000, 'year'],
        'Gedung'              => [5000000, 50000000, 'day'],
        'Aula'                => [2000000, 15000000, 'day'],
        'Ruang Meeting'       => [500000,  5000000,  'day'],
        'Studio'              => [200000,  2000000,  'hour'],
        'Baliho Digital'      => [3000000, 15000000, 'month'],
        'Baliho Konvensional' => [1000000, 8000000,  'month'],
    ];

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_pricings')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $assets = DB::table('assets')
            ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->select('assets.id', 'asset_types.name as type_name')
            ->orderBy('assets.id')
            ->get();

        if ($assets->isEmpty()) {
            $this->command->error('Assets belum ada.');
            return;
        }

        $batch = [];
        foreach ($assets as $asset) {
            $config = $this->pricingMap[$asset->type_name] ?? [500000, 5000000, 'month'];
            [$min, $max, $period] = $config;

            // Harga acak dalam rentang, dibulatkan ke 50.000
            $price = round(rand($min, $max) / 50000) * 50000;

            $batch[] = [
                'asset_id'   => $asset->id,
                'price'      => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('asset_pricings')->insert($batch);
        $this->command->info('✓ ' . count($batch) . ' pricing berhasil dibuat!');
    }
}
