<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Payment untuk setiap booking — SEMUA booking harus punya payment
     * agar tidak ada orphan booking yang memblokir slot tanggal.
     *
     * - completed → payment_status = 'paid'
     * - confirmed → payment_status = 'paid'
     * - active    → payment_status = 'paid'
     * - cancelled → payment_status = 'rejected' atau 'expired'
     * - pending   → 50% 'pending' (aktif, expires 24j ke depan)
     *               50% 'verifying' (sudah upload bukti)
     *
     * TIDAK ada booking pending tanpa payment (orphan).
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
            $method = $methods[array_rand($methods)];

            if ($booking->booking_status === 'pending') {
                /**
                 * Semua booking pending WAJIB punya payment.
                 * 50% masih menunggu bayar (pending, expires 24 jam ke depan dari sekarang).
                 * 50% sudah upload bukti (verifying).
                 */
                $isPendingPayment = rand(0, 1) === 1;

                $batch[] = [
                    'booking_id'       => $booking->id,
                    'payment_method'   => $method,
                    'payment_status'   => $isPendingPayment ? 'pending' : 'verifying',
                    'payment_date'     => $isPendingPayment ? null : now()->format('Y-m-d'),
                    'proof_of_payment' => $isPendingPayment ? null : 'proofs/bukti_' . $booking->id . '.jpg',
                    // expires_at di masa depan agar booking ini masih menghitung sebagai "aktif/bloking"
                    'expires_at'       => now()->addHours(rand(1, 24))->format('Y-m-d H:i:s'),
                    'created_at'       => $booking->created_at,
                    'updated_at'       => now()->format('Y-m-d H:i:s'),
                ];
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

            // Untuk yang sudah selesai/dibatalkan, expires_at bisa di masa lalu (wajar)
            $expiresAt = date('Y-m-d H:i:s', strtotime($booking->created_at . ' +24 hours'));

            $batch[] = [
                'booking_id'       => $booking->id,
                'payment_method'   => $method,
                'payment_status'   => $status,
                'payment_date'     => $paymentDate,
                'proof_of_payment' => 'proofs/bukti_' . $booking->id . '.jpg',
                'expires_at'       => $expiresAt,
                'created_at'       => $paymentDate,
                'updated_at'       => now()->format('Y-m-d H:i:s'),
            ];
        }

        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        $this->command->info('✓ ' . count($batch) . ' payment berhasil dibuat (semua booking ter-cover)!');
    }
}
