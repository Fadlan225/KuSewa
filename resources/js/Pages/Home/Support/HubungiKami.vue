<script setup>
import { Mail, Phone, Clock, MapPin, Facebook, Instagram, Twitter, Headset, AlertTriangle, Info, User, MessageSquareMore, Send } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const activeTab = ref('admin'); // 'admin' or 'masalah'

const formAdmin = ref({
    nama: '',
    email: '',
    subjek: '',
    pesan: ''
});

const formMasalah = ref({
    nama: '',
    email: '',
    jenis_masalah: '',
    id_transaksi: '',
    deskripsi: ''
});

const submitAdmin = () => {
    alert('Pesan Anda telah dikirim ke Admin!');
    // Handle submit
};

const submitMasalah = () => {
    alert('Laporan Masalah Anda telah dikirim!');
    // Handle submit
};
</script>

<template>
    <Head title="Hubungi Kami - KitaSewa" />
    <AppLayout>
        
        <div class="bg-[var(--color-secondary)] pt-12 pb-24 px-4 sm:px-6 lg:px-8 text-center relative">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Hubungi Kami</h1>
            <p class="text-white/80 max-w-2xl mx-auto">Ada pertanyaan atau butuh bantuan terkait KitaSewa? Jangan ragu untuk menghubungi kami melalui informasi di bawah atau mengisi form yang tersedia.</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10 pb-20">
            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Informasi Kontak -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                        <h3 class="text-xl font-bold text-[#0A2540] mb-6 border-b border-gray-100 pb-4">Informasi Kontak</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-xl">
                                    <Mail class="" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Email</h4>
                                    <p class="text-gray-500 text-sm mt-1">support@kitasewa.com</p>
                                    <p class="text-gray-500 text-sm">admin@kitasewa.com</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 text-xl">
                                    <Phone class="" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Telepon / WhatsApp</h4>
                                    <p class="text-gray-500 text-sm mt-1">+62 812-3456-7890</p>
                                    <p class="text-gray-500 text-sm">+62 821-0987-6543</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 text-xl">
                                    <Clock class="" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Jam Operasional</h4>
                                    <p class="text-gray-500 text-sm mt-1">Senin - Jumat: 08:00 - 17:00</p>
                                    <p class="text-gray-500 text-sm">Sabtu: 09:00 - 14:00</p>
                                    <span class="inline-block mt-2 px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded font-semibold">Minggu & Hari Libur Tutup</span>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-xl">
                                    <MapPin class="" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Kantor Pusat</h4>
                                    <p class="text-gray-500 text-sm mt-1 leading-relaxed">
                                        Gedung KitaSewa Lt. 3<br>
                                        Jl. Jend. Sudirman No. 123<br>
                                        Jakarta Pusat, 10220
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 text-center">
                        <h4 class="font-bold text-gray-900 mb-4">Ikuti Kami</h4>
                        <div class="flex justify-center gap-4">
                            <a href="#" class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors">
                                <Facebook class="" />
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-pink-600 hover:text-white transition-colors">
                                <Instagram class="" />
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-blue-400 hover:text-white transition-colors">
                                <Twitter class="" />
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Formulir Tab -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                        <!-- Tabs -->
                        <div class="flex border-b border-gray-200">
                            <button @click="activeTab = 'admin'" :class="['flex-1 py-4 px-6 text-sm md:text-base font-bold text-center transition-colors', activeTab === 'admin' ? 'text-[var(--color-primary)] border-b-2 border-[var(--color-primary)] bg-gray-50/50' : 'text-gray-500 hover:bg-gray-50']">
                                <Headset class="mr-2" /> Hubungi Admin
                            </button>
                            <button @click="activeTab = 'masalah'" :class="['flex-1 py-4 px-6 text-sm md:text-base font-bold text-center transition-colors', activeTab === 'masalah' ? 'text-red-500 border-b-2 border-red-500 bg-red-50/50' : 'text-gray-500 hover:bg-gray-50']">
                                <AlertTriangle class="mr-2" /> Laporkan Masalah
                            </button>
                        </div>

                        <div class="p-6 md:p-8">
                            <!-- Form Hubungi Admin -->
                            <form v-if="activeTab === 'admin'" @submit.prevent="submitAdmin" class="space-y-5 animate-fade-in">
                                <div class="bg-blue-50 border border-blue-100 text-blue-800 rounded-lg p-4 mb-6 flex gap-3 text-sm">
                                    <Info class="mt-0.5" />
                                    <p>Gunakan form ini untuk pertanyaan umum, penawaran kerjasama, atau informasi layanan KitaSewa.</p>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <User class="text-gray-400" />
                                            </div>
                                            <input type="text" v-model="formAdmin.nama" required class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] text-sm" placeholder="Masukkan nama Anda">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Mail class="text-gray-400" />
                                            </div>
                                            <input type="email" v-model="formAdmin.email" required class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] text-sm" placeholder="nama@email.com">
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Subjek <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <MessageSquareMore class="text-gray-400" />
                                        </div>
                                        <input type="text" v-model="formAdmin.subjek" required class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] text-sm" placeholder="Topik pesan Anda">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pesan <span class="text-red-500">*</span></label>
                                    <textarea v-model="formAdmin.pesan" required rows="5" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] text-sm" placeholder="Tuliskan pesan Anda secara detail..."></textarea>
                                </div>
                                
                                <button type="submit" class="w-full bg-[#0A2540] hover:bg-blue-900 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <Send class="" /> Kirim Pesan
                                </button>
                            </form>

                            <!-- Form Laporkan Masalah -->
                            <form v-if="activeTab === 'masalah'" @submit.prevent="submitMasalah" class="space-y-5 animate-fade-in">
                                <div class="bg-red-50 border border-red-100 text-red-800 rounded-lg p-4 mb-6 flex gap-3 text-sm">
                                    <AlertTriangle class="mt-0.5" />
                                    <p>Gunakan form ini untuk melaporkan masalah transaksi, bug aplikasi, penipuan, atau masalah serius lainnya.</p>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pelapor <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="formMasalah.nama" required class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Masukkan nama Anda">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                        <input type="email" v-model="formMasalah.email" required class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm" placeholder="nama@email.com">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Masalah <span class="text-red-500">*</span></label>
                                        <select v-model="formMasalah.jenis_masalah" required class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm text-gray-700">
                                            <option value="" disabled selected>Pilih jenis masalah...</option>
                                            <option value="transaksi">Masalah Transaksi / Pembayaran</option>
                                            <option value="properti">Masalah Properti / Baliho</option>
                                            <option value="akun">Masalah Akun / Keamanan</option>
                                            <option value="bug">Error / Bug Aplikasi</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">ID Transaksi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                        <input type="text" v-model="formMasalah.id_transaksi" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Contoh: TRW-123456">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Masalah <span class="text-red-500">*</span></label>
                                    <textarea v-model="formMasalah.deskripsi" required rows="5" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Jelaskan secara rinci kronologi masalah yang Anda alami..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lampiran Bukti <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                    <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-colors border border-gray-300 rounded-lg p-1.5 cursor-pointer">
                                </div>
                                
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <Send class="" /> Kirim Laporan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
