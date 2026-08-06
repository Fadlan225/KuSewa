<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue'; 

const page = usePage();

const props = defineProps({
    properties: {
        type: Array,
        default: () => []
    }
});

// --- KELOMPOK KATEGORI & JENIS PROPERTI (disamakan dengan create.vue) ---
const kategoriPropertiGroups = [
    {
        label: 'Hunian & Tempat Tinggal',
        options: ['Kos-kosan', 'Hotel', 'Rumah Tapak', 'Villa', 'Homestay', 'Apartemen', 'Guest House', 'Rusun / Condominium']
    },
    {
        label: 'Komersial & Usaha',
        options: ['Ruko (Rumah Toko)', 'Kios / Lapak Pasar', 'Kantor / Workspace', 'Gedung Komersial', 'Food Court / Booth']
    },
    {
        label: 'Penyimpanan & Industri',
        options: ['Gudang Logistik', 'Pabrik / Manufaktur', 'Cold Storage']
    },
    {
        label: 'Tanah & Lahan Kosong',
        options: ['Lahan / Tanah Kosong', 'Lahan Pertanian / Perkebunan']
    },
    {
        label: 'Media Iklan & Ruang Promosi',
        options: ['Baliho / Reklame', 'Billboard / Videotron', 'Neon Box / Titik Toko']
    }
];

// Data Dummy fallback jika backend belum kirim data
// (kategori & jenis_properti disamakan dengan struktur create.vue)
const defaultProperties = [
    {
        id: 1,
        title: 'Kos Exclusive Samarinda Indah #01',
        category: 'Hunian & Tempat Tinggal',
        type: 'Kos-kosan',
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
        category: 'Hunian & Tempat Tinggal',
        type: 'Apartemen',
        price: 3500000,
        rent_period: 'Bulan',
        city: 'Balikpapan',
        address: 'Jl. Jend. Sudirman No. 88',
        status: 'Tersedia',
        tenant: null,
        image: 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=500&q=80',
        occupancy: '0/1 Aset'
    },
    {
        id: 4,
        title: 'Kos Melati Clean & Cozyman #05',
        category: 'Hunian & Tempat Tinggal',
        type: 'Kos-kosan',
        price: 850000,
        rent_period: 'Bulan',
        city: 'Samarinda',
        address: 'Jl. Pramuka 6 No. 44',
        status: 'Tersedia',
        tenant: null,
        image: 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=500&q=80',
        occupancy: '0/1 Aset'
    },
    {
        id: 5,
        title: 'Rumah Kontrakan Minimalis A2',
        kategori: 'Hunian & Tempat Tinggal',
        jenis_properti: 'Rumah Tapak',
        price: 25000000,
        rent_period: 'Tahun',
        city: 'Samarinda',
        address: 'Jl. Juanda 8 Blok B',
        status: 'Tersewa',
        tenant: 'Rava Nanda',
        image: 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=500&q=80',
        occupancy: 'Tersewa s/d Des 2026'
    }
];

const propertyList = computed(() => {
    return props.properties?.data || [];
});

const paginationLinks = computed(() => props.properties?.meta?.links?.filter(l => l.url) || []);
const paginationMeta = computed(() => ({
    from: props.properties?.meta?.from || 0,
    to: props.properties?.meta?.to || 0,
    total: props.properties?.meta?.total || 0,
}));

// State Filter & View Mode
const searchQuery = ref('');
const selectedCategory = ref('Semua');
const selectedJenis = ref('Semua');
const selectedStatus = ref('Semua');
const viewMode = ref('grid');

// Daftar pill Kategori Utama (Semua + tiap grup dari create.vue)
const categories = computed(() => ['Semua', ...kategoriPropertiGroups.map(g => g.label)]);

// Reset filter jenis setiap kali kategori utama diganti, karena daftar jenis ikut berubah
watch(selectedCategory, () => {
    selectedJenis.value = 'Semua';
});

// Daftar jenis properti yang tersedia sesuai kategori utama yang sedang difilter
const availableJenisFilter = computed(() => {
    if (selectedCategory.value === 'Semua') {
        return kategoriPropertiGroups.flatMap(g => g.options);
    }
    const group = kategoriPropertiGroups.find(g => g.label === selectedCategory.value);
    return group ? group.options : [];
});

// Filter Computation
const filteredProperties = computed(() => {
    return propertyList.value.filter(item => {
        const matchesSearch = item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            item.city.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            item.address.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesCategory = selectedCategory.value === 'Semua' || item.category === selectedCategory.value;
        const matchesJenis = selectedJenis.value === 'Semua' || item.type === selectedJenis.value;
        const matchesStatus = selectedStatus.value === 'Semua'
            || item.status === selectedStatus.value
            || item.verification_status === selectedStatus.value;

        return matchesSearch && matchesCategory && matchesJenis && matchesStatus;
    });
});

// Summary Stat Computations
const totalAset = computed(() => paginationMeta.value.total);
const totalTersewa = computed(() => propertyList.value.filter(p => p.status === 'Tersewa').length);
const totalTersedia = computed(() => propertyList.value.filter(p => p.status === 'Tersedia').length);
const totalPendingVerifikasi = computed(() => propertyList.value.filter(p => p.verification_status === 'pending').length);
const verificationLabel = (status) => ({ pending: 'Menunggu Verifikasi', approved: 'Terverifikasi', rejected: 'Ditolak' }[status] || 'Menunggu Verifikasi');
const verificationClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] || 'bg-amber-50 text-amber-700 border-amber-200');
const verificationIcon = (status) => ({
    pending: 'fa-clock',
    approved: 'fa-circle-check',
    rejected: 'fa-circle-xmark',
}[status] || 'fa-clock');

// --- FOTO PREVIEW: AMBIL FOTO TERATAS/PERTAMA YANG DIUPLOAD OWNER ---
// Backend bisa mengirim struktur foto dalam beberapa bentuk (photos: [...], atau
// foto_properti: [{ nama_ruangan, photos/previews: [...] }, ...] sesuai form pengajuan).
// Fungsi ini mencoba tiap kemungkinan secara berurutan dan selalu mengambil foto
// PERTAMA yang ditemukan (foto paling atas/awal saat owner upload), lalu fallback
// ke field 'image' lama, dan terakhir ke placeholder kalau belum ada foto sama sekali.
const placeholderImage = 'https://placehold.co/500x300?text=Belum+Ada+Foto';

const previewImage = (item) => {
    const ambilUrl = (foto) => {
        if (!foto) return null;
        if (typeof foto === 'string') return foto;
        return foto.url || foto.path || foto.image || null;
    };

    // Bentuk 1: daftar foto flat di level properti (photos / gallery / images)
    const daftarFlat = item.photos || item.gallery || item.images;
    if (Array.isArray(daftarFlat) && daftarFlat.length > 0) {
        const url = ambilUrl(daftarFlat[0]);
        if (url) return url;
    }

    // Bentuk 2: foto dikelompokkan per kategori ruangan (mengikuti struktur form pengajuan)
    if (Array.isArray(item.foto_properti)) {
        const kategoriPertama = item.foto_properti.find(k =>
            (Array.isArray(k.photos) && k.photos.length > 0) ||
            (Array.isArray(k.previews) && k.previews.length > 0)
        );
        if (kategoriPertama) {
            const sumberFoto = kategoriPertama.photos || kategoriPertama.previews;
            const url = ambilUrl(sumberFoto[0]);
            if (url) return url;
        }
    }

    // Fallback: field 'image' tunggal (kompatibel dengan data lama/dummy)
    if (item.image) return item.image;

    return placeholderImage;
};

// --- UPDATE STATUS PROPERTY ---
const updateStatusLoading = ref(null);
const updateStatusModal = ref(false);
const updatingProperty = ref(null);
const newStatus = ref('Tersedia');

const openUpdateStatusModal = (item) => {
    updatingProperty.value = item;
    newStatus.value = item.status || 'Tersedia';
    updateStatusModal.value = true;
};

const closeUpdateStatusModal = () => {
    updateStatusModal.value = false;
    updatingProperty.value = null;
};

const confirmUpdateStatus = () => {
    if (!updatingProperty.value) return;
    updateStatusLoading.value = updatingProperty.value.id;
    
    router.patch(`/owner/property/${updatingProperty.value.id}/status`, {
        status: newStatus.value
    }, {
        preserveScroll: true,
        onFinish: () => {
            updateStatusLoading.value = null;
            closeUpdateStatusModal();
        }
    });
};

// --- DELETE PROPERTY ---
const deleteModal = ref(false);
const deletingProperty = ref(null);
const deleting = ref(false);

const openDeleteModal = (item) => {
    deletingProperty.value = item;
    deleteModal.value = true;
};

const closeDeleteModal = () => {
    deleteModal.value = false;
    deletingProperty.value = null;
};

const confirmDelete = () => {
    if (!deletingProperty.value) return;
    deleting.value = true;
    router.delete(`/owner/property/${deletingProperty.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            closeDeleteModal();
        },
    });
};
</script>

<template>
    <Head title="Kelola Properti & Aset - kusewa.id" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <!-- SIDEBAR -->
        <Sidebar />

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <Link href="/owner/dashboard" class="hover:text-[#0A2540] transition">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <span class="text-slate-800 font-bold">Daftar Properti & Aset</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#FFC000] text-[#0A2540] flex items-center justify-center font-black text-xs shadow-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <p class="text-xs font-bold text-slate-800">Pemilik Terverifikasi</p>
                            <p class="text-[10px] text-emerald-600 font-semibold">● Akses Penuh</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT CONTAINER -->
            <div class="p-6 md:p-8 space-y-6 max-w-[1400px] w-full mx-auto">

                <!-- TITLE ROW -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kelola Properti & Aset Saya</h1>
                        <p class="text-xs text-slate-400 mt-1">Pantau status keterisian, harga, dan manajemen unit yang kamu sewakan secara real-time.</p>
                    </div>

                    <Link 
                        href="/owner/property/create" 
                        class="bg-[#0A2540] hover:bg-[#14385f] active:scale-95 text-white font-bold px-5 py-2.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-xs w-fit"
                    >
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Properti Baru</span>
                    </Link>
                </div>

                <!-- METRIC SUMMARY STATS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-[#FFC000] flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Aset Didaftarkan</span>
                            <span class="text-xl font-black text-slate-900">{{ totalAset }} Aset</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-house-lock"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Sedang Tersewa</span>
                            <span class="text-xl font-black text-emerald-600">{{ totalTersewa }} Aset</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Siap Disewakan (Kosong)</span>
                            <span class="text-xl font-black text-blue-600">{{ totalTersedia }} Aset</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Menunggu Verifikasi</span>
                            <span class="text-xl font-black text-amber-600">{{ totalPendingVerifikasi }} Aset</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR & SEARCH -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                        
                        <!-- Search Box -->
                        <div class="relative w-full md:w-80">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Cari nama properti, alamat, kota..." 
                                class="w-full bg-slate-50 text-xs pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                            />
                        </div>

                        <!-- Dropdowns & Toggles -->
                        <div class="flex items-center gap-2.5 w-full md:w-auto justify-between md:justify-end flex-wrap">
                            <select v-model="selectedJenis" class="bg-slate-50 text-xs border border-slate-200 rounded-xl px-3 py-2.5 font-semibold focus:outline-none focus:border-[#0A2540]">
                                <option value="Semua">Semua Jenis</option>
                                <option v-for="jenis in availableJenisFilter" :key="jenis" :value="jenis">{{ jenis }}</option>
                            </select>

                            <select v-model="selectedStatus" class="bg-slate-50 text-xs border border-slate-200 rounded-xl px-3 py-2.5 font-semibold focus:outline-none focus:border-[#0A2540]">
                                <option value="Semua">Semua Status</option>
                                <option value="Tersewa">Tersewa</option>
                                <option value="Tersedia">Tersedia</option>
                                <option value="pending">Menunggu Verifikasi</option>
                                <option value="approved">Terverifikasi</option>
                                <option value="rejected">Ditolak</option>
                            </select>

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

                    <!-- Category Pills (Kategori Utama, disamakan dengan create.vue) -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs pt-2 border-t border-slate-100">
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[
                                'px-3.5 py-1.5 rounded-xl font-semibold transition whitespace-nowrap',
                                selectedCategory === cat 
                                    ? 'bg-[#0A2540] text-white shadow-xs' 
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- VIEW 1: GRID CARDS -->
                <div v-if="viewMode === 'grid' && filteredProperties.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div 
                        v-for="item in filteredProperties" 
                        :key="item.id"
                        class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:shadow-md transition duration-200 group"
                    >
                        <div>
                            <div class="relative h-48 bg-slate-100 overflow-hidden">
                                <img :src="previewImage(item)" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                                
                                <div class="absolute top-3 left-3">
                                    <span class="bg-[#0A2540]/90 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                        {{ item.category }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span><i class="fa-solid fa-location-dot text-[#FFC000]"></i> {{ item.city }}</span>
                                    <span class="font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">{{ item.type }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1 group-hover:text-[#0A2540] transition flex-1">
                                        {{ item.title }}
                                    </h3>
                                    <span :class="['text-[9px] font-black px-2 py-0.5 rounded-lg border shrink-0', verificationClass(item.verification_status)]">
                                        <i :class="['fa-solid mr-1', verificationIcon(item.verification_status)]"></i>
                                        {{ verificationLabel(item.verification_status) }}
                                    </span>
                                </div>

                                <p class="text-[11px] text-slate-400 line-clamp-1">{{ item.address }}</p>

                                <p v-if="item.verification_status === 'rejected' && item.verification_note" class="text-[11px] text-rose-600 bg-rose-50 rounded-lg p-2">
                                    Catatan admin: {{ item.verification_note }}
                                </p>

                                <div v-if="item.tenant" class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Penyewa:</span>
                                    <span class="font-bold text-slate-800">{{ item.tenant }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50/60 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block leading-tight">Harga Sewa</span>
                                <span class="text-sm font-black text-[#0A2540]">
                                    Rp {{ item.price.toLocaleString('id-ID') }}
                                    <span class="text-[10px] font-normal text-slate-400">/{{ item.rent_period }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-1">
                                <!-- UPDATE STATUS -->
                                <button 
                                    v-if="item.verification_status === 'approved'"
                                    @click="openUpdateStatusModal(item)" 
                                    :disabled="!!updateStatusLoading"
                                    :class="[
                                        'p-2 text-slate-400 hover:text-[#FFC000] hover:bg-amber-50 rounded-lg transition flex items-center justify-center',
                                        updateStatusLoading === item.id ? 'opacity-50 cursor-not-allowed' : ''
                                    ]" 
                                    title="Ubah Status Ketersediaan"
                                >
                                    <i :class="updateStatusLoading === item.id ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-gavel'"></i>
                                </button>

                                <!-- LIHAT DETAIL: status pengajuan/diterima/ditolak & info lengkap aset (GRID) -->
                                <Link 
                                    :href="`/owner/property/${item.id}`" 
                                    class="p-2 text-slate-400 hover:text-[#0A2540] hover:bg-slate-200/60 rounded-lg transition flex items-center justify-center" 
                                    title="Lihat Detail"
                                >
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </Link>

                                <!-- REDIRECT KE EDIT PAGE (GRID) -->
                                <Link 
                                    :href="`/owner/property/${item.id}/edit`" 
                                    class="p-2 text-slate-400 hover:text-[#0A2540] hover:bg-slate-200/60 rounded-lg transition flex items-center justify-center" 
                                    title="Edit Unit"
                                >
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </Link>

                                <button @click="openDeleteModal(item)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Unit">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW 2: TABLE LIST -->
                <div v-else-if="viewMode === 'table' && filteredProperties.length > 0" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
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
                                            <img :src="previewImage(item)" class="w-12 h-12 rounded-xl object-cover shrink-0" />
                                            <div>
                                                <h4 class="font-bold text-slate-800">{{ item.title }}</h4>
                                                <p class="text-[10px] text-slate-400">{{ item.type }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="bg-slate-100 text-slate-700 font-bold text-[10px] px-2.5 py-1 rounded-md">
                                            {{ item.category }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold text-slate-800">{{ item.city }}</p>
                                        <p class="text-[10px] text-slate-400 truncate max-w-[160px]">{{ item.address }}</p>
                                    </td>
                                    <td class="p-4 font-bold text-[#0A2540]">
                                        Rp {{ item.price.toLocaleString('id-ID') }}
                                        <span class="text-[10px] font-normal text-slate-400">/{{ item.rent_period }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span :class="['text-[10px] font-bold px-2.5 py-0.5 rounded-full border', verificationClass(item.verification_status)]">
                                            <i :class="['fa-solid mr-1', verificationIcon(item.verification_status)]"></i>
                                            {{ verificationLabel(item.verification_status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- UPDATE STATUS (TABLE) -->
                                            <button
                                                v-if="item.verification_status === 'approved'"
                                                @click="openUpdateStatusModal(item)"
                                                :disabled="!!updateStatusLoading"
                                                :class="[
                                                    'p-1.5 text-slate-400 hover:text-[#FFC000] transition flex items-center justify-center',
                                                    updateStatusLoading === item.id ? 'opacity-50 cursor-not-allowed' : ''
                                                ]"
                                                title="Ubah Status Ketersediaan"
                                            >
                                                <i :class="updateStatusLoading === item.id ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-gavel'"></i>
                                            </button>

                                            <!-- LIHAT DETAIL: status pengajuan/diterima/ditolak & info lengkap aset (TABLE) -->
                                            <Link 
                                                :href="`/owner/property/${item.id}`" 
                                                class="p-1.5 text-slate-400 hover:text-[#0A2540] transition flex items-center justify-center" 
                                                title="Lihat Detail"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                            </Link>

                                            <!-- REDIRECT KE EDIT PAGE (TABLE) -->
                                            <Link 
                                                :href="`/owner/property/${item.id}/edit`" 
                                                class="p-1.5 text-slate-400 hover:text-[#0A2540] transition flex items-center justify-center" 
                                                title="Edit Unit"
                                            >
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </Link>

                                            <button @click="openDeleteModal(item)" class="p-1.5 text-slate-400 hover:text-rose-500 transition" title="Hapus Unit">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="filteredProperties.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-building-circle-exclamation"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Properti Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Coba ubah kata kunci pencarian atau sesuaikan filter status dan kategori kamu.</p>
                </div>

                <!-- PAGINATION -->
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

            </div>
        </main>

        <!-- DELETE CONFIRMATION MODAL -->
        <Teleport to="body">
            <div v-if="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDeleteModal"></div>
                <div class="relative bg-white rounded-2xl p-6 w-full max-w-md shadow-xl mx-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Hapus Properti?</h3>
                            <p class="text-xs text-slate-400">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>

                    <div v-if="deletingProperty" class="bg-slate-50 rounded-xl p-3 text-xs space-y-1">
                        <p class="font-bold text-slate-800">{{ deletingProperty.title }}</p>
                        <p class="text-slate-400">{{ deletingProperty.type }} · {{ deletingProperty.city }}</p>
                    </div>

                    <div class="flex items-center gap-2.5 justify-end">
                        <button @click="closeDeleteModal" :disabled="deleting" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Batal
                        </button>
                        <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50">
                            <i v-if="deleting" class="fa-solid fa-spinner animate-spin"></i>
                            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- UPDATE STATUS MODAL -->
        <Teleport to="body">
            <div v-if="updateStatusModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeUpdateStatusModal"></div>
                <div class="relative bg-white rounded-2xl p-6 w-full max-w-md shadow-xl mx-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-[#FFC000] flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-gavel"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Ubah Status Ketersediaan</h3>
                            <p class="text-xs text-slate-400">Atur ketersediaan properti secara manual untuk customer langsung.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700">
                            Status Baru:
                        </label>
                        <select v-model="newStatus" class="w-full bg-slate-50 text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#FFC000] transition">
                            <option value="Tersedia">✅ Tersedia - Siap disewakan</option>
                            <option value="Tersewa">🏠 Tersewa - Sedang dalam kontrak sewa</option>
                            <option value="Maintenance">🔧 Maintenance - Tidak tersedia untuk perawatan</option>
                        </select>
                        
                        <div v-if="updatingProperty" class="bg-slate-50 rounded-xl p-3 text-xs space-y-1">
                            <p class="font-bold text-slate-800">{{ updatingProperty.title }}</p>
                            <p class="text-slate-400">Dari: <span class="font-semibold">{{ updatingProperty.status }}</span></p>
                        </div>

                        <div class="flex items-start gap-2 bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <i class="fa-solid fa-circle-info text-blue-500 text-sm mt-0.5 shrink-0"></i>
                            <p class="text-[10px] text-blue-700 leading-tight">
                                <strong>Catatan:</strong> Hanya owner dengan properti terverifikasi yang dapat mengubah status ini secara manual saat ada customer datang langsung ke lokasi.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 justify-end pt-2">
                        <button @click="closeUpdateStatusModal" :disabled="!!updateStatusLoading" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition disabled:opacity-50">
                            Batal
                        </button>
                        <button @click="confirmUpdateStatus" :disabled="!!updateStatusLoading" class="px-4 py-2 text-xs font-bold text-white bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-xl transition flex items-center gap-1.5 disabled:opacity-50">
                            <i v-if="updateStatusLoading === updatingProperty?.id" class="fa-solid fa-spinner fa-spin"></i>
                            {{ updateStatusLoading ? 'Memproses...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>