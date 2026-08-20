<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * 600 booking dengan distribusi:
     * - 10% pending   = 60   (dengan payment aktif, tidak expired)
     * - 15% confirmed = 90
     * - 10% active    = 60
     * - 55% completed = 330
     * - 10% cancelled = 60
     *
     * DIJAMIN tidak ada tanggal yang overlap per asset.
     * Setiap asset mendapat slot tanggal non-tumpuk secara berurutan.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('bookings')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $assets = DB::table('assets')
            ->join('asset_pricings', 'asset_pricings.asset_id', '=', 'assets.id')
            ->select('assets.id', 'assets.title as asset_name', 'asset_pricings.price')
            ->orderBy('assets.id')
            ->get();

        $customers = DB::table('users')
            ->where('email', 'like', 'customer%@kitasewa.com')
            ->orderBy('id')
            ->get();

        $serviceFeePct = DB::table('service_fees')
            ->where('fee_type', 'percentage')
            ->value('fee_value') ?? 5;

        if ($assets->isEmpty() || $customers->isEmpty()) {
            $this->command->error('Assets/customers tidak ditemukan.');
            return;
        }

        $custCount  = $customers->count();
        $assetCount = $assets->count();

        // Distribusi status: cancelled tidak memblokir slot, sisanya ya
        $statusPool = array_merge(
            array_fill(0, 60,  'pending'),
            array_fill(0, 90,  'confirmed'),
            array_fill(0, 60,  'active'),
            array_fill(0, 330, 'completed'),
            array_fill(0, 60,  'cancelled')
        );
        shuffle($statusPool);

        $totalBookings = 600;
        $batch         = [];
        $custIndex     = 0;
        $statusIndex   = 0;

        // Tracker: per asset_id, tanggal terakhir yang sudah dipakai
        // Key = asset_id, Value = Carbon date (end_date booking terakhir)
        $assetNextAvailable = [];

        $this->command->info("Membuat {$totalBookings} booking (tanpa overlap)...");

        // Distribusikan booking ke setiap asset secara round-robin
        // cancelled tidak perlu "blocking" slot, tapi kita tetap assign waktunya
        $bookingsPerAsset = (int) ceil($totalBookings / $assetCount);
        $created = 0;

        for ($a = 0; $a < $assetCount && $created < $totalBookings; $a++) {
            $asset = $assets[$a];

            // Berapa booking untuk asset ini?
            $remaining = $totalBookings - $created;
            $count = min($bookingsPerAsset, $remaining);

            // Tracker tanggal untuk asset ini — mulai dari 400 hari lalu
            $currentDate = now()->subDays(400)->startOfDay();

            for ($j = 0; $j < $count && $created < $totalBookings; $j++) {
                $status   = $statusPool[$statusIndex % count($statusPool)];
                $statusIndex++;
                $customer = $customers[$custIndex % $custCount];
                $custIndex++;

                // Tentukan durasi & jeda antar booking
                $duration = rand(2, 14);
                $gap      = rand(1, 5); // Jeda antar booking agar tidak persis bersentuhan

                $startDate = $currentDate->copy()->addDays($gap);
                $endDate   = $startDate->copy()->addDays($duration);

                // Sesuaikan arah waktu berdasarkan status
                // completed = masa lalu, confirmed/active = mendatang, pending = mendatang dekat
                if ($status === 'completed') {
                    // Sudah terjadi di masa lalu — hanya assign jika masih di masa lalu
                    if ($startDate->isFuture()) {
                        // Paksa ke masa lalu jika current date sudah ke depan
                        $status = 'cancelled';
                    }
                } elseif (in_array($status, ['confirmed', 'active'])) {
                    // Harus di masa mendatang — jika currentDate masih lalu, lompat ke masa depan
                    if ($startDate->isPast()) {
                        $startDate = now()->addDays(rand(2, 60) + ($j * 3));
                        $endDate   = $startDate->copy()->addDays($duration);
                    }
                }

                // Maju pointer untuk booking berikutnya di asset yang sama
                $currentDate = $endDate->copy();

                $subtotal   = $asset->price * $duration;
                $serviceFee = round($subtotal * $serviceFeePct / 100);
                $total      = $subtotal + $serviceFee;

                // Tanggal created_at: beberapa hari sebelum start_date (simulasi pemesanan lebih awal)
                $createdAt = $startDate->copy()->subDays(rand(1, 10));

                $batch[] = [
                    'asset_id'       => $asset->id,
                    'asset_unit_id'  => null,
                    'asset_name'     => $asset->asset_name,
                    'asset_unit_name'=> null,
                    'booking_code'   => 'BK' . strtoupper(substr(md5($created . $asset->id . microtime()), 0, 8)),
                    'booker_name'    => $customer->name,
                    'booker_phone'   => $customer->phone ?? '08' . rand(100000000, 999999999),
                    'booker_email'   => $customer->email,
                    'guest_name'     => $customer->name,
                    'user_id'        => $customer->id,
                    'start_date'     => $startDate->format('Y-m-d'),
                    'end_date'       => $endDate->format('Y-m-d'),
                    'subtotal'       => $subtotal,
                    'service_fee'    => $serviceFee,
                    'total'          => $total,
                    'booking_status' => $status,
                    'created_at'     => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at'     => now()->format('Y-m-d H:i:s'),
                ];

                $created++;
            }
        }

        // Batch insert
        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('bookings')->insert($chunk);
        }

        $this->command->info("✓ {$created} booking berhasil dibuat (tanpa overlap)!");
    }
}
