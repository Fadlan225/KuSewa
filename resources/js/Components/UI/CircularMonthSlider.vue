<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 1
    },
    min: {
        type: Number,
        default: 1
    },
    max: {
        type: Number,
        default: 12
    },
    size: {
        type: Number,
        default: 180
    },
    strokeWidth: {
        type: Number,
        default: 16
    }
});

const emit = defineEmits(['update:modelValue']);

const container = ref(null);
const isDragging = ref(false);

const center = computed(() => props.size / 2);
const radius = computed(() => center.value - props.strokeWidth - 4); // inner padding
const circumference = computed(() => 2 * Math.PI * radius.value);

// Value calculation
const value = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    value.value = newVal;
});

// Calculate the stroke dash offset based on value
const dashoffset = computed(() => {
    const fraction = (value.value - props.min) / (props.max - props.min);
    return circumference.value - (fraction * circumference.value);
});

// Angle of thumb
const thumbAngle = computed(() => {
    const fraction = (value.value - props.min) / (props.max - props.min);
    // Start at -90deg (top)
    return (fraction * 360) - 90;
});

const thumbX = computed(() => {
    const angleRad = thumbAngle.value * (Math.PI / 180);
    return center.value + radius.value * Math.cos(angleRad);
});

const thumbY = computed(() => {
    const angleRad = thumbAngle.value * (Math.PI / 180);
    return center.value + radius.value * Math.sin(angleRad);
});

const updateValueFromEvent = (e) => {
    if (!container.value) return;
    
    const rect = container.value.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    
    const x = clientX - rect.left - center.value;
    const y = clientY - rect.top - center.value;
    
    let angle = Math.atan2(y, x) * (180 / Math.PI);
    angle += 90; // offset to make top 0
    if (angle < 0) angle += 360;
    
    let fraction = angle / 360;
    
    // Snap to nearest integer step
    let rawValue = props.min + fraction * (props.max - props.min);
    let snappedValue = Math.round(rawValue);
    
    if (snappedValue < props.min) snappedValue = props.min;
    if (snappedValue > props.max) snappedValue = props.max;
    
    value.value = snappedValue;
    emit('update:modelValue', snappedValue);
};

const increment = () => {
    if (value.value < props.max) {
        value.value++;
        emit('update:modelValue', value.value);
    }
};

const decrement = () => {
    if (value.value > props.min) {
        value.value--;
        emit('update:modelValue', value.value);
    }
};

const startDrag = (e) => {
    isDragging.value = true;
    updateValueFromEvent(e);
};

const onDrag = (e) => {
    if (isDragging.value) {
        updateValueFromEvent(e);
    }
};

const stopDrag = () => {
    isDragging.value = false;
};

onMounted(() => {
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
    window.addEventListener('touchmove', onDrag, { passive: false });
    window.addEventListener('touchend', stopDrag);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
    window.removeEventListener('touchmove', onDrag);
    window.removeEventListener('touchend', stopDrag);
});
</script>

<template>
    <div class="flex items-center justify-between gap-4 w-full px-2">
        <!-- Minus Button -->
        <button 
            @click="decrement" 
            :disabled="value <= min"
            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-[#0A2540] hover:bg-gray-100 hover:shadow-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed shrink-0 border border-gray-200"
        >
            <i class="fa-solid fa-minus"></i>
        </button>

        <div class="relative flex items-center justify-center select-none" :style="{ width: size + 'px', height: size + 'px' }">
            <svg
                ref="container"
                :width="size"
                :height="size"
                class="touch-none cursor-pointer drop-shadow-sm"
                @mousedown="startDrag"
                @touchstart.prevent="startDrag"
            >
                <!-- Background track with soft gradient styling -->
                <circle
                    :cx="center"
                    :cy="center"
                    :r="radius"
                    fill="none"
                    stroke="#F0F0F0"
                    :stroke-width="strokeWidth"
                    class="transition-colors"
                />
                
                <!-- Generate small dots for each month on the track -->
                <g v-for="i in max" :key="i">
                    <circle
                        :cx="center + radius * Math.cos((((i - min) / (max - min)) * 360 - 90) * (Math.PI / 180))"
                        :cy="center + radius * Math.sin((((i - min) / (max - min)) * 360 - 90) * (Math.PI / 180))"
                        r="1.5"
                        fill="#CFCFCF"
                    />
                </g>

                <!-- Active track (Primary color) -->
                <circle
                    :cx="center"
                    :cy="center"
                    :r="radius"
                    fill="none"
                    stroke="#FFC000"
                    :stroke-width="strokeWidth"
                    stroke-linecap="round"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="dashoffset"
                    class="transition-all duration-75 ease-linear"
                    style="transform-origin: center; transform: rotate(-90deg);"
                />
                
                <!-- Thumb -->
                <circle
                    :cx="thumbX"
                    :cy="thumbY"
                    :r="strokeWidth / 1.5"
                    fill="white"
                    class="drop-shadow-md transition-all duration-75 ease-linear pointer-events-none"
                />
            </svg>

            <!-- Center Text -->
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none bg-white rounded-full m-8 shadow-[inset_0_2px_8px_rgba(0,0,0,0.05)] border border-gray-100">
                <span class="text-3xl font-extrabold text-[#0A2540]">{{ value }}</span>
                <span class="text-xs font-bold text-gray-400 capitalize">Bulan</span>
            </div>
        </div>

        <!-- Plus Button -->
        <button 
            @click="increment" 
            :disabled="value >= max"
            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-[#0A2540] hover:bg-gray-100 hover:shadow-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed shrink-0 border border-gray-200"
        >
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
</template>

<style scoped>
svg {
    touch-action: none;
}
</style>
