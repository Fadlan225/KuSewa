<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch, defineAsyncComponent } from 'vue';

import HeroSection from './HeroSection.vue';
import AssetList from './Assets/AssetList.vue';
import { useHomeSearch } from '@/Composables/useHomeSearch';

// ── Lazy load bottom sheets: JS tidak di-parse sampai pertama kali dibuka ──
// Hemat ~30KB JS parse time + eliminasi render overhead DOM saat init
const MobileSearchSheet  = defineAsyncComponent(() => import('./MobileSearchSheet.vue'));
const KeywordSearchSheet = defineAsyncComponent(() => import('./KeywordSearchSheet.vue'));
const LokasiSearchSheet  = defineAsyncComponent(() => import('./LokasiSearchSheet.vue'));

// State dari composable untuk v-if conditional mounting
const { isMobileSearchOpen, isKeywordSheetOpen, isLokasiFullScreen } = useHomeSearch();

// Setelah pertama kali dibuka, tetap mounted (tidak re-destroy saat ditutup)
// Ini mencegah reset state dan jank saat membuka kembali
const mobileSheetMounted  = ref(false);
const keywordSheetMounted = ref(false);
const lokasiSheetMounted  = ref(false);

watch(isMobileSearchOpen, (v) => { if (v) mobileSheetMounted.value = true; });
watch(isKeywordSheetOpen, (v) => { if (v) keywordSheetMounted.value = true; });
watch(isLokasiFullScreen, (v) => { if (v) lokasiSheetMounted.value = true; });

const props = defineProps({
    categories: { type: Array, default: () => [] }
});
</script>

<template>
    <AppLayout transparentNavbar>
        <Head title="Beranda - KuSewa" />

        <HeroSection />

        <!-- Bottom Sheets — hanya di-mount saat pertama kali dibuka -->
        <Suspense><MobileSearchSheet  v-if="mobileSheetMounted" /></Suspense>
        <Suspense><KeywordSearchSheet v-if="keywordSheetMounted" /></Suspense>
        <Suspense><LokasiSearchSheet  v-if="lokasiSheetMounted" /></Suspense>

        <main class="w-full max-w-7xl mx-auto pb-24">
            <AssetList :categories="props.categories" />
        </main>

    </AppLayout>
</template>

<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
