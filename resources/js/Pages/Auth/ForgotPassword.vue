<script setup>
import { ArrowLeft } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import AuthFlow from '@/Components/Auth/AuthFlow.vue';

const initialAuthStep = ref('forgot_password_email');
const initialAuthData = ref({
    purpose: 'forgot_password'
});
provide('initialAuthStep', initialAuthStep);
provide('initialAuthData', initialAuthData);
</script>

<template>
    <Head title="Lupa Password" />

    <div class="min-h-screen relative flex items-center justify-center overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/public.png')"></div>

        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-transparent"></div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 min-h-screen flex items-center py-16">
            
            <!-- Absolute Top Left Logo for Desktop -->
            <Link :href="route('Home')" class="absolute top-10 left-6 lg:left-8 items-center gap-2.5 hidden md:flex z-50">
                <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-9 w-auto brightness-0 invert" />
                <span class="font-bold text-2xl text-white">
                    kitasewa<span class="text-[#FFC000]">.id</span>
                </span>
            </Link>

            <div class="flex flex-col md:flex-row items-center justify-between w-full gap-10">

                <!-- Left side: Hero text -->
                <div class="flex-1 text-left max-w-lg hidden md:block">


                    <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight mb-5">
                        Lupa <span class="text-[#FFC000]">Password?</span>
                    </h1>
                    <p class="text-white/80 text-base leading-relaxed max-w-sm mb-8">
                        Jangan panik. Masukkan email Anda dan kami akan mengirimkan instruksi untuk membuat password baru.
                    </p>

                    <!-- Steps info -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 flex items-center justify-center shrink-0 mt-0.5 border border-[#FFC000]/30">
                                <span class="text-[#FFC000] text-sm font-bold">1</span>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold text-sm">Masukkan Email</h3>
                                <p class="text-white/60 text-xs mt-1">Ketik alamat email yang terdaftar</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 flex items-center justify-center shrink-0 mt-0.5 border border-[#FFC000]/30">
                                <span class="text-[#FFC000] text-sm font-bold">2</span>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold text-sm">Terima OTP</h3>
                                <p class="text-white/60 text-xs mt-1">Cek kotak masuk email Anda</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 flex items-center justify-center shrink-0 mt-0.5 border border-[#FFC000]/30">
                                <span class="text-[#FFC000] text-sm font-bold">3</span>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold text-sm">Buat Password Baru</h3>
                                <p class="text-white/60 text-xs mt-1">Login kembali dengan password baru</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side: Form Card -->
                <div class="w-full max-w-[420px] flex-shrink-0">
                    <!-- Back Button (Desktop) -->
                    <Link :href="route('Home')" class="hidden md:flex items-center gap-2 text-white hover:text-[#FFC000] font-medium text-sm mb-4 transition-colors w-fit">
                        <ArrowLeft class="" />
                        Ke Halaman Utama KitaSewa
                    </Link>

                    <div class="md:hidden text-center mb-8">
                        <Link :href="route('Home')" class="inline-flex items-center gap-2">
                            <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-8 w-auto brightness-0 invert" />
                            <span class="font-bold text-xl text-white">
                                kitasewa<span class="text-[#FFC000]">.id</span>
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
