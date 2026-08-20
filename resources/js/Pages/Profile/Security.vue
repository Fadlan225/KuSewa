<script setup>
import { Head } from '@inertiajs/vue3';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import SecurityForms from './Partials/SecurityForms.vue';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Keamanan" />

    <!-- MOBILE VIEW (Standalone layout) -->
    <div class="md:hidden min-h-screen bg-white pb-24 text-[#333333] font-sans">
        <DetailNavbar title="Keamanan" backUrl="/profile" :forceBackUrl="true" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <main class="max-w-3xl mx-auto py-6 px-5 sm:px-6 lg:px-8 space-y-8">
            <!-- Forms -->
            <SecurityForms :user="user" />
        </main>
    </div>

    <!-- DESKTOP VIEW (Redirect or empty since it's handled by Edit.vue) -->
    <div class="hidden md:block">
        <p class="p-6 text-center text-gray-500">Tampilan ini khusus mobile.</p>
    </div>
</template>
