<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true,
        default: () => ({
            name: 'Fadlan Ramadhan',
            email: 'fadlan.ramadhan@example.com',
            phone: '0812-3456-7890',
            is_owner: false,
        })
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
    { label: 'Profile', icon: 'fa-regular fa-user', route: route('profile.edit') },
    { label: 'Favorite', icon: 'fa-regular fa-heart', route: route('favorites.index') },
    { label: 'Ulasan', icon: 'fa-regular fa-comment-dots', route: '#' },
    { label: 'Aktivitas', icon: 'fa-solid fa-chart-line', route: '#' },
    { label: 'Terakhir di lihat', icon: 'fa-regular fa-clock', route: '#' },
];

const settingsMenuItems = [
    { label: 'Bahasa', icon: 'fa-solid fa-globe', route: '#' },
    { label: 'Notifikasi', icon: 'fa-regular fa-bell', route: '#' },
];

const helpMenuItems = [
    { label: 'Pusat Bantuan', icon: 'fa-solid fa-circle-info', route: '#' },
    { label: 'Customer Services', icon: 'fa-solid fa-headset', route: '#' },
    { label: 'FAQ', icon: 'fa-regular fa-circle-question', route: '#' },
];
</script>

<template>
    <Head title="Profil" />

    <AppLayout>
        <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Hero Section -->
            <div class="bg-white p-6 shadow-md rounded-2xl flex flex-col md:flex-row items-center md:items-center space-y-6 md:space-y-0 md:space-x-6 relative">

                <!-- Foto Profil / Initials -->
                <div class="relative flex-shrink-0">
                    <template v-if="user.avatar && !imageError">
                        <img
                            :src="user.avatar"
                            @error="imageError = true"
                            alt="Foto Profil"
                            class="w-24 h-24 sm:w-20 sm:h-20 rounded-full border-2 border-dashed border-[#FFC000] object-cover shadow-sm"
                        />
                    </template>
                    <div
                        v-else
                        class="w-24 h-24 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-[#0A2540] to-[#466080] text-white flex items-center justify-center font-bold text-2xl sm:text-xl border-2 border-dashed border-[#FFC000] shadow-sm select-none"
                    >
                        {{ initials }}
                    </div>
                    <Link
                        :href="route('profile.edit')"
                        class="absolute -bottom-1 -right-1 bg-white px-2 py-0.5 rounded-full text-[10px] font-bold text-[#FFC000] border border-gray-100 shadow-xs hover:bg-[#F8F9FA] transition-colors"
                    >
                        Profil
                    </Link>
                </div>

                <!-- Informasi Profil -->
                <div class="flex-grow text-center md:text-left w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0A2540] leading-tight pr-0 md:pr-20">
                        {{ user.name }}
                    </h1>

                    <!-- Detail Kontak dan Keanggotaan -->
                    <div class="mt-2 flex flex-col sm:flex-row items-center justify-center md:justify-start sm:space-x-3 space-y-1 sm:space-y-0 text-sm text-[#466080]">
                        <span class="flex items-center text-[#000000]">
                            <i class="fa-regular fa-envelope mr-2 text-[#6C757D]"></i>
                            {{ user.email }}
                        </span>
                        <span class="hidden sm:inline text-gray-300">|</span>
                        <span class="flex items-center text-[#000000]">
                            <i class="fa-solid fa-phone mr-2 text-[#6C757D]"></i>
                            {{ user.phone }}
                        </span>
                    </div>

                    <!-- Badge status keanggotaan -->
                    <div class="flex justify-center md:justify-start mt-4">
                        <div class="flex items-center space-x-2 bg-[#F8F9FA] px-3.5 py-1.5 rounded-full text-xs border border-gray-100">
                            <i class="fa-solid fa-medal text-[#FFC000]"></i>
                            <span class="text-[#000000] font-medium">
                                Penyewa Aktif <span class="mx-1.5 text-gray-300">|</span> Total Aset Disewa: <strong>{{ total_assets_rented }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Ringkasan Pesanan -->
            <div class="bg-white p-6 shadow-md rounded-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-[#0A2540]">Pesanan Saya</h2>
                    <Link
                        href="#"
                        class="text-xs sm:text-sm font-semibold text-[#466080] hover:text-[#0A2540] transition-colors flex items-center space-x-1"
                    >
                        <span>Lihat Riwayat Pesanan</span>
                        <i class="fa-solid fa-chevron-right text-[10px] ml-1 text-[#6C757D]"></i>
                    </Link>
                </div>

                <div class="grid grid-cols-3 gap-4 sm:gap-6 text-center">
                    <!-- Booking -->
                    <div class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-[#FFC000]/10 transition-colors duration-200">
                            <i class="fa-solid fa-clipboard-list text-2xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors"></i>
                            <span v-if="bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ bookings_count }}</span>
                        </div>
                        <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Booking</p>
                    </div>

                    <!-- Belum Bayar -->
                    <div class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-red-50 transition-colors duration-200">
                            <i class="fa-solid fa-wallet text-2xl text-[#0A2540] group-hover:text-red-500 transition-colors"></i>
                            <span v-if="unpaid_bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ unpaid_bookings_count }}</span>
                        </div>
                        <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-red-500 transition-colors">Belum Bayar</p>
                    </div>

                    <!-- Aset Favorit -->
                    <Link :href="route('favorites.index')" class="flex flex-col items-center group cursor-pointer">
                        <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-pink-50 transition-colors duration-200">
                            <i class="fa-solid fa-heart text-2xl text-[#0A2540] group-hover:text-pink-500 transition-colors"></i>
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
                            <i :class="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']"></i>
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200"></i>
                    </Link>

                    <!-- Dashboard (Owner) atau Profile Bisnis (User) -->
                    <Link
                        v-if="user.is_owner"
                        href="#"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <i class="fa-solid fa-border-all text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors"></i>
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Dashboard</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200"></i>
                    </Link>
                    <Link
                        v-else
                        href="#"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <i class="fa-solid fa-briefcase text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors"></i>
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Profile Bisnis</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200"></i>
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
                            <i :class="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']"></i>
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200"></i>
                    </Link>
                </div>

                <!-- Grup Menu 'Bantuan & Lainnya' -->
                <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
                    <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Bantuan & Lainnya</h3>
                    <div class="border-t border-[#F8F9FA] mb-2"></div>

                    <Link
                        v-for="(item, index) in helpMenuItems"
                        :key="index"
                        :href="item.route"
                        class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group"
                    >
                        <div class="flex items-center space-x-4">
                            <i :class="[item.icon, 'text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors']"></i>
                            <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ item.label }}</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200"></i>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
