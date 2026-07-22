<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue';
import { useHomeSearch } from '@/Composables/useHomeSearch';

const page = usePage();

// Kategori dari database (via props dari HomeController)
const assetCategoriesFromDB = computed(() => page.props.categories || []);

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

    // Lokasi
    searchQuery,
    filteredLocations,
    setLocationSuggestions,
    openLokasiFullScreen,

    // Jadwal
    startDate,
    endDate,
    selectDate,
    isStartDate,
    isEndDate,
    isInRange,
    monthsData,
    daysOfWeek,
    loadMoreMonths,

    // Harga
    minPrice,
    maxPrice,
    validatePrices,
    priceError,
    formattedMinPrice,
    formattedMaxPrice,
    handleMinPriceInput,
    handleMaxPriceInput,
    sliderTrack,
    minPercent,
    maxPercent,
    startDrag,
    activeThumb,
    parsedMinPrice,
    parsedMaxPrice,
    maxLimit,
    formatPriceShort
} = useHomeSearch();

// Filter kategori berdasarkan pencarian (from DB)
const filteredCategoriesFromDB = computed(() => {
    if (!assetSearchQuery.value) return assetCategoriesFromDB.value;
    const q = assetSearchQuery.value.toLowerCase();
    return assetCategoriesFromDB.value.filter(cat =>
        cat.name.toLowerCase().includes(q)
    );
});

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
    <BottomSheet v-model="isMobileSearchOpen" title="">
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
                                        <i class="fa-solid fa-magnifying-glass text-[#6C757D] pl-1 text-sm"></i>
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
                                                        <i v-if="selectedAssets.length === 0" class="fa-solid fa-check text-white text-[10px]"></i>
                                                    </div>
                                                    <span class="text-sm font-bold text-[#0A2540]">Semua</span>
                                                    <input type="checkbox" :checked="selectedAssets.length === 0" @change="selectedAssets = []" class="hidden">
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Kategori dari DB -->
                                        <div v-for="cat in filteredCategoriesFromDB" :key="cat.id">
                                            <h3 class="text-xs font-bold text-[#6C757D] mb-2">{{ cat.name }}</h3>
                                            <div class="space-y-2">
                                                <label class="flex items-center gap-3 cursor-pointer group p-1 border border-[#6C757D]/20 rounded-xl px-4 py-3 bg-[#F8F9FA]">
                                                    <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.includes(cat.name)}">
                                                        <i v-if="selectedAssets.includes(cat.name)" class="fa-solid fa-check text-white text-[10px]"></i>
                                                    </div>
                                                    <i v-if="cat.icon" :class="cat.icon + ' text-[#FFC000] text-sm'"></i>
                                                    <span class="text-sm font-medium text-[#0A2540]">{{ cat.name }}</span>
                                                    <input type="checkbox" :value="cat.name" class="hidden" @change="toggleAsset(cat.name)">
                                                </label>
                                            </div>
                                        </div>
                                        <div v-if="filteredCategoriesFromDB.length === 0" class="text-center py-4 text-[#6C757D] font-medium text-sm">
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
                                        <i class="fa-solid fa-magnifying-glass text-[#0A2540] pl-1"></i>
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
                                                <i :class="`${item.icon} ${item.iconColor} text-base`"></i>
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

                                    <div v-for="month in monthsData" :key="month.id" class="mb-6">
                                        <h3 class="text-[15px] font-bold text-[#0A2540] mb-4 text-center">{{ month.title }}</h3>
                                        <div class="grid grid-cols-7 gap-y-5 mb-2">
                                            <div v-for="day in daysOfWeek" :key="day" class="text-center text-[11px] font-bold text-[#6C757D]">
                                                {{ day }}
                                            </div>
                                            <div v-for="i in month.emptyDaysStart" :key="'empty-'+i"></div>
                                            <div v-for="date in month.daysInMonth" :key="date" class="relative flex justify-center items-center h-9" @click="selectDate(month.year, month.month, date)">
                                                <div v-if="isStartDate(month.year, month.month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(month.year, month.month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(month.year, month.month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <div
                                                    class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                    :class="{
                                                        'bg-[#1A1A1A] text-white shadow-md': isStartDate(month.year, month.month, date) || isEndDate(month.year, month.month, date),
                                                        'text-[#1A1A1A]': isInRange(month.year, month.month, date),
                                                        'text-[#0A2540]': !isStartDate(month.year, month.month, date) && !isEndDate(month.year, month.month, date) && !isInRange(month.year, month.month, date)
                                                    }"
                                                >
                                                    <span>{{ date }}</span>
                                                </div>
                                                <div v-if="isStartDate(month.year, month.month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(month.year, month.month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="loadMoreMonths" class="w-full py-3 mt-2 bg-gray-100 rounded-xl text-center text-sm font-bold text-[#0A2540] active:bg-gray-200 transition">
                                        Muat lebih banyak
                                    </button>
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
                                    <div v-else class="mb-5 mt-1 h-[16px]"></div>

                                    <!-- Slider -->
                                    <div class="mb-6 mt-12 relative h-1.5 mx-2" ref="sliderTrack">
                                        <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                                        <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>

                                        <!-- Min Thumb -->
                                        <div
                                            @mousedown="startDrag($event, 'min')"
                                            @touchstart.prevent="startDrag($event, 'min')"
                                            class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-md z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                            :style="`left: calc(${minPercent}% - 10px)`">
                                            <div v-show="activeThumb === 'min'" class="absolute -top-[42px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px]">
                                                {{ parsedMinPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMinPrice) }}
                                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                            </div>
                                        </div>

                                        <!-- Max Thumb -->
                                        <div
                                            @mousedown="startDrag($event, 'max')"
                                            @touchstart.prevent="startDrag($event, 'max')"
                                            class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-md z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                            :style="`left: calc(${maxPercent}% - 10px)`">
                                            <div v-show="activeThumb === 'max'" class="absolute -top-[42px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px]">
                                                {{ parsedMaxPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMaxPrice) }}
                                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-3 mx-2 text-[10px] font-bold text-[#6C757D]">
                                        <span>Rp0</span>
                                        <span>>Rp10 jt</span>
                                    </div>
                                </div>
                            </div>

                        </div>
        
        <template #footer>
            <!-- Footer Action Bar -->
            <div class="absolute bottom-0 w-full bg-[#F8F9FA] border-t border-[#6C757D]/10 p-4 flex justify-center items-center z-20">
                <button @click="handleApply" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold w-full py-3.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-[15px]">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Terapkan Filter
                </button>
            </div>
        </template>
    </BottomSheet>
</template>
