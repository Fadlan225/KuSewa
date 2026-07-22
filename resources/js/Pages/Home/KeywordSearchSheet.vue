<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import BottomSheet from '@/Components/UI/BottomSheet.vue';

const page = usePage();

const {
    keywordQuery,
    isKeywordSheetOpen,
    suggestions,
    isLoadingSuggestions,
    fetchSuggestions,
    performSearch,
} = useHomeSearch();

// Data real dari props controller
const searchHistory = ref([...(page.props.searchHistory || [])]);

// Keep synced if page props change
watch(() => page.props.searchHistory, (newVal) => {
    searchHistory.value = [...(newVal || [])];
});
const trending = computed(() => page.props.trending || []);

// Watch keyword untuk live suggestions
watch(keywordQuery, (val) => {
    fetchSuggestions(val);
});

const showSuggestions = computed(() => keywordQuery.value.length >= 1 && (suggestions.value.length > 0 || isLoadingSuggestions.value));

const handleSearch = () => {
    isKeywordSheetOpen.value = false;
    performSearch();
};

const applySearch = (text) => {
    keywordQuery.value = text;
    isKeywordSheetOpen.value = false;
    performSearch();
};

const inputRef = ref(null);
watch(isKeywordSheetOpen, async (open) => {
    if (open) {
        await nextTick();
        inputRef.value?.focus();
    }
});

function getXsrfToken() {
    const match = document.cookie.match(new RegExp('(^|;\\s*)XSRF-TOKEN=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : '';
}

const deleteHistoryItem = (item) => {
    // Optimistic UI Update (Instant)
    searchHistory.value = searchHistory.value.filter(k => k !== item);

    fetch(route('search.deleteKeyword'), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getXsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ keyword: item }),
    }).catch(e => console.error(e));
};

const clearAllHistory = () => {
    // Optimistic UI Update (Instant)
    searchHistory.value = [];

    fetch(route('search.clear'), {
        method: 'DELETE',
        headers: {
            'X-XSRF-TOKEN': getXsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    }).catch(e => console.error(e));
};
</script>

<template>
    <BottomSheet v-model="isKeywordSheetOpen" heightClass="h-[95vh]">
        <template #header-content>
            <div class="flex-1 mr-3 relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#6C757D] text-xs z-10"></i>
                <input
                    ref="inputRef"
                    v-model="keywordQuery"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Mau sewa apa hari ini?"
                    autocomplete="off"
                    class="w-full bg-[#F8F9FA] text-[#0A2540] text-xs font-medium rounded-full pl-9 pr-10 py-2 border border-[#6C757D]/20 focus:outline-none focus:bg-white focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540] transition-all"
                />
                <button
                    v-if="keywordQuery"
                    @click="keywordQuery = ''; inputRef?.focus()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-[#6C757D] hover:text-[#0A2540] transition z-10 w-6 h-6 flex items-center justify-center rounded-full"
                >
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            </div>
        </template>

        <!-- ══ SUGGESTIONS OVERLAY ══
             Ditempel langsung di atas content area, sehingga terlihat di atas keyboard.
             Menggunakan absolute+z-index supaya tidak tergeser keyboard. -->
        <div class="relative w-full h-full">

            <!-- Live Suggestions — muncul di atas segalanya, sticky top -->
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="showSuggestions"
                    class="absolute inset-x-0 top-0 z-30 bg-[#F8F9FA] border-b border-[#6C757D]/10 px-5 py-2 overflow-y-auto max-h-full"
                >
                    <div v-if="isLoadingSuggestions && suggestions.length === 0" class="flex items-center gap-2 py-3 text-[#6C757D]">
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        <span class="text-sm">Mencari...</span>
                    </div>
                    <div v-else class="space-y-0.5">
                        <div
                            v-for="s in suggestions" :key="s.text"
                            @click="applySearch(s.text)"
                            class="flex items-center gap-3 px-2 py-3 rounded-xl hover:bg-white active:bg-white cursor-pointer transition"
                        >
                            <div class="w-8 h-8 rounded-full bg-white border border-[#6C757D]/10 text-[#6C757D] flex items-center justify-center flex-shrink-0 shadow-sm">
                                <i :class="s.icon + ' text-xs'"></i>
                            </div>
                            <span class="text-sm font-medium text-[#0A2540] flex-1 truncate">{{ s.text }}</span>
                            <span class="text-[10px] text-[#6C757D] bg-gray-100 px-2 py-0.5 rounded-full whitespace-nowrap flex-shrink-0">
                                {{ s.type === 'history' ? 'riwayat' : s.type === 'category' ? 'kategori' : s.type === 'location' ? 'lokasi' : s.type === 'popular' ? 'populer' : 'aset' }}
                            </span>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Default content (riwayat & trending) -->
            <div class="px-5 overflow-y-auto h-full pb-24 hide-scrollbar space-y-6 pt-4">

                <!-- Riwayat Pencarian (hanya jika login & punya riwayat) -->
                <div v-if="page.props.auth.user && searchHistory.length > 0">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-[#0A2540]">Riwayat Pencarian</h3>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <div
                            v-for="item in searchHistory" :key="item"
                            class="bg-white border border-[#6C757D]/20 rounded-full pl-3 pr-1 py-1 flex items-center gap-2 shadow-sm transition group hover:bg-gray-50"
                        >
                            <div @click="applySearch(item)" class="flex items-center gap-2 cursor-pointer active:scale-95 transition">
                                <i class="fa-solid fa-clock-rotate-left text-[#6C757D] text-[10px]"></i>
                                <span class="text-xs font-medium text-[#0A2540]">{{ item }}</span>
                            </div>
                            <button @click.stop="deleteHistoryItem(item)" class="w-6 h-6 flex items-center justify-center rounded-full text-[#6C757D] hover:bg-red-50 hover:text-red-500 transition ml-1 active:scale-95">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-start">
                        <button @click="clearAllHistory" class="text-[11px] font-bold text-[#6C757D] hover:text-red-500 transition underline decoration-transparent hover:decoration-red-500 underline-offset-2 active:scale-95">
                            Hapus Semua Riwayat
                        </button>
                    </div>
                </div>

                <!-- Populer Minggu Ini -->
                <div v-if="trending.length > 0">
                    <h3 class="text-sm font-bold text-[#0A2540] mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-orange-500"></i> Populer Minggu Ini
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="item in trending" :key="item"
                            @click="applySearch(item)"
                            class="bg-white border border-orange-200 rounded-full px-3 py-1.5 flex items-center gap-2 shadow-sm cursor-pointer hover:bg-orange-50 transition active:scale-95"
                        >
                            <i class="fa-solid fa-arrow-trend-up text-orange-500 text-[10px]"></i>
                            <span class="text-xs font-medium text-orange-700">{{ item }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fallback kosong -->
                <div
                    v-if="(!page.props.auth.user || searchHistory.length === 0) && trending.length === 0"
                    class="flex flex-col items-center py-12 text-center"
                >
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-2xl text-[#6C757D]/40"></i>
                    </div>
                    <p class="text-sm text-[#6C757D]">Ketik sesuatu untuk mulai mencari</p>
                </div>

            </div>
        </div>

        <template #footer>
            <div class="absolute bottom-0 w-full bg-[#F8F9FA] border-t border-[#6C757D]/10 p-4 z-20">
                <button @click="handleSearch" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold w-full py-3.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-[15px]">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Cari
                </button>
            </div>
        </template>
    </BottomSheet>
</template>
