<template>
    <div class="relative w-full" ref="containerRef">
        <!-- Input Area -->
        <div 
            class="min-h-[42px] flex items-center w-full bg-white border border-slate-300 rounded-md px-3 py-1.5 focus-within:ring-2 focus-within:ring-[#0A2540] focus-within:border-transparent transition cursor-text"
            @click="focusInput"
        >
            <!-- Search Input -->
            <input
                ref="inputRef"
                type="text"
                v-model="searchQuery"
                @focus="openDropdown"
                @input="onSearch"
                :placeholder="placeholder"
                class="flex-1 w-full bg-transparent border-none p-0 m-0 text-sm text-slate-700 focus:ring-0 focus:outline-none focus:border-transparent shadow-none"
                style="box-shadow: none !important; border: none !important; outline: none !important;"
            />
        </div>

        <!-- Selected Tags (Di Bawah Input) -->
        <div v-if="modelValue.length > 0" class="mt-3 flex flex-wrap gap-2">
            <span 
                v-for="id in modelValue" 
                :key="id"
                class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold px-2.5 py-1.5 rounded-md"
            >
                {{ getOptionName(id) }}
                <button type="button" @click.stop="removeTag(id)" class="hover:text-rose-500 transition focus:outline-none shrink-0 w-4 h-4 flex items-center justify-center ml-1">
                    <i class="fa-solid fa-xmark text-[11px]"></i>
                </button>
            </span>
        </div>

        <!-- Dropdown -->
        <div v-if="isOpen" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <div v-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-slate-500">
                Data tidak ditemukan.
            </div>
            <ul v-else class="py-1">
                <li 
                    v-for="opt in filteredOptions" 
                    :key="opt.code"
                    @click.stop="selectOption(opt)"
                    class="px-4 py-2.5 text-sm cursor-pointer hover:bg-slate-50 transition flex justify-between items-center"
                    :class="isSelected(opt) ? 'bg-[#0A2540]/5 font-semibold text-[#0A2540]' : 'text-slate-700'"
                >
                    <span class="truncate">{{ opt.name }}</span>
                    <i v-if="isSelected(opt)" class="fa-solid fa-check text-[#FFC000] shrink-0 ml-2"></i>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, default: () => [] }, // { code: ID, name: Label }
    placeholder: { type: String, default: 'Ketik untuk mencari...' }
});

const emit = defineEmits(['update:modelValue']);

const containerRef = ref(null);
const inputRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');

const openDropdown = () => { isOpen.value = true; };
const closeDropdown = () => { isOpen.value = false; searchQuery.value = ''; };

const focusInput = () => {
    inputRef.value?.focus();
    openDropdown();
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        closeDropdown();
    }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter(o => o.name.toLowerCase().includes(q));
});

const getOptionName = (code) => {
    const opt = props.options.find(o => o.code == code);
    return opt ? opt.name : code;
};

const isSelected = (opt) => props.modelValue.includes(opt.code);

const selectOption = (opt) => {
    let val = [...props.modelValue];
    if (val.includes(opt.code)) {
        val = val.filter(v => v !== opt.code);
    } else {
        val.push(opt.code);
    }
    emit('update:modelValue', val);
    searchQuery.value = '';
    inputRef.value?.focus();
};

const removeTag = (code) => {
    emit('update:modelValue', props.modelValue.filter(v => v !== code));
};

const onDelete = () => {
    if (searchQuery.value === '' && props.modelValue.length > 0) {
        let val = [...props.modelValue];
        val.pop();
        emit('update:modelValue', val);
    }
};
</script>
