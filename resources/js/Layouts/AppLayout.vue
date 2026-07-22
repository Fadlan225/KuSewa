<script setup>
import { Head } from '@inertiajs/vue3';

import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import Bottombar from '@/Components/Bottombar.vue';
import GlobalLoading from "@/Components/GlobalLoading.vue";

// 1. IMPORT KOMPONEN FLOATING CHAT DI SINI
import FloatingChat from '@/Components/FloatingChat.vue';

defineProps({
    transparentNavbar: {
        type: Boolean,
        default: false
    },
    hideNavbar: {
        type: Boolean,
        default: false
    },
    hideBottombar: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-[#000000]">
        <Head title="KuSewa" />

        <GlobalLoading />

        <Navbar v-if="!hideNavbar" :transparent="transparentNavbar" />

        <main :class="{ 'pt-16': !transparentNavbar && !hideNavbar }">
            <slot />
        </main>

        <Bottombar v-if="!hideBottombar" />

        <Footer />

        <!-- 2. PASANG WIDGET FLOATING CHAT DI SINI (SEBELUM PENUTUP DIV) -->
        <FloatingChat />
    </div>
</template>