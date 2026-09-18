<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HelpCategory;
use App\Models\HelpArticle;
use Illuminate\Support\Str;

class HelpCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kategori Utama
        $kategoriUtama = HelpCategory::create([
            'name' => 'Akun Penyewa',
            'target_audience' => 'penyewa',
            'icon' => 'User',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Sub Kategori: Pendaftaran
        $subKategoriPendaftaran = HelpCategory::create([
            'name' => 'Pendaftaran',
            'target_audience' => 'penyewa',
            'parent_id' => $kategoriUtama->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 3. Sub Kategori: Pengaturan
        $subKategoriPengaturan = HelpCategory::create([
            'name' => 'Pengaturan',
            'target_audience' => 'penyewa',
            'parent_id' => $kategoriUtama->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // 4. Artikel: Pendaftaran
        HelpArticle::create([
            'help_category_id' => $subKategoriPendaftaran->id,
            'title' => 'Bagaimana Cara Daftar Akun Penyewa di KitaSewa?',
            'slug' => Str::slug('Bagaimana Cara Daftar Akun Penyewa di KitaSewa?'),
            'summary_answer' => '<p>Berikut cara mendaftar akun “Penyewa” di KitaSewa:</p>',
            'has_platform_tabs' => true,
            'sort_order' => 1,
            'is_published' => true,
            'content_mobile' => '
                <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-700">
                    <li>Pada halaman utama KitaSewa pilih menu <strong>“Masuk”</strong> pada bottom menu di pojok kanan bawah.</li>
                    <li>Anda dapat mendaftarkan akun “Penyewa” dengan menghubungkan akun Google anda.</li>
                    <li>Anda juga dapat mendaftarkan akun “Penyewa” dengan email anda.</li>
                    <li>Setelah memasukan email anda akan dikirimi kode OTP 6 digit yang berlaku 10 menit sejak OTP di kirimkan untuk memverifikasi akun email anda.</li>
                    <li>Lengkapi form pendaftaran, Lanjutkan dengan klik <strong>“Daftar”</strong>.</li>
                    <li>Selamat, Anda telah memiliki akun sebagai “Penyewa”.</li>
                </ol>
            ',
            'content_desktop' => '
                <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-700">
                    <li>Pada halaman utama KitaSewa pilih menu <strong>“Masuk”</strong> pada navbar menu di pojok kanan atas.</li>
                    <li>Anda dapat mendaftarkan akun “Penyewa” dengan menghubungkan akun Google anda.</li>
                    <li>Anda juga dapat mendaftarkan akun “Penyewa” dengan email anda.</li>
                    <li>Setelah memasukan email anda akan dikirimi kode OTP 6 digit yang berlaku 10 menit sejak OTP di kirimkan untuk memverifikasi akun email anda.</li>
                    <li>Lengkapi form pendaftaran, Lanjutkan dengan klik <strong>“Daftar”</strong>.</li>
                    <li>Selamat, Anda telah memiliki akun sebagai “Penyewa”.</li>
                </ol>
            ',
        ]);

        // 5. Artikel: Pengaturan
        HelpArticle::create([
            'help_category_id' => $subKategoriPengaturan->id,
            'title' => 'Bagaimana cara mengubah password akun “Penyewa” di KitaSewa?',
            'slug' => Str::slug('Bagaimana cara mengubah password akun “Penyewa” di KitaSewa?'),
            'summary_answer' => '<p>Jika Anda mendaftar akun KitaSewa dengan menghubungkan akun Google Anda, maka Anda tidak perlu membuat password. Anda bisa terus masuk menggunakan akun Google, atau Anda dapat memilih untuk ubah password langsung di akun Google Anda.</p>
                                 <p class="mt-4">Apabila Anda mendaftar menggunakan email, Anda dapat mengubah password dengan cara sebagai berikut:</p>',
            'has_platform_tabs' => true,
            'sort_order' => 1,
            'is_published' => true,
            'content_mobile' => '
                <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-700">
                    <li>Login Akun KitaSewa, kemudian pilih menu <strong>“Profil”</strong> pada bottom menu di pojok kanan bawah.</li>
                    <li>Klik menu <strong>“Keamanan”</strong>, lanjutkan dengan klik <strong>“Ubah Kata Sandi”</strong>.</li>
                    <li>Masukkan password lama dan password baru Anda, Kemudian klik simpan kata sandi.</li>
                </ol>
            ',
            'content_desktop' => '
                <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-700">
                    <li>Login Akun KitaSewa, kemudian pilih menu <strong>“Profil”</strong> pada navbar menu di pojok kanan atas.</li>
                    <li>Klik menu <strong>“Keamanan”</strong>, lanjutkan dengan klik <strong>“Ubah Kata Sandi”</strong>.</li>
                    <li>Masukkan password lama dan password baru Anda, Kemudian klik simpan kata sandi.</li>
                </ol>
            ',
        ]);
    }
}
