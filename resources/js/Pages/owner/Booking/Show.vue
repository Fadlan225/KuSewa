<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Image, Loader2, X, CheckCheck, CheckCircle, Flag, Clock, ArrowLeft } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

// ========== STATUS HELPERS ==========
const statusLabel = (status) => ({
    pending:   'Menunggu Konfirmasi',
    confirmed: 'Dikonfirmasi',
    active:    'Sedang Aktif',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    rejected:  'Ditolak',
}[status] || status);

const statusClass = (status) => ({
    pending:   'bg-amber-50 text-amber-700 border border-amber-200',
    confirmed: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    active:    'bg-blue-50 text-blue-700 border border-blue-200',
    completed: 'bg-sky-50 text-sky-700 border border-sky-200',
    cancelled: 'bg-slate-100 text-slate-600 border border-slate-200',
    rejected:  'bg-rose-50 text-rose-700 border border-rose-200',
}[status] || 'bg-slate-100 text-slate-600 border border-slate-200');

const statusIcon = (status) => ({
    pending:   'fa-clock',
    confirmed: 'fa-circle-check',
    active:    'fa-circle-play',
    completed: 'fa-flag-checkered',
    cancelled: 'fa-ban',
    rejected:  'fa-circle-xmark',
}[status] || 'fa-circle-info');

const paymentStatusLabel = (status) => ({
    pending:   'Menunggu Pembayaran',
    verifying: 'Menunggu Verifikasi',
    paid:      'Sudah Dibayar / Diverifikasi',
    verified:  'Pembayaran Terverifikasi',
    failed:    'Pembayaran Gagal',
}[status] || status || '-');

const paymentStatusClass = (status) => ({
    pending:   'text-amber-600',
    verifying: 'text-amber-600',
    paid:      'text-emerald-600',
    verified:  'text-emerald-600',
    failed:    'text-rose-600',
}[status] || 'text-slate-500');

// ========== AKSI OWNER ==========
const confirming  = ref(false);
const rejecting   = ref(false);
const completing  = ref(false);
const verifyingPayment = ref(false);

const confirmBooking = () => {
    confirming.value = true;
    router.patch(route('owner.bookings.confirm', props.booking.id), {}, {
        onSuccess: () => confirming.value = false,
        onError:   () => confirming.value = false,
        onFinish:  () => confirming.value = false,
    });
};

const verifyPaymentBooking = () => {
    verifyingPayment.value = true;
    router.patch(route('owner.bookings.verify-payment', props.booking.id), {}, {
        onSuccess: () => verifyingPayment.value = false,
        onError:   () => verifyingPayment.value = false,
        onFinish:  () => verifyingPayment.value = false,
    });
};

const rejectBooking = () => {
    rejecting.value = true;
    router.patch(route('owner.bookings.reject', props.booking.id), {}, {
        onSuccess: () => rejecting.value = false,
        onError:   () => rejecting.value = false,
        onFinish:  () => rejecting.value = false,
    });
};

const completeBooking = () => {
    completing.value = true;
    router.patch(route('owner.bookings.complete', props.booking.id), {}, {
        onSuccess: () => completing.value = false,
        onError:   () => completing.value = false,
        onFinish:  () => completing.value = false,
    });
};

const isActionable = computed(() => ['pending', 'confirmed', 'active'].includes(props.booking.status));

// ========== SUB-MENU SIDEBAR: scroll ke section ==========
const activeSection = ref('detail');

const scrollTo = (sectionId) => {
    activeSection.value = sectionId;
    const el = document.getElementById(sectionId);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const bookingSubMenu = computed(() => [
    {
        key: 'detail',
        label: 'Detail Pesanan',
        icon: 'fa-solid fa-file-lines',
        active: activeSection.value === 'detail',
        onClick: () => scrollTo('section-detail')
    },
    ...(props.booking.payment ? [{
        key: 'payment',
        label: 'Informasi Pembayaran',
        icon: 'fa-solid fa-credit-card',
        active: activeSection.value === 'payment',
        onClick: () => scrollTo('section-payment')
    }] : []),
    {
        key: 'penyewa',
        label: 'Info Penyewa',
        icon: 'fa-solid fa-user',
        active: activeSection.value === 'penyewa',
        onClick: () => scrollTo('section-penyewa')
    },
    ...(isActionable.value ? [{
        key: 'aksi',
        label: 'Aksi Pesanan',
        icon: 'fa-solid fa-bolt',
        active: activeSection.value === 'aksi',
        onClick: () => scrollTo('section-aksi')
    }] : []),
]);
</script>

<template>
    <DashboardLayout
        :title="`Pesanan #${booking.code}`"
        description="Tinjau detail penyewa sebelum mengonfirmasi, menolak, atau menyelesaikan pesanan."
        role="Owner"
        :breadcrumbs="[{ label: 'Pemesanan', route: route('owner.bookings') }, { label: booking.code }]"
        :subMenu="bookingSubMenu"
        subMenuParentRouteName="owner.bookings*"
    >
        <Head title="Detail Pesanan - kusewa.id" />

        <div class="max-w-3xl mx-auto space-y-5 mt-2">

            <!-- ============ CARD: STATUS & IDENTITAS ============ -->
            <div id="section-detail" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 scroll-mt-20">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-slate-800">Detail Pesanan</h2>
                    <span :class="['text-[10px] font-extrabold px-2.5 py-1 rounded-full flex items-center gap-1.5', statusClass(booking.status)]">
                        <AppIcon :iconClass="['fa-solid', statusIcon(booking.status)]" />
                        {{ statusLabel(booking.status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Booking</span>
                        <p class="font-extrabold text-[#0A2540] text-lg">{{ booking.code }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aset</span>
                        <p class="font-bold text-slate-800">{{ booking.asset }}</p>
                        <p v-if="booking.unit" class="text-xs text-slate-400">Unit: {{ booking.unit }}</p>
                    </div>
                    <div v-if="booking.kategori">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori / Jenis</span>
                        <p class="font-semibold text-slate-700">{{ booking.kategori }} <span v-if="booking.jenis" class="text-slate-400">· {{ booking.jenis }}</span></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Periode Sewa</span>
                        <p class="font-bold text-slate-800">{{ booking.start_date }} - {{ booking.end_date }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pembayaran</span>
                        <p class="font-extrabold text-emerald-600 text-lg">{{ formatCurrency(booking.total) }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dibuat Pada</span>
                        <p class="font-semibold text-slate-700">{{ booking.created_at }}</p>
                    </div>
                </div>

                <!-- Rincian biaya -->
                <div class="border-t border-slate-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Subtotal Sewa</span>
                        <span class="font-semibold">{{ formatCurrency(booking.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Biaya Layanan</span>
                        <span class="font-semibold">{{ formatCurrency(booking.service_fee) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2">
                        <span class="font-bold text-slate-800">Total</span>
                        <span class="font-black text-[#0A2540]">{{ formatCurrency(booking.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- ============ CARD: INFO PEMBAYARAN ============ -->
            <div v-if="booking.payment" id="section-payment" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 scroll-mt-20">
                <h2 class="font-bold text-slate-800">Informasi Pembayaran</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Metode Pembayaran</span>
                        <p class="font-semibold text-slate-700">{{ booking.payment.method || '-' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Pembayaran</span>
                        <p :class="['font-bold', paymentStatusClass(booking.payment.status)]">{{ paymentStatusLabel(booking.payment.status) }}</p>
                    </div>
                    <div v-if="booking.payment.expires_at">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Batas Bayar</span>
                        <p class="font-semibold text-slate-700">{{ booking.payment.expires_at }}</p>
                    </div>
                    <div v-if="booking.payment.proof">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bukti Pembayaran</span>
                        <a :href="booking.payment.proof" target="_blank" class="text-[#0A2540] font-bold underline hover:text-[#FFC000] transition text-xs flex items-center gap-1 mt-0.5">
                            <Image class="" /> Lihat Bukti
                        </a>
                    </div>
                </div>
            </div>

            <!-- ============ CARD: INFO PENYEWA ============ -->
            <div id="section-penyewa" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 scroll-mt-20">
                <h2 class="font-bold text-slate-800">Informasi Penyewa</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Pemesan</span>
                        <p class="font-bold text-slate-800">{{ booking.tenant }}</p>
                    </div>
                    <div v-if="booking.guest_name && booking.guest_name !== booking.tenant">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Tamu / Penghuni</span>
                        <p class="font-bold text-slate-800">{{ booking.guest_name }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</span>
                        <p class="font-bold text-slate-800">{{ booking.tenant_email }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Telepon</span>
                        <p class="font-bold text-slate-800">{{ booking.tenant_phone }}</p>
                    </div>
                </div>
            </div>

            <!-- ============ TOMBOL AKSI ============ -->
            <div v-if="isActionable" id="section-aksi" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-3 flex-wrap justify-end scroll-mt-20">

                <!-- Jika ada pembayaran yang perlu diverifikasi -->
                <template v-if="booking.payment?.status === 'verifying'">
                    <button
                        v-if="booking.status === 'pending'"
                        @click="rejectBooking"
                        :disabled="rejecting || verifyingPayment"
                        class="px-5 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <Loader2 v-if="rejecting" class="animate-spin" />
                        <X v-else class="" />
                        {{ rejecting ? 'Menolak...' : 'Tolak Pengajuan' }}
                    </button>
                    <button
                        @click="verifyPaymentBooking"
                        :disabled="verifyingPayment || rejecting"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <Loader2 v-if="verifyingPayment" class="animate-spin" />
                        <CheckCheck v-else class="" />
                        {{ verifyingPayment ? 'Memverifikasi...' : 'Konfirmasi Pembayaran & Aktifkan' }}
                    </button>
                </template>

                <!-- Tombol untuk PENDING (belum ada pembayaran / belum diverifikasi user) -->
                <template v-else-if="booking.status === 'pending'">
                    <button
                        @click="rejectBooking"
                        :disabled="rejecting || confirming"
                        class="px-5 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <Loader2 v-if="rejecting" class="animate-spin" />
                        <X v-else class="" />
                        {{ rejecting ? 'Menolak...' : 'Tolak Pengajuan' }}
                    </button>
                    <button
                        @click="confirmBooking"
                        :disabled="confirming || rejecting"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <Loader2 v-if="confirming" class="animate-spin" />
                        <CheckCircle v-else class="" />
                        {{ confirming ? 'Mengonfirmasi...' : 'Konfirmasi Booking' }}
                    </button>
                </template>

                <!-- Tombol untuk ACTIVE: Tandai Selesai -->
                <template v-else-if="booking.status === 'active'">
                    <button
                        @click="completeBooking"
                        :disabled="completing"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <Loader2 v-if="completing" class="animate-spin" />
                        <Flag v-else class="" />
                        {{ completing ? 'Memproses...' : 'Tandai Selesai' }}
                    </button>
                </template>

                <!-- Informasi untuk CONFIRMED (menunggu pembayaran) -->
                <template v-else-if="booking.status === 'confirmed'">
                    <div class="flex items-center gap-2 text-xs text-slate-500 bg-amber-50 px-4 py-2.5 rounded-xl border border-amber-200 w-full">
                        <Clock class="text-amber-500" />
                        Menunggu penyewa menyelesaikan pembayaran.
                    </div>
                </template>
            </div>

            <!-- Back Link -->
            <div class="flex justify-start">
                <Link :href="route('owner.bookings')" class="text-xs text-slate-400 hover:text-[#0A2540] flex items-center gap-1.5 transition">
                    <ArrowLeft class="" />
                    Kembali ke Daftar Pesanan
                </Link>
            </div>

        </div>
    </DashboardLayout>
</template>