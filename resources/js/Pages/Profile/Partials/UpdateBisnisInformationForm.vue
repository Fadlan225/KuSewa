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
        <div class="space-y-5">
            <div class="space-y-5">
                <!-- NIK KTP -->
                <div>
                    <label for="national_id" class="block text-sm text-[#333333] mb-1.5">Nomor Induk Kependudukan</label>
                    <input
                        id="national_id"
                        type="text"
                        class="block w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-[15px] text-gray-500 cursor-not-allowed"
                        :value="props.owner_profile?.national_id"
                        disabled
                        placeholder="Belum diatur"
                    />
                </div>

                <!-- Alamat Domisili -->
                <div>
                    <label for="address" class="block text-sm text-[#333333] mb-1.5">Alamat Domisili</label>
                    <textarea
                        id="address"
                        class="block w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-[15px] text-gray-500 cursor-not-allowed min-h-[100px] resize-y"
                        :value="props.owner_profile?.address"
                        disabled
                        placeholder="Belum diatur"
                    ></textarea>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 rounded-xl flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    <p class="text-sm text-blue-800">
                        Informasi bisnis Anda bersifat permanen dan digunakan untuk keperluan verifikasi identitas. Jika Anda perlu mengubah data ini, silakan hubungi tim dukungan kami.
                    </p>
                </div>

            </div>
        </div>
    </section>
</template>
