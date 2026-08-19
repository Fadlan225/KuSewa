<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { storeToRefs } from 'pinia';
import { useAuthFeedbackStore } from '@/Stores/AuthFeedbackStore';

const authFeedbackStore = useAuthFeedbackStore();
const { isOpen, type, title, message, autoClose } = storeToRefs(authFeedbackStore);

</script>

<template>
    <Transition
        enter-active-class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
        enter-from-class="opacity-0 scale-75 translate-y-6"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-300 ease-in-out"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-90 translate-y-6"
    >
        <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="!autoClose ? authFeedbackStore.close() : null"></div>
            
            <!-- Card -->
            <div class="relative bg-white rounded-[2rem] shadow-2xl shadow-slate-900/20 w-full max-w-sm p-8 text-center flex flex-col items-center">
                
                <!-- Ikon Fluffy -->
                <div class="mb-5 flex justify-center items-center w-28 h-28 rounded-full"
                     :class="type === 'success' ? 'bg-emerald-50 text-emerald-500' : 'bg-red-50 text-red-500'">
                    <AppIcon :iconClass="type === 'success' ? 'fa-circle-check animate-bounce-short' : 'fa-circle-xmark animate-shake'"  />
                </div>
                
                <h3 class="text-2xl font-extrabold text-[#0A2540] mb-2.5">
                    {{ title }}
                </h3>
                
                <p class="text-slate-500 text-sm font-medium leading-relaxed" :class="{'mb-7': !autoClose}">
                    {{ message }}
                </p>
                
                <!-- Close Button (Only for errors where autoClose is false) -->
                <button 
                    v-if="!autoClose" 
                    @click="authFeedbackStore.close()"
                    class="w-full py-3.5 px-4 bg-[#FFC000] hover:bg-[#e6ad00] active:scale-[0.98] text-[#0A2540] font-bold rounded-xl transition-all shadow-sm"
                >
                    Tutup
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-bounce-short {
    animation: bounce-short 1s cubic-bezier(0.28, 0.84, 0.42, 1) forwards;
}
@keyframes bounce-short {
    0% { transform: scale(0.5); opacity: 0; }
    50% { transform: scale(1.1); }
    70% { transform: scale(0.95); }
    100% { transform: scale(1); opacity: 1; }
}

.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
