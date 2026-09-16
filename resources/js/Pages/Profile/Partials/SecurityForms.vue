<script setup>
import UpdatePasswordForm from './UpdatePasswordForm.vue';
import DeleteUserForm from './DeleteUserForm.vue';
import { Chrome, ChevronRight, ArrowLeft, Mail, Loader2 } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
});

const showUnlinkGoogleModal = ref(false);

const showEmailChangeModal = ref(false);
const emailChangeStep = ref(1);
const newEmailInput = ref('');
const oldOtpDigits = ref(['', '', '', '', '', '']);
const newOtpDigits = ref(['', '', '', '', '', '']);
const emailError = ref('');
const otpError = ref('');
const isProcessing = ref(false);

// Refs for OTP inputs to handle auto-focus
const oldOtpRefs = ref([]);
const newOtpRefs = ref([]);

const openEmailModal = () => {
    showEmailChangeModal.value = true;
    emailChangeStep.value = 1;
    newEmailInput.value = '';
    oldOtpDigits.value = ['', '', '', '', '', ''];
    newOtpDigits.value = ['', '', '', '', '', ''];
    emailError.value = '';
    otpError.value = '';
};

const closeEmailModal = () => {
    showEmailChangeModal.value = false;
};

const handleOtpInput = (index, event, type = 'old') => {
    const value = event.target.value;
    const digits = type === 'old' ? oldOtpDigits.value : newOtpDigits.value;
    const refs = type === 'old' ? oldOtpRefs.value : newOtpRefs.value;
    
    // Only allow numbers
    if (value && !/^\d+$/.test(value)) {
        digits[index] = '';
        return;
    }
    
    // Auto-advance
    if (value && index < 5) {
        refs[index + 1]?.focus();
    }
};

const handleOtpKeydown = (index, event, type = 'old') => {
    const digits = type === 'old' ? oldOtpDigits.value : newOtpDigits.value;
    const refs = type === 'old' ? oldOtpRefs.value : newOtpRefs.value;
    
    // Auto-backspace
    if (event.key === 'Backspace' && !digits[index] && index > 0) {
        refs[index - 1]?.focus();
    }
};

const handleOtpPaste = (event, type = 'old') => {
    event.preventDefault();
    const pastedData = event.clipboardData?.getData('text') || '';
    // Extract only numbers
    const numbers = pastedData.replace(/\D/g, '').slice(0, 6);
    
    if (numbers.length > 0) {
        const digits = type === 'old' ? oldOtpDigits.value : newOtpDigits.value;
        const refs = type === 'old' ? oldOtpRefs.value : newOtpRefs.value;
        
        // Fill the array
        for (let i = 0; i < 6; i++) {
            digits[i] = numbers[i] || '';
        }
        
        // Focus the next empty box or the last box
        const focusIndex = Math.min(numbers.length, 5);
        if (refs[focusIndex]) {
            refs[focusIndex].focus();
        }
    }
};

const sendOldOtp = async () => {
    isProcessing.value = true;
    otpError.value = '';
    
    try {
        await axios.post(route('profile.email.send-old-otp'));
        emailChangeStep.value = 2; // Move to Old OTP input
    } catch (error) {
        otpError.value = error.response?.data?.message || 'Gagal mengirim kode verifikasi.';
    } finally {
        isProcessing.value = false;
    }
};

const verifyOldOtp = async () => {
    const otp = oldOtpDigits.value.join('');
    if (otp.length !== 6) return;
    
    isProcessing.value = true;
    otpError.value = '';
    
    try {
        await axios.post(route('profile.email.verify-old'), { otp });
        emailChangeStep.value = 3; // Move to Input New Email step
    } catch (error) {
        if (error.response?.data?.errors?.otp) {
            otpError.value = error.response.data.errors.otp[0];
        } else {
            otpError.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        isProcessing.value = false;
    }
};

const sendNewOtp = async () => {
    if (!newEmailInput.value) return;
    isProcessing.value = true;
    emailError.value = '';
    
    try {
        await axios.post(route('profile.email.send-new-otp'), {
            email: newEmailInput.value
        });
        emailChangeStep.value = 4; // Move to New OTP input
    } catch (error) {
        if (error.response?.data?.errors?.email) {
            emailError.value = error.response.data.errors.email[0];
        } else {
            emailError.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        isProcessing.value = false;
    }
};

const verifyNewOtp = async () => {
    const otp = newOtpDigits.value.join('');
    if (otp.length !== 6) return;
    
    isProcessing.value = true;
    otpError.value = '';
    
    try {
        await axios.post(route('profile.email.verify-new'), { otp });
        // Success
        closeEmailModal();
        router.reload({ only: ['user'] }); // reload user prop
    } catch (error) {
        if (error.response?.data?.errors?.otp) {
            otpError.value = error.response.data.errors.otp[0];
        } else {
            otpError.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        isProcessing.value = false;
    }
};

const linkGoogle = () => {
    window.location.href = '/auth/google/redirect';
};

const unlinkGoogle = () => {
    router.delete(route('auth.google.unlink'), {
        preserveScroll: true,
        onSuccess: () => {
            showUnlinkGoogleModal.value = false;
        },
        onError: (errors) => {
            if (errors.error) alert(errors.error);
        }
    });
};

const activeView = ref('menu');
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

const openPasswordForm = () => {
    if (isMobile.value) {
        router.visit(route('profile.security.password'));
    } else {
        activeView.value = 'password';
    }
};

const closePasswordForm = () => {
    activeView.value = 'menu';
};
</script>

<template>
    <div v-if="activeView === 'menu'" class="space-y-8">
        <!-- Verifikasi Akun Section -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-lg shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Verifikasi Akun</h3>
            
            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-[#F8F9FA] shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100 shrink-0">
                        <Mail class="w-5 h-5 text-gray-700" />
                    </div>
                    <div>
                        <h4 class="font-bold text-[#0A2540] text-sm">Email</h4>
                        <p class="text-xs text-gray-500 mt-0.5">{{ user.email }}</p>
                    </div>
                </div>
                
                <button @click="openEmailModal" class="px-4 py-2 bg-white border border-gray-200 text-[#0A2540] text-xs font-bold rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                    Ubah
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-3 pl-2">Email ini digunakan untuk mengirim kode verifikasi guna mencegah akses akun yang tidak sah.</p>
        </div>

        <!-- Keamanan Section -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-lg shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Password & Keamanan</h3>
            
            <button @click="openPasswordForm" class="w-full flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-[#F8F9FA] hover:bg-white transition-colors duration-200 shadow-sm text-left">
                <div>
                    <h4 class="font-bold text-[#0A2540] text-sm">Ubah Kata Sandi</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Perbarui kata sandi untuk keamanan akun</p>
                </div>
                <ChevronRight class="w-5 h-5 text-gray-400" />
            </button>
        </div>

        <!-- Akun Tertaut Section -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-lg shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Akun Tertaut</h3>
            
            <!-- Google Account Link -->
            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-[#F8F9FA] hover:bg-white transition-colors duration-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100 shrink-0">
                        <Chrome class="w-5 h-5 text-gray-700" />
                    </div>
                    <div>
                        <h4 class="font-bold text-[#0A2540] text-sm">Google</h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ user.is_google_linked ? 'Terhubung dengan akun Google' : 'Belum terhubung' }}
                        </p>
                    </div>
                </div>
                
                <button v-if="!user.is_google_linked" @click="linkGoogle" class="px-4 py-2 bg-white border border-gray-200 text-[#0A2540] text-xs font-bold rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                    Hubungkan
                </button>
                <button v-else @click="showUnlinkGoogleModal = true" class="px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-all">
                    Putuskan
                </button>
            </div>
        </div>

        <!-- Hapus Akun Section -->
        <div class="py-2">
            <DeleteUserForm />
        </div>

        <!-- Unlink Google Modal -->
        <Teleport to="body" v-if="showUnlinkGoogleModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-lg max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
                    <h2 class="text-xl font-bold text-gray-900">Putuskan Akun Google?</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Kamu tidak akan bisa login menggunakan Google lagi setelah ini, pastikan kamu mengingat password akunmu.
                    </p>

                    <div class="mt-8 flex flex-col gap-3">
                        <button
                            @click="unlinkGoogle"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-sm text-white hover:bg-red-700 focus:outline-none transition ease-in-out duration-150"
                        >
                            Ya, Putuskan
                        </button>

                        <button
                            type="button"
                            @click="showUnlinkGoogleModal = false"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Email Change Modal -->
        <Teleport to="body" v-if="showEmailChangeModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-lg max-w-md w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Ubah Alamat Email</h2>
                        <button @click="closeEmailModal" class="text-gray-400 hover:text-gray-600 transition-colors text-2xl font-light">
                            &times;
                        </button>
                    </div>

                    <!-- Step 1: Confirm Send Old OTP -->
                    <div v-if="emailChangeStep === 1" class="text-center">
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">Untuk mengubah alamat email Anda, kami perlu memverifikasi identitas Anda terlebih dahulu. Kami akan mengirimkan 6 digit kode OTP ke email Anda saat ini: <br><strong class="text-gray-900 mt-2 block">{{ user.email }}</strong></p>
                        
                        <p v-if="otpError" class="text-red-500 text-xs mb-4">{{ otpError }}</p>

                        <button @click="sendOldOtp" :disabled="isProcessing" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold py-3 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50">
                            <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin mr-2" />
                            Kirim Kode OTP
                        </button>
                    </div>

                    <!-- Step 2: Verify Old Email -->
                    <div v-if="emailChangeStep === 2" class="text-center">
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">Kami telah mengirimkan 6 digit kode OTP ke email <strong class="text-gray-900">{{ user.email }}</strong>. Masukkan kode tersebut di bawah ini.</p>
                        
                        <div class="flex justify-center gap-2 sm:gap-3 mb-6">
                            <input v-for="(digit, index) in oldOtpDigits" :key="index"
                                :ref="el => oldOtpRefs[index] = el"
                                v-model="oldOtpDigits[index]"
                                type="text"
                                maxlength="1"
                                class="w-10 h-12 sm:w-12 sm:h-14 border border-gray-300 rounded-lg text-center text-xl font-bold text-[#0A2540] focus:border-[#FFC000] focus:ring-[#FFC000] outline-none transition-colors"
                                @input="handleOtpInput(index, $event, 'old')"
                                @keydown="handleOtpKeydown(index, $event, 'old')"
                                @paste="handleOtpPaste($event, 'old')"
                            >
                        </div>
                        
                        <p v-if="otpError" class="text-red-500 text-xs mb-4">{{ otpError }}</p>

                        <button @click="verifyOldOtp" :disabled="isProcessing || oldOtpDigits.join('').length !== 6" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold py-3 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50">
                            <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin mr-2" />
                            Verifikasi
                        </button>
                    </div>

                    <!-- Step 3: Input New Email -->
                    <div v-if="emailChangeStep === 3">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Masukkan Email Baru</label>
                            <input type="email" v-model="newEmailInput" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-[#FFC000] focus:ring-[#FFC000] outline-none transition-colors" placeholder="nama@email.com" @keyup.enter="sendNewOtp">
                            <p v-if="emailError" class="text-red-500 text-xs mt-1.5">{{ emailError }}</p>
                        </div>
                        <button @click="sendNewOtp" :disabled="isProcessing || !newEmailInput" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold py-3 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50 mt-2">
                            <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin mr-2" />
                            Lanjut
                        </button>
                    </div>

                    <!-- Step 4: Verify New Email -->
                    <div v-if="emailChangeStep === 4" class="text-center">
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">Hampir selesai! Kami telah mengirimkan 6 digit kode OTP ke alamat email baru Anda: <strong class="text-gray-900">{{ newEmailInput }}</strong>.</p>
                        
                        <div class="flex justify-center gap-2 sm:gap-3 mb-6">
                            <input v-for="(digit, index) in newOtpDigits" :key="index"
                                :ref="el => newOtpRefs[index] = el"
                                v-model="newOtpDigits[index]"
                                type="text"
                                maxlength="1"
                                class="w-10 h-12 sm:w-12 sm:h-14 border border-gray-300 rounded-lg text-center text-xl font-bold text-[#0A2540] focus:border-[#FFC000] focus:ring-[#FFC000] outline-none transition-colors"
                                @input="handleOtpInput(index, $event, 'new')"
                                @keydown="handleOtpKeydown(index, $event, 'new')"
                                @paste="handleOtpPaste($event, 'new')"
                            >
                        </div>
                        
                        <p v-if="otpError" class="text-red-500 text-xs mb-4">{{ otpError }}</p>

                        <button @click="verifyNewOtp" :disabled="isProcessing || newOtpDigits.join('').length !== 6" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold py-3 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50 mt-2">
                            <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin mr-2" />
                            Simpan Perubahan
                        </button>
                    </div>

                </div>
            </div>
        </Teleport>
    </div>

    <!-- Desktop Form View -->
    <div v-else-if="activeView === 'password' && !isMobile" class="space-y-6">
        <button @click="closePasswordForm" class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
            <ArrowLeft class="w-4 h-4" /> Kembali
        </button>
        
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Ubah Kata Sandi</h3>
            <UpdatePasswordForm />
        </div>
    </div>
</template>
