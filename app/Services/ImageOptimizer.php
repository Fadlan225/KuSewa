<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * Process an uploaded image: resize if too large and convert to WebP.
     * 
     * @param UploadedFile $file The uploaded file.
     * @param string $path The directory path to save within the public disk.
     * @param int $maxWidth The max width (default 1280).
     * @param int $quality The WebP quality (default 80).
     * @return string The stored file path relative to disk.
     */
    public static function process(UploadedFile $file, string $path, int $maxWidth = 1280, int $quality = 80): string
    {
        // Jika bukan gambar (misal PDF), simpan secara normal tanpa diubah
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            return $file->store($path, 'public');
        }

        $manager = new ImageManager(new Driver());

        // Decode image from the temporary uploaded path
        $image = $manager->decode($file->getRealPath());

        // Resize down if it's larger than $maxWidth (width)
        $image->scaleDown(width: $maxWidth);

        // Convert to WebP format
        $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder($quality));

        // Generate a unique filename with .webp extension
        $filename = uniqid(time() . '_') . '.webp';
        
        $fullPath = trim($path, '/') . '/' . $filename;

        // Store to public disk
        Storage::disk('public')->put($fullPath, $encoded->toString());

        return $fullPath;
    }
}
