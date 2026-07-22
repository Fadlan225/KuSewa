<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    /**
     * 5 favorite per customer × 99 customer = 495 favorites
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('favorites')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $customers = DB::table('users')
            ->where('email', 'like', 'customer%@kusewa.com')
            ->orderBy('id')
            ->get();
        $assets    = DB::table('assets')->where('status', 'active')->pluck('id')->toArray();

        if ($customers->isEmpty() || empty($assets)) {
            $this->command->error('Customers atau assets belum ada.');
            return;
        }

        $assetCount = count($assets);
        $batch = [];
        $favPerCustomer = 5;

        foreach ($customers as $custIdx => $customer) {
            // Pilih 5 aset yang berbeda untuk customer ini (offset agar variatif)
            $usedAssets = [];
            for ($f = 0; $f < $favPerCustomer; $f++) {
                $assetIdx = ($custIdx * $favPerCustomer + $f * 7 + 3) % $assetCount;
                $assetId  = $assets[$assetIdx];

                // Hindari duplikat untuk customer yang sama
                if (in_array($assetId, $usedAssets)) {
                    $assetIdx = ($assetIdx + 11) % $assetCount;
                    $assetId  = $assets[$assetIdx];
                }
                $usedAssets[] = $assetId;

                $batch[] = [
                    'user_id'    => $customer->id,
                    'asset_id'   => $assetId,
                    'created_at' => now()->subDays(rand(1, 180)),
                    'updated_at' => now(),
                ];
            }
        }

        // Hapus duplikat user_id + asset_id sebelum insert
        $unique = [];
        $seen   = [];
        foreach ($batch as $row) {
            $key = $row['user_id'] . '_' . $row['asset_id'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $row;
            }
        }

        foreach (array_chunk($unique, 100) as $chunk) {
            DB::table('favorites')->insert($chunk);
        }

        $this->command->info('✓ ' . count($unique) . ' favorites berhasil dibuat! (5/customer × ' . $customers->count() . ' customers)');
    }
}
