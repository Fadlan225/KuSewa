<script setup>
import { Lock, Shield, ArrowLeft } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import AuthFlow from '@/Components/Auth/AuthFlow.vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const initialAuthStep = ref('reset_password');
const initialAuthData = ref({
    email: props.email,
    proof: props.token,
    purpose: 'forgot_password'
});
provide('initialAuthStep', initialAuthStep);
provide('initialAuthData', initialAuthData);
</script>

<template>
    <Head title="Reset Password" />

    <!-- Container Utama (Full Screen, Relative) -->
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden">

        <!-- FULLSCREEN BACKGROUND -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/public.png')"></div>
        <div class="absolute inset-0 bg-gray-900/60 mix-blend-multiply"></div>

        <!-- KONTEN UTAMA (Di atas Background) -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center min-h-screen py-16 gap-10 md:gap-4 lg:gap-16">

            <!-- Absolute Top Left Logo for Desktop -->
            <Link :href="route('Home')" class="absolute top-10 left-6 lg:left-8 items-center gap-2.5 hidden md:flex z-50">
                <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-9 w-auto brightness-0 invert" />
                <span class="font-bold text-2xl text-white">
                    kusewa<span class="text-[#FFC000]">.id</span>
                </span>
            </Link>

            <!-- SISI KIRI: Teks Promosi (Hanya muncul di desktop md:) -->
            <div class="hidden md:flex flex-col w-full md:w-1/2 text-white pr-4 gap-12">

                <!-- Teks Utama -->
                <div class="max-w-xl">
                    <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight mb-6">
                        <span class="text-[#FFC107]">Buat</span> Password Baru.
                    </h1>
                    <p class="text-lg text-gray-200 leading-relaxed">
                        Langkah terakhir! Silakan buat password baru yang kuat dan mudah diingat untuk mengamankan kembali akun Anda.
                    </p>
                </div>

                <!-- Fitur -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-200 mt-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-[#FFC107]/20 text-[#FFC107] backdrop-blur-sm border border-[#FFC107]/20">
                            <Lock class="" />
                        </div>
                        <p class="font-medium">Gunakan Kombinasi Unik</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-[#FFC107]/20 text-[#FFC107] backdrop-blur-sm border border-[#FFC107]/20">
                            <Shield class="" />
                        </div>
                        <p class="font-medium">Keamanan Terjamin</p>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN / MOBILE: Form Card -->
            <div class="w-full md:w-1/2 flex justify-center md:justify-end">
                <div class="w-full max-w-[420px] flex flex-col">
                    <!-- Back Button (Desktop) -->
                    <Link :href="route('Home')" class="hidden md:flex items-center gap-2 text-white hover:text-[#FFC000] font-medium text-sm mb-4 transition-colors w-fit">
                        <ArrowLeft class="" />
                        Ke Halaman Utama KuSewa
                    </Link>

                    <div class="w-full bg-white rounded-3xl shadow-2xl overflow-hidden min-h-[500px]">
                        <AuthFlow />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
