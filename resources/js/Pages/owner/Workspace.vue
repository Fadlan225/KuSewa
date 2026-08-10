<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    type: String,
    title: String,
    description: String,
    bookings: { type: Array, default: () => [] },
    income: { type: Number, default: 0 },
    fees: { type: Number, default: 0 },
    transactions: { type: Array, default: () => [] },
    status: { type: String, default: 'pending' },
    documents: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
    faqs: { type: Array, default: () => [] },
});

const activeFaq = ref(null);
const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
const statusLabel = computed(() => ({ pending: 'Menunggu', confirmed: 'Dikonfirmasi', completed: 'Selesai', cancelled: 'Dibatalkan', verified: 'Terverifikasi', rejected: 'Ditolak' }[props.status] || props.status));
const bookingStatusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    active: 'bg-blue-50 text-blue-700 border-blue-200',
    completed: 'bg-sky-50 text-sky-700 border-sky-200',
    cancelled: 'bg-slate-100 text-slate-600 border-slate-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] || 'bg-slate-100 text-slate-600 border-slate-200');
const bookingStatusLabel = (status) => ({
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    active: 'Aktif',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
}[status] || status);
const bookingStatusIcon = (status) => ({
    pending: 'fa-clock',
    confirmed: 'fa-circle-check',
    active: 'fa-circle-play',
    completed: 'fa-flag-checkered',
    cancelled: 'fa-ban',
    rejected: 'fa-circle-xmark',
}[status] || 'fa-circle-info');

// Pagination helpers
const bookingItems = computed(() => props.bookings?.data || []);
const paginationLinks = computed(() => props.bookings?.meta?.links?.filter(l => l.url) || []);
const paginationMeta = computed(() => ({
    from: props.bookings?.meta?.from || 0,
    to: props.bookings?.meta?.to || 0,
    total: props.bookings?.meta?.total || 0,
}));
</script>

<template>
    <DashboardLayout
        :title="title"
        :description="description"
        role="Owner"
    >
        <template #action v-if="type === 'bookings'">
            <Link :href="route('owner.asset.index')" class="bg-[#0A2540] hover:bg-[#123e6b] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors">
                Kelola Aset
            </Link>
        </template>

        <Head :title="`${title} - kusewa.id`" />

        <div class="space-y-6">
            <section v-if="type === 'bookings'" class="space-y-4">
                    <!-- Ringkasan -->
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">Daftar Pesanan</h2>
                        <span class="text-[11px] text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ paginationMeta.total }} total · {{ bookingItems.length }} halaman ini
                        </span>
                    </div>

                    <!-- Card List -->
                    <div v-if="bookingItems.length" class="space-y-3">
                        <div
                            v-for="booking in bookingItems"
                            :key="booking.id"
                            class="bg-white border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:border-slate-300 transition"
                        >
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <!-- Status Icon -->
                                <div :class="[
                                    'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm',
                                    booking.status === 'pending' ? 'bg-amber-50 text-amber-500' :
                                    booking.status === 'confirmed' ? 'bg-emerald-50 text-emerald-500' :
                                    booking.status === 'completed' ? 'bg-sky-50 text-sky-500' :
                                    'bg-slate-100 text-slate-400'
                                ]">
                                    <i :class="['fa-solid', bookingStatusIcon(booking.status)]"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-800 text-sm">{{ booking.asset }}</span>
                                        <span :class="['text-[10px] font-extrabold px-2 py-0.5 rounded-full border', bookingStatusClass(booking.status)]">
                                            {{ bookingStatusLabel(booking.status) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ booking.code }} · {{ booking.tenant }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        <i class="fa-regular fa-calendar text-[10px] mr-1"></i>{{ booking.period }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 sm:flex-col sm:items-end sm:gap-1 shrink-0">
                                <span class="font-extrabold text-[#0A2540] text-sm">{{ formatCurrency(booking.total) }}</span>
                                <Link
                                    v-if="booking.status === 'pending'"
                                    :href="`/owner/bookings/${booking.id}`"
                                    class="text-[11px] font-bold bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] px-3 py-1.5 rounded-lg transition flex items-center gap-1"
                                >
                                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i> Tinjau
                                </Link>
                                <Link
                                    v-else
                                    :href="`/owner/bookings/${booking.id}`"
                                    class="text-[11px] font-medium text-slate-400 hover:text-[#0A2540] transition"
                                >
                                    Lihat Detail
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <i class="fa-solid fa-receipt text-3xl text-slate-200"></i>
                        <p class="mt-3 font-bold text-slate-700">Belum ada pesanan</p>
                        <p class="mt-1 text-sm text-slate-400">Pesanan dari aset Anda akan tampil di halaman ini.</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="paginationLinks.length > 0" class="flex items-center justify-center gap-1 pt-2">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'text-xs font-bold px-3 py-1.5 rounded-lg transition',
                                link.active ? 'bg-[#0A2540] text-white' : 'text-slate-500 hover:bg-slate-100',
                            ]"
                        />
                    </div>
                </section>

                <template v-else-if="type === 'finance'">
                    <section class="grid md:grid-cols-3 gap-4"><div class="bg-[#0A2540] text-white p-5 rounded-2xl"><p class="text-xs text-slate-300">Pendapatan Tercatat</p><p class="mt-2 text-2xl font-black">{{ formatCurrency(income) }}</p></div><div class="bg-white border border-slate-200 p-5 rounded-2xl"><p class="text-xs text-slate-400">Biaya Layanan</p><p class="mt-2 text-2xl font-black text-slate-800">{{ formatCurrency(fees) }}</p></div><div class="bg-white border border-slate-200 p-5 rounded-2xl"><p class="text-xs text-slate-400">Pendapatan Bersih</p><p class="mt-2 text-2xl font-black text-emerald-600">{{ formatCurrency(income - fees) }}</p></div></section>
                    <section class="bg-white border border-slate-200 rounded-2xl p-5"><h2 class="font-bold text-slate-800">Transaksi Terbaru</h2><div v-if="transactions.length" class="mt-4 divide-y divide-slate-100"><div v-for="transaction in transactions" :key="transaction.code" class="py-3 flex justify-between gap-4"><div><p class="font-bold text-sm">{{ transaction.asset }}</p><p class="text-xs text-slate-400">{{ transaction.code }} · {{ transaction.date }}</p></div><p class="font-bold text-emerald-600">{{ formatCurrency(transaction.total) }}</p></div></div><p v-else class="mt-4 text-sm text-slate-400">Belum ada pendapatan yang tercatat.</p></section>
                </template>

                <section v-else-if="type === 'verification'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6"><div class="flex items-center justify-between bg-amber-50 rounded-xl p-4"><div><p class="text-xs text-amber-700">Status verifikasi</p><p class="font-black text-amber-900">{{ statusLabel }}</p></div><i class="fa-solid fa-shield-halved text-xl text-amber-500"></i></div><div class="mt-6 space-y-3"><div v-for="document in documents" :key="document.name" class="flex items-center justify-between p-4 border border-slate-100 rounded-xl"><span class="font-semibold text-sm">{{ document.name }}</span><span :class="document.complete ? 'text-emerald-600' : 'text-amber-600'" class="text-xs font-bold"><i :class="document.complete ? 'fa-circle-check' : 'fa-clock'" class="fa-solid mr-1"></i>{{ document.complete ? 'Lengkap' : 'Perlu dilengkapi' }}</span></div></div></section>

                <section v-else-if="type === 'settings'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6"><h2 class="font-bold text-slate-800">Informasi Akun</h2><div class="mt-5 grid sm:grid-cols-2 gap-4"><label class="text-xs font-bold">Nama<input :value="user.name" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label><label class="text-xs font-bold">Email<input :value="user.email" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label><label class="text-xs font-bold">Nomor telepon<input :value="user.phone || '-'" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label></div><Link :href="route('profile.edit')" class="inline-flex mt-5 bg-[#0A2540] text-white text-xs font-bold px-4 py-2.5 rounded-xl">Ubah profil & kata sandi</Link></section>

                <section v-else-if="type === 'help'" class="max-w-3xl space-y-3"><div v-for="(faq, index) in faqs" :key="faq.question" class="bg-white border border-slate-200 rounded-xl"><button @click="activeFaq = activeFaq === index ? null : index" class="w-full p-4 text-left flex items-center justify-between font-bold text-sm"><span>{{ faq.question }}</span><i :class="activeFaq === index ? 'fa-minus' : 'fa-plus'" class="fa-solid text-slate-400"></i></button><p v-if="activeFaq === index" class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">{{ faq.answer }}</p></div><div class="mt-6 bg-[#0A2540] text-white p-5 rounded-2xl"><p class="font-bold">Butuh bantuan langsung?</p><p class="text-sm text-slate-300 mt-1">Hubungi tim dukungan kusewa melalui email support@kusewa.id.</p></div></section>
        </div>
    </DashboardLayout>
</template>
