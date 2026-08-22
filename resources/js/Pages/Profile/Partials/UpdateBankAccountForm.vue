<script setup>
import { Loader2 } from 'lucide-vue-next';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    bank_account: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name, // Required by backend
    email: user.email, // Required by backend
    bank_name: props.bank_account?.bank_name || '',
    account_number: props.bank_account?.account_number || '',
    account_holder: props.bank_account?.account_holder || '',
});
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div class="space-y-5">
                <!-- Bank Name -->
                <div>
                    <label for="bank_name" class="block text-sm text-[#333333] mb-1.5">Nama Bank</label>
                    <input
                        id="bank_name"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors uppercase"
                        v-model="form.bank_name"
                        placeholder="BCA, MANDIRI, BRI, dll"
                    />
                    <p v-if="form.errors.bank_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                </div>

                <!-- Account Number -->
                <div>
                    <label for="account_number" class="block text-sm text-[#333333] mb-1.5">Nomor Rekening</label>
                    <input
                        id="account_number"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.account_number"
                        placeholder="Contoh: 1234567890"
                    />
                    <p v-if="form.errors.account_number" class="mt-1.5 text-sm text-red-600">{{ form.errors.account_number }}</p>
                </div>

                <!-- Account Holder -->
                <div>
                    <label for="account_holder" class="block text-sm text-[#333333] mb-1.5">Nama Pemilik Rekening</label>
                    <input
                        id="account_holder"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.account_holder"
                        placeholder="Sesuai buku tabungan"
                    />
                    <p v-if="form.errors.account_holder" class="mt-1.5 text-sm text-red-600">{{ form.errors.account_holder }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-8 pt-4 border-t border-gray-100">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-[#FFC000] text-[#0A2540] font-bold py-3 px-8 rounded-xl shadow-sm hover:bg-[#F2C94C] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[140px]"
                >
                    <Loader2 v-if="form.processing" class="w-5 h-5 mr-2 animate-spin" />
                    <span v-else>Simpan</span>
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-from-class="opacity-100 translate-x-0"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
