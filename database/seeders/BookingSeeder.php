<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * 600 booking dengan distribusi:
     * - 10% pending   = 60
     * - 20% confirmed = 120
     * - 60% completed = 360
     * - 10% cancelled = 60
     *
     * Customer dibagi rata ke 99 customer.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('bookings')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $assets = DB::table('assets')
            ->join('asset_pricings', 'asset_pricings.asset_id', '=', 'assets.id')
            ->select('assets.id', 'asset_pricings.price')
            ->orderBy('assets.id')
            ->get();

        $customers = DB::table('users')
            ->where('email', 'like', 'customer%@kusewa.com')
            ->orderBy('id')
            ->get();
        $serviceFeePct = DB::table('service_fees')
            ->where('fee_type', 'percentage')
            ->value('fee_value') ?? 5;

        if ($assets->isEmpty() || $customers->isEmpty()) {
            $this->command->error('Assets/customers tidak ditemukan.');
            return;
        }

        // Distribusi status
        $statusPool = array_merge(
            array_fill(0, 60,  'pending'),
            array_fill(0, 120, 'confirmed'),
            array_fill(0, 360, 'completed'),
            array_fill(0, 60,  'cancelled')
        );
        shuffle($statusPool);

        $totalBookings = 600;
        $assetCount    = $assets->count();
        $custCount     = $customers->count();
        $batch         = [];

        $this->command->info("Membuat {$totalBookings} booking...");

        for ($i = 0; $i < $totalBookings; $i++) {
            $asset    = $assets[$i % $assetCount];
            $customer = $customers[$i % $custCount];
            $status   = $statusPool[$i];

            // Tanggal berdasarkan status
            if ($status === 'completed') {
                $startDate = now()->subDays(rand(30, 365));
                $duration  = rand(1, 14);
            } elseif ($status === 'confirmed') {
                $startDate = now()->addDays(rand(1, 60));
                $duration  = rand(1, 30);
            } elseif ($status === 'cancelled') {
                $startDate = now()->subDays(rand(1, 60));
                $duration  = rand(1, 7);
            } else { // pending
                $startDate = now()->addDays(rand(1, 30));
                $duration  = rand(1, 14);
            }

            $endDate = $startDate->copy()->addDays($duration);
            $subtotal = $asset->price * $duration;
            $serviceFee = round($subtotal * $serviceFeePct / 100);
            $total = $subtotal + $serviceFee;

            $batch[] = [
                'asset_id'       => $asset->id,
                'asset_unit_id'  => null,
                'booking_code'   => 'BK' . strtoupper(substr(md5($i . $asset->id . time()), 0, 8)),
                'user_id'        => $customer->id,
                'start_date'     => $startDate->format('Y-m-d'),
                'end_date'       => $endDate->format('Y-m-d'),
                'subtotal'       => $subtotal,
                'service_fee'    => $serviceFee,
                'total'          => $total,
                'booking_status' => $status,
                'created_at'     => $startDate->copy()->subDays(rand(1, 7)),
                'updated_at'     => now(),
            ];
        }

        // Batch insert
        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('bookings')->insert($chunk);
        }

        $this->command->info("✓ {$totalBookings} booking berhasil dibuat!");
    }
}
