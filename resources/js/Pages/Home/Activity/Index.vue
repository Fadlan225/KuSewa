<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Clock,
    Search,
    Star,
    Heart,
    ChevronRight,
    ShoppingBag,
    ChevronLeft
} from 'lucide-vue-next';

const menus = [
    {
        label: 'Riwayat Transaksi & Booking',
        icon: ShoppingBag,
        route: route('aktivitas.transaksi')
    },
    {
        label: 'Terakhir Dilihat',
        icon: Clock,
        route: route('last-seen.index')
    },
    {
        label: 'Riwayat Pencarian',
        icon: Search,
        route: route('aktivitas.pencarian')
    },
    {
        label: 'Ulasan & Penilaian Saya',
        icon: Star,
        route: route('aktivitas.ulasan')
    },
    {
        label: 'Favorit / Disimpan',
        icon: Heart,
        route: route('favorites.index')
    }
];

const navigateTo = (url) => {
    router.get(url);
};

const goBack = () => {
    // Kembali ke beranda atau profil, di sini kita arahkan ke Home
    // karena aktivitas ada di bottombar.
    router.get(route('Home'));
};
</script>

<template>
    <AppLayout>
        <Head title="Pusat Aktivitas" />

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 pb-24 sm:pb-16 text-[#1D1D1F] min-h-[70vh]">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-[#0A2540]">Pusat aktivitas</h1>
            </div>

            <!-- List Menu -->
            <div class="space-y-1">
                <button
                    v-for="(menu, index) in menus"
                    :key="index"
                    @click="navigateTo(menu.route)"
                    class="w-full flex items-center justify-between p-4 sm:p-5 hover:bg-gray-50 rounded-xl transition-colors group"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-500 group-hover:bg-[#FFC000]/10 group-hover:text-[#FFC000] transition-colors shrink-0">
                            <component :is="menu.icon" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <span class="font-medium text-base text-[#1D1D1F] group-hover:text-[#FFC000] transition-colors">{{ menu.label }}</span>
                    </div>
                    <ChevronRight class="w-5 h-5 text-gray-300 group-hover:text-[#FFC000] transition-colors shrink-0" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>
