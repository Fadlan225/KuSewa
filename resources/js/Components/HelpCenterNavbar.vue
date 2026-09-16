<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Search, X, ChevronRight } from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tab: {
        type: String,
        default: 'penyewa'
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showDropdown = ref(false);

const performSearch = async () => {
    if (!searchQuery.value || searchQuery.value.length < 3) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    isSearching.value = true;
    showDropdown.value = true;

    try {
        const response = await fetch(`/bantuan/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        searchResults.value = data;
    } catch (e) {
        console.error(e);
    } finally {
        isSearching.value = false;
    }
};

let searchTimeout;
watch(searchQuery, (newVal) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 500);
});

const goToArticle = (slug) => {
    showDropdown.value = false;
    searchQuery.value = '';
    router.visit(route('bantuan.article', slug));
};

const handleSearch = () => {
    performSearch();
};

const isScrolled = ref(false);
let lastScrollY = typeof window !== 'undefined' ? window.scrollY : 0;

const handleScroll = () => {
    isScrolled.value = window.scrollY > 60;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:gap-6">
                
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
                            <Link :href="route('bantuan.index')" class="flex items-center gap-2">
                                <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-6 w-auto object-contain" />
                                <div class="h-4 w-px bg-gray-300"></div>
                                <span class="font-bold text-sm text-[#FFC000]">Pusat Bantuan</span>
                            </Link>
                        </div>

                        <!-- KONDISI 2: Sudah di-scroll -> Tampilkan Mini Search Bar -->
                        <div v-else key="mobile-search" class="w-full py-1 flex items-center gap-2 relative">
                            <form @submit.prevent="handleSearch" class="w-full flex items-center relative z-20">
                                <div class="relative flex-grow flex items-center">
                                    <Search class="absolute left-4 text-[#6C757D] w-4 h-4 z-10 pointer-events-none" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        class="w-full bg-[#F8F9FA] text-[#0A2540] text-xs font-medium rounded-l-full pl-10 pr-9 py-2.5 border border-gray-200/80 border-r-0 focus:outline-none focus:bg-white focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all"
                                        placeholder="Cari panduan..."
                                        @focus="searchQuery.length >= 3 && (showDropdown = true)"
                                    />
                                    <button
                                        v-if="searchQuery"
                                        type="button"
                                        @click.stop="searchQuery = ''; showDropdown = false;"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-[#6C757D] hover:text-[#0A2540] transition-colors z-30"
                                    >
                                        <X class="text-xs" />
                                    </button>
                                </div>
                                <button 
                                    type="submit"
                                    class="flex-shrink-0 px-5 h-[38px] bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold text-xs rounded-r-full transition-colors"
                                >
                                    Cari
                                </button>
                            </form>

                            <!-- Mobile Dropdown -->
                            <div v-if="showDropdown && searchQuery.length >= 3" class="absolute top-[110%] left-0 w-full bg-white rounded-lg shadow-xl border border-gray-100 max-h-96 overflow-y-auto z-[100]">
                                <div v-if="isSearching" class="p-4 space-y-4">
                                    <div v-for="i in 3" :key="i" class="animate-pulse flex space-x-4">
                                        <div class="flex-1 space-y-2 py-1">
                                            <div class="h-4 bg-gray-200 w-3/4"></div>
                                            <div class="h-3 bg-gray-200 w-5/6"></div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="searchResults.length > 0" class="py-2">
                                    <button
                                        v-for="item in searchResults"
                                        :key="item.id"
                                        @click="goToArticle(item.slug)"
                                        class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors group"
                                    >
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-[#FFC000] transition-colors line-clamp-1">{{ item.title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <span>{{ item.category?.parent?.name ?? item.category?.name }}</span>
                                            <span v-if="item.category?.parent" class="text-gray-300">•</span>
                                            <span v-if="item.category?.parent">{{ item.category?.name }}</span>
                                        </div>
                                    </button>
                                </div>
                                <div v-else class="p-6 text-center text-gray-500 text-sm">
                                    Tidak ada panduan yang cocok.
                                </div>
                            </div>

                            <!-- Mobile Overlay -->
                            <div v-if="showDropdown" class="fixed inset-0 z-40" @click="showDropdown = false"></div>
                        </div>
                    </Transition>
                </div>

                <!-- ==================== AREA DESKTOP ==================== -->
                <!-- Logo & Title & Breadcrumbs -->
                <div class="hidden md:flex items-center gap-2 shrink-0">
                    <Link href="/" class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto" src="/kitasewa-logo.png" alt="KitaSewa Logo">
                    </Link>
                    <div class="h-6 w-px bg-gray-300 mx-2"></div>
                    <Link :href="route('bantuan.index')" class="text-xl font-bold text-[#FFC000] hover:text-[#e6ad00] transition-colors">
                        Pusat Bantuan
                    </Link>
                    

                </div>

                <!-- Desktop Search Bar -->
                <div class="hidden md:block flex-1 max-w-2xl relative">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="text-gray-400 w-5 h-5" />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition-all sm:text-sm"
                            placeholder="Masukkan topik yang Anda cari..."
                            @focus="searchQuery.length >= 3 && (showDropdown = true)"
                        >
                    </div>

                    <!-- Search Results Dropdown -->
                    <div v-if="showDropdown && searchQuery.length >= 3" class="absolute mt-1 w-full bg-white rounded-lg shadow-xl border border-gray-100 max-h-96 overflow-y-auto z-[100]">
                        <!-- Loading Skeleton -->
                        <div v-if="isSearching" class="p-4 space-y-4">
                            <div v-for="i in 3" :key="i" class="animate-pulse flex space-x-4">
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-4 bg-gray-200 w-3/4"></div>
                                    <div class="h-3 bg-gray-200 w-5/6"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Results -->
                        <div v-else-if="searchResults.length > 0" class="py-2">
                            <button
                                v-for="item in searchResults"
                                :key="item.id"
                                @click="goToArticle(item.slug)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors group"
                            >
                                <div class="text-sm font-semibold text-gray-900 group-hover:text-[#FFC000] transition-colors line-clamp-1">{{ item.title }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                    <span>{{ item.category?.parent?.name ?? item.category?.name }}</span>
                                    <span v-if="item.category?.parent" class="text-gray-300">•</span>
                                    <span v-if="item.category?.parent">{{ item.category?.name }}</span>
                                </div>
                            </button>
                        </div>
                        <!-- Empty -->
                        <div v-else class="p-6 text-center text-gray-500 text-sm">
                            Tidak ada panduan yang cocok dengan pencarian Anda.
                        </div>
                    </div>

                    <!-- Overlay -->
                    <div v-if="showDropdown" class="fixed inset-0 z-[90]" @click="showDropdown = false"></div>
                </div>

                <!-- Navigation Tabs -->
                <div class="hidden md:flex items-center space-x-6">
                    <Link
                        :href="route('bantuan.index', { tab: 'penyewa' })"
                        class="text-sm font-medium transition-colors border-b-2 py-5"
                        :class="tab === 'penyewa' ? 'border-[#FFC000] text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        Penyewa Aset
                    </Link>
                    <Link
                        :href="route('bantuan.index', { tab: 'pemilik' })"
                        class="text-sm font-medium transition-colors border-b-2 py-5"
                        :class="tab === 'pemilik' ? 'border-[#FFC000] text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        Pemilik Aset
                    </Link>
                    <Link
                        :href="route('bantuan.index', { tab: 'umum' })"
                        class="text-sm font-medium transition-colors border-b-2 py-5"
                        :class="tab === 'umum' ? 'border-[#FFC000] text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        Info Umum
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>
