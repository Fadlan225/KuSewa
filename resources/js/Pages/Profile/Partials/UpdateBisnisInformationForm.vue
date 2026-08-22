<script setup>
import { Loader2 } from 'lucide-vue-next';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    owner_profile: {
        type: Object,
        default: null,
    },
    bank_account: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name, // Required by backend
    email: user.email, // Required by backend
    national_id: props.owner_profile?.national_id || '',
    address: props.owner_profile?.address || '',
});
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div class="space-y-5">
                <!-- NIK KTP -->
                <div>
                    <label for="national_id" class="block text-sm text-[#333333] mb-1.5">Nomor Induk Kependudukan</label>
                    <input
                        id="national_id"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.national_id"
                        maxlength="16"
                        placeholder="Masukkan 16 digit NIK"
                    />
                    <div class="flex justify-between items-center mt-1.5">
                        <p v-if="form.errors.national_id" class="text-sm text-red-600">{{ form.errors.national_id }}</p>
                        <p v-else class="text-xs text-[#666666]">
                            <span :class="{'text-red-500': form.national_id.length !== 16 && form.national_id.length > 0}">{{ form.national_id.length }}</span>/16
                        </p>
                    </div>
                </div>

                <!-- Alamat Domisili -->
                <div>
                    <label for="address" class="block text-sm text-[#333333] mb-1.5">Alamat Domisili</label>
                    <textarea
                        id="address"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors min-h-[100px] resize-y"
                        v-model="form.address"
                        placeholder="Sesuai KTP"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-1.5 text-sm text-red-600">{{ form.errors.address }}</p>
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
