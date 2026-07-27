<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    owner_profile: { type: Object, default: null },
    bank_account: { type: Object, default: null },
});

const user = computed(() => props.user);

const initials = computed(() => {
    const name = user.value?.name ?? '';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return parts[0] ? parts[0].substring(0, 2).toUpperCase() : '?';
});

// Photo upload state
const photoInput = ref(null);
const uploadingPhoto = ref(false);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhoto = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('photo', file);

    uploadingPhoto.value = true;
    router.post(route('profile.photo'), formData, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            uploadingPhoto.value = false;
        }
    });
};

// Accordion state
const activeAccordion = ref(null);
const toggleAccordion = (tab) => {
    activeAccordion.value = activeAccordion.value === tab ? null : tab;
};

// Logout modal state
const showLogoutModal = ref(false);
</script>

<template>
    <Head title="Pengaturan Profil" />

    <div class="min-h-screen bg-[#F8F9FA] pb-24 text-[#1D1D1F] font-sans">
        <DetailNavbar backUrl="/profile" :showSections="false" :showShare="false" :showFavorite="false" />

        <main class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Hero / Profile Photo -->
            <div class="flex flex-col items-center justify-center p-6 mb-8">
                <div class="relative group cursor-pointer" @click="selectNewPhoto">
                    <template v-if="user.avatar">
                        <img
                            :src="user.avatar"
                            alt="Foto Profil"
                            class="w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-md object-cover transition-opacity duration-200 group-hover:opacity-80"
                        />
                    </template>
                    <div
                        v-else
                        class="w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-gradient-to-tr from-[#0A2540] to-[#466080] text-white flex items-center justify-center font-bold text-3xl sm:text-4xl border-4 border-white shadow-md select-none transition-opacity duration-200 group-hover:opacity-80"
                    >
                        {{ initials }}
                    </div>

                    <!-- Loading overlay -->
                    <div v-if="uploadingPhoto" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-spinner fa-spin text-white text-2xl"></i>
                    </div>

                    <!-- Camera icon hover -->
                    <div v-else class="absolute bottom-1 right-1 bg-white p-2 rounded-full shadow-lg border border-gray-100 flex items-center justify-center text-[#FFC000] hover:scale-110 transition-transform">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <input type="file" class="hidden" ref="photoInput" @change="updatePhoto" accept="image/*">

                <h2 class="mt-4 text-xl font-extrabold text-[#0A2540]">{{ user.name }}</h2>
                <p class="text-sm text-[#466080]">{{ user.email }}</p>
            </div>

            <!-- Data Pribadi Card -->
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden transition-all duration-300">
                <button @click="toggleAccordion('data_pribadi')" class="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50">
                    <div>
                        <h3 class="text-base font-bold text-[#0A2540]">Data Pribadi</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Ubah informasi profil dan detail kontak Anda</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 'data_pribadi' }"></i>
                </button>
                <div v-show="activeAccordion === 'data_pribadi'" class="px-6 pb-6 border-t border-gray-50 pt-4">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :owner_profile="owner_profile"
                        :bank_account="bank_account"
                    />
                </div>
            </div>

            <!-- Keamanan Card -->
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden transition-all duration-300">
                <button @click="toggleAccordion('keamanan')" class="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50">
                    <div>
                        <h3 class="text-base font-bold text-[#0A2540]">Keamanan</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Perbarui password dan akses login</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 'keamanan' }"></i>
                </button>
                <div v-show="activeAccordion === 'keamanan'" class="px-6 pb-6 border-t border-gray-50 pt-4">
                    <UpdatePasswordForm />
                </div>
            </div>

            <!-- Hapus Akun Card -->
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden transition-all duration-300">
                <button @click="toggleAccordion('hapus_akun')" class="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none hover:bg-red-50/30">
                    <div>
                        <h3 class="text-base font-bold text-red-600">Hapus Akun</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Penghapusan akun permanen dan seluruh data Anda</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-red-400 transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 'hapus_akun' }"></i>
                </button>
                <div v-show="activeAccordion === 'hapus_akun'" class="px-6 pb-6 border-t border-gray-50 pt-4">
                    <DeleteUserForm />
                </div>
            </div>

            <!-- Logout Button -->
            <div class="pt-6 flex justify-center">
                <button
                    @click="showLogoutModal = true"
                    class="flex items-center space-x-2 text-red-600 font-bold hover:text-red-700 bg-white border border-red-100 shadow-xs px-6 py-3 rounded-full hover:bg-red-50 transition-colors"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Keluar dari Akun</span>
                </button>
            </div>
        </main>

        <!-- Logout Modal -->
        <Teleport to="body" v-if="showLogoutModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-arrow-right-from-bracket text-2xl text-red-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Konfirmasi Keluar</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Apakah Anda yakin ingin keluar dari akun ini? Anda harus masuk kembali untuk menggunakan aplikasi.
                    </p>

                    <div class="mt-8 flex flex-col gap-3">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="showLogoutModal = false"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Ya, Keluar
                        </Link>

                        <button
                            type="button"
                            @click="showLogoutModal = false"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
