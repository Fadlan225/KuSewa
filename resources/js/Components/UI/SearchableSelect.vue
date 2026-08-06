<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt => opt.name.toLowerCase().includes(query));
});

const selectedOption = computed(() => {
    return props.options.find(opt => opt.code === props.modelValue);
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 50);
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.code);
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Trigger Button -->
        <button 
            type="button" 
            @click="toggleDropdown"
            class="w-full h-[48px] border rounded-[12px] px-4 text-[14px] text-left transition-all outline-none shadow-[0_2px_4px_rgba(0,0,0,0.02)] flex items-center justify-between"
            :class="[
                error ? 'border-red-500 focus:ring-4 focus:ring-red-500/20' : 'border-muted/20 focus:border-primary focus:ring-4 focus:ring-primary/20',
                disabled ? 'bg-background cursor-not-allowed text-muted' : 'bg-white cursor-pointer',
                !selectedOption ? 'text-muted' : 'text-text'
            ]"
        >
            <span class="truncate block w-full pr-4">
                {{ selectedOption ? selectedOption.name : placeholder }}
            </span>
            <svg class="w-4 h-4 text-muted shrink-0 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div 
            v-if="isOpen" 
            class="absolute z-50 w-full mt-2 bg-white border border-muted/10 rounded-[12px] shadow-lg overflow-hidden flex flex-col"
            style="max-height: 280px;"
        >
            <!-- Search Input -->
            <div class="p-2 border-b border-muted/10 shrink-0 bg-background/50">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        ref="searchInputRef"
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari..." 
                        class="w-full h-9 pl-9 pr-4 text-[13px] border border-muted/20 rounded-[8px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 bg-white"
                    />
                </div>
            </div>

            <!-- Options List -->
            <div class="overflow-y-auto flex-grow p-1">
                <div v-if="filteredOptions.length === 0" class="px-4 py-3 text-[13px] text-muted text-center">
                    Tidak ditemukan.
                </div>
                <button
                    v-else
                    v-for="option in filteredOptions"
                    :key="option.code"
                    type="button"
                    @click="selectOption(option)"
                    class="w-full text-left px-4 py-2.5 text-[14px] rounded-[8px] transition-colors hover:bg-background flex items-center justify-between group"
                    :class="modelValue === option.code ? 'bg-primary/10 text-secondary font-medium' : 'text-text'"
                >
                    <span class="truncate">{{ option.name }}</span>
                    <svg v-if="modelValue === option.code" class="w-4 h-4 text-secondary opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
