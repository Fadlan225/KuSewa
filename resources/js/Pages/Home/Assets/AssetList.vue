<script setup>
import { computed } from 'vue';
import AssetCardSkeleton from '@/Components/UI/AssetCardSkeleton.vue';
import LazyAssetCard from '@/Components/UI/LazyAssetCard.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    // Prop baru untuk mengatur jenis empty state
    emptyStateType: {
        type: String,
        default: 'no-data', // Pilihan: 'no-data', 'filter', 'search'
        validator: (value) => ['no-data', 'filter', 'search'].includes(value)
    },
    // Prop untuk kata kunci pencarian (ditampilkan di kondisi ke-3)
    searchKeyword: {
        type: String,
        default: ''
    }
});

// Daftarkan event emits untuk tombol aksi
const emit = defineEmits(['goHome', 'resetFilter', 'clearSearch']);

// Computed: kategori yang punya aset
const visibleCategories = computed(() =>
    props.categories.filter(c => c.assets && c.assets.length > 0)
);

const toggleFavorite = (asset) => {
    asset.isFavorite = !asset.isFavorite;
};

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const periodLabel = {
    hour: 'jam', day: 'hari',
    week: 'minggu', month: 'bulan', year: 'tahun'
};

// Skeleton sections: tampilkan 3 baris skeleton
const skeletonSections = 3;
// Jumlah kartu skeleton per baris
const skeletonCards = 6;
</script>

<template>
    <div class="w-full py-8 sm:py-10 space-y-10 sm:space-y-12 text-[#0A2540] font-sans overflow-x-hidden">

        <!-- ========== SKELETON (saat data belum ada) ========== -->
        <template v-if="props.loading">
            <section
                v-for="s in skeletonSections"
                :key="'sk-' + s"
                class="pl-4 sm:pl-6 lg:pl-8 animate-pulse"
            >
                <!-- Header skeleton -->
                <div class="flex justify-between items-center mb-4 pr-4 sm:pr-6 lg:pr-8">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-200"></div>
                        <div class="h-6 w-32 rounded-lg bg-gray-200"></div>
                    </div>
                    <div class="h-4 w-16 rounded bg-gray-100"></div>
                </div>

                <!-- Kartu skeleton horizontal -->
                <div class="flex gap-3 sm:gap-4 pb-6 pt-2 overflow-hidden pr-4 sm:pr-6 lg:pr-8">
                    <AssetCardSkeleton v-for="c in skeletonCards" :key="c" />
                </div>
            </section>
        </template>

        <!-- ========== DATA NYATA ========== -->
        <template v-else>
            <template v-if="visibleCategories.length > 0">
                <template v-for="category in visibleCategories" :key="category.id">
                    <section class="pl-4 sm:pl-6 lg:pl-8">

                        <!-- Section Header -->
                        <div class="flex justify-between items-end mb-4 pr-4 sm:pr-6 lg:pr-8">
                            <div class="flex items-center gap-2">
                                <i v-if="category.icon" :class="[category.icon, 'text-[#FFC000] text-base']"></i>
                                <h2 class="text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight">{{ category.name }}</h2>
                            </div>
                            <a href="#" class="text-xs sm:text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] transition-colors flex items-center gap-1">
                                <span>Lihat Semua</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>

                        <!-- Horizontal Scroll -->
                        <div class="flex overflow-x-auto gap-3 sm:gap-4 pb-6 pt-2 snap-x snap-mandatory no-scrollbar pr-4 sm:pr-6 lg:pr-8">

                            <LazyAssetCard
                                v-for="asset in category.assets"
                                :key="asset.id"
                                :asset="asset"
                                :categoryName="category.name"
                            />
                        </div>

                    </section>
                </template>
            </template>

            <!-- ========== EMPTY STATE (3 KONDISI) ========== -->
            <div
                v-else
                class="flex flex-col items-center pt-10 pb-32 px-4 w-full text-center"
            >
                <!-- Gambar SVG tunggal untuk semua kondisi -->
                <img
                    src="/empty.svg"
                    class="w-48 h-48 object-contain mb-6"
                    alt="Ilustrasi Kosong"
                >

                <!-- KONDISI 1: Belum Ada Aset (Sama Sekali) -->
                <template v-if="props.emptyStateType === 'no-data'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum Ada Aset</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Aset sedang disiapkan.</p>
                    <button
                        @click="$emit('goHome')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Ke Beranda
                    </button>
                </template>

                <!-- KONDISI 2: Aset Tidak Ditemukan (Filter) -->
                <template v-else-if="props.emptyStateType === 'filter'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Tidak Ditemukan</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Ubah filter pencarian Anda.</p>
                    <button
                        @click="$emit('resetFilter')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Reset Filter
                    </button>
                </template>

                <!-- KONDISI 3: Aset Tidak Ditemukan (Pencarian Keyword) -->
                <template v-else-if="props.emptyStateType === 'search'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Hasil Kosong</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Coba kata kunci lain.</p>
                    <button
                        @click="$emit('clearSearch')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Hapus Pencarian
                    </button>
                </template>

            </div>
        </template>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
