<script setup>
import { Head } from '@inertiajs/vue3';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';

</script>

<template>
    <Head title="Ubah Kata Sandi" />

    <!-- MOBILE VIEW (Standalone layout) -->
    <div class="md:hidden min-h-screen bg-white pb-24 text-[#333333] font-sans">
        <DetailNavbar title="Ubah Kata Sandi" backUrl="/profile/security" :forceBackUrl="true" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <main class="max-w-3xl mx-auto py-6 px-5 sm:px-6 lg:px-8 space-y-8">
            <div class="p-5 sm:p-6 border border-gray-200/80 rounded-xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white">
                <UpdatePasswordForm />
            </div>
        </main>
    </div>

    <!-- DESKTOP VIEW -->
    <div class="hidden md:block">
        <p class="p-6 text-center text-gray-500">Tampilan ini khusus mobile. Silakan buka lewat tab keamanan profil di Desktop.</p>
    </div>
</template>
