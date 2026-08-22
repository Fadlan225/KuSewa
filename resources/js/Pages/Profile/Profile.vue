<script setup>
import { Loader2, Camera, Trash2, X, Image as ImageIcon } from 'lucide-vue-next';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import SettingsForms from './Partials/SettingsForms.vue';
import BottomSheet from '@/Components/ui/BottomSheet.vue';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';
import UserAvatar from '@/Components/ui/Icons/UserAvatar.vue';
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

// Photo upload state (for Mobile Only)
const photoInput = ref(null);
const cameraInput = ref(null);
const uploadingPhoto = ref(false);
const showPhotoMenu = ref(false);

const showCropModal = ref(false);
const imageToCrop = ref(null);
const cropperImg = ref(null);
let cropperInstance = null;
const originalFile = ref(null);

const selectNewPhoto = () => {
    showPhotoMenu.value = true;
};

const selectCamera = () => {
    showPhotoMenu.value = false;
    if (cameraInput.value) {
        cameraInput.value.click();
    }
};

const selectGallery = () => {
    showPhotoMenu.value = false;
    if (photoInput.value) {
        photoInput.value.click();
    }
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

const deletePhoto = () => {
    showPhotoMenu.value = false;
    uploadingPhoto.value = true;
    router.delete(route('profile.photo.destroy'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (photoInput.value) photoInput.value.value = null;
        },
        onError: () => {
            alert('Gagal menghapus foto profil.');
        },
        onFinish: () => {
            uploadingPhoto.value = false;
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Profil" />

    <!-- MOBILE VIEW (Standalone layout) -->
    <div class="md:hidden min-h-screen bg-white pb-24 text-[#333333] font-sans">
        <DetailNavbar title="Profil Saya" backUrl="/profile" :forceBackUrl="true" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <main class="max-w-3xl mx-auto py-6 px-5 sm:px-6 lg:px-8 space-y-8">
            <!-- Hero / Profile Photo -->
            <div class="flex flex-col items-center justify-center pt-2 pb-4">
                <div class="relative group cursor-pointer" @click="selectNewPhoto">
                    <template v-if="user.avatar">
                        <img
                            :src="user.avatar"
                            alt="Foto Profil"
                            class="w-24 h-24 rounded-full shadow-sm object-cover transition-opacity duration-200 group-hover:opacity-80"
                        />
                    </template>
                    <div
                        v-else
                        class="w-24 h-24 rounded-full bg-[#f8f9fa] flex items-center justify-center shadow-sm select-none transition-opacity duration-200 group-hover:opacity-80 overflow-hidden"
                    >
                        <UserAvatar :user="user" />
                    </div>

                    <!-- Loading overlay -->
                    <div v-if="uploadingPhoto" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center z-20">
                        <Loader2 class="text-white text-xl animate-spin" />
                    </div>

                    <!-- Camera icon hover -->
                    <div v-if="!uploadingPhoto" class="absolute -bottom-2 -right-2 bg-[#FFC000] w-8 h-8 rounded-full shadow-md flex items-center justify-center text-white hover:scale-110 transition-transform z-10" title="Ubah Foto">
                        <Camera class="w-4 h-4" />
                    </div>
                </div>
                <input type="file" class="hidden" ref="photoInput" @change="handleFileChange" accept="image/*">
                <input type="file" class="hidden" ref="cameraInput" @change="handleFileChange" accept="image/*" capture="user">
            </div>

            <!-- Forms -->
            <SettingsForms
                :must-verify-email="mustVerifyEmail"
                :status="status"
                :user="user"
                :owner_profile="owner_profile"
                :bank_account="bank_account"
            />
        </main>

        <!-- Mobile Photo Menu Bottom Sheet -->
        <BottomSheet v-model="showPhotoMenu" title="Foto profil" heightClass="h-auto pb-6">
            <div class="flex flex-col mt-2 px-5">
                <button @click="selectCamera" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-xl transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <Camera class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Kamera</span>
                </button>
                <button @click="selectGallery" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-xl transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <ImageIcon class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Galeri</span>
                </button>
                <button v-if="user.avatar" @click="deletePhoto" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-xl transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <Trash2 class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Hapus Foto Profil</span>
                </button>
            </div>
        </BottomSheet>

        <!-- Mobile Crop Modal -->
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

    <!-- DESKTOP VIEW (Using ProfileLayout) -->
    <div class="hidden md:block">
        <ProfileLayout>
            <div class="bg-white p-6 shadow-md rounded-2xl">
                <!-- Forms -->
                <SettingsForms
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :user="user"
                    :owner_profile="owner_profile"
                    :bank_account="bank_account"
                />
            </div>
        </ProfileLayout>
    </div>
</template>
