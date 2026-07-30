<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

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
</script>

<template>
    <Head :title="`${title} - kusewa.id`" />
    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex">
        <Sidebar />
        <main class="flex-1 min-w-0 p-6 md:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <header class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-[#FFC000] uppercase tracking-wider">Owner Center</p>
                        <h1 class="mt-1 text-2xl font-black text-slate-900">{{ title }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ description }}</p>
                    </div>
                    <Link v-if="type === 'bookings'" :href="route('owner.property.index')" class="bg-[#0A2540] text-white text-xs font-bold px-4 py-2.5 rounded-xl">Kelola Aset</Link>
                </header>

                <section v-if="type === 'bookings'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between"><h2 class="font-bold text-slate-800">Daftar Pesanan</h2><span class="text-xs text-slate-400">{{ bookings.length }} pesanan</span></div>
                    <div v-if="bookings.length" class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs text-slate-400"><tr><th class="p-4">Kode</th><th class="p-4">Aset & Penyewa</th><th class="p-4">Periode</th><th class="p-4">Total</th><th class="p-4">Status</th></tr></thead><tbody><tr v-for="booking in bookings" :key="booking.code" class="border-t border-slate-100"><td class="p-4 font-bold text-[#0A2540]">{{ booking.code }}</td><td class="p-4"><p class="font-semibold">{{ booking.asset }}</p><p class="text-xs text-slate-400">{{ booking.tenant }}</p></td><td class="p-4 text-slate-500">{{ booking.period }}</td><td class="p-4 font-bold">{{ formatCurrency(booking.total) }}</td><td class="p-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">{{ booking.status }}</span></td></tr></tbody></table></div>
                    <div v-else class="p-12 text-center"><i class="fa-solid fa-receipt text-3xl text-slate-200"></i><p class="mt-3 font-bold text-slate-700">Belum ada pesanan</p><p class="mt-1 text-sm text-slate-400">Pesanan dari aset Anda akan tampil di halaman ini.</p></div>
                </section>

                <template v-else-if="type === 'finance'">
                    <section class="grid md:grid-cols-3 gap-4"><div class="bg-[#0A2540] text-white p-5 rounded-2xl"><p class="text-xs text-slate-300">Pendapatan Tercatat</p><p class="mt-2 text-2xl font-black">{{ formatCurrency(income) }}</p></div><div class="bg-white border border-slate-200 p-5 rounded-2xl"><p class="text-xs text-slate-400">Biaya Layanan</p><p class="mt-2 text-2xl font-black text-slate-800">{{ formatCurrency(fees) }}</p></div><div class="bg-white border border-slate-200 p-5 rounded-2xl"><p class="text-xs text-slate-400">Pendapatan Bersih</p><p class="mt-2 text-2xl font-black text-emerald-600">{{ formatCurrency(income - fees) }}</p></div></section>
                    <section class="bg-white border border-slate-200 rounded-2xl p-5"><h2 class="font-bold text-slate-800">Transaksi Terbaru</h2><div v-if="transactions.length" class="mt-4 divide-y divide-slate-100"><div v-for="transaction in transactions" :key="transaction.code" class="py-3 flex justify-between gap-4"><div><p class="font-bold text-sm">{{ transaction.asset }}</p><p class="text-xs text-slate-400">{{ transaction.code }} · {{ transaction.date }}</p></div><p class="font-bold text-emerald-600">{{ formatCurrency(transaction.total) }}</p></div></div><p v-else class="mt-4 text-sm text-slate-400">Belum ada pendapatan yang tercatat.</p></section>
                </template>

                <section v-else-if="type === 'verification'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6"><div class="flex items-center justify-between bg-amber-50 rounded-xl p-4"><div><p class="text-xs text-amber-700">Status verifikasi</p><p class="font-black text-amber-900">{{ statusLabel }}</p></div><i class="fa-solid fa-shield-halved text-xl text-amber-500"></i></div><div class="mt-6 space-y-3"><div v-for="document in documents" :key="document.name" class="flex items-center justify-between p-4 border border-slate-100 rounded-xl"><span class="font-semibold text-sm">{{ document.name }}</span><span :class="document.complete ? 'text-emerald-600' : 'text-amber-600'" class="text-xs font-bold"><i :class="document.complete ? 'fa-circle-check' : 'fa-clock'" class="fa-solid mr-1"></i>{{ document.complete ? 'Lengkap' : 'Perlu dilengkapi' }}</span></div></div></section>

                <section v-else-if="type === 'settings'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6"><h2 class="font-bold text-slate-800">Informasi Akun</h2><div class="mt-5 grid sm:grid-cols-2 gap-4"><label class="text-xs font-bold">Nama<input :value="user.name" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label><label class="text-xs font-bold">Email<input :value="user.email" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label><label class="text-xs font-bold">Nomor telepon<input :value="user.phone || '-'" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"></label></div><Link :href="route('profile.edit')" class="inline-flex mt-5 bg-[#0A2540] text-white text-xs font-bold px-4 py-2.5 rounded-xl">Ubah profil & kata sandi</Link></section>

                <section v-else-if="type === 'help'" class="max-w-3xl space-y-3"><div v-for="(faq, index) in faqs" :key="faq.question" class="bg-white border border-slate-200 rounded-xl"><button @click="activeFaq = activeFaq === index ? null : index" class="w-full p-4 text-left flex items-center justify-between font-bold text-sm"><span>{{ faq.question }}</span><i :class="activeFaq === index ? 'fa-minus' : 'fa-plus'" class="fa-solid text-slate-400"></i></button><p v-if="activeFaq === index" class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">{{ faq.answer }}</p></div><div class="mt-6 bg-[#0A2540] text-white p-5 rounded-2xl"><p class="font-bold">Butuh bantuan langsung?</p><p class="text-sm text-slate-300 mt-1">Hubungi tim dukungan kusewa melalui email support@kusewa.id.</p></div></section>
            </div>
        </main>
    </div>
</template>
