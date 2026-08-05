<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        
        // 1 Admin
        User::updateOrCreate(
            ['email' => 'admin@kusewa.com'],
            [
                'name' => 'Admin Kusewa',
                'phone' => '081200000000',
                'date_of_birth' => '1980-01-01',
                'gender' => 'male',
                'role' => 'admin',
                'status' => 'active',
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        // 5 Owners
        $owners = [
            ['name' => 'Budi Santoso', 'email' => 'budisantoso15@kusewa.com', 'phone' => '08115001001', 'date_of_birth' => '1985-08-15', 'gender' => 'male'],
            ['name' => 'Siti Rahma', 'email' => 'sitirahma02@kusewa.com', 'phone' => '08115001002', 'date_of_birth' => '1990-12-02', 'gender' => 'female'],
            ['name' => 'Agus Prasetyo', 'email' => 'agusprasetyo25@kusewa.com', 'phone' => '08115001003', 'date_of_birth' => '1988-03-25', 'gender' => 'male'],
            ['name' => 'Dewi Lestari', 'email' => 'dewilestari10@kusewa.com', 'phone' => '08115001004', 'date_of_birth' => '1992-07-10', 'gender' => 'female'],
            ['name' => 'Eko Wahyudi', 'email' => 'ekowahyudi18@kusewa.com', 'phone' => '08115001005', 'date_of_birth' => '1980-11-18', 'gender' => 'male'],
        ];

        foreach ($owners as $owner) {
            User::updateOrCreate(
                ['email' => $owner['email']],
                [
                    'name' => $owner['name'],
                    'phone' => $owner['phone'],
                    'date_of_birth' => $owner['date_of_birth'],
                    'gender' => $owner['gender'],
                    'role' => 'customer', // Owner is determined by owner_profile existence based on old logic
                    'status' => 'active',
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 5 Customers
        $customers = [
            ['name' => 'Rina Kusuma', 'email' => 'rinakusuma12@kusewa.com', 'phone' => '08122001001', 'date_of_birth' => '1995-05-12', 'gender' => 'female'],
            ['name' => 'Aldi Nugraha', 'email' => 'aldinugraha22@kusewa.com', 'phone' => '08122001002', 'date_of_birth' => '1998-09-22', 'gender' => 'male'],
            ['name' => 'Bunga Pertiwi', 'email' => 'bungapertiwi05@kusewa.com', 'phone' => '08122001003', 'date_of_birth' => '2000-01-05', 'gender' => 'female'],
            ['name' => 'Dani Firmansyah', 'email' => 'danifirmansyah30@kusewa.com', 'phone' => '08122001004', 'date_of_birth' => '1991-10-30', 'gender' => 'male'],
            ['name' => 'Elsa Fitriani', 'email' => 'elsafitriani14@kusewa.com', 'phone' => '08122001005', 'date_of_birth' => '1996-02-14', 'gender' => 'female'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'date_of_birth' => $customer['date_of_birth'],
                    'gender' => $customer['gender'],
                    'role' => 'customer',
                    'status' => 'active',
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info("✓ 11 users berhasil dibuat! (1 admin + 5 owner + 5 customer)");
    }
}
