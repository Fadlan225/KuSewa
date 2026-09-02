<?php

$type = \App\Models\asset_type::where('name', 'Kos')->first();
$type->detail_fields = [
    [
        'key' => 'tipe_penyewa',
        'type' => 'radio_tipe_penyewa',
        'label' => 'Tipe Penyewa Diizinkan',
        'options' => ['Putra', 'Putri', 'Campur'],
        'required' => true
    ],
    [
        'key' => 'kriteria_penyewa',
        'type' => 'radio_kriteria_penyewa',
        'label' => 'Kriteria Calon Penyewa',
        'options' => ['Khusus Karyawan', 'Khusus Mahasiswa', 'Bisa untuk Semua'],
        'required' => false
    ]
];
$type->unit_detail_fields = [
    [
        'key' => 'kapasitas_penghuni',
        'type' => 'kapasitas_penghuni',
        'label' => 'Kapasitas Penghuni',
        'required' => true
    ],
    [
        'key' => 'ukuran_kamar',
        'type' => 'room_size',
        'label' => 'Ukuran Kamar',
        'required' => true
    ]
];
$type->save();
echo "Done\n";
