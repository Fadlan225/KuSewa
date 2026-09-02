<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PolicyTemplateSeeder extends Seeder
{
    /**
     * Seed template kebijakan untuk tiap tipe aset.
     *
     * asset_type_id:
     *  6 = Kos
     *  4 = Homestay
     *  5 = Guest House
     */
    public function run(): void
    {
        DB::table('policy_templates')->truncate();

        $kosTypeId = 6;

        $templates = [
            // ── PERSYARATAN DAN DOKUMEN ─────────────────────────────────────
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'allow_children',
                'label'         => 'Boleh bawa anak',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 1,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'require_family_card',
                'label'         => 'Wajib sertakan kartu keluarga saat pengajuan sewa',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_children',   // hanya muncul jika allow_children aktif
                'sort_order'    => 2,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'allow_couple',
                'label'         => 'Boleh untuk pasutri',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 3,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'require_marriage_book',
                'label'         => 'Wajib sertakan buku nikah saat pengajuan sewa',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_couple',     // hanya muncul jika allow_couple aktif
                'sort_order'    => 4,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'require_ktp',
                'label'         => 'Wajib sertakan KTP saat pengajuan sewa',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 5,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'persyaratan',
                'group_label'   => 'Persyaratan dan Dokumen',
                'key'           => 'allow_pets',
                'label'         => 'Boleh bawa hewan peliharaan',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 6,
            ],

            // ── AKSES JAM MALAM ─────────────────────────────────────────────
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'akses',
                'group_label'   => 'Akses Jam Malam',
                'key'           => 'night_access',
                'label'         => 'Akses 24 jam',
                'input_type'    => 'radio',
                'radio_group'   => 'night_access',
                'radio_value'   => '24jam',
                'parent_key'    => null,
                'sort_order'    => 1,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'akses',
                'group_label'   => 'Akses Jam Malam',
                'key'           => 'night_access',
                'label'         => 'Ada jam malam',
                'input_type'    => 'radio',
                'radio_group'   => 'night_access',
                'radio_value'   => 'ada_jam_malam',
                'parent_key'    => null,
                'sort_order'    => 2,
            ],

            // ── LARANGAN PENYEWA ────────────────────────────────────────────
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'larangan',
                'group_label'   => 'Larangan Penyewa',
                'key'           => 'no_smoking',
                'label'         => 'Dilarang merokok di dalam kamar/unit',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 1,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'larangan',
                'group_label'   => 'Larangan Penyewa',
                'key'           => 'no_alcohol',
                'label'         => 'Dilarang membawa/mengonsumsi alkohol',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 2,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'larangan',
                'group_label'   => 'Larangan Penyewa',
                'key'           => 'no_drugs',
                'label'         => 'Dilarang membawa narkoba atau barang terlarang',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 3,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'larangan',
                'group_label'   => 'Larangan Penyewa',
                'key'           => 'no_loud_music',
                'label'         => 'Dilarang memainkan musik keras malam hari',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 4,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'larangan',
                'group_label'   => 'Larangan Penyewa',
                'key'           => 'no_cooking',
                'label'         => 'Dilarang memasak di dalam kamar',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 5,
            ],

            // ── PERATURAN TAMU ──────────────────────────────────────────────
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'tamu',
                'group_label'   => 'Peraturan Tamu',
                'key'           => 'allow_guests',
                'label'         => 'Boleh menerima tamu?',
                'input_type'    => 'toggle',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => null,
                'sort_order'    => 1,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'tamu',
                'group_label'   => 'Peraturan Tamu',
                'key'           => 'no_opposite_gender_guest',
                'label'         => 'Dilarang membawa tamu lawan jenis',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_guests',     // hanya muncul jika allow_guests aktif
                'sort_order'    => 2,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'tamu',
                'group_label'   => 'Peraturan Tamu',
                'key'           => 'guest_overnight_fee',
                'label'         => 'Tamu menginap dikenakan biaya',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_guests',
                'sort_order'    => 3,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'tamu',
                'group_label'   => 'Peraturan Tamu',
                'key'           => 'guest_can_overnight',
                'label'         => 'Tamu boleh menginap',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_guests',
                'sort_order'    => 4,
            ],
            [
                'asset_type_id' => $kosTypeId,
                'group_key'     => 'tamu',
                'group_label'   => 'Peraturan Tamu',
                'key'           => 'guest_night_curfew',
                'label'         => 'Ada jam malam untuk tamu',
                'input_type'    => 'checkbox',
                'radio_group'   => null,
                'radio_value'   => null,
                'parent_key'    => 'allow_guests',
                'sort_order'    => 5,
            ],
        ];

        DB::table('policy_templates')->insert(
            array_map(fn($t) => array_merge($t, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $templates)
        );
    }
}
