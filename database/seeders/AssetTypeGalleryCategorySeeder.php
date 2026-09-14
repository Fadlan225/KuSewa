<?php

namespace Database\Seeders;

use App\Models\asset_type;
use App\Models\galery_category;
use App\Services\DefaultGalleryCategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder default kategori galeri per tipe aset.
 *
 * IDEMPOTENT: Aman dijalankan berkali-kali.
 * - Menambah galery_categories yang belum ada (tidak menghapus yang sudah ada).
 * - Hanya mengisi pivot asset_type_gallery_categories untuk scope yang BELUM punya entri.
 *   → Admin yang sudah mengubah konfigurasi TIDAK akan kehilangan perubahannya.
 *
 * Untuk reset paksa ke default, gunakan fitur "Reset ke Default" di halaman admin
 * atau panggil resetGallery() di TemplateAsetController.
 */
class AssetTypeGalleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Pastikan semua kategori galeri yang dibutuhkan ada di tabel global ──
        $this->command->info('Memeriksa dan menambahkan kategori galeri yang belum ada...');

        $allNames = DefaultGalleryCategoryService::allCategoryNames();
        $now = now();

        foreach ($allNames as $name) {
            galery_category::firstOrCreate(
                ['name' => $name],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // Cache semua kategori untuk efisiensi
        $categoryMap = galery_category::whereIn('name', $allNames)
            ->pluck('id', 'name');

        $sampulId = $categoryMap->get('Sampul Utama');
        $lainnyaId = $categoryMap->get('Lainnya');

        $this->command->info('✓ Kategori galeri global sudah lengkap.');

        // ── 2. Seed konfigurasi default per tipe aset (hanya jika belum ada) ───────
        $this->command->info('Memeriksa konfigurasi galeri per tipe aset...');

        $defaultMap = DefaultGalleryCategoryService::defaultMap();
        $seededCount = 0;
        $skippedCount = 0;

        foreach ($defaultMap as $typeName => $config) {
            $assetType = asset_type::where('name', $typeName)->first();

            if (!$assetType) {
                $this->command->warn("  ⚠ Tipe aset '{$typeName}' tidak ditemukan, dilewati.");
                continue;
            }

            foreach (['asset', 'unit'] as $scope) {
                $categories = $config[$scope] ?? [];

                // Cek apakah scope ini sudah punya entri di pivot
                $existingCount = DB::table('asset_type_gallery_categories')
                    ->where('asset_type_id', $assetType->id)
                    ->where('scope', $scope)
                    ->count();

                if ($existingCount > 0) {
                    $skippedCount++;
                    continue; // Sudah dikonfigurasi, jangan timpa
                }

                if (empty($categories) && $scope === 'unit') {
                    continue; // Tidak ada unit categories untuk tipe ini
                }

                // Build pivot data
                $rows = [];
                $sortOrder = 1;

                // Sampul Utama selalu di urutan pertama
                if ($sampulId) {
                    $rows[] = [
                        'asset_type_id'    => $assetType->id,
                        'galery_category_id' => $sampulId,
                        'scope'            => $scope,
                        'is_mandatory'     => true,
                        'sort_order'       => $sortOrder++,
                        'description'      => null,
                        'min_photos'       => 1,
                        'max_photos'       => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                foreach ($categories as $cat) {
                    $catId = $categoryMap->get($cat['name']);
                    if (!$catId) {
                        $this->command->warn("    ⚠ Kategori '{$cat['name']}' tidak ditemukan di DB.");
                        continue;
                    }

                    $rows[] = [
                        'asset_type_id'    => $assetType->id,
                        'galery_category_id' => $catId,
                        'scope'            => $scope,
                        'is_mandatory'     => $cat['is_mandatory'],
                        'sort_order'       => $sortOrder++,
                        'description'      => null,
                        'min_photos'       => $cat['is_mandatory'] ? 1 : 0,
                        'max_photos'       => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                // Lainnya selalu di urutan terakhir
                if ($lainnyaId) {
                    $rows[] = [
                        'asset_type_id'    => $assetType->id,
                        'galery_category_id' => $lainnyaId,
                        'scope'            => $scope,
                        'is_mandatory'     => false,
                        'sort_order'       => $sortOrder++,
                        'description'      => null,
                        'min_photos'       => 0,
                        'max_photos'       => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                if (!empty($rows)) {
                    DB::table('asset_type_gallery_categories')->insert($rows);
                    $seededCount++;
                    $this->command->line("  ✓ {$typeName} [{$scope}] → " . count($rows) . " kategori.");
                }
            }
        }

        $this->command->info("✓ Selesai. {$seededCount} scope dikonfigurasi, {$skippedCount} dilewati (sudah ada).");
    }
}
