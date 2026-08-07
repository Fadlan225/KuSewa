<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyAssetSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $owners = DB::table('owner_profiles')->orderBy('id')->take(5)->get();
        $types = DB::table('asset_types')->orderBy('id')->get()->keyBy('name');

        if ($owners->count() < 5 || $types->isEmpty()) {
            $this->command->error('Jalankan UsersSeeder, OwnerProfileSeeder, dan AssetTypeSeeder terlebih dahulu.');
            return;
        }

        DB::table('assets')->where('title', 'like', 'Demo Validasi -%')->delete();

        $rows = [
            ['title' => 'Demo Validasi - Kos Nyaman Samarinda', 'type' => 'Kos', 'city' => 'Samarinda', 'status' => 'pending'],
            ['title' => 'Demo Validasi - Ruko Strategis Balikpapan', 'type' => 'Ruko', 'city' => 'Balikpapan', 'status' => 'pending'],
            ['title' => 'Demo Validasi - Apartemen Modern Bandung', 'type' => 'Apartemen', 'city' => 'Bandung', 'status' => 'pending'],
            ['title' => 'Demo Validasi - Gedung Acara Jakarta', 'type' => 'Gedung', 'city' => 'Jakarta', 'status' => 'active'],
            ['title' => 'Demo Validasi - Gudang Logistik Surabaya', 'type' => 'Gudang', 'city' => 'Surabaya', 'status' => 'inactive'],
        ];

        foreach ($rows as $index => $row) {
            $type = $types->get($row['type']) ?? $types->first();

            DB::table('assets')->insert([
                'owner_profile_id' => $owners[$index]->id,
                'asset_type_id' => $type->id,
                'title' => $row['title'],
                'description' => 'Data dummy untuk melihat tampilan validasi aset admin.',
                'detail' => json_encode(['facility' => ['parkir', 'wifi'], 'capacity' => '10 orang']),
                'province' => 'Jawa Barat',
                'city' => $row['city'],
                'address' => 'Jl. Demo Validasi No. ' . ($index + 1) . ', ' . $row['city'],
                'latitude' => '-0.502200',
                'longitude' => '117.153600',
                'status' => $row['status'],
                'created_at' => now()->subDays(5 - $index),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ 5 data dummy pengajuan aset berhasil dibuat.');
    }
}
