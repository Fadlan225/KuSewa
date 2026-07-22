<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OwnerProfileSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('owner_profiles')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Ambil user yang email-nya owner1..owner30 (berurutan)
        $ownerUsers = DB::table('users')
            ->where('email', 'like', 'owner%@kusewa.com')
            ->orderBy('id')
            ->get();

        if ($ownerUsers->isEmpty()) {
            $this->command->error('Owner users belum ada. Jalankan UsersSeeder dulu.');
            return;
        }

        $cities = [
            'Samarinda', 'Balikpapan', 'Bontang', 'Tenggarong', 'Berau',
            'Kutai Kertanegara', 'Penajam', 'Sangatta', 'Tarakan', 'Nunukan',
        ];

        $rows = [];
        foreach ($ownerUsers as $idx => $user) {
            $city = $cities[$idx % count($cities)];
            // NIK 16 digit unik (base + offset)
            $nik  = str_pad((string)(6471000000000000 + $idx * 17 + 1001), 16, '0', STR_PAD_LEFT);

            $rows[] = [
                'user_id'         => $user->id,
                'national_id'     => $nik,
                'address'         => 'Jl. Pemilik Aset No. ' . (($idx + 1) * 3) . ", {$city}",
                'place_of_birth'  => $city,
                'date_of_birth'   => date('Y-m-d', mktime(0, 0, 0, rand(1, 12), rand(1, 28), rand(1975, 1998))),
                'ktp_photo'       => 'ktp/placeholder.jpg',
                'status'          => 'verified',
                'verification_at' => now()->subDays(rand(10, 300)),
                'created_at'      => now()->subDays(rand(30, 400)),
                'updated_at'      => now(),
            ];
        }

        DB::table('owner_profiles')->insert($rows);
        $this->command->info('✓ ' . count($rows) . ' owner profiles berhasil dibuat! (status: verified)');
    }
}
