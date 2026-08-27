<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class TesseractOcrService
{
    private string $binary;
    private string $lang;
    private int    $timeout;
    private string $lockFile;

    public function __construct()
    {
        $this->lang     = config('ocr.tesseract_lang', 'eng');
        $this->timeout  = (int) config('ocr.timeout', 30);
        $this->lockFile = storage_path('app/private/ocr.lock');
        $this->binary   = $this->resolveBinaryPath();
    }

    /**
     * Resolve path ke Tesseract binary.
     * Windows: "C:\Program Files\Tesseract-OCR\tesseract.exe" (perlu dikutip saat exec)
     * Linux:   /usr/bin/tesseract
     */
    private function resolveBinaryPath(): string
    {
        $envBinary = config('ocr.tesseract_binary', '');

        if ($envBinary) {
            // Hapus tanda kutip jika ada (dari .env)
            $clean = trim($envBinary, '"\'');
            if (file_exists($clean)) {
                return $clean;
            }
        }

        // Auto-detect berdasarkan OS
        if (PHP_OS_FAMILY === 'Windows') {
            $commonPaths = [
                'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
                'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
            ];
            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            // Coba dari PATH
            $output = shell_exec('where tesseract 2>NUL');
            if ($output) {
                return trim(explode("\n", $output)[0]);
            }
        }

        return '/usr/bin/tesseract';
    }

    /**
     * Jalankan OCR pada file gambar.
     * Dual-attempt: PSM 3 → PSM 6 jika hasil kurang baik.
     *
     * @param  string $imagePath Path absolut ke file gambar
     * @return string            Raw OCR text
     * @throws RuntimeException
     */
    public function recognize(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new RuntimeException("File gambar tidak ditemukan: {$imagePath}");
        }

        if (!$this->isBinaryAvailable()) {
            throw new RuntimeException(
                "Tesseract binary tidak ditemukan di: {$this->binary}. " .
                "Linux: sudo apt-get install tesseract-ocr tesseract-ocr-ind | " .
                "Windows: https://github.com/UB-Mannheim/tesseract/wiki"
            );
        }

        return $this->withConcurrencyLock(function () use ($imagePath) {
            // Attempt 1: PSM 3 (auto page segmentation)
            $result1 = $this->runTesseract($imagePath, psm: 3);

            // Attempt 2: PSM 6 (uniform block) jika result terlalu sedikit
            if ($this->isResultWeak($result1)) {
                $result2 = $this->runTesseract($imagePath, psm: 6);
                return strlen(trim($result2)) > strlen(trim($result1)) ? $result2 : $result1;
            }

            return $result1;
        });
    }

    /**
     * Jalankan satu Tesseract process.
     * Menggunakan proc_open agar bisa set timeout dengan benar.
     */
    private function runTesseract(string $imagePath, int $psm): string
    {
        $lang = $this->getAvailableLang();

        // Bangun command — Windows path dengan spasi perlu dikutip
        $binaryEscaped = PHP_OS_FAMILY === 'Windows'
            ? '"' . $this->binary . '"'
            : escapeshellcmd($this->binary);

        $cmd = sprintf(
            '%s %s stdout -l %s --psm %d --oem 1',
            $binaryEscaped,
            escapeshellarg($imagePath),
            $lang,
            $psm
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Gagal membuka Tesseract process.');
        }

        fclose($pipes[0]);

        stream_set_timeout($pipes[1], $this->timeout);
        $output = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]); // flush stderr

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode > 1) {
            Log::warning("Tesseract exit code {$exitCode} (psm={$psm}, lang={$lang})");
        }

        return (string) $output;
    }

    /**
     * Cek apakah lang yang dikonfigurasi tersedia.
     * Jika tidak, fallback ke 'eng'.
     */
    private function getAvailableLang(): string
    {
        $binaryEscaped = PHP_OS_FAMILY === 'Windows'
            ? '"' . $this->binary . '"'
            : escapeshellcmd($this->binary);

        $output = shell_exec($binaryEscaped . ' --list-langs 2>&1') ?? '';

        // Cek apakah lang yang dikonfigurasi tersedia
        if (str_contains($output, $this->lang)) {
            return $this->lang;
        }

        // Coba ind dulu jika lang bukan eng
        if ($this->lang !== 'ind' && str_contains($output, 'ind')) {
            return 'ind';
        }

        // Fallback ke eng (selalu ada di semua instalasi)
        if (str_contains($output, 'eng')) {
            Log::info("Tesseract: lang={$this->lang} tidak tersedia, fallback ke eng");
            return 'eng';
        }

        return $this->lang; // fallback ke configured, let tesseract error sendiri
    }

    /**
     * Cek apakah hasil OCR sangat sedikit teks.
     */
    private function isResultWeak(string $text): bool
    {
        return str_word_count(trim($text)) < 5;
    }

    /**
     * Atomic file lock — max 1 proses OCR bersamaan (penting untuk VPS RAM 1GB).
     */
    private function withConcurrencyLock(callable $callback): string
    {
        $lockDir = dirname($this->lockFile);
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }

        $fp = fopen($this->lockFile, 'c');
        if (!$fp) {
            // Jika lock file tidak bisa dibuat, jalankan tanpa lock
            Log::warning('OCR lock file tidak dapat dibuat, berjalan tanpa concurrency lock.');
            return $callback();
        }

        $waited = 0;
        while (!flock($fp, LOCK_EX | LOCK_NB)) {
            if ($waited >= $this->timeout) {
                fclose($fp);
                throw new RuntimeException('OCR sedang sibuk. Coba beberapa saat lagi.');
            }
            sleep(1);
            $waited++;
        }

        try {
            return $callback();
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Cek apakah binary tesseract dapat dieksekusi.
     */
    private function isBinaryAvailable(): bool
    {
        return file_exists($this->binary) && is_readable($this->binary);
    }

    /**
     * Expose resolved binary path untuk debugging.
     */
    public function getBinaryPath(): string
    {
        return $this->binary;
    }
}
