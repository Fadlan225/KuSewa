<script setup>
import { computed } from 'vue';
import { useDynamicPlaceholder } from '@/Composables/useDynamicPlaceholder';

const props = defineProps({
    placeholders: {
        type: Array,
        default: () => []
    },
    isFocused: {
        type: Boolean,
        default: false
    },
    hasValue: {
        type: Boolean,
        default: false
    },
    intervalMs: {
        type: Number,
        default: 3000
    },
    offsetClass: {
        type: String,
        default: 'left-0'
    }
});

const { currentPlaceholder, currentIndex } = useDynamicPlaceholder(props.placeholders, props.intervalMs);

const show = computed(() => !props.isFocused && !props.hasValue);
</script>

<template>
    <div :class="['absolute inset-y-0 right-10 flex items-center pointer-events-none overflow-hidden', offsetClass]">
        <Transition name="fade">
            <div v-show="show" class="w-full h-full relative flex items-center">
                <Transition name="slide-up">
                    <span 
                        :key="currentIndex" 
                        class="absolute left-0 w-full truncate inherit-color"
                    >
                        {{ currentPlaceholder }}
                    </span>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Slide up animation for text changing */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.4s ease-in-out;
}
.slide-up-enter-from {
    opacity: 0;
    transform: translateY(15px);
}
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-15px);
}

/* Fade in/out for the whole placeholder container when focused/typing */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
