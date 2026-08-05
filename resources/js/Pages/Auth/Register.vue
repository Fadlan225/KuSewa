<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import AuthFlow from '@/Components/Auth/AuthFlow.vue';

const initialAuthStep = ref('email');
const initialAuthData = ref({});
provide('initialAuthStep', initialAuthStep);
provide('initialAuthData', initialAuthData);
</script>

<template>
    <div class="min-h-screen relative flex items-center justify-center overflow-hidden">
        <Head title="Daftar - KuSewa" />

        <!-- Background Image -->
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('/public.png')"
        ></div>

        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/60 to-black/40"></div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 min-h-screen flex items-center py-16">

            <!-- Absolute Top Left Logo for Desktop -->
            <Link :href="route('Home')" class="absolute top-10 left-6 lg:left-8 items-center gap-2.5 hidden md:flex z-50">
                <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-9 w-auto brightness-0 invert" />
                <span class="font-bold text-2xl text-white">
                    kusewa<span class="text-[#FFC000]">.id</span>
                </span>
            </Link>

            <div class="flex flex-col md:flex-row items-center justify-between w-full gap-10">

                <!-- Left side: Hero text -->
                <div class="flex-1 text-left hidden md:block pr-4">

                    <h1 class="text-[28px] lg:text-[34px] xl:text-[44px] font-extrabold text-white leading-tight tracking-tight mt-10 whitespace-nowrap">
                        Temukan Beragam <span class="text-[#FFC000]">Aset</span> Properti<br>
                        Dalam Satu Platform.
                    </h1>
                    <p class="mt-5 text-white/80 text-sm md:text-base lg:text-lg max-w-lg leading-relaxed">
                        Temukan lahan, gedung, gudang, baliho, hunian, dan berbagai aset lainnya dengan mudah.
                    </p>
                </div>

                <!-- Right side: Register Form Card -->
                <div class="w-full max-w-[380px] xl:max-w-[420px] flex-shrink-0">
                    <!-- Back Button (Desktop) -->
                    <Link :href="route('Home')" class="hidden md:flex items-center gap-2 text-white hover:text-[#FFC000] font-medium text-sm mb-4 transition-colors w-fit">
                        <i class="fa-solid fa-arrow-left"></i>
                        Ke Halaman Utama KuSewa
                    </Link>

                    <!-- Mobile Logo -->
                    <div class="md:hidden text-center mb-6">
                        <Link :href="route('Home')" class="inline-flex items-center gap-2">
                            <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-8 w-auto brightness-0 invert" />
                            <span class="font-bold text-xl text-white">
                                kusewa<span class="text-[#FFC000]">.id</span>
                            </span>
                        </Link>
                    </div>

                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden min-h-[500px]">
                        <AuthFlow />
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
