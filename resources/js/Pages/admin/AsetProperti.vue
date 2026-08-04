<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

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
</script>

<template>
    <Head title="Aset Properti - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3 w-1/3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari properti, pemilik, atau kota..."
                        class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                    />
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Aset</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 tracking-tight">Aset Properti</h1>
                            <p class="text-xs text-slate-400">Kelola semua listing properti, status publikasi, dan detail pemilik.</p>
                        </div>
                    </div>

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
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Statistik</button>
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
        </main>
    </div>
</template>
