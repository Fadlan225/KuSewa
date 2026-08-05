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
                'place_of_birth' => 'Samarinda',
                'date_of_birth' => '1985-08-15',
            ],
            'sitirahma02@kusewa.com' => [
                'national_id' => '6471014212900001',
                'address' => 'Jl. Sudirman No. 25, Balikpapan',
                'place_of_birth' => 'Balikpapan',
                'date_of_birth' => '1990-12-02',
            ],
            'agusprasetyo25@kusewa.com' => [
                'national_id' => '6471012503880001',
                'address' => 'Jl. Antasari No. 8, Bontang',
                'place_of_birth' => 'Bontang',
                'date_of_birth' => '1988-03-25',
            ],
            'dewilestari10@kusewa.com' => [
                'national_id' => '6471015007920001',
                'address' => 'Jl. Hasanuddin No. 15, Tenggarong',
                'place_of_birth' => 'Tenggarong',
                'date_of_birth' => '1992-07-10',
            ],
            'ekowahyudi18@kusewa.com' => [
                'national_id' => '6471011811800001',
                'address' => 'Jl. Gajah Mada No. 2, Berau',
                'place_of_birth' => 'Berau',
                'date_of_birth' => '1980-11-18',
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
                        'place_of_birth' => $data['place_of_birth'],
                        'date_of_birth' => $data['date_of_birth'],
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
