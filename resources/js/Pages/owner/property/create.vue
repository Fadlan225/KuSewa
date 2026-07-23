<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Form Data State menggunakan Inertia useForm
const form = useForm({
    // Step 1: Informasi Dasar
    nama_properti: '',
    kategori: 'Kos & Rumah',
    tipe_sewa: 'Bulanan',
    deskripsi: '',

    // Step 2: Lokasi & Fasilitas
    alamat_lengkap: '',
    kota: 'Samarinda',
    kecamatan: '',
    fasilitas: [],
    jumlah_kamar: 1,
    jumlah_kamar_mandi: 1,

    // Step 3: Harga & Foto
    harga_sewa: '',
    deposit: '',
    foto_properti: []
});

// Step Navigation State
const currentStep = ref(1);

const masterFasilitas = [
    { id: 'wifi', name: 'Wi-Fi / Internet', icon: 'fa-wifi' },
    { id: 'ac', name: 'AC (Pendingin)', icon: 'fa-snowflake' },
    { id: 'parkir', name: 'Area Parkir', icon: 'fa-square-parking' },
    { id: 'kasur', name: 'Kasur & Lemari', icon: 'fa-bed' },
    { id: 'km_dalam', name: 'Kamar Mandi Dalam', icon: 'fa-bath' },
    { id: 'dapur', name: 'Dapur Bersama', icon: 'fa-utensils' },
    { id: 'cctv', name: 'Keamanan / CCTV', icon: 'fa-shield-halved' },
    { id: 'listrik', name: 'Termasuk Listrik', icon: 'fa-[#0A2540] fa-bolt' }
];

const toggleFasilitas = (id) => {
    const index = form.fasilitas.indexOf(id);
    if (index === -1) {
        form.fasilitas.push(id);
    } else {
        form.fasilitas.splice(index, 1);
    }
};

const nextStep = () => {
    if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submitProperty = () => {
    // Jalankan submit inertia ke endpoint backend
    form.post('/owner/property/store', {
        onSuccess: () => {
            alert('Pengajuan properti berhasil dikirim!');
        }
    });
};
</script>

<template>
    <Head title="Ajukan Properti Baru - kusewa.id" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex flex-col antialiased selection:bg-[#FFC000]/30">

        <!-- TOPBAR NAVIGATION -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <Link href="/owner/dashboard" class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center text-xs hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </Link>
                <div class="flex items-center gap-1.5">
                    <span class="font-black text-xl tracking-tight text-[#0A2540]">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                    <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md ml-2">Tambah Properti</span>
                </div>
            </div>

            <div class="text-xs text-slate-400 hidden sm:block">
                Butuh bantuan? <a href="#" class="text-[#0A2540] font-bold hover:underline">Hubungi CS kusewa</a>
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 space-y-6">

            <!-- HEADER TITLE -->
            <div>
                <h1 class="text-xl font-bold text-slate-900">Ajukan Properti Baru</h1>
                <p class="text-xs text-slate-500 mt-1">Lengkapi data aset Anda agar calon penyewa bisa melihat unit Anda di platform kusewa.id</p>
            </div>

            <!-- STEPPER PROGRESS BAR -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between relative">
                    <!-- Progress Line Background -->
                    <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
                    <div 
                        class="absolute top-1/2 left-0 h-0.5 bg-[#0A2540] -translate-y-1/2 z-0 transition-all duration-300"
                        :style="{ width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : '100%' }"
                    ></div>

                    <!-- Step 1 Indicator -->
                    <div class="relative z-10 flex items-center gap-2 bg-white pr-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep >= 1 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">
                            1
                        </div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep >= 1 ? 'text-slate-900' : 'text-slate-400'">Informasi Utama</span>
                    </div>

                    <!-- Step 2 Indicator -->
                    <div class="relative z-10 flex items-center gap-2 bg-white px-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep >= 2 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">
                            2
                        </div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep >= 2 ? 'text-slate-900' : 'text-slate-400'">Lokasi & Fasilitas</span>
                    </div>

                    <!-- Step 3 Indicator -->
                    <div class="relative z-10 flex items-center gap-2 bg-white pl-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep === 3 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">
                            3
                        </div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep === 3 ? 'text-slate-900' : 'text-slate-400'">Harga & Foto</span>
                    </div>
                </div>
            </div>

            <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">

                <!-- ================= STEP 1: INFORMASI UTAMA ================= -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-[#0A2540]"></i>
                        <span>Informasi Dasar Aset</span>
                    </h2>

                    <!-- Nama Properti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Properti / Unit <span class="text-rose-500">*</span></label>
                        <input 
                            v-model="form.nama_properti"
                            type="text" 
                            placeholder="Contoh: Kos Melati Eksklusif Samarinda Kota" 
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540] transition"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kategori Aset -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Properti <span class="text-rose-500">*</span></label>
                            <select v-model="form.kategori" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="Kos & Rumah">Kos & Rumah Kontrakan</option>
                                <option value="Apartemen">Apartemen</option>
                                <option value="Kendaraan">Kendaraan (Mobil/Motor)</option>
                                <option value="Ruko / Lapak">Ruko & Lapak Usaha</option>
                            </select>
                        </div>

                        <!-- Skema Tipe Sewa -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Skema Pembayaran <span class="text-rose-500">*</span></label>
                            <select v-model="form.tipe_sewa" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="Harian">Harian</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Properti</label>
                        <textarea 
                            v-model="form.deskripsi"
                            rows="4" 
                            placeholder="Jelaskan keunggulan properti kamu (lokasi dekat kampus, lingkungan tenang, bebas banjir, dll.)"
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        ></textarea>
                    </div>
                </div>

                <!-- ================= STEP 2: LOKASI & FASILITAS ================= -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-[#0A2540]"></i>
                        <span>Detail Lokasi & Fasilitas</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <select v-model="form.kota" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="Samarinda">Samarinda</option>
                                <option value="Balikpapan">Balikpapan</option>
                                <option value="Tenggarong">Tenggarong (Kutai Kartanegara)</option>
                                <option value="Bontang">Bontang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                            <input 
                                v-model="form.kecamatan"
                                type="text" 
                                placeholder="Contoh: Samarinda Ulu" 
                                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <input 
                            v-model="form.alamat_lengkap"
                            type="text" 
                            placeholder="Jl. M. Yamin No. 45, RT 12 (Samping Kampus UNMUL)" 
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        />
                    </div>

                    <!-- Pilih Fasilitas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Fasilitas yang Tersedia</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <button 
                                type="button"
                                v-for="item in masterFasilitas" 
                                :key="item.id"
                                @click="toggleFasilitas(item.id)"
                                :class="[
                                    'p-3 rounded-xl border text-left flex items-center gap-2.5 text-xs transition',
                                    form.fasilitas.includes(item.id) 
                                        ? 'border-[#0A2540] bg-[#0A2540]/5 font-bold text-[#0A2540]' 
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                ]"
                            >
                                <i :class="['fa-solid', item.icon, form.fasilitas.includes(item.id) ? 'text-[#0A2540]' : 'text-slate-400']"></i>
                                <span>{{ item.name }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 3: HARGA & FOTO ================= -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-[#0A2540]"></i>
                        <span>Harga Sewa & Upload Foto</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Sewa (Rp) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input 
                                    v-model="form.harga_sewa"
                                    type="number" 
                                    placeholder="1.500.000" 
                                    class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Uang Deposit (Opsional)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input 
                                    v-model="form.deposit"
                                    type="number" 
                                    placeholder="200.000" 
                                    class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Upload Foto Aset -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Properti (Maks. 5 Foto)</label>
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:bg-slate-50 transition cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-[#FFC000] flex items-center justify-center mx-auto mb-2 text-base">
                                <i class="fa-solid fa-cloud-arrow-up text-[#0A2540]"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-800">Klik untuk mengunggah foto unit</p>
                            <p class="text-[10px] text-slate-400 mt-1">Format JPG, PNG (Maksimal 2MB per file)</p>
                        </div>
                    </div>
                </div>

                <!-- BUTTON ACTIONS FOOTER -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="prevStep" 
                        v-if="currentStep > 1"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Kembali
                    </button>
                    <div v-else></div>

                    <button 
                        type="button" 
                        @click="nextStep" 
                        v-if="currentStep < 3"
                        class="bg-[#0A2540] hover:bg-[#123e6b] text-white text-xs font-bold px-6 py-2.5 rounded-xl transition flex items-center gap-2"
                    >
                        <span>Lanjut Langkah {{ currentStep + 1 }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>

                    <button 
                        type="submit" 
                        v-if="currentStep === 3"
                        class="bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] text-xs font-black px-6 py-2.5 rounded-xl transition shadow-xs flex items-center gap-2"
                    >
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Kirim Pengajuan Properti</span>
                    </button>
                </div>

            </form>

        </main>

    </div>
</template>