<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Payment untuk setiap booking:
     * - completed → payment_status = 'paid'
     * - confirmed → payment_status = 'paid'
     * - cancelled → payment_status = 'failed'
     * - pending   → tidak ada payment (belum bayar)
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('payments')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $bookings = DB::table('bookings')
            ->whereIn('booking_status', ['completed', 'confirmed', 'cancelled'])
            ->orderBy('id')
            ->get();

        if ($bookings->isEmpty()) {
            $this->command->error('Bookings belum ada.');
            return;
        }

        $methods = ['transfer_bank', 'transfer_bank', 'transfer_bank', 'qris', 'qris', 'virtual_account'];
        $batch   = [];

        foreach ($bookings as $booking) {
            if ($booking->booking_status === 'pending') continue;

            $status = match ($booking->booking_status) {
                'completed' => 'paid',
                'confirmed' => 'paid',
                'cancelled' => 'failed',
                default     => 'pending',
            };

            $paymentDate = match ($booking->booking_status) {
                'completed' => date('Y-m-d', strtotime($booking->start_date . ' -1 day')),
                'confirmed' => date('Y-m-d', strtotime($booking->start_date . ' -' . rand(1, 3) . ' day')),
                'cancelled' => date('Y-m-d', strtotime($booking->created_at . ' +1 day')),
                default     => now()->format('Y-m-d'),
            };

            $batch[] = [
                'booking_id'      => $booking->id,
                'payment_method'  => $methods[array_rand($methods)],
                'payment_status'  => $status,
                'payment_date'    => $paymentDate,
                'proof_of_payment' => 'payments/bukti_' . $booking->id . '.jpg',
                'created_at'      => $paymentDate,
                'updated_at'      => now(),
            ];
        }

        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        $this->command->info('✓ ' . count($batch) . ' payment berhasil dibuat!');
    }
}
