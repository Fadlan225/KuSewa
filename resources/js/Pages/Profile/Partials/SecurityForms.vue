<script setup>
import UpdatePasswordForm from './UpdatePasswordForm.vue';
import DeleteUserForm from './DeleteUserForm.vue';
import { Chrome } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

const showUnlinkGoogleModal = ref(false);

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
</script>

<template>
    <div class="space-y-8">
        <!-- Keamanan Section -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Password & Keamanan</h3>
            <UpdatePasswordForm />
        </div>

        <!-- Akun Tertaut Section -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Akun Tertaut</h3>
            
            <!-- Google Account Link -->
            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl bg-[#F8F9FA] hover:bg-white transition-colors duration-200 shadow-sm">
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
                <div class="bg-white rounded-2xl max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
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
    </div>
</template>
