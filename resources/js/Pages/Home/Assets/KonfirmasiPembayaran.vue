<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Form State
const form = useForm({
    booking_id: '',
    bank_pengirim: '',
    nama_pengirim: '',
    jumlah_transfer: '',
    bukti_transfer: null,
});

// State untuk Modal / Pop-up Alert
const isModalOpen = ref(false);
const modalStatus = ref('success'); // 'success' | 'error'
const modalTitle = ref('');
const modalMessage = ref('');

// Preview file gambar
const imagePreview = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.bukti_transfer = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submitConfirmation = () => {
    // Validasi sederhana di frontend
    if (!form.booking_id || !form.nama_pengirim || !form.jumlah_transfer || !form.bukti_transfer) {
        showModal(
            'error',
            'Data Belum Lengkap',
            'Harap isi semua kolom dan unggah bukti transfer sebelum melanjutkan.'
        );
        return;
    }

    // Mengirim Form via Inertia (Sesuaikan endpoint route di Laravel)
    form.post(route('konfirmasi.store'), {
        onSuccess: () => {
            showModal(
                'success',
                'Konfirmasi Berhasil!',
                'Bukti pembayaran kamu telah kami terima. Tim kami akan melakukan verifikasi maksimal 1x24 jam.'
            );
            form.reset();
            imagePreview.value = null;
        },
        onError: () => {
            showModal(
                'error',
                'Gagal Mengirim',
                'Terjadi kesalahan saat mengunggah konfirmasi. Silakan periksa kembali data kamu.'
            );
        }
    });
};

const showModal = (type, title, message) => {
    modalStatus.value = type;
    modalTitle.value = title;
    modalMessage.value = message;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};
</script>

<template>
    <Head title="Konfirmasi Pembayaran" />

    <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-2xl mx-auto">
            
            <!-- Header Halaman -->
            <div class="mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                    Konfirmasi Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Kirim bukti transfer kamu agar pesanan dapat segera diproses.
                </p>
            </div>

            <!-- Card Form Utama -->
            <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6 sm:p-8">
                <form @submit.prevent="submitConfirmation" class="space-y-5">
                    
                    <!-- ID Pesanan / Kode Booking -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            ID Pesanan / Kode Booking <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.booking_id"
                            type="text" 
                            placeholder="Contoh: KSW-89102"
                            class="w-full bg-slate-50 text-slate-900 text-sm px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:bg-white transition-all"
                        />
                    </div>

                    <!-- Bank Pengirim -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Bank / Metode Pengirim <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="form.bank_pengirim"
                            class="w-full bg-slate-50 text-slate-900 text-sm px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:bg-white transition-all"
                        >
                            <option value="" disabled selected>Pilih Bank / E-Wallet</option>
                            <option value="BCA">BCA</option>
                            <option value="Mandiri">Bank Mandiri</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                            <option value="Gopay">GoPay / ShopeePay / Dana</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Nama Pemilik Rekening -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Pemilik Rekening / Pengirim <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.nama_pengirim"
                            type="text" 
                            placeholder="Sesuai nama di rekening / aplikasi"
                            class="w-full bg-slate-50 text-slate-900 text-sm px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:bg-white transition-all"
                        />
                    </div>

                    <!-- Jumlah Transfer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Jumlah Transfer (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.jumlah_transfer"
                            type="number" 
                            placeholder="Contoh: 150000"
                            class="w-full bg-slate-50 text-slate-900 text-sm px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:bg-white transition-all"
                        />
                    </div>

                    <!-- Upload Bukti Transfer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Upload Bukti Transfer <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition-colors cursor-pointer relative">
                            <input 
                                type="file" 
                                @change="handleFileUpload"
                                accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                            />
                            
                            <div class="text-center">
                                <!-- Preview Image jika file dipilih -->
                                <template v-if="imagePreview">
                                    <img :src="imagePreview" alt="Preview Bukti" class="mx-auto h-36 w-auto object-contain rounded-lg shadow-sm mb-3" />
                                    <p class="text-xs font-semibold text-[#FFC000]">Klik untuk mengganti foto</p>
                                </template>

                                <!-- State Awal belum pilih file -->
                                <template v-else>
                                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                    <div class="flex text-xs text-slate-600 justify-center">
                                        <span class="font-bold text-[#FFC000] hover:underline">Pilih file</span>
                                        <p class="pl-1">atau tarik file ke sini</p>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">PNG, JPG, JPEG hingga 5MB</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full mt-6 bg-[#FFC000] hover:bg-[#e6ad00] active:scale-[0.98] text-slate-950 font-extrabold text-sm py-3.5 px-4 rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>{{ form.processing ? 'Mengirimkan...' : 'Kirim Konfirmasi' }}</span>
                    </button>

                </form>
            </div>
        </div>

        <!-- ================= POP-UP ALERT MODAL ================= -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div 
                v-if="isModalOpen" 
                class="fixed inset-0 z-[99999] flex items-center justify-center px-4 bg-slate-900/60 backdrop-blur-xs"
            >
                <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl relative border border-slate-100">
                    
                    <!-- Icon Status Dynamic -->
                    <div 
                        class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4"
                        :class="modalStatus === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500'"
                    >
                        <i v-if="modalStatus === 'success'" class="fa-solid fa-circle-check text-3xl"></i>
                        <i v-else class="fa-solid fa-triangle-exclamation text-3xl"></i>
                    </div>

                    <!-- Judul & Pesan -->
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">
                        {{ modalTitle }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed">
                        {{ modalMessage }}
                    </p>

                    <!-- Tombol Tutup Pop-Up -->
                    <button 
                        @click="closeModal"
                        class="w-full py-3 px-4 rounded-xl text-xs font-bold text-slate-950 transition-all cursor-pointer shadow-md"
                        :class="modalStatus === 'success' ? 'bg-[#FFC000] hover:bg-[#e6ad00]' : 'bg-slate-200 hover:bg-slate-300'"
                    >
                        {{ modalStatus === 'success' ? 'Selesai' : 'Tutup & Perbaiki' }}
                    </button>

                </div>
            </div>
        </Transition>

    </div>
</template>