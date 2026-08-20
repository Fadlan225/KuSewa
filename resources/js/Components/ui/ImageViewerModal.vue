<template>
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-sm touch-none">
            
            <!-- Header Actions -->
            <div class="absolute top-0 left-0 right-0 p-4 pt-safe flex justify-end z-10 bg-gradient-to-b from-black/60 to-transparent">
                <button @click="close" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors active:scale-95">
                    <X class="text-xl" />
                </button>
            </div>

            <!-- Left Button -->
            <button 
                v-if="images.length > 1 && currentIndex > 0" 
                @click.stop="prev"
                class="absolute left-2 md:left-6 z-10 w-10 h-10 md:w-12 md:h-12 bg-black/50 hover:bg-black/80 border border-white/20 rounded-full flex items-center justify-center text-white transition-colors active:scale-95"
            >
                <ChevronLeft class="text-lg md:text-xl -ml-1" />
            </button>

            <!-- Image Container (Swipeable) -->
            <div 
                class="w-full h-full flex items-center justify-center p-4 md:p-10"
                @touchstart="onTouchStart"
                @touchmove.prevent="onTouchMove"
                @touchend="onTouchEnd"
                :style="{ transform: imageTransform, transition: touchStartY === 0 ? 'transform 0.2s ease-out' : 'none' }"
            >
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                    mode="out-in"
                >
                    <img 
                        :key="currentIndex"
                        :src="currentUrl" 
                        class="max-w-full max-h-full object-contain select-none drop-shadow-2xl"
                        draggable="false"
                    />
                </Transition>
            </div>

            <!-- Right Button -->
            <button 
                v-if="images.length > 1 && currentIndex < images.length - 1" 
                @click.stop="next"
                class="absolute right-2 md:right-6 z-10 w-10 h-10 md:w-12 md:h-12 bg-black/50 hover:bg-black/80 border border-white/20 rounded-full flex items-center justify-center text-white transition-colors active:scale-95"
            >
                <ChevronRight class="text-lg md:text-xl -mr-1" />
            </button>

            <!-- Counter Info -->
            <div v-if="images.length > 1" class="absolute bottom-4 left-0 right-0 flex justify-center z-10 pointer-events-none">
                <div class="bg-black/60 backdrop-blur-md px-4 py-1.5 rounded-full text-white/90 text-sm font-medium tracking-widest">
                    {{ currentIndex + 1 }} / {{ images.length }}
                </div>
            </div>

        </div>
    </Transition>
</template>

<script setup>
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    images: {
        type: Array,
        default: () => [] // Array of URLs or Objects with file_url
    },
    initialIndex: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close']);

const currentIndex = ref(props.initialIndex);

watch(() => props.show, (newVal) => {
    if (newVal) {
        currentIndex.value = props.initialIndex;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

const currentUrl = computed(() => {
    const item = props.images[currentIndex.value];
    if (!item) return '';
    return typeof item === 'string' ? item : (item.file_url || item.url);
});

const close = () => {
    emit('close');
};

const next = () => {
    if (currentIndex.value < props.images.length - 1) {
        currentIndex.value++;
    }
};

const prev = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
};

// Keyboard navigation
const handleKeydown = (e) => {
    if (!props.show) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// Swipe logic
const touchStartY = ref(0);
const touchCurrentY = ref(0);
const touchStartX = ref(0);
const touchCurrentX = ref(0);

const onTouchStart = (e) => {
    touchStartY.value = e.touches[0].clientY;
    touchCurrentY.value = e.touches[0].clientY;
    touchStartX.value = e.touches[0].clientX;
    touchCurrentX.value = e.touches[0].clientX;
};

const onTouchMove = (e) => {
    touchCurrentY.value = e.touches[0].clientY;
    touchCurrentX.value = e.touches[0].clientX;
};

const onTouchEnd = () => {
    const deltaY = touchCurrentY.value - touchStartY.value;
    const deltaX = touchCurrentX.value - touchStartX.value;
    
    // Determine dominant swipe direction
    if (Math.abs(deltaX) > Math.abs(deltaY)) {
        if (deltaX > 50) { // Swipe right -> prev
            prev();
        } else if (deltaX < -50) { // Swipe left -> next
            next();
        }
    } else {
        if (deltaY > 90) { // Swipe down -> close
            close();
        }
    }

    touchStartY.value = 0;
    touchCurrentY.value = 0;
    touchStartX.value = 0;
    touchCurrentX.value = 0;
};

const imageTransform = computed(() => {
    if (touchStartY.value === 0 && touchStartX.value === 0) return 'translate3d(0, 0, 0)';
    
    const deltaY = touchCurrentY.value - touchStartY.value;
    const deltaX = touchCurrentX.value - touchStartX.value;

    let transY = 0;
    let transX = 0;

    // Only allow swipe down visually
    if (deltaY > 0 && Math.abs(deltaY) > Math.abs(deltaX)) {
        transY = deltaY;
    } else if (Math.abs(deltaX) > Math.abs(deltaY)) {
        transX = deltaX;
    }

    return `translate3d(${transX}px, ${transY}px, 0)`;
});
</script>
