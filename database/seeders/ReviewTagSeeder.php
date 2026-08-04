<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\asset_type;
use App\Models\review_tag;

class ReviewTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        review_tag::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $types = asset_type::all()->keyBy('name');

        $tags = [
            'Rumah' => [
                ['name' => 'Bersih', 'icon' => 'fa-solid fa-broom'],
                ['name' => 'Nyaman', 'icon' => 'fa-solid fa-couch'],
                ['name' => 'Aman', 'icon' => 'fa-solid fa-shield-halved'],
                ['name' => 'Lokasi Strategis', 'icon' => 'fa-solid fa-map-location-dot'],
                ['name' => 'Fasilitas Lengkap', 'icon' => 'fa-solid fa-tv'],
            ],
            'Villa' => [
                ['name' => 'View Indah', 'icon' => 'fa-solid fa-mountain-sun'],
                ['name' => 'Kolam Renang Bersih', 'icon' => 'fa-solid fa-water'],
                ['name' => 'Suasana Tenang', 'icon' => 'fa-solid fa-leaf'],
                ['name' => 'Udara Segar', 'icon' => 'fa-solid fa-wind'],
                ['name' => 'Desain Estetik', 'icon' => 'fa-solid fa-palette'],
            ],
            'Apartemen' => [
                ['name' => 'Security 24 Jam', 'icon' => 'fa-solid fa-user-shield'],
                ['name' => 'Dekat Mall', 'icon' => 'fa-solid fa-bag-shopping'],
                ['name' => 'Fasilitas Premium', 'icon' => 'fa-solid fa-dumbbell'],
                ['name' => 'View Kota Bagus', 'icon' => 'fa-solid fa-city'],
                ['name' => 'Akses Mudah', 'icon' => 'fa-solid fa-road'],
            ],
            'Homestay' => [
                ['name' => 'Tuan Rumah Ramah', 'icon' => 'fa-solid fa-face-smile'],
                ['name' => 'Suasana Kekeluargaan', 'icon' => 'fa-solid fa-people-roof'],
                ['name' => 'Harga Terjangkau', 'icon' => 'fa-solid fa-tags'],
                ['name' => 'Bersih', 'icon' => 'fa-solid fa-broom'],
                ['name' => 'Sarapan Enak', 'icon' => 'fa-solid fa-utensils'],
            ],
            'Guest House' => [
                ['name' => 'Nyaman', 'icon' => 'fa-solid fa-couch'],
                ['name' => 'Tenang', 'icon' => 'fa-solid fa-leaf'],
                ['name' => 'Kamar Luas', 'icon' => 'fa-solid fa-bed'],
                ['name' => 'Dekat Tempat Wisata', 'icon' => 'fa-solid fa-map'],
                ['name' => 'Pelayanan Baik', 'icon' => 'fa-solid fa-hand-holding-heart'],
            ],
            'Kos' => [
                ['name' => 'Bebas Jam Malam', 'icon' => 'fa-solid fa-door-open'],
                ['name' => 'Dekat Kampus', 'icon' => 'fa-solid fa-graduation-cap'],
                ['name' => 'Lingkungan Tenang', 'icon' => 'fa-solid fa-book-open-reader'],
                ['name' => 'Sirkulasi Udara Baik', 'icon' => 'fa-solid fa-wind'],
                ['name' => 'Wi-Fi Cepat', 'icon' => 'fa-solid fa-wifi'],
            ],
            'Hotel' => [
                ['name' => 'Pelayanan Profesional', 'icon' => 'fa-solid fa-concierge-bell'],
                ['name' => 'Kamar Mewah', 'icon' => 'fa-solid fa-crown'],
                ['name' => 'Sarapan Variatif', 'icon' => 'fa-solid fa-mug-hot'],
                ['name' => 'Kebersihan Terjamin', 'icon' => 'fa-solid fa-sparkles'],
                ['name' => 'Fasilitas Lengkap', 'icon' => 'fa-solid fa-bell'],
            ],
            'Resort' => [
                ['name' => 'Pemandangan Alam', 'icon' => 'fa-solid fa-tree'],
                ['name' => 'Dekat Pantai/Gunung', 'icon' => 'fa-solid fa-umbrella-beach'],
                ['name' => 'Spa & Relaksasi', 'icon' => 'fa-solid fa-spa'],
                ['name' => 'Makanan Lezat', 'icon' => 'fa-solid fa-utensils'],
                ['name' => 'Aktivitas Menarik', 'icon' => 'fa-solid fa-person-swimming'],
            ],
            'Kontrakan' => [
                ['name' => 'Harga Pas', 'icon' => 'fa-solid fa-wallet'],
                ['name' => 'Tetangga Ramah', 'icon' => 'fa-solid fa-users'],
                ['name' => 'Bebas Banjir', 'icon' => 'fa-solid fa-water-ladder'],
                ['name' => 'Dekat Pasar', 'icon' => 'fa-solid fa-store'],
                ['name' => 'Bangunan Kokoh', 'icon' => 'fa-solid fa-hammer'],
            ],
            'Ruko' => [
                ['name' => 'Cocok untuk Usaha', 'icon' => 'fa-solid fa-shop'],
                ['name' => 'Lokasi Strategis', 'icon' => 'fa-solid fa-location-dot'],
                ['name' => 'Parkir Luas', 'icon' => 'fa-solid fa-square-parking'],
                ['name' => 'Listrik/Air Lancar', 'icon' => 'fa-solid fa-bolt'],
                ['name' => 'Akses Jalan Lebar', 'icon' => 'fa-solid fa-road'],
            ],
            'Toko' => [
                ['name' => 'Ramai Pengunjung', 'icon' => 'fa-solid fa-users-viewfinder'],
                ['name' => 'Terlihat Jelas', 'icon' => 'fa-solid fa-eye'],
                ['name' => 'Keamanan Baik', 'icon' => 'fa-solid fa-shield-halved'],
                ['name' => 'Etalase Menarik', 'icon' => 'fa-solid fa-store'],
                ['name' => 'Harga Sewa Masuk Akal', 'icon' => 'fa-solid fa-money-bill'],
            ],
            'Kios' => [
                ['name' => 'Pusat Keramaian', 'icon' => 'fa-solid fa-people-group'],
                ['name' => 'Sederhana & Fungsional', 'icon' => 'fa-solid fa-box'],
                ['name' => 'Dekat Pintu Masuk', 'icon' => 'fa-solid fa-door-open'],
                ['name' => 'Biaya Murah', 'icon' => 'fa-solid fa-tag'],
                ['name' => 'Komunitas Solid', 'icon' => 'fa-solid fa-handshake'],
            ],
            'Gudang' => [
                ['name' => 'Kapasitas Besar', 'icon' => 'fa-solid fa-boxes-stacked'],
                ['name' => 'Akses Truk Besar', 'icon' => 'fa-solid fa-truck-moving'],
                ['name' => 'Keamanan Ketat', 'icon' => 'fa-solid fa-lock'],
                ['name' => 'Bebas Bocor/Banjir', 'icon' => 'fa-solid fa-house-crack'],
                ['name' => 'Loading Dock Tersedia', 'icon' => 'fa-solid fa-dolly'],
            ],
            'Lahan Kosong' => [
                ['name' => 'Potensi Tinggi', 'icon' => 'fa-solid fa-arrow-trend-up'],
                ['name' => 'Siap Bangun', 'icon' => 'fa-solid fa-trowel-bricks'],
                ['name' => 'Bentuk Tanah Simetris', 'icon' => 'fa-solid fa-square'],
                ['name' => 'Legalitas Aman', 'icon' => 'fa-solid fa-file-signature'],
                ['name' => 'Akses Mudah', 'icon' => 'fa-solid fa-route'],
            ],
            'Lahan Parkir' => [
                ['name' => 'Area Luas', 'icon' => 'fa-solid fa-square-parking'],
                ['name' => 'Mudah Bermanuver', 'icon' => 'fa-solid fa-car-side'],
                ['name' => 'Teduh', 'icon' => 'fa-solid fa-tree'],
                ['name' => 'Paving/Aspal Rata', 'icon' => 'fa-solid fa-road'],
                ['name' => 'Dekat Keramaian', 'icon' => 'fa-solid fa-people-group'],
            ],
            'Lahan Pertanian' => [
                ['name' => 'Tanah Subur', 'icon' => 'fa-solid fa-seedling'],
                ['name' => 'Irigasi Lancar', 'icon' => 'fa-solid fa-water'],
                ['name' => 'Dekat Sumber Air', 'icon' => 'fa-solid fa-faucet-drip'],
                ['name' => 'Hasil Panen Bagus', 'icon' => 'fa-solid fa-wheat-awn'],
                ['name' => 'Akses Alat Berat', 'icon' => 'fa-solid fa-tractor'],
            ],
            'Gedung' => [
                ['name' => 'Kapasitas Ribuan', 'icon' => 'fa-solid fa-users-rays'],
                ['name' => 'Full AC', 'icon' => 'fa-solid fa-snowflake'],
                ['name' => 'Sound System Megah', 'icon' => 'fa-solid fa-volume-high'],
                ['name' => 'Parkir VIP', 'icon' => 'fa-solid fa-car'],
                ['name' => 'Desain Mewah', 'icon' => 'fa-solid fa-gem'],
            ],
            'Aula' => [
                ['name' => 'Multifungsi', 'icon' => 'fa-solid fa-building'],
                ['name' => 'Panggung Tersedia', 'icon' => 'fa-solid fa-microphone'],
                ['name' => 'Harga Terjangkau', 'icon' => 'fa-solid fa-money-bill-wave'],
                ['name' => 'Mudah Didekorasi', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
                ['name' => 'Lokasi Tengah Kota', 'icon' => 'fa-solid fa-city'],
            ],
            'Ruang Meeting' => [
                ['name' => 'Suasana Profesional', 'icon' => 'fa-solid fa-briefcase'],
                ['name' => 'Proyektor & Wi-Fi', 'icon' => 'fa-solid fa-wifi'],
                ['name' => 'Kedap Suara', 'icon' => 'fa-solid fa-ear-deaf'],
                ['name' => 'Coffee Break Enak', 'icon' => 'fa-solid fa-mug-hot'],
                ['name' => 'Kursi Nyaman', 'icon' => 'fa-solid fa-chair'],
            ],
            'Studio' => [
                ['name' => 'Lighting Proper', 'icon' => 'fa-solid fa-lightbulb'],
                ['name' => 'Green Screen', 'icon' => 'fa-solid fa-image'],
                ['name' => 'Akustik Baik', 'icon' => 'fa-solid fa-music'],
                ['name' => 'Peralatan Lengkap', 'icon' => 'fa-solid fa-video'],
                ['name' => 'Ber-AC Dingin', 'icon' => 'fa-solid fa-snowflake'],
            ],
            'Baliho Digital' => [
                ['name' => 'Resolusi Tinggi', 'icon' => 'fa-solid fa-tv'],
                ['name' => 'Terang di Malam Hari', 'icon' => 'fa-solid fa-moon'],
                ['name' => 'Visual Menarik', 'icon' => 'fa-solid fa-eye'],
                ['name' => 'Update Materi Mudah', 'icon' => 'fa-solid fa-rotate'],
                ['name' => 'Lalu Lintas Padat', 'icon' => 'fa-solid fa-car-side'],
            ],
            'Baliho Konvensional' => [
                ['name' => 'Ukuran Raksasa', 'icon' => 'fa-solid fa-expand'],
                ['name' => 'Posisi Perempatan', 'icon' => 'fa-solid fa-crosshairs'],
                ['name' => 'Konstruksi Kokoh', 'icon' => 'fa-solid fa-screwdriver-wrench'],
                ['name' => 'Penerangan Bagus', 'icon' => 'fa-solid fa-lightbulb'],
                ['name' => 'Harga Kompetitif', 'icon' => 'fa-solid fa-tag'],
            ],
        ];

        foreach ($tags as $typeName => $typeTags) {
            if (isset($types[$typeName])) {
                $typeId = $types[$typeName]->id;
                foreach ($typeTags as $tag) {
                    review_tag::create([
                        'asset_type_id' => $typeId,
                        'name' => $tag['name'],
                    ]);
                }
            }
        }
    }
}
