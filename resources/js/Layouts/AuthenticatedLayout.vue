<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import Bottombar from '@/Components/Bottombar.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-[#000000]">
        <Head title="KitaSewa" />
        <Navbar />

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="pt-16">
            <slot />
        </main>

        <Bottombar />
        <Footer />
    </div>
</template>
