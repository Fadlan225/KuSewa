<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <Transition
                    enter-active-class="transition duration-200 ease-out delay-75"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div v-if="show" class="bg-white rounded p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl border border-slate-100 space-y-5 relative overflow-hidden" @click.stop>
                        
                        <slot name="icon">
                            <div class="w-16 h-16 mx-auto bg-rose-50 rounded-full flex items-center justify-center text-rose-500 mb-2 shadow-sm border border-rose-100">
                                <Trash2 class="w-8 h-8" />
                            </div>
                        </slot>

                        <div class="space-y-2">
                            <h3 class="text-lg font-bold text-[#0A2540] tracking-tight">{{ title }}</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ message }}</p>
                        </div>

                        <div class="flex items-center gap-3 w-full pt-3">
                            <button @click="emit('cancel')" type="button" class="flex-1 py-3 px-4 rounded border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition cursor-pointer">
                                {{ cancelText }}
                            </button>
                            <button @click="emit('confirm')" type="button" :class="[
                                'flex-1 py-3 px-4 rounded font-bold text-sm transition shadow-sm cursor-pointer border',
                                type === 'danger' ? 'bg-rose-500 border-rose-500 text-white hover:bg-rose-600 shadow-rose-500/20' : 'bg-[#FFC000] border-[#FFC000] text-[#0A2540] hover:bg-[#e5ac00] shadow-[#FFC000]/20'
                            ]">
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { Trash2 } from 'lucide-vue-next';

defineProps({
    show: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        default: 'danger' // 'danger' or 'primary'
    },
    title: {
        type: String,
        default: 'Konfirmasi Hapus'
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin menghapus data ini?'
    },
    confirmText: {
        type: String,
        default: 'Hapus'
    },
    cancelText: {
        type: String,
        default: 'Batal'
    }
});

const emit = defineEmits(['confirm', 'cancel']);
</script>
