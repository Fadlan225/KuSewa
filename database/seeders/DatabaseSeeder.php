<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        \App\Models\PaymentMethod::insertOrIgnore([
            ['name' => 'BCA', 'code' => 'bca', 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mandiri', 'code' => 'mandiri', 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BRI', 'code' => 'bri', 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BNI', 'code' => 'bni', 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'QRIS', 'code' => 'qris', 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
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
            AssetImageSeeder::class,        // 2200 gambar GD (10/asset)
            AssetPricingSeeder::class,      // Harga realistis per tipe
            DummyAssetSubmissionSeeder::class, // Data demo untuk validasi aset admin

                // ── Transactions ───────────────────────────────────────────────
            BookingSeeder::class,           // 600 booking (10/20/60/10%)
            PaymentSeeder::class,           // Payment sesuai status booking
            ReviewSeeder::class,            // 50% dari completed bookings

                // ── Engagement ─────────────────────────────────────────────────
            FavoriteSeeder::class,          // 5 favorite/customer
            SearchLogSeeder::class,         // 10 search log/customer
        ]);
    }
}
