<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const emit = defineEmits(['open-chat']);

const props = defineProps({
    auth: Object,
    isLoggedIn: { type: Boolean, default: false },
    isChatOpen: { type: Boolean, default: false }
});

const isActive = (url) => computed(() => page.url === url || page.url.startsWith(url + '/'));
const isHomeActive = computed(() => page.url === '/');
const isAuthActive = computed(() => isActive('/profile').value || isActive('/login').value);

const handleChatClick = () => {
    emit('open-chat');
};
</script>

<template>
    <div class="fixed bottom-0 left-0 w-full bg-background border-t border-white/10 rounded-t-3xl shadow-[0_-10px_30px_rgba(0,0,0,0.3)] md:hidden z-50 h-20">
        <div class="flex justify-around items-center h-full px-2">

            <!-- 1. BERANDA -->
            <Link :href="route('Home')"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-16 transition-colors duration-300"
                :class="isHomeActive && !isChatOpen ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-house text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">Beranda</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isHomeActive && !isChatOpen" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- 2. AKTIVITAS (HANYA MUNCUL SETELAH LOGIN) -->
            <Link href="/aktivitas"
                v-if="page.props.auth?.user || isLoggedIn"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-16 transition-colors duration-300"
                :class="isActive('/aktivitas').value && !isChatOpen ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-clipboard-list text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">Aktivitas</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isActive('/aktivitas').value && !isChatOpen" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- 3. CHAT -->
            <button 
                @click="handleChatClick"
                type="button"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-16 transition-colors duration-300 cursor-pointer"
                :class="isChatOpen ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <div class="relative z-10">
                    <i class="fa-solid fa-comments text-xl"></i>
                    <!-- Badge 2 Pesan Merah -->
                    <span class="absolute -top-1.5 -right-2 bg-red-500 text-white text-[8px] font-extrabold w-3.5 h-3.5 rounded-full flex items-center justify-center border border-slate-900">
                        2
                    </span>
                </div>
                <span class="text-[10px] font-bold relative z-10">Chat</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isChatOpen" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </button>

            <!-- 4. PROFIL / MASUK -->
            <Link :href="isLoggedIn ? '/profile' : '/login'"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-16 transition-colors duration-300"
                :class="isAuthActive && !isChatOpen ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <i class="fa-solid fa-user text-xl relative z-10"></i>
                <span class="text-[10px] font-bold relative z-10">
                    {{ isLoggedIn ? 'Profil' : 'Masuk' }}
                </span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="isAuthActive && !isChatOpen" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

        </div>
    </div>
</template>