<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <Head title="Syarat dan Ketentuan" />
        <div class="max-w-4xl mx-auto px-6 py-16">
            <h1 class="text-3xl font-bold text-[#0A2540] mb-6">Syarat dan Ketentuan</h1>
            <p class="text-gray-500 text-sm mb-10">Terakhir diperbarui: September 2026</p>

            <div class="prose prose-gray max-w-none space-y-8 text-gray-700 text-sm leading-relaxed">
                <section>
                    <h2 class="text-lg font-semibold text-[#0A2540] mb-2">1. Penerimaan Syarat</h2>
                    <p>Dengan mengakses atau menggunakan layanan KitaSewa, Anda menyetujui untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak menyetujui syarat ini, mohon untuk tidak menggunakan layanan kami.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-[#0A2540] mb-2">2. Layanan</h2>
                    <p>KitaSewa menyediakan platform untuk menghubungkan penyewa dan pemilik aset. Kami bukan pihak dalam transaksi antara penyewa dan pemilik, dan tidak bertanggung jawab atas kondisi, keamanan, atau legalitas aset yang disewakan.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-[#0A2540] mb-2">3. Akun Pengguna</h2>
                    <p>Anda bertanggung jawab untuk menjaga kerahasiaan akun dan kata sandi Anda. Anda setuju untuk segera memberitahu kami tentang penggunaan tidak sah atas akun Anda.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-[#0A2540] mb-2">4. Perubahan Syarat</h2>
                    <p>Kami berhak untuk mengubah syarat dan ketentuan ini kapan saja. Perubahan akan berlaku segera setelah dipublikasikan di platform kami. Penggunaan layanan setelah perubahan dianggap sebagai penerimaan syarat yang baru.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-[#0A2540] mb-2">5. Hubungi Kami</h2>
                    <p>Jika Anda memiliki pertanyaan tentang syarat dan ketentuan ini, silakan hubungi kami melalui halaman Hubungi Kami.</p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>