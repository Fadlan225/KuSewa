<?php

namespace Database\Seeders;

use App\Models\asset_type;
use App\Models\owner_profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('assets')->truncate();
        DB::table('asset_faqs')->truncate();
        DB::table('asset_policies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');
        $owners = owner_profile::all();
        $assetTypes = asset_type::all();

        if ($owners->isEmpty() || $assetTypes->isEmpty()) {
            $this->command->error('Owners atau Asset Types tidak ditemukan.');
            return;
        }

        $dbCities = DB::table('cities')->where('province_code', '64')->inRandomOrder()->limit(4)->get();
        if ($dbCities->isEmpty()) {
            $this->command->error('Data kota di Kaltim tidak ditemukan. Pastikan WilayahSeeder sudah dijalankan.');
            return;
        }
        
        $cityData = [];
        foreach ($dbCities as $dbCity) {
            $district = DB::table('districts')->where('city_code', $dbCity->code)->inRandomOrder()->first();
            $village = $district ? DB::table('villages')->where('district_code', $district->code)->inRandomOrder()->first() : null;
            $cityData[] = [
                'code' => $dbCity->code,
                'name' => $dbCity->name,
                'district_code' => $district ? $district->code : '647204',
                'village_code' => $village ? $village->code : '6472041002',
            ];
        }

        $assets = [];
        $ownerCount = $owners->count();
        $assetIndex = 0;

        foreach ($assetTypes as $type) {
            foreach ($cityData as $city) {
                // Minimal 5 asset per tipe per kota
                for ($i = 1; $i <= 5; $i++) {
                    $owner = $owners[$assetIndex % $ownerCount];
                    $title = $this->generateTitle($type->name, $city['name'], $i, $faker);
                    
                    $assets[] = [
                        'owner_profile_id' => $owner->id,
                        'asset_type_id' => $type->id,
                        'title' => $title,
                        'slug' => Str::slug($title . '-' . uniqid()),
                        'description' => "Ini adalah deskripsi untuk $title. Menawarkan fasilitas terbaik di {$city['name']} dengan harga terjangkau dan pelayanan memuaskan.",
                        'detail' => json_encode($this->generateDetail($type->name, $faker)),
                        'province_code' => '64',
                        'city_code' => $city['code'],
                        'district_code' => $city['district_code'],
                        'village_code' => $city['village_code'],
                        'postal_code' => $faker->postcode,
                        'address' => $faker->address,
                        'latitude' => (string)(strpos($city['name'], 'Balikpapan') !== false ? $faker->latitude(-1.28, -1.20) : (strpos($city['name'], 'Samarinda') !== false ? $faker->latitude(-0.55, -0.45) : $faker->latitude(-1.26, 1.15))),
                        'longitude' => (string)(strpos($city['name'], 'Balikpapan') !== false ? $faker->longitude(116.80, 116.90) : (strpos($city['name'], 'Samarinda') !== false ? $faker->longitude(117.10, 117.20) : $faker->longitude(116.0, 118.0))),
                        'status' => $faker->boolean(90) ? 'approved' : 'inactive',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $assetIndex++;
                }
            }
        }

        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($assets, 500);
        foreach ($chunks as $chunk) {
            DB::table('assets')->insert($chunk);
        }

        $this->command->info("✓ " . count($assets) . " Assets berhasil dibuat!");

        // Generate FAQs & Policies
        $insertedAssets = DB::table('assets')->select('id')->get();
        $faqs = [];
        $policies = [];

        foreach ($insertedAssets as $asset) {
            // 5 FAQs per asset
            for ($j = 1; $j <= 5; $j++) {
                $faqs[] = [
                    'asset_id' => $asset->id,
                    'question' => "Pertanyaan umum ke-$j terkait aset ini?",
                    'answer' => "Ini adalah jawaban standar untuk pertanyaan ke-$j yang menjelaskan detail dan ketentuan layanan.",
                    'sort_order' => $j,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // 10 Kebijakan per asset
            for ($k = 1; $k <= 10; $k++) {
                $policies[] = [
                    'asset_id' => $asset->id,
                    'title' => "Kebijakan Sewa #$k",
                    'description' => "Penjelasan aturan dan kebijakan nomor $k yang harus dipatuhi oleh penyewa.",
                    'sort_order' => $k,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $faqChunks = array_chunk($faqs, 500);
        foreach ($faqChunks as $chunk) {
            DB::table('asset_faqs')->insert($chunk);
        }
        $this->command->info("✓ " . count($faqs) . " FAQs berhasil dibuat!");

        $policyChunks = array_chunk($policies, 500);
        foreach ($policyChunks as $chunk) {
            DB::table('asset_policies')->insert($chunk);
        }
        $this->command->info("✓ " . count($policies) . " Policies berhasil dibuat!");
    }

    private function generateTitle($typeName, $city, $index, $faker)
    {
        $adjectives = ['Indah', 'Asri', 'Megah', 'Sejahtera', 'Nyaman', 'Kreatif', 'Logistic', 'Amanah', 'Jaya', 'Sentosa', 'Borneo', 'Khatulistiwa'];
        $adj = $faker->randomElement($adjectives);
        
        if (in_array($typeName, ['Rumah', 'Villa', 'Kontrakan'])) {
            return "$typeName $adj $city " . $faker->lastName;
        }
        if (in_array($typeName, ['Hotel', 'Resort', 'Apartemen', 'Homestay', 'Guest House', 'Kos'])) {
            return "$typeName $adj $city";
        }
        if (in_array($typeName, ['Gudang', 'Ruko', 'Toko', 'Kios'])) {
            return "$typeName $adj $city";
        }
        if ($typeName == 'Baliho Digital' || $typeName == 'Baliho Konvensional') {
            return "Baliho Simpang " . $faker->streetName . " $city";
        }
        return "$typeName $adj $city $index";
    }

    private function generateDetail($typeName, $faker)
    {
        $detail = [];
        if (in_array($typeName, ['Rumah', 'Villa', 'Kontrakan'])) {
            $detail['luas_bangunan'] = $faker->numberBetween(50, 300) . ' m2';
            $detail['luas_tanah'] = $faker->numberBetween(70, 500) . ' m2';
            $detail['jumlah_lantai'] = $faker->numberBetween(1, 3);
            $detail['tahun_dibangun'] = $faker->numberBetween(2010, 2023);
            $detail['daya_listrik'] = $faker->randomElement(['900 VA', '1300 VA', '2200 VA', '3500 VA']);
            $detail['sumber_air'] = $faker->randomElement(['PDAM', 'Sumur Bor']);
            $detail['kapasitas_parkir'] = $faker->numberBetween(1, 4) . ' Mobil';
            $detail['kapasitas_tamu'] = $faker->numberBetween(2, 10) . ' Orang';
            if ($typeName == 'Villa') {
                $detail['view'] = $faker->randomElement(['Pantai', 'Pegunungan', 'Hutan', 'Kota']);
            }
        } elseif (in_array($typeName, ['Hotel', 'Resort'])) {
            $detail['bintang'] = $faker->numberBetween(1, 5);
            $detail['luas_bangunan'] = $faker->numberBetween(1000, 5000) . ' m2';
            $detail['luas_tanah'] = $faker->numberBetween(2000, 10000) . ' m2';
            $detail['lantai'] = $faker->numberBetween(3, 15);
            $detail['total_ruangan'] = $faker->numberBetween(50, 200);
            $detail['checkin'] = '14:00';
            $detail['checkout'] = '12:00';
        } elseif (in_array($typeName, ['Apartemen', 'Kos', 'Homestay', 'Guest House'])) {
            $detail['luas_bangunan'] = $faker->numberBetween(200, 2000) . ' m2';
            $detail['lantai'] = $faker->numberBetween(2, 10);
            $detail['tahun_dibangun'] = $faker->numberBetween(2015, 2023);
            $detail['kapasitas_parkir'] = $faker->numberBetween(10, 50) . ' Kendaraan';
            if ($typeName == 'Kos') {
                $detail['aturan_jam_malam'] = $faker->boolean;
                $detail['parkir_motor'] = true;
            }
        } elseif (in_array($typeName, ['Gudang'])) {
            $detail['luas_bangunan'] = $faker->numberBetween(500, 2000) . ' m2';
            $detail['luas_tanah'] = $faker->numberBetween(1000, 5000) . ' m2';
            $detail['tinggi_langit_langit'] = $faker->numberBetween(6, 12) . ' Meter';
            $detail['tahun_dibangun'] = $faker->numberBetween(2010, 2022);
        } elseif (in_array($typeName, ['Ruko', 'Toko', 'Kios'])) {
            $detail['luas_bangunan'] = $faker->numberBetween(20, 150) . ' m2';
            $detail['lantai'] = $faker->numberBetween(1, 3);
            $detail['kamar_mandi'] = $faker->boolean;
            $detail['daya_listrik'] = $faker->randomElement(['1300 VA', '2200 VA', '4400 VA']);
        } elseif (in_array($typeName, ['Lahan Kosong', 'Lahan Parkir', 'Lahan Pertanian'])) {
            $detail['luas_tanah'] = $faker->numberBetween(100, 10000) . ' m2';
            $detail['sertifikat'] = $faker->randomElement(['SHM', 'HGB', 'AJB']);
            $detail['kontur_tanah'] = $faker->randomElement(['Datar', 'Miring', 'Bukit']);
        } elseif (in_array($typeName, ['Gedung', 'Aula', 'Ruang Meeting'])) {
            $detail['kapasitas_orang'] = $faker->numberBetween(20, 1000) . ' Orang';
            $detail['luas_ruangan'] = $faker->numberBetween(50, 2000) . ' m2';
            $detail['tinggi_plafon'] = $faker->numberBetween(3, 8) . ' Meter';
        } elseif ($typeName == 'Studio') {
            $detail['luas_bangunan'] = $faker->numberBetween(30, 150) . ' m2';
            $detail['lantai'] = $faker->numberBetween(1, 3);
            $detail['tahun_dibangun'] = $faker->numberBetween(2015, 2023);
            $detail['soundproof'] = true;
            $detail['air_conditioner'] = true;
        } elseif (strpos($typeName, 'Baliho') !== false) {
            $detail['width'] = $faker->numberBetween(3, 6) . ' Meter';
            $detail['height'] = $faker->numberBetween(4, 8) . ' Meter';
            if ($typeName == 'Baliho Digital') {
                $detail['resolution'] = '1920x1080';
            }
            $detail['sisi'] = $faker->numberBetween(1, 2);
            $detail['orientation'] = $faker->randomElement(['Vertical', 'Horizontal']);
            $detail['lighting'] = true;
        } else {
            $detail['keterangan'] = 'Detail untuk ' . $typeName;
        }
        return $detail;
    }
}
