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
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $faker = Faker::create('id_ID');
        $owners = owner_profile::all();
        $assetTypes = asset_type::all();

        if ($owners->isEmpty() || $assetTypes->isEmpty()) {
            $this->command->error('Owners atau Asset Types tidak ditemukan.');
            return;
        }

        $cities = ['Samarinda', 'Balikpapan', 'Bontang', 'Berau', 'Tenggarong', 'Sangatta', 'Sendawar', 'Penajam', 'Tanah Grogot', 'Ujoh Bilang'];

        $assets = [];
        $ownerCount = $owners->count();
        $assetIndex = 0;

        foreach ($assetTypes as $type) {
            // Minimal 2 asset per tipe
            for ($i = 1; $i <= 2; $i++) {
                $owner = $owners[$assetIndex % $ownerCount];
                $city = $faker->randomElement($cities);
                $title = $this->generateTitle($type->name, $city, $i, $faker);
                
                $assets[] = [
                    'owner_profile_id' => $owner->id,
                    'asset_type_id' => $type->id,
                    'title' => $title,
                    'slug' => Str::slug($title . '-' . uniqid()),
                    'description' => "Ini adalah deskripsi untuk $title. Menawarkan fasilitas terbaik di $city dengan harga terjangkau dan pelayanan memuaskan.",
                    'detail' => json_encode($this->generateDetail($type->name, $faker)),
                    'country' => 'Indonesia',
                    'province' => 'Kalimantan Timur',
                    'city' => $city,
                    'subdistrict' => $faker->streetName,
                    'postal_code' => $faker->postcode,
                    'address' => $faker->address,
                    'latitude' => (string)$faker->latitude(-1.26, 1.15),
                    'longitude' => (string)$faker->longitude(116.0, 118.0),
                    'status' => $faker->boolean(90) ? 'active' : 'inactive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $assetIndex++;
            }
        }

        DB::table('assets')->insert($assets);
        $this->command->info("✓ " . count($assets) . " Assets berhasil dibuat!");
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
