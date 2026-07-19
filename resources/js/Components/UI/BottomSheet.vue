<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    heightClass: {
        type: String,
        default: 'h-[65vh]'
    }
});

const emit = defineEmits(['update:modelValue']);

const close = () => {
    emit('update:modelValue', false);
};

// Internal Swipe to close logic
const touchStartY = ref(0);
const touchCurrentY = ref(0);

const onTouchStart = (e) => {
    touchStartY.value = e.touches[0].clientY;
    touchCurrentY.value = e.touches[0].clientY;
};

const onTouchMove = (e) => {
    touchCurrentY.value = e.touches[0].clientY;
};

const onTouchEnd = () => {
    const deltaY = touchCurrentY.value - touchStartY.value;
    if (deltaY > 90) {
        close();
    }
    touchStartY.value = 0;
    touchCurrentY.value = 0;
};

const sheetTransform = computed(() => {
    if (touchCurrentY.value > touchStartY.value && touchStartY.value !== 0) {
        return `translateY(${touchCurrentY.value - touchStartY.value}px)`;
    }
    return 'translateY(0)';
});
</script>

<template>
    <div>
        <!-- OVERLAY -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="modelValue" @click="close" class="fixed inset-0 bg-black/60 z-[100] md:hidden"></div>
        </Transition>

        <!-- BOTTOM SHEET -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div v-if="modelValue" class="fixed inset-x-0 bottom-0 z-[100] flex items-end justify-center md:hidden pointer-events-none">
                <div
                    :class="['relative w-full bg-[#F8F9FA] rounded-t-[32px] flex flex-col shadow-2xl pointer-events-auto', heightClass]"
                    :style="{ transform: sheetTransform, transition: touchStartY === 0 ? 'transform 0.2s ease-out' : 'none' }"
                >
                    <!-- Drag Handle -->
                    <div
                        class="w-full flex justify-center pt-5 pb-5 cursor-grab active:cursor-grabbing touch-none"
                        @touchstart="onTouchStart"
                        @touchmove.prevent="onTouchMove"
                        @touchend="onTouchEnd"
                    >
                        <div class="w-12 h-1.5 bg-[#6C757D]/30 rounded-full"></div>
                    </div>

                    <!-- Header -->
                    <div class="px-5 pb-0 border-b border-[#6C757D]/10 shrink-0">
                        <div class="flex items-center justify-between mb-4">
                            <h2 v-if="title" class="text-lg font-extrabold text-[#0A2540] capitalize">{{ title }}</h2>
                            <slot name="header-content"></slot>
                            <button
                                @click="close"
                                class="w-8 h-8 rounded-full bg-white border border-[#6C757D]/20 flex items-center justify-center text-[#0A2540] shadow-sm active:scale-95 transition ml-auto"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <slot name="tabs"></slot>
                    </div>

                    <!-- Slider Content Area -->
                    <div class="relative w-full flex-1 overflow-hidden">
                        <slot></slot>
                    </div>

                    <!-- Footer Action Bar -->
                    <slot name="footer"></slot>
                </div>
            </div>
        </Transition>
    </div>
</template>
