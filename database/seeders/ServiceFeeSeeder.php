<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\asset_type as AssetType;
use App\Models\service_fee as ServiceFee;
use Illuminate\Support\Facades\DB;

class ServiceFeeSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar bersih dari default global
        DB::table('service_fees')->truncate();

        $assetTypes = AssetType::all();

        foreach ($assetTypes as $type) {
            // Biaya bawaan untuk scope Aset (Rp 5.000)
            ServiceFee::create([
                'asset_type_id' => $type->id,
                'scope'         => 'asset',
                'name'          => 'Biaya Platform Standar',
                'description'   => 'Biaya layanan dasar untuk transaksi sewa ' . $type->name . '.',
                'fee_type'      => 'fixed',
                'fee_value'     => 5000,
                'sort_order'    => 1,
            ]);

            // Jika tipe aset mendukung unit, sediakan juga biaya bawaan untuk scope Unit (Rp 5.000)
            if ($type->allow_units) {
                ServiceFee::create([
                    'asset_type_id' => $type->id,
                    'scope'         => 'unit',
                    'name'          => 'Biaya Platform Unit Standar',
                    'description'   => 'Biaya layanan dasar per unit/kamar untuk ' . $type->name . '.',
                    'fee_type'      => 'fixed',
                    'fee_value'     => 5000,
                    'sort_order'    => 1,
                ]);
            }
        }
    }
}
