<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Mengambil data page saat ini untuk mengecek route aktif
const page = usePage();

// Fungsi helper untuk mengecek apakah URL saat ini cocok dengan menu
const isActive = (url) => {
    return computed(() => page.url === url || page.url.startsWith(url + '/'));
};

// Khusus untuk Beranda agar tidak bentrok dengan prefix URL lain
const isHomeActive = computed(() => page.url === '/');

// Menggabungkan logika untuk menu profil/login
const isAuthActive = computed(() => isActive('/profile').value || isActive('/login').value);

const isLoggedIn = computed(() => !!page.props.auth.user)
</script>

<template>
    <!-- Wrapper utama bottom bar -->
    <!-- h-20 memberikan tinggi yang cukup untuk efek gradien di dalam -->
    <div class="fixed bottom-0 left-0 w-full bg-background border-t border-white/10 rounded-t-3xl shadow-[0_-10px_30px_rgba(0,0,0,0.3)] md:hidden z-50 h-20">
        <div class="flex justify-around items-center h-full px-2">

            <!-- Item Navigasi Beranda -->
            <Link :href="route('Home')"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isHomeActive ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-house text-xl relative z-10"></i>
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

            <!-- Item Navigasi Aktivitas -->
            <Link href="/aktivitas"
                v-if="page.props.auth.user"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isActive('/aktivitas').value ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-clipboard-list text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">Aktivitas</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/aktivitas').value" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Kotak Masuk -->
            <Link href="/kotakmasuk"
                v-if="page.props.auth.user"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isActive('/kotakmasuk').value ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-inbox text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">Kotak Masuk</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/aktivitas').value" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Profil / Masuk -->
            <Link :href="isLoggedIn ? '/profile' : '/login'"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-20 transition-colors duration-300"
                :class="isAuthActive ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-user text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">
                    {{ isLoggedIn ? 'Profil' : 'Masuk' }}
                </span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isAuthActive" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

        </div>
    </div>
</template>
