<script setup>
import { Check, X } from 'lucide-vue-next';
defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Berhasil!'
    },
    type: {
        type: String,
        default: 'success'
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="toast">
            <div v-if="show" class="fixed bottom-24 md:bottom-8 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 bg-[#FFC000] text-[#0A2540] text-sm font-bold px-5 py-3 rounded-2xl shadow-xl pointer-events-none whitespace-nowrap">
                <div v-if="type === 'success'" class="w-6 h-6 rounded-full bg-[#0A2540] flex items-center justify-center shrink-0">
                    <Check class="text-[#FFC000] text-[10px]" stroke-width="3" />
                </div>
                <div v-else class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center shrink-0">
                    <X class="text-white text-[10px]" />
                </div>
                {{ message }}
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}
</style>
