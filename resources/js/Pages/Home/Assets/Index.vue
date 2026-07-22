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

// Filter state lokal (independen dari useHomeSearch)
const localKeyword    = ref(props.filters.q        || '');
const localCategories = ref(props.filters.category ? (Array.isArray(props.filters.category) ? [...props.filters.category] : [props.filters.category]) : []);
const localDateStart  = ref(props.filters.date_start || '');
const localDateEnd    = ref(props.filters.date_end   || '');
const localFacilities = ref(props.filters.facilities ? (Array.isArray(props.filters.facilities) ? [...props.filters.facilities] : [props.filters.facilities]) : []);
const localSort       = ref(props.filters.sort       || 'popular');

// State Harga & Slider
const maxLimit = 16000000;
const localMinPrice   = ref(props.filters.min_price || 0);
const localMaxPrice   = ref(props.filters.max_price || maxLimit);

const priceStep = computed(() => {
    if (localMaxPrice.value <= 1000000) return 50000;
    if (localMaxPrice.value <= 10000000) return 100000;
    return 500000;
});

const priceError = ref('');
const validatePrices = () => {
    let min = parseInt(localMinPrice.value) || 0;
    let max = parseInt(localMaxPrice.value) || 0;
    if (min < 0) min = 0;
    if (max >= maxLimit) max = maxLimit;
    if (min > max) {
        priceError.value = 'Maksimal harga harus lebih besar dari minimal.';
    } else {
        priceError.value = '';
    }
    localMinPrice.value = min;
    localMaxPrice.value = max;
};

const minPercent = computed(() => {
    let min = localMinPrice.value;
    let max = localMaxPrice.value;
    if (min > max) min = max;
    return Math.min((min / maxLimit) * 100, 100);
});

const maxPercent = computed(() => {
    let min = localMinPrice.value;
    let max = localMaxPrice.value;
    if (max < min) max = min;
    return Math.min((max / maxLimit) * 100, 100);
});

const sliderTrack = ref(null);
let activeThumb = ref(null);

const startDrag = (e, type) => {
    activeThumb.value = type;
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchend', stopDrag);
};

const onDrag = (e) => {
    if (!sliderTrack.value) return;
    const rect = sliderTrack.value.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    let percent = ((clientX - rect.left) / rect.width) * 100;

    if (activeThumb.value === 'min') {
        percent = Math.max(0, Math.min(percent, maxPercent.value - 1));
        let rawPrice = (percent / 100) * maxLimit;
        localMinPrice.value = Math.round(rawPrice / priceStep.value) * priceStep.value;
    } else {
        percent = Math.max(minPercent.value + 1, Math.min(percent, 100));
        let rawPrice = (percent / 100) * maxLimit;
        localMaxPrice.value = Math.round(rawPrice / priceStep.value) * priceStep.value;
    }
    validatePrices();
};

const stopDrag = () => {
    activeThumb.value = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchend', stopDrag);
    // Hanya trigger filter SETELAH drag selesai, bukan setiap pixel!
    applyFilters();
};

const formatPriceIDR = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const handleMinInput = (e) => {
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    localMinPrice.value = val;
    validatePrices();
    applyFilters(); // Trigger search setelah input manual
};
const handleMaxInput = (e) => {
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    if (val > maxLimit) val = maxLimit;
    localMaxPrice.value = val;
    validatePrices();
    applyFilters(); // Trigger search setelah input manual
};

// UI State
const isLoading = ref(false);
const isMobileFilterOpen = ref(false);

const assetData = computed(() => props.assets.data || []);
const hasActiveFilters = computed(() => {
    return localKeyword.value || localCategories.value.length > 0 || localDateStart.value || localDateEnd.value || localFacilities.value.length > 0 || localMinPrice.value > 0 || localMaxPrice.value < maxLimit;
});

const searchTitle = computed(() => {
    if (localKeyword.value) return `Hasil untuk "${localKeyword.value}"`;
    return 'Semua Aset';
});

// Aksi Filter
const toggleCategory = (catName) => {
    const idx = localCategories.value.indexOf(catName);
    if (idx > -1) localCategories.value.splice(idx, 1);
    else localCategories.value.push(catName);
};

const toggleFacility = (fac) => {
    const idx = localFacilities.value.indexOf(fac);
    if (idx > -1) localFacilities.value.splice(idx, 1);
    else localFacilities.value.push(fac);
};

const resetFilters = () => {
    localKeyword.value = '';
    localCategories.value = [];
    localDateStart.value = '';
    localDateEnd.value = '';
    localFacilities.value = [];
    localMinPrice.value = 0;
    localMaxPrice.value = maxLimit;
    localSort.value = 'popular';
    applyFilters();
};

let filterTimeout = null;
const applyFilters = () => {
    if (filterTimeout) clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        isLoading.value = true;
        const params = {};
        if (localKeyword.value) params.q = localKeyword.value;
        if (localCategories.value.length > 0) params.category = localCategories.value;
        if (localDateStart.value) params.date_start = localDateStart.value;
        if (localDateEnd.value) params.date_end = localDateEnd.value;
        if (localFacilities.value.length > 0) params.facilities = localFacilities.value;
        if (localMinPrice.value > 0) params.min_price = localMinPrice.value;
        if (localMaxPrice.value < maxLimit) params.max_price = localMaxPrice.value;
        if (localSort.value !== 'popular') params.sort = localSort.value;

        router.get(route('assets.search'), params, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => { isLoading.value = false; }
        });
    }, 500);
};

// Watcher untuk filter auto-apply — TIDAK termasuk harga (harga trigger via stopDrag/blur)
// Ini mencegah ribuan reactive update saat slider price di-drag
watch([localCategories, localDateStart, localDateEnd, localFacilities, localSort], () => {
    applyFilters();
}, { deep: true });

// Mobile bottom sheet swipe
const sheetTransform = ref('translateY(0)');
let touchStartY = 0;
let touchCurrentY = 0;

const onTouchStart = (e) => {
    touchStartY = e.touches[0].clientY;
    touchCurrentY = e.touches[0].clientY;
};
const onTouchMove = (e) => {
    touchCurrentY = e.touches[0].clientY;
    if (touchCurrentY > touchStartY) {
        sheetTransform.value = `translateY(${touchCurrentY - touchStartY}px)`;
    }
};
const onTouchEnd = () => {
    const deltaY = touchCurrentY - touchStartY;
    if (deltaY > 90) { 
        isMobileFilterOpen.value = false; 
    }
    sheetTransform.value = 'translateY(0)';
    touchStartY = 0;
    touchCurrentY = 0;
};

// Untuk format rupiah di view
const formatIDR = (val) => new Intl.NumberFormat('id-ID').format(val);
</script>

<template>
    <AppLayout>
        <Head :title="searchTitle + ' - KuSewa'" />

        <!-- HEADER PENCARIAN SIMPLE -->
        <div class="bg-white border-b sticky top-0 z-40 shadow-sm mt-16 md:mt-0 pt-4 md:pt-24 pb-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <div class="relative w-full max-w-2xl flex items-center bg-gray-100 rounded-full px-4 py-2 sm:py-3 transition-colors focus-within:bg-white focus-within:ring-2 focus-within:ring-[#FFC000]">
                        <i class="fa-solid fa-magnifying-glass text-gray-500 mr-3"></i>
                        <input 
                            v-model="localKeyword" 
                            @keyup.enter="applyFilters"
                            type="text" 
                            placeholder="Cari nama aset, kota, atau alamat..." 
                            class="bg-transparent w-full border-none p-0 text-sm font-medium focus:ring-0 text-[#0A2540] placeholder-gray-400 outline-none"
                        >
                        <button v-if="localKeyword" @click="localKeyword = ''; applyFilters()" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <!-- Trigger Mobile Filter -->
                    <button @click="isMobileFilterOpen = true" class="lg:hidden h-10 px-4 bg-[#FFC000] text-[#0A2540] rounded-full font-bold text-xs flex items-center gap-2 active:scale-95 transition-transform">
                        <i class="fa-solid fa-sliders"></i>
                        Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- SIDEBAR KIRI (DESKTOP) -->
                <div class="hidden lg:block lg:col-span-3 space-y-6">
                    
                    <!-- Dummy Map -->
                    <div class="bg-[#F8F9FA] rounded-2xl p-4 flex flex-col items-center justify-center text-center border overflow-hidden relative">
                        <!-- Pola peta SVG sederhana sbg background -->
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0ibm9uZSIvPgo8Y2lyY2xlIGN4PSIyIiBjeT0iMiIgcj0iMSIgZmlsbD0iIzBBMjU0MCIvPgo8L3N2Zz4=')]"></div>
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-md text-[#0A2540] mb-3 relative z-10">
                            <i class="fa-solid fa-map-location-dot text-xl"></i>
                        </div>
                        <button class="bg-[#0A2540] text-white px-6 py-2 rounded-full font-bold text-xs shadow hover:bg-[#1a365d] transition relative z-10 w-full">
                            Eksplor di Peta
                        </button>
                    </div>

                    <!-- Rentang Harga -->
                    <div class="bg-white rounded-2xl p-5 border shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-extrabold text-[#0A2540] text-[15px]">Rentang Harga</h3>
                            <button @click="localMinPrice = 0; localMaxPrice = maxLimit; validatePrices()" class="text-xs font-bold text-[#2A75D3] hover:underline">Reset</button>
                        </div>
                        
                        <p class="text-[11px] text-[#6C757D] mb-6">Per kamar, per malam</p>

                        <!-- Custom Range Slider -->
                        <div class="relative h-1.5 bg-gray-200 rounded-full mb-6 mx-2" ref="sliderTrack">
                            <div class="absolute h-full bg-[#FFC000] rounded-full pointer-events-none" :style="{ left: minPercent + '%', width: (maxPercent - minPercent) + '%' }"></div>
                            
                            <!-- Thumb Min -->
                            <div @mousedown.prevent="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')" class="absolute top-1/2 -mt-3 -ml-3 w-6 h-6 bg-white border border-gray-300 rounded-full shadow cursor-grab active:cursor-grabbing flex items-center justify-center z-10" :style="{ left: minPercent + '%' }">
                                <div class="w-2.5 h-2.5 bg-[#FFC000] rounded-full pointer-events-none"></div>
                            </div>
                            <!-- Thumb Max -->
                            <div @mousedown.prevent="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')" class="absolute top-1/2 -mt-3 -ml-3 w-6 h-6 bg-white border border-gray-300 rounded-full shadow cursor-grab active:cursor-grabbing flex items-center justify-center z-20" :style="{ left: maxPercent + '%' }">
                                <div class="w-2.5 h-2.5 bg-[#FFC000] rounded-full pointer-events-none"></div>
                            </div>
                        </div>

                        <!-- Input Harga (IDR) -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 font-medium">IDR</span>
                                <input type="text" :value="formatIDR(localMinPrice)" @input="handleMinInput" class="w-full pl-10 pr-3 py-2 text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-full focus:ring-[#FFC000] focus:border-[#FFC000] transition">
                            </div>
                            <span class="text-gray-400 font-bold">-</span>
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 font-medium">IDR</span>
                                <input type="text" :value="formatIDR(localMaxPrice)" @input="handleMaxInput" class="w-full pl-10 pr-3 py-2 text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-full focus:ring-[#FFC000] focus:border-[#FFC000] transition">
                            </div>
                        </div>
                        <p v-if="priceError" class="text-red-500 text-[10px] mt-2 font-medium">{{ priceError }}</p>
                    </div>

                    <!-- Pilih Jenis Aset (Checkbox DB) -->
                    <div class="bg-white rounded-2xl p-5 border shadow-sm">
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Jenis Aset</h3>
                        <div class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <label v-for="cat in props.categories" :key="cat.id" class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :checked="localCategories.includes(cat.name)" @change="toggleCategory(cat.name)" class="peer sr-only">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:bg-[#0A2540] peer-checked:border-[#0A2540] transition flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-[#6C757D] group-hover:text-[#0A2540] transition">{{ cat.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Filter Fasilitas -->
                    <div class="bg-white rounded-2xl p-5 border shadow-sm">
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Fasilitas Populer</h3>
                        <div class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <label v-for="fac in props.facilities" :key="fac" class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :checked="localFacilities.includes(fac)" @change="toggleFacility(fac)" class="peer sr-only">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:bg-[#0A2540] peer-checked:border-[#0A2540] transition flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-[#6C757D] group-hover:text-[#0A2540] transition">{{ fac }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Jadwal (Checkin Checkout) -->
                    <div class="bg-white rounded-2xl p-5 border shadow-sm">
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Jadwal</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[11px] font-bold text-[#6C757D] block mb-1">Dari Tanggal</label>
                                <input type="date" v-model="localDateStart" class="w-full text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-xl focus:ring-[#FFC000] focus:border-[#FFC000] px-3 py-2">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-[#6C757D] block mb-1">Sampai Tanggal</label>
                                <input type="date" v-model="localDateEnd" class="w-full text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-xl focus:ring-[#FFC000] focus:border-[#FFC000] px-3 py-2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTENT KANAN -->
                <div class="col-span-1 lg:col-span-9">
                    <!-- HEADER HASIL & SORTING -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-[#0A2540]">{{ localLocation || 'Semua Lokasi' }}</h1>
                            <p class="text-xs sm:text-sm text-[#6C757D] mt-0.5">{{ props.assets?.total ?? assetData.length }} properti ditemukan</p>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-[#6C757D] hidden sm:inline">Urutkan:</span>
                            <div class="relative">
                                <select v-model="localSort" class="appearance-none bg-[#F8F9FA] text-[#0A2540] text-xs font-bold py-2.5 pl-4 pr-10 rounded-full border-none focus:ring-2 focus:ring-[#FFC000] cursor-pointer shadow-sm">
                                    <option value="popular">Popularitas Tertinggi</option>
                                    <option value="price_asc">Harga Terendah</option>
                                    <option value="price_desc">Harga Tertinggi</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-[#0A2540] pointer-events-none"></i>
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

        <!-- MOBILE BOTTOM SHEET FILTER -->
        <div v-if="isMobileFilterOpen" class="lg:hidden fixed inset-0 z-[100] flex flex-col justify-end">
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
                @click="isMobileFilterOpen = false"
            ></div>
            
            <!-- Sheet Content -->
            <div 
                class="bg-white w-full rounded-t-3xl shadow-2xl relative flex flex-col max-h-[90vh]"
                :style="{ transform: sheetTransform, transition: touchStartY ? 'none' : 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)' }"
            >
                <!-- Handle Swipe -->
                <div 
                    class="w-full flex justify-center py-3 cursor-grab active:cursor-grabbing"
                    @touchstart="onTouchStart"
                    @touchmove="onTouchMove"
                    @touchend="onTouchEnd"
                >
                    <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- Header Sheet -->
                <div class="px-6 pb-4 border-b flex justify-between items-center shrink-0">
                    <h2 class="text-lg font-extrabold text-[#0A2540]">Filter</h2>
                    <button @click="resetFilters" class="text-xs font-bold text-[#2A75D3]">Reset</button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 overflow-y-auto space-y-8 pb-32">
                    <!-- Rentang Harga -->
                    <div>
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-2">Rentang Harga</h3>
                        <p class="text-[11px] text-[#6C757D] mb-6">Per kamar, per malam</p>
                        
                        <div class="relative h-1.5 bg-gray-200 rounded-full mb-6 mx-2" ref="sliderTrack">
                            <div class="absolute h-full bg-[#FFC000] rounded-full pointer-events-none" :style="{ left: minPercent + '%', width: (maxPercent - minPercent) + '%' }"></div>
                            
                            <!-- Thumb Min -->
                            <div @mousedown.prevent="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')" class="absolute top-1/2 -mt-3 -ml-3 w-6 h-6 bg-white border border-gray-300 rounded-full shadow cursor-grab active:cursor-grabbing flex items-center justify-center z-10" :style="{ left: minPercent + '%' }">
                                <div class="w-2.5 h-2.5 bg-[#FFC000] rounded-full pointer-events-none"></div>
                            </div>
                            <!-- Thumb Max -->
                            <div @mousedown.prevent="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')" class="absolute top-1/2 -mt-3 -ml-3 w-6 h-6 bg-white border border-gray-300 rounded-full shadow cursor-grab active:cursor-grabbing flex items-center justify-center z-20" :style="{ left: maxPercent + '%' }">
                                <div class="w-2.5 h-2.5 bg-[#FFC000] rounded-full pointer-events-none"></div>
                            </div>
                        </div>

                        <!-- Input Harga (IDR) -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 font-medium">IDR</span>
                                <input type="text" :value="formatIDR(localMinPrice)" @input="handleMinInput" class="w-full pl-10 pr-3 py-2 text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000] transition">
                            </div>
                            <span class="text-gray-400 font-bold">-</span>
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 font-medium">IDR</span>
                                <input type="text" :value="formatIDR(localMaxPrice)" @input="handleMaxInput" class="w-full pl-10 pr-3 py-2 text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000] transition">
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Aset -->
                    <div>
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Jenis Aset</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="cat in props.categories" :key="cat.id" class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :checked="localCategories.includes(cat.name)" @change="toggleCategory(cat.name)" class="peer sr-only">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:bg-[#0A2540] peer-checked:border-[#0A2540] transition flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-[#6C757D]">{{ cat.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Fasilitas Populer -->
                    <div>
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Fasilitas Populer</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="fac in props.facilities" :key="fac" class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :checked="localFacilities.includes(fac)" @change="toggleFacility(fac)" class="peer sr-only">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:bg-[#0A2540] peer-checked:border-[#0A2540] transition flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-[#6C757D]">{{ fac }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div>
                        <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Jadwal</h3>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="text-[11px] font-bold text-[#6C757D] block mb-1">Dari Tanggal</label>
                                <input type="date" v-model="localDateStart" class="w-full text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000] px-3 py-2">
                            </div>
                            <div class="flex-1">
                                <label class="text-[11px] font-bold text-[#6C757D] block mb-1">Sampai Tanggal</label>
                                <input type="date" v-model="localDateEnd" class="w-full text-xs font-bold text-[#0A2540] bg-gray-50 border border-gray-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000] px-3 py-2">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer button sheet -->
                <div class="absolute bottom-0 left-0 w-full p-4 bg-white border-t z-10">
                    <button @click="isMobileFilterOpen = false" class="w-full py-3.5 bg-[#FFC000] text-[#0A2540] rounded-full font-extrabold text-sm shadow active:scale-[0.98] transition-transform">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
        
    </AppLayout>
</template>
