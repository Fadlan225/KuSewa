<script setup>
import { Search, Sliders } from 'lucide-vue-next';
import { useHomeSearch } from '@/Composables/useHomeSearch';
import DesktopSearchBar from '@/Components/ui/DesktopSearchBar.vue';
import AnimatedPlaceholder from '@/Components/ui/AnimatedPlaceholder.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const {
    keywordQuery,
    isMobileSearchOpen,
    isKeywordSheetOpen
} = useHomeSearch();
</script>

<template>
    <!-- 1. WRAPPER UTAMA: Berongga (padding) di mobile, kembali full-width (tanpa padding) di desktop (md:px-0 md:pt-0) -->
    <div class="w-full px-3 pt-3 pb-6 sm:px-6 md:px-0 md:pt-0 md:pb-0 relative z-[90]">

        <!-- 2. HERO CONTAINER: rounded di mobile, lurus kembali (rounded-none) di desktop -->
        <div class="relative w-full h-[360px] sm:h-[420px] md:h-[500px] lg:h-[540px] bg-cover bg-center rounded-2xl md:rounded-none overflow-hidden md:overflow-visible shadow-sm md:shadow-none transition-all duration-300" style="background-image: url('/public.png');">

            <!-- Overlay gelap -->
            <div class="absolute inset-0 bg-black/50"></div>

            <!-- Konten di dalam Hero (Teks & Search Bar tetap di tengah area max-w-7xl) -->
            <div class="relative z-10 flex flex-col justify-center h-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-8">

                <!-- Teks Judul & Subjudul -->
                <div class="max-w-3xl text-center md:text-left">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Temukan <span class="text-[#FFC000]">Aset,</span> <br>
                        <span class="text-[#FFC000]">Wujudkan</span> Rencana
                    </h1>
                    <p class="mt-3 sm:mt-4 text-xs sm:text-sm md:text-base text-white/80 font-normal max-w-xl leading-relaxed mx-auto md:mx-0 tracking-wide">
                        Butuh tempat untuk mewujudkan rencana? Temukan aset yang tepat di KitaSewa.
                    </p>
                </div>

                <!-- ==================== DESKTOP SEARCH BAR ==================== -->
                <DesktopSearchBar class="mt-6 lg:mt-8" />

                <!-- ==================== MOBILE SEARCH TRIGGER ==================== -->
                <div class="md:hidden mt-4 sm:mt-6 w-full max-w-sm mx-auto">
                    <div class="relative w-full flex items-center">
                        <!-- Ikon Kaca Pembesar -->
                        <Search class="absolute left-4 text-[#6C757D] text-xs z-10" />

                        <!-- Fake Input that opens Keyword Sheet -->
                        <div
                            @click="isKeywordSheetOpen = true"
                            class="w-full bg-white text-xs font-medium rounded-full pl-10 pr-12 py-3.5 shadow-lg hover:shadow-xl active:scale-[0.98] transition-all cursor-pointer flex items-center relative overflow-hidden group"
                            style="min-height: 44px;"
                        >
                              <span v-if="keywordQuery" class="truncate text-[#0A2540] relative z-10">{{ keywordQuery }}</span>
                            <AnimatedPlaceholder
                                v-else
                                :placeholders="page.props.dynamicPlaceholders"
                                :isFocused="false"
                                :hasValue="!!keywordQuery"
                                offsetClass="left-10"
                                class="text-[#6C757D]"
                            />
                        </div>

                        <!-- Filter Button that opens Filter Sheet (Kanan Dalam) -->
                        <div class="absolute right-1.5 shrink-0 z-20">
                            <button
                                @click.stop="isMobileSearchOpen = true"
                                class="relative w-8 h-8 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-full flex items-center justify-center hover:scale-105 hover:shadow-[0_0_10px_rgba(255,192,0,0.5)] active:scale-90 transition-all shadow-sm group-hover:rotate-12"
                            >
                                <Sliders class="text-[11px] font-bold" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
