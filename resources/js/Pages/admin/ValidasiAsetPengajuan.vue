<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const activeFilter = ref('Pending');
const searchQuery = ref('');
const filters = ['Semua', 'Pending', 'Disetujui', 'Ditolak'];

const assets = ref([
    { id: 1, title: 'Kost Nyaman 2Kamar', owner: 'Ari Widodo', location: 'Jakarta Selatan', category: 'Kost', status: 'Pending', submitted: '02 Ags 2026' },
    { id: 2, title: 'Ruko Strategis Cilandak', owner: 'Dewi Lestari', location: 'Jakarta Selatan', category: 'Ruko', status: 'Disetujui', submitted: '30 Jul 2026' },
    { id: 3, title: 'Apartemen Emerald Tower', owner: 'Rina Oktaviani', location: 'Bandung', category: 'Apartemen', status: 'Pending', submitted: '01 Ags 2026' },
]);

const filteredAssets = computed(() => {
    return assets.value.filter(item => {
        const matchesFilter = activeFilter.value === 'Semua' || item.status === activeFilter.value;
        const matchesSearch = [item.title, item.owner, item.location, item.category]
            .join(' ').toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesFilter && matchesSearch;
    });
});

const totals = computed(() => ({
    pendingAssets: assets.value.filter(item => item.status === 'Pending').length,
    approvedAssets: assets.value.filter(item => item.status === 'Disetujui').length,
    rejectedAssets: assets.value.filter(item => item.status === 'Ditolak').length,
    totalAssets: assets.value.length,
}));
</script>

<template>
    <Head title="Validasi Aset - Admin Panel" />

    <div class="h-screen bg-slate-50 text-slate-700 font-sans flex antialiased overflow-hidden">
        <!-- Sidebar Container -->
        <div class="h-full flex-shrink-0 border-r border-slate-200 bg-white">
            <AdminSidebar />
        </div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <!-- Search Bar -->
                <div class="flex items-center gap-2.5 w-full max-w-md bg-slate-100/80 px-4 py-2 rounded-lg border border-slate-200 focus-within:border-[#0A2540] focus-within:ring-1 focus-within:ring-[#0A2540] transition-all">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari nama aset, pemilik, atau lokasi..."
                        class="w-full text-sm bg-transparent border-none focus:ring-0 p-0 placeholder-slate-400 text-slate-700"
                    />
                </div>

                <button class="ml-4 rounded-lg bg-[#0A2540] px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span>Refresh</span>
                </button>
            </header>

            <!-- Page Content -->
            <div class="p-6 sm:p-8 space-y-6 max-w-[1200px] w-full mx-auto">
                
                <!-- Title & Filter Row -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-check text-[#FFC000]"></i> 
                            Validasi Aset Properti
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">Pantau dan verifikasi aset properti yang didaftarkan oleh para owner.</p>
                    </div>

                    <!-- Compact Filter -->
                    <div class="flex items-center p-1 bg-slate-200/50 rounded-lg">
                        <button
                            v-for="filter in filters"
                            :key="filter"
                            @click="activeFilter = filter"
                            :class="[
                                'px-4 py-1.5 rounded-md text-sm font-medium transition-all duration-200',
                                activeFilter === filter 
                                    ? 'bg-white text-[#0A2540] shadow-sm ring-1 ring-slate-200' 
                                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'
                            ]"
                        >
                            {{ filter }}
                        </button>
                    </div>
                </div>

                <!-- Consolidated Stats Bar -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 overflow-hidden">
                    <div class="p-5 flex flex-col justify-center">
                        <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Menunggu Verifikasi</p>
                        <p class="mt-1 text-2xl font-bold text-amber-600">{{ totals.pendingAssets }}</p>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Aset Disetujui</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ totals.approvedAssets }}</p>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Aset Ditolak</p>
                        <p class="mt-1 text-2xl font-bold text-rose-600">{{ totals.rejectedAssets }}</p>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Total Keseluruhan</p>
                        <p class="mt-1 text-2xl font-bold text-[#0A2540]">{{ totals.totalAssets }}</p>
                    </div>
                </div>

                <!-- Tables Section -->
                <div class="space-y-6">
                    <!-- Table: Aset -->
                    <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h2 class="text-sm font-bold text-slate-800">Daftar Aset Properti</h2>
                            <button class="text-xs font-semibold text-[#0A2540] hover:text-[#FFC000] transition">Lihat Semua &rarr;</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 font-medium">
                                        <th class="py-3 px-5">Nama Aset</th>
                                        <th class="py-3 px-4">Pemilik</th>
                                        <th class="py-3 px-4">Lokasi & Kategori</th>
                                        <th class="py-3 px-4">Tanggal Daftar</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in filteredAssets" :key="item.id" class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-3 px-5 font-semibold text-slate-800">{{ item.title }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ item.owner }}</td>
                                        <td class="py-3 px-4 text-slate-600">
                                            {{ item.location }} <span class="text-slate-300 mx-1">|</span> {{ item.category }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-xs">{{ item.submitted }}</td>
                                        <td class="py-3 px-4">
                                            <span :class="[
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset',
                                                item.status === 'Pending' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 
                                                item.status === 'Disetujui' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 
                                                'bg-rose-50 text-rose-700 ring-rose-200'
                                            ]">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <button class="text-xs font-medium text-slate-400 group-hover:text-[#0A2540] transition border border-transparent group-hover:border-slate-200 px-3 py-1.5 rounded-md hover:bg-slate-100">
                                                Tinjau Aset
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredAssets.length === 0">
                                        <td colspan="6" class="py-10 text-center text-slate-500">Tidak ada data aset yang sesuai.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>
        </main>
    </div>
</template>