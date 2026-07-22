<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AppLayout>
        <Head title="Aktivitas - KuSewa" />
        <div class="pt-24 pb-12 px-6 max-w-7xl mx-auto min-h-screen">
            <h1 class="text-2xl font-bold text-[#0A2540] mb-4">Aktivitas Anda</h1>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-sm">Belum ada aktivitas saat ini.</p>
            </div>
        </div>
    </AppLayout>
</template>
