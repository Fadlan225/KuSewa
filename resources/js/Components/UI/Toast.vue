<script setup>
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
    <Transition name="toast">
        <div v-if="show" class="fixed bottom-24 md:bottom-8 left-1/2 -translate-x-1/2 z-[200] flex items-center gap-3 bg-[#0A2540] text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-xl pointer-events-none whitespace-nowrap">
            <div v-if="type === 'success'" class="w-6 h-6 rounded-full bg-green-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check text-white text-[10px]"></i>
            </div>
            <div v-else class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-xmark text-white text-[10px]"></i>
            </div>
            {{ message }}
        </div>
    </Transition>
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
