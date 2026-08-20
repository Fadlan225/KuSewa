<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
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
        label: 'Pesanan',
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
        label: 'Ulasan',
        icon: Star,
        route: route('aktivitas.ulasan')
    },
    {
        label: 'Favorit',
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

        <DetailNavbar 
            title="Pusat Aktivitas" 
            backUrl="/profile" 
            :forceBackUrl="true" 
            :showBackButton="true" 
            :showSections="false" 
            :showShare="false" 
            :showFavorite="false" 
            class="md:hidden"
        />

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 pb-24 sm:pb-16 text-[#1D1D1F] min-h-[70vh]">
            <div class="mb-6 hidden md:block">
                <h1 class="text-xl sm:text-2xl font-bold text-[#0A2540]">Pusat aktivitas</h1>
            </div>

            <!-- List Menu -->
            <div class="space-y-4">
                <button
                    v-for="(menu, index) in menus"
                    :key="index"
                    @click="navigateTo(menu.route)"
                    class="w-full flex items-center justify-between p-4 sm:p-5 bg-white border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.15)] rounded-2xl hover:bg-gray-50 transition-all duration-300 group"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#F8F9FA] text-gray-500 group-hover:bg-[#FFC000]/10 group-hover:text-[#FFC000] transition-colors shrink-0">
                            <component :is="menu.icon" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <span class="font-semibold text-[15px] sm:text-base text-[#0A2540] group-hover:text-[#FFC000] transition-colors">{{ menu.label }}</span>
                    </div>
                    <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-[#FFC000] group-hover:translate-x-1 transition-all shrink-0" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>
