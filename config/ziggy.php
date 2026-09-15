<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ziggy Route Groups
    |--------------------------------------------------------------------------
    | Batasi route yang di-expose ke frontend agar payload @routes lebih kecil.
    | Route admin tidak perlu di-expose ke halaman publik/penyewa.
    |
    | Dokumentasi: https://github.com/tighten/ziggy#filtering-routes
    */

    // Route yang TIDAK di-expose ke frontend (halaman publik & penyewa)
    // Admin routes dikecualikan — mereka tetap bisa akses via DashboardLayout
    // yang menggunakan @routes('admin.*') secara terpisah
    'except' => [
        'admin.*',
        'ktp-photo',
    ],
];
