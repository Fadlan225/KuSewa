<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AssetImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $imagePath = public_path('assets/images');

        // ── Ambil semua galery_categories dikelompokkan per type_id & applies_to ──
        $galeryCategories = DB::table('galery_categories')->get();

        if ($galeryCategories->isEmpty()) {
            $this->command->error('Galery categories belum ada. Jalankan GaleryCategorySeeder dulu.');
            return;
        }

        // Map: [type_id][applies_to] => Collection of category rows
        $catsByTypeAndScope = [];
        foreach ($galeryCategories as $cat) {
            $catsByTypeAndScope[$cat->asset_type_id][$cat->applies_to][] = $cat;
        }

        // ── Ambil semua asset ──────────────────────────────────────────────────────
        $assets = DB::table('assets')
            ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->select('assets.id', 'assets.asset_type_id', 'asset_types.name as type_name', 'asset_types.allow_units')
            ->get();

        // ── Ambil semua asset units ────────────────────────────────────────────────
        $unitsByAsset = DB::table('asset_units')
            ->select('id', 'asset_id')
            ->get()
            ->groupBy('asset_id');

        $batch       = [];
        $batchLimit  = 500;
        $totalImages = 0;

        foreach ($assets as $asset) {
            $folder = strtolower(str_replace(' ', '_', $asset->type_name));
            $files  = glob($imagePath . '/' . $folder . '/*') ?: [];

            $imagesPool = collect($files)->shuffle()->values();
            $poolSize   = $imagesPool->count();

            if ($poolSize === 0) {
                continue;
            }

            $typeId = $asset->asset_type_id;

            // ── 1. Gambar untuk ASSET utama (applies_to = 'asset') ──
            $assetCats = $catsByTypeAndScope[$typeId]['asset'] ?? [];

            if (!empty($assetCats)) {
                foreach ($assetCats as $idx => $cat) {
                    // Ambil 2-3 foto per kategori (dummy)
                    $count = rand(2, 3);
                    for ($i = 0; $i < $count; $i++) {
                        $file = $imagesPool[($idx * $count + $i) % $poolSize];
                        $batch[] = [
                            'asset_id'           => $asset->id,
                            'asset_unit_id'      => null,
                            'gallery_category_id' => $cat->id,
                            'image'              => 'assets/images/' . $folder . '/' . basename($file),
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ];
                        $totalImages++;
                    }
                }
            } else {
                // Fallback: type ini tidak punya kategori 'asset', pakai default semua foto
                foreach ($imagesPool->take(6) as $idx => $file) {
                    $fallbackCat = collect($catsByTypeAndScope[$typeId]['unit'] ?? [])->first();
                    if (!$fallbackCat) continue;

                    $batch[] = [
                        'asset_id'           => $asset->id,
                        'asset_unit_id'      => null,
                        'gallery_category_id' => $fallbackCat->id,
                        'image'              => 'assets/images/' . $folder . '/' . basename($file),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                    $totalImages++;
                }
            }

            // ── 2. Gambar untuk UNIT (applies_to = 'unit') ──
            if ($asset->allow_units) {
                $unitCats = $catsByTypeAndScope[$typeId]['unit'] ?? [];
                $units    = $unitsByAsset->get($asset->id, collect());

                foreach ($units as $unit) {
                    foreach ($unitCats as $idx => $cat) {
                        // 1-2 foto per kategori unit
                        $count = rand(1, 2);
                        for ($i = 0; $i < $count; $i++) {
                            $file = $imagesPool[($idx * $count + $i) % $poolSize];
                            $batch[] = [
                                'asset_id'           => null,
                                'asset_unit_id'      => $unit->id,
                                'gallery_category_id' => $cat->id,
                                'image'              => 'assets/images/' . $folder . '/' . basename($file),
                                'created_at'         => now(),
                                'updated_at'         => now(),
                            ];
                            $totalImages++;
                        }
                    }
                }
            }

            // Flush batch tiap 500 rows agar tidak habis memory
            if (count($batch) >= $batchLimit) {
                DB::table('asset_images')->insert($batch);
                $batch = [];
            }
        }

        // Flush sisa
        if (!empty($batch)) {
            DB::table('asset_images')->insert($batch);
        }

        $this->command->info("✓ {$totalImages} gambar aset berhasil dibuat dengan kategori galeri!");
    }
}
