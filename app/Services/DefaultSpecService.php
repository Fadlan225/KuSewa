<?php

namespace App\Services;

/**
 * DefaultSpecService
 *
 * Sumber kebenaran tunggal (single source of truth) untuk konfigurasi
 * default/baseline spesifikasi form setiap tipe aset KitaSewa.
 *
 * Digunakan oleh:
 *  - AssetTypeDefaultSpecSeeder  → mengisi DB saat fresh/re-seed
 *  - TemplateAsetController::resetFields() → endpoint "Kembalikan ke Default" di admin
 *
 * Source dokumen: database/data/Spesifikasi-Form-KitaSewa.md
 */
class DefaultSpecService
{
    /**
     * Kembalikan seluruh default spec untuk semua tipe aset.
     *
     * @return array<string, array{asset: array, unit: array}>
     *         Key = nama tipe aset, value = ['asset' => [...fields], 'unit' => [...fields]]
     */
    public static function all(): array
    {
        // ── Reusable field definitions ────────────────────────────────────────

        $fLuasBangunan = [
            'key' => 'luas_bangunan', 'label' => 'Luas Bangunan (m²)',
            'type' => 'number', 'required' => false,
        ];
        $fLuasTanah = [
            'key' => 'luas_tanah', 'label' => 'Luas Tanah (m²)',
            'type' => 'number', 'required' => false,
        ];
        $fJumlahLantai = [
            'key' => 'jumlah_lantai', 'label' => 'Jumlah Lantai',
            'type' => 'counter', 'required' => false,
        ];
        $fTahunDibangun = [
            'key' => 'tahun_dibangun', 'label' => 'Tahun Dibangun',
            'type' => 'number', 'required' => false,
        ];
        $fKapasitasTamu = [
            'key' => 'kapasitas_tamu', 'label' => 'Kapasitas Tamu',
            'type' => 'counter', 'required' => true,
        ];

        // Unit-level reusable fields
        $ufLuasKamar = [
            'key' => 'luas_kamar', 'label' => 'Luas Kamar (m²)',
            'type' => 'number', 'required' => true,
        ];
        $ufTipeKasurWajib = [
            'key' => 'tipe_kasur', 'label' => 'Tipe Kasur',
            'type' => 'select', 'required' => true,
            'options' => ['Single', 'Double', 'King', 'Queen', 'Twin'],
        ];
        $ufTipeKasurOpsional = [
            'key' => 'tipe_kasur', 'label' => 'Tipe Kasur',
            'type' => 'select', 'required' => false,
            'options' => ['Single', 'Double', 'King', 'Queen', 'Twin'],
        ];
        $ufKapasitasTamu = [
            'key' => 'kapasitas_tamu', 'label' => 'Kapasitas Tamu',
            'type' => 'counter', 'required' => true,
        ];

        return [

            // ── 1. Hotel ──────────────────────────────────────────────────────
            'Hotel' => [
                'asset' => [
                    ['key' => 'waktu_check_in',  'label' => 'Waktu Check In',  'type' => 'time', 'required' => true],
                    ['key' => 'waktu_check_out', 'label' => 'Waktu Check Out', 'type' => 'time', 'required' => true],
                    ['key' => 'bintang_hotel',   'label' => 'Bintang Hotel',   'type' => 'counter', 'required' => false],
                    $fJumlahLantai,
                    $fLuasBangunan,
                    $fLuasTanah,
                ],
                'unit' => [
                    $ufLuasKamar,
                    $ufTipeKasurWajib,
                ],
            ],

            // ── 2. Baliho ─────────────────────────────────────────────────────
            'Baliho' => [
                'asset' => [
                    ['key' => 'jenis_media',         'label' => 'Jenis Media',         'type' => 'select',    'required' => true,  'options' => ['Baliho', 'Billboard']],
                    ['key' => 'ukuran_media',         'label' => 'Ukuran Media',         'type' => 'room_size', 'required' => true,  'options' => ['4x6', '4x8', '5x10']],
                    ['key' => 'jumlah_sisi_tampil',   'label' => 'Jumlah Sisi Tampil',   'type' => 'select',    'required' => true,  'options' => ['1 Sisi', '2 Sisi']],
                    ['key' => 'orientasi_media',      'label' => 'Orientasi Media',      'type' => 'select',    'required' => true,  'options' => ['Horizontal', 'Vertikal']],
                ],
                'unit' => [],
            ],

            // ── 3. Villa ──────────────────────────────────────────────────────
            'Villa' => [
                'asset' => [
                    ['key' => 'jumlah_kamar_tidur', 'label' => 'Jumlah Kamar Tidur', 'type' => 'counter', 'required' => true],
                    ['key' => 'jumlah_kamar_mandi', 'label' => 'Jumlah Kamar Mandi', 'type' => 'counter', 'required' => true],
                    $fKapasitasTamu,
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fJumlahLantai,
                ],
                'unit' => [],
            ],

            // ── 4. Apartemen ──────────────────────────────────────────────────
            'Apartemen' => [
                'asset' => [
                    ['key' => 'waktu_check_in',  'label' => 'Waktu Check In',  'type' => 'time', 'required' => true],
                    ['key' => 'waktu_check_out', 'label' => 'Waktu Check Out', 'type' => 'time', 'required' => true],
                    $fJumlahLantai,
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fTahunDibangun,
                ],
                'unit' => [
                    [
                        'key' => 'tipe_unit', 'label' => 'Tipe Unit',
                        'type' => 'select', 'required' => true,
                        'options' => ['1-Room Studio', '1 BR', '2 BR', '3 BR', '4 BR', 'Lainnya'],
                        'option_configs' => [
                            'Lainnya' => [
                                'has_input'   => true,
                                'label'       => 'Keterangan Lainnya',
                                'type'        => 'text',
                                'placeholder' => 'Masukan Keterangan Lainnya...',
                            ],
                        ],
                    ],
                    ['key' => 'kondisi_furnitur',    'label' => 'Kondisi Furnitur',    'type' => 'radio',   'required' => true, 'options' => ['Not Furnished', 'Semi Furnished', 'Furnished']],
                    ['key' => 'luas_unit',           'label' => 'Luas Unit (m²)',      'type' => 'number',  'required' => true],
                    $ufTipeKasurWajib,
                    ['key' => 'jumlah_kamar_mandi',  'label' => 'Jumlah Kamar Mandi',  'type' => 'counter', 'required' => true],
                    ['key' => 'kapasitas_penghuni',  'label' => 'Kapasitas Penghuni',  'type' => 'counter', 'required' => true],
                ],
            ],

            // ── 5. Homestay ───────────────────────────────────────────────────
            'Homestay' => [
                'asset' => [
                    $fJumlahLantai,
                    $fLuasBangunan,
                    $fLuasTanah,
                ],
                'unit' => [
                    $ufLuasKamar,
                    $ufKapasitasTamu,
                    $ufTipeKasurOpsional,
                ],
            ],

            // ── 6. Guest House ────────────────────────────────────────────────
            'Guest House' => [
                'asset' => [
                    ['key' => 'luas_bangunan', 'label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => true],
                    $fLuasTanah,
                    $fJumlahLantai,
                    $fTahunDibangun,
                ],
                'unit' => [
                    $ufKapasitasTamu,
                    $ufLuasKamar,
                ],
            ],

            // ── 7. Kos ────────────────────────────────────────────────────────
            'Kos' => [
                'asset' => [
                    ['key' => 'penyewa_diperbolehkan',   'label' => 'Penyewa Diperbolehkan',   'type' => 'radio', 'required' => true,  'options' => ['Putra', 'Putri', 'Campur']],
                    ['key' => 'kriteria_calon_penyewa',  'label' => 'Kriteria Calon Penyewa',  'type' => 'radio', 'required' => false, 'options' => ['Khusus Mahasiswa', 'Khusus Karyawan', 'Bisa untuk semua']],
                ],
                'unit' => [
                    ['key' => 'luas_kamar',        'label' => 'Luas Kamar',        'type' => 'room_size', 'required' => true, 'options' => ['3x3', '3x4', '4x4']],
                    ['key' => 'kapasitas_penghuni', 'label' => 'Kapasitas Penghuni', 'type' => 'counter',  'required' => true],
                ],
            ],

            // ── 8. Kontrakan ──────────────────────────────────────────────────
            'Kontrakan' => [
                'asset' => [
                    ['key' => 'jumlah_kamar_tidur', 'label' => 'Jumlah Kamar Tidur', 'type' => 'counter', 'required' => true],
                    ['key' => 'jumlah_kamar_mandi', 'label' => 'Jumlah Kamar Mandi', 'type' => 'counter', 'required' => true],
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fJumlahLantai,
                ],
                'unit' => [],
            ],

            // ── 9. Ruko ───────────────────────────────────────────────────────
            'Ruko' => [
                'asset' => [
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fJumlahLantai,
                    $fTahunDibangun,
                    ['key' => 'jumlah_kamar_mandi', 'label' => 'Jumlah Kamar Mandi', 'type' => 'counter', 'required' => false],
                ],
                'unit' => [],
            ],

            // ── 10. Resort ────────────────────────────────────────────────────
            'Resort' => [
                'asset' => [
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fJumlahLantai,
                    $fTahunDibangun,
                ],
                'unit' => [
                    $ufLuasKamar,
                    $ufTipeKasurWajib,
                    $ufKapasitasTamu,
                ],
            ],

            // ── 11. Gudang ────────────────────────────────────────────────────
            'Gudang' => [
                'asset' => [
                    $fLuasBangunan,
                    $fLuasTanah,
                ],
                'unit' => [],
            ],

            // ── 12. Lahan ─────────────────────────────────────────────────────
            'Lahan' => [
                'asset' => [
                    ['key' => 'luas_tanah', 'label' => 'Luas Tanah (m²)', 'type' => 'number', 'required' => true],
                ],
                'unit' => [],
            ],

            // ── 13. Gedung ────────────────────────────────────────────────────
            'Gedung' => [
                'asset' => [
                    $fLuasBangunan,
                    $fLuasTanah,
                    $fKapasitasTamu,
                    $fJumlahLantai,
                ],
                'unit' => [],
            ],

            // ── 14. Aula ──────────────────────────────────────────────────────
            'Aula' => [
                'asset' => [
                    ['key' => 'luas_ruangan', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => true],
                    $fKapasitasTamu,
                ],
                'unit' => [],
            ],

            // ── 15. Ruang Meeting ─────────────────────────────────────────────
            'Ruang Meeting' => [
                'asset' => [
                    $fKapasitasTamu,
                    ['key' => 'luas_ruangan', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => false],
                ],
                'unit' => [],
            ],

            // ── 16. Studio ────────────────────────────────────────────────────
            'Studio' => [
                'asset' => [
                    ['key' => 'luas_bangunan', 'label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => true],
                ],
                'unit' => [
                    $ufKapasitasTamu,
                    ['key' => 'luas_ruangan', 'label' => 'Luas Ruangan (m²)', 'type' => 'number', 'required' => true],
                ],
            ],
        ];
    }

    /**
     * Ambil default spec untuk satu tipe aset berdasarkan nama.
     *
     * @param  string $name  Nama tipe aset (mis. 'Hotel', 'Villa')
     * @return array{asset: array, unit: array}|null  null jika tidak ditemukan
     */
    public static function forType(string $name): ?array
    {
        return static::all()[$name] ?? null;
    }
}
