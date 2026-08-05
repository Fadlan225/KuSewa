<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useAuthFeedbackStore } from '@/Stores/AuthFeedbackStore';

const page = usePage();
const authFeedbackStore = useAuthFeedbackStore();

const confirmingUserDeletion = ref(false);
const step = ref('confirm'); // 'confirm' or 'otp'
const otpCode = ref(['', '', '', '', '', '']);
const otpInputs = ref([]);
const isSendingOtp = ref(false);
const isVerifyingOtp = ref(false);

const form = useForm({
    verified_token: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    step.value = 'confirm';
    otpCode.value = ['', '', '', '', '', ''];
};

const sendOtp = async () => {
    isSendingOtp.value = true;
    try {
        await axios.post('/auth-flow/send-otp', {
            email: page.props.auth.user.email,
            purpose: 'delete_account'
        });
        step.value = 'otp';
    } catch (err) {
        authFeedbackStore.showError({
            title: 'Gagal Mengirim OTP',
            message: err.response?.data?.message || 'Terjadi kesalahan sistem.'
        });
    } finally {
        isSendingOtp.value = false;
    }
};

const handleOtpInput = (index, event) => {
    const value = event.target.value;
    // Allow only numeric characters
    if (value && !/^\d$/.test(value)) {
        otpCode.value[index] = '';
        return;
    }
    // Auto-advance
    if (value && index < 5) {
        otpInputs.value[index + 1]?.focus();
    }
};

const handleOtpKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otpCode.value[index] && index > 0) {
        otpInputs.value[index - 1]?.focus();
    } else if (event.key === 'Enter') {
        verifyOtpAndDelete();
    }
};

const handleOtpPaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (pastedData) {
        pastedData.split('').forEach((char, i) => {
            if (i < 6) otpCode.value[i] = char;
        });
        const nextIndex = Math.min(pastedData.length, 5);
        otpInputs.value[nextIndex]?.focus();
    }
};

const verifyOtpAndDelete = async () => {
    const code = otpCode.value.join('');
    if (code.length !== 6) {
        authFeedbackStore.showError({ title: 'Error', message: 'Masukkan 6 digit kode OTP.' });
        return;
    }

    isVerifyingOtp.value = true;
    form.clearErrors();

    try {
        const response = await axios.post('/auth-flow/verify-otp', {
            email: page.props.auth.user.email,
            purpose: 'delete_account',
            token: code
        });

        // Success, now delete user
        form.verified_token = response.data.verified_token;

        form.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => {
                isVerifyingOtp.value = false;
                step.value = 'confirm'; // fallback if error occurs
            }
        });
    } catch (err) {
        isVerifyingOtp.value = false;
        form.setError('verified_token', err.response?.data?.message || 'Kode OTP salah atau sudah kedaluwarsa.');
    }
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    step.value = 'confirm';
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div class="flex items-center justify-between p-4 sm:p-5 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <div class="pr-4">
                <p class="text-[15px] font-bold text-[#1D1D1F]">Hapus Akun</p>
                <p class="text-[13px] text-gray-500 mt-1 leading-relaxed">Setelah dihapus, akun dan semua datanya tidak dapat dipulihkan.</p>
            </div>
            <button
                type="button"
                @click="confirmUserDeletion"
                class="px-4 py-2 text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] active:scale-95 transition-all shrink-0"
            >
                Hapus
            </button>
        </div>

        <Teleport to="body" v-if="confirmingUserDeletion">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">

                    <template v-if="step === 'confirm'">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            Yakin ingin menghapus akun?
                        </h2>
                        <p class="mt-2 text-sm text-gray-600">
                            Setelah akun dihapus, semua data dan riwayat pesanan akan terhapus permanen. Kami akan mengirimkan kode OTP ke email Anda (<strong>{{ page.props.auth.user.email }}</strong>) untuk mengonfirmasi.
                        </p>

                        <div class="mt-8 flex flex-col gap-3">
                            <button
                                type="button"
                                :disabled="isSendingOtp"
                                @click="sendOtp"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                            >
                                <span v-if="isSendingOtp"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Mengirim...</span>
                                <span v-else>Kirim Kode OTP</span>
                            </button>

                            <button
                                type="button"
                                :disabled="isSendingOtp"
                                @click="closeModal"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
                            >
                                Batal
                            </button>
                        </div>
                    </template>

                    <template v-else-if="step === 'otp'">
                        <h2 class="text-xl font-bold text-[#0A2540] mb-6">
                            Verifikasi Penghapusan Akun
                        </h2>
                        <p class="mt-2 text-sm text-[#6C757D]">
                            Kami telah mengirimkan 6 digit kode OTP ke email <strong>{{ page.props.auth.user.email }}</strong>. Masukkan kode tersebut di bawah ini.
                        </p>

                        <div class="flex justify-center gap-2 mt-6" @paste="handleOtpPaste">
                            <input
                                v-for="(_, index) in 6"
                                :key="index"
                                :ref="el => otpInputs[index] = el"
                                v-model="otpCode[index]"
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                class="w-10 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000]"
                                @input="handleOtpInput(index, $event)"
                                @keydown="handleOtpKeydown(index, $event)"
                            />
                        </div>
                        <p v-if="form.errors.verified_token" class="text-red-500 text-sm mt-3">{{ form.errors.verified_token }}</p>

                        <div class="mt-8 flex flex-col gap-3">
                            <button
                                type="button"
                                :disabled="isVerifyingOtp || form.processing"
                                @click="verifyOtpAndDelete"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                            >
                                <span v-if="isVerifyingOtp || form.processing"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memproses...</span>
                                <span v-else>Hapus Akun</span>
                            </button>

                            <button
                                type="button"
                                :disabled="isVerifyingOtp || form.processing"
                                @click="closeModal"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
                            >
                                Batal
                            </button>
                        </div>
                    </template>

                </div>
            </div>
        </Teleport>
    </section>
</template>
