<script setup>
import { X } from 'lucide-vue-next';
import { useAuthModalStore } from '@/Stores/AuthModalStore';
import BottomSheet from '@/Components/UI/BottomSheet.vue';
import AuthFlow from './AuthFlow.vue';
import { storeToRefs } from 'pinia';

const authModalStore = useAuthModalStore();
const { isOpen } = storeToRefs(authModalStore);

const close = () => {
    authModalStore.close();
};
</script>

<template>
    <div>
        <!-- MOBILE: Bottom Sheet -->
        <div class="md:hidden">
            <BottomSheet 
                :modelValue="isOpen" 
                @update:modelValue="val => !val && close()"
                title="Masuk atau Daftar"
                heightClass="h-[85vh]"
            >
                <AuthFlow />
            </BottomSheet>
        </div>

        <!-- DESKTOP: Centered Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="isOpen" class="hidden md:flex fixed inset-0 z-[100] items-center justify-center p-4">
                <!-- Overlay -->
                <div @click="close" class="absolute inset-0 bg-black/60 transition-opacity"></div>
                
                <!-- Modal Content -->
                <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden z-10">
                    
                    <!-- Header -->
                    <div class="px-6 py-4 flex items-center justify-between border-b border-[#6C757D]/10 shrink-0">
                        <h2 class="text-lg font-extrabold text-[#0A2540]">Masuk atau Daftar</h2>
                        <button
                            @click="close"
                            class="w-8 h-8 rounded-full bg-[#F8F9FA] hover:bg-gray-200 flex items-center justify-center text-[#0A2540] transition ml-auto"
                        >
                            <X class="" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto">
                        <AuthFlow />
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
