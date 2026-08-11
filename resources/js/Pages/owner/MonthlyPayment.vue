<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    billInfo: {
        type: Object,
        default: null
    },
    billingHistory: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

// Method & State Pembayaran
const selectedMethod = ref('qris');
const paymentProof = ref(null);
const form = useForm({
    billing_id:     props.billInfo?.id ?? null,
    payment_method: 'qris',
    payment_proof:  null,
});

const paymentMethods = [
    { id: 'qris',    name: 'QRIS (BCA, Mandiri, GoPay, OVO, ShopeePay)', icon: 'fa-qrcode',             type: 'Instant' },
    { id: 'bca',     name: 'Virtual Account BCA',                         icon: 'fa-building-columns',   type: 'Otomatis' },
    { id: 'mandiri', name: 'Virtual Account Mandiri',                     icon: 'fa-landmark',           type: 'Otomatis' },
    { id: 'manual',  name: 'Transfer Bank Manual',                        icon: 'fa-money-bill-transfer', type: 'Konfirmasi Manual' }
];

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
    });
};

// Status styles
const getStatusClass = (status) => {
    switch (status) {
        case 'Lunas':                return 'bg-emerald-50 text-emerald-600 border border-emerald-200';
        case 'Menunggu Verifikasi':  return 'bg-blue-50 text-blue-600 border border-blue-200';
        case 'Terlambat':           return 'bg-rose-50 text-rose-600 border border-rose-200';
        case 'Ditolak':             return 'bg-rose-50 text-rose-600 border border-rose-200';
        default:                    return 'bg-amber-50 text-amber-600 border border-amber-200';
    }
};

// Cek apakah owner sudah upload bukti (waiting verification)
const isWaiting = computed(() => props.billInfo?.status === 'Menunggu Verifikasi');
const isAlreadyPaid = computed(() => props.billInfo?.status === 'Lunas');
const isCurrentMonth = computed(() => props.billInfo?.isCurrentMonth === true);
const canPay = computed(() => props.billInfo?.canPay === true);
</script>

<template>
    <Head title="Biaya Layanan Bulanan - kusewa.id" />

    <DashboardLayout
        title="Biaya Layanan Bulanan"
        description="Selesaikan tagihan bulanan untuk menjaga kelancaran akses dan promosi properti Anda."
        role="Owner"
        :breadcrumbs="[{ label: 'Dashboard', route: route('owner.dashboard') }, { label: 'Biaya Bulanan' }]"
    >
        <!-- SUCCESS FLASH -->
        <div v-if="successMessage" class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-5 py-4 rounded-2xl">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            {{ successMessage }}
        </div>

        <!-- TIDAK ADA TAGIHAN AKTIF -->
        <div v-if="!billInfo" class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 mb-4">
                <i class="fa-solid fa-circle-check text-3xl"></i>
            </div>
            <h2 class="text-xl font-black text-slate-800 mb-2">Semua Tagihan Lunas!</h2>
            <p class="text-sm text-slate-500 max-w-xs">Tidak ada tagihan aktif saat ini. Tagihan periode berikutnya akan muncul di awal bulan mendatang.</p>
        </div>

        <!-- ADA TAGIHAN AKTIF -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: Detail Tagihan & Metode Pembayaran (7 Cols) -->
            <div class="lg:col-span-7 space-y-5">

                <!-- Card Ringkasan Tagihan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">ID Tagihan</span>
                            <p class="text-xs font-extrabold text-[#0A2540]">{{ billInfo.invoiceId }}</p>
                        </div>
                        <span :class="[getStatusClass(billInfo.status), 'text-[10px] font-extrabold px-2.5 py-1 rounded-full']">
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
                            <span class="font-bold" :class="billInfo.status === 'Terlambat' ? 'text-rose-600' : 'text-slate-800'">{{ billInfo.dueDate }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Layanan / Transaksi</span>
                            <span class="font-semibold text-slate-800">Rp {{ Number(billInfo.serviceFee).toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Jumlah Transaksi Berhasil</span>
                            <span class="font-semibold text-slate-800">{{ billInfo.totalTransactions }} transaksi</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-800">Total Tagihan</span>
                        <span class="text-lg font-black text-[#0A2540]">Rp {{ Number(billInfo.amount).toLocaleString('id-ID') }}</span>
                    </div>
                    <p class="text-[10px] text-slate-400 -mt-1">
                        Rumus: Rp {{ Number(billInfo.serviceFee).toLocaleString('id-ID') }} × {{ billInfo.totalTransactions }} transaksi
                    </p>
                </div>

                <!-- Pilih Metode Pembayaran — tampilkan hanya jika belum waiting/paid & sudah bisa bayar -->
                <div v-if="!isWaiting && !isAlreadyPaid && canPay" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pilih Metode Pembayaran</h3>
                    <div class="space-y-2">
                        <label
                            v-for="method in paymentMethods"
                            :key="method.id"
                            :class="[
                                'flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition text-xs',
                                selectedMethod === method.id
                                    ? 'border-[#0A2540] bg-slate-50/80 ring-1 ring-[#0A2540]'
                                    : 'border-slate-200/80 hover:bg-slate-50'
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

                <!-- Banner: Bulan berjalan, belum bisa dibayar -->
                <div v-if="isCurrentMonth" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-500 shrink-0">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-800">Tagihan Sedang Berjalan</p>
                        <p class="text-xs text-amber-600 mt-1">Ini adalah estimasi tagihan bulan <strong>{{ billInfo.period }}</strong>. Angka ini akan terus bertambah seiring transaksi yang selesai. Anda dapat membayar mulai <strong>tanggal 1 bulan berikutnya</strong>.</p>
                    </div>
                </div>

                <!-- Pesan status jika sudah waiting -->
                <div v-if="isWaiting" class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-500 shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-800">Bukti Pembayaran Sedang Diverifikasi</p>
                        <p class="text-xs text-blue-600 mt-1">Tim KuSewa akan memverifikasi pembayaran Anda dalam 1×24 jam kerja. Jika ada kendala, hubungi kami via menu Bantuan.</p>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: QR Code / Info Transfer + Upload Bukti (5 Cols) -->
            <div class="lg:col-span-5 space-y-5">

                <!-- Panel Info & FAQ — tampil saat masih bulan berjalan -->
                <template v-if="isCurrentMonth">
                    <!-- Kartu: Apa itu Biaya Layanan? -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#0A2540]/5 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-circle-info text-[#0A2540] text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">Apa itu Biaya Layanan?</h3>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Biaya layanan adalah kontribusi Anda sebagai Pemilik Aset kepada platform KuSewa atas setiap transaksi sewa yang berhasil diselesaikan. Dana ini digunakan untuk menjaga keberlanjutan platform, meningkatkan promosi aset Anda, dan memastikan pengalaman terbaik bagi penyewa.
                        </p>
                    </div>

                    <!-- Kartu: FAQ -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-question text-amber-500 text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">Pertanyaan Umum</h3>
                        </div>
                        <div class="space-y-4 divide-y divide-slate-100">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-700">Kenapa saya belum bisa membayar?</p>
                                <p class="text-xs text-slate-500 leading-relaxed">Tagihan bulan berjalan masih dalam tahap akumulasi. Setiap transaksi yang selesai akan otomatis menambah total tagihan. Anda baru bisa membayar mulai <strong class="text-slate-700">tanggal 1 bulan berikutnya</strong> ketika tagihan sudah final.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <p class="text-xs font-bold text-slate-700">Kapan tagihan ini harus dibayar?</p>
                                <p class="text-xs text-slate-500 leading-relaxed">Tagihan harus diselesaikan paling lambat <strong class="text-slate-700">tanggal 10</strong> setiap bulannya. Tagihan yang melewati jatuh tempo akan ditandai sebagai <em>Terlambat</em> dan dapat mempengaruhi status aset Anda di platform.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <p class="text-xs font-bold text-slate-700">Bagaimana cara membayar nanti?</p>
                                <p class="text-xs text-slate-500 leading-relaxed">Anda dapat membayar melalui QRIS, Virtual Account BCA/Mandiri, atau Transfer Bank Manual. Setelah transfer, upload bukti pembayaran dan tim KuSewa akan memverifikasi dalam <strong class="text-slate-700">1×24 jam kerja</strong>.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <p class="text-xs font-bold text-slate-700">Apakah angka ini bisa berubah?</p>
                                <p class="text-xs text-slate-500 leading-relaxed">Ya, selama masih dalam bulan berjalan, angka total akan terus diperbarui secara otomatis setiap kali ada transaksi baru yang selesai. Angka final akan terkunci saat memasuki bulan berikutnya.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu: Butuh Bantuan -->
                    <div class="bg-gradient-to-br from-[#0A2540] to-[#1a3a5c] rounded-2xl p-5 space-y-3 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-headset text-white text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black">Butuh Bantuan?</h3>
                        </div>
                        <p class="text-xs text-white/70 leading-relaxed">Jika ada pertanyaan mengenai tagihan atau pembayaran, tim kami siap membantu Anda.</p>
                        <a :href="route('owner.help')" class="inline-flex items-center gap-2 text-xs font-bold bg-white/10 hover:bg-white/20 transition px-4 py-2.5 rounded-xl">
                            <i class="fa-solid fa-arrow-right"></i>
                            Hubungi Tim KuSewa
                        </a>
                    </div>
                </template>

                <!-- Panel pembayaran — tampil saat bukan bulan berjalan & belum lunas -->
                <template v-if="!isAlreadyPaid && !isCurrentMonth">

                <!-- Tampilan QRIS -->
                <div v-if="selectedMethod === 'qris'" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm text-center space-y-4">
                    <span class="text-xs font-bold text-slate-800 block">Scan QRIS Untuk Membayar</span>
                    <div class="bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200 inline-block">
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
                        <input
                            type="file"
                            @change="handleFileUpload"
                            accept="image/jpeg,image/png,application/pdf"
                            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                            :disabled="isWaiting"
                        />
                        <div v-if="!paymentProof" class="space-y-1">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-xl"></i>
                            <p class="text-xs font-bold text-slate-600">Klik atau Drop file di sini</p>
                        </div>
                        <div v-else class="flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span class="text-xs font-bold text-slate-700">{{ paymentProof }}</span>
                        </div>
                    </div>
                    <p v-if="form.errors.payment_proof" class="text-xs text-rose-600">{{ form.errors.payment_proof }}</p>

                    <button
                        @click="submitPayment"
                        :disabled="form.processing || isWaiting || !paymentProof"
                        class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] text-xs font-black py-3 rounded-xl transition shadow-xs flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                        <span>{{ isWaiting ? 'Menunggu Verifikasi Admin...' : 'Konfirmasi Pembayaran' }}</span>
                    </button>
                </div>

                </template><!-- end payment panel -->

            </div><!-- end right column -->

        </div>

        <!-- RIWAYAT TAGIHAN -->
        <div v-if="billingHistory.length > 0" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm space-y-4 mt-6 mb-8">
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
                            <td class="py-2.5 font-semibold">Rp {{ Number(bill.amount).toLocaleString('id-ID') }}</td>
                            <td class="py-2.5">
                                <span :class="[getStatusClass(bill.status), 'text-[10px] font-extrabold px-2 py-0.5 rounded-full']">
                                    {{ bill.status }}
                                </span>
                            </td>
                            <td class="py-2.5 text-slate-400">{{ bill.paidAt || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Belum ada riwayat -->
        <div v-else-if="!billInfo" class="hidden"></div>
        <div v-else class="mt-6 mb-8 text-center py-8 text-xs text-slate-400">
            <i class="fa-regular fa-folder-open text-2xl mb-2 block"></i>
            Belum ada riwayat tagihan.
        </div>

    </DashboardLayout>
</template>
