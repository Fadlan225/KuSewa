<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\asset_type;
use App\Models\facility_category;
use App\Models\galery_category;

/**
 * Seed default mandatory categories for all asset types.
 *
 * Data ini menggantikan hardcoded logic di asset_type.php:
 *   - getMandatoryFacilityCategories()
 *   - getMandatoryUnitFacilityCategories()
 *   - getMandatoryCategories()
 *   - getMandatoryUnitCategories()
 *
 * Jalankan SETELAH: FacilityCategorySeeder, GaleryCategorySeeder, AssetTypeSeeder.
 * Aman dijalankan berkali-kali (upsert / updateOrInsert).
 */
class AssetTypeMandatorySeeder extends Seeder
{
    public function run(): void
    {
        // ── Helper ──────────────────────────────────────────────────────────
        $facilityCategories = facility_category::pluck('id', 'name');
        $galleryCategories  = galery_category::pluck('id', 'name');
        $assetTypes         = asset_type::pluck('id', 'name');

        $warn = fn(string $msg) => $this->command->warn("  ⚠ $msg");

        // ── Mandatory Facility Categories ────────────────────────────────────
        // Format: [ tipe_aset => [scope => [nama_kategori_fasilitas, ...]], ... ]
        $mandatoryFacility = [
            'Kos'       => [
                'asset' => ['Internet', 'Parkir', 'Keamanan', 'Kamar Mandi', 'Perlengkapan Kamar'],
                'unit'  => ['Perlengkapan Kamar', 'Sirkulasi Udara'],
            ],
            'Hotel'     => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi', 'Perlengkapan Kamar', 'Indoor'],
                'unit'  => ['Perlengkapan Kamar', 'Sirkulasi Udara'],
            ],
            'Apartemen' => [
                'asset' => ['Internet', 'Parkir', 'Keamanan', 'Kamar Mandi', 'Perlengkapan Kamar'],
                'unit'  => ['Perlengkapan Kamar', 'Dapur'],
            ],
            'Villa'     => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi'],
                'unit'  => [],
            ],
            'Homestay'  => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi', 'Perlengkapan Kamar'],
                'unit'  => ['Perlengkapan Kamar', 'Sirkulasi Udara'],
            ],
            'Guest House' => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi', 'Perlengkapan Kamar'],
                'unit'  => ['Perlengkapan Kamar', 'Sirkulasi Udara'],
            ],
            'Resort'    => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi', 'Perlengkapan Kamar'],
                'unit'  => ['Perlengkapan Kamar'],
            ],
            'Kontrakan' => [
                'asset' => ['Internet', 'Parkir', 'Kamar Mandi'],
                'unit'  => [],
            ],
        ];

        DB::table('asset_type_mandatory_categories')->truncate();

        $facilityRows = [];
        foreach ($mandatoryFacility as $typeName => $scopes) {
            $typeId = $assetTypes[$typeName] ?? null;
            if (!$typeId) { $warn("Asset type '$typeName' tidak ditemukan, dilewati."); continue; }

            foreach ($scopes as $scope => $catNames) {
                foreach ($catNames as $catName) {
                    $catId = $facilityCategories[$catName] ?? null;
                    if (!$catId) { $warn("Facility category '$catName' tidak ditemukan, dilewati."); continue; }
                    $facilityRows[] = [
                        'asset_type_id'       => $typeId,
                        'facility_category_id' => $catId,
                        'scope'               => $scope,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }
            }
        }

        if (!empty($facilityRows)) {
            DB::table('asset_type_mandatory_categories')->insert($facilityRows);
        }

        // ── Mandatory Gallery Categories ─────────────────────────────────────
        // Format: [ tipe_aset => [scope => [nama_galeri_kategori, ...]], ... ]
        $mandatoryGallery = [
            'Hotel'      => [
                'asset' => ['Tampak Depan', 'Lobby', 'Kamar', 'Kamar Mandi'],
                'unit'  => ['Kamar Tidur', 'Kamar Mandi'],
            ],
            'Villa'      => [
                'asset' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi', 'Area Outdoor'],
                'unit'  => [],
            ],
            'Apartemen'  => [
                'asset' => ['Tampak Gedung', 'Ruang Unit', 'Kamar', 'Kamar Mandi'],
                'unit'  => ['Kamar Tidur', 'Ruang Unit'],
            ],
            'Homestay'   => [
                'asset' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi'],
                'unit'  => ['Kamar Tidur'],
            ],
            'Guest House' => [
                'asset' => ['Tampak Depan', 'Area Bersama', 'Kamar', 'Kamar Mandi'],
                'unit'  => ['Kamar Tidur'],
            ],
            'Kos'        => [
                'asset' => ['Bangunan Kos', 'Bangunan Kos Dari Jalan', 'Kamar Mandi', 'Fasilitas Bersama'],
                'unit'  => ['Kamar Tidur', 'Depan Kamar'],
            ],
            'Resort'     => [
                'asset' => ['Exterior', 'Lobby', 'Kamar', 'Kamar Mandi'],
                'unit'  => ['Kamar Tidur', 'Kamar Mandi'],
            ],
            'Kontrakan'  => [
                'asset' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi'],
                'unit'  => [],
            ],
            'Ruko'       => [
                'asset' => ['Tampak Depan', 'Area Utama', 'Interior', 'Akses Parkir'],
                'unit'  => [],
            ],
            'Gudang'     => [
                'asset' => ['Tampak Depan', 'Area Gudang', 'Akses Kendaraan', 'Loading Area'],
                'unit'  => [],
            ],
            'Lahan'      => [
                'asset' => ['Keseluruhan Lahan', 'Akses Masuk', 'Lingkungan Sekitar'],
                'unit'  => [],
            ],
            'Gedung'     => [
                'asset' => ['Tampak Depan', 'Ruang Utama', 'Lobby'],
                'unit'  => [],
            ],
            'Aula'       => [
                'asset' => ['Area Utama', 'Area Masuk'],
                'unit'  => [],
            ],
            'Ruang Meeting' => [
                'asset' => ['Ruang Keseluruhan', 'Meja & Kursi', 'Fasilitas Presentasi', 'Area Masuk'],
                'unit'  => [],
            ],
            'Studio'     => [
                'asset' => ['Ruang Keseluruhan', 'Area Utama', 'Peralatan'],
                'unit'  => [],
            ],
            'Baliho'     => [
                'asset' => ['Tampak Baliho', 'Area Sekitar', 'Akses Jalan'],
                'unit'  => [],
            ],
        ];

        DB::table('asset_type_mandatory_gallery_categories')->truncate();

        $galleryRows = [];
        foreach ($mandatoryGallery as $typeName => $scopes) {
            $typeId = $assetTypes[$typeName] ?? null;
            if (!$typeId) { $warn("Asset type '$typeName' tidak ditemukan, dilewati."); continue; }

            foreach ($scopes as $scope => $catNames) {
                foreach ($catNames as $catName) {
                    $catId = $galleryCategories[$catName] ?? null;
                    if (!$catId) { $warn("Gallery category '$catName' tidak ditemukan untuk tipe '$typeName' (scope: $scope), dilewati."); continue; }
                    $galleryRows[] = [
                        'asset_type_id'      => $typeId,
                        'galery_category_id' => $catId,
                        'scope'              => $scope,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }
        }

        if (!empty($galleryRows)) {
            DB::table('asset_type_mandatory_gallery_categories')->insert($galleryRows);
        }

        $this->command->info('✓ Mandatory facility categories: ' . count($facilityRows) . ' rows');
        $this->command->info('✓ Mandatory gallery categories: '  . count($galleryRows)  . ' rows');
    }
}
