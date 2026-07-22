<script setup>
import { Head} from '@inertiajs/vue3';

import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import Bottombar from '@/Components/Bottombar.vue';
import GlobalLoading from "@/Components/GlobalLoading.vue";

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

        <!-- <Footer /> -->
    </div>
</template>
