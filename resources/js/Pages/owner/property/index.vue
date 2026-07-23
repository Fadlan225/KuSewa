<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

// Props yang diterima dari Laravel Controller
const props = defineProps({
    properties: {
        type: Array,
        default: () => [
            {
                id: 1,
                title: 'Kos Exclusive Samarinda Indah #01',
                category: 'Kos',
                type: 'Putra',
                price: 1350000,
                rent_period: 'Bulan',
                city: 'Samarinda',
                address: 'Jl. M. Yamin No. 12, Kel. Gunung Kelua',
                status: 'Tersewa',
                tenant: 'Ahmad Rizky',
                image: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=500&q=80',
                occupancy: '1/1 Unit'
            },
            {
                id: 2,
                title: 'Apartemen Orchard Tower Unit B12',
                category: 'Apartemen',
                type: 'Campur',
                price: 3500000,
                rent_period: 'Bulan',
                city: 'Balikpapan',
                address: 'Jl. Jend. Sudirman No. 88',
                status: 'Tersedia',
                tenant: null,
                image: 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=500&q=80',
                occupancy: '0/1 Unit'
            },
            {
                id: 3,
                title: 'Honda Innova Reborn 2.4 V AT',
                category: 'Kendaraan',
                type: 'Mobil',
                price: 450000,
                rent_period: 'Hari',
                city: 'Samarinda',
                address: 'Jl. Pemilik Aset No. 3',
                status: 'Tersewa',
                tenant: 'Budi Kurniawan',
                image: 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=500&q=80',
                occupancy: 'Aktif'
            },
            {
                id: 4,
                title: 'Kos Melati Clean & Cozyman #05',
                category: 'Kos',
                type: 'Putri',
                price: 850000,
                rent_period: 'Bulan',
                city: 'Samarinda',
                address: 'Jl. Pramuka 6 No. 44',
                status: 'Tersedia',
                tenant: null,
                image: 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=500&q=80',
                occupancy: '0/1 Unit'
            },
            {
                id: 5,
                title: 'Rumah Kontrakan Minimalis A2',
                category: 'Rumah Kontrakan',
                type: 'Pasutri',
                price: 25000000,
                rent_period: 'Tahun',
                city: 'Samarinda',
                address: 'Jl. Juanda 8 Blok B',
                status: 'Tersewa',
                tenant: 'Rava Nanda',
                image: 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=500&q=80',
                occupancy: 'Tersewa s/d Des 2026'
            }
        ]
    }
});

const user = computed(() => page.props.auth?.user || {
    name: 'Budi Santoso',
    email: 'owner1@kusewa.com'
});

// State Filter & Pencarian
const searchQuery = ref('');
const selectedCategory = ref('Semua');
const selectedStatus = ref('Semua');
const viewMode = ref('grid'); // 'grid' atau 'table'

const categories = ['Semua', 'Kos', 'Apartemen', 'Rumah Kontrakan', 'Kendaraan'];

// Compute Filter Data
const filteredProperties = computed(() => {
    return props.properties.filter(item => {
        const matchesSearch = item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              item.city.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              item.address.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesCategory = selectedCategory.value === 'Semua' || item.category === selectedCategory.value;
        const matchesStatus = selectedStatus.value === 'Semua' || item.status === selectedStatus.value;

        return matchesSearch && matchesCategory && matchesStatus;
    });
});

// Stat Computations
const totalUnit = computed(() => props.properties.length);
const totalTersewa = computed(() => props.properties.filter(p => p.status === 'Tersewa').length);
const totalTersedia = computed(() => props.properties.filter(p => p.status === 'Tersedia').length);
</script>

<template>
    <Head title="Kelola Properti & Aset - kusewa.id" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <!-- ==================== SIDEBAR ==================== -->
        <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between p-4 shrink-0 hidden lg:flex">
            <div class="space-y-6">
                <!-- Brand Logo Top Sidebar -->
                <div class="flex items-center justify-between px-2 py-1">
                    <Link href="/owner/dashboard" class="flex items-center gap-1.5">
                        <span class="font-black text-2xl tracking-tight text-[#0A2540]">
                            kusewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>
                    <span class="text-[9px] font-black text-[#0A2540] bg-[#FFC000]/20 px-2 py-0.5 rounded-md uppercase">Owner</span>
                </div>

                <!-- User Profile Switcher -->
                <div class="flex items-center justify-between p-2 rounded-xl border border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-xs">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</h4>
                            <p class="text-[10px] text-slate-400 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                </div>

                <!-- Navigation List Utama -->
                <nav class="space-y-1 text-xs">
                    <Link href="/owner/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fa-solid fa-house-chimney text-slate-400"></i>
                        <span>Dashboard</span>
                    </Link>

                    <!-- ACTIVE MENU: Properti & Aset -->
                    <Link href="#" class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-100 text-[#0A2540] font-bold transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-building text-[#0A2540]"></i>
                            <span>Properti & Aset</span>
                        </div>
                        <span class="bg-[#FFC000]/20 text-[#0A2540] text-[10px] font-black px-1.5 py-0.5 rounded">{{ totalUnit }}</span>
                    </Link>

                    <Link href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-receipt text-slate-400"></i>
                            <span>Pemesanan</span>
                        </div>
                        <span class="bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full">3</span>
                    </Link>

                    <Link href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fa-solid fa-wallet text-slate-400"></i>
                        <span>Keuangan</span>
                    </Link>

                    <Link href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-id-card text-slate-400"></i>
                            <span>Verifikasi Berkas</span>
                        </div>
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                    </Link>
                </nav>

                <hr class="border-slate-100" />

                <!-- Menu Umum -->
                <nav class="space-y-1 text-xs">
                    <Link href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fa-solid fa-gear text-slate-400"></i>
                        <span>Pengaturan Akun</span>
                    </Link>
                    <Link href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fa-solid fa-headset text-slate-400"></i>
                        <span>Bantuan kusewa</span>
                    </Link>
                </nav>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT ==================== -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <Link href="/owner/dashboard" class="hover:text-[#0A2540]">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <span class="text-slate-800 font-bold">Daftar Properti & Aset</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#FFC000] text-[#0A2540] flex items-center justify-center font-black text-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <p class="text-xs font-bold text-slate-800">Pemilik Terverifikasi</p>
                            <p class="text-[10px] text-emerald-600 font-semibold">● Akses Penuh</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT AREA -->
            <div class="p-6 space-y-6 max-w-[1400px] w-full mx-auto">

                <!-- TITLE ROW & ADD ACTION -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Kelola Properti & Aset Saya</h1>
                        <p class="text-xs text-slate-400 mt-0.5">Pantau status keterisian, harga, dan manajemen unit yang kamu sewakan.</p>
                    </div>

                    <Link 
                        href="/owner/property/create" 
                        class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-black px-4 py-2.5 rounded-xl shadow-xs transition flex items-center justify-center gap-2 text-xs w-fit"
                    >
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Properti Baru</span>
                    </Link>
                </div>

                <!-- TOP METRIC SUMMARY -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-[#FFC000] flex items-center justify-center text-base font-bold">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Aset Didaftarkan</span>
                            <span class="text-lg font-black text-slate-900">{{ totalUnit }} Unit</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold">
                            <i class="fa-solid fa-house-lock"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Sedang Tersewa</span>
                            <span class="text-lg font-black text-emerald-600">{{ totalTersewa }} Unit</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold">
                            <i class="fa-solid fa-[#0A2540] fa-door-open"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Siap Disewakan (Kosong)</span>
                            <span class="text-lg font-black text-blue-600">{{ totalTersedia }} Unit</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR & SEARCH -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm space-y-3">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                        
                        <!-- Search Box -->
                        <div class="relative w-full md:w-80">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Cari nama properti, alamat, kota..." 
                                class="w-full bg-slate-50 text-xs pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                            />
                        </div>

                        <!-- Dropdowns & View Mode Toggles -->
                        <div class="flex items-center gap-2.5 w-full md:w-auto justify-between md:justify-end">
                            
                            <!-- Filter Status -->
                            <select v-model="selectedStatus" class="bg-slate-50 text-xs border border-slate-200 rounded-xl px-3 py-2 font-medium focus:outline-none">
                                <option value="Semua">Semua Status</option>
                                <option value="Tersewa">Tersewa</option>
                                <option value="Tersedia">Tersedia</option>
                            </select>

                            <!-- Toggle Tampilan (Grid / List) -->
                            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/60">
                                <button 
                                    @click="viewMode = 'grid'" 
                                    :class="['p-1.5 rounded-lg text-xs transition', viewMode === 'grid' ? 'bg-white text-[#0A2540] shadow-xs font-bold' : 'text-slate-400']"
                                    title="Tampilan Grid"
                                >
                                    <i class="fa-solid fa-border-all"></i>
                                </button>
                                <button 
                                    @click="viewMode = 'table'" 
                                    :class="['p-1.5 rounded-lg text-xs transition', viewMode === 'table' ? 'bg-white text-[#0A2540] shadow-xs font-bold' : 'text-slate-400']"
                                    title="Tampilan Tabel"
                                >
                                    <i class="fa-solid fa-list"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs pt-2 border-t border-slate-100">
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[
                                'px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap',
                                selectedCategory === cat 
                                    ? 'bg-[#0A2540] text-white' 
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- DISPLAY MODE 1: GRID CARDS -->
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div 
                        v-for="item in filteredProperties" 
                        :key="item.id"
                        class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition group"
                    >
                        <div>
                            <!-- Card Image & Badge Header -->
                            <div class="relative h-44 bg-slate-100 overflow-hidden">
                                <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                
                                <div class="absolute top-3 left-3 flex gap-1.5">
                                    <span class="bg-[#0A2540]/90 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                        {{ item.category }}
                                    </span>
                                </div>

                                <div class="absolute top-3 right-3">
                                    <span 
                                        :class="[
                                            'text-[10px] font-black px-2.5 py-1 rounded-lg shadow-sm border backdrop-blur-md',
                                            item.status === 'Tersewa' 
                                                ? 'bg-emerald-500/90 text-white border-emerald-400' 
                                                : 'bg-amber-400/90 text-[#0A2540] border-amber-300'
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span><i class="fa-solid fa-location-dot text-[#FFC000]"></i> {{ item.city }}</span>
                                    <span class="font-semibold text-slate-600">{{ item.type }}</span>
                                </div>

                                <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1 group-hover:text-[#0A2540] transition">
                                    {{ item.title }}
                                </h3>

                                <p class="text-[11px] text-slate-400 line-clamp-1">{{ item.address }}</p>

                                <div v-if="item.tenant" class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Penyewa Aktif:</span>
                                    <span class="font-bold text-slate-800">{{ item.tenant }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Price & Action -->
                        <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block leading-tight">Harga Sewa</span>
                                <span class="text-sm font-extrabold text-[#0A2540]">
                                    Rp {{ item.price.toLocaleString('id-ID') }}
                                    <span class="text-[10px] font-normal text-slate-400">/{{ item.rent_period }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button class="p-2 text-slate-500 hover:text-[#0A2540] hover:bg-slate-200 rounded-lg transition" title="Edit Unit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Unit">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DISPLAY MODE 2: TABLE LIST -->
                <div v-else class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="p-4">Info Properti</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Lokasi</th>
                                    <th class="p-4">Harga Sewa</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                <tr v-for="item in filteredProperties" :key="item.id" class="hover:bg-slate-50/50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="item.image" class="w-12 h-12 rounded-xl object-cover shrink-0" />
                                            <div>
                                                <h4 class="font-bold text-slate-800">{{ item.title }}</h4>
                                                <p class="text-[10px] text-slate-400">{{ item.type }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="bg-slate-100 text-slate-700 font-bold text-[10px] px-2 py-1 rounded-md">
                                            {{ item.category }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold text-slate-800">{{ item.city }}</p>
                                        <p class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ item.address }}</p>
                                    </td>
                                    <td class="p-4 font-bold text-[#0A2540]">
                                        Rp {{ item.price.toLocaleString('id-ID') }}
                                        <span class="text-[10px] font-normal text-slate-400">/{{ item.rent_period }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span 
                                            :class="[
                                                'text-[10px] font-bold px-2 py-0.5 rounded-full border',
                                                item.status === 'Tersewa' 
                                                    ? 'bg-emerald-50 text-emerald-600 border-emerald-200' 
                                                    : 'bg-amber-50 text-amber-700 border-amber-200'
                                            ]"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button class="p-1.5 text-slate-400 hover:text-[#0A2540] transition">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="p-1.5 text-slate-400 hover:text-rose-500 transition">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EMPTY STATE (Jika pencarian tidak ditemukan) -->
                <div v-if="filteredProperties.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/70 shadow-sm space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-building-circle-exclamation"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Properti Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Coba ubah kata kunci pencarian atau sesuaikan filter status dan kategori kamu.</p>
                </div>

            </div>
        </main>

    </div>
</template>