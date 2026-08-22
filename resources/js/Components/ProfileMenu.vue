<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Briefcase } from 'lucide-vue-next';
import AppIcon from '@/Components/AppIcon.vue';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object
});

const page = usePage();
const getOwnerStatus = () => {
    return page.props.owner_profile?.status || page.props.auth.user?.owner_profile?.status;
};

const accountMenuItems = [
    {
        label: 'Profil Saya',
        icon: 'fa-regular fa-user',
        routeDesktop: route('profile.edit', { tab: 'profil' }),
        routeMobile: route('profile.settings'),
        isActive: () => route().current('profile.settings') || (route().current('profile.edit') && (!route().params.tab || route().params.tab === 'profil'))
    },
    {
        label: 'Profil Bisnis',
        icon: 'fa-solid fa-city',
        routeDesktop: route('profile.edit', { tab: 'bisnis' }),
        routeMobile: route('profile.bisnis'),
        isActive: () => route().current('profile.bisnis') || (route().current('profile.edit') && route().params.tab === 'bisnis'),
        show: () => getOwnerStatus() === 'verified'
    },
    {
        label: 'Keamanan',
        icon: 'fa-solid fa-shield-halved',
        routeDesktop: route('profile.edit', { tab: 'keamanan' }),
        routeMobile: route('profile.security'),
        isActive: () => route().current('profile.security') || (route().current('profile.edit') && route().params.tab === 'keamanan')
    },
    {
        label: 'Aktivitas',
        icon: 'fa-solid fa-chart-line',
        routeMobile: route('aktivitas.hub'),
        isActive: () => route().current('aktivitas.*') || route().current('last-seen.*') || route().current('favorites.*')
    },
];

const activityMenuItems = [
    {
        label: 'Pesanan Saya',
        icon: 'fa-solid fa-clipboard-list',
        routeDesktop: route('profile.edit', { tab: 'transaksi' }),
        isActive: () => route().current('profile.edit') && route().params.tab === 'transaksi'
    },
    {
        label: 'Terakhir Dilihat',
        icon: 'fa-solid fa-clock-rotate-left',
        routeDesktop: route('profile.edit', { tab: 'terakhir-dilihat' }),
        isActive: () => route().current('profile.edit') && route().params.tab === 'terakhir-dilihat'
    },
    {
        label: 'Riwayat Pencarian',
        icon: 'fa-solid fa-magnifying-glass',
        routeDesktop: route('profile.edit', { tab: 'pencarian' }),
        isActive: () => route().current('profile.edit') && route().params.tab === 'pencarian'
    },
    {
        label: 'Ulasan',
        icon: 'fa-solid fa-star',
        routeDesktop: route('profile.edit', { tab: 'ulasan' }),
        isActive: () => route().current('profile.edit') && route().params.tab === 'ulasan'
    },
    {
        label: 'Favorit',
        icon: 'fa-regular fa-heart',
        routeDesktop: route('profile.edit', { tab: 'favorit' }),
        isActive: () => route().current('profile.edit') && route().params.tab === 'favorit'
    },
];

const settingsMenuItems = [
    { label: 'Notifikasi', icon: 'fa-regular fa-bell', route: '#', routeNames: ['notifications.*'] },
];

const helpMenuItems = [
    { label: 'Pusat Bantuan', icon: 'fa-solid fa-circle-info', route: route('bantuan'), routeNames: ['bantuan'] },
    { label: 'Hubungi Kami', icon: 'fa-solid fa-headset', route: route('hubungi-kami'), routeNames: ['hubungi-kami'] },
    { label: 'Keluar', icon: 'fa-solid fa-arrow-right-from-bracket text-red-500', action: 'logout' },
];

const showLogoutModal = ref(false);

const checkIsActive = (item) => {
    if (item.isActive) {
        return item.isActive();
    }
    if (!item.routeNames) return false;
    if (Array.isArray(item.routeNames)) {
        return item.routeNames.some(pattern => route().current(pattern));
    }
    return route().current(item.routeNames);
};
</script>

<template>
    <div class="space-y-6 w-full">
        <!-- Grup Menu 'Akun Saya' -->
        <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
            <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Akun Saya</h3>
            <div class="border-t border-[#F8F9FA] mb-2"></div>

            <template v-for="(item, index) in accountMenuItems" :key="index">
                <!-- Desktop Link (jika routeDesktop ada) -->
                <Link
                    v-if="item.routeDesktop && (item.show === undefined || item.show())"
                    :href="item.routeDesktop"
                    :class="[
                        'hidden md:flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]']" />
                </Link>

                <!-- Mobile Link (jika routeMobile ada) -->
                <Link
                    v-if="item.routeMobile && (item.show === undefined || item.show())"
                    :href="item.routeMobile"
                    class="flex md:hidden items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative hover:bg-[#F8F9FA]"
                >
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span class="text-sm sm:text-base font-semibold transition-colors text-[#0A2540] group-hover:text-[#FFC000]">{{ item.label }}</span>
                    </div>
                    <ChevronRight class="text-sm transition-all duration-200 text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]" />
                </Link>

                <!-- General Link (jika hanya ada route biasa) -->
                <Link
                    v-if="!item.routeDesktop && !item.routeMobile && (item.show === undefined || item.show())"
                    :href="item.route"
                    :class="[
                        'flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]']" />
                </Link>
            </template>

            <!-- Profile Bisnis / Pusat Mitra -->
            <Link
                v-if="user"
                :href="user.is_owner ? route('owner.dashboard') : route('owner.register')"
                class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-[#F8F9FA] px-3 rounded-xl transition-colors duration-150 group relative overflow-hidden"
            >
                <div class="flex items-center space-x-4">
                    <Briefcase class="text-lg text-[#6C757D] group-hover:text-[#FFC000] w-6 text-center transition-colors" />
                    <span class="text-sm sm:text-base font-semibold text-[#0A2540] group-hover:text-[#FFC000] transition-colors">
                        {{ user.is_owner ? 'Pusat Mitra' : 'Jadi Mitra KitaSewa' }}
                    </span>
                </div>
                <ChevronRight class="text-sm text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000] transition-all duration-200" />
            </Link>
        </div>

        <!-- Grup Menu 'Pusat Aktivitas' (Hanya tampil di Desktop karena isinya hanya rute desktop) -->
        <div class="hidden md:block bg-white p-6 shadow-md rounded-2xl space-y-2">
            <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Pusat Aktivitas</h3>
            <div class="border-t border-[#F8F9FA] mb-2"></div>

            <template v-for="(item, index) in activityMenuItems" :key="index">
                <!-- Desktop Link -->
                <Link
                    v-if="item.routeDesktop"
                    :href="item.routeDesktop"
                    :class="[
                        'hidden md:flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]']" />
                </Link>
            </template>
        </div>

        <!-- Grup Menu 'Pengaturan Aplikasi' -->
        <div class="bg-white p-6 shadow-md rounded-2xl space-y-2">
            <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Pengaturan Aplikasi</h3>
            <div class="border-t border-[#F8F9FA] mb-2"></div>

            <template v-for="(item, index) in settingsMenuItems" :key="index">
                <Link
                    v-if="!item.routeDesktop && !item.routeMobile"
                    :href="item.route"
                    :class="[
                        'flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]']" />
                </Link>
            </template>
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
                    :class="[
                        'w-full flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:text-[#FFC000] group-hover:translate-x-1']" />
                </Link>
            </template>
        </div>

        <!-- Logout Modal -->
        <Teleport to="body" v-if="showLogoutModal">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-sm w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300 text-center">
                    <h2 class="text-xl font-bold text-gray-900">Yakin ingin keluar dari akun?</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Kamu tetap bisa menjelajahi KitaSewa, tetapi perlu login kembali untuk melakukan booking atau mengelola aset.
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
    </div>
</template>

