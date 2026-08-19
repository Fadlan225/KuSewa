<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Loader2, Camera, Medal, AlertTriangle, ChevronRight, ClipboardList, Wallet, Heart, Briefcase } from 'lucide-vue-next';
import { ref, computed, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    total_assets_rented: {
        type: Number,
        default: 0
    },
    bookings_count: {
        type: Number,
        default: 0
    },
    unpaid_bookings_count: {
        type: Number,
        default: 0
    },
    favorite_assets_count: {
        type: Number,
        default: 0
    }
});

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
    const name = props.user?.name ?? '';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0] ? parts[0].substring(0, 2).toUpperCase() : '?';
});

// Mendefinisikan class FontAwesome untuk setiap menu
const accountMenuItems = [
    { label: 'Profil Saya', icon: 'fa-regular fa-user', route: route('profile.settings') },
    { label: 'Aktivitas', icon: 'fa-solid fa-chart-line', route: route('aktivitas.hub') },
];

const settingsMenuItems = [
    { label: 'Bahasa', icon: 'fa-solid fa-globe', route: '#' },
    { label: 'Notifikasi', icon: 'fa-regular fa-bell', route: '#' },
];

const helpMenuItems = [
    { label: 'Pusat Bantuan', icon: 'fa-solid fa-circle-info', route: route('bantuan') },
    { label: 'Pelayanan Pelanggan', icon: 'fa-solid fa-headset', route: route('hubungi-kami') },
    { label: 'Keluar', icon: 'fa-solid fa-arrow-right-from-bracket text-red-500', action: 'logout' },
];

const showLogoutModal = ref(false);

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
        <div class="max-w-4xl mx-auto pt-6 pb-24 md:pb-8 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Hero Section -->
            <div class="bg-white p-6 shadow-md rounded-2xl flex flex-col md:flex-row items-center md:items-center space-y-6 md:space-y-0 md:space-x-6 relative">

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
                <div class="flex-grow text-center md:text-left w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0A2540] leading-tight pr-0 md:pr-20">
                        {{ user.name }}
                    </h1>

                    <!-- Badge status keanggotaan -->
                    <div class="flex justify-center md:justify-start mt-4">
                        <div class="flex items-center space-x-2 bg-[#F8F9FA] px-3.5 py-1.5 rounded-full text-xs border border-gray-100">
                            <Medal class="text-[#FFC000]" />
                            <span class="text-[#000000] font-medium">
                                Penyewa Aktif <span class="mx-1.5 text-gray-300">|</span> Total Aset Disewa: <strong>{{ total_assets_rented }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesan Izin Lokasi (ditampilkan jika ditolak) -->
            <div v-if="locationDenied" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-2xl shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <AlertTriangle class="text-amber-500 text-lg" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-amber-800">Izin Lokasi Ditolak</h3>
                        <p class="text-sm text-amber-700 mt-1">
                            Izinkan lokasi pada pengaturan browser Anda untuk melihat rekomendasi <strong>Aset Dekat Anda</strong> di halaman Beranda.
                        </p>
                    </div>
                </div>
                <button
                    @click="requestLocationPermission"
                    class="shrink-0 px-4 py-2 bg-amber-400 text-amber-900 font-bold text-xs rounded hover:bg-amber-500 transition-colors w-full sm:w-auto"
                >
                    Izinkan Lokasi
                </button>
            </div>

            <!-- Bagian Ringkasan Pesanan -->
            <div class="bg-white p-6 shadow-md rounded-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-[#0A2540]">Pesanan Saya</h2>
                    <Link
                        :href="route('aktivitas.hub')"
                        class="text-xs sm:text-sm font-semibold text-[#466080] hover:text-[#0A2540] transition-colors flex items-center space-x-1"
                    >
                        <span>Lihat Riwayat Pesanan</span>
                        <ChevronRight class="text-[10px] ml-1 text-[#6C757D]" />
                    </Link>
                </div>

                <div class="grid grid-cols-3 gap-4 sm:gap-6 text-center">
                    <!-- Booking -->
                    <Link :href="route('aktivitas.transaksi', { status: 'Berlangsung' })" class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-[#FFC000]/10 transition-colors duration-200">
                            <ClipboardList class="text-2xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                            <span v-if="bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ bookings_count }}</span>
                        </div>
                        <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Booking</p>
                    </Link>

                    <!-- Belum Bayar -->
                    <Link :href="route('aktivitas.transaksi', { status: 'Belum Bayar' })" class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-red-50 transition-colors duration-200">
                            <Wallet class="text-2xl text-[#0A2540] group-hover:text-red-500 transition-colors" />
                            <span v-if="unpaid_bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ unpaid_bookings_count }}</span>
                        </div>
                        <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-red-500 transition-colors">Belum Bayar</p>
                    </Link>

                    <!-- Aset Favorit -->
                    <Link :href="route('favorites.index')" class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-pink-50 transition-colors duration-200">
                            <Heart class="text-2xl text-[#0A2540] group-hover:text-pink-500 transition-colors" />
                            <span v-if="favorite_assets_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ favorite_assets_count }}</span>
                        </div>
                        <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-pink-500 transition-colors">Aset Favorit</p>
                    </Link>
                </div>
            </div>

            <!-- Daftar Menu -->
            <div class="space-y-6">
                <!-- Grup Menu 'Akun Saya' -->
                <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
                    <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Akun Saya</h3>
                    <div class="border-t border-[#F8F9FA] mb-2"></div>

                    <Link
                        v-for="(item, index) in accountMenuItems"
                        :key="index"
                        :href="item.route"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <AppIcon :iconClass="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']" />
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                        </div>
                        <ChevronRight class="text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200" />
                    </Link>

                    <!-- Profile Bisnis (Bagi yang belum jadi mitra) -->
                    <Link
                        v-if="!user.is_owner"
                        href="#"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <Briefcase class="text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors" />
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Buka Bisnis / Jadi Mitra</span>
                        </div>
                        <ChevronRight class="text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200" />
                    </Link>
                </div>

                <!-- Grup Menu 'Pengaturan Aplikasi' -->
                <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
                    <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Pengaturan Aplikasi</h3>
                    <div class="border-t border-[#F8F9FA] mb-2"></div>

                    <Link
                        v-for="(item, index) in settingsMenuItems"
                        :key="index"
                        :href="item.route"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <AppIcon :iconClass="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']" />
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                        </div>
                        <ChevronRight class="text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200" />
                    </Link>
                </div>

                <!-- Grup Menu 'Bantuan & Lainnya' -->
                <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
                    <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Bantuan & Lainnya</h3>
                    <div class="border-t border-[#F8F9FA] mb-2"></div>

                    <template v-for="(item, index) in helpMenuItems" :key="index">
                        <button
                            v-if="item.action === 'logout'"
                            @click="showLogoutModal = true"
                            class="w-full flex items-center justify-between py-3 border-b border-gray-50 hover:bg-red-50 px-3 rounded-xl transition-colors duration-150 group"
                        >
                            <div class="flex items-center space-x-4">
                                <AppIcon :iconClass="[item.icon, 'text-lg text-[#6C757D] group-hover:text-red-500 w-6 text-center transition-colors']" />
                                <span class="text-sm sm:text-base font-semibold text-red-500 group-hover:text-red-600 transition-colors">{{ item.label }}</span>
                            </div>
                            <ChevronRight class="text-sm text-red-500 group-hover:text-red-600 transition-all duration-200 group-hover:translate-x-1" />
                        </button>
                        <Link
                            v-else
                            :href="item.route"
                            class="w-full flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                        >
                            <div class="flex items-center space-x-4">
                                <AppIcon :iconClass="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']" />
                                <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                            </div>
                            <ChevronRight class="text-sm text-[#6C757D] group-hover:text-[#FFC000] transition-all duration-200 group-hover:translate-x-1" />
                        </Link>
                    </template>
                </div>
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

        <!-- Logout Modal -->
        <Teleport to="body" v-if="showLogoutModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
                    <h2 class="text-xl font-bold text-gray-900">Yakin ingin keluar dari akun?</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Kamu tetap bisa menjelajahi KuSewa, tetapi perlu login kembali untuk melakukan booking atau mengelola aset.
                    </p>

                    <div class="mt-8 flex flex-col gap-3">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="showLogoutModal = false"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Ya, Keluar
                        </Link>

                        <button
                            type="button"
                            @click="showLogoutModal = false"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
                        >
                            Tidak
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
