<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\asset;
use App\Models\asset_type;
use App\Models\facility;

class AssetFacilitySeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_facilities')->truncate();
        DB::table('asset_unit_facilities')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Map: type_name → nama fasilitas yang diassign ke setiap aset tipe itu
        // (subset dari yang diperbolehkan di asset_type_facilities scope=asset)
        $facilityMapByType = [
            'Rumah'       => ['WiFi', 'AC', 'TV', 'Kulkas', 'Mesin Cuci', 'Parkir Motor', 'Parkir Mobil', 'CCTV'],
            'Villa'       => ['WiFi', 'AC', 'Kolam Renang', 'TV', 'Dapur Bersama', 'Parkir Mobil', 'BBQ Area', 'Taman', 'CCTV'],
            'Apartemen'   => ['WiFi', 'Lift', 'Kolam Renang', 'Gym', 'Parkir Mobil', 'CCTV', 'Satpam 24 Jam'],
            'Kos'         => ['WiFi', 'Parkir Motor', 'CCTV', 'Dapur Bersama', 'Dispenser Air'],
            'Hotel'       => ['WiFi', 'Lift', 'Kolam Renang', 'Restoran', 'Parkir Mobil', 'CCTV', 'Satpam 24 Jam', 'Resepsionis 24 Jam', 'Sarapan Gratis'],
            'Resort'      => ['WiFi', 'Kolam Renang', 'Gym', 'Restoran', 'BBQ Area', 'Taman', 'Parkir Mobil', 'CCTV'],
            'Homestay'    => ['WiFi', 'Dapur Bersama', 'Parkir Motor', 'Parkir Mobil'],
            'Guest House' => ['WiFi', 'Dapur Bersama', 'Parkir Motor', 'Parkir Mobil', 'CCTV'],
            'Kontrakan'   => ['WiFi', 'Parkir Motor', 'Air PDAM'],
            'Ruko'        => ['WiFi', 'AC', 'Parkir Motor', 'Parkir Mobil', 'CCTV'],
            'Toko'        => ['AC', 'Parkir Motor', 'CCTV'],
            'Kios'        => ['Parkir Motor', 'CCTV'],
            'Gudang'      => ['CCTV', 'Satpam 24 Jam', 'Parkir Mobil', 'Loading Dock', 'Alat Pemadam Api'],
            'Gedung'      => ['WiFi', 'AC', 'Lift', 'Parkir Mobil', 'CCTV', 'Satpam 24 Jam', 'Ruang Meeting', 'Sound System'],
            'Aula'        => ['WiFi', 'AC', 'Parkir Mobil', 'Sound System', 'Proyektor'],
            'Ruang Meeting'=> ['WiFi', 'AC', 'Proyektor', 'Whiteboard', 'Sound System', 'TV', 'Printer'],
            'Studio'      => ['WiFi', 'AC', 'Parkir Motor', 'CCTV'],
            'Lahan Kosong'=> ['Pagar', 'CCTV'],
            'Lahan Parkir'=> ['CCTV', 'Satpam 24 Jam', 'Pagar'],
        ];

        // Fasilitas level unit
        $unitFacilityMapByType = [
            'Hotel'     => ['AC', 'TV', 'Hair Dryer', 'Brankas', 'Water Heater'],
            'Resort'    => ['AC', 'TV', 'Hair Dryer', 'Bathtub'],
            'Apartemen' => ['AC', 'TV', 'Kulkas', 'Kompor', 'Water Heater'],
            'Kos'       => ['AC', 'Kamar Mandi Dalam', 'Lemari Pakaian', 'Meja Kerja'],
            'Studio'    => ['Drum', 'Gitar Listrik', 'Bass', 'Keyboard / Piano', 'Mixer', 'Soundproof Room'],
            'Kios'      => ['AC'],
        ];

        // Ambil semua fasilitas sekali
        $allFacilities = facility::pluck('id', 'name');

        // Ambil semua asset beserta type name
        $assets = asset::with('type')->get();

        $assetRows     = [];
        $unitFacRows   = [];
        $now           = now();

        foreach ($assets as $assetModel) {
            $typeName = $assetModel->type?->name;
            if (!$typeName || !isset($facilityMapByType[$typeName])) continue;

            foreach ($facilityMapByType[$typeName] as $facName) {
                $facId = $allFacilities[$facName] ?? null;
                if (!$facId) continue;

                $assetRows[] = [
                    'asset_id'    => $assetModel->id,
                    'facility_id' => $facId,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            // Unit facilities
            if (isset($unitFacilityMapByType[$typeName])) {
                $units = DB::table('asset_units')->where('asset_id', $assetModel->id)->pluck('id');
                foreach ($units as $unitId) {
                    foreach ($unitFacilityMapByType[$typeName] as $facName) {
                        $facId = $allFacilities[$facName] ?? null;
                        if (!$facId) continue;

                        $unitFacRows[] = [
                            'asset_unit_id' => $unitId,
                            'facility_id'   => $facId,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }
                }
            }
        }

        // Insert in chunks untuk performa
        foreach (array_chunk($assetRows, 200) as $chunk) {
            DB::table('asset_facilities')->insertOrIgnore($chunk);
        }
        foreach (array_chunk($unitFacRows, 200) as $chunk) {
            DB::table('asset_unit_facilities')->insertOrIgnore($chunk);
        }

        $this->command->info('✓ ' . count($assetRows) . ' asset facilities berhasil dibuat!');
        $this->command->info('✓ ' . count($unitFacRows) . ' unit facilities berhasil dibuat!');
    }
}
