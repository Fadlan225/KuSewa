<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'kitasewa.web.id@gmail.com'],
            [
                'name' => 'ADMIN KITASEWA',
                'password' => \Illuminate\Support\Facades\Hash::make('KitaSewa#123'),
                'role' => 'admin',
                'phone' => '085151242588',
                'place_of_birth_code' => '6472', // Kota Samarinda
                'date_of_birth' => '2000-09-07',
                'nationality' => 'WNI',
                'occupation' => 'ADMINISTRATOR',
            ]
        );
    }
}
