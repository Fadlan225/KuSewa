<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ServiceFeeSeeder::class,
            AssetCategorySeeder::class,
            UsersSeeder::class,
            OwnerProfileSeeder::class,
            BankAccountSeeder::class,
            AssetSeeder::class,
            AssetImageSeeder::class,
            AssetPricingSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);

    }
}
