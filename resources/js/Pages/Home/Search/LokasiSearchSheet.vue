<script setup>
import { useHomeSearch } from '@/Composables/useHomeSearch';

const {
    isLokasiFullScreen,
    closeLokasiFullScreen,
    searchQuery,
    filteredLocations,
    touchStartLokasiY,
    lokasiSheetTransform,
    onTouchStartLokasi,
    onTouchMoveLokasi,
    onTouchEndLokasi
} = useHomeSearch();
</script>

<template>
    <div>
        <!-- LOKASI FULL SCREEN (Taller Bottom Sheet) -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isLokasiFullScreen" @click="closeLokasiFullScreen" class="fixed inset-0 bg-black/60 z-[105] md:hidden"></div>
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div v-if="isLokasiFullScreen" class="fixed inset-x-0 bottom-0 z-[110] flex items-end justify-center md:hidden pointer-events-none">
                <div
                    class="relative w-full h-[90vh] bg-[#F8F9FA] flex flex-col rounded-t-[32px] shadow-[0_-10px_40px_rgba(0,0,0,0.15)] pointer-events-auto"
                    :style="{ transform: lokasiSheetTransform, transition: touchStartLokasiY === 0 ? 'transform 0.2s ease-out' : 'none' }"
                >
                    <!-- Drag Handle -->
                    <div
                        class="w-full flex justify-center pt-5 pb-5 cursor-grab active:cursor-grabbing touch-none"
                        @touchstart="onTouchStartLokasi"
                        @touchmove.prevent="onTouchMoveLokasi"
                        @touchend="onTouchEndLokasi"
                    >
                        <div class="w-12 h-1.5 bg-[#6C757D]/30 rounded-full"></div>
                    </div>

                    <div class="px-4 pb-4 border-b border-[#6C757D]/10 flex items-center gap-3 pt-2">
                        <button @click="closeLokasiFullScreen" class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm border border-[#6C757D]/10 hover:bg-gray-50 flex-shrink-0 active:scale-95 transition">
                            <i class="fa-solid fa-chevron-down text-[#0A2540] text-sm"></i>
                        </button>
                        <div class="flex-1 flex items-center gap-2 border-2 border-[#0A2540] rounded-xl p-2 px-3 bg-white">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari destinasi"
                                class="w-full bg-transparent outline-none text-[#0A2540] placeholder-[#6C757D] font-medium text-[15px]"
                                autofocus
                            >
                            <button v-if="searchQuery" @click="searchQuery = ''" class="text-[#6C757D]">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-5 hide-scrollbar">
                        <h3 class="text-xs font-bold text-[#6C757D] mb-4">Destinasi yang disarankan</h3>
                        <div class="space-y-5">
                            <div
                                v-for="item in filteredLocations"
                                :key="item.id"
                                @click="searchQuery = item.title; closeLokasiFullScreen()"
                                class="flex gap-4 items-center cursor-pointer group"
                            >
                                <div :class="`w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                    <i :class="`${item.icon} ${item.iconColor} text-lg`"></i>
                                </div>
                                <div class="border-b border-[#6C757D]/10 pb-4 w-full group-last:border-0">
                                    <h4 class="font-bold text-[#0A2540] text-[15px]">{{ item.title }}</h4>
                                    <p class="text-sm text-[#6C757D] mt-0.5 truncate">{{ item.desc }}</p>
                                </div>
                            </div>
                            <div v-if="filteredLocations.length === 0" class="text-center py-4 text-[#6C757D] font-medium text-sm">
                                Tidak ada hasil yang cocok dengan pencarian Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
