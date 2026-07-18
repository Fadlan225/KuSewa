<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';

defineProps({
    transparentNavbar: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-[#000000]">
        <Head title="KuSewa" />
        <Navbar :transparent="transparentNavbar" />

        <main :class="{ 'pt-16': !transparentNavbar }">
            <slot />
        </main>
    </div>
</template>
