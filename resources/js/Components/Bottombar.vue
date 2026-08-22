<script setup>
import { Home, Search, History, Heart, MessageSquareMore, User } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';
import UserAvatar from '@/Components/ui/Icons/UserAvatar.vue';

// Mengambil data page saat ini untuk mengecek route aktif
const page = usePage();

// Fungsi helper untuk mengecek apakah URL saat ini cocok dengan menu
const isActive = (url) => {
    return computed(() => {
        const path = page.url.split('?')[0];
        return path === url || path.startsWith(url + '/');
    });
};

// Khusus untuk Beranda agar tidak bentrok dengan prefix URL lain
const isHomeActive = computed(() => {
    const path = page.url.split('?')[0];
    return path === '/';
});

// Menggabungkan logika untuk menu profil/login
const isAuthActive = computed(() => isActive('/profile').value || isActive('/login').value);

const isLoggedIn = computed(() => !!page.props.auth.user);

const openAuthModal = inject('openAuthModal', () => { console.log('AuthModal not provided') });
</script>

<template>
    <!-- Wrapper utama bottom bar -->
    <!-- h-20 memberikan tinggi yang cukup untuk efek gradien di dalam -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-100 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] md:hidden z-50 h-20">
        <div class="flex justify-around items-center h-full px-2">

            <!-- Item Navigasi Beranda -->
            <Link :href="route('Home')"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isHomeActive ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <Home class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Beranda</span>

                <!-- Efek Aktif: Garis Solid & Gradien Ultra-Halus Ke Atas -->
                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isHomeActive" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <!-- Gradien memudar SANGAT HALUS dari bawah ke atas -->
                        <!-- Kita menggunakan style inline untuk kontrol opacity presisi: rgba(255,192,0, 0.03) -->
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <!-- Garis Solid di paling bawah -->
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Cari -->
            <Link :href="route('assets.search')"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isActive('/search').value ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <Search class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Cari</span>

                <!-- Efek Aktif: Garis Solid & Gradien Ultra-Halus Ke Atas -->
                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/search').value" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Aktivitas -->
            <Link :href="route('aktivitas.hub')"
                v-if="page.props.auth.user"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isActive('/aktivitas').value ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <History class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Aktivitas</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/aktivitas').value" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Kotak Masuk -->
            <Link :href="route('chat.index')"
                v-if="page.props.auth.user"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isActive('/chat').value ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <div class="relative flex flex-col items-center z-10">
                    <MessageSquareMore class="text-xl" />
                    <!-- Notification Badge -->
                    <span v-if="page.props.auth.unreadCount > 0" class="absolute -top-1 -right-3 flex items-center justify-center bg-red-500 text-white text-[9px] font-bold px-1 min-w-[15px] h-[15px] rounded-full">
                        {{ page.props.auth.unreadCount > 99 ? '99+' : page.props.auth.unreadCount }}
                    </span>
                </div>
                <span class="text-[10px] font-bold relative z-10">Pesan</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/chat').value" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Profil / Masuk -->
            <component :is="isLoggedIn ? Link : 'button'"
                :href="isLoggedIn ? '/profile' : undefined"
                @click="!isLoggedIn && openAuthModal()"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isAuthActive ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <template v-if="isLoggedIn">
                    <img v-if="page.props.auth.user.avatar" :src="page.props.auth.user.avatar" alt="Profil" class="w-[22px] h-[22px] rounded-full object-cover relative z-10 border border-gray-200" />
                    <div v-else class="w-[22px] h-[22px] rounded-full bg-[#f8f9fa] flex items-center justify-center overflow-hidden relative z-10 border border-gray-200">
                        <UserAvatar :user="page.props.auth.user" />
                    </div>
                </template>
                <User v-else class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10 mt-0.5">
                    {{ isLoggedIn ? 'Profil' : 'Masuk' }}
                </span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isAuthActive" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </component>

        </div>
    </div>
</template>
