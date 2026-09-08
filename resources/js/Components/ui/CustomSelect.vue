<script setup>
import { ChevronDown } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

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
    },
    fullWidth: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);
const dropdownStyle = ref({});

const updatePosition = () => {
    if (!dropdownRef.value) return;
    const rect = dropdownRef.value.getBoundingClientRect();
    dropdownStyle.value = {
        top: `${rect.bottom + window.scrollY}px`,
        left: `${rect.left + window.scrollX}px`,
        width: `${rect.width}px`
    };
};

const toggleDropdown = async () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
        if (isOpen.value) {
            await nextTick();
            updatePosition();
            window.addEventListener('scroll', updatePosition, true);
            window.addEventListener('resize', updatePosition);
        } else {
            window.removeEventListener('scroll', updatePosition, true);
            window.removeEventListener('resize', updatePosition);
        }
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.value !== undefined ? option.value : option);
    isOpen.value = false;
    window.removeEventListener('scroll', updatePosition, true);
    window.removeEventListener('resize', updatePosition);
};

const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        // also check if the click is inside the teleported dropdown
        const dropdownElement = document.getElementById('custom-select-dropdown');
        if (dropdownElement && dropdownElement.contains(e.target)) return;
        
        isOpen.value = false;
        window.removeEventListener('scroll', updatePosition, true);
        window.removeEventListener('resize', updatePosition);
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    window.removeEventListener('scroll', updatePosition, true);
    window.removeEventListener('resize', updatePosition);
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
    <div class="relative w-full min-w-[160px]" :class="[isOpen ? 'z-[100]' : 'z-10', fullWidth ? '' : 'md:w-auto']" ref="dropdownRef">
        <!-- Trigger -->
        <div
            @click.stop="toggleDropdown"
            class="flex items-center justify-between text-sm rounded-md px-4 py-2.5 transition-colors relative select-none cursor-pointer"
            :class="[
                isOpen
                    ? 'border border-[#FFC000] bg-white ring-4 ring-[#FFC000]/20'
                    : 'border border-slate-200 hover:border-[#FFC000] hover:bg-[#FFC000]/5'
            ]"
        >
            <span class="text-slate-600 truncate pr-4">{{ selectedLabel }}</span>
            <ChevronDown class="text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
        </div>

        <!-- Dropdown Menu Teleported -->
        <Teleport to="body">
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
                    id="custom-select-dropdown"
                    :style="dropdownStyle"
                    class="absolute z-[9999] mt-1 bg-white border border-slate-200 shadow-lg py-1 max-h-64 overflow-y-auto custom-scrollbar rounded-md"
                >
                    <div
                        v-for="(option, index) in normalizedOptions"
                        :key="index"
                        @click.stop="selectOption(option)"
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
        </Teleport>
    </div>
</template>
