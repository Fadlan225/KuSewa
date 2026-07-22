<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Total 130 users:
     * - 1  Admin    : admin@kusewa.com (role=admin)
     * - 30 Owner    : owner1@kusewa.com … owner30@kusewa.com (role=customer, punya owner_profile)
     * - 99 Customer : customer1@kusewa.com … customer99@kusewa.com (role=customer, tidak punya owner_profile)
     *
     * Owner bukan role terpisah — ditentukan dari keberadaan record di tabel owner_profiles.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $rows = [];

        // ── 1 Admin ────────────────────────────────────────────────────────────
        $rows[] = [
            'name'              => 'Admin KuSewa',
            'email'             => 'admin@kusewa.com',
            'phone'             => '081200000000',
            'email_verified_at' => $now,
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'status'            => 'active',
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        // ── 30 Owner (role=customer, akan punya owner_profile) ─────────────────
        $ownerNames = [
            'Budi Santoso', 'Siti Rahma', 'Agus Prasetyo', 'Dewi Lestari', 'Eko Wahyudi',
            'Fitri Handayani', 'Gunawan Saputra', 'Hana Kusuma', 'Irfan Hakim', 'Joko Susilo',
            'Kartini Wulandari', 'Lukman Hakim', 'Mega Putri', 'Nanda Setiawan', 'Oki Pratama',
            'Putri Maharani', 'Qori Annisa', 'Rudi Hermawan', 'Sari Melati', 'Tono Hidayat',
            'Umi Kalsum', 'Vino Bastian', 'Wati Susanti', 'Xandra Wijaya', 'Yanto Purnomo',
            'Zahra Fadilla', 'Aldi Nugraha', 'Bella Safitri', 'Cahyo Prabowo', 'Diana Permata',
        ];
        $ownerPhones = [
            '08115001001','08115001002','08115001003','08115001004','08115001005',
            '08115001006','08115001007','08115001008','08115001009','08115001010',
            '08115001011','08115001012','08115001013','08115001014','08115001015',
            '08115001016','08115001017','08115001018','08115001019','08115001020',
            '08115001021','08115001022','08115001023','08115001024','08115001025',
            '08115001026','08115001027','08115001028','08115001029','08115001030',
        ];
        for ($i = 0; $i < 30; $i++) {
            $rows[] = [
                'name'              => $ownerNames[$i],
                'email'             => 'owner' . ($i + 1) . '@kusewa.com',
                'phone'             => $ownerPhones[$i],
                'email_verified_at' => $now,
                'password'          => Hash::make('password'),
                'role'              => 'customer', // Owner bukan role terpisah
                'status'            => 'active',
                'created_at'        => $now->copy()->subDays(rand(90, 500)),
                'updated_at'        => $now,
            ];
        }

        // ── 99 Customer (role=customer, tidak punya owner_profile) ─────────────
        $customerNames = [
            'Achmad Fauzi', 'Aditya Permana', 'Agung Wibowo', 'Aini Rahmawati', 'Alfi Nuraini',
            'Ananda Putri', 'Andika Pratama', 'Andriani Sari', 'Annisa Aulia', 'Ari Wicaksono',
            'Arief Budiman', 'Arini Maharani', 'Arman Saleh', 'Asep Kurniawan', 'Ayu Lestari',
            'Bagas Nugroho', 'Bayu Saputra', 'Bima Setiadi', 'Bunga Pertiwi', 'Citra Dewi',
            'Dani Firmansyah', 'Desi Ratnasari', 'Dicky Ramadhan', 'Dinda Amelia', 'Dita Puspita',
            'Dwi Susanto', 'Ega Rahayu', 'Elsa Fitriani', 'Endra Wijaya', 'Ervan Maulana',
            'Eva Nurlaila', 'Fahri Akbar', 'Fajar Hidayat', 'Farah Amalia', 'Fauzan Ilham',
            'Feby Anggraini', 'Ferdi Hasan', 'Fira Nurazizah', 'Galang Pratama', 'Galih Kusuma',
            'Gina Puspitasari', 'Hafiz Rahman', 'Hani Safitri', 'Harun Nasution', 'Hendra Gunawan',
            'Hilmy Maulana', 'Ibnu Sina', 'Ika Permatasari', 'Ilham Naufal', 'Indah Sari',
            'Irham Hidayat', 'Ivan Prasetya', 'Jefri Simanjuntak', 'Karina Maharani', 'Kevin Santoso',
            'Kiki Amelia', 'Kurnia Sari', 'Laila Fitriani', 'Lina Anggraeni', 'Luthfi Hakim',
            'Mahfud Ibrahim', 'Maulana Yusuf', 'Mira Rahayu', 'Muhammad Rizki', 'Nadia Putri',
            'Naufal Fadillah', 'Nisa Aulia', 'Noval Hermawan', 'Novi Ratnasari', 'Nurul Hidayah',
            'Oscar Pradipta', 'Panji Nugroho', 'Putri Amalia', 'Rahmat Hidayat', 'Raihan Akbar',
            'Raisa Andriani', 'Ramdan Maulana', 'Rara Setiawati', 'Restu Febrianto', 'Rian Permana',
            'Ridho Alfian', 'Rifki Zulkarnain', 'Rina Kusuma', 'Rio Satria', 'Rizal Fauzan',
            'Rosa Melliana', 'Sandi Kusuma', 'Sela Damayanti', 'Septian Nugroho', 'Shinta Dewi',
            'Sigit Purnomo', 'Silvia Andriana', 'Sinta Rahayu', 'Syahrul Gunawan', 'Tiara Putri',
            'Tito Maulana', 'Wahyu Ramadhan', 'Winda Sari', 'Yoga Pratama',
        ];
        for ($i = 0; $i < 99; $i++) {
            $rows[] = [
                'name'              => $customerNames[$i],
                'email'             => 'customer' . ($i + 1) . '@kusewa.com',
                'phone'             => '0812' . str_pad((string)(2000000 + $i), 7, '0', STR_PAD_LEFT),
                'email_verified_at' => $now,
                'password'          => Hash::make('password'),
                'role'              => 'customer',
                'status'            => 'active',
                'created_at'        => $now->copy()->subDays(rand(10, 300)),
                'updated_at'        => $now,
            ];
        }

        // Insert semua sekaligus
        DB::table('users')->insert($rows);

        $total = count($rows);
        $this->command->info("✓ {$total} users berhasil dibuat! (1 admin + 30 owner + 99 customer)");
    }
}
