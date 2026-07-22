<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetImageSeeder extends Seeder
{
    // Palet warna per kategori aset (vivid, menarik)
    private array $palette = [
        'Hunian'      => [[52, 152, 219], [41, 128, 185], [142, 68, 173], [39, 174, 96], [22, 160, 133]],
        'Komersial'   => [[231, 76, 60], [192, 57, 43], [230, 126, 34], [243, 156, 18], [211, 84, 0]],
        'Lahan'       => [[39, 174, 96], [46, 204, 113], [22, 160, 133], [26, 188, 156], [44, 62, 80]],
        'Event'       => [[155, 89, 182], [142, 68, 173], [41, 128, 185], [52, 73, 94], [44, 62, 80]],
        'Media Iklan' => [[52, 73, 94], [44, 62, 80], [127, 140, 141], [149, 165, 166], [85, 85, 85]],
    ];

    public function run(): void
    {
        // Pastikan GD tersedia
        if (!extension_loaded('gd')) {
            $this->command->error('PHP GD extension tidak aktif! Aktifkan di php.ini.');
            return;
        }

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asset_images')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Buat direktori jika belum ada
        $publicDir = public_path('assets/images');
        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        // Ambil semua aset beserta tipe dan kategori
        $assets = DB::table('assets')
            ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->join('asset_categories', 'asset_categories.id', '=', 'asset_types.category_id')
            ->select('assets.id', 'asset_types.name as type_name', 'asset_categories.name as cat_name')
            ->orderBy('assets.id')
            ->get();

        if ($assets->isEmpty()) {
            $this->command->error('Assets belum ada.');
            return;
        }

        $totalAssets = $assets->count();
        $imagesPerAsset = 10;
        $totalImages = $totalAssets * $imagesPerAsset;
        $batchSize = 500;
        $batch = [];
        $processed = 0;

        $this->command->info("Membuat {$totalImages} gambar untuk {$totalAssets} aset ({$imagesPerAsset} gambar/aset)...");
        $this->command->info("Output: public/assets/images/");

        foreach ($assets as $asset) {
            $catPalette = $this->palette[$asset->cat_name] ?? $this->palette['Hunian'];

            for ($n = 1; $n <= $imagesPerAsset; $n++) {
                $filename = "asset_{$asset->id}_{$n}.jpg";
                $filePath = $publicDir . '/' . $filename;

                // Generate gambar dengan GD jika belum ada
                if (!file_exists($filePath)) {
                    $this->generateImage(
                        $filePath,
                        $asset->type_name,
                        $asset->cat_name,
                        $asset->id,
                        $n,
                        $catPalette[$n % count($catPalette)]
                    );
                }

                $batch[] = [
                    'asset_id'   => $asset->id,
                    'image'      => 'assets/images/' . $filename,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $processed++;
            }

            // Insert per batch untuk hemat memory
            if (count($batch) >= $batchSize) {
                DB::table('asset_images')->insert($batch);
                $batch = [];
                $this->command->info("  {$processed}/{$totalImages} gambar selesai...");
            }
        }

        // Insert sisa batch
        if (!empty($batch)) {
            DB::table('asset_images')->insert($batch);
        }

        $this->command->info("✓ {$totalImages} gambar berhasil dibuat di public/assets/images/");
    }

    /**
     * Generate sebuah gambar JPEG 800×500 dengan GD Library.
     * Gambar berisi gradient background + nama tipe aset + nomor gambar.
     */
    private function generateImage(
        string $filePath,
        string $typeName,
        string $catName,
        int $assetId,
        int $imageNum,
        array $baseColor
    ): void {
        $width  = 800;
        $height = 500;
        $img = imagecreatetruecolor($width, $height);

        // Warna dasar & variannya
        [$r, $g, $b] = $baseColor;
        $darkR = max(0, $r - 40);
        $darkG = max(0, $g - 40);
        $darkB = max(0, $b - 40);

        $bgColor     = imagecolorallocate($img, $r, $g, $b);
        $bgDark      = imagecolorallocate($img, $darkR, $darkG, $darkB);
        $white       = imagecolorallocate($img, 255, 255, 255);
        $whiteAlpha  = imagecolorallocatealpha($img, 255, 255, 255, 80);
        $overlay     = imagecolorallocatealpha($img, 0, 0, 0, 50);

        // Fill background dengan gradient manual (horizontal bands)
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $cr = (int)($r + ($darkR - $r) * $ratio);
            $cg = (int)($g + ($darkG - $g) * $ratio);
            $cb = (int)($b + ($darkB - $b) * $ratio);
            $lineColor = imagecolorallocate($img, max(0, $cr), max(0, $cg), max(0, $cb));
            imageline($img, 0, $y, $width, $y, $lineColor);
        }

        // Panel gelap di bagian bawah untuk teks
        imagefilledrectangle($img, 0, $height - 120, $width, $height, $overlay);

        // Icon/pattern dekoratif (kotak-kotak transparan)
        for ($i = 0; $i < 8; $i++) {
            $x1 = rand(50, $width - 50);
            $y1 = rand(20, $height - 140);
            $size = rand(30, 80);
            imagefilledrectangle($img, $x1, $y1, $x1 + $size, $y1 + $size, $whiteAlpha);
        }

        // Garis dekoratif
        imageline($img, 0, $height - 125, $width, $height - 125, imagecolorallocate($img, 255, 255, 255));

        // Teks: nama tipe (besar) - gunakan font bawaan GD
        $fontSize  = 5; // Font built-in GD (1-5)
        $charWidth = imagefontwidth($fontSize);

        // Judul tipe
        $text      = strtoupper($typeName);
        $textWidth = $charWidth * strlen($text);
        $textX     = max(20, ($width - $textWidth) / 2);
        imagestring($img, $fontSize, (int)$textX, $height - 105, $text, $white);

        // Subtitle kategori
        $subText  = $catName . ' · Foto ' . $imageNum;
        $subWidth = imagefontwidth(3) * strlen($subText);
        $subX     = max(20, ($width - $subWidth) / 2);
        imagestring($img, 3, (int)$subX, $height - 75, $subText, $white);

        // Asset ID kecil di pojok kanan bawah
        $idText = 'ID #' . $assetId;
        imagestring($img, 2, $width - (imagefontwidth(2) * strlen($idText)) - 15, $height - 30, $idText, $white);

        // Watermark KuSewa di pojok kiri bawah
        imagestring($img, 2, 15, $height - 30, 'KuSewa', $white);

        // Simpan sebagai JPEG kualitas 85
        imagejpeg($img, $filePath, 85);
        imagedestroy($img);
    }
}
