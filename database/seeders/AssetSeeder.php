<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('assets')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $ownerProfiles = DB::table('owner_profiles')->orderBy('id')->get();
        if ($ownerProfiles->isEmpty()) {
            $this->command->error('Owner profiles belum ada.');
            return;
        }

        $types = DB::table('asset_types')
            ->join('asset_categories', 'asset_categories.id', '=', 'asset_types.category_id')
            ->select('asset_types.*', 'asset_categories.name as category_name')
            ->orderBy('asset_types.id')
            ->get();

        if ($types->isEmpty()) {
            $this->command->error('Asset types belum ada.');
            return;
        }

        $cities = [
            'Samarinda', 'Balikpapan', 'Bontang', 'Tenggarong', 'Berau',
            'Kutai Kertanegara', 'Penajam', 'Sangatta', 'Jakarta', 'Bandung',
            'Surabaya', 'Yogyakarta', 'Makassar', 'Denpasar', 'Medan',
        ];

        $facilitiesByCategory = [
            'Hunian'      => [['wifi', 'parkir', 'ac', 'dapur'], ['wifi', 'parkir', 'kolam_renang', 'gym'], ['parkir', 'ac', 'mushola'], ['dapur', 'wifi', 'cctv'], ['ac', 'parkir', 'toilet']],
            'Komersial'   => [['parkir', 'toilet', 'cctv'], ['listrik_3phase', 'toilet', 'loading_dock'], ['toilet', 'wifi', 'ac'], ['parkir', 'keamanan_24jam']],
            'Lahan'       => [['pagar', 'listrik'], ['akses_jalan', 'listrik', 'pagar'], ['irigasi', 'akses_jalan']],
            'Event'       => [['ac', 'wifi', 'proyektor', 'sound_system'], ['toilet', 'ac', 'wifi', 'parkir'], ['sound_system', 'lighting', 'panggung'], ['wifi', 'whiteboard', 'ac', 'proyektor']],
            'Media Iklan' => [['listrik', 'penerangan'], ['rangka_besi', 'visibility_tinggi']],
        ];

        $ownerCount  = $ownerProfiles->count();
        $assetsPerType = 10;
        $totalAssets = $types->count() * $assetsPerType; // 22 × 10 = 220
        $batch = [];
        $assetNum = 0;

        $this->command->info("Membuat {$totalAssets} aset (10 per type, {$types->count()} types)...");

        foreach ($types as $typeIdx => $type) {
            $facs = $facilitiesByCategory[$type->category_name] ?? [['parkir', 'toilet']];

            for ($n = 1; $n <= $assetsPerType; $n++) {
                $assetNum++;
                $ownerProfile = $ownerProfiles[$assetNum % $ownerCount];
                $city         = $cities[$assetNum % count($cities)];
                $fac          = $facs[$assetNum % count($facs)];

                $batch[] = [
                    'owner_profile_id' => $ownerProfile->id,
                    'asset_type_id'    => $type->id,
                    'title'            => "{$type->name} {$city} " . ($typeIdx * $assetsPerType + $n),
                    'description'      => "Tersedia {$type->name} untuk disewa di {$city}. Lokasi strategis, kondisi baik, siap huni/pakai. Fasilitas lengkap sesuai kebutuhan.",
                    'detail'           => json_encode([
                        'capacity' => rand(2, 200) . ' orang',
                        'facility' => $fac,
                        'luas'     => rand(20, 500) . ' m²',
                    ]),
                    'province'    => 'Kalimantan Timur',
                    'city'        => $city,
                    'address'     => "Jl. {$type->name} No. " . ($typeIdx * 10 + $n) . ", {$city}",
                    'latitude'    => number_format(-0.5022 + (($assetNum * 7) % 200 - 100) / 1000, 6),
                    'longitude'   => number_format(117.1536 + (($assetNum * 13) % 200 - 100) / 1000, 6),
                    'status'      => 'active',
                    'created_at'  => now()->subDays(rand(30, 365)),
                    'updated_at'  => now(),
                ];
            }
        }

        DB::table('assets')->insert($batch);
        $this->command->info("✓ {$totalAssets} aset berhasil dibuat! (Dibagi ke {$ownerCount} owner)");
    }
}
