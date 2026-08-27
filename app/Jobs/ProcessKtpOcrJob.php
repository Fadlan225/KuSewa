<?php

namespace App\Jobs;

use App\DTOs\KtpOcrResult;
use App\Services\KtpOcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessKtpOcrJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    /**
     * Tidak ada retry otomatis — OCR failure langsung jadi failed state.
     * User dapat upload ulang jika diperlukan.
     */
    public int $tries = 1;

    /**
     * Timeout 60 detik untuk seluruh proses OCR.
     */
    public int $timeout = 60;

    /**
     * TTL cache result: 10 menit (user polling dalam window ini).
     */
    private const CACHE_TTL = 600;

    public function __construct(
        public readonly string $storedPath, // path di private storage
        public readonly string $jobId,      // UUID yang dikembalikan ke frontend untuk polling
        public readonly int    $userId,     // untuk logging saja (bukan NIK)
    ) {
        // Named queue 'ocr' — worker: php artisan queue:work --queue=ocr --concurrency=1
        $this->onQueue('ocr');
    }

    /**
     * Jalankan OCR.
     */
    public function handle(KtpOcrService $service): void
    {
        try {
            $result = $service->process($this->storedPath);

            // Simpan result ke cache untuk polling frontend
            Cache::put(
                $this->cacheKey(),
                [
                    'status'  => $result->status === 'failed' ? 'failed' : 'completed',
                    'data'    => $result->data,
                    'errors'  => $result->errors,
                    'partial' => $result->partial,
                    'ktp_photo_path' => $result->ktpPhotoPath,
                ],
                now()->addSeconds(self::CACHE_TTL)
            );

            Log::info("OCR job completed for user #{$this->userId}", [
                'job_id' => $this->jobId,
                'status' => $result->status,
            ]);

        } catch (\Throwable $e) {
            Log::error("OCR job exception for user #{$this->userId}: " . $e->getMessage(), [
                'job_id' => $this->jobId,
            ]);

            Cache::put(
                $this->cacheKey(),
                [
                    'status'  => 'failed',
                    'data'    => [],
                    'errors'  => ['general' => 'Terjadi kesalahan saat memproses KTP.'],
                    'partial' => false,
                    'ktp_photo_path' => $this->storedPath,
                ],
                now()->addSeconds(self::CACHE_TTL)
            );
        }
    }

    /**
     * Handle job failure (timeout, dll).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("OCR job failed (job failed handler) user #{$this->userId}: " . $exception->getMessage());

        Cache::put(
            $this->cacheKey(),
            [
                'status'  => 'failed',
                'data'    => [],
                'errors'  => ['general' => 'Proses OCR gagal atau timeout. Silakan coba lagi.'],
                'partial' => false,
                'ktp_photo_path' => $this->storedPath,
            ],
            now()->addSeconds(self::CACHE_TTL)
        );
    }

    public function cacheKey(): string
    {
        return "ocr_result_{$this->jobId}";
    }
}
