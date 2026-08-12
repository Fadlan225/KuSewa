<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assets: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const activeFilter = ref('Pending');
const searchQuery = ref(props.filters.search || '');
const filters = ['Semua', 'Pending', 'Disetujui', 'Ditolak'];
const selectedAsset = ref(null);

const filteredAssets = computed(() => {
    return props.assets.filter(item => {
        const matchesFilter = activeFilter.value === 'Semua' || item.status === activeFilter.value;
        const matchesSearch = [item.title, item.owner, item.location, item.category]
            .join(' ').toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesFilter && matchesSearch;
    });
});

const totals = computed(() => ({
    pendingAssets: props.assets.filter(item => item.status === 'Pending').length,
    approvedAssets: props.assets.filter(item => item.status === 'Disetujui').length,
    rejectedAssets: props.assets.filter(item => item.status === 'Ditolak').length,
    totalAssets: props.assets.length,
}));

const refresh = () => router.get(route('admin.validasi-aset'), { search: searchQuery.value }, { preserveState: true, replace: true });
const updateStatus = (id, action) => router.patch(route(`admin.validasi-aset.${action}`, id), {}, { preserveScroll: true });
const reviewAsset = (asset) => { selectedAsset.value = asset; };
const formatRupiah = (value) => value ? `Rp ${Number(value).toLocaleString('id-ID')}` : '-';
const assetImages = (asset) => asset?.images?.length ? asset.images : ['https://placehold.co/800x500?text=Belum+Ada+Foto'];
const detailLabel = (key) => key.replaceAll('_', ' ');
</script>

<template>
    <Head title="Validasi Aset - Admin Panel" />

    <DashboardLayout role="Admin" title="Validasi Aset Properti" description="Pantau dan verifikasi aset properti yang didaftarkan oleh para owner.">
        <template #header-actions>
            <div class="flex items-center gap-2.5 w-64 bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 focus-within:border-[#0A2540] focus-within:ring-1 focus-within:ring-[#0A2540] transition-all">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari nama aset, pemilik..."
                    class="w-full text-sm bg-transparent border-none focus:ring-0 p-0 placeholder-slate-400 text-slate-700"
                />
            </div>
            <button @click="refresh" class="rounded-lg bg-[#0A2540] px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-rotate-right text-xs"></i>
                <span>Refresh</span>
            </button>
        </template>

            <!-- Page Content -->
            <div class="p-6 sm:p-8 space-y-6 max-w-[1200px] w-full mx-auto">
                
                <!-- Title & Filter Row -->
                <div class="flex flex-col md:flex-row md:items-center justify-end gap-4">

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
                            <button @click="activeFilter = 'Semua'" class="text-xs font-semibold text-[#0A2540] hover:text-[#FFC000] transition">Lihat Semua &rarr;</button>
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
                                            <div class="inline-flex gap-2">
                                                <button @click="reviewAsset(item)" class="text-xs font-medium text-[#0A2540] border border-slate-200 px-3 py-1.5 rounded-md hover:bg-slate-50">Tinjau Detail</button>
                                            </div>
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


        <div v-if="selectedAsset" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="selectedAsset = null">
            <section class="bg-[#F8FAFC] rounded-2xl shadow-2xl w-full max-w-5xl max-h-[94vh] overflow-y-auto">
                <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-10">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                        <span>Validasi Aset</span><i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                        <span class="text-slate-800 font-bold">{{ selectedAsset.title }}</span>
                    </div>
                    <button @click="selectedAsset = null" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 text-lg">&times;</button>
                </header>

                <div class="p-6 md:p-8 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3 space-y-6">
                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                                <div class="relative h-72 bg-slate-100">
                                    <img :src="assetImages(selectedAsset)[0]" :alt="selectedAsset.title" class="w-full h-full object-cover" />
                                    <span class="absolute top-3 left-3 bg-[#0A2540]/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">{{ selectedAsset.category }}</span>
                                    <span class="absolute top-3 right-3 text-[10px] font-black px-2.5 py-1 rounded-lg border bg-amber-50 text-amber-700 border-amber-200">{{ selectedAsset.status }}</span>
                                </div>
                                <div class="p-5 space-y-3">
                                    <h1 class="text-lg font-black text-slate-900">{{ selectedAsset.title }}</h1>
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-[#FFC000]"></i>{{ selectedAsset.address }}, {{ selectedAsset.location }}</p>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Informasi Properti</h3>
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div><span class="text-slate-400 block">Kategori</span><b>{{ selectedAsset.category || '-' }}</b></div>
                                    <div><span class="text-slate-400 block">Jenis Properti</span><b>{{ selectedAsset.type || '-' }}</b></div>
                                    <div><span class="text-slate-400 block">Skema Pembayaran</span><b>{{ selectedAsset.pricing?.[0]?.unit || '-' }}</b></div>
                                    <div><span class="text-slate-400 block">Harga Sewa</span><b>{{ formatRupiah(selectedAsset.pricing?.[0]?.price) }}</b></div>
                                </div>
                                <div class="border-t border-slate-100 mt-4 pt-4 text-xs"><span class="text-slate-400 block mb-1">Deskripsi</span>{{ selectedAsset.description || '-' }}</div>
                            </div>

                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Fasilitas & Detail</h3>
                                <div v-if="selectedAsset.detail" class="grid grid-cols-2 gap-4 text-xs">
                                    <div v-for="(value, key) in selectedAsset.detail" :key="key"><span class="text-slate-400 block capitalize">{{ detailLabel(key) }}</span><b>{{ Array.isArray(value) ? value.join(', ') : value }}</b></div>
                                </div>
                                <span v-else class="text-xs text-slate-400">Belum ada detail.</span>
                            </div>

                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Galeri Foto</h3>
                                <div class="flex flex-wrap gap-2"><img v-for="(image, index) in assetImages(selectedAsset)" :key="index" :src="image" class="w-20 h-20 object-cover rounded-xl border border-slate-200" /></div>
                            </div>
                        </div>

                        <div class="lg:col-span-2 space-y-4">
                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs"><span class="text-[11px] font-medium text-slate-400 block">Pemilik Aset</span><span class="text-xl font-black text-[#0A2540]">{{ selectedAsset.owner }}</span></div>
                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Detail Aset</h3>
                                <div class="flex justify-between text-xs"><span class="text-slate-400">Jenis Properti</span><b>{{ selectedAsset.type || '-' }}</b></div>
                                <div class="flex justify-between text-xs"><span class="text-slate-400">Kategori</span><b>{{ selectedAsset.category || '-' }}</b></div>
                                <div class="flex justify-between text-xs"><span class="text-slate-400">Tanggal Daftar</span><b>{{ selectedAsset.submitted || '-' }}</b></div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-2"><h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-1">Lokasi</h3><div class="flex justify-between text-xs"><span class="text-slate-400">Provinsi</span><b>{{ selectedAsset.province || '-' }}</b></div><div class="flex justify-between text-xs"><span class="text-slate-400">Kota</span><b>{{ selectedAsset.location || '-' }}</b></div><div class="text-xs text-slate-500 pt-2 border-t border-slate-100">{{ selectedAsset.address || '-' }}</div></div>
                            <div class="flex gap-2">
                                <button v-if="selectedAsset.status === 'Pending'" @click="updateStatus(selectedAsset.id, 'reject'); selectedAsset = null" class="flex-1 border border-rose-200 text-rose-700 font-bold px-4 py-3 rounded-xl hover:bg-rose-50 text-xs"><i class="fa-solid fa-xmark mr-1"></i>Tolak Aset</button>
                                <button v-if="selectedAsset.status === 'Pending'" @click="updateStatus(selectedAsset.id, 'approve'); selectedAsset = null" class="flex-1 bg-emerald-600 text-white font-bold px-4 py-3 rounded-xl hover:bg-emerald-700 text-xs"><i class="fa-solid fa-check mr-1"></i>Validasi Aset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>
