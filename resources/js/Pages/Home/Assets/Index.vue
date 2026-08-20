<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Map, Search, ChevronDown, Check } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { router, usePage, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HorizontalAssetCard from '@/Components/ui/HorizontalAssetCard.vue';
import AssetCardSkeleton from '@/Components/ui/AssetCardSkeleton.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    assets:              { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    filters:             { type: Object, default: () => ({}) },
    searchHistory:       { type: Array,  default: () => [] },
    trending:            { type: Array,  default: () => [] },
    locationSuggestions: { type: Array,  default: () => [] },
    facilitiesByType:    { type: Array,  default: () => [] }, // [{type_id, type_name, facilities:[{id,name,icon}]}]
    categories:          { type: Array,  default: () => [] },
});

import { useHomeSearch } from '@/Composables/useHomeSearch';
import MobileSearchSheet from '@/Pages/Home/Search/MobileSearchSheet.vue';
import KeywordSearchSheet from '@/Pages/Home/Search/KeywordSearchSheet.vue';
import StickySubNavSearch from '@/Components/ui/StickySubNavSearch.vue';

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

// Filters hydrated in the main onMounted block below


const isSortOpenDesktop = ref(false);

const sortOptions = [
    { label: 'Populer', value: 'popular', icon: 'fa-solid fa-star' },
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

const assetData = ref(props.assets.data || []);
const nextUrl = ref(props.assets.next_page_url || (props.assets.links && props.assets.links.next));
const isLoadingMore = ref(false);
const loadMoreTarget = ref(null);

watch(() => props.assets, (newAssets) => {
    assetData.value = newAssets.data || [];
    nextUrl.value = newAssets.next_page_url || (newAssets.links && newAssets.links.next);
}, { deep: true });

const loadMore = async () => {
    if (!nextUrl.value || isLoadingMore.value) return;
    isLoadingMore.value = true;
    try {
        const response = await axios.get(nextUrl.value, {
            headers: {
                'X-Inertia': 'true',
                'X-Inertia-Version': page.version
            }
        });
        const newAssets = response.data.props.assets;
        assetData.value = [...assetData.value, ...newAssets.data];
        nextUrl.value = newAssets.next_page_url || (newAssets.links && newAssets.links.next);
    } catch (e) {
        console.error('Gagal memuat lebih banyak data', e);
    } finally {
        isLoadingMore.value = false;
    }
};

onMounted(() => {
    hydrateFilters(props.filters);

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && nextUrl.value && !isLoadingMore.value) {
            loadMore();
        }
    }, {
        rootMargin: '100px',
        threshold: 0.1
    });

    watch(loadMoreTarget, (el) => {
        if (el) observer.observe(el);
    }, { immediate: true });
});

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

const toggleFacility = (facId) => {
    const idx = selectedFacilities.value.indexOf(facId);
    if (idx > -1) selectedFacilities.value.splice(idx, 1);
    else selectedFacilities.value.push(facId);
};

// State untuk pencarian dan collapse fasilitas
const facilitySearch = ref('');
const openFacilityCategories = ref({});

const toggleFacilityCategory = (catName) => {
    openFacilityCategories.value[catName] = !openFacilityCategories.value[catName];
};

// Fasilitas yang tampil di sidebar — dinamis berdasarkan tipe yang dipilih, digrup per kategori dan difilter
const groupedFacilities = computed(() => {
    if (!props.facilitiesByType || props.facilitiesByType.length === 0) return [];

    let flat = [];
    const seen = new Set();

    // 1. Ambil list fasilitas flat sesuai tipe yang dipilih
    if (selectedAssets.value.length === 0) {
        props.facilitiesByType.forEach(typeGroup => {
            typeGroup.facilities.forEach(f => {
                if (!seen.has(f.id)) {
                    seen.add(f.id);
                    flat.push(f);
                }
            });
        });
    } else {
        props.facilitiesByType
            .filter(typeGroup => selectedAssets.value.includes(typeGroup.type_name))
            .forEach(typeGroup => {
                typeGroup.facilities.forEach(f => {
                    if (!seen.has(f.id)) {
                        seen.add(f.id);
                        flat.push(f);
                    }
                });
            });
    }

    // 2. Filter berdasarkan pencarian
    if (facilitySearch.value.trim() !== '') {
        const query = facilitySearch.value.toLowerCase();
        flat = flat.filter(f => f.name.toLowerCase().includes(query));
    }

    // 3. Kelompokkan per kategori
    const groups = {};
    flat.forEach(f => {
        const cat = f.category_name || 'Lainnya';
        if (!groups[cat]) {
            groups[cat] = [];
            // Buka kategori secara default jika belum diset
            if (openFacilityCategories.value[cat] === undefined) {
                openFacilityCategories.value[cat] = true;
            }
        }
        groups[cat].push(f);
    });

    // Jika sedang mencari, paksa buka semua kategori yang tampil
    if (facilitySearch.value.trim() !== '') {
        Object.keys(groups).forEach(cat => openFacilityCategories.value[cat] = true);
    }

    // Konversi object ke array dan urutkan abjad kategori
    return Object.keys(groups).map(catName => ({
        name: catName,
        facilities: groups[catName]
    })).sort((a, b) => a.name.localeCompare(b.name));
});

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
        <Head :title="searchTitle + ' - KitaSewa'" />

        <!-- Mobile Search Sheet Component -->
        <MobileSearchSheet />
        <KeywordSearchSheet :search-history="props.searchHistory" :trending="props.trending" />

        <!-- DESKTOP STICKY SUB-NAV SEARCH -->
        <div class="lg:sticky lg:top-[64px] z-[60] w-full">
            <StickySubNavSearch />
        </div>

        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-12 pb-24 sm:pb-16 text-[#1D1D1F]">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- SIDEBAR KIRI (DESKTOP) -->
                <div class="hidden lg:block lg:col-span-3 space-y-6 lg:sticky lg:top-[140px] h-fit max-h-[calc(100vh-160px)] overflow-y-auto hide-scrollbar pb-6 pr-4 border-r border-gray-100 shadow-[4px_0_15px_-5px_rgba(0,0,0,0.05)]">

                    <!-- Dummy Map -->
                    <div class="bg-white rounded-2xl p-4 flex flex-col items-center justify-center text-center shadow-sm overflow-hidden relative">
                        <!-- Gambar peta sebagai background -->
                        <div class="absolute inset-0 opacity-40 bg-[url('/images/dummy-map.png')] bg-cover bg-center"></div>
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-md text-[#0A2540] mb-3 relative z-10">
                            <Map class="text-xl" />
                        </div>
                        <button class="bg-[#0A2540] text-white px-6 py-2 rounded-full font-bold text-xs shadow hover:bg-[#1a365d] transition relative z-10 w-full">
                            Eksplor di Peta
                        </button>
                    </div>


                    <!-- Filter Fasilitas Header & Search -->
                    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-extrabold text-[#0A2540] text-[15px]">
                                Fasilitas
                                <span v-if="selectedAssets.length > 0" class="ml-1 text-[11px] font-semibold text-[#FFC000] bg-amber-50 px-1.5 py-0.5 rounded-full">
                                    {{ selectedAssets[0] }}
                                </span>
                            </h3>
                            <span v-if="selectedFacilities.length > 0" class="text-[10px] font-bold text-[#0A2540] bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                {{ selectedFacilities.length }} dipilih
                            </span>
                        </div>

                        <!-- Search input for facilities -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <Search class="text-gray-400 text-xs" />
                            </div>
                            <input type="text" v-model="facilitySearch" placeholder="Cari fasilitas..." class="w-full text-sm border-gray-200 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl pl-9 py-2 bg-slate-50 transition" />
                        </div>
                    </div>

                    <!-- Empty state kalau tipe dipilih tapi tidak punya fasilitas -->
                    <p v-if="groupedFacilities.length === 0" class="text-xs text-gray-400 text-center py-4 bg-white rounded-2xl border border-gray-200 shadow-sm">
                        Tidak ada fasilitas tersedia
                    </p>

                    <!-- Fasilitas Cards (Per Kategori) -->
                    <div v-else class="space-y-4">
                        <div v-for="group in groupedFacilities" :key="group.name" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <!-- Group Header -->
                            <button @click="toggleFacilityCategory(group.name)" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition">
                                <span class="text-[15px] font-extrabold text-[#0A2540]">{{ group.name }}</span>
                                <div class="w-7 h-7 rounded-full bg-slate-50 flex items-center justify-center">
                                    <ChevronDown class="text-xs text-slate-400 transition-transform" :class="{'rotate-180': openFacilityCategories[group.name]}" />
                                </div>
                            </button>

                            <!-- Group Content (Checkboxes) -->
                            <div v-show="openFacilityCategories[group.name]" class="px-4 pb-4 space-y-1">
                                <label
                                    v-for="fac in group.facilities"
                                    :key="fac.id"
                                    class="flex items-center gap-3 cursor-pointer group py-1.5 px-2 rounded-lg hover:bg-slate-50 transition"
                                >
                                    <div class="relative flex items-center shrink-0">
                                        <input
                                            type="checkbox"
                                            :checked="selectedFacilities.includes(fac.id)"
                                            @change="toggleFacility(fac.id)"
                                            class="peer sr-only"
                                        >
                                        <div class="w-5 h-5 rounded border border-gray-300 bg-white peer-checked:bg-[#FFC000] peer-checked:border-[#FFC000] transition flex items-center justify-center">
                                            <Check class="text-white text-[10px] opacity-0 peer-checked:opacity-100" />
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-[#6C757D] group-hover:text-[#0A2540] transition leading-tight">
                                        {{ fac.name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- CONTENT KANAN -->
                <div class="col-span-1 lg:col-span-9">
                    <!-- HEADER HASIL & SORTING -->
                    <div class="flex flex-row items-center justify-between mb-6 gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg sm:text-2xl font-extrabold text-[#0A2540] truncate">{{ searchQuery || 'Semua Lokasi' }}</h1>
                            <p class="text-[11px] sm:text-sm text-[#6C757D] mt-0.5">{{ props.assets?.total ?? assetData.length }} Aset ditemukan</p>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                            <span class="text-xs font-bold text-[#6C757D] hidden sm:inline">Urutkan:</span>
                            <div class="relative w-36 sm:w-48 z-40">
                                <button
                                    @click="isSortOpenDesktop = !isSortOpenDesktop"
                                    class="w-full flex items-center justify-between rounded-xl bg-slate-100/80 hover:bg-slate-200/60 border-0 px-3 py-2 text-xs font-medium text-[#1D1D1F] transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <AppIcon iconClass="text-slate-500 w-3 text-center" :class="sortOptions.find(o => o.value === sortOption)?.icon" />
                                        {{ sortOptions.find(o => o.value === sortOption)?.label || 'Urutkan' }}
                                    </div>
                                    <ChevronDown class="text-slate-400 text-[10px] transition-transform" :class="isSortOpenDesktop ? 'rotate-180' : ''" />
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
                                                <AppIcon :iconClass="[option.icon, sortOption === option.value ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center" />
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

                            <!-- Intersection Observer Target for Infinite Scroll -->
                            <div ref="loadMoreTarget" class="h-4 w-full"></div>

                            <!-- Loading Indicator -->
                            <div v-if="isLoadingMore" class="flex justify-center items-center py-6">
                                <div class="w-8 h-8 border-4 border-[#FFC000] border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>

                        <!-- ========== EMPTY STATE (3 KONDISI) ========== -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center pt-12 pb-32 px-4 w-full text-center"
                        >
                            <EmptyStateIcon class="w-40 sm:w-48 h-40 sm:h-48 object-contain mb-6 opacity-80" />
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
