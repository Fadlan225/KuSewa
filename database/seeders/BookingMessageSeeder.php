<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingMessageSeeder extends Seeder
{
    /**
     * Membuat 60 pesan booking (1-2 per booking) untuk melihat detail conversation.
     */
    public function run(): void
    {
        DB::table('booking_messages')->truncate();

        $bookings = DB::table('bookings')
            ->whereNotNull('id')
            ->orderBy('id')
            ->limit(60)
            ->get();

        if ($bookings->isEmpty()) {
            $this->command->error('Tidak ada booking ditemukan untuk di-message.');
            return;
        }

        $messages = [];
        $counter = 0;

        foreach ($bookings as $booking) {
            // Ambil user dan pemilik asset untuk nama pengirim/penerima
            $customer = DB::table('users')
                ->where('id', $booking->user_id)
                ->first();

            $asset = DB::table('assets')->find($booking->asset_id);
            $ownerProfileId = $asset ? $asset->owner_profile_id : null;
            $ownerId = $ownerProfileId
                ? DB::table('owner_profiles')->where('id', $ownerProfileId)->value('user_id')
                : null;

            $senderId = $customer ? $customer->id : $booking->user_id;
            $receiverId = $ownerId ?? 1; // fallback ke owner pertama jika tidak ada

            // Pesan awal dari customer saat booking dibuat
            $counter++;
            $messages[] = [
                'booking_id' => $booking->id,
                'sender_id' => $senderId,
                'sender_type' => 'customer',
                'message' => "Halo, saya tertarik menyewa " . ($asset->title ?? 'unit ini') . " mulai {$booking->start_date} sampai {$booking->end_date}. Apakah tersedia?",
                'created_at' => now()->subDays(rand(1, 5))->addHours(rand(0, 23)),
                'updated_at' => now()->subDays(rand(1, 5))->addHours(rand(0, 23)),
            ];

            // Balasan dari owner
            $counter++;
            $messages[] = [
                'booking_id' => $booking->id,
                'sender_id' => $receiverId,
                'sender_type' => 'owner',
                'message' => $booking->booking_status === 'cancelled'
                    ? 'Maaf, unit tersebut sedang tidak tersedia untuk tanggal yang Anda minta.'
                    : 'Terima kasih atas minatnya. Unit tersedia dan sudah saya reservasi. Silakan lakukan pembayaran untuk konfirmasi.',
                'created_at' => now()->subDays(rand(0, 4))->addHours(rand(0, 23)),
                'updated_at' => now()->subDays(rand(0, 4))->addHours(rand(0, 23)),
            ];

            // Jika confirmed/completed, tambahkan pesan follow-up
            if (in_array($booking->booking_status, ['confirmed', 'completed'])) {
                $counter++;
                $messages[] = [
                    'booking_id' => $booking->id,
                    'sender_id' => $senderId,
                    'sender_type' => 'customer',
                    'message' => "Apakah ada instruksi tambahan untuk pickup unit? Saya bisa datang sendiri atau ambil kurir.",
                    'created_at' => now()->subDays(rand(0, 2))->addHours(rand(0, 23)),
                    'updated_at' => now()->subDays(rand(0, 2))->addHours(rand(0, 23)),
                ];

                $counter++;
                $messages[] = [
                    'booking_id' => $booking->id,
                    'sender_id' => $receiverId,
                    'sender_type' => 'owner',
                    'message' => 'Pickup bisa dilakukan langsung ke lokasi unit. Alamat lengkap akan dikirimkan via chat sebelum hari H. Terima kasih!',
                    'created_at' => now()->subDays(rand(0, 1))->addHours(rand(0, 23)),
                    'updated_at' => now()->subDays(rand(0, 1))->addHours(rand(0, 23)),
                ];
            }
        }

        DB::table('booking_messages')->insert($messages);

        $this->command->info("Berhasil membuat {$counter} pesan booking.");
    }
}
