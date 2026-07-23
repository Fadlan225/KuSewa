<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    hasPendingProfile: {
        type: Boolean,
        default: false,
    },
});

// Step tracker state (1: Identitas, 2: Alamat, 3: Dokumen)
const currentStep = ref(1);

const form = useForm({
    national_id: '',
    place_of_birth: '',
    date_of_birth: '',
    address: '',
    ktp_photo: null,
});

const ktpPreview = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.ktp_photo = file;
        ktpPreview.value = URL.createObjectURL(file);
    }
};

// Navigasi & Validasi Step
const nextStep = () => {
    if (currentStep.value === 1) {
        form.clearErrors('national_id', 'place_of_birth', 'date_of_birth');
        let valid = true;

        if (!form.national_id || form.national_id.length !== 16) {
            form.setError('national_id', 'NIK wajib diisi 16 digit angka.');
            valid = false;
        }
        if (!form.place_of_birth) {
            form.setError('place_of_birth', 'Tempat lahir wajib diisi.');
            valid = false;
        }
        if (!form.date_of_birth) {
            form.setError('date_of_birth', 'Tanggal lahir wajib diisi.');
            valid = false;
        }

        if (valid) currentStep.value = 2;
    } else if (currentStep.value === 2) {
        form.clearErrors('address');
        if (!form.address) {
            form.setError('address', 'Alamat lengkap wajib diisi.');
            return;
        }
        currentStep.value = 3;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    form.clearErrors('ktp_photo');
    if (!form.ktp_photo) {
        form.setError('ktp_photo', 'Foto KTP wajib diunggah.');
        return;
    }

    form.post(route('owner.register.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pendaftaran Owner - kusewa.id" />

    <div class="min-h-screen bg-[#F8FAFC] font-sans text-slate-700 antialiased py-8 px-4 sm:px-6">
        
        <!-- CONTAINER TERPUSAT -->
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- HEADER & BACK LINK -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link 
                        href="/dashboard" 
                        class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-[#0A2540] hover:border-slate-300 transition shadow-xs"
                    >
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">Pendaftaran Owner</h1>
                        <p class="text-xs text-slate-400">Lengkapi data verifikasi akun pemilik aset</p>
                    </div>
                </div>

                <Link href="/" class="flex items-center gap-1">
                    <span class="font-black text-2xl tracking-tight text-[#0A2540]">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>
            </div>

            <!-- BANNER HERO -->
            <div class="bg-[#0A2540] rounded-2xl p-6 text-white relative overflow-hidden shadow-sm flex items-center justify-between">
                <div class="space-y-2 relative z-10 max-w-lg">
                    <span class="bg-[#FFC000] text-[#0A2540] text-[10px] font-black px-2.5 py-0.5 rounded uppercase tracking-wider">
                        Program Kemitraan Owner
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Mulai Sewakan Aset & Tingkatkan Pendapatanmu
                    </h2>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Kelola kos, rumah, kendaraan, atau alat secara profesional di kusewa.id. Verifikasi akunmu hanya butuh waktu singkat.
                    </p>
                </div>
                <i class="fa-solid fa-building-circle-check text-8xl text-white/5 absolute -right-4 -bottom-4 pointer-events-none"></i>
            </div>

            <!-- ALERT PENDAFTARAN PENDING (JIKA DALAM PROSES KONFIRMASI EMAIL) -->
            <div v-if="props.hasPendingProfile" class="bg-amber-50 border border-amber-200/80 rounded-2xl p-5 flex items-start gap-3.5 text-amber-900 shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-envelope-circle-check text-xl text-amber-600"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-amber-900">Email Konfirmasi Telah Dikirim!</h4>
                    <p class="text-[11px] leading-relaxed text-amber-700">
                        Pengajuan kamu telah kami terima. Silakan periksa inbox/spam email kamu dan klik tautan **"Konfirmasi Email Owner"** untuk menyelesaikan proses pendaftaran.
                    </p>
                </div>
            </div>

            <!-- STEP PROGRESS BAR DENGAN GARIS PENGHUBUNG -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs relative overflow-hidden">
                
                <!-- GARIS BACKGROUND ANIMASI -->
                <div class="absolute top-1/2 left-10 right-10 -translate-y-1/2 h-[2px] bg-slate-100 -z-0">
                    <div 
                        class="h-full bg-[#0A2540] transition-all duration-300"
                        :style="{
                            width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : '100%'
                        }"
                    ></div>
                </div>

                <!-- ITEM STEPPER -->
                <div class="grid grid-cols-3 gap-2 relative z-10">
                    
                    <!-- Step 1 -->
                    <div class="flex items-center gap-3 bg-white pr-2 w-fit">
                        <div 
                            class="w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center transition shrink-0 shadow-xs"
                            :class="currentStep >= 1 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400'"
                        >
                            <i v-if="currentStep > 1" class="fa-solid fa-check text-xs text-emerald-400"></i>
                            <span v-else>1</span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs font-bold" :class="currentStep >= 1 ? 'text-slate-900' : 'text-slate-400'">Identitas</p>
                            <p class="text-[10px] text-slate-400">NIK & Tgl Lahir</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-center gap-3 justify-center mx-auto bg-white px-2 w-fit">
                        <div 
                            class="w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center transition shrink-0 shadow-xs"
                            :class="currentStep >= 2 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400'"
                        >
                            <i v-if="currentStep > 2" class="fa-solid fa-check text-xs text-emerald-400"></i>
                            <span v-else>2</span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs font-bold" :class="currentStep >= 2 ? 'text-slate-900' : 'text-slate-400'">Alamat</p>
                            <p class="text-[10px] text-slate-400">Domisili KTP</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-center gap-3 justify-end ml-auto bg-white pl-2 w-fit">
                        <div 
                            class="w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center transition shrink-0 shadow-xs"
                            :class="currentStep === 3 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400'"
                        >
                            3
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs font-bold" :class="currentStep === 3 ? 'text-slate-900' : 'text-slate-400'">Dokumen</p>
                            <p class="text-[10px] text-slate-400">Upload KTP</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FORM CARD -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                
                <!-- STEP 1: IDENTITAS -->
                <div v-if="currentStep === 1" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Langkah 1: Data Identitas Diri</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Masukkan data kependudukan sesuai yang tertera di KTP.</p>
                    </div>

                    <hr class="border-slate-100" />

                    <!-- NIK -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Nomor Induk Kependudukan (NIK)</label>
                        <input 
                            v-model="form.national_id" 
                            type="text" 
                            maxlength="16"
                            placeholder="Contoh: 3271234567890001"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:bg-white focus:outline-none focus:border-[#0A2540] transition"
                            :class="{ 'border-rose-500': form.errors.national_id }"
                        />
                        <p v-if="form.errors.national_id" class="text-[11px] text-rose-500 font-medium">{{ form.errors.national_id }}</p>
                    </div>

                    <!-- TEMPAT & TANGGAL LAHIR -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Tempat Lahir</label>
                            <input 
                                v-model="form.place_of_birth" 
                                type="text" 
                                placeholder="Contoh: Bandung"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:bg-white focus:outline-none focus:border-[#0A2540] transition"
                                :class="{ 'border-rose-500': form.errors.place_of_birth }"
                            />
                            <p v-if="form.errors.place_of_birth" class="text-[11px] text-rose-500 font-medium">{{ form.errors.place_of_birth }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Tanggal Lahir</label>
                            <input 
                                v-model="form.date_of_birth" 
                                type="date" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:bg-white focus:outline-none focus:border-[#0A2540] transition"
                                :class="{ 'border-rose-500': form.errors.date_of_birth }"
                            />
                            <p v-if="form.errors.date_of_birth" class="text-[11px] text-rose-500 font-medium">{{ form.errors.date_of_birth }}</p>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: ALAMAT -->
                <div v-if="currentStep === 2" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Langkah 2: Alamat Domisili</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Isi alamat tempat tinggal resmi kamu.</p>
                    </div>

                    <hr class="border-slate-100" />

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Alamat Lengkap Sesuai KTP</label>
                        <textarea 
                            v-model="form.address" 
                            rows="4" 
                            placeholder="Jl. Merdeka No. 12, RT 01/RW 02, Kel. Pasir Kaliki, Kec. Cicendo, Kota Bandung"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:bg-white focus:outline-none focus:border-[#0A2540] transition resize-none"
                            :class="{ 'border-rose-500': form.errors.address }"
                        ></textarea>
                        <p v-if="form.errors.address" class="text-[11px] text-rose-500 font-medium">{{ form.errors.address }}</p>
                    </div>
                </div>

                <!-- STEP 3: UPLOAD KTP -->
                <div v-if="currentStep === 3" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Langkah 3: Unggah Identitas Resmi</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Upload foto kartu identitas (KTP) untuk proses verifikasi.</p>
                    </div>

                    <hr class="border-slate-100" />

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Foto KTP Asli</label>
                        <div class="border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-6 text-center hover:bg-slate-100/60 transition relative">
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="handleFileUpload" 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            />
                            <div v-if="!ktpPreview" class="space-y-2">
                                <i class="fa-solid fa-id-card text-4xl text-slate-400"></i>
                                <p class="text-xs font-bold text-slate-700">Klik atau seret foto KTP ke sini</p>
                                <p class="text-[10px] text-slate-400">Format PNG, JPG atau WEBP (Maksimal 2MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-4">
                                <img :src="ktpPreview" class="h-20 rounded-lg object-cover border border-slate-300 shadow-xs" />
                                <div class="text-left">
                                    <p class="text-xs font-bold text-emerald-600">✓ KTP Berhasil Terpilih</p>
                                    <p class="text-[10px] text-slate-400">Klik area ini jika ingin mengganti foto</p>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.ktp_photo" class="text-[11px] text-rose-500 font-medium">{{ form.errors.ktp_photo }}</p>
                    </div>
                </div>

                <!-- STEP NAVIGATION BUTTONS -->
                <div class="pt-6 flex items-center justify-between border-t border-slate-100 mt-6">
                    <button 
                        type="button" 
                        @click="prevStep"
                        :disabled="currentStep === 1"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Sebelumnya</span>
                    </button>

                    <button 
                        v-if="currentStep < 3"
                        type="button" 
                        @click="nextStep"
                        class="px-6 py-2.5 rounded-xl bg-[#0A2540] hover:bg-[#081d33] text-white text-xs font-bold transition flex items-center gap-2 shadow-xs"
                    >
                        <span>Lanjut</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>

                    <button 
                        v-else
                        type="button" 
                        @click="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] text-xs font-black transition flex items-center gap-2 shadow-xs disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran Owner' }}</span>
                    </button>
                </div>

            </div>

        </div>

    </div>
</template>