<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceFeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('service_fees')->insert([
            [
                'fee_type'  => 'fixed',
                'fee_value' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
