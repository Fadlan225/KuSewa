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

        $allBanks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga'];
        $deterministicRandoms = ['12345', '67890', '54321', '09876', '11223', '33445'];

        $count = 0;
        foreach ($owners as $index => $owner) {
            // Pick exactly 5 banks by excluding one bank based on owner index
            $banks = $allBanks;
            unset($banks[$index % 6]);
            $banks = array_values($banks);

            foreach ($banks as $bankIndex => $bank) {
                // Generate a unique 12-digit account number that is deterministic for idempotency
                // Format: 10 + owner_id (2 digits) + bank_index (2 digits) + deterministic_random (5 digits)
                // Result length: 2 + 2 + 2 + 5 = 11 digits
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
                        'bank_name' => $bank,
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
