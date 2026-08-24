<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isModalOpen = ref(false);

const handleShowModal = () => {
    isModalOpen.value = true;
};

const handleCloseModal = () => {
    isModalOpen.value = false;
};

onMounted(() => {
    window.addEventListener('show-profile-incomplete-bubble', handleShowModal);
});

onUnmounted(() => {
    window.removeEventListener('show-profile-incomplete-bubble', handleShowModal);
});
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
        <div v-if="isModalOpen" class="fixed top-0 inset-x-0 z-[100] flex justify-center">
            <div class="bg-white w-full sm:max-w-md shadow-lg border-b border-gray-200 sm:border-x sm:border-b p-4 flex gap-4 items-start">
                
                <div class="flex-shrink-0 mt-0.5">
                    <img src="/kitasewa-logo.png" alt="KitaSewa" class="w-10 h-10 object-contain" />
                </div>
                
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-[15px] mb-1">
                        Profil Belum Lengkap!
                    </h3>
                    <p class="text-xs text-gray-600 mb-3 leading-relaxed">
                        Anda harus melengkapi profil bisnis (seperti Rekening Bank) sebelum bisa mendaftarkan aset dan menerima pesanan.
                    </p>
                    <div class="flex items-center justify-end gap-3">
                        <button @click="handleCloseModal" class="px-5 py-2 text-xs sm:text-sm font-semibold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-none transition">
                            Nanti Saja
                        </button>
                        <Link :href="route('owner.profile', { tab: 'bisnis' })" @click="handleCloseModal" class="px-6 py-2 text-xs sm:text-sm font-semibold text-[#0A2540] bg-[#FFC000] hover:bg-[#e6ad00] rounded-none transition shadow-sm">
                            Lengkapi Sekarang
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </Transition>
</template>
