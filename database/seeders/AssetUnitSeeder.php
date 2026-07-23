<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\asset;
use App\Models\asset_units;
use App\Models\asset_type;

class AssetUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $typesWithUnits = [
            3 => [ // Apartemen
                [
                    'name' => 'Studio Type',
                    'detail' => ['luas' => '30m2', 'kamar_mandi' => 1, 'bed' => 'Queen Size', 'fasilitas' => ['AC', 'Water Heater', 'Dapur Minimalis']],
                    'quantity_range' => [5, 10],
                ],
                [
                    'name' => '2 Bedroom Type',
                    'detail' => ['luas' => '50m2', 'kamar_mandi' => 1, 'bed' => '1 Queen Size, 1 Single Size', 'fasilitas' => ['AC', 'Water Heater', 'Dapur', 'Ruang Tamu']],
                    'quantity_range' => [2, 5],
                ],
                [
                    'name' => 'Penthouse',
                    'detail' => ['luas' => '120m2', 'kamar_mandi' => 2, 'bed' => '2 King Size', 'fasilitas' => ['AC', 'Private Lift', 'Bathtub', 'Balcony']],
                    'quantity_range' => [1, 2],
                ]
            ],
            4 => [ // Homestay
                [
                    'name' => 'Standard Room',
                    'detail' => ['kapasitas' => '2 Orang', 'bed' => 'Queen Size', 'kamar_mandi' => 'Dalam', 'fasilitas' => ['AC', 'TV', 'Wifi']],
                    'quantity_range' => [3, 6],
                ],
                [
                    'name' => 'Family Room',
                    'detail' => ['kapasitas' => '4 Orang', 'bed' => '2 Queen Size', 'kamar_mandi' => 'Dalam', 'fasilitas' => ['AC', 'TV', 'Wifi', 'Kulkas']],
                    'quantity_range' => [1, 3],
                ]
            ],
            5 => [ // Guest House
                [
                    'name' => 'Single Room',
                    'detail' => ['kapasitas' => '1 Orang', 'bed' => 'Single Bed', 'fasilitas' => ['Kipas Angin', 'Meja Belajar']],
                    'quantity_range' => [2, 5],
                ],
                [
                    'name' => 'Double Room',
                    'detail' => ['kapasitas' => '2 Orang', 'bed' => 'Double Bed', 'fasilitas' => ['AC', 'TV', 'Kamar Mandi Dalam']],
                    'quantity_range' => [2, 5],
                ]
            ],
            6 => [ // Kos
                [
                    'name' => 'Kamar Non-AC',
                    'detail' => ['luas' => '3x3', 'kamar_mandi' => 'Luar', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin']],
                    'quantity_range' => [5, 10],
                ],
                [
                    'name' => 'Kamar AC',
                    'detail' => ['luas' => '3x4', 'kamar_mandi' => 'Dalam', 'fasilitas' => ['Kasur', 'Lemari', 'Meja', 'AC', 'Wifi']],
                    'quantity_range' => [3, 8],
                ]
            ],
            7 => [ // Hotel
                [
                    'name' => 'Standard Room',
                    'detail' => ['bed' => 'Twin/Queen Size', 'luas' => '24m2', 'view' => 'City View', 'fasilitas' => ['AC', 'TV', 'Wifi', 'Kopi/Teh Maker']],
                    'quantity_range' => [10, 20],
                ],
                [
                    'name' => 'Deluxe Room',
                    'detail' => ['bed' => 'King Size', 'luas' => '32m2', 'view' => 'City/Pool View', 'fasilitas' => ['AC', 'Smart TV', 'Kulkas Mini', 'Sofa']],
                    'quantity_range' => [5, 10],
                ],
                [
                    'name' => 'Suite Room',
                    'detail' => ['bed' => 'King Size', 'luas' => '54m2', 'view' => 'Premium View', 'fasilitas' => ['AC', 'Smart TV', 'Living Area', 'Bathtub', 'Minibar']],
                    'quantity_range' => [1, 3],
                ]
            ],
            8 => [ // Resort
                [
                    'name' => 'Garden View Villa',
                    'detail' => ['bed' => 'King Size', 'luas' => '60m2', 'fasilitas' => ['AC', 'Semi-open Bathroom', 'Terrace']],
                    'quantity_range' => [4, 8],
                ],
                [
                    'name' => 'Ocean View Villa',
                    'detail' => ['bed' => 'King Size', 'luas' => '80m2', 'fasilitas' => ['AC', 'Private Pool', 'Sunbed', 'Bathtub']],
                    'quantity_range' => [2, 5],
                ]
            ],
            12 => [ // Kios
                [
                    'name' => 'Kios Ukuran Kecil',
                    'detail' => ['luas' => '2x2 meter', 'daya_listrik' => '900W', 'fasilitas' => ['Rolling Door']],
                    'quantity_range' => [5, 15],
                ],
                [
                    'name' => 'Kios Ukuran Sedang',
                    'detail' => ['luas' => '3x4 meter', 'daya_listrik' => '1300W', 'fasilitas' => ['Rolling Door', 'Kipas Gantung']],
                    'quantity_range' => [2, 5],
                ]
            ],
            20 => [ // Studio
                [
                    'name' => 'Studio Musik Reguler',
                    'detail' => ['luas' => '4x4 meter', 'fasilitas' => ['Drum Set Standard', '2 Gitar', '1 Bass', 'Ampli', 'AC', 'Peredam Suara']],
                    'quantity_range' => [2, 4],
                ],
                [
                    'name' => 'Studio Musik VIP',
                    'detail' => ['luas' => '5x6 meter', 'fasilitas' => ['Drum Set Premium', 'Gitar Premium', 'Keyboard', 'AC', 'Peredam Extra']],
                    'quantity_range' => [1, 2],
                ],
                [
                    'name' => 'Studio Foto',
                    'detail' => ['luas' => '6x8 meter', 'fasilitas' => ['Lighting Set', 'Background Putih/Hitam/Hijau', 'AC', 'Ruang Ganti']],
                    'quantity_range' => [1, 2],
                ]
            ],
        ];

        // Ambil ID tipe aset yang mengizinkan unit (untuk konfirmasi tambahan)
        $allowedTypeIds = asset_type::where('allow_units', true)->pluck('id')->toArray();

        // Ambil semua aset yang tipenya ada dalam daftar $typesWithUnits
        $assets = asset::whereIn('asset_type_id', array_keys($typesWithUnits))
            ->whereIn('asset_type_id', $allowedTypeIds)
            ->get();

        foreach ($assets as $asset) {
            $typeId = $asset->asset_type_id;
            
            if (isset($typesWithUnits[$typeId])) {
                $unitConfigs = $typesWithUnits[$typeId];
                
                foreach ($unitConfigs as $config) {
                    $qty = rand($config['quantity_range'][0], $config['quantity_range'][1]);
                    
                    asset_units::create([
                        'asset_id' => $asset->id,
                        'name' => $config['name'],
                        'detail' => $config['detail'],
                        'quantity' => $qty,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
