<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PendingBookingSeeder extends Seeder
{
    public function run(): void
    {
        $asset = DB::table('assets')
            ->join('asset_pricings', 'asset_pricings.asset_id', '=', 'assets.id')
            ->select('assets.id', 'asset_pricings.price')
            ->orderBy('assets.id')
            ->first();

        $customer = DB::table('users')
            ->where('email', 'like', 'customer%@kusewa.com')
            ->orderBy('id')
            ->first();

        if (!$asset || !$customer) {
            $this->command->error('Asset atau customer belum tersedia. Jalankan seeder asset dan user terlebih dahulu.');
            return;
        }

        $startDate = Carbon::now()->addDays(7)->startOfDay();
        $endDate = $startDate->copy()->addDays(3);
        $subtotal = (float) $asset->price * 3;
        $serviceFee = round($subtotal * 0.05, 2);

        $bookingId = DB::table('bookings')->insertGetId([
            'asset_id' => $asset->id,
            'asset_unit_id' => null,
            'booking_code' => 'BK-PENDING-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => $customer->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $subtotal + $serviceFee,
            'booking_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Booking pending berhasil dibuat. ID: {$bookingId}");
    }
}
