<?php

namespace Database\Seeders;

use App\Models\owner_profile;
use App\Models\bank_account;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $owners = owner_profile::with('user')->get();

        $allBanks = [
            'BCA' => '014',
            'Mandiri' => '008',
            'BNI' => '009',
            'BRI' => '002',
            'BSI' => '451',
            'CIMB Niaga' => '022'
        ];
        $bankKeys = array_keys($allBanks);
        $deterministicRandoms = ['12345', '67890', '54321', '09876', '11223', '33445'];

        $count = 0;
        foreach ($owners as $index => $owner) {
            // Pick exactly 5 banks by excluding one bank based on owner index
            $banks = $bankKeys;
            unset($banks[$index % 6]);
            $banks = array_values($banks);

            foreach ($banks as $bankIndex => $bankName) {
                // Generate a unique 12-digit account number that is deterministic for idempotency
                $accountNumber = '10' . 
                                 str_pad($owner->id, 2, '0', STR_PAD_LEFT) . 
                                 str_pad($bankIndex, 2, '0', STR_PAD_LEFT) . 
                                 $deterministicRandoms[$bankIndex];
                
                bank_account::updateOrCreate(
                    [
                        'owner_profile_id' => $owner->id,
                        'account_number' => $accountNumber,
                    ],
                    [
                        'bank_code' => $allBanks[$bankName],
                        'account_holder' => $owner->user->name,
                        'status' => 'active',
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✓ {$count} bank accounts berhasil dibuat!");
    }
}
