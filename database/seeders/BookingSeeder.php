<?php

namespace Database\Seeders;

use App\Models\asset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Generate a unique booking code with format: KS-YYYYMMDD-XXXXXX
     */
    private function generateBookingCode(\DateTime $date): string
    {
        $dateStr = $date->format('Ymd');
        do {
            $random = strtoupper(Str::random(6));
            $code   = "KS-{$dateStr}-{$random}";
        } while (DB::table('bookings')->where('booking_code', $code)->exists());

        return $code;
    }

    public function run(): void
    {
        $assets = asset::with('pricings')->get();

        foreach ($assets as $index => $asset) {
            $pricing = $asset->pricings()->first();

            if (!$pricing) {
                continue;
            }

            $startDate = now()->addDays($index + 1);
            $endDate   = now()->addDays($index + 4);
            $nights    = $startDate->diffInDays($endDate);
            $subtotal  = $pricing->price * max($nights, 1);
            $fee       = round($subtotal * 0.05); // 5% service fee dummy
            $total     = $subtotal + $fee;

            DB::table('bookings')->insert([
                'asset_id'       => $asset->id,
                'asset_unit_id'  => null,
                'booking_code'   => $this->generateBookingCode($startDate->toDateTime()),
                'user_id'        => 2,
                'start_date'     => $startDate->toDateString(),
                'end_date'       => $endDate->toDateString(),
                'subtotal'       => $subtotal,
                'service_fee'    => $fee,
                'total'          => $total,
                'booking_status' => 'accepted',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
