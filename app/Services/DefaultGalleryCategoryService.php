<?php

namespace App\Services;

/**
 * Konfigurasi default kategori galeri per tipe aset.
 * Digunakan oleh seeder dan fitur "Reset ke Default".
 *
 * Format setiap entry:
 *   ['name' => string, 'is_mandatory' => bool]
 *
 * Catatan:
 * - 'Sampul Utama' selalu ditambahkan otomatis di urutan pertama (is_mandatory: true).
 * - 'Lainnya' selalu ditambahkan otomatis di urutan terakhir (is_mandatory: false).
 * - Kategori di sini tidak perlu menyebut keduanya.
 * - is_mandatory: true  → Wajib diunggah oleh pemilik aset.
 * - is_mandatory: false → Opsional (tampil sebagai pilihan, tidak diwajibkan).
 */
class DefaultGalleryCategoryService
{
    /**
     * Kembalikan semua nama kategori galeri yang dibutuhkan (global).
     * Dipakai untuk memastikan data ada di tabel galery_categories.
     */
    public static function allCategoryNames(): array
    {
        return array_unique(array_merge(
            // System categories
            ['Sampul Utama', 'Lainnya'],
            // Shared categories
            [
                'Eksterior', 'Interior',
                'Lobi & Resepsionis', 'Fasilitas Umum', 'Kolam Renang',
                'Area Parkir', 'Taman & Area Terbuka', 'Lingkungan Sekitar',
                'Ruang Makan', 'Pemandangan', 'Ruang Utama',
                'Kamar Tidur', 'Kamar Mandi', 'Balkon & Teras', 'Ballroom',
                'Tampak Depan', 'Tampak Samping', 'Detail Baliho', 'Lokasi',
                'Akses & Visibilitas', 'Struktur & Tiang', 'Pencahayaan Malam Hari',
                'Taman & Halaman', 'Area Bersantai', 'Fasilitas',
                'Pusat Kebugaran', 'Lobi', 'Denah Unit', 'Teras',
                'Halaman & Taman', 'Ruang Tamu', 'Dapur', 'Balkon',
                'Ruang Bersama', 'Area Cuci', 'Teras & Halaman',
                'Keamanan', 'Dapur Bersama', 'Depan Kamar',
                'Halaman', 'Fasad & Papan Nama', 'Akses Jalan',
                'Ruang Belakang', 'Ruang Penyimpanan', 'Lantai Atas',
                'Area Bongkar Muat', 'Akses Kendaraan', 'Ruang Gudang',
                'Area Penyimpanan', 'Ruang Kantor', 'Area Loading',
                'Tampak Lahan', 'Batas Lahan', 'Kondisi Lahan',
                'Fasilitas & Infrastruktur', 'Area Publik', 'Ruang Kerja', 'Koridor',
                'Ruang Aula', 'Panggung', 'Area Tamu', 'Ruang Persiapan',
                'Ruang Meeting', 'Area Tunggu', 'Ruang Resepsionis',
                'Area Presentasi', 'Ruang Diskusi', 'Area Produksi',
                'Ruang Studio', 'Ruang Kontrol',
            ]
        ));
    }

    /**
     * Kembalikan konfigurasi default per nama tipe aset.
     *
     * @param string $assetTypeName Nama tipe aset (case-insensitive match)
     * @return array{asset: array, unit: array}|null
     */
    public static function forType(string $assetTypeName): ?array
    {
        $map = static::defaultMap();
        $key = mb_strtolower(trim($assetTypeName));

        foreach ($map as $typeName => $config) {
            if (mb_strtolower($typeName) === $key) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Map lengkap semua tipe aset → konfigurasi default galeri.
     */
    public static function defaultMap(): array
    {
        return [

            'Hotel' => [
                'asset' => [
                    ['name' => 'Eksterior',          'is_mandatory' => true],
                    ['name' => 'Interior',            'is_mandatory' => true],
                    ['name' => 'Lobi & Resepsionis',  'is_mandatory' => false],
                    ['name' => 'Fasilitas Umum',      'is_mandatory' => false],
                    ['name' => 'Kolam Renang',        'is_mandatory' => false],
                    ['name' => 'Area Parkir',         'is_mandatory' => false],
                    ['name' => 'Taman & Area Terbuka','is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar',  'is_mandatory' => false],
                    ['name' => 'Ruang Makan',         'is_mandatory' => false],
                    ['name' => 'Pemandangan',         'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Utama',   'is_mandatory' => true],
                    ['name' => 'Kamar Tidur',   'is_mandatory' => true],
                    ['name' => 'Kamar Mandi',   'is_mandatory' => true],
                    ['name' => 'Balkon & Teras','is_mandatory' => false],
                    ['name' => 'Ballroom',      'is_mandatory' => false],
                ],
            ],

            'Baliho' => [
                'asset' => [
                    ['name' => 'Tampak Depan',          'is_mandatory' => true],
                    ['name' => 'Tampak Samping',         'is_mandatory' => true],
                    ['name' => 'Detail Baliho',          'is_mandatory' => true],
                    ['name' => 'Lokasi',                 'is_mandatory' => true],
                    ['name' => 'Lingkungan Sekitar',     'is_mandatory' => true],
                    ['name' => 'Akses & Visibilitas',    'is_mandatory' => true],
                    ['name' => 'Struktur & Tiang',       'is_mandatory' => true],
                    ['name' => 'Pencahayaan Malam Hari', 'is_mandatory' => true],
                ],
                'unit' => [],
            ],

            'Villa' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Kolam Renang',       'is_mandatory' => false],
                    ['name' => 'Taman & Halaman',    'is_mandatory' => false],
                    ['name' => 'Area Bersantai',     'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                    ['name' => 'Pemandangan',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',     'is_mandatory' => false],
                    ['name' => 'Kamar Tidur',    'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',    'is_mandatory' => false],
                    ['name' => 'Dapur',          'is_mandatory' => false],
                    ['name' => 'Ruang Makan',    'is_mandatory' => false],
                    ['name' => 'Balkon & Teras', 'is_mandatory' => false],
                    ['name' => 'Pemandangan',    'is_mandatory' => false],
                ],
            ],

            'Apartemen' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Kolam Renang',       'is_mandatory' => false],
                    ['name' => 'Pusat Kebugaran',    'is_mandatory' => false],
                    ['name' => 'Lobi',               'is_mandatory' => true],
                    ['name' => 'Pemandangan',        'is_mandatory' => true],
                    ['name' => 'Denah Unit',         'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',  'is_mandatory' => true],
                    ['name' => 'Kamar Tidur', 'is_mandatory' => true],
                    ['name' => 'Kamar Mandi', 'is_mandatory' => true],
                    ['name' => 'Dapur',       'is_mandatory' => false],
                    ['name' => 'Balkon',      'is_mandatory' => false],
                    ['name' => 'Fasilitas',   'is_mandatory' => true],
                ],
            ],

            'Homestay' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Teras',              'is_mandatory' => false],
                    ['name' => 'Halaman & Taman',   'is_mandatory' => false],
                    ['name' => 'Pemandangan',        'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',  'is_mandatory' => true],
                    ['name' => 'Kamar Tidur', 'is_mandatory' => true],
                    ['name' => 'Kamar Mandi', 'is_mandatory' => true],
                    ['name' => 'Ruang Makan', 'is_mandatory' => false],
                    ['name' => 'Dapur',       'is_mandatory' => false],
                    ['name' => 'Fasilitas',   'is_mandatory' => false],
                ],
            ],

            'Guest House' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Teras',              'is_mandatory' => false],
                    ['name' => 'Halaman & Taman',   'is_mandatory' => false],
                    ['name' => 'Pemandangan',        'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Ruang Bersama',      'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',  'is_mandatory' => true],
                    ['name' => 'Kamar Tidur', 'is_mandatory' => true],
                    ['name' => 'Kamar Mandi', 'is_mandatory' => true],
                    ['name' => 'Ruang Makan', 'is_mandatory' => false],
                    ['name' => 'Dapur',       'is_mandatory' => false],
                    ['name' => 'Fasilitas',   'is_mandatory' => false],
                ],
            ],

            'Kos' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Ruang Bersama',      'is_mandatory' => false],
                    ['name' => 'Area Cuci',          'is_mandatory' => false],
                    ['name' => 'Teras & Halaman',    'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Keamanan',           'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Dapur Bersama',      'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Kamar Tidur',  'is_mandatory' => true],
                    ['name' => 'Depan Kamar',  'is_mandatory' => true],
                    ['name' => 'Kamar Mandi',  'is_mandatory' => true],
                    ['name' => 'Fasilitas',    'is_mandatory' => false],
                ],
            ],

            'Resort' => [
                'asset' => [
                    ['name' => 'Eksterior',          'is_mandatory' => true],
                    ['name' => 'Interior',            'is_mandatory' => true],
                    ['name' => 'Lobi & Resepsionis',  'is_mandatory' => false],
                    ['name' => 'Fasilitas',           'is_mandatory' => false],
                    ['name' => 'Kolam Renang',        'is_mandatory' => false],
                    ['name' => 'Taman & Area Terbuka','is_mandatory' => false],
                    ['name' => 'Area Parkir',         'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar',  'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',  'is_mandatory' => true],
                    ['name' => 'Kamar Tidur', 'is_mandatory' => true],
                    ['name' => 'Kamar Mandi', 'is_mandatory' => true],
                    ['name' => 'Ruang Makan', 'is_mandatory' => false],
                    ['name' => 'Dapur',       'is_mandatory' => false],
                    ['name' => 'Fasilitas',   'is_mandatory' => false],
                ],
            ],

            'Kontrakan' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Halaman',            'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Tamu',  'is_mandatory' => true],
                    ['name' => 'Kamar Tidur', 'is_mandatory' => true],
                    ['name' => 'Kamar Mandi', 'is_mandatory' => true],
                    ['name' => 'Dapur',       'is_mandatory' => false],
                    ['name' => 'Ruang Makan', 'is_mandatory' => false],
                    ['name' => 'Fasilitas',   'is_mandatory' => false],
                ],
            ],

            'Ruko' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Fasad & Papan Nama', 'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Akses Jalan',        'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Utama',       'is_mandatory' => true],
                    ['name' => 'Ruang Belakang',    'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',       'is_mandatory' => true],
                    ['name' => 'Ruang Penyimpanan', 'is_mandatory' => false],
                    ['name' => 'Lantai Atas',       'is_mandatory' => false],
                    ['name' => 'Fasilitas',         'is_mandatory' => false],
                ],
            ],

            'Gudang' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Area Bongkar Muat', 'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Akses Kendaraan',   'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Gudang',      'is_mandatory' => true],
                    ['name' => 'Area Penyimpanan',  'is_mandatory' => true],
                    ['name' => 'Ruang Kantor',      'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',       'is_mandatory' => true],
                    ['name' => 'Area Loading',      'is_mandatory' => false],
                    ['name' => 'Fasilitas',         'is_mandatory' => false],
                ],
            ],

            'Lahan' => [
                'asset' => [
                    ['name' => 'Tampak Lahan',              'is_mandatory' => true],
                    ['name' => 'Batas Lahan',               'is_mandatory' => true],
                    ['name' => 'Akses Jalan',               'is_mandatory' => true],
                    ['name' => 'Kondisi Lahan',             'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar',        'is_mandatory' => false],
                    ['name' => 'Fasilitas & Infrastruktur', 'is_mandatory' => false],
                    ['name' => 'Pemandangan',               'is_mandatory' => false],
                ],
                'unit' => [],
            ],

            'Gedung' => [
                'asset' => [
                    ['name' => 'Eksterior',          'is_mandatory' => true],
                    ['name' => 'Interior',            'is_mandatory' => true],
                    ['name' => 'Lobi & Resepsionis',  'is_mandatory' => false],
                    ['name' => 'Area Publik',         'is_mandatory' => false],
                    ['name' => 'Area Parkir',         'is_mandatory' => false],
                    ['name' => 'Fasilitas',           'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar',  'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Utama',       'is_mandatory' => true],
                    ['name' => 'Ruang Kerja',       'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',       'is_mandatory' => true],
                    ['name' => 'Ruang Penyimpanan', 'is_mandatory' => false],
                    ['name' => 'Koridor',           'is_mandatory' => false],
                    ['name' => 'Fasilitas',         'is_mandatory' => false],
                ],
            ],

            'Aula' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Ruang Utama',        'is_mandatory' => true],
                    ['name' => 'Panggung',           'is_mandatory' => false],
                    ['name' => 'Area Tamu',          'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Aula',      'is_mandatory' => true],
                    ['name' => 'Panggung',        'is_mandatory' => true],
                    ['name' => 'Ruang Persiapan', 'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',     'is_mandatory' => true],
                    ['name' => 'Fasilitas',       'is_mandatory' => false],
                ],
            ],

            'Ruang Meeting' => [
                'asset' => [
                    ['name' => 'Eksterior',          'is_mandatory' => true],
                    ['name' => 'Interior',            'is_mandatory' => true],
                    ['name' => 'Ruang Meeting',       'is_mandatory' => true],
                    ['name' => 'Area Tunggu',         'is_mandatory' => false],
                    ['name' => 'Ruang Resepsionis',   'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',         'is_mandatory' => false],
                    ['name' => 'Fasilitas',           'is_mandatory' => false],
                    ['name' => 'Area Parkir',         'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Meeting',    'is_mandatory' => true],
                    ['name' => 'Area Presentasi',  'is_mandatory' => true],
                    ['name' => 'Ruang Diskusi',    'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',      'is_mandatory' => true],
                    ['name' => 'Fasilitas',        'is_mandatory' => false],
                ],
            ],

            'Studio' => [
                'asset' => [
                    ['name' => 'Eksterior',         'is_mandatory' => true],
                    ['name' => 'Interior',           'is_mandatory' => true],
                    ['name' => 'Area Produksi',      'is_mandatory' => true],
                    ['name' => 'Area Tunggu',        'is_mandatory' => false],
                    ['name' => 'Ruang Penyimpanan',  'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',        'is_mandatory' => false],
                    ['name' => 'Fasilitas',          'is_mandatory' => false],
                    ['name' => 'Area Parkir',        'is_mandatory' => false],
                    ['name' => 'Lingkungan Sekitar', 'is_mandatory' => false],
                ],
                'unit' => [
                    ['name' => 'Ruang Studio',      'is_mandatory' => true],
                    ['name' => 'Area Produksi',     'is_mandatory' => true],
                    ['name' => 'Ruang Kontrol',     'is_mandatory' => false],
                    ['name' => 'Ruang Penyimpanan', 'is_mandatory' => false],
                    ['name' => 'Kamar Mandi',       'is_mandatory' => true],
                    ['name' => 'Fasilitas',         'is_mandatory' => false],
                ],
            ],

        ];
    }
}
