<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ClipboardList, Wallet, Heart, ChevronRight } from 'lucide-vue-next';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';
import SettingsForms from './Partials/SettingsForms.vue';
import SecurityForms from './Partials/SecurityForms.vue';
import BisnisForms from './Partials/BisnisForms.vue';
import Transaksi from '@/Pages/Home/Activity/Transaksi.vue';
import SearchHistory from '@/Pages/Home/Activity/SearchHistory.vue';
import LastSeen from '@/Pages/Home/LastSeen.vue';
import MyReviews from '@/Pages/Home/Activity/MyReviews.vue';
import Favorite from '@/Pages/Home/Favorite.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    user: { type: Object, required: true },
    bookings_count: { type: Number, default: 0 },
    unpaid_bookings_count: { type: Number, default: 0 },
    favorite_assets_count: { type: Number, default: 0 },
    mustVerifyEmail: Boolean,
    status: String,
    owner_profile: { type: Object, default: null },
    bank_account: { type: Object, default: null },
    tab: { type: String, default: 'profil' },
    bookings: { type: Array, default: () => [] },
    searchLogs: { type: Object, default: () => ({}) },
    lastSeen: { type: Object, default: () => ({}) },
    reviews: { type: Object, default: () => ({}) },
    initialFavorites: { type: Array, default: () => [] },
    categoriesList: { type: Array, default: () => ['Semua'] }
});

const locationDenied = ref(false);

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

    <ProfileLayout>
        <!-- Pesan Izin Lokasi (ditampilkan jika ditolak) -->
        <div v-if="locationDenied" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-2xl shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
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
                <!-- Mobile Link -->
                <Link
                    :href="route('aktivitas.hub')"
                    class="md:hidden text-xs sm:text-sm font-semibold text-[#466080] hover:text-[#0A2540] transition-colors flex items-center space-x-1"
                >
                    <span>Lihat Riwayat Pesanan</span>
                    <ChevronRight class="text-[10px] ml-1 text-[#6C757D]" />
                </Link>
                <!-- Desktop Link -->
                <Link
                    :href="route('profile.edit', { tab: 'transaksi' })"
                    class="hidden md:flex text-xs sm:text-sm font-semibold text-[#466080] hover:text-[#0A2540] transition-colors items-center space-x-1"
                >
                    <span>Lihat Riwayat Pesanan</span>
                    <ChevronRight class="text-[10px] ml-1 text-[#6C757D]" />
                </Link>
            </div>

            <div class="grid grid-cols-3 gap-4 sm:gap-6 text-center">
                <!-- Booking -->
                <Link :href="route('aktivitas.transaksi', { status: 'Berlangsung' })" class="md:hidden flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-[#FFC000]/10 transition-colors duration-200">
                        <ClipboardList class="text-2xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                        <span v-if="bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ bookings_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Booking</p>
                </Link>
                <Link :href="route('profile.edit', { tab: 'transaksi', status: 'Berlangsung' })" class="hidden md:flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-[#FFC000]/10 transition-colors duration-200">
                        <ClipboardList class="text-2xl text-[#0A2540] group-hover:text-[#FFC000] transition-colors" />
                        <span v-if="bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ bookings_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">Booking</p>
                </Link>

                <!-- Belum Bayar -->
                <Link :href="route('aktivitas.transaksi', { status: 'Belum Bayar' })" class="md:hidden flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-red-50 transition-colors duration-200">
                        <Wallet class="text-2xl text-[#0A2540] group-hover:text-red-500 transition-colors" />
                        <span v-if="unpaid_bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ unpaid_bookings_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-red-500 transition-colors">Belum Bayar</p>
                </Link>
                <Link :href="route('profile.edit', { tab: 'transaksi', status: 'Belum Bayar' })" class="hidden md:flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-red-50 transition-colors duration-200">
                        <Wallet class="text-2xl text-[#0A2540] group-hover:text-red-500 transition-colors" />
                        <span v-if="unpaid_bookings_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ unpaid_bookings_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-red-500 transition-colors">Belum Bayar</p>
                </Link>

                <!-- Aset Favorit -->
                <Link :href="route('favorites.index')" class="md:hidden flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-pink-50 transition-colors duration-200">
                        <Heart class="text-2xl text-[#0A2540] group-hover:text-pink-500 transition-colors" />
                        <span v-if="favorite_assets_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ favorite_assets_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-pink-500 transition-colors">Aset Favorit</p>
                </Link>
                <Link :href="route('profile.edit', { tab: 'favorit' })" class="hidden md:flex flex-col items-center group cursor-pointer">
                    <div class="relative bg-[#F8F9FA] p-4 rounded-2xl group-hover:bg-pink-50 transition-colors duration-200">
                        <Heart class="text-2xl text-[#0A2540] group-hover:text-pink-500 transition-colors" />
                        <span v-if="favorite_assets_count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-xs">{{ favorite_assets_count }}</span>
                    </div>
                    <p class="mt-2 text-xs sm:text-sm font-semibold text-[#0A2540] group-hover:text-pink-500 transition-colors">Aset Favorit</p>
                </Link>
            </div>
        </div>

        <!-- Bagian Settings Forms (Desktop Only) -->
        <div class="hidden md:block">
            <SettingsForms
                v-if="tab === 'profil'"
                :must-verify-email="mustVerifyEmail"
                :status="status"
                :user="user"
            />
            <BisnisForms 
                v-if="tab === 'bisnis'"
                :must-verify-email="mustVerifyEmail"
                :status="status"
                :user="user"
                :owner_profile="owner_profile"
                :bank_account="bank_account"
            />
            <SecurityForms
                v-if="tab === 'keamanan'"
                :user="user"
            />

            <!-- Aktivitas Components -->
            <Transaksi
                v-if="tab === 'transaksi'"
                :isComponent="true"
                :bookings="bookings"
            />
            <LastSeen
                v-if="tab === 'terakhir-dilihat'"
                :isComponent="true"
                :initialViews="lastSeen"
            />
            <SearchHistory
                v-if="tab === 'pencarian'"
                :isComponent="true"
                :searchLogs="searchLogs"
            />
            <MyReviews
                v-if="tab === 'ulasan'"
                :isComponent="true"
                :reviews="reviews"
            />
            <Favorite
                v-if="tab === 'favorit'"
                :isComponent="true"
                :initialFavorites="initialFavorites"
                :categoriesList="categoriesList"
            />
        </div>
    </ProfileLayout>
</template>
