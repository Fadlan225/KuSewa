<script setup>
import { Head } from '@inertiajs/vue3';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import AccountHistory from './Partials/AccountHistory.vue';

defineProps({
    accountActivities: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Riwayat Akun" />

    <div class="md:hidden min-h-screen bg-[#F8F9FA] pb-24 text-[#333333] font-sans">
        <DetailNavbar title="Riwayat Akun" backUrl="/profile" :forceBackUrl="true" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <div class="px-4 py-6">
            <AccountHistory :accountActivities="accountActivities" />
        </div>
    </div>
</template>
