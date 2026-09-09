<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Process an uploaded image: resize if too large and convert to WebP.
     *
     * @param UploadedFile $file       The uploaded file.
     * @param string       $path       Directory path to save within the public disk.
     * @param int          $maxWidth   Max width in px (default 1280).
     * @param int          $quality    WebP quality 1–100 (default 80).
     * @return string  Stored file path relative to public disk.
     */
    public static function process(UploadedFile $file, string $path, int $maxWidth = 1280, int $quality = 80): string
    {
        // Jika bukan gambar (misal PDF / doc), simpan langsung tanpa konversi
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            return $file->store($path, 'public');
        }

        try {
            $manager = new ImageManager(new Driver());

            // Intervention Image v4: gunakan read() bukan decode()
            $image = $manager->read($file->getRealPath());

            // Scale down hanya jika lebih lebar dari $maxWidth
            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            // Encode ke WebP
            $encoded = $image->toWebp($quality);

            // Nama file unik dengan ekstensi .webp
            $filename = Str::uuid() . '.webp';
            $fullPath = trim($path, '/') . '/' . $filename;

            Storage::disk('public')->put($fullPath, $encoded->toString());

            return $fullPath;

        } catch (\Throwable $e) {
            // Fallback: simpan file asli jika konversi gagal
            \Log::warning('ImageOptimizer: WebP conversion failed, storing original. Error: ' . $e->getMessage());
            return $file->store($path, 'public');
        }
    }
}
