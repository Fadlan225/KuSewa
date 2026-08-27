<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tesseract OCR Binary Path
    |--------------------------------------------------------------------------
    | Path ke executable Tesseract.
    | Linux/Ubuntu: /usr/bin/tesseract
    | Windows (dev): C:\Program Files\Tesseract-OCR\tesseract.exe
    | atau biarkan 'tesseract' jika sudah di PATH
    */
    'tesseract_binary' => env('TESSERACT_BINARY', '/usr/bin/tesseract'),

    /*
    |--------------------------------------------------------------------------
    | Tesseract Language
    |--------------------------------------------------------------------------
    | Gunakan 'ind' untuk KTP Indonesia.
    | Fallback otomatis ke 'eng' jika ind tidak tersedia.
    | Install di Ubuntu: sudo apt-get install tesseract-ocr-ind
    */
    'tesseract_lang' => env('TESSERACT_LANG', 'ind'),

    /*
    |--------------------------------------------------------------------------
    | OCR Timeout (detik)
    |--------------------------------------------------------------------------
    | Batas waktu proses OCR. Disesuaikan dengan RAM VPS.
    */
    'timeout' => (int) env('OCR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Concurrency
    |--------------------------------------------------------------------------
    | Untuk VPS RAM 1GB, batasi 1 proses OCR bersamaan.
    | Dikontrol via queue worker: php artisan queue:work --queue=ocr
    */
    'max_concurrency' => (int) env('OCR_MAX_CONCURRENCY', 1),
];
