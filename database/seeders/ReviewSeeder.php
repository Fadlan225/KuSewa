<?php

namespace Database\Seeders;

use App\Models\booking;
use App\Models\review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = booking::where('booking_status', 'accepted')->get();

        foreach ($bookings as $b) {

            if (fake()->boolean(70)) {

                review::create([
                    'user_id'    => $b->user_id,
                    'booking_id' => $b->id,
                    'rating'     => fake()->numberBetween(3, 5),
                    'review'     => fake()->randomElement([
                        'Tempatnya bagus dan sesuai deskripsi.',
                        'Pelayanan sangat baik.',
                        'Lokasi mudah ditemukan.',
                        'Fasilitas cukup lengkap.',
                        'Aset bersih dan nyaman.',
                        'Pengalaman menyewa sangat memuaskan.',
                        'Akan menyewa lagi jika ada kesempatan.',
                        'Pemilik aset sangat responsif.',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
