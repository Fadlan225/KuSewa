<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    billInfo: {
        type: Object,
        default: () => ({
            period: 'Agustus 2026',
            dueDate: '10 Agustus 2026',
            amount: 250000,
            serviceFee: 5000,
            status: 'Belum Dibayar', // Belum Dibayar, Menunggu Verifikasi, Lunas
            invoiceId: 'INV/202608/KSW/0091'
        })
    }
});

const user = computed(() => page.props.auth?.user || {
    name: 'Budi Santoso',
    email: 'owner@kusewa.id'
});

// Method & State Pembayaran
const selectedMethod = ref('qris');
const isUploading = ref(false);
const paymentProof = ref(null);
const isSubmitted = ref(false);

const paymentMethods = [
    { id: 'qris', name: 'QRIS (BCA, Mandiri, GoPay, OVO, ShopeePay)', icon: 'fa-qrcode', type: 'Instant' },
    { id: 'bca', name: 'Virtual Account BCA', icon: 'fa-building-columns', type: 'Otomatis' },
    { id: 'mandiri', name: 'Virtual Account Mandiri', icon: 'fa-landmark', type: 'Otomatis' },
    { id: 'manual', name: 'Transfer Bank Manual', icon: 'fa-money-bill-transfer', type: 'Konfirmasi Manual' }
];

const totalPayment = computed(() => {
    return props.billInfo.amount + props.billInfo.serviceFee;
});

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        paymentProof.value = URL.createObjectURL(file);
    }
};

const submitPayment = () => {
    isUploading.value = true;
    setTimeout(() => {
        isUploading.value = false;
        isSubmitted.value = true;
    }, 1200);
};
</script>

<template>
    <Head title="Pembayaran Biaya Bulanan - kusewa.id" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <!-- ==================== SIDEBAR ==================== -->
        <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between p-4 shrink-0 hidden lg:flex">
            <div class="space-y-6">
                <!-- Brand Logo -->
                <div class="flex items-center justify-between px-2 py-1">
                    <Link :href="route('Home')" class="flex items-center gap-1.5">
                        <span class="font-black text-2xl tracking-tight text-[#0A2540]">
                            kusewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>
                    <span class="text-[9px] font-black text-[#0A2540] bg-[#FFC000]/20 px-2 py-0.5 rounded-md uppercase">Owner</span>
                </div>

                <!-- Profile Switcher -->
                <div class="flex items-center justify-between p-2 rounded-xl border border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-xs">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</h4>
                            <p class="text-[10px] text-slate-400 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation List -->
                <nav class="space-y-1 text-xs">
                    <Link :href="route('owner.dashboard')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fa-solid fa-house-chimney text-slate-400"></i>
                        <span>Dashboard</span>
                    </Link>
                    <Link href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-building text-slate-400"></i>
                            <span>Properti & Aset</span>
                        </div>
                    </Link>
                    <!-- Active Menu: Biaya Bulanan -->
                    <Link href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-100 text-[#0A2540] font-bold transition">
                        <i class="fa-solid fa-credit-card text-[#0A2540]"></i>
                        <span>Biaya Bulanan</span>
                    </Link>
                </nav>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT ==================== -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <Link :href="route('owner.dashboard')" class="hover:text-slate-800">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="font-bold text-[#0A2540]">Tagihan & Biaya Bulanan</span>
                </div>
            </header>

            <!-- BODY CONTENT -->
            <div class="p-6 space-y-6 max-w-[1000px] w-full mx-auto">

                <!-- TITLE -->
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pembayaran Biaya Layanan Bulanan</h1>
                    <p class="text-xs text-slate-500 mt-1">Selesaikan tagihan bulanan untuk menjaga kelancaran akses dan promosi properti Anda.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- LEFT COLUMN: Detail Tagihan & Metode Pembayaran (7 Cols) -->
                    <div class="lg:col-span-7 space-y-5">
                        
                        <!-- Card Ringkasan Tagihan -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">ID Tagihan</span>
                                    <p class="text-xs font-extrabold text-[#0A2540]">{{ billInfo.invoiceId }}</p>
                                </div>
                                <span class="bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                                    {{ billInfo.status }}
                                </span>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between text-slate-600">
                                    <span>Periode Layanan</span>
                                    <span class="font-bold text-slate-800">{{ billInfo.period }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Jatuh Tempo</span>
                                    <span class="font-bold text-rose-600">{{ billInfo.dueDate }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Biaya Langganan Owner</span>
                                    <span class="font-semibold text-slate-800">Rp {{ billInfo.amount.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Biaya Penanganan / Aplikasi</span>
                                    <span class="font-semibold text-slate-800">Rp {{ billInfo.serviceFee.toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-800">Total Tagihan</span>
                                <span class="text-lg font-black text-[#0A2540]">Rp {{ totalPayment.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <!-- Pilih Metode Pembayaran -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pilih Metode Pembayaran</h3>
                            
                            <div class="space-y-2">
                                <label 
                                    v-for="method in paymentMethods" 
                                    :key="method.id"
                                    :class="[
                                        'flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition text-xs',
                                        selectedMethod === method.id ? 'border-[#0A2540] bg-slate-50/80 ring-1 ring-[#0A2540]' : 'border-slate-200/80 hover:bg-slate-50'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_method" :value="method.id" v-model="selectedMethod" class="accent-[#0A2540]" />
                                        <div class="flex items-center gap-2.5">
                                            <i :class="['fa-solid text-slate-600 text-sm', method.icon]"></i>
                                            <span class="font-bold text-slate-800">{{ method.name }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-extrabold bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                                        {{ method.type }}
                                    </span>
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: QRIS / Rekening Instruksi & Form Upload (5 Cols) -->
                    <div class="lg:col-span-5 space-y-5">
                        
                        <!-- Tampilan QRIS -->
                        <div v-if="selectedMethod === 'qris'" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm text-center space-y-4">
                            <span class="text-xs font-bold text-slate-800 block">Scan QRIS Untuk Membayar</span>
                            <div class="bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200 inline-block">
                                <!-- Placehoder QR Code -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=kusewa-monthly-bill" alt="QRIS Kusewa" class="w-40 h-40 mx-auto" />
                            </div>
                            <p class="text-[11px] text-slate-400">Mendukung BCA, Mandiri, BRI, GoPay, ShopeePay, OVO, Dana, DLL.</p>
                        </div>

                        <!-- Tampilan Virtual Account / Manual -->
                        <div v-else class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-3">
                            <span class="text-xs font-bold text-slate-800 block">Instruksi Transfer Bank</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                                <p class="text-slate-400 text-[10px]">Bank Tujuan</p>
                                <p class="font-bold text-slate-800">Bank Central Asia (BCA)</p>
                                <p class="text-slate-400 text-[10px] mt-2">Nomor Rekening / VA</p>
                                <div class="flex items-center justify-between">
                                    <span class="font-black text-[#0A2540] text-sm tracking-wider">88012 0812345678</span>
                                    <button class="text-[10px] font-bold text-[#0A2540] hover:underline">Salin</button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Bukti Pembayaran -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-3">
                            <h4 class="text-xs font-bold text-slate-800">Upload Bukti Transfer</h4>
                            <p class="text-[10px] text-slate-400">Format yang didukung: JPG, PNG, PDF (Maks. 2MB)</p>

                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:bg-slate-50 transition relative">
                                <input type="file" @change="handleFileUpload" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                <div v-if="!paymentProof" class="space-y-1">
                                    <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-xl"></i>
                                    <p class="text-xs font-bold text-slate-600">Klik atau Drop file di sini</p>
                                </div>
                                <div v-else class="flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                    <span class="text-xs font-bold text-slate-700">Bukti berhasil dipilih</span>
                                </div>
                            </div>

                            <button 
                                @click="submitPayment" 
                                :disabled="isUploading || isSubmitted"
                                class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] text-xs font-black py-3 rounded-xl transition shadow-xs flex items-center justify-center gap-2 disabled:opacity-50"
                            >
                                <i v-if="isUploading" class="fa-solid fa-spinner animate-spin"></i>
                                <span>{{ isSubmitted ? 'Memproses Verifikasi...' : 'Konfirmasi Pembayaran' }}</span>
                            </button>
                        </div>

                    </div>

                </div>

            </div>
        </main>

    </div>
</template>