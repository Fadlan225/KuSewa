<script setup>
import HelpCenterNavbar from '@/Components/HelpCenterNavbar.vue';
import HelpCenterBottombar from '@/Components/HelpCenterBottombar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    tab: {
        type: String,
        default: 'penyewa' // penyewa, pemilik, umum
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans flex flex-col pb-20 md:pb-0">
        <HelpCenterNavbar :tab="tab" :breadcrumbs="breadcrumbs" />

        <!-- Main Content Slot -->
        <main class="w-full flex-grow">
            <slot />
        </main>

        <Footer />
        <HelpCenterBottombar :tab="tab" />
    </div>
</template>
