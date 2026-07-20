<script setup>
import { loadingState } from '@/Stores/loading';
</script>

<template>
    <Transition name="fade">
        <div
            v-if="loadingState.show"
            class="fixed inset-0 z-[9999] bg-white/80 flex items-center justify-center"
        >
            <div class="flex flex-col items-center gap-4">

                <!-- Container Spinner & Logo -->
                <div class="relative w-14 h-14 flex items-center justify-center">
                    <!-- Ring Spinner (Animasi Putar) -->
                    <div
                        class="absolute inset-0 rounded-full border-4 border-gray-200 border-t-[#FFC000] animate-spin"
                    ></div>

                    <!-- Logo Kusewa Tengah -->
                    <!-- Sesuaikan atribut src dengan lokasi file logo Anda (misal: /images/logo.png) -->
                    <img
                        src="/kusewa-logo.png"
                        alt="Logo Kusewa"
                        class="w-6 h-6 object-contain z-10"
                    />
                </div>

                <!-- Teks -->
                <p class="text-sm font-bold text-[#0A2540] tracking-widest uppercase">
                    Kusewa
                </p>

            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
