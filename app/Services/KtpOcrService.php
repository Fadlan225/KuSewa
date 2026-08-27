<?php

namespace App\Services;

use App\DTOs\KtpOcrResult;
use App\Services\Ocr\KtpCityMatcher;
use App\Services\Ocr\KtpDataValidator;
use App\Services\Ocr\KtpFieldParser;
use App\Services\Ocr\KtpImagePreprocessor;
use App\Services\Ocr\KtpRegionMatcher;
use App\Services\Ocr\TesseractOcrService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class KtpOcrService
{
    public function __construct(
        private readonly KtpImagePreprocessor $preprocessor,
        private readonly TesseractOcrService  $tesseract,
        private readonly KtpFieldParser       $parser,
        private readonly KtpCityMatcher       $cityMatcher,
        private readonly KtpRegionMatcher     $regionMatcher,
        private readonly KtpDataValidator     $validator,
    ) {}

    /**
     * Proses gambar KTP: preprocess → OCR → parse → validate → return DTO.
     *
     * @param  string $storedPath  Path file KTP di private storage (e.g. owner/ktp_temp/uuid.jpg)
     * @return KtpOcrResult
     */
    public function process(string $storedPath): KtpOcrResult
    {
        $absolutePath = Storage::disk('local')->path($storedPath);
        $tempProcessed = null;

        try {
            // 1. Preprocess gambar
            $tempProcessed = storage_path('app/private/ocr_temp/' . Str::uuid() . '_processed.png');
            $this->preprocessor->process($absolutePath, $tempProcessed);

            // 2. Jalankan OCR
            $rawText = $this->tesseract->recognize($tempProcessed);

            // Log raw OCR output di dev untuk debugging (tidak log data sensitif di prod)
            if (config('app.debug')) {
                Log::debug('KTP OCR raw text', ['raw' => $rawText]);
            }

            if (empty(trim($rawText))) {
                return KtpOcrResult::failed($storedPath, [
                    'general' => 'Tidak ada teks yang terdeteksi pada gambar KTP.',
                ]);
            }

            // 3. Parse fields
            $data = $this->parser->parse($rawText);

            // 4. Fuzzy match tempat lahir ke city_code
            $birthPlaceMatch = null;
            if (!empty($data['birth_place'])) {
                $birthPlaceMatch = $this->cityMatcher->matchWithDetails($data['birth_place']);
            }

            // 5. Resolve provinsi & kota dari kecamatan + kelurahan OCR (DB lookup)
            $regionMatch = $this->regionMatcher->match(
                $data['village']  ?? null,
                $data['district'] ?? null
            );

            if (config('app.debug') && $regionMatch) {
                Log::debug('KTP Region match', [
                    'via'        => $regionMatch['match_via'],
                    'province'   => $regionMatch['province_name'] ?? null,
                    'city'       => $regionMatch['city_name']     ?? null,
                    'similarity' => $regionMatch['similarity']    ?? null,
                ]);
            }

            // 6. Validasi
            $errors = $this->validator->validate($data);

            // 7. Tentukan status
            $filledCount = $this->validator->countFilledFields($data);
            $hasMinimum  = $this->validator->hasMinimumData($data);

            $status = match (true) {
                $filledCount === 0 => 'failed',
                !$hasMinimum       => 'partial',
                default            => $filledCount >= 5 ? 'success' : 'partial',
            };

            // Jangan log isi NIK/KTP ke application log
            Log::info('KTP OCR completed', [
                'status'        => $status,
                'fields_filled' => $filledCount,
                'has_minimum'   => $hasMinimum,
            ]);

            return new KtpOcrResult(
                status: $status,
                data: array_merge($data, [
                    // Data tempat lahir
                    'birth_place_city_code' => $birthPlaceMatch ? $birthPlaceMatch['code'] : null,
                    'birth_place_ocr_text'  => $data['birth_place'] ?? null,
                    // Data wilayah dari DB lookup (kecamatan/kelurahan → kota/provinsi)
                    'province_code'  => $regionMatch['province_code']  ?? null,
                    'province_name'  => $regionMatch['province_name']  ?? null,
                    'city_code'      => $regionMatch['city_code']      ?? null,
                    'city_name'      => $regionMatch['city_name']      ?? null,
                    'district_code'  => $regionMatch['district_code']  ?? null,
                    'district_name'  => $regionMatch['district_name']  ?? null,
                    'village_code'   => $regionMatch['village_code']   ?? null,
                    'village_name'   => $regionMatch['village_name']   ?? null,
                    'region_match_via' => $regionMatch['match_via']    ?? null,
                ]),
                errors: $errors,
                partial: $status === 'partial',
                ktpPhotoPath: $storedPath,
            );

        } catch (RuntimeException $e) {
            Log::warning('KTP OCR failed: ' . $e->getMessage());
            return KtpOcrResult::failed($storedPath, ['general' => $e->getMessage()]);

        } finally {
            // Selalu bersihkan file temp preprocessing
            if ($tempProcessed && file_exists($tempProcessed)) {
                @unlink($tempProcessed);
            }
        }
    }
}
