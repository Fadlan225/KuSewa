<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\owner_profile;
use App\Models\asset;
use App\Models\asset_units;
use App\Models\booking;
use Carbon\Carbon;
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

        // 3. Buat Aset
        $asset1 = asset::firstOrCreate(
            ['slug' => 'ruko-suka-maju'],
            [
                'owner_profile_id' => $ownerProfile->id,
                'asset_type_id' => 1,
                'title' => 'Ruko Suka Maju',
                'description' => 'Ruko strategis untuk usaha bisnis Anda.',
                'province_code' => '64',
                'city_code' => '6472',
                'district_code' => '647204',
                'village_code' => '6472041002',
                'postal_code' => '75123',
                'address' => 'Jl AW Syahrani Gang 45',
                'latitude' => '-0.468205',
                'longitude' => '117.142322',
                'detail' => [],
                'status' => 'approved',
            ]
        );

        $asset2 = asset::firstOrCreate(
            ['slug' => 'hotel-suka-maju'],
            [
                'owner_profile_id' => $ownerProfile->id,
                'asset_type_id' => 2,
                'title' => 'Hotel Suka Maju',
                'description' => 'Hotel nyaman dengan fasilitas lengkap.',
                'province_code' => '64',
                'city_code' => '6472',
                'district_code' => '647204',
                'village_code' => '6472041002',
                'postal_code' => '75123',
                'address' => 'Jl AW Syahrani Gang 45',
                'latitude' => '-0.468205',
                'longitude' => '117.142322',
                'detail' => [],
                'status' => 'approved',
            ]
        );

        // 4. Buat Unit Aset
        $unit1 = asset_units::firstOrCreate(
            ['asset_id' => $asset1->id, 'name' => 'Ruko Lantai 1'],
            [
                'detail' => [],
                'quantity' => 2,
                'status' => 'active',
            ]
        );

        $unit2 = asset_units::firstOrCreate(
            ['asset_id' => $asset2->id, 'name' => 'Kamar Standard'],
            [
                'detail' => [],
                'quantity' => 5,
                'status' => 'active',
            ]
        );

        // Bersihkan booking lama untuk aset Budi (jika di-run berulang kali)
        booking::whereIn('asset_id', [$asset1->id, $asset2->id])->delete();

        // 5. Buat Bookings untuk Data Chart (1 Tahun Terakhir Intensif)
        $now = Carbon::now();

        // Buat data intensif untuk 90 hari terakhir agar chart 7, 30, dan 90 hari penuh
        for ($i = 0; $i <= 90; $i++) {
            // 85% peluang ada booking per hari
            if (rand(1, 100) <= 85) {
                $day = $now->copy()->subDays($i);
                $jumlahBooking = rand(1, 4); // 1 sampai 4 booking per hari

                for ($j = 0; $j < $jumlahBooking; $j++) {
                    $amount = rand(500000, 4500000);

                    booking::create([
                        'asset_id' => $i % 2 == 0 ? $asset1->id : $asset2->id,
                        'asset_unit_id' => $i % 2 == 0 ? $unit1->id : $unit2->id,
                        'asset_name' => $i % 2 == 0 ? $asset1->title : $asset2->title,
                        'asset_unit_name' => $i % 2 == 0 ? $unit1->name : $unit2->name,
                        'booking_code' => 'BK-' . strtoupper(Str::random(6)),
                        'booker_name' => 'Guest ' . $i . $j,
                        'booker_phone' => '0899999999',
                        'booker_email' => 'guest' . $i . $j . '@example.com',
                        'guest_name' => 'Guest ' . $i . $j,
                        'user_id' => $user->id,
                        'start_date' => $day->copy()->toDateString(),
                        'end_date' => $day->copy()->addDays(3)->toDateString(),
                        'subtotal' => $amount,
                        'service_fee' => 0,
                        'total' => $amount,
                        'booking_status' => 'completed',
                        'updated_at' => $day,
                        'created_at' => $day,
                    ]);
                }
            }
        }

        // Buat data bulanan untuk sisa tahun (agar chart 1 tahun juga penuh)
        for ($i = 3; $i <= 11; $i++) {
            $month = $now->copy()->subMonths($i);
            $jumlahBooking = rand(15, 35); // 15 sampai 35 booking per bulan

            for ($j = 0; $j < $jumlahBooking; $j++) {
                $day = $month->copy()->startOfMonth()->addDays(rand(1, 27));
                $amount = rand(500000, 4500000);

                booking::create([
                    'asset_id' => $i % 2 == 0 ? $asset1->id : $asset2->id,
                    'asset_unit_id' => $i % 2 == 0 ? $unit1->id : $unit2->id,
                    'asset_name' => $i % 2 == 0 ? $asset1->title : $asset2->title,
                    'asset_unit_name' => $i % 2 == 0 ? $unit1->name : $unit2->name,
                    'booking_code' => 'BK-' . strtoupper(Str::random(6)),
                    'booker_name' => 'Monthly Guest ' . $i . $j,
                    'booker_phone' => '0899999999',
                    'booker_email' => 'monthly' . $i . $j . '@example.com',
                    'guest_name' => 'Monthly Guest ' . $i . $j,
                    'user_id' => $user->id,
                    'start_date' => $day->copy()->toDateString(),
                    'end_date' => $day->copy()->addDays(3)->toDateString(),
                    'subtotal' => $amount,
                    'service_fee' => 0,
                    'total' => $amount,
                    'booking_status' => 'completed',
                    'updated_at' => $day,
                    'created_at' => $day,
                ]);
            }
        }

        // 6. Buat Booking Aktif (Sedang disewa hari ini)
        for ($i = 0; $i < 3; $i++) {
            booking::create([
                'asset_id' => $asset1->id,
                'asset_unit_id' => $unit1->id,
                'asset_name' => $asset1->title,
                'asset_unit_name' => $unit1->name,
                'booking_code' => 'BK-ACT-' . $i,
                'booker_name' => 'Active Renter ' . $i,
                'booker_phone' => '088888',
                'booker_email' => 'active' . $i . '@example.com',
                'guest_name' => 'Active Renter ' . $i,
                'user_id' => $user->id,
                'start_date' => Carbon::now()->subDays(2)->toDateString(),
                'end_date' => Carbon::now()->addDays(5)->toDateString(),
                'subtotal' => 1500000,
                'service_fee' => 0,
                'total' => 1500000,
                'booking_status' => 'active', // Status aktif
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        }

        // 7. Buat Booking Pending
        for ($i = 0; $i < 2; $i++) {
            booking::create([
                'asset_id' => $asset2->id,
                'asset_unit_id' => $unit2->id,
                'asset_name' => $asset2->title,
                'asset_unit_name' => $unit2->name,
                'booking_code' => 'BK-PND-' . $i,
                'booker_name' => 'Pending Renter ' . $i,
                'booker_phone' => '0877777',
                'booker_email' => 'pending' . $i . '@example.com',
                'guest_name' => 'Pending Renter ' . $i,
                'user_id' => $user->id,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(15)->toDateString(),
                'subtotal' => 3000000,
                'service_fee' => 0,
                'total' => 3000000,
                'booking_status' => 'pending', // Status pending
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        }
    }
}
