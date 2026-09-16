<script setup>
import { ref, watch } from 'vue';
import { Search } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

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
</script>

<template>
    <div class="w-full bg-white py-12 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 tracking-tight">Hai, ada yang bisa dibantu?</h1>
            
            <div class="relative w-full max-w-xl">
                <form @submit.prevent="handleSearch" class="flex shadow-sm">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="text-gray-400 w-5 h-5" />
                        </div>
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 border-r-0 rounded-l-md text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition-all bg-white"
                            placeholder="Masukkan kata pencarian di sini"
                            @focus="searchQuery.length >= 3 && (showDropdown = true)"
                        >
                    </div>
                    <button 
                        type="submit"
                        class="px-6 py-3 bg-[#FFC000] hover:bg-yellow-400 text-[#0A2540] font-bold text-sm md:text-base rounded-r-md transition-colors shrink-0"
                    >
                        Cari
                    </button>
                </form>

                <!-- Search Results Dropdown -->
                <div v-if="showDropdown && searchQuery.length >= 3" class="absolute mt-2 w-full bg-white rounded-lg shadow-xl border border-gray-100 max-h-96 overflow-y-auto z-[100]">
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
            </div>

            <!-- Overlay for clicking outside -->
            <div v-if="showDropdown" class="fixed inset-0 z-[90]" @click="showDropdown = false"></div>
        </div>
    </div>
</template>
