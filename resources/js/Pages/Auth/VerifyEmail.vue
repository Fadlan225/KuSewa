<script setup>
import { ArrowLeft } from 'lucide-vue-next';
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
        <Head title="Verifikasi Email" />

        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/public.png')"></div>

        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-transparent"></div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 min-h-screen flex items-center py-16">
            <div class="flex flex-col md:flex-row items-center justify-between w-full gap-10">

                <!-- Left side: Hero text -->
                <div class="flex-1 text-left max-w-lg hidden md:block">
                    <!-- Logo -->
                    <Link :href="route('Home')" class="flex items-center gap-2.5 mb-10 group">
                        <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-9 w-auto brightness-0 invert group-hover:scale-105 transition-transform" />
                        <span class="font-bold text-2xl text-white">
                            kusewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>

                    <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight mb-5">
                        Verifikasi <span class="text-[#FFC000]">Email.</span>
                    </h1>
                    <p class="text-white/80 text-base leading-relaxed max-w-sm">
                        Demi keamanan dan kenyamanan bertransaksi, silakan verifikasi alamat email Anda sebelum melanjutkan.
                    </p>
                </div>

                <!-- Right side: Form Card -->
                <div class="w-full max-w-[420px] flex-shrink-0">
                    <!-- Back Button (Desktop) -->
                    <Link :href="route('Home')" class="hidden md:flex items-center gap-2 text-white hover:text-[#FFC000] font-medium text-sm mb-4 transition-colors w-fit">
                        <ArrowLeft class="" />
                        Ke Halaman Utama KuSewa
                    </Link>

                    <div class="md:hidden text-center mb-8">
                        <Link :href="route('Home')" class="inline-flex items-center gap-2">
                            <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-8 w-auto brightness-0 invert" />
                            <span class="font-bold text-xl text-white">
                                kusewa<span class="text-[#FFC000]">.id</span>
                            </span>
                        </Link>
                    </div>

                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden min-h-[400px]">
                        <AuthFlow />
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
