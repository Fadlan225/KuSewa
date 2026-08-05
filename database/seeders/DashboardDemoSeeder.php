<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\booking;
use App\Models\User;
use App\Models\asset;
use App\Models\owner_profile;

class DashboardDemoSeeder extends Seeder
{
  public function run(): void
  {
    $user = User::where('role', 'owner')->first();

    if (!$user) {
      $this->command?->error('No owner user found!');
      return;
    }

    // --- 1. Properties ---
    $props = [
      ['title' => 'Kos Exclusive Samarinda Indah #01', 'category' => 'Hunian & Tempat Tinggal', 'type' => 'Kos-kosan', 'price' => 1350000, 'rent_period' => 'Bulan', 'city' => 'Samarinda', 'address' => 'Jl. M. Yamin No. 12', 'status' => 'Tersewa', 'verification_status' => 'approved', 'tenant' => 'Ahmad Rizky'],
      ['title' => 'Apartemen Orchard Tower B12', 'category' => 'Hunian & Tempat Tinggal', 'type' => 'Apartemen', 'price' => 3500000, 'rent_period' => 'Bulan', 'city' => 'Balikpapan', 'address' => 'Jl. Sudirman No. 88', 'status' => 'Tersewa', 'verification_status' => 'approved', 'tenant' => 'Siti Rahma'],
      ['title' => 'Kos Melati Clean #05', 'category' => 'Hunian & Tempat Tinggal', 'type' => 'Kos-kosan', 'price' => 850000, 'rent_period' => 'Bulan', 'city' => 'Samarinda', 'address' => 'Jl. Pramuka 6 No. 44', 'status' => 'Tersedia', 'verification_status' => 'approved', 'tenant' => null],
      ['title' => 'Ruko Strategis Pasar Pagi', 'category' => 'Komersial & Usaha', 'type' => 'Ruko (Rumah Toko)', 'price' => 5000000, 'rent_period' => 'Bulan', 'city' => 'Samarinda', 'address' => 'Jl. Pasar Pagi No. 5', 'status' => 'Tersedia', 'verification_status' => 'pending', 'tenant' => null],
      ['title' => 'Gudang Logistik Ringroad', 'category' => 'Penyimpanan & Industri', 'type' => 'Gudang Logistik', 'price' => 15000000, 'rent_period' => 'Tahun', 'city' => 'Balikpapan', 'address' => 'Jl. Ringroad Km 8', 'status' => 'Tersedia', 'verification_status' => 'approved', 'tenant' => null],
      ['title' => 'Rumah Kontrakan Minimalis A2', 'category' => 'Hunian & Tempat Tinggal', 'type' => 'Rumah Tapak', 'price' => 25000000, 'rent_period' => 'Tahun', 'city' => 'Samarinda', 'address' => 'Jl. Juanda 8 Blok B', 'status' => 'Tersewa', 'verification_status' => 'approved', 'tenant' => 'Rava Nanda'],
      ['title' => 'Baliho Jalan Protokol', 'category' => 'Media Iklan & Ruang Promosi', 'type' => 'Baliho / Reklame', 'price' => 3000000, 'rent_period' => 'Bulan', 'city' => 'Samarinda', 'address' => 'Jl. Ahmad Yani', 'status' => 'Tersedia', 'verification_status' => 'rejected', 'tenant' => null, 'verification_note' => 'Foto kurang jelas, mohon upload ulang'],
    ];

    $createdProps = [];
    foreach ($props as $d) {
      $d['user_id'] = $user->id;
      $p = Property::create($d);
      $createdProps[] = $p;
    }

    $this->command?->info('7 properties created.');

    // --- 2. Pastikan owner_profile ada ---
    $profile = owner_profile::updateOrCreate(
      ['user_id' => $user->id],
      ['status' => 'verified', 'address' => 'Samarinda', 'national_id' => '6471000000002001', 'place_of_birth' => 'Samarinda', 'date_of_birth' => '1995-03-22', 'ktp_photo' => 'ktp/dummy.jpg']
    );

    // --- 3. Pastikan asset terkait ada (dibutuhkan booking) ---
    $ownerAsset = asset::firstOrCreate(
      ['owner_profile_id' => $profile->id, 'title' => 'Asset Default'],
      ['asset_type_id' => 1, 'status' => 'active']
    );

    // --- 4. Bookings ---
    $months = [
      ['month' => now()->subMonths(5), 'status' => 'completed', 'total' => 1200000],
      ['month' => now()->subMonths(4), 'status' => 'completed', 'total' => 2500000],
      ['month' => now()->subMonths(3), 'status' => 'completed', 'total' => 1800000],
      ['month' => now()->subMonths(2), 'status' => 'completed', 'total' => 4200000],
      ['month' => now()->subMonths(1), 'status' => 'completed', 'total' => 3100000],
      ['month' => now(), 'status' => 'completed', 'total' => 5000000],
      ['month' => now(), 'status' => 'confirmed', 'total' => 1350000],
      ['month' => now(), 'status' => 'confirmed', 'total' => 3500000],
      ['month' => now(), 'status' => 'pending', 'total' => 850000],
      ['month' => now(), 'status' => 'pending', 'total' => 5000000],
    ];

    foreach ($months as $i => $m) {
      booking::create([
        'asset_id' => $ownerAsset->id,
        'booking_code' => 'BOOK-' . strtoupper(uniqid()),
        'user_id' => $user->id,
        'start_date' => $m['month']->copy()->startOfMonth(),
        'end_date' => $m['month']->copy()->endOfMonth(),
        'subtotal' => $m['total'],
        'service_fee' => 5000,
        'total' => $m['total'] + 5000,
        'booking_status' => $m['status'],
        'created_at' => $m['month'],
        'updated_at' => $m['month'],
      ]);
    }

    $this->command?->info('10 bookings created (6 completed, 2 confirmed, 2 pending).');
    $this->command?->info('Dashboard demo data ready!');
  }
}