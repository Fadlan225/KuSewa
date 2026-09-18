<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import ProfileMenu from '@/Components/ProfileMenu.vue';
import { Loader2, Camera, Medal, AlertTriangle, ChevronRight, ClipboardList, Wallet, Heart, Briefcase, Trash2, X, Image as ImageIcon, User } from 'lucide-vue-next';
import { ref, computed, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import UserAvatar from '@/Components/ui/Icons/UserAvatar.vue';
import BottomSheet from '@/Components/ui/BottomSheet.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const page = usePage();

const props = defineProps({
    isDashboard: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Profil Saya'
    }
});

const user = computed(() => page.props.user || page.props.auth.user);
const total_assets_rented = computed(() => page.props.total_assets_rented || 0);

// Image load error fallback state
const imageError = ref(false);

// Progress bar calculation
const profileCompletion = computed(() => {
    let filled = 0;
    const fields = [
        'name', 'email', 'phone', 'gender',
        'date_of_birth', 'place_of_birth_code', 'marital_status',
        'occupation', 'nationality'
    ];
    
    // Check 9 fields
    fields.forEach(field => {
        if (user.value && user.value[field]) {
            filled++;
        }
    });
    
    // Field 10 is profile_photo or avatar (via Google auth)
    if (user.value && (user.value.profile_photo || user.value.avatar)) {
        filled++;
    }
    
    const percentage = Math.round((filled / 10) * 100);
    return {
        filled,
        total: 10,
        percentage
    };
});

// Photo upload state
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

const showPreviewModal = ref(false);

const previewPhoto = () => {
    showPhotoMenu.value = false;
    showPreviewModal.value = true;
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

const deletePhoto = () => {
    showPhotoMenu.value = false;
    uploadingPhoto.value = true;
    router.delete(route('profile.photo.destroy'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            imageError.value = false;
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
    <Head :title="title" />

    <component :is="isDashboard ? DashboardLayout : AppLayout" :title="title">
        <div :class="['max-w-6xl mx-auto pb-24 md:pb-8 px-4 sm:px-6 lg:px-8 flex flex-col md:grid md:grid-cols-12 gap-6 md:items-start', isDashboard ? 'pt-0' : 'pt-6']">
            
            <!-- LEFT PANEL WRAPPER -->
            <div :class="[(route().current('profile.edit') || route().current('owner.profile')) ? 'contents md:flex md:flex-col md:gap-6' : 'hidden md:flex md:flex-col md:gap-6', 'md:col-span-4 md:col-start-1 md:order-1']">
                <!-- Hero Section -->
                <div class="bg-white p-6 shadow-md rounded-lg flex flex-col items-center gap-6 relative order-1 md:order-none">

                <!-- Foto Profil / Initials -->
                <div class="relative flex-shrink-0 group cursor-pointer" @click="selectNewPhoto">
                    <template v-if="user.avatar && !imageError">
                        <img
                            :src="user.avatar"
                            @error="imageError = true"
                            alt="Foto Profil"
                            class="w-24 h-24 sm:w-20 sm:h-20 rounded-full object-cover shadow-sm transition-opacity duration-200 group-hover:opacity-80"
                        />
                    </template>
                    <div
                        v-else
                        class="w-24 h-24 sm:w-20 sm:h-20 rounded-full bg-[#f8f9fa] flex items-center justify-center shadow-sm select-none transition-opacity duration-200 group-hover:opacity-80 overflow-hidden"
                    >
                        <UserAvatar :user="user" />
                    </div>

                    <!-- Loading overlay -->
                    <div v-if="uploadingPhoto" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center z-20">
                        <Loader2 class="text-white text-xl animate-spin" />
                    </div>

                    <!-- Camera icon hover -->
                    <div v-if="!uploadingPhoto" class="absolute -bottom-2 -right-2 bg-[#FFC000] w-8 h-8 sm:w-9 sm:h-9 rounded-full shadow-md flex items-center justify-center text-white hover:scale-110 transition-transform z-10" title="Ubah Foto">
                        <Camera class="w-4 h-4 sm:w-5 sm:h-5" />
                    </div>
                </div>
                <input type="file" class="hidden" ref="photoInput" @change="handleFileChange" accept="image/*">
                <input type="file" class="hidden" ref="cameraInput" @change="handleFileChange" accept="image/*" capture="user">

                <!-- Informasi Profil -->
                <div class="flex-grow text-center w-full">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0A2540] leading-tight">
                        {{ user.name }}
                    </h1>

                    <!-- Progress Bar Kelengkapan Profil -->
                    <div v-if="profileCompletion.percentage < 100 && user.role !== 'admin'" class="flex flex-col mt-5 w-full mx-auto max-w-sm px-4">
                        <div class="w-full max-w-[280px] mx-auto">
                            <!-- Bar -->
                            <div class="w-full bg-gray-200 h-1.5">
                                <div class="bg-[#FFC000] h-1.5 transition-all duration-500 ease-out" :style="{ width: profileCompletion.percentage + '%' }"></div>
                            </div>
                            
                            <!-- Angka Persen & Step -->
                            <div class="flex justify-between items-center mt-1.5">
                                <span class="text-sm font-bold text-[#0A2540]">{{ profileCompletion.percentage }}%</span>
                                <span class="text-xs font-medium text-gray-500">{{ profileCompletion.filled }} / {{ profileCompletion.total }} data terisi</span>
                            </div>
                        </div>
                        
                        <!-- Teks Penjelasan -->
                        <p class="text-[13px] text-gray-600 text-center mt-3 leading-relaxed font-medium">
                            Profil yang lengkap bisa membantu kami memberikan rekomendasi yang lebih akurat.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Daftar Menu -->
            <ProfileMenu class="order-3 md:order-none" :user="user" :isDashboard="isDashboard" />
            </div>

            <!-- MAIN CONTENT SLOT -->
            <div :class="['md:col-span-8 md:col-start-5 w-full order-2 md:order-2', (route().current('profile.edit') || route().current('owner.profile')) ? 'flex flex-col gap-6' : 'block']">
                <slot />
            </div>
        </div>
        
        <!-- Mobile Photo Menu Bottom Sheet -->
        <BottomSheet v-model="showPhotoMenu" title="Foto profil" heightClass="h-auto pb-6">
            <div class="flex flex-col mt-2 px-5">
                <button @click="previewPhoto" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <User class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Lihat Foto Profil</span>
                </button>
                <button @click="selectCamera" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <Camera class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Kamera</span>
                </button>
                <button @click="selectGallery" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <ImageIcon class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Galeri</span>
                </button>
                <button v-if="user.avatar" @click="deletePhoto" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                    <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                        <Trash2 class="w-6 h-6" />
                    </div>
                    <span class="text-base font-medium text-gray-800">Hapus Foto Profil</span>
                </button>
            </div>
        </BottomSheet>

        <!-- Desktop Photo Menu Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
            >
                <div v-if="showPhotoMenu" class="fixed inset-0 z-[150] hidden md:flex items-center justify-center bg-black/50 transition-opacity" @click.self="showPhotoMenu = false">
                    <div class="bg-white rounded-lg w-full max-w-sm p-5 shadow-xl transition-transform">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-10">
                                <button @click="showPhotoMenu = false" class="text-gray-500 hover:text-gray-700 transition-colors p-1.5 rounded-full hover:bg-gray-100">
                                    <X class="w-6 h-6" />
                                </button>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Foto profil</h3>
                            <div class="w-10 flex justify-end"></div>
                        </div>
                        
                        <div class="flex flex-col mt-2">
                            <button @click="previewPhoto" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                                <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                                    <User class="w-6 h-6" />
                                </div>
                                <span class="text-base font-medium text-gray-800">Lihat Foto Profil</span>
                            </button>
                            <button @click="selectCamera" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                                <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                                    <Camera class="w-6 h-6" />
                                </div>
                                <span class="text-base font-medium text-gray-800">Kamera</span>
                            </button>
                            <button @click="selectGallery" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                                <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                                    <ImageIcon class="w-6 h-6" />
                                </div>
                                <span class="text-base font-medium text-gray-800">Galeri</span>
                            </button>
                            <button v-if="user.avatar" @click="deletePhoto" class="flex items-center gap-5 p-3 hover:bg-gray-50 rounded-lg transition-colors text-left w-full">
                                <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-600">
                                    <Trash2 class="w-6 h-6" />
                                </div>
                                <span class="text-base font-medium text-gray-800">Hapus Foto Profil</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Crop Modal -->
        <Teleport to="body" v-if="showCropModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/80 overflow-hidden">
                <div class="bg-white rounded-lg w-full max-w-lg p-6 shadow-xl flex flex-col max-h-[90vh]">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Sesuaikan Foto Profil</h2>

                    <div class="flex-grow min-h-0 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center" style="max-height: 60vh;">
                        <img ref="cropperImg" :src="imageToCrop" alt="Mulai potong gambar" class="max-w-full max-h-full block">
                    </div>

                    <div class="mt-6 flex justify-end gap-3 shrink-0">
                        <button
                            type="button"
                            @click="cancelCrop"
                            class="px-6 py-2.5 bg-white border border-gray-300 rounded-lg font-bold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitCroppedImage"
                            class="px-6 py-2.5 bg-primary border border-transparent rounded-lg font-bold text-sm text-white hover:bg-primary/90 focus:outline-none transition-colors"
                        >
                            Crop & Upload
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Preview Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
            >
                <div v-if="showPreviewModal" class="fixed inset-0 z-[250] flex items-center justify-center bg-black/90 transition-opacity p-4" @click.self="showPreviewModal = false">
                    <button @click="showPreviewModal = false" class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/70 hover:text-white transition-colors p-2">
                        <X class="w-8 h-8 sm:w-10 sm:h-10" />
                    </button>
                    <div class="relative max-w-full max-h-full">
                        <template v-if="user.avatar && !imageError">
                            <img :src="user.avatar" alt="Foto Profil" class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg shadow-2xl">
                        </template>
                        <div v-else class="w-64 h-64 sm:w-[400px] sm:h-[400px] rounded-full bg-[#f8f9fa] flex items-center justify-center shadow-2xl overflow-hidden select-none">
                            <UserAvatar :user="user" />
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </component>
</template>
