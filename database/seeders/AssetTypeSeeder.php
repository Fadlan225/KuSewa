<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori berdasarkan nama
        $categories = DB::table('asset_categories')->pluck('id', 'name');

        if ($categories->isEmpty()) {
            $this->command->error('Kategori tidak ditemukan. Jalankan AssetCategorySeeder dulu.');
            return;
        }



        // Common Fields
        $f_floor = ['key' => 'floor', 'label' => 'Jumlah Lantai', 'type' => 'number', 'required' => false];
        $f_building_area = ['key' => 'building_area', 'label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => false];
        $f_land_area = ['key' => 'land_area', 'label' => 'Luas Tanah (m²)', 'type' => 'number', 'required' => false];
        $f_year_built = ['key' => 'year_built', 'label' => 'Tahun Dibangun', 'type' => 'number', 'required' => false];
        $f_parking = ['key' => 'parking', 'label' => 'Kapasitas Parkir', 'type' => 'text', 'required' => false];
        $f_capacity = ['key' => 'capacity', 'label' => 'Kapasitas', 'type' => 'number', 'required' => false];
        $f_electricity = ['key' => 'electricity', 'label' => 'Daya Listrik (VA)', 'type' => 'select', 'required' => false, 'options' => ['900', '1300', '2200', '3500', '4400', '5500', '11000']];
        $f_ceiling = ['key' => 'ceiling_height', 'label' => 'Tinggi Plafon (m)', 'type' => 'number', 'required' => false];

        // Unit Common Fields
        $uf_room_size = ['key' => 'room_size', 'label' => 'Ukuran Unit (m²)', 'type' => 'number', 'required' => true];
        $uf_bed_type = ['key' => 'bed_type', 'label' => 'Tipe Kasur', 'type' => 'select', 'required' => false, 'options' => ['Single', 'Double', 'Queen', 'King', 'Twin']];
        $uf_bathroom = ['key' => 'bathroom', 'label' => 'Kamar Mandi', 'type' => 'select', 'required' => false, 'options' => ['Dalam', 'Luar', 'Bersama']];

        $types = [
            // ── Hunian ──────────────────────────────────────────────────
            [
                'category' => 'Hunian',
                'name' => 'Hotel',
                'allow_units' => true,
                'detail_fields' => [
                    ['key' => 'stars', 'label' => 'Bintang Hotel', 'type' => 'select', 'required' => false, 'options' => ['1', '2', '3', '4', '5']],
                    $f_floor,
                    $f_building_area,
                    $f_land_area,
                    ['key' => 'checkin', 'label' => 'Waktu Check-in', 'type' => 'time', 'required' => false],
                    ['key' => 'checkout', 'label' => 'Waktu Check-out', 'type' => 'time', 'required' => false],
                ],
                'unit_detail_fields' => [$uf_room_size, $uf_bed_type, $uf_bathroom]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Villa',
                'allow_units' => false,
                'detail_fields' => [
                    $f_building_area,
                    $f_land_area,
                    $f_floor,
                    $f_year_built,
                    $f_electricity,
                    ['key' => 'water_source', 'label' => 'Sumber Air', 'type' => 'select', 'required' => false, 'options' => ['PDAM', 'Sumur Bor']],
                    $f_parking,
                    $f_capacity,
                    ['key' => 'view', 'label' => 'Pemandangan', 'type' => 'select', 'required' => false, 'options' => ['Pantai', 'Pegunungan', 'Hutan', 'Kota', 'Danau']]
                ],
                'unit_detail_fields' => []
            ],
            [
                'category' => 'Hunian',
                'name' => 'Apartemen',
                'allow_units' => true,
                'detail_fields' => [$f_floor, $f_building_area, $f_year_built, $f_parking],
                'unit_detail_fields' => [$uf_room_size, $uf_bed_type, $uf_bathroom]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Homestay',
                'allow_units' => true,
                'detail_fields' => [
                    $f_building_area,
                    $f_land_area,
                    $f_floor,
                    $f_year_built,
                    $f_electricity,
                    ['key' => 'water_source', 'label' => 'Sumber Air', 'type' => 'select', 'required' => false, 'options' => ['PDAM', 'Sumur Bor']],
                    $f_parking,
                    $f_capacity,
                ],
                'unit_detail_fields' => [$uf_room_size, $uf_bed_type, $uf_bathroom]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Guest House',
                'allow_units' => true,
                'detail_fields' => [
                    $f_building_area,
                    $f_land_area,
                    $f_floor,
                    $f_year_built,
                    $f_electricity,
                    ['key' => 'water_source', 'label' => 'Sumber Air', 'type' => 'select', 'required' => false, 'options' => ['PDAM', 'Sumur Bor']],
                    $f_parking,
                    $f_capacity,
                ],
                'unit_detail_fields' => [$uf_room_size, $uf_bed_type, $uf_bathroom]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Kos',
                'allow_units' => true,
                'detail_fields' => [
                    ['key' => 'tipe_penyewa', 'label' => 'nkan', 'type' => 'radio', 'required' => true, 'options' => ['Putra', 'Putri', 'Campur']],
                    ['key' => 'aturan_jam_malam', 'label' => 'Aturan Jam Malam', 'type' => 'radio', 'required' => true, 'options' => ['Ya', 'Tidak']],
                ],
                'unit_detail_fields' => [
                    ['key' => 'ukuran_kamar', 'label' => 'Ukuran Kamar', 'type' => 'room_size', 'required' => true]
                ]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Resort',
                'allow_units' => true,
                'detail_fields' => [
                    ['key' => 'stars', 'label' => 'Bintang Resort', 'type' => 'select', 'required' => false, 'options' => ['1', '2', '3', '4', '5']],
                    $f_floor,
                    $f_building_area,
                    $f_land_area,
                    ['key' => 'checkin', 'label' => 'Waktu Check-in', 'type' => 'time', 'required' => false],
                    ['key' => 'checkout', 'label' => 'Waktu Check-out', 'type' => 'time', 'required' => false],
                ],
                'unit_detail_fields' => [$uf_room_size, $uf_bed_type, $uf_bathroom]
            ],
            [
                'category' => 'Hunian',
                'name' => 'Kontrakan',
                'allow_units' => false,
                'detail_fields' => [
                    $f_building_area,
                    $f_land_area,
                    $f_floor,
                    $f_year_built,
                    $f_electricity,
                    ['key' => 'water_source', 'label' => 'Sumber Air', 'type' => 'select', 'required' => false, 'options' => ['PDAM', 'Sumur Bor']],
                    $f_parking,
                    $f_capacity,
                ],
                'unit_detail_fields' => []
            ],

            // ── Komersial ───────────────────────────────────────────────
            [
                'category' => 'Komersial',
                'name' => 'Ruko',
                'allow_units' => false,
                'detail_fields' => [
                    $f_building_area,
                    $f_floor,
                    $f_electricity,
                    ['key' => 'bathroom', 'label' => 'Kamar Mandi Dalam', 'type' => 'radio', 'required' => false, 'options' => ['Ya', 'Tidak']]
                ],
                'unit_detail_fields' => []
            ],
            [
                'category' => 'Komersial',
                'name' => 'Gudang',
                'allow_units' => false,
                'detail_fields' => [$f_land_area, $f_building_area, $f_ceiling, $f_year_built],
                'unit_detail_fields' => []
            ],

            // ── Lahan ───────────────────────────────────────────────────
            [
                'category' => 'Lahan',
                'name' => 'Lahan',
                'allow_units' => false,
                'detail_fields' => [
                    ['key' => 'land_area', 'label' => 'Luas Tanah (m²)', 'type' => 'number', 'required' => true],
                    ['key' => 'certificate', 'label' => 'Sertifikat Kepemilikan', 'type' => 'select', 'required' => false, 'options' => ['SHM', 'HGB', 'AJB', 'Girik', 'Lainnya']],
                    ['key' => 'terrain', 'label' => 'Kontur Tanah', 'type' => 'select', 'required' => false, 'options' => ['Datar', 'Miring', 'Berbukit']]
                ],
                'unit_detail_fields' => []
            ],

            // ── Event ───────────────────────────────────────────────────
            [
                'category' => 'Event',
                'name' => 'Gedung',
                'allow_units' => false,
                'detail_fields' => [
                    $f_building_area,
                    $f_floor,
                    $f_electricity,
                    ['key' => 'bathroom', 'label' => 'Kamar Mandi Dalam', 'type' => 'radio', 'required' => false, 'options' => ['Ya', 'Tidak']],
                    $f_capacity,
                    $f_ceiling
                ],
                'unit_detail_fields' => []
            ],
            [
                'category' => 'Event',
                'name' => 'Aula',
                'allow_units' => false,
                'detail_fields' => [$f_capacity, ['key' => 'building_area', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => false], ['key' => 'floor', 'label' => 'Berada di Lantai', 'type' => 'number', 'required' => false]],
                'unit_detail_fields' => []
            ],
            [
                'category' => 'Event',
                'name' => 'Ruang Meeting',
                'allow_units' => false,
                'detail_fields' => [$f_capacity, ['key' => 'building_area', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => false], ['key' => 'floor', 'label' => 'Berada di Lantai', 'type' => 'number', 'required' => false]],
                'unit_detail_fields' => []
            ],
            [
                'category' => 'Event',
                'name' => 'Studio',
                'allow_units' => true,
                'detail_fields' => [$f_capacity, ['key' => 'building_area', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => false], ['key' => 'floor', 'label' => 'Berada di Lantai', 'type' => 'number', 'required' => false]],
                'unit_detail_fields' => [
                    ['key' => 'room_size', 'label' => 'Ukuran Studio (m²)', 'type' => 'number', 'required' => false]
                ]
            ],

            // ── Media Iklan ─────────────────────────────────────────────
            [
                'category' => 'Media Iklan',
                'name' => 'Baliho',
                'allow_units' => false,
                'detail_fields' => [
                    ['key' => 'display_type', 'label' => 'Jenis Tampilan', 'type' => 'select', 'required' => true, 'options' => ['Konvensional', 'Elektronik']],
                    ['key' => 'dimension', 'label' => 'Dimensi (m)', 'type' => 'text', 'required' => false],
                    ['key' => 'sides', 'label' => 'Jumlah Sisi Tampil', 'type' => 'select', 'required' => false, 'options' => ['1', '2']],
                    ['key' => 'orientation', 'label' => 'Orientasi', 'type' => 'select', 'required' => false, 'options' => ['Horizontal', 'Vertical']],
                    ['key' => 'lighting', 'label' => 'Penerangan Malam', 'type' => 'checkbox', 'required' => false],
                    ['key' => 'resolution', 'label' => 'Resolusi Layar', 'type' => 'text', 'required' => false],
                ],
                'unit_detail_fields' => []
            ],
        ];

        foreach ($types as $type) {
            DB::table('asset_types')->updateOrInsert(
                [
                    'category_id' => $categories[$type['category']],
                    'name' => $type['name'],
                ],
                [
                    'allow_units' => $type['allow_units'],
                    'detail_fields' => json_encode($type['detail_fields']),
                    'unit_detail_fields' => json_encode($type['unit_detail_fields']),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ ' . count($types) . ' asset types berhasil dibuat!');
    }
}
