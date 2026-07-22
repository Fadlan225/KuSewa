<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { router, usePage, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HorizontalAssetCard from '@/Components/UI/HorizontalAssetCard.vue';
import AssetCardSkeleton from '@/Components/UI/AssetCardSkeleton.vue';

const page = usePage();

const props = defineProps({
    assets:              { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    filters:             { type: Object, default: () => ({}) },
    searchHistory:       { type: Array,  default: () => [] },
    trending:            { type: Array,  default: () => [] },
    locationSuggestions: { type: Array,  default: () => [] },
    facilities:          { type: Array,  default: () => [] },
    categories:          { type: Array,  default: () => [] },
});

import { useHomeSearch } from '@/Composables/useHomeSearch';
import MobileSearchSheet from '@/Pages/Home/MobileSearchSheet.vue';
import StickySubNavSearch from '@/Components/UI/StickySubNavSearch.vue';

const {
    keywordQuery,
    selectedAssets,
    startDate,
    endDate,
    minPrice,
    maxPrice,
    selectedFacilities,
    sortOption,
    hydrateFilters,
    isMobileSearchOpen,
    searchQuery, // location search
    performSearch,
    minPercent,
    maxPercent,
    startDrag,
    sliderTrack,
    activeThumb,
    validatePrices,
    parsedMinPrice,
    parsedMaxPrice,
    formatPriceShort
} = useHomeSearch();

const maxLimit = 16000000;

// On mount, hydrate filters from Inertia page props
onMounted(() => {
    hydrateFilters(props.filters);
});


const isSortOpenDesktop = ref(false);

const sortOptions = [
    { label: 'Popularitas Tertinggi', value: 'popular', icon: 'fa-solid fa-star' },
    { label: 'Harga Terendah', value: 'price_asc', icon: 'fa-solid fa-arrow-down-short-wide' },
    { label: 'Harga Tertinggi', value: 'price_desc', icon: 'fa-solid fa-arrow-up-wide-short' }
];

// Watch for specific changes to auto-apply, EXCEPT price since it triggers via stopDrag/blur
watch([selectedAssets, startDate, endDate, selectedFacilities, sortOption], () => {
    // Only apply if user is NOT on mobile (mobile uses "Terapkan Filter" button)
    // Actually, on desktop it auto-applies, let's keep it simple.
    applyFilters();
}, { deep: true });

const handleMin = (e) => {
    // we use imported handleMinPriceInput from useHomeSearch
    // wait, we don't have handleMinPriceInput in Index? We imported it! But let's just make a simple one:
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    minPrice.value = val;
    validatePrices();
    applyFilters(); 
};
const handleMax = (e) => {
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    if (val > maxLimit) val = maxLimit;
    maxPrice.value = val;
    validatePrices();
    applyFilters(); 
};

// Watch activeThumb to trigger applyFilters when drag ends (activeThumb becomes null)
watch(activeThumb, (newVal, oldVal) => {
    if (oldVal !== null && newVal === null) {
        applyFilters();
    }
});

// UI State
const isLoading = ref(false);

const assetData = computed(() => props.assets.data || []);
const hasActiveFilters = computed(() => {
    return keywordQuery.value || selectedAssets.value.length > 0 || startDate.value || endDate.value || selectedFacilities.value.length > 0 || minPrice.value > 0 || maxPrice.value < maxLimit;
});

const searchTitle = computed(() => {
    if (keywordQuery.value) return `Hasil untuk "${keywordQuery.value}"`;
    return 'Semua Aset';
});

// Aksi Filter
const toggleCategory = (catName) => {
    const idx = selectedAssets.value.indexOf(catName);
    if (idx > -1) selectedAssets.value.splice(idx, 1);
    else selectedAssets.value.push(catName);
};

const toggleFacility = (fac) => {
    const idx = selectedFacilities.value.indexOf(fac);
    if (idx > -1) selectedFacilities.value.splice(idx, 1);
    else selectedFacilities.value.push(fac);
};

const resetFilters = () => {
    keywordQuery.value = '';
    selectedAssets.value = [];
    startDate.value = null;
    endDate.value = null;
    selectedFacilities.value = [];
    minPrice.value = 0;
    maxPrice.value = maxLimit;
    sortOption.value = 'popular';
    applyFilters();
};

let filterTimeout = null;
const applyFilters = () => {
    if (filterTimeout) clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        isLoading.value = true;
        // Kita panggil performSearch dari useHomeSearch saja
        performSearch();
        isLoading.value = false;
    }, 500);
};

// Untuk format rupiah di view

// Untuk format rupiah di view
const formatIDR = (val) => new Intl.NumberFormat('id-ID').format(val);
</script>

<template>
    <AppLayout>
        <Head :title="searchTitle + ' - KuSewa'" />

        <!-- Mobile Search Sheet Component -->
        <MobileSearchSheet />

        <!-- DESKTOP STICKY SUB-NAV SEARCH -->
        <div class="lg:sticky lg:top-[64px] z-[60] w-full">
            <StickySubNavSearch />
        </div>

        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-12 pb-24 sm:pb-16 text-[#1D1D1F]">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- SIDEBAR KIRI (DESKTOP) -->
                <div class="hidden lg:block lg:col-span-3 space-y-6">
                    
                    <!-- Dummy Map -->
                    <div class="bg-white rounded-2xl p-4 flex flex-col items-center justify-center text-center shadow-sm overflow-hidden relative">
                        <!-- Pola peta SVG sederhana sbg background -->
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0ibm9uZSIvPgo8Y2lyY2xlIGN4PSIyIiBjeT0iMiIgcj0iMSIgZmlsbD0iIzBBMjU0MCIvPgo8L3N2Zz4=')]"></div>
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-md text-[#0A2540] mb-3 relative z-10">
                            <i class="fa-solid fa-map-location-dot text-xl"></i>
                        </div>
                        <button class="bg-[#0A2540] text-white px-6 py-2 rounded-full font-bold text-xs shadow hover:bg-[#1a365d] transition relative z-10 w-full">
                            Eksplor di Peta
                        </button>
                    </div>


                    <!-- Filter Fasilitas -->
                    <div class="bg-white rounded-2xl p-5 border shadow-sm">
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Fasilitas Populer</h3>
                        <div class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <label v-for="fac in props.facilities" :key="fac" class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :checked="selectedFacilities.includes(fac)" @change="toggleFacility(fac)" class="peer sr-only">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:bg-[#0A2540] peer-checked:border-[#0A2540] transition flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-[#6C757D] group-hover:text-[#0A2540] transition">{{ fac }}</span>
                            </label>
                        </div>
                    </div>


                </div>

                <!-- CONTENT KANAN -->
                <div class="col-span-1 lg:col-span-9">
                    <!-- HEADER HASIL & SORTING -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-[#0A2540]">{{ searchQuery || 'Semua Lokasi' }}</h1>
                            <p class="text-xs sm:text-sm text-[#6C757D] mt-0.5">{{ props.assets?.total ?? assetData.length }} properti ditemukan</p>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-[#6C757D] hidden sm:inline">Urutkan:</span>
                            <div class="relative w-48 z-10">
                                <button
                                    @click="isSortOpenDesktop = !isSortOpenDesktop"
                                    class="w-full flex items-center justify-between rounded-xl bg-slate-100/80 hover:bg-slate-200/60 border-0 px-3 py-2 text-xs font-medium text-[#1D1D1F] transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <i class="text-slate-500 w-3 text-center" :class="sortOptions.find(o => o.value === sortOption)?.icon"></i>
                                        {{ sortOptions.find(o => o.value === sortOption)?.label || 'Urutkan' }}
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform" :class="isSortOpenDesktop ? 'rotate-180' : ''"></i>
                                </button>
                                
                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95"
                                >
                                    <div v-if="isSortOpenDesktop" class="absolute z-50 right-0 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
                                        <div class="py-1">
                                            <button
                                                v-for="option in sortOptions"
                                                :key="option.value"
                                                @click="sortOption = option.value; isSortOpenDesktop = false"
                                                class="w-full flex items-center gap-2 px-3 py-2.5 text-xs text-left"
                                                :class="sortOption === option.value ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                            >
                                                <i :class="[option.icon, sortOption === option.value ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center"></i>
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- HASIL PENCARIAN -->
                    <div class="relative min-h-[400px]">
                        <!-- Loading Overlay -->
                        <div v-if="isLoading" class="absolute inset-0 z-20 bg-white/70 backdrop-blur-sm flex justify-center items-start pt-10">
                            <div class="w-10 h-10 border-4 border-[#FFC000] border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        <div v-if="isLoading && assetData.length === 0" class="flex flex-col gap-4">
                            <AssetCardSkeleton layout="horizontal" v-for="i in 5" :key="i" class="w-full flex-none" />
                        </div>

                        <div v-else-if="assetData.length > 0" class="flex flex-col gap-4">
                            <HorizontalAssetCard 
                                v-for="(asset, index) in assetData" 
                                :key="asset.id"
                                :asset="asset"
                            :categoryName="asset.type?.category?.name || asset.category?.name || 'Lainnya'"
                            />
                        </div>

                        <!-- ========== EMPTY STATE (3 KONDISI) ========== -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center pt-12 pb-32 px-4 w-full text-center"
                        >
                            <img
                                src="/empty.svg"
                                class="w-40 sm:w-48 h-40 sm:h-48 object-contain mb-6 opacity-80"
                                alt="Ilustrasi Kosong"
                                onerror="this.src='https://placehold.co/400x300/f8f9fa/6c757d.png?text=Kosong'"
                            >
                            <h3 class="text-lg sm:text-xl font-extrabold text-[#0A2540] mb-2">
                                {{ hasActiveFilters ? 'Filter Terlalu Spesifik' : 'Pencarian Kosong' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mb-6">
                                {{ hasActiveFilters ? 'Coba ubah harga, jadwal, atau hapus beberapa kategori/fasilitas untuk melihat lebih banyak hasil.' : 'Belum ada aset yang sesuai dengan kata kunci pencarian Anda.' }}
                            </p>
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                class="bg-[#FFC000] text-[#0A2540] text-sm font-bold py-2.5 px-6 rounded-full shadow uppercase tracking-wide hover:bg-[#e6ad00] transition-colors active:scale-95"
                            >
                                Reset Semua Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
    </AppLayout>
</template>
