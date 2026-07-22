<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

import HeroSection from './HeroSection.vue';
import MobileSearchSheet from './MobileSearchSheet.vue';
import KeywordSearchSheet from './KeywordSearchSheet.vue';
import LokasiSearchSheet from './LokasiSearchSheet.vue';
import AssetList from './Assets/AssetList.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <AppLayout transparentNavbar>
        <Head title="Beranda - KuSewa" />

        <HeroSection/>

        <!-- Mobile Bottom Sheets -->
        <MobileSearchSheet />
        <KeywordSearchSheet />
        <LokasiSearchSheet />

        <main class="w-full max-w-7xl mx-auto pb-24">
            <AssetList :categories="props.categories" />
        </main>

    </AppLayout>
</template>

<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
