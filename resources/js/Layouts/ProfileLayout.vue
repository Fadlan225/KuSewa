<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import ProfileMenu from '@/Components/ProfileMenu.vue';
import { Loader2, Camera, Medal, AlertTriangle, ChevronRight, ClipboardList, Wallet, Heart, Briefcase } from 'lucide-vue-next';
import { ref, computed, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const page = usePage();

const user = computed(() => page.props.user || page.props.auth.user);
const total_assets_rented = computed(() => page.props.total_assets_rented || 0);

// Image load error fallback state
const imageError = ref(false);

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
                imageError.value = false;
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

// Generate initials from user's name
const initials = computed(() => {
    const name = user.value?.name ?? '';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0] ? parts[0].substring(0, 2).toUpperCase() : '?';
});

const locationDenied = ref(false);
import { onMounted } from 'vue';

onMounted(() => {
    if (localStorage.getItem('location_denied') === 'true') {
        locationDenied.value = true;
    }
});

const requestLocationPermission = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                localStorage.removeItem('location_denied');
                locationDenied.value = false;
            },
            (error) => {
                alert("Izin lokasi masih ditolak atau diblokir secara permanen oleh browser. Silakan ubah pengaturan situs pada browser Anda.");
            }
        );
    } else {
        alert("Geolocation tidak didukung oleh browser Anda.");
    }
};
</script>

<template>
    <Head title="Profil Saya" />

    <AppLayout>
        <div class="max-w-6xl mx-auto pt-6 pb-24 md:pb-8 px-4 sm:px-6 lg:px-8 flex flex-col md:grid md:grid-cols-12 gap-6 md:items-start">
            
            <!-- LEFT PANEL WRAPPER -->
            <div :class="[route().current('profile.edit') ? 'contents md:flex md:flex-col md:gap-6' : 'hidden md:flex md:flex-col md:gap-6', 'md:col-span-4 md:col-start-1 md:order-1']">
                <!-- Hero Section -->
                <div class="bg-white p-6 shadow-md rounded-2xl flex flex-col items-center gap-6 relative order-1 md:order-none">

                <!-- Foto Profil / Initials -->
                <div class="relative flex-shrink-0 group cursor-pointer" @click="selectNewPhoto">
                    <template v-if="user.avatar && !imageError">
                        <img
                            :src="user.avatar"
                            @error="imageError = true"
                            alt="Foto Profil"
                            class="w-24 h-24 sm:w-20 sm:h-20 rounded-full border-2 border-dashed border-[#FFC000] object-cover shadow-sm transition-opacity duration-200 group-hover:opacity-80"
                        />
                    </template>
                    <div
                        v-else
                        class="w-24 h-24 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-[#0A2540] to-[#466080] text-white flex items-center justify-center font-bold text-2xl sm:text-xl border-2 border-dashed border-[#FFC000] shadow-sm select-none transition-opacity duration-200 group-hover:opacity-80"
                    >
                        {{ initials }}
                    </div>

                    <!-- Loading overlay -->
                    <div v-if="uploadingPhoto" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center">
                        <Loader2 class="text-white text-xl animate-spin" />
                    </div>

                    <!-- Camera icon hover -->
                    <div v-else class="absolute -bottom-0 -right-0 bg-white p-1.5 rounded-full shadow-lg border border-gray-100 flex items-center justify-center text-[#FFC000] hover:scale-110 transition-transform">
                        <Camera class="text-xs" />
                    </div>
                </div>
                <input type="file" class="hidden" ref="photoInput" @change="handleFileChange" accept="image/*">

                <!-- Informasi Profil -->
                <div class="flex-grow text-center w-full">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0A2540] leading-tight">
                        {{ user.name }}
                    </h1>

                    <!-- Badge status keanggotaan -->
                    <div class="flex justify-center mt-4">
                        <div class="flex items-center space-x-2 bg-[#F8F9FA] px-3.5 py-1.5 rounded-full text-xs border border-gray-100">
                            <Medal class="text-[#FFC000]" />
                            <span class="text-[#000000] font-medium">
                                Penyewa Aktif <span class="mx-1.5 text-gray-300">|</span> Total Aset Disewa: <strong>{{ total_assets_rented }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Menu -->
            <ProfileMenu class="order-3 md:order-none" :user="user" />
            </div>

            <!-- MAIN CONTENT SLOT -->
            <div :class="['md:col-span-8 md:col-start-5 w-full order-2 md:order-2', route().current('profile.edit') ? 'flex flex-col gap-6' : 'block']">
                <slot />
            </div>
        </div>
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
    </AppLayout>
</template>
