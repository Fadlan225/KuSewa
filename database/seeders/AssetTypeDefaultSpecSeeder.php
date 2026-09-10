<?php

namespace Database\Seeders;

use App\Services\DefaultSpecService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AssetTypeDefaultSpecSeeder
 *
 * Menyimpan konfigurasi default/baseline `detail_fields` dan `unit_detail_fields`
 * untuk setiap tipe aset KitaSewa, sesuai Spesifikasi-Form-KitaSewa.md.
 *
 * Seeder ini:
 *  - Idempotent: aman dijalankan berulang kali
 *  - Hanya mengupdate kolom detail_fields & unit_detail_fields
 *  - Tidak mengubah kolom lain (allow_units, payment_countdown, dll)
 *  - Lookup berdasarkan name, tidak hardcode ID
 *
 * Source of truth: App\Services\DefaultSpecService
 */
class AssetTypeDefaultSpecSeeder extends Seeder
{
    public function run(): void
    {
        $specs    = DefaultSpecService::all();
        $updated  = 0;
        $notFound = [];

        foreach ($specs as $typeName => $fields) {
            $assetType = DB::table('asset_types')->where('name', $typeName)->first();

            if (!$assetType) {
                $notFound[] = $typeName;
                continue;
            }

            DB::table('asset_types')
                ->where('id', $assetType->id)
                ->update([
                    'detail_fields'      => json_encode(array_values($fields['asset'])),
                    'unit_detail_fields' => json_encode(array_values($fields['unit'])),
                    'updated_at'         => now(),
                ]);

            $updated++;
        }

        $this->command->info("✓ {$updated} dari " . count($specs) . " tipe aset berhasil diupdate spesifikasinya.");

        if (!empty($notFound)) {
            $this->command->warn('⚠ Tipe aset tidak ditemukan: ' . implode(', ', $notFound));
            $this->command->warn('  Pastikan AssetTypeSeeder sudah dijalankan lebih dulu.');
        }
    }
}
