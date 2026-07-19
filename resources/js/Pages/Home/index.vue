<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import HeroSection from './HeroSection.vue';
import MobileSearchSheet from './MobileSearchSheet.vue';
import KeywordSearchSheet from './KeywordSearchSheet.vue';
import LokasiSearchSheet from './LokasiSearchSheet.vue';
import AssetList from './AssetList.vue';

const isLoading = ref(true);

onMounted(() => {
    // Simulasi loading data dari API (1.5 detik)
    setTimeout(() => {
        isLoading.value = false;
    }, 1500);
});
</script>

<template>
    <AppLayout transparentNavbar>
        <Head title="Beranda - KuSewa" />

        <!-- SKELETON LOADING (Tampil saat isLoading = true) -->
        <div v-if="isLoading" class="w-full h-[450px] lg:h-[500px] bg-[#1a1f2e] animate-pulse relative">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative z-10 flex flex-col justify-center h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="max-w-3xl mt-8 md:mt-0 flex flex-col items-center md:items-start w-full">
                    <!-- Title Skeleton -->
                    <div class="h-10 sm:h-12 md:h-14 bg-white/20 rounded-xl w-3/4 md:w-2/3 mb-3"></div>
                    <div class="h-10 sm:h-12 md:h-14 bg-white/20 rounded-xl w-1/2 md:w-1/3 mb-6"></div>

                    <!-- Subtitle Skeleton -->
                    <div class="h-4 sm:h-5 bg-white/10 rounded-lg w-full max-w-xl mb-2"></div>
                    <div class="h-4 sm:h-5 bg-white/10 rounded-lg w-4/5 max-w-md mb-8"></div>
                </div>

                <!-- Desktop Search Bar Skeleton -->
                <div class="hidden md:flex mt-8 flex-col items-center w-full max-w-[850px] relative z-50 mx-auto">
                    <div class="bg-white/10 rounded-full h-16 w-full shadow-xl border border-white/5"></div>
                </div>

                <!-- Mobile Search Trigger Skeleton -->
                <div class="md:hidden mt-6 w-full px-2">
                    <div class="w-full h-[52px] bg-white/10 rounded-full shadow-lg"></div>
                </div>
            </div>
        </div>

        <!-- ACTUAL CONTENT (Tampil setelah loading selesai) -->
        <template v-else>
            <HeroSection/>
            <!-- Mobile Bottom Sheets -->
            <MobileSearchSheet />
            <KeywordSearchSheet />
            <LokasiSearchSheet />
            <main class="w-full">
                <AssetList />
            </main>
        </template>

    </AppLayout>
</template>

<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
