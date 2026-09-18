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
            WilayahSeeder::class,              // Master data wilayah (Provinsi, Kota, Kecamatan, Desa)
            BankSeeder::class,                 // Master data bank Indonesia (dari CSV)
            ServiceFeeSeeder::class,           // Biaya platform default per tipe aset (Rp 5.000)
            AssetCategorySeeder::class,        // 5 kategori aset (Hunian, Komersial, Lahan, Event, Media Iklan)
            AssetTypeSeeder::class,            // 16 tipe aset (Hotel, Villa, Kos, Studio, Baliho, dll)
            AssetTypeDefaultSpecSeeder::class, // Default spesifikasi form (detail_fields & unit_detail_fields) per tipe aset

                // ── Facilities ─────────────────────────────────────────────────
            FacilityCategorySeeder::class,     // 18 master kategori fasilitas (Internet, Parkir, Keamanan, dll)
            FacilitySeeder::class,             // ~81 master fasilitas (WiFi, AC, Kolam Renang, Drum, dll)
            AssetTypeFacilitySeeder::class,    // ~184 aturan fasilitas yang relevan per tipe aset
            AssetTypeMandatorySeeder::class,   // Kategori fasilitas & galeri wajib per tipe aset

                // ── Gallery ────────────────────────────────────────────────────
            GaleryCategorySeeder::class,       // ~55 kategori galeri global (Tampak Depan, Lobby, Kamar, dll)

                // ── Review ─────────────────────────────────────────────────────
            ReviewTagSeeder::class,            // Master tag review per tipe aset (Bersih, Nyaman, View Indah, dll)

                // ── Policy ─────────────────────────────────────────────────────
            PolicyTemplateSeeder::class,       // Template kebijakan default per tipe aset (Kos: jam malam, tamu, dll)

                // ── Users & Profiles ───────────────────────────────────────────
            AdminSeeder::class,                // 1 akun admin (admin@kitasewa.com)
            // UsersSeeder::class,             // [DUMMY] 1 admin + 5 owner + 5 customer
            // OwnerProfileSeeder::class,      // [DUMMY] 5 profil owner (verified)
            // BankAccountSeeder::class,       // [DUMMY] 25 Rekening bank owner

            // ── Assets & Images ─────────────────────────────────────── [DUMMY]
            // AssetSeeder::class,
            // AssetUnitSeeder::class,
            // AssetFacilitySeeder::class,
            // AssetImageSeeder::class,
            // AssetPricingSeeder::class,

            // ── Transactions ─────────────────────────────────────────── [DUMMY]
            // BookingSeeder::class,           // 600 booking (10/20/60/10%)
            // PaymentSeeder::class,           // Payment sesuai status booking
            // ReviewSeeder::class,            // 50% dari completed bookings
            // ReviewTagItemSeeder::class,     // Tag item per review

            // ── Engagement ───────────────────────────────────────────── [DUMMY]
            // FavoriteSeeder::class,          // 5 favorite/customer
            // SearchLogSeeder::class,         // 10 search log/customer
        ]);
    }
}
