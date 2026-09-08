<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BannedIcon from '@/Components/ui/icon/BannedIcon.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <Head title="Akun Ditangguhkan" />

        <div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-md w-full text-center space-y-2 p-6 sm:p-12  shadow-[0_8px_30px_rgb(0,0,0,0.04)]">

                <!-- Icon -->
                <div class="w-48 h-48 mx-auto text-primary opacity-90 drop-shadow-sm">
                    <BannedIcon />
                </div>

                <!-- Text Content -->
                <div class="space-y-2">
                    <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Akses Ditangguhkan
                    </h1>

                    <div class="flex justify-center">
                        <div class="h-1 w-12 bg-primary/40 rounded-full"></div>
                    </div>

                    <div class="text-gray-600 space-y-3 px-2">
                        <p class="text-[13px] sm:text-base font-medium whitespace-nowrap">
                            Akun Anda saat ini tidak dapat digunakan.
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                            Kami mendeteksi aktivitas yang melanggar ketentuan. Hubungi <a href="#" class="font-semibold text-primary hover:text-amber-600 transition-colors underline decoration-2 underline-offset-4">layanan bantuan</a> untuk informasi lebih lanjut.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
