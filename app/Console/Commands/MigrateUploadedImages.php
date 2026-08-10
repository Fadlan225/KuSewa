<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\asset_image;

class MigrateUploadedImages extends Command
{
    protected $signature   = 'images:migrate-paths';
    protected $description = 'Pindahkan path gambar yang diupload owner dari assets/images/ ke uploads/assets/';

    public function handle(): int
    {
        // Ambil semua gambar yang pathnya dimulai dengan 'assets/images/'
        // (bukan seeder yang ada di public/assets/foto/)
        $images = asset_image::where('image', 'like', 'assets/images/%')->get();

        if ($images->isEmpty()) {
            $this->info('Tidak ada gambar yang perlu dimigasi.');
            return self::SUCCESS;
        }

        $migrated = 0;
        $failed   = 0;

        foreach ($images as $img) {
            $oldPath = $img->image; // e.g. assets/images/abc.jpg

            // Hanya migrasikan jika file ada di storage disk (bukan seeder file)
            if (!Storage::disk('public')->exists($oldPath)) {
                $this->warn("File tidak ditemukan di storage: {$oldPath} — dilewati.");
                $failed++;
                continue;
            }

            // Tentukan path baru
            $filename = basename($oldPath);
            $newPath  = 'uploads/assets/' . $filename;

            // Salin file ke path baru
            Storage::disk('public')->copy($oldPath, $newPath);

            // Verifikasi file berhasil disalin
            if (!Storage::disk('public')->exists($newPath)) {
                $this->error("Gagal menyalin: {$oldPath} → {$newPath}");
                $failed++;
                continue;
            }

            // Update record di DB
            $img->image = $newPath;
            $img->save();

            // Hapus file lama
            Storage::disk('public')->delete($oldPath);

            $this->line("✓ {$oldPath} → {$newPath}");
            $migrated++;
        }

        $this->info("Selesai. Berhasil: {$migrated}, Gagal: {$failed}");
        return self::SUCCESS;
    }
}
