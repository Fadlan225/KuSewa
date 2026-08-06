<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    user: { type: Object, default: () => ({}) },
});

// ==========================================
// DUMMY DATA — dipakai otomatis kalau prop "stats" dari backend masih kosong,
// jadi halaman ini bisa langsung ditest tanpa perlu controller/DB nyala dulu.
// Begitu backend sudah mengirim data asli, ini otomatis diabaikan.
// ==========================================
const dummyStats = {
    totalUnit: 12,
    totalTersewa: 7,
    bookingPending: 3,
    pendapatanBulanIni: 18750000,

    monthlyIncome: [
        { month: 'Mar', income: 9500000 },
        { month: 'Apr', income: 12300000 },
        { month: 'Mei', income: 8750000 },
        { month: 'Jun', income: 15200000 },
        { month: 'Jul', income: 11000000 },
        { month: 'Agu', income: 18750000 },
    ],
    totalPendapatan: 75500000,

    kotaData: {
        Samarinda: 6,
        Balikpapan: 3,
        Bontang: 2,
        Tenggarong: 1,
    },

    totalTerverifikasi: 8,
    totalPendingVerifikasi: 3,
    totalDitolak: 1,

    bookingAktif: 5,
    bookingSelesaiBulanIni: 9,
    totalTersedia: 5,
};

// Kalau backend belum kirim apa-apa (props.stats === {}), pakai dummy.
// Kalau backend sudah kirim sebagian/semua field, itu yang dipakai.
const displayStats = computed(() => {
    const hasRealData = props.stats && Object.keys(props.stats).length > 0;
    return hasRealData ? props.stats : dummyStats;
});

const formatCurrency = (v) => 'Rp ' + Number(v || 0).toLocaleString('id-ID');

const monthlyIncome = computed(() => displayStats.value.monthlyIncome || []);
const maxIncome = computed(() => Math.max(...monthlyIncome.value.map(m => m.income), 1));

const kotaList = computed(() => {
    const data = displayStats.value.kotaData || {};
    return Object.entries(data)
        .map(([name, count]) => ({ name, count: Number(count) }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 3);
});
</script>

<template>
    <Head title="Dashboard Owner - kusewa.id" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased">
        <Sidebar />

        <main class="flex-1 min-w-0 p-6 md:p-8 overflow-y-auto">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- HEADER -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900">Dashboard</h1>
                        <p class="text-xs text-slate-400 mt-1">Ringkasan operasional properti & sewa Anda di kusewa.id</p>
                    </div>
                    <Link
                        href="/owner/property/create"
                        class="bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-2 w-fit"
                    >
                        <i class="fa-solid fa-plus"></i> Tambah Properti
                    </Link>
                </div>

                <!-- 4 METRIC CARDS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-[#FFC000] flex items-center justify-center text-sm">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">Total Unit</p>
                        <p class="text-2xl font-black text-slate-900">{{ displayStats.totalUnit }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">Sedang Tersewa</p>
                        <p class="text-2xl font-black text-emerald-600">{{ displayStats.totalTersewa }}</p>
                    </div>
                    <Link href="/owner/bookings" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:border-[#0A2540] transition block">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">Perlu Konfirmasi</p>
                        <p class="text-2xl font-black text-rose-500">{{ displayStats.bookingPending }}</p>
                    </Link>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">Pendapatan Bulan Ini</p>
                        <p class="text-lg font-black text-slate-900 truncate">{{ formatCurrency(displayStats.pendapatanBulanIni) }}</p>
                    </div>
                </div>

                <!-- CHART + DISTRIBUSI KOTA -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    <div class="lg:col-span-8 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800">Pendapatan 6 Bulan Terakhir</h3>
                                <p class="text-[10px] text-slate-400">Dari booking yang telah selesai</p>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">
                                Total: {{ formatCurrency(displayStats.totalPendapatan) }}
                            </span>
                        </div>
                        <div v-if="monthlyIncome.length" class="flex items-end gap-3 h-48">
                            <div v-for="item in monthlyIncome" :key="item.month" class="flex-1 flex flex-col items-center gap-1.5">
                                <span class="text-[10px] font-bold text-slate-600">
                                    {{ item.income > 0 ? formatCurrency(item.income).replace('Rp ', '') : '0' }}
                                </span>
                                <div class="w-full rounded-t-lg transition-all duration-500"
                                    :style="{ height: (item.income / maxIncome) * 100 + '%', backgroundColor: item.income > 0 ? '#0A2540' : '#e2e8f0', minHeight: '4px' }"
                                ></div>
                                <span class="text-[10px] font-semibold text-slate-400">{{ item.month }}</span>
                            </div>
                        </div>
                        <div v-else class="h-48 flex items-center justify-center text-xs text-slate-400">
                            <i class="fa-solid fa-chart-simple text-2xl text-slate-200 mr-2"></i> Belum ada data pendapatan
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-4">
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                            <h3 class="text-xs font-bold text-slate-800 mb-3">Distribusi per Kota</h3>
                            <div v-if="kotaList.length" class="space-y-2.5">
                                <div v-for="kota in kotaList" :key="kota.name" class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-700">{{ kota.name }}</span>
                                    <span class="font-bold text-[#0A2540]">{{ kota.count }} Aset</span>
                                </div>
                            </div>
                            <p v-else class="text-xs text-slate-400">Belum ada data</p>
                        </div>
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                            <h3 class="text-xs font-bold text-slate-800 mb-3">Status Verifikasi</h3>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-slate-400">Terverifikasi</span><span class="font-bold text-emerald-600">{{ displayStats.totalTerverifikasi }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Menunggu</span><span class="font-bold text-amber-600">{{ displayStats.totalPendingVerifikasi }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Ditolak</span><span class="font-bold text-rose-600">{{ displayStats.totalDitolak }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW BAWAH -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm text-center">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Booking Aktif</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">{{ displayStats.bookingAktif }}</p>
                        <Link href="/owner/bookings" class="text-[10px] font-bold text-[#0A2540] hover:underline mt-1 inline-block">Lihat Pemesanan</Link>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm text-center">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Selesai Bulan Ini</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ displayStats.bookingSelesaiBulanIni }}</p>
                        <span class="text-[10px] text-slate-400">Booking completed</span>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm text-center">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Siap Disewakan</p>
                        <p class="text-2xl font-black text-sky-600 mt-1">{{ displayStats.totalTersedia }}</p>
                        <span class="text-[10px] text-slate-400">Unit kosong</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>