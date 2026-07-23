<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApartemenImageSeeder extends Seeder
{
    public function run(): void
    {
        $typeName = 'Apartemen';
        $folder   = 'apartemen';

        // ── Ambil type ─────────────────────────────────────────────────────────
        $typeId = DB::table('asset_types')->where('name', $typeName)->value('id');
        if (!$typeId) {
            $this->command->error("Tipe '{$typeName}' tidak ditemukan.");
            return;
        }

        // ── Ambil galery_categories untuk Apartemen ────────────────────────────
        $categories = DB::table('galery_categories')
            ->where('asset_type_id', $typeId)
            ->get();

        if ($categories->isEmpty()) {
            $this->command->error('Galery categories untuk Apartemen tidak ditemukan. Jalankan GaleryCategorySeeder dulu.');
            return;
        }

        $assetCats = $categories->where('applies_to', 'asset')->values();
        $unitCats  = $categories->where('applies_to', 'unit')->values();

        // ── Scan file gambar ───────────────────────────────────────────────────
        $imagePath = public_path("assets/{$folder}");
        $files     = collect(glob($imagePath . '/*'))->filter()->values();

        if ($files->isEmpty()) {
            $this->command->error("Tidak ada file gambar di public/assets/{$folder}/");
            return;
        }

        $this->command->info("Ditemukan {$files->count()} file gambar di {$folder}/");

        // ── Ambil semua aset bertipe Apartemen ────────────────────────────────
        $assets = DB::table('assets')
            ->where('asset_type_id', $typeId)
            ->select('id')
            ->get();

        // ── Ambil units untuk aset Apartemen ─────────────────────────────────
        $assetIds   = $assets->pluck('id');
        $unitsByAsset = DB::table('asset_units')
            ->whereIn('asset_id', $assetIds)
            ->select('id', 'asset_id')
            ->get()
            ->groupBy('asset_id');

        // ── Hapus gambar lama untuk tipe ini dulu ─────────────────────────────
        $categoryIds = $categories->pluck('id');

        DB::table('asset_images')
            ->whereIn('gallery_category_id', $categoryIds)
            ->whereIn('asset_id', $assetIds)
            ->delete();

        // Hapus juga gambar unit yang terkait
        $allUnitIds = $unitsByAsset->flatten()->pluck('id');
        if ($allUnitIds->isNotEmpty()) {
            DB::table('asset_images')
                ->whereIn('gallery_category_id', $categoryIds)
                ->whereIn('asset_unit_id', $allUnitIds)
                ->delete();
        }

        $fileCount = $files->count();
        $batch     = [];
        $total     = 0;

        // ── Utility: ambil file secara round-robin ────────────────────────────
        $getFile = function (int $idx) use ($files, $fileCount, $folder) {
            $file = $files[$idx % $fileCount];
            return 'assets/' . $folder . '/' . basename($file);
        };

        $imgIdx = 0; // global rotating index

        foreach ($assets as $asset) {

            // ── Gambar untuk ASSET (applies_to = 'asset') ──
            foreach ($assetCats as $catIdx => $cat) {
                $count = rand(2, 3); // 2–3 foto per kategori
                for ($i = 0; $i < $count; $i++) {
                    $batch[] = [
                        'asset_id'            => $asset->id,
                        'asset_unit_id'       => null,
                        'gallery_category_id' => $cat->id,
                        'image'               => $getFile($imgIdx++),
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                    $total++;
                }
            }

            // ── Gambar untuk tiap UNIT (applies_to = 'unit') ──
            $units = $unitsByAsset->get($asset->id, collect());
            foreach ($units as $unit) {
                foreach ($unitCats as $catIdx => $cat) {
                    $count = rand(1, 2); // 1–2 foto per kategori unit
                    for ($i = 0; $i < $count; $i++) {
                        $batch[] = [
                            'asset_id'            => null,
                            'asset_unit_id'       => $unit->id,
                            'gallery_category_id' => $cat->id,
                            'image'               => $getFile($imgIdx++),
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ];
                        $total++;
                    }
                }
            }

            // Flush setiap 300 rows
            if (count($batch) >= 300) {
                DB::table('asset_images')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('asset_images')->insert($batch);
        }

        $this->command->info("✓ {$total} gambar berhasil dimasukkan untuk {$assets->count()} aset Apartemen!");
        $this->command->info("  └ Asset categories: {$assetCats->count()} | Unit categories: {$unitCats->count()}");
    }
}
