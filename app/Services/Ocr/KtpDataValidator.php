<?php

namespace App\Services\Ocr;

class KtpDataValidator
{
    /**
     * Validasi structured data hasil parsing OCR.
     * Return array of error messages per field (kosong = valid).
     */
    public function validate(array $data): array
    {
        $errors = [];

        // NIK: 16 digit, hanya angka
        if (!empty($data['nik'])) {
            if (!preg_match('/^\d{16}$/', $data['nik'])) {
                $errors['nik'] = 'NIK harus berupa 16 digit angka.';
            }
        }

        // Tanggal lahir: valid date, tidak di masa depan, tidak terlalu jauh
        if (!empty($data['birth_date'])) {
            $date = date_create($data['birth_date']);
            if (!$date) {
                $errors['birth_date'] = 'Tanggal lahir tidak valid.';
            } else {
                $year = (int) $date->format('Y');
                if ($date > new \DateTime()) {
                    $errors['birth_date'] = 'Tanggal lahir tidak boleh di masa depan.';
                } elseif ($year < 1900) {
                    $errors['birth_date'] = 'Tanggal lahir tidak valid.';
                }
            }
        }

        // Gender: hanya male/female/null
        if (!empty($data['gender']) && !in_array($data['gender'], ['male', 'female'])) {
            $errors['gender'] = 'Jenis kelamin tidak dikenali.';
        }

        // NIK: tidak boleh huruf sama sekali
        if (!empty($data['nik']) && preg_match('/[a-zA-Z]/', $data['nik'])) {
            $errors['nik'] = 'NIK mengandung karakter tidak valid.';
        }

        return $errors;
    }

    /**
     * Cek apakah setidaknya field utama berhasil dibaca.
     * Field utama: nik, name, gender, birth_date.
     */
    public function hasMinimumData(array $data): bool
    {
        $primaryFields = ['nik', 'name', 'birth_date', 'gender'];
        $filled = 0;

        foreach ($primaryFields as $field) {
            if (!empty($data[$field])) {
                $filled++;
            }
        }

        return $filled >= 2; // setidaknya 2 field utama berhasil
    }

    /**
     * Hitung jumlah field yang berhasil dibaca.
     */
    public function countFilledFields(array $data): int
    {
        return count(array_filter($data, fn($v) => $v !== null && $v !== ''));
    }
}
