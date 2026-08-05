<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // ── Infrastructure ─────────────────────────────────────────────
            ServiceFeeSeeder::class,        // Service fee config
            AssetCategorySeeder::class,     // 5 kategori
            AssetTypeSeeder::class,         // 22 tipe aset

            // ── Facilities ─────────────────────────────────────────────────
            FacilityCategorySeeder::class,  // 16 Master kategori fasilitas
            FacilitySeeder::class,          // 81 Master fasilitas (WiFi, AC, dll)
            AssetTypeFacilitySeeder::class, // 184 Aturan fasilitas per asset type

            // ── Users & Profiles ───────────────────────────────────────────
            UsersSeeder::class,             // 1 admin + 5 owner + 5 customer
            OwnerProfileSeeder::class,      // 5 profil owner (verified)
            BankAccountSeeder::class,       // 25 Rekening bank owner

            // ── Assets & Images ────────────────────────────────────────────
            AssetSeeder::class,             
            AssetUnitSeeder::class,         
            AssetFacilitySeeder::class,     
            GaleryCategorySeeder::class,    
            AssetImageSeeder::class,        
            AssetPricingSeeder::class,      

            // // ── Transactions ───────────────────────────────────────────────
            // BookingSeeder::class,           // 600 booking (10/20/60/10%)
            // PaymentSeeder::class,           // Payment sesuai status booking
            // ReviewSeeder::class,            // 50% dari completed bookings

            // ── Engagement ─────────────────────────────────────────────────
            // FavoriteSeeder::class,          // 5 favorite/customer
            // SearchLogSeeder::class,         // 10 search log/customer
        ]);
    }
}
