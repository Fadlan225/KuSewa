<script setup>
import { ref, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const toasts = ref([]);
let toastId = 0;

const addToast = (notification) => {
    const id = ++toastId;
    toasts.value.unshift({ id, ...notification, visible: true });

    // Auto-dismiss setelah 5 detik
    setTimeout(() => {
        dismissToast(id);
    }, 5000);

    // Batasi maksimal 3 toast tampil bersamaan
    if (toasts.value.length > 3) {
        toasts.value.pop();
    }
};

const dismissToast = (id) => {
    const toast = toasts.value.find(t => t.id === id);
    if (toast) {
        toast.visible = false;
        // Hapus dari array setelah animasi selesai
        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, 300);
    }
};

// Expose addToast agar bisa dipanggil dari parent (AppLayout)
defineExpose({ addToast });
</script>

<template>
    <!-- Container Toast (kiri bawah desktop, bawah mobile) -->
    <div class="fixed bottom-20 md:bottom-6 left-0 right-0 md:left-auto md:right-6 z-[200] flex flex-col gap-2 items-center md:items-end px-4 md:px-0 pointer-events-none">
        <TransitionGroup
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-for="toast in toasts"
                v-show="toast.visible"
                :key="toast.id"
                class="pointer-events-auto w-full md:w-[340px] bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden"
            >
                <div class="flex gap-3 p-3.5 items-start">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-full bg-[#FFC000]/10 flex items-center justify-center">
                            <img src="/kitasewa-logo.png" alt="KitaSewa" class="w-6 h-6 object-contain" />
                        </div>
                    </div>

                    <!-- Konten -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#0A2540] leading-snug line-clamp-1">{{ toast.title }}</p>
                        <p class="text-xs text-[#6C757D] mt-0.5 line-clamp-2">{{ toast.message }}</p>
                        <component
                            :is="toast.action_url ? Link : 'span'"
                            v-if="toast.action_url"
                            :href="toast.action_url"
                            @click="dismissToast(toast.id)"
                            class="text-[10px] text-[#FFC000] font-bold mt-1 inline-block hover:underline"
                        >
                            Lihat Detail →
                        </component>
                    </div>

                    <!-- Tombol tutup -->
                    <button
                        @click="dismissToast(toast.id)"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors mt-0.5"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Progress bar auto-dismiss -->
                <div class="h-0.5 bg-[#FFC000] animate-shrink-width origin-left"></div>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
@keyframes shrink-width {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
.animate-shrink-width {
    animation: shrink-width 5s linear forwards;
}
</style>
