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
     * - active    → payment_status = 'paid'
     * - cancelled → payment_status = 'rejected' atau 'expired'
     * - pending   → beberapa dengan 'verifying', sisanya tidak ada payment
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('payments')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $bookings = DB::table('bookings')
            ->whereIn('booking_status', ['completed', 'confirmed', 'active', 'cancelled', 'pending'])
            ->orderBy('id')
            ->get();

        if ($bookings->isEmpty()) {
            $this->command->error('Bookings belum ada.');
            return;
        }

        $methods = ['BCA', 'BCA', 'Mandiri', 'BRI', 'BNI', 'QRIS'];
        $batch   = [];

        foreach ($bookings as $booking) {
            // Booking pending: hanya sebagian yang punya payment verifying, sisanya skip
            if ($booking->booking_status === 'pending') {
                if (rand(1, 3) === 1) { // 1/3 chance punya pembayaran verifying
                    $batch[] = [
                        'booking_id'       => $booking->id,
                        'payment_method'   => $methods[array_rand($methods)],
                        'payment_status'   => 'verifying',
                        'payment_date'     => now()->format('Y-m-d'),
                        'proof_of_payment' => 'proofs/bukti_' . $booking->id . '.jpg',
                        'expires_at'       => date('Y-m-d H:i:s', strtotime($booking->created_at . ' +24 hours')),
                        'created_at'       => $booking->created_at,
                        'updated_at'       => now(),
                    ];
                }
                continue;
            }

            $status = match ($booking->booking_status) {
                'completed' => 'paid',
                'confirmed' => 'paid',
                'active'    => 'paid',
                'cancelled' => (rand(0, 1) ? 'rejected' : 'expired'),
                default     => 'pending',
            };

            $paymentDate = match ($booking->booking_status) {
                'completed' => date('Y-m-d', strtotime($booking->start_date . ' -1 day')),
                'confirmed' => date('Y-m-d', strtotime($booking->start_date . ' -' . rand(1, 3) . ' day')),
                'active'    => date('Y-m-d', strtotime($booking->start_date . ' -1 day')),
                'cancelled' => date('Y-m-d', strtotime($booking->created_at . ' +1 day')),
                default     => now()->format('Y-m-d'),
            };

            $batch[] = [
                'booking_id'       => $booking->id,
                'payment_method'   => $methods[array_rand($methods)],
                'payment_status'   => $status,
                'payment_date'     => $paymentDate,
                'proof_of_payment' => 'proofs/bukti_' . $booking->id . '.jpg',
                'expires_at'       => date('Y-m-d H:i:s', strtotime($booking->created_at . ' +24 hours')),
                'created_at'       => $paymentDate,
                'updated_at'       => now(),
            ];
        }

        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        $this->command->info('✓ ' . count($batch) . ' payment berhasil dibuat!');
    }
}
