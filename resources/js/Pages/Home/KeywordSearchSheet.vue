<script setup>
import { ref } from 'vue';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import BottomSheet from '@/Components/UI/BottomSheet.vue';

const { isKeywordSheetOpen } = useHomeSearch();

const recentSearches = ref([
    'Villa di Bali',
    'Gudang Jakarta Selatan',
    'Kamera DSLR'
]);

const recommended = ref([
    { title: 'Apartemen Pusat Kota', icon: 'fa-solid fa-building', bg: 'bg-blue-100', color: 'text-blue-600' },
    { title: 'Mobil Keluarga', icon: 'fa-solid fa-car', bg: 'bg-green-100', color: 'text-green-600' },
    { title: 'Kamera Mirrorless', icon: 'fa-solid fa-camera', bg: 'bg-purple-100', color: 'text-purple-600' }
]);

const trending = ref([
    'Ruang Kantor',
    'Tenda Camping',
    'PS5'
]);

const keywordQueryLocal = ref('');

const handleSearch = () => {
    if (keywordQueryLocal.value.trim()) {
        console.log('Searching for:', keywordQueryLocal.value);
        isKeywordSheetOpen.value = false;
        // Logic pencarian asli:
        // router.get('/search', { q: keywordQueryLocal.value });
    }
};

const applySearch = (text) => {
    keywordQueryLocal.value = text;
    handleSearch();
};
</script>

<template>
    <BottomSheet v-model="isKeywordSheetOpen" heightClass="h-[95vh]">
        <template #header-content>
            <div class="flex-1 mr-3 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#6C757D] text-xs"></i>
                <input
                    v-model="keywordQueryLocal"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Mau sewa apa hari ini?"
                    class="w-full bg-[#F8F9FA] text-[#0A2540] text-xs font-medium rounded-full pl-9 pr-4 py-2 border border-[#6C757D]/20 focus:outline-none focus:bg-white focus:border-[#0A2540] focus:ring-1 focus:ring-[#0A2540] transition-all"
                    autofocus
                />
            </div>
        </template>
        <div class="px-5 overflow-y-auto h-full pb-20 hide-scrollbar space-y-6 mt-4">
            
            <!-- Riwayat Pencarian -->
            <div v-if="recentSearches.length > 0">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-[#0A2540]">Riwayat Pencarian</h3>
                    <button @click="recentSearches = []" class="text-[11px] font-bold text-[#6C757D] hover:text-[#0A2540] underline decoration-[#6C757D]/30 underline-offset-2">Hapus</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div v-for="(item, index) in recentSearches" :key="index" @click="applySearch(item)" class="bg-white border border-[#6C757D]/20 rounded-full px-3 py-1.5 flex items-center gap-2 shadow-sm cursor-pointer hover:bg-gray-50 transition active:scale-95">
                        <i class="fa-solid fa-clock-rotate-left text-[#6C757D] text-[10px]"></i>
                        <span class="text-xs font-medium text-[#0A2540]">{{ item }}</span>
                    </div>
                </div>
            </div>

            <!-- Anda Mungkin Suka -->
            <div>
                <h3 class="text-sm font-bold text-[#0A2540] mb-3">Anda Mungkin Suka</h3>
                <div class="space-y-3">
                    <div v-for="(item, index) in recommended" :key="index" @click="applySearch(item.title)" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white hover:shadow-sm border border-transparent hover:border-[#6C757D]/10 cursor-pointer transition active:scale-95">
                        <div :class="`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${item.bg}`">
                            <i :class="`${item.icon} ${item.color} text-sm`"></i>
                        </div>
                        <div class="flex-1 border-b border-[#6C757D]/10 pb-2">
                            <h4 class="font-bold text-sm text-[#0A2540]">{{ item.title }}</h4>
                        </div>
                        <i class="fa-solid fa-arrow-right -rotate-45 text-[#6C757D] text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Trending -->
            <div>
                <h3 class="text-sm font-bold text-[#0A2540] mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-fire text-orange-500"></i> Trending
                </h3>
                <div class="flex flex-wrap gap-2">
                    <div v-for="(item, index) in trending" :key="index" @click="applySearch(item)" class="bg-white border border-orange-200 rounded-full px-3 py-1.5 flex items-center gap-2 shadow-sm cursor-pointer hover:bg-orange-50 transition active:scale-95">
                        <i class="fa-solid fa-arrow-trend-up text-orange-500 text-[10px]"></i>
                        <span class="text-xs font-medium text-orange-700">{{ item }}</span>
                    </div>
                </div>
            </div>

        </div>
    </BottomSheet>
</template>
