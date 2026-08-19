<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { ChevronDown, Search, Locate, Check, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import CircularMonthSlider from '@/Components/UI/CircularMonthSlider.vue';
import AnimatedPlaceholder from '@/Components/UI/AnimatedPlaceholder.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const {
    selectedAssets,
    searchQuery,
    formattedSchedule,
    parsedMinPrice,
    parsedMaxPrice,
    maxLimit,
    formatPriceShort,
    assetSearchQuery,
    filteredAssetCategories,
    toggleAsset,
    filteredLocations,
    desktopCalendarPage,
    prevDesktopMonth,
    nextDesktopMonth,
    monthsData,
    daysOfWeek,
    selectDate,
    isStartDate,
    isEndDate,
    isInRange,
    isPastDate,
    isDateDisabled,
    endDate,
    formattedMinPrice,
    formattedMaxPrice,
    handleMinPriceInput,
    handleMaxPriceInput,
    priceError,
    minPercent,
    maxPercent,
    startDrag,
    sliderTrack,
    activeThumb,
    performSearch,
    startTime,
    endTime,
    durationMonths,
    activeScheduleMode,
    simpleDateString,
    priceDistribution,
    handleBucketClick,
    initUserLocation,
} = useHomeSearch();

const desktopActiveMenu = ref(null);

const maxDistributionCount = computed(() => {
    if (!priceDistribution.value || priceDistribution.value.length === 0) return 1;
    return Math.max(...priceDistribution.value) || 1;
});

const isBucketActive = (idx) => {
    const bucketMin = (idx / 30) * 100;
    const bucketMax = ((idx + 1) / 30) * 100;
    return bucketMax >= minPercent.value && bucketMin <= maxPercent.value;
};

let lastScrollY = typeof window !== 'undefined' ? window.scrollY : 0;

const handleScroll = () => {
    if (desktopActiveMenu.value !== null) {
        if (Math.abs(window.scrollY - lastScrollY) > 50) {
            desktopActiveMenu.value = null;
        }
    } else {
        lastScrollY = window.scrollY;
    }
};

const handlePerformSearch = () => {
    desktopActiveMenu.value = null;
    performSearch();
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    if (typeof initUserLocation === 'function') {
        initUserLocation();
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="hidden md:flex flex-col w-full max-w-[850px] relative z-[70]">
        <!-- Overlay untuk menutup modal jika di klik di luar -->
        <div v-if="desktopActiveMenu" @click="desktopActiveMenu = null" class="fixed inset-0 z-40 bg-black/5 transition-opacity"></div>

        <!-- Container untuk Card Utama & Tombol Search (Desain Kapsul) -->
        <div class="bg-white rounded-full p-1.5 shadow-lg border border-gray-200/80 flex flex-row items-center justify-between w-full relative z-50 transition-all duration-300">
            <!-- Inner Items Container -->
            <div class="flex flex-row items-center justify-between flex-1">

                <!-- Item 1: Jenis Aset -->
                <div @click="desktopActiveMenu = desktopActiveMenu === 'jenis' ? null : 'jenis'" class="flex-1 flex items-center justify-between px-5 py-2 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-300" :class="desktopActiveMenu === 'jenis' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-[#0A2540] tracking-wide group-hover:text-[#FFC000] transition-colors">Jenis Aset</span>
                        <span class="text-[13px] text-[#6C757D] truncate max-w-[150px]" :class="selectedAssets.length > 0 ? 'text-[#0A2540] font-bold' : ''">
                            {{ selectedAssets.length > 0 ? selectedAssets.join(', ') : 'Pilih jenis aset' }}
                        </span>
                    </div>
                    <ChevronDown class="text-[#6C757D]/70 text-[10px] transition-all duration-300 group-hover:text-[#0A2540] group-hover:translate-y-1" :class="desktopActiveMenu === 'jenis' ? 'rotate-180 text-[#0A2540]' : ''" />
                </div>

                <!-- Divider -->
                <div class="h-8 w-px bg-gray-200 mx-1"></div>

                <!-- Item 2: Lokasi -->
                <div @click="desktopActiveMenu = desktopActiveMenu === 'lokasi' ? null : 'lokasi'" class="flex-1 flex items-center justify-between px-5 py-2 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-300" :class="desktopActiveMenu === 'lokasi' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-[#0A2540] tracking-wide relative z-10 group-hover:text-[#FFC000] transition-colors">Lokasi</span>
                        <span v-if="searchQuery" class="text-[13px] text-[#0A2540] font-bold truncate max-w-[150px] relative z-10 mt-0.5">
                            {{ searchQuery }}
                        </span>
                        <span v-else class="text-[13px] text-[#6C757D] truncate max-w-[150px] relative z-10 mt-0.5">
                            Cari destinasi...
                        </span>
                    </div>
                    <ChevronDown class="text-[#6C757D]/70 text-[10px] transition-all duration-300 group-hover:text-[#0A2540] group-hover:translate-y-1" :class="desktopActiveMenu === 'lokasi' ? 'rotate-180 text-[#0A2540]' : ''" />
                </div>

                <!-- Divider -->
                <div class="h-8 w-px bg-gray-200 mx-1"></div>

                <!-- Item 3: Jadwal -->
                <div @click="desktopActiveMenu = desktopActiveMenu === 'jadwal' ? null : 'jadwal'" class="flex-1 flex items-center justify-between px-5 py-2 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-300" :class="desktopActiveMenu === 'jadwal' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-[#0A2540] tracking-wide group-hover:text-[#FFC000] transition-colors">Jadwal</span>
                        <span class="text-[13px] text-[#6C757D] truncate max-w-[150px]" :class="formattedSchedule !== 'Pilih Tanggal' && formattedSchedule ? 'text-[#0A2540] font-bold' : ''">
                            {{ formattedSchedule || 'Tentukan tanggal' }}
                        </span>
                    </div>
                    <ChevronDown class="text-[#6C757D]/70 text-[10px] transition-all duration-300 group-hover:text-[#0A2540] group-hover:translate-y-1" :class="desktopActiveMenu === 'jadwal' ? 'rotate-180 text-[#0A2540]' : ''" />
                </div>

                <!-- Divider -->
                <div class="h-8 w-px bg-gray-200 mx-1"></div>

                <!-- Item 4: Rentang Harga -->
                <div @click="desktopActiveMenu = desktopActiveMenu === 'harga' ? null : 'harga'" class="flex-1 flex items-center justify-between px-5 py-2 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-300" :class="desktopActiveMenu === 'harga' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-[#0A2540] tracking-wide group-hover:text-[#FFC000] transition-colors">Harga</span>
                        <span class="text-[13px] text-[#6C757D] truncate max-w-[150px]" :class="parsedMinPrice > 0 || parsedMaxPrice < maxLimit ? 'text-[#0A2540] font-bold' : ''">
                            {{ parsedMinPrice > 0 || parsedMaxPrice < maxLimit ? 'Rp ' + formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice) : 'Batas budget' }}
                        </span>
                    </div>
                    <ChevronDown class="text-[#6C757D]/70 text-[10px] transition-all duration-300 group-hover:text-[#0A2540] group-hover:translate-y-1" :class="desktopActiveMenu === 'harga' ? 'rotate-180 text-[#0A2540]' : ''" />
                </div>

            </div>

            <!-- Tombol Search -->
            <button @click="handlePerformSearch" class="bg-[#FFC000] hover:bg-primary/90 text-[#0A2540] w-12 h-12 rounded-full flex items-center justify-center transition-transform duration-200 shadow-md hover:shadow-lg flex-shrink-0 ml-2 active:scale-95 cursor-pointer group">
                <Search class="text-lg transition-transform duration-300 group-hover:scale-110" />
            </button>
        </div>

        <!-- Overlay for closing the modal on outside click -->
        <div v-if="desktopActiveMenu" @click="desktopActiveMenu = null" class="fixed inset-0 z-40 cursor-default"></div>

        <!-- DESKTOP MODAL DROPDOWN -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-4"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-4"
        >
            <div v-if="desktopActiveMenu"
                 class="absolute top-[108%] bg-white rounded-2xl shadow-2xl border border-[#6C757D]/10 p-6 z-[70] flex flex-col max-h-[75vh] overflow-y-auto hide-scrollbar overscroll-contain transition-all"
                 :class="{
                    'w-[380px] left-0 origin-top-left': desktopActiveMenu === 'jenis',
                    'w-[380px] left-[25%] origin-top': desktopActiveMenu === 'lokasi',
                    'w-[700px] left-1/2 -translate-x-1/2 origin-top': desktopActiveMenu === 'jadwal',
                    'w-[380px] right-0 origin-top-right': desktopActiveMenu === 'harga'
                 }"
            >

                <!-- ================== DESKTOP: LOKASI ================== -->
                <div v-if="desktopActiveMenu === 'lokasi'" class="w-full max-w-sm mx-auto">
                    <h2 class="text-lg font-extrabold text-[#0A2540] mb-3">Pencarian Lokasi</h2>
                    <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                        <Search class="text-[#0A2540] pl-1 text-sm" />
                        <input v-model="searchQuery" type="text" placeholder="Cari destinasi..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                    </div>

                    <h3 class="text-[11px] font-bold text-[#6C757D] mb-3 uppercase tracking-wider">Disarankan</h3>
                    <div class="space-y-2 max-h-[200px] overflow-y-auto pr-2">
                        <!-- Gunakan Lokasi Saat Ini -->
                        <div @click="initUserLocation(true); desktopActiveMenu = 'jadwal'" class="flex gap-3 items-center cursor-pointer group hover:bg-blue-50 p-2 -mx-2 rounded-xl transition border border-transparent hover:border-blue-100">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-100 text-blue-600">
                                <Locate class="text-sm" />
                            </div>
                            <div class="border-b border-[#6C757D]/10 pb-2 pt-1 w-full group-last:border-0">
                                <h4 class="font-bold text-[13px] text-blue-700">Dekat lokasi Anda saat ini</h4>
                                <p class="text-[11px] text-blue-600/70 mt-0.5 truncate">Gunakan GPS / Lokasi Anda</p>
                            </div>
                        </div>

                        <!-- Disarankan dari sistem -->
                        <div v-for="item in filteredLocations" :key="item.id" @click="searchQuery = item.title; desktopActiveMenu = 'jadwal'" class="flex gap-3 items-center cursor-pointer group hover:bg-gray-50 p-2 -mx-2 rounded-xl transition">
                            <div :class="`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                <AppIcon :iconClass="`${item.icon} ${item.iconColor} text-sm`" />
                            </div>
                            <div class="border-b border-[#6C757D]/10 pb-2 pt-1 w-full group-last:border-0">
                                <h4 class="font-bold text-[13px] text-[#0A2540]">{{ item.title }}</h4>
                                <p class="text-[11px] text-[#6C757D] mt-0.5 truncate">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================== DESKTOP: JENIS ASET ================== -->
                <div v-if="desktopActiveMenu === 'jenis'" class="w-full max-w-sm mx-auto">
                    <h2 class="text-lg font-extrabold text-[#0A2540] mb-4">Pilih Jenis Aset</h2>
                    <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                        <Search class="text-[#0A2540] pl-1 text-sm" />
                        <input v-model="assetSearchQuery" type="text" placeholder="Cari jenis aset..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                    </div>
                    <div class="space-y-4 max-h-[250px] overflow-y-auto pr-2 overscroll-contain">
                        <div v-if="!assetSearchQuery" class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer group p-1 border border-[#6C757D]/20 rounded-xl px-4 py-3 bg-[#F8F9FA]">
                                <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.length === 0}">
                                    <Check v-if="selectedAssets.length === 0" class="text-white text-[10px]" />
                                </div>
                                <span class="text-sm font-bold text-[#0A2540]">Semua</span>
                                <input type="checkbox" :checked="selectedAssets.length === 0" @change="selectedAssets = []" class="hidden">
                            </label>
                        </div>
                        <div v-for="(cat, idx) in filteredAssetCategories" :key="idx">
                            <h3 class="text-xs font-bold text-[#6C757D] mb-2">{{ cat.name }}</h3>
                            <div class="space-y-2">
                                <label v-for="item in cat.items" :key="item" class="flex items-center gap-3 cursor-pointer group p-1">
                                    <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.includes(item)}">
                                        <Check v-if="selectedAssets.includes(item)" class="text-white text-[10px]" />
                                    </div>
                                    <span class="text-sm font-medium text-[#0A2540]">{{ item }}</span>
                                    <input type="checkbox" :value="item" class="hidden" @change="toggleAsset(item)">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================== DESKTOP: JADWAL (DUAL CALENDAR) ================== -->
                <div v-if="desktopActiveMenu === 'jadwal'" class="w-full flex flex-col">

                    <div class="flex justify-between items-center mb-4 px-2">
                        <button @click="prevDesktopMonth" class="w-8 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition" :class="desktopCalendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''">
                            <ChevronLeft class="text-[#0A2540] text-sm" />
                        </button>
                        <div class="flex gap-8 w-full px-4">
                            <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage]?.title }}</h3>
                            <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage + 1]?.title }}</h3>
                        </div>
                        <button @click="nextDesktopMonth" class="w-8 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                            <ChevronRight class="text-[#0A2540] text-sm" />
                        </button>
                    </div>

                    <div v-if="['day', 'night'].includes(activeScheduleMode)" class="flex gap-8 px-2">
                        <!-- Kalender Kiri -->
                        <div class="flex-1">
                            <div class="grid grid-cols-7 gap-y-5 mb-1">
                                <div v-for="day in daysOfWeek" :key="'d1-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                <template v-for="(week, wIdx) in monthsData[desktopCalendarPage]?.weeks" :key="'w1-'+wIdx">
                                    <div v-for="(date, dIdx) in week" :key="'d1-'+wIdx+'-'+dIdx" class="relative flex justify-center items-center h-10">
                                        <template v-if="date">
                                            <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                :class="[
                                                    isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) ? 'text-gray-300 cursor-not-allowed line-through' : 'hover:bg-gray-100',
                                                    { 'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) || isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                    'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                    'text-[#0A2540] hover:border hover:border-[#1A1A1A]': !isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) }
                                                ]"
                                                @click="!isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && selectDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)">
                                                <span>{{ date }}</span>
                                            </div>
                                            <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                            <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Kalender Kanan -->
                        <div class="flex-1">
                            <div class="grid grid-cols-7 gap-y-5 mb-1">
                                <div v-for="day in daysOfWeek" :key="'d2-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                <template v-for="(week, wIdx) in monthsData[desktopCalendarPage + 1]?.weeks" :key="'w2-'+wIdx">
                                    <div v-for="(date, dIdx) in week" :key="'d2-'+wIdx+'-'+dIdx" class="relative flex justify-center items-center h-10">
                                        <template v-if="date">
                                            <div v-if="isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                :class="[
                                                    isDateDisabled(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) ? 'text-gray-300 cursor-not-allowed line-through' : 'hover:bg-gray-100',
                                                    { 'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) || isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date),
                                                    'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date),
                                                    'text-[#0A2540] hover:border hover:border-[#1A1A1A]': !isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && !isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && !isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && !isDateDisabled(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) }
                                                ]"
                                                @click="!isDateDisabled(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && selectDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)">
                                                <span>{{ date }}</span>
                                            </div>
                                            <div v-if="isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                            <div v-else-if="isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- UI KHUSUS HOUR (Jam) -->
                    <div v-if="activeScheduleMode === 'hour'" class="pt-2 px-2 max-w-sm mx-auto w-full">
                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Tanggal Sewa</label>
                            <input type="date" v-model="simpleDateString" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                        </div>

                        <h4 class="text-sm font-bold text-[#0A2540] mb-3 text-center">Tentukan Waktu (Jam)</h4>
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
                        <div class="mb-6">
                            <label class="block text-[11px] font-bold text-[#6C757D] mb-1">Mulai Dari Tanggal</label>
                            <input type="date" v-model="simpleDateString" class="w-full border border-[#6C757D]/30 rounded-xl p-2.5 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#1A1A1A]" />
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#6C757D] mb-2 text-center">Durasi Sewa (Bulan)</label>
                            <CircularMonthSlider v-model="durationMonths" />
                        </div>

                        <div v-if="endDate" class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] text-[#6C757D] font-bold">Tanggal Selesai Otomatis:</span>
                            <span class="text-[11px] font-bold text-[#0A2540]">
                                {{ endDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ================== DESKTOP: RENTANG HARGA ================== -->
                <div v-if="desktopActiveMenu === 'harga'" class="w-full max-w-sm mx-auto">
                    <h2 class="text-lg font-extrabold text-[#0A2540] mb-2">Rentang Harga</h2>
                    <p class="text-[12px] text-[#6C757D] mb-5">Sesuaikan batas anggaran yang Anda inginkan.</p>

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
                    <p v-if="priceError" class="text-[11px] font-bold text-red-500 mt-1 mb-6">{{ priceError }}</p>
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
                    <div class="mb-2 mt-8 relative h-1.5 mx-2" ref="sliderTrack">
                        <!-- Background track -->
                        <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                        <!-- Active track -->
                        <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>

                        <!-- Min Thumb & Tooltip -->
                        <div @mousedown.prevent="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')"
                            class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                            :style="`left: calc(${minPercent}% - 10px)`">
                            <div v-show="activeThumb === 'min'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px] select-none">
                                {{ parsedMinPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMinPrice) }}
                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                            </div>
                        </div>

                        <!-- Max Thumb & Tooltip -->
                        <div @mousedown.prevent="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')"
                            class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                            :style="`left: calc(${maxPercent}% - 10px)`">
                            <div v-show="activeThumb === 'max'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px] select-none">
                                {{ parsedMaxPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMaxPrice) }}
                                <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-3 mx-2 text-[10px] font-bold text-[#6C757D]">
                        <span>{{ 'Rp' + formatPriceShort(0) }}</span>
                        <span>{{ 'Rp' + formatPriceShort(maxLimit) + ' +' }}</span>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
