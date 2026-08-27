<?php

namespace App\Services\Ocr;

use App\Models\city;
use App\Models\district;
use App\Models\province;
use App\Models\village;
use Illuminate\Support\Facades\Cache;

/**
 * Mencocokkan hasil OCR kecamatan / kelurahan ke data wilayah di database.
 *
 * Alur prioritas:
 *   1. Coba fuzzy-match kelurahan → villages → district_code → city → province
 *   2. Jika gagal, coba fuzzy-match kecamatan → districts → city_code → province
 *
 * Hasilnya:
 *   [
 *     'province_code'  => '64',
 *     'province_name'  => 'KALIMANTAN TIMUR',
 *     'city_code'      => '6471',
 *     'city_name'      => 'KOTA SAMARINDA',
 *     'district_code'  => '647101',
 *     'district_name'  => 'SAMARINDA UTARA',
 *     'village_code'   => '6471012001',   // hanya saat match via village
 *     'village_name'   => 'LEMPAKE',
 *     'match_via'      => 'village'|'district',
 *     'similarity'     => 87.5,
 *   ]
 */
class KtpRegionMatcher
{
    private const CACHE_TTL          = 3600 * 6; // 6 jam
    private const MIN_SIMILARITY_VLG = 60;        // threshold kelurahan
    private const MIN_SIMILARITY_DST = 65;        // threshold kecamatan

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Match kelurahan & kecamatan OCR ke wilayah database.
     *
     * @param  string|null $villageOcr   Nama kelurahan dari OCR (e.g. "LEMPAKE")
     * @param  string|null $districtOcr  Nama kecamatan dari OCR (e.g. "SAMARINDA UTARA")
     * @return array|null
     */
    public function match(?string $villageOcr, ?string $districtOcr): ?array
    {
        // Pass 1: via kelurahan (lebih spesifik)
        if ($villageOcr && strlen(trim($villageOcr)) >= 3) {
            $result = $this->matchViaVillage($villageOcr, $districtOcr);
            if ($result) return $result;
        }

        // Pass 2: fallback via kecamatan
        if ($districtOcr && strlen(trim($districtOcr)) >= 3) {
            $result = $this->matchViaDistrict($districtOcr);
            if ($result) return $result;
        }

        return null;
    }

    // =========================================================================
    // Pass 1: Match via kelurahan
    // =========================================================================

    private function matchViaVillage(string $villageOcr, ?string $districtOcr): ?array
    {
        $villages      = $this->getVillages();
        $normalizedVlg = $this->normalize($villageOcr);

        $bestVillage    = null;
        $bestSimilarity = 0;

        foreach ($villages as $v) {
            $norm = $this->normalize($v['name']);

            // Exact match langsung return
            if ($norm === $normalizedVlg) {
                $bestVillage    = $v;
                $bestSimilarity = 100.0;
                break;
            }

            similar_text($normalizedVlg, $norm, $pct);
            if ($pct > $bestSimilarity) {
                $bestSimilarity = $pct;
                $bestVillage    = $v;
            }
        }

        if ($bestSimilarity < self::MIN_SIMILARITY_VLG || !$bestVillage) {
            return null;
        }

        // Cross-validate: jika kecamatan OCR tersedia, pastikan kelurahan yang
        // cocok memang berada di kecamatan yang namanya mirip.
        // Ini mencegah false positive kelurahan bernama sama di kota berbeda.
        if ($districtOcr) {
            $districtRec = district::where('code', $bestVillage['district_code'])->first();
            if ($districtRec) {
                similar_text($this->normalize($districtOcr), $this->normalize($districtRec->name), $dstPct);
                // Jika kecamatan sangat tidak mirip dan kelurahan tidak 100% match, skip
                if ($dstPct < 45 && $bestSimilarity < 90) {
                    return null;
                }
            }
        }

        return $this->buildFromDistrictCode($bestVillage['district_code'], [
            'match_via'    => 'village',
            'similarity'   => round($bestSimilarity, 1),
            'village_code' => $bestVillage['code'],
            'village_name' => $bestVillage['name'],
        ]);
    }

    // =========================================================================
    // Pass 2: Match via kecamatan
    // =========================================================================

    private function matchViaDistrict(string $districtOcr): ?array
    {
        $districts     = $this->getDistricts();
        $normalizedDst = $this->normalize($districtOcr);

        $bestDistrict   = null;
        $bestSimilarity = 0;

        foreach ($districts as $d) {
            $norm = $this->normalize($d['name']);

            if ($norm === $normalizedDst) {
                $bestDistrict   = $d;
                $bestSimilarity = 100.0;
                break;
            }

            similar_text($normalizedDst, $norm, $pct);
            if ($pct > $bestSimilarity) {
                $bestSimilarity = $pct;
                $bestDistrict   = $d;
            }
        }

        if ($bestSimilarity < self::MIN_SIMILARITY_DST || !$bestDistrict) {
            return null;
        }

        return $this->buildFromDistrictCode($bestDistrict['code'], [
            'match_via'  => 'district',
            'similarity' => round($bestSimilarity, 1),
        ]);
    }

    // =========================================================================
    // Builder: susun hasil dari district_code ke atas
    // =========================================================================

    private function buildFromDistrictCode(string $districtCode, array $extra = []): ?array
    {
        $districtRec = district::where('code', $districtCode)->first();
        if (!$districtRec) return null;

        $cityRec = city::where('code', $districtRec->city_code)->first();
        if (!$cityRec) return null;

        $provinceRec = province::where('code', $cityRec->province_code)->first();
        if (!$provinceRec) return null;

        return array_merge([
            'province_code'  => $provinceRec->code,
            'province_name'  => $provinceRec->name,
            'city_code'      => $cityRec->code,
            'city_name'      => $cityRec->name,
            'district_code'  => $districtRec->code,
            'district_name'  => $districtRec->name,
            'village_code'   => null,
            'village_name'   => null,
            'match_via'      => null,
            'similarity'     => 0.0,
        ], $extra);
    }

    // =========================================================================
    // Data loaders — cached agar tidak query jutaan baris setiap request
    // =========================================================================

    private function getVillages(): array
    {
        return Cache::remember('ktp_region_matcher_villages', self::CACHE_TTL, function () {
            return village::select('code', 'name', 'district_code')->get()->map(fn($v) => [
                'code'          => $v->code,
                'name'          => $v->name,
                'district_code' => $v->district_code,
            ])->toArray();
        });
    }

    private function getDistricts(): array
    {
        return Cache::remember('ktp_region_matcher_districts', self::CACHE_TTL, function () {
            return district::select('code', 'name', 'city_code')->get()->map(fn($d) => [
                'code'      => $d->code,
                'name'      => $d->name,
                'city_code' => $d->city_code,
            ])->toArray();
        });
    }

    // =========================================================================
    // Normalisasi nama wilayah
    // =========================================================================

    /**
     * Normalisasi untuk fuzzy matching:
     * - Uppercase
     * - Hapus prefix label OCR (KEL., DESA, KEC., dsb.)
     * - Collapse whitespace
     */
    private function normalize(string $name): string
    {
        $upper = strtoupper(trim($name));
        $upper = preg_replace('/\b(KEL\.|KEL|KELURAHAN|DESA|KEC\.|KEC|KECAMATAN)\b/', '', $upper);
        $upper = preg_replace('/\s+/', ' ', $upper);
        return trim($upper);
    }
}
