<script setup>
import { useLocationPermission } from '@/Composables/useLocationPermission';

const { isLocationModalOpen, handleLocationAllow, handleLocationDeny } = useLocationPermission();
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="-translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0"
        leave-to-class="-translate-y-full"
    >
        <div v-if="isLocationModalOpen" class="fixed top-0 inset-x-0 z-[100] flex justify-center">
            <div class="bg-white w-full sm:max-w-md shadow-lg border-b border-gray-200 sm:border-x sm:border-b p-4 flex gap-4 items-start">
                
                <div class="flex-shrink-0 mt-0.5">
                    <img src="/kitasewa-logo.png" alt="KitaSewa" class="w-10 h-10 object-contain" />
                </div>
                
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-[15px] mb-1">
                        Mau dapat rekomendasi aset terbaik di sekitar kamu?
                    </h3>
                    <p class="text-xs text-gray-600 mb-3 leading-relaxed">
                        Aktifkan lokasi untuk menemukan pilihan sewa yang paling relevan di sekitar kamu.
                    </p>
                    <div class="flex items-center justify-end gap-3">
                        <button @click="handleLocationDeny" class="px-5 py-2 text-xs sm:text-sm font-semibold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-none transition">
                            Nanti Saja
                        </button>
                        <button @click="handleLocationAllow" class="px-6 py-2 text-xs sm:text-sm font-semibold text-[#0A2540] bg-[#FFC000] hover:bg-[#e6ad00] rounded-none transition shadow-sm">
                            Izinkan
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </Transition>
</template>
