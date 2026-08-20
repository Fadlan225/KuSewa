<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { ChevronDown, Search, X, Sliders, Bell, Loader2, History, Flame, ChevronRight, ChevronLeft, Check, HelpCircle, Headset, User, Shield, PieChart, LogOut, Smartphone, Building, Megaphone } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed, watch, inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import AnimatedPlaceholder from '@/Components/ui/AnimatedPlaceholder.vue';
import StickySubNavSearch from '@/Components/ui/StickySubNavSearch.vue';
import NoImageIcon from '@/Components/ui/Icons/NoImageIcon.vue';
import NotificationDropdown from '@/Components/ui/NotificationDropdown.vue';
import { useNotifications } from '@/Composables/useNotifications';

const isHome = computed(() => route().current('Home'));
const isBantuan = computed(() => route().current('Bantuan.*'));
const isActivity = computed(() => route().current('aktivitas.*'));
const isKotakMasuk = computed(() => route().current('chat.*'));
const isInbox = isKotakMasuk;

const props = defineProps({
    transparent: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const openAuthModal = inject('openAuthModal', () => { console.log('AuthModal not provided') });
const isScrolled = ref(false);
const isUserMenuOpen = ref(false);

const ownerStatus = computed(() => {
    const user = page.props.auth.user;
    if (!user) return null;
    const profile = user.owner_profile || user.ownerProfile;
    if (profile) return profile.status;
    return null;
});

const isVerifiedOwner = computed(() => ownerStatus.value === 'verified');
const isPendingOwner = computed(() => ownerStatus.value === 'pending' || ownerStatus.value === 'rejected');
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');

const userProfilePhoto = computed(() => {
    const photo = page.props.auth.user?.profile_photo;
    if (!photo) return null;
    return photo.startsWith('http') || photo.startsWith('/storage/') ? photo : '/storage/' + photo;
});

// Data real dari props controller
const searchHistory = computed(() => page.props.searchHistory || []);
const trending = computed(() => page.props.trending || []);

const {
    keywordQuery, isMobileSearchOpen, isKeywordSheetOpen,

    // Jadwal
    desktopCalendarPage, prevDesktopMonth, nextDesktopMonth, monthsData, daysOfWeek, selectDate, isStartDate, isInRange, isEndDate, endDate, formattedSchedule, isPastDate,

    // Harga
    priceError, formattedMinPrice, formattedMaxPrice, handleMinPriceInput, handleMaxPriceInput, sliderTrack, minPercent, maxPercent, startDrag, activeThumb, parsedMinPrice, parsedMaxPrice, maxLimit, formatPriceShort,

    // Aset
    assetSearchQuery, selectedAssets, filteredAssetCategories, toggleAsset,

    // Lokasi
    searchQuery, filteredLocations,

    // Search
    suggestions, isLoadingSuggestions, fetchSuggestions, performSearch,
    // Lokasi
    setLocationSuggestions,

    priceDistribution, handleBucketClick,
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

// Inisialisasi lokasi dari DB props
onMounted(() => {
    setLocationSuggestions(page.props.locationSuggestions || []);
});
watch(() => page.props.locationSuggestions, (val) => {
    setLocationSuggestions(val || []);
});

// Watch keyword untuk live suggestions
watch(keywordQuery, (val) => {
    fetchSuggestions(val);
});

// Bersihkan search jika kembali ke beranda tanpa parameter keyword
watch(() => page.url, () => {
    if (isHome.value && (!page.props.filters || !page.props.filters.q)) {
        keywordQuery.value = '';
    }
}, { immediate: true });

const isMobileMenuOpen = ref(false);
const desktopNavActiveMenu = ref(null);
const isNotifDropdownOpen = ref(false);

const { unreadCount, init: initNotifications } = useNotifications();

let lastScrollY = typeof window !== 'undefined' ? window.scrollY : 0;

const handleScroll = () => {
    isScrolled.value = window.scrollY > 60;

    if (desktopNavActiveMenu.value !== null) {
        if (Math.abs(window.scrollY - lastScrollY) > 50) {
            desktopNavActiveMenu.value = null;
        }
    } else {
        lastScrollY = window.scrollY;
    }
};

// Fungsi ketika mini search bar di enter / diklik
const handleNavSearch = () => {
    desktopNavActiveMenu.value = null;
    isKeywordSheetOpen.value = false;
    performSearch();
};

const applySuggestion = (text) => {
    keywordQuery.value = text;
    handleNavSearch();
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
    if (page.props.auth.user) {
        initNotifications();
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const isCurrentlyTransparent = computed(() => {
    return props.transparent && !isScrolled.value;
});

const initials = computed(() => {
    const name = page.props.auth.user?.name ?? '';

    return name
        .trim()
        .substring(0, 2)
        .toUpperCase();
});
</script>
<template>
    <nav class="fixed top-0 left-0 w-full z-[100] transition-all duration-300">

        <!-- Top Mini Nav (Desktop Only) -->
        <div class="hidden lg:block w-full border-b transition-colors duration-300"
             :class="isCurrentlyTransparent ? 'bg-transparent border-white/20 text-white' : 'bg-slate-50 border-slate-200 text-slate-500'">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex justify-between items-center h-8 text-[11px] font-medium tracking-wide">
                    <!-- Kiri -->
                    <div class="flex items-center gap-5">
                        <Link :href="route('assets.search')" class="flex items-center gap-1.5 hover:text-[#FFC000] transition-colors">
                            <Building class="w-3.5 h-3.5" />
                            Sewa Aset
                        </Link>
                    </div>

                    <!-- Kanan -->
                    <div class="flex items-center gap-5">
                        <button v-if="!page.props.auth.user" @click="openAuthModal()" class="flex items-center gap-1.5 hover:text-[#FFC000] transition-colors">
                            <Megaphone class="w-3.5 h-3.5" />
                            Promosikan Aset Anda
                        </button>
                        <Link v-else-if="page.props.auth.user.role === 'owner'" :href="route('owner.dashboard')" class="flex items-center gap-1.5 hover:text-[#FFC000] transition-colors">
                            <Megaphone class="w-3.5 h-3.5" />
                            Dashboard Owner
                        </Link>
                        <Link v-else :href="route('owner.register')" class="flex items-center gap-1.5 hover:text-[#FFC000] transition-colors">
                            <Megaphone class="w-3.5 h-3.5" />
                            Promosikan Aset Anda
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navbar Wrapper (Selalu di atas segalanya agar tidak ikut gelap oleh backdrop) -->
        <div
            class="relative w-full z-50 transition-all duration-300"
            :class="isCurrentlyTransparent
                ? 'bg-transparent shadow-none border-transparent'
                : 'bg-white border-b border-gray-200'"
        >
            <div class="w-full max-w-7xl mx-auto px-6 lg:px-8 transition-all duration-300">
                <div class="flex justify-between items-center h-16">

                <!-- ==================== AREA MOBILE: LOGO VS SEARCH BAR ==================== -->
                <div class="flex md:hidden w-full items-center">
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-2   00 ease-out"
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
                                    src="/kitasewa-logo.png"
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
                                    kitasewa<span class="text-[#FFC000]">.id</span>
                                </span>
                            </Link>

                            <!-- Mobile Notification Button -->
                            <Link
                                v-if="page.props.auth.user"
                                :href="route('notifications.page')"
                                class="relative w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 active:scale-95"
                                :class="isCurrentlyTransparent ? 'text-white hover:bg-white/10' : 'text-[#0A2540] hover:bg-gray-100'"
                            >
                                <Bell class="w-5 h-5" />
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 flex items-center justify-center bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-4 rounded-full px-1 shadow"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </Link>
                        </div>

                        <!-- KONDISI 2: Sudah di-scroll -> Tampilkan Mini Search Bar -->
                        <div v-else key="mobile-search" class="w-full py-1 flex items-center gap-2">
                            <div class="relative w-full flex items-center">
                                <!-- Ikon Kaca Pembesar -->
                                <Search class="absolute left-4 text-[#6C757D] w-4 h-4 z-10" />

                                <!-- Fake Input Search -->
                                <div
                                    @click="isKeywordSheetOpen = true"
                                    class="w-full bg-[#F8F9FA] text-[#0A2540] text-xs font-medium rounded-full pl-10 pr-10 py-2.5 border border-gray-200/80 focus:outline-none focus:bg-white focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540] transition-all shadow-inner flex items-center cursor-pointer relative overflow-hidden"
                                    style="min-height: 38px;"
                                >
                                    <span v-if="keywordQuery" class="truncate pr-4 text-[#0A2540] relative z-10">{{ keywordQuery }}</span>
                                    <AnimatedPlaceholder
                                        v-else
                                        :placeholders="page.props.dynamicPlaceholders"
                                        :isFocused="false"
                                        :hasValue="!!keywordQuery"
                                        offsetClass="left-10"
                                        class="text-[#6C757D]"
                                    />
                                </div>

                                <!-- Tombol Clear Search (Mobile Fake Input) -->
                                <button
                                    v-if="keywordQuery"
                                    @click.stop="keywordQuery = ''"
                                    class="absolute right-9 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-[#6C757D] hover:text-[#0A2540] transition-colors z-10"
                                >
                                    <X class="text-xs" />
                                </button>

                                <!-- Tombol Filter Mini (Kanan) -->
                                <button
                                    @click="isMobileSearchOpen = true"
                                    class="absolute right-1 w-7 h-7 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-full flex items-center justify-center active:scale-90 transition-transform shadow-sm"
                                >
                                    <Sliders class="text-[10px] font-bold" />
                                </button>
                            </div>

                            <!-- Notification Button -->
                            <Link
                                v-if="page.props.auth.user"
                                :href="route('notifications.page')"
                                class="relative w-9 h-9 flex-shrink-0 bg-white border border-gray-200/80 rounded-full flex items-center justify-center text-[#0A2540] active:scale-95 transition-transform shadow-sm"
                            >
                                <Bell class="text-sm" />
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 flex items-center justify-center bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-4 rounded-full px-1 shadow"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </Link>
                        </div>
                    </Transition>
                </div>


                <!-- ==================== AREA DESKTOP (TIDAK BERUBAH) ==================== -->
                <!-- Bagian Kiri: Logo & Search Bar -->
                <div class="hidden md:flex items-center gap-8">
                    <!-- Logo Desktop -->
                    <Link :href="route('Home')" class="flex items-center gap-2">
                        <img
                            src="/kitasewa-logo.png"
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
                            kitasewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>

                    <!-- Desktop Mini Search Bar -->
                    <div
                        class="relative flex items-center w-[280px] lg:w-[380px] transition-opacity duration-300"
                        :class="isScrolled && isHome ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                    >
                        <!-- Wrapper luar yang bentuknya persis seperti input -->
                        <Transition
                            enter-active-class="transition-opacity duration-300 ease-out"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-200 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-if="desktopNavActiveMenu" @click="desktopNavActiveMenu = null" class="fixed inset-0 -z-10 bg-black/50 backdrop-blur-sm"></div>
                        </Transition>

                        <!-- Search Bar -->
                        <div
                            class="relative w-full z-50 rounded-l-lg rounded-r-none transition-all border border-r-0 overflow-hidden"
                            :class="[
                                isCurrentlyTransparent
                                    ? 'bg-white/10 border-white/30'
                                    : 'bg-[#F8F9FA] border-gray-200/80',
                                desktopNavActiveMenu === 'keyword' && !isCurrentlyTransparent ? 'bg-white ring-1 ring-[#0A2540] border-[#0A2540]' : '',
                                desktopNavActiveMenu === 'keyword' && isCurrentlyTransparent ? 'bg-white' : ''
                            ]"
                        >
                            <AnimatedPlaceholder
                                :placeholders="page.props.dynamicPlaceholders"
                                :isFocused="desktopNavActiveMenu === 'keyword'"
                                :hasValue="!!keywordQuery"
                                offsetClass="left-4 text-xs"
                                :class="isCurrentlyTransparent ? 'text-white' : 'text-[#6C757D]'"
                            />

                            <input
                                type="text"
                                v-model="keywordQuery"
                                @click="desktopNavActiveMenu = 'keyword'"
                                @keyup.enter="handleNavSearch"
                                :class="[
                                    'w-full text-xs font-medium rounded-l-lg rounded-r-none pl-4 pr-8 py-2.5 border-none focus:outline-none focus:ring-0 transition-all shadow-inner relative z-50 bg-transparent placeholder-transparent',
                                    isCurrentlyTransparent && desktopNavActiveMenu !== 'keyword'
                                        ? 'text-white'
                                        : 'text-[#0A2540]'
                                ]"
                            />

                            <!-- Tombol Clear Search (Desktop Input) -->
                            <button
                                v-if="keywordQuery"
                                @click.stop="keywordQuery = ''; desktopNavActiveMenu = null;"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-[#6C757D] hover:text-[#0A2540] transition-colors z-[60]"
                            >
                                <X class="text-[10px]" />
                            </button>
                        </div>

                        <!-- Tombol Search Terpisah -->
                        <button
                            @click="handleNavSearch"
                            class="flex-shrink-0 w-[38px] h-[38px] bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-r-lg rounded-l-none flex items-center justify-center active:scale-90 transition-transform shadow-sm z-50 border border-[#FFC000]"
                        >
                            <Search class="text-xs font-bold" />
                        </button>

                        <!-- Dropdown Modal -->
                        <Transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="transform scale-95 opacity-0 -translate-y-4"
                            enter-to-class="transform scale-100 opacity-100 translate-y-0"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="transform scale-100 opacity-100 translate-y-0"
                            leave-to-class="transform scale-95 opacity-0 -translate-y-4"
                        >
                            <div v-if="desktopNavActiveMenu" class="absolute top-[120%] left-0 w-[340px] bg-white rounded-2xl shadow-xl border border-[#6C757D]/10 p-5 z-50 origin-top-left flex flex-col max-h-[75vh] overflow-y-auto hide-scrollbar transition-all duration-300 overscroll-contain">

                                <!-- Keyword Search Modal -->
                                <div v-if="desktopNavActiveMenu === 'keyword'">

                                    <!-- Live Suggestions (saat user mengetik) -->
                                    <template v-if="keywordQuery.length >= 2">
                                        <div class="flex items-center justify-between mb-3">
                                            <h2 class="text-sm font-extrabold text-[#0A2540]">Saran Pencarian</h2>
                                            <Loader2 v-if="isLoadingSuggestions" class="text-[#6C757D] text-xs animate-spin" />
                                        </div>
                                        <div v-if="suggestions.length > 0" class="flex flex-col gap-0.5 mb-4">
                                            <div v-for="s in suggestions" :key="s.text" @click="applySuggestion(s.text)" class="flex items-center gap-3 cursor-pointer hover:bg-[#F8F9FA] p-2 -mx-2 rounded-xl transition">
                                                <div class="w-6 h-6 rounded-full bg-gray-100 text-[#6C757D] flex items-center justify-center flex-shrink-0">
                                                    <AppIcon :iconClass="s.icon + ' text-[10px]'" />
                                                </div>
                                                <span class="text-xs font-medium text-[#0A2540]">{{ s.text }}</span>
                                                <span class="ml-auto text-[9px] text-[#6C757D] capitalize">{{ s.type === 'history' ? 'riwayat' : s.type === 'category' ? 'kategori' : s.type === 'location' ? 'lokasi' : s.type === 'popular' ? 'populer' : 'aset' }}</span>
                                            </div>
                                        </div>
                                        <p v-else-if="!isLoadingSuggestions" class="text-xs text-[#6C757D] mb-4">Tidak ada saran untuk "{{ keywordQuery }}"</p>
                                    </template>

                                    <!-- Default (belum mengetik) -->
                                    <template v-else>
                                        <!-- Riwayat Pencarian (hanya jika login & ada riwayat) -->
                                        <template v-if="page.props.auth.user && searchHistory.length > 0">
                                            <div class="flex items-center justify-between mb-3">
                                                <h2 class="text-sm font-extrabold text-[#0A2540]">Riwayat Pencarian</h2>
                                            </div>
                                            <div class="flex flex-wrap gap-2 mb-5">
                                                <div v-for="item in searchHistory" :key="item" @click="applySuggestion(item)" class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F8F9FA] text-[#0A2540] border border-[#6C757D]/20 rounded-full text-xs font-medium cursor-pointer hover:bg-gray-100 transition">
                                                    <History class="text-[9px] text-[#6C757D]" />
                                                    {{ item }}
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Trending Minggu Ini -->
                                        <template v-if="trending.length > 0">
                                            <h2 class="text-sm font-extrabold text-[#0A2540] mb-3">Populer Minggu Ini</h2>
                                            <div class="flex flex-col gap-1">
                                                <div v-for="item in trending" :key="item" @click="applySuggestion(item)" class="flex items-center gap-3 cursor-pointer group hover:bg-[#F8F9FA] p-2 -mx-2 rounded-xl transition">
                                                    <div class="w-7 h-7 rounded-full bg-[#FFC000]/10 text-[#FFC000] flex items-center justify-center flex-shrink-0">
                                                        <Flame class="text-[10px]" />
                                                    </div>
                                                    <span class="text-xs font-medium text-[#0A2540]">{{ item }}</span>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Fallback jika tidak ada data -->
                                        <template v-if="(!page.props.auth.user || searchHistory.length === 0) && trending.length === 0">
                                            <p class="text-xs text-[#6C757D] text-center py-4">Ketik sesuatu untuk mencari aset sewa</p>
                                        </template>
                                    </template>
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
                                            <ChevronRight class="text-[#6C757D] text-[10px]" />
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'lokasi'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Lokasi</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ searchQuery || 'Semua Lokasi' }}</span>
                                            </div>
                                            <ChevronRight class="text-[#6C757D] text-[10px]" />
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'jadwal'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Jadwal</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ formattedSchedule || 'Pilih Tanggal' }}</span>
                                            </div>
                                            <ChevronRight class="text-[#6C757D] text-[10px]" />
                                        </div>
                                        <div @click="desktopNavActiveMenu = 'harga'" class="p-3 border border-[#6C757D]/20 rounded-xl hover:bg-[#F8F9FA] cursor-pointer transition flex items-center justify-between">
                                            <div class="flex flex-col flex-1 truncate pr-2">
                                                <span class="text-xs font-bold text-[#0A2540]">Harga</span>
                                                <span class="text-[11px] text-[#6C757D] truncate">{{ (parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? (formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice)) : 'Budget Anda' }}</span>
                                            </div>
                                            <ChevronRight class="text-[#6C757D] text-[10px]" />
                                        </div>
                                    </div>
                                    <button @click="handleNavSearch" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-[13px]">
                                        <Search class="" />
                                        Terapkan Filter
                                    </button>
                                </div>

                                <!-- Filter Detail Modals (Aset, Lokasi, Jadwal, Harga) -->
                                <div v-if="['aset', 'lokasi', 'jadwal', 'harga'].includes(desktopNavActiveMenu)">
                                    <div class="flex items-center gap-2 mb-4">
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                            <ChevronLeft class="text-xs text-[#6C757D]" />
                                        </button>
                                        <h2 class="text-sm font-extrabold text-[#0A2540] capitalize">{{ desktopNavActiveMenu === 'aset' ? 'Jenis Aset' : desktopNavActiveMenu }}</h2>
                                    </div>

                                    <!-- JENIS ASET -->
                                    <div v-if="desktopNavActiveMenu === 'aset'" class="w-full">
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
                                        <button @click="desktopNavActiveMenu = 'filter'" class="w-full mt-4 bg-[#0A2540] hover:bg-[#113a63] text-white font-bold py-2 rounded-xl transition text-xs">Terapkan Aset</button>
                                    </div>

                                    <!-- LOKASI -->
                                    <div v-else-if="desktopNavActiveMenu === 'lokasi'" class="w-full">
                                        <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                                            <Search class="text-[#0A2540] pl-1 text-sm" />
                                            <input v-model="searchQuery" type="text" placeholder="Cari destinasi..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                                        </div>

                                        <h3 class="text-[11px] font-bold text-[#6C757D] mb-3 uppercase tracking-wider">Disarankan</h3>
                                        <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 overscroll-contain">
                                            <div v-for="item in filteredLocations" :key="item.id" @click="searchQuery = item.title; desktopNavActiveMenu = 'filter'" class="flex gap-3 items-center cursor-pointer group hover:bg-gray-50 p-2 -mx-2 rounded-xl transition">
                                                <div :class="`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                                    <AppIcon :iconClass="`${item.icon} ${item.iconColor} text-xs`" />
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
                                                <ChevronLeft class="text-[#0A2540] text-xs" />
                                            </button>
                                            <h3 class="text-sm font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage]?.title }}</h3>
                                            <button @click="nextDesktopMonth" class="w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                                <ChevronRight class="text-[#0A2540] text-xs" />
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
                                                <div class="relative z-10 w-7 h-7 flex flex-col items-center justify-center rounded-full text-[11px] font-bold transition"
                                                    :class="[
                                                        isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) ? 'text-gray-300 cursor-not-allowed line-through' : 'cursor-pointer hover:border hover:border-[#1A1A1A]',
                                                        { 'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) || isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                          'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                          'text-[#0A2540]': !isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) }
                                                    ]"
                                                    @click="!isDateDisabled(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && selectDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)">
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
                                        <!-- Histogram -->
                                        <div v-else class="h-[24px] w-full flex items-end justify-between px-2 gap-[1px] mb-2 mt-4">
                                            <div v-for="(count, idx) in priceDistribution" :key="idx"
                                                @click="handleBucketClick(idx)"
                                                class="flex-1 rounded-t-[1px] transition-all duration-300 min-h-[2px] cursor-pointer hover:bg-opacity-80"
                                                :style="{ height: `${Math.max((count / maxDistributionCount) * 100, 4)}%` }"
                                                :class="isBucketActive(idx) ? 'bg-[#FFC000]' : 'bg-[#E2E8F0]'">
                                            </div>
                                        </div>

                                        <div class="mb-2 mt-4 relative h-1.5 mx-2" ref="sliderTrack">
                                            <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                                            <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>
                                            <div @mousedown.prevent="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')"
                                                class="absolute top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-[2px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                                :style="`left: calc(${minPercent}% - 8px)`">
                                                <div v-show="activeThumb === 'min'" class="absolute -top-[40px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[10px] font-bold px-2 py-1 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[24px] select-none">
                                                    {{ parsedMinPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMinPrice) }}
                                                    <div class="absolute -bottom-[3px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                                </div>
                                            </div>
                                            <div @mousedown.prevent="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')"
                                                class="absolute top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-[2px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                                :style="`left: calc(${maxPercent}% - 8px)`">
                                                <div v-show="activeThumb === 'max'" class="absolute -top-[40px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[10px] font-bold px-2 py-1 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[24px] select-none">
                                                    {{ parsedMaxPrice >= maxLimit ? formatPriceShort(maxLimit) + ' +' : formatPriceShort(parsedMaxPrice) }}
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

                </div>

                <!-- Bagian Kanan: Menu Links & Actions -->
                <div class="hidden md:flex items-center gap-4 h-full">
                    <!-- Desktop Menu Links -->
                    <div class="flex items-center space-x-7 h-full">

                    <Link
                        :href="route('Home')"
                        :class="[
                            'relative h-full flex items-center text-sm transition-colors duration-300 group',
                            isHome ? 'font-bold' : 'font-semibold',
                            isCurrentlyTransparent ? 'text-white hover:text-white/80' : 'text-[#0A2540] hover:text-[#0A2540]/80'
                        ]"
                    >
                        Beranda
                        <span
                            class="absolute bottom-0 left-0 w-full h-[3px] bg-[#FFC000] transition-all duration-300"
                            :class="isHome ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0'"
                        ></span>
                    </Link>

                    <!-- Bantuan -->
                    <Link
                        v-if="!page.props.auth.user"
                        :href="route('bantuan')"
                        :class="[
                            'relative h-full flex items-center text-sm transition-colors duration-300 group',
                            isBantuan ? 'font-bold' : 'font-semibold',
                            isCurrentlyTransparent ? 'text-white hover:text-white/80' : 'text-[#0A2540] hover:text-[#0A2540]/80'
                        ]"
                    >
                        Pusat Bantuan
                        <span
                            class="absolute bottom-0 left-0 w-full h-[3px] bg-[#FFC000] transition-all duration-300"
                            :class="isBantuan ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0'"
                        ></span>
                    </Link>

                    <!-- Aktivitas -->
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('aktivitas.hub')"
                        :class="[
                            'relative h-full flex items-center text-sm transition-colors duration-300 group',
                            isActivity ? 'font-bold' : 'font-semibold',
                            isCurrentlyTransparent ? 'text-white hover:text-white/80' : 'text-[#0A2540] hover:text-[#0A2540]/80'
                        ]"
                    >
                        Aktivitas
                        <span
                            class="absolute bottom-0 left-0 w-full h-[3px] bg-[#FFC000] transition-all duration-300"
                            :class="isActivity ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0'"
                        ></span>
                    </Link>

                    <!-- Kotak Masuk -->
                    <Link
                        v-if="page.props.auth.user"
                        href="/chat"
                        :class="[
                            'relative h-full flex items-center text-sm transition-colors duration-300 gap-1.5 group',
                            isInbox ? 'font-bold' : 'font-semibold',
                            isCurrentlyTransparent ? 'text-white hover:text-white/80' : 'text-[#0A2540] hover:text-[#0A2540]/80'
                        ]"
                    >
                        Pesan
                        <span
                            class="absolute bottom-0 left-0 w-full h-[3px] bg-[#FFC000] transition-all duration-300"
                            :class="isInbox ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0'"
                        ></span>

                        <!-- Notification Badge -->
                        <span v-if="page.props.auth.unreadCount > 0" class="flex items-center justify-center bg-red-500 text-white text-[10px] font-bold px-1.5 min-w-[18px] h-[18px] rounded-full">
                            {{ page.props.auth.unreadCount > 99 ? '99+' : page.props.auth.unreadCount }}
                        </span>
                    </Link>
                    </div>
                    <!-- Language Selector Desktop -->
                    <!-- <div
                        class="flex items-center gap-2 cursor-pointer transition-all duration-300 px-3 py-1.5 rounded-lg border border-transparent"
                        v-if="!page.props.auth.user"
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
                        <ChevronDown class="text-[10px] ml-0.5" />
                    </div> -->

                    <!-- Desktop User Actions -->
                    <div class="relative flex items-center gap-2">

                        <!-- Bell Notification Icon (Hanya untuk user login) -->
                        <div v-if="page.props.auth.user" class="relative">
                            <button
                                @click="isNotifDropdownOpen = !isNotifDropdownOpen; isUserMenuOpen = false;"
                                class="relative w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none"
                                :class="isCurrentlyTransparent ? 'text-white hover:bg-white/10' : 'text-[#0A2540] hover:bg-gray-100'"
                                title="Notifikasi"
                            >
                                <Bell class="w-5 h-5" />
                                <!-- Badge Unread Count -->
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 flex items-center justify-center bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-4 rounded-full px-1 shadow"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Backdrop untuk tutup dropdown -->
                            <div v-if="isNotifDropdownOpen" @click="isNotifDropdownOpen = false" class="fixed inset-0 z-40"></div>

                            <!-- Dropdown Notifikasi -->
                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                                enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                            >
                                <div
                                    v-if="isNotifDropdownOpen"
                                    class="absolute top-[130%] right-0 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 origin-top-right overflow-hidden"
                                >
                                    <NotificationDropdown @close="isNotifDropdownOpen = false" />
                                </div>
                            </Transition>
                        </div>

                        <template v-if="page.props.auth.user">
                            <!-- Trigger Button -->
                            <button
                                type="button"
                                @click="isUserMenuOpen = !isUserMenuOpen"
                                class="relative w-9 h-9 rounded-full border-2 transition-all duration-200 focus:outline-none overflow-hidden hover:scale-105"
                                :class="[
                                    isCurrentlyTransparent
                                        ? 'border-white/30 hover:border-white/70 shadow-md'
                                        : 'border-gray-200 hover:border-gray-300 shadow-sm'
                                ]"
                            >
                                <!-- User Avatar / Initials -->
                                <img
                                    v-if="userProfilePhoto"
                                    :src="userProfilePhoto"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-[#0A2540] text-white flex items-center justify-center font-bold text-sm"
                                >
                                    {{ initials }}
                                </div>
                            </button>

                            <!-- Backdrop Overlay to Close Menu -->
                            <div
                                v-if="isUserMenuOpen"
                                @click="isUserMenuOpen = false"
                                class="fixed inset-0 z-40"
                            ></div>

                            <!-- User Menu Dropdown Modal -->
                            <Transition
                                enter-active-class="transition duration-250 ease-out"
                                enter-from-class="transform scale-95 opacity-0 -translate-y-3"
                                enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                leave-to-class="transform scale-95 opacity-0 -translate-y-3"
                            >
                                <div
                                    v-if="isUserMenuOpen"
                                    class="absolute top-[130%] right-0 w-[320px] sm:w-[340px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-5 z-50 origin-top-right text-[#0A2540]"
                                >
                                    <!-- 1. Pusat Bantuan -->
                                    <Link :href="route('bantuan')" class="flex items-center gap-3 pb-3 cursor-pointer group" @click="isUserMenuOpen = false">
                                        <HelpCircle class="text-xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                                        <span class="text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Pusat Bantuan</span>
                                    </Link>

                                    <div class="h-px bg-gray-100 my-2"></div>

                                    <Link :href="route('hubungi-kami')" class="flex items-center gap-3 pb-3 cursor-pointer group" @click="isUserMenuOpen = false">
                                        <Headset class="text-xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                                        <span class="text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Hubungi Kami</span>
                                    </Link>

                                    <div class="h-px bg-gray-100 my-2"></div>

                                    <Link :href="route('profile.edit')" class="flex items-center gap-3 pb-3 cursor-pointer group" @click="isUserMenuOpen = false">
                                        <User class="text-xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                                        <span class="text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Profile</span>
                                    </Link>

                                    <div class="h-px bg-gray-100 my-2"></div>

                                    <!-- 1.5 Dashboard Admin Banner (Only if admin) -->
                                    <template v-if="isAdmin">
                                        <Link
                                            :href="route('admin.dashboard')"
                                            @click="isUserMenuOpen = false"
                                            class="relative overflow-hidden py-3 px-4 bg-white rounded-xl border border-gray-200 hover:border-indigo-400 transition-all cursor-pointer group shadow-sm hover:shadow-md my-1 block"
                                        >
                                            <!-- Ilustrasi (Ditempatkan di sudut kanan) -->
                                            <div class="absolute -right-2 bottom-0 h-full w-28 opacity-90 group-hover:opacity-100 transition-all duration-300 pointer-events-none flex items-end">
                                                <div class="w-full h-full flex items-center justify-end pr-4 text-[#0A2540] opacity-10 group-hover:opacity-20 transition-opacity">
                                                    <Shield class="text-5xl" />
                                                </div>
                                            </div>

                                            <!-- Konten Teks -->
                                            <div class="relative z-10 w-3/4 pr-2">
                                                <h3 class="text-sm font-bold text-[#0A2540] group-hover:text-indigo-600 transition-colors">
                                                    Dashboard Admin
                                                </h3>
                                                <p class="text-[11px] text-gray-500 leading-snug mt-1 font-normal">
                                                    Kelola sistem, pengguna, dan validasi seluruh aset penyewaan.
                                                </p>
                                            </div>
                                        </Link>
                                        <div class="h-px bg-gray-100 my-2"></div>
                                    </template>

                                    <!-- 2. Mulai Sewakan Aset Card Banner (Only if NOT owner and not pending and not admin) -->
                                    <template v-if="!isVerifiedOwner && !isPendingOwner && !isAdmin">
                                        <Link
                                            :href="route('owner.register')"
                                            @click="isUserMenuOpen = false"
                                            class="relative overflow-hidden py-3 px-4 bg-white rounded-xl border border-gray-200 hover:border-amber-400 transition-all cursor-pointer group shadow-sm hover:shadow-md my-1 block"
                                        >
                                            <!-- Ilustrasi SVG (Ditempatkan di sudut kanan) -->
                                            <div class="absolute -right-2 bottom-0 h-full w-28 opacity-90 group-hover:opacity-100 transition-all duration-300 pointer-events-none flex items-end">
                                                <NoImageIcon class="w-full object-contain object-bottom drop-shadow-sm group-hover:scale-105 transition-transform" />
                                            </div>

                                            <!-- Konten Teks -->
                                            <div class="relative z-10 w-2/3 pr-2">
                                                <h3 class="text-sm font-bold text-[#0A2540] group-hover:text-amber-600 transition-colors">
                                                    Mulai Sewakan Aset
                                                </h3>
                                                <p class="text-[11px] text-gray-500 leading-snug mt-1 font-normal">
                                                    Maksimalkan potensi aset Anda dan mulai hasilkan pendapatan tambahan.
                                                </p>
                                            </div>
                                        </Link>
                                        <div class="h-px bg-gray-100 my-2"></div>
                                    </template>

                                    <!-- 2.5 Cek Status Verifikasi Banner (If pending or rejected) -->
                                    <template v-if="isPendingOwner">
                                        <Link
                                            :href="route('owner.verification')"
                                            @click="isUserMenuOpen = false"
                                            class="relative overflow-hidden py-3 px-4 bg-white rounded-xl border border-gray-200 hover:border-amber-400 transition-all cursor-pointer group shadow-sm hover:shadow-md my-1 block"
                                        >
                                            <div class="absolute -right-2 bottom-0 h-full w-28 opacity-90 group-hover:opacity-100 transition-all duration-300 pointer-events-none flex items-end">
                                                <NoImageIcon class="w-full object-contain object-bottom drop-shadow-sm group-hover:scale-105 transition-transform" />
                                            </div>

                                            <div class="relative z-10 w-2/3 pr-2">
                                                <h3 class="text-sm font-bold text-[#0A2540] group-hover:text-amber-600 transition-colors">
                                                    Status Verifikasi
                                                </h3>
                                                <p class="text-[11px] text-gray-500 leading-snug mt-1 font-normal">
                                                    Cek status pengajuan akun Owner Anda saat ini.
                                                </p>
                                            </div>
                                        </Link>
                                        <div class="h-px bg-gray-100 my-2"></div>
                                    </template>

                                    <!-- 3. Option to go to Dashboard IF user has owner_profile -->
                                    <template v-if="isVerifiedOwner">
                                        <div class="h-px bg-gray-100 my-2"></div>
                                        <Link
                                            :href="route('owner.dashboard')"
                                            @click="isUserMenuOpen = false"
                                            class="relative overflow-hidden py-3 px-4 bg-white rounded-xl border border-gray-200 hover:border-amber-400 transition-all cursor-pointer group shadow-sm hover:shadow-md my-1 block"
                                        >
                                            <!-- Ilustrasi (Ditempatkan di sudut kanan) -->
                                            <div class="absolute -right-2 bottom-0 h-full w-28 opacity-90 group-hover:opacity-100 transition-all duration-300 pointer-events-none flex items-end">
                                                <div class="w-full h-full flex items-center justify-end pr-4 text-[#0A2540] opacity-10 group-hover:opacity-20 transition-opacity">
                                                    <PieChart class="text-5xl" />
                                                </div>
                                            </div>

                                            <!-- Konten Teks -->
                                            <div class="relative z-10 w-3/4 pr-2">
                                                <h3 class="text-sm font-bold text-[#0A2540] group-hover:text-amber-600 transition-colors">
                                                    Dashboard Owner
                                                </h3>
                                                <p class="text-[11px] text-gray-500 leading-snug mt-1 font-normal">
                                                    Kelola aset, pantau penyewaan, dan lihat pendapatan Anda.
                                                </p>
                                            </div>
                                        </Link>
                                        <div class="h-px bg-gray-100 my-2"></div>
                                    </template>

                                    <!-- 4. Footer: Logout -->
                                    <div class="pt-1">
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            @click="isUserMenuOpen = false"
                                            class="w-full text-left text-sm font-bold text-red-600 hover:text-red-700 py-1.5 transition flex items-center gap-2.5"
                                        >
                                            <LogOut class="text-xs" />
                                            Keluar
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </template>

                        <!-- Login Button if not logged in -->
                        <template v-else>
                            <button
                                @click="openAuthModal()"
                                class="ml-1 px-5 py-2 bg-primary hover:bg-[#e6ad00] text-secondary text-xs font-bold rounded transition-all shadow-sm"
                            >
                                Masuk
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Sub Navbar Filter (Sticky) -->
        <div class="hidden md:block absolute w-full left-0 -z-10 top-full pointer-events-none">
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="-translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="-translate-y-full"
            >
                <div v-if="isScrolled && isHome" class="w-full bg-white shadow-sm border-b border-[#6C757D]/10 pointer-events-auto pb-2">
                    <StickySubNavSearch />
                </div>
            </Transition>
        </div>
    </nav>
</template>
