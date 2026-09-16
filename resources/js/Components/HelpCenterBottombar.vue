<script setup>
import { User, Building, Info } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    tab: {
        type: String,
        default: 'penyewa'
    }
});
</script>

<template>
    <!-- Wrapper utama bottom bar -->
    <!-- h-20 memberikan tinggi yang cukup untuk efek gradien di dalam -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-100 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] md:hidden z-50 h-20">
        <div class="flex justify-around items-center h-full px-2">

            <!-- Item Navigasi Penyewa -->
            <Link :href="route('bantuan.index', { tab: 'penyewa' })"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-24 transition-colors duration-300"
                :class="tab === 'penyewa' ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <User class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Penyewa Aset</span>

                <!-- Efek Aktif: Garis Solid & Gradien Ultra-Halus Ke Atas -->
                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="tab === 'penyewa'" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Pemilik -->
            <Link :href="route('bantuan.index', { tab: 'pemilik' })"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-24 transition-colors duration-300"
                :class="tab === 'pemilik' ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <Building class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Pemilik Aset</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="tab === 'pemilik'" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

            <!-- Item Navigasi Info Umum -->
            <Link :href="route('bantuan.index', { tab: 'umum' })"
                class="relative flex flex-col items-center justify-center gap-1.5 h-full w-24 transition-colors duration-300"
                :class="tab === 'umum' ? 'text-[#FFC000]' : 'text-[#6A7282] hover:text-[#FFC000]'">

                <Info class="text-xl relative z-10" />
                <span class="text-[10px] font-bold relative z-10">Info Umum</span>

                <transition enter-active-class="transition opacity-0 duration-300" enter-to-class="opacity-100">
                    <div v-if="tab === 'umum'" class="absolute inset-x-0 bottom-0 top-1 flex flex-col items-center">
                        <div class="w-full h-full rounded-t-lg" style="background: linear-gradient(to top, rgba(255, 192, 0, 0.03), transparent)"></div>
                        <div class="w-full h-1 bg-[#FFC000] rounded-t-full shadow-[0_0_10px_rgba(255,192,0,0.5)]"></div>
                    </div>
                </transition>
            </Link>

        </div>
    </div>
</template>
