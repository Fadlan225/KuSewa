<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Search, Check, ChevronDown } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import CircularMonthSlider from '@/Components/UI/CircularMonthSlider.vue';

const page = usePage();

// We'll use filteredAssetCategories from useHomeSearch instead


const {
    isMobileSearchOpen,
    activeSearchStep,
    steps,
    currentStepIndex,
    isLastStep,
    nextStep,
    prevStep,
    clearCurrentOrAll,
    performSearch,

    // Aset
    assetSearchQuery,
    selectedAssets,
    toggleAsset,
    filteredAssetCategories,

    // Lokasi
    searchQuery,
    filteredLocations,
    setLocationSuggestions,
    openLokasiFullScreen,

    // Jadwal
    startDate,
    endDate,
    startTime,
    endTime,
    durationMonths,
    activeScheduleMode,
    simpleDateString,
    selectDate,
    isStartDate,
    isEndDate,
    isInRange,
    isPastDate,
    isDateDisabled,
    monthsData,
    daysOfWeek,
    loadMoreMonths,

    // Harga
    minPrice,
    maxPrice,
    parsedMinPrice,
    parsedMaxPrice,
    formattedMinPrice,
    formattedMaxPrice,
    handleMinPriceInput,
    handleMaxPriceInput,
    maxLimit,
    minPercent,
    maxPercent,
    priceError,
    activeThumb,
    startDrag,
    sliderTrack,
    validatePrices,
    formatPriceShort,
    isLokasiFullScreen,
    closeLokasiFullScreen,
    priceDistribution,
    handleBucketClick,

    // Fasilitas
    selectedFacilities,
    toggleFacility
} = useHomeSearch();

const maxDistributionCount = computed(() => {
    if (!priceDistribution.value || priceDistribution.value.length === 0) return 1;
    return Math.max(...priceDistribution.value) || 1;
});

const isBucketActive = (idx) => {
    const bucketMin = (idx / 30) * 100;
    const bucketMax = ((idx + 1) / 30) * 100;
    return bucketMax >= minPercent.value && bucketMin <= maxPercent.value;
};

// State untuk pencarian dan collapse fasilitas di mobile
const facilitySearchMobile = ref('');
const openFacilityCategoriesMobile = ref({});

const toggleFacilityCategoryMobile = (catName) => {
    openFacilityCategoriesMobile.value[catName] = !openFacilityCategoriesMobile.value[catName];
};

// Fasilitas dinamis berdasarkan tipe yang dipilih, digrup per kategori
const groupedFacilitiesForMobile = computed(() => {
    const byType = page.props.facilitiesByType || [];
    if (byType.length === 0) return [];

    let flat = [];
    const seen = new Set();
    
    if (selectedAssets.value.length === 0) {
        byType.forEach(group => {
            group.facilities.forEach(f => {
                if (!seen.has(f.id)) { seen.add(f.id); flat.push(f); }
            });
        });
    } else {
        byType
            .filter(group => selectedAssets.value.includes(group.type_name))
            .forEach(group => {
                group.facilities.forEach(f => {
                    if (!seen.has(f.id)) { seen.add(f.id); flat.push(f); }
                });
            });
    }

    if (facilitySearchMobile.value.trim() !== '') {
        const query = facilitySearchMobile.value.toLowerCase();
        flat = flat.filter(f => f.name.toLowerCase().includes(query));
    }

    const groups = {};
    flat.forEach(f => {
        const cat = f.category_name || 'Lainnya';
        if (!groups[cat]) {
            groups[cat] = [];
            if (openFacilityCategoriesMobile.value[cat] === undefined) {
                openFacilityCategoriesMobile.value[cat] = true;
            }
        }
        groups[cat].push(f);
    });

    if (facilitySearchMobile.value.trim() !== '') {
        Object.keys(groups).forEach(cat => openFacilityCategoriesMobile.value[cat] = true);
    }

    return Object.keys(groups).map(catName => ({
        name: catName,
        facilities: groups[catName]
    })).sort((a, b) => a.name.localeCompare(b.name));
});

// Filter categories are now handled in useHomeSearch


// Inisialisasi lokasi dari DB props
onMounted(() => {
    setLocationSuggestions(page.props.locationSuggestions || []);
});
watch(() => page.props.locationSuggestions, (val) => {
    setLocationSuggestions(val || []);
});

const handleApply = () => {
    isMobileSearchOpen.value = false;
    performSearch();
};

import BottomSheet from '@/Components/UI/BottomSheet.vue';
</script>

<template>
    <BottomSheet v-model="isMobileSearchOpen" title="" height-class="h-[85vh]">
        <!-- Kita tidak menggunakan judul bawaan BottomSheet karena ada tabs di header -->
        <template #tabs>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-extrabold text-[#0A2540] capitalize">{{ activeSearchStep }}</h2>
            </div>
            <!-- Tabs -->
                        <div class="flex items-center gap-6 overflow-x-auto hide-scrollbar">
                            <button v-for="step in steps" :key="step"
                                    @click="activeSearchStep = step"
                                    class="pb-2.5 text-sm font-bold capitalize transition-colors whitespace-nowrap relative"
                                    :class="activeSearchStep === step ? 'text-[#0A2540]' : 'text-[#6C757D]'">
                                {{ step }}
                                <div v-if="activeSearchStep === step" class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#0A2540] rounded-t-md"></div>
                            </button>
            </div>
        </template>

        <!-- Slider Content Area -->
        <div class="flex w-full h-full transition-transform duration-300 ease-in-out" :style="`transform: translateX(-${currentStepIndex * 100}%)`">

                            <!-- 1. JENIS ASET -->
                            <div class="w-full h-full flex-shrink-0 px-4 overflow-y-auto pb-24 hide-scrollbar">
                                <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#6C757D]/5 relative">
                                    <button @click="selectedAssets = []" class="absolute top-5 right-5 text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2 z-10">Reset</button>

                                    <!-- Search Aset -->
                                    <div class="flex items-center gap-3 border border-[#6C757D]/20 rounded-xl p-3 bg-white mb-4">
                                        <Search class="text-[#6C757D] pl-1 text-sm" />
                                        <input
                                            v-model="assetSearchQuery"
                                            type="text"
                                            placeholder="Cari jenis aset..."
                                            class="w-full bg-transparent outline-none text-[#0A2540] placeholder-[#6C757D] text-sm"
                                        >
                                    </div>

                                    <!-- Opsi Semua -->
                                        <div v-if="!assetSearchQuery">
                                            <div class="space-y-2">
                                                <label class="flex items-center gap-3 cursor-pointer group p-1 border border-[#6C757D]/20 rounded-xl px-4 py-3 bg-[#F8F9FA]">
                                                    <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.length === 0}">
                                                        <Check v-if="selectedAssets.length === 0" class="text-white text-[10px]" />
                                                    </div>
                                                    <span class="text-sm font-bold text-[#0A2540]">Semua</span>
                                                    <input type="checkbox" :checked="selectedAssets.length === 0" @change="selectedAssets = []" class="hidden">
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Kategori & Tipe Aset (Grup) -->
                                        <div v-for="cat in filteredAssetCategories" :key="cat.name" class="mb-4">
                                            <h3 class="text-xs font-bold text-[#6C757D] mb-2 flex items-center gap-1.5">
                                                <AppIcon :iconClass="cat.icon + ' text-[#FFC000] text-xs'" v-if="cat.icon" />
                                                {{ cat.name }}
                                            </h3>
                                            <div class="space-y-2">
                                                <label v-for="item in cat.items" :key="item" class="flex items-center gap-3 cursor-pointer group p-1 border border-[#6C757D]/20 rounded-xl px-4 py-3 bg-[#F8F9FA]">
                                                    <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.includes(item)}">
                                                        <Check v-if="selectedAssets.includes(item)" class="text-white text-[10px]" />
                                                    </div>
                                                    <span class="text-sm font-medium text-[#0A2540]">{{ item }}</span>
                                                    <input type="checkbox" :value="item" class="hidden" @change="toggleAsset(item)">
                                                </label>
                                            </div>
                                        </div>
                                        <div v-if="filteredAssetCategories.length === 0" class="text-center py-4 text-[#6C757D] font-medium text-sm">
                                            Tidak ada hasil.
                                        </div>
                                </div>
                            </div>

                            <!-- 2. LOKASI -->
                            <div class="w-full h-full flex-shrink-0 px-4 overflow-y-auto pb-24 hide-scrollbar">
                                <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#6C757D]/5">
                                    <div
                                        @click="openLokasiFullScreen"
                                        class="flex items-center gap-3 border border-[#6C757D]/30 rounded-2xl p-3 bg-white cursor-pointer hover:bg-gray-50 transition mb-4"
                                    >
                                        <Search class="text-[#0A2540] pl-1" />
                                        <div class="text-[#0A2540] font-medium text-[15px] flex-1 truncate">
                                            {{ searchQuery || 'Cari destinasi atau aset' }}
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-3 mt-1">
                                        <h3 class="text-xs font-bold text-[#6C757D]">Destinasi yang disarankan</h3>
                                        <button @click="searchQuery = ''" class="text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2">Reset</button>
                                    </div>
                                    <div class="space-y-3">
                                        <div
                                            v-for="item in filteredLocations.slice(0, 4)"
                                            :key="item.id"
                                            @click="searchQuery = item.title; nextStep()"
                                            class="flex gap-4 items-center cursor-pointer active:scale-95 transition"
                                        >
                                            <div :class="`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                                <AppIcon :iconClass="`${item.icon} ${item.iconColor} text-base`" />
                                            </div>
                                            <div class="border-b border-[#6C757D]/10 pb-2 w-full">
                                                <h4 class="font-bold text-[#0A2540] text-sm">{{ item.title }}</h4>
                                                <p class="text-[11px] text-[#6C757D] mt-0.5 truncate max-w-[200px]">{{ item.desc }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. JADWAL -->
                            <div class="w-full h-full flex-shrink-0 px-4 overflow-y-auto pb-24 hide-scrollbar">
                                <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#6C757D]/5 relative">
                                    <button @click="startDate = null; endDate = null" class="absolute top-5 right-5 text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2 z-10">Reset</button>

                                    <div v-if="['day', 'night'].includes(activeScheduleMode)">
                                        <div v-for="month in monthsData" :key="month.id" class="mb-6">
                                            <h3 class="text-[15px] font-bold text-[#0A2540] mb-4 text-center">{{ month.title }}</h3>
                                            <div class="grid grid-cols-7 gap-y-5 mb-2">
                                                <div v-for="day in daysOfWeek" :key="day" class="text-center text-[11px] font-bold text-[#6C757D]">
                                                    {{ day }}
                                                </div>
                                                <template v-for="(week, wIdx) in month.weeks" :key="'w-'+wIdx">
                                                    <div v-for="(date, dIdx) in week" :key="'d-'+wIdx+'-'+dIdx" class="relative flex justify-center items-center h-9">
                                                        <template v-if="date">
                                                            <div v-if="isStartDate(month.year, month.month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                            <div v-else-if="isInRange(month.year, month.month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                            <div v-else-if="isEndDate(month.year, month.month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                            <div
                                                                class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                                :class="[
                                                                    (isDateDisabled(month.year, month.month, date)) ? 'text-gray-300 cursor-not-allowed line-through' : 'hover:bg-gray-100',
                                                                    { 
                                                                        'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDate(month.year, month.month, date) || isEndDate(month.year, month.month, date),
                                                                        'text-[#1A1A1A]': isInRange(month.year, month.month, date),
                                                                        'text-[#0A2540]': !isStartDate(month.year, month.month, date) && !isEndDate(month.year, month.month, date) && !isInRange(month.year, month.month, date) && !isDateDisabled(month.year, month.month, date)
                                                                    }
                                                                ]"
                                                                @click="!isDateDisabled(month.year, month.month, date) && selectDate(month.year, month.month, date)"
                                                            >
                                                                <span>{{ date }}</span>
                                                            </div>
                                                            <div v-if="isStartDate(month.year, month.month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                            <div v-else-if="isEndDate(month.year, month.month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <button @click="loadMoreMonths" class="w-full py-3 mt-2 bg-gray-100 rounded-xl text-center text-sm font-bold text-[#0A2540] active:bg-gray-200 transition">
                                            Muat lebih banyak
                                        </button>
                                    </div>

                                    <!-- UI KHUSUS HOUR (Jam) -->
                                    <div v-if="activeScheduleMode === 'hour'" class="pt-2">
                                        <div class="mb-4">
                                            <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Tanggal Sewa</label>
                                            <input type="date" v-model="simpleDateString" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                                        </div>

                                        <h4 class="text-sm font-bold text-[#0A2540] mb-3">Tentukan Waktu (Jam)</h4>
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Mulai</label>
                                                <input v-model="startTime" type="time" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Selesai</label>
                                                <input v-model="endTime" type="time" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- UI KHUSUS MONTH (Bulan) -->
                                    <div v-if="activeScheduleMode === 'month'" class="pt-2 px-2 max-w-md mx-auto w-full">
                                        <div class="mb-4">
                                            <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Mulai Dari Tanggal</label>
                                            <input type="date" v-model="simpleDateString" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-xs font-bold text-[#6C757D] mb-2 text-center">Durasi Sewa (Bulan)</label>
                                            <CircularMonthSlider v-model="durationMonths" />
                                        </div>

                                        <div v-if="endDate" class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                                            <span class="text-[11px] text-[#6C757D] font-bold">Tgl. Selesai Otomatis:</span>
                                            <span class="text-[11px] font-bold text-[#0A2540]">
                                                {{ endDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. RENTANG HARGA -->
                            <div class="w-full h-full flex-shrink-0 px-4 overflow-y-auto pb-24 hide-scrollbar">
                                <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#6C757D]/5 relative">
                                    <button @click="minPrice = 0; maxPrice = 10000000; validatePrices()" class="absolute top-5 right-5 text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2 z-10">Reset</button>
                                    <p class="text-xs text-[#6C757D] mb-6 w-4/5">Sesuaikan batas anggaran yang Anda inginkan.</p>

                                    <!-- Input Harga -->
                                    <div class="flex gap-3 mb-1">
                                        <div class="flex-1 border rounded-xl p-3 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                            <label class="block text-[11px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Min Harga</label>
                                            <div class="flex items-center text-[#0A2540] font-bold text-sm">
                                                <span class="mr-1">Rp</span>
                                                <input :value="formattedMinPrice" @input="handleMinPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                            </div>
                                        </div>
                                        <div class="flex-1 border rounded-xl p-3 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                            <label class="block text-[11px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Maks Harga</label>
                                            <div class="flex items-center text-[#0A2540] font-bold text-sm">
                                                <span class="mr-1">Rp</span>
                                                <input :value="formattedMaxPrice" @input="handleMaxPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="priceError" class="text-[11px] font-bold text-red-500 mt-1 mb-5">{{ priceError }}</p>
                                    
                                    <!-- Histogram -->
                                    <div v-else class="h-12 w-full flex items-end justify-between px-2 gap-[1px] mb-2 mt-4">
                                        <div v-for="(count, idx) in priceDistribution" :key="idx"
                                            @click="handleBucketClick(idx)"
                                            class="flex-1 rounded-t-[2px] transition-all duration-300 min-h-[2px] cursor-pointer hover:bg-opacity-80"
                                            :style="{ height: `${Math.max((count / maxDistributionCount) * 100, 2)}%` }"
                                            :class="isBucketActive(idx) ? 'bg-[#FFC000]' : 'bg-[#E2E8F0]'">
                                        </div>
                                    </div>

                                    <!-- Slider -->
                                    <div class="mb-6 mt-2 relative h-1.5 mx-2" ref="sliderTrack">
                                        <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                                        <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>

                                        <!-- Min Thumb & Tooltip -->
                                        <div @mousedown.prevent="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')"
                                            class="absolute top-1/2 -translate-y-1/2 w-6 h-6 bg-white border-[3px] border-[#0A2540] rounded-full shadow-md z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                            :style="`left: calc(${minPercent}% - 12px)`">
                                            <div v-show="activeThumb === 'min'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px] select-none">
                                                {{ parsedMinPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMinPrice) }}
                                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                            </div>
                                        </div>

                                        <!-- Max Thumb & Tooltip -->
                                        <div @mousedown.prevent="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')"
                                            class="absolute top-1/2 -translate-y-1/2 w-6 h-6 bg-white border-[3px] border-[#0A2540] rounded-full shadow-md z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                            :style="`left: calc(${maxPercent}% - 12px)`">
                                            <div v-show="activeThumb === 'max'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px] select-none">
                                                {{ parsedMaxPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMaxPrice) }}
                                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-4 mx-2 text-[11px] font-bold text-[#6C757D]">
                                        <span>{{ 'Rp' + formatPriceShort(0) }}</span>
                                        <span>{{ 'Rp' + formatPriceShort(maxLimit) + ' +' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. FASILITAS -->
                            <div v-if="steps.includes('fasilitas')" class="w-full h-full flex-shrink-0 px-4 overflow-y-auto pb-24 hide-scrollbar">
                                <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#6C757D]/5 relative">
                                    <button @click="selectedFacilities = []" class="absolute top-5 right-5 text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2 z-10">Reset</button>
                                    <h3 class="font-extrabold text-[#0A2540] text-[15px] mb-4">Fasilitas Populer</h3>
                                    
                                    <!-- Search input for facilities -->
                                    <div class="mb-4 relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <Search class="text-gray-400 text-xs" />
                                        </div>
                                        <input type="text" v-model="facilitySearchMobile" placeholder="Cari fasilitas..." class="w-full text-sm border-gray-200 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl pl-9 py-2.5 bg-slate-50 transition" />
                                    </div>

                                    <!-- Grouped Facilities (Accordion) -->
                                    <p v-if="groupedFacilitiesForMobile.length === 0" class="text-sm text-gray-400 text-center py-6">
                                        Tidak ada fasilitas tersedia
                                    </p>
                                    
                                    <div v-else class="space-y-3 pb-8">
                                        <div v-for="group in groupedFacilitiesForMobile" :key="group.name" class="bg-white rounded-xl overflow-hidden border border-slate-100 shadow-sm">
                                            <!-- Group Header -->
                                            <button @click="toggleFacilityCategoryMobile(group.name)" class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 hover:bg-slate-50 transition active:bg-slate-100">
                                                <span class="text-[15px] font-bold text-[#0A2540]">{{ group.name }}</span>
                                                <div class="w-7 h-7 rounded-full bg-white shadow-sm flex items-center justify-center">
                                                    <ChevronDown class="text-xs text-slate-400 transition-transform" :class="{'rotate-180': openFacilityCategoriesMobile[group.name]}" />
                                                </div>
                                            </button>
                                            
                                            <!-- Group Content (Checkboxes) -->
                                            <div v-show="openFacilityCategoriesMobile[group.name]" class="px-4 pb-4 pt-2 space-y-2">
                                                <label
                                                    v-for="fac in group.facilities"
                                                    :key="fac.id"
                                                    class="flex items-center gap-3.5 cursor-pointer group py-2 rounded-lg transition"
                                                >
                                                    <div class="relative flex items-center shrink-0">
                                                        <input
                                                            type="checkbox"
                                                            :checked="selectedFacilities.includes(fac.id)"
                                                            @change="toggleFacility(fac.id)"
                                                            class="peer sr-only"
                                                        >
                                                        <div class="w-6 h-6 rounded-md border-2 border-gray-300 bg-white peer-checked:bg-[#FFC000] peer-checked:border-[#FFC000] transition flex items-center justify-center">
                                                            <Check class="text-white text-xs opacity-0 peer-checked:opacity-100" />
                                                        </div>
                                                    </div>
                                                    <span class="text-[15px] font-medium text-[#495057] group-hover:text-[#0A2540] transition leading-tight">
                                                        {{ fac.name }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

        <template #footer>
            <!-- Footer Action Bar -->
            <div class="absolute bottom-0 w-full bg-[#F8F9FA] border-t border-[#6C757D]/10 p-4 flex justify-center items-center z-20">
                <button @click="handleApply" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold w-full py-3.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-[15px]">
                    <Search class="" />
                    Terapkan Filter
                </button>
            </div>
        </template>
    </BottomSheet>
</template>
