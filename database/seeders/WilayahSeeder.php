<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/wilayah.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan: {$csvPath}");
            return;
        }

        // 1. Membuka file CSV untuk dibaca
        $file = fopen($csvPath, 'r');
        
        if ($file === false) {
            $this->command->error("Gagal membuka file CSV: {$csvPath}");
            return;
        }

        // 2. Melewati baris pertama (header) agar tidak masuk ke database
        fgetcsv($file);

        // 7. Menyiapkan penampung array per level
        $provinces = [];
        $cities = [];
        $districts = [];
        $villages = [];

        // Membaca baris demi baris hingga akhir file
        while (($row = fgetcsv($file)) !== false) {
            // 11. Validasi sederhana: pastikan baris memiliki minimal 2 kolom dan tidak kosong
            if (empty($row) || count($row) < 2) {
                continue;
            }

            // 3. Menggunakan trim() untuk membersihkan whitespace di awal dan akhir
            $rawCode = trim($row[0]);
            $name = trim($row[1]);

            // Jika kosong setelah di trim, lewati
            if ($rawCode === '' || $name === '') {
                continue;
            }

            // 4. Menghitung level berdasarkan jumlah titik
            $level = substr_count($rawCode, '.');

            // 5. Menghapus tanda titik untuk mendapatkan clean code
            $cleanCode = str_replace('.', '', $rawCode);

            // 6. Memasukkan data ke dalam array sesuai level wilayah
            if ($level === 0) { // Provinsi
                $provinces[] = [
                    'code' => $cleanCode,
                    'name' => $name,
                ];
            } elseif ($level === 1) { // Kabupaten / Kota
                $cities[] = [
                    'code' => $cleanCode,
                    'province_code' => substr($cleanCode, 0, 2),
                    'name' => $name,
                ];
            } elseif ($level === 2) { // Kecamatan
                $districts[] = [
                    'code' => $cleanCode,
                    'city_code' => substr($cleanCode, 0, 4),
                    'name' => $name,
                ];
            } elseif ($level === 3) { // Desa / Kelurahan
                $villages[] = [
                    'code' => $cleanCode,
                    'district_code' => substr($cleanCode, 0, 6),
                    'name' => $name,
                ];
            } else {
                Log::warning("Format kode wilayah tidak dikenali (level tidak valid): {$rawCode} - {$name}");
            }
        }

        // Tutup file resource
        fclose($file);

        $this->command->info('Memulai insert/upsert data wilayah (Provinces, Cities, Districts, Villages)...');

        // 10. Menggunakan DB::transaction(function() {...}) agar otomatis rollback jika gagal
        DB::transaction(function () use ($provinces, $cities, $districts, $villages) {
            
            // Helper function untuk upsert chunked array
            // 13. Seeder dibuat idempotent menggunakan upsert, data tidak akan double jika di-run berulang.
            // 8, 9, 14, 15. Gunakan DB::table, array_chunk per 1000, performa optimal tanpa Eloquent.
            $insertChunks = function ($tableName, $dataArray, $uniqueCols, $updateCols) {
                foreach (array_chunk($dataArray, 1000) as $chunk) {
                    DB::table($tableName)->upsert($chunk, $uniqueCols, $updateCols);
                }
            };

            // Insert Provinces
            $insertChunks('provinces', $provinces, ['code'], ['name']);
            $this->command->info('Berhasil import ' . count($provinces) . ' provinsi.');

            // Insert Cities
            $insertChunks('cities', $cities, ['code'], ['province_code', 'name']);
            $this->command->info('Berhasil import ' . count($cities) . ' kabupaten/kota.');

            // Insert Districts
            $insertChunks('districts', $districts, ['code'], ['city_code', 'name']);
            $this->command->info('Berhasil import ' . count($districts) . ' kecamatan.');

            // Insert Villages
            $insertChunks('villages', $villages, ['code'], ['district_code', 'name']);
            $this->command->info('Berhasil import ' . count($villages) . ' desa/kelurahan.');
            
        });

        $this->command->info('Semua data wilayah berhasil diimport.');
    }
}
