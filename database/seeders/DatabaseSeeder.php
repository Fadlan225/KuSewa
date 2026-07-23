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

            // ── Users & Profiles ───────────────────────────────────────────
            UsersSeeder::class,             // 1 admin + 30 owner + 99 customer
            OwnerProfileSeeder::class,      // 30 profil owner (verified)
            BankAccountSeeder::class,       // Rekening bank owner

            // ── Assets & Images ────────────────────────────────────────────
            AssetSeeder::class,             // 220 aset (10/type)
            AssetUnitSeeder::class,         // Asset units (kamar, tipe apartemen, kios, studio, dll)
            GaleryCategorySeeder::class,    // Kategori galeri foto per tipe
            AssetImageSeeder::class,        // Gambar per aset & unit dengan kategori galeri
            AssetPricingSeeder::class,      // Harga realistis per tipe

            // ── Transactions ───────────────────────────────────────────────
            BookingSeeder::class,           // 600 booking (10/20/60/10%)
            PaymentSeeder::class,           // Payment sesuai status booking
            ReviewSeeder::class,            // 50% dari completed bookings

            // ── Engagement ─────────────────────────────────────────────────
            FavoriteSeeder::class,          // 5 favorite/customer
            SearchLogSeeder::class,         // 10 search log/customer

            ApartemenImageSeeder::class,
        ]);
    }
}
