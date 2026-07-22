<script setup>
import { useHomeSearch } from '@/Composables/useHomeSearch';
import DesktopSearchBar from '@/Components/UI/DesktopSearchBar.vue';

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
                        Semua Kebutuhan <span class="text-[#FFC000]">Sewa, </span><br class="hidden sm:block">
                        <span class="text-[#FFC000]">Dalam</span> Satu Platform
                    </h1>
                    <p class="mt-2 sm:mt-4 text-xs sm:text-sm md:text-base lg:text-lg text-white/90 font-medium max-w-xl leading-relaxed mx-auto md:mx-0">
                        Temukan lahan, gedung, gudang, baliho, hunian, dan berbagai aset lainnya dengan mudah.
                    </p>
                </div>

                <!-- ==================== DESKTOP SEARCH BAR ==================== -->
                <DesktopSearchBar class="mt-6 lg:mt-8" />

                <!-- ==================== MOBILE SEARCH TRIGGER ==================== -->
                <div class="md:hidden mt-4 sm:mt-6 w-full max-w-sm mx-auto">
                    <div class="relative w-full flex items-center">
                        <!-- Ikon Kaca Pembesar -->
                        <i class="fa-solid fa-magnifying-glass absolute left-4 text-[#6C757D] text-xs z-10"></i>

                        <!-- Fake Input that opens Keyword Sheet -->
                        <div
                            @click="isKeywordSheetOpen = true"
                            class="w-full bg-white text-xs font-medium rounded-full pl-10 pr-12 py-3.5 shadow-lg active:scale-[0.98] transition-transform cursor-pointer flex items-center"
                        >
                            <span class="truncate" :class="keywordQuery ? 'text-[#0A2540]' : 'text-[#6C757D]'">{{ keywordQuery || 'Mau sewa apa hari ini?' }}</span>
                        </div>

                        <!-- Filter Button that opens Filter Sheet (Kanan Dalam) -->
                        <button
                            @click.stop="isMobileSearchOpen = true"
                            class="absolute right-1.5 w-8 h-8 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-full flex items-center justify-center active:scale-90 transition-transform shadow-sm"
                        >
                            <i class="fa-solid fa-sliders text-[11px] font-bold"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
