<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: 'Pilih',
    },
    disabled: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.value !== undefined ? option.value : option);
    isOpen.value = false;
};

const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const normalizedOptions = computed(() => {
    return props.options.map(opt => {
        if (typeof opt === 'object') {
            return opt;
        }
        return { label: opt, value: opt };
    });
});

const selectedLabel = computed(() => {
    const selected = normalizedOptions.value.find(opt => opt.value === props.modelValue);
    return selected ? selected.label : props.placeholder;
});
</script>

<template>
    <div class="relative w-full md:w-auto min-w-[160px]" :class="isOpen ? 'z-[100]' : 'z-10'" ref="dropdownRef">
        <!-- Trigger -->
        <div
            @click="toggleDropdown"
            class="flex items-center justify-between text-sm rounded-md px-4 py-2.5 transition-colors relative select-none"
            :class="[
                isOpen
                    ? 'border border-[#FFC000] bg-white ring-4 ring-[#FFC000]/20'
                    : 'border border-slate-200 hover:border-[#FFC000] hover:bg-[#FFC000]/5'
            ]"
        >
            <span class="text-slate-600 truncate pr-4">{{ selectedLabel }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
        </div>

        <!-- Dropdown Menu -->
        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute z-[100] w-full mt-1 bg-white border border-slate-200 shadow-lg py-1 max-h-96 overflow-y-auto hide-scrollbar"
            >
                <div
                    v-for="(option, index) in normalizedOptions"
                    :key="index"
                    @click="selectOption(option)"
                    class="px-4 py-2.5 text-xs cursor-pointer transition-colors select-none"
                    :class="[
                        modelValue === option.value
                            ? 'bg-[#FFC000] text-[#0A2540] font-bold'
                            : 'text-slate-700 hover:bg-[#FFC000]/10 hover:text-[#0A2540]'
                    ]"
                >
                    {{ option.label }}
                </div>
            </div>
        </transition>
    </div>
</template>
