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
            // --------------------------------------------------------------
            //  PRODUCTION-READY: Master Data (aman dijalankan di production)
            // --------------------------------------------------------------

            // -- Infrastructure --------------------------------------------
            WilayahSeeder::class,              // Master wilayah: Provinsi, Kota, Kecamatan, Desa
            BankSeeder::class,                 // Master bank Indonesia (dari CSV)
            ServiceFeeSeeder::class,           // Biaya platform default per tipe aset (Rp 5.000)
            AssetCategorySeeder::class,        // 5 kategori aset (Hunian, Komersial, Lahan, Event, Media Iklan)
            AssetTypeSeeder::class,            // 16 tipe aset (Hotel, Villa, Kos, Studio, Baliho, dll)
            AssetTypeDefaultSpecSeeder::class, // Default spesifikasi form (detail_fields & unit_detail_fields) per tipe aset

            // -- Facilities ------------------------------------------------
            FacilityCategorySeeder::class,     // 18 kategori fasilitas (Internet, Parkir, Keamanan, dll)
            FacilitySeeder::class,             // ~81 master fasilitas (WiFi, AC, Kolam Renang, Drum, dll)
            AssetTypeFacilitySeeder::class,    // ~184 aturan fasilitas per tipe aset
            AssetTypeMandatorySeeder::class,   // Kategori fasilitas & galeri wajib per tipe aset

            // -- Gallery ---------------------------------------------------
            GaleryCategorySeeder::class,           // ~55 kategori galeri global (Tampak Depan, Lobby, Kamar, dll)
            AssetTypeGalleryCategorySeeder::class, // Konfigurasi galeri default per tipe aset (idempotent)

            // -- Review ----------------------------------------------------
            ReviewTagSeeder::class,            // Master tag review per tipe aset (Bersih, Nyaman, View Indah, dll)

            // -- Policy ----------------------------------------------------
            PolicyTemplateSeeder::class,       // Template kebijakan default per tipe aset (Kos: jam malam, tamu, dll)

            // -- Platform Content ------------------------------------------
            SocialMediaLinkSeeder::class,      // Tautan media sosial platform (Instagram, Email, WhatsApp)
            HelpCenterSeeder::class,           // Artikel pusat bantuan (kategori + artikel panduan)

            // -- Users -----------------------------------------------------
            AdminSeeder::class,                // 1 akun admin (admin@kitasewa.com)

            // --------------------------------------------------------------
            //  DUMMY: Data pengembangan (JANGAN dijalankan di production)
            // --------------------------------------------------------------

            // -- Users & Profiles -----------------------------------------
            // UsersSeeder::class,             // 1 admin + 5 owner + 5 customer (akun testing)
            // OwnerProfileSeeder::class,      // 5 profil owner (verified, bergantung UsersSeeder)
            // BankAccountSeeder::class,       // 25 rekening bank owner (bergantung OwnerProfileSeeder)
            // FadlanFirdausSeeder::class,     // Akun personal developer + 3 aset Hotel/Villa/Studio

            // -- Assets ---------------------------------------------------
            // AssetSeeder::class,             // ~320 aset random (Faker, 5 aset/tipe/kota)
            // AssetUnitSeeder::class,         // Unit random per aset (Faker)
            // AssetFacilitySeeder::class,     // Fasilitas random 80% per aset/unit (Faker)
            // AssetImageSeeder::class,        // 5-10 gambar placeholder per aset (Faker)
            // AssetPricingSeeder::class,      // Harga random per tipe aset (Faker)

            // -- Transactions ---------------------------------------------
            // BookingSeeder::class,           // 600 booking (distribusi: 10/15/10/55/10%)
            // PaymentSeeder::class,           // Payment untuk setiap booking
            // ReviewSeeder::class,            // Review untuk 50% booking completed
            // ReviewTagItemSeeder::class,     // Tag item per review (bergantung ReviewSeeder)

            // -- Engagement -----------------------------------------------
            // FavoriteSeeder::class,          // 5 favorit random per customer
            // SearchLogSeeder::class,         // 10 keyword pencarian random per customer
        ]);
    }
}
