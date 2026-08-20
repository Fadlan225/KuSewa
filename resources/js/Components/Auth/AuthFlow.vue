<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { CheckCircle, Loader2, ArrowLeft, Circle, ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import LocationSelect from '@/Components/UI/LocationSelect.vue';

import { inject, watch } from 'vue';
import { useAuthFeedbackStore } from '@/Stores/AuthFeedbackStore';
import { useAuthModalStore } from '@/Stores/AuthModalStore';

const authFeedbackStore = useAuthFeedbackStore();
const authModalStore = useAuthModalStore();

const initialAuthStep = inject('initialAuthStep', { value: null });
const initialAuthData = inject('initialAuthData', { value: {} });

const step = ref('email');
const loading = ref(false);
const error = ref('');
const successMsg = ref('');
const showPassword = ref(false);

const passwordCriteria = computed(() => {
    const pwd = form.value.password;
    const min8 = pwd.length >= 8;
    const uniqueChars = new Set(pwd.split('')).size;
    const min3unique = uniqueChars >= 3;

    return {
        min8,
        min3unique
    };
});

watch(step, () => {
    showPassword.value = false;
});

// Form Data
const form = ref({
    email: '',
    password: '',
    password_confirmation: '',
    name: '',
    date_of_birth: '',
    place_of_birth_code: '',
    gender: '',
    otp: '',
});

const dob_day = ref('');
const dob_month = ref('');
const dob_year = ref('');
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 100 }, (_, i) => currentYear - i);

watch([dob_year, dob_month, dob_day], ([y, m, d]) => {
    if (y && m && d) {
        form.value.date_of_birth = `${y}-${m.toString().padStart(2, '0')}-${d.toString().padStart(2, '0')}`;
    } else {
        form.value.date_of_birth = '';
    }
});

const purpose = ref('');
const verifiedToken = ref('');

const clearError = () => {
    error.value = '';
    successMsg.value = '';
};

// Countdown State
const countdown = ref(0);
let timer = null;

const startCountdown = () => {
    countdown.value = 60;
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(timer);
        }
    }, 1000);
};

import { onUnmounted, onMounted } from 'vue';

const applyInitialData = () => {
    if (initialAuthStep.value) {
        step.value = initialAuthStep.value;
        form.value.email = initialAuthData.value.email || '';
        purpose.value = initialAuthData.value.purpose || '';
        verifiedToken.value = initialAuthData.value.proof || '';
    }
};

onMounted(() => {
    applyInitialData();
});

watch(initialAuthStep, () => {
    applyInitialData();
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

// KONDISI:
// 'not_registered' -> register -> OTP -> Onboarding
// 'registered_with_password' -> password -> Login
// 'registered_without_password' -> google login / create password -> OTP -> set password

const checkEmail = async () => {
    clearError();
    if (!form.value.email) {
        error.value = 'Email wajib diisi.';
        return;
    }

    loading.value = true;
    try {
        const res = await axios.post('/auth-flow/check-email', { email: form.value.email });
        const status = res.data.status;

        if (status === 'not_registered') {
            purpose.value = 'register';
            await sendOtp(false); // false means don't show resend success msg yet
            step.value = 'otp';
        } else if (status === 'registered_with_password') {
            step.value = 'password';
        } else if (status === 'registered_without_password') {
            step.value = 'create_password_choice';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Terjadi kesalahan.';
    } finally {
        loading.value = false;
    }
};

const sendOtp = async (isResend = true) => {
    if (isResend && countdown.value > 0) return;

    clearError();
    const wasNotLoading = !loading.value;
    if (wasNotLoading) loading.value = true;

    try {
        await axios.post('/auth-flow/send-otp', {
            email: form.value.email,
            purpose: purpose.value
        });
        startCountdown();
        if (isResend) {
            successMsg.value = 'Kode OTP berhasil dikirim ulang ke email Anda.';
            setTimeout(() => successMsg.value = '', 5000); // hilangkan setelah 5 detik
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal mengirim OTP.';
        throw err;
    } finally {
        if (wasNotLoading) loading.value = false;
    }
};

const verifyOtp = async () => {
    clearError();
    if (form.value.otp.length < 6) return;

    loading.value = true;
    try {
        const res = await axios.post('/auth-flow/verify-otp', {
            email: form.value.email,
            purpose: purpose.value,
            token: form.value.otp
        });

        verifiedToken.value = res.data.verified_token;

        if (purpose.value === 'register') {
            step.value = 'register_password';
        } else if (purpose.value === 'forgot_password' || purpose.value === 'create_password') {
            step.value = 'reset_password';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'OTP salah atau kadaluarsa.';
    } finally {
        loading.value = false;
    }
};

const login = async () => {
    clearError();
    loading.value = true;
    try {
        const res = await axios.post('/auth-flow/login', {
            email: form.value.email,
            password: form.value.password,
            remember: true
        });

        await authFeedbackStore.showSuccess({
            title: 'Login Berhasil!',
            message: 'Selamat datang kembali. Senang bisa bertemu denganmu lagi.'
        });
        authModalStore.close();
        router.reload({ preserveScroll: true });
    } catch (err) {
        authFeedbackStore.showError({
            title: 'Login Gagal',
            message: err.response?.data?.message || 'Email atau password yang Anda masukkan tidak sesuai.'
        });
    } finally {
        loading.value = false;
    }
};

const submitRegisterPassword = async () => {
    clearError();
    if (!passwordCriteria.value.min8 || !passwordCriteria.value.min3unique) {
        error.value = 'Password harus minimal 8 karakter dan memiliki minimal 3 karakter berbeda.';
        return;
    }
    if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Konfirmasi password tidak cocok.';
        return;
    }
    step.value = 'onboarding';
};

const finishRegistration = async (skip = false) => {
    clearError();
    loading.value = true;
    try {
        await axios.post('/auth-flow/register', {
            email: form.value.email,
            verified_token: verifiedToken.value,
            name: form.value.name,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            date_of_birth: skip ? null : form.value.date_of_birth,
            gender: skip ? null : form.value.gender
        });

        await authFeedbackStore.showSuccess({
            title: 'Pendaftaran Berhasil!',
            message: 'Selamat datang! Akun Anda telah berhasil dibuat.'
        });
        authModalStore.close();
        router.reload({ preserveScroll: true });
    } catch (err) {
        authFeedbackStore.showError({
            title: 'Pendaftaran Gagal',
            message: err.response?.data?.message || 'Terjadi kesalahan saat mendaftar.'
        });
    } finally {
        loading.value = false;
    }
};

const submitResetPassword = async () => {
    clearError();
    if (!passwordCriteria.value.min8 || !passwordCriteria.value.min3unique) {
        error.value = 'Password harus minimal 8 karakter dan memiliki minimal 3 karakter berbeda.';
        return;
    }
    if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Konfirmasi password tidak cocok.';
        return;
    }

    loading.value = true;
    try {
        await axios.post('/auth-flow/reset-password', {
            email: form.value.email,
            verified_token: verifiedToken.value,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            purpose: purpose.value
        });

        await authFeedbackStore.showSuccess({
            title: 'Password Berhasil Diubah!',
            message: 'Silakan masuk kembali menggunakan password baru Anda.'
        });
        authModalStore.close();
        router.reload({ preserveScroll: true });
    } catch (err) {
        authFeedbackStore.showError({
            title: 'Gagal Mengubah Password',
            message: err.response?.data?.message || 'Terjadi kesalahan saat mengubah password.'
        });
    } finally {
        loading.value = false;
    }
};

const startForgotPassword = () => {
    purpose.value = 'forgot_password';
    clearError();
    step.value = 'forgot_password_email';
};

const submitForgotPasswordEmail = async () => {
    clearError();
    if (!form.value.email) {
        error.value = 'Email wajib diisi.';
        return;
    }

    loading.value = true;
    try {
        await sendOtp(false);
        step.value = 'otp';
    } catch (err) {
    } finally {
        loading.value = false;
    }
};

const startCreatePassword = () => {
    purpose.value = 'create_password';
    clearError();
    step.value = 'forgot_password_email';
};

const goBackFromForgotEmail = () => {
    if (purpose.value === 'create_password') {
        step.value = 'create_password_choice';
    } else {
        step.value = 'password';
    }
};

const handleGoogleLogin = () => {
    window.location.href = '/auth/google/redirect';
};

// Expose open methods if needed
</script>

<template>
    <div class="flex flex-col h-full bg-white text-[#0A2540] md:rounded-b-3xl">
        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 hide-scrollbar">

            <div v-if="error" class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-sm border border-red-100 animate-fade-in">
                {{ error }}
            </div>
            <div v-if="successMsg" class="mb-4 p-3 bg-green-50 text-green-600 rounded-xl text-sm border border-green-100 animate-fade-in">
                <CheckCircle class="mr-1" /> {{ successMsg }}
            </div>

            <!-- STEP: EMAIL -->
            <div v-if="step === 'email'" class="animate-fade-in">
                <h3 class="text-xl font-extrabold mb-2">Selamat Datang di KitaSewa</h3>
                <p class="text-sm text-[#6C757D] mb-6">Masukkan email Anda untuk masuk atau mendaftar.</p>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="email" value="Email" class="text-xs font-bold" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 p-2 block w-full bg-[#F8F9FA] border-[#6C757D]/20 focus:border-[#0A2540] focus:ring-[#0A2540] text-sm"
                            v-model="form.email"
                            required
                            placeholder="example@gmail.com"
                            @keyup.enter="checkEmail"
                            :disabled="loading"
                        />
                    </div>

                    <PrimaryButton
                        class="w-full justify-center bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all"
                        :class="{ 'opacity-50': loading }"
                        :disabled="loading"
                        @click="checkEmail"
                    >
                        <Loader2 v-if="loading" class="mr-2 animate-spin" />
                        Lanjutkan
                    </PrimaryButton>

                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-[#6C757D]/20"></div>
                        <span class="flex-shrink-0 mx-4 text-[#6C757D] text-xs">atau</span>
                        <div class="flex-grow border-t border-[#6C757D]/20"></div>
                    </div>

                    <a
                        :href="route('auth.google.redirect')"
                        class="w-full flex items-center justify-center gap-3 bg-white border border-[#6C757D]/30 hover:bg-gray-50 text-[#0A2540] py-3 rounded-xl font-bold shadow-sm transition-all"
                    >
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google" />
                        Lanjutkan dengan Google
                    </a>
                </div>
            </div>

            <!-- STEP: PASSWORD -->
            <div v-else-if="step === 'password'" class="animate-fade-in">
                <button @click="step = 'email'" class="mb-4 text-[#6C757D] hover:text-[#0A2540] transition">
                    <ArrowLeft class="text-sm mr-1" /> Kembali
                </button>
                <h3 class="text-xl font-extrabold mb-2">Masukkan Password</h3>
                <p class="text-sm text-[#6C757D] mb-6">Akun {{ form.email }} sudah terdaftar.</p>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="password" value="Password" class="text-xs font-bold" />
                        <div class="relative">
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="mt-1 p-2 block w-full bg-[#F8F9FA] border-primary/20 text-sm pr-10"
                                v-model="form.password"
                                required
                                @keyup.enter="login"
                                :disabled="loading"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#6C757D] hover:text-[#0A2540]">
                                <AppIcon :iconClass="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'" />
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="startForgotPassword" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 underline">
                            Lupa Password?
                        </button>
                    </div>

                    <PrimaryButton
                        class="w-full justify-center bg-primary hover:bg-primary/80 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all"
                        :disabled="loading"
                        @click="login"
                    >
                        <Loader2 v-if="loading" class="mr-2 animate-spin" />
                        Masuk
                    </PrimaryButton>
                </div>
            </div>

            <!-- STEP: FORGOT PASSWORD EMAIL -->
            <div v-else-if="step === 'forgot_password_email'" class="animate-fade-in">
                <button type="button" @click="goBackFromForgotEmail" class="mb-4 text-[#6C757D] hover:text-[#0A2540] transition text-left">
                    <ArrowLeft class="text-sm mr-1" /> Kembali
                </button>
                <h3 class="text-xl font-extrabold mb-2">
                    {{ purpose === 'create_password' ? 'Buat Password Baru' : 'Lupa Password?' }}
                </h3>
                <p class="text-sm text-[#6C757D] mb-6">
                    {{ purpose === 'create_password' 
                        ? 'Pastikan email di bawah ini benar dan kami akan mengirimkan kode OTP untuk memverifikasi pembuatan password Anda.'
                        : 'Jangan khawatir! Pastikan email di bawah ini benar dan kami akan mengirimkan kode OTP untuk mereset password Anda.' 
                    }}
                </p>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="forgot_email" value="Email" class="text-xs font-bold" />
                        <TextInput
                            id="forgot_email"
                            type="email"
                            class="mt-1 p-2 block w-full bg-[#F8F9FA] border-primary/20 focus:border-primary focus:ring-primary text-sm"
                            v-model="form.email"
                            required
                            placeholder="example@gmail.com"
                            @keyup.enter="submitForgotPasswordEmail"
                            :disabled="loading"
                        />
                    </div>

                    <PrimaryButton
                        class="w-full justify-center bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all"
                        :disabled="loading"
                        @click="submitForgotPasswordEmail"
                    >
                        <Loader2 v-if="loading" class="mr-2 animate-spin" />
                        Kirim Kode OTP
                    </PrimaryButton>
                </div>
            </div>

            <!-- STEP: GOOGLE/CREATE PASSWORD CHOICE -->
            <div v-else-if="step === 'create_password_choice'" class="animate-fade-in text-center">
                <button @click="step = 'email'" class="mb-4 text-[#6C757D] hover:text-[#0A2540] transition text-left w-full">
                    <ArrowLeft class="text-sm mr-1" /> Kembali
                </button>
                <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-8 h-8" alt="Google" />
                </div>
                <h3 class="text-xl font-extrabold mb-2">Akun Dibuat via Google</h3>
                <p class="text-sm text-[#6C757D] mb-6">Akun Anda terkait dengan Google. Anda bisa langsung masuk atau membuat password terpisah.</p>

                <div class="space-y-3">
                    <a
                        :href="route('auth.google.redirect')"
                        class="w-full flex items-center justify-center gap-3 bg-white border border-[#6C757D]/30 hover:bg-gray-50 text-[#0A2540] py-3 rounded-xl font-bold shadow-sm transition-all"
                    >
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google" />
                        Masuk dengan Google
                    </a>
                    <button
                        @click="startCreatePassword"
                        class="w-full justify-center bg-primary hover:bg-primary/90 text-[#0A2540] py-3 rounded-xl font-bold transition-all"
                    >
                        Buat Password
                    </button>
                </div>
            </div>

            <!-- STEP: OTP -->
            <div v-else-if="step === 'otp'" class="animate-fade-in text-center pt-4">
                <h3 class="text-xl font-extrabold mb-2">Cek Email Anda</h3>
                <p class="text-sm text-[#6C757D] mb-6">Kami telah mengirim 6 digit kode OTP ke <span class="font-bold text-[#0A2540]">{{ form.email }}</span>. Anda juga dapat menggunakan Magic Link di email tersebut.</p>

                <div class="flex justify-center mb-6">
                    <TextInput
                        v-model="form.otp"
                        type="text"
                        maxlength="6"
                        class="text-center text-3xl font-extrabold tracking-[0.5em] w-48 bg-[#F8F9FA] p-2 border-[#6C757D]/30 focus:border-[#0A2540] focus:ring-[#0A2540]"
                        placeholder="••••••"
                        @input="form.otp.length === 6 && verifyOtp()"
                        :disabled="loading"
                    />
                </div>

                <p class="text-xs text-[#6C757D]">
                    Belum menerima email?
                    <button v-if="countdown === 0" @click="sendOtp(true)" class="font-bold text-[#FFC000] hover:text-amber-500" :disabled="loading">
                        Kirim Ulang
                    </button>
                    <span v-else class="font-bold text-gray-400 ml-1">
                        Kirim Ulang ({{ countdown }}s)
                    </span>
                </p>

                <p class="text-xs text-[#6C757D] mt-2">
                    Salah memasukkan email?
                    <button type="button" @click="step = 'email'" class="text-[#0A2540] font-bold underline hover:text-gray-700">
                        Ganti Email
                    </button>
                </p>
            </div>

            <!-- STEP: REGISTER (NAME & PASSWORD) -->
            <div v-else-if="step === 'register_password'" class="animate-fade-in">
                <h3 class="text-xl font-extrabold mb-2">Buat Akun Anda</h3>
                <p class="text-sm text-[#6C757D] mb-6">Lengkapi informasi dasar Anda.</p>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Lengkap" class="text-xs font-bold" />
                        <TextInput id="name" type="text" class="mt-1 p-2 block w-full bg-[#F8F9FA] text-sm" v-model="form.name" required />
                    </div>
                    <div>
                        <InputLabel for="reg_password" value="Password" class="text-xs font-bold" />
                        <div class="relative">
                            <TextInput id="reg_password" :type="showPassword ? 'text' : 'password'" class="mt-1 p-2 block w-full bg-[#F8F9FA] text-sm pr-10" v-model="form.password" required />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#6C757D] hover:text-[#0A2540]">
                                <AppIcon :iconClass="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'" />
                            </button>
                        </div>
                        <ul class="text-xs mt-2 space-y-1 ml-1 font-semibold">
                            <li :class="passwordCriteria.min8 ? 'text-green-600' : 'text-[#6C757D]'">
                                <Circle class="text-[5px] mr-2 align-middle" /> Min. 8 karakter
                            </li>
                            <li :class="passwordCriteria.min3unique ? 'text-green-600' : 'text-[#6C757D]'">
                                <Circle class="text-[5px] mr-2 align-middle" /> Pakai min. 3 karakter berbeda
                            </li>
                        </ul>
                    </div>
                    <div>
                        <InputLabel for="reg_password_confirmation" value="Konfirmasi Password" class="text-xs font-bold" />
                        <TextInput id="reg_password_confirmation" :type="showPassword ? 'text' : 'password'" class="mt-1 p-2 block w-full bg-[#F8F9FA] text-sm" v-model="form.password_confirmation" required />
                    </div>

                    <PrimaryButton class="w-full justify-center bg-primary py-3 rounded-xl mt-4" @click="submitRegisterPassword">
                        Lanjut
                    </PrimaryButton>
                </div>
            </div>

            <!-- STEP: ONBOARDING -->
            <div v-else-if="step === 'onboarding'" class="animate-fade-in">
                <h3 class="text-xl font-extrabold mb-2">Informasi Tambahan</h3>

                <div class="space-y-4">
                    <!-- Tempat Lahir -->
                    <div class="mb-5">
                        <InputLabel value="Tempat Lahir" class="text-xs font-bold mb-3 block" />
                        <LocationSelect
                            v-model="form.place_of_birth_code"
                            endpoint="/api/cities"
                            placeholder="Cari Kota/Kabupaten..."
                        />
                    </div>

                    <div>
                        <InputLabel value="Tanggal Lahir" class="text-xs font-bold mb-3 block" />
                        <div class="flex gap-3">
                            <!-- Tanggal -->
                            <div class="relative w-1/3">
                                <select v-model="dob_day" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-4 pr-10 text-sm font-semibold text-[#0A2540] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                    <option value="" disabled selected>DD</option>
                                    <option v-for="d in 31" :key="d" :value="d">{{ String(d).padStart(2, '0') }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors" :class="dob_day ? 'bg-amber-100 text-[#FFC000]' : 'bg-gray-100 text-gray-400'">
                                        <ChevronDown class="text-[10px]" />
                                    </div>
                                </div>
                            </div>

                            <!-- Bulan -->
                            <div class="relative w-1/3">
                                <select v-model="dob_month" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-4 pr-10 text-sm font-semibold text-[#0A2540] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                    <option value="" disabled selected>MM</option>
                                    <option v-for="m in 12" :key="m" :value="m">{{ String(m).padStart(2, '0') }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors" :class="dob_month ? 'bg-amber-100 text-[#FFC000]' : 'bg-gray-100 text-gray-400'">
                                        <ChevronDown class="text-[10px]" />
                                    </div>
                                </div>
                            </div>

                            <!-- Tahun -->
                            <div class="relative w-1/3">
                                <select v-model="dob_year" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-4 pr-10 text-sm font-semibold text-[#0A2540] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                    <option value="" disabled selected>YYYY</option>
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors" :class="dob_year ? 'bg-amber-100 text-[#FFC000]' : 'bg-gray-100 text-gray-400'">
                                        <ChevronDown class="text-[10px]" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] font-semibold text-gray-400 mt-2 leading-snug">
                            Untuk informasi pesanan yang akurat, pastikan data ini sama persis dengan apa yang tertulis di KTP/Paspor.
                        </p>
                    </div>
                    <div>
                        <InputLabel value="Kelamin (Opsional)" class="text-xs font-bold mb-3 block" />
                        <div class="flex items-center gap-6">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" value="male" v-model="form.gender" class="w-5 h-5 text-blue-500 bg-blue-50 border-blue-400 focus:ring-blue-500 cursor-pointer">
                                <span class="ml-2 text-sm font-semibold text-[#333333] group-hover:text-[#0A2540]">Laki-laki</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" value="female" v-model="form.gender" class="w-5 h-5 text-blue-500 bg-blue-50 border-blue-400 focus:ring-blue-500 cursor-pointer">
                                <span class="ml-2 text-sm font-semibold text-[#333333] group-hover:text-[#0A2540]">Perempuan</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="finishRegistration(true)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-[#0A2540] font-bold py-3 rounded-xl transition" :disabled="loading">
                            Lewati
                        </button>
                        <PrimaryButton class="flex-1 justify-center bg-primary py-3 rounded-xl" :disabled="loading" @click="finishRegistration(false)">
                            <Loader2 v-if="loading" class="mr-2 animate-spin" />
                            Daftar
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- STEP: RESET/CREATE PASSWORD -->
            <div v-else-if="step === 'reset_password'" class="animate-fade-in">
                <h3 class="text-xl font-extrabold mb-2">{{ purpose === 'create_password' ? 'Buat Password Baru' : 'Reset Password' }}</h3>
                <p class="text-sm text-[#6C757D] mb-6">Masukkan password baru untuk akun Anda.</p>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="new_password" value="Password Baru" class="text-xs font-bold" />
                        <div class="relative">
                            <TextInput id="new_password" :type="showPassword ? 'text' : 'password'" class="mt-1 p-2 block w-full bg-[#F8F9FA] border-primary/20 text-sm pr-10" v-model="form.password" required />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#6C757D] hover:text-[#0A2540]">
                                <AppIcon :iconClass="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'" />
                            </button>
                        </div>
                        <ul class="text-xs mt-2 space-y-1 ml-1 font-semibold">
                            <li :class="passwordCriteria.min8 ? 'text-green-600' : 'text-[#6C757D]'">
                                <Circle class="text-[5px] mr-2 align-middle" /> Min. 8 karakter
                            </li>
                            <li :class="passwordCriteria.min3unique ? 'text-green-600' : 'text-[#6C757D]'">
                                <Circle class="text-[5px] mr-2 align-middle" /> Pakai min. 3 karakter berbeda
                            </li>
                        </ul>
                    </div>
                    <div>
                        <InputLabel for="new_password_confirmation" value="Konfirmasi Password Baru" class="text-xs font-bold" />
                        <TextInput id="new_password_confirmation" :type="showPassword ? 'text' : 'password'" class="mt-1 p-2 block w-full bg-[#F8F9FA] border-primary/20 text-sm pr-10" v-model="form.password_confirmation" required @keyup.enter="submitResetPassword" />
                    </div>

                    <PrimaryButton class="w-full justify-center bg-primary py-3 rounded-xl mt-4" :disabled="loading" @click="submitResetPassword">
                        <Loader2 v-if="loading" class="mr-2 animate-spin" />
                        Simpan
                    </PrimaryButton>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
