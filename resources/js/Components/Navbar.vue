<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useHomeSearch } from '@/Composables/useHomeSearch';

const props = defineProps({
    transparent: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const isScrolled = ref(false);

const { 
    keywordQuery, isMobileSearchOpen, isKeywordSheetOpen,
    
    // Jadwal
    desktopCalendarPage, prevDesktopMonth, nextDesktopMonth, monthsData, daysOfWeek, selectDate, isStartDate, isInRange, isEndDate, endDate, formattedSchedule,

    // Harga
    priceError, formattedMinPrice, formattedMaxPrice, handleMinPriceInput, handleMaxPriceInput, sliderTrack, minPercent, maxPercent, startDrag, activeThumb, parsedMinPrice, parsedMaxPrice, maxLimit, formatPriceShort,
    
    // Aset
    assetSearchQuery, selectedAssets, filteredAssetCategories, toggleAsset,

    // Lokasi
    searchQuery, filteredLocations
} = useHomeSearch();

const isMobileMenuOpen = ref(false);
const desktopNavActiveMenu = ref(null);

let lastScrollY = typeof window !== 'undefined' ? window.scrollY : 0;

const handleScroll = () => {
    // Mengubah logo menjadi search bar setelah scroll melewati 60px
    isScrolled.value = window.scrollY > 60;
    
    if (desktopNavActiveMenu.value !== null) {
        if (Math.abs(window.scrollY - lastScrollY) > 15) {
            desktopNavActiveMenu.value = null;
        }
    } else {
        lastScrollY = window.scrollY;
    }
};

// Fungsi ketika mini search bar di enter / diklik
const handleNavSearch = () => {
    if (keywordQuery.value.trim()) {
        console.log("Mencari:", keywordQuery.value);
        desktopNavActiveMenu.value = null;
        isKeywordSheetOpen.value = false;
        // Tambahkan logika pencarian / redirect inertia Anda di sini:
        // router.get('/search', { q: keywordQuery.value });
    }
};

const applySuggestion = (text) => {
    keywordQuery.value = text;
    handleNavSearch();
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const isCurrentlyTransparent = computed(() => {
    return props.transparent && !isScrolled.value;
});
</script>
<template>
    <nav
        :class="[
            'fixed top-0 left-0 w-full z-50 transition-all duration-300',
            isCurrentlyTransparent
                ? 'bg-transparent shadow-none border-transparent'
                : 'bg-white shadow-sm border-b border-[#6C757D]/10'
        ]"
    >
        <div class="w-full mx-auto px-6 lg:px-8 transition-all duration-300">
            <div class="flex justify-between items-center h-16">

                <!-- ==================== AREA MOBILE: LOGO VS SEARCH BAR ==================== -->
                <div class="flex md:hidden w-full items-center">
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <!-- KONDISI 1: Belum di-scroll -> Tampilkan Logo -->
                        <div v-if="!isScrolled" key="mobile-logo" class="flex justify-between items-center w-full">
                            <Link :href="route('Home')" class="flex items-center gap-2">
                                <img
                                    src="/kusewa-logo.png"
                                    alt="logo"
                                    :class="[
                                        'h-8 w-auto object-contain transition-all duration-300',
                                        isCurrentlyTransparent ? 'brightness-0 invert' : 'brightness-100 invert-0'
                                    ]"
                                />
                                <span
                                    :class="[
                                        'font-bold text-lg transition-colors duration-300',
                                        isCurrentlyTransparent ? 'text-white' : 'text-[#0A2540]'
                                    ]"
                                >
                                    kusewa<span class="text-[#FFC000]">.id</span>
                                </span>
                            </Link>

                            <!-- Mobile Language Selector -->
                            <div
                                class="flex items-center gap-1.5 cursor-pointer transition px-2 py-1 rounded-lg"
                                :class="[
                                    isCurrentlyTransparent
                                        ? 'text-white hover:bg-white/10'
                                        : 'text-[#0A2540] hover:bg-gray-100'
                                ]"
                            >
                                <img
                                    src="https://flagcdn.com/id.svg"
                                    alt="Indonesia Flag"
                                    class="w-4 h-4 rounded-full object-cover border border-white/20"
                                />
                                <span class="font-semibold text-xs">ID</span>
                                <i class="fa-solid fa-chevron-down text-[8px] ml-0.5"></i>
                            </div>
                        </div>

                        <!-- KONDISI 2: Sudah di-scroll -> Tampilkan Mini Search Bar -->
                        <div v-else key="mobile-search" class="w-full py-1 flex items-center gap-2">
                            <div class="relative w-full flex items-center">
                                <!-- Ikon Kaca Pembesar -->
                                <i class="fa-solid fa-magnifying-glass absolute left-4 text-[#6C757D] text-xs"></i>

                                <!-- Fake Input Search -->
                                <div
                                    @click="isKeywordSheetOpen = true"
                                    class="w-full bg-[#F8F9FA] text-[#0A2540] text-xs font-medium rounded-full pl-10 pr-10 py-2.5 border border-gray-200/80 focus:outline-none focus:bg-white focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540] transition-all shadow-inner flex items-center cursor-pointer"
                                >
                                    <span :class="keywordQuery ? 'text-[#0A2540]' : 'text-[#6C757D]'">{{ keywordQuery || 'Mau sewa apa hari ini?' }}</span>
                                </div>

                                <!-- Tombol Filter Mini (Kanan) -->
                                <button
                                    @click="isMobileSearchOpen = true"
                                    class="absolute right-1 w-7 h-7 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-full flex items-center justify-center active:scale-90 transition-transform shadow-sm"
                                >
                                    <i class="fa-solid fa-sliders text-[10px] font-bold"></i>
                                </button>
                            </div>

                            <!-- Notification Button -->
                            <button class="w-9 h-9 flex-shrink-0 bg-white border border-gray-200/80 rounded-full flex items-center justify-center text-[#0A2540] active:scale-95 transition-transform shadow-sm">
                                <i class="fa-regular fa-bell text-sm"></i>
                            </button>
                        </div>
                    </Transition>
                </div>


                <!-- ==================== AREA DESKTOP (TIDAK BERUBAH) ==================== -->
                <!-- Logo Desktop (Selalu Tampil di Layar Besar) -->
                <Link :href="route('Home')" class="hidden md:flex items-center gap-2">
                    <img
                        src="/kusewa-logo.png"
                        alt="logo"
                        :class="[
                            'h-8 w-auto object-contain transition-all duration-300',
                            isCurrentlyTransparent ? 'brightness-0 invert' : 'brightness-100 invert-0'
                        ]"
                    />
                    <span
                        :class="[
                            'font-bold text-lg transition-colors duration-300',
                            isCurrentlyTransparent ? 'text-white' : 'text-[#0A2540]'
                        ]"
                    >
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>

                <!-- Desktop Menu Links -->
                <div class="hidden md:flex items-center space-x-7">
                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Jelajahi
                    </Link>

                    <Link
                        v-if="page.props.auth.user"
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Favorit
                    </Link>

                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Bantuan
                    </Link>

                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Mulai Sewakan Aset
                    </Link>
                </div>

                <!-- Desktop Right Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Desktop Mini Search Bar -->
                    <div class="relative flex items-center w-[220px] lg:w-[280px]">
                        
                        <!-- Overlay for closing the modal -->
                        <div v-if="desktopNavActiveMenu" @click="desktopNavActiveMenu = null" class="fixed inset-0 z-40"></div>

                        <!-- Search Bar -->
                        <div class="relative w-full z-50">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs transition-colors duration-300" :class="isCurrentlyTransparent ? 'text-white' : 'text-[#6C757D]'"></i>
                            <input
                                type="text"
                                v-model="keywordQuery"
                                @click="desktopNavActiveMenu = 'keyword'"
                                @keyup.enter="handleNavSearch"
                                placeholder="Mau sewa apa hari ini?"
                                :class="[
                                    'w-full text-xs font-medium rounded-full pl-9 pr-10 py-2 border focus:outline-none transition-all shadow-inner relative z-50',
                                    isCurrentlyTransparent
                                        ? 'bg-white/10 text-white placeholder-white border-white/30 focus:bg-white focus:text-[#0A2540] focus:placeholder-[#6C757D]'
                                        : 'bg-[#F8F9FA] text-[#0A2540] placeholder-[#6C757D] border-gray-200/80 focus:bg-white focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540]'
                                ]"
                            />
                            <button
                                @click="desktopNavActiveMenu = desktopNavActiveMenu === 'filter' ? null : 'filter'"
                                class="absolute right-1 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-full flex items-center justify-center active:scale-90 transition-transform shadow-sm z-50"
                            >
                                <i class="fa-solid fa-sliders text-[9px] font-bold"></i>
                            </button>
                        </div>

                        <!-- Dropdown Modal -->
                        <Transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="transform scale-95 opacity-0 -translate-y-4"
                            enter-to-class="transform scale-100 opacity-100 translate-y-0"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="transform scale-100 opacity-100 translate-y-0"
                            leave-to-class="transform scale-95 opacity-0 -translate-y-4"
                        >
                            <div v-if="desktopNavActiveMenu" class="absolute top-[120%] right-0 w-[340px] bg-white rounded-2xl shadow-xl border border-[#6C757D]/10 p-5 z-50 origin-top flex flex-col max-h-[75vh] overflow-y-auto hide-scrollbar transition-all duration-300 overscroll-contain">
                                
                                <!-- Keyword Search Modal -->
                                <div v-if="desktopNavActiveMenu === 'keyword'">
                                    <div class="flex items-center justify-between mb-3">
                                        <h2 class="text-sm font-extrabold text-[#0A2540]">Riwayat Pencarian</h2>
                                        <button class="text-[10px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2">Hapus</button>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mb-5">
                                        <div v-for="item in ['Vila di Bali', 'Mobil Avanza', 'Kamera Canon']" :key="item" @click="applySuggestion(item)" class="px-3 py-1.5 bg-[#F8F9FA] text-[#0A2540] border border-[#6C757D]/20 rounded-full text-xs font-medium cursor-pointer hover:bg-gray-100 transition">
                                            {{ item }}
                                        </div>
                                    </div>

                                    <h2 class="text-sm font-extrabold text-[#0A2540] mb-3">Anda Mungkin Suka</h2>
                                    <div class="flex flex-wrap gap-2 mb-5">
                                        <div v-for="item in ['Lahan Kosong', 'Gedung Pernikahan', 'Proyektor']" :key="item" @click="applySuggestion(item)" class="px-3 py-1.5 bg-[#F8F9FA] text-[#0A2540] border border-[#6C757D]/20 rounded-full text-xs font-medium cursor-pointer hover:bg-gray-100 transition">
                                            {{ item }}
                                        </div>
                                    </div>

                                    <h2 class="text-sm font-extrabold text-[#0A2540] mb-3">Trending</h2>
                                    <div class="flex flex-col gap-1">
                                        <div v-for="item in ['Sewa Mobil Bulanan', 'Villa Puncak', 'Peralatan Camping']" :key="item" @click="applySuggestion(item)" class="flex items-center gap-3 cursor-pointer group hover:bg-[#F8F9FA] p-2 -mx-2 rounded-xl transition">
                                            <div class="w-7 h-7 rounded-full bg-[#FFC000]/10 text-[#FFC000] flex items-center justify-center flex-shrink-0">
                                                <i class="fa-solid fa-fire text-[10px]"></i>
                                            </div>
                                            <span class="text-xs font-medium text-[#0A2540]">{{ item }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Modal -->
                                <div v-if="desktopNavActiveMenu === 'filter'">
                                    <h2 class="text-sm font-extrabold text-[#0A2540] mb-3">Pencarian Filter</h2>
                                    <p class="text-xs text-[#6C757D] mb-4">Pilih kategori filter pencarian Anda</p>
                                    <div class="space-y-2 mb-4">
                                        <div @click="desktopNavActiveMenu = 'aset'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Jenis Aset</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ selectedAssets.length > 0 ? selectedAssets.join(', ') : 'Semua Jenis' }}</span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-[#6C757D] text-[10px]"></i>
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'lokasi'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Lokasi</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ searchQuery || 'Semua Lokasi' }}</span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-[#6C757D] text-[10px]"></i>
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'jadwal'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Jadwal</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ formattedSchedule || 'Pilih Tanggal' }}</span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-[#6C757D] text-[10px]"></i>
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'harga'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Harga</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ (parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? (formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice)) : 'Budget Anda' }}</span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-[#6C757D] text-[10px]"></i>
                                        </div>
                                    </div>
                                    <button @click="handleNavSearch" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-[13px]">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        Terapkan Filter
                                    </button>
                                </div>

                                <!-- Filter Detail Modals (Aset, Lokasi, Jadwal, Harga) -->
                                <div v-if="['aset', 'lokasi', 'jadwal', 'harga'].includes(desktopNavActiveMenu)">
                                    <div class="flex items-center gap-2 mb-4">
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                            <i class="fa-solid fa-chevron-left text-xs text-[#6C757D]"></i>
                                        </button>
                                        <h2 class="text-sm font-extrabold text-[#0A2540] capitalize">{{ desktopNavActiveMenu === 'aset' ? 'Jenis Aset' : desktopNavActiveMenu }}</h2>
                                    </div>
                                    
                                    <!-- JENIS ASET -->
                                    <div v-if="desktopNavActiveMenu === 'aset'" class="w-full">
                                        <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                                            <i class="fa-solid fa-magnifying-glass text-[#0A2540] pl-1 text-sm"></i>
                                            <input v-model="assetSearchQuery" type="text" placeholder="Cari jenis aset..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                                        </div>
                                        <div class="space-y-4 max-h-[250px] overflow-y-auto pr-2 overscroll-contain">
                                            <div v-if="!assetSearchQuery" class="space-y-2">
                                                <label class="flex items-center gap-3 cursor-pointer group p-1 border border-[#6C757D]/20 rounded-xl px-4 py-3 bg-[#F8F9FA]">
                                                    <div class="relative flex items-center justify-center w-5 h-5 rounded border border-[#6C757D]/40 transition" :class="{'bg-[#0A2540] border-[#0A2540]': selectedAssets.length === 0}">
                                                        <i v-if="selectedAssets.length === 0" class="fa-solid fa-check text-white text-[10px]"></i>
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
                                                            <i v-if="selectedAssets.includes(item)" class="fa-solid fa-check text-white text-[10px]"></i>
                                                        </div>
                                                        <span class="text-sm font-medium text-[#0A2540]">{{ item }}</span>
                                                        <input type="checkbox" :value="item" class="hidden" @change="toggleAsset(item)">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-full mt-4 bg-[#0A2540] hover:bg-[#113a63] text-white font-bold py-2 rounded-xl transition text-xs">Terapkan Aset</button>
                                    </div>

                                    <!-- LOKASI -->
                                    <div v-else-if="desktopNavActiveMenu === 'lokasi'" class="w-full">
                                        <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                                            <i class="fa-solid fa-magnifying-glass text-[#0A2540] pl-1 text-sm"></i>
                                            <input v-model="searchQuery" type="text" placeholder="Cari destinasi..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                                        </div>

                                        <h3 class="text-[11px] font-bold text-[#6C757D] mb-3 uppercase tracking-wider">Disarankan</h3>
                                        <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 overscroll-contain">
                                            <div v-for="item in filteredLocations" :key="item.id" @click="searchQuery = item.title; desktopNavActiveMenu = 'filter'" class="flex gap-3 items-center cursor-pointer group hover:bg-gray-50 p-2 -mx-2 rounded-xl transition">
                                                <div :class="`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                                    <i :class="`${item.icon} ${item.iconColor} text-xs`"></i>
                                                </div>
                                                <div class="border-b border-[#6C757D]/10 pb-2 pt-1 w-full group-last:border-0">
                                                    <h4 class="font-bold text-[12px] text-[#0A2540]">{{ item.title }}</h4>
                                                    <p class="text-[10px] text-[#6C757D] mt-0.5 truncate">{{ item.desc }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- JADWAL -->
                                    <div v-else-if="desktopNavActiveMenu === 'jadwal'" class="w-full flex flex-col">
                                        <div class="flex justify-between items-center mb-4 px-2">
                                            <button @click="prevDesktopMonth" class="w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition" :class="desktopCalendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''">
                                                <i class="fa-solid fa-chevron-left text-[#0A2540] text-xs"></i>
                                            </button>
                                            <h3 class="text-sm font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage]?.title }}</h3>
                                            <button @click="nextDesktopMonth" class="w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                                <i class="fa-solid fa-chevron-right text-[#0A2540] text-xs"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-7 gap-y-3 mb-1 px-2">
                                            <div v-for="day in daysOfWeek" :key="'d1-'+day" class="text-center text-[10px] font-bold text-[#6C757D]">{{ day }}</div>
                                            <div v-for="i in monthsData[desktopCalendarPage]?.emptyDaysStart" :key="'e1-'+i"></div>
                                            <div v-for="date in monthsData[desktopCalendarPage]?.daysInMonth" :key="'d1-'+date" class="relative flex justify-center items-center h-8" @click="selectDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)">
                                                <!-- KONEKTOR RENTANG -->
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <!-- TANGGAL -->
                                                <div class="relative z-10 w-7 h-7 flex flex-col items-center justify-center rounded-full text-[11px] font-bold cursor-pointer transition"
                                                    :class="{ 'bg-[#1A1A1A] text-white shadow-md': isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) || isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                            'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                            'text-[#0A2540] hover:border hover:border-[#1A1A1A]': !isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) }">
                                                    <span>{{ date }}</span>
                                                </div>
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-3 left-1/2 -translate-x-1/2 text-[8px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-3 left-1/2 -translate-x-1/2 text-[8px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-full mt-4 bg-[#0A2540] hover:bg-[#113a63] text-white font-bold py-2 rounded-xl transition text-xs">Simpan Jadwal</button>
                                    </div>

                                    <!-- HARGA -->
                                    <div v-else-if="desktopNavActiveMenu === 'harga'" class="w-full">
                                        <div class="flex gap-3 mb-1 mt-2">
                                            <div class="flex-1 border rounded-xl p-2 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                                <label class="block text-[10px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Min Harga</label>
                                                <div class="flex items-center text-[#0A2540] font-bold text-xs">
                                                    <span class="mr-1">Rp</span>
                                                    <input :value="formattedMinPrice" @input="handleMinPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                                </div>
                                            </div>
                                            <div class="flex-1 border rounded-xl p-2 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                                <label class="block text-[10px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Maks Harga</label>
                                                <div class="flex items-center text-[#0A2540] font-bold text-xs">
                                                    <span class="mr-1">Rp</span>
                                                    <input :value="formattedMaxPrice" @input="handleMaxPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="priceError" class="text-[10px] font-bold text-red-500 mt-1 mb-4">{{ priceError }}</p>
                                        <div v-else class="mb-4 mt-1 h-[14px]"></div>

                                        <div class="mb-2 mt-6 relative h-1.5 mx-2" ref="sliderTrack">
                                            <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                                            <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>
                                            <div @mousedown="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')"
                                                class="absolute top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-[2px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                                :style="`left: calc(${minPercent}% - 8px)`">
                                                <div v-show="activeThumb === 'min'" class="absolute -top-[40px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[10px] font-bold px-2 py-1 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[24px]">
                                                    {{ parsedMinPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMinPrice) }}
                                                    <div class="absolute -bottom-[3px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                                </div>
                                            </div>
                                            <div @mousedown="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')"
                                                class="absolute top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-[2px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                                :style="`left: calc(${maxPercent}% - 8px)`">
                                                <div v-show="activeThumb === 'max'" class="absolute -top-[40px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[10px] font-bold px-2 py-1 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[24px]">
                                                    {{ parsedMaxPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMaxPrice) }}
                                                    <div class="absolute -bottom-[3px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-full mt-6 bg-[#0A2540] hover:bg-[#113a63] text-white font-bold py-2 rounded-xl transition text-xs">Terapkan Harga</button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Language Selector Desktop -->
                    <div
                        class="flex items-center gap-2 cursor-pointer transition-all duration-300 px-3 py-1.5 rounded-lg border border-transparent"
                        :class="[
                            isCurrentlyTransparent
                                ? 'text-white hover:bg-white/10'
                                : 'text-[#0A2540] hover:bg-gray-100'
                        ]"
                    >
                        <img
                            src="https://flagcdn.com/id.svg"
                            alt="Indonesia Flag"
                            class="w-5 h-5 rounded-full object-cover border border-white/20"
                        />
                        <span class="font-semibold text-xs">ID</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                    </div>

                    <Link
                        :href="route('login')"
                        :class="[
                            'relative px-6 py-2 rounded-full font-semibold transition-all duration-300 active:scale-95',
                            isCurrentlyTransparent
                                ? 'bg-[#FFC000]/10 backdrop-blur-md border border-[#FFC000]/30 text-white shadow-lg hover:bg-[#FFC000] hover:border-[#FFC000] hover:text-[#0A2540]'
                                : 'bg-[#FFC000] border border-[#FFC000] text-[#0A2540] shadow-md hover:opacity-90'
                        ]"
                    >
                        Login
                    </Link>
                </div>

            </div>
        </div>
    </nav>
</template>
