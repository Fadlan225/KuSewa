<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const faqs = ref([
    {
        question: 'Bagaimana cara mendaftar sebagai penyewa?',
        answer: 'Anda dapat mendaftar dengan mengklik tombol "Daftar" di sudut kanan atas halaman, kemudian pilih peran sebagai "Penyewa" dan lengkapi data diri Anda.',
        isOpen: false
    },
    {
        question: 'Metode pembayaran apa saja yang didukung?',
        answer: 'Kami mendukung berbagai metode pembayaran melalui sistem Payment Gateway yang aman, termasuk transfer bank, e-wallet (OVO, GoPay, DANA), dan kartu kredit.',
        isOpen: false
    },
    {
        question: 'Bagaimana jika properti atau baliho yang saya sewa bermasalah?',
        answer: 'Anda dapat langsung melaporkan masalah tersebut melalui menu "Hubungi Kami" atau menghubungi pemilik langsung melalui fitur obrolan (chat) di dalam aplikasi.',
        isOpen: false
    },
    {
        question: 'Berapa biaya admin untuk transaksi di KuSewa?',
        answer: 'KuSewa membebankan biaya admin yang sangat terjangkau per transaksi. Rincian biaya admin akan selalu ditampilkan sebelum Anda melakukan konfirmasi pembayaran.',
        isOpen: false
    }
]);

const toggleFaq = (index) => {
    faqs.value[index].isOpen = !faqs.value[index].isOpen;
};
</script>

<template>
    <Head title="Pusat Bantuan - KuSewa" />
    <AppLayout>
        <!-- Hero Section / Search -->
        <div class="bg-[var(--color-secondary)] pt-12 pb-20 px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden">
            <!-- decorative circles -->
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-white opacity-5 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-[var(--color-primary)] opacity-10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Hai, Ada yang bisa kami bantu?</h1>
                <p class="text-white/80 text-sm md:text-base mb-8">Temukan jawaban, panduan, dan solusi untuk pengalaman terbaik Anda di KuSewa.</p>
                
                <div class="relative max-w-2xl mx-auto">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input type="text" class="block w-full pl-12 pr-4 py-4 rounded-xl border-0 ring-4 ring-white/20 focus:ring-[var(--color-primary)] bg-white text-gray-900 placeholder-gray-500 shadow-lg text-lg transition-all" placeholder="Cari topik bantuan (contoh: cara bayar, refund)...">
                    <button class="absolute inset-y-2 right-2 bg-[var(--color-primary)] hover:bg-yellow-400 text-[#0A2540] px-6 rounded-lg font-semibold transition-colors">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20 pb-20">
            
            <!-- Kategori Bantuan -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-16">
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg p-6 text-center transition-all cursor-pointer border border-gray-100 group hover:-translate-y-1">
                    <div class="w-14 h-14 mx-auto bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Akun & Profil</h3>
                    <p class="text-xs text-gray-500">Kelola informasi akun Anda</p>
                </div>
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg p-6 text-center transition-all cursor-pointer border border-gray-100 group hover:-translate-y-1">
                    <div class="w-14 h-14 mx-auto bg-green-50 text-green-600 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Pembayaran</h3>
                    <p class="text-xs text-gray-500">Panduan transaksi sewa</p>
                </div>
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg p-6 text-center transition-all cursor-pointer border border-gray-100 group hover:-translate-y-1">
                    <div class="w-14 h-14 mx-auto bg-orange-50 text-orange-600 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-house-chimney"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Sewa Properti</h3>
                    <p class="text-xs text-gray-500">Tata cara & aturan sewa</p>
                </div>
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg p-6 text-center transition-all cursor-pointer border border-gray-100 group hover:-translate-y-1">
                    <div class="w-14 h-14 mx-auto bg-purple-50 text-purple-600 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-sign-hanging"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Sewa Baliho</h3>
                    <p class="text-xs text-gray-500">Ketentuan sewa baliho</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- FAQ Section -->
                <div class="md:col-span-2 space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-[var(--color-primary)] flex items-center justify-center text-[#0A2540] text-xl shadow-sm">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-[#0A2540] m-0">Pertanyaan Populer (FAQ)</h2>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div v-for="(faq, index) in faqs" :key="index" class="border-b border-gray-200 last:border-0">
                            <button @click="toggleFaq(index)" class="w-full text-left px-6 py-4 flex items-center justify-between focus:outline-none hover:bg-gray-50 transition-colors">
                                <span class="font-semibold text-gray-800" :class="{ 'text-[var(--color-primary)]': faq.isOpen }">{{ faq.question }}</span>
                                <i class="fa-solid text-gray-400 transition-transform duration-300" :class="faq.isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                            <div v-show="faq.isOpen" class="px-6 pb-4 text-gray-600 text-sm leading-relaxed border-t border-gray-50 bg-gray-50 pt-3">
                                {{ faq.answer }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panduan & Kontak -->
                <div class="space-y-6">
                    <!-- Panduan Singkat -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-[#0A2540] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-book text-[var(--color-primary)]"></i> Panduan Singkat
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="#" class="group flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <i class="fa-solid fa-play text-[10px]"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Cara Booking Properti</h4>
                                        <p class="text-xs text-gray-500">Video tutorial 2 menit</p>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="group flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <i class="fa-solid fa-play text-[10px]"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Verifikasi Akun Pemilik</h4>
                                        <p class="text-xs text-gray-500">Video tutorial 3 menit</p>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="group flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-gray-600 group-hover:text-white transition-colors">
                                        <i class="fa-regular fa-file-pdf text-[10px]"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-gray-900 transition-colors">Buku Panduan PDF</h4>
                                        <p class="text-xs text-gray-500">Unduh panduan lengkap</p>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Hubungi Kami Banner -->
                    <div class="bg-gradient-to-br from-[#0A2540] to-blue-900 p-6 rounded-xl shadow-md text-center text-white relative overflow-hidden">
                        <i class="fa-solid fa-headset absolute -right-4 -bottom-4 text-7xl text-white opacity-10"></i>
                        <h3 class="text-lg font-bold mb-2">Masih Butuh Bantuan?</h3>
                        <p class="text-sm text-blue-100 mb-6">Tim dukungan kami siap membantu Anda menyelesaikan masalah dengan cepat.</p>
                        <Link href="/hubungin-kami" class="inline-block bg-[var(--color-primary)] text-[#0A2540] font-bold py-2.5 px-6 rounded-lg w-full hover:bg-yellow-400 transition-colors shadow-sm">
                            <i class="fa-solid fa-phone-volume mr-2"></i> Hubungi Kami
                        </Link>
                    </div>
                </div>
            </div>
            
        </div>
    </AppLayout>
</template>
