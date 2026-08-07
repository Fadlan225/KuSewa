<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    billInfo: {
        type: Object,
        default: () => ({
            id: null,
            period: 'Agustus 2026',
            dueDate: '10 Agustus 2026',
            amount: 250000,
            serviceFee: 5000,
            totalTransactions: 0,
            status: 'Belum Dibayar',
            invoiceId: 'INV/202608/KSW/0091'
        })
    },
    billingHistory: {
        type: Array,
        default: () => []
    }
});

// Method & State Pembayaran
const selectedMethod = ref('qris');
const paymentProof = ref(null);
const isSubmitted = ref(false);
const form = useForm({
    billing_id: props.billInfo.id,
    payment_method: 'qris',
    payment_proof: null,
});

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
        paymentProof.value = file.name;
        form.payment_proof = file;
    }
};

const submitPayment = () => {
    form.payment_method = selectedMethod.value;
    form.post(route('owner.monthly-payment.store'), {
        forceFormData: true,
        onSuccess: () => { isSubmitted.value = true; },
    });
};
</script>

<template>
    <Head title="Pembayaran Biaya Bulanan - kusewa.id" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <Sidebar />

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
                                    <span>Biaya Layanan / Transaksi</span>
                                    <span class="font-semibold text-slate-800">Rp {{ billInfo.serviceFee.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Jumlah Transaksi Berhasil</span>
                                    <span class="font-semibold text-slate-800">{{ billInfo.totalTransactions }} transaksi</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-800">Total Tagihan</span>
                                <span class="text-lg font-black text-[#0A2540]">Rp {{ billInfo.amount.toLocaleString('id-ID') }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400 -mt-1">
                                Rumus: {{ billInfo.serviceFee.toLocaleString('id-ID') }} × {{ billInfo.totalTransactions }} transaksi
                            </p>
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
                                <input type="file" @change="handleFileUpload" accept="image/jpeg,image/png,application/pdf" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                <div v-if="!paymentProof" class="space-y-1">
                                    <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-xl"></i>
                                    <p class="text-xs font-bold text-slate-600">Klik atau Drop file di sini</p>
                                </div>
                                <div v-else class="flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                    <span class="text-xs font-bold text-slate-700">Bukti berhasil dipilih</span>
                                </div>
                            </div>
                            <p v-if="form.errors.payment_proof" class="text-xs text-rose-600">{{ form.errors.payment_proof }}</p>
                            <p v-if="isSubmitted" class="text-xs text-emerald-600 font-semibold">Bukti pembayaran berhasil dikirim untuk diverifikasi.</p>

                            <button 
                                @click="submitPayment" 
                                :disabled="form.processing || isSubmitted"
                                class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] text-xs font-black py-3 rounded-xl transition shadow-xs flex items-center justify-center gap-2 disabled:opacity-50"
                            >
                                <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                                <span>{{ isSubmitted ? 'Memproses Verifikasi...' : 'Konfirmasi Pembayaran' }}</span>
                            </button>
                        </div>

                    </div>

                </div>

                <!-- RIWAYAT TAGIHAN -->
                <div v-if="billingHistory.length > 0" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Riwayat Tagihan Bulanan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-slate-400 border-b border-slate-100">
                                    <th class="pb-2 font-medium">ID Tagihan</th>
                                    <th class="pb-2 font-medium">Periode</th>
                                    <th class="pb-2 font-medium">Transaksi</th>
                                    <th class="pb-2 font-medium">Total</th>
                                    <th class="pb-2 font-medium">Status</th>
                                    <th class="pb-2 font-medium">Tgl Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="bill in billingHistory" :key="bill.id" class="border-b border-slate-50 hover:bg-slate-50/50">
                                    <td class="py-2.5 font-bold text-[#0A2540]">{{ bill.invoiceId }}</td>
                                    <td class="py-2.5">{{ bill.period }}</td>
                                    <td class="py-2.5">{{ bill.totalTransactions }}</td>
                                    <td class="py-2.5 font-semibold">Rp {{ bill.amount.toLocaleString('id-ID') }}</td>
                                    <td class="py-2.5">
                                        <span :class="[
                                            'text-[10px] font-extrabold px-2 py-0.5 rounded-full',
                                            bill.status === 'Lunas' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 
                                            bill.status === 'Terlambat' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 
                                            'bg-amber-50 text-amber-600 border border-amber-200'
                                        ]">
                                            {{ bill.status }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-slate-400">{{ bill.paidAt || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

    </div>
</template>
