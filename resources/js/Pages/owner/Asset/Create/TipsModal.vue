<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { X, Images, Check } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const currentSlide = ref(0);

const slides = [
    {
        title: 'Tidak Berupa Kolase',
        desc: 'Gunakan satu foto untuk menonjolkan satu ruangan atau fasilitas. Anda bisa upload banyak foto sesuai keperluan.',
    },
    {
        title: 'Sesuai Sudut Pandang',
        desc: 'Ambil foto dari sudut yang luas untuk memperlihatkan keseluruhan ruangan dengan jelas.',
    },
    {
        title: 'Hindari Teks & Logo',
        desc: 'Pastikan foto bersih dari tulisan, watermark, atau logo yang menutupi gambar.',
    },
    {
        title: 'Pencahayaan Terang',
        desc: 'Gunakan cahaya alami atau lampu agar foto terlihat jelas dan menarik.',
    }
];

const nextSlide = () => {
    if (currentSlide.value < slides.length - 1) currentSlide.value++;
};
const prevSlide = () => {
    if (currentSlide.value > 0) currentSlide.value--;
};
const finish = () => {
    emit('close');
    setTimeout(() => {
        currentSlide.value = 0;
    }, 300);
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="emit('close')"></div>

                <!-- Modal Content -->
                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div v-show="show" class="bg-white rounded-2xl overflow-hidden shadow-2xl relative z-10 w-full max-w-md mx-auto flex flex-col h-[500px]">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b border-slate-100">
                            <h3 class="font-bold text-[#0A2540]">Tips Foto Menarik</h3>
                            <button @click="emit('close')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50 cursor-pointer">
                                <X :size="20" />
                            </button>
                        </div>

                        <!-- Body (Carousel) -->
                        <div class="flex-1 overflow-hidden relative">
                            <div 
                                class="flex transition-transform duration-500 ease-in-out h-full"
                                :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                            >
                                <div 
                                    v-for="(slide, index) in slides" 
                                    :key="index"
                                    class="w-full flex-shrink-0 flex flex-col p-6 h-full"
                                >
                                    <!-- Illustrations Placeholder -->
                                    <div class="flex-1 bg-slate-50 rounded-xl mb-6 flex items-center justify-center p-4 border border-slate-100 gap-4">
                                        <!-- Good Example -->
                                        <div class="flex flex-col items-center gap-2 relative">
                                            <div class="w-24 h-24 bg-white border border-slate-200 rounded-lg shadow-sm flex items-center justify-center overflow-hidden">
                                                <Images class="text-slate-300" :size="32" />
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 bg-emerald-100 border-2 border-white text-emerald-700 w-7 h-7 rounded-full flex items-center justify-center shadow-sm">
                                                <Check :size="16" />
                                            </div>
                                        </div>
                                        <!-- Bad Example -->
                                        <div class="flex flex-col items-center gap-2 relative">
                                            <div class="w-24 h-24 bg-white border border-slate-200 rounded-lg shadow-sm flex items-center justify-center overflow-hidden">
                                                <div class="grid grid-cols-2 grid-rows-2 gap-1 w-full h-full p-1 opacity-50">
                                                    <div class="bg-slate-200 rounded-sm"></div>
                                                    <div class="bg-slate-200 rounded-sm"></div>
                                                    <div class="bg-slate-200 rounded-sm"></div>
                                                    <div class="bg-slate-200 rounded-sm"></div>
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 bg-rose-100 border-2 border-white text-rose-700 w-7 h-7 rounded-full flex items-center justify-center shadow-sm font-bold text-xs">
                                                <X :size="16" stroke-width="3" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Text Content -->
                                    <div class="text-center">
                                        <h4 class="text-lg font-bold text-slate-800 mb-2">{{ slide.title }}</h4>
                                        <p class="text-sm text-slate-500 leading-relaxed">{{ slide.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer (Controls) -->
                        <div class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <!-- Prev Button -->
                            <button 
                                @click="prevSlide" 
                                :disabled="currentSlide === 0"
                                class="text-sm font-semibold text-slate-500 hover:text-slate-800 disabled:opacity-0 transition-all cursor-pointer"
                            >
                                Sebelumnya
                            </button>

                            <!-- Indicators -->
                            <div class="flex items-center gap-2">
                                <button 
                                    v-for="(_, index) in slides" 
                                    :key="index"
                                    @click="currentSlide = index"
                                    class="w-2 h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="currentSlide === index ? 'bg-[#FFC000] w-6' : 'bg-slate-300'"
                                ></button>
                            </div>

                            <!-- Next / Finish Button -->
                            <button 
                                v-if="currentSlide < slides.length - 1"
                                @click="nextSlide"
                                class="text-sm font-semibold text-[#0A2540] hover:text-[#0A2540] cursor-pointer"
                            >
                                Selanjutnya
                            </button>
                            <button 
                                v-else
                                @click="finish"
                                class="text-sm font-semibold text-[#0A2540] bg-[#FFC000] hover:bg-[#e5ac00] px-4 py-1.5 rounded-full shadow-sm transition-all cursor-pointer"
                            >
                                Mengerti
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
