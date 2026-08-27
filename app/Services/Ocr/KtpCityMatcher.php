<?php

namespace App\Services\Ocr;

use App\Models\city;
use Illuminate\Support\Facades\Cache;

class KtpCityMatcher
{
    private const CACHE_KEY = 'ktp_city_matcher_cities';
    private const CACHE_TTL = 3600; // 1 jam
    private const MIN_SIMILARITY = 70; // threshold 70%

    /**
     * Fuzzy match nama kota dari OCR ke city_code di database.
     *
     * @param  string|null $cityName  Nama kota dari hasil OCR (e.g. "SAMARINDA")
     * @return string|null            City code (e.g. "6471") atau null jika tidak match
     */
    public function match(?string $cityName): ?string
    {
        $result = $this->matchWithDetails($cityName);
        return $result ? $result['code'] : null;
    }

    /**
     * Cari nama kota berdasarkan OCR text (untuk digunakan sebagai placeholder hint).
     *
     * @param  string|null $cityName
     * @return array|null ['code' => '...', 'name' => '...', 'similarity' => 85.5]
     */
    public function matchWithDetails(?string $cityName): ?array
    {
        if (!$cityName || strlen(trim($cityName)) < 3) {
            return null;
        }

        $cities = $this->getCities();
        $normalized = $this->normalize($cityName);

        $bestCity       = null;
        $bestSimilarity = 0;

        foreach ($cities as $city) {
            $cityNormalized = $this->normalize($city['name']);

            // Exact match dulu
            if ($cityNormalized === $normalized) {
                return [
                    'code'       => $city['code'],
                    'name'       => $city['name'],
                    'similarity' => 100.0,
                ];
            }

            // Fuzzy match dengan similar_text
            similar_text($normalized, $cityNormalized, $percent);

            if ($percent > $bestSimilarity) {
                $bestSimilarity = $percent;
                $bestCity       = $city;
            }
        }

        if ($bestSimilarity >= self::MIN_SIMILARITY && $bestCity) {
            return [
                'code'       => $bestCity['code'],
                'name'       => $bestCity['name'],
                'similarity' => round($bestSimilarity, 1),
                'ocr_text'   => $cityName, // teks asli dari OCR untuk hint UI
            ];
        }

        return null;
    }

    /**
     * Load semua kota dari database, cached.
     * city model: primary_key = 'code', field = 'name'
     */
    private function getCities(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return city::select('code', 'name')->get()->map(function ($c) {
                return ['code' => $c->code, 'name' => $c->name];
            })->toArray();
        });
    }

    /**
     * Normalisasi string untuk perbandingan:
     * - Uppercase
     * - Hapus prefix "KOTA", "KAB.", "KABUPATEN"
     * - Trim & collapse spasi
     */
    private function normalize(string $name): string
    {
        $upper = strtoupper(trim($name));
        $upper = preg_replace('/\b(KOTA|KAB\.|KAB|KABUPATEN|KOTA ADM\.)\b/', '', $upper);
        $upper = preg_replace('/\s+/', ' ', $upper);
        return trim($upper);
    }
}
