<script setup>
import { storeToRefs } from 'pinia';
import { useAuthFeedbackStore } from '@/Stores/AuthFeedbackStore';

const authFeedbackStore = useAuthFeedbackStore();
const { isOpen, type, title, message, illustration, autoClose } = storeToRefs(authFeedbackStore);
</script>

<template>
    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="!autoClose ? authFeedbackStore.close() : null"></div>
            
            <!-- Card -->
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center flex flex-col items-center">
                
                <img :src="illustration" alt="Illustration" class="w-32 h-32 object-contain mb-6" />
                
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ title }}
                </h3>
                
                <p class="text-gray-600 text-sm leading-relaxed" :class="{'mb-6': !autoClose}">
                    {{ message }}
                </p>
                
                <!-- Close Button (Only for errors where autoClose is false) -->
                <button 
                    v-if="!autoClose" 
                    @click="authFeedbackStore.close()"
                    class="w-full py-3 px-4 bg-[#FFC000] hover:bg-[#e6ad00] text-gray-900 font-bold rounded-xl transition-colors mt-2"
                >
                    Tutup
                </button>
            </div>
        </div>
    </Transition>
</template>
