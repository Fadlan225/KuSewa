<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    year: Number, availableYears: Array, monthlyRevenue: Array, summary: Object, transactions: Object,
});
const transactionRows = computed(() => props.transactions?.data || []);
const paginationLinks = computed(() => {
    const links = props.transactions?.links || [];
    const numbered = links.filter((link) => /^\d+$/.test(String(link.label).trim()));
    if (numbered.length <= 7) return links;

    const activeIndex = numbered.findIndex((link) => link.active);
    const keep = new Set([0, numbered.length - 1, activeIndex - 1, activeIndex, activeIndex + 1]);
    const result = [];
    let previousKept = -1;
    numbered.forEach((link, index) => {
        if (!keep.has(index)) return;
        if (previousKept !== -1 && index - previousKept > 1) result.push({ label: '…', url: null, active: false });
        result.push(link);
        previousKept = index;
    });
    return [links[0], ...result, links[links.length - 1]];
});
const maxRevenue = computed(() => Math.max(...(props.monthlyRevenue || []).map(item => Number(item.revenue)), 1));
const formatRupiah = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
const changeYear = (event) => router.get(route('admin.payment-system'), { year: event.target.value }, { preserveState: true });
const paymentMethods = computed(() => [...new Set(transactionRows.value.map(item => item.method))].map((name, id) => ({ id, name, active: true, description: 'Metode pembayaran yang tercatat.' })));
const statusLabel = (status) => ({ paid: 'Berhasil', pending: 'Menunggu', failed: 'Gagal' }[status] || status);
const review = (item, action) => {
    if (!window.confirm(action === 'approve' ? 'Setujui pembayaran dan bukti transaksi ini?' : 'Tolak pembayaran ini?')) return;
    useForm({}).patch(route(`admin.payment-system.${action}`, item.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Sistem Pembayaran - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Sistem Pembayaran</h1>
                        <p class="text-xs text-slate-400">Kontrol metode pembayaran dan tinjau transaksi terakhir.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Metode</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Metode Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ paymentMethods.filter(method => method.active).length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah metode pembayaran aktif.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Transaksi Terbaru</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ summary.paidTransactions }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Pembayaran berhasil tahun {{ year }}.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Potensi Pendapatan</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ formatRupiah(summary.revenue) }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Omzet biaya layanan website tahun {{ year }}.</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div><h2 class="text-sm font-bold text-slate-900">Laporan Omzet Bulanan</h2><p class="text-[11px] text-slate-400">Total biaya layanan dari pembayaran owner yang berhasil.</p></div>
                        <select :value="year" @change="changeYear" class="rounded-xl border-slate-200 text-xs font-bold"><option v-for="item in availableYears" :key="item" :value="item">{{ item }}</option></select>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <div v-for="month in monthlyRevenue" :key="month.month" class="rounded-2xl bg-slate-50 p-3"><p class="text-[11px] text-slate-400">{{ month.label }}</p><p class="mt-2 text-sm font-extrabold text-[#0A2540]">{{ formatRupiah(month.revenue) }}</p><p class="text-[10px] text-slate-500">{{ month.transactions }} pembayaran</p></div>
                        <p v-if="!monthlyRevenue.length" class="col-span-full text-xs text-slate-400">Belum ada pembayaran berhasil pada tahun ini.</p>
                    </div>
                    <div v-if="monthlyRevenue.length" class="mt-6 h-56 flex items-end gap-2 border-b border-slate-200 px-2">
                        <div v-for="month in monthlyRevenue" :key="`chart-${month.month}`" class="flex-1 h-full flex flex-col justify-end items-center gap-2">
                            <span class="text-[9px] text-slate-500">{{ formatRupiah(month.revenue) }}</span>
                            <div class="w-full max-w-10 rounded-t-lg bg-[#FFC000] hover:bg-[#0A2540] transition" :style="{ height: `${Math.max((month.revenue / maxRevenue) * 75, 4)}%` }" :title="`${month.label}: ${formatRupiah(month.revenue)}`"></div>
                            <span class="text-[10px] text-slate-500">{{ month.label.slice(0, 3) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Metode Pembayaran</h2>
                            <p class="text-[11px] text-slate-400">Kelola status aktif dan konfigurasi setiap metode.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Atur Prioritas</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Metode</th>
                                    <th class="py-4 px-4">Deskripsi</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ method.name }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ method.description }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            method.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ method.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h2>
                            <p class="text-[11px] text-slate-400">Daftar ringkas transaksi pembayaran terakhir.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Transaksi</th>
                                    <th class="py-4 px-4">Jumlah</th>
                                    <th class="py-4 px-4">Biaya Layanan</th>
                                    <th class="py-4 px-4">Metode</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-5">Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in transactionRows" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.label }}</td>
                                    <td class="py-4 px-4 text-slate-700">{{ formatRupiah(item.amount) }}</td>
                                    <td class="py-4 px-4 text-emerald-700 font-semibold">{{ formatRupiah(item.service_fee) }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.method }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.date }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ statusLabel(item.status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <a v-if="item.proof" :href="item.proof" target="_blank" class="mr-2 text-[#0A2540] font-bold hover:underline">Bukti</a>
                                        <template v-if="item.status === 'pending'">
                                            <button @click="review(item, 'approve')" class="mr-1 rounded-lg bg-emerald-600 px-2 py-1 text-[10px] font-bold text-white">Setujui</button>
                                            <button @click="review(item, 'reject')" class="rounded-lg bg-rose-600 px-2 py-1 text-[10px] font-bold text-white">Tolak</button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-slate-400">{{ transactions.from || 0 }}–{{ transactions.to || 0 }} dari {{ transactions.total || 0 }} transaksi</span>
                        <nav class="flex items-center gap-1" aria-label="Pagination">
                            <template v-for="(link, index) in paginationLinks" :key="`${link.label}-${index}`">
                                <button v-if="link.url" :disabled="link.active" @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })" class="min-w-8 h-8 px-2 rounded-lg border text-[11px] font-semibold transition" :class="link.active ? 'bg-[#0A2540] text-white border-[#0A2540]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0A2540] hover:text-[#0A2540]'">
                                    <span v-if="link.label.includes('Previous')" aria-label="Sebelumnya">‹</span>
                                    <span v-else-if="link.label.includes('Next')" aria-label="Berikutnya">›</span>
                                    <span v-else>{{ link.label }}</span>
                                </button>
                                <span v-else class="w-8 text-center text-slate-400">{{ link.label }}</span>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
