<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $owners = DB::table('owner_profiles')->get();
        $faker = \Faker\Factory::create('id_ID');

        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga'];
        $bankAccounts = [];

        foreach ($owners as $owner) {
            $selectedBanks = $faker->randomElements($banks, 2);

            foreach ($selectedBanks as $bank) {
                $bankAccounts[] = [
                    'owner_profile_id' => $owner->id,
                    'bank_name' => $bank,
                    'account_number' => $faker->numerify('##########'),
                    'account_holder' => 'Owner ' . $owner->id,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        DB::table('bank_accounts')->insert($bankAccounts);
    }
}
