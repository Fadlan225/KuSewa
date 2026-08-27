<?php

namespace App\Console\Commands;

use App\Services\KtpOcrService;
use App\Services\Ocr\KtpFieldParser;
use App\Services\Ocr\KtpImagePreprocessor;
use App\Services\Ocr\TesseractOcrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestKtpOcr extends Command
{
    protected $signature   = 'ocr:test {path : Path absolut ke file gambar KTP}';
    protected $description = 'Test OCR pada file gambar KTP dan tampilkan hasil parsing';

    public function handle(
        TesseractOcrService  $tesseract,
        KtpFieldParser       $parser,
        KtpImagePreprocessor $preprocessor,
    ): int {
        $imagePath = $this->argument('path');

        if (!file_exists($imagePath)) {
            $this->error("File tidak ditemukan: {$imagePath}");
            return 1;
        }

        $this->info("=== KTP OCR Test ===");
        $this->line("File  : {$imagePath}");
        $this->line("Binary: {$tesseract->getBinaryPath()}");

        // Step 1: Preprocessing
        $this->newLine();
        $this->info("Step 1: Preprocessing gambar...");
        $tempOutput = storage_path('app/private/ocr_test_' . Str::uuid() . '.png');

        try {
            $preprocessor->process($imagePath, $tempOutput);
            $this->line("  ✓ Preprocessing selesai → {$tempOutput}");
        } catch (\Throwable $e) {
            $this->warn("  ! Preprocessing gagal: " . $e->getMessage() . " — lanjut dengan file original");
            $tempOutput = $imagePath;
        }

        // Step 2: OCR
        $this->newLine();
        $this->info("Step 2: Menjalankan Tesseract OCR...");

        try {
            $rawText = $tesseract->recognize($tempOutput);
            $this->line("  ✓ OCR selesai, " . strlen($rawText) . " karakter");
            $this->newLine();
            $this->comment("=== Raw OCR Output ===");
            $this->line($rawText);
        } catch (\Throwable $e) {
            $this->error("  ✗ OCR gagal: " . $e->getMessage());
            // Cleanup
            if ($tempOutput !== $imagePath && file_exists($tempOutput)) {
                @unlink($tempOutput);
            }
            return 1;
        }

        // Step 3: Parse
        $this->newLine();
        $this->info("Step 3: Parsing fields dari raw text...");
        $parsed = $parser->parse($rawText);

        $this->newLine();
        $this->comment("=== Hasil Parsing ===");
        $this->table(
            ['Field', 'Value', 'Status'],
            collect($parsed)->map(function ($value, $key) {
                $status = $value !== null ? '<fg=green>✓ OK</>' : '<fg=red>✗ Tidak terbaca</>';
                return [$key, $value ?? '(null)', $status];
            })->values()->toArray()
        );

        $filledCount = count(array_filter($parsed, fn($v) => $v !== null));
        $totalFields = count($parsed);

        $this->newLine();
        if ($filledCount >= 6) {
            $this->info("✓ Berhasil membaca {$filledCount}/{$totalFields} field");
        } elseif ($filledCount >= 3) {
            $this->warn("⚠ Sebagian terbaca: {$filledCount}/{$totalFields} field");
        } else {
            $this->error("✗ Hanya {$filledCount}/{$totalFields} field terbaca — gambar mungkin terlalu buram atau format tidak dikenali");
        }

        // Cleanup
        if ($tempOutput !== $imagePath && file_exists($tempOutput)) {
            @unlink($tempOutput);
            $this->line("  (Temp file dibersihkan)");
        }

        return 0;
    }
}
