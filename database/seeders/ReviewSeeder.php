<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * 50% dari booking completed mendapat review.
     * Customer yang sama dengan booking tersebut.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('reviews')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Hanya completed booking yang bisa di-review
        $completedBookings = DB::table('bookings')
            ->where('booking_status', 'completed')
            ->orderBy('id')
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->error('Completed bookings belum ada.');
            return;
        }

        // Ambil 50% (acak)
        $bookingArray = $completedBookings->toArray();
        shuffle($bookingArray);
        $reviewBookings = array_slice($bookingArray, 0, (int)(count($bookingArray) * 0.5));

        $positiveReviews = [
            "Tempat sangat bersih dan nyaman, sesuai foto. Pemilik ramah dan responsif. Pasti balik lagi!",
            "Lokasi strategis, fasilitas lengkap. Sangat puas dengan pelayanannya. Recommended!",
            "Harga sesuai kualitas. Tempatnya terawat dan bersih. Staff sangat membantu.",
            "Pengalaman menyewa yang menyenangkan. Proses booking mudah dan cepat.",
            "Tempatnya lebih bagus dari ekspektasi. Fasilitasnya lengkap, sangat worth it!",
            "Pemilik komunikatif dan helpful. Tempat bersih dan nyaman. Akan kembali lagi.",
            "Sangat memuaskan! Harga terjangkau dengan kualitas premium. Highly recommended.",
            "Lokasi mudah ditemukan, parkir luas. Interior bersih dan modern. Top!",
        ];
        $neutralReviews = [
            "Tempatnya oke, sesuai ekspektasi. Fasilitas standar, cukup untuk kebutuhan.",
            "Pelayanan lumayan, tempat cukup bersih. Ada beberapa hal yang perlu diperbaiki tapi secara keseluruhan oke.",
            "Harga sedikit mahal untuk fasilitasnya, tapi lokasi sangat strategis.",
            "Cukup memuaskan. Pemilik responsif walau ada sedikit keterlambatan.",
        ];
        $negativeReviews = [
            "Beberapa fasilitas tidak berfungsi dengan baik. Perlu perhatian lebih dari pemilik.",
            "Tidak sesuai foto, kondisi kurang terawat. Komunikasi pemilik kurang responsif.",
            "Ada beberapa kekurangan yang perlu diperbaiki. Overall masih bisa diterima.",
        ];

        $batch = [];
        foreach ($reviewBookings as $idx => $booking) {
            // Rating distribution: 60% bintang 5, 25% bintang 4, 10% bintang 3, 5% bintang <=2
            $rand = rand(1, 100);
            if ($rand <= 60) {
                $rating = 5;
                $review = $positiveReviews[array_rand($positiveReviews)];
            } elseif ($rand <= 85) {
                $rating = 4;
                $review = $positiveReviews[array_rand($positiveReviews)];
            } elseif ($rand <= 95) {
                $rating = 3;
                $review = $neutralReviews[array_rand($neutralReviews)];
            } else {
                $rating = rand(1, 2);
                $review = $negativeReviews[array_rand($negativeReviews)];
            }

            $batch[] = [
                'user_id'    => $booking->user_id,
                'booking_id' => $booking->id,
                'rating'     => $rating,
                'review'     => $review,
                'created_at' => date('Y-m-d H:i:s', strtotime($booking->end_date . ' +' . rand(1, 5) . ' days')),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('reviews')->insert($chunk);
        }

        $this->command->info('✓ ' . count($batch) . ' reviews berhasil dibuat! (50% dari ' . count($bookingArray) . ' completed bookings)');
    }
}
