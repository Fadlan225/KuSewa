<?php

namespace App\Services\Ocr;

class KtpFieldParser
{
    /**
     * Urutan field KTP standar Indonesia (positional fallback reference).
     * Setelah NIK ditemukan sebagai anchor, field berikutnya mengikuti urutan ini.
     */
    private const KTP_FIELD_ORDER = [
        'name',           // Nama
        'birth',          // Tempat/Tgl Lahir (KOTA, DD-MM-YYYY)
        'gender',         // Jenis Kelamin
        'address',        // Alamat
        'rt_rw',          // RT/RW
        'village',        // Kel/Desa
        'district',       // Kecamatan
        'religion',       // Agama
        'marital_status', // Status Perkawinan
        'occupation',     // Pekerjaan
        'nationality',    // Kewarganegaraan
    ];

    /**
     * Label-label yang menandai field baru (untuk deteksi batas nilai multi-baris).
     */
    private const FIELD_LABELS = [
        'NIK', 'NAMA', 'TEMPAT', 'LAHIR', 'JENIS', 'KELAMIN',
        'ALAMAT', 'RT', 'RW', 'KEL', 'DESA', 'KECAMATAN',
        'AGAMA', 'STATUS', 'PERKAWINAN', 'PEKERJAAN',
        'KEWARGANEGARAAN', 'BERLAKU', 'HINGGA', 'PROVINSI',
        'GOL', 'DARAH',
    ];

    /**
     * Parse raw OCR text dari KTP Indonesia.
     * Strategy: label-based primary, positional fallback.
     */
    public function parse(string $rawText): array
    {
        $lines = $this->normalizeLines($rawText);
        $upper = strtoupper($rawText);

        $result = [
            'nik'            => $this->parseNik($rawText, $lines),
            'name'           => $this->parseName($lines),
            'birth_place'    => $this->parseBirthPlace($lines),
            'birth_date'     => $this->parseBirthDate($rawText),
            'gender'         => $this->parseGender($upper),
            'address'        => $this->parseAddress($lines),   // sudah termasuk RT/RW
            'village'        => $this->parseFieldByLabel($lines, ['KEL/DESA', 'KEL / DESA', 'KELURAHAN', 'DESA']),
            'district'       => $this->parseDistrict($lines),
            'religion'       => $this->parseReligion($lines),
            'marital_status' => $this->parseMaritalStatus($lines),
            'occupation'     => $this->parseOccupation($lines),
            'nationality'    => $this->parseNationality($lines),
        ];

        // Fallback positional untuk field yang null
        $result = $this->applyPositionalFallback($lines, $result);

        return $result;
    }

    // =========================================================================
    // NIK Parser
    // =========================================================================

    /**
     * Parse NIK dengan berbagai variasi format OCR.
     * KTP standar: NIK selalu berupa 16 digit angka.
     *
     * Strategi multi-pass:
     *  0. Scan baris NIK, termasuk NIK yang terpotong newline di baris berikutnya
     *  1. Regex fleksibel di baris yang mengandung "NIK"
     *  2. Scan semua digit di baris tersebut
     *  3. Blok 14-18 karakter pure digit/ocr-char di seluruh teks
     *  4. Sekuen digit di mana saja (paling agresif)
     */
    private function parseNik(string $rawText, array $lines): ?string
    {
        $upper    = strtoupper($rawText);
        $rawLines = explode("\n", $upper);
        // rawLines asli (case-sensitive) untuk Pass 0 — PENTING: b lowercase → 6, B uppercase → 8
        $rawLinesOriginal = explode("\n", $rawText);

        // Pass 0: Scan baris NIK dari teks ASLI (case-sensitive) agar b→6 bukan B→8
        foreach ($rawLinesOriginal as $idx => $line) {
            // Skip baris yang tidak mengandung 'NIK' (case-insensitive)
            if (stripos($line, 'NIK') === false) continue;

            // Pre-substitute karakter OCR noise yang merepresentasikan digit
            // ? → 7 (bukan dihapus!), © → 0
            $combined = strtr($line, ['?' => '7', '©' => '0', '°' => '0']);
            // Strip karakter non-alphanumeric sisanya (pertahankan huruf case aslinya)
            $combined = preg_replace('/[^A-Za-z0-9]/', '', $combined);
            // Hapus prefiks "NIK" dari string (case-insensitive)
            $combined = preg_replace('/NIK/i', '', $combined);

            // Jika kurang dari 14 char, coba gabung dengan baris berikutnya
            if (strlen($combined) < 14 && isset($rawLinesOriginal[$idx + 1])) {
                $nextStripped = strtr($rawLinesOriginal[$idx + 1], ['?' => '7', '©' => '0']);
                $nextStripped = preg_replace('/[^A-Za-z0-9]/i', '', $nextStripped);
                $combined .= $nextStripped;
            }

            $result = $this->tryNormalizeNik($combined);
            if ($result) return $result;
        }

        // Pass 1: Cari baris yang mengandung "NIK" dengan regex fleksibel
        foreach ($rawLines as $line) {
            if (!str_contains($line, 'NIK')) continue;

            // Pattern sangat fleksibel: boleh ada spasi, =, :, >, |, -, . setelahnya
            if (preg_match('/NIK\s*[=:>\|\.\-\s]+([A-Z0-9\s]{12,22})/i', $line, $m)) {
                $candidate = preg_replace('/\s+/', '', trim($m[1]));
                $result = $this->tryNormalizeNik($candidate);
                if ($result) return $result;
            }

            // Coba ambil semua digit/ocr-char di baris setelah "NIK"
            $afterNik = substr($line, strpos($line, 'NIK') + 3);
            $afterNik = preg_replace('/[^A-Z0-9]/i', '', $afterNik);
            if (strlen($afterNik) >= 14) {
                $result = $this->tryNormalizeNik($afterNik);
                if ($result) return $result;
            }
        }

        // Pass 2: Cari blok 14-18 karakter yang seluruhnya digit/ocr-char di seluruh teks
        // (untuk kasus NIK tanpa label)
        foreach ($lines as $i => $line) {
            $stripped = preg_replace('/[^A-Z0-9]/i', '', $line);
            if (strlen($stripped) >= 14 && strlen($stripped) <= 18) {
                $result = $this->tryNormalizeNik($stripped);
                if ($result) {
                    // Validasi: 2 digit pertama harus kode provinsi valid (11-99)
                    $province = (int) substr($result, 0, 2);
                    if ($province >= 11 && $province <= 99) {
                        return $result;
                    }
                }
            }
        }

        // Pass 3: Cari sekuen 16 digit/ocr-char di mana saja di teks
        if (preg_match_all('/(?<![A-Z0-9])[\dOILSBGEZTC]{14,20}(?![A-Z0-9])/i', $upper, $matches)) {
            foreach ($matches[0] as $candidate) {
                $clean = preg_replace('/[^A-Z0-9]/i', '', $candidate);
                $result = $this->tryNormalizeNik($clean);
                if ($result) return $result;
            }
        }

        return null;
    }

    /**
     * Two-pass NIK normalization.
     *
     * Dari hasil OCR nyata: "bY ?eO3e 709080004" untuk NIK "6472032709080004"
     *   b→6, Y→4, ?→7 (sudah disubstitusi di Pass 0), e→2 atau 3, O→0
     *
     * Multi-pass untuk menangani semua kemungkinan substitusi:
     *   Pass 1: substitusi standar (O→0, I→1, L→1, S→5, B→8, G→6)
     *   Pass 2: agresif dengan e→3 (e mirip 3 terbalik)
     *   Pass 3: e→3, c→0
     *   Pass 4: agresif dengan e→2 (2 juga bisa terbaca e di font KTP)
     *   Pass 5: e→2, c→0
     */
    private function tryNormalizeNik(string $candidate): ?string
    {
        // Pre-substitute karakter noise yang mungkin lolos
        $candidate = strtr($candidate, ['?' => '7', '©' => '0', '°' => '0']);
        // Strip semua non-alphanumeric
        $candidate = preg_replace('/[^A-Za-z0-9]/', '', $candidate);

        if (strlen($candidate) < 14 || strlen($candidate) > 20) return null;

        // Map standar — visual similarity tinggi
        $standardMap = [
            'O' => '0', 'I' => '1', 'L' => '1', 'S' => '5',
            'B' => '8', 'G' => '6',
        ];

        // Pass 1: standard
        $result1 = $this->applyMap($candidate, $standardMap);
        if (preg_match('/^\d{16}$/', $result1)) return $result1;

        // Map agresif dengan e→2 (diutamakan: dari debug terbukti e→2 benar untuk KTP Samarinda)
        $aggressiveMap2 = $standardMap + [
            'E' => '2', 'e' => '2', 'Z' => '2', 'T' => '7',
            'b' => '6',               // b mirip 6
            'Y' => '4', 'y' => '4',  // Y/y mirip 4
            'Q' => '9', 'q' => '9',
            'U' => '0', 'u' => '0',
            'D' => '0', 'd' => '0',
            'A' => '4',               // 4 kadang terbaca A
            'R' => '8',               // R mirip 8
        ];

        // Pass 2: aggressive e→2
        $result2 = $this->applyMap($candidate, $aggressiveMap2);
        if (preg_match('/^\d{16}$/', $result2)) return $result2;

        // Pass 3: e→2, c→0
        $candidate3 = str_replace(['e', 'c'], ['2', '0'], $candidate);
        $result3 = $this->applyMap($candidate3, $aggressiveMap2);
        if (preg_match('/^\d{16}$/', $result3)) return $result3;

        // Map agresif dengan e→3 (fallback: e kadang mirip 3 terbalik)
        $aggressiveMap3 = $standardMap + [
            'E' => '3', 'e' => '3', 'Z' => '2', 'T' => '7',
            'b' => '6',
            'Y' => '4', 'y' => '4',
            'Q' => '9', 'q' => '9',
            'U' => '0', 'u' => '0',
            'D' => '0', 'd' => '0',
            'A' => '4',
            'R' => '8',
        ];

        // Pass 4: aggressive e→3
        $result4 = $this->applyMap($candidate, $aggressiveMap3);
        if (preg_match('/^\d{16}$/', $result4)) return $result4;

        // Pass 5: e→3, c→0
        $candidate5 = str_replace(['e', 'c'], ['3', '0'], $candidate);
        $result5 = $this->applyMap($candidate5, $aggressiveMap3);
        if (preg_match('/^\d{16}$/', $result5)) return $result5;

        return null;
    }

    /**
     * Apply character substitution map ke string.
     * PENTING: case-sensitive — b ≠ B. Pass 0 mempertahankan case asli OCR.
     * Karakter yang tidak ada di map dikembalikan apa adanya.
     */
    private function applyMap(string $str, array $map): string
    {
        $result = '';
        foreach (str_split($str) as $ch) {
            $result .= $map[$ch] ?? $ch;
        }
        return $result;
    }

    // =========================================================================
    // Nama
    // =========================================================================

    private function parseName(array $lines): ?string
    {
        $value = $this->parseFieldByLabel($lines, ['NAMA']);
        if ($value) return $this->cleanName($value);

        // Fallback: baris setelah NIK biasanya nama (jika label tidak ada)
        $nikIdx = $this->findLineIndex($lines, 'NIK');
        if ($nikIdx !== null && isset($lines[$nikIdx + 1])) {
            $next = $lines[$nikIdx + 1];
            // Nama tidak mengandung angka dan tidak mengandung label lain
            if (!preg_match('/\d/', $next) && !$this->isFieldLabel($next)) {
                return $this->cleanName($next);
            }
        }
        return null;
    }

    private function cleanName(string $name): string
    {
        // Hapus prefix yang sering muncul dari noise
        $name = preg_replace('/^[^A-Z]+/i', '', trim($name));
        // Ambil hanya huruf, spasi, titik, dan tanda hubung (untuk nama multi-kata)
        if (preg_match('/^([A-Z][A-Z\s\.\-]{1,60})/i', $name, $m)) {
            return trim($m[1]);
        }
        return trim($name);
    }

    // =========================================================================
    // Tempat & Tanggal Lahir
    // =========================================================================

    private function parseBirthPlace(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (!str_contains($line, 'LAHIR')) continue;

            $valuePart = $this->extractAfterLabel($line, ['TEMPAT/TGL LAHIR', 'TEMPAT / TGL LAHIR', 'TEMPAT/TGL', 'TGL LAHIR', 'LAHIR']);
            if ($valuePart) {
                return $this->extractCityFromBirthLine($valuePart);
            }
        }
        return null;
    }

    private function parseBirthDate(string $rawText): ?string
    {
        // Cari semua tanggal, ambil yang konteks-nya dekat dengan "LAHIR"
        $lines = explode("\n", strtoupper($rawText));
        foreach ($lines as $line) {
            if (!str_contains($line, 'LAHIR') && !str_contains($line, 'TGL')) continue;

            if (preg_match('/(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})/', $line, $m)) {
                $date = $this->buildDate($m[1], $m[2], $m[3]);
                if ($date) return $date;
            }
        }

        // Fallback: cari semua pola tanggal
        if (preg_match_all('/\b(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})\b/', $rawText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $date = $this->buildDate($match[1], $match[2], $match[3]);
                if ($date) return $date;
            }
        }
        return null;
    }

    private function extractCityFromBirthLine(string $line): ?string
    {
        // Format "JAKARTA, 18-07-1990"
        if (preg_match('/^([A-Z][A-Z\s]{1,40}),\s*\d{1,2}[-\/\.]/i', $line, $m)) {
            return trim($m[1]);
        }
        // Format "JAKARTA 18-07-1990"
        if (preg_match('/^([A-Z][A-Z\s]{1,40}?)\s+\d{1,2}[-\/\.]/i', $line, $m)) {
            return trim($m[1]);
        }
        // Hanya kota tanpa tanggal
        if (preg_match('/^([A-Z][A-Z\s]{2,30})$/', trim($line))) {
            return trim($line);
        }
        return null;
    }

    private function buildDate(string $d, string $m, string $y): ?string
    {
        $day = (int)$d; $month = (int)$m; $year = (int)$y;
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < 1900 || $year > (int)date('Y')) return null;
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    // =========================================================================
    // Jenis Kelamin
    // =========================================================================

    private function parseGender(string $upper): ?string
    {
        if (preg_match('/LAKI[-\s—–]+LAKI/i', $upper)) return 'male';
        if (str_contains($upper, 'PEREMPUAN')) return 'female';
        // Fallback singkatan
        if (preg_match('/KELAMIN\s*[:\-=>\|\.]+\s*([LP])\b/i', $upper, $m)) {
            return strtoupper($m[1]) === 'L' ? 'male' : 'female';
        }
        return null;
    }

    // =========================================================================
    // Alamat
    // =========================================================================

    /**
     * Parse alamat KTP.
     * Menggabungkan baris alamat utama + continuation + RT/RW (jika ditemukan).
     * RT/RW tidak lagi menjadi field terpisah — ikut di dalam address.
     *
     * Menangani variasi OCR:
     *   ALAMAT : JL. MERDEKA (dengan separator)
     *   ALAMAT   JL. MERDEKA (tanpa separator, hanya spasi)
     */
    private function parseAddress(array $lines): ?string
    {
        foreach ($lines as $i => $line) {
            // Separator opsional: bisa ada : - = > . atau tidak ada (hanya spasi)
            if (!preg_match('/^ALAMAT\s*(?:[:\-=>\|\.]\s*|\s+)(.*)/i', $line, $m)) continue;

            $value = trim($m[1]);

            // Kumpulkan baris lanjutan hingga bertemu label field baru berikutnya
            $j = $i + 1;
            $rtRwFound = null;
            while (isset($lines[$j])) {
                $nextLine = $lines[$j];

                // Jika baris ini adalah RT/RW, tangkap dan lanjutkan
                $rtRwCandidate = $this->extractRtRwFromLine($nextLine);
                if ($rtRwCandidate !== null) {
                    $rtRwFound = $rtRwCandidate;
                    $j++;
                    continue;
                }

                // Berhenti jika label field baru (kecuali RT/RW yang sudah ditangani)
                if ($this->isFieldLabel($nextLine)) break;

                $value .= ' ' . $nextLine;
                $j++;
            }

            // Jika RT/RW ada di baris ini sendiri (inline dengan alamat)
            if (!$rtRwFound) {
                $rtRwFound = $this->extractRtRwFromLine($value);
                if ($rtRwFound) {
                    // Bersihkan RT/RW dari value agar tidak duplikat
                    $value = preg_replace('/RT\.?\s*\d{1,3}\s*[\.\/]?\s*RW\.?\s*\d{1,3}/i', '', $value);
                    $value = preg_replace('/\b\d{3}\s*\/\s*\d{3}\b/', '', $value);
                }
            }

            // Jika RT/RW belum ditemukan, cari di baris-baris berikutnya (sampai max 3 baris)
            if (!$rtRwFound) {
                for ($k = $i + 1; $k <= $i + 3 && isset($lines[$k]); $k++) {
                    $rtRwFound = $this->extractRtRwFromLine($lines[$k]);
                    if ($rtRwFound) break;
                }
            }

            $value = $this->cleanFieldValue(trim($value));

            // Gabungkan RT/RW ke alamat dalam format "RT 12 RW 0" (tanpa leading zeros)
            if ($rtRwFound && strlen($value) >= 3) {
                [$rtNum, $rwNum] = explode('/', $rtRwFound);
                $rt = (string) ((int) $rtNum); // hapus leading zero: "012" → "12"
                $rw = (string) ((int) $rwNum); // hapus leading zero: "000" → "0"
                $value = $value . ' RT ' . $rt . ' RW ' . $rw;
            }

            return strlen($value) >= 3 ? $value : null;
        }
        return null;
    }

    /**
     * Ekstrak string RT/RW dari sebuah baris.
     * Return format "003/002" atau null jika tidak ada.
     */
    private function extractRtRwFromLine(string $line): ?string
    {
        // Format: RT/RW : 003/002 atau RT/RW - 003/002 atau RT/RW > 003/002 (OCR noise)
        if (preg_match('/RT\s*\/\s*RW\s*[:\-=\.>\|©]+\s*(\d{1,3})\s*\/\s*(\d{1,3})/i', $line, $m)) {
            return str_pad($m[1], 3, '0', STR_PAD_LEFT) . '/' . str_pad($m[2], 3, '0', STR_PAD_LEFT);
        }
        // Format: RT.007 RW.003 atau RT 007 RW 003
        if (preg_match('/RT\.?\s*(\d{1,3})\s+RW\.?\s*(\d{1,3})/i', $line, $m)) {
            return str_pad($m[1], 3, '0', STR_PAD_LEFT) . '/' . str_pad($m[2], 3, '0', STR_PAD_LEFT);
        }
        // Format slash bare: 007/003 (hanya jika baris hampir hanya berisi itu)
        $stripped = preg_replace('/[^\d\/]/', '', $line);
        if (preg_match('/^(\d{3})\/(\d{3})$/', trim($stripped), $m)) {
            return $m[1] . '/' . $m[2];
        }
        return null;
    }

    // =========================================================================
    // RT/RW — hanya digunakan internal oleh parseAddress()
    // (tidak lagi menjadi field publik di output parse())
    // =========================================================================
    // extractRtRwFromLine() sudah di-define di seksi parseAddress di atas.

    // =========================================================================
    // Kecamatan
    // =========================================================================

    private function parseDistrict(array $lines): ?string
    {
        $value = $this->parseFieldByLabel($lines, ['KECAMATAN']);
        return $value ? $this->cleanDistrictValue($value) : null;
    }

    private function cleanDistrictValue(string $val): string
    {
        // Ambil hanya bagian huruf valid (berhenti saat ketemu noise non-huruf)
        if (preg_match('/^([A-Z][A-Z\s]{1,40}?)(?:\s+[^A-Z\s].*)?$/u', trim($val), $m)) {
            return trim($m[1]);
        }
        // Ambil kata-kata pure huruf saja
        $words = array_filter(explode(' ', $val), fn($w) => preg_match('/^[A-Z]+$/i', $w));
        return implode(' ', array_slice($words, 0, 5));
    }

    // =========================================================================
    // Agama
    // =========================================================================

    private function parseReligion(array $lines): ?string
    {
        $value = $this->parseFieldByLabel($lines, ['AGAMA']);
        if (!$value) return null;

        $knownReligions = ['ISLAM', 'KRISTEN', 'PROTESTAN', 'KATOLIK', 'HINDU', 'BUDHA', 'BUDDHA', 'KONGHUCU'];
        foreach ($knownReligions as $r) {
            if (str_starts_with(strtoupper($value), $r)) return $r;
        }
        // Ambil kata pertama yang valid
        $first = explode(' ', trim($value))[0];
        return strlen($first) >= 3 ? strtoupper($first) : null;
    }

    // =========================================================================
    // Status Perkawinan
    // =========================================================================

    private function parseMaritalStatus(array $lines): ?string
    {
        $value = $this->parseFieldByLabel($lines, ['STATUS PERKAWINAN', 'STATUS PERKAWIN', 'PERKAWINAN', 'STATUS']);
        if (!$value) return null;

        $upper = strtoupper($value);
        $known = ['BELUM KAWIN', 'CERAI HIDUP', 'CERAI MATI', 'KAWIN'];
        foreach ($known as $k) {
            if (str_starts_with($upper, $k)) return $k;
        }
        // Coba regex
        if (preg_match('/^(BELUM KAWIN|CERAI HIDUP|CERAI MATI|KAWIN)/i', $upper, $m)) {
            return strtoupper($m[1]);
        }
        $words = explode(' ', trim($value));
        return implode(' ', array_slice($words, 0, 2));
    }

    // =========================================================================
    // Pekerjaan
    // =========================================================================

    private function parseOccupation(array $lines): ?string
    {
        $value = $this->parseFieldByLabel($lines, ['PEKERJAAN']);
        if (!$value) return null;

        // Ambil token kata, izinkan slash di tengah (PELAJAR/MAHASISWA, KARYAWAN/PEGAWAI)
        $words  = preg_split('/\s+/', trim($value));
        $result = [];
        foreach ($words as $word) {
            // Berhenti jika ada angka
            if (preg_match('/\d/', $word)) break;
            // Berhenti jika kata sangat pendek (<=2 char) dan sudah ada hasil
            if (count($result) > 0 && strlen($word) <= 2) break;
            // Izinkan huruf dan slash (PELAJAR/MAHASISWA)
            if (preg_match('/^[A-Z\/]+$/i', $word) && strlen($word) >= 2) {
                $result[] = strtoupper($word);
            } else {
                break;
            }
        }
        $occ = implode(' ', $result);
        return strlen($occ) >= 3 ? $occ : null;
    }

    // =========================================================================
    // Kewarganegaraan
    // =========================================================================

    private function parseNationality(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (!str_contains($line, 'KEWARGANEGARAAN')) continue;
            if (str_contains($line, 'WNA')) return 'WNA';
            $val = $this->extractAfterLabel($line, ['KEWARGANEGARAAN']);
            if ($val) {
                $first = trim(explode(' ', $val)[0]);
                return in_array($first, ['WNI', 'WNA']) ? $first : 'WNI';
            }
        }
        // Default WNI jika tidak ditemukan
        return 'WNI';
    }

    // =========================================================================
    // Positional Fallback
    // =========================================================================

    /**
     * Jika beberapa field null, coba pendekatan positional berdasarkan anchor NIK.
     * Layout KTP selalu sama: NIK → Nama → Lahir → JK → Alamat → dst.
     */
    private function applyPositionalFallback(array $lines, array $result): array
    {
        $nikIdx = $this->findLineIndex($lines, 'NIK');
        if ($nikIdx === null) return $result;

        // Indeks relatif dari NIK
        $nameIdx    = $nikIdx + 1; // Nama
        $birthIdx   = $nikIdx + 2; // Tempat/Tgl Lahir
        $genderIdx  = $nikIdx + 3; // Jenis Kelamin
        $addressIdx = $nikIdx + 4; // Alamat

        // Fallback Nama
        if ($result['name'] === null && isset($lines[$nameIdx])) {
            $candidate = $lines[$nameIdx];
            if (!$this->isFieldLabel($candidate) && !preg_match('/\d{5,}/', $candidate)) {
                $result['name'] = $this->cleanName($candidate);
            }
        }

        // Fallback Lahir
        if ($result['birth_date'] === null && isset($lines[$birthIdx])) {
            $line = $lines[$birthIdx];
            if (preg_match('/(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})/', $line, $m)) {
                $result['birth_date'] = $this->buildDate($m[1], $m[2], $m[3]);
            }
        }
        if ($result['birth_place'] === null && isset($lines[$birthIdx])) {
            $result['birth_place'] = $this->extractCityFromBirthLine($lines[$birthIdx]);
        }

        // Fallback Alamat (jika masih null)
        if ($result['address'] === null && isset($lines[$addressIdx])) {
            $candidate = $lines[$addressIdx];
            if (!$this->isFieldLabel($candidate) || str_contains(strtoupper($candidate), 'ALAMAT')) {
                $val = $this->extractAfterLabel($candidate, ['ALAMAT']) ?? $candidate;
                if (strlen(trim($val)) >= 5) {
                    $result['address'] = $this->cleanFieldValue($val);
                }
            }
        }

        return $result;
    }

    // =========================================================================
    // Generic helpers
    // =========================================================================

    /**
     * Parse field berdasarkan label.
     */
    private function parseFieldByLabel(array $lines, array $labels): ?string
    {
        foreach ($lines as $i => $line) {
            foreach ($labels as $label) {
                if (!str_contains($line, $label)) continue;

                $value = $this->extractAfterLabel($line, [$label]);
                if ($value && strlen(trim($value)) > 1) {
                    return $this->cleanFieldValue(trim($value));
                }

                // Coba baris berikutnya
                if (isset($lines[$i + 1])) {
                    $next = trim($lines[$i + 1]);
                    if (!$this->isFieldLabel($next) && strlen($next) > 1) {
                        return $this->cleanFieldValue($next);
                    }
                }
                break;
            }
        }
        return null;
    }

    /**
     * Ekstrak teks setelah label.
     */
    private function extractAfterLabel(string $line, array $labels): ?string
    {
        foreach ($labels as $label) {
            $pos = strpos($line, $label);
            if ($pos === false) continue;

            $remainder = substr($line, $pos + strlen($label));
            // Hanya strip separator standar — JANGAN strip titik (bisa bagian dari JL.)
            $remainder = ltrim($remainder, ' :;|=->~`');
            return strlen(trim($remainder)) > 0 ? trim($remainder) : null;
        }
        return null;
    }

    /**
     * Bersihkan noise OCR di ujung nilai.
     */
    private function cleanFieldValue(string $val): string
    {
        // Hapus noise di ujung — PERTAHANKAN titik (JL. AW.X) dan slash (PELAJAR/MAHASISWA)
        $val = rtrim($val, '"\'\'`~^_|{}[]<>\\#@!');
        // Hapus simbol noise tunggal di akhir
        $val = preg_replace('/\s+["\'\'`~^_|{}\[\]<>\\#@!]\s*$/i', '', trim($val));
        return trim($val);
    }

    /**
     * Cek apakah baris adalah label field KTP.
     */
    private function isFieldLabel(string $line): bool
    {
        $upper = strtoupper($line);
        foreach (self::FIELD_LABELS as $label) {
            if (str_contains($upper, $label)) return true;
        }
        return false;
    }

    /**
     * Normalisasi baris: uppercase, trim, hapus baris kosong.
     */
    private function normalizeLines(string $text): array
    {
        $lines = explode("\n", strtoupper($text));
        return array_values(array_filter(
            array_map('trim', $lines),
            fn($l) => strlen($l) > 0
        ));
    }

    /**
     * Cari index baris yang mengandung string tertentu.
     */
    private function findLineIndex(array $lines, string $needle): ?int
    {
        foreach ($lines as $i => $line) {
            if (str_contains($line, strtoupper($needle))) return $i;
        }
        return null;
    }
}
