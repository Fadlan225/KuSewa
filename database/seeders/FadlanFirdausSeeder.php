<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\owner_profile;
use App\Models\asset;
use App\Models\asset_units;
use App\Models\asset_pricing;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FadlanFirdausSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Buat User Fadlan Firdaus
        $user = User::firstOrCreate(
            ['email' => 'fadlanfirdaus220@gmail.com'],
            [
                'name' => 'Fadlan Firdaus',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Owner Profile
        $ownerProfile = owner_profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'national_id' => '6472032709080004',
                'province_code' => '64', // Kaltim
                'city_code' => '6472', // Samarinda
                'district_code' => '647204', // Samarinda Ulu
                'village_code' => '6472041002', // Gunung Kelua
                'postal_code' => '75123',
                'address' => 'Jl AW Syahrani Gang 45',
                'status' => 'verified',
                'verification_at' => now()->subMonths(13),
            ]
        );

        // Bersihkan data lama dari owner ini jika ada
        $oldAssets = asset::where('owner_profile_id', $ownerProfile->id)->get();
        foreach($oldAssets as $oldAsset) {
            asset_pricing::where('asset_id', $oldAsset->id)->delete();
            asset_units::where('asset_id', $oldAsset->id)->delete();
            $oldAsset->delete();
        }

        // 3. Data FAQ & Kebijakan (Minimal 5 per aset)
        
        $hotelFaqs = [
            ['question' => 'Apakah sarapan sudah termasuk dalam harga kamar?', 'answer' => 'Ya, untuk tipe kamar tertentu sarapan sudah termasuk untuk 2 orang.', 'sort_order' => 1],
            ['question' => 'Jam berapa waktu check-in dan check-out?', 'answer' => 'Waktu check-in standar adalah pukul 14:00 WITA, dan check-out pukul 12:00 WITA.', 'sort_order' => 2],
            ['question' => 'Apakah hotel ini menyediakan layanan antar-jemput bandara?', 'answer' => 'Ya, kami menyediakan layanan antar-jemput bandara berbayar. Harap hubungi kami H-1.', 'sort_order' => 3],
            ['question' => 'Apakah tersedia kolam renang dan pusat kebugaran?', 'answer' => 'Tentu, tersedia kolam renang outdoor dan pusat kebugaran yang buka dari pukul 06:00 hingga 19:00 WITA.', 'sort_order' => 4],
            ['question' => 'Bisakah saya membawa hewan peliharaan menginap?', 'answer' => 'Mohon maaf, demi kenyamanan seluruh tamu, hewan peliharaan tidak diperkenankan dibawa menginap.', 'sort_order' => 5],
        ];
        $hotelPolicies = [
            ['title' => 'Identitas Resmi', 'description' => 'Tamu diwajibkan menunjukkan kartu identitas asli (KTP/Paspor) yang berlaku pada saat check-in.', 'sort_order' => 1],
            ['title' => 'Deposit Keamanan', 'description' => 'Deposit jaminan sebesar Rp 300.000 wajib dibayarkan saat check-in dan akan dikembalikan pada saat check-out.', 'sort_order' => 2],
            ['title' => 'Bebas Asap Rokok', 'description' => 'Dilarang merokok di dalam kamar. Pelanggaran aturan ini akan dikenakan denda sterilisasi ruangan.', 'sort_order' => 3],
            ['title' => 'Keamanan Barang', 'description' => 'Tamu tidak diizinkan membawa senjata tajam, obat-obatan terlarang, atau barang berbahaya lainnya ke dalam lingkungan hotel.', 'sort_order' => 4],
            ['title' => 'Tanggung Jawab Kerusakan', 'description' => 'Segala bentuk kerusakan pada properti hotel yang disebabkan oleh kelalaian tamu akan menjadi tanggung jawab tamu.', 'sort_order' => 5],
        ];

        $villaFaqs = [
            ['question' => 'Berapa kapasitas maksimal tamu untuk villa ini?', 'answer' => 'Kapasitas maksimal adalah 8 orang tamu. Lebih dari itu, disarankan menyewa kasur tambahan.', 'sort_order' => 1],
            ['question' => 'Apakah peralatan memasak disediakan di villa?', 'answer' => 'Ya, dapur villa telah dilengkapi dengan kompor, kulkas, rice cooker, serta alat masak dan makan.', 'sort_order' => 2],
            ['question' => 'Apakah kami bisa mengadakan pesta BBQ?', 'answer' => 'Bisa. Kami menyediakan alat panggangan BBQ secara gratis. Bahan makanan disediakan sendiri oleh tamu.', 'sort_order' => 3],
            ['question' => 'Bagaimana dengan privasi di area kolam renang?', 'answer' => 'Kolam renang kami 100% private, tidak terlihat dari luar dan hanya diperuntukkan bagi tamu villa yang menyewa.', 'sort_order' => 4],
            ['question' => 'Apakah area villa dekat dengan minimarket atau restoran?', 'answer' => 'Villa berada di lokasi strategis, hanya 5 menit berkendara menuju minimarket, cafe, dan restoran.', 'sort_order' => 5],
        ];
        $villaPolicies = [
            ['title' => 'Waktu Tenang (Quiet Hours)', 'description' => 'Dilarang membuat keributan yang berlebihan setelah pukul 22:00 WITA demi menghormati tetangga sekitar.', 'sort_order' => 1],
            ['title' => 'Acara Tertentu Dilarang', 'description' => 'Dilarang keras menyelenggarakan pesta yang melanggar hukum, perjudian, atau penggunaan obat-obatan terlarang.', 'sort_order' => 2],
            ['title' => 'Kebijakan Hewan Peliharaan', 'description' => 'Hewan peliharaan (anjing/kucing) diperbolehkan dengan syarat telah lapor kepada admin dan menjaga kebersihan.', 'sort_order' => 3],
            ['title' => 'Kapasitas Tamu', 'description' => 'Menyewa villa untuk keperluan acara/gathering berskala besar harus mendapatkan izin tertulis sebelumnya.', 'sort_order' => 4],
            ['title' => 'Kebersihan', 'description' => 'Tamu diharapkan membantu menjaga kebersihan villa, termasuk mencuci alat makan setelah pemakaian berat.', 'sort_order' => 5],
        ];

        $studioFaqs = [
            ['question' => 'Peralatan apa saja yang termasuk dalam biaya sewa?', 'answer' => 'Sewa sudah termasuk 3 set lighting profesional, trigger, C-stand, dan area makeup.', 'sort_order' => 1],
            ['question' => 'Berapa maksimal orang dalam satu sesi pemotretan?', 'answer' => 'Kapasitas maksimal 10 orang termasuk fotografer dan kru. Ada biaya tambahan jika melebihi kuota.', 'sort_order' => 2],
            ['question' => 'Apakah saya bisa memperpanjang waktu sewa secara mendadak?', 'answer' => 'Perpanjangan on-the-spot memungkinkan jika tidak ada booking di jadwal berikutnya.', 'sort_order' => 3],
            ['question' => 'Apakah tersedia berbagai warna background kertas?', 'answer' => 'Tersedia lebih dari 8 pilihan warna background. Mohon konfirmasi warna yang diinginkan sebelum hari H.', 'sort_order' => 4],
            ['question' => 'Apakah studio ini memiliki peredam suara (soundproof) untuk syuting video?', 'answer' => 'Area studio tertutup dengan sistem semi-soundproof, cukup baik untuk interview atau syuting konten harian.', 'sort_order' => 5],
        ];
        $studioPolicies = [
            ['title' => 'Aturan Waktu', 'description' => 'Waktu penyewaan dihitung sejak jam mulai booking, bukan saat kedatangan. Datang lebih awal tidak diperbolehkan jika ada sesi sebelum Anda.', 'sort_order' => 1],
            ['title' => 'Kebersihan Sepatu', 'description' => 'Penyewa dan kru diwajibkan mengganti sepatu atau membersihkan alas sepatu (dilakban) sebelum menginjak background kertas / cyclorama.', 'sort_order' => 2],
            ['title' => 'Larangan Makan & Minum', 'description' => 'Dilarang keras makan dan minum di atas area karpet maupun area background/cyclorama.', 'sort_order' => 3],
            ['title' => 'Tanggung Jawab Alat', 'description' => 'Segala kerusakan pada lighting, stand, atau noda yang tidak bisa dihilangkan pada cyclorama menjadi tanggung jawab penyewa sepenuhnya.', 'sort_order' => 4],
            ['title' => 'Pengosongan Studio', 'description' => 'Studio harus dikosongkan dan dikembalikan pada posisi semula paling lambat 5 menit sebelum waktu penyewaan berakhir.', 'sort_order' => 5],
        ];


        // 4. Buat Aset

        // ASET 1: Midtown Hotel Samarinda
        $hotel = asset::create([
            'owner_profile_id' => $ownerProfile->id,
            'asset_type_id' => 7, // Hotel
            'title' => 'Midtown Hotel Samarinda',
            'slug' => 'midtown-hotel-samarinda',
            'description' => "Midtown Hotel Samarinda adalah pilihan akomodasi berbintang yang elegan di jantung kota Samarinda. Menggabungkan arsitektur kontemporer dengan layanan kelas satu, hotel ini sangat pas untuk pebisnis maupun wisatawan. Kami menawarkan kamar yang luas, restoran eksklusif dengan masakan khas nusantara dan internasional, serta lounge yang nyaman untuk bersantai.\n\nSetiap kamar dilengkapi dengan Wi-Fi berkecepatan tinggi, Smart TV, minibar, dan amenitas mandi premium. Nikmati pengalaman menginap yang tak terlupakan bersama Midtown Hotel Samarinda.",
            'province_code' => '64',
            'city_code' => '6472',
            'district_code' => '647204',
            'village_code' => '6472041002',
            'postal_code' => '75117',
            'address' => 'Jl. Hasan Basri No.58, Bandara, Kec. Sungai Pinang, Kota Samarinda',
            'latitude' => '-0.485183',
            'longitude' => '117.151740',
            'detail' => [],
            'status' => 'approved',
        ]);
        $hotel->faqs()->createMany($hotelFaqs);
        $hotel->policies()->createMany($hotelPolicies);

        // ASET 2: Villa Alam Asri Samarinda (Populer)
        $villa = asset::create([
            'owner_profile_id' => $ownerProfile->id,
            'asset_type_id' => 2, // Villa
            'title' => 'Villa Alam Asri Samarinda',
            'slug' => 'villa-alam-asri-samarinda',
            'description' => "Villa Alam Asri adalah tempat pelarian sempurna yang menyejukkan. Terletak di kawasan eksklusif yang asri, villa ini mengusung perpaduan gaya modern tropis yang menyatu dengan alam. \n\nDilengkapi fasilitas bintang lima termasuk kolam renang pribadi, gazebo santai, dapur modern fully-equipped, hingga area living room terbuka yang luas. Tersedia 3 kamar tidur yang didesain memanjakan dan nyaman. Sangat ideal untuk staycation, liburan keluarga, maupun kumpul santai dengan teman di akhir pekan.",
            'province_code' => '64',
            'city_code' => '6472',
            'district_code' => '647206',
            'village_code' => '6472061001',
            'postal_code' => '75119',
            'address' => 'Jl. PM Noor Perumahan Bumi Sempaja, Samarinda Utara',
            'latitude' => '-0.457812',
            'longitude' => '117.159345',
            'detail' => [],
            'status' => 'approved',
        ]);
        $villa->faqs()->createMany($villaFaqs);
        $villa->policies()->createMany($villaPolicies);

        // ASET 3: Kencana Creative Studio (Internet Real Data style)
        $studio = asset::create([
            'owner_profile_id' => $ownerProfile->id,
            'asset_type_id' => 20, // Studio
            'title' => 'Kencana Creative Studio',
            'slug' => 'kencana-creative-studio',
            'description' => "Kencana Creative Studio adalah ruang tematik dan cyclorama premium untuk segala kebutuhan visual Anda. Ruang studio kami sangat cocok untuk pemotretan fashion, foto produk, sesi keluarga, prewedding indoor, maupun produksi video klip skala kecil.\n\nDengan penyewaan studio, Anda sudah mendapatkan akses penuh ke peralatan lighting Godox (3 titik cahaya), berbagai aksesoris pembentuk cahaya (softbox, beauty dish, payung), C-stands, serta ruang rias dan ruang ganti yang nyaman. Hadirkan kreativitas Anda tanpa batas bersama kami.",
            'province_code' => '64',
            'city_code' => '6472',
            'district_code' => '647204',
            'village_code' => '6472041002',
            'postal_code' => '75123',
            'address' => 'Jl. A.W. Sjahranie No. 12, Sempaja Selatan, Samarinda',
            'latitude' => '-0.468305',
            'longitude' => '117.143212',
            'detail' => [],
            'status' => 'approved',
        ]);
        $studio->faqs()->createMany($studioFaqs);
        $studio->policies()->createMany($studioPolicies);

        // 5. Buat Unit & Pricing
        
        // --- Units for Hotel ---
        $hotelUnits = [
            ['name' => 'Superior Twin Bed Room', 'quantity' => 15, 'price' => 485000],
            ['name' => 'Deluxe King Room with City View', 'quantity' => 10, 'price' => 675000],
            ['name' => 'Executive Suite Room', 'quantity' => 3, 'price' => 1250000],
        ];
        foreach($hotelUnits as $hu) {
            $u = asset_units::create([
                'asset_id' => $hotel->id,
                'name' => $hu['name'],
                'detail' => [],
                'quantity' => $hu['quantity'],
                'status' => 'active',
            ]);
            asset_pricing::create([
                'asset_id' => $hotel->id,
                'asset_unit_id' => $u->id,
                'duration' => 1,
                'rental_unit' => 'night',
                'price' => $hu['price'],
            ]);
        }

        // --- Units for Villa ---
        $villaUnits = [
            ['name' => 'Entire 3-Bedroom Villa with Private Pool', 'quantity' => 1, 'price' => 2500000],
        ];
        foreach($villaUnits as $vu) {
            $u = asset_units::create([
                'asset_id' => $villa->id,
                'name' => $vu['name'],
                'detail' => [],
                'quantity' => $vu['quantity'],
                'status' => 'active',
            ]);
            asset_pricing::create([
                'asset_id' => $villa->id,
                'asset_unit_id' => $u->id,
                'duration' => 1,
                'rental_unit' => 'night',
                'price' => $vu['price'],
            ]);
        }

        // --- Units for Studio ---
        $studioUnits = [
            ['name' => 'Cyclorama Putih - Sewa per Jam', 'quantity' => 1, 'duration' => 1, 'rental_unit' => 'hour', 'price' => 125000], 
            ['name' => 'Ruang Tematik Klasik - Sewa per Jam', 'quantity' => 1, 'duration' => 1, 'rental_unit' => 'hour', 'price' => 175000],
            ['name' => 'Sewa Studio Seharian (10 Jam)', 'quantity' => 1, 'duration' => 1, 'rental_unit' => 'day', 'price' => 1100000],
        ];
        foreach($studioUnits as $su) {
            $u = asset_units::create([
                'asset_id' => $studio->id,
                'name' => $su['name'],
                'detail' => [],
                'quantity' => $su['quantity'],
                'status' => 'active',
            ]);
            asset_pricing::create([
                'asset_id' => $studio->id,
                'asset_unit_id' => $u->id,
                'duration' => $su['duration'],
                'rental_unit' => $su['rental_unit'],
                'price' => $su['price'],
            ]);
        }
        
        // Catatan: Tidak ada data booking yang dibuat. Seeder difokuskan memberikan aset realistis untuk owner baru.
    }
}
