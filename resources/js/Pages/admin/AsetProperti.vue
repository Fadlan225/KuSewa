<script setup>
import { Search, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const searchQuery = ref('');
const statusFilter = ref('Semua');
const typeFilter = ref('Semua');
const statuses = ['Semua', 'Publikasi', 'Draft', 'Ditolak'];
const propertyTypes = ['Semua', 'Kost', 'Apartemen', 'Ruko', 'Rumah', 'Gudang'];

const properties = ref([
    { id: 1, name: 'Kos Mewah Pondok Indah', owner: 'Dian Prasetyo', type: 'Kost', city: 'Jakarta Selatan', price: 'Rp 2.400.000/bulan', status: 'Publikasi', rooms: 12 },
    { id: 2, name: 'Apartemen Sakura Residence', owner: 'Fajar Maulana', type: 'Apartemen', city: 'Bandung', price: 'Rp 3.200.000/bulan', status: 'Draft', rooms: 8 },
    { id: 3, name: 'Ruko Elok Sentra Niaga', owner: 'Ratna Sari', type: 'Ruko', city: 'Surabaya', price: 'Rp 1.350.000/hari', status: 'Publikasi', rooms: 1 },
    { id: 4, name: 'Villa Bukit Hijau', owner: 'Toni Hidayat', type: 'Rumah', city: 'Bogor', price: 'Rp 4.500.000/malam', status: 'Ditolak', rooms: 5 },
]);

const filteredProperties = computed(() => {
    return properties.value.filter(item => {
        const matchesStatus = statusFilter.value === 'Semua' || item.status === statusFilter.value;
        const matchesType = typeFilter.value === 'Semua' || item.type === typeFilter.value;
        const matchesSearch = [item.name, item.owner, item.city, item.type]
            .join(' ')
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesType && matchesSearch;
    });
});

const totals = computed(() => ({
    totalProperties: properties.value.length,
    published: properties.value.filter(item => item.status === 'Publikasi').length,
    draft: properties.value.filter(item => item.status === 'Draft').length,
    rejected: properties.value.filter(item => item.status === 'Ditolak').length,
}));

// ==== Statistik: state & logic ====
const showStatsModal = ref(false);

const byType = computed(() => {
    const map = {};
    for (const item of properties.value) {
        map[item.type] = (map[item.type] || 0) + 1;
    }
    return Object.entries(map)
        .map(([type, count]) => ({ type, count }))
        .sort((a, b) => b.count - a.count);
});

const byCity = computed(() => {
    const map = {};
    for (const item of properties.value) {
        map[item.city] = (map[item.city] || 0) + 1;
    }
    return Object.entries(map)
        .map(([city, count]) => ({ city, count }))
        .sort((a, b) => b.count - a.count);
});

const totalRooms = computed(() =>
    properties.value.reduce((sum, item) => sum + (item.rooms || 0), 0)
);

const publishRate = computed(() => {
    if (properties.value.length === 0) return 0;
    return Math.round((totals.value.published / properties.value.length) * 100);
});

function openStats() {
    showStatsModal.value = true;
}

function closeStats() {
    showStatsModal.value = false;
}
</script>

<template>
    <Head title="Aset Properti - Admin Panel" />

    <DashboardLayout role="Admin" title="Aset Properti" description="Kelola semua listing properti, status publikasi, dan detail pemilik.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <Search class="text-slate-400 text-xs" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari properti..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
            <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Aset</button>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <select v-model="statusFilter" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20">
                            <option v-for="status in statuses" :key="status">{{ status }}</option>
                        </select>
                        <select v-model="typeFilter" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20">
                            <option v-for="type in propertyTypes" :key="type">{{ type }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Properti</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ totals.totalProperties }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah semua listing properti.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Publikasi</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ totals.published }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Properti sudah live.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Draft</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ totals.draft }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Properti belum selesai.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Ditolak</p>
                        <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ totals.rejected }}</p>
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
                                <tr v-for="item in filteredProperties" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.name }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.owner }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.city }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.price }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Publikasi' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'Draft' ? 'bg-slate-50 text-slate-700 border border-slate-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                    </td>
                                </tr>
                                <tr v-if="filteredProperties.length === 0">
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        Tidak ada properti sesuai filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
