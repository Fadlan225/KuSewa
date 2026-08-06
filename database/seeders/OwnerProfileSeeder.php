<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\owner_profile;
use Illuminate\Database\Seeder;

class OwnerProfileSeeder extends Seeder
{
    public function run(): void
    {
        $ownerData = [
            'budisantoso15@kusewa.com' => [
                'national_id' => '6471011508850001',
                'address' => 'Jl. Pahlawan No. 10, Samarinda',
            ],
            'sitirahma02@kusewa.com' => [
                'national_id' => '6471014212900001',
                'address' => 'Jl. Sudirman No. 25, Balikpapan',
            ],
            'agusprasetyo25@kusewa.com' => [
                'national_id' => '6471012503880001',
                'address' => 'Jl. Antasari No. 8, Bontang',
            ],
            'dewilestari10@kusewa.com' => [
                'national_id' => '6471015007920001',
                'address' => 'Jl. Hasanuddin No. 15, Tenggarong',
            ],
            'ekowahyudi18@kusewa.com' => [
                'national_id' => '6471011811800001',
                'address' => 'Jl. Gajah Mada No. 2, Berau',
            ],
        ];

        foreach ($ownerData as $email => $data) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                owner_profile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'national_id' => $data['national_id'],
                        'address' => $data['address'],
                        'ktp_photo' => 'ktp/placeholder.jpg',
                        'status' => 'verified',
                        'verification_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✓ 5 owner profiles berhasil dibuat! (status: verified)');
    }
}
