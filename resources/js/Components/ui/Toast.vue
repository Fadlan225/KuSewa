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
            <div v-if="show" class="fixed top-20 md:top-8 right-4 md:right-8 z-[9999] flex items-center gap-3 bg-white border border-slate-200 text-slate-800 text-sm font-bold px-5 py-3.5 rounded-lg shadow-lg pointer-events-none whitespace-nowrap">
                <div v-if="type === 'success'" class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 shadow-sm">
                    <Check class="text-white text-[10px]" stroke-width="3" />
                </div>
                <div v-else class="w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center shrink-0 shadow-sm">
                    <X class="text-white text-[10px]" stroke-width="3" />
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
    transform: translateY(-24px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateY(-24px);
}
</style>
