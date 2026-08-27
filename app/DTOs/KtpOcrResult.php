<?php

namespace App\DTOs;

class KtpOcrResult
{
    public function __construct(
        public readonly string $status,       // 'success' | 'partial' | 'failed'
        public readonly array  $data,         // structured fields (null untuk field gagal)
        public readonly array  $errors,       // validation errors per field
        public readonly bool   $partial,      // true jika sebagian field berhasil
        public readonly string $ktpPhotoPath, // private storage path file KTP
        public readonly ?string $rawText = null, // raw OCR text (tidak disimpan ke DB)
    ) {}

    public function toArray(): array
    {
        return [
            'status'        => $this->status,
            'data'          => $this->data,
            'errors'        => $this->errors,
            'partial'       => $this->partial,
            'ktp_photo_path' => $this->ktpPhotoPath,
        ];
    }

    public static function failed(string $ktpPhotoPath, array $errors = []): self
    {
        return new self(
            status: 'failed',
            data: [],
            errors: $errors,
            partial: false,
            ktpPhotoPath: $ktpPhotoPath,
        );
    }
}
