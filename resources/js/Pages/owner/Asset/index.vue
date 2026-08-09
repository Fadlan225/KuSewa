<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import LazyAssetCard from '@/Components/UI/LazyAssetCard.vue';
import HorizontalAssetCard from '@/Components/UI/HorizontalAssetCard.vue';

const page = usePage();

const props = defineProps({
    properties: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({
            totalAsset: 0,
            totalAvailable: 0,
            totalOccupied: 0,
            totalPendingVerification: 0
        })
    },
    kategoriPropertiGroups: {
        type: Array,
        default: () => []
    }
});

// Kategori langsung ambil dari database (lewat props)
const kategoriPropertiGroups = computed(() => props.kategoriPropertiGroups);

// Kategori dihapus default properties-nya karena langsung ambil dari database

const propertyList = computed(() => {
    return props.properties?.data || [];
});

const paginationLinks = computed(() => props.properties?.links?.filter(l => l.url) || []);
const paginationMeta = computed(() => ({
    from: props.properties?.from || 0,
    to: props.properties?.to || 0,
    total: props.properties?.total || 0,
}));

// State Filter & View Mode
const urlParams = new URLSearchParams(window.location.search);
const searchQuery = ref(urlParams.get('search') || '');
const selectedCategory = ref(urlParams.get('category') || 'Semua');
const selectedJenis = ref(urlParams.get('jenis') || 'Semua');
const selectedStatus = ref(urlParams.get('status') || 'Semua');
const viewMode = ref('grid');

// Reset filter jenis setiap kali kategori utama diganti, karena daftar jenis ikut berubah
watch(selectedCategory, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        selectedJenis.value = 'Semua';
    }
});

// Daftar jenis properti yang tersedia sesuai kategori utama yang sedang difilter
const availableJenisFilter = computed(() => {
    if (selectedCategory.value === 'Semua') {
        return kategoriPropertiGroups.value.flatMap(g => g.options);
    }
    const group = kategoriPropertiGroups.value.find(g => g.label === selectedCategory.value);
    return group ? group.options : [];
});

// Daftar pill Kategori Utama (Semua + tiap grup dari create.vue)
const categories = computed(() => ['Semua', ...kategoriPropertiGroups.value.map(g => g.label)]);

// Update filter watch to automatically trigger Inertia visit or just let frontend filter it?
// The prompt asked for: "Gunakan request: search, category, jenis, status Lalu gunakan: ->withQueryString() agar filter tetap tersimpan saat berpindah halaman."
// To do this, we need to watch these values and use Inertia router.get
watch([searchQuery, selectedCategory, selectedJenis, selectedStatus], () => {
    router.get('/owner/asset', {
        search: searchQuery.value,
        category: selectedCategory.value,
        jenis: selectedJenis.value,
        status: selectedStatus.value
    }, { preserveState: true, replace: true, preserveScroll: true });
}, { deep: true });

// Filter Computation is not needed for frontend anymore, but we can just return propertyList
const filteredProperties = computed(() => {
    return propertyList.value;
});

// Summary Stat Computations
const totalUnit = computed(() => props.stats?.totalAsset || 0);
const totalTersewa = computed(() => props.stats?.totalOccupied || 0);
const totalTersedia = computed(() => props.stats?.totalAvailable || 0);
const totalPendingVerifikasi = computed(() => props.stats?.totalPendingVerification || 0);
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
    router.delete(`/owner/asset/${deletingProperty.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            closeDeleteModal();
        },
    });
};
</script>

<template>
    <Head title="Kelola Aset & Unit" />

    <DashboardLayout
        title="Kelola Aset & Unit Saya"
        description="Pantau status keterisian, harga, dan manajemen unit yang Anda sewakan secara real-time."
        role="Owner"
    >
        <template #action>
            <Link
                href="/owner/asset/create"
                class="bg-primary hover:bg-primary/80 active:scale-95 text-white font-bold px-5 py-2.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-xs w-fit"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Ajukan Aset Baru</span>
            </Link>
        </template>

        <div class="space-y-6 mt-6">

                <!-- METRIC SUMMARY STATS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-[#FFC000] flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Aset Didaftarkan</span>
                            <span class="text-xl font-black text-slate-900">{{ totalUnit }} Unit</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-house-lock"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Sedang Tersewa</span>
                            <span class="text-xl font-black text-emerald-600">{{ totalTersewa }} Unit</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Siap Disewakan (Kosong)</span>
                            <span class="text-xl font-black text-blue-600">{{ totalTersedia }} Unit</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-base font-bold shrink-0">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Menunggu Verifikasi</span>
                            <span class="text-xl font-black text-amber-600">{{ totalPendingVerifikasi }} Unit</span>
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
                                placeholder="Cari nama aset, alamat, kota..."
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
                                    ? 'bg-primary text-white shadow-xs'
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- VIEW 1: GRID CARDS (LazyAssetCard Style) -->
                <div v-if="viewMode === 'grid' && filteredProperties.length > 0" class="flex flex-wrap gap-5">
                    <LazyAssetCard
                        v-for="item in filteredProperties"
                        :key="item.id"
                        :asset="item"
                        :category-name="item.category"
                        :is-owner="true"
                        @delete="openDeleteModal"
                    />
                </div>

                <!-- VIEW 2: HORIZONTAL LIST (HorizontalAssetCard Style) -->
                <div v-else-if="viewMode === 'table' && filteredProperties.length > 0" class="flex flex-col gap-3">
                    <HorizontalAssetCard
                        v-for="item in filteredProperties"
                        :key="item.id"
                        :asset="item"
                        :category-name="item.category"
                        :is-owner="true"
                        @delete="openDeleteModal"
                    />
                </div>

                <!-- EMPTY STATE -->
                <div v-if="filteredProperties.length === 0" class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <img src="/empty.svg" alt="Belum ada data" class="w-40 sm:w-48 h-auto mb-5 mx-auto opacity-90 drop-shadow-sm" />
                    <h3 class="text-2xl font-black text-[#0A2540] mb-3 tracking-tight">Belum Ada Aset Terdaftar</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mb-8 leading-relaxed">
                        Anda belum menambahkan aset apa pun untuk disewakan. Mulai langkah pertama Anda untuk mengelola bisnis sewa bersama kami!
                    </p>

                    <Link
                        href="/owner/asset/create"
                        class="bg-[#FFC000] hover:bg-[#e5ac00] active:scale-95 text-[#0A2540] font-black px-8 py-3.5 rounded-full text-sm uppercase tracking-wider transition-all shadow-sm inline-block"
                    >
                        Tambahkan Aset Baru
                    </Link>
                </div>

                <!-- PAGINATION -->
                <div v-if="props.properties?.last_page > 1" class="flex items-center justify-center gap-1 pt-2">
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

        <!-- DELETE CONFIRMATION MODAL -->
        <Teleport to="body">
            <div v-if="deleteModal" class="fixed inset-0 z-[60] flex items-center justify-center">
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
    </DashboardLayout>
</template>
