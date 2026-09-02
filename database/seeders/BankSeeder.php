<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = fopen(database_path('data/bank-indonesia.csv'), 'r');
        
        $firstLine = true;
        $banks = [];
        $seenCodes = [];
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if ($firstLine) {
                $firstLine = false;
                continue;
            }
            $code = str_pad(trim($data[1]), 3, '0', STR_PAD_LEFT);
            
            $originalCode = $code;
            $suffix = 1;
            while (in_array($code, $seenCodes)) {
                $code = $originalCode . '-' . $suffix;
                $suffix++;
            }

            $banks[] = [
                'name' => trim($data[0]),
                'code' => $code,
            ];
            $seenCodes[] = $code;
        }
        fclose($csvFile);

        // Delete all data first with foreign key check disabled
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('banks')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        DB::table('banks')->insert($banks);
        
        $this->command->info("✓ " . count($banks) . " banks berhasil diimport dari CSV.");
    }
}
