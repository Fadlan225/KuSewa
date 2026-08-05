<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

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

const showCropModal = ref(false);
const imageToCrop = ref(null);
const cropperImg = ref(null);
let cropperInstance = null;
const originalFile = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    originalFile.value = file;
    const reader = new FileReader();
    reader.onload = (event) => {
        imageToCrop.value = event.target.result;
        showCropModal.value = true;

        nextTick(() => {
            if (cropperInstance) cropperInstance.destroy();
            cropperInstance = new Cropper(cropperImg.value, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
            });
        });
    };
    reader.readAsDataURL(file);
};

const cancelCrop = () => {
    showCropModal.value = false;
    if (cropperInstance) cropperInstance.destroy();
    if (photoInput.value) photoInput.value.value = null;
};

const submitCroppedImage = () => {
    if (!cropperInstance) return;

    cropperInstance.getCroppedCanvas().toBlob((blob) => {
        if (!blob) return;

        const file = new File([blob], originalFile.value.name, {
            type: originalFile.value.type,
            lastModified: Date.now(),
        });

        uploadingPhoto.value = true;
        showCropModal.value = false;

        router.post(route('profile.photo'), {
            photo: file
        }, {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true,
            onSuccess: () => {
                if (photoInput.value) photoInput.value.value = null;
            },
            onError: (errors) => {
                if (errors.photo) {
                    alert(errors.photo);
                } else {
                    alert('Gagal mengupload foto.');
                }
                if (photoInput.value) photoInput.value.value = null;
            },
            onFinish: () => {
                uploadingPhoto.value = false;
            }
        });

        cropperInstance.destroy();
    }, originalFile.value.type);
};

// Accordion state
const activeAccordion = ref(null);
const toggleAccordion = (tab) => {
    activeAccordion.value = activeAccordion.value === tab ? null : tab;
};

// Logout modal state
const showLogoutModal = ref(false);

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

const showUnlinkGoogleModal = ref(false);
</script>

<template>
    <Head title="Pengaturan Profil" />

    <div class="min-h-screen bg-white pb-24 text-[#333333] font-sans">
        <DetailNavbar backUrl="/profile" :forceBackUrl="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <main class="max-w-3xl mx-auto py-6 px-5 sm:px-6 lg:px-8 space-y-8">

            <!-- Hero / Profile Photo -->
            <div class="flex flex-col items-center justify-center pt-2 pb-4">
                <div class="relative group cursor-pointer" @click="selectNewPhoto">
                    <template v-if="user.avatar">
                        <img
                            :src="user.avatar"
                            alt="Foto Profil"
                            class="w-24 h-24 rounded-full border-4 border-white shadow-sm object-cover transition-opacity duration-200 group-hover:opacity-80"
                        />
                    </template>
                    <div
                        v-else
                        class="w-24 h-24 rounded-full bg-gradient-to-tr from-[#0A2540] to-[#466080] text-white flex items-center justify-center font-bold text-3xl border-4 border-white shadow-sm select-none transition-opacity duration-200 group-hover:opacity-80"
                    >
                        {{ initials }}
                    </div>

                    <!-- Loading overlay -->
                    <div v-if="uploadingPhoto" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-spinner fa-spin text-white text-xl"></i>
                    </div>

                    <!-- Camera icon hover -->
                    <div v-else class="absolute bottom-0 right-0 bg-white p-1.5 rounded-full shadow-md border border-gray-100 flex items-center justify-center text-primary hover:scale-110 transition-transform">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </div>
                </div>
                <input type="file" class="hidden" ref="photoInput" @change="handleFileChange" accept="image/*">
            </div>

            <!-- Data Pribadi Form -->
            <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
                <h3 class="text-lg font-bold text-[#1D1D1F] mb-6">Data Pribadi</h3>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :owner_profile="owner_profile"
                    :bank_account="bank_account"
                />
            </div>

            <!-- Keamanan Section -->
            <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
                <h3 class="text-lg font-bold text-[#1D1D1F] mb-4">Password & Keamanan</h3>
                <UpdatePasswordForm />
            </div>

            <!-- Hapus Akun Section -->
            <div class="py-2">
                <DeleteUserForm />
            </div>

            <!-- Akun Tertaut Section -->
            <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Akun yang Terhubung</h3>
                    <p class="text-[13px] text-gray-500 mt-1">Masuk lebih mudah dengan menghubungkan akun sosial Anda ke KuSewa.</p>
                </div>

                <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Original Google Icon SVG -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <div class="flex items-center space-x-1.5">
                            <p class="text-[16px] font-bold text-[#1D1D1F]">Google</p>
                            <i v-if="user.is_google_linked" class="fa-solid fa-circle-check text-[#00B14F] text-sm"></i>
                        </div>
                    </div>

                    <button type="button" v-if="!user.is_google_linked" @click.prevent="linkGoogle" class="text-[15px] font-bold text-[#0066FF] hover:text-blue-700 transition-colors">
                        Hubungkan
                    </button>
                    <button type="button" v-else @click.prevent="showUnlinkGoogleModal = true" class="text-[15px] font-bold text-red-500 hover:text-red-700 transition-colors">
                        Putuskan
                    </button>
                </div>
            </div>
        </main>

        <!-- Unlink Google Modal -->
        <Teleport to="body" v-if="showUnlinkGoogleModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-brands fa-google text-2xl text-red-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Putuskan Tautan</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Apakah Anda yakin ingin memutuskan tautan akun Google ini? Anda tidak akan bisa lagi login dengan satu klik menggunakan akun Google tersebut.
                    </p>

                    <div class="mt-8 flex flex-col gap-3">
                        <button
                            @click="unlinkGoogle"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                        >
                            Ya, Putuskan
                        </button>
                        <button
                            @click="showUnlinkGoogleModal = false"
                            class="w-full flex justify-center py-3 px-4 border-2 border-gray-200 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Crop Modal -->
        <Teleport to="body" v-if="showCropModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/80 overflow-hidden">
                <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl flex flex-col max-h-[90vh]">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Sesuaikan Foto Profil</h2>

                    <div class="flex-grow min-h-0 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center" style="max-height: 60vh;">
                        <img ref="cropperImg" :src="imageToCrop" alt="Mulai potong gambar" class="max-w-full max-h-full block">
                    </div>

                    <div class="mt-6 flex justify-end gap-3 shrink-0">
                        <button
                            type="button"
                            @click="cancelCrop"
                            class="px-6 py-2.5 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitCroppedImage"
                            class="px-6 py-2.5 bg-primary border border-transparent rounded-xl font-bold text-sm text-white hover:bg-primary/90 focus:outline-none transition-colors"
                        >
                            Crop & Upload
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
