<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class KtpImagePreprocessor
{
    private const MAX_WIDTH  = 2000;
    private const MAX_HEIGHT = 2000;
    private const MIN_WIDTH  = 300;
    private const MIN_HEIGHT = 150;

    /**
     * Preprocess gambar KTP untuk meningkatkan akurasi OCR.
     * Menggunakan PHP GD (built-in, tidak butuh extension tambahan).
     *
     * @param  string $sourcePath  Path file sumber (absolute atau storage-relative)
     * @param  string $outputPath  Path output file hasil preprocessing
     * @return string              Path output
     * @throws RuntimeException    Jika gambar tidak valid atau terlalu kecil
     */
    public function process(string $sourcePath, string $outputPath): string
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('PHP GD extension tidak tersedia.');
        }

        $image = $this->loadImage($sourcePath);
        if (!$image) {
            throw new RuntimeException('Gambar tidak dapat dibuka atau format tidak didukung.');
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
            imagedestroy($image);
            throw new RuntimeException("Resolusi gambar terlalu rendah ({$width}x{$height}px). Minimal " . self::MIN_WIDTH . "x" . self::MIN_HEIGHT . "px.");
        }

        // 1. Resize jika terlalu besar
        $image = $this->resize($image, $width, $height);

        // 2. Convert ke grayscale
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        // 3. Sedikit tambah brightness/contrast untuk gambar gelap
        imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);
        imagefilter($image, IMG_FILTER_CONTRAST, -15);

        // 4. Sharpen
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1,  16, -1],
            [-1, -1, -1],
        ];
        $divisor = array_sum(array_merge(...$sharpenMatrix));
        imageconvolution($image, $sharpenMatrix, max($divisor, 1), 0);

        // 5. Smooth sedikit untuk noise reduction (tidak terlalu agresif)
        imagefilter($image, IMG_FILTER_SMOOTH, 1);

        // Simpan sebagai PNG (lossless, lebih baik untuk OCR)
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagepng($image, $outputPath);
        imagedestroy($image);

        return $outputPath;
    }

    /**
     * Load image dari berbagai format (jpg, jpeg, png, webp).
     */
    private function loadImage(string $path): \GdImage|false
    {
        $mime = @mime_content_type($path);

        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default      => false,
        };
    }

    /**
     * Resize image jika melebihi batas maksimum, pertahankan aspect ratio.
     */
    private function resize(\GdImage $image, int $width, int $height): \GdImage
    {
        if ($width <= self::MAX_WIDTH && $height <= self::MAX_HEIGHT) {
            return $image;
        }

        $ratio     = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);
        $newWidth  = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
