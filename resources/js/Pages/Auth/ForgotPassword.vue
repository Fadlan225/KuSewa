<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Password" />

    <!-- Container Utama (Full Screen, Relative) -->
    <div class="relative min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

        <!-- FULLSCREEN BACKGROUND -->
        <div class="absolute inset-0 z-0">
            <img
                src="/public.png"
                alt="Background kusewa.id"
                class="h-full w-full object-cover"
            />
            <!-- Overlay gelap agar teks dan form lebih mudah dibaca -->
            <div class="absolute inset-0 bg-gray-900/60 mix-blend-multiply"></div>
        </div>

        <!-- KONTEN UTAMA (Di atas Background) -->
        <div class="relative z-10 w-full max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10 md:gap-4 lg:gap-16">

            <!-- SISI KIRI: Teks Promosi (Hanya muncul di desktop md:) -->
            <div class="hidden md:flex flex-col w-full md:w-1/2 text-white pr-4 gap-12">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl backdrop-blur-sm">
                         <img src="/kusewa-logo.png" alt="Logo" class="h-8 w-auto"/>
                    </div>
                    <span class="text-3xl font-bold text-white tracking-wide">kusewa<span class="text-[#FFC107]">.id</span></span>
                </div>

                <!-- Teks Utama -->
                <div class="max-w-xl">
                    <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight mb-6">
                        <span class="text-[#FFC107]">Pulihkan</span> Akses Akun Anda.
                    </h1>
                    <p class="text-lg text-gray-200 leading-relaxed">
                        Kami akan membantu Anda mengatur ulang kata sandi. Cukup masukkan email yang terdaftar dan periksa kotak masuk Anda.
                    </p>
                </div>

                <!-- Fitur -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-200 mt-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-[#FFC107]/20 text-[#FFC107] backdrop-blur-sm border border-[#FFC107]/20">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <p class="font-medium">Proses Aman & Terverifikasi</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-[#FFC107]/20 text-[#FFC107] backdrop-blur-sm border border-[#FFC107]/20">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <p class="font-medium">Link Reset Cepat Dikirim</p>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN / MOBILE: Form Card -->
            <div class="w-full md:w-1/2 flex justify-center md:justify-end">
                <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-white/20 backdrop-blur-md">

                    <!-- Tombol Kembali -->
                    <div class="mb-8 flex justify-start">
                        <Link
                            :href="route('login')"
                            class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-[#001F3F] transition-colors"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali ke Login
                        </Link>
                    </div>

                    <!-- Header Form -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center p-3 mb-5 rounded-2xl bg-gray-50 shadow-inner border border-gray-100">
                            <img src="/kusewa-logo.png" alt="Icon" class="h-9 w-9"/>
                        </div>
                        <h2 class="text-3xl font-extrabold text-[#001F3F]">Lupa Password</h2>
                        <p class="mt-3 text-sm text-gray-500 max-w-xs mx-auto leading-relaxed">
                            Masukkan email Anda untuk menerima link riset password.
                        </p>
                    </div>

                    <!-- Status Berhasil -->
                    <div
                        v-if="status"
                        class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-sm text-green-700 shadow-inner flex items-center gap-3"
                    >
                        <i class="fa-solid fa-circle-check text-lg text-green-500"></i>
                        <span class="font-medium">{{ status }}</span>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-bold text-[#001F3F] mb-2"
                            >
                                Alamat Email
                            </label>

                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#001F3F] transition-colors">
                                    <i class="fa-regular fa-envelope text-lg"></i>
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="johndoe@example.com"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-12 pr-4 py-3.5 text-base text-gray-800 placeholder:text-gray-400 outline-none transition duration-200 shadow-sm focus:bg-white focus:border-[#001F3F] focus:ring-2 focus:ring-[#001F3F]/10"
                                />
                            </div>

                            <p
                                v-if="form.errors.email"
                                class="mt-2.5 text-sm font-medium text-red-500 flex items-center gap-1.5"
                            >
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-xl bg-[#FFC107] px-6 py-4 text-[#001F3F] text-base font-extrabold transition duration-200 hover:bg-[#ffcd38] hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-[#FFC107]/30 shadow-md disabled:cursor-not-allowed disabled:opacity-60 disabled:transform-none flex items-center justify-center gap-2"
                            >
                                <span v-if="form.processing">
                                    <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...
                                </span>
                                <span v-else>Kirim Link Reset Password</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>
