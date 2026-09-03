<script setup>
import { Search, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assets:     { type: Object, default: () => ({ data: [] }) },
    assetTypes: { type: Array,  default: () => [] },
    stats:      { type: Object, default: () => ({ total: 0, approved: 0, pending: 0, rejected: 0, draft: 0 }) },
    filters:    { type: Object, default: () => ({}) },
});

const searchQuery  = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'Semua');
const typeFilter   = ref(props.filters.type   || 'Semua');

const statuses     = ['Semua', 'approved', 'pending', 'rejected', 'draft'];
const statusLabels = { Semua: 'Semua', approved: 'Disetujui', pending: 'Pending', rejected: 'Ditolak', draft: 'Draft' };

const selectedAsset   = ref(null);
const showStatsModal  = ref(false);

let searchTimer = null;
const applyFilters = () => {
    router.get(route('admin.aset-properti'), {
        search: searchQuery.value || undefined,
        status: statusFilter.value !== 'Semua' ? statusFilter.value : undefined,
        type:   typeFilter.value   !== 'Semua' ? typeFilter.value   : undefined,
    }, { preserveState: true, replace: true });
};

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
};

const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const formatRupiah = (v) => v ? `Rp ${Number(v).toLocaleString('id-ID')}` : '-';
const thumbnail    = (asset) => asset.thumbnail_images?.[0]?.image ? '/storage/' + asset.thumbnail_images[0].image : null;

const statusClass = (s) => ({
    approved: 'bg-emerald-50 text-emerald-600 border-emerald-200/60',
    pending:  'bg-amber-50 text-amber-600 border-amber-200/60',
    rejected: 'bg-rose-50 text-rose-600 border-rose-200/60',
    draft:    'bg-slate-100 text-slate-500 border-slate-200',
}[s] ?? 'bg-slate-50 text-slate-500 border-slate-200');
</script>

<template>
    <Head title="Aset Properti - Admin Panel" />

    <DashboardLayout role="Admin" title="Aset Properti" description="Kelola semua listing properti, status publikasi, dan detail pemilik.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 focus-within:border-[#FFC000] focus-within:ring-1 focus-within:ring-[#FFC000] transition-all">
                <Search class="text-slate-400 w-3.5 h-3.5 shrink-0" />
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="applySearch"
                    placeholder="Cari properti, pemilik..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <select v-model="statusFilter" @change="applyFilters" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20">
                            <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] }}</option>
                        </select>
                        <select v-model="typeFilter" @change="applyFilters" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20">
                            <option value="Semua">Semua Tipe</option>
                            <option v-for="t in assetTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Properti</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ stats.total }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah semua listing properti.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Disetujui</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ stats.approved }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Properti sudah live.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Draft / Pending</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ stats.draft + stats.pending }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Properti belum selesai / belum divalidasi.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Ditolak</p>
                        <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ stats.rejected }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Properti yang tidak disetujui.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Properti</h2>
                            <p class="text-[11px] text-slate-400">Saring dan edit detail listing properti.</p>
                        </div>
                        <button
                            type="button"
                            @click="openStats"
                            class="text-[11px] font-semibold text-[#0A2540] hover:underline"
                        >
                            Lihat Statistik
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Properti</th>
                                    <th class="py-4 px-4">Pemilik</th>
                                    <th class="py-4 px-4">Jenis</th>
                                    <th class="py-4 px-4">Kota</th>
                                    <th class="py-4 px-4">Harga</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in assets.data" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5">
                                        <p class="font-semibold text-slate-900">{{ item.title }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ item.city?.name ?? '-' }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.owner_profile?.user?.name ?? '-' }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type?.name ?? '-' }}</td>
                                    <td class="py-4 px-4 text-slate-600">
                                        {{ item.pricings?.[0] ? formatRupiah(item.pricings[0].price) + '/' + item.pricings[0].rental_unit : '-' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span :class="['inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold', statusClass(item.status)]">
                                            {{ statusLabels[item.status] ?? item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 text-[11px] whitespace-nowrap">{{ formatDate(item.created_at) }}</td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button @click="selectedAsset = item" class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Detail</button>
                                    </td>
                                </tr>
                                <tr v-if="assets.data.length === 0">
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        Tidak ada properti sesuai filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="assets.last_page > 1" class="flex items-center justify-between text-xs text-slate-500">
                    <span>Menampilkan {{ assets.from }}–{{ assets.to }} dari {{ assets.total }} aset</span>
                    <div class="flex items-center gap-1">
                        <Link v-if="assets.prev_page_url" :href="assets.prev_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            <ChevronLeft class="w-3.5 h-3.5" /> Prev
                        </Link>
                        <span class="px-3 py-1.5 font-semibold text-slate-700">{{ assets.current_page }} / {{ assets.last_page }}</span>
                        <Link v-if="assets.next_page_url" :href="assets.next_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            Next <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>


        <!-- Modal Statistik -->
        <Teleport to="body">
            <div
                v-if="showStatsModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeStats"
            >
                <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Statistik Properti</h3>
                            <p class="text-[11px] text-slate-400">Ringkasan berdasarkan data listing saat ini.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeStats"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                        <!-- Ringkasan cepat -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Total Kamar</p>
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ totalRooms }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Tingkat Publikasi</p>
                                <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ publishRate }}%</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Jenis Aktif</p>
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ byType.length }}</p>
                            </div>
                        </div>

                        <!-- Per Jenis -->
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-3">Berdasarkan Jenis</p>
                            <div class="space-y-2.5">
                                <div v-for="row in byType" :key="row.type" class="flex items-center gap-3">
                                    <span class="w-24 text-xs text-slate-600 shrink-0">{{ row.type }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div
                                            class="h-full bg-[#0A2540] rounded-full"
                                            :style="{ width: (row.count / totals.totalProperties * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="w-6 text-right text-xs font-semibold text-slate-700">{{ row.count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Per Kota -->
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-3">Berdasarkan Kota</p>
                            <div class="space-y-2.5">
                                <div v-for="row in byCity" :key="row.city" class="flex items-center gap-3">
                                    <span class="w-32 text-xs text-slate-600 shrink-0 truncate">{{ row.city }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div
                                            class="h-full bg-[#FFC000] rounded-full"
                                            :style="{ width: (row.count / totals.totalProperties * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="w-6 text-right text-xs font-semibold text-slate-700">{{ row.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                        <button
                            type="button"
                            @click="closeStats"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>
