<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SearchLogSeeder extends Seeder
{
    /**
     * 10 search log per customer × 99 = 990 logs.
     * Kata kunci manusiawi dalam bahasa Indonesia.
     */
    private array $keywords = [
        // Hunian
        'rumah sewa samarinda', 'villa murah balikpapan', 'apartemen harian jakarta',
        'kos dekat kampus', 'homestay bintang', 'hotel budget bontang',
        'kontrakan 2 kamar', 'guest house tenggarong', 'villa pantai',
        'rumah kost murah', 'apartemen furnished', 'villa kolam renang',
        // Komersial
        'ruko strategis samarinda', 'toko konveksi', 'kios pasar murah',
        'gudang sewa logistik', 'ruko 2 lantai', 'kios mall balikpapan',
        'gudang cold storage', 'toko pinggir jalan',
        // Lahan
        'lahan kosong strategis', 'tanah parkir kota', 'lahan pertanian sawah',
        'lahan industri', 'tanah kavling murah',
        // Event
        'gedung pernikahan', 'aula seminar', 'ruang meeting kantor',
        'studio foto murah', 'gedung konvensi', 'aula olahraga',
        'studio podcast', 'ruang meeting harian',
        // Media Iklan
        'baliho digital led', 'billboard pinggir jalan', 'baliho konvensional murah',
        'papan iklan strategis', 'reklame digital kota',
        // Lokasi spesifik
        'sewa aset samarinda', 'properti balikpapan', 'aset bontang murah',
        'properti kalimantan', 'sewa tempat acara', 'sewa gedung event',
        'aset komersial murah', 'properti investasi',
        // Umum
        'sewa murah', 'properti terjangkau', 'tempat strategis',
        'aset premium', 'sewa jangka panjang', 'sewa bulanan',
    ];

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('search_logs')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $customers = DB::table('users')
            ->where('email', 'like', 'customer%@kusewa.com')
            ->orderBy('id')
            ->get();

        if ($customers->isEmpty()) {
            $this->command->error('Customers belum ada.');
            return;
        }

        $totalKeywords = count($this->keywords);
        $logsPerCustomer = 10;
        $batch = [];

        foreach ($customers as $custIdx => $customer) {
            for ($l = 0; $l < $logsPerCustomer; $l++) {
                // Pilih keyword yang variatif per customer
                $kwIdx = ($custIdx * 3 + $l * 7 + $l) % $totalKeywords;

                // Waktu search acak dalam 90 hari terakhir
                $searchedAt = now()->subMinutes(rand(1, 90 * 24 * 60));

                $batch[] = [
                    'user_id'    => $customer->id,
                    'keyword'    => $this->keywords[$kwIdx],
                    'searched_at' => $searchedAt,
                    'created_at' => $searchedAt,
                    'updated_at' => $searchedAt,
                ];
            }
        }

        foreach (array_chunk($batch, 200) as $chunk) {
            DB::table('search_logs')->insert($chunk);
        }

        $this->command->info('✓ ' . count($batch) . ' search logs berhasil dibuat! (10/customer × ' . $customers->count() . ' customers)');
    }
}
