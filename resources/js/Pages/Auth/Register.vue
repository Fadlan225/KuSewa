<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
// Pastikan AuthLayout di-import jika tidak diregistrasikan secara global
// import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    if (!isPasswordStrong.value) {
        form.setError(
            'password',
            'Password belum memenuhi seluruh persyaratan di bawah.'
        );
        return;
    }

    form.clearErrors('password');

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const passwordScore = computed(() => {
    const password = form.password;
    let score = 0;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    return score;
});

const passwordLevel = computed(() => {
    switch (passwordScore.value) {
        case 0:
        case 1:
            return { label: 'Sangat Lemah', level: 1, color: 'bg-red-500' };
        case 2:
            return { label: 'Lemah', level: 2, color: 'bg-orange-500' };
        case 3:
            return { label: 'Kuat', level: 3, color: 'bg-yellow-500' };
        case 4:
            return { label: 'Sangat Kuat', level: 4, color: 'bg-green-500' };
        default:
            return { label: 'Sangat Lemah', level: 1, color: 'bg-red-500' };
    }
});

const passwordRules = computed(() => {
    const password = form.password;
    return {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password),
    };
});

const isPasswordStrong = computed(() => {
    return Object.values(passwordRules.value).every(Boolean);
});
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
            <div class="flex flex-col md:flex-row items-center justify-between w-full gap-10">

                <!-- Left side: Hero text -->
                <div class="flex-1 text-left max-w-lg hidden md:block">
                    <!-- Logo -->
                    <Link :href="route('Home')" class="flex items-center gap-2.5 mb-10">
                        <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-9 w-auto brightness-0 invert" />
                        <span class="font-bold text-2xl text-white">
                            kusewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>

                    <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight mb-5">
                        Bergabung &<br>
                        Temukan <span class="text-[#FFC000]">Aset</span><br>
                        Terbaik.
                    </h1>
                    <p class="text-white/75 text-base leading-relaxed max-w-sm">
                        Daftarkan diri Anda dan nikmati kemudahan menemukan serta menyewa berbagai aset properti terbaik di Indonesia.
                    </p>

                    <!-- Features -->
                    <div class="mt-8 flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 border border-[#FFC000]/40 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-user-check text-[#FFC000] text-xs"></i>
                            </div>
                            <span class="text-white/70 text-sm">Proses pendaftaran cepat & mudah</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 border border-[#FFC000]/40 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-bell text-[#FFC000] text-xs"></i>
                            </div>
                            <span class="text-white/70 text-sm">Notifikasi properti sesuai kebutuhan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#FFC000]/20 border border-[#FFC000]/40 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-lock text-[#FFC000] text-xs"></i>
                            </div>
                            <span class="text-white/70 text-sm">Data pribadi aman & terenkripsi</span>
                        </div>
                    </div>
                </div>

                <!-- Right side: Register Form Card -->
                <div class="w-full max-w-[430px] flex-shrink-0">
                    <!-- Mobile Logo -->
                    <div class="md:hidden text-center mb-6">
                        <Link :href="route('Home')" class="inline-flex items-center gap-2">
                            <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-8 w-auto brightness-0 invert" />
                            <span class="font-bold text-xl text-white">
                                kusewa<span class="text-[#FFC000]">.id</span>
                            </span>
                        </Link>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl p-8">
                        <!-- Card Header -->
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-[#FFC000]/10 rounded-xl mb-3">
                                <img src="/kusewa-logo.png" alt="KuSewa" class="h-7 w-auto" />
                            </div>
                            <h2 class="text-2xl font-bold text-[#0A2540]">Daftar Akun</h2>
                            <p class="text-[#6C757D] text-sm mt-1">Buat akun baru dan mulai sewa!</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-[#0A2540] mb-1.5">
                                    Nama Lengkap
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-[#6C757D] text-sm"></i>
                                    </div>
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        placeholder="John Doe"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm text-[#0A2540] placeholder-[#6C757D]/60 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/40 focus:border-[#FFC000] transition-all duration-200"
                                        :class="{ 'border-red-400 bg-red-50': form.errors.name }"
                                    />
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.name" />
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-[#0A2540] mb-1.5">
                                    Email
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-envelope text-[#6C757D] text-sm"></i>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        maxlength="255"
                                        autocomplete="username"
                                        placeholder="johndoe@example.com"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm text-[#0A2540] placeholder-[#6C757D]/60 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/40 focus:border-[#FFC000] transition-all duration-200"
                                        :class="{ 'border-red-400 bg-red-50': form.errors.email }"
                                    />
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.email" />
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-[#0A2540] mb-1.5">
                                    Nomor HP
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-phone text-[#6C757D] text-sm"></i>
                                    </div>
                                    <input
                                        id="phone"
                                        type="tel"
                                        v-model="form.phone"
                                        required
                                        maxlength="15"
                                        autocomplete="tel"
                                        placeholder="08xxxxxxxxxx"
                                        @input="form.phone = form.phone.replace(/[^0-9]/g, '')"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm text-[#0A2540] placeholder-[#6C757D]/60 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/40 focus:border-[#FFC000] transition-all duration-200"
                                        :class="{ 'border-red-400 bg-red-50': form.errors.phone }"
                                    />
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.phone" />
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-[#0A2540] mb-1.5">
                                    Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-lock text-[#6C757D] text-sm"></i>
                                    </div>
                                    <input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        v-model="form.password"
                                        required
                                        minlength="8"
                                        maxlength="64"
                                        autocomplete="new-password"
                                        placeholder="Min. 8 karakter"
                                        class="w-full pl-10 pr-11 py-3 border border-gray-200 rounded-xl text-sm text-[#0A2540] placeholder-[#6C757D]/60 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/40 focus:border-[#FFC000] transition-all duration-200"
                                        :class="{ 'border-red-400 bg-red-50': form.errors.password }"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#6C757D] hover:text-[#0A2540] transition-colors"
                                    >
                                        <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                                    </button>
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.password" />

                                <!-- Password Strength -->
                                <div v-if="form.password.length > 0" class="mt-2.5">
                                    <!-- Strength bars -->
                                    <div class="flex gap-1 mb-1.5">
                                        <div
                                            v-for="index in 4"
                                            :key="index"
                                            class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                            :class="index <= passwordLevel.level ? passwordLevel.color : 'bg-gray-200'"
                                        ></div>
                                    </div>
                                    <p class="text-xs font-medium" :class="{
                                        'text-red-500': passwordLevel.level <= 1,
                                        'text-orange-500': passwordLevel.level === 2,
                                        'text-yellow-600': passwordLevel.level === 3,
                                        'text-green-600': passwordLevel.level === 4
                                    }">
                                        Kekuatan: {{ passwordLevel.label }}
                                    </p>

                                    <!-- Password rules -->
                                    <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <ul class="space-y-1">
                                            <li
                                                v-for="[key, label] in [
                                                    ['length', 'Minimal 8 karakter'],
                                                    ['uppercase', 'Huruf besar (A-Z)'],
                                                    ['lowercase', 'Huruf kecil (a-z)'],
                                                    ['number', 'Angka (0-9)'],
                                                    ['symbol', 'Simbol (!@#$%^&*)'],
                                                ]"
                                                :key="key"
                                                class="flex items-center gap-1.5 text-xs"
                                                :class="passwordRules[key] ? 'text-green-600' : 'text-[#6C757D]'"
                                            >
                                                <i :class="passwordRules[key] ? 'fa-solid fa-circle-check text-green-600' : 'fa-regular fa-circle text-gray-400'" class="text-[10px] w-3"></i>
                                                {{ label }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-[#0A2540] mb-1.5">
                                    Konfirmasi Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-lock text-[#6C757D] text-sm"></i>
                                    </div>
                                    <input
                                        id="password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        v-model="form.password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Ulangi password"
                                        class="w-full pl-10 pr-11 py-3 border border-gray-200 rounded-xl text-sm text-[#0A2540] placeholder-[#6C757D]/60 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/40 focus:border-[#FFC000] transition-all duration-200"
                                        :class="{ 'border-red-400 bg-red-50': form.errors.password_confirmation }"
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#6C757D] hover:text-[#0A2540] transition-colors"
                                    >
                                        <i :class="showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                                    </button>
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                            </div>

                            <!-- Register Button -->
                            <button
                                type="submit"
                                :disabled="form.processing || !isPasswordStrong"
                                class="w-full py-3.5 bg-[#FFC000] hover:bg-[#e6ac00] text-[#0A2540] font-bold text-sm rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:shadow-[#FFC000]/30 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ form.processing ? 'Memproses...' : 'Buat Akun' }}
                            </button>

                            <!-- Divider -->
                            <div class="relative flex items-center gap-3">
                                <div class="flex-1 h-px bg-gray-200"></div>
                                <span class="text-xs text-[#6C757D] font-medium">atau daftar dengan</span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>

                            <!-- Google Register -->
                            <button
                                type="button"
                                class="w-full py-3 border border-gray-200 rounded-xl flex items-center justify-center gap-2.5 text-sm font-medium text-[#0A2540] hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 active:scale-[0.98]"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                                Sign up with Google
                            </button>
                        </form>

                        <!-- Footer -->
                        <p class="text-center text-sm text-[#6C757D] mt-5">
                            Sudah punya akun?
                            <Link :href="route('login')" class="text-[#FFC000] hover:text-[#e6ac00] font-semibold transition-colors">
                                Login
                            </Link>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
